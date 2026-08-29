<?php
declare(strict_types=1);
ini_set('display_errors', '0');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');

const MAX_FILES = 3;
const MAX_FILE_BYTES = 5_000_000;
const SUCCESS_MESSAGE = 'Thank you! We have successfully received your request. Our team will review your information and get back to you shortly.';

function wants_json(): bool {
    return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

function load_site_config(): array {
    $source = @file_get_contents(__DIR__ . '/config/site-config.js');
    if (!is_string($source) || !preg_match('/window\.SITE_CONFIG\s*=\s*(\{.*\})\s*;\s*$/s', $source, $matches)) {
        throw new RuntimeException('Site configuration is unavailable.');
    }
    try {
        $config = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        throw new RuntimeException('Site configuration is invalid.', 0, $error);
    }
    if (!is_array($config)) throw new RuntimeException('Site configuration is invalid.');
    return $config;
}

function fallback_location(): string {
    $allowed = ['index.html', 'wildlife-removal.html', 'attic-cleanup-restoration.html', 'entry-point-sealing-prevention.html'];
    $posted = basename((string) ($_POST['return_to'] ?? ''));
    if (in_array($posted, $allowed, true)) return $posted;
    $path = basename((string) parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH));
    return in_array($path, $allowed, true) ? $path : 'index.html';
}

function respond(int $status, bool $ok, string $message): never {
    http_response_code($status);
    if (wants_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_SLASHES);
        exit;
    }
    $_SESSION['form_flash'] = ['ok' => $ok, 'message' => $message];
    header('Location: ' . fallback_location() . ($ok ? '#request-success' : '#request'), true, 303);
    exit;
}

