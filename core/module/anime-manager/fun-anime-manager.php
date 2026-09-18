<?php
/**
 * 📺 追番管理模块
 * 🎨 拟态拟物玻璃质感设计，与评论网格 UI 风格一致
 *
 * @package Lolimeow_Shiroki
 * @subpackage Anime_Manager
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 📺 追番管理主类
 */
class Shiroki_Anime_Manager {

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📝 选项键名
     */
    private const OPTION_KEY = 'shiroki_anime_list';
    private const PAGE_SLUG = 'shiroki-anime-manager';
    private const PAGE_CATEGORY_META_KEY = '_shiroki_anime_page_category_name';
    private const ANIME_PAGE_TEMPLATE = 'page/page-anime.php';

    /**
     * 📊 状态配置
     */
    private const STATUS_CONFIG = array(
        'watching'  => array('label' => '正在追', 'color' => 'blue'),
        'completed' => array('label' => '已看完', 'color' => 'green'),
        'planned'   => array('label' => '计划看', 'color' => 'orange'),
        'on_hold'   => array('label' => '暂停中', 'color' => 'purple'),
        'dropped'   => array('label' => '已弃番', 'color' => 'red'),
    );

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
        /* 📋 注册子菜单（盒子萌主题设置 > 追番管理，优先级 15，位于友链检测之上） */
        add_action('admin_menu', array($this, 'register_submenu'), 15);

        /* 🎨 加载资源 */
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        /* 📡 AJAX 处理 */
        add_action('wp_ajax_shiroki_get_anime_list', array($this, 'ajax_get_anime_list'));
        add_action('wp_ajax_shiroki_save_anime', array($this, 'ajax_save_anime'));
        add_action('wp_ajax_shiroki_delete_anime', array($this, 'ajax_delete_anime'));
        add_action('wp_ajax_shiroki_bulk_delete_anime', array($this, 'ajax_bulk_delete_anime'));

        /* 📄 追番页面分类设置 */
        add_action('add_meta_boxes', array($this, 'register_page_meta_box'));
        add_action('save_post_page', array($this, 'save_page_meta'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_page_edit_assets'));
    }

    /**
     * 📋 注册子菜单
     */
    public function register_submenu() {
        add_submenu_page(
            'boxmoe_options',
            __('追番管理', 'textdomain'),
            __('追番管理', 'textdomain'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        $is_our_page = isset($_GET['page']) && sanitize_text_field($_GET['page']) === self::PAGE_SLUG;

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
        $css_path = $theme_dir . '/assets/css/admin/anime-manager/anime-manager.css';
        $js_path = $theme_dir . '/assets/js/admin/anime-manager/anime-manager.js';
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

        /* 🎨 追番管理样式 */
        wp_enqueue_style(
            'shiroki-anime-manager',
            $theme_uri . '/assets/css/admin/anime-manager/anime-manager.css',
            array('admin-variables'),
            $css_version
        );

        /* 📦 追番管理脚本 */
        wp_enqueue_script(
            'shiroki-anime-manager',
            $theme_uri . '/assets/js/admin/anime-manager/anime-manager.js',
            array('jquery'),
            $js_version,
            true
        );

        /* 🎯 传递 AJAX 配置 */
        wp_localize_script('shiroki-anime-manager', 'shirokiAnimeConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_anime_nonce'),
            'statusConfig' => self::STATUS_CONFIG,
            'categoryNames' => $this->get_category_names(),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无番剧',
                'addAnime' => '➕ 添加番剧',
                'editAnime' => '编辑番剧',
                'save' => '保存',
                'cancel' => '取消',
                'delete' => '删除',
                'deleteConfirm' => '确定要删除这部番剧吗？删除后无法恢复。',
                'bulkDeleteConfirm' => '确定要删除选中的 {count} 部番剧吗？删除后无法恢复。',
                'emptySelection' => '请先选择番剧',
                'titlePlaceholder' => '请输入番剧名称',
                'coverPlaceholder' => '封面图片 URL',
                'linkPlaceholder' => '相关链接（可选）',
                'descriptionPlaceholder' => '番剧简介',
                'tagsPlaceholder' => '多个标签用 , 、 ， 分隔',
                'protagonistsPlaceholder' => '多个主角用 , 、 ， 分隔',
                'voiceActorsPlaceholder' => '多个声优用 , 、 ， 分隔',
                'categoryNamePlaceholder' => '例如：悄悄追番、日剧、公开',
                'all' => '全部',
                'selected' => '已选择',
                'search' => '搜索番剧名称...',
                'copySuccess' => '复制成功',
                'copyError' => '复制失败',
                'saveSuccess' => '保存成功',
                'saveError' => '保存失败',
                'deleteSuccess' => '删除成功',
                'deleteError' => '删除失败',
                'loadError' => '加载失败'
            )
        ));
    }

