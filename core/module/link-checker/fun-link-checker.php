<?php
/**
 * 🔗 友链反链检测模块
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Link_Checker
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 友链反链检测主类
 */
class Shiroki_Friend_Link_Checker {

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📝 选项键名
     */
    private const CHECK_RESULTS_OPTION = 'shiroki_friend_link_checks';
    private const WHITELIST_OPTION = 'shiroki_friend_link_whitelist';
    private const PAGE_SLUG = 'shiroki-link-checker';

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
        /* 📋 注册子菜单（优先级 20，确保位于会员管理菜单 erphpdown 99 之上） */
        add_action('admin_menu', array($this, 'register_submenu'), 20);

        /* 🎨 加载资源 */
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        /* 📡 AJAX 处理 */
        add_action('wp_ajax_shiroki_get_friend_links', array($this, 'ajax_get_friend_links'));
        add_action('wp_ajax_shiroki_check_friend_link', array($this, 'ajax_check_friend_link'));
        add_action('wp_ajax_shiroki_check_all_friend_links', array($this, 'ajax_check_all_friend_links'));
        add_action('wp_ajax_shiroki_toggle_friend_link_whitelist', array($this, 'ajax_toggle_whitelist'));
    }

    /**
     * 📋 注册子菜单（盒子萌主题设置 > 友链检测）
     */
    public function register_submenu() {
        add_submenu_page(
            'boxmoe_options',
            __('友链检测', 'textdomain'),
            __('友链检测', 'textdomain'),
            'manage_links',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        $is_our_page = isset($_GET['page']) && sanitize_text_field($_GET['page']) === self::PAGE_SLUG;

        /* 🎯 兼容多种父菜单模式：顶级菜单 / 外观子菜单 / 其他子菜单 */
        $expected_hooks = array(
            'toplevel_page_' . self::PAGE_SLUG,
            'boxmoe_page_' . self::PAGE_SLUG,
            'appearance_page_' . self::PAGE_SLUG,
            'themes_page_' . self::PAGE_SLUG,
        );
        $is_our_hook = in_array($hook, $expected_hooks, true);

        if (!$is_our_page && !$is_our_hook && strpos((string) $hook, self::PAGE_SLUG) === false) {
            return;
        }

        $theme_dir = get_stylesheet_directory();
        $theme_uri = get_stylesheet_directory_uri();
        $css_path = $theme_dir . '/assets/css/admin/link-checker/link-checker.css';
        $js_path = $theme_dir . '/assets/js/admin/link-checker/link-checker.js';
        $version = wp_get_theme()->get('Version');
        $css_version = file_exists($css_path) ? (string) filemtime($css_path) : $version;
        $js_version = file_exists($js_path) ? (string) filemtime($js_path) : $version;

        /* 🎨 统一变量 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );

        /* 🎨 友链检测样式 */
        wp_enqueue_style(
            'shiroki-link-checker',
            $theme_uri . '/assets/css/admin/link-checker/link-checker.css',
            array('admin-variables'),
            $css_version
        );

        /* 📦 友链检测脚本 */
        wp_enqueue_script(
            'shiroki-link-checker',
            $theme_uri . '/assets/js/admin/link-checker/link-checker.js',
            array('jquery'),
            $js_version,
            true
        );

        /* 🎯 传递 AJAX 配置 */
        wp_localize_script('shiroki-link-checker', 'shirokiLinkCheckerConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_link_checker_nonce'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无友链',
                'checkAll' => '🔍 检测全部',
                'checking' => '检测中...',
                'check' => '检测反链',
                'addWhitelist' => '加入白名单',
                'removeWhitelist' => '移出白名单',
                'visit' => '访问网站',
                'copyLink' => '复制链接',
                'sourceCode' => '源码来源',
                'statusHas' => '有反链',
                'statusNo' => '无反链',
                'statusWhitelisted' => '白名单',
                'statusUnknown' => '未检测',
                'lastCheck' => '最后检测',
                'never' => '从未检测',
                'bulkCheck' => '批量检测',
                'bulkWhitelist' => '批量加白',
                'bulkUnwhitelist' => '批量移白',
                'cancel' => '取消选择',
                'selected' => '已选择',
                'emptySelection' => '请先选择友链',
                'confirmCheckAll' => '确定要立即检测所有友链吗？\n\n检测过程可能需要几秒到几十秒，请勿关闭页面。'
            )
        ));
    }

    /**
     * 🖥️ 渲染检测页面
     */
    public function render_page() {
        if (!current_user_can('manage_links')) {
            wp_die(__('权限不足', 'textdomain'));
        }

        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all';
        $categories = $this->get_link_categories();
        $counts = $this->get_status_counts($current_category);
        ?>
        <div class="wrap shiroki-link-checker-wrap">
            <h1 class="wp-heading-inline" style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

            <!-- 🎯 自定义页面头部 -->
            <div class="shiroki-link-checker-header">
                <div class="shiroki-link-checker-title">
                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/404/routes.svg'); ?>" alt="" class="shiroki-link-checker-title-icon">
                    <span>友链反链检测</span>
                </div>
                <a href="https://github.com/liseezn/see-friends" target="_blank" rel="noopener noreferrer" class="shiroki-link-checker-source-btn">
                    <span>📦</span>
                    <span>源码来源</span>
                </a>
            </div>

            <!-- 🎯 顶部工具栏 -->
            <div class="shiroki-link-checker-top-bar">
                <div class="shiroki-link-checker-filters">
                    <div class="shiroki-link-checker-filter-row">
                        <span class="shiroki-link-checker-filter-label">📊 状态：</span>
                        <div class="shiroki-link-checker-status-options">
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-status-btn <?php echo $current_status === 'all' ? 'active' : ''; ?>" data-status="all">
                                📁 全部 (<?php echo intval($counts['all']); ?>)
                            </button>
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-status-btn <?php echo $current_status === 'has' ? 'active' : ''; ?>" data-status="has">
                                🟢 有反链 (<?php echo intval($counts['has']); ?>)
                            </button>
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-status-btn <?php echo $current_status === 'no' ? 'active' : ''; ?>" data-status="no">
                                🔴 无反链 (<?php echo intval($counts['no']); ?>)
                            </button>
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-status-btn <?php echo $current_status === 'whitelisted' ? 'active' : ''; ?>" data-status="whitelisted">
                                ⚪ 白名单 (<?php echo intval($counts['whitelisted']); ?>)
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($categories)) : ?>
                    <div class="shiroki-link-checker-filter-row">
                        <span class="shiroki-link-checker-filter-label">🏷️ 分类：</span>
                        <div class="shiroki-link-checker-category-options">
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-category-btn <?php echo $current_category === 'all' ? 'active' : ''; ?>" data-category="all">
                                📁 全部 (<?php echo intval($counts['all']); ?>)
                            </button>
                            <?php foreach ($categories as $category) : ?>
                            <button type="button" class="shiroki-link-checker-filter-btn shiroki-link-checker-category-btn <?php echo $current_category === (string) $category['id'] ? 'active' : ''; ?>" data-category="<?php echo esc_attr($category['id']); ?>">
                                <?php echo esc_html($category['name']); ?> (<?php echo intval($category['count']); ?>)
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 🛠️ 批量操作 -->
                <div class="shiroki-link-checker-bulk-actions" id="shiroki-link-checker-bulk-actions" style="display: none;">
                    <span class="shiroki-link-checker-bulk-count">已选择 <span class="shiroki-link-checker-bulk-count-num">0</span> 个</span>
                    <button class="shiroki-link-checker-bulk-btn shiroki-link-checker-bulk-check" data-action="check">
                        🔍 批量检测
                    </button>
                    <button class="shiroki-link-checker-bulk-btn shiroki-link-checker-bulk-whitelist" data-action="whitelist">
                        ⚪ 批量加白
                    </button>
                    <button class="shiroki-link-checker-bulk-btn shiroki-link-checker-bulk-unwhitelist" data-action="unwhitelist">
                        ❌ 批量移白
                    </button>
                    <button class="shiroki-link-checker-bulk-btn shiroki-link-checker-bulk-cancel" data-action="cancel">
                        🚫 取消选择
                    </button>
                </div>

                <div class="shiroki-link-checker-actions-right">
                    <!-- 🔍 搜索框 -->
                    <div class="shiroki-link-checker-search-wrapper">
                        <div class="shiroki-link-checker-search">
                            <input type="text" id="shiroki-link-checker-search" placeholder="🔍 搜索网站名称或链接..." autocomplete="off">
                        </div>
                    </div>

                    <!-- 🔍 检测全部 -->
                    <button class="shiroki-link-checker-check-all-btn" id="shiroki-link-checker-check-all">
                        <span>🔍</span>
                        <span>检测全部</span>
                    </button>
                </div>
            </div>

            <!-- ⏳ 加载状态 -->
            <div class="shiroki-link-checker-loading" id="shiroki-link-checker-loading" style="display: none;">
                <div class="shiroki-link-checker-loading-spinner"></div>
                <span>⏳ 加载中...</span>
            </div>

            <!-- 📦 友链网格 -->
            <div class="shiroki-link-checker-grid" id="shiroki-link-checker-grid"></div>

            <!-- 📭 空状态 -->
            <div class="shiroki-link-checker-empty" id="shiroki-link-checker-empty" style="display: none;">
                <svg class="shiroki-link-checker-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="shiroki-link-checker-empty-text">📭 暂无友链</div>
                <div class="shiroki-link-checker-empty-subtext">请先在 WordPress 后台的「链接」中添加友链</div>
            </div>
        </div>
        <?php
    }

    /**
     * 📊 获取各状态数量
     */
    private function get_status_counts($category = 'all') {
        $args = array('hide_invisible' => 0);
        if ($category !== 'all') {
            $args['category'] = intval($category);
        }
        $bookmarks = get_bookmarks($args);
        $results = get_option(self::CHECK_RESULTS_OPTION, array());
        $whitelist = $this->get_whitelist();

        $counts = array('all' => count($bookmarks), 'has' => 0, 'no' => 0, 'whitelisted' => 0, 'unknown' => 0);

        foreach ($bookmarks as $bookmark) {
            $domain = $this->get_domain($bookmark->link_url);
            if (in_array($domain, $whitelist, true)) {
                $counts['whitelisted']++;
                continue;
            }

            $link_id = intval($bookmark->link_id);
            if (isset($results[$link_id]['status'])) {
                $status = $results[$link_id]['status'];
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
            } else {
                $counts['unknown']++;
            }
        }

        return $counts;
    }

    /**
     * 📡 AJAX 获取友链列表
     */
    public function ajax_get_friend_links() {
        $this->verify_ajax();

        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';

        $args = array('hide_invisible' => 0);
        if ($category !== 'all') {
            $args['category'] = intval($category);
        }
        $bookmarks = get_bookmarks($args);
        $results = get_option(self::CHECK_RESULTS_OPTION, array());
        $whitelist = $this->get_whitelist();

        $items = array();
        foreach ($bookmarks as $bookmark) {
            $item = $this->format_link_item($bookmark, $results, $whitelist);

            /* 🔍 状态筛选 */
            if ($status !== 'all' && $item['status'] !== $status) {
                continue;
            }

            /* 🔍 搜索 */
            if (!empty($search)) {
                $search_lower = mb_strtolower($search);
                $name_lower = mb_strtolower($item['name']);
                $url_lower = mb_strtolower($item['url']);
                if (mb_stripos($name_lower, $search_lower) === false && mb_stripos($url_lower, $search_lower) === false) {
                    continue;
                }
            }

            $items[] = $item;
        }

        wp_send_json_success(array('links' => $items, 'total' => count($bookmarks)));
    }

    /**
     * 📡 AJAX 检测单个友链
     */
    public function ajax_check_friend_link() {
        $this->verify_ajax();

        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        if (!$link_id) {
            wp_send_json_error(array('message' => '无效的友链 ID'));
        }

        $bookmark = get_bookmark($link_id);
        if (!$bookmark) {
            wp_send_json_error(array('message' => '友链不存在'));
        }

        $result = $this->check_single_link($link_id, $bookmark->link_url);
        wp_send_json_success(array(
            'link' => $this->format_link_item($bookmark, get_option(self::CHECK_RESULTS_OPTION, array()), $this->get_whitelist()),
            'message' => $result['status'] === 'has' ? '检测到反链' : ($result['status'] === 'whitelisted' ? '已加入白名单' : '未检测到反链')
        ));
    }

    /**
     * 📡 AJAX 检测全部友链
     */
    public function ajax_check_all_friend_links() {
        $this->verify_ajax();

        $bookmarks = get_bookmarks(array('hide_invisible' => 0));
        $total = count($bookmarks);
        $invalid = 0;
        $checked = 0;

        foreach ($bookmarks as $bookmark) {
            $result = $this->check_single_link(intval($bookmark->link_id), $bookmark->link_url);
            if ($result['status'] === 'no') {
                $invalid++;
            }
            if ($result['status'] !== 'unknown') {
                $checked++;
            }
            usleep(200000);
        }

        wp_send_json_success(array(
            'total' => $total,
            'checked' => $checked,
            'invalid' => $invalid,
            'message' => "批量检测完成！共检测 {$checked} 个友链，正常 " . ($checked - $invalid) . " 个，失效 {$invalid} 个"
        ));
    }

    /**
     * 📡 AJAX 切换白名单
     */
    public function ajax_toggle_whitelist() {
        $this->verify_ajax();

        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $action = isset($_POST['whitelist_action']) ? sanitize_text_field($_POST['whitelist_action']) : '';

        if (!$link_id || !in_array($action, array('add', 'remove'), true)) {
            wp_send_json_error(array('message' => '参数错误'));
        }

        $bookmark = get_bookmark($link_id);
        if (!$bookmark || empty($bookmark->link_url)) {
            wp_send_json_error(array('message' => '友链不存在'));
        }

        $domain = $this->get_domain($bookmark->link_url);
        $whitelist = $this->get_whitelist();

        if ($action === 'add') {
            if (!in_array($domain, $whitelist, true)) {
                $whitelist[] = $domain;
            }
            $this->update_link_status($link_id, 'whitelisted');
            $message = '已加入白名单';
        } else {
            $whitelist = array_values(array_diff($whitelist, array($domain)));
            $this->update_link_status($link_id, 'unknown');
            $message = '已移出白名单';
        }

        update_option(self::WHITELIST_OPTION, array_values(array_unique($whitelist)));

        wp_send_json_success(array(
            'link' => $this->format_link_item($bookmark, get_option(self::CHECK_RESULTS_OPTION, array()), $this->get_whitelist()),
            'message' => $message
        ));
    }

    /**
     * 🔍 检测单个链接
     */
    private function check_single_link($link_id, $url) {
        $link_id = intval($link_id);
        $domain = $this->get_domain($url);

        /* ⚪ 白名单优先 */
        if (in_array($domain, $this->get_whitelist(), true)) {
            $this->update_link_status($link_id, 'whitelisted');
            return array('status' => 'whitelisted');
        }

        /* 🔍 执行反链检测 */
        $has_backlink = shiroki_check_friend_backlink($url);
        $status = $has_backlink ? 'has' : 'no';
        $this->update_link_status($link_id, $status);

        return array('status' => $status);
    }

    /**
     * 📝 更新链接检测状态
     */
    private function update_link_status($link_id, $status) {
        $link_id = intval($link_id);
        $results = get_option(self::CHECK_RESULTS_OPTION, array());
        $results[$link_id] = array(
            'status' => sanitize_text_field($status),
            'checked_at' => time(),
        );
        update_option(self::CHECK_RESULTS_OPTION, $results);
    }

    /**
     * 📝 格式化友链数据
     */
    private function format_link_item($bookmark, $results, $whitelist) {
        $link_id = intval($bookmark->link_id);
        $domain = $this->get_domain($bookmark->link_url);
        $is_whitelisted = in_array($domain, $whitelist, true);

        if ($is_whitelisted) {
            $status = 'whitelisted';
        } elseif (isset($results[$link_id]['status'])) {
            $status = $results[$link_id]['status'];
        } else {
            $status = 'unknown';
        }

        $checked_at = isset($results[$link_id]['checked_at']) ? intval($results[$link_id]['checked_at']) : 0;

        $category_ids = wp_get_link_cats($link_id);
        $category_names = array();
        foreach ($category_ids as $cat_id) {
            $term = get_term($cat_id, 'link_category');
            if ($term && !is_wp_error($term)) {
                $category_names[] = $term->name;
            }
        }

        return array(
            'id' => $link_id,
            'name' => $bookmark->link_name,
            'url' => $bookmark->link_url,
            'domain' => $domain,
            'description' => $bookmark->link_description,
            'image' => $bookmark->link_image,
            'target' => $bookmark->link_target,
            'status' => $status,
            'checked_at' => $checked_at,
            'checked_at_text' => $checked_at ? date('Y-m-d H:i:s', $checked_at) : '从未检测',
            'is_whitelisted' => $is_whitelisted,
            'categories' => $category_names,
            'category_ids' => $category_ids,
        );
    }

    /**
     * 🌐 获取域名
     */
    private function get_domain($url) {
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return '';
        }
        return preg_replace('/^www\./', '', strtolower($host));
    }

    /**
     * 🏷️ 获取链接分类列表
     */
    private function get_link_categories() {
        $terms = get_terms(array(
            'taxonomy' => 'link_category',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        $categories = array();
        foreach ($terms as $term) {
            $categories[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
            );
        }

        return $categories;
    }

    /**
     * 📋 获取白名单
     */
    private function get_whitelist() {
        $whitelist = get_option(self::WHITELIST_OPTION, array());
        return is_array($whitelist) ? array_map(array($this, 'normalize_domain'), $whitelist) : array();
    }

    /**
     * 📝 标准化域名
     */
    private function normalize_domain($domain) {
        return preg_replace('/^www\./', '', strtolower(trim($domain)));
    }

    /**
     * 🔐 验证 AJAX 请求
     */
    private function verify_ajax() {
        if (!check_ajax_referer('shiroki_link_checker_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        if (!current_user_can('manage_links')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
    }
}

/**
 * 🔍 友链反链检测核心函数（基于 see-friends）
 *
 * @param string $target_url 目标友链 URL
 * @return bool 是否检测到反链
 */
function shiroki_check_friend_backlink($target_url) {
    if (empty($target_url)) {
        return false;
    }

    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $site_host_clean = preg_replace('/^www\./', '', $site_host);
    $site_host_www = 'www.' . $site_host_clean;
    $site_url_full = home_url();
    $site_url_http = str_replace('https://', 'http://', $site_url_full);
    $site_url_https = str_replace('http://', 'https://', $site_url_full);
    $site_name = get_bloginfo('name');

    $keywords_str = '友情链接,友链,友人帐,合作伙伴,推荐网站,友情,友站,友邻,小伙伴,站点推荐,博客邻居,友情互链,交换链接,friend,friends,friendly,link,links,flink,blogroll,partner,partners,exchange,site,sites,follow,following,community';
    $friend_link_keywords = array_map('trim', explode(',', $keywords_str));
    $friend_link_keywords = array_filter($friend_link_keywords);

    /* 🌐 模拟真实浏览器请求 */
    $request_args = array(
        'timeout' => 20,
        'sslverify' => false,
        'redirection' => 5,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 Edg/125.0.0.0',
        'headers' => array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
        )
    );

    /* 🔍 页面内容检测闭包 */
    $check_page_for_backlink = function($body) use ($site_host_clean, $site_host_www, $site_url_full, $site_url_http, $site_url_https, $site_name) {
        if (empty($body)) {
            return false;
        }

        $host_pattern = '/\b' . preg_quote($site_host_clean, '/') . '\b/i';
        if (
            preg_match($host_pattern, $body) ||
            stripos($body, $site_host_www) !== false ||
            stripos($body, $site_url_full) !== false ||
            stripos($body, $site_url_http) !== false ||
            stripos($body, $site_url_https) !== false ||
            (mb_strlen($site_name) >= 2 && stripos($body, $site_name) !== false)
        ) {
            return true;
        }

        /* 📦 动态框架预渲染内容 */
        if (
            stripos($body, '__NEXT_DATA__') !== false ||
            stripos($body, 'data-server-rendered="true"') !== false ||
            stripos($body, 'data-reactroot') !== false
        ) {
            preg_match_all('/"url":"([^"]+)"/i', $body, $json_urls);
            if (!empty($json_urls[1])) {
                foreach ($json_urls[1] as $json_url) {
                    $decoded_url = urldecode($json_url);
                    if (
                        stripos($decoded_url, $site_host_clean) !== false ||
                        stripos($decoded_url, $site_url_full) !== false
                    ) {
                        return true;
                    }
                }
            }
        }

        /* 📝 标题和描述检测 */
        preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $title_match);
        if (!empty($title_match[1]) && (stripos($title_match[1], $site_name) !== false || stripos($title_match[1], $site_host_clean) !== false)) {
            return true;
        }

        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/is', $body, $meta_match);
        if (!empty($meta_match[1]) && (stripos($meta_match[1], $site_name) !== false || stripos($meta_match[1], $site_host_clean) !== false)) {
            return true;
        }

        /* 🖼️ 图片链接检测 */
        preg_match_all('/<img\s+[^>]*>/is', $body, $img_tags);
        if (!empty($img_tags[0])) {
            foreach ($img_tags[0] as $img_tag) {
                preg_match_all('/\s+([a-zA-Z-]+)=["\']([^"\']*)["\']/i', $img_tag, $attributes);
                if (empty($attributes[1]) || empty($attributes[2])) {
                    continue;
                }
                $img_data = array_combine($attributes[1], $attributes[2]);
                $check_fields = array('src', 'srcset', 'alt', 'title', 'data-src', 'data-lazy', 'data-original');
                foreach ($check_fields as $field) {
                    if (isset($img_data[$field])) {
                        $value = $img_data[$field];
                        if (
                            preg_match($host_pattern, $value) ||
                            stripos($value, $site_host_www) !== false ||
                            stripos($value, $site_url_full) !== false ||
                            stripos($value, $site_url_http) !== false ||
                            stripos($value, $site_url_https) !== false ||
                            (mb_strlen($site_name) >= 2 && stripos($value, $site_name) !== false)
                        ) {
                            return true;
                        }
                    }
                }
            }
        }

        /* 🎨 CSS 背景图片检测 */
        preg_match_all('/background(?:-image)?\s*:\s*url\(["\']?([^"\')]+)["\']?\)/i', $body, $bg_images);
        if (!empty($bg_images[1])) {
            foreach ($bg_images[1] as $bg_url) {
                if (
                    preg_match($host_pattern, $bg_url) ||
                    stripos($bg_url, $site_host_www) !== false ||
                    stripos($bg_url, $site_url_full) !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    };

    /* 🔄 带重试的请求 */
    $safe_remote_get = function($url, $args) {
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            sleep(2);
            $response = wp_remote_get($url, $args);
        }
        return $response;
    };

    $response = $safe_remote_get($target_url, $request_args);
    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return false;
    }

    if ($check_page_for_backlink($body)) {
        return true;
    }

    /* 🔗 查找友链页面候选链接 */
    $base_url = trailingslashit($target_url);
    $candidate_links = array();
    $all_links = array();
    preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $body, $matches);

    if (!empty($matches[1])) {
        foreach ($matches[1] as $index => $href) {
            $href = trim($href);
            $link_text = trim(strip_tags($matches[2][$index]));

            if (
                empty($href) ||
                strpos($href, 'javascript:') === 0 ||
                strpos($href, 'mailto:') === 0 ||
                strpos($href, 'tel:') === 0 ||
                strpos($href, '#') === 0 ||
                strpos($href, '?') === 0
            ) {
                continue;
            }

            if (strpos($href, 'http') !== 0) {
                $href = $base_url . ltrim($href, '/');
            }

            $link_host = parse_url($href, PHP_URL_HOST);
            $target_host = parse_url($target_url, PHP_URL_HOST);
            if (empty($link_host) || empty($target_host)) {
                continue;
            }

            if (preg_replace('/^www\./', '', $link_host) !== preg_replace('/^www\./', '', $target_host)) {
                continue;
            }

            $href_normalized = trailingslashit(strtolower($href));
            if (in_array($href_normalized, $all_links, true)) {
                continue;
            }
            $all_links[] = $href_normalized;

            $has_keyword_in_url = false;
            foreach ($friend_link_keywords as $kw) {
                if (stripos($href, $kw) !== false) {
                    $has_keyword_in_url = true;
                    break;
                }
            }

            $has_keyword_in_text = false;
            if (!empty($link_text)) {
                foreach ($friend_link_keywords as $kw) {
                    if (mb_stripos($link_text, $kw) !== false) {
                        $has_keyword_in_text = true;
                        break;
                    }
                }
            }

            if ($has_keyword_in_url || $has_keyword_in_text) {
                $candidate_links[] = $href;
            }
        }
    }

    /* 🔍 常见友链路径 */
    $common_paths = array('friend', 'link', 'links', 'friends', 'blogroll', 'flink', 'partner', 'partners', 'site', 'sites', 'about', 'contact');
    foreach ($common_paths as $path) {
        $test_url = $base_url . $path;
        $test_url_normalized = trailingslashit(strtolower($test_url));
        if (!in_array($test_url_normalized, $all_links, true)) {
            $candidate_links[] = $test_url;
            $all_links[] = $test_url_normalized;
        }
    }

    if (!empty($candidate_links)) {
        $candidate_links = array_slice($candidate_links, 0, 15);
        foreach ($candidate_links as $flink_url) {
            usleep(300000);
            $flink_response = $safe_remote_get($flink_url, $request_args);
            if (is_wp_error($flink_response)) {
                continue;
            }
            $flink_body = wp_remote_retrieve_body($flink_response);
            if (empty($flink_body)) {
                continue;
            }
            if ($check_page_for_backlink($flink_body)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * 🚀 初始化友链检测模块
 */
function shiroki_init_friend_link_checker() {
    Shiroki_Friend_Link_Checker::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_friend_link_checker');
