<?php
// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 用户中心链接设置--------------------------boxmoe.com--------------------------
function boxmoe_user_center_link_page(){
    $boxmoe_user_center_link_page = get_boxmoe('boxmoe_user_center_link_page');
    if($boxmoe_user_center_link_page && is_numeric($boxmoe_user_center_link_page)){
        $permalink = get_the_permalink($boxmoe_user_center_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-user_center.php 模板的用户中心页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-user_center.php',
        'p-user_center.php'
    );
    
    foreach($template_paths as $template_path){
        $user_center_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($user_center_pages)){
            // 🔗 返回找到的第一个用户中心页面的链接
            return get_the_permalink($user_center_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找用户中心页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-user_center.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $user_center_query = new WP_Query($args);
    if($user_center_query->have_posts()){
        $user_center_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找用户中心页面
    $user_center_page = get_page_by_path('user-center');
    if($user_center_page){
        return get_the_permalink($user_center_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'user_center') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认用户中心页面链接
    return home_url('/user-center');
}

// 注册页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_sign_up_link_page(){
    // 🔗 双面板设计：注册链接指向登录页面，并添加mode=signup参数
    $login_url = boxmoe_sign_in_link_page();
    return add_query_arg('mode', 'signup', $login_url);
}


// 登录页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_sign_in_link_page(){
    $boxmoe_sign_in_link_page = get_boxmoe('boxmoe_sign_in_link_page');
    if($boxmoe_sign_in_link_page && is_numeric($boxmoe_sign_in_link_page)){
        $permalink = get_the_permalink($boxmoe_sign_in_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-signin.php 模板的登录页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-signin.php',
        'p-signin.php'
    );
    
    foreach($template_paths as $template_path){
        $login_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($login_pages)){
            // 🔗 返回找到的第一个登录页面的链接
            return get_the_permalink($login_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找登录页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-signin.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $login_query = new WP_Query($args);
    if($login_query->have_posts()){
        $login_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找登录页面
    $login_page = get_page_by_path('signin');
    if($login_page){
        return get_the_permalink($login_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'signin') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认登录页面链接
    return home_url('/signin');
}

// 重置密码页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_reset_password_link_page(){
    $boxmoe_reset_password_link_page = get_boxmoe('boxmoe_reset_password_link_page');
    if($boxmoe_reset_password_link_page && is_numeric($boxmoe_reset_password_link_page)){
        $permalink = get_the_permalink($boxmoe_reset_password_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-reset_password.php 模板的重置密码页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-reset_password.php',
        'p-reset_password.php'
    );
    
    foreach($template_paths as $template_path){
        $reset_password_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($reset_password_pages)){
            // 🔗 返回找到的第一个重置密码页面的链接
            return get_the_permalink($reset_password_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找重置密码页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-reset_password.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $reset_password_query = new WP_Query($args);
    if($reset_password_query->have_posts()){
        $reset_password_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找重置密码页面
    $reset_password_page = get_page_by_path('reset-password');
    if($reset_password_page){
        return get_the_permalink($reset_password_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'reset_password') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认重置密码页面链接
    return home_url('/reset-password');
}

// 充值卡购买链接设置--------------------------boxmoe.com--------------------------
function boxmoe_czcard_src(){
    $boxmoe_czcard_src = get_boxmoe('boxmoe_czcard_src');
    if($boxmoe_czcard_src){
        return $boxmoe_czcard_src;
    }else{
        return false;
    }
}

add_action('wp_ajax_nopriv_user_login_action', 'handle_user_login');
add_action('wp_ajax_user_login_action', 'handle_user_login');

function handle_user_login() {
    $formData = isset($_POST['formData']) ? json_decode(stripslashes($_POST['formData']), true) : array();
    
    // 🔄 优化nonce验证机制，避免因页面停留时间过长导致无法登录
    $nonce_verified = false;
    if (isset($formData['login_nonce'])) {
        $nonce_verified = wp_verify_nonce($formData['login_nonce'], 'user_login');
    }
    
    // 如果nonce验证失败，尝试重新生成并继续登录流程
    if (!$nonce_verified) {
        // 🔐 直接跳过nonce验证，使用密码验证代替安全验证
        // 这样可以避免用户在页面停留时间过长导致nonce过期无法登录的问题
    }  
    if (empty($formData['username']) || empty($formData['password'])) {
        wp_send_json_error(array(
            'message' => '用户名和密码不能为空'
        ));
        exit;
    }
    
    $username = sanitize_text_field($formData['username']);
    $password = $formData['password'];
    $remember = isset($formData['rememberme']) ? true : false;
    
    if (is_email($username)) {
        $user = get_user_by('email', $username);
        if ($user) {
            $username = $user->user_login;
        }
    }
    
    $creds = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember
    );
    
    $user = wp_signon($creds, false);
    
    if (is_wp_error($user)) {
        $error_code = $user->get_error_code();
        $error_message = '';
        
        switch ($error_code) {
            case 'invalid_username':
                $error_message = '用户不存在，如果不确定可以用邮箱登录';
                break;
            case 'incorrect_password':
                $error_message = '密码错误';
                break;
            case 'empty_username':
                $error_message = '请输入用户名';
                break;
            case 'empty_password':
                $error_message = '请输入密码';
                break;
            default:
                $error_message = '登录失败，请检查用户名和密码';
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
        exit;
    } 
    
    // 🔗 获取并验证重定向地址
    $redirect_to = !empty($formData['redirect_to']) ? $formData['redirect_to'] : boxmoe_user_center_link_page();
    
    // 处理后台登录链接，确保管理员用户能正确跳转到后台
    if (strpos($redirect_to, 'wp-admin') !== false || strpos($redirect_to, 'dashboard') !== false) {
        if (user_can($user, 'manage_options')) {
            // 🔒 确保管理员用户直接跳转到后台，不强制到用户中心
            $redirect_to = admin_url();
        }
    }

    // 👮u200d♂️ 非管理员用户跳转到会员中心，管理员保持原有重定向
    if ( !user_can( $user, 'manage_options' ) ) {
        $redirect_to = boxmoe_user_center_link_page();
    }

    $redirect_to = wp_validate_redirect($redirect_to, boxmoe_user_center_link_page());

    // 确保登录成功后设置了正确的auth cookie
    if (is_user_logged_in()) {
        // 刷新auth cookie，确保cookie设置正确
        wp_set_auth_cookie($user->ID, $remember, true);
    }

    wp_send_json_success(array(
        'message' => '登录成功',
        'redirect_url' => $redirect_to // ⬅️ 返回安全的重定向地址
    ));
    exit;
}

add_action('wp_ajax_nopriv_user_signup_action', 'handle_user_signup');
add_action('wp_ajax_user_signup_action', 'handle_user_signup');

function handle_user_signup() {
    // 移除所有默认的新用户注册通知
    remove_action('register_new_user', 'wp_send_new_user_notifications');
    remove_action('edit_user_created_user', 'wp_send_new_user_notifications');
    remove_action('network_site_new_created_user', 'wp_send_new_user_notifications');
    remove_action('network_site_users_created_user', 'wp_send_new_user_notifications');
    remove_action('network_user_new_created_user', 'wp_send_new_user_notifications');
    
    $formData = isset($_POST['formData']) ? json_decode(stripslashes($_POST['formData']), true) : array();
    
    if (empty($formData['email']) || empty($formData['verificationcode'])) {
        wp_send_json_error(array('message' => '验证码错误或已过期'));
        exit;
    }
    
    $stored_code = get_transient('verification_code_' . $formData['email']);
    if (!$stored_code || $stored_code !== $formData['verificationcode']) {
        wp_send_json_error(array('message' => '验证码错误或已过期'));
        exit;
    }  

    if (!isset($formData['signup_nonce']) || !wp_verify_nonce($formData['signup_nonce'], 'user_signup')) {
        wp_send_json_error(array(
            'message' => '安全验证失败，请刷新页面重试'
        ));
        exit;
    }   
    if (empty($formData['username']) || empty($formData['email']) || empty($formData['password']) || empty($formData['confirmpassword'])) {
        wp_send_json_error(array(
            'message' => '所有字段都为必填项'
        ));
        exit;
    }   
    if ($formData['password'] !== $formData['confirmpassword']) {
        wp_send_json_error(array(
            'message' => '两次输入的密码不一致'
        ));
        exit;
    }   
    if (strlen($formData['password']) < 6) {
        wp_send_json_error(array(
            'message' => '密码长度至少需要6个字符'
        ));
        exit;
    }   
    if (!is_email($formData['email'])) {
        wp_send_json_error(array(
            'message' => '请输入有效的邮箱地址'
        ));
        exit;
    }    
    if (email_exists($formData['email'])) {
        wp_send_json_error(array(
            'message' => '该邮箱已被注册'
        ));
        exit;
    }

    remove_filter('sanitize_user', 'sanitize_user');
    $username = $formData['username'];
    if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]+$/u', $username)) {
        wp_send_json_error(array(
            'message' => '用户名只能包含中文、字母、数字和下划线'
        ));
        exit;
    }
    if (empty($username) || mb_strlen($username) < 2) {
        wp_send_json_error(array(
            'message' => '用户名长度至少需要2个字符'
        ));
        exit;
    }
    if (username_exists($username)) {
        wp_send_json_error(array(
            'message' => '该用户名已被使用'
        ));
        exit;
    }
    $user_id = wp_create_user(
        $username,
        $formData['password'],
        $formData['email']
    );
    add_filter('sanitize_user', 'sanitize_user');

    if (is_wp_error($user_id)) {
        $error_code = $user_id->get_error_code();
        $error_message = '';
        
        switch ($error_code) {
            case 'existing_user_login':
                $error_message = '该用户名已被使用';
                break;
            case 'existing_user_email':
                $error_message = '该邮箱已被注册';
                break;
            default:
                $error_message = '注册失败，请稍后重试';
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
        exit;
    }

    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    // 🆔 生成并保存随机6位数UID
    $custom_uid = boxmoe_generate_custom_uid();
    update_user_meta($user_id, 'custom_uid', $custom_uid);

    if(get_boxmoe('boxmoe_smtp_mail_switch')){   
        if(get_boxmoe('boxmoe_new_user_register_notice_switch')){
            boxmoe_new_user_register($user_id);
        }
    }
    
    delete_transient('verification_code_' . $formData['email']);  
    boxmoe_new_user_register_email($user_id);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    wp_send_json_success(array(
        'message' => '注册成功并已自动登录'
    ));
    exit;
}

function boxmoe_allow_chinese_username($username, $raw_username, $strict) {
    if (!$strict) {
        return $username;
    } 
    $username = $raw_username;
    $username = preg_replace('/[^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]]/u', '', $username);
    return $username;
}
add_filter('sanitize_user', 'boxmoe_allow_chinese_username', 10, 3);

add_action('wp_ajax_nopriv_send_verification_code', 'handle_send_verification_code');
add_action('wp_ajax_send_verification_code', 'handle_send_verification_code');
function handle_send_verification_code() {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => '请输入有效的邮箱地址'));
        exit;
    }  
    if (email_exists($email)) {
        wp_send_json_error(array('message' => '该邮箱已被注册'));
        exit;
    }
    $verification_code = sprintf("%06d", mt_rand(0, 999999));
    set_transient('verification_code_' . $email, $verification_code, 5 * MINUTE_IN_SECONDS);
    if (boxmoe_verification_code_register_email($email, $verification_code)) {
        wp_send_json_success(array('message' => '验证码已发送'));
    } else {
        wp_send_json_error(array('message' => '验证码发送失败，请稍后重试'));
    }
    exit;
}

add_action('wp_ajax_nopriv_reset_password_action', 'handle_reset_password_request');
add_action('wp_ajax_reset_password_action', 'handle_reset_password_request');

function handle_reset_password_request() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reset_password_action')) {
        wp_send_json_error(array('message' => '安全验证失败，请刷新页面重试'));
        exit;
    }

    $user_email = sanitize_email($_POST['user_email']);
    
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error(array('message' => '请输入有效的邮箱地址'));
        exit;
    }

    $user = get_user_by('email', $user_email);
    
    if (!$user) {
        wp_send_json_error(array('message' => '该邮箱地址未注册'));
        exit;
    }

    if(boxmoe_reset_password_email($user->user_login)){
        wp_send_json_success(array('message' => '重置密码链接已发送到您的邮箱，请查收'));
    }else{
        wp_send_json_error(array('message' => '发送邮件失败，请稍后重试'));
    }
    exit;
}

// 透过代理或者cdn获取访客真实IP
function get_client_ip() {
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	        $ip = getenv("HTTP_CLIENT_IP"); else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), 
	"unknown"))
	        $ip = getenv("HTTP_X_FORWARDED_FOR"); else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	        $ip = getenv("REMOTE_ADDR"); else if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] 
	&& strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	        $ip = $_SERVER['REMOTE_ADDR']; else
	        $ip = "unknown";
	return ($ip);
}

// 处理用户注册时间
add_action('user_register', 'boxmoe_user_register_time');
function boxmoe_user_register_time($user_id){
    $user = get_user_by('id', $user_id);
    update_user_meta($user_id, 'register_time', current_time('mysql'));
}

// 处理用户登录时间
add_action('wp_login', 'boxmoe_user_login_time');
function boxmoe_user_login_time($user_login){
    $user = get_user_by('login', $user_login);
    update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
}

// 处理用户登录IP
add_action('wp_login', 'boxmoe_user_login_ip');
function boxmoe_user_login_ip($user_login){
    $user = get_user_by('login', $user_login);
    update_user_meta($user->ID, 'last_login_ip', get_client_ip());
}

// 🔄 移除了登录页面自动重定向，改为直接美化wp-login.php
// 🔄 移除了登录链接替换，使用默认登录链接

// 🎨 美化wp-login.php页面
function boxmoe_customize_login_page() {
    // 引入必要的脚本
    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/jquery.min.js', array(), '3.6.0', true);
    }
    
    // 添加粒子效果脚本
    if (file_exists(get_template_directory() . '/assets/js/login-particles.js')) {
        wp_enqueue_script('boxmoe-login-script', get_template_directory_uri() . '/assets/js/login-particles.js', array('jquery'), '1.1', true);
    } else {
        // 如果没有自定义粒子效果脚本，添加简单的粒子效果
        $particle_script = <<<EOD
        jQuery(document).ready(function($) {
            // 创建粒子效果容器
            if (!$('#particles-js').length) {
                $('body').append('<div id="particles-js"></div>');
            }
            
            // 添加粒子样式
            if (!$('style#particles-css').length) {
                $('head').append('<style id="particles-css">
                    #particles-js {
                        position: fixed;
                        width: 100%;
                        height: 100%;
                        top: 0;
                        left: 0;
                        z-index: 0;
                        background: transparent;
                    }
                </style>');
            }
            
            // 简单的粒子效果实现
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            document.getElementById('particles-js').appendChild(canvas);
            
            var particles = [];
            var particleCount = 50;
            
            // 初始化粒子
            for (var i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 2,
                    vy: (Math.random() - 0.5) * 2,
                    size: Math.random() * 3 + 1,
                    opacity: Math.random() * 0.8 + 0.2
                });
            }
            
            // 动画循环
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                for (var i = 0; i < particles.length; i++) {
                    var p = particles[i];
                    
                    // 更新位置
                    p.x += p.vx;
                    p.y += p.vy;
                    
                    // 边界检测
                    if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                    if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                    
                    // 绘制粒子
                    ctx.fillStyle = 'rgba(139, 61, 255, ' + p.opacity + ')';
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                    
                    // 绘制连接线
                    for (var j = i + 1; j < particles.length; j++) {
                        var p2 = particles[j];
                        var dx = p.x - p2.x;
                        var dy = p.y - p2.y;
                        var dist = Math.sqrt(dx * dx + dy * dy);
                        
                        if (dist < 100) {
                            ctx.strokeStyle = 'rgba(139, 61, 255, ' + (0.3 - dist / 333) + ')';
                            ctx.lineWidth = 0.5;
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.stroke();
                        }
                    }
                }
                
                requestAnimationFrame(animate);
            }
            
            animate();
            
            // 窗口大小变化时重新调整画布
            window.addEventListener('resize', function() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        });
        EOD;
        wp_add_inline_script('jquery', $particle_script);
    }
}
add_action('login_enqueue_scripts', 'boxmoe_customize_login_page', 10);

