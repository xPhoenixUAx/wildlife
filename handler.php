<?php
declare(strict_types=1);
ini_set('display_errors', '0');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
require __DIR__ . '/config/server-config.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');

const SUCCESS_MESSAGE = 'Thank you! We have successfully received your request. Our team will review your information and get back to you shortly.';
const MAX_FILES = 3;
const MAX_FILE_BYTES = 5_000_000;

function wants_json(): bool {
    return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
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
    return mb_substr($value, 0, $max);
}

function render_confirmation(): never {
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
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Confirm request | Wildlife Match</title><link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/legal.css"></head><body><main class="legal-main"><a class="legal-back" href="' . $returnTo . '#request">← Return to the form</a><p class="eyebrow">One more step</p><h1>Confirm your request</h1><p>This confirmation protects the form when JavaScript is unavailable. Optional photographs must be reselected after returning to the form; they are not carried through this confirmation.</p><form method="post" action="handler.php">' . $hidden . '<input type="hidden" name="return_to" value="' . $returnTo . '"><input type="hidden" name="csrf_token" value="' . $token . '"><button class="button button--primary" type="submit">Confirm and send</button></form></main></body></html>';
    exit;
}

function rate_limited(string $ip): bool {
    $now = time();
    $sessionLast = (int) ($_SESSION['last_submit_at'] ?? 0);
    if ($sessionLast && ($now - $sessionLast) < 30) return true;
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wildlife-match-ratelimit';
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

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') respond(405, false, 'Method not allowed.');
if (!empty($_POST['website'] ?? '')) respond(200, true, SUCCESS_MESSAGE);

$token = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
    if (!wants_json()) render_confirmation();
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

if ($name === '' || mb_strlen($name) < 2) respond(422, false, 'Enter a valid name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) respond(422, false, 'Enter a valid email address.');
if (!preg_match('/^[A-Za-z0-9 -]{3,10}$/', $zip)) respond(422, false, 'Enter a valid ZIP or postal code.');
if (!in_array($service, $allowedServices, true)) respond(422, false, 'Select a valid service.');
if ($message === '') respond(422, false, 'Describe what you are noticing.');
if (!isset($_POST['consent'])) respond(422, false, 'Consent is required to share the request.');
if (!defined('FORM_DESTINATION_EMAIL') || !filter_var(FORM_DESTINATION_EMAIL, FILTER_VALIDATE_EMAIL)) respond(500, false, 'The request destination has not been configured.');

$files = $_FILES['photos'] ?? null;
$attachments = [];
if ($files && is_array($files['name'] ?? null)) {
    if (count($files['name']) > MAX_FILES) respond(422, false, 'Upload no more than three images.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    foreach ($files['name'] as $index => $unused) {
        $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        $tmp = (string) ($files['tmp_name'][$index] ?? '');
        $size = (int) ($files['size'][$index] ?? 0);
        if ($error !== UPLOAD_ERR_OK || $size < 1 || $size > MAX_FILE_BYTES || !is_uploaded_file($tmp)) respond(422, false, 'One of the images could not be accepted.');
        $mime = (string) $finfo->file($tmp);
        if (!isset($allowedMime[$mime])) respond(422, false, 'Only JPG, PNG and WebP images are accepted.');
        $attachments[] = ['path' => $tmp, 'mime' => $mime, 'name' => bin2hex(random_bytes(12)) . '.' . $allowedMime[$mime]];
    }
}

$host = preg_replace('/[^A-Za-z0-9.-]/', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
$subject = 'Wildlife Match request: ' . $service;
$plain = "Name: {$name}\nEmail: {$email}\nZIP: {$zip}\nService: {$service}\nConsent: yes\n\nRequest:\n{$message}\n";
$headers = ['MIME-Version: 1.0', 'From: no-reply@' . $host, 'Reply-To: ' . $email];

if ($attachments) {
    $boundary = 'wm_' . bin2hex(random_bytes(18));
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

if (!@mail(FORM_DESTINATION_EMAIL, $subject, $body, implode("\r\n", $headers))) respond(500, false, 'We could not send the request. Please try again later.');
$_SESSION['last_submit_at'] = time();
respond(200, true, SUCCESS_MESSAGE);
