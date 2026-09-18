<?php
if(!defined('ABSPATH')){echo 'Look your sister';exit;}

/**
 * 获取主题强调色（r-markdown 组件默认使用）。
 */
function boxmoe_get_theme_accent(){
    $color = function_exists('get_boxmoe') ? get_boxmoe('boxmoe_theme_color') : '';
    if($color && $color !== 'default' && preg_match('/^#[0-9a-fA-F]{6}$/', $color)){
        return $color;
    }
    return '#27ae60';
}

/**
 * 解析 HTML 属性字符串为关联数组。
 */
function boxmoe_parse_html_attrs($attr_str){
    $attrs = [];
    if(preg_match_all('/(\w+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^>\s]*)))?/', $attr_str, $m, PREG_SET_ORDER)){
        foreach($m as $match){
            $key = strtolower($match[1]);
            $val = isset($match[2]) && $match[2] !== '' ? $match[2] : (isset($match[3]) && $match[3] !== '' ? $match[3] : (isset($match[4]) ? $match[4] : ''));
            $attrs[$key] = $val;
        }
    }
    return $attrs;
}

/**
 * 将十六进制颜色转为 rgba 字符串。
 */
function boxmoe_hex_to_rgba($hex, $alpha = 1){
    $hex = ltrim($hex, '#');
    if(strlen($hex) === 3){
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = isset($hex[0]) ? hexdec(substr($hex, 0, 2)) : 0;
    $g = isset($hex[2]) ? hexdec(substr($hex, 2, 2)) : 0;
    $b = isset($hex[4]) ? hexdec(substr($hex, 4, 2)) : 0;
    return "rgba($r,$g,$b,$alpha)";
}

/**
 * 从字符串中截取到指定标签的配对闭合标签。
 */
function boxmoe_take_until_balanced_close($s, $tag){
    $pattern = '/<(\/?)'.preg_quote($tag, '/').'\b[^>]*>/i';
    $depth = 1;
    $offset = 0;
    while(preg_match($pattern, $s, $m, PREG_OFFSET_CAPTURE, $offset)){
        $tag_pos = $m[0][1];
        $tag_end = $tag_pos + strlen($m[0][0]);
        $is_self_close = substr(rtrim($m[0][0]), -2) === '/>';
        if($is_self_close){
            $offset = $tag_end;
            continue;
        }
        $is_close = ($m[1][0] === '/');
        if($is_close){
            $depth--;
            if($depth === 0){
                return ['body' => substr($s, 0, $tag_pos), 'end' => $tag_end];
            }
        } else {
            $depth++;
        }
        $offset = $tag_end;
    }
    return null;
}

/**
 * 解析字重关键字为 CSS 字重值。
 */
function boxmoe_resolve_weight($weight){
    if(!$weight) return '';
    $map = [
        'thin' => '100', 'light' => '300', 'normal' => '400', 'regular' => '400',
        'medium' => '500', 'semibold' => '600', 'bold' => '700', 'bolder' => '800'
    ];
    $lower = strtolower($weight);
    return isset($map[$lower]) ? $map[$lower] : $weight;
}

/**
 * 解析字号值，纯数字自动补 px。
 */
function boxmoe_resolve_size($size){
    if(!$size) return '';
    return preg_match('/^\d+(\.\d+)?$/', $size) ? $size.'px' : $size;
}

/**
 * 渲染单个 r-markdown 扩展组件。
 */
function boxmoe_render_r_markdown_component($tag, $attrs, $body){
    $accent = boxmoe_get_theme_accent();
    $accent_dark = boxmoe_hex_to_rgba($accent, 1);
    // 简单加深：直接沿用原色，保持组件可见
    $text = trim($body);
    if($text === '' && isset($attrs['text'])) $text = $attrs['text'];

    switch($tag){
        case 'text':
            $styles = [];
            if(isset($attrs['color'])) $styles[] = 'color:'.$attrs['color'];
            $weight = boxmoe_resolve_weight(isset($attrs['weight']) ? $attrs['weight'] : '');
            if($weight) $styles[] = 'font-weight:'.$weight;
            $size = boxmoe_resolve_size(isset($attrs['size']) ? $attrs['size'] : '');
            if($size) $styles[] = 'font-size:'.$size;
            $style = $styles ? ' style="'.implode(';', $styles).'"' : '';
            return '<span'.$style.'>'.esc_html($text).'</span>';

        case 'pill-text':
            $accent_rgba = boxmoe_hex_to_rgba($accent, 1);
            return '<span style="background:linear-gradient(90deg,'.boxmoe_hex_to_rgba($accent, 0.85).' 0%,'.$accent_rgba.' 100%);padding:0px 6px;border-radius:4px;font-weight:700;color:#fff">'.esc_html($text).'</span>';

        case 'strong-text':
            return '<strong style="color:'.$accent.'">'.esc_html($text).'</strong>';

        case 'soft-text':
            return '<span style="color:'.$accent.';font-weight:700;background:'.boxmoe_hex_to_rgba($accent, 0.08).';padding:1px 6px;border-radius:4px">'.esc_html($text).'</span>';

        case 'underline-text':
            return '<span style="text-decoration:underline;text-decoration-color:'.$accent.';text-underline-offset:3px">'.esc_html($text).'</span>';

        case 'strike-text':
            return '<del style="color:#9ca3af">'.esc_html($text).'</del>';

        case 'gradient-text':
            $color = isset($attrs['color']) ? $attrs['color'] : '';
            if($color && preg_match('/^#[0-9a-fA-F]{6}$/', $color)){
                return '<span style="background:linear-gradient(120deg,'.boxmoe_hex_to_rgba($color, 0.3).' 0%,'.boxmoe_hex_to_rgba($color, 0.5).' 100%);padding:2px 8px;border-radius:4px;font-weight:700;color:'.$color.'">'.esc_html($text).'</span>';
            }
            return '<span style="background:linear-gradient(120deg,'.boxmoe_hex_to_rgba($accent, 0.3).' 0%,'.boxmoe_hex_to_rgba($accent, 0.5).' 100%);padding:2px 8px;border-radius:4px;font-weight:700;color:'.$accent.'">'.esc_html($text).'</span>';

        case 'gradient-text-2':
            $from = isset($attrs['from']) ? $attrs['from'] : $accent;
            $to = isset($attrs['to']) ? $attrs['to'] : $accent;
            $text_color = isset($attrs['textcolor']) ? $attrs['textcolor'] : 'var(--text-primary)';
            return '<span style="background:linear-gradient(120deg,'.boxmoe_hex_to_rgba($from, 0.3).' 0%,'.boxmoe_hex_to_rgba($to, 0.5).' 100%);padding:2px 8px;border-radius:4px;font-weight:700;color:'.$text_color.'">'.esc_html($text).'</span>';

        case 'gradient-text-3':
            $color = isset($attrs['color']) ? $attrs['color'] : $accent;
            $text_color = isset($attrs['textcolor']) ? $attrs['textcolor'] : 'var(--text-primary)';
            return '<span style="background:linear-gradient(120deg,'.boxmoe_hex_to_rgba($color, 0.3).' 0%,'.boxmoe_hex_to_rgba($color, 0.5).' 100%);padding:2px 8px;border-radius:4px;font-weight:700;color:'.$text_color.'">'.esc_html($text).'</span>';

        case 'pill-btn':
            $link = isset($attrs['link']) ? $attrs['link'] : '';
            $color = isset($attrs['color']) ? $attrs['color'] : '';
            $gradient = $color
                ? 'linear-gradient(135deg,'.$color.','.$color.')'
                : (isset($attrs['gradient']) ? $attrs['gradient'] : 'linear-gradient(135deg,'.$accent.','.$accent.')');
            $style = 'display:inline-flex;align-items:center;font-size:13px;padding:4px 14px;font-weight:500;color:#fff;border-radius:50px;border:none;transition:all 0.3s cubic-bezier(.5,2.5,.7,.7);text-decoration:none;box-shadow:0 2px 6px rgba(0,0,0,0.15);background:'.$gradient.';cursor:pointer;vertical-align:middle;';
            $hover = 'onmouseenter="this.style.transform=\'translateY(-2px)\';this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.2)\'" onmouseleave="this.style.transform=\'\';this.style.boxShadow=\'0 2px 6px rgba(0,0,0,0.15)\'"';
            if($link){
                return '<a href="'.esc_url($link).'" target="_blank" rel="noopener" style="'.$style.'" '.$hover.'>'.esc_html($text).'</a>';
            }
            return '<span style="'.$style.'" '.$hover.'>'.esc_html($text).'</span>';

        case 'pill-btn-2':
            $link = isset($attrs['link']) ? $attrs['link'] : '';
            $color = isset($attrs['color']) ? $attrs['color'] : $accent;
            $text_color = isset($attrs['textcolor']) ? $attrs['textcolor'] : '#fff';
            $style = 'display:inline-flex;align-items:center;font-size:13px;padding:4px 14px;font-weight:500;color:'.$text_color.';border-radius:50px;border:none;transition:all 0.3s cubic-bezier(.5,2.5,.7,.7);text-decoration:none;box-shadow:0 2px 6px rgba(0,0,0,0.15);background:'.$color.';cursor:pointer;vertical-align:middle;';
            $hover = 'onmouseenter="this.style.transform=\'translateY(-2px)\';this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.2)\'" onmouseleave="this.style.transform=\'\';this.style.boxShadow=\'0 2px 6px rgba(0,0,0,0.15)\'"';
            if($link){
                return '<section style="margin:10px 0;text-align:center;"><a href="'.esc_url($link).'" target="_blank" rel="noopener" style="'.$style.'" '.$hover.'>'.esc_html($text).'</a></section>';
            }
            return '<section style="margin:10px 0;text-align:center;"><span style="'.$style.'" '.$hover.'>'.esc_html($text).'</span></section>';

        case 'statement':
            $title = isset($attrs['title']) ? $attrs['title'] : (isset($attrs['text']) ? $attrs['text'] : $text);
            return '<section style="padding:28px 24px;margin:20px 0;text-align:center;border-radius:12px;background:linear-gradient(135deg,'.boxmoe_hex_to_rgba($accent, 0.06).','.boxmoe_hex_to_rgba($accent, 0.02).');border-left:4px solid '.$accent.'">
      <p style="margin:0;font-size:17px;font-weight:600;color:var(--text-primary);line-height:1.8">'.esc_html($title).'</p>
    </section>';

        case 'lead':
            $title = isset($attrs['title']) ? $attrs['title'] : (isset($attrs['text']) ? $attrs['text'] : $text);
            return '<section style="margin:16px 0;padding:12px 16px;border-left:3px solid '.$accent.';background:rgba(0,0,0,0.02);border-radius:0 8px 8px 0">
      <p style="margin:0;font-size:15px;color:#555;line-height:1.8;font-style:italic">'.esc_html($title).'</p>
    </section>';
    }

    return '';
}

/**
 * 解析 r-markdown 的 <html> 片段内容：
 * - 自定义 HTML 原样保留
 * - 内嵌扩展组件（text/pill-text/statement/lead 等）渲染为对应 HTML
 */
function boxmoe_parse_r_markdown_html_fragment($html){
    $tags = ['text','pill-text','strong-text','soft-text','underline-text','strike-text','gradient-text','gradient-text-2','gradient-text-3','pill-btn','pill-btn-2','statement','lead'];
    $tag_alt = implode('|', array_map(function($t){ return preg_quote($t, '/'); }, $tags));
    $pattern = '/<('.$tag_alt.')\b([^>]*?)(\/?)>/i';

    $result = '';
    $offset = 0;
    while(preg_match($pattern, $html, $m, PREG_OFFSET_CAPTURE, $offset)){
        $start = $m[0][1];
        $tag = strtolower($m[1][0]);
        $full_open = $m[0][0];
        $attr_str = isset($m[2]) ? $m[2][0] : '';
        $self_closing = (isset($m[3]) && trim($m[3][0]) === '/') || substr(rtrim($full_open), -2) === '/';

        $result .= substr($html, $offset, $start - $offset);

        $attrs = boxmoe_parse_html_attrs($attr_str);

        if($self_closing){
            $body = isset($attrs['text']) ? $attrs['text'] : '';
            $result .= boxmoe_render_r_markdown_component($tag, $attrs, $body);
            $offset = $start + strlen($full_open);
            continue;
        }

        $after = substr($html, $start + strlen($full_open));
        $closed = boxmoe_take_until_balanced_close($after, $tag);
        if(!$closed){
            $result .= $full_open;
            $offset = $start + strlen($full_open);
            continue;
        }

        $end = $start + strlen($full_open) + $closed['end'];
        $inner = $closed['body'];
        $result .= boxmoe_render_r_markdown_component($tag, $attrs, $inner);
        $offset = $end;
    }
    $result .= substr($html, $offset);
    return $result;
}

/**
 * 提取自定义 HTML 区块为占位符，内部 HTML 原样保留。
 * 支持两种语法：
 *   1. <!--!html-->...<!--!html-->
 *   2. r-markdown 的 <html>...</html>（支持嵌套，栈匹配；内部扩展组件会被渲染）
 */
function boxmoe_extract_html_blocks($text, &$html_blocks){
    $preserve_tables = function($html){
        // r-markdown 自定义 HTML 块中的表格保持原样，不被主题 table-responsive 再次包装
        return preg_replace('/<table\b/i', '<table data-no-table-replace="1"', $html);
    };

    // 1. 传统 <!--!html--> 语法
    $text = preg_replace_callback('/<!--!html-->([\s\S]*?)<!--!html-->/', function($m) use (&$html_blocks, $preserve_tables){
        $key = '__MD_HTML_'.count($html_blocks).'__';
        $html_blocks[$key] = $preserve_tables($m[1]);
        return $key;
    }, $text);

    // 2. r-markdown 的 <html>...</html> 语法：栈匹配找到正确闭合标签
    $result = '';
    $offset = 0;
    while(preg_match('/<html\b[^>]*>/i', $text, $m, PREG_OFFSET_CAPTURE, $offset)){
        $start = $m[0][1];
        $open_end = $start + strlen($m[0][0]);
        $depth = 1;
        $pos = $open_end;
        $found = false;
        while(preg_match('/<\/?html\b[^>]*>/i', $text, $mm, PREG_OFFSET_CAPTURE, $pos)){
            $tag_pos = $mm[0][1];
            $tag_end = $tag_pos + strlen($mm[0][0]);
            $is_close = (substr($mm[0][0], 1, 1) === '/');
            $is_self_close = (substr(rtrim($mm[0][0]), -2) === '/>');
            if($is_self_close){
                $pos = $tag_end;
                continue;
            }
            if($is_close){
                $depth--;
                if($depth === 0){
                    $body = substr($text, $open_end, $tag_pos - $open_end);
                    $body = boxmoe_parse_r_markdown_html_fragment($body);
                    $body = $preserve_tables($body);
                    $key = '__MD_HTML_'.count($html_blocks).'__';
                    $html_blocks[$key] = '<section data-html-fragment="true" style="margin:16px 0;display:block">'.$body.'</section>';
                    $result .= substr($text, $offset, $start - $offset).$key;
                    $offset = $tag_end;
                    $found = true;
                    break;
                }
            } else {
                $depth++;
            }
            $pos = $tag_end;
        }
        if(!$found){
            // 未找到闭合标签：保留已扫描部分，继续向后避免无限循环
            $result .= substr($text, $offset, $open_end - $offset);
            $offset = $open_end;
        }
    }
    $result .= substr($text, $offset);
    return $result;
}

/**
 * 提取完整合法的 HTML 为占位符，仅转义未闭合/孤立的尖括号片段。
 * 例如 `<h1>标题</h1>` 会正常渲染，而 `<h1 class="page-title">` 会被转义为纯文本。
 *
 * @param string $text
 * @param array  $safe_html 占位符 => 原始 HTML
 * @return string
 */
function boxmoe_preserve_valid_html($text, &$safe_html){
    $save_block = function($html) use (&$safe_html){
        $key = '__MD_SAFE_BLOCK_'.count($safe_html).'__';
        $safe_html[$key] = $html;
        return $key;
    };
    $save_inline = function($html) use (&$safe_html){
        $key = '__MD_SAFE_INLINE_'.count($safe_html).'__';
        $safe_html[$key] = $html;
        return $key;
    };

    // 🧱 优先提取块级元素，避免内部 <i class="fa"> 等行内标签被单独占位导致还原失败
    // 同类型可嵌套块级元素（如 section、div）：使用栈匹配，避免正则错误截断内层闭合标签
    $nestable_tags = 'section|div|article|aside|details|figure|blockquote|nav|header|footer|main|form';
    for($i = 0; $i < 50; $i++){
        $matched = false;
        if(preg_match('/<('.$nestable_tags.')(\s[^>]*)?>/i', $text, $m, PREG_OFFSET_CAPTURE)){
            $tag = strtolower($m[1][0]);
            $start = $m[0][1];
            $depth = 0;
            $pos = $start;
            $found = false;
            while(preg_match('/<(\/?)('.$tag.')(\s[^>]*)?>/i', $text, $mm, PREG_OFFSET_CAPTURE, $pos)){
                $tag_pos = $mm[0][1];
                $is_close = ($mm[1][0] === '/');
                if($is_close){
                    $depth--;
                    if($depth === 0){
                        $end = $tag_pos + strlen($mm[0][0]);
                        $html = substr($text, $start, $end - $start);
                        $key = $save_block($html);
                        $text = substr($text, 0, $start).$key.substr($text, $end);
                        $matched = true;
                        $found = true;
                        break;
                    }
                } else {
                    $depth++;
                }
                $pos = $tag_pos + strlen($mm[0][0]);
            }
            if(!$found){
                // 未找到闭合标签：转义该开始标签并保存，避免无限循环
                $escaped = str_replace(['<','>'], ['&lt;','&gt;'], $m[0][0]);
                $key = $save_block($escaped);
                $text = substr($text, 0, $start).$key.substr($text, $start + strlen($m[0][0]));
                $matched = true;
            }
        }
        if(!$matched){
            break;
        }
    }

    // 块级元素（迭代由内向外，支持嵌套）
    $block_tags = 'address|article|aside|blockquote|canvas|dd|div|dl|dt|fieldset|figcaption|figure|footer|form|h[1-6]|header|li|main|nav|ol|p|pre|section|table|tbody|td|tfoot|th|thead|tr|ul|video|audio|details|summary|style|script|iframe|svg|math|center|font';
    for($i = 0; $i < 30 && preg_match('/<('.$block_tags.')(\s[^>]*)?>[\s\S]*?<\/\1>/i', $text); $i++){
        $text = preg_replace_callback('/<('.$block_tags.')(\s[^>]*)?>[\s\S]*?<\/\1>/i', function($m) use ($save_block){
            return $save_block($m[0]);
        }, $text);
    }

    // 行内元素（迭代匹配，支持嵌套）——在块级提取之后处理剩余孤立行内标签
    $inline_tags = 'a|abbr|b|bdi|bdo|cite|code|data|del|dfn|em|i|ins|kbd|mark|q|s|samp|small|span|strong|sub|sup|time|u|var|label|ruby|rt|rp';
    for($i = 0; $i < 30 && preg_match('/<('.$inline_tags.')(\s[^>]*)?>[\s\S]*?<\/\1>/i', $text); $i++){
        $text = preg_replace_callback('/<('.$inline_tags.')(\s[^>]*)?>[\s\S]*?<\/\1>/i', function($m) use ($save_inline){
            return $save_inline($m[0]);
        }, $text);
    }

    // 自闭合 / void 标签——在块级提取之后处理剩余孤立标签
    $void_tags = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $text = preg_replace_callback('/<('.$void_tags.')(\s[^>]*)?\/?>/i', function($m) use ($save_inline){
        return $save_inline($m[0]);
    }, $text);

    // 转义剩余未闭合/孤立的尖括号，防止布局错乱
    return str_replace(['<', '>'], ['&lt;', '&gt;'], $text);
}

/**
 * 🔄 多轮还原 HTML 占位符，确保块级占位符内的行内占位符也能正确还原
 */
function boxmoe_restore_html_placeholders($html, $placeholders){
    if(empty($placeholders)){
        return $html;
    }
    for($pass = 0; $pass < 30; $pass++){
        $changed = false;
        foreach($placeholders as $key => $value){
            if(strpos($html, $key) !== false){
                $html = str_replace($key, $value, $html);
                $changed = true;
            }
        }
        if(!$changed){
            break;
        }
    }
    return $html;
}

function boxmoe_markdown_to_html($text){
    // 检查内容是否为密码保护表单，如果是则不进行Markdown转换
    if(strpos($text, 'password-protected-form') !== false || strpos($text, 'post_password') !== false || strpos($text, 'wp-login.php?action=postpass') !== false){
        return $text;
    }
    $text = str_replace(["\r\n","\r"],"\n",$text);
    $html_blocks = [];
    $safe_html = [];
    $blocks = [];

    // 自定义 HTML 区块：<!--!html-->...<!--!html-->，优先提取以免被 Markdown 处理
    $text = boxmoe_extract_html_blocks($text, $html_blocks);
    // 强制 HTML 区块占位符独占段落，避免被 <p> 包裹
    $text = preg_replace('/(__MD_HTML_\d+__)/', "\n\n$1\n\n", $text);

    // 代码块解析，确保与主题自带语法兼容
    $text = preg_replace_callback('/```(\w+)?\s*([\s\S]*?)```/m', function($m) use (&$blocks){
        $key = '__MD_CODE_'.count($blocks).'__';
        $language = $m[1] ? $m[1] : '';
        $code = $m[2];
        $lang_class = $language ? ' lang-'.esc_attr($language) : '';
        // 确保生成的HTML结构与主题样式兼容，包含必要的<code>标签
        $blocks[$key] = '<pre class="prettyprint linenums'.esc_attr($lang_class).'"><code'.esc_attr($lang_class).'>'.esc_html($code).'</code></pre>';
        return $key;
    }, $text);

    // 🌊 自定义分割线注释须在尖括号转义前转为块级 HTML，否则会被转义成纯文本
    $text = preg_replace('/<!--\s*shiroki-divider\s*-->/', "\n\n<div class=\"shiroki-divider\"></div>\n\n", $text);

    // 保留完整 HTML、转义孤立尖括号（HTML 区块与代码块已隔离）
    $text = boxmoe_preserve_valid_html($text, $safe_html);
    // 块级 HTML 占位符独占段落，避免被 <p> 包裹
    $text = preg_replace('/(__MD_SAFE_BLOCK_\d+__)/', "\n\n$1\n\n", $text);

    // 标题解析，确保与主题自带语法兼容
    $text = preg_replace('/^\s*######\s*(.+)$/m','<h6>$1</h6>',$text);
    $text = preg_replace('/^\s*#####\s*(.+)$/m','<h5>$1</h5>',$text);
    $text = preg_replace('/^\s*####\s*(.+)$/m','<h4>$1</h4>',$text);
    $text = preg_replace('/^\s*###\s*(.+)$/m','<h3>$1</h3>',$text);
    $text = preg_replace('/^\s*##\s*(.+)$/m','<h2>$1</h2>',$text);
    $text = preg_replace('/^\s*#\s*(.+)$/m','<h1>$1</h1>',$text);
    $text = preg_replace('/^\s*>\s?(.+)$/m','<blockquote><p>$1</p></blockquote>',$text);
    // 支持三种任务清单状态：未完成[- [ ]]、进行中[- [>]]、已完成[- [x]]
    $text = preg_replace_callback('/(^|\n)(?:-\s*\[( |x|>)\]\s+.+)(?:\n(?:-\s*\[( |x|>)\]\s+.+))*/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        global $post;
        $is_author = false;
        if(is_user_logged_in() && $post){
            $current_user_id = get_current_user_id();
            $is_author = ($current_user_id == $post->post_author);
            
            // 检查用户是否是被授权的编辑者
            if (!$is_author) {
                $editors = get_post_meta($post->ID, '_boxmoe_post_editors', true);
                $editors = is_array($editors) ? $editors : array();
                $is_author = in_array($current_user_id, $editors);
            }
        }
        $list_class = $is_author ? 'md-task-list-interactive' : 'md-task-list-static';
        foreach($items as $it){
            if(preg_match('/^-\s*\[( |x|>)\]\s+(.+)/', $it, $mm)){
                $status_char = $mm[1];
                // 根据状态字符设置emoji和状态值
                switch($status_char){
                    case 'x':
                        $emoji = '✅';
                        $task_status = 'completed';
                        break;
                    case '>':
                        $emoji = '🔄';
                        $task_status = 'in-progress';
                        break;
                    default:
                        $emoji = '❌';
                        $task_status = 'pending';
                        break;
                }
                $item_class = $is_author ? 'md-task-item-interactive' : 'md-task-item-static';
                $task_content = esc_attr($mm[2]);
                $lis .= '<li class="md-task-item ' . $item_class . '" data-task-status="' . $task_status . '" data-task-content="' . $task_content . '" data-is-author="' . ($is_author ? 'true' : 'false') . '">';
                $lis .= '<span class="md-task-emoji">' . $emoji . '</span>';
                $lis .= '<span class="md-task-text">' . $mm[2] . '</span>';
                $lis .= '</li>';
            }
        }
        return '<ul class="md-task-list ' . $list_class . '">' . $lis . '</ul>';
    }, $text);
    

    $text = preg_replace_callback('/(^|\n)(?:-\s+.+(:?\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        foreach($items as $it){
            if(preg_match('/^-\s+(.+)/',$it,$mm)){$lis .= '<li>'.$mm[1].'</li>';}
        }
        return '<ul>'.$lis.'</ul>';
    }, $text);
    /* 🔢 有序列表（Ordered List）解析 - 支持自定义起始序号 */
    $text = preg_replace_callback('/(^|\n)(?:\d+\.\s+.+(:?\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        $start_num = 1; // ◀️ 默认起始序号为1
        $first = true;
        foreach($items as $it){
            /* 📋 捕获序号和内容，保留用户指定的序号 */
            if(preg_match('/^(\d+)\.\s+(.+)/',$it,$mm)){
                if($first){
                    $start_num = intval($mm[1]); // ◀️ 获取第一个项目的序号作为起始值
                    $first = false;
                }
                $lis .= '<li>'.$mm[2].'</li>';
            }
        }
        /* 🎯 如果起始序号不是1，添加start属性 */
        if($start_num !== 1){
            return '<ol start="'.$start_num.'">'.$lis.'</ol>';
        }
        return '<ol>'.$lis.'</ol>';
    }, $text);
    // 解析卡片内容，将其替换为临时占位符
    $card_placeholders = [];
    $text = preg_replace_callback('/名称：\s*(.+?)\s*\n头像链接：\s*(.+?)\s*\n描述：\s*(.+?)\s*\n链接：\s*(.+?)\s*\n勋章：\s*(.*?)\s*(\n|$)/s', function($m) use (&$card_placeholders){
        $name = $m[1];
        $avatar = $m[2];
        $desc = $m[3];
        $link = $m[4];
        $badge = $m[5];
        $badge_html = $badge !== '' ? '<div class="md-card-badge">'.$badge.'</div>' : '';
        
        $card_html = '<a href="'.esc_url($link).'" target="_blank" rel="noopener" class="md-card-link-wrap shiroki-md-card-link">
            <div class="md-card">
                <div class="md-card-avatar">
                    <img src="'.esc_url($avatar).'" alt="'.esc_attr($name).'" />
                    '.$badge_html.'
                </div>
                <div class="md-card-content">
                    <h3 class="md-card-title">'.$name.'</h3>
                    <p class="md-card-desc">'.$desc.'</p>
                </div>
            </div>
        </a>';
        
        $placeholder = '__MD_CARD_'.count($card_placeholders).'__';
        $card_placeholders[$placeholder] = $card_html;
        return $placeholder;
    }, $text);
    // 🎯 卡片占位符独占段落，避免被 <p> 包裹导致布局错乱
    $text = preg_replace('/(__MD_CARD_\d+__)/', "\n\n$1\n\n", $text);
    
    // 处理其他Markdown元素，包括链接转换
    // 🔤 文本格式：粗体
    $text = preg_replace('/\*\*(.+?)\*\*/s','<strong>$1</strong>',$text);
    // 🔤 文本格式：斜体
    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s','<em>$1</em>',$text);
    // 📋 文本格式：行内代码
    $text = preg_replace('/`([^`]+)`/s','<code>$1</code>',$text);
    // 📷 图片（支持自定义尺寸语法：![alt](url =widthxheight) 或 ![alt](url =widthxheightxalignment)）
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\s*=\s*(\d+)x(\d+)(x(\w+))?\)/i','<img src="$2" alt="$1" width="$3" height="$4" $5$6 />',$text);
    // 处理基础图片语法（无尺寸）
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/','<img src="$2" alt="$1" />',$text);
    // 🔗 链接（标记为可统计的 Markdown 链接）
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/','<a href="$2" class="shiroki-md-link"'.(is_admin()?'':' target="_blank" rel="noopener"').'>$1</a>',$text);
    // 📊 表格支持
    // 🔍 匹配连续的管道行，不要求表格后存在空行或分割线
    $tables = [];
    $text = preg_replace_callback('/^[\t ]*\|[^\n]*\|[\t ]*(?:\n[\t ]*\|[^\n]*\|[\t ]*)+/m', function($m) use (&$tables) {
        $table_key = '__MD_TABLE_' . count($tables) . '__';
        $tables[$table_key] = trim($m[0]);
        return "\n\n" . $table_key . "\n\n";
    }, $text);
    
    // 🧩 逐个处理每个表格
    foreach ($tables as $key => $table_content) {
        $lines = preg_split('/\n/', $table_content);
        $rows = [];
        foreach ($lines as $line) {
            if (preg_match('/^[\t ]*\|(.*)\|[\t ]*$/', $line, $mm)) {
                $rows[] = array_map('trim', explode('|', $mm[1]));
            }
        }

        $header_cells = array_shift($rows);
        if (!$header_cells) {
            $text = str_replace($key, $table_content, $text);
            continue;
        }

        // 🧹 标准 Markdown 分隔行只定义表头，不渲染为数据行
        if (isset($rows[0]) && count($rows[0]) === count($header_cells)) {
            $is_separator = true;
            foreach ($rows[0] as $cell) {
                if (!preg_match('/^:?-{3,}:?$/', $cell)) {
                    $is_separator = false;
                    break;
                }
            }
            if ($is_separator) {
                array_shift($rows);
            }
        }

        $html = '<div class="md-table-wrapper"><table class="md-table"><thead><tr>';
        foreach ($header_cells as $cell) {
            $html .= '<th>' . $cell . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $cells) {
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        $text = str_replace($key, $html, $text);
    }
    // 📏 水平分割线
    $text = preg_replace('/^---$/m','<hr class="md-hr" />',$text);
    $text = preg_replace('/^___$/m','<hr class="md-hr" />',$text);
    $text = preg_replace('/^\*\*\*$/m','<hr class="md-hr" />',$text);
    
    // 📦 折叠语法支持
    // 支持:::details 标题
    // 内容
    // ::: 格式
    $text = preg_replace_callback('/:::details\s+(.+)\s*([\s\S]*?):::/m', function($m){
        $title = $m[1];
        $content = trim($m[2]);
        // 对内容进行递归处理，确保内部Markdown语法也能被正确解析
        $content_html = boxmoe_markdown_to_html($content);
        return '<details class="shiroki-collapse"><summary class="shiroki-collapse-title">' . $title . '</summary><div class="shiroki-collapse-content">' . $content_html . '</div></details>';
    }, $text);
    
    // 支持<details>和<summary>HTML标签
    $text = preg_replace_callback('/<details>\s*<summary>(.+?)<\/summary>\s*([\s\S]*?)<\/details>/i', function($m){
        $title = $m[1];
        $content = trim($m[2]);
        // 对内容进行递归处理，确保内部Markdown语法也能被正确解析
        $content_html = boxmoe_markdown_to_html($content);
        return '<details class="shiroki-collapse"><summary class="shiroki-collapse-title">' . $title . '</summary><div class="shiroki-collapse-content">' . $content_html . '</div></details>';
    }, $text);
    
    // 先处理段落，添加<p>标签
    $parts = preg_split('/\n\n+/', trim($text));
    foreach($parts as &$p){
        // 🎯 检查是否是代码块占位符，如果是则不添加<p>标签
        if(!preg_match('/^\s*<(h\d|ul|ol|pre|blockquote|img|a|table|audio|video)/i',$p) && 
           !preg_match('/^__MD_CODE_\d+__$/', $p) &&
           !preg_match('/^__MD_HTML_\d+__$/', $p) &&
           !preg_match('/^__MD_SAFE_BLOCK_\d+__$/', $p) &&
           !preg_match('/^__MD_CARD_\d+__$/', $p)){
            $p = '<p>'.$p.'</p>';
        }
    }
    $text = implode("\n", $parts);
    
    // 将卡片占位符替换回完整的HTML
    foreach($card_placeholders as $placeholder => $card_html){
        $text = str_replace($placeholder, $card_html, $text);
    }
    
    // 🛠️ 修复：将包裹在<p>标签中的卡片HTML提取出来，移除<p>标签
    $text = preg_replace('/<p>\s*(<a[^>]*class="[^"]*md-card-link-wrap[^"]*"[^>]*>[\s\S]*?<\/a>)\s*<\/p>/s', '$1', $text);
    
    // 处理 HTML 区块与代码块占位符
    $html = $text;
    $html = preg_replace('/<p>\s*(__MD_HTML_\d+__|__MD_SAFE_BLOCK_\d+__)\s*<\/p>/', '$1', $html);
    foreach($html_blocks as $k=>$v){
        $html = str_replace($k, $v, $html);
    }
    $html = boxmoe_restore_html_placeholders($html, $safe_html);
    foreach($blocks as $k=>$v){
        $html = str_replace($k,$v,$html);
    }
    
    // 🎯 修复：移除包裹在代码块HTML外的<p>标签
    $html = preg_replace('/<p>\s*(<pre class="prettyprint linenums.*?<\/pre>)\s*<\/p>/s', '$1', $html);
    // 移除包裹块级 HTML 区块的 <p> 标签
    $html = preg_replace('/<p>\s*(<(div|section|style|table|ul|ol|h[1-6]|form|iframe|video|audio|nav|header|footer|main|article|aside|details|figure|blockquote)\b)/i', '$1', $html);
    $html = preg_replace('/(<\/(?:div|section|style|table|ul|ol|h[1-6]|form|iframe|video|audio|nav|header|footer|main|article|aside|details|figure|blockquote)>)\s*<\/p>/i', '$1', $html);
    // 移除空段落，避免占位符隔离产生的多余间距
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);

    return $html;
}

