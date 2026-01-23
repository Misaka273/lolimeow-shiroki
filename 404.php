<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}

// 🔽 同步站点字体设置
$shiroki_fonts_css = '';
if(get_boxmoe('boxmoe_custom_font_switch')){
    $fonts = get_boxmoe('boxmoe_fonts');
    if(is_array($fonts) && !empty($fonts)){
        foreach($fonts as $f){
            $name = isset($f['name']) ? trim($f['name']) : '';
            $src = '';
            if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
            elseif(!empty($f['url'])){ $src = trim($f['url']); }
            if($name && $src){
                $shiroki_fonts_css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";
            }
        }
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $shiroki_fonts_css .= "body{font-family:'".esc_attr($default)."',".$fallback." !important;}";
    }
}

// 🎨 获取站点图标
$shiroki_site_logo = get_site_icon_url();

// 🎨 获取站点名称
$shiroki_site_name = get_bloginfo('name');

// 🎨 获取主题目录URI
$shiroki_template_uri = get_template_directory_uri();

// 🎨 获取首页URL
$shiroki_home_url = home_url();

// 🎨 构建404页面标题
$shiroki_404_title = get_option('shiroki_404_title', 'oi~坏惹，居然是404');

// 🎨 构建404页面主标题
$shiroki_404_main_title = get_option('shiroki_404_main_title', '404惹呢~');

// 🎨 构建404页面描述文字
$shiroki_404_description = get_option('shiroki_404_description', '您好像访问了一个不存在的页面');

// 🎨 构建404页面提示文字
$shiroki_404_hint = get_option('shiroki_404_hint', '要不您返回首页叭~或者联系站长也可以的');

// 🎨 构建返回按钮文字
$shiroki_404_button_text = get_option('shiroki_404_button_text', '返回');

// 🎨 构建404页面图片路径
$shiroki_404_gif_path = $shiroki_template_uri . '/assets/404/gura-zq.gif';
$shiroki_404_svg_path = $shiroki_template_uri . '/assets/404/routes.svg';
$shiroki_favicon_path = $shiroki_template_uri . '/assets/images/favicon.ico';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($shiroki_404_title); ?></title>
    
    <!-- 🎨 404页面自定义字体样式 -->
    <style>
        <?php echo $shiroki_fonts_css; ?>
    </style>
    
    <!-- 🔽 引入tsParticles粒子效果库 -->
    <script src="<?php echo esc_url($shiroki_template_uri); ?>/assets/404/tsparticles.preset.fountain.bundle.min.js"></script>
    
    <!-- 🔽 引入404页面样式文件 -->
    <link rel="stylesheet" href="<?php echo esc_url($shiroki_template_uri); ?>/assets/404/shiroki-404.css" />
    
    <!-- 🔽 引入网站图标 -->
    <link rel="icon" type="image/x-icon" href="<?php echo esc_url($shiroki_favicon_path); ?>" />
</head>
<body>
    <!-- 🎨 tsParticles粒子容器 -->
    <div id="tsparticles"></div>
    
    <!-- � 404页面主容器 -->
    <main class="container">
        <div class="content">
            <div class="header">
                <div class="header__title">
                    <h1 class="header__h1" data-text="<?php echo esc_attr($shiroki_404_main_title); ?>"><?php echo esc_html($shiroki_404_main_title); ?></h1>
                    <img
                      class="header__ilustration"
                      src="<?php echo esc_url($shiroki_404_gif_path); ?>"
                      alt="Minimalist compass color gray"
                    />
                </div>
                <p class="header__text">
                <?php echo esc_html($shiroki_404_description); ?>
                </p>
                <p class="header__text__02">
                   <?php echo esc_html($shiroki_404_hint); ?>
                </p>
                <a
                  class="header__button"
                  href="<?php echo esc_url($shiroki_home_url); ?>"
                >
                    <span class="header__button-text"><?php echo esc_html($shiroki_404_button_text); ?></span>
                    <?php 
                    if ($shiroki_site_logo) {
                        echo '<img src="' . esc_url($shiroki_site_logo) . '" alt="' . esc_attr($shiroki_site_name) . '" class="header__button-logo" >';
                    }
                    ?>
                    <span class="header__button-site-name"><?php echo esc_html($shiroki_site_name); ?></span>
                </a>
            </div>
            <div class="ilustration-container">
                <img
                  class="ilustration"
                  src="<?php echo esc_url($shiroki_404_svg_path); ?>"
                  alt="random figures pattern"
                />
            </div>
        </div>
    </main>
    
    <!-- 🔽 引入404页面交互脚本-->
    <script src="<?php echo esc_url($shiroki_template_uri); ?>/assets/404/shiroki-404.js"></script>
</body>
</html>
