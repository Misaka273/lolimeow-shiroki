<?php
/**
 * 🚀 超高效重置处理文件
 * 使用预定义默认值，避免动态获取，大幅提高速度
 */

// 🔧 修复：使用正确的WordPress加载路径
$wordpress_root = dirname(dirname(dirname(dirname(__FILE__))));
require_once($wordpress_root . '/wp-load.php');

// 安全设置
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 权限检查
if (!current_user_can('manage_options')) {
    wp_die('❌ 权限不足：只有管理员才能访问此页面！');
}

// 🔧 更灵活的请求检测
$isResetRequest = (isset($_POST['direct_reset']) && $_POST['direct_reset'] === '1') ||
                 (isset($_POST['reset']) && $_POST['reset'] === '1') ||
                 (isset($_POST['reset_flag']) && $_POST['reset_flag'] === '1');

// 🔧 记录请求类型
if (isset($_POST['direct_reset'])) {
    error_log('Super Fast Reset Request Type: direct_reset');
} elseif (isset($_POST['reset'])) {
    error_log('Super Fast Reset Request Type: reset');
} elseif (isset($_POST['reset_flag'])) {
    error_log('Super Fast Reset Request Type: reset_flag');
}

if ($isResetRequest) {
    // 🔧 设置更长的执行时间和更大的内存限制
    @set_time_limit(300);
    @ini_set('memory_limit', '512M');
    
    // 🔧 直接使用预定义的默认值
    $start_time = microtime(true);
    
    // 获取主题选项名称
    $option_name = 'options-framework-theme';
    
    // 🔧 使用预定义的默认值，避免动态获取
    $default_settings = array(
        // 基础设置
        'boxmoe_basics_logo' => '',
        'boxmoe_basics_favicon' => '',
        'boxmoe_basics_icp' => '',
        'boxmoe_basics_cdn' => '',
        'boxmoe_basics_seo_title' => '',
        'boxmoe_basics_seo_keywords' => '',
        'boxmoe_basics_seo_description' => '',
        'boxmoe_basics_headcode' => '',
        'boxmoe_basics_statistics' => '',
        'boxmoe_basics_css' => '',
        'boxmoe_basics_js' => '',
        
        // Banner设置
        'boxmoe_banner_type' => 'img',
        'boxmoe_banner_img' => get_template_directory_uri() . '/assets/images/banner.jpg',
        'boxmoe_banner_video' => '',
        'boxmoe_banner_height' => '300',
        
        // SEO设置
        'boxmoe_seo_open' => '1',
        'boxmoe_seo_baidustatistics' => '',
        'boxmoe_seo_googlestatistic' => '',
        
        // 文章设置
        'boxmoe_article_list' => 'excerpt',
        'boxmoe_article_thumbnail' => '1',
        'boxmoe_article_views' => '1',
        'boxmoe_article_author' => '1',
        'boxmoe_article_date' => '1',
        'boxmoe_article_category' => '1',
        'boxmoe_article_tags' => '1',
        
        // 页面标语设置
        'boxmoe_slogan_home_text' => '欢迎来到我的博客',
        'boxmoe_slogan_home_desc' => '这是一个很棒的博客',
        'boxmoe_slogan_category_text' => '分类目录',
        'boxmoe_slogan_category_desc' => '所有文章分类',
        'boxmoe_slogan_tag_text' => '标签云',
        'boxmoe_slogan_tag_desc' => '所有标签',
        'boxmoe_slogan_search_text' => '搜索结果',
        'boxmoe_slogan_search_desc' => '搜索到的文章',
        'boxmoe_slogan_404_text' => '页面未找到',
        'boxmoe_slogan_404_desc' => '抱歉，您访问的页面不存在',
        'boxmoe_slogan_author_text' => '作者信息',
        'boxmoe_slogan_author_desc' => '关于作者',
        
        // 用户设置
        'boxmoe_user_center' => '1',
        'boxmoe_user_register' => '1',
        'boxmoe_user_login_captcha' => '0',
        'boxmoe_user_avatar' => '1',
        'boxmoe_user_vip' => '0',
        
        // 主题设置
        'boxmoe_theme_mode' => 'day',
        'boxmoe_theme_color' => 'default',
        'boxmoe_theme_font' => 'default',
        'boxmoe_theme_layout' => 'right-sidebar',
        'boxmoe_theme_animation' => '1',
        
        // 其他设置...
    );
    
    // 🔧 记录获取默认设置的时间
    $get_defaults_time = microtime(true) - $start_time;
    error_log('Shiroki主题：获取预定义默认设置耗时 ' . number_format($get_defaults_time, 4) . ' 秒');
    
    // 直接更新数据库
    $update_start = microtime(true);
    update_option($option_name, $default_settings);
    $update_time = microtime(true) - $update_start;
    
    // 记录重置操作
    $total_time = microtime(true) - $start_time;
    error_log('Shiroki主题：超高效重置操作已执行 - ' . date('Y-m-d H:i:s'));
    error_log('Shiroki主题：更新数据库耗时 ' . number_format($update_time, 4) . ' 秒');
    error_log('Shiroki主题：总耗时 ' . number_format($total_time, 4) . ' 秒');
    
    // 🔧 使用正确的主题设置页面URL
    $redirect_url = admin_url('admin.php?page=boxmoe_options');
    error_log('Shiroki主题：重定向到 ' . $redirect_url);
    error_log('Shiroki主题：admin_url()函数返回: ' . admin_url('admin.php?page=boxmoe_options'));
    error_log('Shiroki主题：site_url()函数返回: ' . site_url('/wp-admin/admin.php?page=boxmoe_options'));
    
    // 重定向到主题设置页面
    header('Location: ' . $redirect_url);
    exit;
}

// 🔧 如果不是重置请求，显示更详细的错误信息
echo '<h1>❌ 无效的请求！</h1>';
echo '<p>请通过主题设置页面的重置按钮访问此页面。</p>';
echo '<h3>调试信息：</h3>';
echo '<pre>';
echo 'POST数据：' . print_r($_POST, true);
echo 'GET数据：' . print_r($_GET, true);
echo '当前用户：' . (current_user_can('manage_options') ? '管理员' : '非管理员');
echo '</pre>';
echo '<p><a href="' . admin_url('themes.php?page=options-framework') . '">返回主题设置</a></p>';
?>