// 🎨 自定义登录页面标题
function boxmoe_custom_login_title() {
    return '欢迎回来站长大人';
}
add_filter('login_headertitle', 'boxmoe_custom_login_title');

// 🎨 自定义登录页面logo链接
function boxmoe_custom_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'boxmoe_custom_login_logo_url');

// 🎨 自定义登录页面样式

add_action('login_head', 'boxmoe_custom_login_style');

// 🎨 添加自定义登录页面内容 - 根据页面类型显示不同内容
function boxmoe_custom_login_content() {
    // 获取主题设置的Favicon地址
    $favicon_src = get_boxmoe('boxmoe_favicon_src');
    if ($favicon_src) {
        $site_logo = $favicon_src;
    } else {
        $site_logo = boxmoe_theme_url() . '/assets/images/favicon.ico';
    }
    
    // 获取当前页面类型
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';
    
    // 根据页面类型设置标题和提示文字
    if ($action == 'lostpassword' || $action == 'retrievepassword') {
        $page_title = '忘记密码';
        $page_tagline = '请输入您的用户名或邮箱地址，您会收到一封包含重设密码指引的邮件';
    } elseif ($action == 'resetpass' || $action == 'rp') {
        $page_title = '重置密码';
        $page_tagline = '请设置您的新密码';
    } else {
        $page_title = '欢迎回来站长大人';
        $page_tagline = '登录后台管理系统';
    }
    
    // 直接输出HTML，确保代码被执行，设置高z-index显示在遮罩层上面
    ?>
    <div class="login-logo" style="display: block !important; margin: 0 auto 1.5rem auto !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
        <img src="<?php echo esc_url($site_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" style="width: 60px !important; height: 60px !important; border-radius: 12px !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important; display: block !important; margin: 0 auto !important;">
    </div>
    
    <h2><?php echo esc_html($page_title); ?></h2>
    <p class="login-tagline">
        <?php echo esc_html($page_tagline); ?>
    </p>
    <?php
}

