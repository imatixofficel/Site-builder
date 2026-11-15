<?php
/* Site Builder WordPress theme - content comes from schema.json: Customizer = texts/colors/images, custom post types = lists. */
if (!defined('ABSPATH')) { exit; }
define('SB', 1);
require_once get_template_directory() . '/inc/engine.php';

function sb_schema()
{
    static $s = null;
    if ($s === null) {
        $s = json_decode((string)file_get_contents(get_template_directory() . '/schema.json'), true);
        if (!is_array($s)) { $s = array(); }
        $s += array('settings' => array(), 'collections' => array(), 'site' => array());
    }
    return $s;
}

function sb_lang() { $s = sb_schema(); return isset($s['site']['lang']) ? $s['site']['lang'] : 'fa'; }

function sb_msg($code)
{
    $fa = array('sent' => 'پیام شما ارسال شد. به‌زودی با شما تماس می‌گیریم.', 'csrf' => 'نشست منقضی شد. صفحه را تازه کنید و دوباره بفرستید.',
        'rate' => 'تعداد ارسال‌ها زیاد بود. کمی بعد دوباره امتحان کنید.', 'empty' => 'لطفاً پیام یا شماره تماس خود را بنویسید.', 'email' => 'ایمیل واردشده درست نیست.',
        '404' => 'صفحه‌ای که دنبالش بودید پیدا نشد.', 'home' => 'بازگشت به صفحه اصلی', 'nothing' => 'مطلبی پیدا نشد.', 'messages' => 'پیام‌ها', 'msg_one' => 'پیام');
    $en = array('sent' => 'Thanks! Your message was sent.', 'csrf' => 'Session expired. Reload the page and try again.',
        'rate' => 'Too many messages. Please try again later.', 'empty' => 'Please write a message or a phone number.', 'email' => 'The email address is not valid.',
        '404' => 'The page you were looking for was not found.', 'home' => 'Back to home', 'nothing' => 'Nothing found.', 'messages' => 'Messages', 'msg_one' => 'Message');
    $d = (sb_lang() === 'fa') ? $fa : $en;
    return isset($d[$code]) ? $d[$code] : '';
}

function sb_clean_url($v)
{
    $v = trim((string)$v);
    if (preg_match('#^assets/[A-Za-z0-9._/\-]+$#', $v)) { return $v; }   // bundled theme asset
    return esc_url_raw($v);
}

function sb_sanitize_by_type($type, $v)
{
    $v = is_scalar($v) ? (string)$v : '';
    if ($type === 'textarea') { return sanitize_textarea_field($v); }
    if ($type === 'color') { $c = sanitize_hex_color($v); return $c ? $c : ''; }
    if ($type === 'url' || $type === 'image') { return sb_clean_url($v); }
    return sanitize_text_field($v);
}

function sb_collection($key)
{
    $s = sb_schema();
    foreach ($s['collections'] as $c) { if ($c['key'] === $key) { return $c; } }
    return null;
}

/* ---------- theme support ---------- */
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('woocommerce');
});

/* ---------- Customizer: texts, colors, images ---------- */
add_action('customize_register', function ($wp) {
    $s = sb_schema();
    $groups = array();
    foreach ($s['settings'] as $f) {
        $g = (isset($f['group']) && $f['group'] !== '') ? $f['group'] : 'Site';
        $groups[$g][] = $f;
    }
    $n = 0;
    foreach ($groups as $g => $list) {
        $n++;
        $sec = 'sb_sec_' . $n;
        $wp->add_section($sec, array('title' => $g, 'priority' => 30 + $n));
        foreach ($list as $f) {
            $id = 'sb_' . $f['key'];
            $type = isset($f['type']) ? $f['type'] : 'text';
            $label = isset($f['label']) ? $f['label'] : $f['key'];
            $wp->add_setting($id, array(
                'default' => isset($f['default']) ? $f['default'] : '',
                'sanitize_callback' => function ($v) use ($type) { return sb_sanitize_by_type($type, $v); },
                'transport' => 'refresh',
            ));
            if ($type === 'color') {
                $wp->add_control(new WP_Customize_Color_Control($wp, $id, array('label' => $label, 'section' => $sec)));
            } elseif ($type === 'image') {
                $wp->add_control(new WP_Customize_Image_Control($wp, $id, array('label' => $label, 'section' => $sec)));
            } else {
                $wp->add_control($id, array('label' => $label, 'section' => $sec, 'type' => ($type === 'textarea' ? 'textarea' : ($type === 'url' ? 'url' : 'text'))));
            }
        }
    }
});

