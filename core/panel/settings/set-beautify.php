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

$shiroki_cursor_base = get_template_directory_uri() . '/assets/guangbiao/';

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
        'name' => __('鼠标点击特效 UI 风格', 'ui_boxmoe_com'),
        'id' => 'boxmoe_guangbiao_click_style',
        'std' => 'none',
        'type' => 'radio',
        'options' => array(
            'none' => __('关闭', 'ui_boxmoe_com'),
            'five_lines' => __('扇形线条', 'ui_boxmoe_com'),
            'firework_lines' => __('八彩烟花', 'ui_boxmoe_com'),
        ),
        'desc' => __('选择鼠标点击时的特效样式', 'ui_boxmoe_com'),
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
        'std' => $shiroki_cursor_base . 'Arrow.png',
        'desc' => __('上传默认状态下的光标图片「推荐尺寸 32x32，支持 PNG / AVIF / GIF 格式；AVIF 在不支持时会自动回退为 assets/guangbiao/Arrow.png」', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('文本输入光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_handwriting',
        'type' => "upload",
        'std' => $shiroki_cursor_base . 'Handwriting.png',
        'desc' => __('上传文本输入时的光标图片「支持 PNG / AVIF / GIF 格式；AVIF 在不支持时会自动回退为 Handwriting.png」', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('文本框选光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_ibeam',
        'type' => "upload",
        'std' => $shiroki_cursor_base . 'IBeam.png',
        'desc' => __('上传框选文本时的光标图片「支持 PNG / AVIF / GIF 格式；AVIF 在不支持时会自动回退为 IBeam.png」', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('加载中光标', 'ui_boxmoe_com'),
        'id' => 'boxmoe_cursor_appstarting',
        'type' => "upload",
        'std' => $shiroki_cursor_base . 'AppStarting.png',
        'desc' => __('上传页面加载资源时的光标图片「支持 PNG / AVIF / GIF 格式；AVIF 在不支持时会自动回退为 AppStarting.png」', 'ui_boxmoe_com'),
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

    // 💬 气泡提示设定
    $options[] = array(
        'group' => 'start',
        'group_title' => '气泡设定',
        'name' => __('气泡 UI 风格', 'ui_boxmoe_com'),
        'id' => 'boxmoe_bubble_style',
        'std' => 'normal',
        'type' => 'radio',
        'options' => array(
            'normal' => __('普通', 'ui_boxmoe_com'),
            'border' => __('漫画边框效果', 'ui_boxmoe_com'),
            'shadow' => __('阴影边框效果', 'ui_boxmoe_com'),
            'lines' => __('线条边框效果', 'ui_boxmoe_com'),
            'glass' => __('圆角拟态玻璃效果', 'ui_boxmoe_com'),
        ),
        'desc' => __('选择全站气泡提示的 UI 风格，选项与基础设置中的 UI风格布局边框 对应', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('搜索框提示文本', 'ui_boxmoe_com'),
        'id' => 'boxmoe_bubble_field_hint_msg',
        'type' => 'text',
        'std' => '探索',
        'desc' => __('鼠标悬停或聚焦顶部搜索框时，气泡提示的文案', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('输入框内容', 'ui_boxmoe_com'),
        'id' => 'boxmoe_bubble_empty_validation_msg',
        'type' => 'text',
        'std' => '请输入内容',
        'desc' => __('输入框留空提交时，气泡提示的文案', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'start',
        'group_title' => '后台配色设置',
        'name' => __('开启自定义后台配色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_custom_switch',
        'type' => 'checkbox',
        'std' => false,
        'desc' => __('开启后，下方颜色选择器会覆盖后台统一变量，关闭则恢复主题默认配色', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('按钮浅色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_pink_light',
        'type' => 'color',
        'std' => '#ffb6c1',
        'desc' => __('用于按钮悬停底、边框浅色与列表悬停', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('按钮中色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_pink_medium',
        'type' => 'color',
        'std' => '#ffc0cb',
        'desc' => __('用于主按钮渐变终点与替换类按钮', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('按钮深色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_pink_dark',
        'type' => 'color',
        'std' => '#ff69b4',
        'desc' => __('用于主按钮、聚焦边框与强调描边', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('按钮最深色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_pink_deep',
        'type' => 'color',
        'std' => '#ff1493',
        'desc' => __('用于主按钮悬停渐变与下拉箭头聚焦态', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('强调文字色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_pink_text',
        'type' => 'color',
        'std' => '#8b008b',
        'desc' => __('用于自定义下拉框文字等强调文案', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('信息色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_primary',
        'type' => 'color',
        'std' => '#63b3ed',
        'desc' => __('用于后台信息提示、主色标签与光晕', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('成功色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_success',
        'type' => 'color',
        'std' => '#68d391',
        'desc' => __('用于成功状态按钮与标签', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('警告色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_warning',
        'type' => 'color',
        'std' => '#fbbf24',
        'desc' => __('用于警告提示与待处理状态', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('危险色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_danger',
        'type' => 'color',
        'std' => '#f87171',
        'desc' => __('用于删除、错误与危险操作', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('辅助色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_purple',
        'type' => 'color',
        'std' => '#b794f4',
        'desc' => __('用于辅助标签与次要强调', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('浅灰', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_gray_light',
        'type' => 'color',
        'std' => '#f5f5f5',
        'desc' => __('用于卡片浅底与提示条背景', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('中灰', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_gray_medium',
        'type' => 'color',
        'std' => '#e0e0e0',
        'desc' => __('用于边框与分隔线', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('深灰', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_gray_dark',
        'type' => 'color',
        'std' => '#d0d0d0',
        'desc' => __('用于标题栏底与较深分隔', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('正文色', 'ui_boxmoe_com'),
        'id' => 'boxmoe_admin_color_text',
        'type' => 'color',
        'std' => '#333333',
        'desc' => __('用于后台正文与按钮文字', 'ui_boxmoe_com'),
        );
