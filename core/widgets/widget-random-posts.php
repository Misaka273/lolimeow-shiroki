<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================
// 🎲 随机文章小部件
class widget_random_posts extends WP_Widget {

	function __construct(){
		parent::__construct( 'widget_random_posts', '随机文章_shiroki', array( 'description' => __('随机文章展示', 'text_domain'),
			  'classname' => __('widget-random-posts', 'text_domain' )) );
	}
	function widget( $args, $instance ) {
		extract( $args );
		$title       = apply_filters('widget_name', $instance['title']);
		$limit       = isset($instance['limit']) ? $instance['limit'] : 6;
		$cat         = isset($instance['cat']) ? $instance['cat'] : 0;
		$show_thumb  = isset($instance['show_thumb']) ? $instance['show_thumb'] : true;
		$show_excerpt = isset($instance['show_excerpt']) ? $instance['show_excerpt'] : false;
		$excerpt_length = isset($instance['excerpt_length']) ? $instance['excerpt_length'] : 100;
		$show_date   = isset($instance['show_date']) ? $instance['show_date'] : true;
		$showstyle   = isset($instance['showstyle']) ? $instance['showstyle'] : 'widget-content';
		
		$style = ' class="'.$showstyle.'"';
		echo $before_widget;
		echo $before_title.$title.$after_title; 
		echo '<div'.$style.'>';
		$args = array(
			'order'            => 'DESC',
			'cat'              => $cat,
			'orderby'          => 'rand', // ⬅️ 固定为随机排序
			'showposts'        => $limit,
			'ignore_sticky_posts' => 1
		);
		query_posts($args);
		while (have_posts()) : the_post();  		
		echo '<article class="widget-post">';
		
		// 🖼️ 显示缩略图
		if ($show_thumb) {
			echo '<div class="info">
			       <a href="'. get_the_permalink() .'" '. boxmoe_article_new_window() .' class="thumb">
			         <span class="fullimage" style="background-image: url('.boxmoe_article_thumbnail_src().'?'.boxmoe_random_string(6).');"></span>
			       </a>
			       <div class="right">';
		}
		
		// 📝 显示标题
		echo '<h4 class="title">
			 <a '. boxmoe_article_new_window() .' href="'. get_the_permalink() .'">'. get_the_title() . get_the_subtitle() .'</a></h4>';
		
		// 📅 显示发布日期和阅读链接容器
		echo '<div class="post-meta">';
		if ($show_date) {
			echo '<time datetime="'.get_the_time('Y-m-d').'">'.get_the_time('Y-m-d').'</time>';
		}
		
		// 📄 显示摘要
		if ($show_excerpt) {
			echo '<div class="excerpt">'.wp_trim_words(get_the_excerpt(), $excerpt_length, '...').'</div>';
		}
		
		// 🔗 显示阅读链接
		echo '<a href="'. get_the_permalink() .'" '. boxmoe_article_new_window() .' class="read-more">阅读全文</a>';
		echo '</div>';
		
		if ($show_thumb) {
			echo '</div></div>';
		}
		
		echo '</article>';				
		endwhile; wp_reset_query();
		echo '</div>';
		echo $after_widget;
	}

	function form( $instance ) {
		$defaults = array( 
			'title' => __('随机文章', 'boxmoe-com'), 
			'limit' => 6, 
			'cat' => '', 
			'show_thumb' => true,
			'show_excerpt' => false,
			'excerpt_length' => 20,
			'show_date' => true,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
	?>
		<p>
			<label>
				<?php echo __('标题：', 'boxmoe-com') ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('分类限制：', 'boxmoe-com') ?>
				<a style="font-weight:bold;color:#f60;text-decoration:none;" href="javascript:;" title="<?php echo __('格式：1,2 &nbsp;表限制ID为1,2分类的文章&#13;格式：-1,-2 &nbsp;表排除分类ID为1,2的文章&#13;也可直接写1或者-1；注意逗号须是英文的', 'boxmoe-com') ?>">？</a>
				<input style="width:100%;" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" value="<?php echo $instance['cat']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('显示数目：', 'boxmoe-com') ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>" value="1" <?php checked($instance['show_thumb'], 1); ?> />
				<?php echo __('显示缩略图', 'boxmoe-com') ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" value="1" <?php checked($instance['show_date'], 1); ?> />
				<?php echo __('显示发布日期', 'boxmoe-com') ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" value="1" <?php checked($instance['show_excerpt'], 1); ?> />
				<?php echo __('显示文章摘要', 'boxmoe-com') ?>
			</label>
		</p>
		<p>
			<label>
				<?php echo __('摘要长度：', 'boxmoe-com') ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('excerpt_length'); ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" type="number" value="<?php echo $instance['excerpt_length']; ?>" size="24" />
			</label>
		</p>
	
	<?php
	}
}
