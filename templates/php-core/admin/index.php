<?php
/* Admin panel front controller: login (rate-limited, optional 2FA), settings, collections, messages, security. */
define('SB_DEPTH', 1);
require __DIR__ . '/../inc/bootstrap.php';
sb_require_installed();
require SB_ROOT . '/inc/db.php';
require SB_ROOT . '/inc/security.php';
require SB_ROOT . '/inc/admin_lib.php';

sb_send_headers(sb_nonce(), true);
sb_session_start();

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$a = isset($_GET['a']) ? (string)$_GET['a'] : 'home';
$schema = sb_schema();

/* ---------- session check ---------- */
function admin_uid()
{
    if (empty($_SESSION['uid'])) { return 0; }
    $ua = hash('sha256', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $ok = isset($_SESSION['ua'], $_SESSION['start'], $_SESSION['last'])
        && hash_equals($_SESSION['ua'], $ua)
        && (time() - $_SESSION['last']) < 1800      // 30 min idle
        && (time() - $_SESSION['start']) < 28800;   // 8 h max
    if (!$ok) { $_SESSION = array(); return 0; }
    $_SESSION['last'] = time();
    return (int)$_SESSION['uid'];
}

function admin_finish_login(array $u)
{
    session_regenerate_id(true);
    unset($_SESSION['pending_uid'], $_SESSION['pending_time'], $_SESSION['csrf']);
    $_SESSION['uid'] = (int)$u['id'];
    $_SESSION['ua'] = hash('sha256', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $_SESSION['start'] = time();
    $_SESSION['last'] = time();
    rate_clear('login:' . sb_ip());
    sb_redirect(admin_url('home'));
}

function admin_login()
{
    $err = '';
    $stage = (!empty($_SESSION['pending_uid']) && (time() - (isset($_SESSION['pending_time']) ? $_SESSION['pending_time'] : 0)) < 300) ? 2 : 1;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rk = 'login:' . sb_ip();
        if (!csrf_ok(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
            $err = t('err_csrf');
        } elseif (rate_count($rk, 900) >= 5) {
            $err = t('locked');
        } elseif ($stage === 2 && isset($_POST['code'])) {
            $u = db_one('SELECT * FROM users WHERE id = ?', array((int)$_SESSION['pending_uid']));
            if ($u && !empty($u['totp']) && sb_totp_verify($u['totp'], $_POST['code'])) {
                admin_finish_login($u);
            }
            rate_add($rk);
            $err = t('bad_code');
        } else {
            $user = sb_clean(isset($_POST['username']) ? $_POST['username'] : '', 100);
            $pw = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            $u = db_one('SELECT * FROM users WHERE username = ?', array($user));
            $hash = $u ? $u['pass_hash'] : '$2y$10$' . str_repeat('a', 22) . str_repeat('b', 31);
            $ok = password_verify($pw, $hash);
            if ($u && $ok) {
                if (password_needs_rehash($u['pass_hash'], PASSWORD_DEFAULT)) {
                    db_exec('UPDATE users SET pass_hash = ? WHERE id = ?', array(password_hash($pw, PASSWORD_DEFAULT), (int)$u['id']));
                }
                $flag = SB_ROOT . '/data/DISABLE_2FA';   // recovery: create this empty file with your File Manager
                if (!empty($u['totp']) && is_file($flag)) {
                    db_exec('UPDATE users SET totp = NULL WHERE id = ?', array((int)$u['id']));
                    @unlink($flag);
                    $u['totp'] = null;
                }
                if (!empty($u['totp'])) {
                    session_regenerate_id(true);
                    $_SESSION['pending_uid'] = (int)$u['id'];
                    $_SESSION['pending_time'] = time();
                    $stage = 2;
                } else {
                    admin_finish_login($u);
                }
            } else {
                rate_add($rk);
                usleep(400000);
                $err = t('bad_login');
            }
        }
    }
    $f = '<div class="card"><h2>' . h($stage === 2 ? t('code_title') : t('login_title')) . '</h2>';
    if ($err !== '') { $f .= '<div class="flash err">' . h($err) . '</div>'; }
    $f .= '<form method="post" action="' . h(admin_url('home')) . '" autocomplete="off">' . ui_csrf();
    if ($stage === 2) {
        $f .= '<label for="code">' . h(t('code_label')) . '</label><input type="text" id="code" name="code" inputmode="numeric" maxlength="6" dir="ltr" autofocus>';
    } else {
        $f .= '<label for="username">' . h(t('username')) . '</label><input type="text" id="username" name="username" dir="ltr" autofocus>'
            . '<label for="password">' . h(t('password')) . '</label><input type="password" id="password" name="password" dir="ltr">';
    }
    $f .= '<p><button class="btn" type="submit">' . h(t('login_btn')) . '</button></p></form></div>';
    echo ui_shell(t('login_title'), $f, false);
    exit;
}

$uid = admin_uid();
if (!$uid) { admin_login(); }
$me = db_one('SELECT * FROM users WHERE id = ?', array($uid));
if (!$me) { $_SESSION = array(); admin_login(); }

/* ---------- all POST requests need a valid CSRF token ---------- */
if ($isPost && !csrf_ok(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
    admin_flash('err', t('err_csrf'));
    sb_redirect(admin_url('home'));
}

switch ($a) {

case 'logout':
    if ($isPost) {
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
    sb_redirect(admin_url('home'));
    break;

case 'settings':
    $groups = array();
    foreach ($schema['settings'] as $s) { $groups[isset($s['group']) ? $s['group'] : ''][] = $s; }
    if ($isPost) {
        $errs = array();
        foreach ($schema['settings'] as $s) {
            $k = $s['key'];
            $type = isset($s['type']) ? $s['type'] : 'text';
            $label = isset($s['label']) ? $s['label'] : $k;
            if ($type === 'image') {
                $old = setting_get($k);
                if (!empty($_POST['reset_s_' . $k])) { sb_delete_upload($old); setting_save($k, null); continue; }
                if (!isset($_FILES['file_s_' . $k])) { continue; }
                $path = sb_upload_image($_FILES['file_s_' . $k], $e);
                if ($e !== '') { $errs[] = $label . ': ' . t('err_' . $e); continue; }
                if ($path !== '') { sb_delete_upload($old); setting_save($k, $path); }
                continue;
            }
            $v = sb_field_value($type, isset($_POST['s_' . $k]) ? $_POST['s_' . $k] : '');
            setting_save($k, $v);
        }
        admin_flash($errs ? 'err' : 'ok', $errs ? implode(' | ', $errs) : t('saved'));
        sb_redirect(admin_url('settings'));
    }
    $f = '<form method="post" enctype="multipart/form-data" action="' . h(admin_url('settings')) . '">' . ui_csrf();
    foreach ($groups as $g => $list) {
        $f .= '<div class="card">' . ($g !== '' ? '<h2>' . h($g) . '</h2>' : '');
        foreach ($list as $s) { $f .= ui_field('s_', $s, setting_get($s['key'])); }
        $f .= '</div>';
    }
    $f .= '<button class="btn" type="submit">' . h(t('save')) . '</button></form>';
    echo ui_shell(t('settings'), $f, true, 'settings');
    break;

case 'list':
    $c = find_collection(isset($_GET['c']) ? (string)$_GET['c'] : '');
    if (!$c) { sb_redirect(admin_url('home')); }
    $label = isset($c['label']) ? $c['label'] : $c['key'];
    $first = $c['fields'][0]['key'];
    $imgKey = '';
    foreach ($c['fields'] as $fd) { if (isset($fd['type']) && $fd['type'] === 'image') { $imgKey = $fd['key']; break; } }
    $rows = db_items($c['key']);
    $f = '<div class="card"><div class="row" style="justify-content:space-between"><h2>' . h($label) . '</h2>'
       . '<a class="btn" href="' . h(admin_url('edit', array('c' => $c['key']))) . '">+ ' . h(t('add')) . '</a></div>';
    if (!$rows) { $f .= '<p class="muted">' . h(t('empty')) . '</p>'; }
    else {
        $f .= '<table>';
        foreach ($rows as $r) {
            $f .= '<tr>';
            $f .= '<td style="width:70px">' . ($imgKey !== '' && !empty($r[$imgKey]) ? '<img class="thumb" src="' . h(admin_asset_url($r[$imgKey])) . '" alt="">' : '') . '</td>';
            $f .= '<td>' . h(isset($r[$first]) ? $r[$first] : '') . '</td><td><div class="row">';
            $f .= '<a class="btn sm sec" href="' . h(admin_url('edit', array('c' => $c['key'], 'id' => $r['id']))) . '">' . h(t('edit')) . '</a>';
            foreach (array('up' => t('up'), 'down' => t('down')) as $dir => $txt) {
                $f .= '<form method="post" action="' . h(admin_url('move', array('c' => $c['key'], 'id' => $r['id'], 'dir' => $dir))) . '">' . ui_csrf() . '<button class="btn sm sec" type="submit">' . h($txt) . '</button></form>';
            }
            $f .= '<form method="post" data-confirm="' . h(t('confirm_delete')) . '" action="' . h(admin_url('delete', array('c' => $c['key'], 'id' => $r['id']))) . '">' . ui_csrf() . '<button class="btn sm red" type="submit">' . h(t('delete')) . '</button></form>';
            $f .= '</div></td></tr>';
        }
        $f .= '</table>';
    }
    $f .= '</div>';
    echo ui_shell($label, $f, true, 'list:' . $c['key']);
    break;

case 'edit':
    $c = find_collection(isset($_GET['c']) ? (string)$_GET['c'] : '');
    if (!$c) { sb_redirect(admin_url('home')); }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $item = array();
    if ($id) {
        $item = item_get($c['key'], $id);
        if ($item === null) { sb_redirect(admin_url('list', array('c' => $c['key']))); }
    }
    $label = isset($c['label']) ? $c['label'] : $c['key'];
    if ($isPost) {
        $errs = array();
        $data = $item;
        foreach ($c['fields'] as $fd) {
            $k = $fd['key'];
            $type = isset($fd['type']) ? $fd['type'] : 'text';
            if ($type === 'image') {
                $old = isset($data[$k]) ? $data[$k] : '';
                if (!empty($_POST['reset_f_' . $k])) { sb_delete_upload($old); $data[$k] = ''; continue; }
                if (!isset($_FILES['file_f_' . $k])) { continue; }
                $path = sb_upload_image($_FILES['file_f_' . $k], $e);
                if ($e !== '') { $errs[] = (isset($fd['label']) ? $fd['label'] : $k) . ': ' . t('err_' . $e); continue; }
                if ($path !== '') { sb_delete_upload($old); $data[$k] = $path; }
                continue;
            }
            $data[$k] = sb_field_value($type, isset($_POST['f_' . $k]) ? $_POST['f_' . $k] : '');
        }
        unset($data['id']);
        item_save($c['key'], $id, $data);
        admin_flash($errs ? 'err' : 'ok', $errs ? implode(' | ', $errs) : t('saved'));
        sb_redirect(admin_url('list', array('c' => $c['key'])));
    }
    $f = '<div class="card"><h2>' . h($label) . '</h2><form method="post" enctype="multipart/form-data" action="' . h(admin_url('edit', array('c' => $c['key'], 'id' => $id))) . '">' . ui_csrf();
    foreach ($c['fields'] as $fd) { $f .= ui_field('f_', $fd, isset($item[$fd['key']]) ? $item[$fd['key']] : ''); }
    $f .= '<p class="row"><button class="btn" type="submit">' . h(t('save')) . '</button> <a class="btn sec" href="' . h(admin_url('list', array('c' => $c['key']))) . '">' . h(t('back')) . '</a></p></form></div>';
    echo ui_shell($label, $f, true, 'list:' . $c['key']);
    break;

case 'delete':
case 'move':
    $c = find_collection(isset($_GET['c']) ? (string)$_GET['c'] : '');
    if ($c && $isPost) {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($a === 'delete') {
            $it = item_get($c['key'], $id);
            if (is_array($it)) {
                foreach ($c['fields'] as $fd) { if (isset($fd['type']) && $fd['type'] === 'image' && !empty($it[$fd['key']])) { sb_delete_upload($it[$fd['key']]); } }
            }
            item_delete($c['key'], $id);
        } else {
            item_move($c['key'], $id, (isset($_GET['dir']) && $_GET['dir'] === 'up') ? 'up' : 'down');
        }
    }
    sb_redirect(admin_url('list', array('c' => $c ? $c['key'] : '')));
    break;

case 'messages':
    $rows = db_all('SELECT * FROM messages ORDER BY id DESC LIMIT 200');
    $f = '<div class="card"><h2>' . h(t('messages')) . '</h2>';
    if (!$rows) { $f .= '<p class="muted">' . h(t('no_messages')) . '</p>'; }
    foreach ($rows as $r) {
        $d = json_decode($r['data'], true);
        if (!is_array($d)) { $d = array(); }
        $head = (isset($d['name']) && $d['name'] !== '' ? $d['name'] : '-') . ' - ' . date('Y-m-d H:i', (int)$r['created']);
        $f .= '<details style="border-bottom:1px solid var(--border);padding:8px 0"><summary>' . h($head) . ($r['is_read'] ? '' : ' <span class="badge">' . h(t('new')) . '</span>') . '</summary><div style="padding:8px 0">';
        foreach (array('phone' => t('phone'), 'email' => t('email'), 'message' => t('message')) as $k => $lbl) {
            if (!empty($d[$k])) { $f .= '<p><strong>' . h($lbl) . ':</strong> ' . nl2br(h($d[$k]), false) . '</p>'; }
        }
        $f .= '<p class="muted">IP: ' . h($r['ip']) . '</p>'
            . '<form method="post" data-confirm="' . h(t('confirm_delete')) . '" action="' . h(admin_url('msg_delete', array('id' => $r['id']))) . '">' . ui_csrf() . '<button class="btn sm red" type="submit">' . h(t('delete')) . '</button></form></div></details>';
    }
    $f .= '</div>';
    echo ui_shell(t('messages'), $f, true, 'messages');
    db_exec('UPDATE messages SET is_read = 1 WHERE is_read = 0');
    break;

case 'msg_delete':
    if ($isPost) { db_exec('DELETE FROM messages WHERE id = ?', array(isset($_GET['id']) ? (int)$_GET['id'] : 0)); admin_flash('ok', t('delete_msg')); }
    sb_redirect(admin_url('messages'));
    break;

case 'security':
    if ($isPost) {
        $do = isset($_POST['do']) ? (string)$_POST['do'] : '';
        if ($do === 'password') {
            $cur = isset($_POST['cur']) && is_string($_POST['cur']) ? $_POST['cur'] : '';
            $new = isset($_POST['new']) && is_string($_POST['new']) ? $_POST['new'] : '';
            $rep = isset($_POST['rep']) && is_string($_POST['rep']) ? $_POST['rep'] : '';
            $prob = sb_password_problem($new);
            if (!password_verify($cur, $me['pass_hash'])) { admin_flash('err', t('pw_wrong')); }
            elseif ($new !== $rep) { admin_flash('err', t('pw_mismatch')); }
            elseif ($prob !== '') { admin_flash('err', t('pw_' . $prob)); }
            else {
                db_exec('UPDATE users SET pass_hash = ? WHERE id = ?', array(password_hash($new, PASSWORD_DEFAULT), (int)$me['id']));
                session_regenerate_id(true);
                admin_flash('ok', t('pw_changed'));
            }
        } elseif ($do === '2fa_start') {
            $_SESSION['totp_pending'] = sb_totp_new_secret();
        } elseif ($do === '2fa_confirm') {
            if (!empty($_SESSION['totp_pending']) && sb_totp_verify($_SESSION['totp_pending'], isset($_POST['code']) ? $_POST['code'] : '')) {
                db_exec('UPDATE users SET totp = ? WHERE id = ?', array($_SESSION['totp_pending'], (int)$me['id']));
                unset($_SESSION['totp_pending']);
                admin_flash('ok', t('twofa_enabled_ok'));
            } else { admin_flash('err', t('twofa_bad')); }
        } elseif ($do === '2fa_off') {
            $cur = isset($_POST['cur']) && is_string($_POST['cur']) ? $_POST['cur'] : '';
            if (password_verify($cur, $me['pass_hash'])) {
                db_exec('UPDATE users SET totp = NULL WHERE id = ?', array((int)$me['id']));
                unset($_SESSION['totp_pending']);
                admin_flash('ok', t('twofa_disabled_ok'));
            } else { admin_flash('err', t('pw_wrong')); }
        }
        sb_redirect(admin_url('security'));
    }
    $f = '<div class="card"><h2>' . h(t('change_password')) . '</h2><form method="post" action="' . h(admin_url('security')) . '">' . ui_csrf() . '<input type="hidden" name="do" value="password">'
       . '<label>' . h(t('current_password')) . '</label><input type="password" name="cur" dir="ltr" autocomplete="current-password">'
       . '<label>' . h(t('new_password')) . '</label><input type="password" name="new" dir="ltr" autocomplete="new-password">'
       . '<label>' . h(t('repeat_password')) . '</label><input type="password" name="rep" dir="ltr" autocomplete="new-password">'
       . '<p class="muted">' . h(t('pw_short')) . ' ' . h(t('pw_weak')) . '</p><button class="btn" type="submit">' . h(t('save')) . '</button></form></div>';
    $f .= '<div class="card"><h2>' . h(t('twofa')) . '</h2>';
    if (!empty($me['totp'])) {
        $f .= '<p class="flash ok">' . h(t('twofa_on')) . '</p><form method="post" action="' . h(admin_url('security')) . '">' . ui_csrf() . '<input type="hidden" name="do" value="2fa_off">'
            . '<label>' . h(t('current_password')) . '</label><input type="password" name="cur" dir="ltr"><p><button class="btn red" type="submit">' . h(t('twofa_disable')) . '</button></p></form>';
    } elseif (!empty($_SESSION['totp_pending'])) {
        $f .= '<p>' . h(t('twofa_secret')) . '</p><p><code>' . h($_SESSION['totp_pending']) . '</code></p>'
            . '<form method="post" action="' . h(admin_url('security')) . '">' . ui_csrf() . '<input type="hidden" name="do" value="2fa_confirm">'
            . '<label>' . h(t('code_label')) . '</label><input type="text" name="code" inputmode="numeric" maxlength="6" dir="ltr"><p><button class="btn" type="submit">' . h(t('twofa_confirm')) . '</button></p></form>';
    } else {
        $f .= '<p class="muted">' . h(t('twofa_off_msg')) . '</p><form method="post" action="' . h(admin_url('security')) . '">' . ui_csrf() . '<input type="hidden" name="do" value="2fa_start"><button class="btn" type="submit">' . h(t('twofa_enable')) . '</button></form>';
    }
    $f .= '</div>';
    echo ui_shell(t('security'), $f, true, 'security');
    break;

default: // dashboard
    $warn = array();
    if (is_file(SB_ROOT . '/install.php')) { $warn[] = t('warn_install'); }
    if (!sb_https()) { $warn[] = t('warn_https'); }
    if (empty($me['totp'])) { $warn[] = t('warn_2fa'); }
    $f = '<div class="card"><h2>' . h(t('welcome')) . ', ' . h($me['username']) . '</h2>';
    $f .= '<h3>' . h(t('warnings')) . '</h3>';
    if ($warn) { foreach ($warn as $w) { $f .= '<div class="flash warn">' . h($w) . '</div>'; } }
    else { $f .= '<div class="flash ok">' . h(t('all_good')) . '</div>'; }
    $f .= '</div><div class="card"><table>';
    foreach ($schema['collections'] as $c) {
        $n = (int)db_val('SELECT COUNT(*) FROM items WHERE coll = ?', array($c['key']));
        $f .= '<tr><td><a href="' . h(admin_url('list', array('c' => $c['key']))) . '">' . h(isset($c['label']) ? $c['label'] : $c['key']) . '</a></td><td>' . $n . ' ' . h(t('items')) . '</td></tr>';
    }
    if (!empty($schema['contact_form'])) {
        $f .= '<tr><td><a href="' . h(admin_url('messages')) . '">' . h(t('messages')) . '</a></td><td>' . (int)db_val('SELECT COUNT(*) FROM messages WHERE is_read = 0') . ' ' . h(t('unread')) . '</td></tr>';
    }
    $f .= '</table></div>';
    echo ui_shell(t('dashboard'), $f, true, 'home');
}
