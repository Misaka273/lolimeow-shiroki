<?php
/**
 * @link https://gl.baimu.live/
 * @package 灵阈研都-纸鸢社
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}

// 🎯 创建订阅文章数据表
function shiroki_create_post_follow_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        post_id bigint(20) NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY user_post_follow (user_id, post_id),
        KEY post_id (post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'shiroki_create_post_follow_table');

// 🎯 检查用户是否已订阅文章
function shiroki_is_post_followed($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));
    return $result > 0;
}

// 🎯 获取文章订阅数量
function shiroki_get_post_follow_count($post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
        $post_id
    ));
    return $count ? $count : 0;
}

// 🎯 订阅文章
function shiroki_follow_post($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return array('success' => false, 'message' => '请先登录');
    }

    if (!get_post($post_id)) {
        return array('success' => false, 'message' => '文章不存在');
    }

    if (shiroki_is_post_followed($post_id, $user_id)) {
        return array('success' => false, 'message' => '已订阅此文章');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%s')
    );

    if ($result) {
        return array('success' => true, 'message' => '订阅成功');
    } else {
        return array('success' => false, 'message' => '订阅失败，请重试');
    }
}

// 🎯 取消订阅文章
function shiroki_unfollow_post($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return array('success' => false, 'message' => '请先登录');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $result = $wpdb->delete(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id
        ),
        array('%d', '%d')
    );

    if ($result) {
        return array('success' => true, 'message' => '已取消订阅');
    } else {
        return array('success' => false, 'message' => '取消订阅失败，请重试');
    }
}

// 🎯 获取订阅此文章的所有用户
function shiroki_get_post_followers($post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_follows';
    $results = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM $table_name WHERE post_id = %d",
        $post_id
    ));
    return $results;
}

// 🎯 AJAX处理订阅文章
add_action('wp_ajax_shiroki_follow_post', 'shiroki_ajax_follow_post');
add_action('wp_ajax_nopriv_shiroki_follow_post', 'shiroki_ajax_follow_post');

function shiroki_ajax_follow_post() {
    check_ajax_referer('shiroki_follow_post_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $action = isset($_POST['follow_action']) ? sanitize_text_field($_POST['follow_action']) : '';

    if (!$post_id) {
        wp_send_json_error(array('message' => '无效的文章ID'));
    }

    if ($action === 'follow') {
        $result = shiroki_follow_post($post_id);
    } elseif ($action === 'unfollow') {
        $result = shiroki_unfollow_post($post_id);
    } else {
        wp_send_json_error(array('message' => '无效的操作'));
    }

    if ($result['success']) {
        $follow_count = shiroki_get_post_follow_count($post_id);
        wp_send_json_success(array(
            'message' => $result['message'],
            'follow_count' => $follow_count
        ));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}

// 🎯 文章更新时发送邮件通知
add_action('save_post', 'shiroki_notify_post_followers', 10, 3);

function shiroki_notify_post_followers($post_id, $post, $update) {
    if (!$update || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if ($post->post_status !== 'publish') {
        return;
    }

    $followers = shiroki_get_post_followers($post_id);
    if (empty($followers)) {
        return;
    }

    $site_name = get_bloginfo('name');
    $post_title = get_the_title($post_id);
    $post_url = get_permalink($post_id);
    $post_author = get_the_author_meta('display_name', $post->post_author);

    $subject = "【{$site_name}】已更新《{$post_title}》";
    
    $message = "<html><head><meta charset='UTF-8'><style>";
    $message .= "body{font-family:'Microsoft YaHei',Arial,sans-serif;line-height:1.6;color:#333;}";
    $message .= ".container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:8px;}";
    $message .= ".header{background:#667eea;color:#fff;padding:20px;border-radius:8px 8px 0 0;text-align:center;}";
    $message .= ".content{padding:30px 20px;}";
    $message .= ".post-title{font-size:20px;font-weight:bold;margin-bottom:15px;color:#667eea;}";
    $message .= ".post-meta{color:#666;font-size:14px;margin-bottom:20px;}";
    $message .= ".btn{display:inline-block;padding:12px 30px;background:#667eea;color:#fff;text-decoration:none;border-radius:5px;margin:20px 0;}";
    $message .= ".btn:hover{background:#5568d3;}";
    $message .= ".footer{text-align:center;color:#999;font-size:12px;padding:20px;border-top:1px solid #eee;}";
    $message .= "</style></head><body>";
    $message .= "<div class='container'>";
    $message .= "<div class='header'><h2>🥰您订阅的文章已更新</h2></div>";
    $message .= "<div class='content'>";
    $message .= "<p>😘您好，</p>";
    $message .= "<p>🎈您订阅的文章已更新，快来看看吧！</p>";
    $message .= "<div class='post-title'>《{$post_title}》</div>";
    $message .= "<div class='post-meta'>作者：{$post_author}</div>";
    $message .= "<a href='{$post_url}' class='btn'>查看文章🚀</a>";
    $message .= "</div>";
    $message .= "<div class='footer'>";
    $message .= "<p>本邮件由系统自动发送，请勿回复</p>";
    $message .= "<p>{$site_name}</p>";
    $message .= "</div></div></body></html>";

    $headers = array('Content-Type: text/html; charset=UTF-8');

    foreach ($followers as $user_id) {
        $user = get_userdata($user_id);
        if ($user && !empty($user->user_email)) {
            $user_name = $user->display_name;
            $personal_message = $message;
            $personal_message = str_replace('<p>😘您好，</p>', "<p>😘您好，<strong>{$user_name}</strong></p>", $personal_message);
            wp_mail($user->user_email, $subject, $personal_message, $headers);
        }
    }
}

// 🎯 在前端加载订阅文章的脚本
add_action('wp_enqueue_scripts', 'shiroki_enqueue_post_follow_script', 20);

function shiroki_enqueue_post_follow_script() {
    if (is_single()) {
        $jquery_handle = get_boxmoe('boxmoe_jquery_switch') ? 'jquery-script' : 'jquery';
        wp_enqueue_script('shiroki-post-follow', get_template_directory_uri() . '/assets/js/shiroki-post-follow.js', array($jquery_handle), '1.0.0', true);
        wp_localize_script('shiroki-post-follow', 'shirokiPostFollow', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shiroki_follow_post_nonce')
        ));
    }
}