function boxmoe_md_the_content($content){
    // 检查内容是否为密码保护表单，如果是则不进行Markdown转换
    if(strpos($content, 'password-protected-form') !== false || strpos($content, 'post_password') !== false){
        return $content;
    }
    // 只在前端显示时转换为HTML，后台编辑器中保持原始Markdown语法
    if(get_boxmoe('boxmoe_md_editor_switch') && !is_admin()){
        return boxmoe_markdown_to_html($content);
    }
    return $content;
}
// 调整执行Markdown转换优先级
add_filter('the_content', 'boxmoe_md_the_content', 2);

// 修复后台编辑器中的HTML实体问题
function boxmoe_fix_md_editor_content($content){
    if(get_boxmoe('boxmoe_md_editor_switch') && is_admin()){
        // 将HTML实体转换为原始字符，确保后台编辑器中显示正确的Markdown语法
        $content = str_replace('&gt;', '>', $content);
        $content = str_replace('&lt;', '<', $content);
        $content = str_replace('&quot;', '"', $content);
        $content = str_replace('&#039;', "'", $content);
    }
    return $content;
}
add_filter('content_edit_pre', 'boxmoe_fix_md_editor_content');
add_filter('the_editor_content', 'boxmoe_fix_md_editor_content');

if(get_boxmoe('boxmoe_md_editor_switch')){
    add_filter('use_block_editor_for_post', '__return_false');
    add_filter('user_can_richedit', '__return_false');
    // Markdown 模式下禁用 wpautop，避免已渲染的 HTML 被再次插入 <p>/<br>
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
    add_action('admin_enqueue_scripts', function($hook){
        if($hook==='post.php' || $hook==='post-new.php'){
            wp_enqueue_style('font-awesome', get_template_directory_uri().'/assets/css/font-awesome.min.css', [], '4.7.0');
            wp_enqueue_style('boxmoe-md-editor', get_template_directory_uri().'/assets/css/markdown-editor.css', ['font-awesome'], THEME_VERSION);
            wp_enqueue_script('boxmoe-md-editor', get_template_directory_uri().'/assets/js/markdown-editor.js', ['jquery'], THEME_VERSION, true);
            wp_localize_script('boxmoe-md-editor','BoxmoeMdEditor',[
                'enabled'=>true,
                'ajaxUrl'=>admin_url('admin-ajax.php'),
                'nonce'=>wp_create_nonce('boxmoe_md')
            ]);
        }
    });
    add_action('wp_ajax_boxmoe_md_preview', function(){
        if(!current_user_can('edit_posts')){wp_send_json_error(['message'=>'forbidden']);}
        if(!isset($_POST['nonce'])||!wp_verify_nonce($_POST['nonce'],'boxmoe_md')){wp_send_json_error(['message'=>'bad_nonce']);}
        $md = isset($_POST['markdown']) ? (string) wp_unslash($_POST['markdown']) : '';
        $html = boxmoe_markdown_to_html($md);
        $html = do_shortcode($html);
        wp_send_json_success(['html'=>$html]);
    });
}

