<?php
/** 
* Template Name: 外链直跳版
* Description:白木重构页面UI
*/
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================
$my_urls = [
	['boxmoe', 'https://www.boxmoe.com'],
	['jsmoe', 'https://www.jsmoe.com'],
	['ggy', 'https://www.ggy.net/aff.php?aff=614'],
	['kvmla', 'https://www.kvmla.pro/aff.php?aff=2793']
];
if(strlen($_SERVER['REQUEST_URI']) > 384 || strpos($_SERVER['REQUEST_URI'], "eval(") || strpos($_SERVER['REQUEST_URI'], "base64")) {
@header("HTTP/1.1 414 Request-URI Too Long");
@header("Status: 414 Request-URI Too Long");
@header("Connection: Close");
@exit;
}
$go_url=preg_replace('/^url=(.*)$/i','$1',$_SERVER["QUERY_STRING"]);
//自定义URL
foreach($my_urls as $key => $url_info)
{
	if($go_url==$url_info[0]) {
		$go_url = $url_info[1];	
	}
}
if(!empty($go_url)) {
// 首先尝试解码URL（如果被URL编码）
$decoded_url = urldecode($go_url);

// 检查是否是base64编码
if ($decoded_url == base64_encode(base64_decode($decoded_url))) {
    $decoded_url = base64_decode($decoded_url);
}

// 检查是否已经包含协议
preg_match('/^(http|https|thunder|qqdl|ed2k|Flashget|qbrowser):\/\//i', $decoded_url, $matches);
if($matches){
$url=$decoded_url;
$title= '页面加载中,请稍候...';
} else {
preg_match('/\./i',$decoded_url,$matche);
if($matche){
$url='https://'.$decoded_url;
$title= '页面加载中,请稍候...';
} else {
$url = 'https://'.$_SERVER['HTTP_HOST'];
$title='参数错误，中止跳转！正在返回首页...';
echo "<script>setTimeout(function(){window.opener=null;window.close();}, 3000);</script>";
}
}
} else {
$title ='参数缺失，中止跳转！正在返回首页...';
$url = 'https://'.$_SERVER['HTTP_HOST'];
echo "<script>setTimeout(function(){window.opener=null;window.close();}, 3000);</script>";
}

// 获取倒计时秒数
$delay = get_boxmoe('boxmoe_external_link_countdown', 3);
?>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex, nofollow"/>
<meta http-equiv="refresh" content="<?php echo $delay;?>;url=<?php echo $url;?>">
<meta charset="UTF-8">
<link rel="shortcut icon" href="<?php echo boxmoe_favicon(); ?>">
<!--[if IE 8]>
<style>
.ie8 .alert-circle,.ie8 .alert-footer{display:none}.ie8 .alert-box{padding-top:75px}.ie8 .alert-sec-text{top:45px}
</style>
<![endif]--><title><?php echo $title;?></title>

<style type="text/css">
/* 🥳 背景样式「采用登录页面UI」 */
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
    z-index: -2;
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

/* ✨ 高斯模糊磨砂质感遮盖层 */
.login-page-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: oklch(0 0 0 / 0.3); /* ⬅️ 背景遮罩，提升文字可读性 */
    backdrop-filter: blur(12px); /* ⬅️ 全局背景模糊 */
    -webkit-backdrop-filter: blur(12px);
    z-index: -1;
}

/* 🎨 主体动画样式 */
body {
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: oklch(0 0 0 / 0); /* ⬅️ 透明背景，显示下层背景图 */
    position: relative;
    overflow: hidden;
}

.container {
    width: 8em;
    height: 1em;
    font-size: 35px;
    display: flex;
    justify-content: space-between;
    z-index: 1;
    position: relative;
}

.container span {
    width: 1em;
    height: 1em;
    --duration: 1.5s;
}

.girl {
    animation: slide var(--duration) ease-in-out infinite alternate
}

