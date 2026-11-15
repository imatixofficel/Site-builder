<?php
/* Admin panel: translations, layout and form helpers. */
if (!defined('SB')) { exit; }

function sb_admin_lang()
{
    $c = sb_config();
    return (is_array($c) && isset($c['admin_lang']) && $c['admin_lang'] === 'en') ? 'en' : 'fa';
}

function t($key)
{
    static $d = null;
    if ($d === null) {
        $d = array(
            'fa' => array(
                'panel' => 'پنل مدیریت', 'login_title' => 'ورود به پنل مدیریت', 'username' => 'نام کاربری', 'password' => 'رمز عبور',
                'login_btn' => 'ورود', 'bad_login' => 'نام کاربری یا رمز عبور درست نیست.', 'locked' => 'تعداد تلاش‌ها زیاد بود. ۱۵ دقیقه بعد دوباره امتحان کنید.',
                'code_title' => 'تأیید دو مرحله‌ای', 'code_label' => 'کد ۶ رقمی برنامه احراز هویت', 'bad_code' => 'کد درست نیست.',
                'dashboard' => 'داشبورد', 'settings' => 'تنظیمات و متن‌های سایت', 'messages' => 'پیام‌ها', 'security' => 'امنیت و رمز عبور',
                'view_site' => 'مشاهده سایت', 'logout' => 'خروج', 'save' => 'ذخیره', 'saved' => 'تغییرات ذخیره شد.', 'add' => 'افزودن',
                'edit' => 'ویرایش', 'delete' => 'حذف', 'confirm_delete' => 'مطمئنی حذف شود؟', 'up' => 'بالا', 'down' => 'پایین',
                'back' => 'بازگشت', 'empty' => 'هنوز چیزی اضافه نشده است.', 'remove_image' => 'حذف تصویر / بازگشت به پیش‌فرض',
                'no_messages' => 'هنوز پیامی نرسیده است.', 'new' => 'جدید', 'from' => 'فرستنده', 'phone' => 'تلفن', 'email' => 'ایمیل',
                'message' => 'پیام', 'date' => 'تاریخ', 'count' => 'تعداد', 'welcome' => 'خوش آمدید', 'warnings' => 'هشدارهای امنیتی',
                'warn_install' => 'فایل install.php هنوز روی هاست هست. همین حالا آن را با File Manager حذف کنید.',
                'warn_https' => 'سایت با HTTPS باز نشده است. گواهی SSL را در هاست فعال کنید.',
                'warn_2fa' => 'ورود دو مرحله‌ای فعال نیست. از بخش «امنیت و رمز عبور» فعالش کنید.',
                'all_good' => 'مورد امنیتی فوری وجود ندارد.',
                'change_password' => 'تغییر رمز عبور', 'current_password' => 'رمز فعلی', 'new_password' => 'رمز جدید', 'repeat_password' => 'تکرار رمز جدید',
                'pw_short' => 'رمز باید حداقل ۱۰ نویسه باشد.', 'pw_weak' => 'رمز باید هم حرف داشته باشد هم عدد.', 'pw_mismatch' => 'تکرار رمز یکسان نیست.',
                'pw_wrong' => 'رمز فعلی درست نیست.', 'pw_changed' => 'رمز عبور تغییر کرد.',
                'twofa' => 'ورود دو مرحله‌ای (Google Authenticator و مشابه)', 'twofa_on' => 'فعال است.', 'twofa_off_msg' => 'غیرفعال است.',
                'twofa_enable' => 'شروع فعال‌سازی', 'twofa_secret' => 'این کلید را در برنامه احراز هویت (Google Authenticator، Authy، ...) وارد کنید:',
                'twofa_confirm' => 'تأیید و فعال‌سازی', 'twofa_bad' => 'کد درست نیست.', 'twofa_enabled_ok' => 'ورود دو مرحله‌ای فعال شد.',
                'twofa_disable' => 'غیرفعال کردن', 'twofa_disabled_ok' => 'ورود دو مرحله‌ای غیرفعال شد.',
                'err_csrf' => 'نشست منقضی شد یا حجم فایل زیاد بود. دوباره امتحان کنید.', 'err_upload' => 'آپلود تصویر انجام نشد.',
                'err_size' => 'حجم یا ابعاد تصویر زیاد است (حداکثر ۶ مگابایت).', 'err_type' => 'فقط تصویر JPG، PNG، WebP یا GIF مجاز است.',
                'items' => 'مورد', 'delete_msg' => 'پیام حذف شد.', 'unread' => 'خوانده‌نشده',
            ),
            'en' => array(
                'panel' => 'Admin panel', 'login_title' => 'Admin login', 'username' => 'Username', 'password' => 'Password',
                'login_btn' => 'Sign in', 'bad_login' => 'Wrong username or password.', 'locked' => 'Too many attempts. Try again in 15 minutes.',
                'code_title' => 'Two-factor check', 'code_label' => '6-digit code from your authenticator app', 'bad_code' => 'Wrong code.',
                'dashboard' => 'Dashboard', 'settings' => 'Site settings & texts', 'messages' => 'Messages', 'security' => 'Security & password',
                'view_site' => 'View site', 'logout' => 'Log out', 'save' => 'Save', 'saved' => 'Changes saved.', 'add' => 'Add',
                'edit' => 'Edit', 'delete' => 'Delete', 'confirm_delete' => 'Delete this item?', 'up' => 'Up', 'down' => 'Down',
                'back' => 'Back', 'empty' => 'Nothing added yet.', 'remove_image' => 'Remove image / back to default',
                'no_messages' => 'No messages yet.', 'new' => 'New', 'from' => 'From', 'phone' => 'Phone', 'email' => 'Email',
                'message' => 'Message', 'date' => 'Date', 'count' => 'Count', 'welcome' => 'Welcome', 'warnings' => 'Security warnings',
                'warn_install' => 'install.php is still on the server. Delete it now with your File Manager.',
                'warn_https' => 'The site is not opened over HTTPS. Enable an SSL certificate in your hosting panel.',
                'warn_2fa' => 'Two-factor login is off. Enable it under "Security & password".',
                'all_good' => 'No urgent security issue found.',
                'change_password' => 'Change password', 'current_password' => 'Current password', 'new_password' => 'New password', 'repeat_password' => 'Repeat new password',
                'pw_short' => 'Password must be at least 10 characters.', 'pw_weak' => 'Password needs letters and digits.', 'pw_mismatch' => 'Passwords do not match.',
                'pw_wrong' => 'Current password is wrong.', 'pw_changed' => 'Password changed.',
                'twofa' => 'Two-factor login (Google Authenticator and similar)', 'twofa_on' => 'Enabled.', 'twofa_off_msg' => 'Disabled.',
                'twofa_enable' => 'Start setup', 'twofa_secret' => 'Enter this key in your authenticator app (Google Authenticator, Authy, ...):',
                'twofa_confirm' => 'Confirm and enable', 'twofa_bad' => 'Wrong code.', 'twofa_enabled_ok' => 'Two-factor login enabled.',
                'twofa_disable' => 'Disable', 'twofa_disabled_ok' => 'Two-factor login disabled.',
                'err_csrf' => 'Session expired or the upload was too large. Try again.', 'err_upload' => 'Image upload failed.',
                'err_size' => 'Image is too large (max 6 MB).', 'err_type' => 'Only JPG, PNG, WebP or GIF images are allowed.',
                'items' => 'items', 'delete_msg' => 'Message deleted.', 'unread' => 'unread',
            ),
        );
    }
    $l = sb_admin_lang();
    return isset($d[$l][$key]) ? $d[$l][$key] : $key;
}

