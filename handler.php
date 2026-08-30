<?php
declare(strict_types=1);
ini_set('display_errors', '0');

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

function site_pages(): array {
    return ['index.html', 'wildlife-removal.html', 'attic-cleanup-restoration.html',
        'entry-point-sealing-prevention.html', 'privacy.html', 'terms.html', 'cookie-policy.html'];
}

function render_site_html(string $page, array $config): string {
    if (!in_array($page, site_pages(), true)) throw new RuntimeException('Unknown page.');
    $source = file_get_contents(__DIR__ . '/' . $page);
    if ($source === false) throw new RuntimeException('Page template is unavailable.');

    $document = new DOMDocument('1.0', 'UTF-8');
    $previousErrors = libxml_use_internal_errors(true);
    try {
        $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $source, LIBXML_NONET);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);
    }
    if (!$loaded) throw new RuntimeException('Page template is invalid.');
    foreach (iterator_to_array($document->childNodes) as $child) {
        if ($child->nodeType === XML_PI_NODE) $document->removeChild($child);
    }
    $xpath = new DOMXPath($document);
    $classQuery = static fn(string $class): string =>
        'contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")';
    $setText = static function (DOMNode $node, string $text) use ($document): void {
        while ($node->firstChild) $node->removeChild($node->firstChild);
        $node->appendChild($document->createTextNode($text));
    };
    $brand = trim((string) ($config['brand'] ?? 'Wildlife Match')) ?: 'Wildlife Match';
    $format = static fn(string $text): string => str_replace('{brand}', $brand, $text);

    // Update existing template text without interpreting config values as HTML.
    foreach ($xpath->query('//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::textarea)]') as $node) {
        $node->nodeValue = str_replace('Wildlife Match', $brand, $node->nodeValue);
    }
    foreach ($xpath->query('//*[@aria-label or @title or @alt]') as $element) {
        foreach (['aria-label', 'title', 'alt'] as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $element->setAttribute($attribute, str_replace('Wildlife Match', $brand, $element->getAttribute($attribute)));
            }
        }
    }
    foreach ($xpath->query('//meta[@content]') as $meta) {
        $meta->setAttribute('content', str_replace('Wildlife Match', $brand, $meta->getAttribute('content')));
    }

    $textValues = array_merge($config, [
        'companyName' => $config['company'] ?? null,
        'copyrightYear' => date('Y'),
        'legalLastUpdated' => $config['legal']['lastUpdated'] ?? null,
        'governingLaw' => $config['legal']['governingLaw'] ?? null,
        'legalVenue' => $config['legal']['venue'] ?? null
    ]);
    foreach ($xpath->query('//*[@data-config]') as $element) {
        $value = $textValues[$element->getAttribute('data-config')] ?? null;
        if (is_scalar($value)) $setText($element, (string) $value);
    }
    foreach ($xpath->query('//*[@data-config-href="email"]') as $element) {
        if (!empty($config['email'])) $element->setAttribute('href', 'mailto:' . $config['email']);
    }

    $brandParts = preg_split('/\s+/u', $brand, 2);
    foreach (['rail-wordmark' => 'span', 'footer-brand' => null, 'hero__brand' => 'strong'] as $class => $leadTag) {
        foreach ($xpath->query('//*[' . $classQuery($class) . ']') as $wordmark) {
            $setText($wordmark, '');
            $lead = $document->createTextNode($brandParts[0]);
            if ($leadTag !== null) {
                $wrapper = $document->createElement($leadTag);
                $wrapper->appendChild($lead);
                $lead = $wrapper;
            }
            $wordmark->appendChild($lead);
            if (isset($brandParts[1])) {
                $accent = $document->createElement('em');
                $accent->appendChild($document->createTextNode($brandParts[1]));
                $wordmark->appendChild($document->createTextNode(' '));
                $wordmark->appendChild($accent);
            }
        }
    }

    if (!empty($config['logo'])) {
        foreach ($xpath->query('//*[' . $classQuery('rail-mark') . ' or ' . $classQuery('mobile-menu__head') . ' or ' . $classQuery('cookie-banner__mark') . ']//img') as $image) {
            $image->setAttribute('src', (string) $config['logo']);
        }
        foreach ($xpath->query('//link[contains(concat(" ", normalize-space(@rel), " "), " icon ")]') as $icon) {
            $icon->setAttribute('href', (string) $config['logo']);
            $icon->removeAttribute('type');
        }
    }

    if (isset($config['disclaimer'])) {
        $text = trim(preg_replace('/^Disclaimer:\s*/i', '', $format((string) $config['disclaimer'])) ?? '');
        foreach ($xpath->query('//*[' . $classQuery('site-footer') . ']') as $footer) {
            $disclaimer = $xpath->query('.//*[' . $classQuery('site-footer__disclaimer') . ']', $footer)->item(0);
            if ($text === '') {
                if ($disclaimer) $disclaimer->parentNode->removeChild($disclaimer);
                continue;
            }
            if (!$disclaimer) {
                $disclaimer = $document->createElement('p');
                $disclaimer->setAttribute('class', 'site-footer__disclaimer');
                $bottom = $xpath->query('./*[' . $classQuery('site-footer__bottom') . ']', $footer)->item(0);
                $footer->insertBefore($disclaimer, $bottom);
            }
            $setText($disclaimer, '');
            $label = $document->createElement('strong');
            $setText($label, 'Disclaimer:');
            $disclaimer->appendChild($label);
            $disclaimer->appendChild($document->createTextNode(' ' . $text));
        }
    }

    $head = $document->getElementsByTagName('head')->item(0);
    if (isset($config['pageTitles'][$page])) {
        $title = $document->getElementsByTagName('title')->item(0);
        if (!$title) $title = $head->appendChild($document->createElement('title'));
        $setText($title, $format((string) $config['pageTitles'][$page]));
    }
    if (isset($config['pageDescriptions'][$page])) {
        $description = $xpath->query('//meta[@name="description"]')->item(0);
        if (!$description) {
            $description = $head->appendChild($document->createElement('meta'));
            $description->setAttribute('name', 'description');
        }
        $description->setAttribute('content', $format((string) $config['pageDescriptions'][$page]));
    }
    // A new config URL prevents stale browser settings from overriding fresh HTML.
    $configVersion = substr(hash('sha256', json_encode($config, JSON_THROW_ON_ERROR)), 0, 16);
    foreach ($xpath->query('//script[@src="config/site-config.js"]') as $script) {
        $script->setAttribute('src', 'config/site-config.js?v=' . $configVersion);
    }
    $document->documentElement->setAttribute('data-config-rendered', 'true');
    $html = $document->saveHTML();
    if ($html === false) throw new RuntimeException('Page could not be rendered.');
    return $html;
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
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Confirm request | ' . $safeBrand . '</title>' . $favicon . '<link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/legal.css"></head><body><main class="legal-main"><a class="legal-back" href="' . $returnTo . '#request">← Return to the form</a><h1>Confirm your request</h1><p>This confirmation protects the form when JavaScript is unavailable. Optional photographs must be reselected after returning to the form; they are not carried through this confirmation.</p><form method="post" action="handler.php">' . $hidden . '<input type="hidden" name="return_to" value="' . $returnTo . '"><input type="hidden" name="csrf_token" value="' . $token . '"><button class="button button--primary" type="submit">Confirm and send</button></form></main></body></html>';
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
$requestPath = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/handler.php', PHP_URL_PATH));
$requestedPage = PHP_SAPI === 'cli-server'
    ? ltrim($requestPath, '/')
    : (str_ends_with($requestPath, '/') ? '' : basename($requestPath));
if ($requestedPage === '') $requestedPage = 'index.html';
if (in_array($method, ['GET', 'HEAD'], true) && in_array($requestedPage, site_pages(), true)) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');
    try {
        $html = render_site_html($requestedPage, load_site_config());
        if ($method !== 'HEAD') echo $html;
    } catch (Throwable $error) {
        error_log('Site rendering failed: ' . $error->getMessage());
        http_response_code(503);
        if ($method !== 'HEAD') echo 'The website is temporarily unavailable.';
    }
    exit;
}
// Run locally with: php -S 127.0.0.1:8080 handler.php
if (PHP_SAPI === 'cli-server' && $requestPath !== '/handler.php') return false;

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');
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
