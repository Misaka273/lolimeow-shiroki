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
    'name' => __('SMTP邮局设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-email',
    'type' => 'heading'); 
    $options[] = array(
        'name' => __('SMTP设置说明', 'ui_boxmoe_com'), 
        'id' => 'boxmoe_smtp_info',
        'desc' => __(
         '<p>请在这里配置您的SMTP邮件发送设置，用于发送评论通知、注册通知等邮件</p>
        ', 'ui_boxmoe_com'),
        'type' => 'info');
    
    $options[] = array(
        'name' => __('启用SMTP发件系统', 'ui_boxmoe_com'),
        'id' => 'boxmoe_smtp_mail_switch',
        'type' => 'checkbox',
        'std' => false,
        'desc' => __('启用后，系统将使用SMTP服务器发送邮件', 'ui_boxmoe_com'),
    );

    $options[] = array(
        'group' => 'start',
        'group_title' => 'SMTP服务器配置',
        'name' => __('SMTP服务器地址', 'ui_boxmoe_com'),
        'id' => 'boxmoe_smtp_host',
        'type' => 'text',
        'std' => '',
        'desc' => __('请输入SMTP服务器地址，如smtp.qq.com', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('SMTP服务器端口', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_port',
            'type' => 'text',
            'std' => '465',
            'desc' => __('请输入SMTP服务器端口，如465（SSL）或25（非SSL）', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('SMTP加密方式', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_secure',
            'type' => 'radio',
            'std' => 'ssl',
            'options' => array(
                'ssl' => __('SSL', 'ui_boxmoe_com'),
                'tls' => __('TLS', 'ui_boxmoe_com'),
                'none' => __('无', 'ui_boxmoe_com'),
            ),
            'desc' => __('请选择SMTP加密方式', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('SMTP邮箱账号', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_user',
            'type' => 'text',
            'std' => '',
            'desc' => __('请输入SMTP邮箱账号', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('SMTP邮箱密码', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_pass',
            'type' => 'password',
            'std' => '',
            'desc' => __('请输入SMTP邮箱密码或授权码', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('发件人邮箱', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_from',
            'type' => 'text',
            'std' => '',
            'desc' => __('请输入发件人邮箱地址', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'group' => 'end',
            'name' => __('发件人名称', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_name',
            'type' => 'text',
            'std' => '',
            'desc' => __('请输入发件人名称', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'start',
        'group_title' => 'SMTP发件消息通知设置开关',
        'name' => __('新评论通知博主', 'ui_boxmoe_com'),
        'id' => 'boxmoe_new_comment_notice_switch',
        'type' => 'checkbox',
        'std' => false,
        'desc' => __('若开启则新评论通知将使用SMTP发件系统', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'name' => __('新会员注册通知博主', 'ui_boxmoe_com'),
            'id' => 'boxmoe_new_user_register_notice_switch',
            'type' => 'checkbox',
            'std' => false,
            'desc' => __('若开启则新会员注册通知将使用SMTP发件系统', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'group' => 'end',
            'name' => __('消息接受邮箱', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_receive_email',
            'type' => 'text',
            'std' => '',
            'desc' => __('用于接收新评论和新会员注册通知的邮箱地址，留空则使用系统管理员邮箱', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'group' => 'start',
        'group_title' => 'SMTP测试',
        'name' => __('测试收件邮箱', 'ui_boxmoe_com'),
        'id' => 'boxmoe_smtp_test_email',
        'type' => 'text',
        'std' => '',
        'desc' => __('请输入用于测试的收件邮箱地址', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'group' => 'end',
            'name' => __('发送测试邮件', 'ui_boxmoe_com'),
            'id' => 'boxmoe_smtp_test_send',
            'type' => 'button',
            'std' => '发送测试邮件',
            'desc' => __('点击发送测试邮件，验证SMTP设置是否正确', 'ui_boxmoe_com'),
            'button_text' => '发送测试邮件',
        );