function admin_url($a = 'home', array $q = array())
{
    $q = array('a' => $a) + $q;
    return SB_BASE . '/admin/?' . http_build_query($q);
}

function admin_asset_url($v)
{
    $v = trim((string)$v);
    if ($v === '') { return ''; }
    if (preg_match('#^https?://#i', $v)) { return $v; }
    if ($v[0] === '/') { return $v; }
    return SB_BASE . '/' . $v;
}

function admin_flash($type, $text)
{
    $_SESSION['flash'] = array($type, $text);
}

function ui_csrf()
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function ui_css()
{
    return ':root{--bg:#f4f5f9;--card:#fff;--text:#1b2030;--muted:#6b7285;--primary:#4f46e5;--danger:#dc2626;--ok:#15803d;--border:#e3e6ee}'
    . '*{box-sizing:border-box}body{margin:0;font-family:Tahoma,"Segoe UI",system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.7}'
    . '.wrap{display:flex;min-height:100vh}.side{width:250px;background:#12162b;color:#cfd4ea;padding:20px 14px;flex-shrink:0}'
    . '.side h1{font-size:16px;margin:0 0 18px;color:#fff}.side a,.side button.link{display:block;color:#cfd4ea;text-decoration:none;padding:9px 12px;border-radius:10px;margin-bottom:4px;background:none;border:0;width:100%;text-align:start;font:inherit;cursor:pointer}'
    . '.side a.on,.side a:hover,.side button.link:hover{background:#242a4d;color:#fff}.main{flex:1;padding:28px;max-width:980px}'
    . '.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px;margin-bottom:18px}h2{margin:0 0 14px;font-size:20px}h3{margin:18px 0 6px;font-size:16px}'
    . 'label{display:block;font-weight:700;margin:12px 0 6px;font-size:14px}'
    . 'input[type=text],input[type=password],textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;background:#fff;color:var(--text)}'
    . 'textarea{min-height:110px}input[type=color]{width:64px;height:40px;padding:2px;border:1px solid var(--border);border-radius:10px;background:#fff}'
    . '.btn{display:inline-block;background:var(--primary);color:#fff;border:0;border-radius:10px;padding:10px 18px;font:inherit;cursor:pointer;text-decoration:none}'
    . '.btn.sec{background:#e8eaf6;color:#2b2f5c}.btn.red{background:var(--danger)}.btn.sm{padding:4px 12px;font-size:13px}'
    . '.flash{padding:12px 16px;border-radius:12px;margin-bottom:16px}.flash.ok{background:#e7f7ec;color:var(--ok)}.flash.err{background:#fdecec;color:var(--danger)}.flash.warn{background:#fff6e0;color:#92580a}'
    . 'table{width:100%;border-collapse:collapse}th,td{text-align:start;padding:10px;border-bottom:1px solid var(--border);vertical-align:middle}'
    . '.thumb{width:56px;height:56px;object-fit:cover;border-radius:10px;border:1px solid var(--border)}.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}'
    . '.muted{color:var(--muted);font-size:13px}.badge{background:var(--danger);color:#fff;border-radius:20px;padding:1px 8px;font-size:12px}'
    . '.login{max-width:390px;margin:10vh auto;padding:0 16px}code{background:#eef0f6;padding:2px 6px;border-radius:6px;direction:ltr;unicode-bidi:embed}'
    . '@media(max-width:800px){.wrap{display:block}.side{width:auto}.main{padding:16px}}';
}

