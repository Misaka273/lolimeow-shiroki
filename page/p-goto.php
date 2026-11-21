<?php
/**
* Template Name:外链提醒版
* Description:白木重构页面UI
*/
$my_urls = array(
array('boxmoe','https://www.boxmoe.com'),
array('jsmoe','https://www.jsmoe.com')
);

$err = "0"; 

if(strlen($_SERVER['REQUEST_URI']) > 384 || strpos($_SERVER['REQUEST_URI'], "eval(") || strpos($_SERVER['REQUEST_URI'], "base64")) {
@header("HTTP/1.1 414 Request-URI Too Long");
@header("Status: 414 Request-URI Too Long");
@header("Connection: Close");
@exit;
}
$go_url=preg_replace('/^url=(.*)$/i','$1',$_SERVER["QUERY_STRING"]);
//自定义URL
foreach($my_urls as $x=>$x_value)
{
	if($go_url==$x_value[0]) {
		$go_url = $x_value[1];	
	}
}
if(!empty($go_url)) {
// 首先尝试解码URL（如果被URL编码）
$decoded_url = urldecode($go_url);

// 检查是否是base64编码
if (!empty($decoded_url) && $decoded_url === base64_encode(base64_decode($decoded_url))) {
    $decoded_url = base64_decode($decoded_url);
}

// 检查是否已经包含协议
preg_match('/^(http|https|thunder|qqdl|ed2k|Flashget|qbrowser):\/\//i', $decoded_url, $matches);
if($matches){
    $url = $decoded_url;
    $title= '安全中心 | 加载中...';
} else {
    // 检查是否包含域名（有.符号）
    preg_match('/\./i', $decoded_url, $matche);
    if($matche){
        $url = 'https://' . $decoded_url;
        $title= '安全中心 | 加载中...';
    } else {
        $err = "1";
        $url = 'https://' . $_SERVER['HTTP_HOST'];
        $title='参数错误，中止跳转！正在返回首页...';
    }
}
} else {
$err = "1";	
$title ='参数缺失，中止跳转！正在返回首页...';
$url = 'https://'.$_SERVER['HTTP_HOST'];
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
        /* 🥳 跳转页样式 - 玻璃拟态设计 */
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
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
            opacity: 0; /* ⬅️ 初始透明度为0 */
            animation: fadeIn 0.3s ease-in-out forwards; /* ⬅️ 添加0.3秒渐显动画 */
        }
        
        /* ✨ 渐显动画 */
        @keyframes fadeIn {
            from {
                opacity: 0; /* ⬅️ 开始时完全透明 */
            }
            to {
                opacity: 1; /* ⬅️ 结束时完全不透明 */
            }
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
        [data-bs-theme="dark"] .text-danger {
            color: oklch(0.7446 0.1564 33.13) !important;
        }
        [data-bs-theme="dark"] .text-primary {
            color: oklch(0.6711 0.1733 269.58) !important;
        }
        [data-bs-theme="dark"] .btn-primary {
            background-color: oklch(0.6711 0.1733 269.58);
            border-color: oklch(0.6711 0.1733 269.58);
        }
        [data-bs-theme="dark"] .btn-primary:hover {
            background-color: oklch(0.6037 0.1705 269.68);
            border-color: oklch(0.6037 0.1705 269.68);
        }

        /* 🔔 警告图标 */
        .warning-icon {
            font-size: 4rem;
            color: oklch(0.8442 0.1722 84.93);
            margin-bottom: 1.5rem;
            display: block;
            text-align: center;
        }
        [data-bs-theme="dark"] .warning-icon {
            color: oklch(0.8859 0.1539 91.23);
        }

        /* 📱 响应式调整 */
        @media (max-width: 576px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }
            .warning-icon {
                font-size: 3rem;
            }
        }

        /* 🏷️ 跳转信息样式 */
        .redirect-info {
            background: oklch(1 0 0 / 0.6);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
            border: 1px solid oklch(1 0 0 / 0.3);
        }
        [data-bs-theme="dark"] .redirect-info {
            background: oklch(0 0 0 / 0.2);
            border-color: oklch(1 0 0 / 0.1);
            color: oklch(0.9067 0 0);
        }

        /* ⏱️ 倒计时样式 */
        .countdown {
            font-size: 2rem;
            font-weight: bold;
            color: var(--bs-primary);
            margin: 1rem 0;
            display: block;
            text-align: center;
        }

        /* 🎯 按钮样式 */
        .btn-primary {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            box-shadow: 0 4px 6px oklch(var(--bs-primary-oklch) / 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
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

        /* 🛠️ 主题切换按钮 */
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
               <h3 class="mt-3 mb-1 fw-bold">安全提醒</h3>
            </div>

            <?php if($err != "1"){?>
            <!-- 跳转提醒内容 -->
            <div class="text-center">
                <i class="bi bi-exclamation-triangle warning-icon"></i>
                <h4 class="mb-3 text-danger">请注意您的账号和财产安全</h4>
                <p class="mb-4">您即将离开 <strong><?php bloginfo('name'); ?></strong>，去往以下链接：</p>
                <div class="redirect-info">
                    <?php echo htmlspecialchars($url);?>
                </div>
                <p class="mt-3">
                    <span id="time" class="countdown"><?php echo get_boxmoe('boxmoe_external_link_countdown', 3);?></span>秒后自动跳转
                </p>
                <script type="text/javascript">  
                    delayURL();    
                    function delayURL() { 
                        var delay = parseInt(document.getElementById("time").innerHTML);
                        var t = setTimeout(delayURL, 1000);
                        if (delay > 0) {
                            delay--;
                            document.getElementById("time").innerHTML = delay;
                        } else {
                        clearTimeout(t); 
                            window.location.href = "<?php echo $url;?>";
                        }        
                    } 
                </script>
                <div class="d-grid gap-2 mt-4">
                    <a class="btn btn-primary" href="<?php echo $url;?>" rel="external nofollow">立即前往</a>
                    <a class="btn btn-outline-secondary" href="<?php echo get_option('home'); ?>">取消返回</a>
                </div>
            </div>
            <?php }else{ ?>
            <!-- 错误提醒内容 -->
            <div class="text-center">
                <i class="bi bi-x-circle warning-icon" style="color: oklch(0.5915 0.202 21.24);"></i>
                <h4 class="mb-3 text-danger">目标网址未通过检测</h4>
                <p class="mb-4"><?php echo $title;?></p>
                <p class="mt-3">
                    <span id="time" class="countdown"><?php echo get_boxmoe('boxmoe_external_link_countdown', 3);?></span>秒后自动返回首页
                </p>
                <script type="text/javascript">  
                    delayURL();    
                    function delayURL() { 
                        var delay = parseInt(document.getElementById("time").innerHTML);
                        var t = setTimeout(delayURL, 1000);
                        if (delay > 0) {
                            delay--;
                            document.getElementById("time").innerHTML = delay;
                        } else {
                        clearTimeout(t); 
                            window.location.href = "<?php echo $url;?>";
                        }        
                    } 
                </script>
                <div class="d-grid gap-2 mt-4">
                    <a class="btn btn-primary" href="<?php echo $url;?>" rel="external nofollow">返回首页</a>
                </div>
            </div>
            <?php }?>

            <!-- 底部版权 -->
            <div class="text-center mt-5 pt-3 border-top border-light">
               <div class="small text-body-tertiary">
                  Copyright © <?php echo date('Y'); ?> 
                  <span class="text-primary"><a href="<?php echo get_option('home'); ?>" class="text-reset text-decoration-none fw-bold"><?php echo get_bloginfo('name'); ?></a></span>
                  <br> Theme by
                  <span class="text-primary"><a href="https://www.boxmoe.com" class="text-reset text-decoration-none fw-bold">Boxmoe</a></span> powered by WordPress
                  <br> 页面由
                  <span class="text-primary"><a href="https://gl.baimu.live" class="text-reset text-decoration-none fw-bold">白木</a></span> 重构
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" data-swindex="0"><g stroke-dasharray="2"><path d="M12 21v1M21 12h1M12 3v-1M3 12h-1"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="4;2"/></path><path d="M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="4;2"/></path></g><path fill="currentColor" d="M7 6 C7 12.08 11.92 17 18 17 C18.53 17 19.05 16.96 19.56 16.89 C17.95 19.36 15.17 21 12 21 C7.03 21 3 16.97 3 12 C3 8.83 4.64 6.05 7.11 4.44 C7.04 4.95 7 5.47 7 6 Z" opacity="0"><set attributeName="opacity" begin="0.5s" to="1"/></path></g><g fill="currentColor" fill-opacity="0"><path d="m15.22 6.03l2.53-1.94L14.56 4L13.5 1l-1.06 3l-3.19.09l2.53 1.94l-.91 3.06l2.63-1.81l2.63 1.81z"><animate id="lineMdSunnyFilledLoopToMoonFilledLoopTransition0" fill="freeze" attributeName="fill-opacity" begin="0.6s;lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+6s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.2s" dur="0.4s" values="1;0"/></path><path d="M13.61 5.25L15.25 4l-2.06-.05L12.5 2l-.69 1.95l-2.06.05l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+3s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+5.2s" dur="0.4s" values="1;0"/></path><path d="M19.61 12.25L21.25 11l-2.06-.05L18.5 9l-.69 1.95l-2.06.05l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+0.4s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.6s" dur="0.4s" values="1;0"/></path></g></svg>
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
      // 延时30S关闭跳转页面，用于文件下载后不会关闭跳转页的问题
      setTimeout(function() {
          window.opener = null;
          window.close();
      }, 30000);
    </script>
</body></html>
