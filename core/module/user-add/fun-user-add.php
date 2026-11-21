<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * ➕ 添加用户页面新UI风格模板
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage User_Add
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 添加用户UI主类
 */
class Shiroki_User_Add_UI {

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📝 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 🚀 构造函数
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * 🔗 初始化钩子
     */
    private function init_hooks() {
        /* 🎨 加载自定义样式和脚本 */
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        /* 🎨 在管理页脚添加自定义UI */
        add_action('admin_footer', array($this, 'add_custom_user_add_ui'), 20);
    }

    /**
     * 🪁 获取语言名称
     */
    private function get_language_name($locale) {
        $languages = array(
            'en_US' => 'English (United States)',
            'zh_CN' => '简体中文',
            'zh_TW' => '繁體中文',
            'ja' => '日本語',
            'ko_KR' => '한국어',
            'fr_FR' => 'Français',
            'de_DE' => 'Deutsch',
            'es_ES' => 'Español',
            'it_IT' => 'Italiano',
            'ru_RU' => 'Русский',
            'pt_BR' => 'Português do Brasil',
            'ar' => 'العربية',
            'hi_IN' => 'हिन्दी',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'tr_TR' => 'Türkçe',
            'pl_PL' => 'Polski',
            'nl_NL' => 'Nederlands',
            'sv_SE' => 'Svenska',
            'da_DK' => 'Dansk',
            'fi' => 'Suomi',
            'nb_NO' => 'Norsk bokmål',
            'el' => 'Ελληνικά',
            'he_IL' => 'עברית',
            'id_ID' => 'Bahasa Indonesia',
            'ms_MY' => 'Bahasa Melayu',
            'uk' => 'Українська',
            'cs_CZ' => 'Čeština',
            'sk_SK' => 'Slovenčina',
            'hu_HU' => 'Magyar',
            'ro_RO' => 'Română',
            'bg_BG' => 'Български',
            'hr' => 'Hrvatski',
            'sr_RS' => 'Српски језик',
            'sl_SI' => 'Slovenščina',
            'lt_LT' => 'Lietuvių kalba',
            'lv' => 'Latviešu valoda',
            'et' => 'Eesti',
            'fa_IR' => 'فارسی',
            'ur' => 'اردو',
            'bn_BD' => 'বাংলা',
            'ta_IN' => 'தமிழ்',
            'te' => 'తెలుగు',
            'mr' => 'मराठी',
            'gu' => 'ગુજરાતી',
            'kn' => 'ಕನ್ನಡ',
            'ml_IN' => 'മലയാളം',
            'pa_IN' => 'ਪੰਜਾਬੀ',
        );

        return isset($languages[$locale]) ? $languages[$locale] : $locale;
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* 🎯 只在添加用户页面加载 */
        if ($hook !== 'user-new.php') {
            return;
        }

        $theme_uri = get_template_directory_uri();
        $version = wp_get_theme()->get('Version');

        /* 🎨 先加载统一变量文件 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );

        /* 🎨 添加用户样式 */
        wp_enqueue_style(
            'shiroki-user-add',
            $theme_uri . '/assets/css/admin/user-add/user-add.css',
            array('admin-variables'),
            $version
        );

        /* 📦 添加用户脚本 */
        wp_enqueue_script(
            'shiroki-user-add',
            $theme_uri . '/assets/js/admin/user-add/user-add.js',
            array('jquery', 'user-profile'),
            $version,
            true
        );

        /* 🎯 传递配置 */
        wp_localize_script('shiroki-user-add', 'shirokiUserAddConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_user_add_nonce'),
            'strings' => array(
                'generatePassword' => '🔐 生成密码',
                'showPassword' => '👁️ 显示密码',
                'hidePassword' => '🙈 隐藏密码',
                'passwordWeak' => '⚠️ 密码强度弱',
                'passwordMedium' => '👍 密码强度中等',
                'passwordStrong' => '💪 密码强度强',
                'requiredField' => '✏️ 必填字段',
                'optionalField' => '📝 选填字段'
            )
        ));
    }

    /**
     * 🎨 添加自定义添加用户UI
     */
    public function add_custom_user_add_ui() {
        /* 🔍 确保 get_current_screen 函数存在 */
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        /* 🎯 检查是否在添加用户页面 */
        if (!$screen || $screen->id !== 'user') {
            return;
        }

        /* 🔐 检查权限 */
        $can_create_users = current_user_can('create_users');
        $can_promote_users = current_user_can('promote_users');

        if (!$can_create_users && !$can_promote_users) {
            return;
        }

        /* 📝 获取表单默认值 */
        $creating = isset($_POST['createuser']);
        $new_user_login = $creating && isset($_POST['user_login']) ? wp_unslash($_POST['user_login']) : '';
        $new_user_firstname = $creating && isset($_POST['first_name']) ? wp_unslash($_POST['first_name']) : '';
        $new_user_lastname = $creating && isset($_POST['last_name']) ? wp_unslash($_POST['last_name']) : '';
        $new_user_email = $creating && isset($_POST['email']) ? wp_unslash($_POST['email']) : '';
        $new_user_uri = $creating && isset($_POST['url']) ? wp_unslash($_POST['url']) : '';
        $new_user_role = $creating && isset($_POST['role']) ? wp_unslash($_POST['role']) : '';
        $new_user_send_notification = $creating && !isset($_POST['send_user_notification']) ? false : true;

        /* 🎭 获取可编辑角色 */
        $editable_roles = get_editable_roles();
        $default_role = get_option('default_role');
        if (!$new_user_role) {
            $new_user_role = $default_role;
        }

        /* 🌐 获取语言选项 */
        $languages = get_available_languages();
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            /* 🔧 隐藏原版表单 */
            $('#createuser, oklch(0.8622 0.0525 196.22)user').hide();
            $('.wrap > h1').hide();
            $('.wrap > p').hide();

            /* 📦 插入自定义UI */
            var customUI = `
                <div class="shiroki-user-add-wrapper">
                    <!-- 🎯 页面标题区域 -->
                    <div class="shiroki-user-add-header">
                        <h1 class="shiroki-user-add-title">
                            <?php if ($can_create_users) : ?>
                                ➕ 添加新用户
                            <?php else : ?>
                                ➕ 添加现有用户
                            <?php endif; ?>
                        </h1>
                        <p class="shiroki-user-add-subtitle">创建新用户并将其添加到本站</p>
                    </div>

                    <!-- 📦 主内容区域 -->
                    <div class="shiroki-user-add-content">
                        <?php if ($can_create_users) : ?>
                        <!-- 📝 创建新用户表单 -->
                        <form method="post" name="createuser" id="shiroki-createuser" class="shiroki-user-add-form validate" novalidate="novalidate">
                            <input name="action" type="hidden" value="createuser">
                            <?php wp_nonce_field('create-user', '_wpnonce_create-user'); ?>

                            <!-- 👤 基本信息卡片 -->
                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">👤</span>
                                    <span class="shiroki-user-add-card-title">基本信息</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <!-- 用户名 -->
                                    <div class="shiroki-user-add-field required">
                                        <label for="shiroki_user_login">
                                            <span class="shiroki-user-add-label-text">用户名</span>
                                            <span class="shiroki-user-add-required">*</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <span class="shiroki-user-add-input-icon">@</span>
                                            <input type="text" name="user_login" id="shiroki_user_login"
                                                   value="<?php echo esc_attr($new_user_login); ?>"
                                                   class="regular-text" autocomplete="off" autocapitalize="none" autocorrect="off" maxlength="60"
                                                   placeholder="请输入用户名">
                                        </div>
                                        <p class="shiroki-user-add-description">用户名将用于登录，创建后不可修改</p>
                                    </div>

                                    <!-- 邮箱 -->
                                    <div class="shiroki-user-add-field required">
                                        <label for="shiroki_email">
                                            <span class="shiroki-user-add-label-text">电子邮箱</span>
                                            <span class="shiroki-user-add-required">*</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <span class="shiroki-user-add-input-icon">✉️</span>
                                            <input type="email" name="email" id="shiroki_email"
                                                   value="<?php echo esc_attr($new_user_email); ?>"
                                                   class="regular-text" autocomplete="off"
                                                   placeholder="请输入电子邮箱">
                                        </div>
                                        <p class="shiroki-user-add-description">用于接收通知和密码重置</p>
                                    </div>

                                    <!-- 名字 -->
                                    <div class="shiroki-user-add-field">
                                        <label for="shiroki_first_name">
                                            <span class="shiroki-user-add-label-text">名字</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <input type="text" name="first_name" id="shiroki_first_name"
                                                   value="<?php echo esc_attr($new_user_firstname); ?>"
                                                   class="regular-text" autocomplete="off"
                                                   placeholder="请输入名字">
                                        </div>
                                    </div>

                                    <!-- 姓氏 -->
                                    <div class="shiroki-user-add-field">
                                        <label for="shiroki_last_name">
                                            <span class="shiroki-user-add-label-text">姓氏</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <input type="text" name="last_name" id="shiroki_last_name"
                                                   value="<?php echo esc_attr($new_user_lastname); ?>"
                                                   class="regular-text" autocomplete="off"
                                                   placeholder="请输入姓氏">
                                        </div>
                                    </div>

                                    <!-- 网站 -->
                                    <div class="shiroki-user-add-field">
                                        <label for="shiroki_url">
                                            <span class="shiroki-user-add-label-text">网站</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <span class="shiroki-user-add-input-icon">🌐</span>
                                            <input type="url" name="url" id="shiroki_url"
                                                   value="<?php echo esc_attr($new_user_uri); ?>"
                                                   class="regular-text code" autocomplete="off"
                                                   placeholder="https://gl.baimu.live/">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($languages) : ?>
                            <!-- 🌐 语言设置卡片 -->
                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">🌐</span>
                                    <span class="shiroki-user-add-card-title">语言设置</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <div class="shiroki-user-add-field">
                                        <label>
                                            <span class="shiroki-user-add-label-text">界面语言</span>
                                        </label>
                                        <div class="shiroki-user-add-lang-options" id="shiroki-lang-options">
                                            <!-- 🏠 站点默认选项 -->
                                            <label class="shiroki-user-add-lang-option selected">
                                                <input type="radio" name="locale" value="site-default" checked="checked">
                                                <span class="shiroki-user-add-lang-option-text">🏠 站点默认</span>
                                            </label>
                                            <!-- 🌐 English 选项 -->
                                            <label class="shiroki-user-add-lang-option">
                                                <input type="radio" name="locale" value="">
                                                <span class="shiroki-user-add-lang-option-text">🇺🇸 English (United States)</span>
                                            </label>
                                            <!-- 🇨🇳 简体中文选项 -->
                                            <?php if (in_array('zh_CN', $languages)) : ?>
                                            <label class="shiroki-user-add-lang-option">
                                                <input type="radio" name="locale" value="zh_CN">
                                                <span class="shiroki-user-add-lang-option-text">🇨🇳 简体中文</span>
                                            </label>
                                            <?php endif; ?>
                                            <!-- 🌍 其他已安装语言 -->
                                            <?php foreach ($languages as $lang_code) :
                                                if ($lang_code === 'zh_CN') continue;
                                                $lang_name = $this->get_language_name($lang_code);
                                            ?>
                                            <label class="shiroki-user-add-lang-option">
                                                <input type="radio" name="locale" value="<?php echo esc_attr($lang_code); ?>">
                                                <span class="shiroki-user-add-lang-option-text">🌍 <?php echo esc_html($lang_name); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="shiroki-user-add-description">选择用户后台界面显示的语言</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- 🔐 密码设置卡片 -->
                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">🔐</span>
                                    <span class="shiroki-user-add-card-title">密码设置</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <input type="hidden" value=" "><!-- #24364 workaround -->

                                    <div class="shiroki-user-add-password-section">
                                        <button type="button" class="shiroki-user-add-generate-pw" id="shiroki-generate-pw">
                                            🔐 生成密码
                                        </button>

                                        <div class="shiroki-user-add-pw-wrapper" id="shiroki-pw-wrapper">
                                            <!-- 密码输入 -->
                                            <div class="shiroki-user-add-password-input-wrapper">
                                                <input type="password" name="pass1" id="shiroki_pass1"
                                                       class="regular-text strong" autocomplete="new-password"
                                                       spellcheck="false" data-reveal="1"
                                                       aria-describedby="shiroki-pass-strength-result"
                                                       placeholder="请输入密码">
                                                <button type="button" class="shiroki-user-add-toggle-pw" id="shiroki-toggle-pw" data-toggle="0">
                                                    <span class="dashicons dashicons-hidden"></span>
                                                </button>
                                            </div>
                                            <div id="shiroki-pass-strength-result" class="shiroki-user-add-pass-strength" aria-live="polite"></div>

                                            <!-- 再次确认密码输入 -->
                                            <div class="shiroki-user-add-password-confirm-wrapper">
                                                <label for="shiroki_pass2" class="shiroki-user-add-password-confirm-label">
                                                    <span class="shiroki-user-add-label-text">再次输入密码</span>
                                                </label>
                                                <div class="shiroki-user-add-password-input-wrapper">
                                                    <input type="password" name="pass2" id="shiroki_pass2"
                                                           class="regular-text" autocomplete="new-password"
                                                           spellcheck="false"
                                                           placeholder="请再次输入密码">
                                                    <button type="button" class="shiroki-user-add-toggle-pw" id="shiroki-toggle-pw2" data-toggle="0">
                                                        <span class="dashicons dashicons-hidden"></span>
                                                    </button>
                                                </div>
                                                <p class="shiroki-user-add-description" id="shiroki-pass-match-result"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 弱密码确认 -->
                                    <div class="shiroki-user-add-pw-weak" id="shiroki-pw-weak" style="display: none;">
                                        <label class="shiroki-user-add-weak-pw-label">
                                            <input type="checkbox" name="pw_weak" id="shiroki_pw_weak" class="pw-checkbox" value="1">
                                            <span class="shiroki-user-add-weak-pw-check"></span>
                                            <span class="shiroki-user-add-weak-pw-text">⚠️ 确认使用弱密码</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- 🎭 角色设置卡片 -->
                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">🎭</span>
                                    <span class="shiroki-user-add-card-title">角色设置</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <div class="shiroki-user-add-field">
                                        <label for="shiroki_role">
                                            <span class="shiroki-user-add-label-text">用户角色</span>
                                        </label>
                                        <div class="shiroki-user-add-role-options" id="shiroki-role-options">
                                            <?php foreach ($editable_roles as $role_key => $role_info) :
                                                $role_names = array(
                                                    'administrator' => '🔴 管理员',
                                                    'editor' => '🟢 编辑',
                                                    'author' => '🔵 作者',
                                                    'contributor' => '🟣 贡献者',
                                                    'subscriber' => '⚪ 订阅者'
                                                );
                                                $role_label = isset($role_names[$role_key]) ? $role_names[$role_key] : $role_info['name'];
                                                $is_selected = ($role_key === $new_user_role);
                                            ?>
                                            <label class="shiroki-user-add-role-option <?php echo $is_selected ? 'selected' : ''; ?>">
                                                <input type="radio" name="role" value="<?php echo esc_attr($role_key); ?>" <?php checked($is_selected); ?>>
                                                <span class="shiroki-user-add-role-option-text"><?php echo esc_html($role_label); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="shiroki-user-add-description">选择用户的权限级别</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 📧 通知设置卡片 -->
                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">📧</span>
                                    <span class="shiroki-user-add-card-title">通知设置</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <div class="shiroki-user-add-field">
                                        <label class="shiroki-user-add-checkbox-label shiroki-user-add-checkbox-large">
                                            <input type="checkbox" name="send_user_notification" id="shiroki_send_notification" value="1" <?php checked($new_user_send_notification); ?>>
                                            <span class="shiroki-user-add-checkbox-custom"></span>
                                            <span class="shiroki-user-add-checkbox-content">
                                                <span class="shiroki-user-add-checkbox-title">📧 发送用户通知邮件</span>
                                                <span class="shiroki-user-add-checkbox-desc">向新用户发送包含账户详细信息的欢迎邮件</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php do_action('user_new_form', 'add-new-user'); ?>

                            <!-- 🚀 提交按钮 -->
                            <div class="shiroki-user-add-actions">
                                <button type="submit" name="createuser" class="shiroki-user-add-submit">
                                    <span class="shiroki-user-add-submit-icon">➕</span>
                                    <span class="shiroki-user-add-submit-text">添加用户</span>
                                </button>
                                <a href="<?php echo admin_url('users.php'); ?>" class="shiroki-user-add-cancel">
                                    ❌ 取消
                                </a>
                            </div>
                        </form>
                        <?php endif; ?>

                        <?php if (is_multisite() && $can_promote_users) : ?>
                        <!-- 🔗 添加现有用户表单 -->
                        <form method="post" name="adduser" id="shiroki-adduser" class="shiroki-user-add-form validate" novalidate="novalidate">
                            <input name="action" type="hidden" value="adduser">
                            <?php wp_nonce_field('add-user', '_wpnonce_add-user'); ?>

                            <div class="shiroki-user-add-card">
                                <div class="shiroki-user-add-card-header">
                                    <span class="shiroki-user-add-card-icon">🔗</span>
                                    <span class="shiroki-user-add-card-title">添加现有用户</span>
                                </div>
                                <div class="shiroki-user-add-card-body">
                                    <p class="shiroki-user-add-intro">
                                        <?php if (!current_user_can('manage_network_users')) : ?>
                                            输入网络上现有用户的电子邮箱，邀请他们加入本站。对方将收到确认邮件。
                                        <?php else : ?>
                                            输入网络上现有用户的电子邮箱或用户名，邀请他们加入本站。对方将收到确认邮件。
                                        <?php endif; ?>
                                    </p>

                                    <div class="shiroki-user-add-field required">
                                        <label for="shiroki_adduser_email">
                                            <span class="shiroki-user-add-label-text">
                                                <?php echo current_user_can('manage_network_users') ? '电子邮箱或用户名' : '电子邮箱'; ?>
                                            </span>
                                            <span class="shiroki-user-add-required">*</span>
                                        </label>
                                        <div class="shiroki-user-add-input-wrapper">
                                            <span class="shiroki-user-add-input-icon">✉️</span>
                                            <input type="<?php echo current_user_can('manage_network_users') ? 'text' : 'email'; ?>"
                                                   name="email" id="shiroki_adduser_email"
                                                   class="regular-text wp-suggest-user" autocomplete="off"
                                                   placeholder="<?php echo current_user_can('manage_network_users') ? '请输入电子邮箱或用户名' : '请输入电子邮箱'; ?>">
                                        </div>
                                    </div>

                                    <div class="shiroki-user-add-field">
                                        <label for="shiroki_adduser_role">
                                            <span class="shiroki-user-add-label-text">用户角色</span>
                                        </label>
                                        <div class="shiroki-user-add-select-wrapper">
                                            <select name="role" id="shiroki_adduser_role">
                                                <?php wp_dropdown_roles($default_role); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <?php if (current_user_can('manage_network_users')) : ?>
                                    <div class="shiroki-user-add-field">
                                        <label class="shiroki-user-add-checkbox-label shiroki-user-add-checkbox-large">
                                            <input type="checkbox" name="noconfirmation" id="shiroki_adduser_noconfirmation" value="1">
                                            <span class="shiroki-user-add-checkbox-custom"></span>
                                            <span class="shiroki-user-add-checkbox-content">
                                                <span class="shiroki-user-add-checkbox-title">⚡ 跳过确认邮件</span>
                                                <span class="shiroki-user-add-checkbox-desc">直接添加用户，不发送需要确认的邮件</span>
                                            </span>
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php do_action('user_new_form', 'add-existing-user'); ?>

                            <div class="shiroki-user-add-actions">
                                <button type="submit" name="adduser" class="shiroki-user-add-submit">
                                    <span class="shiroki-user-add-submit-icon">🔗</span>
                                    <span class="shiroki-user-add-submit-text">添加现有用户</span>
                                </button>
                                <a href="<?php echo admin_url('users.php'); ?>" class="shiroki-user-add-cancel">
                                    ❌ 取消
                                </a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            `;

            /* 📦 插入到页面 */
            $('.wrap').append(customUI);

            /* 🚀 触发自定义事件 */
            $(document).trigger('shiroki-user-add-ready');
        });
        </script>
        <?php
    }
}

/**
 * 🚀 初始化添加用户UI
 */
function shiroki_init_user_add_ui() {
    Shiroki_User_Add_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_user_add_ui');
