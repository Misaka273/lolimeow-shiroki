<?php
/**
 * 🔗 链接后台网格管理
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Link_Grid
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Shiroki_Link_Grid_UI {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_footer', array($this, 'mount_page'), 30);
        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_action('wp_ajax_shiroki_get_links', array($this, 'ajax_get_links'));
        add_action('wp_ajax_shiroki_save_link', array($this, 'ajax_save_link'));
        add_action('wp_ajax_shiroki_delete_link', array($this, 'ajax_delete_link'));
        add_action('wp_ajax_shiroki_bulk_delete_links', array($this, 'ajax_bulk_delete_links'));
        add_action('wp_ajax_shiroki_get_link_categories', array($this, 'ajax_get_categories'));
        add_action('wp_ajax_shiroki_save_link_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_shiroki_delete_link_category', array($this, 'ajax_delete_category'));
        add_action('wp_ajax_shiroki_bulk_delete_link_categories', array($this, 'ajax_bulk_delete_categories'));
        add_action('wp_ajax_shiroki_bulk_update_link_categories', array($this, 'ajax_bulk_update_categories'));
    }

    public function admin_body_class($classes) {
        if ($this->is_link_screen() && current_user_can('manage_links')) {
            $classes .= ' shiroki-link-grid-ready';
        }
        return $classes;
    }

    private function is_link_screen() {
        global $pagenow;
        return in_array($pagenow, array('link-manager.php', 'link-add.php', 'link.php', 'edit-tags.php'), true)
            && (($pagenow !== 'edit-tags.php') || (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'link_category'));
    }

    public function enqueue_assets($hook) {
        if (!$this->is_link_screen()) {
            return;
        }

        $uri = get_stylesheet_directory_uri();
        $dir = get_stylesheet_directory();
        $css = $dir . '/assets/css/admin/link-grid/link-grid.css';
        $js = $dir . '/assets/js/admin/link-grid/link-grid.js';

        wp_enqueue_style('shiroki-link-grid', $uri . '/assets/css/admin/link-grid/link-grid.css', array('admin-variables'), file_exists($css) ? filemtime($css) : wp_get_theme()->get('Version'));
        wp_enqueue_script('shiroki-link-grid', $uri . '/assets/js/admin/link-grid/link-grid.js', array('jquery'), file_exists($js) ? filemtime($js) : wp_get_theme()->get('Version'), true);
        wp_localize_script('shiroki-link-grid', 'shirokiLinkGridConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_link_grid_nonce'),
            'screen' => $this->get_screen_mode(),
        ));
    }

    private function get_screen_mode() {
        global $pagenow;
        if ($pagenow === 'link-add.php') {
            return 'add';
        }
        if ($pagenow === 'link.php') {
            return 'edit';
        }
        if ($pagenow === 'edit-tags.php') {
            return 'categories';
        }
        return 'list';
    }

    public function mount_page() {
        if (!$this->is_link_screen() || !current_user_can('manage_links')) {
            return;
        }
        ?>
        <script>
        jQuery(function($) {
            $('.wrap > h1.wp-heading-inline').first().remove();
            $('.wrap .page-title-action, .wrap .wp-header-end').remove();
            $('.wrap').first().prepend('<div id="shiroki-link-grid-root" class="shiroki-link-grid-root"></div>');
            $(document).trigger('shiroki-link-grid-ready');
        });
        </script>
        <?php
    }

    private function verify_ajax() {
        if (!check_ajax_referer('shiroki_link_grid_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        if (!current_user_can('manage_links')) {
            wp_send_json_error(array('message' => '权限不足'));
        }
    }

    private function format_link($link) {
        $categories = wp_get_link_cats($link->link_id);
        $category_names = array();
        $category_paths = array();
        foreach ($categories as $category_id) {
            $term = get_term($category_id, 'link_category');
            if ($term && !is_wp_error($term)) {
                $category_names[] = $term->name;
                $ancestor_ids = array_reverse(get_ancestors($term->term_id, 'link_category'));
                $path_names = array();
                foreach ($ancestor_ids as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'link_category');
                    if ($ancestor && !is_wp_error($ancestor)) {
                        $path_names[] = $ancestor->name;
                    }
                }
                $path_names[] = $term->name;
                $category_paths[] = implode(' / ', $path_names);
            }
        }
        return array(
            'id' => intval($link->link_id),
            'name' => $link->link_name,
            'url' => $link->link_url,
            'description' => $link->link_description,
            'image' => $link->link_image,
            'target' => $link->link_target,
            'rel' => isset($link->link_rel) ? $link->link_rel : '',
            'rss' => isset($link->link_rss) ? $link->link_rss : '',
            'notes' => isset($link->link_notes) ? $link->link_notes : '',
            'rating' => isset($link->link_rating) ? intval($link->link_rating) : 0,
            'visible' => $link->link_visible,
            'categories' => $categories,
            'category_names' => $category_names,
            'category_paths' => $category_paths,
            'edit_url' => admin_url('link.php?action=edit&link_id=' . intval($link->link_id)),
        );
    }

    public function ajax_get_links() {
        $this->verify_ajax();
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $args = array('hide_invisible' => 0, 'orderby' => 'name', 'order' => 'ASC');
        if ($category > 0) {
            $args['category'] = $category;
        }
        $items = array();
        foreach (get_bookmarks($args) as $link) {
            $item = $this->format_link($link);
            if ($search && stripos($item['name'] . ' ' . $item['url'] . ' ' . $item['description'], $search) === false) {
                continue;
            }
            $items[] = $item;
        }
        wp_send_json_success(array('links' => $items, 'categories' => $this->get_categories()));
    }

    private function get_categories() {
        $terms = get_terms(array('taxonomy' => 'link_category', 'hide_empty' => false));
        if (is_wp_error($terms)) {
            return array();
        }
        return array_map(function($term) {
            $parent = $term->parent ? get_term($term->parent, 'link_category') : null;
            return array('id' => intval($term->term_id), 'name' => $term->name, 'description' => $term->description, 'slug' => $term->slug, 'parent' => intval($term->parent), 'parent_name' => $parent && !is_wp_error($parent) ? $parent->name : '', 'count' => intval($term->count));
        }, $terms);
    }

    public function ajax_get_categories() {
        $this->verify_ajax();
        wp_send_json_success(array('categories' => $this->get_categories()));
    }

    public function ajax_save_link() {
        $this->verify_ajax();
        $id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $data = array(
            'link_id' => $id,
            'link_name' => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
            'link_url' => isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '',
            'link_description' => isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '',
            'link_image' => isset($_POST['image']) ? esc_url_raw(wp_unslash($_POST['image'])) : '',
            'link_target' => isset($_POST['target']) && in_array($_POST['target'], array('', '_blank', '_top'), true) ? sanitize_text_field(wp_unslash($_POST['target'])) : '',
            'link_rel' => isset($_POST['rel']) ? sanitize_text_field(wp_unslash($_POST['rel'])) : '',
            'link_rss' => isset($_POST['rss']) ? esc_url_raw(wp_unslash($_POST['rss'])) : '',
            'link_notes' => isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '',
            'link_rating' => isset($_POST['rating']) ? max(0, min(10, intval($_POST['rating']))) : 0,
            'link_visible' => isset($_POST['visible']) && $_POST['visible'] === 'N' ? 'N' : 'Y',
        );
        if (!$data['link_name'] || !$data['link_url'] || !wp_http_validate_url($data['link_url'])) {
            wp_send_json_error(array('message' => '请填写有效的链接名称和 URL'));
        }
        $saved_id = $id ? wp_update_link($data) : wp_insert_link($data);
        if (is_wp_error($saved_id) || !$saved_id) {
            wp_send_json_error(array('message' => '链接保存失败'));
        }
        $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();
        wp_set_link_cats($saved_id, $categories);
        wp_send_json_success(array('id' => intval($saved_id), 'message' => $id ? '链接已更新' : '链接已添加'));
    }

    public function ajax_delete_link() {
        $this->verify_ajax();
        $id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        if (!$id || !get_bookmark($id)) {
            wp_send_json_error(array('message' => '链接不存在'));
        }
        wp_send_json_success(array('message' => wp_delete_link($id) ? '链接已删除' : '删除失败'));
    }

    public function ajax_bulk_delete_links() {
        $this->verify_ajax();
        $ids = isset($_POST['link_ids']) && is_array($_POST['link_ids']) ? array_map('intval', $_POST['link_ids']) : array();
        $deleted = 0;
        foreach (array_unique($ids) as $id) {
            if ($id && get_bookmark($id) && wp_delete_link($id)) {
                $deleted++;
            }
        }
        wp_send_json_success(array('message' => sprintf('已删除 %d 个链接', $deleted), 'count' => $deleted));
    }

    public function ajax_save_category() {
        $this->verify_ajax();
        $id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        $parent = isset($_POST['parent']) ? max(0, intval($_POST['parent'])) : 0;
        if (!$name) {
            wp_send_json_error(array('message' => '分类名称不能为空'));
        }
        if ($id && $parent === $id) {
            wp_send_json_error(array('message' => '分类不能设置自身为父级'));
        }
        if ($id && $parent && term_is_ancestor_of($id, $parent, 'link_category')) {
            wp_send_json_error(array('message' => '分类不能设置子级为父级'));
        }
        $term_args = array('name' => $name, 'description' => $description, 'parent' => $parent);
        if ($slug) {
            $term_args['slug'] = $slug;
        }
        $result = $id ? wp_update_term($id, 'link_category', $term_args) : wp_insert_term($name, 'link_category', $term_args);
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        wp_send_json_success(array('message' => $id ? '分类已更新' : '分类已添加'));
    }

    public function ajax_delete_category() {
        $this->verify_ajax();
        $id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        if (!$id || is_wp_error(wp_delete_term($id, 'link_category'))) {
            wp_send_json_error(array('message' => '分类删除失败'));
        }
        wp_send_json_success(array('message' => '分类已删除'));
    }

    public function ajax_bulk_delete_categories() {
        $this->verify_ajax();
        $ids = isset($_POST['term_ids']) && is_array($_POST['term_ids']) ? array_map('intval', $_POST['term_ids']) : array();
        $deleted = 0;
        foreach (array_unique($ids) as $id) {
            if ($id && !is_wp_error(wp_delete_term($id, 'link_category'))) {
                $deleted++;
            }
        }
        wp_send_json_success(array('message' => sprintf('已删除 %d 个分类', $deleted), 'count' => $deleted));
    }

    public function ajax_bulk_update_categories() {
        $this->verify_ajax();
        $ids = isset($_POST['term_ids']) && is_array($_POST['term_ids']) ? array_map('intval', $_POST['term_ids']) : array();
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        $parent = isset($_POST['parent']) ? max(0, intval($_POST['parent'])) : -1;
        if (!$ids || (!$description && !$slug && $parent < 0)) {
            wp_send_json_error(array('message' => '请填写需要批量修改的内容'));
        }
        $updated = 0;
        foreach (array_unique($ids) as $id) {
            $term = get_term($id, 'link_category');
            if (!$term || is_wp_error($term)) {
                continue;
            }
            $term_args = array();
            if ($description) {
                $term_args['description'] = $description;
            }
            if ($slug) {
                $term_args['slug'] = $slug;
            }
            if ($parent >= 0 && $parent !== $id && !($parent && term_is_ancestor_of($id, $parent, 'link_category'))) {
                $term_args['parent'] = $parent;
            }
            if ($term_args && !is_wp_error(wp_update_term($id, 'link_category', $term_args))) {
                $updated++;
            }
        }
        wp_send_json_success(array('message' => sprintf('已批量修改 %d 个分类', $updated), 'count' => $updated));
    }
}

add_action('after_setup_theme', function() {
    Shiroki_Link_Grid_UI::get_instance();
});
