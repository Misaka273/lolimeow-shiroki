<?php
if(!defined('ABSPATH')){exit;}

function boxmoe_fonts_sanitize_name($name){
    return str_replace(array('"', "'"), '', trim((string) $name));
}

function boxmoe_fonts_get_faces(){
    if(!get_boxmoe('boxmoe_custom_font_switch')){
        return array();
    }
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){
        return array();
    }
    $faces = array();
    foreach($fonts as $f){
        $name = isset($f['name']) ? boxmoe_fonts_sanitize_name($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){
            $src = trim($f['woff2']);
        } elseif(!empty($f['url'])){
            $src = trim($f['url']);
        }
        if($name && $src){
            $faces[] = array(
                'name' => $name,
                'src' => $src
            );
        }
    }
    return $faces;
}

function boxmoe_fonts_get_applied_names(){
    $names = array();
    $default = boxmoe_fonts_sanitize_name(get_boxmoe('boxmoe_default_font'));
    $welcome = boxmoe_fonts_sanitize_name(get_boxmoe('boxmoe_welcome_font'));
    if($default && $default !== 'default'){
        $names[] = $default;
    }
    if($welcome && $welcome !== 'default'){
        $names[] = $welcome;
    }
    return array_values(array_unique($names));
}

function boxmoe_fonts_get_gate_faces(){
    $faces = boxmoe_fonts_get_faces();
    if(empty($faces)){
        return array();
    }
    $applied = boxmoe_fonts_get_applied_names();
    if(empty($applied)){
        return $faces;
    }
    $gate = array();
    foreach($faces as $face){
        if(in_array($face['name'], $applied, true)){
            $gate[] = $face;
        }
    }
    return $gate;
}

function boxmoe_fonts_should_gate(){
    if(is_admin()){
        return false;
    }
    return !empty(boxmoe_fonts_get_gate_faces());
}

function boxmoe_fonts_build_face_css($faces, $display = 'swap'){
    $css = '';
    $display = ($display === 'block') ? 'block' : 'swap';
    foreach($faces as $face){
        $css .= "@font-face{font-family:'".esc_attr($face['name'])."';src:url(".esc_url($face['src']).") format('woff2');font-display:".$display.";}";
    }
    return $css;
}

function boxmoe_fonts_build_apply_css($important = false){
    if(!get_boxmoe('boxmoe_custom_font_switch')){
        return '';
    }
    $css = '';
    $bang = $important ? ' !important' : '';
    $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
    $default = boxmoe_fonts_sanitize_name(get_boxmoe('boxmoe_default_font'));
    if($default && $default !== 'default'){
        $css .= "body{font-family:'".esc_attr($default)."',".$fallback.$bang.";}";
    }
    $welcome = boxmoe_fonts_sanitize_name(get_boxmoe('boxmoe_welcome_font'));
    if($welcome && $welcome !== 'default'){
        $css .= ".boxmoe-typing-animation{font-family:'".esc_attr($welcome)."',".$fallback.$bang.";}";
    }
    return $css;
}

function boxmoe_fonts_html_class($output){
    // ⏳ 首屏即隐藏，避免系统字体先闪再替换
    if(!boxmoe_fonts_should_gate()){
        return $output;
    }
    if(strpos($output, 'class=') !== false){
        $output = preg_replace('/class="([^"]*)"/', 'class="$1 fonts-loading"', $output, 1);
    } else {
        $output .= ' class="fonts-loading"';
    }
    if(strpos($output, 'style=') === false){
        $output .= ' style="visibility:hidden"';
    }
    return $output;
}
add_filter('language_attributes', 'boxmoe_fonts_html_class');

function boxmoe_fonts_early_output(){
    // 🚀 预加载字体并等就绪后再挂载页面
    static $printed = false;
    if($printed || !boxmoe_fonts_should_gate()){
        return;
    }
    $printed = true;

    $faces = boxmoe_fonts_get_faces();
    $gate_faces = boxmoe_fonts_get_gate_faces();
    $gate_names = array();
    foreach($gate_faces as $face){
        $gate_names[] = $face['name'];
    }

    $first = true;
    foreach($gate_faces as $face){
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin="anonymous"%s>' . "\n",
            esc_url($face['src']),
            $first ? ' fetchpriority="high"' : ''
        );
        $first = false;
    }

    $css = boxmoe_fonts_build_face_css($faces, 'block');
    $css .= boxmoe_fonts_build_apply_css();
    $css .= 'html.fonts-loading{visibility:hidden;}html.fonts-ready{visibility:visible;}';

    echo '<style id="shiroki-fonts-critical">' . $css . '</style>' . "\n";
    echo '<noscript><style>html.fonts-loading{visibility:visible}html[style*="visibility:hidden"]{visibility:visible}</style></noscript>' . "\n";

    $families_json = wp_json_encode(array_values($gate_names));
    echo '<script id="shiroki-fonts-gate">';
    echo '(function(){';
    echo 'var html=document.documentElement;';
    echo 'if(html.classList.contains("fonts-ready")){html.style.visibility="";return;}';
    echo 'html.classList.add("fonts-loading");';
    echo 'var families=' . $families_json . ';';
    echo 'var done=false;';
    echo 'function reveal(){if(done)return;done=true;html.classList.remove("fonts-loading");html.classList.add("fonts-ready");html.style.visibility="";}';
    echo 'setTimeout(reveal,3000);';
    echo 'if(!families||!families.length||!document.fonts||!document.fonts.load){reveal();return;}';
    echo 'var ready=true;';
    echo 'try{for(var i=0;i<families.length;i++){if(!document.fonts.check("16px "+JSON.stringify(families[i]))){ready=false;break;}}}catch(e){ready=false;}';
    echo 'if(ready){reveal();return;}';
    echo 'var loads=[];';
    echo 'for(var j=0;j<families.length;j++){loads.push(document.fonts.load("16px "+JSON.stringify(families[j])));}';
    echo 'Promise.all(loads).then(reveal).catch(reveal);';
    echo '})();';
    echo '</script>' . "\n";
}
add_action('wp_head', 'boxmoe_fonts_early_output', 0);

