<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📝 文章列表网格卡片式布局
 * 🎨 拟态拟物玻璃质感设计
 * 
 * @package Lolimeow_Shiroki
 * @subpackage Post_Grid
 * @since 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 文章网格UI主类
 */
class Shiroki_Post_Grid_UI {
    
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
        add_action('wp_ajax_shiroki_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_shiroki_trash_post', array($this, 'ajax_trash_post'));
        add_action('wp_ajax_shiroki_restore_post', array($this, 'ajax_restore_post'));
        add_action('wp_ajax_shiroki_bulk_trash_posts', array($this, 'ajax_bulk_trash_posts'));
        add_action('wp_ajax_shiroki_get_post_content', array($this, 'ajax_get_post_content'));
        add_action('wp_ajax_shiroki_get_posts_content', array($this, 'ajax_get_posts_content'));
        add_action('wp_ajax_shiroki_clone_post', array($this, 'ajax_clone_post'));
        add_action('wp_ajax_shiroki_clone_posts', array($this, 'ajax_clone_posts'));
        add_action('wp_ajax_shiroki_bulk_edit_category', array($this, 'ajax_bulk_edit_category'));
        add_action('wp_ajax_shiroki_publish_post', array($this, 'ajax_publish_post'));
        add_action('wp_ajax_shiroki_bulk_draft_posts', array($this, 'ajax_bulk_draft_posts'));
        add_action('wp_ajax_shiroki_bulk_delete_posts', array($this, 'ajax_bulk_delete_posts'));

        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_action('current_screen', array($this, 'disable_native_list_ui'));

