<?php
/**
 * Template Name: 登录页面
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}

// 获取验证码设置
$captcha_enabled = get_boxmoe('captcha_enabled');
$captcha_type = get_boxmoe('captcha_type');
$captcha_login_enabled = get_boxmoe('captcha_login_enabled');
$captcha_register_enabled = get_boxmoe('captcha_register_enabled');
$cloudflare_site_key = get_boxmoe('captcha_cloudflare_site_key');

// 加载Cloudflare脚本
if ($captcha_enabled && $captcha_type === 'cloudflare' && !empty($cloudflare_site_key)) {
    wp_enqueue_script('cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true);
}

// 注册验证码脚本
function register_captcha_scripts() {
    global $captcha_enabled, $captcha_type, $cloudflare_site_key;
    
    // 验证码核心脚本
    if ($captcha_enabled) {
        wp_enqueue_script('captcha-manager', get_template_directory_uri() . '/assets/js/captcha.js', array('jquery'), '2.0.0', true);
        
        // 传递验证码设置给JavaScript
        wp_localize_script('captcha-manager', 'boxmoe_captcha_settings', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'enabled' => $captcha_enabled,
            'type' => $captcha_type,
            'login_enabled' => get_boxmoe('captcha_login_enabled'),
            'register_enabled' => get_boxmoe('captcha_register_enabled'),
            'cloudflare_site_key' => $cloudflare_site_key,
            'captcha_nonce' => wp_create_nonce('captcha_verify')
        ));
    }
}
add_action('wp_enqueue_scripts', 'register_captcha_scripts');

//如果用户已经登陆那么跳转到首页或重定向页面
if (is_user_logged_in()){
   // 🔗 检查是否有 reauth 参数，如果有则不重定向，允许重新认证
   if (isset($_GET['reauth']) && $_GET['reauth'] == '1') {
       // 如果是重新认证请求，允许继续访问登录页面
       // 这里不需要重定向，直接退出条件判断
   } else {
       // 🔗 检查是否有重定向参数
       if (isset($_GET['redirect_to'])) {
           $redirect_url = urldecode($_GET['redirect_to']);
           // 验证重定向地址的安全性
           if (wp_validate_redirect($redirect_url)) {
               // 避免重定向循环：检查是否已经在目标页面
               if (strpos($_SERVER['REQUEST_URI'], basename(parse_url($redirect_url, PHP_URL_PATH))) === false) {
                   wp_safe_redirect($redirect_url);
                   exit;
               }
           }
       }
       
       // 检查用户是否是管理员，如果是管理员则跳转到后台
       if (current_user_can('manage_options')) {
           // 避免重定向循环：检查是否已经在后台
           if (strpos($_SERVER['REQUEST_URI'], 'wp-admin') === false) {
               wp_safe_redirect( admin_url() );
               exit;
           }
       }
       
       // 普通用户跳转到首页
       // 避免重定向循环：检查是否已经在首页
       $home_url = get_option('home');
       $home_path = parse_url($home_url, PHP_URL_PATH);
       if (empty($home_path)) {
           $home_path = '/';
       }
       
       // 检查当前请求是否已经是首页，避免循环
       if ($_SERVER['REQUEST_URI'] == $home_path || $_SERVER['REQUEST_URI'] == $home_path . '/') {
           exit;
       }
       
       wp_safe_redirect( $home_url );
       exit;
   }
}
?>
<html <?php language_attributes(); ?>>
    <head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title><?php echo boxmoe_theme_title(); ?></title>
   <link rel="icon" href="<?php echo boxmoe_favicon(); ?>" type="image/x-icon">
    <?php boxmoe_keywords(); ?>
    <?php boxmoe_description(); ?>
    <?php ob_start();wp_head();$wp_head_output = ob_get_clean();echo preg_replace('/\n/', "\n    ", trim($wp_head_output))."\n    ";?>
    <!-- 传递验证码设置给JS -->
    <meta name="captcha-settings" content='<?php echo json_encode([
        'type' => $captcha_type,
        'enabled' => $captcha_enabled,
        'loginEnabled' => $captcha_login_enabled,
        'registerEnabled' => $captcha_register_enabled,
        'cloudflareSiteKey' => $cloudflare_site_key
    ]); ?>'>
    <?php if ($captcha_type === 'cloudflare' && !empty($cloudflare_site_key)): ?>
    <meta name="captcha-cloudflare-sitekey" content="<?php echo esc_attr($cloudflare_site_key); ?>">
    <?php endif; ?>
    <style>
        /* 🥳 登录页样式 - 双面板设计 */
        :root {
            --primary: #5995fd;
            --primary-dark: #4d84e2;
            --bg: rgba(176, 208, 255, 0.56);
            --text: #222;
            --white: rgba(202, 214, 255, 0.6);
        }
        
        /* 🌟 背景图设置 */
        body {
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0;
            animation: fadeInScale 1.5s ease-out forwards;
            background-color: #dde9ffff; /* 确保图片加载前有背景色 */
        }
        
        /* 🌟 背景图淡入放大动画 */
        @keyframes fadeInScale {
            0% {
                opacity: 0;
                background-size: 105% auto;
            }
            100% {
                opacity: 1;
                background-size: cover;
            }
        }
        
        /* 🌟 隐藏的预加载图片 */
        #bg-preloader {
            position: absolute;
            top: -9999px;
            left: -9999px;
            width: 0;
            height: 0;
            opacity: 0;
        }
        
        /* 🌟 解决无法全屏显示 */
        html, body, main {
            width: 100vw !important;
            height: 100vh !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            position: relative !important;
            left: 0 !important;
            top: 0 !important;
        }
        
        /* 🌟 强制覆盖所有可能的居中样式 */
        body > * {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            max-width: none !important;
            width: 100vw !important;
        }
        
        /* 🌟 确保容器绝对定位 */
        .container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
            overflow: hidden !important;
            background-color: var(--white);
            z-index: 1;
            backdrop-filter: blur(20px); /* 增强高斯模糊效果 */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            overflow: hidden; /* 防止页面滚动 */
        }
        
        main {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
        }
        
        input {
            font-family: "Poppins", sans-serif;
        }
        
        /* 🌟 强制覆盖所有可能的居中样式 */
        body > * {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            max-width: none !important;
            width: 100vw !important;
        }
        
        /* 🌟 确保body强制全屏 */
        body {
            display: block !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            overflow: hidden !important;
        }
        
        /* 🌟 确保main标签强制全屏 */
        main {
            display: block !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        /* 🌟 粒子效果样式 */
        #particles-js {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1 !important;
            overflow: hidden !important;
        }
        
        .container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
            overflow: hidden !important;
            background-color: var(--white);
            z-index: 2 !important;
            transform: none !important;
            display: block !important;
            box-sizing: border-box !important;
            border: none !important;
            outline: none !important;
        }
        
        /* 🌟 防止WordPress添加额外容器 */
        body > *:not(main) {
            display: none !important;
        }
        
        /* 🌟 确保main是唯一显示的容器 */
        body > main {
            display: block !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
        }
        
        .container::before {
            content: "";
            position: absolute;
            height: 2000px;
            width: 2000px;
            top: -10%;
            right: 52%;
            transform: translateY(-50%);
            background-image: linear-gradient(-45deg, #1c5fd1 0%, #1ec3fa 100%);
            transition: 1.8s ease-in-out;
            border-radius: 50%;
            z-index: 6;
        }
        
        .forms-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }
        
        .signin-signup {
            position: absolute;
            top: 50%;
            transform: translate(0, -50%);
            right: 0;
            width: 50%;
            transition: 1s 0.7s ease-in-out;
            display: grid;
            grid-template-columns: 1fr;
            z-index: 5;
        }
        
        form {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 5rem;
            transition: all 0.2s 0.7s;
            overflow: hidden;
            grid-column: 1 / 2;
            grid-row: 1 / 2;
        }
        
        form.sign-up-form {
            opacity: 0;
            z-index: 1;
        }
        
        form.sign-in-form {
            z-index: 2;
        }
        
        .title {
            font-size: 2.2rem;
            color: #444;
            margin-bottom: 10px;
        }
        
        /* 🌟 网站Logo样式 */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        /* 🏷️ 浮动标签与动态文本 - 勋章效果 */
        .floating-label-group {
            position: relative;
            margin-bottom: 1.5rem;
            width: 100%;
            max-width: 380px;
            min-height: 60px;
        }
        .floating-label-group .form-control {
            height: 3.5rem;
            padding: 1.25rem 40px 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
            /* 隐藏浏览器默认的密码显示按钮 */
            -moz-appearance: none;
            -webkit-appearance: none;
        }

        /* 强制隐藏浏览器默认的密码显示按钮 */
        input[type="password"]::-ms-reveal {
            display: none;
        }

        input[type="password"]::-webkit-clear-button,
        input[type="password"]::-webkit-inner-spin-button,
        input[type="password"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* 确保显示密码按钮始终可见 */
        .toggle-password {
            z-index: 10 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            opacity: 0.7 !important;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text);
            transition: opacity 0.3s ease;
        }

        .toggle-password:hover {
            opacity: 1 !important;
        }

        .toggle-password:focus {
            outline: none !important;
            opacity: 1 !important;
        }
        .floating-label-group .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(89, 149, 253, 0.2);
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        .floating-label-group label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: 0.2s ease all;
            color: #6c757d;
            padding: 0 5px;
            z-index: 5;
            margin: 0;
            width: auto;
            height: auto;
            font-size: 1rem;
            border-radius: 0;
            background: transparent;
            box-sizing: border-box;
            line-height: 1.2;
            font-weight: 500;
        }
        .floating-label-group label::after {
            content: attr(data-default);
            transition: all 0.2s ease;
        }
        /* 激活状态 */
        .floating-label-group .form-control:focus ~ label,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label {
            top: 0;
            left: 0.8rem;
            font-size: 0.75rem;
            transform: translateY(-50%);
            color: var(--primary);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 4px;
        }
        .floating-label-group .form-control:focus ~ label::after,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label::after {
            content: attr(data-active);
        }
        /* 表单验证反馈 - 避免影响输入框布局 */
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
            position: absolute;
            bottom: -25px;
            left: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            z-index: 3;
            background: transparent;
            box-sizing: border-box;
            text-align: left;
        }
        /* 表单消息样式 - 避免影响输入框布局 */
        #login-message,
        #signup-message {
            width: 100%;
            max-width: 380px;
            margin: 10px 0;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            box-sizing: border-box;
            position: relative;
            z-index: 10;
        }
        /* 验证码按钮样式 - 修复被盖住问题 */
        .floating-label-group .Acquire_box {
            position: absolute !important;
            right: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .floating-label-group .Acquire {
            display: inline-block !important;
            background: var(--primary) !important;
            color: white !important;
            padding: 8px 16px !important;
            border-radius: 20px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            border: none !important;
            line-height: 1 !important;
            height: auto !important;
            min-height: 32px !important;
            box-sizing: border-box !important;
        }

        /* 确保验证码输入框有足够的右侧内边距 */
        .floating-label-group input[name="verificationcode"] {
            padding-right: 130px !important;
        }
        /* 按钮样式优化 - 统一居中样式 */
        .btn {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            box-shadow: 0 4px 6px rgba(89, 149, 253, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(89, 149, 253, 0.4);
        }
        
        .btn::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -100%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                135deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                rgba(255, 255, 255, 0.6),
                rgba(255, 255, 255, 0.4),
                transparent
            );
            transform: rotate(30deg);
            transition: all 0.8s ease;
        }
        
        .btn:hover::after {
            left: 100%;
        }
        
        .input_field {
            max-width: 380px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            margin: 10px 0;
            height: 55px;
            border-radius: 55px;
            display: grid;
            grid-template-columns: 15% 85%;
            padding: 0 0.4rem;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .input_field input {
            background: none;
            outline: none;
            border: none;
            line-height: 1;
            min-width: 270px;
            font-weight: 600;
            font-size: 1.1rem;
            padding-left: 10px;
            color: var(--text);
            font-size: 16px;
        }
        
        .input_field input::placeholder {
            color: rgba(0, 0, 0, 0.4);
            font-weight: 500;
        }
        
        .shortMessage,
        .Password_login {
            width: 16px;
            height: 16px;
            display: inline-block;
            text-align: center;
            vertical-align: baseline;
            position: relative;
            border-radius: 50%;
            outline: none;
            -webkit-appearance: none;
            border: 1px solid #fff;
            -webkit-tab-highlight-color: rgba(0, 0, 0, 0);
            color: #fff;
            background: #fff;
        }
        
        .shortMessage::before,
        .Password_login::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            background: #fff;
            width: 100%;
            height: 100%;
            border: 1px solid #999999;
            border-radius: 50%;
            color: #fff;
        }
        
        .shortMessage:checked::before,
        .Password_login:checked::before {
            content: "\2713";
            background-color: #51a7e0;
            border: 1px solid #51a7e0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            color: #fff;
            font-size: 0.52rem;
            border-radius: 50%;
        }
        
        .agree_text {
            padding-left: 4px;
            white-space: normal;
            word-break: break-all;
            font-size: 12px;
            line-height: 21px;
        }
        
        .agree_text a {
            color: #51a7e0;
            text-decoration: none;
        }
        
        .btn {
            width: 200px;
            background-color: #5995fd;
            border: none;
            outline: none;
            height: 49px;
            border-radius: 49px;
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            margin: 10px 0;
            cursor: pointer;
            transition: 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn:hover {
            background-color: #4d84e2;
        }
        
        .panels-container {
            position: absolute;
            height: 100%;
            width: 100%;
            top: 0;
            left: 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
        
        .panel {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-around;
            text-align: center;
            z-index: 6;
        }
        
        .left-panel {
            pointer-events: all;
            padding: 3rem 17% 2rem 12%;
        }
        
        .right-panel {
            pointer-events: none;
            padding: 3rem 12% 2rem 17%;
        }
        
        .panel .content {
            color: #fff;
            transition: transform 0.9s ease-in-out;
            transition-delay: 0.6s;
        }
        
        .panel h3 {
            font-weight: 600;
            line-height: 1;
            font-size: 1.5rem;
        }
        
        .panel p {
            font-size: 0.95rem;
            padding: 0.7rem 0;
        }
        
        .btn.transparent {
            margin: 0;
            background: none;
            border: 2px solid #fff;
            width: auto;
            height: auto;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            padding: 10px 50px;
            box-sizing: border-box;
            line-height: 1.2;
            letter-spacing: 0;
            text-indent: 0;
            border-radius: 22px;
            display: inline-block;
        }
        
        .image {
            width: 100%;
            transition: transform 1.1s ease-in-out;
            transition-delay: 0.4s;
        }
        
        .right-panel .image,
        .right-panel .content {
            transform: translateX(800px);
        }
        
        /* 🌙 暗色模式适配 */
        [data-bs-theme="dark"] {
            --primary: #6e9eff;
            --primary-dark: #5a87e3;
            --bg: #1a1a1a;
            --text: #e0e0e0;
            --white: #2d2d2d;
        }
        
        [data-bs-theme="dark"] .title {
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .input_field {
            background-color: #3d3d3d;
        }
        
        [data-bs-theme="dark"] .input_field input {
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .input_field input::placeholder {
            color: #888;
        }
        
        /* ANIMATION */
        
        /* 注册模式样式 */
        .container.sign-up-mode::before {
            transform: translate(100%, -50%);
            right: 52%;
        }
        
        .container.sign-up-mode .signin-signup {
            left: 0;
            right: auto;
        }
        
        .container.sign-up-mode form.sign-up-form {
            opacity: 1;
            z-index: 2;
        }
        
        .container.sign-up-mode form.sign-in-form {
            opacity: 0;
            z-index: 1;
        }
        
        .container.sign-up-mode .left-panel .content,
        .container.sign-up-mode .left-panel .image {
            transform: translateX(-800px);
        }
        
        .container.sign-up-mode .right-panel .content,
        .container.sign-up-mode .right-panel .image {
            transform: translateX(0);
        }
        
        .container.sign-up-mode .left-panel {
            pointer-events: none;
        }
        
        .container.sign-up-mode .right-panel {
            pointer-events: all;
        }
        
        /* 验证码相关样式 */
        .captcha-wrapper {
            max-width: 380px;
            width: 100%;
            margin: 15px 0;
            position: relative;
        }
        
        .captcha-label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        [data-bs-theme="dark"] .captcha-label {
            color: #aaa;
        }
        
        .input-group-captcha {
            display: flex;
            gap: 10px;
            align-items: stretch;
            max-width: 380px;
        }
        
        .input-group-captcha .captcha-input {
            flex: 1;
            min-width: 0;
            height: 3.5rem;
            padding: 1.25rem 15px 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            border-color: transparent;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            color: var(--text);
            font-size: 1rem;
            outline: none;
        }
        
        .input-group-captcha .captcha-image-container {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .captcha-image {
            height: 45px;
            width: 120px;
            border-radius: 5px;
            cursor: pointer;
            background: #f5f5f5;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .captcha-image:hover {
            opacity: 0.8;
        }
        
        .captcha-refresh {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            height: 45px;
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .captcha-refresh:hover {
            background: var(--primary-dark);
            transform: rotate(90deg);
        }
        
        .cf-turnstile-container {
            margin: 10px 0;
            min-height: 65px;
            display: flex;
            justify-content: center;
        }
        
        .captcha-message {
            font-size: 12px;
            color: #ff0000;
            margin-top: 5px;
            min-height: 20px;
            text-align: center;
        }
        
        .captcha-message.success {
            color: #28a745;
        }
        
        .captcha-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.6);
            color: var(--text);
            outline: none;
            transition: all 0.3s ease;
        }
        
        .captcha-input:focus {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(89, 149, 253, 0.2);
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        
        .captcha-input.error {
            border-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }
        
        .captcha-input.success {
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }
        
        /* 加载状态 */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }
        
        .btn-loading:after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-top: -8px;
            margin-left: -8px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: btn-loading-spinner 0.6s linear infinite;
        }
        
        @keyframes btn-loading-spinner {
            to { transform: rotate(360deg); }
        }
        
        /* 防止表单提交动画 */
        .form-submitting {
            pointer-events: none;
            opacity: 0.7;
        }
        
        /* 错误提示 */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            text-align: center;
        }
        
        .success-message {
            color: #28a745;
            font-size: 12px;
            margin-top: 5px;
            text-align: center;
        }
        
        /* 验证码刷新动画 */
        .captcha-success {
            animation: captcha-success 0.6s ease;
        }
        
        @keyframes captcha-success {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* 暗色模式下的验证码样式 */
        [data-bs-theme="dark"] .captcha-input,
        [data-bs-theme="dark"] .input-group-captcha .captcha-input {
            background: #3d3d3d;
            border-color: #555;
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .captcha-input:focus,
        [data-bs-theme="dark"] .input-group-captcha .captcha-input:focus {
            background: #4a4a4a;
            border-color: #6e9eff;
        }
        
        [data-bs-theme="dark"] .captcha-image {
            background: #3d3d3d;
            border-color: #555;
        }
        
        @media (max-width: 870px) {
            .container {
                min-height: 800px;
                height: 100vh;
            }
            
            .signin-signup {
                width: 100%;
                top: 95%;
                transform: translate(-50%, -100%);
                transition: 1s 0.8s ease-in-out;
            }
            
            .signin-signup,
            .container.sign-up-mode .signin-signup {
                left: 50%;
                transform: translate(-50%, -100%);
                right: auto;
            }
            
            .panels-container {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 2fr 1fr;
            }
            
            .panel {
                flex-direction: row;
                justify-content: space-around;
                align-items: center;
                padding: 2.5rem 8%;
                grid-column: 1 / 2;
            }
            
            .right-panel {
                grid-row: 3 / 4;
            }
            
            .left-panel {
                grid-row: 1 / 2;
            }
            
            .image {
                width: 200px;
                transition: transform 0.9s ease-in-out;
                transition-delay: 0.6s;
            }
            
            .panel .content {
                padding-right: 15%;
                transition: transform 0.9s ease-in-out;
                transition-delay: 0.8s;
            }
            
            .panel h3 {
                font-size: 1.2rem;
            }
            
            .panel p {
                font-size: 0.7rem;
                padding: 0.5rem 0;
            }
            
            .btn.transparent {
                width: auto;
                height: auto;
                font-size: 0.7rem;
                text-align: center;
                padding: 8px 17px;
                box-sizing: border-box;
                line-height: 1.2;
                display: inline-block;
            }
            
            .container:before {
                width: 1500px;
                height: 1500px;
                transform: translateX(-50%);
                left: 30%;
                bottom: 68%;
                right: initial;
                top: initial;
                transition: 2s ease-in-out;
            }
            
            .container.sign-up-mode:before {
                transform: translate(-50%, 100%);
                bottom: 32%;
                right: initial;
            }
            
            .container.sign-up-mode .left-panel .image,
            .container.sign-up-mode .left-panel .content {
                transform: translateY(-300px);
            }
            
            .container.sign-up-mode .right-panel .image,
            .container.sign-up-mode .right-panel .content {
                transform: translateY(0px);
            }
            
            .right-panel .image,
            .right-panel .content {
                transform: translateY(300px);
            }
            
            .container.sign-up-mode .signin-signup {
                top: 5%;
                transform: translate(-50%, 0);
            }
            
            .captcha-field,
            .input-group-captcha {
                flex-direction: column;
                align-items: stretch;
            }
            
            .captcha-refresh {
                width: 100%;
                margin-top: 5px;
            }
        }
        
        @media (max-width: 570px) {
            form {
                padding: 0 1.5rem;
            }
            
            .image {
                display: none;
            }
            
            .panel .content {
                padding: 0.5rem 1rem;
            }
            
            .container {
                padding: 1.5rem;
            }
            
            .container:before {
                bottom: 72%;
                left: 50%;
            }
            
            .container.sign-up-mode:before {
                bottom: 28%;
                left: 50%;
            }
            
            .cf-turnstile-container {
                transform: scale(0.9);
                transform-origin: center center;
            }
            
            .captcha-input,
            .input-group-captcha .captcha-input {
                padding: 10px 12px;
                font-size: 13px;
            }
        }

    </style>
    <!-- 🌟 移动端专用样式文件 -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/mobile-signin-shiroki.css">
</head>

<body>
   <main>
      <!-- 🥳 双面板登录页面 -->
      <div class="container">
         <!-- 表单区 -->
         <div class="forms-container">
            <div class="signin-signup">
               <!-- 登录表单 -->
               <form class="sign-in-form needs-validation" action="" method="post" id="loginform" novalidate>
                  <div class="logo-container">
                     <?php boxmoe_logo(); ?>
                  </div>
                  <h2 class="title">登录</h2>
                  <div class="floating-label-group">
                     <input type="text" name="username" class="form-control" id="username" required placeholder=" " />
                     <label for="username" data-default="请输入用户名" data-active="用户名"></label>
                     <div class="invalid-feedback">请输入有效的用户名。</div>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="password" class="form-control" id="password" required placeholder=" " />
                     <label for="password" data-default="请输入密码" data-active="密码"></label>
                     <div class="invalid-feedback">请输入密码。</div>
                     <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  
                  <!-- 登录验证码 -->
                  <?php if ($captcha_enabled && $captcha_login_enabled): ?>
                  <div class="captcha-wrapper">
                      <?php if ($captcha_type === 'cloudflare' && !empty($cloudflare_site_key)): ?>
                          <div id="login-captcha-widget" class="cf-turnstile-container"></div>
                          <input type="hidden" id="login-cf-response" name="cf_response">
                          <div class="captcha-message" id="login-captcha-message"></div>
                      <?php else: ?>
                          <div class="input-group-captcha">
                              <input type="text" 
                                     class="captcha-input" 
                                     name="captcha_code" 
                                     placeholder="请输入验证码" 
                                     required
                                     maxlength="6"
                                     autocomplete="off"
                                     id="login-captcha-input">
                              <div class="captcha-image-container">
                                  <img src="<?php echo admin_url('admin-ajax.php'); ?>?action=generate_captcha_image&t=<?php echo time(); ?>" 
                                       class="captcha-image" 
                                       alt="验证码"
                                       id="login-captcha-image"
                                       onclick="refreshCaptcha('login')">
                                  <button type="button" 
                                          class="captcha-refresh" 
                                          title="刷新验证码" 
                                          onclick="refreshCaptcha('login')">
                                      ↻
                                  </button>
                              </div>
                          </div>
                          <div class="captcha-message" id="login-captcha-message"></div>
                      <?php endif; ?>
                  </div>
                  <?php endif; ?>
                  
                  <p class="social_text">
                     <input class="Password_login" type="checkbox" name="rememberme" id="rememberme">
                     <span class="agree_text">
                        记住账号
                     </span>
                  </p>
                  <?php wp_nonce_field('user_login', 'login_nonce'); ?>
                  <button class="btn" type="submit" name="login_submit" id="login-submit-btn">
                     <span class="btn-text">Go</span>
                  </button>
                  <div id="login-message" class="mt-3"></div>
                  <div class="mt-3">
                     <a href="<?php echo boxmoe_reset_password_link_page(); ?>" class="text-primary text-decoration-none">忘记密码?</a>
                  </div>
               </form>

               <!-- 注册表单 -->
               <form class="sign-up-form needs-validation" id="signupform" novalidate>
                  <div class="logo-container">
                     <?php boxmoe_logo(); ?>
                  </div>
                  <h2 class="title">注册</h2>
                  <div class="floating-label-group">
                     <input type="text" name="username" class="form-control" id="signupFullnameInput" required placeholder=" " />
                     <label for="signupFullnameInput" data-default="请输入用户名" data-active="用户名"></label>
                     <div class="invalid-feedback">请输入有效的用户名。</div>
                  </div>
                  <div class="floating-label-group">
                     <input type="email" name="email" class="form-control" id="signupEmailInput" required placeholder=" " />
                     <label for="signupEmailInput" data-default="请输入邮箱" data-active="邮箱"></label>
                     <div class="invalid-feedback">请输入有效的邮箱地址。</div>
                  </div>
                  
                  <!-- 注册验证码 -->
                  <?php if ($captcha_enabled && $captcha_register_enabled): ?>
                  <div class="captcha-wrapper">
                      <?php if ($captcha_type === 'cloudflare' && !empty($cloudflare_site_key)): ?>
                          <div id="register-captcha-widget" class="cf-turnstile-container"></div>
                          <input type="hidden" id="register-cf-response" name="cf_response">
                          <div class="captcha-message" id="register-captcha-message"></div>
                      <?php else: ?>
                          <div class="input-group-captcha">
                              <input type="text" 
                                     class="captcha-input" 
                                     name="captcha_code" 
                                     placeholder="请输入验证码" 
                                     required
                                     maxlength="6"
                                     autocomplete="off"
                                     id="register-captcha-input">
                              <div class="captcha-image-container">
                                  <img src="<?php echo admin_url('admin-ajax.php'); ?>?action=generate_captcha_image&t=<?php echo time(); ?>" 
                                       class="captcha-image" 
                                       alt="验证码"
                                       id="register-captcha-image"
                                       onclick="refreshCaptcha('register')">
                                  <button type="button" 
                                          class="captcha-refresh" 
                                          title="刷新验证码" 
                                          onclick="refreshCaptcha('register')">
                                      ↻
                                  </button>
                              </div>
                          </div>
                          <div class="captcha-message" id="register-captcha-message"></div>
                      <?php endif; ?>
                  </div>
                  <?php endif; ?>
                  
                  <div class="floating-label-group">
                     <input type="text" name="verificationcode" class="form-control" id="signupVerificationCode" required placeholder=" " style="padding-right: 130px !important;" />
                     <label for="signupVerificationCode" data-default="请输入验证码" data-active="验证码"></label>
                     <div class="invalid-feedback">请输入验证码。</div>
                     <div class="Acquire_box" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                        <span class="Acquire" id="sendVerificationCode" style="display: inline-block; background: var(--primary); color: white; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px;">获取验证码</span>
                     </div>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="password" class="form-control" id="formSignUpPassword" required placeholder=" " />
                     <label for="formSignUpPassword" data-default="请设置密码" data-active="密码"></label>
                     <div class="invalid-feedback">请设置密码。</div>
                     <button type="button" class="toggle-password" data-target="formSignUpPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="confirmpassword" class="form-control" id="formSignUpConfirmPassword" required placeholder=" " />
                     <label for="formSignUpConfirmPassword" data-default="请确认密码" data-active="确认密码"></label>
                     <div class="invalid-feedback">请确认密码。</div>
                     <button type="button" class="toggle-password" data-target="formSignUpConfirmPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  <p class="social_text">
                     <input class="shortMessage" type="checkbox" name="agree" required />
                     <span class="agree_text">
                        已阅读并同意<a href="#">《用户协议》</a><a href="#">《隐私政策》</a>
                     </span>
                  </p>
                  <input type="hidden" name="signup_nonce" value="<?php echo wp_create_nonce('user_signup'); ?>">
                  <button class="btn" type="submit" name="signup_submit" id="signup-submit-btn">
                     <span class="btn-text">Go</span>
                  </button>
                  <div id="signup-message" class="mt-3"></div>
               </form>
            </div>
         </div>

         <!-- 双面板 -->
         <div class="panels-container">
            <div class="panel left-panel">
               <div class="content">
                  <h3>新用户?</h3>
                  <p>
                     注册账号，开始您的旅程，探索更多精彩内容。
                  </p>
                  <button class="btn transparent" id="sign-up-btn">注册</button>
               </div>
               <img class="image" src="<?php echo get_template_directory_uri(); ?>/assets/images/logon/注册.png" alt="注册" />
            </div>

            <div class="panel right-panel">
               <div class="content">
                  <h3>已有账号?</h3>
                  <p>
                     登录您的账号，继续之前的体验。
                  </p>
                  <button class="btn transparent" id="sign-in-btn">登录</button>
               </div>
               <img class="image" src="<?php echo get_template_directory_uri(); ?>/assets/images/logon/登录.png" alt="登录" />
            </div>
         </div>
      </div>

      <!-- 📝 主题版权信息 -->
      <style>
          .theme-copyright {
              position: fixed;
              bottom: 20px;
              left: 50%;
              transform: translateX(-50%);
              text-align: center;
              font-size: 12px;
              color: rgba(0, 0, 0, 0.6);
              z-index: 9999;
              line-height: 1.6;
          }
          
          .theme-copyright a {
              color: #1a5fb4;
              text-decoration: none;
              transition: color 0.3s ease;
          }
          
          .theme-copyright a:hover {
              color: #154360;
          }
      </style>
      
      <div class="theme-copyright">
          <p>主题名称🎉<?php $theme_data = wp_get_theme(); echo $theme_data->get('Name'); ?></p>
          <p>主题版本🛰️<?php $theme_data = wp_get_theme(); echo $theme_data->get('Version'); ?></p>
          <p>本页面由🗼 <a href="https://gl.baimu.live/864" target="_blank">白木</a> 重构</p>
      </div>
   </main>
   
   <!-- 🌟 背景图预加载 -->
   <img id="bg-preloader" src="<?php boxmoe_banner_image(); ?>" alt="Preload Background" />
   
   <?php 
    ob_start();
    wp_footer();
    $wp_footer_output = ob_get_clean();
    echo preg_replace('/\n/', "\n    ", trim($wp_footer_output))."\n    ";
    ?>
    <script>
      // 直接定义ajax_object，避免依赖主题脚本加载
      var ajax_object = {
        ajaxurl: '<?php echo admin_url("admin-ajax.php"); ?>',
        themeurl: '<?php echo boxmoe_theme_url(); ?>'
      };

      // 🌟 背景图预加载功能
      document.addEventListener('DOMContentLoaded', function() {
          // 获取预加载图片元素
          const preloaderImg = document.getElementById('bg-preloader');
          const body = document.body;
          
          // 设置预加载图片的onload事件
          preloaderImg.onload = function() {
              // 图片加载完成后，将图片应用到body背景
              body.style.backgroundImage = `url('${this.src}')`;
              // 触发动画效果
              body.style.opacity = '1';
          };
          
          // 🔗 双面板切换功能
          const sign_in_btn = document.getElementById('sign-in-btn');
          const sign_up_btn = document.getElementById('sign-up-btn');
          const container = document.querySelector('.container');

          sign_up_btn.addEventListener('click', () => {
              container.classList.add('sign-up-mode');
          });

          sign_in_btn.addEventListener('click', () => {
              container.classList.remove('sign-up-mode');
          });
          
          // 🔗 根据URL参数自动切换登录/注册模块
          const urlParams = new URLSearchParams(window.location.search);
          const mode = urlParams.get('mode');
          
          if (mode === 'signup') {
              container.classList.add('sign-up-mode');
          } else {
              container.classList.remove('sign-up-mode');
          }
          
          // 🔗 显示/隐藏密码功能 - 优化实现
          const togglePasswordBtns = document.querySelectorAll('.toggle-password');
          
          // 确保按钮元素存在
          if (togglePasswordBtns.length > 0) {
              togglePasswordBtns.forEach(btn => {
                  // 使用原生事件监听器，确保事件绑定成功
                  btn.addEventListener('click', function(e) {
                      e.preventDefault();
                      
                      const targetId = this.getAttribute('data-target');
                      const passwordInput = document.getElementById(targetId);
                      const icon = this.querySelector('i');
                      
                      // 确保目标元素存在
                      if (passwordInput && icon) {
                          // 切换密码显示状态
                          if (passwordInput.type === 'password') {
                              passwordInput.type = 'text';
                              icon.classList.remove('fa-eye-slash');
                              icon.classList.add('fa-eye');
                          } else {
                              passwordInput.type = 'password';
                              icon.classList.remove('fa-eye');
                              icon.classList.add('fa-eye-slash');
                          }
                      }
                  });
              });
          }

          // 获取验证码设置
          const captchaSettings = JSON.parse(document.querySelector('meta[name="captcha-settings"]').content);
          const captchaEnabled = captchaSettings.enabled;
          const captchaLoginEnabled = captchaSettings.loginEnabled;
          const captchaRegisterEnabled = captchaSettings.registerEnabled;
          const captchaType = captchaSettings.type;
          const cloudflareSiteKey = captchaSettings.cloudflareSiteKey;
          
          // 全局变量
          var isSubmitting = false;
          var loginCfToken = '';
          var registerCfToken = '';
          
          // 刷新验证码函数
          function refreshCaptcha(type) {
              var imgElement = document.getElementById(type + '-captcha-image');
              var captchaInput = document.getElementById(type + '-captcha-input');
              var messageElement = document.getElementById(type + '-captcha-message');
              
              if (!imgElement) return;
              
              // 清空验证码输入框
              if (captchaInput) {
                  captchaInput.value = '';
                  captchaInput.classList.remove('error', 'success');
              }
              
              // 清空错误消息
              if (messageElement) {
                  messageElement.innerHTML = '';
                  messageElement.className = 'captcha-message';
              }
              
              // 构建新的URL
              var newUrl = ajax_object.ajaxurl + '?action=generate_captcha_image&t=' + new Date().getTime() + '&r=' + Math.random().toString(36).substring(7);
              
              // 添加加载效果
              imgElement.style.opacity = '0.5';
              
              // 预加载图片
              var tempImg = new Image();
              tempImg.onload = function() {
                  imgElement.src = newUrl;
                  imgElement.style.opacity = '1';
                  
                  // 成功动画
                  var container = imgElement.closest('.captcha-image-container');
                  if (container) {
                      container.classList.add('captcha-success');
                      setTimeout(function() {
                          container.classList.remove('captcha-success');
                      }, 600);
                  }
                  
                  // 聚焦到输入框
                  if (captchaInput) {
                      captchaInput.focus();
                  }
              };
              tempImg.onerror = function() {
                  imgElement.style.opacity = '1';
                  if (messageElement) {
                      messageElement.innerHTML = '<span class="error-message">验证码加载失败，请重试</span>';
                  }
              };
              tempImg.src = newUrl;
          }
          
          // 显示消息
          function showMessage(elementId, message, isError = true) {
              var element = document.getElementById(elementId);
              if (element) {
                  var alertClass = isError ? 'alert-danger' : 'alert-success';
                  element.innerHTML = '<div class="alert ' + alertClass + ' mt-3">' + message + '</div>';
                  
                  // 5秒后自动消失
                  setTimeout(function() {
                      if (element.firstChild) {
                          element.firstChild.classList.remove('show');
                          setTimeout(function() {
                              element.innerHTML = '';
                          }, 300);
                      }
                  }, 5000);
              }
          }
          
          // 显示验证码消息
          function showCaptchaMessage(type, message, isError = true) {
              var element = document.getElementById(type + '-captcha-message');
              if (element) {
                  element.innerHTML = '<span class="' + (isError ? 'error-message' : 'success-message') + '">' + message + '</span>';
                  element.className = 'captcha-message ' + (isError ? '' : 'success');
              }
          }
          
          // 验证普通验证码
          function validateNormalCaptcha(captchaCode, type) {
              return new Promise((resolve, reject) => {
                  if (!captchaCode || captchaCode.trim() === '') {
                      reject(new Error('请输入验证码'));
                      return;
                  }
                  
                  fetch(ajax_object.ajaxurl, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/x-www-form-urlencoded',
                      },
                      body: new URLSearchParams({
                          action: 'verify_captcha',
                          captcha_code: captchaCode,
                          nonce: '<?php echo wp_create_nonce('captcha_verify'); ?>'
                      })
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          resolve(data);
                      } else {
                          reject(new Error(data.message || '验证码错误'));
                      }
                  })
                  .catch(error => {
                      reject(new Error('验证码验证失败，请重试'));
                  });
              });
          }
          
          // 验证Cloudflare验证码
          function validateCloudflareCaptcha(token) {
              return new Promise((resolve, reject) => {
                  if (!token) {
                      reject(new Error('请先完成人机验证'));
                      return;
                  }
                  
                  fetch(ajax_object.ajaxurl, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/x-www-form-urlencoded',
                      },
                      body: new URLSearchParams({
                          action: 'verify_cloudflare_captcha',
                          cf_response: token
                      })
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          resolve(data);
                      } else {
                          reject(new Error(data.message || '人机验证失败'));
                      }
                  })
                  .catch(error => {
                      reject(new Error('人机验证失败，请重试'));
                  });
              });
          }
          
          // 获取表单验证状态
          function getFormValidationState(form) {
              var isValid = true;
              var inputs = form.querySelectorAll('input[required]');
              
              inputs.forEach(function(input) {
                  if (!input.value.trim()) {
                      isValid = false;
                      input.classList.add('error');
                  } else {
                      input.classList.remove('error');
                  }
              });
              
              return isValid;
          }
          
          // 设置按钮加载状态
          function setButtonLoading(button, isLoading) {
              var btnText = button.querySelector('.btn-text');
              if (btnText) {
                  if (isLoading) {
                      button.classList.add('btn-loading');
                      btnText.textContent = '验证中...';
                  } else {
                      button.classList.remove('btn-loading');
                      if (button.id.includes('login')) {
                          btnText.textContent = 'Go';
                      } else {
                          btnText.textContent = 'Go';
                      }
                  }
              }
              button.disabled = isLoading;
          }
          
          // 初始化Cloudflare Turnstile验证码
          <?php if ($captcha_enabled && $captcha_type === 'cloudflare' && !empty($cloudflare_site_key)): ?>
          function initTurnstileCaptcha() {
              if (typeof turnstile === 'undefined') {
                  console.error('Cloudflare Turnstile未加载');
                  return;
              }
              
              <?php if ($captcha_login_enabled): ?>
              // 初始化登录验证码
              turnstile.render('#login-captcha-widget', {
                  sitekey: '<?php echo esc_js($cloudflare_site_key); ?>',
                  callback: function(token) {
                      loginCfToken = token;
                      document.getElementById('login-cf-response').value = token;
                      showCaptchaMessage('login-captcha', '验证通过', false);
                  },
                  'expired-callback': function() {
                      loginCfToken = '';
                      document.getElementById('login-cf-response').value = '';
                      showCaptchaMessage('login-captcha', '验证已过期，请重新验证', true);
                  },
                  'error-callback': function() {
                      loginCfToken = '';
                      document.getElementById('login-cf-response').value = '';
                      showCaptchaMessage('login-captcha', '验证失败，请重试', true);
                  },
                  theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'
              });
              <?php endif; ?>
              
              <?php if ($captcha_register_enabled): ?>
              // 初始化注册验证码
              turnstile.render('#register-captcha-widget', {
                  sitekey: '<?php echo esc_js($cloudflare_site_key); ?>',
                  callback: function(token) {
                      registerCfToken = token;
                      document.getElementById('register-cf-response').value = token;
                      showCaptchaMessage('register-captcha', '验证通过', false);
                  },
                  'expired-callback': function() {
                      registerCfToken = '';
                      document.getElementById('register-cf-response').value = '';
                      showCaptchaMessage('register-captcha', '验证已过期，请重新验证', true);
                  },
                  'error-callback': function() {
                      registerCfToken = '';
                      document.getElementById('register-cf-response').value = '';
                      showCaptchaMessage('register-captcha', '验证失败，请重试', true);
                  },
                  theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'
              });
              <?php endif; ?>
          }
          
          // 等待Turnstile脚本加载完成
          if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', function() {
                  if (typeof turnstile !== 'undefined') {
                      initTurnstileCaptcha();
                  } else {
                      // 等待Turnstile脚本加载
                      var checkTurnstile = setInterval(function() {
                          if (typeof turnstile !== 'undefined') {
                              clearInterval(checkTurnstile);
                              initTurnstileCaptcha();
                          }
                      }, 100);
                      
                      setTimeout(function() {
                          clearInterval(checkTurnstile);
                      }, 10000);
                  }
              });
          } else {
              if (typeof turnstile !== 'undefined') {
                  initTurnstileCaptcha();
              } else {
                  // 等待Turnstile脚本加载
                  var checkTurnstile = setInterval(function() {
                      if (typeof turnstile !== 'undefined') {
                          clearInterval(checkTurnstile);
                          initTurnstileCaptcha();
                      }
                  }, 100);
                  
                  setTimeout(function() {
                      clearInterval(checkTurnstile);
                  }, 10000);
              }
          }
          <?php endif; ?>

          // 🔗 登录表单提交事件监听
          document.getElementById('loginform').addEventListener('submit', async function(e) {
              e.preventDefault();
              
              if (isSubmitting) {
                  return;
              }
              
              isSubmitting = true;
              
              const loginButton = document.getElementById('login-submit-btn');
              const form = this;
              
              // 设置按钮加载状态
              setButtonLoading(loginButton, true);
              
              try {
                  // 验证表单
                  if (!getFormValidationState(form)) {
                      throw new Error('请填写所有必填字段');
                  }
                  
                  // 获取验证码配置
                  var captchaEnabled = <?php echo $captcha_enabled ? 'true' : 'false'; ?>;
                  var captchaLoginEnabled = <?php echo $captcha_login_enabled ? 'true' : 'false'; ?>;
                  var captchaType = '<?php echo esc_js($captcha_type); ?>';
                  
                  // 第一步：验证验证码（如果启用）
                  if (captchaEnabled && captchaLoginEnabled) {
                      console.log('开始验证登录验证码');
                      
                      if (captchaType === 'cloudflare') {
                          // Cloudflare验证码
                          if (!loginCfToken) {
                              throw new Error('请先完成人机验证');
                          }
                          const cfResult = await validateCloudflareCaptcha(loginCfToken);
                          console.log('Cloudflare验证结果:', cfResult);
                      } else {
                          // 普通验证码
                          const captchaInput = document.getElementById('login-captcha-input');
                          const captchaCode = captchaInput ? captchaInput.value : '';
                          const captchaResult = await validateNormalCaptcha(captchaCode, 'login');
                          
                          if (!captchaResult.success) {
                              // 标记输入框错误
                              if (captchaInput) {
                                  captchaInput.classList.add('error');
                                  captchaInput.classList.remove('success');
                              }
                              throw new Error(captchaResult.message || '验证码错误');
                          } else {
                              // 标记输入框成功
                              if (captchaInput) {
                                  captchaInput.classList.remove('error');
                                  captchaInput.classList.add('success');
                              }
                          }
                      }
                      console.log('验证码验证通过');
                  }
                  
                  // 第二步：收集表单数据并提交
                  const urlParams = new URLSearchParams(window.location.search);
                  const redirect_to = urlParams.get('redirect_to');
                  
                  const formData = {
                      username: document.getElementById('username').value,
                      password: document.getElementById('password').value,
                      rememberme: document.getElementById('rememberme').checked,
                      login_nonce: document.querySelector('input[name="login_nonce"]').value,
                      redirect_to: redirect_to
                  };
                  
                  // 添加验证码数据
                  if (captchaEnabled && captchaLoginEnabled) {
                      if (captchaType === 'cloudflare') {
                          formData.cf_response = loginCfToken;
                      } else {
                          formData.captcha_code = document.getElementById('login-captcha-input')?.value;
                      }
                  }
                  
                  console.log('提交登录数据:', formData);
                  
                  // 提交表单
                  const formDataToSend = new FormData();
                  formDataToSend.append('action', 'user_login_action');
                  formDataToSend.append('formData', JSON.stringify(formData));
                  
                  const response = await fetch(ajax_object.ajaxurl, {
                      method: 'POST',
                      credentials: 'same-origin',
                      body: formDataToSend
                  });
                  
                  const responseData = await response.json();
                  console.log('登录响应数据:', responseData);
                  
                  if(responseData.success) {
                      showMessage('login-message', responseData.data.message + '，正在跳转...', false);
                      setTimeout(() => {
                          if (responseData.data.redirect_url) {
                              window.location.href = responseData.data.redirect_url;
                          } else if (redirect_to) {
                              window.location.href = redirect_to;
                          } else {
                               window.location.href = '/';
                          }
                      }, 1000);
                  } else {
                      showMessage('login-message', responseData.data.message || '登录失败', true);
                      
                      // 刷新验证码
                      if (captchaEnabled && captchaLoginEnabled && captchaType !== 'cloudflare') {
                          refreshCaptcha('login');
                      }
                  }
                  
              } catch (error) {
                  console.error('登录过程错误:', error);
                  
                  // 显示错误信息
                  let errorMessage = error.message || '登录失败，请重试';
                  showMessage('login-message', errorMessage, true);
                  
                  // 显示验证码错误
                  if (errorMessage.includes('验证码') || errorMessage.includes('验证') || errorMessage.includes('人机')) {
                      showCaptchaMessage('login-captcha', errorMessage, true);
                      
                      // 刷新验证码
                      if (captchaEnabled && captchaLoginEnabled && captchaType !== 'cloudflare') {
                          refreshCaptcha('login');
                      }
                  }
              } finally {
                  // 恢复按钮状态
                  setButtonLoading(loginButton, false);
                  isSubmitting = false;
              }
          });

          // 🔗 注册表单提交
          document.getElementById('signupform').addEventListener('submit', async function(e) {
              e.preventDefault();
              
              if (isSubmitting) {
                  return;
              }
              
              isSubmitting = true;
              
              var btn = document.getElementById('signup-submit-btn');
              var form = this;
              
              // 设置按钮加载状态
              setButtonLoading(btn, true);
              
              try {
                  // 验证表单
                  if (!getFormValidationState(form)) {
                      throw new Error('请填写所有必填字段');
                  }
                  
                  // 验证密码一致性
                  const password = document.getElementById('formSignUpPassword').value;
                  const confirmPassword = document.getElementById('formSignUpConfirmPassword').value;
                  
                  if (password !== confirmPassword) {
                      throw new Error('两次输入的密码不一致');
                  }
                  
                  // 验证密码长度
                  if (password.length < 6) {
                      throw new Error('密码长度至少需要6个字符');
                  }
                  
                  // 检查协议是否同意
                  const agreeCheckbox = form.querySelector('input[name="agree"]');
                  if (!agreeCheckbox.checked) {
                      throw new Error('请阅读并同意用户协议和隐私政策');
                  }
                  
                  // 获取验证码配置
                  var captchaEnabled = <?php echo $captcha_enabled ? 'true' : 'false'; ?>;
                  var captchaRegisterEnabled = <?php echo $captcha_register_enabled ? 'true' : 'false'; ?>;
                  var captchaType = '<?php echo esc_js($captcha_type); ?>';
                  
                  // 第一步：验证验证码（如果启用）
                  if (captchaEnabled && captchaRegisterEnabled) {
                      console.log('开始验证注册验证码');
                      
                      if (captchaType === 'cloudflare') {
                          // Cloudflare验证码
                          if (!registerCfToken) {
                              throw new Error('请先完成人机验证');
                          }
                          const cfResult = await validateCloudflareCaptcha(registerCfToken);
                          console.log('Cloudflare验证结果:', cfResult);
                      } else {
                          // 普通验证码
                          const captchaInput = document.getElementById('register-captcha-input');
                          const captchaCode = captchaInput ? captchaInput.value : '';
                          const captchaResult = await validateNormalCaptcha(captchaCode, 'register');
                          
                          if (!captchaResult.success) {
                              // 标记输入框错误
                              if (captchaInput) {
                                  captchaInput.classList.add('error');
                                  captchaInput.classList.remove('success');
                              }
                              throw new Error(captchaResult.message || '验证码错误');
                          } else {
                              // 标记输入框成功
                              if (captchaInput) {
                                  captchaInput.classList.remove('error');
                                  captchaInput.classList.add('success');
                              }
                          }
                      }
                  }
                  
                  // 第二步：提交注册表单
                  var formData = {
                      username: document.getElementById('signupFullnameInput').value,
                      email: document.getElementById('signupEmailInput').value,
                      verificationcode: document.getElementById('signupVerificationCode').value,
                      password: password,
                      confirmpassword: confirmPassword,
                      agree: agreeCheckbox.checked,
                      signup_nonce: document.querySelector('input[name="signup_nonce"]').value
                  };
                  
                  // 添加验证码数据
                  if (captchaEnabled && captchaRegisterEnabled) {
                      if (captchaType === 'cloudflare') {
                          formData.cf_response = registerCfToken;
                      } else {
                          formData.captcha_code = document.getElementById('register-captcha-input')?.value;
                      }
                  }
                  
                  console.log('提交注册数据:', formData);
                  
                  const response = await fetch(ajax_object.ajaxurl, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/x-www-form-urlencoded'
                      },
                      body: 'action=user_signup_action&formData=' + encodeURIComponent(JSON.stringify(formData))
                  });
                  
                  const data = await response.json();
                  console.log('注册响应数据:', data);
                  
                  if(data.success) {
                      showMessage('signup-message', data.data.message + '，正在跳转到会员中心...', false);
                      setTimeout(function(){
                          window.location.href = '<?php echo boxmoe_user_center_link_page(); ?>';
                      }, 2000);
                  } else {
                      showMessage('signup-message', data.data.message || '注册失败', true);
                      
                      // 刷新验证码
                      if (captchaEnabled && captchaRegisterEnabled && captchaType !== 'cloudflare') {
                          refreshCaptcha('register');
                      }
                  }
                  
              } catch (error) {
                  console.error('注册过程错误:', error);
                  showMessage('signup-message', error.message || '注册失败，请重试', true);
                  
                  // 显示验证码错误
                  if (error.message.includes('验证码') || error.message.includes('验证') || error.message.includes('人机')) {
                      showCaptchaMessage('register-captcha', error.message, true);
                      
                      // 刷新验证码
                      if (captchaEnabled && captchaRegisterEnabled && captchaType !== 'cloudflare') {
                          refreshCaptcha('register');
                      }
                  }
              } finally {
                  // 恢复按钮状态
                  setButtonLoading(btn, false);
                  isSubmitting = false;
              }
          });
          
          // 发送验证码按钮
          document.getElementById('sendVerificationCode').addEventListener('click', async function() {
              var email = document.getElementById('signupEmailInput').value;
              var btn = this;
              
              if(!email) {
                  showMessage('signup-message', '请先填写邮箱地址', true);
                  return;
              }
              
              // 验证邮箱格式
              var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
              if (!emailRegex.test(email)) {
                  showMessage('signup-message', '请输入有效的邮箱地址', true);
                  return;
              }
              
              if (btn.disabled) return;
              
              btn.disabled = true;
              var originalText = btn.textContent;
              btn.textContent = '发送中...';
              
              try {
                  // 获取验证码配置
                  var captchaEnabled = <?php echo $captcha_enabled ? 'true' : 'false'; ?>;
                  var captchaRegisterEnabled = <?php echo $captcha_register_enabled ? 'true' : 'false'; ?>;
                  var captchaType = '<?php echo esc_js($captcha_type); ?>';
                  
                  // 验证验证码（如果启用）
                  if (captchaEnabled && captchaRegisterEnabled) {
                      console.log('发送验证码前验证验证码');
                      
                      if (captchaType === 'cloudflare') {
                          if (!registerCfToken) {
                              throw new Error('请先完成人机验证');
                          }
                          const cfResult = await validateCloudflareCaptcha(registerCfToken);
                          if (!cfResult.success) {
                              throw new Error(cfResult.message || '人机验证失败');
                          }
                      } else {
                          const captchaInput = document.getElementById('register-captcha-input');
                          const captchaCode = captchaInput ? captchaInput.value : '';
                          const captchaResult = await validateNormalCaptcha(captchaCode, 'register');
                          
                          if (!captchaResult.success) {
                              throw new Error(captchaResult.message || '验证码错误');
                          }
                      }
                  }
                  
                  // 构建请求数据
                  const formData = new FormData();
                  formData.append('action', 'send_verification_code');
                  formData.append('email', email);
                  
                  // 添加验证码数据
                  if (captchaEnabled && captchaRegisterEnabled) {
                      if (captchaType === 'cloudflare') {
                          formData.append('cf_response', registerCfToken);
                      } else {
                          const captchaCode = document.getElementById('register-captcha-input')?.value;
                          formData.append('captcha_code', captchaCode);
                      }
                  }
                  
                  const response = await fetch(ajax_object.ajaxurl, {
                      method: 'POST',
                      body: formData
                  });
                  
                  const data = await response.json();
                  
                  if(data.success) {
                      showMessage('signup-message', data.data?.message || '验证码已发送到您的邮箱', false);
                      var countdown = 60;
                      var timer = setInterval(function() {
                          btn.textContent = countdown + 's后重试';
                          countdown--;
                          if(countdown < 0) {
                              clearInterval(timer);
                              btn.disabled = false;
                              btn.textContent = originalText;
                          }
                      }, 1000);
                  } else {
                      showMessage('signup-message', data.data?.message || '发送失败，请重试', true);
                      btn.disabled = false;
                      btn.textContent = originalText;
                  }
              } catch (error) {
                  console.error('发送验证码失败:', error);
                  showMessage('signup-message', error.message || '发送失败，请重试', true);
                  btn.disabled = false;
                  btn.textContent = originalText;
              }
          });
          
          // 输入框实时验证
          function setupRealTimeValidation() {
              // 登录表单验证
              const loginInputs = document.querySelectorAll('#loginform input[required]');
              loginInputs.forEach(input => {
                  input.addEventListener('input', function() {
                      if (this.value.trim()) {
                          this.classList.remove('error');
                      }
                  });
              });
              
              // 注册表单验证
              const registerInputs = document.querySelectorAll('#signupform input[required]');
              registerInputs.forEach(input => {
                  input.addEventListener('input', function() {
                      if (this.value.trim()) {
                          this.classList.remove('error');
                      }
                      
                      // 特殊处理密码确认
                      if (this.id === 'formSignUpConfirmPassword') {
                          const password = document.getElementById('formSignUpPassword').value;
                          if (this.value && password !== this.value) {
                              this.classList.add('error');
                              showMessage('signup-message', '两次输入的密码不一致', true);
                          } else if (this.value && password === this.value) {
                              this.classList.remove('error');
                              const messageDiv = document.getElementById('signup-message');
                              if (messageDiv && messageDiv.textContent.includes('密码不一致')) {
                                  messageDiv.innerHTML = '';
                              }
                          }
                      }
                  });
              });
              
              // 验证码输入框验证
              const captchaInputs = document.querySelectorAll('.captcha-input');
              captchaInputs.forEach(input => {
                  input.addEventListener('input', function() {
                      const value = this.value.trim();
                      if (value.length >= 4 && value.length <= 6) {
                          this.classList.remove('error');
                      }
                  });
              });
          }
          
          setupRealTimeValidation();
          
          // 📱 移动端480x690尺寸专用功能
          function initMobileFeatures() {
              // 检测是否为480x690尺寸
              const isMobile480x690 = window.innerWidth === 480 && window.innerHeight === 690;
              
              if (isMobile480x690) {
                  console.log('🌟 移动端480x690模式激活');
                  
                  // 🌟 增强左右切换动画
                  const signInBtn = document.getElementById('sign-in-btn');
                  const signUpBtn = document.getElementById('sign-up-btn');
                  const container = document.querySelector('.container');
                  
                  if (signInBtn && signUpBtn && container) {
                      // 移除原有的点击事件监听器，防止重复绑定
                      signInBtn.removeEventListener('click', mobileSignInHandler);
                      signUpBtn.removeEventListener('click', mobileSignUpHandler);
                      
                      // 绑定移动端专用事件处理器
                      signInBtn.addEventListener('click', mobileSignInHandler);
                      signUpBtn.addEventListener('click', mobileSignUpHandler);
                  }
                  
                  // 🌟 版权信息位置优化
                  optimizeCopyrightPosition();
              }
          }
          
          // 🌟 移动端登录按钮处理器
          function mobileSignInHandler() {
              const container = document.querySelector('.container');
              if (container) {
                  // 🌟 添加球体动画效果
                  container.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                  container.classList.remove('sign-up-mode');
                  
                  // 🌟 球体脉冲动画
                  const ball = container.querySelector('::before');
                  if (ball) {
                      container.style.setProperty('--ball-scale', '1.05');
                      setTimeout(() => {
                          container.style.setProperty('--ball-scale', '1');
                      }, 400);
                  }
                  
                  // 添加切换动画完成后的回调
                  setTimeout(() => {
                      console.log('📱 切换到登录模式');
                  }, 800);
              }
          }
          
          // 🌟 移动端注册按钮处理器
          function mobileSignUpHandler() {
              const container = document.querySelector('.container');
              if (container) {
                  // 🌟 添加球体动画效果
                  container.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                  container.classList.add('sign-up-mode');
                  
                  // 🌟 球体脉冲动画
                  const ball = container.querySelector('::before');
                  if (ball) {
                      container.style.setProperty('--ball-scale', '1.05');
                      setTimeout(() => {
                          container.style.setProperty('--ball-scale', '1');
                      }, 400);
                  }
                  
                  // 添加切换动画完成后的回调
                  setTimeout(() => {
                      console.log('📱 切换到注册模式');
                  }, 800);
              }
          }
          
          // 🌟 版权信息位置优化
          function optimizeCopyrightPosition() {
              const copyright = document.querySelector('.theme-copyright');
              if (copyright) {
                  // 确保版权信息始终位于底部
                  copyright.style.position = 'fixed';
                  copyright.style.bottom = '10px';
                  copyright.style.left = '50%';
                  copyright.style.transform = 'translateX(-50%)';
                  copyright.style.zIndex = '9999';
                  
                  // 监听窗口大小变化
                  window.addEventListener('resize', () => {
                      if (window.innerWidth === 480 && window.innerHeight === 690) {
                          copyright.style.display = 'block';
                      }
                  });
                  
                  console.log('📱 版权信息位置已优化');
              }
          }
          
          // 🌟 监听窗口大小变化
          let resizeTimer;
          window.addEventListener('resize', () => {
              clearTimeout(resizeTimer);
              resizeTimer = setTimeout(() => {
                  initMobileFeatures();
              }, 250);
          });
          
          // 🌟 页面加载完成后初始化移动端功能
          setTimeout(initMobileFeatures, 100);
          
          console.log('页面初始化完成');
      });
    </script>
    <!-- 📱 移动端触摸滑动脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/mobile-touch-scroll-shiroki.js"></script>
    <!-- 🌌 引入粒子效果脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login-particles.js"></script>
</body>
</html>