// 📝 更新任务状态的AJAX处理函数
add_action('wp_ajax_update_task_status', 'boxmoe_update_task_status');
add_action('wp_ajax_nopriv_update_task_status', 'boxmoe_update_task_status_nopriv');

// 前端任务清单AJAX初始化
add_action('wp_enqueue_scripts', function(){
    // 只在单页文章和页面中加载任务清单脚本
    if(is_singular()){
        // 使用不同的对象名，避免覆盖ajax_object
        wp_localize_script('boxmoe-script', 'task_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('boxmoe_task_status')
        ));
    }
});

function boxmoe_update_task_status(){
    // 检查nonce
    if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'boxmoe_task_status')){
        wp_send_json_error(['message'=>'无效的nonce']);
    }
    
    if(!isset($_POST['post_id']) || !isset($_POST['task_content']) || !isset($_POST['current_status'])){
        wp_send_json_error(['message'=>'缺少必要参数']);
    }
    
    $post_id = intval($_POST['post_id']);
    $task_content = wp_unslash($_POST['task_content']);
    $current_status = $_POST['current_status'];
    
    // 获取当前用户ID
    $current_user_id = get_current_user_id();
    
    // 验证用户权限
    $post = get_post($post_id);
    if(!$post){
        wp_send_json_error(['message'=>'文章不存在']);
    }
    
    // 初始化编辑者数组，避免作用域问题
    $editors = array();
    
    // 检查用户是否有修改权限
    $is_allowed = false;
    
    // 检查WordPress内置权限（管理员、编辑、作者等）
    if(current_user_can('edit_post', $post_id)){
        $is_allowed = true;
    } else {
        // 检查用户是否是文章作者
        if($current_user_id === $post->post_author){
            $is_allowed = true;
        } else {
            // 检查用户是否是被授权的编辑者
            $editors = get_post_meta($post_id, '_boxmoe_post_editors', true);
            
            // 添加详细调试日志
            error_log('原始编辑者数据: ' . print_r($editors, true));
            
            $editors = is_array($editors) ? $editors : array();
            
            // 确保$current_user_id是整数
            $current_user_id = intval($current_user_id);
            
            // 将编辑者列表转换为整数数组
            $editors = array_map('intval', $editors);
            
            // 添加调试日志
            error_log('处理后的编辑者列表: ' . implode(', ', $editors));
            error_log('当前用户ID (整数): ' . $current_user_id);
            error_log('in_array返回值: ' . (in_array($current_user_id, $editors) ? 'true' : 'false'));
            
            $is_allowed = in_array($current_user_id, $editors);
        }
    }
    
    // 添加调试日志
    error_log('任务状态更新权限检查:');
    error_log('当前用户ID: ' . $current_user_id);
    error_log('文章作者ID: ' . $post->post_author);
    error_log('文章编辑者列表: ' . implode(', ', $editors));
    error_log('是否有权限: ' . ($is_allowed ? '是' : '否'));
    error_log('内置权限检查: ' . (current_user_can('edit_post', $post_id) ? '是' : '否'));
    
    // 只有有权限的用户才能修改任务状态
    if(!$is_allowed){
        wp_send_json_error(['message'=>'没有权限修改此任务']);
    }
    
    // 获取当前文章内容
    $post = get_post($post_id);
    if(!$post){
        wp_send_json_error(['message'=>'文章不存在']);
    }
    
    $content = $post->post_content;
    
    // 根据当前状态计算下一个状态
    // 状态循环：in-progress → pending → completed → in-progress
    // 对应语法：- [>] → - [ ] → - [x] → - [>]
    switch($current_status){
        case 'in-progress':
            $next_status = 'pending';
            $status_char = ' ';
            break;
        case 'pending':
            $next_status = 'completed';
            $status_char = 'x';
            break;
        case 'completed':
            $next_status = 'in-progress';
            $status_char = '>';
            break;
        default:
            $next_status = 'in-progress';
            $status_char = '>';
            break;
    }
    
    // 记录调试信息
    error_log('更新任务状态: post_id='.$post_id.', task_content='.$task_content.', current_status='.$current_status.', next_status='.$next_status.', status_char='.$status_char);
    error_log('原始文章内容前100字符: '.substr($content, 0, 100));
    
    // 记录完整的文章内容用于调试
    error_log('完整文章内容: '.str_replace('\n', '\\n', $content));
    
    // 当所有任务内容完全相同时，我们需要使用更智能的匹配策略
    // 首先将文章内容按行分割（使用双引号确保换行符被正确解释）
    $lines = explode("\n", $content);
    $updated = false;
    
    // 遍历每一行，查找需要更新的任务行
    for($i = 0; $i < count($lines); $i++){
        $line = $lines[$i];
        
        // 检查是否是任务行
        if(preg_match('/^-\s*\[( |x|>|&gt;)\]\s+(.*)$/', $line, $matches)){
            $current_status_char = $matches[1];
            $line_content = $matches[2];
            
            // 精确匹配任务内容（去除HTML实体影响）
            $clean_line_content = trim(str_replace('&gt;', '>', str_replace('&lt;', '<', $line_content)));
            $clean_task_content = trim($task_content);
            
            // 记录当前行的匹配信息
            error_log('检查行 ' . ($i+1) . ': "' . $line . '"');
            error_log('  当前状态字符: "' . $current_status_char . '"');
            error_log('  行内容(clean): "' . $clean_line_content . '"');
            error_log('  目标内容(clean): "' . $clean_task_content . '"');
            error_log('  内容匹配: ' . ($clean_line_content === $clean_task_content ? '是' : '否'));
            error_log('  当前状态: "' . $current_status . '"');
            
            // 匹配条件🔽
            // 任务内容完全匹配
            // 当前状态字符与请求的当前状态匹配
            $status_matched = false;
            if($current_status == 'pending' && $current_status_char == ' ') {
                $status_matched = true;
            } elseif($current_status == 'completed' && $current_status_char == 'x') {
                $status_matched = true;
            } elseif($current_status == 'in-progress' && ($current_status_char == '>' || $current_status_char == '&gt;')) {
                $status_matched = true;
            }
            
            error_log('  状态匹配: ' . ($status_matched ? '是' : '否'));
            
            if($clean_line_content === $clean_task_content && $status_matched){
                // 找到匹配的任务行，更新状态
                error_log('  找到匹配行，更新状态');
                
                // 替换该行的状态字符
                $new_line = preg_replace('/^(-\s*)\[( |x|>|&gt;)\]/', '$1['.$status_char.']', $line);
                $lines[$i] = $new_line;
                $updated = true;
                break; // 只更新第一个匹配的行，避免更新所有相同内容的行
            }
        }
    }
    
    // 如果找到并更新了任务行，重新组合文章内容（使用双引号确保换行符被正确解释）
    if($updated){
        $updated_content = implode("\n", $lines);
        error_log('找到并更新了匹配的任务行');
    } else {
        // 如果没有找到匹配的任务行，保持原内容不变
        $updated_content = $content;
        error_log('没有找到匹配的任务行');
    }
    
    // 确保所有HTML实体都被转换为原始字符
    $updated_content = str_replace('&gt;', '>', $updated_content);
    $updated_content = str_replace('&lt;', '<', $updated_content);
    
    // 添加调试日志，查看最终更新后的内容
    error_log('最终更新后的内容片段: ' . substr($updated_content, 0, 200));
    $updated_content = str_replace('&quot;', '"', $updated_content);
    $updated_content = str_replace('&#039;', "'", $updated_content);
    
    // 记录替换结果
    error_log('替换结果: '.($updated_content === $content ? '未找到匹配的任务' : '成功更新任务状态'));
    
    // 更新文章
    error_log('调用wp_update_post前: post_id='.$post_id.', updated_content前100字符: '.substr($updated_content, 0, 100));
    
    $result = wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content
    ]);
    
    error_log('wp_update_post结果: '.($result === 0 ? '没有更新' : ($result === false ? '更新失败' : '更新成功，post_id='.$result)));
    
    if(is_wp_error($result)){
        error_log('wp_update_post错误: '. $result->get_error_message());
        wp_send_json_error(['message'=>'更新任务状态失败: '. $result->get_error_message()]);
    }
    
    if($result === 0){
        // 没有更新，可能是因为内容没有变化
        error_log('wp_update_post没有更新，可能是因为内容没有变化');
        wp_send_json_success(['message'=>'任务状态没有变化']);
    }
    
    if($result === false){
        // 更新失败
        error_log('wp_update_post更新失败，原因未知');
        wp_send_json_error(['message'=>'更新任务状态失败']);
    }
    
    // 更新成功，返回新状态
    error_log('任务状态更新成功，返回的post_id='.$result);
    wp_send_json_success([
        'message'=>'更新任务状态成功',
        'new_status' => $next_status
    ]);
}

function boxmoe_update_task_status_nopriv(){
    wp_send_json_error(['message'=>'请先登录']);
}