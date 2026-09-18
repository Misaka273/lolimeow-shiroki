<?php
/**
 * @link https://gl.baimu.live
 * @package l白木
 */

if (!defined('ABSPATH')) {
    echo 'Look your sister';
    exit;
}

function boxmoe_admin_color_defaults() {
    // 🎨 与 admin-variables 默认色值对齐，供选择器回退
    return array(
        'boxmoe_admin_color_pink_light' => '#ffb6c1',
        'boxmoe_admin_color_pink_medium' => '#ffc0cb',
        'boxmoe_admin_color_pink_dark' => '#ff69b4',
        'boxmoe_admin_color_pink_deep' => '#ff1493',
        'boxmoe_admin_color_pink_text' => '#8b008b',
        'boxmoe_admin_color_primary' => '#63b3ed',
        'boxmoe_admin_color_success' => '#68d391',
        'boxmoe_admin_color_warning' => '#fbbf24',
        'boxmoe_admin_color_danger' => '#f87171',
        'boxmoe_admin_color_purple' => '#b794f4',
        'boxmoe_admin_color_gray_light' => '#f5f5f5',
        'boxmoe_admin_color_gray_medium' => '#e0e0e0',
        'boxmoe_admin_color_gray_dark' => '#d0d0d0',
        'boxmoe_admin_color_text' => '#333333',
    );
}

function boxmoe_admin_color_option($id) {
    $defaults = boxmoe_admin_color_defaults();
    $fallback = isset($defaults[$id]) ? $defaults[$id] : '#000000';
    $value = function_exists('get_boxmoe') ? get_boxmoe($id, $fallback) : $fallback;
    if (!is_string($value) || !preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
        return $fallback;
    }
    if ($value[0] !== '#') {
        $value = '#' . $value;
    }
    return strtolower($value);
}

function boxmoe_admin_hex_to_oklch_parts($hex) {
    // 🌈 把后台颜色选择器的十六进制转成 OKLCH 分量
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    $to_linear = function ($channel) {
        $channel = $channel / 255;
        return $channel <= 0.04045 ? $channel / 12.92 : pow(($channel + 0.055) / 1.055, 2.4);
    };
    $lr = $to_linear(hexdec(substr($hex, 0, 2)));
    $lg = $to_linear(hexdec(substr($hex, 2, 2)));
    $lb = $to_linear(hexdec(substr($hex, 4, 2)));
    $l = 0.4122214708 * $lr + 0.5363325363 * $lg + 0.0514459929 * $lb;
    $m = 0.2119034982 * $lr + 0.6806995451 * $lg + 0.1073969566 * $lb;
    $s = 0.0883024619 * $lr + 0.2817188376 * $lg + 0.6299787005 * $lb;
    $l_ = pow(max($l, 0), 1 / 3);
    $m_ = pow(max($m, 0), 1 / 3);
    $s_ = pow(max($s, 0), 1 / 3);
    $ok_l = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
    $ok_a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
    $ok_b = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;
    $c = sqrt($ok_a * $ok_a + $ok_b * $ok_b);
    $h = rad2deg(atan2($ok_b, $ok_a));
    if ($h < 0) {
        $h += 360;
    }
    return array(
        'l' => max(0, min(1, $ok_l)),
        'c' => max(0, $c),
        'h' => $h,
    );
}

function boxmoe_admin_oklch($l, $c, $h, $alpha = 1) {
    if ($alpha >= 0.999) {
        return sprintf('oklch(%.4f %.4f %.2f)', $l, $c, $h);
    }
    return sprintf('oklch(%.4f %.4f %.2f / %.2f)', $l, $c, $h, $alpha);
}

function boxmoe_admin_color_from_hex($hex, $alpha = 1) {
    $parts = boxmoe_admin_hex_to_oklch_parts($hex);
    if (!$parts) {
        return 'oklch(0.5 0 0)';
    }
    return boxmoe_admin_oklch($parts['l'], $parts['c'], $parts['h'], $alpha);
}

