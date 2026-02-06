<?php
/**
 * @link https://gl.baimu.live
 * @package l白木
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

$options[] = array(
    'name' => __('站点美化', 'ui_boxmoe_com'),
    'icon' => 'dashicons-art',
    'type' => 'heading');

    // ✨ 鼠标特效和光标设置
    $options[] = array(
        'group' => 'start',
        'group_title' => '鼠标特效设置',
        'name' => __('开启鼠标移动特效', 'ui_boxmoe_com'),
        'id' => 'boxmoe_guangbiao_tx_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启，鼠标在页面上移动时会产生彩色流光星星特效，为站点增添视觉效果', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('开启自定义鼠标光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_custom_cursor_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启，将使用自定义的鼠标光标样式替换系统默认光标', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('默认光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_arrow',
        'type' => "upload",
        'std' => '',
        'desc' => __('上传默认状态下的光标图片「推荐尺寸 32x32，PNG格式」', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('文本输入光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_handwriting',
        'type' => "upload",
        'std' => '',
        'desc' => __('上传文本输入时的光标图片「如点击输入框时显示」', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('文本框选光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_ibeam',
        'type' => "upload",
        'std' => '',
        'desc' => __('上传框选文本时的光标图片', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('加载中光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_appstarting',
        'type' => "upload",
        'std' => '',
        'desc' => __('上传页面加载资源时的光标图片', 'ui_boxmoe_com'),
        );

    // 🌟 LOGO呼吸动画设置
    $options[] = array(
        'group' => 'start',
        'group_title' => 'LOGO动效设置',
        'name' => __('开启LOGO呼吸动画', 'ui_boxmoe_com'),
        'id' => 'boxmoe_logo_breathe_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启，网站LOGO将每8秒轻微放大缩小一次，呈现呼吸效果', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('LOGO动画周期', 'ui_boxmoe_com'),
        'id' => 'boxmoe_logo_breathe_duration',
        'type' => "text",
        'std' => '8',
        'desc' => __('设置LOGO呼吸动画的周期「单位：秒，默认8秒」', 'ui_boxmoe_com'),
        );