        // 在管理页脚添加自定义UI（较低优先级，确保脚本已加载）
        add_action('admin_footer', array($this, 'add_custom_post_ui'), 20);
    }

    /**
     * 🏷️ 为文章列表页添加 body class，便于 CSS 精确覆盖盒子萌原生样式
     */
    public function admin_body_class($classes) {
        if ($this->is_post_grid_screen()) {
            $classes .= ' shiroki-post-grid-active';
        }
        return $classes;
    }

    /**
     * 🎯 判断当前是否为文章网格列表页
     */
    private function is_post_grid_screen($screen = null) {
        if (!function_exists('get_current_screen')) {
            return false;
        }

        if ($screen === null) {
            $screen = get_current_screen();
        }

        if (!$screen || $screen->id !== 'edit-post') {
            return false;
        }

        $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : 'post';
        return $post_type === 'post';
    }

    /**
     * 🚫 禁用原生文章列表 UI（缩略图列等）
     */
    public function disable_native_list_ui($screen) {
        if (!$this->is_post_grid_screen($screen)) {
            return;
        }

        remove_filter('manage_posts_columns', 'boxmoe_admin_post_thumbnail_column');
        remove_action('manage_posts_custom_column', 'boxmoe_admin_post_thumbnail_column_content', 10);
    }
    
    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        // 只在文章列表页面加载
        if ($hook !== 'edit.php') {
            return;
        }
        
        /* 🎯 检查是否是文章类型（排除页面、媒体等其他类型） */
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        if ($post_type !== 'post') {
            return;
        }
        
        $theme_uri = get_template_directory_uri();
        $version = wp_get_theme()->get('Version');
        $post_grid_script = get_template_directory() . '/assets/js/admin/post-grid/post-grid.js';
        $post_grid_version = file_exists($post_grid_script) ? filemtime($post_grid_script) : $version;
        
        /* 🎨 先加载统一变量文件 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );
        
        /* 📝 文章网格样式 */
        wp_enqueue_style(
            'shiroki-post-grid',
            $theme_uri . '/assets/css/admin/post-grid/post-grid.css',
            array('admin-variables'),
            $version
        );
        
        /* 📦 文章网格脚本 */
        wp_enqueue_script(
            'shiroki-post-grid',
            $theme_uri . '/assets/js/admin/post-grid/post-grid.js',
            array('jquery'),
            $post_grid_version,
            true
        );
        
        /* 🎯 传递AJAX配置 */
        wp_localize_script('shiroki-post-grid', 'shirokiPostConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_post_nonce'),
            'editTargetBlank' => (bool) get_boxmoe('boxmoe_article_edit_target_blank'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无文章',
                'loadMore' => '加载更多'
            )
        ));

        /* 🚫 首屏即隐藏原生列表，避免盒子萌 flat-rounded 表格样式闪现 */
        wp_add_inline_style(
            'shiroki-post-grid',
            'body.post-type-post.edit-php .wp-list-table,' .
            'body.post-type-post.edit-php .tablenav,' .
            'body.post-type-post.edit-php #posts-filter,' .
            'body.post-type-post.edit-php .subsubsub,' .
            'body.post-type-post.edit-php .view-switch{display:none!important}'
        );
    }
    
    /**
     * 🎨 添加自定义文章列表UI
     */
    public function add_custom_post_ui() {
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        if (!$this->is_post_grid_screen($screen)) {
            return;
        }

        // 获取分类列表
        $categories = get_categories(array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        ));
        
        // 获取当前文章类型
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        
        // 获取文章状态数量
        $status_counts = wp_count_posts($post_type);
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            /* 🚫 隐藏 WordPress / 盒子萌 原生文章列表 UI */
            $('.wp-list-table, .tablenav, .view-switch, #posts-filter, .subsubsub, .search-box, .wp-filter, hr.wp-header-end').hide();
            
            // 获取新建文章按钮
            var $addButton = $('.page-title-action');
            
            // 构建分类按钮
            var categoryCount = <?php echo count($categories); ?>;
            var maxVisible = 15;
            var categoryButtons = '';
            var moreCategories = '';
            
            <?php 
            $catIndex = 0;
            foreach ($categories as $category) : 
            ?>
            if (<?php echo $catIndex; ?> < maxVisible) {
                categoryButtons += '<button class="shiroki-post-category-btn" data-category="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_js($category->name); ?></button>';
            } else {
                moreCategories += '<button class="shiroki-post-category-btn" data-category="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_js($category->name); ?></button>';
            }
            <?php 
            $catIndex++;
            endforeach; 
            ?>
            
            // 插入自定义UI
            var customUI = `
                <div class="shiroki-post-wrapper">
                    <!-- 🎯 顶部工具栏：状态筛选 + 批量操作 + 新建文章按钮 -->
                    <div class="shiroki-post-top-bar">
                        <!-- 📊 状态筛选 -->
                        <div class="shiroki-post-filter-wrapper">
                            <span class="shiroki-post-filter-label">📊 状态筛选：</span>
                            <div class="shiroki-post-status-options">
                                <button class="shiroki-post-status-btn active" data-status="all">
                                    📁 全部 (<?php echo intval($status_counts->publish + $status_counts->future + $status_counts->draft + $status_counts->pending + $status_counts->private); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="publish">
                                    🟢 已发布 (<?php echo intval($status_counts->publish); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="future">
                                    🔵 定时发布 (<?php echo intval($status_counts->future); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="draft">
                                    🟡 草稿 (<?php echo intval($status_counts->draft); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="pending">
                                    🟠 待审核 (<?php echo intval($status_counts->pending); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="trash">
                                    🗑️ 回收站 (<?php echo intval($status_counts->trash); ?>)
                                </button>
                            </div>
                        </div>
                        
                        <!-- 📦 批量操作工具栏（初始隐藏） -->
                        <div class="shiroki-post-bulk-actions" id="shiroki-post-bulk-actions" style="display: none;">
                            <span class="shiroki-post-bulk-count">已选择 <span class="shiroki-post-bulk-count-num">0</span> 篇</span>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-copy-links" data-action="copy-links">
                                🔗 复制链接
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-copy-content" data-action="copy-content">
                                📋 复制文章
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-edit-category" data-action="edit-category">
                                🏷️ 修改分类
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-draft" data-action="draft">
                                📝 转为草稿
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-trash" data-action="trash">
                                🗑️ 移至回收站
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-delete" data-action="delete">
                                ❌ 彻底删除
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-cancel" data-action="cancel">
                                ✕ 取消选择
                            </button>
                        </div>
                        
                        <!-- 📝 新建文章按钮容器 -->
                        <div class="shiroki-post-add-wrapper">
                            <div class="shiroki-post-add-container"></div>
                        </div>
                    </div>
                    
                    <!-- 🧰 自定义工具栏 -->
                    <div class="shiroki-post-toolbar">
                        <!-- 🔍 搜索框 -->
                        <div class="shiroki-post-search">
                            <input type="text" 
                                   id="shiroki-post-search" 
                                   placeholder="🔍 搜索文章..."
                                   autocomplete="off">
                        </div>
                        
                        <!-- 🏷️ 分类筛选 - 玻璃拟态按钮组 -->
                        <div class="shiroki-post-category-filter" style="position: relative;">
                            <span class="shiroki-post-filter-label">🏷️ 分类：</span>
                            <div class="shiroki-post-category-options" id="shiroki-post-category-options">
                                <button class="shiroki-post-category-btn active" data-category="">📁 全部</button>
                                ${categoryButtons}
                                ${categoryCount > maxVisible ? '<button class="shiroki-post-category-btn shiroki-post-category-more" data-action="more">📂 更多 (' + (categoryCount - maxVisible) + ')</button>' : ''}
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-post-loading" id="shiroki-post-loading" style="display: none;">
                        ⏳ 加载中...
                    </div>
                    
                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-post-grid" id="shiroki-post-grid">
                        <!-- 文章卡片将通过JavaScript动态插入 -->
                    </div>
                    
                    <!-- 📭 空状态 -->
                    <div class="shiroki-post-empty" id="shiroki-post-empty" style="display: none;">
                        <svg class="shiroki-post-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <div class="shiroki-post-empty-text">📭 暂无文章</div>
                        <div class="shiroki-post-empty-subtext">点击上方【写新文章】按钮创建</div>
                    </div>
                    
                    <!-- 📄 分页 -->
                    <div class="shiroki-post-pagination" id="shiroki-post-pagination"></div>
                </div>
            `;
            
            // 插入到 .wrap 容器内
            $('.wrap > h1').after(customUI);
            
            // 将新建文章按钮移动到自定义容器
            if ($addButton.length) {
                $addButton.appendTo('.shiroki-post-add-container');
            }

            // 🎯 将新建文章按钮容器移动到页面标题右侧
            $('.shiroki-post-add-container').insertAfter('.wrap > h1.wp-heading-inline');

            // 🔍 将搜索框移动到状态筛选右侧
            $('.shiroki-post-search').insertAfter('.shiroki-post-filter-wrapper');
            
            // 📦 将分类筛选模态框添加到 body 级别
            var modalHTML = `
                <div class="shiroki-post-category-modal" id="shiroki-post-category-modal" style="display: none;">
                    <div class="shiroki-post-category-modal-backdrop"></div>
                    <div class="shiroki-post-category-modal-content">
                        <div class="shiroki-post-category-modal-header">
                            <span class="shiroki-post-category-modal-title">🏷️ 选择分类</span>
                            <button class="shiroki-post-category-modal-close">✕</button>
                        </div>
                        <div class="shiroki-post-category-modal-body">
                            ${moreCategories}
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);

            // 🏷️ 批量修改分类模态框
            var allCategoryButtons = '';
            <?php foreach ($categories as $category) : ?>
            allCategoryButtons += '<button class="shiroki-post-bulk-category-btn" data-category="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_js($category->name); ?></button>';
            <?php endforeach; ?>

            var bulkCategoryModalHTML = `
                <div class="shiroki-post-bulk-category-modal" id="shiroki-post-bulk-category-modal" style="display: none;">
                    <div class="shiroki-post-bulk-category-modal-backdrop"></div>
                    <div class="shiroki-post-bulk-category-modal-content">
                        <div class="shiroki-post-bulk-category-modal-header">
                            <span class="shiroki-post-bulk-category-modal-title">🏷️ 批量修改分类</span>
                            <button class="shiroki-post-bulk-category-modal-close">✕</button>
                        </div>
                        <div class="shiroki-post-bulk-category-modal-body">
                            <p class="shiroki-post-bulk-category-hint">请选择要设置的新分类（点击选择，可多选）：</p>
                            <!-- 🔍 分类搜索框 -->
                            <div class="shiroki-post-bulk-category-search">
                                <input type="text"
                                       id="shiroki-post-bulk-category-search-input"
                                       placeholder="🔍 搜索分类..."
                                       autocomplete="off">
                            </div>
                            <div class="shiroki-post-bulk-category-options" id="shiroki-post-bulk-category-options">
                                ${allCategoryButtons}
                            </div>
                        </div>
                        <div class="shiroki-post-bulk-category-modal-footer">
                            <button class="shiroki-post-bulk-category-confirm" id="shiroki-post-bulk-category-confirm">✅ 确认修改</button>
                            <button class="shiroki-post-bulk-category-cancel" id="shiroki-post-bulk-category-cancel">❌ 取消</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(bulkCategoryModalHTML);

            // 🗑️ 批量删除确认弹窗
            var bulkDeleteModalHTML = `
                <div class="shiroki-post-bulk-delete-modal" id="shiroki-post-bulk-delete-modal" style="display: none;">
                    <div class="shiroki-post-bulk-delete-modal-backdrop"></div>
                    <div class="shiroki-post-bulk-delete-modal-content">
                        <div class="shiroki-post-bulk-delete-modal-header">
                            <span class="shiroki-post-bulk-delete-modal-title">🗑️ 批量删除确认</span>
                            <button class="shiroki-post-bulk-delete-modal-close">✕</button>
                        </div>
                        <div class="shiroki-post-bulk-delete-modal-body">
                            <div class="shiroki-post-bulk-delete-icon">🗑️</div>
                            <p class="shiroki-post-bulk-delete-message">确定要将选中的 <span class="shiroki-post-bulk-delete-count" id="shiroki-post-bulk-delete-count">0</span> 篇文章移到回收站吗？</p>
                            <p class="shiroki-post-bulk-delete-hint">此操作可以将文章移到回收站，之后可以恢复。</p>
                        </div>
                        <div class="shiroki-post-bulk-delete-modal-footer">
                            <button class="shiroki-post-bulk-delete-confirm" id="shiroki-post-bulk-delete-confirm">🗑️ 确认删除</button>
                            <button class="shiroki-post-bulk-delete-cancel" id="shiroki-post-bulk-delete-cancel">❌ 取消</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(bulkDeleteModalHTML);

            // 📝 批量转为草稿确认弹窗
            var bulkDraftModalHTML = `
                <div class="shiroki-post-bulk-draft-modal" id="shiroki-post-bulk-draft-modal" style="display: none;">
                    <div class="shiroki-post-bulk-draft-modal-backdrop"></div>
                    <div class="shiroki-post-bulk-draft-modal-content">
                        <div class="shiroki-post-bulk-draft-modal-header">
                            <span class="shiroki-post-bulk-draft-modal-title">📝 转为草稿确认</span>
                            <button class="shiroki-post-bulk-draft-modal-close">✕</button>
                        </div>
                        <div class="shiroki-post-bulk-draft-modal-body">
                            <div class="shiroki-post-bulk-draft-icon">📝</div>
                            <p class="shiroki-post-bulk-draft-message">确定要将选中的 <span class="shiroki-post-bulk-draft-count" id="shiroki-post-bulk-draft-count">0</span> 篇文章转为草稿吗？</p>
                            <p class="shiroki-post-bulk-draft-hint">转为草稿后，文章将不再对外公开显示。</p>
                        </div>
                        <div class="shiroki-post-bulk-draft-modal-footer">
                            <button class="shiroki-post-bulk-draft-confirm" id="shiroki-post-bulk-draft-confirm">📝 确认转为草稿</button>
                            <button class="shiroki-post-bulk-draft-cancel" id="shiroki-post-bulk-draft-cancel">❌ 取消</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(bulkDraftModalHTML);

            // 🚀 触发自定义事件，通知JS可以初始化了
            $(document).trigger('shiroki-post-grid-ready');
        });
        </script>
        <?php
    }
    
    /**
     * 📡 AJAX获取文章列表
     */
    public function ajax_get_posts() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        // 构建查询参数
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // 状态筛选
        if ($status !== 'all') {
            $args['post_status'] = $status;
        } else {
            $args['post_status'] = array('publish', 'future', 'draft', 'pending', 'private');
        }
        
        // 分类筛选
        if ($category > 0) {
            $args['cat'] = $category;
        }
        
        // 搜索
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        $posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $posts[] = $this->format_post_item($post_id);
            }
        }
        
        wp_reset_postdata();

        /* ◀️ 计算是否还有更多文章 */
        $total_loaded = ($page - 1) * $per_page + count($posts);
        $has_more = $query->found_posts > $total_loaded;

        wp_send_json_success(array(
            'posts' => $posts,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page,
            'has_more' => $has_more,
            'total_loaded' => $total_loaded,
            'total_posts' => $query->found_posts
        ));
    }
    
    /**
     * 🎨 格式化文章数据
     */
    private function format_post_item($post_id) {
        $post = get_post($post_id);
        
        // 获取特色图片
        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        
        // 获取作者
        $author = get_the_author_meta('display_name', $post->post_author);
        
        // 获取分类
        $categories = get_the_category($post_id);
        $category_names = array();
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
        $category_string = implode(', ', $category_names);
        
        // 格式化日期
        $date = get_the_date('Y-m-d H:i', $post_id);

        /* 🔐 读取 shiroki-content-paywall 插件内容保护配置 */
        $paywall_enabled = false;
        $paywall_types   = array();
        $paywall_price   = 0;

        if ( class_exists( 'Shiroki_CP_Post_Meta' ) ) {
            $paywall_config = Shiroki_CP_Post_Meta::get_post_config( $post_id );
            $paywall_enabled = ! empty( $paywall_config['enabled'] );

            if ( $paywall_enabled && ! empty( $paywall_config['zones'] ) ) {
                foreach ( $paywall_config['zones'] as $zone ) {
                    if ( isset( $zone['type'] ) && ! in_array( $zone['type'], $paywall_types, true ) ) {
                        $paywall_types[] = $zone['type'];
                    }
                }
            }

            if ( ! empty( $paywall_config['price'] ) ) {
                $paywall_price = floatval( $paywall_config['price'] );
            }
        }

        return array(
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'author' => $author,
            'categories' => $category_string,
            'date' => $date,
            'status' => $post->post_status,
            'thumbnail' => $thumbnail,
            'edit_link' => get_edit_post_link($post_id, 'raw'),
            'view_link' => get_permalink($post_id),
            'password_protected' => !empty($post->post_password),
            'paywall_enabled' => $paywall_enabled,
            'paywall_types' => $paywall_types,
            'paywall_price' => $paywall_price
        );
    }
    
    /**
     * 🗑️ AJAX单篇文章移到回收站
     */
    public function ajax_trash_post() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }
        
        $result = wp_trash_post($post_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '已移到回收站'));
        } else {
            wp_send_json_error('操作失败');
        }
    }
    
    /**
     * ♻️ AJAX还原文章
     */
    public function ajax_restore_post() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }
        
        $result = wp_untrash_post($post_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '已还原'));
        } else {
            wp_send_json_error('操作失败');
        }
    }
    
    /**
     * 🗑️ AJAX批量移到回收站
     */
    public function ajax_bulk_trash_posts() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';
        
        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }
        
        $ids = explode(',', $post_ids);
        $success_count = 0;
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id > 0) {
                $result = wp_trash_post($id);
                if ($result) {
                    $success_count++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => "已成功将 {$success_count} 篇文章移到回收站",
            'count' => $success_count
        ));
    }
    
    /**
     * 📋 AJAX获取文章内容
     */
    public function ajax_get_post_content() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }
        
        $post = get_post($post_id);
        
        if (!$post) {
            wp_send_json_error('文章不存在');
        }
        
        /* 📝 获取文章内容（已格式化） */
        $content = apply_filters('the_content', $post->post_content);
        
        /* 📝 构建完整的文章内容 */
        $full_content = '';
        $full_content .= "标题：" . $post->post_title . "\n\n";
        $full_content .= "链接：" . get_permalink($post_id) . "\n\n";
        $full_content .= "作者：" . get_the_author_meta('display_name', $post->post_author) . "\n";
        $full_content .= "日期：" . get_the_date('Y-m-d H:i', $post_id) . "\n\n";
        $full_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $full_content .= strip_tags($content);
        
        wp_send_json_success(array(
            'content' => $full_content,
            'title' => $post->post_title
        ));
    }
    
    /**
     * 📋 AJAX批量获取文章内容
     */
    public function ajax_get_posts_content() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';
        
        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }
        
        $ids = explode(',', $post_ids);
        $all_content = array();
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            
            $post = get_post($id);
            if (!$post) continue;
            
            /* 📝 获取文章内容 */
            $content = apply_filters('the_content', $post->post_content);
            
            /* 📝 构建单篇文章内容 */
            $full_content = '';
            $full_content .= "标题：" . $post->post_title . "\n";
            $full_content .= "链接：" . get_permalink($id) . "\n";
            $full_content .= "作者：" . get_the_author_meta('display_name', $post->post_author) . "\n";
            $full_content .= "日期：" . get_the_date('Y-m-d H:i', $id) . "\n";
            $full_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $full_content .= strip_tags($content);
            
            $all_content[] = $full_content;
        }
        
        if (empty($all_content)) {
            wp_send_json_error('未找到文章内容');
        }
        
        /* 📝 用分隔符连接所有文章 */
        $separator = "\n\n══════════════════════════════════════════════════\n\n";
        $final_content = implode($separator, $all_content);
        
        wp_send_json_success(array(
            'content' => $final_content,
            'count' => count($all_content)
        ));
    }
    
    /**
     * 📋 AJAX复制单篇文章
     */
    public function ajax_clone_post() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }
        
        $result = $this->clone_post($post_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(array(
            'new_post_id' => $result,
            'edit_link' => get_edit_post_link($result, 'raw')
        ));
    }
    
    /**
     * 📋 AJAX批量复制文章
     */
    public function ajax_clone_posts() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';
        
        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }
        
        $ids = explode(',', $post_ids);
        $cloned_count = 0;
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            
            $result = $this->clone_post($id);
            if (!is_wp_error($result)) {
                $cloned_count++;
            }
        }
        
        wp_send_json_success(array(
            'cloned_count' => $cloned_count
        ));
    }

    /**
     * 🏷️ AJAX批量修改文章分类
     */
    public function ajax_bulk_edit_category() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }

        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';
        $category_ids = isset($_POST['category_ids']) ? sanitize_text_field($_POST['category_ids']) : '';

        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }

        if (empty($category_ids)) {
            wp_send_json_error('未选择分类');
        }

        $ids = explode(',', $post_ids);
        /* ◀️ 将分类ID转换为整数数组 */
        $cat_ids = array_map('intval', explode(',', $category_ids));
        $cat_ids = array_filter($cat_ids);
        $success_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            /* 🏷️ 设置文章分类 */
            $result = wp_set_object_terms($id, $cat_ids, 'category');
            if (!is_wp_error($result)) {
                $success_count++;
            }
        }

        wp_send_json_success(array(
            'message' => "已成功修改 {$success_count} 篇文章的分类",
            'count' => $success_count
        ));
    }

    /**
     * 🚀 AJAX发布文章
     */
    public function ajax_publish_post() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }

        /* 🔐 检查权限 */
        if (!current_user_can('publish_posts')) {
            wp_send_json_error('权限不足');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }

        /* 📝 更新文章状态为已发布 */
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ), true);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => '文章已发布'
        ));
    }

    /**
     * 📝 AJAX批量转为草稿
     */
    public function ajax_bulk_draft_posts() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }

        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';

        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }

        $ids = explode(',', $post_ids);
        $success_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            /* 📝 更新文章状态为草稿 */
            $result = wp_update_post(array(
                'ID' => $id,
                'post_status' => 'draft'
            ), true);

            if (!is_wp_error($result)) {
                $success_count++;
            }
        }

        wp_send_json_success(array(
            'message' => "已成功将 {$success_count} 篇文章转为草稿",
            'count' => $success_count
        ));
    }

    /**
     * ❌ AJAX批量彻底删除
     */
    public function ajax_bulk_delete_posts() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_post_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }

        /* 🔐 检查权限 */
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }

        $post_ids = isset($_POST['post_ids']) ? sanitize_text_field($_POST['post_ids']) : '';

        if (empty($post_ids)) {
            wp_send_json_error('未选择文章');
        }

        $ids = explode(',', $post_ids);
        $success_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            /* ❌ 彻底删除文章 */
            $result = wp_delete_post($id, true);

            if ($result !== false) {
                $success_count++;
            }
        }

        wp_send_json_success(array(
            'message' => "已成功彻底删除 {$success_count} 篇文章",
            'count' => $success_count
        ));
    }

    /**
     * 📋 复制文章核心方法
     */
    private function clone_post($post_id) {
        /* 🔍 获取原文章 */
        $post = get_post($post_id);
        
        if (!$post) {
            return new WP_Error('post_not_found', '文章不存在');
        }
        
        /* 📝 构建新文章数据 */
        $new_post = array(
            'post_title'   => $post->post_title . ' - 副本',
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_status'  => 'draft', /* ◀️ 新文章设为草稿状态 */
            'post_type'    => $post->post_type,
            'post_author'  => get_current_user_id(),
            'post_password'=> $post->post_password,
            'comment_status'=> $post->comment_status,
            'ping_status'  => $post->ping_status,
            'post_parent'  => $post->post_parent,
            'menu_order'   => $post->menu_order,
        );
        
        /* 📝 插入新文章 */
        $new_post_id = wp_insert_post($new_post, true);
        
        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }
        
        /* 🏷️ 复制分类 */
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_object_terms($new_post_id, $terms, $taxonomy);
            }
        }
        
        /* 🏷️ 复制标签 */
        $tags = wp_get_post_tags($post_id, array('fields' => 'ids'));
        if (!is_wp_error($tags) && !empty($tags)) {
            wp_set_post_tags($new_post_id, $tags);
        }
        
        /* 🖼️ 复制特色图片 */
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }
        
        /* 📝 复制自定义字段 */
        $meta_keys = get_post_custom_keys($post_id);
        if ($meta_keys) {
            foreach ($meta_keys as $meta_key) {
                /* ◀️ 跳过一些内部字段 */
                if (in_array($meta_key, array('_wp_old_slug', '_wp_old_date', '_edit_lock', '_edit_last'))) {
                    continue;
                }
                $meta_values = get_post_custom_values($meta_key, $post_id);
                foreach ($meta_values as $meta_value) {
                    add_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
                }
            }
        }
        
        return $new_post_id;
    }
}

/**
 * 🚀 初始化文章网格UI
 */
function shiroki_init_post_grid_ui() {
    Shiroki_Post_Grid_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_post_grid_ui');