function ui_shell($title, $inner, $withNav = true, $active = '')
{
    $lang = sb_admin_lang();
    $dir = $lang === 'fa' ? 'rtl' : 'ltr';
    $sc = sb_schema();
    $site = isset($sc['site']['name']) ? $sc['site']['name'] : '';
    $o = '<!doctype html><html lang="' . $lang . '" dir="' . $dir . '"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">'
       . '<title>' . h($title) . '</title><style>' . ui_css() . '</style></head><body>';
    if ($withNav) {
        $unread = (int)db_val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
        $o .= '<div class="wrap"><aside class="side"><h1>' . h(t('panel')) . ($site !== '' ? ' - ' . h($site) : '') . '</h1>';
        $nav = array(array('home', t('dashboard'), array()));
        if (!empty($sc['settings'])) { $nav[] = array('settings', t('settings'), array()); }
        foreach ($sc['collections'] as $c) { $nav[] = array('list', isset($c['label']) ? $c['label'] : $c['key'], array('c' => $c['key'])); }
        if (!empty($sc['contact_form'])) { $nav[] = array('messages', t('messages') . ($unread ? ' <span class="badge">' . $unread . '</span>' : ''), array()); }
        $nav[] = array('security', t('security'), array());
        foreach ($nav as $n) {
            $isOn = ($active === $n[0] . (isset($n[2]['c']) ? ':' . $n[2]['c'] : ''));
            $o .= '<a href="' . h(admin_url($n[0], $n[2])) . '"' . ($isOn ? ' class="on"' : '') . '>' . $n[1] . '</a>';
        }
        $o .= '<a href="' . h(SB_BASE . '/') . '" target="_blank" rel="noopener">' . h(t('view_site')) . '</a>'
            . '<form method="post" action="' . h(admin_url('logout')) . '">' . ui_csrf() . '<button class="link" type="submit">' . h(t('logout')) . '</button></form>'
            . '</aside><main class="main">';
    } else {
        $o .= '<main class="login">';
    }
    if (!empty($_SESSION['flash'])) {
        $o .= '<div class="flash ' . h($_SESSION['flash'][0]) . '">' . h($_SESSION['flash'][1]) . '</div>';
        unset($_SESSION['flash']);
    }
    $o .= $inner . '</main>' . ($withNav ? '</div>' : '')
        . '<script nonce="' . sb_nonce() . '">document.addEventListener("submit",function(e){var m=e.target.getAttribute("data-confirm");if(m&&!confirm(m)){e.preventDefault();}});</script>'
        . '</body></html>';
    return $o;
}

