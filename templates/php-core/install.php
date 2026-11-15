<?php
/* One-time installer. Opens in the browser, asks for the install key + admin account, then locks itself. */
define('SB_DEPTH', 0);
require __DIR__ . '/inc/bootstrap.php';
require SB_ROOT . '/inc/db.php';
require SB_ROOT . '/inc/security.php';
require SB_ROOT . '/inc/admin_lib.php';

sb_send_headers(sb_nonce(), true);
sb_session_start();
$schema = sb_schema();
$fa = !(isset($schema['site']['lang']) && $schema['site']['lang'] !== 'fa');

$T = $fa ? array(
    'title' => 'نصب سایت', 'done' => 'سایت قبلاً نصب شده است. برای امنیت، فایل install.php را از هاست حذف کنید.',
    'req' => 'بررسی پیش‌نیازها', 'key' => 'کد نصب (داخل فایل README-نصب.txt)', 'user' => 'نام کاربری مدیر (انگلیسی)',
    'pass' => 'رمز عبور مدیر (حداقل ۱۰ نویسه، حرف و عدد)', 'pass2' => 'تکرار رمز عبور', 'db' => 'نوع دیتابیس',
    'sqlite' => 'SQLite (ساده‌ترین، بدون نیاز به تنظیم)', 'mysql' => 'MySQL (اطلاعات دیتابیس را از پنل هاست بگیرید)',
    'host' => 'آدرس سرور دیتابیس (معمولاً localhost)', 'name' => 'نام دیتابیس', 'dbuser' => 'کاربر دیتابیس', 'dbpass' => 'رمز دیتابیس',
    'proxy' => 'سایتم پشت Cloudflare یا پروکسی است', 'go' => 'نصب کن', 'bad_key' => 'کد نصب درست نیست.', 'no_key' => 'فایل کد نصب (data/install.key) روی هاست نیست. فایل‌ها را کامل آپلود کنید.',
    'bad_user' => 'نام کاربری فقط انگلیسی، عدد و _ . - و ۳ تا ۴۰ نویسه باشد.', 'short' => 'رمز باید حداقل ۱۰ نویسه باشد.', 'weak' => 'رمز باید هم حرف داشته باشد هم عدد.',
    'mismatch' => 'تکرار رمز یکسان نیست.', 'dberr' => 'اتصال به دیتابیس انجام نشد. اطلاعات را بررسی کنید.', 'fail' => 'نصب کامل نشد. جزئیات در data/error.log است.',
    'csrf' => 'نشست منقضی شد. صفحه را تازه کنید.', 'blocked' => 'تلاش‌های ناموفق زیاد بود. چند دقیقه بعد دوباره امتحان کنید.',
) : array(
    'title' => 'Install', 'done' => 'The site is already installed. For safety, delete install.php from your server.',
    'req' => 'Requirements', 'key' => 'Install key (inside README file)', 'user' => 'Admin username',
    'pass' => 'Admin password (min 10 chars, letters and digits)', 'pass2' => 'Repeat password', 'db' => 'Database type',
    'sqlite' => 'SQLite (simplest, no setup)', 'mysql' => 'MySQL (get details from your hosting panel)',
    'host' => 'Database host (usually localhost)', 'name' => 'Database name', 'dbuser' => 'Database user', 'dbpass' => 'Database password',
    'proxy' => 'My site is behind Cloudflare or a proxy', 'go' => 'Install', 'bad_key' => 'Wrong install key.', 'no_key' => 'data/install.key is missing. Upload all files.',
    'bad_user' => 'Username: 3-40 chars, letters, digits and _ . - only.', 'short' => 'Password must be at least 10 characters.', 'weak' => 'Password needs letters and digits.',
    'mismatch' => 'Passwords do not match.', 'dberr' => 'Could not connect to the database. Check the details.', 'fail' => 'Install failed. See data/error.log.',
    'csrf' => 'Session expired. Reload the page.', 'blocked' => 'Too many failed attempts. Try again later.',
);

if (is_array(sb_config())) {
    echo ui_shell($T['title'], '<div class="card"><div class="flash warn">' . h($T['done']) . '</div></div>', false);
    exit;
}

$checks = array(
    array('PHP >= 7.4 (' . PHP_VERSION . ')', version_compare(PHP_VERSION, '7.4.0', '>='), true),
    array('PDO', extension_loaded('pdo'), true),
    array('pdo_sqlite / pdo_mysql', extension_loaded('pdo_sqlite') || extension_loaded('pdo_mysql'), true),
    array('json', function_exists('json_encode'), true),
    array('folder data/ writable', is_writable(SB_ROOT . '/data'), true),
    array('folder uploads/ writable', is_writable(SB_ROOT . '/uploads'), true),
    array('GD + WebP (image optimizing)', function_exists('imagewebp'), false),
    array('mbstring', function_exists('mb_substr'), false),
);
$blocking = false;
foreach ($checks as $c) { if ($c[2] && !$c[1]) { $blocking = true; } }

