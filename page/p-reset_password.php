<?php
/**
 * Template Name: 重置密码
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
//如果用户已经登陆那么跳转到首页或用户中心
if (is_user_logged_in()){
    // 避免重定向循环：检查当前是否已经在首页或用户中心页面
    $home_url = get_option('home');
    $home_path = parse_url($home_url, PHP_URL_PATH);
    if (empty($home_path)) {
        $home_path = '/';
    }
    
    $current_uri = $_SERVER['REQUEST_URI'];
    $user_center_url = boxmoe_user_center_link_page();
    $user_center_path = parse_url($user_center_url, PHP_URL_PATH);
    
    if ($current_uri == $home_path || $current_uri == $home_path . '/' || $current_uri == $user_center_path || $current_uri == $user_center_path . '/') {
        // 如果已经在首页或用户中心页面，直接退出，避免循环
        exit;
    }
    
    wp_safe_redirect( $user_center_url );
    exit;
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
    <style>
        /* 🥳 样式统一管理 - 与登录页面一致 */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: oklch(0.9605 0.0046 258.32);
        }
        .login-page-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url(<?php echo get_boxmoe('boxmoe_user_login_bg')? get_boxmoe('boxmoe_user_login_bg') :'https://api.boxmoe.com/random.php'; ?>);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }
        .login-page-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: oklch(0 0 0 / 0.2); /* ⬅️ 背景遮罩，提升文字可读性 */
            backdrop-filter: blur(8px); /* ⬅️ 全局背景模糊 */
            -webkit-backdrop-filter: blur(8px);
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative; /* ⬅️ 确保在粒子层之上 */
            z-index: 1;
        }
        /* ✨ 玻璃拟态卡片 */
        .glass-card {
            background: radial-gradient(circle at top left in srgb, oklch(0.8677 0.0735 7.09 / 0.75), oklch(0.8562 0.0489 219.65 / 0.75)); /* ⬅️ 浅粉色到浅蓝色圆形扩散渐变 */
            backdrop-filter: blur(20px); /* ⬅️ 局部高斯模糊 */
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; /* ⬅️ 圆角风格 */
            border: 1px solid oklch(1 0 0 / 0.6);
            box-shadow: 0 8px 32px 0 oklch(0.3382 0.1582 271.28 / 0.15);
            width: 100%;
            max-width: 460px;
            padding: 3rem 2.5rem;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 oklch(0.3382 0.1582 271.28 / 0.2);
        }
        /* 🌙 暗色模式适配 */
        [data-bs-theme="dark"] .glass-card {
            background: oklch(0.2372 0.0095 285.65 / 0.75);
            border: 1px solid oklch(1 0 0 / 0.08);
            box-shadow: 0 8px 32px 0 oklch(0 0 0 / 0.3);
            color: oklch(0.9067 0 0);
        }
        [data-bs-theme="dark"] .text-body-tertiary {
            color: oklch(0.7692 0.0145 248.02) !important;
        }
        
        /* 🏷️ 浮动标签与动态文本 */
        .floating-label-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .floating-label-group .form-control {
            height: 3.5rem;
            padding: 1.25rem 1rem 0.75rem;
            background: oklch(1 0 0 / 0.6);
            border: 1px solid oklch(1 0 0 / 0.3); /* ⬅️ 增加边框线，配合浮动标签 */
            border-radius: 12px;
            box-shadow: inset 0 2px 4px oklch(0 0 0 / 0.02);
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .floating-label-group .form-control {
            background: oklch(0 0 0 / 0.2);
            border-color: oklch(1 0 0 / 0.1);
            color: oklch(1 0 0);
        }
        .floating-label-group .form-control:focus {
            background: oklch(1 0 0 / 0.95);
            box-shadow: 0 0 0 3px oklch(var(--bs-primary-oklch) / 0.2);
            border-color: var(--bs-primary);
            transform: translateY(-1px);
        }
        [data-bs-theme="dark"] .floating-label-group .form-control:focus {
            background: oklch(0 0 0 / 0.4);
            border-color: var(--bs-primary);
        }
        .floating-label-group label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: 0.2s ease all;
            color: oklch(0.5575 0.0165 244.89);
            padding: 0 5px;
            z-index: 5;
            margin: 0;
            width: auto;
            height: auto;
            font-size: 1rem;
            border-radius: 4px;
        }
        .floating-label-group label::after {
            content: attr(data-default);
            transition: all 0.2s ease;
        }
        /* 激活状态 */
        .floating-label-group .form-control:focus ~ label,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label {
            top: 0; /* ⬅️ 移动到顶部边框线上 */
            left: 0.8rem;
            font-size: 0.75rem;
            transform: translateY(-50%); /* ⬅️ 垂直居中于边框 */
            color: var(--bs-primary);
            background: oklch(1 0 0 / 0.8); /* ⬅️ 添加背景遮挡边框线，保持玻璃感 */
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px oklch(0 0 0 / 0.05);
        }
        [data-bs-theme="dark"] .floating-label-group .form-control:focus ~ label,
        [data-bs-theme="dark"] .floating-label-group .form-control:not(:placeholder-shown) ~ label {
            background: oklch(0.2991 0.0089 285.85 / 0.8);
            color: var(--bs-primary);
        }
        .floating-label-group .form-control:focus ~ label::after,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label::after {
            content: attr(data-active);
        }

        .btn-primary {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            box-shadow: 0 4px 6px oklch(var(--bs-primary-oklch) / 0.3);
            transition: all 0.3s ease;
            position: relative; /* ⬅️ 为扫光动画定位 */
            overflow: hidden;   /* ⬅️ 隐藏溢出的扫光 */
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px oklch(var(--bs-primary-oklch) / 0.4);
        }
        /* ✨ 按钮扫光动画 */
        .btn-primary::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg in srgb,
                oklch(0 0 0 / 0),
                oklch(1 0 0 / 0.6),
                oklch(0 0 0 / 0)
            );
            transition: all 0.6s;
        }
        .btn-primary:hover::after {
            left: 100%;
        }
        /* 💕 底部工具栏 */
        .theme-toggle-fixed {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
        }
    </style>
