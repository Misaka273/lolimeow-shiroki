<?php
if(!defined('ABSPATH')){exit;}
$options[] = array(
    'name' => __('字体设置','ui_boxmoe_com'),
    'type' => 'heading',
    'icon' => 'dashicons-editor-textcolor'
);


$options[] = array(
    'group' => 'start',
    'group_title' => '自定义字体',
    'name' => __('启用自定义字体','ui_boxmoe_com'),
    'id' => 'boxmoe_custom_font_switch',
    'type' => 'checkbox',
    'std' => 0,
    'desc' => __('开启后自定义字体才会生效，并会等待字体就绪后再显示页面，避免字体闪烁','ui_boxmoe_com')
);

$options[] = array(
    'name' => __('字体列表','ui_boxmoe_com'),
    'id'   => 'boxmoe_fonts',
    'type' => 'fonts_table',
    'std'  => array(),
    'group' => 'end',
);

// 站点默认字体（从自定义字体列表读取）
$font_options = array();
$fonts_saved = get_boxmoe('boxmoe_fonts');
if ( is_array($fonts_saved) ) {
    foreach ($fonts_saved as $f) {
        if (!empty($f['name'])) {
            $font_options[$f['name']] = $f['name'];
        }
    }
}
$options[] = array(
    'name' => __('站点默认字体','ui_boxmoe_com'),
    'id'   => 'boxmoe_default_font',
    'type' => 'select',
    'options' => array_merge(array('default' => __('默认字体','ui_boxmoe_com')), $font_options),
    'std'  => '',
    'desc' => __('选择后将应用为全站默认字体（需启用自定义字体）','ui_boxmoe_com'),
    'append_checkbox' => array(
        'id'   => 'boxmoe_admin_font_sync',
        'std'  => 0,
        'desc' => __('开启后后台页面将同步使用上方选择的站点默认字体（需启用自定义字体并选择字体）','ui_boxmoe_com')
    )
);

// 欢迎语独立字体设置
$options[] = array(
    'name' => __('欢迎语独立字体','ui_boxmoe_com'),
    'id'   => 'boxmoe_welcome_font',
    'type' => 'select',
    'options' => array_merge(array('default' => __('使用站点默认字体','ui_boxmoe_com')), $font_options),
    'std'  => '',
    'desc' => __('选择后将仅应用于Banner欢迎语（需启用自定义字体）','ui_boxmoe_com')
);
