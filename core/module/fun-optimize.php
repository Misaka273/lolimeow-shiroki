<?php
// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// =================默认开启优化项=================

// 隐藏后台工具栏--------------------------boxmoe.com--------------------------
function boxmoe_hide_admin_bar($flag) {
    return false;
}
add_filter('show_admin_bar', 'boxmoe_hide_admin_bar');

// 移除Open Sans字体--------------------------boxmoe.com--------------------------

function boxmoe_remove_open_sans() {
    wp_deregister_style('open-sans');
    wp_register_style('open-sans', false);
    wp_enqueue_style('open-sans', '');
}
add_action('init', 'boxmoe_remove_open_sans');

// 移除jQuery--------------------------boxmoe.com--------------------------
function boxmoe_custom_deregister_jquery() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'boxmoe_custom_deregister_jquery', 100);

// 屏蔽6.71新增无用的代码加载在前端--------------------------boxmoe.com--------------------------
function boxmoe_disable_add_auto_sizes( $add_auto_sizes ) {
    return false;
}
add_filter( 'wp_img_tag_add_auto_sizes', 'boxmoe_disable_add_auto_sizes' );

// 删除全局样式内联 CSS--------------------------boxmoe.com--------------------------
function boxmoe_remove_global_inline_css() {
    remove_action('wp_head', 'wp_print_styles');
}
add_action('init', 'boxmoe_remove_global_inline_css');
add_action('after_setup_theme', function() {
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
}, 0);
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'global-styles-inline' );
    wp_dequeue_style( 'classic-theme-styles' );
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wp-block-style' );
    // 🎬 禁用视频区块特定样式
    wp_dequeue_style( 'wp-block-video' );
    wp_dequeue_style( 'wp-block-video-style' );
    // 🎬 禁用所有单个区块的样式
    wp_dequeue_style( 'wp-block-audio' );
    wp_dequeue_style( 'wp-block-code' );
    wp_dequeue_style( 'wp-block-cover' );
    wp_dequeue_style( 'wp-block-embed' );
    wp_dequeue_style( 'wp-block-file' );
    wp_dequeue_style( 'wp-block-group' );
    wp_dequeue_style( 'wp-block-image' );
    wp_dequeue_style( 'wp-block-list' );
    wp_dequeue_style( 'wp-block-quote' );
    wp_dequeue_style( 'wp-block-table' );
}, 20 );


// =================自定义开启优化项=================

// 禁止非管理员访问后台--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_no_admin_switch')){
    function boxmoe_restrict_admin_access_optimize() {
        // 避免重定向循环：检查当前是否正在执行AJAX请求
        if (wp_doing_ajax()) {
            return;
        }
        // 检查是否是登录页面相关请求
        if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
            return;
        }
        // 检查是否是注册页面相关请求
        if (strpos($_SERVER['REQUEST_URI'], 'wp-register.php') !== false) {
            return;
        }
        // 检查是否是密码重置页面相关请求
        if (strpos($_SERVER['REQUEST_URI'], 'action=lostpassword') !== false || strpos($_SERVER['REQUEST_URI'], 'action=resetpass') !== false) {
            return;
        }
        // 检查是否是后台登录后跳转请求，包含reauth参数的情况
        if (strpos($_SERVER['REQUEST_URI'], 'reauth=1') !== false) {
            return;
        }
        // 检查用户是否已登录
        if (!is_user_logged_in()) {
            return;
        }
        // 检查用户是否有管理权限，只有非管理员才需要重定向
        if (!current_user_can('manage_options') && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF']) {
            // 避免重定向循环：检查是否已经在首页或首页相关页面
            $home_url = home_url();
            $home_path = parse_url($home_url, PHP_URL_PATH);
            if (empty($home_path)) {
                $home_path = '/';
            }
            
            // 检查当前请求是否已经是首页，避免循环
            if ($_SERVER['REQUEST_URI'] == $home_path || 
                $_SERVER['REQUEST_URI'] == $home_path . '/' || 
                $_SERVER['REQUEST_URI'] == $home_path . '/index.php') {
                exit;
            }
            
            // 检查是否已经由erphpdown插件处理过重定向
            if (function_exists('erphpdown_noadmin_redirect')) {
                $erphpdown_front_noadmin = get_option('erphp_url_front_noadmin');
                if ($erphpdown_front_noadmin == 'yes') {
                    // 如果erphpdown插件已经设置了重定向，就不再执行主题的重定向
                    return;
                }
            }
            
            // 执行重定向
            wp_safe_redirect( home_url() );
            exit();
        }
    }
    add_action('admin_init', 'boxmoe_restrict_admin_access_optimize');
}

