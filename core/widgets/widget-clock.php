<?php 
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======if (!defined('ABSPATH')) {echo'Look your sister';exit;}//=========================================

class widget_clock extends WP_Widget {

	function __construct(){
		parent::__construct( 'widget_clock', '时钟小部件_shiroki', array( 'classname' => 'widget_clock' ) );
	}

	function widget( $args, $instance ) {
						extract( $args );
						$title = apply_filters('widget_name', $instance['title']);
						$timezone = isset( $instance['timezone'] ) ? $instance['timezone'] : 'Asia/Shanghai';
						
						// 获取自定义字体设置
						$default_font = get_boxmoe('boxmoe_default_font');
						$font_family = $default_font && $default_font !== 'default' ? $default_font : 'boxmoe';
						
						// 时区名称映射，用于将英文时区转换为中文+英文格式
							$timezone_names = array(
								'Asia/Shanghai' => '北京时间 (Asia/Shanghai)',
								'Asia/Tokyo' => '东京时间 (Asia/Tokyo)',
								'Asia/Seoul' => '首尔时间 (Asia/Seoul)',
								'Asia/Hong_Kong' => '香港时间 (Asia/Hong_Kong)',
								'Europe/London' => '伦敦时间 (Europe/London)',
								'Europe/Paris' => '巴黎时间 (Europe/Paris)',
								'America/New_York' => '纽约时间 (America/New_York)',
								'America/Los_Angeles' => '洛杉矶时间 (America/Los_Angeles)',
								'UTC' => 'UTC时间 (UTC)'
							);
							
							// 设置时区名称作为默认标题，使用中文+英文格式
							if ( empty( $title ) ) {
								$title = isset($timezone_names[$timezone]) ? $timezone_names[$timezone] : $timezone;
							}
						
						echo $before_widget;
						echo '<H4 class="widget-title">'.$title.'</H4>';
						?>	  
						<style type="text/css">
							#clock-<?php echo $this->id; ?>,
							#clock-<?php echo $this->id; ?> .time,
							#clock-<?php echo $this->id; ?> .date {
								text-align: center !important;
								display: block !important;
								width: 100% !important;
							}
							
