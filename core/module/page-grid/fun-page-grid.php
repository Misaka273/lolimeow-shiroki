<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📝 页面列表网格卡片式布局
 * 🎨 拟态拟物玻璃质感设计
 * 
 * @package Lolimeow_Shiroki
 * @subpackage Page_Grid
 * @since 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 页面网格UI主类
 */
class Shiroki_Page_Grid_UI {
    
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
        
        /* 📡 AJAX处理 */
        add_action('wp_ajax_shiroki_get_pages', array($this, 'ajax_get_pages'));
        add_action('wp_ajax_shiroki_trash_page', array($this, 'ajax_trash_page'));
        add_action('wp_ajax_shiroki_restore_page', array($this, 'ajax_restore_page'));
        add_action('wp_ajax_shiroki_bulk_trash_pages', array($this, 'ajax_bulk_trash_pages'));
        add_action('wp_ajax_shiroki_get_page_content', array($this, 'ajax_get_page_content'));
        add_action('wp_ajax_shiroki_get_pages_content', array($this, 'ajax_get_pages_content'));
        add_action('wp_ajax_shiroki_clone_page', array($this, 'ajax_clone_page'));
        add_action('wp_ajax_shiroki_clone_pages', array($this, 'ajax_clone_pages'));
        add_action('wp_ajax_shiroki_bulk_set_parent', array($this, 'ajax_bulk_set_parent'));
        
