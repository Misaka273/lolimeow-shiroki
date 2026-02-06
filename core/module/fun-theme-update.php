<?php
/**
 * 🎉 主题更新检查模块
 * 集成 WordPress 主题更新系统，支持一键更新
 * 🕊️ 白木 <https://gl.baimu.live/>
 */

//安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 💾 GitHub 仓库配置
define('SHIROKI_GITHUB_REPO', 'Misaka273/lolimeow-shiroki');
define('SHIROKI_GITHUB_API', 'https://api.github.com/repos/' . SHIROKI_GITHUB_REPO . '/releases/latest');
define('SHIROKI_UPDATE_CHECK_INTERVAL', 86400); // ◀️ 24小时检查一次(秒)

/**
 * 🔄 清理所有相关缓存
 */
function shiroki_clear_all_cache() {
    delete_transient('shiroki_github_version');
    delete_site_transient('update_themes');
    delete_option('_site_transient_update_themes');
    wp_cache_flush();
}

/**
 * 🎉 获取当前主题版本号
 */
function shiroki_get_current_version() {
    $theme = wp_get_theme();
    return $theme->get('Version');
}

/**
 * 💾 获取 GitHub 最新版本信息
 */
function shiroki_get_github_latest_version($force_refresh = false) {
    if ($force_refresh) {
        shiroki_clear_all_cache();
    }
    
    $cached_version = get_transient('shiroki_github_version');
    
    if ($cached_version !== false && !$force_refresh) {
        return $cached_version;
    }
    
    $response = wp_remote_get(SHIROKI_GITHUB_API, array(
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        ),
        'timeout' => 15,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!isset($data['tag_name']) || !isset($data['html_url'])) {
        return false;
    }
    
    $version_info = array(
        'version' => ltrim($data['tag_name'], 'Vv'),
        'tag_name' => $data['tag_name'],
        'download_url' => 'https://github.com/' . SHIROKI_GITHUB_REPO . '/releases/download/' . $data['tag_name'] . '/lolimeow-shiroki.zip',
        'release_url' => $data['html_url'],
        'release_notes' => isset($data['body']) ? $data['body'] : '',
        'published_at' => isset($data['published_at']) ? $data['published_at'] : ''
    );
    
    set_transient('shiroki_github_version', $version_info, SHIROKI_UPDATE_CHECK_INTERVAL);
    
    return $version_info;
}

/**
 * 🔄 检查是否有新版本
 */
function shiroki_check_for_update() {
    $current_version = shiroki_get_current_version();
    $github_version = shiroki_get_github_latest_version();
    
    if (!$github_version) {
        return false;
    }
    
    return version_compare($github_version['version'], $current_version, '>');
}

/**
 * 🏄🏻‍♀️ 注入主题更新信息到 WordPress 更新系统
 */
add_filter('pre_set_site_transient_update_themes', 'shiroki_inject_theme_update');
function shiroki_inject_theme_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }
    
    $github_version = shiroki_get_github_latest_version();
    
    if (!$github_version) {
        if (isset($transient->response['lolimeow-shiroki'])) {
            unset($transient->response['lolimeow-shiroki']);
        }
        return $transient;
    }
    
    $current_version = shiroki_get_current_version();
    
    if (!version_compare($github_version['version'], $current_version, '>')) {
        if (isset($transient->response['lolimeow-shiroki'])) {
            unset($transient->response['lolimeow-shiroki']);
        }
        return $transient;
    }
    
    $theme_slug = 'lolimeow-shiroki';
    
    $update = array(
        'theme' => $theme_slug,
        'new_version' => $github_version['version'],
        'url' => $github_version['release_url'],
        'package' => $github_version['download_url'],
        'requires' => false,
        'requires_php' => false
    );
    
    $transient->response[$theme_slug] = $update;
    
    return $transient;
}

/**
 * GitHub 下载的 zip 包可能包含版本号前缀的目录，需要重命名
 */
add_filter('upgrader_source_selection', 'shiroki_fix_upgrader_source_selection', 10, 4);
function shiroki_fix_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
    if (!isset($hook_extra['theme']) || $hook_extra['theme'] !== 'lolimeow-shiroki') {
        return $source;
    }
    
    $correct_name = 'lolimeow-shiroki';
    $current_name = basename($source);
    
    if ($current_name !== $correct_name) {
        $new_source = trailingslashit(dirname($source)) . $correct_name;
        
        if (@rename($source, $new_source)) {
            return $new_source;
        }
    }
    
    return $source;
}

/**
 * 🎉 在后台显示主题更新通知
 */
