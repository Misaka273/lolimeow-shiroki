<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================

class widget_userinfo extends WP_Widget {

	function __construct(){
		parent::__construct( 
			'widget_userinfo', 
			'Boxmoe_用户信息', 
			array( 
				'description' => __('用户信息侧栏小工具', 'boxmoe-com'),
				'classname'   => __('widget-userinfo', 'boxmoe-com')
			) 
		);
	}
	
	// 小工具前端显示
	public function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_name', $instance['title']);
		
		// 获取用户ID设置
		$avatarid = isset($instance['avatarid']) ? $instance['avatarid'] : '';
		
		// 获取自定义头像链接
		$custom_avatar_url = isset($instance['avatar_url']) ? $instance['avatar_url'] : '';
		
		// 根据用户ID获取用户信息
		$use_user_id = 0;
		if (!empty($avatarid)) {
			// 使用指定的用户ID
			$user = get_user_by('ID', $avatarid);
			if ($user) {
				// 如果用户存在，使用用户资料作为默认值
				$default_nickname = $user->display_name;
				$default_bio = $user->description;
				$default_email = $user->user_email;
				$user_avatar_url = get_avatar_url($user->ID, array('size' => 100));
				$use_user_id = $user->ID;
			} else {
				// 如果用户不存在，使用默认值
				$default_nickname = '昵称在这里';
				$default_bio = '个人简介文字';
				$default_email = '';
				$user_avatar_url = get_avatar_url(0, array('size' => 100));
			}
		} else {
			// 如果没有设置用户ID，使用默认值
			$default_nickname = '昵称在这里';
			$default_bio = '个人简介文字';
			$default_email = '';
			$user_avatar_url = get_avatar_url(0, array('size' => 100));
		}
		
		// 头像优先级：自定义头像链接 > 主题自定义头像函数获取的头像
		$avatar_url = !empty($custom_avatar_url) ? $custom_avatar_url : (function_exists('boxmoe_get_avatar_url') ? boxmoe_get_avatar_url($use_user_id, 100) : $user_avatar_url);
		
		// 获取用户填写的信息，优先级：自定义填写 > 用户资料 > 默认值
		$nickname = isset($instance['nickname']) && !empty($instance['nickname']) ? $instance['nickname'] : $default_nickname;
		$bio = isset($instance['bio']) && !empty($instance['bio']) ? $instance['bio'] : $default_bio;
		$qq = isset($instance['qq']) ? $instance['qq'] : '';
		$email = isset($instance['email']) && !empty($instance['email']) ? $instance['email'] : $default_email;
		$github = isset($instance['github']) ? $instance['github'] : '';
		$gitee = isset($instance['gitee']) ? $instance['gitee'] : '';
		$wechat = isset($instance['wechat']) ? $instance['wechat'] : '';
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div class="widget-content">';	
		echo '
		<div class="widget-profile">
			<div class="profile-avatar">
				<img src="'.boxmoe_lazy_load_images().'"  class="lazy" data-src="'.esc_url($avatar_url).'" alt="avatar" onerror="this.src=\''.boxmoe_default_avatar_url().'\'">
			</div>
			<h3 class="profile-name">'. esc_html($nickname) .'</h3>
			<p class="profile-desc">'. esc_html($bio) .'</p>
			<div class="profile-social">';
			// 显示QQ图标（如果填写了QQ号）
			if(!empty($qq)) {
				echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($qq).'" title="点击复制QQ号"><i class="fa fa-qq"></i></a>';
			}
			// 显示邮箱图标（如果填写了邮箱）
			if(!empty($email)) {
				// 统一使用邮箱图标，不再根据域名显示不同图标
				$email_icon = 'fa-envelope';
				echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($email).'" title="点击复制邮箱"><i class="fa '.esc_attr($email_icon).'"></i></a>';
			}
			// 显示GitHub图标（如果填写了GitHub链接）
			if(!empty($github)) {
				echo '<a href="'.esc_url($github).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问GitHub"><i class="fa fa-github"></i></a>';
			}
			// 显示Gitee图标（如果填写了Gitee链接）
			if(!empty($gitee)) {
				echo '<a href="'.esc_url($gitee).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问Gitee"><img src="https://gitee.com/static/images/gitee-logos/logo_gitee_g_red.svg" alt="Gitee" style="width: 16px; height: 16px;"></a>';
			}
			// 显示微信图标（如果填写了微信号）
			if(!empty($wechat)) {
				echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($wechat).'" title="点击复制微信"><i class="fa fa-weixin"></i></a>';
			}
			echo '</div>';
			
