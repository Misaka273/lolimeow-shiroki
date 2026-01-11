<?php
/**
 * @package   Options_Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2014 WP Theming
 */

class Options_Framework_Interface {

	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function optionsframework_tabs() {
		$counter = 0;
		$options = & Options_Framework::_optionsframework_options();
		$menu = '<ul>'; 
		foreach ( $options as $value ) {
			if ( isset( $value['type'] ) && $value['type'] == "heading" ) {
				$counter++;
				$class = '';
				$class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
				$class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';			
				$active_class = ($counter === 1) ? ' active' : '';
				$default_icon = 'dashicons-admin-generic';
				$icon = isset($value['icon']) ? $value['icon'] : $default_icon;
				
				$menu .= '<li class="tab-item' . $active_class . '">';
				$menu .= '<a id="options-group-' . $counter . '-tab" class="' . $class . '" 
							title="' . esc_attr( $value['name'] ) . '" 
							href="' . esc_attr( '#options-group-' . $counter ) . '">
							<span class="dashicons ' . esc_attr($icon) . '"></span>' 
							. esc_html( $value['name'] ) . '</a>';
				$menu .= '</li>';
			}
		}
		$menu .= '</ul>';
		return $menu;
	}


	/**
	 * Generates the options fields that are used in the form.
	 */
	static function optionsframework_fields() {

		global $allowedtags;
		// 允许在描述中使用span标签，用于样式化
		$allowedtags['span'] = array(
			'class' => array(),
			'style' => array()
		);

		$options_framework = new Options_Framework;
		$option_name = $options_framework->get_option_name();
		$settings = get_option( $option_name );
		$options = & Options_Framework::_optionsframework_options();

		$counter = 0;
		$menu = '';

		$group_opened = false;
		$group_section_id = '';

		foreach ( $options as $value ) {

			$val = '';
			$select_value = '';
			$output = '';

			// Wrap all options
			if ( ( !isset( $value['type'] ) || $value['type'] != "heading" ) && ( !isset( $value['type'] ) || $value['type'] != "info" ) ) {

				// Keep all ids lowercase with no spaces
				if ( isset( $value['id'] ) ) {
					$value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']) );
				}

				$id = isset( $value['id'] ) ? 'section-' . $value['id'] : '';

				$class = 'section';
				if ( isset( $value['type'] ) ) {
					$class .= ' section-' . $value['type'];
				}
				if ( isset( $value['class'] ) ) {
					$class .= ' ' . $value['class'];
				}

				if (isset($value['group']) && $value['group'] == 'start' && isset($value['id'])) {
					$group_opened = true;
					$group_section_id = 'section-' . $value['id'];
					$output .= '<div id="' . esc_attr($group_section_id) .'" class="' . esc_attr( $class ) . ' mini col">' . "\n";
					
					if (isset($value['group_title'])) {
						$output .= '<div class="boxmoe_tab_header"><span class="dashicons dashicons-info-outline"></span> ' . esc_html($value['group_title']) . '</div>' . "\n";
					}
					
					// 只在分组开始时创建一次 boxmoe_group_opened div
					$output .= '<div class="boxmoe_group_opened">' . "\n";
				}
				
				if (!$group_opened && !empty($id)) {
					$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . ' col">' . "\n";
				}

                if ( isset( $value['name'] ) ) {
                    $heading = '<h4 class="heading"><span class="dashicons dashicons-shortcode"></span> ' . esc_html( $value['name'] );
                    if ( isset($value['type']) && $value['type'] === 'fonts_table' ) {
                        $heading .= ' <button type="button" id="boxmoe-fonts-add-btn" class="btn-pill btn-blue fonts-add-btn">新增</button>';
                    }
                    $heading .= '</h4>' . "\n";
                    $output .= $heading;
                }
				if ( !isset( $value['type'] ) || $value['type'] != 'editor' ) {
					$id_attr = isset($value['id']) ? 'id="'.$value['id'].'-controls"' : '';
					$output .= '<div class="option">' . "\n" . '<div '.$id_attr.' class="controls">' . "\n";
				}
				else {
					$output .= '<div class="option">' . "\n" . '<div>' . "\n";
				}
			}

			// Set default value to $val
			if ( isset( $value['std'] ) ) {
				$val = $value['std'];
			}

			// If the option is already saved, override $val
			if ( ( !isset( $value['type'] ) || $value['type'] != 'heading' ) && ( !isset( $value['type'] ) || $value['type'] != 'info') ) {
				if ( isset( $value['id'] ) && isset( $settings[($value['id'])]) ) {
					$val = $settings[($value['id'])];
					// Striping slashes of non-array options
					if ( !is_array($val) ) {
						$val = stripslashes( $val );
					}
				}
			}

			// If there is a description save it for labels
			$explain_value = '';
			if ( isset( $value['desc'] ) ) {
				$explain_value = $value['desc'];
			}

			// Set the placeholder if one exists
			$placeholder = '';
			if ( isset( $value['placeholder'] ) ) {
				$placeholder = ' placeholder="' . esc_attr( $value['placeholder'] ) . '"';
			}

			if ( isset( $value['type'] ) && has_filter( 'optionsframework_' . $value['type'] ) ) {
				$output .= apply_filters( 'optionsframework_' . $value['type'], $option_name, $value, $val );
			}


			switch ( isset($value['type']) ? $value['type'] : '' ) {

			// Basic text input
			case 'text':
				$class = isset($value['class']) ? ' ' . $value['class'] : '';
				if ( isset($value['id']) && $value['id'] == 'boxmoe_article_card_kanban_image' ) {
					$default_image = get_template_directory_uri() . '/assets/images/post-list.png';
					if ( isset($value['id']) ) {
						$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input' . $class . '" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="text" value="' . esc_attr( $val ) . '"' . $placeholder . ' />';
					}
					$output .= '<div style="margin-top: 10px; display: flex; gap: 10px;">';
					if ( isset($value['id']) ) {
						$output .= '<input id="upload-' . esc_attr( $value['id'] ) . '" class="upload-button button" type="button" value="' . __( '替换', 'textdomain' ) . '" />';
						$output .= '<input id="confirm-' . esc_attr( $value['id'] ) . '" class="confirm-button button button-primary" type="button" value="' . __( '确认', 'textdomain' ) . '" />';
						$output .= '<input id="reset-' . esc_attr( $value['id'] ) . '" class="reset-button button" type="button" value="' . __( '重置', 'textdomain' ) . '" data-default="' . esc_attr( $default_image ) . '" />';
					}
					$output .= '</div>';
					// 将预览图的HTML存储起来，稍后在描述之后显示
					if ( isset($value['id']) ) {
						$preview_html = '<div class="screenshot" id="' . esc_attr( $value['id'] ) . '-image" style="margin-top: 10px;">';
					} else {
						$preview_html = '<div class="screenshot" style="margin-top: 10px;">';
					}
					if ( $val ) {
						$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico|svg*)/i', $val );
						if ( $image ) {
							$preview_html .= '<img src="' . esc_url( $val ) . '" alt="" style="max-width: 162px; max-height: 75px; object-fit: contain; background: #f5f5f5;" />';
						}
					}
					$preview_html .= '</div>';
					// 存储预览HTML以便稍后使用
					$GLOBALS['boxmoe_preview_html'] = $preview_html;
				} else {
					if ( isset($value['id']) ) {
						$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input' . $class . '" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="text" value="' . esc_attr( $val ) . '"' . $placeholder . ' />';
					}
				}
				break;

			// Password input
			case 'password':
				if ( isset($value['id']) ) {
					$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="password" value="' . esc_attr( $val ) . '" />';
				}
				break;

			// Textarea
			case 'textarea':
				$rows = '8';

				if ( isset( $value['settings']['rows'] ) ) {
					$custom_rows = $value['settings']['rows'];
					if ( is_numeric( $custom_rows ) ) {
						$rows = $custom_rows;
					}
				}

				$val = stripslashes( $val );
				if ( isset($value['id']) ) {
					$output .= '<textarea id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" rows="' . $rows . '"' . $placeholder . '>' . esc_textarea( $val ) . '</textarea>';
				}
				break;

			// Select Box
			case 'select':
				if ( isset($value['id']) && isset($value['options']) ) {
					$output .= '<select class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '">';

					foreach ($value['options'] as $key => $option ) {
						$output .= '<option'. selected( $val, $key, false ) .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
					}
					$output .= '</select>';
				}
				break;


			// Radio Box
			case "radio":
				if ( isset($value['id']) && isset($value['options']) ) {
					$name = $option_name .'['. $value['id'] .']';
					foreach ($value['options'] as $key => $option) {
						$id = $option_name . '-' . $value['id'] .'-'. $key;
						$output .= '<input class="of-input of-radio" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="'. esc_attr( $key ) . '" '. checked( $val, $key, false) .' /><label for="' . esc_attr( $id ) . '">' . esc_html( $option ) . '</label>';
					}
				}
				break;

			// Image Selectors
			case "images":
				if ( isset($value['id']) && isset($value['options']) ) {
					$name = $option_name .'['. $value['id'] .']';
					foreach ( $value['options'] as $key => $option ) {
						$selected = '';
						if ( $val != '' && ($val == $key) ) {
							$selected = ' of-radio-img-selected';
						}
						$output .= '<input type="radio" id="' . esc_attr( $value['id'] .'_'. $key) . '" class="of-radio-img-radio" value="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" '. checked( $val, $key, false ) .' />';
						$output .= '<div class="of-radio-img-label">' . esc_html( $key ) . '</div>';
						$output .= '<img src="' . esc_url( $option ) . '" alt="' . $option .'" class="of-radio-img-img' . $selected .'" onclick="document.getElementById(\''. esc_attr($value['id'] .'_'. $key) .'\').checked=true;" />';
					}
				}
				break;

			// Checkbox
			case "checkbox":
				if ( isset($value['id']) ) {
					$output .= '<input type="hidden" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" value="0" />';
					$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" value="1" '. checked( $val, 1, false) .' />';
					$output .= '<label class="explain" for="' . esc_attr( $value['id'] ) . '">' . wp_kses( $explain_value, $allowedtags) . '</label>';
				}
				break;

			// Multicheck
			case "multicheck":
				if ( isset($value['id']) && isset($value['options']) ) {
					foreach ($value['options'] as $key => $option) {
						$checked = '';
						$label = $option;
						$option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

						$id = $option_name . '-' . $value['id'] . '-'. $option;
						$name = $option_name . '[' . $value['id'] . '][' . $option .']';

						if ( isset($val[$option]) ) {
							$checked = checked($val[$option], 1, false);
						}

						$output .= '<input id="' . esc_attr( $id ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $name ) . '" ' . $checked . ' /><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
					}
				}
				break;

			// Color picker
			case "color":
				if ( isset($value['id']) ) {
					$default_color = '';
					if ( isset($value['std']) ) {
						if ( $val !=  $value['std'] )
							$default_color = ' data-default-color="' .$value['std'] . '" ';
					}
					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '" class="of-color"  type="text" value="' . esc_attr( $val ) . '"' . $default_color .' />';
				}

				break;

			// Uploader
			case "upload":
				if ( isset($value['id']) ) {
					$output .= Options_Framework_Media_Uploader::optionsframework_uploader( $value['id'], $val, null );
				}

				break;

			// Typography
			case 'typography':

				unset( $font_size, $font_style, $font_face, $font_color );

				$typography_defaults = array(
					'size' => '',
					'face' => '',
					'style' => '',
					'color' => ''
				);

				$typography_stored = wp_parse_args( $val, $typography_defaults );

				$typography_options = array(
					'sizes' => of_recognized_font_sizes(),
					'faces' => of_recognized_font_faces(),
					'styles' => of_recognized_font_styles(),
					'color' => true
				);

				if ( isset( $value['options'] ) ) {
					$typography_options = wp_parse_args( $value['options'], $typography_options );
				}

				// Font Size
				if ( $typography_options['sizes'] ) {
					$font_size = '<select class="of-typography of-typography-size" name="' . esc_attr( $option_name . '[' . $value['id'] . '][size]' ) . '" id="' . esc_attr( $value['id'] . '_size' ) . '">';
					$sizes = $typography_options['sizes'];
					foreach ( $sizes as $i ) {
						$size = $i . 'px';
						$font_size .= '<option value="' . esc_attr( $size ) . '" ' . selected( $typography_stored['size'], $size, false ) . '>' . esc_html( $size ) . '</option>';
					}
					$font_size .= '</select>';
				}

				// Font Face
				if ( $typography_options['faces'] ) {
					$font_face = '<select class="of-typography of-typography-face" name="' . esc_attr( $option_name . '[' . $value['id'] . '][face]' ) . '" id="' . esc_attr( $value['id'] . '_face' ) . '">';
					$faces = $typography_options['faces'];
					foreach ( $faces as $key => $face ) {
						$font_face .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['face'], $key, false ) . '>' . esc_html( $face ) . '</option>';
					}
					$font_face .= '</select>';
				}

				// Font Styles
				if ( $typography_options['styles'] ) {
					$font_style = '<select class="of-typography of-typography-style" name="'.$option_name.'['.$value['id'].'][style]" id="'. $value['id'].'_style">';
					$styles = $typography_options['styles'];
					foreach ( $styles as $key => $style ) {
						$font_style .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['style'], $key, false ) . '>'. $style .'</option>';
					}
					$font_style .= '</select>';
				}

