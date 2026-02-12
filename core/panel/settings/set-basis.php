<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 📁 定义图片路径变量
$theme_url = get_template_directory_uri();
$image_path = $theme_url . '/assets/images/';

$options[] = array(
    'name' => __('基础设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-admin-settings',
    'type' => 'heading');

    $options[] = array(
        'group' => 'start',
		'group_title' => '博客布局效果设置',
		'name' => __('博客布局', 'ui_boxmoe_com'),
		'id' => 'boxmoe_blog_layout',
		'std' => "one",
		'type' => "radio",
		'options' => array(
			'one' => __('单栏布局', 'ui_boxmoe_com'),
			'two' => __('双栏布局', 'ui_boxmoe_com')
		)); 
    $options[] = array(
		'name' => __('文章布局风格', 'ui_boxmoe_com'),
		'id' => 'boxmoe_article_layout_style',
		'std' => "single",
		'type' => "radio",
		'options' => array(
			'single' => __('单排布局', 'ui_boxmoe_com'),
			'three' => __('一排三个布局', 'ui_boxmoe_com')
		)); 
    $options[] = array(
		'name' => __('布局边框', 'ui_boxmoe_com'),
		'id' => 'boxmoe_blog_border',
		'std' => "default",
		'type' => "radio",
		'options' => array(
			'default' => __('无边框效果', 'ui_boxmoe_com'),
			'border' => __('漫画边框效果', 'ui_boxmoe_com'),
			'shadow' => __('阴影边框效果', 'ui_boxmoe_com'),
			'lines' => __('线条边框效果', 'ui_boxmoe_com'),
			'glass' => __('圆角拟态玻璃效果', 'ui_boxmoe_com')
		));
    $options[] = array(
        'name' => __('懒加载自定义占位图', 'ui_boxmoe_com'), 
        'id' => 'boxmoe_lazy_load_images',
        'std' => $image_path.'loading.gif',
        'class' => '',
        'type' => 'text');   

    $options[] = array(
		'name' => __('过渡页面动画', 'ui_boxmoe_com'),
		'id' => 'boxmoe_page_loading_type',
		'type' => "radio",
		'std' => "none",
		'options' => array(
			'none' => __('关闭过渡动画', 'ui_boxmoe_com'),
			'sakura' => __('🌸 樱花过渡动画', 'ui_boxmoe_com'),
			'ripple' => __('💧 涟漪式过渡动画', 'ui_boxmoe_com')
		));    
    $options[] = array(
		'name' => __('网页飘落动画', 'ui_boxmoe_com'),
		'id' => 'boxmoe_falling_animation_type',
		'type' => "radio",
		'std' => "none",
		'options' => array(
			'none' => __('关闭飘落动画', 'ui_boxmoe_com'),
			'sakura' => __('🌸 樱花飘落', 'ui_boxmoe_com'),
			'redpacket' => __('🧧 红包雨', 'ui_boxmoe_com')
		)); 
    $options[] = array(
        'name' => __('后台所有链接新窗口打开', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_all_links_new_tab',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('开启后，后台所有a标签链接将在新窗口打开', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('导航栏链接新窗口打开', 'ui_boxmoe_com'),
        'id' => 'boxmoe_nav_target_blank',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('开启后，前台顶部导航菜单链接将在新窗口打开', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('文章编辑按钮新窗口打开', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_edit_target_blank',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('开启后，文章页/页面内的编辑按钮（包括顶部工具栏编辑）将在新窗口打开', 'ui_boxmoe_com'),
        );          
    $options[] = array(
        'group' => 'end',
        'name' => __('悼念模式-全站变灰', 'ui_boxmoe_com'),
		'id' => 'boxmoe_body_grey_switch',
		'type' => "checkbox",
		'std' => false,
		);
    $options[] = array(
        'group' => 'start',
        'group_title' => '节日灯笼开关设置',
		'id' => 'boxmoe_festival_lantern_switch',
		'type' => "checkbox",
		'std' => false,
		);
	$options[] = array(
		'name' => __( '灯笼文字(1)', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_lanternfont1',
		'std' => '新',
		'class' => 'mini',
		'type' => 'text');
	$options[] = array(
		'name' => __( '灯笼文字(2)', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_lanternfont2',
		'std' => '春',
		'class' => 'mini',
		'type' => 'text');
	$options[] = array(
		'name' => __( '灯笼文字(3)', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_lanternfont3',
		'std' => '快',
		'class' => 'mini',
		'type' => 'text');
	$options[] = array(
        'group' => 'end',
		'name' => __( '灯笼文字(4)', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_lanternfont4',
		'std' => '乐',
		'class' => 'mini',
		'type' => 'text');      
    $options[] = array(
		'name' => __( 'LOGO设置', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_logo_src',
		'desc' => __(' ', 'ui_boxmoe_com'),
		'std' => $image_path.'logo.png',
		'type' => 'upload');      
    $options[] = array(
		'name' => __( 'Favicon地址', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_favicon_src',
		'std' => $image_path.'favicon.ico',
		'type' => 'upload'); 
    $options[] = array(
		'name' => __( '自定义背景装饰图', 'ui_boxmoe_com' ),
		'desc' => __('设置全站背景装饰图，留空则不显示', 'ui_boxmoe_com'),
		'id' => 'boxmoe_background_image',
		'std' => $image_path.'background.svg',
		'type' => 'upload');
    $options[] = array(
		'name' => __('分类链接去除category标识', 'ui_boxmoe_com'),
		'desc' => __('（需主机伪静态，开关都需要 后台导航的 设置>固定链接 点保存一次）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_no_categoty',
		'type' => "checkbox",
		'std' => false,
		);       
    $options[] = array(
        'group' => 'start',
		'group_title' => '网页右侧看板开关「点击可回到顶部」',
		'id' => 'boxmoe_lolijump_switch',
		'type' => "checkbox",
		'std' => false,
		); 
    $lolijump_custom_list = array();
    if(function_exists('get_boxmoe')){
        $lolijump_custom_list = get_boxmoe('boxmoe_lolijump_custom_list');
    }
    
    $lolijump_options = array(
			'lolisister1' => __(' 姐姐 ', 'ui_boxmoe_com'),
			'lolisister2' => __(' 妹妹', 'ui_boxmoe_com'),
			'dance' => __(' 阿尼亚', 'ui_boxmoe_com'),
			'meow' => __(' 喵小娘', 'ui_boxmoe_com'),
			'lemon' => __(' 柠檬妹', 'ui_boxmoe_com'),			
			'bear' => __(' 熊宝宝', 'ui_boxmoe_com'),
			'gurayao' => __(' gura摇', 'ui_boxmoe_com'),
    );
    if(is_array($lolijump_custom_list) && !empty($lolijump_custom_list)){
        $i = 1;
        foreach($lolijump_custom_list as $item){
            // Check if it's the new format (array with url)
            if(is_array($item) && isset($item['url']) && !empty($item['url'])){
                $name = isset($item['name']) && !empty($item['name']) ? $item['name'] : __(' 自定义看板 ', 'ui_boxmoe_com') . $i;
                $lolijump_options[$item['url']] = $name;
                $i++;
            }
        }
    }

	$options[] = array(
        'group' => 'end',
		'name' => __('选择前端看板形象', 'ui_boxmoe_com'),
		'id' => 'boxmoe_lolijump_img',
		'type' => "radio",
		'std' => 'lolisister1',
		'options' => $lolijump_options);

    $options[] = array(
        'name' => __('自定义看板列表', 'ui_boxmoe_com'),
        'desc' => __('新增后以首页风格的列表显示在下方，点击替换按钮、删除按钮进行管理。', 'ui_boxmoe_com'),
        'id' => 'boxmoe_lolijump_custom_list',
        'type' => 'custom_board_list',
        'std' => array()
    );

	$options[] = array(
        'group' => 'start',
		'group_title' => '底部设置',
		'name' => __('底部显示页面执行时间', 'ui_boxmoe_com'),
		'desc' => __('（默认关闭，开启后底部显示页面执行时间）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_footer_dataquery_switch',
		'type' => "checkbox",
		'std' => false,
		);	
	$options[] = array(
		'name' => __('底部隐藏 Copyright 文字', 'ui_boxmoe_com'),
		'desc' => __('（开启后只显示 ©）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_footer_copyright_hidden',
		'type' => "checkbox",
		'std' => false,
		);
	$options[] = array(
		'name' => __('网站底部导航链接', 'ui_boxmoe_com'),
		'id' => 'boxmoe_footer_seo',
		'std' => '<li class="nav-item"><a href="'.site_url('/sitemap.xml').'" target="_blank" class="nav-link">网站地图</a></li>'."\n",
		'desc' => __('（网站地图可自行使用sitemap插件自动生成）', 'ui_boxmoe_com'),
		'settings' => array('rows' => 3),
		'type' => 'textarea');
	$options[] = array(
		'name' => __('网站底部自定义信息（如备案号支持HTML代码）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_footer_info',
		'std' => '本站使用Wordpress创作'."\n",
		'settings' => array('rows' => 3),
		'type' => 'textarea');	
	$options[] = array(
		'name' => __('底部版权信息自定义', 'ui_boxmoe_com'),
		'id' => 'boxmoe_footer_theme_by_text',
		'std' => '本站主题作者 <a href="https://www.boxmoe.com" target="_blank">Boxmoe</a>'."\n".'🎉'."\n".'本站二次开发 <a href="https://gl.baimu.live" target="_blank">白木</a>'."\n".'🕊️ 主题版本：{THEME_VERSION}',
		'desc' => __('自定义底部 Theme by 信息，支持HTML', 'ui_boxmoe_com'),
		'type' => 'textarea',
		'settings' => array('rows' => 2));
    $options[] = array(
		'name' => __('统计代码', 'ui_boxmoe_com'),
		'desc' => __('（底部第三方流量数据统计代码）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_trackcode',
		'std' => '统计代码',
		'settings' => array('rows' => 3),
		'type' => 'textarea');
	$options[] = array(
        'group' => 'end',
		'name' => __('自定义代码', 'ui_boxmoe_com'),
		'desc' => __('（适用于自定义如css js代码置于底部加载）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_diy_code_footer',
		'std' => '',
		'settings' => array('rows' => 3),
		'type' => 'textarea');   
		$options[] = array(
			'group' => 'start',
			'group_title' => '底部运行天数设置',
			'name' => __('底部运行天数开关', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_switch',
			'type' => 'checkbox',
			'std' => false,
		);
		$options[] = array(
			'name' => __('建站时间', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_time',
			'type' => 'text',
			'class' => 'mini',
			'std' => '2025-01-01',
		);
		$options[] = array(
			'name' => __('运行天数自定义文字前缀', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_prefix',
			'type' => 'text',
			'class' => 'small',
			'std' => '本站已稳定运行了',
		);
		$options[] = array(
			'name' => __('运行天数自定义文字后缀', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_suffix',
			'type' => 'text',
			'class' => 'small',
			'std' => '天',
		);
		$options[] = array(
			'name' => __('运行（时）自定义文字后缀', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_suffix_hours',
			'type' => 'text',
			'class' => 'small',
			'std' => '时',
		);
		$options[] = array(
			'name' => __('运行（分）自定义文字后缀', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_suffix_minutes',
			'type' => 'text',
			'class' => 'small',
			'std' => '分',
		);
		$options[] = array(
			'group' => 'end',
			'name' => __('运行（秒）自定义文字后缀', 'ui_boxmoe_com'),
			'id' => 'boxmoe_footer_running_days_suffix_seconds',
			'type' => 'text',
			'class' => 'small',
			'std' => '秒',
		);
		
		// 🎯 页面焦点状态文字显示控制
		$options[] = array(
			'group' => 'start',
			'group_title' => '页面焦点状态文字显示控制',
			'name' => __('启用页面焦点状态文字显示', 'ui_boxmoe_com'),
			'id' => 'boxmoe_page_focus_switch',
			'type' => 'checkbox',
			'std' => false,
			'desc' => __('开启后，当用户离开或返回浏览器标签页时，页面标题或指定区域会显示自定义文字', 'ui_boxmoe_com'),
		);
		$options[] = array(
			'name' => __('离开时显示文字', 'ui_boxmoe_com'),
			'id' => 'boxmoe_page_focus_leave_text',
			'type' => 'text',
			'std' => '🚨你快回来~',
			'desc' => __('当用户离开浏览器标签页时显示的文字', 'ui_boxmoe_com'),
		);
		$options[] = array(
			'group' => 'end',
			'name' => __('返回时欢迎语', 'ui_boxmoe_com'),
			'id' => 'boxmoe_page_focus_return_text',
			'type' => 'text',
			'std' => '🥱你可算回来了！',
			'desc' => __('当用户返回浏览器标签页时显示的文字', 'ui_boxmoe_com'),
		);
