<?php
/* Security helpers: rate limiting, password policy, image upload, TOTP two-factor. */
if (!defined('SB')) { exit; }

/* ---- rate limiting (stored in the attempts table) ---- */
function rate_count($key, $window)
{
    return (int)db_val('SELECT COUNT(*) FROM attempts WHERE k = ? AND created > ?', array($key, time() - $window));
}

function rate_add($key)
{
    db_exec('INSERT INTO attempts (k, created) VALUES (?, ?)', array($key, time()));
    if (mt_rand(1, 25) === 1) { db_exec('DELETE FROM attempts WHERE created < ?', array(time() - 86400)); }
}

function rate_clear($key)
{
    db_exec('DELETE FROM attempts WHERE k = ?', array($key));
}

/* ---- password policy: at least 10 chars, letters and digits ---- */
function sb_password_problem($pw)
{
    $len = function_exists('mb_strlen') ? mb_strlen($pw, 'UTF-8') : strlen($pw);
    if ($len < 10) { return 'short'; }
    if (!preg_match('/\p{L}/u', $pw) || !preg_match('/\d/', $pw)) { return 'weak'; }
    return '';
}

/* ---- colours ---- */
function sb_norm_color($c)
{
    $c = trim((string)$c);
    if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $c)) { return ''; }
    if (strlen($c) === 4) { $c = '#' . $c[1] . $c[1] . $c[2] . $c[2] . $c[3] . $c[3]; }
    return strtolower(substr($c, 0, 7));
}

/* ---- safe image upload: validate, re-encode to WebP (strips hidden payloads), random name ---- */
function sb_upload_image(array $f, &$err)
{
    $err = '';
    if (!isset($f['error']) || is_array($f['error'])) { $err = 'upload'; return ''; }
    if ($f['error'] === UPLOAD_ERR_NO_FILE) { return ''; }
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'upload'; return ''; }
    if ($f['size'] > 6 * 1024 * 1024) { $err = 'size'; return ''; }
    if (!is_uploaded_file($f['tmp_name'])) { $err = 'upload'; return ''; }
    $info = @getimagesize($f['tmp_name']);
    if (!$info) { $err = 'type'; return ''; }
    $map = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp');
    if (!isset($map[$info[2]])) { $err = 'type'; return ''; }
    if ($info[0] > 8000 || $info[1] > 8000) { $err = 'size'; return ''; }
    $name = bin2hex(random_bytes(12));
    $dir = SB_ROOT . '/uploads';
    if (!is_dir($dir) || !is_writable($dir)) { $err = 'upload'; return ''; }

    if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
        $img = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
        if ($img) {
            $w = imagesx($img);
            $h = imagesy($img);
            if ($w > 1920) {
                $res = imagescale($img, 1920, (int)round($h * 1920 / $w));
                if ($res) { imagedestroy($img); $img = $res; }
            }
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            $dest = $dir . '/' . $name . '.webp';
            $ok = @imagewebp($img, $dest, 82);
            imagedestroy($img);
            if ($ok) { @chmod($dest, 0644); return 'uploads/' . $name . '.webp'; }
        }
    }
    $dest = $dir . '/' . $name . '.' . $map[$info[2]];
    if (move_uploaded_file($f['tmp_name'], $dest)) { @chmod($dest, 0644); return 'uploads/' . basename($dest); }
    $err = 'upload';
    return '';
}

function sb_delete_upload($path)
{
    if (preg_match('#^uploads/[a-f0-9]{24}\.(webp|jpg|png|gif)$#', (string)$path)) {
        @unlink(SB_ROOT . '/' . $path);
    }
}

/* ---- TOTP (RFC 6238) for optional two-factor login ---- */
function sb_b32_encode($bin)
{
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($bin) as $ch) { $bits .= str_pad(decbin(ord($ch)), 8, '0', STR_PAD_LEFT); }
    $out = '';
    foreach (str_split($bits, 5) as $b) { $out .= $alpha[bindec(str_pad($b, 5, '0', STR_PAD_RIGHT))]; }
    return $out;
}

function sb_b32_decode($s)
{
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $s = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $s));
    $bits = '';
    foreach (str_split($s) as $ch) { $bits .= str_pad(decbin(strpos($alpha, $ch)), 5, '0', STR_PAD_LEFT); }
    $out = '';
    foreach (str_split($bits, 8) as $b) { if (strlen($b) === 8) { $out .= chr(bindec($b)); } }
    return $out;
}

function sb_totp_code($secret, $slice)
{
    $hash = hash_hmac('sha1', pack('N2', 0, $slice), sb_b32_decode($secret), true);
    $o = ord($hash[19]) & 0x0f;
    $v = ((ord($hash[$o]) & 0x7f) << 24) | ((ord($hash[$o + 1]) & 0xff) << 16) | ((ord($hash[$o + 2]) & 0xff) << 8) | (ord($hash[$o + 3]) & 0xff);
    return str_pad((string)($v % 1000000), 6, '0', STR_PAD_LEFT);
}

function sb_totp_verify($secret, $code)
{
    $code = preg_replace('/\s+/', '', (string)$code);
    if (!preg_match('/^\d{6}$/', $code)) { return false; }
    $t = (int)floor(time() / 30);
    for ($i = -1; $i <= 1; $i++) {
        if (hash_equals(sb_totp_code($secret, $t + $i), $code)) { return true; }
    }
    return false;
}

function sb_totp_new_secret()
{
    return sb_b32_encode(random_bytes(10));
}
