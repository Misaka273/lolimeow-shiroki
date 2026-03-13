<?php
/**
 * 验证码功能模块
 * @link https://www.chuyel.top
 * @package 初叶🍂
 * Version: 2.2.0 - Complete Fix
 */

// 安全设置
if (!defined('ABSPATH')) {
    exit('Access Denied');
}

/* 🔄 Session 会话管理优化 - 增强服务器兼容性 */
function boxmoe_init_session() {
    /* 🚫 排除 wp-login.php 页面，避免干扰密码保护等功能设置 cookie */
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
        return;
    }
    
    /* 如果 headers 已发送，无法启动 session */
    if (headers_sent()) {
        return;
    }
    
    /* 设置 session cookie 参数，确保跨域兼容 */
    if (!session_id()) {
        /* 设置 session cookie 有效期和路径 */
        $cookie_params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookie_params['lifetime'],
            'path' => '/',
            'domain' => '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        /* 启动 session */
        @session_start();
    }
}
add_action('init', 'boxmoe_init_session', 1);

// ==================== 验证码核心函数 ====================

/**
 * 生成验证码字符串
 */
function generate_captcha_code($type = 'normal', $length = 6) {
    $length = max(4, min(8, intval($length)));
    
    $chars = '';
    switch ($type) {
        case 'simple':
            $chars = '0123456789';
            break;
        case 'letter':
            $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
            break;
        case 'normal':
        default:
            $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
            break;
    }
    
    $code = '';
    $chars_length = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, $chars_length - 1)];
    }
    
    return $code;
}

/**
 * 生成验证码图片
 */
function generate_captcha_image($code) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    
    $width = 120;
    $height = 40;
    $image = imagecreatetruecolor($width, $height);
    
    if (!$image) return false;
    
    // 设置颜色
    $bg_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    $line_color = imagecolorallocate($image, 200, 200, 200);
    $pixel_color = imagecolorallocate($image, 150, 150, 150);
    
    // 填充背景
    imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);
    
    // 添加干扰线
    for ($i = 0; $i < 3; $i++) {
        imageline($image, 0, rand(0, $height), $width, rand(0, $height), $line_color);
    }
    
    // 添加干扰点
    for ($i = 0; $i < 50; $i++) {
        imagesetpixel($image, rand(0, $width), rand(0, $height), $pixel_color);
    }
    
    // 绘制文字
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($code);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    // 逐个字符绘制，添加扭曲效果
    for ($i = 0; $i < strlen($code); $i++) {
        $char = $code[$i];
        $char_color = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
        $char_y = $y + rand(-3, 3);
        
        imagestring($image, $font_size, $x + ($i * imagefontwidth($font_size)), $char_y, $char, $char_color);
    }
    
    // 输出图片
    header('Content-Type: image/png');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    imagepng($image);
    imagedestroy($image);
    
    return true;
}

/**
 * AJAX生成验证码图片
 */
add_action('wp_ajax_generate_captcha_image', 'boxmoe_ajax_generate_captcha_image');
add_action('wp_ajax_nopriv_generate_captcha_image', 'boxmoe_ajax_generate_captcha_image');

function boxmoe_ajax_generate_captcha_image() {
    // 确保会话已启动
    boxmoe_init_session();
    
    // 生成验证码
    $type = get_boxmoe('captcha_type') ?: 'normal';
    $length = get_boxmoe('captcha_length') ? intval(get_boxmoe('captcha_length')) : 6;
    $code = generate_captcha_code($type, $length);
    
    // 存储到会话（不区分大小写）
    $_SESSION['captcha_code'] = strtolower($code);
    $_SESSION['captcha_time'] = time();
    $_SESSION['captcha_session_id'] = session_id();
    
    // 生成图片
    generate_captcha_image($code);
    exit;
}

/**
 * 验证验证码 - 完整修复版
 */
function boxmoe_verify_captcha($input_code) {
    // 确保会话已启动
    boxmoe_init_session();
    
    // 检查会话数据
    if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
        return false;
    }
    
    $stored_code = $_SESSION['captcha_code'];
    $captcha_time = $_SESSION['captcha_time'];
    $expiry = get_boxmoe('captcha_expiry') ? intval(get_boxmoe('captcha_expiry')) : 300;
    
    // 检查是否过期
    if (time() - $captcha_time > $expiry) {
        unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
        return false;
    }
    
    // 清理和比较
    $input_code = strtolower(trim($input_code));
    $result = ($input_code === $stored_code);
    
    // 一次性使用，验证后清除
    unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
    
    return $result;
}

/**
 * AJAX验证码验证 - 修复版
 */
