<?php
/**
 * Template Name: 注册页面
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
// 🔗 重定向到登录页面的注册模块
$login_url = boxmoe_sign_in_link_page();
wp_safe_redirect(boxmoe_auth_entry_url(add_query_arg('mode', 'signup', $login_url)));
exit;
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
        /* 🥳 注册页样式 - 双面板设计 */
        :root {
            --primary: oklch(0.6786 0.1664 260.64);
            --primary-dark: oklch(0.6213 0.154 260.42);
            --bg: oklch(0.9551 0 0);
            --text: oklch(0.3867 0 0);
            --white: oklch(1 0 0);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body,
        input {
            font-family: "Poppins", sans-serif;
        }
        
        .container {
            position: relative;
            width: 100%;
            background-color: var(--white);
            min-height: 100vh;
            overflow: hidden;
        }
        
        .container::before {
            content: "";
            position: absolute;
            height: 2000px;
            width: 2000px;
            top: -10%;
            right: 48%;
            transform: translateY(-50%);
            background-image: linear-gradient(-45deg in srgb, oklch(0.5148 0.1884 260.62) 0%, oklch(0.7642 0.1443 228.04) 100%);
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
            transform: translate(-50%, -50%);
            left: 75%;
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
            color: oklch(0.3867 0 0);
            margin-bottom: 10px;
        }
        
        .input_field {
            max-width: 380px;
            width: 100%;
            background-color: oklch(0.9551 0 0);
            margin: 10px 0;
            height: 55px;
            border-radius: 55px;
            display: grid;
            grid-template-columns: 15% 85%;
            padding: 0 0.4rem;
            position: relative;
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
            color: oklch(0.3211 0 0);
            font-size: 16px;
        }
        
        .input_field input::placeholder {
            color: oklch(0.738 0 0);
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
            border: 1px solid oklch(1 0 0);
            -webkit-tab-highlight-color: oklch(0 0 0 / 0);
            color: oklch(1 0 0);
            background: oklch(1 0 0);
        }
        
        .shortMessage::before,
        .Password_login::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            background: oklch(1 0 0);
            width: 100%;
            height: 100%;
            border: 1px solid oklch(0.683 0 0);
            border-radius: 50%;
            color: oklch(1 0 0);
        }
        
        .shortMessage:checked::before,
        .Password_login:checked::before {
            content: "\2713";
            background-color: oklch(0.699 0.1179 240.42);
            border: 1px solid oklch(0.699 0.1179 240.42);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            color: oklch(1 0 0);
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
            color: oklch(0.699 0.1179 240.42);
            text-decoration: none;
        }
        
        .btn {
            width: 150px;
            background-color: oklch(0.6786 0.1664 260.64);
            border: none;
            outline: none;
            height: 49px;
            border-radius: 49px;
            color: oklch(1 0 0);
            text-transform: uppercase;
            font-weight: 600;
            margin: 10px 0;
            cursor: pointer;
            transition: 0.5s;
        }
        
        .btn:hover {
            background-color: oklch(0.6213 0.154 260.42);
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
            color: oklch(1 0 0);
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
            border: 2px solid oklch(1 0 0);
            width: 130px;
            height: 41px;
            font-weight: 600;
            font-size: 0.8rem;
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
            --primary: oklch(0.7084 0.1517 263.15);
            --primary-dark: oklch(0.6344 0.1473 263.17);
            --bg: oklch(0.2178 0 0);
            --text: oklch(0.9067 0 0);
            --white: oklch(0.2972 0 0);
        }
        
        [data-bs-theme="dark"] .title {
            color: oklch(0.9067 0 0);
        }
        
        [data-bs-theme="dark"] .input_field {
            background-color: oklch(0.36 0 0);
        }
        
        [data-bs-theme="dark"] .input_field input {
            color: oklch(0.9067 0 0);
        }
        
        [data-bs-theme="dark"] .input_field input::placeholder {
            color: oklch(0.6268 0 0);
        }
        
        /* ANIMATION */
        
        /* 默认进入注册模式 */
        .container.sign-up-mode {
            &::before {
                transform: translate(100%, -50%);
                right: 52%;
            }
            
            .signin-signup {
                left: 25%;
            }
            
            form.sign-up-form {
                opacity: 1;
                z-index: 2;
            }
            
            form.sign-in-form {
                opacity: 0;
                z-index: 1;
            }
            
            .left-panel .content,
            .left-panel .image {
                transform: translateX(-800px);
            }
            
            .right-panel .content,
            .right-panel .image {
                transform: translateX(0);
            }
            
            .left-panel {
                pointer-events: none;
            }
            
            .right-panel {
                pointer-events: all;
            }
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
                width: 110px;
                height: 35px;
                font-size: 0.7rem;
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
        }
        /* 💕 底部工具栏 */
        .theme-toggle-fixed {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
            z-index: 10;
        }
    </style>
</head>