$err = '';
$post = ($_SERVER['REQUEST_METHOD'] === 'POST');
if ($post && !$blocking) {
    $keyFile = SB_ROOT . '/data/install.key';
    $tries = isset($_SESSION['ik_tries']) ? (int)$_SESSION['ik_tries'] : 0;
    if (!csrf_ok(isset($_POST['csrf']) ? $_POST['csrf'] : '')) { $err = $T['csrf']; }
    elseif ($tries >= 8) { $err = $T['blocked']; }
    elseif (!is_file($keyFile)) { $err = $T['no_key']; }
    elseif (!hash_equals(trim((string)file_get_contents($keyFile)), hash('sha256', trim(isset($_POST['key']) && is_string($_POST['key']) ? $_POST['key'] : '')))) {
        $_SESSION['ik_tries'] = $tries + 1;
        sleep(1);
        $err = $T['bad_key'];
    } else {
        $user = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
        $pw = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        $pw2 = isset($_POST['password2']) && is_string($_POST['password2']) ? $_POST['password2'] : '';
        $driver = (isset($_POST['driver']) && $_POST['driver'] === 'mysql') ? 'mysql' : 'sqlite';
        $prob = sb_password_problem($pw);
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $user)) { $err = $T['bad_user']; }
        elseif ($prob !== '') { $err = $T[$prob]; }
        elseif ($pw !== $pw2) { $err = $T['mismatch']; }
        else {
            $cfg = array(
                'driver' => $driver,
                'sqlite' => 'data/' . bin2hex(random_bytes(10)) . '.sqlite',
                'mysql' => array(
                    'host' => sb_clean(isset($_POST['db_host']) ? $_POST['db_host'] : 'localhost', 120),
                    'name' => sb_clean(isset($_POST['db_name']) ? $_POST['db_name'] : '', 120),
                    'user' => sb_clean(isset($_POST['db_user']) ? $_POST['db_user'] : '', 120),
                    'pass' => isset($_POST['db_pass']) && is_string($_POST['db_pass']) ? $_POST['db_pass'] : '',
                ),
                'behind_proxy' => !empty($_POST['proxy']),
                'timezone' => $fa ? 'Asia/Tehran' : 'UTC',
                'admin_lang' => $fa ? 'fa' : 'en',
                'created' => time(),
            );
            $cfgFile = SB_ROOT . '/data/config.php';
            $php = "<?php\nif (!defined('SB')) { exit; }\nreturn " . var_export($cfg, true) . ";\n";
            try {
                if (file_put_contents($cfgFile, $php, LOCK_EX) === false) { throw new RuntimeException('cannot write config'); }
                @chmod($cfgFile, 0640);
                sb_config(true);
                try {
                    $pdo = db();
                } catch (Exception $e) {
                    @unlink($cfgFile);
                    sb_config(true);
                    $err = $T['dberr'];
                    throw new RuntimeException('db');
                }
                sb_create_tables($pdo, $driver);
                db_exec('INSERT INTO users (username, pass_hash, created) VALUES (?, ?, ?)', array($user, password_hash($pw, PASSWORD_DEFAULT), time()));
                sb_seed_items();
                @unlink($keyFile);
                @unlink(__FILE__);
                session_regenerate_id(true);
                unset($_SESSION['csrf'], $_SESSION['ik_tries']);
                sb_redirect(SB_BASE . '/admin/');
            } catch (Exception $e) {
                if ($err === '') {
                    error_log('install failed: ' . $e->getMessage());
                    @unlink($cfgFile);
                    sb_config(true);
                    $err = $T['fail'];
                }
            }
        }
    }
}

$f = '<div class="card"><h2>' . h($T['title']) . '</h2>';
if ($err !== '') { $f .= '<div class="flash err">' . h($err) . '</div>'; }
$f .= '<h3>' . h($T['req']) . '</h3><ul>';
foreach ($checks as $c) { $f .= '<li>' . ($c[1] ? '&#10003; ' : ($c[2] ? '&#10007; ' : '&#9888; ')) . h($c[0]) . '</li>'; }
$f .= '</ul>';
if (!$blocking) {
    $hasSqlite = extension_loaded('pdo_sqlite');
    $hasMysql = extension_loaded('pdo_mysql');
    $f .= '<form method="post" action="install.php" autocomplete="off">' . ui_csrf()
        . '<label>' . h($T['key']) . '</label><input type="text" name="key" dir="ltr">'
        . '<label>' . h($T['user']) . '</label><input type="text" name="username" dir="ltr" value="admin">'
        . '<label>' . h($T['pass']) . '</label><input type="password" name="password" dir="ltr" autocomplete="new-password">'
        . '<label>' . h($T['pass2']) . '</label><input type="password" name="password2" dir="ltr" autocomplete="new-password">'
        . '<label>' . h($T['db']) . '</label>'
        . '<label style="font-weight:400"><input type="radio" name="driver" value="sqlite"' . ($hasSqlite ? ' checked' : ' disabled') . '> ' . h($T['sqlite']) . '</label>'
        . '<label style="font-weight:400"><input type="radio" name="driver" value="mysql"' . ($hasSqlite ? '' : ' checked') . ($hasMysql ? '' : ' disabled') . '> ' . h($T['mysql']) . '</label>'
        . '<label>' . h($T['host']) . '</label><input type="text" name="db_host" dir="ltr" value="localhost">'
        . '<label>' . h($T['name']) . '</label><input type="text" name="db_name" dir="ltr">'
        . '<label>' . h($T['dbuser']) . '</label><input type="text" name="db_user" dir="ltr">'
        . '<label>' . h($T['dbpass']) . '</label><input type="password" name="db_pass" dir="ltr" autocomplete="new-password">'
        . '<label style="font-weight:400"><input type="checkbox" name="proxy" value="1"> ' . h($T['proxy']) . '</label>'
        . '<p><button class="btn" type="submit">' . h($T['go']) . '</button></p></form>';
}
$f .= '</div>';
echo ui_shell($T['title'], $f, false);
