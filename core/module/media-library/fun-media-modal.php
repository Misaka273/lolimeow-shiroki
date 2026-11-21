<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🪟 媒体弹窗专用处理模块
 * 🎨 拟态拟物玻璃质感媒体弹窗UI设计
 * 
 * @package Lolimeow_Shiroki
 * @subpackage Media_Modal
 * @since 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 媒体弹窗处理类
 */
class Shiroki_Media_Modal {
    
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
        /* ◀️ 加载媒体弹窗样式和脚本 */
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        /* ◀️ 在管理页脚添加媒体弹窗HTML */
        add_action('admin_footer', array($this, 'add_media_modal_template'));
        
        /* ◀️ AJAX处理 */
        add_action('wp_ajax_shiroki_get_media_modal_items', array($this, 'ajax_get_media_items'));
        add_action('wp_ajax_shiroki_upload_media_modal', array($this, 'ajax_upload_media'));
        add_action('wp_ajax_shiroki_get_media_detail', array($this, 'ajax_get_media_detail'));
        add_action('wp_ajax_shiroki_delete_media', array($this, 'ajax_delete_media'));
        add_action('wp_ajax_shiroki_save_media_detail', array($this, 'ajax_save_media_detail'));
    }
    
    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* ◀️ 只在文章编辑页面和小工具页面加载 */
        if ($hook !== 'post.php' && $hook !== 'post-new.php' && $hook !== 'widgets.php') {
            return;
        }
        
        $theme_uri = get_template_directory_uri();
        $version = wp_get_theme()->get('Version');
        
        /* ◀️ 加载WordPress媒体库 */
        wp_enqueue_media();
        
        /* ◀️ 加载媒体弹窗样式 */
        wp_enqueue_style(
            'shiroki-media-modal',
            $theme_uri . '/assets/css/admin/media-library/media-modal.css',
            array(),
            $version
        );
        
        /* ◀️ 加载媒体弹窗脚本 */
        wp_enqueue_script(
            'shiroki-media-modal',
            $theme_uri . '/assets/js/admin/media-library/media-modal.js',
            array('jquery', 'media-editor'),
            $version,
            true
        );
        
        /* ◀️ 传递配置 */
        wp_localize_script('shiroki-media-modal', 'shirokiMediaModalConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shiroki_media_modal_nonce'),
            'strings' => array(
                'title' => '🗃️ 添加媒体',
                'allItems' => '所有多媒体项目',
                'uploadToPost' => '上传到本文章',
                'images' => '图片',
                'audio' => '音频',
                'video' => '视频',
                'documents' => '文档',
                'spreadsheet' => '试算表',
                'archives' => '归档',
                'unattached' => '孤立',
                'mine' => '我的',
                'allDates' => '全部日期',
                'uploadFiles' => '上传文件',
                'mediaLibrary' => '媒体库',
                'insertIntoPost' => '插入至文章',
                'select' => '选择',
                'loading' => '加载中...',
                'noItems' => '暂无媒体文件',
                'uploadSuccess' => '上传成功',
                'uploadError' => '上传失败'
            )
        ));
        
        /* ◀️ 添加内联脚本禁用原生媒体弹窗 */
        wp_add_inline_script('shiroki-media-modal', '
            /* 🔧 禁用WordPress原生媒体弹窗 */
            (function() {
                /* ◀️ 在wp.media加载前拦截 */
                if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
                    /* ◀️ 覆盖open方法阻止原生弹窗 */
                    wp.media.editor.open = function(editorId, options) {
                        /* ◀️ 返回this以保持链式调用 */
                        return this;
                    };
                }
                
                /* ◀️ 移除所有添加媒体按钮的原生事件 */
                jQuery(document).ready(function($) {
                    /* ◀️ 移除原生的点击事件 */
                    $(document).off("click", ".insert-media, .add_media, #insert-media-button");
                    
                    /* ◀️ 禁用按钮的默认行为 */
                    $(".insert-media, .add_media, #insert-media-button").each(function() {
                        var $btn = $(this);
                        /* ◀️ 克隆按钮以移除所有事件 */
                        var $newBtn = $btn.clone();
                        $newBtn.removeClass("insert-media add_media").addClass("shiroki-media-trigger");
                        $newBtn.attr("id", "shiroki-insert-media-button");
                        $btn.replaceWith($newBtn);
                    });
                });
            })();
        ', 'before');
    }
    
    /**
     * 🎨 添加媒体弹窗HTML模板
     */
    public function add_media_modal_template() {
        $screen = get_current_screen();
        if (!$screen || ($screen->id !== 'post' && $screen->id !== 'post-new' && $screen->id !== 'widgets')) {
            return;
        }
        ?>
        <!-- 🪟 自定义媒体弹窗 -->
        <div id="shiroki-media-modal" class="shiroki-media-modal" style="display: none;">
            <div class="shiroki-media-modal-overlay"></div>
            <div class="shiroki-media-modal-container">
                <!-- 📝 弹窗头部 -->
                <div class="shiroki-media-modal-header">
                    <h2 class="shiroki-media-modal-title">🗃️ 添加媒体</h2>
                    <button type="button" class="shiroki-media-modal-close" aria-label="关闭">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M18.3 5.7a1 1 0 0 0-1.4 0L12 10.6 7.1 5.7a1 1 0 0 0-1.4 1.4L10.6 12l-4.9 4.9a1 1 0 0 0 1.4 1.4L12 13.4l4.9 4.9a1 1 0 0 0 1.4-1.4L13.4 12l4.9-4.9a1 1 0 0 0 0-1.4z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- 🧭 分类标签栏 -->
                <div class="shiroki-media-modal-tabs">
                    <button type="button" class="shiroki-media-tab active" data-filter="all">
                        <span class="shiroki-media-tab-icon">📁</span>
                        <span class="shiroki-media-tab-text">所有多媒体项目</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="uploaded-to-post">
                        <span class="shiroki-media-tab-icon">📤</span>
                        <span class="shiroki-media-tab-text">上传到本文章</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="image">
                        <span class="shiroki-media-tab-icon">🖼️</span>
                        <span class="shiroki-media-tab-text">图片</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="audio">
                        <span class="shiroki-media-tab-icon">🎵</span>
                        <span class="shiroki-media-tab-text">音频</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="video">
                        <span class="shiroki-media-tab-icon">🎬</span>
                        <span class="shiroki-media-tab-text">视频</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="document">
                        <span class="shiroki-media-tab-icon">📄</span>
                        <span class="shiroki-media-tab-text">文档</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="spreadsheet">
                        <span class="shiroki-media-tab-icon">📊</span>
                        <span class="shiroki-media-tab-text">试算表</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="archive">
                        <span class="shiroki-media-tab-icon">📦</span>
                        <span class="shiroki-media-tab-text">归档</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="unattached">
                        <span class="shiroki-media-tab-icon">🔗</span>
                        <span class="shiroki-media-tab-text">孤立</span>
                    </button>
                    <button type="button" class="shiroki-media-tab" data-filter="mine">
                        <span class="shiroki-media-tab-icon">👤</span>
                        <span class="shiroki-media-tab-text">我的</span>
                    </button>
                </div>
                
                <!-- 📊 日期筛选栏 -->
                <div class="shiroki-media-modal-filters">
                    <button type="button" class="shiroki-media-filter-btn active" data-date="all">
                        <span class="shiroki-media-filter-text">全部日期</span>
                    </button>
                    <button type="button" class="shiroki-media-filter-btn" data-date="2026-03">
                        <span class="shiroki-media-filter-text">2026 年 3 月</span>
                    </button>
                    <!-- ◀️ 搜索框 -->
                    <div class="shiroki-media-search-wrapper">
                        <input type="text" class="shiroki-media-search-input" placeholder="🔍 搜索媒体文件...">
                    </div>
                </div>
                
                <!-- 📦 媒体网格区域 -->
                <div class="shiroki-media-modal-content">
                    <div class="shiroki-media-modal-grid" id="shiroki-media-modal-grid">
                        <!-- ◀️ 媒体项目将通过JS动态加载 -->
                    </div>
                    
                    <!-- 📭 空状态 -->
                    <div class="shiroki-media-modal-empty" id="shiroki-media-modal-empty" style="display: none;">
                        <div class="shiroki-media-empty-icon">📭</div>
                        <div class="shiroki-media-empty-text">暂无媒体文件</div>
                        <div class="shiroki-media-empty-subtext">点击【上传文件】按钮上传</div>
                    </div>
                    
                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-media-modal-loading" id="shiroki-media-modal-loading">
                        <div class="shiroki-media-loading-spinner"></div>
                        <div class="shiroki-media-loading-text">加载中...</div>
                    </div>
                </div>
                
                <!-- ⚡ 底部工具栏 -->
                <div class="shiroki-media-modal-footer">
                    <div class="shiroki-media-modal-actions-left">
                        <button type="button" class="shiroki-media-btn shiroki-media-btn-upload" id="shiroki-media-btn-upload">
                            <span class="shiroki-media-btn-icon">📤</span>
                            <span class="shiroki-media-btn-text">上传文件</span>
                        </button>
                        <button type="button" class="shiroki-media-btn shiroki-media-btn-library" id="shiroki-media-btn-library">
                            <span class="shiroki-media-btn-icon">📚</span>
                            <span class="shiroki-media-btn-text">媒体库</span>
                        </button>
                    </div>
                    <div class="shiroki-media-modal-actions-right">
                        <button type="button" class="shiroki-media-btn shiroki-media-btn-cancel" id="shiroki-media-btn-cancel">
                            <span class="shiroki-media-btn-text">取消</span>
                        </button>
                        <button type="button" class="shiroki-media-btn shiroki-media-btn-insert" id="shiroki-media-btn-insert" disabled>
                            <span class="shiroki-media-btn-text">插入至文章</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📋 详情抽屉遮罩 -->
        <div class="shiroki-media-detail-overlay" id="shiroki-media-detail-overlay"></div>

        <!-- 📋 详情抽屉 -->
        <div class="shiroki-media-detail-drawer" id="shiroki-media-detail-drawer">
            <!-- ◀️ 抽屉头部 -->
            <div class="shiroki-media-detail-header">
                <h3 class="shiroki-media-detail-title">🗃️ 附件详情</h3>
                <button type="button" class="shiroki-media-detail-close" id="shiroki-media-detail-close" aria-label="关闭">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M18.3 5.7a1 1 0 0 0-1.4 0L12 10.6 7.1 5.7a1 1 0 0 0-1.4 1.4L10.6 12l-4.9 4.9a1 1 0 0 0 1.4 1.4L12 13.4l4.9 4.9a1 1 0 0 0 1.4-1.4L13.4 12l4.9-4.9a1 1 0 0 0 0-1.4z"/>
                    </svg>
                </button>
            </div>

            <!-- ◀️ 抽屉内容 -->
            <div class="shiroki-media-detail-content">
                <!-- 🖼️ 大图预览 -->
                <div class="shiroki-media-detail-preview" id="shiroki-media-detail-preview">
                    <!-- ◀️ 动态加载 -->
                </div>

                <!-- 📝 表单字段 -->
                <div class="shiroki-media-detail-form">
                    <!-- ◀️ 标题 -->
                    <div class="shiroki-media-detail-field">
                        <label class="shiroki-media-detail-label">标题</label>
                        <input type="text" class="shiroki-media-detail-input" id="shiroki-media-detail-title" placeholder="附件标题">
                    </div>

                    <!-- ◀️ 题注/说明 -->
                    <div class="shiroki-media-detail-field">
                        <label class="shiroki-media-detail-label">题注/说明</label>
                        <input type="text" class="shiroki-media-detail-input" id="shiroki-media-detail-caption" placeholder="显示在图片下方的说明文字">
                    </div>

                    <!-- ◀️ 替代文本（仅图片显示） -->
                    <div class="shiroki-media-detail-field" id="shiroki-media-detail-alt-field">
                        <label class="shiroki-media-detail-label">替代文本</label>
                        <input type="text" class="shiroki-media-detail-input" id="shiroki-media-detail-alt" placeholder="图片无法显示时的替代文字">
                    </div>

                    <!-- ◀️ 描述 -->
                    <div class="shiroki-media-detail-field">
                        <label class="shiroki-media-detail-label">描述</label>
                        <textarea class="shiroki-media-detail-textarea" id="shiroki-media-detail-description" placeholder="附件的详细说明"></textarea>
                    </div>

                    <!-- ℹ️ 文件信息 -->
                    <div class="shiroki-media-detail-info">
                        <div class="shiroki-media-detail-info-title">文件信息</div>
                        <div class="shiroki-media-detail-info-item">
                            <span class="shiroki-media-detail-info-label">文件名</span>
                            <span class="shiroki-media-detail-info-value" id="shiroki-media-detail-filename">-</span>
                        </div>
                        <div class="shiroki-media-detail-info-item">
                            <span class="shiroki-media-detail-info-label">文件类型</span>
                            <span class="shiroki-media-detail-info-value" id="shiroki-media-detail-mime">-</span>
                        </div>
                        <div class="shiroki-media-detail-info-item">
                            <span class="shiroki-media-detail-info-label">上传日期</span>
                            <span class="shiroki-media-detail-info-value" id="shiroki-media-detail-date">-</span>
                        </div>
                        <div class="shiroki-media-detail-info-item">
                            <span class="shiroki-media-detail-info-label">文件大小</span>
                            <span class="shiroki-media-detail-info-value" id="shiroki-media-detail-size">-</span>
                        </div>
                        <div class="shiroki-media-detail-info-item" id="shiroki-media-detail-dimensions-item">
                            <span class="shiroki-media-detail-info-label">尺寸</span>
                            <span class="shiroki-media-detail-info-value" id="shiroki-media-detail-dimensions">-</span>
                        </div>
                    </div>

                    <!-- 🔗 文件URL -->
                    <div class="shiroki-media-detail-field">
                        <label class="shiroki-media-detail-label">文件 URL</label>
                        <div class="shiroki-media-detail-url-wrapper">
                            <input type="text" class="shiroki-media-detail-input shiroki-media-detail-url-input" id="shiroki-media-detail-url" readonly>
                            <button type="button" class="shiroki-media-detail-copy-btn" id="shiroki-media-detail-copy-url" title="复制链接">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                </svg>
                                <span>复制</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ◀️ 操作按钮 -->
            <div class="shiroki-media-detail-actions">
                <a href="#" class="shiroki-media-detail-btn shiroki-media-detail-btn-primary" id="shiroki-media-detail-view" target="_blank">
                    查看附件页面
                </a>
                <button type="button" class="shiroki-media-detail-btn shiroki-media-detail-btn-danger" id="shiroki-media-detail-delete">
                    永久删除
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * 📡 AJAX获取媒体项目
     */
    public function ajax_get_media_items() {
        /* ◀️ 验证nonce */
        if (!check_ajax_referer('shiroki_media_modal_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* ◀️ 检查权限 */
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : 'all';
        $is_search = isset($_POST['is_search']) ? boolval($_POST['is_search']) : false;

        /* ◀️ 懒加载分页：每次加载21条 */
        /* ◀️ 搜索模式加载所有结果，不使用懒加载 */
        if ($is_search || !empty($search)) {
            $posts_per_page = -1; // ⬅️ 搜索时加载所有结果
        } else {
            $posts_per_page = 21; // ⬅️ 每次加载21条（首次和后续相同）
        }

        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        /* ◀️ 筛选条件 */
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
                    $args['post_mime_type'] = array('application/pdf', 'application/msword');
                    break;
                case 'uploaded-to-post':
                    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
                    if ($post_id) {
                        $args['post_parent'] = $post_id;
                    }
                    break;
                case 'unattached':
                    $args['post_parent'] = 0;
                    break;
                case 'mine':
                    $args['author'] = get_current_user_id();
                    break;
            }
        }
        
        /* ◀️ 搜索条件 */
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        /* ◀️ 日期筛选 */
        if ($date !== 'all') {
            $args['year'] = intval(substr($date, 0, 4));
            $args['monthnum'] = intval(substr($date, 5, 2));
        }
        
        $query = new WP_Query($args);
        $items = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $items[] = $this->format_media_item($post_id);
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'items' => $items,
            'has_more' => $query->max_num_pages > $page,
            'total' => $query->found_posts
        ));
    }
    
    /**
     * 📡 AJAX上传媒体
     */
    public function ajax_upload_media() {
        /* ◀️ 验证nonce */
        if (!check_ajax_referer('shiroki_media_modal_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* ◀️ 检查权限 */
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        /* ◀️ 检查文件 */
        if (!isset($_FILES['file'])) {
            wp_send_json_error('没有文件被上传');
        }
        
        /* ◀️ 上传文件 */
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        $attachment_id = media_handle_upload('file', $post_id);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }
        
        wp_send_json_success(array(
            'item' => $this->format_media_item($attachment_id),
            'message' => '上传成功'
        ));
    }
    
    /**
     * 🎨 格式化媒体项目数据
     */
    private function format_media_item($post_id) {
        $post = get_post($post_id);
        $metadata = wp_get_attachment_metadata($post_id);
        $file_url = wp_get_attachment_url($post_id);
        $file_path = get_attached_file($post_id);
        $mime_type = get_post_mime_type($post_id);
        
        /* ◀️ 获取文件尺寸 */
        $dimensions = '';
        if (isset($metadata['width']) && isset($metadata['height'])) {
            $dimensions = $metadata['width'] . '×' . $metadata['height'];
        }
        
        /* ◀️ 获取文件大小 */
        $file_size = '';
        if (file_exists($file_path)) {
            $file_size = size_format(filesize($file_path));
        }
        
        /* ◀️ 获取缩略图 */
        $thumbnail = wp_get_attachment_image_src($post_id, 'medium');
        $thumbnail_url = $thumbnail ? $thumbnail[0] : '';
        
        /* ◀️ 获取文件类型图标 */
        $file_icon = $this->get_file_icon_svg($mime_type);
        
        /* ◀️ 获取文件扩展名 */
        $file_extension = pathinfo($file_url, PATHINFO_EXTENSION);
        
        return array(
            'id' => $post_id,
            'title' => $post->post_title,
            'caption' => $post->post_excerpt,
            'description' => $post->post_content,
            'url' => $file_url,
            'thumbnail' => $thumbnail_url,
            'mime_type' => $mime_type,
            'file_type' => $this->get_file_type($mime_type, $file_extension),
            'file_size' => $file_size,
            'dimensions' => $dimensions,
            'date' => get_the_date('Y-m-d H:i', $post_id),
            'icon_svg' => $file_icon,
            'file_extension' => strtolower($file_extension)
        );
    }
    
    /**
     * 🔣 获取文件类型SVG图标
     */
    private function get_file_icon_svg($mime_type) {
        /* 🖼️ 图片 */
        if (strpos($mime_type, 'image/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7392 0.1154 242.05)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        }
        
        /* 🎬 视频 */
        if (strpos($mime_type, 'video/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7565 0.143 355.9)" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>';
        }
        
        /* 🎵 音频 */
        if (strpos($mime_type, 'audio/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.6637 0.1634 296.96)" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
        }
        
        /* 📄 PDF */
        if ($mime_type === 'application/pdf') {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7421 0.1503 21.35)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
        }
        
        /* 📊 文档 */
        if (strpos($mime_type, 'application/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.786 0.1379 154.52)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
        }
        
        /* 📦 默认 */
        return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7457 0.0304 254.72)" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>';
    }
    
    /**
     * 📁 获取文件类型分类
     */
    private function get_file_type($mime_type, $file_extension = '') {
        if (strpos($mime_type, 'image/') === 0) return 'image';
        if (strpos($mime_type, 'video/') === 0) return 'video';
        if (strpos($mime_type, 'audio/') === 0) return 'audio';
        
        $ext = strtolower($file_extension);
        $document_exts = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'md');
        if (in_array($ext, $document_exts)) return 'document';
        
        $archive_exts = array('zip', 'rar', '7z', 'tar', 'gz');
        if (in_array($ext, $archive_exts)) return 'archive';
        
        return 'file';
    }
    
    /**
     * 📡 AJAX获取媒体详情
     */
    public function ajax_get_media_detail() {
        /* ◀️ 验证nonce */
        if (!check_ajax_referer('shiroki_media_modal_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* ◀️ 检查权限 */
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('无效的附件ID');
        }
        
        $post = get_post($id);
        if (!$post || $post->post_type !== 'attachment') {
            wp_send_json_error('附件不存在');
        }
        
        $metadata = wp_get_attachment_metadata($id);
        $file_url = wp_get_attachment_url($id);
        $file_path = get_attached_file($id);
        $mime_type = get_post_mime_type($id);
        
        /* ◀️ 获取替代文本 */
        $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        
        /* ◀️ 获取文件尺寸 */
        $dimensions = '';
        if (isset($metadata['width']) && isset($metadata['height'])) {
            $dimensions = $metadata['width'] . ' × ' . $metadata['height'];
        }
        
        /* ◀️ 获取文件大小 */
        $file_size = '';
        if (file_exists($file_path)) {
            $file_size = size_format(filesize($file_path));
        }
        
        /* ◀️ 获取文件名 */
        $filename = basename($file_path);
        
        wp_send_json_success(array(
            'id' => $id,
            'title' => $post->post_title,
            'caption' => $post->post_excerpt,
            'alt' => $alt,
            'description' => $post->post_content,
            'filename' => $filename,
            'mime_type' => $mime_type,
            'date' => get_the_date('Y-m-d H:i:s', $id),
            'file_size' => $file_size,
            'dimensions' => $dimensions,
            'url' => $file_url,
            'permalink' => get_attachment_link($id)
        ));
    }
    
    /**
     * 📡 AJAX删除媒体
     */
    public function ajax_delete_media() {
        /* ◀️ 验证nonce */
        if (!check_ajax_referer('shiroki_media_modal_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* ◀️ 检查权限 */
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('无效的附件ID');
        }
        
        $post = get_post($id);
        if (!$post || $post->post_type !== 'attachment') {
            wp_send_json_error('附件不存在');
        }
        
        /* ◀️ 检查当前用户是否有权限删除 */
        if (!current_user_can('delete_post', $id)) {
            wp_send_json_error('您没有权限删除此附件');
        }
        
        /* ◀️ 删除附件（包括文件） */
        $result = wp_delete_attachment($id, true);

        if ($result === false) {
            wp_send_json_error('删除失败');
        }

        wp_send_json_success(array('message' => '删除成功'));
    }

    /**
     * 📡 AJAX保存媒体详情
     */
    public function ajax_save_media_detail() {
        /* ◀️ 验证nonce */
        if (!check_ajax_referer('shiroki_media_modal_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }

        /* ◀️ 检查权限 */
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$id) {
            wp_send_json_error('无效的附件ID');
        }

        $post = get_post($id);
        if (!$post || $post->post_type !== 'attachment') {
            wp_send_json_error('附件不存在');
        }

        /* ◀️ 检查编辑权限 */
        if (!current_user_can('edit_post', $id)) {
            wp_send_json_error('您没有权限编辑此附件');
        }

        /* ◀️ 获取并清理数据 */
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $caption = isset($_POST['caption']) ? sanitize_textarea_field($_POST['caption']) : '';
        $alt = isset($_POST['alt']) ? sanitize_text_field($_POST['alt']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

        /* ◀️ 更新附件post数据 */
        $update_data = array(
            'ID' => $id,
            'post_title' => $title,
            'post_excerpt' => $caption,
            'post_content' => $description
        );

        $result = wp_update_post($update_data, true);

        if (is_wp_error($result)) {
            wp_send_json_error('保存失败：' . $result->get_error_message());
        }

        /* ◀️ 更新替代文本（仅图片） */
        if (strpos(get_post_mime_type($id), 'image/') === 0) {
            update_post_meta($id, '_wp_attachment_image_alt', $alt);
        }

        /* ◀️ 返回更新后的数据 */
        wp_send_json_success(array(
            'message' => '保存成功',
            'data' => array(
                'id' => $id,
                'title' => $title,
                'caption' => $caption,
                'alt' => $alt,
                'description' => $description
            )
        ));
    }
}

/**
 * 🚀 初始化媒体弹窗
 */
function shiroki_init_media_modal() {
    Shiroki_Media_Modal::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_media_modal');