<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💎 拟态拟物玻璃质感媒体库UI设计
 * 🎨 替换原版WordPress媒体库为网格卡片式布局
 * 
 * @package Lolimeow_Shiroki
 * @subpackage Media_Library
 * @since 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 媒体库UI主类
 */
class Shiroki_Media_Library_UI {
    
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
        // 加载自定义样式和脚本
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX处理
        add_action('wp_ajax_shiroki_get_media_items', array($this, 'ajax_get_media_items'));
        add_action('wp_ajax_shiroki_copy_media_url', array($this, 'ajax_copy_media_url'));
        add_action('wp_ajax_shiroki_delete_media', array($this, 'ajax_delete_media'));
        
        // 修改媒体库每页显示数量
        add_filter('upload_per_page', array($this, 'set_media_per_page'));

        add_action('load-upload.php', array($this, 'force_grid_view'));
        add_action('all_admin_notices', array($this, 'render_custom_media_ui'));
    }

    /**
     * WordPress 的列表模式不会加载媒体脚本，统一使用网格模式作为自定义页面的底座。
     */
    public function force_grid_view() {
        if (isset($_GET['mode']) && $_GET['mode'] === 'list') {
            $_GET['mode'] = 'grid';
        }

        if (get_user_option('media_library_mode', get_current_user_id()) !== 'grid') {
            update_user_option(get_current_user_id(), 'media_library_mode', 'grid');
        }
    }
    
    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        $theme_uri = get_template_directory_uri();
        $theme_dir = get_template_directory();
        $version = wp_get_theme()->get('Version');

        /* 🎨 先加载统一变量文件 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );

        /* ☀️ 媒体库页面（upload.php）样式 */
        if ($hook === 'upload.php') {
            $media_css = $theme_dir . '/assets/css/admin/media-library/media-library.css';
            $media_js = $theme_dir . '/assets/js/admin/media-library/media-library.js';

            // 页面只使用自定义网格，避免隐藏的原生网格再次发起附件查询。
            wp_dequeue_script('media-grid');

            // ☀️ 加载CSS
            wp_enqueue_style(
                'shiroki-media-library',
                $theme_uri . '/assets/css/admin/media-library/media-library.css',
                array('admin-variables'), // ◀️ 依赖变量文件
                is_file($media_css) ? filemtime($media_css) : $version
            );

            // 📦 加载JavaScript
            wp_enqueue_script(
                'shiroki-media-library',
                $theme_uri . '/assets/js/admin/media-library/media-library.js',
                array('jquery', 'wp-plupload'),
                is_file($media_js) ? filemtime($media_js) : $version,
                true
            );

            // 🎯 传递AJAX配置
            wp_localize_script('shiroki-media-library', 'shirokiMediaConfig', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'adminUrl' => admin_url(),
                'nonce' => wp_create_nonce('shiroki_media_nonce'),
                'mediaFormNonce' => wp_create_nonce('media-form'),
                'strings' => array(
                    'copySuccess' => '✅ 已复制到剪贴板',
                    'copyError' => '❌ 复制失败',
                    'loading' => '⏳ 加载中...',
                    'noItems' => '📭 暂无媒体文件',
                    'loadMore' => '加载更多',
                    'retry' => '重试',
                    'requestFailed' => '媒体文件加载失败，请重试。'
                )
            ));
        }

        /* 📤 添加新媒体页面（media-new.php）样式 */
        /* 🔍 使用多种方式检测 media-new.php 页面 */
        $is_media_new = false;
        
        // 方式1  通过 hook 名称检测
        if ($hook === 'media-new' || $hook === 'media_page_add-new') {
            $is_media_new = true;
        }
        
        // 方式2  通过当前页面文件名检测
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'media-new.php') !== false) {
            $is_media_new = true;
        }
        
        // 方式3  通过 get_current_screen() 检测
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && ($screen->id === 'media-new' || $screen->base === 'media-new')) {
                $is_media_new = true;
            }
        }
        
        if ($is_media_new) {
            // ☀️ 加载media-new页面专用CSS
            wp_enqueue_style(
                'shiroki-media-new',
                $theme_uri . '/assets/css/admin/media-library/media-new.css',
                array('admin-variables'), // ◀️ 依赖变量文件
                $version
            );
        }
    }
    
    /**
     * 直接输出自定义媒体库页面，附件由 AJAX 分页填充。
     */
    public function render_custom_media_ui() {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'upload') {
            return;
        }
        ?>
        <div class="wrap shiroki-media-page">
            <h1 class="wp-heading-inline">媒体库</h1>

            <div class="shiroki-media-wrapper">
                    <!-- 🎯 顶部工具栏：排序方式 + 批量操作 + 添加媒体按钮 -->
                    <div class="shiroki-media-top-bar">
                        <!-- 📊 排序方式 -->
                        <div class="shiroki-media-sort-wrapper">
                            <span class="shiroki-media-sort-label">📊 排序方式：</span>
                            <div class="shiroki-media-sort-options">
                                <button class="shiroki-media-sort-btn active" data-sort="date" data-order="desc">
                                    📅 日期
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="name" data-order="asc">
                                    📝 名称
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="size" data-order="desc" title="按当前页文件大小排序">
                                    📦 本页大小
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="type" data-order="asc">
                                    🏷️ 类型
                                </button>
                            </div>
                        </div>

                        <!-- 📦 批量操作工具栏（初始隐藏） -->
                        <div class="shiroki-media-bulk-actions" id="shiroki-media-bulk-actions" style="display: none;">
                            <span class="shiroki-media-bulk-count">已选择 <span class="shiroki-media-bulk-count-num">0</span> 项</span>
                            <button class="shiroki-media-bulk-btn shiroki-media-bulk-delete" data-action="delete">
                                🗑️ 批量删除
                            </button>
                            <button class="shiroki-media-bulk-btn shiroki-media-bulk-cancel" data-action="cancel">
                                ❌ 取消选择
                            </button>
                        </div>

                        <!-- 📤 添加媒体按钮容器 -->
                        <div class="shiroki-media-upload-wrapper">
                            <div class="shiroki-media-upload-container"><button type="button" class="shiroki-upload-btn">添加媒体文件</button></div>
                        </div>
                    </div>
                    
                    <div class="shiroki-upload-area" style="display: none;">
                        <div class="shiroki-upload-dropzone">
                            <button type="button" class="shiroki-upload-close" aria-label="关闭上传区域">×</button>
                            <div class="shiroki-upload-content">
                                <div class="shiroki-upload-icon">📤</div>
                                <h3 class="shiroki-upload-title">拖文件至此可上传</h3>
                                <p class="shiroki-upload-or">或</p>
                                <button type="button" class="shiroki-upload-select-btn">选择文件</button>
                                <p class="shiroki-upload-max-size">最大上传文件大小：<?php echo esc_html(size_format(wp_max_upload_size())); ?></p>
                            </div>
                            <div class="shiroki-upload-progress" style="display: none;">
                                <div class="shiroki-upload-progress-bar"><div class="shiroki-upload-progress-fill"></div></div>
                                <p class="shiroki-upload-progress-text">正在上传...</p>
                            </div>
                        </div>
                    </div>

                    <!-- 🧰 自定义媒体库工具栏 -->
                    <div class="shiroki-media-toolbar">
                        <!-- 🔍 搜索框 -->
                        <div class="shiroki-media-search">
                            <input type="text" 
                                   id="shiroki-media-search" 
                                   placeholder="🔍 搜索媒体文件..."
                                   autocomplete="off">
                        </div>
                        
                        <!-- 🏷️ 筛选按钮 -->
                        <div class="shiroki-media-filters">
                            <button class="shiroki-media-filter-btn active" data-filter="all">
                                📁 全部
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="image">
                                🖼️ 图片
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="video">
                                🎬 视频
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="audio">
                                🎵 音频
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="document">
                                📄 文档
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="file">
                                📦 文件
                            </button>
                        </div>
                    </div>
                    
                    <div id="shiroki-media-error" class="shiroki-media-error" role="alert" style="display: none;">
                        <span class="shiroki-media-error-message"></span>
                        <button type="button" id="shiroki-media-retry">重试</button>
                    </div>
                    <div id="shiroki-media-loading" class="shiroki-media-loading" role="status">⏳ 正在加载媒体文件…若长时间无响应，请刷新页面。</div>

                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-media-grid" id="shiroki-media-grid">
                        <!-- 媒体卡片将通过JavaScript动态插入 -->
                    </div>
                    
                    <!-- 📭 空状态 -->
                    <div class="shiroki-media-empty" id="shiroki-media-empty" style="display: none;">
                        <svg class="shiroki-media-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <div class="shiroki-media-empty-text">📭 暂无媒体文件</div>
                        <div class="shiroki-media-empty-subtext">点击上方【添加媒体文件】按钮上传</div>
                    </div>
                <button type="button" id="shiroki-media-load-more" class="shiroki-media-load-more" style="display: none;">加载更多</button>
            </div>

        </div>
        <?php
    }

    /**
     * 📡 AJAX获取媒体项目
     */
    public function ajax_get_media_items() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_media_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $page = isset($_POST['page']) ? min(10000, max(1, absint($_POST['page']))) : 1;
        $per_page = isset($_POST['per_page']) ? min(24, max(1, absint($_POST['per_page']))) : 10;
        $search = isset($_POST['search']) && is_string($_POST['search'])
            ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $filter = isset($_POST['filter']) && is_string($_POST['filter'])
            ? sanitize_key(wp_unslash($_POST['filter'])) : 'all';
        $sort = isset($_POST['sort']) && is_string($_POST['sort'])
            ? sanitize_key(wp_unslash($_POST['sort'])) : 'date';
        $order = isset($_POST['order']) && is_string($_POST['order'])
            ? strtolower(sanitize_key(wp_unslash($_POST['order']))) : 'desc';

        if (!in_array($filter, array('all', 'image', 'video', 'audio', 'document', 'file'), true)) {
            $filter = 'all';
        }
        if (!in_array($sort, array('date', 'name', 'size', 'type'), true)) {
            $sort = 'date';
        }
        $order = $order === 'asc' ? 'ASC' : 'DESC';

        // 文件大小默认不具备可用于全局 SQL 排序的独立元数据；先按日期安全分页。
        $orderby = 'date';
        switch ($sort) {
            case 'name':
                $orderby = 'title';
                break;
            case 'date':
                $orderby = 'date';
                break;
            case 'size':
                $orderby = 'date';
                break;
            case 'type':
                $orderby = 'date';
                break;
        }

        $post_status = array('inherit');
        $attachment_type = get_post_type_object('attachment');
        if ($attachment_type && current_user_can($attachment_type->cap->read_private_posts)) {
            $post_status[] = 'private';
        }

        $args = array(
            'post_type' => 'attachment',
            'post_status' => $post_status,
            'orderby' => array($orderby => $order, 'ID' => $order),
            'order' => $order,
            'posts_per_page' => $per_page,
            'paged' => $page,
            'ignore_sticky_posts' => true
        );
        
        // 🔍 搜索条件
        if (!empty($search)) {
            $args['s'] = $search;
            add_filter('wp_allow_query_attachment_by_filename', '__return_true');
        }
        
        // 🏷️ 筛选条件
        if ($filter !== 'all') {
            switch ($filter) {
                case 'image':
                    $args['post_mime_type'] = 'image';
                    break;
                case 'video':
                    $args['post_mime_type'] = 'video';
                    break;
                case 'audio':
                    $args['post_mime_type'] = 'audio';
                    break;
                case 'document':
                    // 使用 SQL 过滤器来筛选文档类型
                    add_filter('posts_where', array($this, 'filter_document_mime_type'));
                    break;
                case 'file':
                    // 筛选文件类型（字体、exe、zip等）
                    add_filter('posts_where', array($this, 'filter_file_mime_type'));
                    break;
            }
        }
        
        global $wpdb;
        $type_order_filter = null;
        if ($sort === 'type') {
            $type_order_filter = function($orderby) use ($wpdb, $order) {
                return "{$wpdb->posts}.post_mime_type {$order}, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
            };
            add_filter('posts_orderby', $type_order_filter);
        }

        $wpdb->last_error = '';
        $query = new WP_Query($args);

        if ($type_order_filter) {
            remove_filter('posts_orderby', $type_order_filter);
        }
        if ($search !== '') {
            remove_filter('wp_allow_query_attachment_by_filename', '__return_true');
        }
        if ($filter === 'document') {
            remove_filter('posts_where', array($this, 'filter_document_mime_type'));
        }
        if ($filter === 'file') {
            remove_filter('posts_where', array($this, 'filter_file_mime_type'));
        }

        if (!empty($wpdb->last_error)) {
            wp_send_json_error('媒体文件查询失败，请稍后重试。', 500);
        }

        $items = array();
        foreach ($query->posts as $post) {
            try {
                $items[] = $this->format_media_item($post->ID);
            } catch (Throwable $error) {
                error_log('Shiroki media item ' . (int) $post->ID . ': ' . $error->getMessage());
                $items[] = array(
                    'id' => (int) $post->ID,
                    'title' => (string) $post->post_title,
                    'caption' => '',
                    'description' => '',
                    'url' => '',
                    'thumbnail' => '',
                    'mime_type' => (string) $post->post_mime_type,
                    'file_type' => 'file',
                    'file_size' => '',
                    'file_size_bytes' => 0,
                    'dimensions' => '',
                    'date' => (string) $post->post_date,
                    'icon_svg' => $this->get_file_icon_svg(''),
                    'file_extension' => '',
                    'edit_link' => admin_url('post.php?post=' . (int) $post->ID . '&action=edit'),
                    'delete_link' => ''
                );
            }
        }

        if ($sort === 'size') {
            usort($items, function($first, $second) use ($order) {
                $difference = $first['file_size_bytes'] <=> $second['file_size_bytes'];
                return $order === 'ASC' ? $difference : -$difference;
            });
        }

        wp_send_json_success(array(
            'items' => $items,
            'has_more' => $query->max_num_pages > $page,
            'total' => $query->found_posts
        ));
    }
    
    /**
     * 📄 文档类型筛选过滤器
     */
    public function filter_document_mime_type($where) {
        global $wpdb;
        // 只匹配明确的文档类型（PDF、Office文档、OpenDocument等）
        $document_types = array(
            // PDF
            'application/pdf',
            // Microsoft Office
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // OpenDocument
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.formula',
            // RTF
            'application/rtf',
            'text/rtf',
            // Markdown
            'text/markdown',
            'text/x-markdown'
        );
        $types_str = "'" . implode("', '", $document_types) . "'";
        $where .= " AND {$wpdb->posts}.post_mime_type IN ({$types_str})";

        // 排除可执行文件和代码文件扩展名（即使它们的MIME类型是text/plain或application/octet-stream）
        $exclude_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'log', 'ini', 'conf');
        foreach ($exclude_extensions as $ext) {
            $where .= " AND {$wpdb->posts}.guid NOT LIKE '%.{$ext}'";
            $where .= " AND {$wpdb->posts}.post_title NOT LIKE '%.{$ext}'";
        }

        return $where;
    }
    
    /**
     * 📦 文件类型筛选过滤器（字体、exe、zip、代码文件等）
     */
    public function filter_file_mime_type($where) {
        global $wpdb;

        // 根据文件扩展名匹配（优先）
        $file_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'dmg', 'pkg', 'deb', 'rpm', 'zip', 'rar', '7z', 'tar', 'gz', 'html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'hpp', 'cs', 'go', 'rs', 'swift', 'kt', 'rb', 'pl', 'sql', 'yaml', 'yml', 'toml', 'ini', 'conf', 'cfg', 'log');

        $where .= " AND (";
        $ext_conditions = array();
        foreach ($file_extensions as $ext) {
            $ext_conditions[] = "{$wpdb->posts}.guid LIKE '%.{$ext}'";
            $ext_conditions[] = "{$wpdb->posts}.post_title LIKE '%.{$ext}'";
        }
        $where .= implode(" OR ", $ext_conditions);

        // 或者匹配特定的MIME类型
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'font/%'";
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'application/x-font-%'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/vnd.ms-fontobject'";
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'application/font-%'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/zip'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-zip-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-rar-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-7z-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/gzip'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-tar'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-msdownload'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-exe'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-msdos-program'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-executable'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/html'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/css'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/json'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/xml'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/xml'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-bat'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/bat'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/x-python'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-php'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/x-php'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/octet-stream'";
        $where .= ")";

        return $where;
    }
    
    /**
     * 🎨 格式化媒体项目数据
     */
    private function format_media_item($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'attachment') {
            throw new RuntimeException('Attachment record is unavailable');
        }
        $metadata = wp_get_attachment_metadata($post_id);
        $file_url = wp_get_attachment_url($post_id);
        $file_path = get_attached_file($post_id);
        $mime_type = get_post_mime_type($post_id);

        $metadata = is_array($metadata) ? $metadata : array();
        $file_url = is_string($file_url) ? $file_url : '';
        $file_path = is_string($file_path) ? $file_path : '';
        $mime_type = is_string($mime_type) ? $mime_type : '';
        
        // 📐 获取文件尺寸
        $dimensions = '';
        if (isset($metadata['width']) && isset($metadata['height'])) {
            $dimensions = $metadata['width'] . '×' . $metadata['height'];
        }
        
        // 📦 获取文件大小
        $file_size_bytes = 0;
        if (isset($metadata['filesize']) && is_numeric($metadata['filesize'])) {
            $file_size_bytes = max(0, (int) $metadata['filesize']);
        } elseif ($file_path !== '' && is_readable($file_path)) {
            $file_size_bytes = max(0, (int) wp_filesize($file_path));
        }
        $file_size = $file_size_bytes > 0 ? size_format($file_size_bytes) : '';
        
        // 🖼️ 获取缩略图
        $thumbnail = wp_get_attachment_image_src($post_id, 'medium');
        $thumbnail_url = is_array($thumbnail) && isset($thumbnail[0]) && is_string($thumbnail[0])
            ? $thumbnail[0] : '';
        
        // 🔣 获取文件类型图标
        $file_icon = $this->get_file_icon_svg($mime_type);
        
        // 🏷️ 获取文件扩展名（用于类型判断和格式勋章）
        $file_extension = $this->get_file_extension($file_url !== '' ? $file_url : $file_path);

        $edit_link = get_edit_post_link($post_id, 'raw');
        if (!is_string($edit_link) || $edit_link === '') {
            $edit_link = admin_url('post.php?post=' . (int) $post_id . '&action=edit');
        }
        $delete_link = get_delete_post_link($post_id, '', true);

        return array(
            'id' => $post_id,
            'title' => (string) $post->post_title,
            'caption' => (string) $post->post_excerpt,
            'description' => (string) $post->post_content,
            'url' => $file_url,
            'thumbnail' => $thumbnail_url,
            'mime_type' => $mime_type,
            'file_type' => $this->get_file_type($mime_type, $file_extension),
            'file_size' => $file_size,
            'file_size_bytes' => $file_size_bytes,
            'dimensions' => $dimensions,
            'date' => (string) get_the_date('Y-m-d H:i', $post_id),
            'icon_svg' => $file_icon,
            'file_extension' => $file_extension,
            'edit_link' => $edit_link,
            'delete_link' => is_string($delete_link) ? $delete_link : ''
        );
    }
    
    /**
     * 🏷️ 获取文件扩展名
     */
    private function get_file_extension($file_url) {
        $file_url = is_string($file_url) ? $file_url : '';
        $path = parse_url($file_url, PHP_URL_PATH);
        $extension = pathinfo(is_string($path) ? $path : $file_url, PATHINFO_EXTENSION);
        return strtolower($extension);
    }
    
    /**
     * 🔣 获取文件类型SVG图标
     */
    private function get_file_icon_svg($mime_type) {
        // 🖼️ 图片
        if (strpos($mime_type, 'image/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7392 0.1154 242.05)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        }
        
        // 🎬 视频
        if (strpos($mime_type, 'video/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7565 0.143 355.9)" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>';
        }
        
        // 🎵 音频
        if (strpos($mime_type, 'audio/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.6637 0.1634 296.96)" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
        }
        
        // 📄 PDF
        if ($mime_type === 'application/pdf') {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7421 0.1503 21.35)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
        }
        
        // 📊 文档
        if (strpos($mime_type, 'application/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.786 0.1379 154.52)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
        }
        
        // 📦 默认
        return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7457 0.0304 254.72)" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>';
    }
    
    /**
     * 📁 获取文件类型分类
     */
    private function get_file_type($mime_type, $file_extension = '') {
        if (strpos($mime_type, 'image/') === 0) return 'image';
        if (strpos($mime_type, 'video/') === 0) return 'video';
        if (strpos($mime_type, 'audio/') === 0) return 'audio';

        // 检查是否为文档类型（明确的文档格式）
        if ($this->is_document_type($mime_type, $file_extension)) {
            return 'document';
        }

        // 默认归类为文件类型
        return 'file';
    }
    
    /**
     * 📦 检查是否为文件类型
     */
    private function is_file_type($mime_type, $file_extension = '') {
        $ext = strtolower($file_extension);

        // 根据扩展名判断（优先）
        $file_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'dmg', 'pkg', 'deb', 'rpm');
        $code_extensions = array('html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'hpp', 'cs', 'go', 'rs', 'swift', 'kt', 'rb', 'pl', 'sql', 'yaml', 'yml', 'toml', 'ini', 'conf', 'cfg', 'log');

        if (in_array($ext, $file_extensions) || in_array($ext, $code_extensions)) {
            return true;
        }

        // 字体文件
        if (strpos($mime_type, 'font/') === 0) return true;
        if (strpos($mime_type, 'application/x-font-') === 0) return true;
        if ($mime_type === 'application/vnd.ms-fontobject') return true;
        if (strpos($mime_type, 'application/font-') === 0) return true;

        // 压缩包
        if ($mime_type === 'application/zip') return true;
        if ($mime_type === 'application/x-zip-compressed') return true;
        if ($mime_type === 'application/x-rar-compressed') return true;
        if ($mime_type === 'application/x-7z-compressed') return true;
        if ($mime_type === 'application/gzip') return true;
        if ($mime_type === 'application/x-tar') return true;

        // 可执行文件
        if ($mime_type === 'application/x-msdownload') return true;
        if ($mime_type === 'application/x-exe') return true;
        if ($mime_type === 'application/x-msdos-program') return true;
        if ($mime_type === 'application/x-executable') return true;

        // 代码文件（HTML、JS、CSS、BAT等）
        if ($mime_type === 'text/html') return true;
        if ($mime_type === 'text/javascript') return true;
        if ($mime_type === 'application/javascript') return true;
        if ($mime_type === 'application/x-javascript') return true;
        if ($mime_type === 'text/css') return true;
        if ($mime_type === 'application/json') return true;
        if ($mime_type === 'text/xml') return true;
        if ($mime_type === 'application/xml') return true;
        if ($mime_type === 'application/x-bat') return true;
        if ($mime_type === 'application/bat') return true;
        if ($mime_type === 'text/x-python') return true;
        if ($mime_type === 'application/x-php') return true;
        if ($mime_type === 'text/x-php') return true;

        return false;
    }

    /**
     * 📄 检查是否为文档类型
     */
    private function is_document_type($mime_type, $file_extension = '') {
        $ext = strtolower($file_extension);

        // 根据扩展名判断（明确的文档格式）
        $document_extensions = array(
            // Microsoft Office
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            // OpenDocument
            'odt', 'ods', 'odp', 'odg', 'odf',
            // PDF
            'pdf',
            // 文本文档
            'rtf', 'tex', 'txt',
            // Markdown
            'md', 'markdown',
            // 电子书
            'epub', 'mobi', 'azw', 'azw3'
        );

        if (in_array($ext, $document_extensions)) {
            return true;
        }

        // 根据 MIME 类型判断
        $document_mime_types = array(
            // PDF
            'application/pdf',
            // Microsoft Word
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Microsoft Excel
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // Microsoft PowerPoint
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // OpenDocument
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.formula',
            // RTF
            'application/rtf',
            'text/rtf',
            // Markdown
            'text/markdown',
            'text/x-markdown'
        );

        if (in_array($mime_type, $document_mime_types)) {
            return true;
        }

        return false;
    }

    /**
     * 📋 AJAX复制媒体URL
     */
    public function ajax_copy_media_url() {
        // 验证nonce
        check_ajax_referer('shiroki_media_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的媒体ID');
        }
        
        $url = wp_get_attachment_url($post_id);
        
        if (!$url) {
            wp_send_json_error('获取URL失败');
        }
        
        wp_send_json_success(array('url' => $url));
    }
    
    /**
     * 🗑️ AJAX删除媒体文件
     */
    public function ajax_delete_media() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_media_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的媒体ID');
        }
        
        // 检查是否为附件
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'attachment') {
            wp_send_json_error('不是有效的媒体文件');
        }
        
        // 使用WordPress原生的wp_delete_attachment函数删除
        $result = wp_delete_attachment($post_id, true);
        
        // wp_delete_attachment 成功时返回被删除的附件对象，失败时返回false或WP_Error
        if ($result === false || is_wp_error($result)) {
            $error_msg = is_wp_error($result) ? $result->get_error_message() : '删除失败';
            wp_send_json_error($error_msg);
        }
        
        wp_send_json_success(array('message' => '删除成功', 'deleted_id' => $post_id));
    }
    
    /**
     * 📄 设置媒体库每页显示数量
     */
    public function set_media_per_page($per_page) {
        return 20;
    }
}

/**
 * 🚀 初始化媒体库UI
 */
function shiroki_init_media_library_ui() {
    Shiroki_Media_Library_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_media_library_ui');