			// 添加CSS动画样式
			echo '<style>';
			echo '.social-link {';
			echo '    position: relative;';
			echo '    transition: transform 0.3s ease;';
			echo '    overflow: hidden;';
			echo '}';
			echo '.social-link:hover {';
			echo '    transform: translateY(-3px);';
			echo '}';
			echo '.social-link::after {';
			echo '    content: "";';
			echo '    position: absolute;';
			echo '    top: -50%;';
			echo '    left: -50%;';
			echo '    width: 200%;';
			echo '    height: 200%;';
			echo '    background: linear-gradient(45deg in srgb, oklch(0 0 0 / 0), oklch(1 0 0 / 0.3), oklch(0 0 0 / 0));';
			echo '    transform: rotate(45deg);';
			echo '    transition: left 0.5s ease, opacity 0.3s ease;';
			echo '    opacity: 0;';
			echo '    pointer-events: none;';
			echo '}';
			echo '.social-link:hover::after {';
			echo '    left: 150%;';
			echo '    opacity: 1;';
			echo '}';
			echo '</style>';
			
			// 显示统计数据
			echo '<div class="profile-stats">';
			
			// 显示文章数量
			if (!empty($instance['show_posts_count'])) {
				$posts_count = $use_user_id > 0 ? count_user_posts($use_user_id) : wp_count_posts('post')->publish;
				echo '<div class="stat-item">
						<div class="stat-value">'. $this->format_number($posts_count) .'</div>
						<div class="stat-label">文章</div>
					</div>';
			}
			
			// 显示评论数量
			if (!empty($instance['show_comments_count'])) {
				$comments_count = $use_user_id > 0 ? get_comments(array(
					'status'     => 'approve',
					'user_id'    => $use_user_id,
					'count'      => true
				)) : $this->get_admin_comments_count();
				echo '<div class="stat-item">
						<div class="stat-value">'. $this->format_number($comments_count) .'</div>
						<div class="stat-label">评论</div>
					</div>';
			}
			
