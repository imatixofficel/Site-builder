<?php
/* Core bootstrap: config, error handling, base path, HTTPS/IP helpers, security headers. */
if (!defined('SB')) { define('SB', 1); }
if (!defined('SB_DEPTH')) { define('SB_DEPTH', 0); }
define('SB_ROOT', dirname(__DIR__));

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    http_response_code(500);
    exit('PHP 7.4 or newer is required.');
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', SB_ROOT . '/data/error.log');
header_remove('X-Powered-By');

function sb_config($reload = false)
{
    static $cfg = null;
    if ($cfg === null || $reload) {
        $f = SB_ROOT . '/data/config.php';
        $cfg = is_file($f) ? include $f : false;
        if (is_array($cfg) && !empty($cfg['timezone'])) {
            @date_default_timezone_set($cfg['timezone']);
        }
    }
    return $cfg;
}

function sb_base_path()
{
    $p = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php'));
    for ($i = 0; $i < SB_DEPTH; $i++) { $p = dirname($p); }
    $p = rtrim($p, '/');
    return ($p === '.' || $p === '\\') ? '' : $p;
}
define('SB_BASE', sb_base_path());

function sb_behind_proxy()
{
    $c = sb_config();
    return is_array($c) && !empty($c['behind_proxy']);
}

function sb_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') { return true; }
    if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) { return true; }
    if (sb_behind_proxy() && isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') { return true; }
    return false;
}

function sb_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    if (sb_behind_proxy()) {
        foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP') as $h) {
            if (!empty($_SERVER[$h]) && filter_var($_SERVER[$h], FILTER_VALIDATE_IP)) { return $_SERVER[$h]; }
        }
    }
    return $ip;
}

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sb_redirect($url)
{
    header('Location: ' . $url, true, 303);
    exit;
}

function sb_send_headers($nonce, $admin = false)
{
    $csp = "default-src 'self'; "
        . "script-src 'self' 'nonce-" . $nonce . "'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' data: https://fonts.gstatic.com; "
        . "img-src 'self' data: https:; "
        . "connect-src 'self'; "
        . "object-src 'none'; base-uri 'self'; form-action 'self'; "
        . ($admin ? "frame-ancestors 'none'" : "frame-ancestors 'self'");
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: ' . ($admin ? 'DENY' : 'SAMEORIGIN'));
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (sb_https()) { header('Strict-Transport-Security: max-age=31536000'); }
    if ($admin) {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Robots-Tag: noindex, nofollow');
    }
}

function sb_nonce()
{
    static $n = null;
    if ($n === null) { $n = bin2hex(random_bytes(16)); }
    return $n;
}

function sb_add_nonce($html, $nonce)
{
    return preg_replace('/<script\b(?![^>]*\bnonce=)/i', '<script nonce="' . $nonce . '"', $html);
}

function sb_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    session_name('sbsess');
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => (SB_BASE === '' ? '/' : SB_BASE . '/'),
        'secure' => sb_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ));
    session_start();
}

function csrf_token()
{
    sb_session_start();
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(24)); }
    return $_SESSION['csrf'];
}

function csrf_ok($t)
{
    sb_session_start();
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}

function sb_clean($s, $max)
{
    $s = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$s));
    if (function_exists('mb_substr')) { return mb_substr($s, 0, $max, 'UTF-8'); }
    return substr($s, 0, $max);
}

function sb_schema()
{
    static $s = null;
    if ($s === null) {
        $s = json_decode((string)file_get_contents(SB_ROOT . '/theme/schema.json'), true);
        if (!is_array($s)) { $s = array(); }
        $s += array('settings' => array(), 'collections' => array(), 'site' => array());
    }
    return $s;
}

function sb_require_installed()
{
    if (!is_array(sb_config())) {
        sb_redirect((SB_BASE === '' ? '' : SB_BASE) . '/install.php');
    }
}