function boxmoe_admin_color_text_from_hex($hex, $dark_mode = false) {
    $parts = boxmoe_admin_hex_to_oklch_parts($hex);
    if (!$parts) {
        return $dark_mode ? 'oklch(0.86 0.08 250)' : 'oklch(0.38 0.12 250)';
    }
    $l = $dark_mode ? min(0.92, max(0.72, $parts['l'] + 0.22)) : min(0.45, max(0.28, $parts['l'] - 0.28));
    $c = min($parts['c'] + 0.02, $dark_mode ? 0.12 : 0.18);
    return boxmoe_admin_oklch($l, $c, $parts['h']);
}

function boxmoe_admin_color_family_css($hex, $prefix) {
    $parts = boxmoe_admin_hex_to_oklch_parts($hex);
    if (!$parts) {
        return '';
    }
    $l = $parts['l'];
    $c = $parts['c'];
    $h = $parts['h'];
    $rules = array(
        "--{$prefix}-bg: " . boxmoe_admin_oklch($l, $c, $h, 0.35),
        "--{$prefix}-bg-hover: " . boxmoe_admin_oklch($l, $c, $h, 0.5),
        "--{$prefix}-border: " . boxmoe_admin_oklch($l, $c, $h, 0.6),
        "--{$prefix}-shadow: " . boxmoe_admin_oklch($l, $c, $h, 0.25),
        "--{$prefix}-text: " . boxmoe_admin_color_text_from_hex($hex, false),
        "--{$prefix}-text-dark: " . boxmoe_admin_color_text_from_hex($hex, true),
    );
    if ($prefix === 'admin-primary') {
        $rules[] = "--{$prefix}-bg-strong: " . boxmoe_admin_oklch($l, $c, $h, 0.7);
        $rules[] = '--admin-shadow-glow: 0 0 20px ' . boxmoe_admin_oklch($l, $c, $h, 0.3);
        $rules[] = '--admin-scheduled-bg: ' . boxmoe_admin_oklch(min(0.78, $l + 0.04), max(0.08, $c - 0.02), $h, 0.75);
    }
    if ($prefix === 'admin-warning') {
        $rules[] = "--{$prefix}-bg-solid: " . boxmoe_admin_oklch($l, $c, $h, 0.85);
    }
    if ($prefix === 'admin-danger') {
        $rules[] = "--{$prefix}-bg: " . boxmoe_admin_oklch(min(0.95, $l + 0.18), max(0.04, $c - 0.08), $h, 0.92);
        $rules[] = "--{$prefix}-bg-hover: " . boxmoe_admin_oklch(min(0.9, $l + 0.08), max(0.06, $c - 0.04), $h, 0.88);
        $rules[] = "--{$prefix}-border: " . boxmoe_admin_oklch($l, $c, $h, 0.55);
        $rules[] = '--admin-error-text: ' . boxmoe_admin_color_text_from_hex($hex, false);
    }
    if ($prefix === 'admin-replace') {
        $rules[] = "--{$prefix}-bg: " . boxmoe_admin_oklch(min(0.96, $l + 0.12), max(0.04, $c - 0.06), $h, 0.92);
        $rules[] = "--{$prefix}-bg-hover: " . boxmoe_admin_oklch(min(0.92, $l + 0.06), max(0.06, $c - 0.03), $h, 0.88);
        $rules[] = "--{$prefix}-border: " . boxmoe_admin_oklch($l, $c, $h, 0.55);
    }
    return implode(';', $rules);
}

function boxmoe_admin_color_select_arrow($hex) {
    $hex = ltrim($hex, '#');
    $svg = '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><path d="M5 6l5 5 5-5 2 1-7 7-7-7 2-1z" fill="#' . $hex . '"/></svg>';
    return 'url("data:image/svg+xml;charset=US-ASCII,' . rawurlencode($svg) . '")';
}

