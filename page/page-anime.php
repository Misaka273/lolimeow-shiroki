<?php
/**
 * Template Name: 追番页面
 * @link https://gl.baimu.live
 * @package lolimeow
 */

// gl.baimu.live===安全设置=阻止直接访问主题文件
if (!defined('ABSPATH')) {
    echo 'Look your sister';
    exit;
}

// 检查页面是否受密码保护
if (post_password_required()) {
    get_header();
    echo get_the_password_form();
    get_footer();
    exit;
}

get_header();
get_template_part('page/template/anime-page');
get_footer();
?>