function boxmoe_fonts_inline_style(){
    $faces = boxmoe_fonts_get_faces();
    if(empty($faces)){
        return;
    }
    $display = boxmoe_fonts_should_gate() ? 'block' : 'swap';
    $css = boxmoe_fonts_build_face_css($faces, $display);
    $css .= boxmoe_fonts_build_apply_css();
    if($css){
        wp_add_inline_style('boxmoe-style', $css);
    }
}
add_action('wp_enqueue_scripts','boxmoe_fonts_inline_style',13);

function boxmoe_admin_font_sync(){
    if(!get_boxmoe('boxmoe_custom_font_switch') || !get_boxmoe('boxmoe_admin_font_sync')){
        return;
    }

    $default = trim((string) get_boxmoe('boxmoe_default_font'));
    $fonts = get_boxmoe('boxmoe_fonts');
    if(empty($default) || $default === 'default' || !is_array($fonts) || empty($fonts)){
        return;
    }

    $font_src = '';
    foreach($fonts as $font){
        $name = isset($font['name']) ? trim($font['name']) : '';
        if($name !== $default){
            continue;
        }

        if(!empty($font['woff2'])){
            $font_src = trim($font['woff2']);
        } elseif(!empty($font['url'])){
            $font_src = trim($font['url']);
        }
        break;
    }

    if(empty($font_src)){
        return;
    }

    $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
    $css = "@font-face{font-family:'".esc_attr($default)."';src:url(".esc_url($font_src).") format('woff2');font-display:swap;}";
    $css .= "body.wp-admin,body.wp-admin #wpbody,body.wp-admin .wrap,body.wp-admin #wpadminbar{font-family:'".esc_attr($default)."',".$fallback.";}";

    wp_register_style('boxmoe-admin-fonts', false, array(), defined('THEME_VERSION') ? THEME_VERSION : null);
    wp_enqueue_style('boxmoe-admin-fonts');
    wp_add_inline_style('boxmoe-admin-fonts', $css);
}
add_action('admin_enqueue_scripts','boxmoe_admin_font_sync',20);

function boxmoe_fonts_theme_json($theme_json){
    if(!get_boxmoe('boxmoe_custom_font_switch')){return $theme_json;}
    $fonts_cfg = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts_cfg)){return $theme_json;}
    $fonts = array();
    foreach($fonts_cfg as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $slug = sanitize_title($name);
            $fonts[] = array(
                'name' => $name,
                'slug' => $slug,
                'fontFamily' => '"'.$name.'"',
                'fontFace' => array(
                    array(
                        'fontFamily' => $name,
                        'fontStyle' => 'normal',
                        'fontWeight' => '400',
                        'src' => array($src)
                    )
                )
            );
        }
    }
    if($fonts){
        $data = array(
            'settings' => array(
                'typography' => array(
                    'fontFamilies' => array(
                        'theme' => $fonts
                    )
                )
            )
        );
        $theme_json->update_with($data);
    }
    return $theme_json;
}
add_filter('wp_theme_json_data_theme','boxmoe_fonts_theme_json',10,1);

function boxmoe_fonts_editor_assets(){
    if(!get_boxmoe('boxmoe_custom_font_switch')){return;}
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){return;}
    $css = '';
    foreach($fonts as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";
        }
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $css .= "body{font-family:'".esc_attr($default)."',".$fallback.";}";
    }
    if($css){
        wp_add_inline_style('wp-block-library', $css);
    }
}
add_action('enqueue_block_editor_assets','boxmoe_fonts_editor_assets');