function boxmoe_admin_color_build_css() {
    // 🧩 用站点美化里的色值覆盖后台 CSS 变量
    $pink_light = boxmoe_admin_color_option('boxmoe_admin_color_pink_light');
    $pink_medium = boxmoe_admin_color_option('boxmoe_admin_color_pink_medium');
    $pink_dark = boxmoe_admin_color_option('boxmoe_admin_color_pink_dark');
    $pink_deep = boxmoe_admin_color_option('boxmoe_admin_color_pink_deep');
    $pink_text = boxmoe_admin_color_option('boxmoe_admin_color_pink_text');
    $primary = boxmoe_admin_color_option('boxmoe_admin_color_primary');
    $success = boxmoe_admin_color_option('boxmoe_admin_color_success');
    $warning = boxmoe_admin_color_option('boxmoe_admin_color_warning');
    $danger = boxmoe_admin_color_option('boxmoe_admin_color_danger');
    $purple = boxmoe_admin_color_option('boxmoe_admin_color_purple');
    $gray_light = boxmoe_admin_color_option('boxmoe_admin_color_gray_light');
    $gray_medium = boxmoe_admin_color_option('boxmoe_admin_color_gray_medium');
    $gray_dark = boxmoe_admin_color_option('boxmoe_admin_color_gray_dark');
    $text = boxmoe_admin_color_option('boxmoe_admin_color_text');
    $arrow_dark = boxmoe_admin_color_select_arrow($pink_dark);
    $arrow_deep = boxmoe_admin_color_select_arrow($pink_deep);
    $arrow_text = boxmoe_admin_color_select_arrow($text);
    $root = array(
        '--shiroki-pink-light: ' . boxmoe_admin_color_from_hex($pink_light),
        '--shiroki-pink-medium: ' . boxmoe_admin_color_from_hex($pink_medium),
        '--shiroki-pink-dark: ' . boxmoe_admin_color_from_hex($pink_dark),
        '--shiroki-pink-deep: ' . boxmoe_admin_color_from_hex($pink_deep),
        '--shiroki-pink-text: ' . boxmoe_admin_color_from_hex($pink_text),
        '--shiroki-pink-pale: ' . boxmoe_admin_color_from_hex($pink_light, 0.45),
        '--shiroki-gray-light: ' . boxmoe_admin_color_from_hex($gray_light),
        '--shiroki-gray-medium: ' . boxmoe_admin_color_from_hex($gray_medium),
        '--shiroki-gray-dark: ' . boxmoe_admin_color_from_hex($gray_dark),
        '--shiroki-black-medium: ' . boxmoe_admin_color_from_hex($text),
        '--admin-current-text-secondary: ' . boxmoe_admin_color_from_hex($text),
        boxmoe_admin_color_family_css($primary, 'admin-primary'),
        boxmoe_admin_color_family_css($success, 'admin-success'),
        boxmoe_admin_color_family_css($warning, 'admin-warning'),
        boxmoe_admin_color_family_css($danger, 'admin-danger'),
        boxmoe_admin_color_family_css($purple, 'admin-purple'),
        boxmoe_admin_color_family_css($pink_medium, 'admin-replace'),
    );
    $css = ':root{' . implode(';', array_filter($root)) . '}';
    $css .= 'body select:not(.mm):not(.jj):not(.aa),.wrap select:not(.mm):not(.jj):not(.aa),.form-table select:not(.mm):not(.jj):not(.aa),.wp-core-ui select:not(.mm):not(.jj):not(.aa),.wpjam-page select,.wpjam-field select,.has-dependents select,[data-show_if] select,[name="gravatar"],[name="google_fonts"]{background-image:' . $arrow_dark . '}';
    $css .= 'body select:not(.mm):not(.jj):not(.aa):hover,.wrap select:not(.mm):not(.jj):not(.aa):hover,.wpjam-page select:hover,.wpjam-field select:hover,.has-dependents select:hover,[data-show_if] select:hover,[name="gravatar"]:hover,[name="google_fonts"]:hover{background-image:' . $arrow_text . '}';
    $css .= 'body select:not(.mm):not(.jj):not(.aa):focus,.wrap select:not(.mm):not(.jj):not(.aa):focus,.wpjam-page select:focus,.wpjam-field select:focus,.has-dependents select:focus,[data-show_if] select:focus,[name="gravatar"]:focus,[name="google_fonts"]:focus{background-image:' . $arrow_deep . '}';
    $css .= '.boxmoe-select-trigger:after{background-image:' . $arrow_dark . '}';
    return $css;
}

