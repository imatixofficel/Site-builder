<?php
/*
Plugin Name: SB Security
Description: Lightweight WordPress hardening - login attempt limit, hidden WP version, no XML-RPC, no user enumeration, security headers, no file editor, security status page.
Version: 1.0.0
Author: Site Builder
License: GPLv2 or later
Requires PHP: 7.4
*/
if (!defined('ABSPATH')) { exit; }

/* 1. No theme/plugin file editor inside wp-admin */
if (!defined('DISALLOW_FILE_EDIT')) { define('DISALLOW_FILE_EDIT', true); }

/* 2. Hide WordPress version and useless head links */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
add_filter('the_generator', '__return_empty_string');

/* 3. XML-RPC off (a common brute-force and DDoS door) */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function ($h) { unset($h['X-Pingback']); return $h; });

/* 4. Login attempt limit: 5 failures per IP, then 15 minutes lock. Generic error text. */
function sb_sec_key()
{
    return 'sb_login_' . md5(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
}
add_filter('authenticate', function ($user) {
    if ((int)get_transient(sb_sec_key()) >= 5) {
        return new WP_Error('sb_locked', 'Too many failed logins. Try again in 15 minutes.');
    }
    return $user;
}, 99, 1);
add_action('wp_login_failed', function () {
    $k = sb_sec_key();
    set_transient($k, (int)get_transient($k) + 1, 15 * MINUTE_IN_SECONDS);
});
add_action('wp_login', function () { delete_transient(sb_sec_key()); });
add_filter('login_errors', function () { return 'Wrong username or password.'; });

/* 5. Stop user name discovery (?author=1, REST users, sitemap users) */
add_action('parse_request', function () {
    if (!is_admin() && isset($_GET['author']) && !is_user_logged_in()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});
add_action('template_redirect', function () {
    if (is_author() && !is_user_logged_in()) { wp_safe_redirect(home_url('/'), 301); exit; }
});
add_filter('rest_endpoints', function ($e) {
    if (!is_user_logged_in()) {
        foreach (array_keys($e) as $route) { if (strpos($route, '/wp/v2/users') === 0) { unset($e[$route]); } }
    }
    return $e;
});
add_filter('wp_sitemaps_add_provider', function ($p, $name) { return $name === 'users' ? false : $p; }, 10, 2);

/* 6. Security headers */
add_action('send_headers', function () {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    if (is_ssl()) { header('Strict-Transport-Security: max-age=31536000'); }
});

/* 7. Security status page: Tools > Security status */
add_action('admin_menu', function () {
    add_management_page('Security status', 'Security status', 'manage_options', 'sb-security', 'sb_sec_page');
});

function sb_sec_page()
{
    if (!current_user_can('manage_options')) { return; }
    global $wpdb;
    $core = get_site_transient('update_core');
    $pending = 0;
    if ($core && !empty($core->updates)) { foreach ($core->updates as $u) { if (isset($u->response) && $u->response === 'upgrade') { $pending++; } } }
    $plugins = get_site_transient('update_plugins');
    $pp = ($plugins && !empty($plugins->response)) ? count($plugins->response) : 0;
    $rows = array(
        array('HTTPS is active', is_ssl(), 'Enable the free SSL certificate in your hosting panel, then set Settings > General URLs to https.'),
        array('No user called "admin"', !username_exists('admin'), 'Create a new administrator with a different name, then delete "admin".'),
        array('Database prefix is not "wp_"', $wpdb->prefix !== 'wp_', 'Use a custom prefix on new installs.'),
        array('PHP 8.1 or newer', version_compare(PHP_VERSION, '8.1', '>='), 'Choose PHP 8.1+ in your hosting panel.'),
        array('WordPress core is up to date', $pending === 0, 'Dashboard > Updates.'),
        array('All plugins are up to date', $pp === 0, 'Dashboard > Updates. Delete plugins you do not use.'),
        array('Debug output is off', !(defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY), 'Set WP_DEBUG to false in wp-config.php.'),
        array('Theme/plugin file editor is off', defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT, 'Active automatically with this plugin.'),
        array('XML-RPC is off', !apply_filters('xmlrpc_enabled', true), 'Active automatically with this plugin.'),
    );
    echo '<div class="wrap"><h1>Security status</h1><table class="widefat striped" style="max-width:820px"><tbody>';
    foreach ($rows as $r) {
        echo '<tr><td style="width:36px">' . ($r[1] ? '&#9989;' : '&#10060;') . '</td><td><strong>' . esc_html($r[0]) . '</strong>' . ($r[1] ? '' : '<br><span>' . esc_html($r[2]) . '</span>') . '</td></tr>';
    }
    echo '</tbody></table><p>Also keep daily backups (for example with UpdraftPlus) and read the README file that came with this package.</p></div>';
}