function sb_get_setting($key)
{
    $default = '';
    $s = sb_schema();
    foreach ($s['settings'] as $f) { if ($f['key'] === $key) { $default = isset($f['default']) ? (string)$f['default'] : ''; break; } }
    $v = get_theme_mod('sb_' . $key, $default);
    return is_scalar($v) ? (string)$v : '';
}

/* ---------- Lists (services, products, gallery...) as custom post types ---------- */
add_action('init', function () {
    $s = sb_schema();
    foreach ($s['collections'] as $c) {
        $label = isset($c['label']) ? $c['label'] : $c['key'];
        register_post_type('sb_' . $c['key'], array(
            'labels' => array('name' => $label, 'singular_name' => isset($c['item_label']) ? $c['item_label'] : $label, 'add_new_item' => '+ ' . (isset($c['item_label']) ? $c['item_label'] : $label), 'edit_item' => $label, 'all_items' => $label),
            'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-list-view', 'menu_position' => 26,
            'supports' => array('title', 'page-attributes'), 'hierarchical' => false, 'rewrite' => false, 'has_archive' => false,
            'show_in_rest' => false, 'capability_type' => 'post',
        ));
    }
    if (!empty($s['contact_form'])) {
        register_post_type('sb_message', array(
            'labels' => array('name' => sb_msg('messages'), 'singular_name' => sb_msg('msg_one')),
            'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-email', 'menu_position' => 25,
            'supports' => array('title', 'editor'), 'capability_type' => 'post', 'map_meta_cap' => true,
            'capabilities' => array('create_posts' => 'do_not_allow'),
        ));
    }
});

add_action('add_meta_boxes', function () {
    $s = sb_schema();
    foreach ($s['collections'] as $c) {
        add_meta_box('sb_fields', isset($c['label']) ? $c['label'] : $c['key'], 'sb_render_box', 'sb_' . $c['key'], 'normal', 'high', array('coll' => $c));
    }
});

function sb_render_box($post, $box)
{
    $c = $box['args']['coll'];
    wp_nonce_field('sb_save_' . $post->ID, 'sb_box_nonce');
    foreach ($c['fields'] as $f) {
        if ($f['key'] === 'title') { continue; }
        $type = isset($f['type']) ? $f['type'] : 'text';
        $val = get_post_meta($post->ID, 'sb_' . $f['key'], true);
        $name = 'sb_f[' . esc_attr($f['key']) . ']';
        echo '<p><label><strong>' . esc_html(isset($f['label']) ? $f['label'] : $f['key']) . '</strong></label><br>';
        if ($type === 'textarea') {
            echo '<textarea class="widefat" rows="4" name="' . $name . '">' . esc_textarea($val) . '</textarea>';
        } elseif ($type === 'image') {
            echo '<input type="text" class="widefat" dir="ltr" name="' . $name . '" value="' . esc_attr($val) . '" style="width:75%"> <button type="button" class="button sb-media">' . esc_html(sb_lang() === 'fa' ? 'انتخاب تصویر' : 'Choose image') . '</button>';
        } elseif ($type === 'color') {
            echo '<input type="text" class="widefat" dir="ltr" name="' . $name . '" value="' . esc_attr($val) . '" placeholder="#RRGGBB">';
        } else {
            echo '<input type="text" class="widefat"' . ($type === 'url' ? ' dir="ltr"' : '') . ' name="' . $name . '" value="' . esc_attr($val) . '">';
        }
        echo '</p>';
    }
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['sb_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sb_box_nonce'])), 'sb_save_' . $post_id)) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    $t = get_post_type($post_id);
    if (strpos((string)$t, 'sb_') !== 0) { return; }
    $c = sb_collection(substr($t, 3));
    if (!$c) { return; }
    $in = (isset($_POST['sb_f']) && is_array($_POST['sb_f'])) ? wp_unslash($_POST['sb_f']) : array();
    foreach ($c['fields'] as $f) {
        if ($f['key'] === 'title') { continue; }
        $type = isset($f['type']) ? $f['type'] : 'text';
        $v = isset($in[$f['key']]) ? sb_sanitize_by_type($type, $in[$f['key']]) : '';
        update_post_meta($post_id, 'sb_' . $f['key'], wp_slash($v));
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') { return; }
    $t = get_post_type();
    if (strpos((string)$t, 'sb_') !== 0) { return; }
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', 'jQuery(function($){$(document).on("click",".sb-media",function(e){e.preventDefault();var i=$(this).prev("input");var f=wp.media({multiple:false,library:{type:"image"}});f.on("select",function(){i.val(f.state().get("selection").first().toJSON().url);});f.open();});});');
});