// 关闭古藤堡编辑器，使用经典编辑器--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_gutenberg_switch')){
function boxmoe_disable_gutenberg() {
    remove_filter('the_content', 'do_blocks', 9);
    remove_action('admin_enqueue_scripts', 'wp_common_block_scripts_and_styles');
    add_filter('gutenberg_use_widgets_block_editor', '__return_false');
    add_filter('use_widgets_block_editor', '__return_false');
    add_filter('use_block_editor_for_post', '__return_false');
    remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles'); 
    add_action('wp_enqueue_scripts', 'fanly_remove_styles_inline');
    function fanly_remove_styles_inline() {
    wp_deregister_style('global-styles');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wp-block-style');
    // 🎬 禁用视频区块特定样式
    wp_dequeue_style( 'wp-block-video' );
    wp_dequeue_style( 'wp-block-video-style' );
    // 🎬 禁用所有单个区块的样式
    wp_dequeue_style( 'wp-block-audio' );
    wp_dequeue_style( 'wp-block-code' );
    wp_dequeue_style( 'wp-block-cover' );
    wp_dequeue_style( 'wp-block-embed' );
    wp_dequeue_style( 'wp-block-file' );
    wp_dequeue_style( 'wp-block-group' );
    wp_dequeue_style( 'wp-block-image' );
    wp_dequeue_style( 'wp-block-list' );
    wp_dequeue_style( 'wp-block-quote' );
    wp_dequeue_style( 'wp-block-table' );
    }
}
add_action('init', 'boxmoe_disable_gutenberg');
}

// 移除 WP_Head没用代码--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_wphead_switch')){
function boxmoe_remove_wp_head_unused_code() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles'); 
    remove_action('wp_head', 'rel_canonical');
    remove_action('wp_head', 'wp_shortlink_wp_head');
        add_filter('wp_robots', function($robots) {
        unset($robots['max-image-preview']);
        return $robots;
    }, 20);
}
add_action('init', 'boxmoe_remove_wp_head_unused_code');
}

// 移除dns-prefetch--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_dns_prefetch_switch')){
function boxmoe_remove_dns_prefetch($hints, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        return array_diff(wp_dependencies_unique_hosts(), $hints);
    }
    return $hints;
}
add_filter('wp_resource_hints', 'boxmoe_remove_dns_prefetch', 10, 2);
}


// 禁用 XML-RPC 接口--------------------------boxmoe.com--------------------------

if(get_boxmoe('boxmoe_xmlrpc_switch')){
function boxmoe_disable_xmlrpc() {
    add_filter('xmlrpc_enabled', '__return_false');
}
add_action('init', 'boxmoe_disable_xmlrpc');
}


// 移除feed--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_feed_switch')){
function boxmoe_remove_feed() {
    remove_action('do_feed_rdf', 'do_feed_rdf');
    remove_action('do_feed_rss', 'do_feed_rss');
    remove_action('do_feed_rss2', 'do_feed_rss2');
    remove_action('do_feed_atom', 'do_feed_atom');
}
add_action('init', 'boxmoe_remove_feed');
}


// 禁用Emoji表情--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_emojis_switch')){
function boxmoe_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_filter('the_content', 'wp_staticize_emoji');
    remove_filter('comment_text', 'wp_staticize_emoji');
    remove_filter('widget_text_content', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_filter('wp_resource_hints', 'wp_resource_hints', 10, 2);
}
add_action('init', 'boxmoe_disable_emojis');
}