			// 显示用户数量
			if (!empty($instance['show_users_count'])) {
				echo '<div class="stat-item">
						<div class="stat-value">'. $this->format_number(get_user_count()) .'</div>
						<div class="stat-label">用户</div>
					</div>';
			}
			echo '</div>';
			echo '</div>';
		echo '</div>';
		echo $after_widget;
		

	}

	// 后台表单
	public function form( $instance ) {
		$defaults = array(
			'title' => __('个人信息', 'boxmoe-com'),
			'nickname' => '',
			'bio' => '',
			'avatarid' => '',
			'avatar_url' => '',
			'qq' => '',
			'email' => '',
			'github' => '',
			'gitee' => '',
			'wechat' => '',
			'show_posts_count' => true,
			'show_comments_count' => true,
			'show_users_count' => true
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label>
				<?php echo __('标题：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
					name="<?php echo $this->get_field_name('title'); ?>" type="text" 
					value="<?php echo esc_attr($instance['title']); ?>" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('昵称：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('nickname'); ?>" 
					name="<?php echo $this->get_field_name('nickname'); ?>" type="text" 
					value="<?php echo esc_attr($instance['nickname']); ?>" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('个人简介：', 'boxmoe-com') ?>
				<textarea class="widefat" id="<?php echo $this->get_field_id('bio'); ?>" 
					name="<?php echo $this->get_field_name('bio'); ?>"><?php echo esc_textarea($instance['bio']); ?></textarea>
			</label>
		</p>
		<p>
			<label>
				<?php echo __('用户ID：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('avatarid'); ?>" 
					name="<?php echo $this->get_field_name('avatarid'); ?>" type="number" 
					value="<?php echo esc_attr($instance['avatarid']); ?>" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('头像链接：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('avatar_url'); ?>" 
					name="<?php echo $this->get_field_name('avatar_url'); ?>" type="url" 
					value="<?php echo esc_attr($instance['avatar_url']); ?>" placeholder="留空则使用用户默认头像" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('QQ号：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('qq'); ?>" 
					name="<?php echo $this->get_field_name('qq'); ?>" type="text" 
					value="<?php echo esc_attr($instance['qq']); ?>" placeholder="请输入QQ号" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('邮箱：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('email'); ?>" 
					name="<?php echo $this->get_field_name('email'); ?>" type="email" 
					value="<?php echo esc_attr($instance['email']); ?>" placeholder="请输入邮箱地址" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('GitHub链接：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('github'); ?>" 
					name="<?php echo $this->get_field_name('github'); ?>" type="url" 
					value="<?php echo esc_attr($instance['github']); ?>" placeholder="请输入GitHub链接" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('Gitee链接：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('gitee'); ?>" 
					name="<?php echo $this->get_field_name('gitee'); ?>" type="url" 
					value="<?php echo esc_attr($instance['gitee']); ?>" placeholder="<?php echo __('请输入Gitee链接', 'boxmoe-com'); ?>" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('微信号：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('wechat'); ?>" 
					name="<?php echo $this->get_field_name('wechat'); ?>" type="text" 
					value="<?php echo esc_attr($instance['wechat']); ?>" placeholder="请输入微信号" />
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_posts_count'); ?>" 
					name="<?php echo $this->get_field_name('show_posts_count'); ?>" 
					<?php checked($instance['show_posts_count']); ?> />
				<?php echo __('显示文章数量', 'boxmoe-com') ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_comments_count'); ?>" 
					name="<?php echo $this->get_field_name('show_comments_count'); ?>" 
					<?php checked($instance['show_comments_count']); ?> />
				<?php echo __('显示评论数量', 'boxmoe-com') ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_users_count'); ?>" 
					name="<?php echo $this->get_field_name('show_users_count'); ?>" 
					<?php checked($instance['show_users_count']); ?> />
				<?php echo __('显示用户数量', 'boxmoe-com') ?>
			</label>
		</p>

		<?php
	}
	
	// 更新小工具设置
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['nickname'] = sanitize_text_field( $new_instance['nickname'] );
		$instance['bio'] = sanitize_textarea_field( $new_instance['bio'] );
		// 当用户ID为空时保持为空字符串，而不是转换为0
		$instance['avatarid'] = !empty($new_instance['avatarid']) ? absint( $new_instance['avatarid'] ) : '';
		$instance['avatar_url'] = esc_url_raw( $new_instance['avatar_url'] );
		$instance['qq'] = sanitize_text_field( $new_instance['qq'] );
		$instance['email'] = sanitize_email( $new_instance['email'] );
		$instance['github'] = esc_url_raw( $new_instance['github'] );
		$instance['gitee'] = esc_url_raw( $new_instance['gitee'] );
		$instance['wechat'] = sanitize_text_field( $new_instance['wechat'] );
		$instance['show_posts_count'] = !empty($new_instance['show_posts_count']);
		$instance['show_comments_count'] = !empty($new_instance['show_comments_count']);
		$instance['show_users_count'] = !empty($new_instance['show_users_count']);
		return $instance;
	}

	private function get_admin_comments_count() {
		$admin_users = get_users(array(
			'role__in' => array('administrator'),
			'fields'   => array('ID')
		));
		
		return get_comments(array(
			'status'     => 'approve',
			'author__in' => wp_list_pluck($admin_users, 'ID'),
			'count'      => true
		));
	}

	private function format_number($num) {
		if ($num >= 1000000) {
			$formatted = number_format($num / 1000000, 1);
			return rtrim(rtrim($formatted, '0'), '.') . 'M';
		} elseif ($num >= 1000) {
			$formatted = number_format($num / 1000, 1);
			return rtrim(rtrim($formatted, '0'), '.') . 'K';
		}
		return $num;
	}
}