function sb_get_items($coll)
{
    $c = sb_collection($coll);
    if (!$c) { return array(); }
    $posts = get_posts(array('post_type' => 'sb_' . $coll, 'numberposts' => -1, 'post_status' => 'publish',
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'), 'suppress_filters' => true));
    $out = array();
    foreach ($posts as $p) {
        $row = array('id' => (string)$p->ID);
        foreach ($c['fields'] as $f) {
            $k = $f['key'];
            $row[$k] = ($k === 'title') ? (string)$p->post_title : (string)get_post_meta($p->ID, 'sb_' . $k, true);
        }
        $out[] = $row;
    }
    return $out;
}

/* Default content (from schema.json) is added once when the theme is activated. */
add_action('after_switch_theme', function () {
    $s = sb_schema();
    foreach ($s['collections'] as $c) {
        if (empty($c['items'])) { continue; }
        $have = get_posts(array('post_type' => 'sb_' . $c['key'], 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids'));
        if ($have) { continue; }
        $i = 0;
        foreach ($c['items'] as $it) {
            $i++;
            $id = wp_insert_post(array('post_type' => 'sb_' . $c['key'], 'post_status' => 'publish', 'menu_order' => $i,
                'post_title' => wp_slash(isset($it['title']) ? (string)$it['title'] : ('#' . $i))));
            if (!$id || is_wp_error($id)) { continue; }
            foreach ($c['fields'] as $f) {
                if ($f['key'] === 'title' || !isset($it[$f['key']])) { continue; }
                update_post_meta($id, 'sb_' . $f['key'], wp_slash((string)$it[$f['key']]));
            }
        }
    }
});

/* ---------- contact form: nonce + honeypot + rate limit, stored as a private message and emailed ---------- */
add_action('admin_post_nopriv_sb_contact', 'sb_handle_contact');
add_action('admin_post_sb_contact', 'sb_handle_contact');

function sb_handle_contact()
{
    $s = sb_schema();
    $back = wp_get_referer() ? wp_get_referer() : home_url('/');
    $back = remove_query_arg('sb', $back);
    $go = function ($code) use ($back) { wp_safe_redirect(add_query_arg('sb', $code, $back) . '#contact'); exit; };
    if (empty($s['contact_form'])) { $go('empty'); }
    if (empty($_POST['sb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sb_nonce'])), 'sb_contact')) { $go('csrf'); }
    if (!empty($_POST['website'])) { $go('sent'); }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $rk = 'sb_rl_' . md5($ip);
    $n = (int)get_transient($rk);
    if ($n >= 5) { $go('rate'); }
    $name = sanitize_text_field(wp_unslash(isset($_POST['name']) ? $_POST['name'] : ''));
    $phone = sanitize_text_field(wp_unslash(isset($_POST['phone']) ? $_POST['phone'] : ''));
    $email = sanitize_email(wp_unslash(isset($_POST['email']) ? $_POST['email'] : ''));
    $msg = sanitize_textarea_field(wp_unslash(isset($_POST['message']) ? $_POST['message'] : ''));
    $name = mb_substr($name, 0, 100); $phone = mb_substr($phone, 0, 40); $msg = mb_substr($msg, 0, 3000);
    if ($msg === '' && $phone === '' && $email === '') { $go('empty'); }
    if (!empty($_POST['email']) && $email === '') { $go('email'); }
    set_transient($rk, $n + 1, HOUR_IN_SECONDS);
    $body = "Name: $name\nPhone: $phone\nEmail: $email\n\n$msg";
    $id = wp_insert_post(array('post_type' => 'sb_message', 'post_status' => 'publish', 'post_title' => wp_slash($name !== '' ? $name : sb_msg('msg_one')), 'post_content' => wp_slash($body)));
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    if ($email !== '') { $headers[] = 'Reply-To: ' . $email; }
    wp_mail(get_option('admin_email'), '[' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . '] ' . sb_msg('msg_one'), $body, $headers);
    $go('sent');
}

/* ---------- rendering ---------- */
function sb_engine()
{
    $s = sb_schema();
    $lang = sb_lang();
    $dir = isset($s['site']['dir']) ? $s['site']['dir'] : ($lang === 'fa' ? 'rtl' : 'ltr');
    $flash = '';
    if (isset($_GET['sb']) && is_string($_GET['sb'])) { $flash = sb_msg(sanitize_key($_GET['sb'])); }
    $builtin = array(
        'site_name' => get_bloginfo('name'), 'site_description' => get_bloginfo('description'),
        'site_lang' => $lang, 'site_dir' => $dir, 'year' => gmdate('Y'),
        'base' => get_template_directory_uri(), 'home' => home_url('/'),
        'contact_url' => admin_url('admin-post.php'), 'flash' => $flash, 'csrf' => '',
    );
    $raw = array('form_fields' => function () {
        return wp_nonce_field('sb_contact', 'sb_nonce', true, false)
            . '<input type="hidden" name="action" value="sb_contact">'
            . '<input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">';
    });
    return new SB_Engine($s, 'sb_get_setting', 'sb_get_items', get_template_directory_uri(), $builtin, $raw);
}

function sb_template_html()
{
    return sb_engine()->render((string)file_get_contents(get_template_directory() . '/index.tpl.html'));
}

function sb_capture($fn)
{
    ob_start();
    call_user_func($fn);
    return (string)ob_get_clean();
}

function sb_render_landing()
{
    $html = sb_template_html();
    $head = sb_capture('wp_head');
    $foot = sb_capture('wp_footer');
    $html = preg_replace_callback('#</head>#i', function () use ($head) { return $head . '</head>'; }, $html, 1);
    $html = preg_replace_callback('#</body>#i', function () use ($foot) { return $foot . '</body>'; }, $html, 1);
    return $html;
}

/* Posts, pages, archives, search, 404, WooCommerce: same head, header and footer as the landing page. */
function sb_inner_page($callback = null)
{
    $html = sb_template_html();
    $m = array();
    $open = preg_match('#<html\b[^>]*>#i', $html, $m) ? $m[0] : '<html>';
    $head = preg_match('#<head\b.*?</head>#is', $html, $m) ? $m[0] : '<head><meta charset="utf-8"></head>';
    $bodyOpen = preg_match('#<body\b[^>]*>#i', $html, $m) ? $m[0] : '<body>';
    $header = preg_match('#<header\b.*?</header>#is', $html, $m) ? $m[0] : '';
    $footer = '';
    if (preg_match_all('#<footer\b.*?</footer>#is', $html, $mm) && !empty($mm[0])) { $footer = end($mm[0]); }
    $noHead = preg_replace('#<head\b.*?</head>#is', '', $html, 1);
    $scripts = '';
    if (preg_match_all('#<script\b[^>]*>.*?</script>#is', (string)$noHead, $sm)) { $scripts = implode("\n", $sm[0]); }
    $home = esc_url(home_url('/'));
    $fix = function ($x) use ($home) { return str_replace('href="#', 'href="' . $home . '#', $x); };

    $content = $callback ? sb_capture($callback) : sb_inner_content();
    $css = '<style id="sb-inner-css">.sb-inner{max-width:900px;margin:0 auto;padding:56px 20px;line-height:1.9;overflow-wrap:anywhere}.sb-inner img{max-width:100%;height:auto;border-radius:12px}.sb-inner h1,.sb-inner h2{line-height:1.4}.sb-inner article{margin-bottom:36px}.sb-inner .page-numbers{display:inline-block;margin:0 4px;padding:4px 10px;border:1px solid currentColor;border-radius:8px;text-decoration:none}</style>';
    $wpHead = sb_capture('wp_head');
    $wpFoot = sb_capture('wp_footer');
    $head = preg_replace_callback('#</head>#i', function () use ($css, $wpHead) { return $css . $wpHead . '</head>'; }, $head, 1);
    echo '<!doctype html>' . $open . $head . $bodyOpen . $fix($header) . '<main class="sb-inner" id="main">' . $content . '</main>' . $fix($footer) . $scripts . $wpFoot . '</body></html>';
}

function sb_inner_content()
{
    ob_start();
    if (is_singular()) {
        while (have_posts()) {
            the_post();
            echo '<article class="sb-article"><h1>' . esc_html(get_the_title()) . '</h1><div class="sb-entry">';
            the_content();
            echo '</div></article>';
        }
    } elseif (is_404()) {
        echo '<h1>404</h1><p>' . esc_html(sb_msg('404')) . '</p><p><a href="' . esc_url(home_url('/')) . '">' . esc_html(sb_msg('home')) . '</a></p>';
    } else {
        if (is_search()) { echo '<h1>' . esc_html(get_search_query()) . '</h1>'; }
        elseif (is_archive()) { the_archive_title('<h1>', '</h1>'); }
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                echo '<article class="sb-item"><h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2>';
                the_excerpt();
                echo '</article>';
            }
            the_posts_pagination();
        } else {
            echo '<p>' . esc_html(sb_msg('nothing')) . '</p>';
        }
    }
    return ob_get_clean();
}
