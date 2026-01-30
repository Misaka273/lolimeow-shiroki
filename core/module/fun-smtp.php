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
// 添加管理菜单
// 菜单放置位置
add_action('admin_menu', 'boxmoe_smtp_menu', 99);

// 添加SMTP设置菜单
function boxmoe_smtp_menu() {
    // 将SMTP设置添加为盒子萌主题设置的子菜单
    add_submenu_page(
        'boxmoe_options', // ⬅️ 父菜单slug（盒子萌主题设置）
        'SMTP邮局设置', // ⬅️ 页面标题
        'SMTP邮局设置', // ⬅️ 菜单标题
        'manage_options', // ⬅️ 权限
        'boxmoe-smtp-settings', // ⬅️ 菜单slug
        'boxmoe_smtp_settings_page', // ⬅️ 回调函数
    );

}

// SMTP设置页面内容
function boxmoe_smtp_settings_page() {
    if(isset($_POST['boxmoe_smtp_save'])) {
        update_option('boxmoe_smtp_host', sanitize_text_field($_POST['smtp_host']));
        update_option('boxmoe_smtp_port', sanitize_text_field($_POST['smtp_port']));
        update_option('boxmoe_smtp_user', sanitize_text_field($_POST['smtp_user']));
        update_option('boxmoe_smtp_pass', sanitize_text_field($_POST['smtp_pass']));
        update_option('boxmoe_smtp_from', sanitize_text_field($_POST['smtp_from']));
        update_option('boxmoe_smtp_name', sanitize_text_field($_POST['smtp_name']));
        update_option('boxmoe_smtp_secure', sanitize_text_field($_POST['smtp_secure']));
        // 保存消息接受邮箱设置
        update_option('boxmoe_smtp_receive_email', sanitize_text_field($_POST['smtp_receive_email']));
        echo '<div class="updated"><p>设置已保存！</p></div>';
    }

    // 添加测试邮件发送功能
    if(isset($_POST['boxmoe_smtp_test'])) {
        // 检查SMTP开关状态
        $smtp_switch = get_boxmoe('boxmoe_smtp_mail_switch');
        
        if (!$smtp_switch) {
            echo '<div class="error"><p>测试邮件发送失败！SMTP发件系统开关未启用，请先在通知设置中启用。</p></div>';
            echo '<p><a href="admin.php?page=boxmoe-settings&tab=notice" class="button">前往启用SMTP开关</a></p>';
            return;
        }
        
        // 获取SMTP配置
        $from = get_option('boxmoe_smtp_from');
        $name = get_option('boxmoe_smtp_name');
        
        $to = sanitize_email($_POST['test_email']);
        $subject = '测试邮件 - ' . get_bloginfo('name');
        $message = '这是一封测试邮件，如果您收到这封邮件，说明SMTP配置正确。';
        // 确保使用配置的发件人地址
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $name . ' <' . $from . '>',
            'Reply-To: ' . $name . ' <' . $from . '>'
        );
        
        $result = wp_mail($to, $subject, $message, $headers);
        
        if($result) {
            echo '<div class="updated"><p>测试邮件发送成功！请检查收件箱和垃圾邮件文件夹。</p></div>';
        } else {
            echo '<div class="error"><p>测试邮件发送失败，请检查SMTP配置。</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h2>SMTP邮局设置</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>SMTP服务器</th>
                    <td><input type="text" name="smtp_host" value="<?php echo esc_attr(get_option('boxmoe_smtp_host')); ?>" class="regular-text" placeholder="例如: smtp.qq.com"></td>
                </tr>
                <tr>
                    <th>SMTP端口</th>
                    <td><input type="text" name="smtp_port" value="<?php echo esc_attr(get_option('boxmoe_smtp_port')); ?>" class="regular-text" placeholder="例如: 465 (SSL) 或 587 (TLS)"></td>
                </tr>
                <tr>
                    <th>加密方式</th>
                    <td>
                        <select name="smtp_secure" class="regular-text">
                            <option value="" <?php selected(get_option('boxmoe_smtp_secure'), ''); ?>>无加密</option>
                            <option value="ssl" <?php selected(get_option('boxmoe_smtp_secure'), 'ssl'); ?>>SSL</option>
                            <option value="tls" <?php selected(get_option('boxmoe_smtp_secure'), 'tls'); ?>>TLS</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>邮箱账号</th>
                    <td><input type="text" name="smtp_user" value="<?php echo esc_attr(get_option('boxmoe_smtp_user')); ?>" class="regular-text" placeholder="您的邮箱地址"></td>
                </tr>
                <tr>
                    <th>邮箱密码</th>
                    <td><input type="password" name="smtp_pass" value="<?php echo esc_attr(get_option('boxmoe_smtp_pass')); ?>" class="regular-text" placeholder="SMTP授权码或密码"></td>
                </tr>
                <tr>
                    <th>发件人邮箱</th>
                    <td><input type="text" name="smtp_from" value="<?php echo esc_attr(get_option('boxmoe_smtp_from')); ?>" class="regular-text" placeholder="发件人邮箱地址"></td>
                </tr>
                <tr>
                    <th>发件人名称</th>
                    <td><input type="text" name="smtp_name" value="<?php echo esc_attr(get_option('boxmoe_smtp_name')); ?>" class="regular-text" placeholder="发件人显示名称"></td>
                </tr>
                <tr>
                    <th>消息接受邮箱</th>
                    <td><input type="text" name="smtp_receive_email" value="<?php echo esc_attr(get_option('boxmoe_smtp_receive_email')); ?>" class="regular-text" placeholder="用于接收通知的邮箱地址，留空则使用默认设置"></td>
                </tr>
        
            </table>
            <p class="submit">
                <input type="submit" name="boxmoe_smtp_save" class="button-primary" value="保存设置">
            </p>
        </form>

        

        <!-- 添加测试邮件表单 -->
        <h3>测试邮件发送</h3>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>测试收件邮箱</th>
                    <td>
                        <input type="email" name="test_email" class="regular-text" required placeholder="请输入用于测试的收件邮箱">
                        <p class="description">请输入用于测试的收件邮箱地址</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="boxmoe_smtp_test" class="button-secondary" value="发送测试邮件">
            </p>
        </form>
    </div>
    <?php
}

// 配置WordPress邮件发送
$smtp_switch = get_boxmoe('boxmoe_smtp_mail_switch');

if($smtp_switch){
    // 使用高优先级确保我们的配置不被其他插件覆盖
    add_action('phpmailer_init', 'boxmoe_smtp_config', 9999);
    function boxmoe_smtp_config($phpmailer) {
        // 获取SMTP配置
        $host = get_option('boxmoe_smtp_host');
        $port = get_option('boxmoe_smtp_port');
        $user = get_option('boxmoe_smtp_user');
        $pass = get_option('boxmoe_smtp_pass');
        $from = get_option('boxmoe_smtp_from');
        $name = get_option('boxmoe_smtp_name');
        $secure = get_option('boxmoe_smtp_secure', 'ssl');
        
        // 确保所有必要参数都已设置
        if (empty($host) || empty($port) || empty($user) || empty($pass) || empty($from)) {
            return;
        }
        
        // 强制重置所有相关配置
        $phpmailer->Mailer = 'smtp'; // 先设置为SMTP，确保后续设置生效
        
        // 启用SMTP模式
        $phpmailer->isSMTP();
        
        // 基本配置 - 强制覆盖所有现有设置
        $phpmailer->Host = $host;
        $phpmailer->Port = $port;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $user;
        $phpmailer->Password = $pass;
        
        // 强制使用配置的发件人地址，覆盖所有其他设置
        $phpmailer->setFrom($from, $name, false); // false表示不允许覆盖
        $phpmailer->From = $from;
        $phpmailer->FromName = $name;
        
        // 确保Return-Path与From一致
        $phpmailer->Sender = $from;
        
        // 设置安全协议
        switch ($secure) {
            case 'tls':
                // 使用字符串值兼容所有PHPMailer版本
                $phpmailer->SMTPSecure = 'tls';
                $phpmailer->SMTPAutoTLS = true;
                break;
            case 'ssl':
                $phpmailer->SMTPSecure = 'ssl';
                $phpmailer->SMTPAutoTLS = false;
                break;
            default:
                $phpmailer->SMTPSecure = false;
                $phpmailer->SMTPAutoTLS = false;
                break;
        }
        
        // 添加额外配置以提高可靠性
        $phpmailer->Timeout = 30; // 设置超时时间
        $phpmailer->SMTPKeepAlive = false;
        $phpmailer->CharSet = 'UTF-8';
        $phpmailer->Encoding = 'base64'; // 使用base64编码，提高兼容性
        

        
        // 确保使用正确的邮件格式
        $phpmailer->isHTML(true);
        $phpmailer->WordWrap = 70;
        
        // 最后再次确认使用SMTP，确保不被覆盖
        $phpmailer->Mailer = 'smtp';
    }
    
    // 添加直接测试SMTP连接的功能
    add_action('admin_post_boxmoe_test_smtp_connection', 'boxmoe_test_smtp_connection');
    function boxmoe_test_smtp_connection() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限访问此页面');
        }
        
        // 获取SMTP配置
        $host = get_option('boxmoe_smtp_host');
        $port = get_option('boxmoe_smtp_port');
        $user = get_option('boxmoe_smtp_user');
        $pass = get_option('boxmoe_smtp_pass');
        $secure = get_option('boxmoe_smtp_secure', 'ssl');
        
        // 验证参数
        if (empty($host) || empty($port) || empty($user) || empty($pass)) {
            wp_die('请先填写完整的SMTP配置');
        }
        
        // 直接创建PHPMailer实例测试连接
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // 配置SMTP
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            
            // 设置安全协议
            switch ($secure) {
                case 'tls':
                    // 使用字符串值兼容所有PHPMailer版本
                    $mail->SMTPSecure = 'tls';
                    break;
                case 'ssl':
                    $mail->SMTPSecure = 'ssl';
                    break;
                default:
                    $mail->SMTPSecure = false;
                    break;
            }
            
            $mail->Timeout = 10;
            $mail->SMTPDebug = 0;
            
            // 尝试连接
            $connection = $mail->smtpConnect();
            
            if ($connection) {
                echo '<div class="updated"><p>SMTP连接测试成功！</p></div>';
                $mail->smtpClose();
            } else {
                echo '<div class="error"><p>SMTP连接测试失败！无法连接到SMTP服务器。</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="error"><p>SMTP连接测试失败：' . $e->getMessage() . '</p></div>';
        }
        
        // 返回SMTP设置页面
        echo '<p><a href="admin.php?page=boxmoe-smtp-settings" class="button">返回SMTP设置</a></p>';
        exit;
    }
}
