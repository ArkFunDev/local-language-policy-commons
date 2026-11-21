<?php
/**
 * Security helpers: output escaping, headers, sessions, CSRF, input cleaning, moderator auth.
 */

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function llpc_is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (llpc_config('trust_proxy_headers')
        && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return false;
}

function llpc_send_security_headers()
{
    if (headers_sent()) {
        return;
    }
    // No JavaScript and no third-party resources are used by this application.
    header("Content-Security-Policy: default-src 'none'; style-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (llpc_is_https()) {
        header('Strict-Transport-Security: max-age=15552000');
    }
}

function llpc_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('llpc_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => llpc_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function llpc_boot()
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    if (!llpc_config('debug')) {
        ini_set('display_errors', '0');
    }
    llpc_send_security_headers();
    llpc_start_session();
}

/* ---------- Input helpers (always strings, never arrays) ---------- */

function llpc_clean_text($value)
{
    $value = (string) $value;
    if (!preg_match('//u', $value)) {
        return '';
    }
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    return $clean === null ? '' : trim($clean);
}

function llpc_get($key)
{
    return (isset($_GET[$key]) && is_string($_GET[$key])) ? llpc_clean_text($_GET[$key]) : '';
}

function llpc_post($key)
{
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? llpc_clean_text($_POST[$key]) : '';
}

function llpc_is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}

function llpc_len($text)
{
    return function_exists('mb_strlen') ? mb_strlen((string) $text, 'UTF-8') : strlen((string) $text);
}

function llpc_contains($haystack, $needle)
{
    if ($needle === '') {
        return true;
    }
    if (function_exists('mb_stripos')) {
        return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
    }
    return stripos($haystack, $needle) !== false;
}

function llpc_redirect($url)
{
    header('Location: ' . $url, true, 303);
    exit;
}

/* ---------- CSRF ---------- */

function llpc_csrf_token()
{
    if (empty($_SESSION['llpc_csrf'])) {
        $_SESSION['llpc_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['llpc_csrf'];
}

function llpc_csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . h(llpc_csrf_token()) . '">';
}

function llpc_csrf_valid()
{
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    return isset($_SESSION['llpc_csrf'])
        && is_string($token)
        && hash_equals($_SESSION['llpc_csrf'], $token);
}

/** Hidden anti-bot field. Real users never fill it in. */
function llpc_honeypot_field()
{
    return '<div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" value="" tabindex="-1" autocomplete="off"></label></div>';
}

function llpc_honeypot_tripped()
{
    return llpc_post('website') !== '';
}

/* ---------- Flash messages (store a translation key, not text) ---------- */

function llpc_flash_set($type, $key)
{
    $_SESSION['llpc_flash'] = ['type' => $type, 'key' => $key];
}

function llpc_flash_take()
{
    if (empty($_SESSION['llpc_flash']) || !is_array($_SESSION['llpc_flash'])) {
        return null;
    }
    $flash = $_SESSION['llpc_flash'];
    unset($_SESSION['llpc_flash']);
    return $flash;
}

/* ---------- Moderator authentication ---------- */

function llpc_moderator_configured()
{
    $hash = llpc_config('moderator_password_hash', '');
    return is_string($hash) && $hash !== '';
}

function llpc_is_moderator()
{
    if (empty($_SESSION['llpc_moderator'])) {
        return false;
    }
    $last = isset($_SESSION['llpc_last']) ? (int) $_SESSION['llpc_last'] : 0;
    if ($last > 0 && (time() - $last) > 3600) {
        unset($_SESSION['llpc_moderator']);
        return false;
    }
    $_SESSION['llpc_last'] = time();
    return true;
}

function llpc_require_moderator()
{
    if (!llpc_is_moderator()) {
        llpc_redirect('login.php');
    }
}

/** Standard guard for every moderator POST action. Returns an error key or null. */
function llpc_check_moderator_post()
{
    if (!llpc_is_moderator()) {
        return 'err.forbidden';
    }
    if (!llpc_csrf_valid()) {
        return 'err.csrf';
    }
    return null;
}