<body>
   <main>
      <!-- 🥳 双面板登录页面 -->
      <div class="container sign-up-mode">
         <!-- 表单区 -->
         <div class="forms-container">
            <div class="signin-signup">
               <!-- 登录表单（原密码登录） -->
               <form class="sign-in-form needs-validation" action="" method="post" id="loginform" novalidate>
                  <h2 class="title">登录</h2>
                  <div class="input_field">
                     <input type="text" name="username" id="username" required placeholder="用户名" />
                  </div>
                  <div class="input_field">
                     <input type="password" name="password" id="password" required placeholder="密码" />
                  </div>
                  <p class="social_text">
                     <input class="Password_login" type="checkbox" name="rememberme" id="rememberme">
                     <span class="agree_text">
                        记住账号
                     </span>
                  </p>
                  <?php wp_nonce_field('user_login', 'login_nonce'); ?>
                  <button class="btn" type="submit" name="login_submit">Go</button>
                  <div id="login-message" class="mt-3"></div>
                  <div class="mt-3">
                     <a href="<?php echo boxmoe_reset_password_link_page(); ?>" class="text-primary text-decoration-none">忘记密码?</a>
                  </div>
               </form>

               <!-- 注册表单（原短信登录，已改为注册） -->
               <form class="sign-up-form needs-validation" id="signupform" novalidate>
                  <h2 class="title">注册</h2>
                  <div class="input_field">
                     <input type="text" name="username" id="signupFullnameInput" required placeholder="用户名" />
                  </div>
                  <div class="input_field">
                     <input type="email" name="email" id="signupEmailInput" required placeholder="邮箱" />
                  </div>
                  <div class="input_field">
                     <input type="text" name="verificationcode" id="signupVerificationCode" required placeholder="验证码" />
                     <div class="Acquire_box">
                        <span class="Acquire" id="sendVerificationCode">获取验证码</span>
                     </div>
                  </div>
                  <div class="input_field">
                     <input type="password" name="password" id="formSignUpPassword" required placeholder="设置密码" />
                  </div>
                  <p class="social_text">
                     <input class="shortMessage" type="checkbox" name="agree" required />
                     <span class="agree_text">
                        已阅读并同意<a href="#">《用户协议》</a><a href="#">《隐私政策》</a>
                     </span>
                  </p>
                  <input type="hidden" name="signup_nonce" value="<?php echo wp_create_nonce('user_signup'); ?>">
                  <button class="btn" type="submit" name="signup_submit">Go</button>
                  <div id="signup-message" class="mt-3"></div>
               </form>
            </div>
         </div>

         <!-- 面板 -->
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

      <!-- 🛠️ 主题切换按钮 -->
      <div class="theme-toggle-fixed">
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" data-swindex="0"><g stroke-dasharray="2"><path d="M12 21v1M21 12h1M12 3v-1M3 12h-1"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="4;2"/></path><path d="M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="4;2"/></path></g><path fill="currentColor" d="M7 6 C7 12.08 11.92 17 18 17 C18.53 17 19.05 16.96 19.56 16.89 C17.95 19.36 15.17 21 12 21 C7.03 21 3 16.97 3 12 C3 8.83 4.64 6.05 7.11 4.44 C7.04 4.95 7 5.47 7 6 Z" opacity="0"><set attributeName="opacity" begin="0.5s" to="1"/></path></g><g fill="currentColor" fill-opacity="0"><path d="m15.22 6.03l2.53-1.94L14.56 4L13.5 1l-1.06 3l-3.19.09l2.53 1.94l-.91 3.06l2.63-1.81l2.63 1.81z"><animate id="lineMdSunnyFilledLoopToMoonFilledLoopTransition0" fill="freeze" attributeName="fill-opacity" begin="0.6s;lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+6s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.2s" dur="0.4s" values="1;0"/></path><path d="M13.61 5.25L15.25 4l-2.06-.05L12.5 2l-.69 1.95L9.75 4l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+3s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+5.2s" dur="0.4s" values="1;0"/></path><path d="M19.61 12.25L21.25 11l-2.06-.05L18.5 9l-.69 1.95l-2.06.05l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+0.4s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.6s" dur="0.4s" values="1;0"/></path></g></svg>
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
      // 直接定义ajax_object，避免依赖主题脚本加载
      var ajax_object = {
        ajaxurl: '<?php echo admin_url("admin-ajax.php"); ?>',
        themeurl: '<?php echo boxmoe_theme_url(); ?>'
      };

      // 🔗 双面板切换功能
      document.addEventListener('DOMContentLoaded', function() {
          const sign_in_btn = document.getElementById('sign-in-btn');
          const sign_up_btn = document.getElementById('sign-up-btn');
          const container = document.querySelector('.container');

          sign_up_btn.addEventListener('click', () => {
              container.classList.add('sign-up-mode');
          });

          sign_in_btn.addEventListener('click', () => {
              container.classList.remove('sign-up-mode');
          });

          // 🔗 登录表单提交事件监听
          document.getElementById('loginform').addEventListener('submit', function(e) {
              e.preventDefault();
              
              const loginButton = this.querySelector('button[type="submit"]');
              
              loginButton.disabled = true;
              loginButton.textContent = '登录中...';

              // 🔗 获取 URL 中的 redirect_to 参数
              const urlParams = new URLSearchParams(window.location.search);
              const redirect_to = urlParams.get('redirect_to');

              // 🔄 动态生成新的nonce，避免过期问题
              const newNonce = document.querySelector('input[name="login_nonce"]').value;
              const formData = {
                  username: document.getElementById('username').value,
                  password: document.getElementById('password').value,
                  rememberme: document.getElementById('rememberme').checked,
                  login_nonce: newNonce,
                  redirect_to: redirect_to // ⬅️ 将重定向参数传给后端
              };
              
              // 使用FormData来构建请求体，确保WordPress能正确解析
              const formDataToSend = new FormData();
              formDataToSend.append('action', 'user_login_action');
              formDataToSend.append('formData', JSON.stringify(formData));
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  credentials: 'same-origin',
                  body: formDataToSend
              })
              .then(response => response.json())
              .then(response => {
                  if(response.success) {
                      document.getElementById('login-message').innerHTML = 
                          '<div class="alert alert-success mt-3">' + response.data.message + '，正在跳转...</div>';
                      setTimeout(() => {
                          // 🔗 优先跳转到后端返回的地址，其次尝试 URL 参数，最后回落到 referrer 或首页
                          if (response.data.redirect_url) {
                              window.location.href = response.data.redirect_url;
                          } else if (redirect_to) {
                              window.location.href = redirect_to;
                          } else {
                               window.location.href = '/';
                          }
                      }, 1000);
                  } else {
                      loginButton.disabled = false;
                      loginButton.textContent = 'Go';
                      
                      document.getElementById('login-message').innerHTML = 
                          '<div class="alert alert-danger mt-3">' + response.data.message + '</div>';
                  }
              })
              .catch(error => {
                  loginButton.disabled = false;
                  loginButton.textContent = 'Go';
                  
                  // 显示更详细的错误信息，帮助用户了解登录失败的原因
                  const errorMessage = error.message || '未知错误';
                  document.getElementById('login-message').innerHTML = 
                      '<div class="alert alert-danger mt-3">登录请求失败: ' + errorMessage + '，请稍后重试</div>';
                  
                  // 在控制台打印完整的错误信息，方便开发者调试
                  console.error('登录请求失败:', error);
              });
          });

          // 🔗 注册相关JS功能
          // 发送验证码逻辑
          document.getElementById('sendVerificationCode').addEventListener('click', function() {
              var email = document.getElementById('signupEmailInput').value;
              var btn = this;
              if(!email) {
                  alert('请先填写邮箱地址');
                  return;
              }
              
              btn.disabled = true;
              btn.textContent = '发送中...';
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                  body: 'action=send_verification_code&email=' + encodeURIComponent(email)
              })
              .then(response => response.json())
              .then(data => {
                  if(data.success) {
                      alert(data.data.message);
                      var countdown = 60;
                      var timer = setInterval(function() {
                          btn.textContent = countdown + 's后重试';
                          countdown--;
                          if(countdown < 0) {
                              clearInterval(timer);
                              btn.disabled = false;
                              btn.textContent = '获取验证码';
                          }
                      }, 1000);
                  } else {
                      alert(data.data.message);
                      btn.disabled = false;
                      btn.textContent = '获取验证码';
                  }
              })
              .catch(err => {
                  alert('发送失败，请重试');
                  btn.disabled = false;
                  btn.textContent = '获取验证码';
              });
          });

          // 注册表单提交
          document.getElementById('signupform').addEventListener('submit', function(e) {
              e.preventDefault();
              var btn = this.querySelector('button[type="submit"]');
              
              btn.disabled = true;
              btn.textContent = '注册中...';
              
              // 构建表单数据对象
              var formData = {
                  username: document.getElementById('signupFullnameInput').value,
                  email: document.getElementById('signupEmailInput').value,
                  verificationcode: document.getElementById('signupVerificationCode').value,
                  password: document.getElementById('formSignUpPassword').value,
                  signup_nonce: this.querySelector('input[name="signup_nonce"]').value
              };
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: 'action=user_signup_action&formData=' + encodeURIComponent(JSON.stringify(formData))
              })
              .then(response => response.json())
              .then(data => {
                  if(data.success) {
                      document.getElementById('signup-message').innerHTML = '<div class="alert alert-success mt-3">'+data.data.message+'</div>';
                      setTimeout(function(){
                          window.location.href = '<?php echo boxmoe_sign_in_link_page(); ?>';
                      }, 2000);
                  } else {
                      document.getElementById('signup-message').innerHTML = '<div class="alert alert-danger mt-3">'+data.data.message+'</div>';
                      btn.disabled = false;
                      btn.textContent = 'Go';
                  }
              })
              .catch(err => {
                  document.getElementById('signup-message').innerHTML = '<div class="alert alert-danger mt-3">网络错误，请重试</div>';
                  btn.disabled = false;
                  btn.textContent = 'Go';
              });
          });
      });
    </script>
    <!-- 🌌 引入粒子效果脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login-particles.js"></script>
</body></html>
