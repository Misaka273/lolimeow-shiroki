<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================
add_action('widgets_init','unregister_d_widget');
function unregister_d_widget(){
    unregister_widget('WP_Widget_Recent_Comments');
    // 注销 WordPress 默认搜索小部件，使用自定义的
    unregister_widget('WP_Widget_Search');
}

$widgets = array(
	'ads',
	'postlist',
	'random-posts',
	'comments',
	'category',
	'archive',
	'tags',
	'userinfo',
	'currentuser',
	'search',
	'postauthor',
	'clock',

);

foreach ($widgets as $widget) {
	include 'widget-'.$widget.'.php';
}

add_action( 'widgets_init', 'widget_ui_loader', 20 );
function widget_ui_loader() {
	global $widgets;
	foreach ($widgets as $widget) {
		$class_name = str_replace('-', '_', $widget);
		register_widget( 'widget_'.$class_name );
	}
}

// 📋 加载统一的复制功能脚本
add_action('wp_enqueue_scripts', 'load_copy_function_script');
function load_copy_function_script() {
    // 只在前端加载脚本
    if (!is_admin()) {
        // 获取脚本的绝对路径和URL
        $script_path = get_template_directory() . '/core/widgets/copy-function.js';
        $script_url = get_template_directory_uri() . '/core/widgets/copy-function.js';
        
        // 检查脚本文件是否存在
        if (file_exists($script_path)) {
            // 加载复制功能脚本，确保只加载一次
            wp_enqueue_script(
                'boxmoe-copy-function',
                $script_url,
                array(),
                filemtime($script_path), // 使用文件修改时间作为版本号，确保缓存更新
                true // 在页脚加载，确保DOM已完全加载
            );
        }
    }
}