// 禁用Embeds--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_embeds_switch')){
function boxmoe_disable_embeds() {
    wp_deregister_script('wp-embed');
}
add_action('init', 'boxmoe_disable_embeds');

function boxmoe_remove_embeds() {
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}
add_action('init', 'boxmoe_remove_embeds');
}

// 移除WordPress版本号--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_remove_wp_version_switch')){
function boxmoe_remove_wp_version() {
    return '';
}
add_filter('the_generator', 'boxmoe_remove_wp_version');
}


// 禁用文章修订版本--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_revision_switch')){
function boxmoe_disable_revisions($num, $post) {
    return 0;
}
add_filter('wp_revisions_to_keep', 'boxmoe_disable_revisions', 10, 2);
}


// 禁用文章自动保存--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_autosave_switch')){
function boxmoe_disable_autosave() {
    wp_deregister_script('autosave');
}
add_action('wp_enqueue_scripts', 'boxmoe_disable_autosave');
}


// 优化数据库自动清理--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_optimize_database_switch')){
function boxmoe_optimize_database() {
    if (!wp_next_scheduled('boxmoe_optimize_database_event')) {
        $timestamp = strtotime('today 00:00:00') + DAY_IN_SECONDS;
        wp_schedule_event($timestamp, 'daily', 'boxmoe_optimize_database_event');
    }
}
add_action('wp', 'boxmoe_optimize_database');

function boxmoe_run_optimize_database() {
    global $wpdb;
    $wpdb->query("OPTIMIZE TABLE $wpdb->posts");
    $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
    $wpdb->query("OPTIMIZE TABLE $wpdb->options");
}
add_action('boxmoe_optimize_database_event', 'boxmoe_run_optimize_database');
}


// 禁用REST API--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_disable_rest_api_switch')){
function boxmoe_disable_rest_api() {
    if (!is_user_logged_in()) {
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }
}
add_action('init', 'boxmoe_disable_rest_api');
}

// Trackbacks--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_trackbacks_switch')){
function boxmoe_disable_trackbacks() {
    remove_action('wp_head', 'wp_trackback_header', 10);
}
add_action('init', 'boxmoe_disable_trackbacks');
}

// 禁止Pingback--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_pingbacks_switch')){
function boxmoe_disable_pingbacks() {
    remove_action('wp_head', 'wp_generator');
}
add_action('init', 'boxmoe_disable_pingbacks');
}

// 📤 媒体库上传大小限制设置--------------------------boxmoe.com--------------------------
function boxmoe_set_upload_limits() {
    // 获取上传大小限制设置，默认为10MB
    $upload_max_filesize = get_boxmoe('boxmoe_upload_max_filesize', 10);
    // 获取执行时间限制设置，默认为30秒
    $max_execution_time = get_boxmoe('boxmoe_max_execution_time', 30);
    
    // 转换为字节单位
    $upload_max_filesize_bytes = $upload_max_filesize * 1024 * 1024;
    $post_max_size_bytes = $upload_max_filesize_bytes * 1.5; // POST大小设置为上传大小的1.5倍
    
    // 设置PHP上传限制参数
    ini_set('upload_max_filesize', $upload_max_filesize . 'M');
    ini_set('post_max_size', ceil($post_max_size_bytes / (1024 * 1024)) . 'M');
    ini_set('max_execution_time', $max_execution_time);
    ini_set('max_input_time', $max_execution_time);
}
add_action('init', 'boxmoe_set_upload_limits');

// 📤 WordPress媒体库上传大小限制设置--------------------------boxmoe.com--------------------------
function boxmoe_filter_upload_size_limit($size) {
    // 获取上传大小限制设置，默认为10MB
    $upload_max_filesize = get_boxmoe('boxmoe_upload_max_filesize', 10);
    // 转换为字节单位
    $limit_bytes = $upload_max_filesize * 1024 * 1024;
    // 返回较小的值（PHP限制和WordPress限制中的较小值）
    return min($size, $limit_bytes);
}
add_filter('upload_size_limit', 'boxmoe_filter_upload_size_limit');
