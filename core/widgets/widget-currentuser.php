<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================

// 🥳 当前用户信息小工具
class widget_currentuser extends WP_Widget {

	function __construct(){
		parent::__construct( 
			'widget_currentuser', 
			'Boxmoe_当前用户信息', 
			array( 
				'description' => __('显示当前登录用户的详细信息', 'boxmoe-com'),
				'classname'   => __('widget-currentuser', 'boxmoe-com')
			) 
		);
	}
	
	// 小工具前端显示
	public function widget( $args, $instance ) {
		// 检查导航会员注册链接开关是否开启
		if (!get_boxmoe('boxmoe_sign_in_link_switch')) {
			return;
		}
		// 检查用户是否登录
		if (!is_user_logged_in()) {
			return;
		}
		
		// 获取用户ID设置
		$avatarid = isset($instance['avatarid']) ? $instance['avatarid'] : '';
		
		// 根据用户ID获取用户信息
		if (!empty($avatarid)) {
			// 使用指定的用户ID
			$user = get_user_by('ID', $avatarid);
			if (!$user) {
				// 如果用户不存在，使用当前登录用户
				$user = wp_get_current_user();
			}
		} else {
			// 使用当前登录用户
			$user = wp_get_current_user();
		}
		
		extract($args);
		$title = apply_filters('widget_name', isset($instance['title']) ? $instance['title'] : __('当前用户信息', 'boxmoe-com'));
		
		// 获取用户填写的信息，优先级：自定义填写 > 用户资料
		$qq = !empty($instance['qq']) ? $instance['qq'] : '';
		$email = !empty($instance['email']) ? $instance['email'] : $user->user_email;
		$github = !empty($instance['github']) ? $instance['github'] : '';
		$gitee = !empty($instance['gitee']) ? $instance['gitee'] : '';
		$wechat = !empty($instance['wechat']) ? $instance['wechat'] : '';
		// 确保昵称有值，优先使用自定义填写，否则使用用户资料中的显示名，再否则使用用户名
		$nickname = isset($instance['nickname']) && !empty($instance['nickname']) ? $instance['nickname'] : (isset($user->display_name) && !empty($user->display_name) ? $user->display_name : $user->user_login);
		// 获取个人介绍，确保能正确获取到用户资料中的描述
		$bio = isset($instance['bio']) && $instance['bio'] !== '' ? $instance['bio'] : (isset($user->description) ? $user->description : '');
		$avatar_url = isset($instance['avatar_url']) ? $instance['avatar_url'] : '';
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div class="widget-content">';	
		echo '<div class="widget-profile">';
		echo '<div class="profile-avatar">';
		echo '<img src="'.boxmoe_lazy_load_images().'"  class="lazy" data-src="';
		// 优先级：自定义头像链接 > 主题自定义头像函数获取的头像
		if (!empty($avatar_url)) {
			echo esc_url($avatar_url);
		} else {
			// 使用主题自定义的头像获取函数，支持QQ头像等
			echo function_exists('boxmoe_get_avatar_url') ? boxmoe_get_avatar_url($user->ID, 100) : get_avatar_url($user->ID, array('size' => 100));
		}
		echo '" alt="'.esc_html($nickname).'">';
		echo '</div>';
		echo '<h3 class="profile-name">'. esc_html($nickname) .'</h3>';
		
		// 显示个人介绍（如果有内容，包括空格和短文本）
		if (isset($bio) && $bio !== '') {
			echo '<p class="profile-desc">'. esc_html($bio) .'</p>';
		}
		
		// 显示社交媒体图标
		echo '<div class="profile-social">';
		
		// 显示QQ图标（如果填写了QQ号）
		if (!empty($qq)) {
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($qq).'" title="点击复制QQ号"><i class="fa fa-qq"></i></a>';
		}
		
		// 显示邮箱图标（如果填写了邮箱）
		if (!empty($email)) {
			// 统一使用邮箱图标，不再根据域名显示不同图标
			$email_icon = 'fa-envelope';
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($email).'" title="点击复制邮箱"><i class="fa '.esc_attr($email_icon).'"></i></a>';
		}
		
		// 显示GitHub图标（如果填写了GitHub链接）
		if (!empty($github)) {
			echo '<a href="'.esc_url($github).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问GitHub"><i class="fa fa-github"></i></a>';
		}
		
		// 显示Gitee图标（如果填写了Gitee链接）
		if (!empty($gitee)) {
			echo '<a href="'.esc_url($gitee).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问Gitee"><img src="https://gitee.com/static/images/gitee-logos/logo_gitee_g_red.svg" alt="Gitee" style="width: 16px; height: 16px;"></a>';
		}
		
		// 显示微信图标（如果填写了微信号）
		if (!empty($wechat)) {
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($wechat).'" title="点击复制微信"><i class="fa fa-weixin"></i></a>';
		}
		
		echo '</div>';
		
		// 显示统计数据
		echo '<div class="profile-stats">';
		
		// 显示文章数量
		if (!empty($instance['show_posts_count'])) {
			$user_posts_count = count_user_posts($user->ID);
			echo '<div class="stat-item">
					<div class="stat-value">'. $this->format_number($user_posts_count) .'</div>
					<div class="stat-label">文章</div>
				</div>';
		}
		
		// 显示评论数量
		if (!empty($instance['show_comments_count'])) {
			$user_comments_count = get_comments(array(
				'status'     => 'approve',
				'user_id'    => $user->ID,
				'count'      => true
			));
			echo '<div class="stat-item">
					<div class="stat-value">'. $this->format_number($user_comments_count) .'</div>
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
		
		// 添加CSS动画样式和邮箱显示样式
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
		echo '    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);';
		echo '    transform: rotate(45deg);';
		echo '    transition: left 0.5s ease, opacity 0.3s ease;';
		echo '    opacity: 0;';
		echo '    pointer-events: none;';
		echo '}';
		echo '.social-link:hover::after {';
		echo '    left: 150%;';
		echo '    opacity: 1;';
		echo '}';
		echo '/* 邮箱文本显示样式 */';
		echo '.profile-email {';
		echo '    margin: 15px 0;';
		echo '    padding: 10px 15px;';
		echo '    background-color: #ffffff;';
		echo '    border-radius: 8px;';
		echo '    font-size: 14px;';
		echo '    text-align: center;';
		echo '    color: #333333;';
		echo '    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);';
		echo '    font-weight: 500;';
		echo '    z-index: 10;';
		echo '    display: block !important;';
		echo '}';
		echo '.profile-email span {';
		echo '    word-break: break-all;';
		echo '    font-weight: 500;';
		echo '    color: #333333 !important;';
		echo '}';
		echo '</style>';
		

	}
	
	// 后台表单
	public function form( $instance ) {
		$defaults = array(
			'title' => __('当前用户信息', 'boxmoe-com'),
			'avatarid' => '',
			'avatar_url' => '',
			'nickname' => '',
			'bio' => '',
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
				<?php echo __('用户ID：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('avatarid'); ?>" 
					name="<?php echo $this->get_field_name('avatarid'); ?>" type="number" 
					value="<?php echo esc_attr($instance['avatarid']); ?>" placeholder="留空则使用当前登录用户" />
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
				<?php echo __('昵称：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('nickname'); ?>" 
					name="<?php echo $this->get_field_name('nickname'); ?>" type="text" 
					value="<?php echo esc_attr($instance['nickname']); ?>" placeholder="留空则使用用户资料中的昵称" />
			</label>
		</p>
		<p>
			<label>
				<?php echo __('个人简介：', 'boxmoe-com') ?>
				<textarea class="widefat" id="<?php echo $this->get_field_id('bio'); ?>" 
					name="<?php echo $this->get_field_name('bio'); ?>" rows="3" placeholder="留空则使用用户资料中的个人简介"><?php echo esc_textarea($instance['bio']); ?></textarea>
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
					value="<?php echo esc_attr($instance['gitee']); ?>" placeholder="请输入Gitee链接" />
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
		$instance['title'] = sanitize_text_field( isset($new_instance['title']) ? $new_instance['title'] : '' );
		// 当用户ID为空时保持为空字符串，而不是转换为0
		$instance['avatarid'] = !empty($new_instance['avatarid']) ? absint( $new_instance['avatarid'] ) : '';
		$instance['avatar_url'] = esc_url_raw( $new_instance['avatar_url'] );
		$instance['nickname'] = sanitize_text_field( $new_instance['nickname'] );
		$instance['bio'] = sanitize_textarea_field( $new_instance['bio'] );
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
	
	// 获取管理员评论数量
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
	
	// 格式化数字
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