add_action('wp_ajax_verify_captcha', 'boxmoe_ajax_verify_captcha');
add_action('wp_ajax_nopriv_verify_captcha', 'boxmoe_ajax_verify_captcha');

function boxmoe_ajax_verify_captcha() {
    // 确保会话已启动
    boxmoe_init_session();
    
    $input_code = isset($_POST['captcha_code']) ? sanitize_text_field($_POST['captcha_code']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    
    // 验证nonce
    if (!wp_verify_nonce($nonce, 'captcha_verify')) {
        wp_send_json([
            'success' => false,
            'message' => '安全验证失败'
        ]);
        return;
    }
    
    if (empty($input_code)) {
        wp_send_json([
            'success' => false,
            'message' => '请输入验证码'
        ]);
        return;
    }
    
    $verified = boxmoe_verify_captcha($input_code);
    
    wp_send_json([
        'success' => $verified,
        'message' => $verified ? '验证码正确' : '验证码错误或已过期'
    ]);
}

/**
 * 登录表单验证码验证
 */
add_action('wp_ajax_validate_login_with_captcha', 'boxmoe_ajax_validate_login_with_captcha');
add_action('wp_ajax_nopriv_validate_login_with_captcha', 'boxmoe_ajax_validate_login_with_captcha');

function boxmoe_ajax_validate_login_with_captcha() {
    $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
    $captcha_code = isset($_POST['captcha_code']) ? sanitize_text_field($_POST['captcha_code']) : '';
    
    // 验证验证码
    if (!boxmoe_verify_captcha($captcha_code)) {
        wp_send_json([
            'success' => false,
            'message' => '验证码错误'
        ]);
        return;
    }
    
    // 验证登录数据
    if (empty($form_data['username']) || empty($form_data['password'])) {
        wp_send_json([
            'success' => false,
            'message' => '用户名和密码不能为空'
        ]);
        return;
    }
    
    wp_send_json([
        'success' => true,
        'message' => '验证通过'
    ]);
}

/**
 * 注册表单验证码验证
 */
add_action('wp_ajax_validate_register_with_captcha', 'boxmoe_ajax_validate_register_with_captcha');
add_action('wp_ajax_nopriv_validate_register_with_captcha', 'boxmoe_ajax_validate_register_with_captcha');

function boxmoe_ajax_validate_register_with_captcha() {
    $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
    $captcha_code = isset($_POST['captcha_code']) ? sanitize_text_field($_POST['captcha_code']) : '';
    
    // 验证验证码
    if (!boxmoe_verify_captcha($captcha_code)) {
        wp_send_json([
            'success' => false,
            'message' => '验证码错误'
        ]);
        return;
    }
    
    // 验证注册数据
    if (empty($form_data['username']) || empty($form_data['email']) || 
        empty($form_data['password']) || empty($form_data['confirmpassword'])) {
        wp_send_json([
            'success' => false,
            'message' => '所有字段都为必填项'
        ]);
        return;
    }
    
    if ($form_data['password'] !== $form_data['confirmpassword']) {
        wp_send_json([
            'success' => false,
            'message' => '两次输入的密码不一致'
        ]);
        return;
    }
    
    wp_send_json([
        'success' => true,
        'message' => '验证通过'
    ]);
}

/**
 * Cloudflare Turnstile验证
 */
add_action('wp_ajax_verify_cloudflare_captcha', 'boxmoe_ajax_verify_cloudflare_captcha');
add_action('wp_ajax_nopriv_verify_cloudflare_captcha', 'boxmoe_ajax_verify_cloudflare_captcha');

function boxmoe_ajax_verify_cloudflare_captcha() {
    $token = isset($_POST['cf_response']) ? sanitize_text_field($_POST['cf_response']) : '';
    $secret_key = get_boxmoe('captcha_cloudflare_secret_key');
    
    if (empty($token) || empty($secret_key)) {
        wp_send_json([
            'success' => false,
            'message' => '验证参数缺失'
        ]);
        return;
    }
    
    // 调用Cloudflare API验证
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $response = wp_remote_post($url, [
        'body' => $data,
        'timeout' => 10
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json([
            'success' => false,
            'message' => '验证服务异常'
        ]);
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if ($result && isset($result['success']) && $result['success']) {
        wp_send_json([
            'success' => true,
            'message' => '验证成功'
        ]);
    } else {
        wp_send_json([
            'success' => false,
            'message' => '人机验证失败'
        ]);
    }
}

// ==================== 验证码HTML生成 ====================

/**
 * 生成验证码nonce
 * 提前定义这个函数，避免函数未定义错误
 */
function boxmoe_generate_captcha_nonce() {
    return wp_create_nonce('captcha_verify');
}

/**
 * 获取验证码HTML
 */
function boxmoe_get_captcha_html($type = 'login') {
    $captcha_enabled = get_boxmoe('captcha_enabled');
    $captcha_type = get_boxmoe('captcha_type');
    $login_enabled = get_boxmoe('captcha_login_enabled');
    $register_enabled = get_boxmoe('captcha_register_enabled');
    $cloudflare_site_key = get_boxmoe('captcha_cloudflare_site_key');
    
    // 检查是否启用
    if (!$captcha_enabled) {
        return '';
    }
    
    if ($type === 'login' && !$login_enabled) {
        return '';
    }
    
    if ($type === 'register' && !$register_enabled) {
        return '';
    }
    
    ob_start();
    
    if ($captcha_type === 'cloudflare' && !empty($cloudflare_site_key)) {
        // Cloudflare Turnstile
        $widget_id = $type === 'login' ? 'login-captcha-widget' : 'register-captcha-widget';
        $response_id = $type === 'login' ? 'login-cf-response' : 'register-cf-response';
        ?>
        <div class="captcha-container mb-3">
            <div id="<?php echo esc_attr($widget_id); ?>" class="cf-turnstile-container"></div>
            <input type="hidden" id="<?php echo esc_attr($response_id); ?>" name="cf_response">
            <div class="captcha-message mt-2" id="<?php echo esc_attr($type); ?>-captcha-message"></div>
        </div>
        <?php
    } else {
        // 普通验证码
        $ajax_url = admin_url('admin-ajax.php?action=generate_captcha_image');
        ?>
        <div class="captcha-container mb-3">
            <label for="captcha-input-<?php echo esc_attr($type); ?>" class="form-label">验证码</label>
            <div class="d-flex align-items-center gap-2">
                <input type="text" 
                       id="captcha-input-<?php echo esc_attr($type); ?>" 
                       class="form-control captcha-input" 
                       name="captcha_code" 
                       placeholder="请输入验证码" 
                       required
                       maxlength="6"
                       autocomplete="off">
                <img src="<?php echo esc_url($ajax_url); ?>" 
                     class="captcha-image border rounded" 
                     alt="验证码" 
                     style="cursor: pointer; height: 38px;"
                     onclick="refreshCaptcha(this, '<?php echo esc_js($type); ?>')">
                <button type="button" class="btn btn-outline-secondary captcha-refresh" 
                        style="height: 38px;"
                        onclick="refreshCaptcha(this.closest('.captcha-container').querySelector('.captcha-image'), '<?php echo esc_js($type); ?>')">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="form-text">点击图片刷新验证码</div>
            <div class="captcha-message mt-2" id="<?php echo esc_attr($type); ?>-captcha-message"></div>
        </div>
        <?php
    }
    
    return ob_get_clean();
}

/**
 * 在登录/注册时强制验证验证码
 */
add_action('authenticate', 'boxmoe_validate_login_captcha', 30, 3);
function boxmoe_validate_login_captcha($user, $username, $password) {
    // 只在登录表单提交时验证
    if (isset($_POST['wp-submit']) && $_POST['wp-submit'] === '登录') {
        $captcha_enabled = get_boxmoe('captcha_enabled');
        $captcha_login_enabled = get_boxmoe('captcha_login_enabled');
        
        if ($captcha_enabled && $captcha_login_enabled) {
            $captcha_type = get_boxmoe('captcha_type');
            
            if ($captcha_type === 'cloudflare') {
                // Cloudflare验证
                $token = isset($_POST['cf_response']) ? sanitize_text_field($_POST['cf_response']) : '';
                $verified = boxmoe_verify_cloudflare_turnstile($token);
            } else {
                // 普通验证码
                $captcha_code = isset($_POST['captcha_code']) ? sanitize_text_field($_POST['captcha_code']) : '';
                $verified = boxmoe_verify_captcha($captcha_code);
            }
            
            if (!$verified) {
                return new WP_Error('captcha_error', '验证码错误或已过期');
            }
        }
    }
    
    return $user;
}

// 辅助函数
function is_dark_mode() {
    if (isset($_COOKIE['data-bs-theme'])) {
        return $_COOKIE['data-bs-theme'] === 'dark';
    }
    return false;
}

// 添加缺少的Cloudflare验证函数
function boxmoe_verify_cloudflare_turnstile($token) {
    if (empty($token)) {
        return false;
    }
    
    $secret_key = get_boxmoe('captcha_cloudflare_secret_key');
    if (empty($secret_key)) {
        return false;
    }
    
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $response = wp_remote_post($url, [
        'body' => $data,
        'timeout' => 10
    ]);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    return $result && isset($result['success']) && $result['success'];
}
?>