				// Font Color
				if ( $typography_options['color'] ) {
					$default_color = '';
					if ( isset($value['std']['color']) ) {
						if ( $val !=  $value['std']['color'] )
							$default_color = ' data-default-color="' .$value['std']['color'] . '" ';
					}
					$font_color = '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" class="of-color of-typography-color  type="text" value="' . esc_attr( $typography_stored['color'] ) . '"' . $default_color .' />';
				}

				// Allow modification/injection of typography fields
				$typography_fields = compact( 'font_size', 'font_face', 'font_style', 'font_color' );
				$typography_fields = apply_filters( 'of_typography_fields', $typography_fields, $typography_stored, $option_name, $value );
				$output .= implode( '', $typography_fields );

				break;

			// Background
			case 'background':

				$background = $val;

				// Background Color
				$default_color = '';
				if ( isset( $value['std']['color'] ) ) {
					if ( $val !=  $value['std']['color'] )
						$default_color = ' data-default-color="' .$value['std']['color'] . '" ';
				}
				$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" class="of-color of-background-color"  type="text" value="' . esc_attr( $background['color'] ) . '"' . $default_color .' />';

				// Background Image
				if ( !isset($background['image']) ) {
					$background['image'] = '';
				}

				$output .= Options_Framework_Media_Uploader::optionsframework_uploader( $value['id'], $background['image'], null, esc_attr( $option_name . '[' . $value['id'] . '][image]' ) );

