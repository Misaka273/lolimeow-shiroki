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
    'name' => __('用户设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-admin-users',
    'type' => 'heading'); 

    $options[] = array(
        'name' => __('开启导航会员注册链接', 'ui_boxmoe_com'),
        'id' => 'boxmoe_sign_in_link_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启则导航栏将显示会员注册链接', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('用户登录注册页面背景图', 'ui_boxmoe_com'),
        'id' => 'boxmoe_user_login_bg',
        'type' => 'text',
        'std' => '',
        'desc' => __('（用户登录注册页面背景图，填写图片URL，支持API）', 'ui_boxmoe_com'),
        );    
    $options[] = array(
        'group' => 'start',
        'group_title' => '用户中心链接设置',
        'name' => __('用户中心选择', 'ui_boxmoe_com'),
        'id' => 'boxmoe_user_center_link_page',
        'type' => "select",
        'std' => 'user_center',
        'options' => $options_pages
        );
    $options[] = array(
        'name' => __('注册页面选择', 'ui_boxmoe_com'),
        'id' => 'boxmoe_sign_up_link_page',
        'type' => "select",
        'std' => 'user_center',
        'options' => $options_pages

        );
    $options[] = array(
        'name' => __('登录页面选择', 'ui_boxmoe_com'),
        'id' => 'boxmoe_sign_in_link_page',
        'type' => "select",
        'std' => 'user_center',
        'options' => $options_pages
        );
    $options[] = array(
        'name' => __('重置密码页面选择', 'ui_boxmoe_com'),
        'id' => 'boxmoe_reset_password_link_page',
        'type' => "select",
        'std' => 'user_center',
        'options' => $options_pages
        );
    $options[] = array(
        'group' => 'end',
        'name' => __('前端充值卡购买链接', 'ui_boxmoe_com'), 
        'id' => 'boxmoe_czcard_src',
        'std' => '',
        'desc' => __('（前端用户充值中心，充值卡购买链接）', 'ui_boxmoe_com'),
        'type' => 'text'); 
        
// 人机验证设置 由初叶🍂www.chuyel.top提供集成
$options[] = array(
    'name' => '人机验证设置',
    'desc' => '以下设置用于验证码功能，可以有效防止机器人和恶意注册/登录',
    'type' => 'heading'
);

    $options[] = array(
        'name' => __('验证设置说明', 'ui_boxmoe_com'), 
        'id' => 'boxmoe_msg_notice_info',
        'desc' => __('
         <p>1.Cloudflare Turnstile需要前往<span style="color: #0073aa;cursor: pointer;" onclick="window.open(\'https://dash.cloudflare.com/?to=/:account/turnstile\')">Cloudflare</span>进行添加</p>
         <p>2.小组件模式选择 托管/非交互式 之一即可</p>
         <p>3.此模块由<span style="color: #0073aa;cursor: pointer;" onclick="window.open(\'https://www.chuyel.top\')">初叶🍂竹叶</span>进行构建集成与Bug修复</p>
        ', 'ui_boxmoe_com'),
        'type' => 'info');

$options[] = array(
    'name' => '启用验证码功能',
    'desc' => '启用验证码功能，防止恶意注册和登录',
    'id' => 'captcha_enabled',
    'std' => '0',
    'type' => 'checkbox'
);

$options[] = array(
    'name' => '验证码类型',
    'desc' => '选择验证码类型。纯数字、纯字母、数字+字母为本地验证码，Cloudflare Turnstile需要配置API密钥',
    'id' => 'captcha_type',
    'std' => 'normal',
    'type' => 'select',
    'options' => array(
        'simple' => '纯数字验证码',
        'letter' => '纯字母验证码', 
        'normal' => '数字+字母验证码',
        'cloudflare' => 'Cloudflare Turnstile'
    )
);

$options[] = array(
    'name' => 'Cloudflare 站点密钥',
    'desc' => 'Cloudflare Turnstile 站点密钥。<br>在<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" style="color:#007cba;">Cloudflare控制台</a>创建验证码后获取',
    'id' => 'captcha_cloudflare_site_key',
    'std' => '',
    'type' => 'text'
);

$options[] = array(
    'name' => 'Cloudflare 密钥',
    'desc' => 'Cloudflare Turnstile 密钥。请妥善保管',
    'id' => 'captcha_cloudflare_secret_key',
    'std' => '',
    'type' => 'text'
);

$options[] = array(
    'name' => '验证码长度',
    'desc' => '验证码字符长度（4-8个字符）',
    'id' => 'captcha_length',
    'std' => '6',
    'type' => 'text',
    'validate' => 'numeric'
);

$options[] = array(
    'name' => '验证码有效期',
    'desc' => '验证码有效期（秒），默认300秒（5分钟）',
    'id' => 'captcha_expiry',
    'std' => '300',
    'type' => 'text',
    'validate' => 'numeric'
);

$options[] = array(
    'name' => '启用登录验证码',
    'desc' => '在登录页面启用验证码',
    'id' => 'captcha_login_enabled',
    'std' => '0',
    'type' => 'checkbox'
);

$options[] = array(
    'name' => '启用注册验证码',
    'desc' => '在注册页面启用验证码',
    'id' => 'captcha_register_enabled',
    'std' => '0',
    'type' => 'checkbox'
);

// 添加JavaScript来控制字段显示/隐藏
add_action('admin_footer', function() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // 监听验证码类型变化
        $('#section-captcha_type select').change(function() {
            toggleCloudflareFields();
        });
        
        // 初始化时根据验证码类型显示/隐藏Cloudflare字段
        toggleCloudflareFields();
        
        function toggleCloudflareFields() {
            var captchaType = $('#section-captcha_type select').val();
            
            if (captchaType === 'cloudflare') {
                $('#section-captcha_cloudflare_site_key').show();
                $('#section-captcha_cloudflare_secret_key').show();
            } else {
                $('#section-captcha_cloudflare_site_key').hide();
                $('#section-captcha_cloudflare_secret_key').hide();
            }
        }
    });
    </script>
    <style type="text/css">
    /* 隐藏标题的边框 */
    #heading-captcha_enabled {
        border-bottom: 2px solid #007cba;
        margin-top: 20px;
    }
    
    /* 设置选项之间的间距 */
    #section-captcha_enabled,
    #section-captcha_type,
    #section-captcha_cloudflare_site_key,
    #section-captcha_cloudflare_secret_key,
    #section-captcha_length,
    #section-captcha_expiry,
    #section-captcha_login_enabled,
    #section-captcha_register_enabled {
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    
    /* 修改选中状态的样式为FFE92C */
    #section-captcha_enabled input:checked,
    #section-captcha_login_enabled input:checked,
    #section-captcha_register_enabled input:checked {
        background-color: #FFE92C !important;
        border-color: #FFE92C !important;
    }
    
    /* 为开关添加悬停效果 */
    #section-captcha_enabled input:hover,
    #section-captcha_login_enabled input:hover,
    #section-captcha_register_enabled input:hover {
        border-color: #FFE92C;
    }
    </style>
    <?php
});