function ui_field($prefix, array $f, $val)
{
    $k = $f['key'];
    $type = isset($f['type']) ? $f['type'] : 'text';
    $label = isset($f['label']) ? $f['label'] : $k;
    $name = $prefix . $k;
    $o = '<label for="' . h($name) . '">' . h($label) . '</label>';
    if ($type === 'textarea') {
        $o .= '<textarea id="' . h($name) . '" name="' . h($name) . '" maxlength="5000">' . h($val) . '</textarea>';
    } elseif ($type === 'color') {
        $c = sb_norm_color($val);
        if ($c === '') { $c = '#000000'; }
        $o .= '<input type="color" id="' . h($name) . '" name="' . h($name) . '" value="' . h($c) . '">';
    } elseif ($type === 'image') {
        $url = admin_asset_url($val);
        if ($url !== '') { $o .= '<div><img class="thumb" src="' . h($url) . '" alt=""></div>'; }
        $o .= '<input type="file" id="' . h($name) . '" name="file_' . h($name) . '" accept="image/jpeg,image/png,image/webp,image/gif">'
            . '<label style="font-weight:400"><input type="checkbox" name="reset_' . h($name) . '" value="1"> ' . h(t('remove_image')) . '</label>';
    } else {
        $o .= '<input type="text" id="' . h($name) . '" name="' . h($name) . '" value="' . h($val) . '" maxlength="' . ($type === 'url' ? 500 : 300) . '"' . ($type === 'url' ? ' dir="ltr"' : '') . '>';
    }
    return $o;
}

/* Sanitize one posted value according to its field type (images are handled by the caller). */
function sb_field_value($type, $raw)
{
    if (is_array($raw)) { $raw = ''; }
    if ($type === 'textarea') { return sb_clean($raw, 5000); }
    if ($type === 'color') { return sb_norm_color($raw); }
    if ($type === 'url') {
        $u = sb_clean($raw, 500);
        if ($u === '') { return ''; }
        if (preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $u)) { return $u; }
        return preg_match('#^[a-z][a-z0-9+.-]*:#i', $u) ? '' : $u;
    }
    return sb_clean($raw, 300);
}

function find_collection($key)
{
    $sc = sb_schema();
    foreach ($sc['collections'] as $c) { if ($c['key'] === $key) { return $c; } }
    return null;
}