				$class = 'of-background-properties';
				if ( '' == $background['image'] ) {
					$class .= ' hide';
				}
				$output .= '<div class="' . esc_attr( $class ) . '">';

				// Background Repeat
				$output .= '<select class="of-background of-background-repeat" name="' . esc_attr( $option_name . '[' . $value['id'] . '][repeat]'  ) . '" id="' . esc_attr( $value['id'] . '_repeat' ) . '">';
				$repeats = of_recognized_background_repeat();

				foreach ($repeats as $key => $repeat) {
					$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['repeat'], $key, false ) . '>'. esc_html( $repeat ) . '</option>';
				}
				$output .= '</select>';

				// Background Position
				$output .= '<select class="of-background of-background-position" name="' . esc_attr( $option_name . '[' . $value['id'] . '][position]' ) . '" id="' . esc_attr( $value['id'] . '_position' ) . '">';
				$positions = of_recognized_background_position();

				foreach ($positions as $key=>$position) {
					$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['position'], $key, false ) . '>'. esc_html( $position ) . '</option>';
				}
				$output .= '</select>';

				// Background Attachment
				$output .= '<select class="of-background of-background-attachment" name="' . esc_attr( $option_name . '[' . $value['id'] . '][attachment]' ) . '" id="' . esc_attr( $value['id'] . '_attachment' ) . '">';
				$attachments = of_recognized_background_attachment();

				foreach ($attachments as $key => $attachment) {
					$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['attachment'], $key, false ) . '>' . esc_html( $attachment ) . '</option>';
				}
				$output .= '</select>';
				$output .= '</div>';

				break;

			// Custom Board List
			case 'custom_board_list':
				$output .= '<div class="custom-board-list-wrap" id="custom-board-list-' . esc_attr( $value['id'] ) . '">';
				$output .= '<div class="custom-board-items" style="display:flex;flex-wrap:wrap;gap:15px;margin-bottom:15px;">';
				
				$current_lolijump_img = get_boxmoe('boxmoe_lolijump_img');

				if ( is_array( $val ) && ! empty( $val ) ) {
					foreach ( $val as $k => $item ) {
						$url = isset($item['url']) ? $item['url'] : '';
						$name = isset($item['name']) ? $item['name'] : '';
						if(empty($url)) continue;
						
						$isActive = ($url == $current_lolijump_img);
						$btnText = $isActive ? __('已启动', 'ui_boxmoe_com') : __('启动', 'ui_boxmoe_com');
						$btnClass = $isActive ? 'button-primary disabled' : 'button-secondary';
						
						$output .= '<div class="custom-board-item" style="width:150px;border:1px solid #ddd;padding:10px;border-radius:5px;background:#fff;text-align:center;">';
						$output .= '<div class="custom-board-preview" style="margin-bottom:10px;height:150px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f5f5f5;">';
						$output .= '<img src="' . esc_url( $url ) . '" style="max-width:100%;max-height:100%;object-fit:contain;">';
						$output .= '</div>';
						$output .= '<input type="hidden" name="' . esc_attr( $option_name . '[' . $value['id'] . '][' . $k . '][url]' ) . '" value="' . esc_attr( $url ) . '" class="custom-board-url">';
						$output .= '<div class="custom-board-input-group">';
						$output .= '<input type="text" name="' . esc_attr( $option_name . '[' . $value['id'] . '][' . $k . '][name]' ) . '" value="' . esc_attr( $name ) . '" class="custom-board-name" placeholder=" ">';
						$output .= '<span class="custom-board-floating-label" data-normal="' . __('请输入名称', 'ui_boxmoe_com') . '" data-active="' . __('名称', 'ui_boxmoe_com') . '"></span>';
						$output .= '</div>';
						$output .= '<div class="actions" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:5px;">';
						$output .= '<button type="button" class="button custom-board-enable ' . $btnClass . '" data-url="' . esc_attr($url) . '" style="width:100%;margin-bottom:5px;">' . $btnText . '</button>';
						$output .= '<button type="button" class="button custom-board-replace" data-update="' . __('选择图片', 'ui_boxmoe_com') . '" data-choose="' . __('选择看板图片', 'ui_boxmoe_com') . '" style="flex:1;">' . __('替换', 'ui_boxmoe_com') . '</button>';
						$output .= '<button type="button" class="button custom-board-delete" style="color:#b32d2e;border-color:#b32d2e;flex:1;">' . __('删除', 'ui_boxmoe_com') . '</button>';
						$output .= '</div>';
						$output .= '</div>';
					}
				}
				
				$output .= '</div>';
				$output .= '<div class="custom-board-add-section" style="display:flex;gap:10px;align-items:center;margin-top:15px;">';
				$output .= '<button type="button" class="button button-primary custom-board-add" data-name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '">' . __('新增看板形象', 'ui_boxmoe_com') . '</button>';
				$output .= '<div class="custom-board-url-input" style="display:flex;gap:5px;flex:1;">';
				$output .= '<input type="text" id="custom-board-direct-url" placeholder="' . __('直接输入图片链接', 'ui_boxmoe_com') . '" style="flex:1;padding:4px 8px;border:1px solid #ddd;">';
				$output .= '<button type="button" class="button button-secondary custom-board-add-by-url" data-name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '">' . __('添加', 'ui_boxmoe_com') . '</button>';
				$output .= '</div>';
				$output .= '</div>';
				$output .= '</div>';
				break;

			// Editor
			case 'editor':
				$output .= '<div class="explain">' . wp_kses( $explain_value, $allowedtags ) . '</div>'."\n";
				echo $output;
				$textarea_name = esc_attr( $option_name . '[' . $value['id'] . ']' );
				$default_editor_settings = array(
					'textarea_name' => $textarea_name,
					'media_buttons' => false,
					'tinymce' => array( 'plugins' => 'wordpress' )
				);
				$editor_settings = array();
				if ( isset( $value['settings'] ) ) {
					$editor_settings = $value['settings'];
				}
				$editor_settings = array_merge( $default_editor_settings, $editor_settings );
				wp_editor( $val, $value['id'], $editor_settings );
				$output = '';
				break;

			// Info
			case "info":
				$id = '';
				$class = 'section';
				if ( isset( $value['id'] ) ) {
					$id = 'id="' . esc_attr( $value['id'] ) . '" ';
				}
				if ( isset( $value['type'] ) ) {
					$class .= ' section-' . $value['type'];
				}
				if ( isset( $value['class'] ) ) {
					$class .= ' ' . $value['class'];
				}

				$output .= '<div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";
				if ( isset($value['name']) ) {
					$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
				}
				if ( isset( $value['desc'] ) ) {
					$output .= $value['desc'] . "\n";
				}
				$output .= '</div>' . "\n";
				break;

			// Heading for Navigation
			case "heading":
				$counter++;
				if ( $counter >= 2 ) {
					$output .= '</div>'."\n";
				}
				$class = '';
				$class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
				$class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class) );
                $output .= '<div id="options-group-' . $counter . '" class="group ' . $class . '">';
                $default_icon = 'dashicons-wordpress';
                $icon = isset($value['icon']) ? $value['icon'] : $default_icon;
                $header  = '<div class="boxmoe_tab_header"><span class="dashicons ' . esc_attr($icon) . '"></span> ' . esc_html( $value['name'] );
                if ( isset($value['name']) && $value['name'] === '页面标语设置' ) {
                    $header .= ' <input type="button" id="of-reset-slogan-btn" class="button-secondary" name="reset_slogan" value="' . esc_attr__( '重置标语', 'textdomain' ) . '" />';
                }
                $header .= '</div>' . "\n";
                $output .= $header;
                break;
			}

			if ( ( !isset( $value['type'] ) || $value['type'] != "heading" ) && ( !isset( $value['type'] ) || $value['type'] != "info" ) ) {
				$output .= '</div>';
				if ( ( !isset( $value['type'] ) || $value['type'] != "checkbox" ) && ( !isset( $value['type'] ) || $value['type'] != "editor" ) ) {
					$output .= '<div class="explain">' . wp_kses( $explain_value, $allowedtags) . '</div>' ."
";
					// 如果是看板娘图片设置，在描述之后显示预览图
					if ( isset($value['id']) && $value['id'] == 'boxmoe_article_card_kanban_image' && isset($GLOBALS['boxmoe_preview_html'])) {
						$output .= $GLOBALS['boxmoe_preview_html'];
						// 清除全局变量，避免影响其他设置项
						unset($GLOBALS['boxmoe_preview_html']);
					}
				}
				$output .= '</div>';
				// 如果不是checkbox或editor，并且不是看板娘图片设置，确保正常显示
				if ( ( !isset( $value['type'] ) || $value['type'] != "checkbox" ) && ( !isset( $value['type'] ) || $value['type'] != "editor" ) && ( !isset( $value['id'] ) || $value['id'] != 'boxmoe_article_card_kanban_image' ) ) {
					// 普通设置项的正常处理
				}

				if (isset($value['group']) && $value['group'] == 'end') {
					// 关闭分组的 boxmoe_group_opened div
					$output .= '</div>'."\n";
					// 关闭分组的外部 div
					$output .= '</div>'."\n";
					$group_opened = false;
				} else if (!$group_opened) {
					$output .= '</div>'."\n";
				}
			}

			echo $output;
		}

		// Outputs closing div if there tabs
		if ( Options_Framework_Interface::optionsframework_tabs() != '' ) {
			echo '</div>';
		}
	}

}