@keyframes slide {
    0% {
        transform: translateX(0);
        filter: brightness(1)
    }
    to {
        transform: translatex(6.75em);
        filter: brightness(1.45)
    }
}

.boys {
    width: 6em;
    display: flex;
    justify-content: space-between
}

.boys span {
    animation: var(--duration) ease-in-out infinite alternate
}

.boys span:nth-child(1) {
    animation-name: jump-off-1
}

.boys span:nth-child(2) {
    animation-name: jump-off-2
}

.boys span:nth-child(3) {
    animation-name: jump-off-3
}

.boys span:nth-child(4) {
    animation-name: jump-off-4
}

@keyframes jump-off-1 {
    0%, 15% {
        transform: rotate(0deg)
    }
    35%, to {
        transform-origin: -50% center;
        transform: rotate(-180deg)
    }
}

@keyframes jump-off-2 {
    0%, 30% {
        transform: rotate(0deg)
    }
    50%, to {
        transform-origin: -50% center;
        transform: rotate(-180deg)
    }
}

@keyframes jump-off-3 {
    0%, 45% {
        transform: rotate(0deg)
    }
    65%, to {
        transform-origin: -50% center;
        transform: rotate(-180deg)
    }
}

@keyframes jump-off-4 {
    0%, 60% {
        transform: rotate(0deg)
    }
    80%, to {
        transform-origin: -50% center;
        transform: rotate(-180deg)
    }
}

.container span:before {
    content: '';
    position: absolute;
    width: inherit;
    height: inherit;
    border-radius: 15%;
    box-shadow: 0 0 .1em oklch(0 0 0 / .3)
}

.girl:before {
    background-color: hotpink
}

.boys span:before {
    background-color: oklch(0.652 0.1901 253.21);
    animation: var(--duration) ease-in-out infinite alternate
}

.boys span:nth-child(1):before {
    filter: brightness(1);
    animation-name: jump-down-1
}

.boys span:nth-child(2):before {
    filter: brightness(1.15);
    animation-name: jump-down-2
}

.boys span:nth-child(3):before {
    filter: brightness(1.3);
    animation-name: jump-down-3
}

.boys span:nth-child(4):before {
    filter: brightness(1.45);
    animation-name: jump-down-4
}

@keyframes jump-down-1 {
    5% {
        transform: scale(1, 1)
    }
    15% {
        transform-origin: center bottom;
        transform: scale(1.3, 0.7)
    }
    20%, 25% {
        transform-origin: center bottom;
        transform: scale(0.8, 1.3)
    }
    30%, to {
        transform: scale(1, 1)
    }
}

@keyframes jump-down-2 {
    20% {
        transform: scale(1, 1)
    }
    30% {
        transform-origin: center bottom;
        transform: scale(1.3, 0.7)
    }
    35%, 40% {
        transform-origin: center bottom;
        transform: scale(0.8, 1.3)
    }
    45%, to {
        transform: scale(1, 1)
    }
}

@keyframes jump-down-3 {
    35% {
        transform: scale(1, 1)
    }
    45% {
        transform-origin: center bottom;
        transform: scale(1.3, 0.7)
    }
    50%, 55% {
        transform-origin: center bottom;
        transform: scale(0.8, 1.3)
    }
    60%, to {
        transform: scale(1, 1)
    }
}

@keyframes jump-down-4 {
    50% {
        transform: scale(1, 1)
    }
    60% {
        transform-origin: center bottom;
        transform: scale(1.3, 0.7)
    }
    65%, 70% {
        transform-origin: center bottom;
        transform: scale(0.8, 1.3)
    }
    75%, to {
        transform: scale(1, 1)
    }
}

/* 📱 响应式调整 */
@media (max-width: 768px) {
    .container {
        font-size: 25px;
    }
}
</style>
</head>
<body>
<!-- 🖼️ 全屏背景容器 -->
<div class="login-page-bg"></div>

<div class="container">
  <span class="girl"></span>
  <div class="boys">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>

</body>
</html>