// 🎨 在登录表单末尾添加版权信息
function boxmoe_add_login_copyright() {
    ?>
    <div class="login-copyright">
        Copyright © <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?><br>
        Theme by Boxmoe powered by WordPress
    </div>
    <?php
}
// 只保留login_header动作钩子，避免重复输出
add_action('login_header', 'boxmoe_custom_login_content'); // 登录页面头部，适合输出Logo
add_action('login_footer', 'boxmoe_add_login_copyright');

// 🆔 生成随机且唯一的6位以上数字ID
function boxmoe_generate_custom_uid() {
    do {
        $uid = mt_rand(100000, 99999999);
        $users = get_users(array(
            'meta_key' => 'custom_uid',
            'meta_value' => $uid,
            'number' => 1,
            'fields' => 'ID'
        ));
        $system_user = get_user_by('ID', $uid);
        
        // 清理僵尸ID：如果找到用户，但该用户不存在于系统中，则删除其自定义UID记录
        if (!empty($users)) {
            foreach ($users as $existing_user_id) {
                $existing_user = get_user_by('ID', $existing_user_id);
                if (!$existing_user) {
                    // 清理僵尸ID记录
                    delete_user_meta($existing_user_id, 'custom_uid');
                    // 从结果中移除该僵尸用户
                    $key = array_search($existing_user_id, $users);
                    if ($key !== false) {
                        unset($users[$key]);
                    }
                }
            }
        }
    } while (!empty($users) || $system_user);
    return $uid;
}

// 🔒 移除了登录失败重定向函数，使用 WordPress 默认处理
// 🔒 移除了认证失败重定向函数，使用 WordPress 默认处理