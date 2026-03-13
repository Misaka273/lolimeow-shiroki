<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================
class widget_search extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'boxmoe_widget_search',
            'Boxmoe_侧栏搜索',
            array('description' => __('Boxmoe_侧栏搜索框', 'text_domain'),
            'classname' => __('widget-search', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);

        // 根据布局边框设置自动选择搜索框样式
        $search_style = 'glass'; // 默认样式
        
        if (function_exists('get_boxmoe')) {
            // 获取布局边框设置
            $border_style = get_boxmoe('boxmoe_blog_border', 'default');
            
            // emmm...根据边框设置映射到搜索样式
            $style_map = array(
                'border' => 'comic',    // 漫画边框效果 -> 漫画风搜索
                'shadow' => 'shadow',   // 阴影边框效果 -> 阴影搜索
                'lines' => 'line',      // 线条边框效果 -> 线条搜索
                'glass' => 'glass',     // 玻璃边框效果 -> 玻璃搜索
                'default' => 'glass'    // 默认 -> 玻璃搜索
            );
            
            if (isset($style_map[$border_style])) {
                $search_style = $style_map[$border_style];
            }
        }

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        ?>
        <div class="widget-content">
            <form role="search" method="get" class="search-form search-style-<?php echo esc_attr($search_style); ?>" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="search-wrap">
                    <input type="search" class="search-input" placeholder="<?php echo esc_attr_x('搜索...', 'placeholder', 'text_domain'); ?>"
                           value="<?php echo get_search_query(); ?>" name="s" required>
                    <button type="submit" class="search-submit">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('搜索', 'text_domain');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}