function clean_text(string $value, int $max): string {
    $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function text_length(string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function image_mime_type(string $path): string {
    if (class_exists('finfo')) {
        $detector = new finfo(FILEINFO_MIME_TYPE);
        return (string) $detector->file($path);
    }
    if (function_exists('getimagesize')) {
        $details = @getimagesize($path);
        return is_array($details) ? (string) ($details['mime'] ?? '') : '';
    }
    return '';
}

function is_placeholder_email(string $email): bool {
    return str_ends_with(strtolower($email), '@example.com');
}

function render_confirmation(string $brand, string $logo): never {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $fields = ['name', 'email', 'zip', 'service', 'message'];
    $hidden = '';
    foreach ($fields as $field) {
        $value = htmlspecialchars((string) ($_POST[$field] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hidden .= '<input type="hidden" name="' . $field . '" value="' . $value . '">';
    }
    if (isset($_POST['consent'])) $hidden .= '<input type="hidden" name="consent" value="1">';
    $token = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
    $returnTo = 'index.html';
    $safeBrand = htmlspecialchars($brand, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeLogo = htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $favicon = $safeLogo !== '' ? '<link rel="icon" href="' . $safeLogo . '">' : '';
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Confirm request | ' . $safeBrand . '</title>' . $favicon . '<link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/legal.css"></head><body><main class="legal-main"><a class="legal-back" href="' . $returnTo . '#request">← Return to the form</a><p class="eyebrow">' . $safeBrand . '</p><h1>Confirm your request</h1><p>This confirmation protects the form when JavaScript is unavailable. Optional photographs must be reselected after returning to the form; they are not carried through this confirmation.</p><form method="post" action="handler.php">' . $hidden . '<input type="hidden" name="return_to" value="' . $returnTo . '"><input type="hidden" name="csrf_token" value="' . $token . '"><button class="button button--primary" type="submit">Confirm and send</button></form></main></body></html>';
    exit;
}

function rate_limited(string $ip): bool {
    $now = time();
    $sessionLast = (int) ($_SESSION['last_submit_at'] ?? 0);
    if ($sessionLast && ($now - $sessionLast) < 30) return true;
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'service-referral-ratelimit';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) return false;
    $file = $dir . DIRECTORY_SEPARATOR . hash('sha256', $ip) . '.json';
    $handle = @fopen($file, 'c+');
    if (!$handle) return false;
    $limited = false;
    if (flock($handle, LOCK_EX)) {
        $raw = stream_get_contents($handle);
        $events = array_values(array_filter((array) json_decode($raw ?: '[]', true), fn($t) => is_int($t) && $t > $now - 3600));
        $limited = count($events) >= 5 || (!empty($events) && end($events) > $now - 30);
        if (!$limited) $events[] = $now;
        ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($events)); fflush($handle); flock($handle, LOCK_UN);
    }
    fclose($handle);
    return $limited;
}

$method = (string) ($_SERVER['REQUEST_METHOD'] ?? '');
if ($method === 'GET' && ($_GET['action'] ?? '') === 'csrf') {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['token' => $_SESSION['csrf_token']], JSON_UNESCAPED_SLASHES);
    exit;
}
if ($method !== 'POST') respond(405, false, 'Method not allowed.');
try {
    $siteConfig = load_site_config();
} catch (RuntimeException) {
    respond(500, false, 'The site configuration could not be loaded.');
}
$handlerConfig = (array) ($siteConfig['handler'] ?? []);
$brand = clean_text((string) ($siteConfig['brand'] ?? 'Wildlife Match'), 100);
if ($brand === '') $brand = 'Wildlife Match';
$logo = clean_text((string) ($siteConfig['logo'] ?? ''), 300);
$allowedHost = strtolower(trim((string) ($handlerConfig['allowedHost'] ?? '')));
$requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
$requestHost = preg_replace('/:\d+$/', '', $requestHost) ?? '';
if ($allowedHost !== '' && $requestHost !== $allowedHost) respond(403, false, 'Requests from this host are not allowed.');
if (!empty($_POST['website'] ?? '')) respond(200, true, SUCCESS_MESSAGE);

$token = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
    if (!wants_json()) render_confirmation($brand, $logo);
    respond(403, false, 'Your session expired. Refresh the page and try again.');
}
$ip = clean_text((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 64);
if (rate_limited($ip)) respond(429, false, 'Please wait before sending another request.');

$name = clean_text((string) ($_POST['name'] ?? ''), 120);
$email = trim((string) ($_POST['email'] ?? ''));
$zip = clean_text((string) ($_POST['zip'] ?? ''), 10);
$service = clean_text((string) ($_POST['service'] ?? ''), 80);
$message = clean_text((string) ($_POST['message'] ?? ''), 5000);
$allowedServices = ['Wildlife Removal', 'Attic Cleanup & Restoration', 'Entry Point Sealing & Prevention'];

if ($name === '' || text_length($name) < 2) respond(422, false, 'Enter a valid name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) respond(422, false, 'Enter a valid email address.');
if (!preg_match('/^[A-Za-z0-9 -]{3,10}$/', $zip)) respond(422, false, 'Enter a valid ZIP or postal code.');
if (!in_array($service, $allowedServices, true)) respond(422, false, 'Select a valid service.');
if ($message === '') respond(422, false, 'Describe what you are noticing.');
if (!isset($_POST['consent'])) respond(422, false, 'Consent is required to share the request.');
$destinationEmail = trim((string) ($handlerConfig['recipient'] ?? ''));
if (!filter_var($destinationEmail, FILTER_VALIDATE_EMAIL) || is_placeholder_email($destinationEmail)) respond(500, false, 'The request destination has not been configured.');
$senderEmail = trim((string) ($handlerConfig['sender'] ?? ''));
if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || is_placeholder_email($senderEmail)) respond(500, false, 'The request sender has not been configured.');

$files = $_FILES['photos'] ?? null;
$attachments = [];
if ($files && is_array($files['name'] ?? null)) {
    if (count($files['name']) > MAX_FILES) respond(422, false, 'Upload no more than three images.');
    $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    foreach ($files['name'] as $index => $unused) {
        $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        $tmp = (string) ($files['tmp_name'][$index] ?? '');
        $size = (int) ($files['size'][$index] ?? 0);
        if ($error !== UPLOAD_ERR_OK || $size < 1 || $size > MAX_FILE_BYTES || !is_uploaded_file($tmp)) respond(422, false, 'One of the images could not be accepted.');
        $mime = image_mime_type($tmp);
        if (!isset($allowedMime[$mime])) respond(422, false, 'Only JPG, PNG and WebP images are accepted.');
        $attachments[] = ['path' => $tmp, 'mime' => $mime, 'name' => bin2hex(random_bytes(12)) . '.' . $allowedMime[$mime]];
    }
}

$subject = $brand . ' request: ' . $service;
$plain = "Name: {$name}\nEmail: {$email}\nZIP: {$zip}\nService: {$service}\nConsent: yes\n\nRequest:\n{$message}\n";
$headers = ['MIME-Version: 1.0', 'From: ' . $senderEmail, 'Reply-To: ' . $email];

if ($attachments) {
    $boundary = 'form_' . bin2hex(random_bytes(18));
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
    $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n{$plain}\r\n";
    foreach ($attachments as $attachment) {
        $encoded = chunk_split(base64_encode((string) file_get_contents($attachment['path'])));
        $body .= "--{$boundary}\r\nContent-Type: {$attachment['mime']}; name=\"{$attachment['name']}\"\r\nContent-Disposition: attachment; filename=\"{$attachment['name']}\"\r\nContent-Transfer-Encoding: base64\r\n\r\n{$encoded}\r\n";
    }
    $body .= "--{$boundary}--\r\n";
} else {
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $body = $plain;
}

if (!function_exists('mail') || !@mail($destinationEmail, $subject, $body, implode("\r\n", $headers))) respond(500, false, 'We could not send the request. Please try again later.');
$_SESSION['last_submit_at'] = time();
respond(200, true, SUCCESS_MESSAGE);
