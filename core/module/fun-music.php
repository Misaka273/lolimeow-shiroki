<?php
/**
 * @link gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器功能模块
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 🎵 音乐播放器功能模块 - 由初叶🍂提供盒子萌V8.01版本主题包，白木🥰集成
function boxmoe_music_player_init() {
    // 获取主题设置
    $music_switch = get_boxmoe('music_on', false);
    
    // 如果音乐播放器未启用，则返回
    if (!$music_switch) {
        return;
    }
    
    // 注册音乐播放器样式
    wp_register_style('boxmoe-music-player-css', get_template_directory_uri() . '/assets/css/music-player/APlayer.min.css', array(), THEME_VERSION, 'all');
    
    // 🎵 注册miniswitcher修复样式 - 修复箭头图标不显示问题
    wp_register_style('shiroki-aplayer-miniswitcher-fix', get_template_directory_uri() . '/assets/css/music-player/aplayer-miniswitcher-fix-shiroki.css', array('boxmoe-music-player-css'), THEME_VERSION, 'all');
    
    // 加载样式
    wp_enqueue_style('boxmoe-music-player-css');
    wp_enqueue_style('shiroki-aplayer-miniswitcher-fix');
}

// 加载音乐播放器资源
add_action('wp_enqueue_scripts', 'boxmoe_music_player_init');

// 🎵 构建完整的Meting API URL（自动添加查询参数）
function boxmoe_build_meting_api_url($base_url) {
    // 移除末尾的斜杠
    $base_url = rtrim($base_url, '/');
    
    // 🎵 Meting API 标准查询参数
    $api_params = '?server=:server&type=:type&id=:id&r=:r';
    
    // 检查URL是否已经包含查询参数
    if (strpos($base_url, '?') !== false) {
        // 如果已经有查询参数，则不重复添加
        return $base_url;
    }
    
    return $base_url . $api_params;
}

// 🎵 输出音乐播放器HTML
function boxmoe_music_player_html() {
    // 获取主题设置
    $music_switch = get_boxmoe('music_on', false);
    
    // 如果音乐播放器未启用，则返回
    if (!$music_switch) {
        return;
    }
    
    // 获取音乐播放器设置
    $server = get_boxmoe('music_server', 'netease');
    $id = get_boxmoe('music_id', '6814606449');
    $order = get_boxmoe('music_order', 'list');
    $api = get_boxmoe('music_api', '');
    $api_source = get_boxmoe('music_api_source', 'api_injahow');
    
    // 🎵 定义多个备用API源（基础URL，不含查询参数）
    $api_base_urls = array(
        'api_injahow' => 'https://api.injahow.cn/meting/',
        'api_imeto' => 'https://api.i-meto.com/meting/api',
        'api_ihuan' => 'https://meting-api.ihuan.me/api',
        'api_github' => 'https://api-meting.github.io/api',
        'api_chuyel' => 'https://musicapi.chuyel.top/meting/api'
    );
    
    // 🎵 构建完整的API URL数组（自动添加查询参数）
    $api_urls = array();
    foreach ($api_base_urls as $key => $base_url) {
        $api_urls[$key] = boxmoe_build_meting_api_url($base_url);
    }
    
    // 🎵 强制使用自定义API接口：如果用户自定义了API，则强制使用自定义API
    if (!empty($api)) {
        // 自动为自定义API添加查询参数
        $api_url = boxmoe_build_meting_api_url($api);
        // 🎵 将自定义API添加到API源数组中，确保API切换功能也能使用自定义API
        $api_urls['custom_api'] = $api_url;
    } else {
        // 否则根据用户选择的API源设置
        $api_url = isset($api_urls[$api_source]) ? $api_urls[$api_source] : $api_urls['api_injahow'];
    }
    
    // 输出播放器核心脚本和HTML标签
    $html = '<script src="' . get_template_directory_uri() . '/assets/js/music-player/APlayer.min.js"></script>';
    $html .= '<script src="' . get_template_directory_uri() . '/assets/js/music-player/Meting.min.js"></script>';
    $html .= '<script src="' . get_template_directory_uri() . '/assets/js/music-player/aplayer-lyrics-fix.js"></script>';
    $html .= '<script src="' . get_template_directory_uri() . '/assets/js/music-player/shiroki-music-api-manager.js"></script>';
    $html .= '<script src="' . get_template_directory_uri() . '/assets/js/music-player/shiroki-music-error-handler.js"></script>';
    $html .= '<script>';
    $html .= '// 🎵 定义备用API源数组（已包含完整查询参数）';
    $html .= 'window.shirokiMusicAPIs = ' . json_encode($api_urls) . ';';
    $html .= '// 🎵 设置默认API';
    $html .= 'window.meting_api = "' . esc_js($api_url) . '";';
    // 🎵 如果使用了自定义API，则设置全局变量
    if (!empty($api)) {
        $html .= '// 🎵 标记使用了自定义API';
        $html .= 'window.shirokiCustomAPI = "' . esc_js($api_url) . '";';
    }
    $html .= '</script>';
    // 添加核心的meting-js标签，这是播放器显示的关键
    $html .= '<meting-js server="' . esc_attr($server) . '" type="playlist" id="' . esc_attr($id) . '" fixed="true" order="' . esc_attr($order) . '" preload="auto" list-folded="true" lrc-type="3" api="' . esc_attr($api_url) . '"></meting-js>';
    
    echo $html;
}

// 引入音乐播放器弹窗菜单模块
require_once dirname(__FILE__) . '/fun-music-popup-shiroki.php';

// 在页面底部输出音乐播放器
add_action('wp_footer', 'boxmoe_music_player_html');