</head>

<body>
   <main>
      <!-- 🖼️ 全屏背景容器 -->
      <div class="login-page-bg"></div>

      <div class="login-container">
         <div class="glass-card">
            <!-- Logo区域 -->
            <div class="text-center mb-4">
               <a href="<?php echo get_option('home'); ?>" class="d-inline-block transition-hover">
                   <?php boxmoe_logo(); ?>
               </a>
               <h3 class="mt-3 mb-1 fw-bold">重置密码</h3>
               <p class="text-muted small mb-0">
                  请输入您的邮箱，我们将发送重置密码的链接
               </p>
            </div>

            <!-- 重置密码表单 -->
            <form class="needs-validation mb-3" action="" method="post" id="resetPasswordForm" novalidate>
               <?php wp_nonce_field('reset_password_action', 'reset_password_nonce'); ?>
               <div class="mb-4 floating-label-group">
                  <input type="email" name="user_email" class="form-control" id="resetEmailInput" required placeholder=" " />
                  <label for="resetEmailInput" data-default="请输入您的邮箱" data-active="邮箱"></label>
                  <div class="invalid-feedback">请输入有效的邮箱地址。</div>
               </div>
               <div id="signup-message"></div>
               <div class="d-grid">
                  <button class="btn btn-primary" type="submit" id="resetSubmitBtn">
                     <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                     <span class="btn-text">发送重置链接</span>
                  </button>
               </div>
            </form>

            <!-- 返回登录链接 -->
            <div class="text-center mt-3">
               <a href="<?php echo esc_url(boxmoe_auth_entry_url(boxmoe_sign_in_link_page())); ?>" class="text-primary fw-bold text-decoration-none">
                  <i class="bi bi-arrow-left me-1"></i> 返回登录
               </a>
            </div>

            <!-- 底部版权 -->
            <div class="text-center mt-4 pt-3 border-top border-light">
               <div class="small text-body-tertiary">
                  Copyright © <?php echo date('Y'); ?> 
                  <span class="text-primary"><a href="<?php echo get_option('home'); ?>" class="text-reset text-decoration-none fw-bold"><?php echo get_bloginfo('name'); ?></a></span>
                  <br> Theme by
                  <span class="text-primary"><a href="https://www.boxmoe.com" class="text-reset text-decoration-none fw-bold">Boxmoe</a></span> powered by WordPress
               </div>
            </div>
         </div>
      </div>

      <!-- 🛠️ 主题切换按钮 -->
      <div class="position-absolute start-0 bottom-0 m-4">
         <div class="dropdown">
            <button
                    class="float-btn bd-theme btn btn-light btn-icon rounded-circle d-flex align-items-center shadow-sm"
                    type="button"
                    aria-expanded="false"
                    data-bs-toggle="dropdown"
                    aria-label="Toggle theme (auto)">
                    <i class="fa fa-adjust"></i>
                    <span class="visually-hidden bs-theme-text">主题颜色切换</span>
            </button>
            <ul class="bs-theme dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g stroke="currentColor" stroke-linecap="round" stroke-width="2" data-swindex="0"><path fill="currentColor" fill-opacity="0" stroke-dasharray="34" stroke-dashoffset="34" d="M12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.4s" values="34;0"/><animate fill="freeze" attributeName="fill-opacity" begin="0.9s" dur="0.5s" values="0;1"/></path><g fill="none" stroke-dasharray="2" stroke-dashoffset="2"><path d="M0 0"><animate fill="freeze" attributeName="d" begin="0.5s" dur="0.2s" values="M12 19v1M19 12h1M12 5v-1M5 12h-1;M12 21v1M21 12h1M12 3v-1M3 12h-1"/><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="2;0"/></path><path d="M0 0"><animate fill="freeze" attributeName="d" begin="0.7s" dur="0.2s" values="M17 17l0.5 0.5M17 7l0.5 -0.5M7 7l-0.5 -0.5M7 17l-0.5 0.5;M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"/><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" values="2;0"/></path><animateTransform attributeName="transform" dur="30s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></g></g></svg>
                        <span class="ms-2">亮色</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" data-swindex="0"><g stroke-dasharray="2"><path d="M12 21v1M21 12h1M12 3v-1M3 12h-1"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="4;2"/></path><path d="M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="4;2"/></path></g><path fill="currentColor" d="M7 6 C7 12.08 11.92 17 18 17 C18.53 17 19.05 16.96 19.56 16.89 C17.95 19.36 15.17 21 12 21 C7.03 21 3 16.97 3 12 C3 8.83 4.64 6.05 7.11 4.44 C7.04 4.95 7 5.47 7 6 Z" opacity="0"><set attributeName="opacity" begin="0.5s" to="1"/></path></g><g fill="currentColor" fill-opacity="0"><path d="m15.22 6.03l2.53-1.94L14.56 4L13.5 1l-1.06 3l-3.19.09l2.53 1.94l-.91 3.06l2.63-1.81l2.63 1.81z"><animate id="lineMdSunnyFilledLoopToMoonFilledLoopTransition0" fill="freeze" attributeName="fill-opacity" begin="0.6s;lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+6s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.2s" dur="0.4s" values="1;0"/></path><path d="M13.61 5.25L15.25 4l-2.06-.05L12.5 2l-.69 1.95L9.75 4l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+3s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+5.2s" dur="0.4s" values="1;0"/></path><path d="M19.61 12.25L21.25 11l-2.06-.05L18.5 9l-.69 1.95l-2.06.05l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+0.4s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.8s" dur="0.4s" values="1;0"/></path><path d="m20.828 9.731l1.876-1.439l-2.366-.067L19.552 6l-.786 2.225l-2.366.067l1.876 1.439L17.601 12l1.951-1.342L21.503 12z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+3.4s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+5.6s" dur="0.4s" values="1;0"/></path></g><mask id="lineMdSunnyFilledLoopToMoonFilledLoopTransition1"><circle cx="12" cy="12" r="12" fill="oklch(1 0 0)"/><circle cx="22" cy="2" r="3" fill="oklch(1 0 0)"><animate fill="freeze" attributeName="cx" begin="0.1s" dur="0.4s" values="22;18"/><animate fill="freeze" attributeName="cy" begin="0.1s" dur="0.4s" values="2;6"/><animate fill="freeze" attributeName="r" begin="0.1s" dur="0.4s" values="3;12"/></circle><circle cx="22" cy="2" r="1"><animate fill="freeze" attributeName="cx" begin="0.1s" dur="0.4s" values="22;18"/><animate fill="freeze" attributeName="cy" begin="0.1s" dur="0.4s" values="2;6"/><animate fill="freeze" attributeName="r" begin="0.1s" dur="0.4s" values="1;10"/></circle></mask><circle cx="12" cy="12" r="6" fill="currentColor" mask="url(#lineMdSunnyFilledLoopToMoonFilledLoopTransition1)"><set attributeName="opacity" begin="0.5s" to="0"/><animate fill="freeze" attributeName="r" begin="0.1s" dur="0.4s" values="6;10"/></circle></svg>
                        <span class="ms-2">暗色</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                        <i class="fa fa-adjust"></i>
                        <span class="ms-2">跟随系统</span>
                    </button>
                </li>
            </ul>
         </div>
      </div>
   </main>
   <?php 
    ob_start();
    wp_footer();
    $wp_footer_output = ob_get_clean();
    echo preg_replace('/\n/', "\n    ", trim($wp_footer_output))."\n    ";
    ?>
    <script>
      // 🔗 重置密码表单提交事件监听
      document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const submitBtn = document.getElementById('resetSubmitBtn');
    const emailInput = document.getElementById('resetEmailInput');
    const messageDiv = document.getElementById('signup-message');
    const spinner = submitBtn.querySelector('.spinner-border');
    const btnText = submitBtn.querySelector('.btn-text');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        messageDiv.innerHTML = '';
        
        // 🔗 表单验证
        if (!emailInput.value) {
            emailInput.classList.add('is-invalid');
            return false;
        }
        
        // 🔗 禁用按钮并显示加载动画
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = '发送中...';
        
        fetch(ajax_object.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'reset_password_action',
                user_email: emailInput.value,
                nonce: document.getElementById('reset_password_nonce').value
            })
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                messageDiv.innerHTML = '<div class="alert alert-success mt-3">' + response.data.message + '</div>';
                form.reset();
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger mt-3">' + response.data.message + '</div>';
            }
            // 🔗 恢复按钮状态
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = '发送重置链接';
        })
        .catch(() => {
            messageDiv.innerHTML = '<div class="alert alert-danger mt-3">发送请求失败，请稍后重试</div>';
            // 🔗 恢复按钮状态
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = '发送重置链接';
        });
    });
});
    </script>
    <!-- 🌌 引入粒子效果脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login-particles.js"></script>
</body></html>