    /**
     * 🖥️ 渲染管理页面
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'textdomain'));
        }

        $counts = $this->get_status_counts();
        ?>
        <div class="wrap shiroki-anime-manager-wrap">
            <h1 class="wp-heading-inline" style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

            <!-- 🎯 自定义页面头部 -->
            <div class="shiroki-anime-manager-header">
                <div class="shiroki-anime-manager-title">
                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/404/routes.svg'); ?>" alt="" class="shiroki-anime-manager-title-icon">
                    <span>追番管理</span>
                </div>
                <button type="button" class="shiroki-anime-manager-add-btn" id="shiroki-anime-add-btn">
                    <span>➕</span>
                    <span>添加番剧</span>
                </button>
            </div>

            <!-- 🎯 顶部工具栏 -->
            <div class="shiroki-anime-manager-top-bar">
                <div class="shiroki-anime-manager-filters">
                    <div class="shiroki-anime-manager-filter-row">
                        <span class="shiroki-anime-manager-filter-label">📊 状态：</span>
                        <div class="shiroki-anime-manager-status-options">
                            <button type="button" class="shiroki-anime-manager-filter-btn shiroki-anime-manager-status-btn active" data-status="all">
                                📁 全部 (<?php echo intval($counts['all']); ?>)
                            </button>
                            <?php foreach (self::STATUS_CONFIG as $key => $config) : ?>
                            <button type="button" class="shiroki-anime-manager-filter-btn shiroki-anime-manager-status-btn" data-status="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($config['label']); ?> (<?php echo intval($counts[$key]); ?>)
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="shiroki-anime-manager-bulk-actions" id="shiroki-anime-manager-bulk-actions" style="display: none;">
                    <span class="shiroki-anime-manager-bulk-count">已选择 <span class="shiroki-anime-manager-bulk-count-num">0</span> 个</span>
                    <button class="shiroki-anime-manager-bulk-btn shiroki-anime-manager-bulk-delete" data-action="delete">
                        🗑️ 批量删除
                    </button>
                    <button class="shiroki-anime-manager-bulk-btn shiroki-anime-manager-bulk-cancel" data-action="cancel">
                        ❌ 取消选择
                    </button>
                </div>

                <div class="shiroki-anime-manager-actions-right">
                    <div class="shiroki-anime-manager-search-wrapper">
                        <div class="shiroki-anime-manager-search">
                            <input type="text" id="shiroki-anime-manager-search" placeholder="🔍 搜索番剧名称..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⏳ 加载状态 -->
            <div class="shiroki-anime-manager-loading" id="shiroki-anime-manager-loading">
                <div class="shiroki-anime-manager-loading-spinner"></div>
                <span>⏳ 加载中...</span>
            </div>

            <!-- 📦 番剧网格 -->
            <div class="shiroki-anime-manager-grid" id="shiroki-anime-manager-grid"></div>

            <!-- 📭 空状态 -->
            <div class="shiroki-anime-manager-empty" id="shiroki-anime-manager-empty" style="display: none;">
                <svg class="shiroki-anime-manager-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect>
                    <line x1="7" y1="2" x2="7" y2="22"></line>
                    <line x1="17" y1="2" x2="17" y2="22"></line>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <line x1="2" y1="7" x2="7" y2="7"></line>
                    <line x1="2" y1="17" x2="7" y2="17"></line>
                    <line x1="17" y1="17" x2="22" y2="17"></line>
                    <line x1="17" y1="7" x2="22" y2="7"></line>
                </svg>
                <div class="shiroki-anime-manager-empty-text">📭 暂无番剧</div>
                <div class="shiroki-anime-manager-empty-subtext">点击右上角“添加番剧”按钮开始管理你的追番列表</div>
            </div>
        </div>

        <!-- 📝 番剧编辑弹窗 -->
        <div class="shiroki-anime-modal" id="shiroki-anime-modal" style="display: none;">
            <div class="shiroki-anime-modal-overlay"></div>
            <div class="shiroki-anime-modal-content">
                <div class="shiroki-anime-modal-header">
                    <h3 id="shiroki-anime-modal-title">添加番剧</h3>
                    <button type="button" class="shiroki-anime-modal-close" id="shiroki-anime-modal-close">×</button>
                </div>
                <div class="shiroki-anime-modal-body">
                    <form id="shiroki-anime-form">
                        <input type="hidden" id="shiroki-anime-id" value="">
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-title">番剧名称 <span class="required">*</span></label>
                                <input type="text" id="shiroki-anime-title" name="title" required placeholder="请输入番剧名称">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-status">观看状态</label>
                                <select id="shiroki-anime-status" name="status">
                                    <?php foreach (self::STATUS_CONFIG as $key => $config) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($config['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-rating">评分</label>
                                <input type="number" id="shiroki-anime-rating" name="rating" min="0" max="10" step="0.1" value="0">
                            </div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-cover">封面图片 URL</label>
                                <input type="url" id="shiroki-anime-cover" name="cover" placeholder="https://gl.baimu.live/image.jpg">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-watch-link">看番链接</label>
                                <input type="url" id="shiroki-anime-watch-link" name="watch_link" placeholder="https://gl.baimu.live/watch">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-official-link">官网链接</label>
                                <input type="url" id="shiroki-anime-official-link" name="official_link" placeholder="https://gl.baimu.live/official">
                            </div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-start-date">上映时间</label>
                                <input type="date" id="shiroki-anime-start-date" name="start_date">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-end-date">完结时间</label>
                                <input type="date" id="shiroki-anime-end-date" name="end_date">
                            </div>
                            <div class="shiroki-anime-form-group"></div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-progress">当前进度</label>
                                <input type="number" id="shiroki-anime-progress" name="progress" min="0" value="0">
                            </div>
                            <div class="shiroki-anime-form-group shiroki-anime-total-group">
                                <label for="shiroki-anime-total">总话数</label>
                                <input type="number" id="shiroki-anime-total" name="total" min="0" value="0">
                            </div>
                            <div class="shiroki-anime-form-group shiroki-anime-infinite-group">
                                <label class="shiroki-anime-checkbox-label">
                                    <input type="checkbox" id="shiroki-anime-is-infinite" name="is_infinite" value="1">
                                    <span>无限话</span>
                                </label>
                            </div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group shiroki-anime-autocomplete-wrap">
                                <label for="shiroki-anime-category-name">分类名称</label>
                                <input type="text" id="shiroki-anime-category-name" name="category_name" autocomplete="off" placeholder="例如：悄悄追番、日剧、公开">
                                <div class="shiroki-anime-autocomplete-list" id="shiroki-anime-category-name-suggestions"></div>
                            </div>
                            <div class="shiroki-anime-form-group shiroki-anime-private-group">
                                <label class="shiroki-anime-checkbox-label">
                                    <input type="checkbox" id="shiroki-anime-is-private" name="is_private" value="1">
                                    <span>设为私密</span>
                                </label>
                                <p class="shiroki-anime-field-hint">私密番剧仅管理员可见</p>
                            </div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-tags">分类标签</label>
                                <input type="text" id="shiroki-anime-tags" name="tags" placeholder="科幻、战斗、校园">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-protagonists">主角标签</label>
                                <input type="text" id="shiroki-anime-protagonists" name="protagonists" placeholder="多个主角用 , 、 ， 分隔">
                            </div>
                            <div class="shiroki-anime-form-group">
                                <label for="shiroki-anime-voice-actors">声优标签</label>
                                <input type="text" id="shiroki-anime-voice-actors" name="voice_actors" placeholder="多个声优用 , 、 ， 分隔">
                            </div>
                        </div>
                        <div class="shiroki-anime-form-row shiroki-anime-form-row-3">
                            <div class="shiroki-anime-form-group shiroki-anime-form-span-3">
                                <label for="shiroki-anime-description">简介</label>
                                <textarea id="shiroki-anime-description" name="description" rows="4" placeholder="请输入番剧简介"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="shiroki-anime-modal-footer">
                    <button type="button" class="shiroki-anime-modal-btn shiroki-anime-modal-cancel" id="shiroki-anime-modal-cancel">取消</button>
                    <button type="button" class="shiroki-anime-modal-btn shiroki-anime-modal-save" id="shiroki-anime-modal-save">保存</button>
                </div>
            </div>
        </div>

        <!-- 🍞 Toast 提示 -->
        <div class="shiroki-anime-toast" id="shiroki-anime-toast" style="display: none;">
            <span class="shiroki-anime-toast-icon"></span>
            <span class="shiroki-anime-toast-message"></span>
        </div>
        <?php
    }

    /**
     * 📡 AJAX 获取番剧列表
     */
    public function ajax_get_anime_list() {
        if (!check_ajax_referer('shiroki_anime_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $anime_list = $this->get_anime_list();

        if ($status !== 'all') {
            $anime_list = array_filter($anime_list, function($anime) use ($status) {
                return isset($anime['status']) && $anime['status'] === $status;
            });
        }

        if (!empty($search)) {
            $search_lower = mb_strtolower($search);
            $anime_list = array_filter($anime_list, function($anime) use ($search_lower) {
                $title = isset($anime['title']) ? mb_strtolower($anime['title']) : '';
                $category_name = isset($anime['category_name']) ? mb_strtolower($anime['category_name']) : '';
                $tags = isset($anime['tags']) && is_array($anime['tags']) ? implode(' ', $anime['tags']) : '';
                $protagonists = isset($anime['protagonists']) && is_array($anime['protagonists']) ? implode(' ', $anime['protagonists']) : '';
                $voice_actors = isset($anime['voice_actors']) && is_array($anime['voice_actors']) ? implode(' ', $anime['voice_actors']) : '';
                $description = isset($anime['description']) ? mb_strtolower($anime['description']) : '';
                return strpos($title, $search_lower) !== false
                    || strpos($category_name, $search_lower) !== false
                    || strpos(mb_strtolower($tags), $search_lower) !== false
                    || strpos(mb_strtolower($protagonists), $search_lower) !== false
                    || strpos(mb_strtolower($voice_actors), $search_lower) !== false
                    || strpos($description, $search_lower) !== false;
            });
        }

        $anime_list = array_values($anime_list);

        wp_send_json_success(array(
            'anime' => $anime_list,
            'counts' => $this->get_status_counts(),
            'categoryNames' => $this->get_category_names(),
        ));
    }

    /**
     * 📡 AJAX 保存番剧
     */
    public function ajax_save_anime() {
        if (!check_ajax_referer('shiroki_anime_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

        if (empty($title)) {
            wp_send_json_error(array('message' => '番剧名称不能为空'));
        }

        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'planned';
        if (!isset(self::STATUS_CONFIG[$status])) {
            $status = 'planned';
        }

        $anime_list = $this->get_anime_list();

        // 检查番剧名称是否已存在（编辑时排除自身）
        $title_lower = mb_strtolower($title);
        foreach ($anime_list as $item) {
            if (isset($item['title']) && mb_strtolower($item['title']) === $title_lower) {
                if (empty($id) || $item['id'] !== $id) {
                    wp_send_json_error(array('message' => '添加失败，已存在同名番剧'));
                }
            }
        }

        $is_infinite = isset($_POST['is_infinite']) && $_POST['is_infinite'] === '1';
        $is_private = isset($_POST['is_private']) && $_POST['is_private'] === '1';

        $anime_item = array(
            'id'            => empty($id) ? $this->generate_id() : $id,
            'title'         => $title,
            'category_name' => isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '',
            'cover'         => isset($_POST['cover']) ? esc_url_raw($_POST['cover']) : '',
            'status'        => $status,
            'progress'      => isset($_POST['progress']) ? intval($_POST['progress']) : 0,
            'total'         => isset($_POST['total']) ? intval($_POST['total']) : 0,
            'is_infinite'   => $is_infinite,
            'is_private'    => $is_private,
            'rating'        => isset($_POST['rating']) ? floatval($_POST['rating']) : 0,
            'tags'          => $this->parse_tags(isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : ''),
            'protagonists'  => $this->parse_tags(isset($_POST['protagonists']) ? sanitize_text_field($_POST['protagonists']) : ''),
            'voice_actors'  => $this->parse_tags(isset($_POST['voice_actors']) ? sanitize_text_field($_POST['voice_actors']) : ''),
            'watch_link'    => isset($_POST['watch_link']) ? esc_url_raw($_POST['watch_link']) : '',
            'official_link' => isset($_POST['official_link']) ? esc_url_raw($_POST['official_link']) : '',
            'start_date'    => isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '',
            'end_date'      => isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '',
            'description'   => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
            'updated_at'    => current_time('mysql'),
        );

        if (empty($id)) {
            $anime_item['created_at'] = current_time('mysql');
            $anime_list[] = $anime_item;
        } else {
            $found = false;
            foreach ($anime_list as $index => $item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $anime_list[$index] = array_merge($item, $anime_item);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                wp_send_json_error(array('message' => '未找到要更新的番剧'));
            }
        }

        $this->save_anime_list($anime_list);

        // 返回前再次规范化数据，确保与数据库一致
        $anime_item = $this->validate_anime_item($anime_item);
        if (!$anime_item) {
            wp_send_json_error(array('message' => '数据验证失败，请检查输入内容'));
        }

        wp_send_json_success(array(
            'message' => '保存成功',
            'anime' => $anime_item,
            'counts' => $this->get_status_counts()
        ));
    }

    /**
     * 📡 AJAX 删除番剧
     */
    public function ajax_delete_anime() {
        if (!check_ajax_referer('shiroki_anime_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        if (empty($id)) {
            wp_send_json_error(array('message' => '缺少番剧 ID'));
        }

        $anime_list = $this->get_anime_list();
        $new_list = array_filter($anime_list, function($item) use ($id) {
            return !(isset($item['id']) && $item['id'] === $id);
        });

        $this->save_anime_list(array_values($new_list));

        wp_send_json_success(array(
            'message' => '删除成功',
            'counts' => $this->get_status_counts()
        ));
    }

    /**
     * 📡 AJAX 批量删除番剧
     */
    public function ajax_bulk_delete_anime() {
        if (!check_ajax_referer('shiroki_anime_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();
        $ids = array_map('sanitize_text_field', $ids);

        if (empty($ids)) {
            wp_send_json_error(array('message' => '请先选择番剧'));
        }

        $anime_list = $this->get_anime_list();
        $new_list = array_filter($anime_list, function($item) use ($ids) {
            return !(isset($item['id']) && in_array($item['id'], $ids, true));
        });

        $this->save_anime_list(array_values($new_list));

        wp_send_json_success(array(
            'message' => '批量删除成功',
            'counts' => $this->get_status_counts()
        ));
    }

    /**
     * 📊 获取状态统计
     */
    private function get_status_counts() {
        $anime_list = $this->get_anime_list();
        $counts = array(
            'all' => count($anime_list),
            'watching' => 0,
            'completed' => 0,
            'planned' => 0,
            'on_hold' => 0,
            'dropped' => 0,
        );

        foreach ($anime_list as $anime) {
            $status = isset($anime['status']) ? $anime['status'] : 'planned';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    /**
     * 📋 获取番剧列表（自动清理无效数据）
     */
    public function get_anime_list() {
        $anime_list = get_option(self::OPTION_KEY, array());
        if (!is_array($anime_list)) {
            $anime_list = array();
        }

        $cleaned_list = $this->clean_anime_list($anime_list);

        // 如果清理后发现数据有变化，自动回写数据库
        if ($cleaned_list !== $anime_list) {
            update_option(self::OPTION_KEY, $cleaned_list);
        }

        return $cleaned_list;
    }

    /**
     * 🧹 清理并验证番剧列表
     *
     * 自动移除非数组项、缺少关键字段的项，并统一字段类型。
     */
    public function clean_anime_list($anime_list) {
        if (!is_array($anime_list)) {
            return array();
        }

        $cleaned = array();
        foreach ($anime_list as $anime) {
            $item = $this->validate_anime_item($anime);
            if (is_array($item) && !empty($item['id']) && !empty($item['title'])) {
                $cleaned[] = $item;
            }
        }

        return array_values($cleaned);
    }

    /**
     * ✅ 验证并规范化单个番剧数据
     */
    public function validate_anime_item($anime) {
        if (!is_array($anime)) {
            return null;
        }

        // 必须有唯一 ID 和名称
        $id = isset($anime['id']) ? sanitize_text_field($anime['id']) : '';
        $title = isset($anime['title']) ? sanitize_text_field($anime['title']) : '';
        if (empty($id) || empty($title)) {
            return null;
        }

        // 状态必须是合法值，否则重置为 planned
        $status = isset($anime['status']) ? sanitize_text_field($anime['status']) : 'planned';
        if (!isset(self::STATUS_CONFIG[$status])) {
            $status = 'planned';
        }

        // 数值字段统一类型
        $progress = isset($anime['progress']) ? intval($anime['progress']) : 0;
        $total = isset($anime['total']) ? intval($anime['total']) : 0;
        $rating = isset($anime['rating']) ? floatval($anime['rating']) : 0;
        $is_infinite = !empty($anime['is_infinite']);
        $is_private = !empty($anime['is_private']);

        // 确保数值在合理范围
        $progress = max(0, $progress);
        $total = max(0, $total);
        $rating = max(0, min(10, $rating));

        // 链接字段清洗
        $cover = isset($anime['cover']) ? esc_url_raw($anime['cover']) : '';
        $watch_link = isset($anime['watch_link']) ? esc_url_raw($anime['watch_link']) : '';
        $official_link = isset($anime['official_link']) ? esc_url_raw($anime['official_link']) : '';

        // 日期字段清洗（仅保留合法 YYYY-MM-DD 格式）
        $start_date = isset($anime['start_date']) ? sanitize_text_field($anime['start_date']) : '';
        $end_date = isset($anime['end_date']) ? sanitize_text_field($anime['end_date']) : '';
        if (!empty($start_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $start_date = '';
        }
        if (!empty($end_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $end_date = '';
        }

        // 标签统一为数组
        $tags = $this->normalize_tags_field(isset($anime['tags']) ? $anime['tags'] : array());
        $protagonists = $this->normalize_tags_field(isset($anime['protagonists']) ? $anime['protagonists'] : array());
        $voice_actors = $this->normalize_tags_field(isset($anime['voice_actors']) ? $anime['voice_actors'] : array());

        $description = isset($anime['description']) ? sanitize_textarea_field($anime['description']) : '';
        $category_name = isset($anime['category_name']) ? sanitize_text_field($anime['category_name']) : '';
        $created_at = isset($anime['created_at']) ? sanitize_text_field($anime['created_at']) : current_time('mysql');
        $updated_at = isset($anime['updated_at']) ? sanitize_text_field($anime['updated_at']) : current_time('mysql');

        return array(
            'id'            => $id,
            'title'         => $title,
            'category_name' => $category_name,
            'cover'         => $cover,
            'status'        => $status,
            'progress'      => $progress,
            'total'         => $total,
            'is_infinite'   => $is_infinite,
            'is_private'    => $is_private,
            'rating'        => $rating,
            'tags'          => $tags,
            'protagonists'  => $protagonists,
            'voice_actors'  => $voice_actors,
            'watch_link'    => $watch_link,
            'official_link' => $official_link,
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'description'   => $description,
            'created_at'    => $created_at,
            'updated_at'    => $updated_at,
        );
    }

    /**
     * 💾 保存番剧列表
     */
    public function save_anime_list($anime_list) {
        $anime_list = $this->clean_anime_list($anime_list);

        // 确保每个番剧都有唯一 ID
        foreach ($anime_list as $index => $anime) {
            if (empty($anime['id'])) {
                $anime_list[$index]['id'] = 'anime_' . time() . '_' . wp_rand(1000, 9999) . '_' . $index;
            }
        }

        update_option(self::OPTION_KEY, array_values($anime_list));
    }

    /**
     * 🔖 解析标签（支持 , 、 ， 分隔）
     */
    private function parse_tags($tags_string) {
        if (empty($tags_string)) {
            return array();
        }

        $tags = preg_split('/[,，、]+/u', $tags_string);
        $tags = array_map('trim', $tags);
        $tags = array_filter($tags, function($tag) {
            return $tag !== '';
        });

        return array_values(array_unique($tags));
    }

    /**
     * 🏷️ 规范化标签字段
     */
    private function normalize_tags_field($value) {
        if (is_array($value)) {
            $tags = array_values(array_filter(array_map('trim', $value), function($tag) {
                return $tag !== '';
            }));
            return array_values(array_unique($tags));
        }

        if (is_string($value)) {
            return $this->parse_tags($value);
        }

        return array();
    }

    /**
     * 🆔 生成唯一 ID
     */
    private function generate_id() {
        return 'anime_' . time() . '_' . wp_rand(1000, 9999);
    }

    /**
     * 👁️ 按访问权限过滤番剧列表
     */
    public function filter_anime_for_viewer($anime_list) {
        if (!is_array($anime_list)) {
            return array();
        }

        if (current_user_can('manage_options')) {
            return array_values($anime_list);
        }

        return array_values(array_filter($anime_list, function($anime) {
            return empty($anime['is_private']);
        }));
    }

    /**
     * 📁 获取已使用的分类名称
     */
    public function get_category_names() {
        $names = array();

        foreach ($this->get_anime_list() as $anime) {
            $name = isset($anime['category_name']) ? trim((string) $anime['category_name']) : '';
            if ($name !== '') {
                $names[$name] = $name;
            }
        }

        natcasesort($names);
        return array_values($names);
    }

    /**
     * 📄 注册追番页面设置 Meta Box
     */
    public function register_page_meta_box() {
        add_meta_box(
            'shiroki_anime_page_settings',
            __('追番页面设置', 'textdomain'),
            array($this, 'render_page_meta_box'),
            'page',
            'side',
            'default'
        );
    }

    /**
     * 🖥️ 渲染追番页面 Meta Box
     */
    public function render_page_meta_box($post) {
        wp_nonce_field('shiroki_anime_page_meta', 'shiroki_anime_page_meta_nonce');

        $category_name = get_post_meta($post->ID, self::PAGE_CATEGORY_META_KEY, true);
        $template = get_page_template_slug($post);
        $is_anime_template = ($template === self::ANIME_PAGE_TEMPLATE);
        ?>
        <div class="shiroki-anime-page-meta" id="shiroki-anime-page-meta" <?php echo $is_anime_template ? '' : 'style="display:none;"'; ?>>
            <p>
                <label for="shiroki-anime-page-category-name"><strong><?php esc_html_e('分类名称', 'textdomain'); ?></strong></label>
            </p>
            <div class="shiroki-anime-autocomplete-wrap">
                <input
                    type="text"
                    class="widefat"
                    id="shiroki-anime-page-category-name"
                    name="shiroki_anime_page_category_name"
                    value="<?php echo esc_attr($category_name); ?>"
                    autocomplete="off"
                    placeholder="<?php esc_attr_e('例如：2024冬季番', 'textdomain'); ?>"
                >
                <div class="shiroki-anime-autocomplete-list" id="shiroki-anime-page-category-suggestions"></div>
            </div>
            <p class="description">
                <?php esc_html_e('仅展示该分类名称下的番剧。留空则展示全部分类。', 'textdomain'); ?>
            </p>
        </div>
        <p class="shiroki-anime-page-meta-hint" id="shiroki-anime-page-meta-hint" <?php echo $is_anime_template ? 'style="display:none;"' : ''; ?>>
            <?php esc_html_e('请先将页面模板设置为「追番页面」。', 'textdomain'); ?>
        </p>
        <?php
    }

    /**
     * 💾 保存追番页面 Meta
     */
    public function save_page_meta($post_id, $post) {
        if (!isset($_POST['shiroki_anime_page_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['shiroki_anime_page_meta_nonce'])), 'shiroki_anime_page_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }

        $category_name = isset($_POST['shiroki_anime_page_category_name'])
            ? sanitize_text_field(wp_unslash($_POST['shiroki_anime_page_category_name']))
            : '';

        if ($category_name === '') {
            delete_post_meta($post_id, self::PAGE_CATEGORY_META_KEY);
            return;
        }

        update_post_meta($post_id, self::PAGE_CATEGORY_META_KEY, $category_name);
    }

    /**
     * 🎨 加载页面编辑资源
     */
    public function enqueue_page_edit_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'page') {
            return;
        }

        $theme_dir = get_stylesheet_directory();
        $theme_uri = get_stylesheet_directory_uri();
        $css_path = $theme_dir . '/assets/css/admin/anime-manager/anime-manager.css';
        $js_path = $theme_dir . '/assets/js/admin/anime-manager/anime-page-meta.js';
        $css_version = file_exists($css_path) ? (string) filemtime($css_path) : wp_get_theme()->get('Version');
        $js_version = file_exists($js_path) ? (string) filemtime($js_path) : wp_get_theme()->get('Version');

        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            wp_get_theme()->get('Version')
        );

        wp_enqueue_style(
            'shiroki-anime-page-meta',
            $theme_uri . '/assets/css/admin/anime-manager/anime-manager.css',
            array('admin-variables'),
            $css_version
        );

        wp_enqueue_script(
            'shiroki-anime-page-meta',
            $theme_uri . '/assets/js/admin/anime-manager/anime-page-meta.js',
            array('jquery'),
            $js_version,
            true
        );

        wp_localize_script('shiroki-anime-page-meta', 'shirokiAnimePageMeta', array(
            'templateSlug' => self::ANIME_PAGE_TEMPLATE,
            'categoryNames' => $this->get_category_names(),
        ));
    }
}

/**
 * 🚀 初始化追番管理模块
 */
Shiroki_Anime_Manager::get_instance();