add_action('admin_notices', 'shiroki_update_notice');
function shiroki_update_notice() {
    if (!current_user_can('update_themes')) {
        return;
    }
    
    $github_version = shiroki_get_github_latest_version();
    
    if (!$github_version) {
        return;
    }
    
    $current_version = shiroki_get_current_version();
    
    if (!version_compare($github_version['version'], $current_version, '>')) {
        return;
    }
    
    $update_url = admin_url('update-core.php');
    
    ?>
    <div class="notice notice-info is-dismissible shiroki-update-notice" style="border-left-color: #2271b1;">
        <h3 style="margin-top: 0;">🎉 主题更新提示</h3>
        <p>
            <strong>lolimeow-纸鸢版</strong> 有新版本可用！<br>
            当前版本：<code><?php echo esc_html($current_version); ?></code> | 
            最新版本：<code style="color: #2271b1;"><?php echo esc_html($github_version['version']); ?></code>
        </p>
        <p>
            <a href="<?php echo esc_url($update_url); ?>" class="button button-primary">
                🔄 立即更新
            </a>
            <a href="<?php echo esc_url($github_version['release_url']); ?>" class="button" target="_blank">
                📄 查看更新日志
            </a>
        </p>
        <p style="font-size: 12px; color: #666; margin-top: 10px;">
            发布时间：<?php echo date('Y-m-d H:i:s', strtotime($github_version['published_at'])); ?>
        </p>
    </div>
    <script>
    jQuery(document).on('click', '.shiroki-update-notice .notice-dismiss', function() {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'shiroki_dismiss_update_notice',
                version: '<?php echo esc_js($github_version['version']); ?>',
                nonce: '<?php echo wp_create_nonce('shiroki_dismiss_nonce'); ?>'
            }
        });
    });
    </script>
    <?php
}

/**
 * 🔄 处理更新通知的 dismiss 操作
 */
add_action('wp_ajax_shiroki_dismiss_update_notice', 'shiroki_dismiss_update_notice');
function shiroki_dismiss_update_notice() {
    check_ajax_referer('shiroki_dismiss_nonce', 'nonce');
    
    if (!current_user_can('update_themes')) {
        wp_send_json_error();
    }
    
    $version = isset($_POST['version']) ? sanitize_text_field($_POST['version']) : '';
    if ($version) {
        update_user_meta(get_current_user_id(), 'shiroki_update_dismissed', $version);
    }
    
    wp_send_json_success();
}

/**
 * 🌸 在主题列表页面显示更新徽章
 */
add_filter('wp_prepare_themes_for_js', 'shiroki_theme_update_badge');
function shiroki_theme_update_badge($prepared_themes) {
    $github_version = shiroki_get_github_latest_version();
    
    if (!$github_version) {
        return $prepared_themes;
    }
    
    $current_version = shiroki_get_current_version();
    
    if (!version_compare($github_version['version'], $current_version, '>')) {
        return $prepared_themes;
    }
    
    foreach ($prepared_themes as $slug => $theme) {
        if ($theme['id'] === 'lolimeow-shiroki') {
            $prepared_themes[$slug]['hasUpdate'] = true;
            $update = array(
                'new_version' => $github_version['version'],
                'package' => $github_version['download_url'],
                'url' => $github_version['release_url']
            );
            $prepared_themes[$slug]['update'] = $update;
            break;
        }
    }
    
    return $prepared_themes;
}

/**
 * 🔄 强制刷新版本检查缓存
 */
add_action('admin_post_shiroki_force_check_update', 'shiroki_force_check_update');
function shiroki_force_check_update() {
    if (!current_user_can('update_themes')) {
        wp_die('权限不足');
    }
    
    shiroki_clear_all_cache();
    wp_redirect(admin_url('themes.php?check=1'));
    exit;
}

/**
 * 🔄 手动清理缓存
 */
add_action('admin_post_shiroki_clear_cache', 'shiroki_clear_cache_action');
function shiroki_clear_cache_action() {
    if (!current_user_can('update_themes')) {
        wp_die('权限不足');
    }
    
    check_admin_referer('shiroki_clear_cache');
    
    shiroki_clear_all_cache();
    
    add_settings_error('shiroki_cache', 'cache_cleared', '缓存已清理完成！', 'updated');
    
    set_transient('settings_errors', get_settings_errors(), 30);
    
    wp_redirect(admin_url('themes.php?settings-updated=true'));
    exit;
}

/**
 * 🔄 添加手动检查更新和清理缓存按钮到主题页面
 */
add_action('admin_menu', 'shiroki_add_update_check_button');
function shiroki_add_update_check_button() {
    add_action('admin_footer-themes.php', 'shiroki_add_update_button_script');
}

function shiroki_add_update_button_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var themeRow = $('.theme[data-slug="lolimeow-shiroki"]');
        if (themeRow.length) {
            var checkButton = $('<a href="<?php echo admin_url('admin-post.php?action=shiroki_force_check_update'); ?>" class="button" style="margin-left: 10px;">🔄 检查更新</a>');
            var clearButton = $('<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=shiroki_clear_cache'), 'shiroki_clear_cache'); ?>" class="button" style="margin-left: 5px;">🗑️ 清理缓存</a>');
            themeRow.find('.theme-actions').append(checkButton).append(clearButton);
        }
    });
    </script>
    <?php
}

/**
 * 🔄 主题更新完成后清理缓存
 */
add_action('upgrader_process_complete', 'shiroki_after_theme_update', 10, 2);
function shiroki_after_theme_update($upgrader_object, $hook_extra) {
    if (isset($hook_extra['themes']) && in_array('lolimeow-shiroki', $hook_extra['themes'])) {
        shiroki_clear_all_cache();
    }
}

/**
 * 🔄 在主题详情页面添加缓存管理选项
 */
add_action('admin_init', 'shiroki_add_cache_management');
function shiroki_add_cache_management() {
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        $errors = get_transient('settings_errors');
        if ($errors) {
            foreach ($errors as $error) {
                add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
            }
            delete_transient('settings_errors');
        }
    }
}