        /* 🎨 在管理页脚添加自定义UI - 使用较低优先级确保在脚本之后 */
        add_action('admin_footer', array($this, 'add_custom_page_ui'), 20);
    }
    
    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* 🎯 只在页面列表页面加载 */
        if ($hook !== 'edit.php') {
            return;
        }
        
        /* 🎯 检查是否是页面类型 */
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        if ($post_type !== 'page') {
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
        
        /* 📝 复用文章网格样式 */
        wp_enqueue_style(
            'shiroki-page-grid',
            $theme_uri . '/assets/css/admin/post-grid/post-grid.css',
            array('admin-variables'),
            $version
        );
        
        /* 📦 页面网格脚本 */
        wp_enqueue_script(
            'shiroki-page-grid',
            $theme_uri . '/assets/js/admin/page-grid/page-grid.js',
            array('jquery'),
            $version,
            true
        );
        
        /* 🎯 传递AJAX配置 */
        wp_localize_script('shiroki-page-grid', 'shirokiPageConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_page_nonce'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无页面',
                'loadMore' => '加载更多'
            )
        ));
    }
    
    /**
     * 🎨 添加自定义页面列表UI
     */
    public function add_custom_page_ui() {
        /* 🔍 确保 get_current_screen 函数存在 */
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        
        /* 🎯 检查是否在页面列表页面 */
        if (!$screen) {
            return;
        }
        
        /* 📋 支持的页面ID列表 - 只支持页面列表页面 */
        $allowed_screens = array('edit-page');
        if (!in_array($screen->id, $allowed_screens)) {
            return;
        }
        
        /* 📝 获取当前post type */
        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        
        /* 🎯 只在页面列表页面显示 */
        if ($post_type !== 'page') {
            return;
        }
        
        /* 📊 获取页面状态数量 */
        $status_counts = wp_count_posts('page');
        
        /* ◀️ 确保 $status_counts 是对象 */
        if (!is_object($status_counts)) {
            $status_counts = new stdClass();
            $status_counts->publish = 0;
            $status_counts->draft = 0;
            $status_counts->pending = 0;
            $status_counts->private = 0;
            $status_counts->trash = 0;
        }
        
        /* 📑 获取页面层级关系 */
        $parent_pages = get_pages(array(
            'parent' => 0,
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'sort_column' => 'menu_order, post_title'
        ));
        
        /* ◀️ 确保 $parent_pages 是数组 */
        if (!is_array($parent_pages)) {
            $parent_pages = array();
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 隐藏原版列表表格和分页
            $('.wp-list-table, .tablenav, .view-switch').hide();
            
            // 获取新建页面按钮
            var $addButton = $('.page-title-action');
            
            // 插入自定义UI
            var customUI = `
                <div class="shiroki-post-wrapper">
                    <!-- 🎯 顶部工具栏：状态筛选 + 批量操作 + 新建页面按钮 -->
                    <div class="shiroki-post-top-bar">
                        <!-- 📊 状态筛选 -->
                        <div class="shiroki-post-filter-wrapper">
                            <span class="shiroki-post-filter-label">📊 状态筛选：</span>
                            <div class="shiroki-post-status-options">
                                <button class="shiroki-post-status-btn active" data-status="all">
                                    📁 全部 (<?php echo intval($status_counts->publish + $status_counts->draft + $status_counts->pending + $status_counts->private); ?>)
                                </button>
                                <button class="shiroki-post-status-btn" data-status="publish">
                                    🟢 已发布 (<?php echo intval($status_counts->publish); ?>)
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
                            <span class="shiroki-post-bulk-count">已选择 <span class="shiroki-post-bulk-count-num">0</span> 个</span>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-copy-links" data-action="copy-links">
                                🔗 复制链接
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-copy-content" data-action="copy-content">
                                📋 复制页面
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-set-parent" data-action="set-parent">
                                📑 修改父级
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-delete" data-action="delete">
                                🗑️ 批量删除
                            </button>
                            <button class="shiroki-post-bulk-btn shiroki-post-bulk-cancel" data-action="cancel">
                                ❌ 取消选择
                            </button>
                        </div>
                        
                        <!-- 📝 新建页面按钮容器 -->
                        <div class="shiroki-post-add-wrapper">
                            <div class="shiroki-post-add-container"></div>
                        </div>
                    </div>
                    
                    <!-- 🔍 搜索框 -->
                    <div class="shiroki-post-search">
                        <input type="text"
                               id="shiroki-post-search"
                               placeholder="🔍 搜索页面..."
                               autocomplete="off">
                    </div>
                    
                    <!-- 🧰 自定义工具栏 -->
                    <div class="shiroki-post-toolbar">
                        <!-- 📑 父页面筛选 -->
                        <div class="shiroki-post-category-filter" style="position: relative; flex: 1;">
                            <span class="shiroki-post-filter-label">📑 父页面：</span>
                            <div class="shiroki-post-category-options" id="shiroki-post-parent-options">
                                <button class="shiroki-post-category-btn active" data-parent="">📁 全部</button>
                                <button class="shiroki-post-category-btn" data-parent="0">📄 顶级页面</button>
                                <?php foreach ($parent_pages as $parent) : ?>
                                <button class="shiroki-post-category-btn" data-parent="<?php echo esc_attr($parent->ID); ?>">
                                    📁 <?php echo esc_html($parent->post_title); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-post-loading" id="shiroki-post-loading" style="display: none;">
                        ⏳ 加载中...
                    </div>
                    
                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-post-grid" id="shiroki-post-grid">
                        <!-- 页面卡片将通过JavaScript动态插入 -->
                    </div>
                    
                    <!-- 📭 空状态 -->
                    <div class="shiroki-post-empty" id="shiroki-post-empty" style="display: none;">
                        <svg class="shiroki-post-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="9" x2="15" y2="9"></line>
                            <line x1="9" y1="13" x2="15" y2="13"></line>
                            <line x1="9" y1="17" x2="11" y2="17"></line>
                        </svg>
                        <div class="shiroki-post-empty-text">📭 暂无页面</div>
                        <div class="shiroki-post-empty-subtext">点击上方【新建页面】按钮创建</div>
                    </div>
                    
                    <!-- 📄 分页 -->
                    <div class="shiroki-post-pagination" id="shiroki-post-pagination"></div>
                </div>
                
                <!-- 📑 批量修改父页面模态框 -->
                <div class="shiroki-post-bulk-parent-modal" id="shiroki-post-bulk-parent-modal" style="display: none;">
                    <div class="shiroki-post-bulk-parent-modal-backdrop"></div>
                    <div class="shiroki-post-bulk-parent-modal-content">
                        <div class="shiroki-post-bulk-parent-modal-header">
                            <span class="shiroki-post-bulk-parent-modal-title">📑 批量修改父页面</span>
                            <button class="shiroki-post-bulk-parent-modal-close">✕</button>
                        </div>
                        <div class="shiroki-post-bulk-parent-modal-body">
                            <p class="shiroki-post-bulk-parent-hint">请选择要设置的新父页面：</p>
                            <!-- 🔍 父页面搜索框 -->
                            <div class="shiroki-post-bulk-parent-search">
                                <input type="text"
                                       id="shiroki-post-bulk-parent-search-input"
                                       placeholder="🔍 搜索父页面..."
                                       autocomplete="off">
                            </div>
                            <div class="shiroki-post-bulk-parent-options" id="shiroki-post-bulk-parent-options">
                                <!-- 选项将通过JavaScript动态生成 -->
                            </div>
                        </div>
                        <div class="shiroki-post-bulk-parent-modal-footer">
                            <button class="shiroki-post-bulk-parent-confirm" id="shiroki-post-bulk-parent-confirm">✅ 确认修改</button>
                            <button class="shiroki-post-bulk-parent-cancel" id="shiroki-post-bulk-parent-cancel">❌ 取消</button>
                        </div>
                    </div>
                </div>
            `;
            
            // 插入到 .wrap 容器内
            $('.wrap > h1').after(customUI);
            
            // 将新建页面按钮移动到自定义容器
            if ($addButton.length) {
                $addButton.appendTo('.shiroki-post-add-container');
            }

            // 🎯 将新建页面按钮容器移动到页面标题右侧
            $('.shiroki-post-add-container').insertAfter('.wrap > h1.wp-heading-inline');

            // 🔍 将搜索框移动到状态筛选右侧
            $('.shiroki-post-search').insertAfter('.shiroki-post-filter-wrapper');
            
            // 🚀 触发自定义事件，通知JS可以初始化了
            $(document).trigger('shiroki-page-grid-ready');
        });
        </script>
        <?php
    }
    
    /**
     * 📡 AJAX获取页面列表
     */
    public function ajax_get_pages() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $parent = isset($_POST['parent']) ? sanitize_text_field($_POST['parent']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        /* 📝 构建查询参数 */
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        /* 📊 状态筛选 */
        if ($status !== 'all') {
            $args['post_status'] = $status;
        } else {
            $args['post_status'] = array('publish', 'draft', 'pending', 'private');
        }
        
        /* 📑 父页面筛选 */
        if ($parent !== '') {
            $args['post_parent'] = intval($parent);
        }
        
        /* 🔍 搜索 */
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        $pages = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $page_id = get_the_ID();
                $formatted_page = $this->format_page_item($page_id);
                if ($formatted_page !== null) {
                    $pages[] = $formatted_page;
                }
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'pages' => $pages,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page,
            'has_more' => $page < $query->max_num_pages
        ));
    }
    
    /**
     * 🎨 格式化页面数据
     */
    private function format_page_item($page_id) {
        $page = get_post($page_id);
        
        if (!$page) {
            return null;
        }
        
        /* 🖼️ 获取特色图片 */
        $thumbnail = get_the_post_thumbnail_url($page_id, 'medium');
        
        /* 👤 获取作者 */
        $author = get_the_author_meta('display_name', $page->post_author);
        
        /* 📑 获取父页面 */
        $parent_title = '';
        if ($page->post_parent > 0) {
            $parent = get_post($page->post_parent);
            if ($parent) {
                $parent_title = $parent->post_title;
            }
        }
        
        /* 📅 格式化日期 */
        $date = get_the_date('Y-m-d H:i', $page_id);
        
        /* 📊 获取子页面数量 - 使用更高效的查询 */
        $child_count = 0;
        $child_query = new WP_Query(array(
            'post_type' => 'page',
            'post_parent' => $page_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'any'
        ));
        if (!is_wp_error($child_query)) {
            $child_count = $child_query->found_posts;
        }
        wp_reset_postdata();
        
        return array(
            'id' => $page_id,
            'title' => get_the_title($page_id),
            'author' => $author,
            'parent' => $parent_title,
            'child_count' => $child_count,
            'date' => $date,
            'status' => $page->post_status,
            'thumbnail' => $thumbnail,
            'edit_link' => get_edit_post_link($page_id, 'raw'),
            'view_link' => get_permalink($page_id),
            'password_protected' => !empty($page->post_password),
            'menu_order' => $page->menu_order
        );
    }
    
    /**
     * 🗑️ AJAX单个页面移到回收站
     */
    public function ajax_trash_page() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('delete_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_send_json_error(array('message' => '无效的页面ID'));
        }
        
        $result = wp_trash_post($page_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '已移到回收站'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }
    
    /**
     * ♻️ AJAX还原页面
     */
    public function ajax_restore_page() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('delete_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_send_json_error(array('message' => '无效的页面ID'));
        }
        
        $result = wp_untrash_post($page_id);
        
        if ($result) {
            wp_send_json_success(array('message' => '已还原'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }
    
    /**
     * 🗑️ AJAX批量移到回收站
     */
    public function ajax_bulk_trash_pages() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('delete_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_ids = isset($_POST['page_ids']) ? sanitize_text_field($_POST['page_ids']) : '';
        
        if (empty($page_ids)) {
            wp_send_json_error(array('message' => '未选择页面'));
        }
        
        $ids = explode(',', $page_ids);
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
            'message' => "已成功将 {$success_count} 个页面移到回收站",
            'count' => $success_count
        ));
    }
    
    /**
     * 📋 AJAX获取页面内容
     */
    public function ajax_get_page_content() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_send_json_error(array('message' => '无效的页面ID'));
        }
        
        $page = get_post($page_id);
        
        if (!$page) {
            wp_send_json_error(array('message' => '页面不存在'));
        }
        
        /* 📝 获取页面内容（已格式化） */
        $content = apply_filters('the_content', $page->post_content);
        
        /* 📝 构建完整的页面内容 */
        $full_content = '';
        $full_content .= "标题：" . $page->post_title . "\n\n";
        $full_content .= "链接：" . get_permalink($page_id) . "\n\n";
        $full_content .= "作者：" . get_the_author_meta('display_name', $page->post_author) . "\n";
        $full_content .= "日期：" . get_the_date('Y-m-d H:i', $page_id) . "\n\n";
        $full_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $full_content .= strip_tags($content);
        
        wp_send_json_success(array(
            'content' => $full_content,
            'title' => $page->post_title
        ));
    }
    
    /**
     * 📋 AJAX批量获取页面内容
     */
    public function ajax_get_pages_content() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_ids = isset($_POST['page_ids']) ? sanitize_text_field($_POST['page_ids']) : '';
        
        if (empty($page_ids)) {
            wp_send_json_error(array('message' => '未选择页面'));
        }
        
        $ids = explode(',', $page_ids);
        $all_content = array();
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            
            $page = get_post($id);
            if (!$page) continue;
            
            /* 📝 获取页面内容 */
            $content = apply_filters('the_content', $page->post_content);
            
            /* 📝 构建单个页面内容 */
            $full_content = '';
            $full_content .= "标题：" . $page->post_title . "\n";
            $full_content .= "链接：" . get_permalink($id) . "\n";
            $full_content .= "作者：" . get_the_author_meta('display_name', $page->post_author) . "\n";
            $full_content .= "日期：" . get_the_date('Y-m-d H:i', $id) . "\n";
            $full_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $full_content .= strip_tags($content);
            
            $all_content[] = $full_content;
        }
        
        if (empty($all_content)) {
            wp_send_json_error(array('message' => '未找到页面内容'));
        }
        
        /* 📝 用分隔符连接所有页面 */
        $separator = "\n\n══════════════════════════════════════════════════\n\n";
        $final_content = implode($separator, $all_content);
        
        wp_send_json_success(array(
            'content' => $final_content,
            'count' => count($all_content)
        ));
    }
    
    /**
     * 📋 AJAX复制单个页面
     */
    public function ajax_clone_page() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!$page_id) {
            wp_send_json_error(array('message' => '无效的页面ID'));
        }
        
        $result = $this->clone_page($page_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'new_page_id' => $result,
            'edit_link' => get_edit_post_link($result, 'raw')
        ));
    }
    
    /**
     * 📋 AJAX批量复制页面
     */
    public function ajax_clone_pages() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_ids = isset($_POST['page_ids']) ? sanitize_text_field($_POST['page_ids']) : '';
        
        if (empty($page_ids)) {
            wp_send_json_error(array('message' => '未选择页面'));
        }
        
        $ids = explode(',', $page_ids);
        $cloned_count = 0;
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            
            $result = $this->clone_page($id);
            if (!is_wp_error($result)) {
                $cloned_count++;
            }
        }
        
        wp_send_json_success(array(
            'cloned_count' => $cloned_count
        ));
    }
    
    /**
     * � AJAX批量修改父级页面
     */
    public function ajax_bulk_set_parent() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_page_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        
        /* 🔐 检查权限 */
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
        
        $page_ids = isset($_POST['page_ids']) ? sanitize_text_field($_POST['page_ids']) : '';
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        
        if (empty($page_ids)) {
            wp_send_json_error(array('message' => '未选择页面'));
        }
        
        /* ◀️ 验证父页面是否存在（0表示顶级页面） */
        if ($parent_id > 0) {
            $parent_page = get_post($parent_id);
            if (!$parent_page || $parent_page->post_type !== 'page') {
                wp_send_json_error(array('message' => '无效的父页面'));
            }
        }
        
        $ids = explode(',', $page_ids);
        $updated_count = 0;
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;
            
            /* ◀️ 防止将页面设置为自己的子页面 */
            if ($id === $parent_id) {
                continue;
            }
            
            /* ◀️ 防止循环引用 - 检查是否将页面设置为其子页面的子页面 */
            if ($parent_id > 0) {
                $ancestors = get_post_ancestors($id);
                if (in_array($parent_id, $ancestors)) {
                    continue;
                }
            }
            
            $result = wp_update_post(array(
                'ID' => $id,
                'post_parent' => $parent_id
            ), true);
            
            if (!is_wp_error($result)) {
                $updated_count++;
            }
        }
        
        wp_send_json_success(array(
            'updated_count' => $updated_count,
            'message' => "已成功修改 {$updated_count} 个页面的父级"
        ));
    }
    
    /**
     * �� 复制页面核心方法
     */
    private function clone_page($page_id) {
        /* 🔍 获取原页面 */
        $page = get_post($page_id);
        
        if (!$page) {
            return new WP_Error('page_not_found', '页面不存在');
        }
        
        /* 📝 构建新页面数据 */
        $new_page = array(
            'post_title'   => $page->post_title . ' - 副本',
            'post_content' => $page->post_content,
            'post_excerpt' => $page->post_excerpt,
            'post_status'  => 'draft', /* ◀️ 新页面设为草稿状态 */
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
            'post_password'=> $page->post_password,
            'comment_status'=> $page->comment_status,
            'ping_status'  => $page->ping_status,
            'post_parent'  => $page->post_parent,
            'menu_order'   => $page->menu_order,
        );
        
        /* 📝 插入新页面 */
        $new_page_id = wp_insert_post($new_page, true);
        
        if (is_wp_error($new_page_id)) {
            return $new_page_id;
        }
        
        /* 🖼️ 复制特色图片 */
        $thumbnail_id = get_post_thumbnail_id($page_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_page_id, $thumbnail_id);
        }
        
        /* 📝 复制自定义字段 */
        $meta_keys = get_post_custom_keys($page_id);
        if ($meta_keys) {
            foreach ($meta_keys as $meta_key) {
                /* ◀️ 跳过一些内部字段 */
                if (in_array($meta_key, array('_wp_old_slug', '_wp_old_date', '_edit_lock', '_edit_last'))) {
                    continue;
                }
                $meta_values = get_post_custom_values($meta_key, $page_id);
                foreach ($meta_values as $meta_value) {
                    add_post_meta($new_page_id, $meta_key, maybe_unserialize($meta_value));
                }
            }
        }
        
        return $new_page_id;
    }
}

/**
 * 🚀 初始化页面网格UI
 */
function shiroki_init_page_grid_ui() {
    Shiroki_Page_Grid_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_page_grid_ui');