							#clock-<?php echo $this->id; ?>.clock-display {
								padding: 20px 10px !important;
								display: flex !important;
								flex-direction: column !important;
								align-items: center !important;
								justify-content: center !important;
								box-sizing: border-box !important;
								overflow: visible !important;
							}
							
							/* 基础时间样式 */
							#time-<?php echo $this->id; ?>.time {
								font-size: 2.5rem !important;
								font-weight: bold !important;
								margin-bottom: 10px !important;
								letter-spacing: 2px !important;
								font-family: "<?php echo $font_family; ?>", monospace !important;
								line-height: 1.2 !important;
								background: none !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: transparent !important;
								background-clip: text !important;
								position: relative !important;
								z-index: 1 !important;
								white-space: nowrap !important;
								overflow: visible !important;
								word-break: keep-all !important;
							}
							
							/* 亮色模式：蓝色渐变 */
							#time-<?php echo $this->id; ?>.time {
								background: linear-gradient(135deg, #3b82f6, #10b981) !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: transparent !important;
								background-clip: text !important;
							}
							
							/* 暗色模式：紫色渐变 */
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time {
								background: linear-gradient(135deg, #8b5cf6, #ec4899) !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: transparent !important;
								background-clip: text !important;
								color: transparent !important;
							}
							
							/* 确保暗色模式下没有额外的背景色 */
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time:before,
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time:after {
								display: none !important;
							}
							
							#date-<?php echo $this->id; ?>.date {
								font-size: 0.9rem !important;
								color: var(--bs-gray-600) !important;
								font-weight: 500 !important;
								margin: 0 !important;
								line-height: 1.2 !important;
								white-space: nowrap !important;
							}
			
			/* 响应式设计：适配不同屏幕尺寸 */
			@media (max-width: 1200px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: 2.2rem !important;
					letter-spacing: 1.5px !important;
				}
			}
			
			@media (max-width: 992px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: 2rem !important;
					letter-spacing: 1px !important;
				}
			}
			
			/* 一排三个布局的特殊处理 */
			@media (min-width: 768px) and (max-width: 1199px) {
				/* 针对三列布局的小部件 */
				.widget_clock {
					min-width: auto !important;
					max-width: 100% !important;
				}
				
				#time-<?php echo $this->id; ?>.time {
					font-size: 1.8rem !important;
					margin-bottom: 8px !important;
					letter-spacing: 1px !important;
				}
				
				#clock-<?php echo $this->id; ?>.clock-display {
					padding: 15px 8px !important;
				}
			}
			
			/* 确保小部件容器有足够宽度 */
			.widget_clock_inner {
				width: 100% !important;
				min-width: 0 !important;
				overflow: visible !important;
				box-sizing: border-box !important;
			}
			
			/* 防止时间被截断 */
			#time-<?php echo $this->id; ?>.time {
				overflow: visible !important;
				text-overflow: clip !important;
				clip: auto !important;
			}
		</style>
		<div class="widget_clock_inner">
			<div class="clock-display" id="clock-<?php echo $this->id; ?>">
				<div class="time" id="time-<?php echo $this->id; ?>"></div>
				<div class="date" id="date-<?php echo $this->id; ?>"></div>
			</div>
			<script type="text/javascript">
				// 🕒 时钟小部件时间更新逻辑
				(function() {
					var clockId = "<?php echo $this->id; ?>";
					var timezone = "<?php echo $timezone; ?>";
					
					// 获取目标元素
					var timeElement = document.getElementById('time-' + clockId);
					var dateElement = document.getElementById('date-' + clockId);
					
					// 确保元素存在
					if (!timeElement || !dateElement) {
						console.error('Clock elements not found for ID: ' + clockId);
						return;
					}
					
					function updateClock() {
						// 使用Intl.DateTimeFormat API处理时区，自动支持夏令时
						var now = new Date();
						
						// 时间格式化选项
						var timeOptions = {
							hour: '2-digit',
							minute: '2-digit',
							second: '2-digit',
							hour12: false,
							timeZone: timezone
						};
						
						// 直接使用API格式化日期和时间
						var formattedTime = new Intl.DateTimeFormat('zh-CN', timeOptions).format(now);
						
						// 分别获取年、月、日和星期
						var year = now.toLocaleString('zh-CN', { year: 'numeric', timeZone: timezone });
						var month = now.toLocaleString('zh-CN', { month: '2-digit', timeZone: timezone });
						var day = now.toLocaleString('zh-CN', { day: '2-digit', timeZone: timezone });
						var weekday = now.toLocaleString('zh-CN', { weekday: 'long', timeZone: timezone });
						
						// 组合成所需格式：YYYY-MM-DD 星期X
						var dateString = year + '-' + month + '-' + day + ' ' + weekday;
						
						// 更新显示
						timeElement.innerHTML = formattedTime;
						dateElement.innerHTML = dateString;
					}
					
					// 立即更新一次
					updateClock();
					
					// 每秒更新一次
					setInterval(updateClock, 1000);
				})();
			</script>
		</div>
		<?php
		echo $after_widget;
	}

	function form($instance) {
		// 设置默认值
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'timezone' => 'Asia/Shanghai'
		));
		
		// 使用简单的时区列表，避免复杂格式问题
		$timezones = array(
			'Asia/Shanghai' => '北京时间',
			'Asia/Tokyo' => '东京时间',
			'Asia/Seoul' => '首尔时间',
			'Asia/Hong_Kong' => '香港时间',
			'Europe/London' => '伦敦时间',
			'Europe/Paris' => '巴黎时间',
			'America/New_York' => '纽约时间',
			'America/Los_Angeles' => '洛杉矶时间',
			'UTC' => 'UTC时间'
		);
		
		// 生成标题输入框
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">标题：</label>';
		echo '<input type="text" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . esc_attr($instance['title']) . '" class="widefat" />';
		echo '<small>留空将显示时区名称</small>';
		echo '</p>';
		
		// 生成时区选择下拉框
		echo '<p>';
		echo '<label for="' . $this->get_field_id('timezone') . '">时区：</label>';
		echo '<select id="' . $this->get_field_id('timezone') . '" name="' . $this->get_field_name('timezone') . '" class="widefat">';
		
		foreach ( $timezones as $value => $label ) {
			$selected = selected( $instance['timezone'], $value, false );
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		
		echo '</select>';
		echo '</p>';
	}
	
	// 更新小部件设置
	function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['timezone'] = (!empty($new_instance['timezone'])) ? strip_tags($new_instance['timezone']) : 'Asia/Shanghai';
		return $instance;
	}
}