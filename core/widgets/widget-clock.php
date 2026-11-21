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
						$title = isset($instance['title']) ? apply_filters('widget_name', $instance['title']) : '';
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
							
							/* 基础时间样式 - 使用 clamp 实现自适应字体大小 */
							#time-<?php echo $this->id; ?>.time {
								font-size: clamp(1.2rem, 8vw, 2.5rem) !important;
								font-weight: bold !important;
								margin-bottom: 10px !important;
								letter-spacing: clamp(0.5px, 1vw, 2px) !important;
								font-family: "<?php echo $font_family; ?>", monospace !important;
								line-height: 1.2 !important;
								background: none !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: oklch(0 0 0 / 0) !important;
								background-clip: text !important;
								position: relative !important;
								z-index: 1 !important;
								white-space: nowrap !important;
								transform-origin: center top !important;
							}
							
							/* 亮色模式：蓝色渐变 */
							#time-<?php echo $this->id; ?>.time {
								background: linear-gradient(135deg in srgb, oklch(0.6231 0.188 259.81), oklch(0.6959 0.1491 162.48)) !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: oklch(0 0 0 / 0) !important;
								background-clip: text !important;
							}
							
							/* 暗色模式：紫色渐变 */
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time {
								background: linear-gradient(135deg in srgb, oklch(0.6056 0.2189 292.72), oklch(0.6559 0.2118 354.31)) !important;
								-webkit-background-clip: text !important;
								-webkit-text-fill-color: oklch(0 0 0 / 0) !important;
								background-clip: text !important;
								color: oklch(0 0 0 / 0) !important;
							}
							
							/* 确保暗色模式下没有额外的背景色 */
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time:before,
							[data-bs-theme="dark"] #time-<?php echo $this->id; ?>.time:after {
								display: none !important;
							}
							
							#date-<?php echo $this->id; ?>.date {
							font-size: clamp(0.7rem, 2.5vw, 0.9rem) !important;
							color: var(--bs-gray-600) !important;
							font-weight: 500 !important;
							margin: 0 !important;
							line-height: 1.2 !important;
							white-space: nowrap !important;
							max-width: 100% !important;
							transform-origin: center top !important;
						}
			
			/* 响应式设计：适配不同屏幕尺寸 */
			@media (max-width: 1200px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: clamp(1.1rem, 7vw, 2.2rem) !important;
					letter-spacing: clamp(0.5px, 0.8vw, 1.5px) !important;
				}
			}
			
			@media (max-width: 992px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: clamp(1rem, 6vw, 2rem) !important;
					letter-spacing: clamp(0.3px, 0.6vw, 1px) !important;
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
					font-size: clamp(0.9rem, 5vw, 1.8rem) !important;
					margin-bottom: 8px !important;
					letter-spacing: clamp(0.3px, 0.5vw, 1px) !important;
				}
				
				#clock-<?php echo $this->id; ?>.clock-display {
					padding: 15px 8px !important;
				}
			}
			
			/* 小屏幕设备适配 */
			@media (max-width: 576px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: clamp(0.85rem, 5.5vw, 1.5rem) !important;
					letter-spacing: 0.3px !important;
				}
				
				#date-<?php echo $this->id; ?>.date {
					font-size: clamp(0.7rem, 3vw, 0.85rem) !important;
				}
				
				#clock-<?php echo $this->id; ?>.clock-display {
					padding: 12px 6px !important;
				}
			}
			
			/* 超小屏幕设备 */
			@media (max-width: 360px) {
				#time-<?php echo $this->id; ?>.time {
					font-size: clamp(0.75rem, 5vw, 1.2rem) !important;
					letter-spacing: 0.2px !important;
				}
			}
			
			/* 确保小部件容器有足够宽度 */
			.widget_clock_inner {
				width: 100% !important;
				min-width: 0 !important;
				max-width: 100% !important;
				overflow: hidden !important;
				box-sizing: border-box !important;
			}
			
			/* 时钟显示容器自适应 */
			#clock-<?php echo $this->id; ?>.clock-display {
				width: 100% !important;
				max-width: 100% !important;
				box-sizing: border-box !important;
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
					
					// 自适应缩放：文本始终完整显示在容器内，不会出现省略号
					var _lastScaleTime = 1;
					var _lastScaleDate = 1;

					function scaleSingleElement(el, containerW, lastScale) {
						el.style.transform = 'scale(1)';
						var textW = el.scrollWidth;
						if (textW <= containerW) {
							if (lastScale !== 1) {
								el.style.transform = 'scale(1)';
								return 1;
							}
							return lastScale;
						}
						var scale = Math.floor((containerW / textW) * 98) / 100;
						if (scale < 0.3) scale = 0.3;
						if (scale !== lastScale) {
							el.style.transform = 'scale(' + scale + ')';
						}
						return scale;
					}

					function scaleTextToFit() {
						var container = document.getElementById('clock-' + clockId);
						if (!container) return;
						var containerW = container.clientWidth;
						if (!containerW) return;
						if (timeElement) {
							_lastScaleTime = scaleSingleElement(timeElement, containerW, _lastScaleTime);
						}
						if (dateElement) {
							_lastScaleDate = scaleSingleElement(dateElement, containerW, _lastScaleDate);
						}
					}

					function updateClock() {
						var now = new Date();

						var timeOptions = {
							hour: '2-digit',
							minute: '2-digit',
							second: '2-digit',
							hour12: false,
							timeZone: timezone
						};

						var formattedTime = new Intl.DateTimeFormat('zh-CN', timeOptions).format(now);

						var dateFormatter = new Intl.DateTimeFormat('zh-CN', {
							year: 'numeric',
							month: 'numeric',
							day: 'numeric',
							timeZone: timezone
						});
						var parts = dateFormatter.formatToParts(now);
						var year = '', month = '', day = '';
						parts.forEach(function(part) {
							if (part.type === 'year') year = part.value;
							else if (part.type === 'month') month = part.value;
							else if (part.type === 'day') day = part.value;
						});

						var weekday = now.toLocaleString('zh-CN', { weekday: 'long', timeZone: timezone });

						month = month < 10 ? '0' + month : month;
						day = day < 10 ? '0' + day : day;

						var dateString = year + '年' + month + '月' + day + '日 ' + weekday;

						timeElement.innerHTML = formattedTime;
						dateElement.innerHTML = dateString;

						scaleTextToFit();
					}

					// 立即更新一次
					updateClock();

					// 每秒更新一次
					setInterval(updateClock, 1000);

					// 容器尺寸变化时重新计算缩放（如侧边栏折叠/展开、响应式断点切换）
					if (typeof ResizeObserver !== 'undefined') {
						new ResizeObserver(scaleTextToFit).observe(document.getElementById('clock-' + clockId));
					}
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