function boxmoe_admin_color_custom_enqueue() {
    // 🚀 开启自定义配色后，把变量写进 admin-variables 之后
    if (!function_exists('get_boxmoe') || !get_boxmoe('boxmoe_admin_color_custom_switch')) {
        return;
    }
    $css = boxmoe_admin_color_build_css();
    if ($css === '') {
        return;
    }
    wp_add_inline_style('admin-variables', $css);
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_color_custom_enqueue', 20);

function boxmoe_admin_color_preview_script($hook) {
    // 👀 主题设置页里改色时即时预览，保存前也能看到效果
    if (strpos((string) $hook, 'boxmoe_options') === false) {
        return;
    }
    $js = 'jQuery(function($){';
    $js .= 'var defaults=' . wp_json_encode(boxmoe_admin_color_defaults()) . ';';
    $js .= 'function hexVal(id){var $el=$("#"+id);var v="";if($el.length){try{v=$el.wpColorPicker("color")||"";}catch(e){v="";}if(!v){v=$el.val()||"";}}if(!v){v=defaults[id]||"#000000";}if(v.charAt(0)!=="#"){v="#"+v;}return v;}';
    $js .= 'function applyPreview(){var on=$("#boxmoe_admin_color_custom_switch").is(":checked");var $s=$("#shiroki-admin-color-preview");if(!on){$s.remove();return;}if(!$s.length){$s=$("<style id=\\"shiroki-admin-color-preview\\"></style>").appendTo("head");}';
    $js .= 'var light=hexVal("boxmoe_admin_color_pink_light"),mid=hexVal("boxmoe_admin_color_pink_medium"),dark=hexVal("boxmoe_admin_color_pink_dark"),deep=hexVal("boxmoe_admin_color_pink_deep"),txt=hexVal("boxmoe_admin_color_pink_text"),pri=hexVal("boxmoe_admin_color_primary"),ok=hexVal("boxmoe_admin_color_success"),warn=hexVal("boxmoe_admin_color_warning"),danger=hexVal("boxmoe_admin_color_danger"),purple=hexVal("boxmoe_admin_color_purple"),gl=hexVal("boxmoe_admin_color_gray_light"),gm=hexVal("boxmoe_admin_color_gray_medium"),gd=hexVal("boxmoe_admin_color_gray_dark"),body=hexVal("boxmoe_admin_color_text");';
    $js .= '$s.text(":root{--shiroki-pink-light:"+light+";--shiroki-pink-medium:"+mid+";--shiroki-pink-dark:"+dark+";--shiroki-pink-deep:"+deep+";--shiroki-pink-text:"+txt+";--shiroki-pink-pale:"+light+";--shiroki-gray-light:"+gl+";--shiroki-gray-medium:"+gm+";--shiroki-gray-dark:"+gd+";--shiroki-black-medium:"+body+";--admin-primary-bg:"+pri+";--admin-success-bg:"+ok+";--admin-warning-bg:"+warn+";--admin-danger-text:"+danger+";--admin-purple-bg:"+purple+";}");}';
    $js .= '$(document).on("change","#boxmoe_admin_color_custom_switch,.of-color",applyPreview);';
    $js .= 'setTimeout(applyPreview,0);';
    $js .= '});';
    wp_add_inline_script('options-custom', $js);
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_color_preview_script', 30);
