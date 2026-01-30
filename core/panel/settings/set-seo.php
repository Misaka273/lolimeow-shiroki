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

$options[] = array(
    'name' => __('搜索引擎优化', 'ui_boxmoe_com'),
    'icon' => 'dashicons-chart-line',
    'type' => 'heading');

    $options[] = array(
        'group' => 'start',
	    'group_title' => '搜索引擎推送设置',
        'name' => __('百度推送开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_baidu_submit_switch',
        'desc' => __('开启后下方填写百度推送Token Key', 'ui_boxmoe_com'),
        'class' => 'small',
        'type' => "checkbox",
        'std' => false,
        );
    $options[] = array(
	    'id' => 'boxmoe_baidu_token',
	    'std' => '',
        'class' => 'small',
	    'type' => 'text'
        ); 

    $options[] = array(
        'name' => __('Bing推送开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_bing_submit_switch',
        'desc' => __('开启后下方填写Bing推送API Key', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );
    $options[] = array(
	    'id' => 'boxmoe_bing_api_key',
	    'std' => '',
        'class' => 'small',
	    'type' => 'text'
        );   

    $options[] = array(
        'name' => __('360推送开关', 'ui_boxmoe_com'),
        'desc' => __('开启后下方填写360推送API Key', 'ui_boxmoe_com'),
        'id' => 'boxmoe_360_submit_switch',
        'type' => "checkbox",
        'std' => false,
        );
    $options[] = array(
	    'id' => 'boxmoe_360_api_key',
	    'std' => '',
        'class' => 'small',
	    'type' => 'text'
        );
        
    $options[] = array(
        'name' => __('谷歌推送开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_google_submit_switch',
        'desc' => __('开启后下方填写谷歌推送API Key', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );
    $options[] = array(
	'id' => 'boxmoe_google_api_key',
	'std' => '',
        'class' => 'small',
	'group' => 'end',
	'type' => 'text'
        );

    $options[] = array(
        'group' => 'start',
        'group_title' => '网站头部设置',
        'name' => __('网站标题连接符', 'ui_boxmoe_com'),
        'id' => 'boxmoe_title_link',
        'type' => "text",
        'std' => '-',
        'class' => 'mini',
        'desc' => __('网站标题连接符，默认是"-"', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('网站关键词', 'ui_boxmoe_com'),
        'id' => 'boxmoe_keywords',
        'type' => "textarea",
        'settings' => array('rows' => 3),
        'std' => 'wordpress,boxmoe,lolimeow',
        'desc' => __('网站关键词，多个关键词用英文逗号隔开', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('网站描述', 'ui_boxmoe_com'),
        'id' => 'boxmoe_description',
        'type' => "textarea",
        'settings' => array('rows' => 3),
        'std' => '这个一个wordpress网站的描述',
        'desc' => __('网站描述', 'ui_boxmoe_com'),
        );

    $options[] = array(
	'name' => __('网站自动添加关键字和描述', 'ui_boxmoe_com'),
	'desc' => __('（开启后所有页面将自动使用主题配置的关键字和描述）', 'ui_boxmoe_com'),
	'id' => 'boxmoe_auto_keywords_description_switch',
	'type' => "checkbox",
	'std' => true,
	);    

    $options[] = array(
        'group' => 'end',
	'name' => __('自定义文章关键字和描述', 'ui_boxmoe_com'),
	'desc' => __('（开启后你需要在编辑文章的时候书写关键字和描述，如果为空，将自动使用主题配置的关键字和描述；开启这个必须开启上面的"网站自动添加关键字和描述"开关）', 'ui_boxmoe_com'),
	'id' => 'boxmoe_post_keywords_description_switch',
	'type' => "checkbox",
	'std' => false,
	);

    $options[] = array(
        'group' => 'start',
	'group_title' => 'GEO生成式搜索引擎优化设置「未来功能」',
        'name' => __('Meta标签开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_meta_switch',
        'desc' => __('开启后输出AI友好的Meta标签', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('结构化数据开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_structured_switch',
        'desc' => __('开启后输出AI友好的结构化数据', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('面包屑导航开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_breadcrumb_switch',
        'desc' => __('开启后输出AI友好的面包屑导航', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('FAQ结构化数据开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_faq_switch',
        'desc' => __('开启后输出AI友好的FAQ结构化数据', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('文章摘要开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_summary_switch',
        'desc' => __('开启后输出AI友好的文章摘要', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('内容质量评分开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_quality_switch',
        'desc' => __('开启后输出AI友好的内容质量评分', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('主题分类开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_topic_switch',
        'desc' => __('开启后输出AI友好的主题分类', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('相关内容开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_related_switch',
        'desc' => __('开启后输出AI友好的相关内容', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('多语言支持开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_language_switch',
        'desc' => __('开启后输出AI友好的多语言支持', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('移动端优化开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_mobile_switch',
        'desc' => __('开启后输出AI友好的移动端优化标签', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('性能优化开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_performance_switch',
        'desc' => __('开启后输出AI友好的性能优化标签', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('本地化优化开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_local_switch',
        'desc' => __('开启后输出AI友好的本地化优化标签', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('作者信息开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_author_switch',
        'desc' => __('开启后输出AI友好的作者信息', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('社交媒体信息开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_social_switch',
        'desc' => __('开启后输出AI友好的社交媒体信息', 'ui_boxmoe_com'),
        'type' => "checkbox",
        'std' => false,
        );

    $options[] = array(
        'name' => __('所在地区', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_region',
        'type' => "text",
        'std' => '',
        'class' => 'small',
        'desc' => __('网站所在地区', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('所在城市', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_city',
        'type' => "text",
        'std' => '',
        'class' => 'small',
        'desc' => __('网站所在城市', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('所在国家', 'ui_boxmoe_com'),
        'id' => 'boxmoe_ai_country',
        'type' => "text",
        'std' => '',
        'class' => 'small',
        'group' => 'end',
        'desc' => __('网站所在国家', 'ui_boxmoe_com'),
        );