function boxmoe_tinymce_config($init){
    if(!get_boxmoe('boxmoe_custom_font_switch')){ return $init; }
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){ return $init; }
    $custom_formats = array();
    $css = '';
    foreach($fonts as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $custom_formats[] = $name.'='.$name;
            $css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";    
        }
    }
    if(!empty($custom_formats)){
        $existing = isset($init['font_formats']) ? trim($init['font_formats']) : '';
        if(!$existing){ $existing = boxmoe_tinymce_default_font_formats(); }
        $init['font_formats'] = implode(';', $custom_formats) . ';' . $existing;
        $init['content_style'] = ( isset($init['content_style']) ? $init['content_style'] : '' ) . $css;
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $init['content_style'] = ( isset($init['content_style']) ? $init['content_style'] : '' ) . "body{font-family:'".esc_attr($default)."',".$fallback.";}";
    }
    return $init;
}
add_filter('tiny_mce_before_init','boxmoe_tinymce_config');

function boxmoe_tinymce_buttons($buttons){
    if(!in_array('fontselect', $buttons)){
        array_unshift($buttons, 'fontselect');
    }
    return $buttons;
}
add_filter('mce_buttons','boxmoe_tinymce_buttons');

function boxmoe_tinymce_default_font_formats(){
    return 'Andale Mono=andale mono,monospace;'
        .'Arial=arial,helvetica,sans-serif;'
        .'Arial Black=arial black,avant garde;'
        .'Book Antiqua=book antiqua,palatino;'
        .'Comic Sans MS=comic sans ms;'
        .'Courier New=courier new,courier;'
        .'Georgia=georgia;'
        .'Helvetica=helvetica;'
        .'Impact=impact,chicago;'
        .'Symbol=symbol;'
        .'Tahoma=tahoma,arial,helvetica,sans-serif;'
        .'Terminal=terminal,monaco;'
        .'Times New Roman=times new roman,times;'
        .'Trebuchet MS=trebuchet ms,geneva;'
        .'Verdana=verdana,geneva;'
        .'Webdings=webdings;'
        .'Wingdings=wingdings,zapf dingbats';
}

function boxmoe_fonts_table_render($option_name, $value, $val){
    $rows = is_array($val) ? $val : array();
    $html = '<div class="boxmoe-fonts-table" data-option-name="'.esc_attr($option_name).'">';
    $html .= '<div class="fonts-table-header"><span>显示名称</span><span>woff2 上传</span><span>woff2 链接</span><span>操作</span></div>';
    
    $idx = 0;
    foreach($rows as $row){
        $name = isset($row['name']) ? $row['name'] : '';
        $woff2 = isset($row['woff2']) ? $row['woff2'] : '';
        $url = isset($row['url']) ? $row['url'] : '';
        $hidden = '<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][name]" value="'.esc_attr($name).'" />'
                .'<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][woff2]" value="'.esc_attr($woff2).'" />'
                .'<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][url]" value="'.esc_attr($url).'" />';
        $actions = '<div class="fonts-actions">'
            .'<button type="button" class="btn-pill btn-blue boxmoe-font-edit" data-index="'.$idx.'">修改</button>'
            .'<button type="button" class="btn-pill btn-red boxmoe-font-delete-row" data-index="'.$idx.'">删除</button>'
            .'</div>';
        $html .= '<div class="fonts-table-row" data-index="'.$idx.'">'
            .'<div class="cell cell-name"><span class="cell-text">'.esc_html($name ?: '未设置').'</span>'.$hidden.'</div>'
            .'<div class="cell cell-upload"><span class="cell-text">'.esc_html($woff2 ?: '未设置').'</span></div>'
            .'<div class="cell cell-url"><span class="cell-text">'.esc_html($url ?: '未设置').'</span></div>'
            .'<div class="cell cell-actions">'.$actions.'</div>'
            .'</div>';
        $idx++;
    }
    $html .= '</div>';
    // Modal
    $html .= '<div id="boxmoe-fonts-modal-mask" class="of-modal-mask" style="display:none">'
        .'<div id="boxmoe-fonts-modal" class="of-modal">'
        .'<div class="of-modal-header">新增/编辑自定义字体</div>'
        .'<div class="of-modal-body">'
        .'<label>显示名称</label><input type="text" id="bmf-name" class="of-input" placeholder="例如：My Sans">'
        .'<label style="display:block;margin-top:8px">上传 woff2</label>'
        .'<input type="text" id="bmf-woff2" class="upload of-input" placeholder="没有选择文件" />'
        .'<input type="button" id="bmf-upload-btn" class="upload-button button" value="上传" />'
        .'<div class="screenshot" id="bmf-woff2-image"></div>'
        .'<label style="display:block;margin-top:8px">自定义 woff2 链接</label>'
        .'<input type="text" id="bmf-url" class="of-input" placeholder="https://...">'
        .'<div id="bmf-hint" class="form-hint">上传或链接二选一，至少填写其一</div>'
        .'<div id="bmf-error" class="form-error" style="display:none"></div>'
        .'</div>'
        .'<div class="of-modal-actions">'
        .'<button type="button" id="bmf-cancel" class="of-btn of-btn-secondary">取消</button>'
        .'<button type="button" id="bmf-save" class="of-btn of-btn-primary">保存</button>'
        .'</div>'
        .'</div>'
        .'</div>';
    return $html;
}
add_filter('optionsframework_fonts_table','boxmoe_fonts_table_render',10,3);
