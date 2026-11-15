<?php
/* Public site: renders theme/index.tpl.html with content from the database. Also handles the contact form. */
define('SB_DEPTH', 0);
require __DIR__ . '/inc/bootstrap.php';
sb_require_installed();
require SB_ROOT . '/inc/db.php';
require SB_ROOT . '/inc/security.php';
require SB_ROOT . '/inc/engine.php';

$schema = sb_schema();
$lang = isset($schema['site']['lang']) ? $schema['site']['lang'] : 'fa';
$dir = isset($schema['site']['dir']) ? $schema['site']['dir'] : ($lang === 'fa' ? 'rtl' : 'ltr');

function sb_front_msg($code)
{
    $fa = array(
        'sent' => 'پیام شما ارسال شد. به‌زودی با شما تماس می‌گیریم.', 'csrf' => 'نشست منقضی شد. صفحه را تازه کنید و دوباره بفرستید.',
        'rate' => 'تعداد ارسال‌ها زیاد بود. کمی بعد دوباره امتحان کنید.', 'empty' => 'لطفاً پیام یا شماره تماس خود را بنویسید.', 'email' => 'ایمیل واردشده درست نیست.',
    );
    $en = array(
        'sent' => 'Thanks! Your message was sent.', 'csrf' => 'Session expired. Reload the page and try again.',
        'rate' => 'Too many messages. Please try again later.', 'empty' => 'Please write a message or a phone number.', 'email' => 'The email address is not valid.',
    );
    $s = sb_schema();
    $l = isset($s['site']['lang']) ? $s['site']['lang'] : 'fa';
    $d = ($l === 'fa') ? $fa : $en;
    return isset($d[$code]) ? $d[$code] : '';
}

function sb_handle_contact()
{
    $s = sb_schema();
    if (empty($s['contact_form'])) { http_response_code(404); exit; }
    $back = (SB_BASE === '' ? '' : SB_BASE) . '/';
    sb_session_start();
    if (!csrf_ok(isset($_POST['csrf']) ? $_POST['csrf'] : '')) { sb_redirect($back . '?err=csrf#contact'); }
    if (!empty($_POST['website'])) { sb_redirect($back . '?sent=1#contact'); }   // honeypot filled: pretend success
    $key = 'contact:' . sb_ip();
    if (rate_count($key, 3600) >= 5) { sb_redirect($back . '?err=rate#contact'); }
    $name = sb_clean(isset($_POST['name']) ? $_POST['name'] : '', 100);
    $phone = sb_clean(isset($_POST['phone']) ? $_POST['phone'] : '', 40);
    $email = sb_clean(isset($_POST['email']) ? $_POST['email'] : '', 150);
    $msg = sb_clean(isset($_POST['message']) ? $_POST['message'] : '', 3000);
    if ($msg === '' && $phone === '' && $email === '') { sb_redirect($back . '?err=empty#contact'); }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { sb_redirect($back . '?err=email#contact'); }
    rate_add($key);
    db_exec('INSERT INTO messages (data, ip, created, is_read) VALUES (?, ?, ?, 0)',
        array(json_encode(array('name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $msg), JSON_UNESCAPED_UNICODE), sb_ip(), time()));
    unset($_SESSION['csrf']);
    sb_redirect($back . '?sent=1#contact');
}

sb_send_headers(sb_nonce(), false);

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_GET['a']) && $_GET['a'] === 'contact') { sb_handle_contact(); }
if ($method !== 'GET' && $method !== 'HEAD') { http_response_code(405); header('Allow: GET, HEAD, POST'); exit; }

$flash = '';
if (isset($_GET['sent'])) { $flash = sb_front_msg('sent'); }
elseif (isset($_GET['err']) && is_string($_GET['err'])) { $flash = sb_front_msg($_GET['err']); }

$builtin = array(
    'site_name' => isset($schema['site']['name']) ? $schema['site']['name'] : '',
    'site_description' => isset($schema['site']['description']) ? $schema['site']['description'] : '',
    'site_lang' => $lang,
    'site_dir' => $dir,
    'year' => date('Y'),
    'base' => SB_BASE,
    'contact_url' => (SB_BASE === '' ? '' : SB_BASE) . '/?a=contact',
    'flash' => $flash,
);
$raw = array(
    'form_fields' => function () {
        return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'
             . '<input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">';
    },
);

$engine = new SB_Engine($schema, 'setting_get', 'db_items', SB_BASE, $builtin, $raw);
$html = $engine->render((string)file_get_contents(SB_ROOT . '/theme/index.tpl.html'));
$html = sb_add_nonce($html, sb_nonce());
header('Content-Type: text/html; charset=UTF-8');
echo $html;
