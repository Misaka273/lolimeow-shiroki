<?php
// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

//H2设置标
add_shortcode('h2set', 'h2set_shortcode');  
function h2set_shortcode( $attr , $content = '') {             
    $out ='<h2 class="section-title"><span><i class="fa fa-paint-brush"></i>'.$content.'</span></h2>';  
    return $out;  
} 

//H2下载标
add_shortcode('h2down', 'h2down_shortcode');  
function h2down_shortcode( $attr , $content = '') {             
    $out ='<h2 class="section-title"><span><i class="fa fa-cloud-download"></i>'.$content.'</span></h2>';  
    return $out;  
}

//下载按钮
add_shortcode('downloadbtn', 'downloadbtn_shortcode');  
function downloadbtn_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'link' => '' ), $attr );
    $link = isset( $atts['link'] ) ? $atts['link'] : '';
    $out = '<a href="'.esc_url( $link ).'" rel="noopener" target="_blank" class="download_btn btn-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="该资源来源于网络如有侵权,请联系删除." data-container="body" data-animation="true">'.$content.'</a>';
    return $out;
}

//链接按钮
add_shortcode('linksbtn', 'linksbtn_shortcode');  
function linksbtn_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'link' => '' ), $attr );
    $link = isset( $atts['link'] ) ? $atts['link'] : '';
    $out = '<a href="'.esc_url( $link ).'" rel="noopener" target="_blank" class="links_btn">'.$content.'</a>';
    return $out;
}

//blockquote1
add_shortcode('blockquote1', 'blockquote1_shortcode');  
function blockquote1_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'name' => '' ), $attr );
    $name = isset( $atts['name'] ) ? $atts['name'] : '';
    $out = '<div class="quote"><blockquote><p>'.$content.'</p><cite>'.esc_attr( $name ).'</cite></blockquote></div>';
    return $out;
}

//blockquote2
add_shortcode('blockquote2', 'blockquote2_shortcode');  
function blockquote2_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'name' => '' ), $attr );
    $name = isset( $atts['name'] ) ? $atts['name'] : '';
    $out = '<div class="animated-border-quote"><blockquote><p>'.$content.'</p><cite>'.esc_attr( $name ).'</cite></blockquote></div>';
    return $out;
}

//OL列表
add_shortcode( 'listol', 'listol_shortcode' );
function listol_shortcode( $atts, $content='' ) {
    extract( shortcode_atts( array('type' => '0'), $atts ) );
    $lists = explode("\n", $content);
    $output = null;
    foreach($lists as $li){
        if(trim($li) != '') {
            $output .= "<li>{$li}</li>\n";
        }
    }
    $outputs = "<ol class='ol'>\n".$output."</ol>\n";
    return $outputs;
}

//飞进来模块
add_shortcode('rollin', 'rollin_shortcode');  
function rollin_shortcode( $attr , $content = '') {             
    $out ='<div class="link-title wow rollIn">'.$content.'</div>';  
    return $out;  
}

//药丸
add_shortcode('yaowan', 'yaowan_shortcode');  
function yaowan_shortcode( $atts , $content = '') {
    $style = isset($atts['style']) ? intval($atts['style']) : 0;
    $map = array(
        1 => 'badge text-bg-primary',
        2 => 'badge text-bg-secondary',
        3 => 'badge text-bg-info',
        4 => 'badge text-bg-success',
        5 => 'badge text-bg-danger',
        6 => 'badge text-bg-warning',
        7 => 'badge text-bg-light',
        8 => 'badge text-bg-dark',
        9 => 'badge text-bg-primary bg-gradient',
        10 => 'badge text-bg-secondary bg-gradient',
        11 => 'badge text-bg-info bg-gradient',
        12 => 'badge text-bg-success bg-gradient',
        13 => 'badge text-bg-danger bg-gradient',
        14 => 'badge text-bg-warning bg-gradient',
        15 => 'badge text-bg-light bg-gradient',
        16 => 'badge text-bg-dark bg-gradient'
    );
    $classes = isset($map[$style]) ? $map[$style] : 'badge text-bg-dark';
    return '<span class="'.$classes.' mb-1 mt-1">'.wp_kses_post($content).'</span>';
}

/* 🛡️ 代码高亮短代码 - 自动转义 Markdown 特殊字符 */
add_shortcode('precode', 'precode_shortcode');
function precode_shortcode( $attr , $content = '' ) {
    $defaults = array(
        'linenums' => '1',
        'lang'     => '',
    );
    $attr = shortcode_atts( $defaults, $attr );
    $code = (string) $content;
    $code = shortcode_unautop( $code );
    $code = str_replace(array("\r\n","\r"), "\n", $code);
    $code = preg_replace( '~<br\s*/?>~i', "\n", $code );
    $code = preg_replace( '~<(p|div|section|article)[^>]*>~i', '', $code );
    $code = preg_replace( '~</(p|div|section|article)>~i', "\n", $code );
    $code = preg_replace( '~<span[^>]*>~i', '', $code );
    $code = preg_replace( '~</span>~i', '', $code );
    $code = preg_replace( "/\n{2,}/", "\n", $code );
    $code = trim( $code );

    /* 🔒 转义 Markdown 特殊字符，防止被解析 */
    $markdown_chars = array(
        '&#35;' => '#',   /* ◀️ 井号 - 标题 */
        '&#42;' => '*',   /* ◀️ 星号 - 粗体/斜体/列表 */
        '&#95;' => '_',   /* ◀️ 下划线 - 粗体/斜体 */
        '&#96;' => '`',   /* ◀️ 反引号 - 行内代码 */
        '&#126;' => '~',  /* ◀️ 波浪号 - 删除线 */
        '&#91;' => '[',   /* ◀️ 左方括号 - 链接/图片 */
        '&#93;' => ']',   /* ◀️ 右方括号 - 链接/图片 */
        '&#33;' => '!',   /* ◀️ 感叹号 - 图片 */
        '&#40;' => '(',   /* ◀️ 左圆括号 */
        '&#41;' => ')',   /* ◀️ 右圆括号 */
        '&#62;' => '>',   /* ◀️ 大于号 - 引用 */
        '&#43;' => '+',   /* ◀️ 加号 - 列表 */
        '&#45;' => '-',   /* ◀️ 减号 - 列表/分隔线 */
        '&#124;' => '|',  /* ◀️ 竖线 - 表格 */
    );
    $code = str_replace( array_values( $markdown_chars ), array_keys( $markdown_chars ), $code );

    $classes = array( 'prettyprint' );
    if ( $attr['linenums'] === '1' || $attr['linenums'] === 'true' ) {
        $classes[] = 'linenums';
    }
    if ( ! empty( $attr['lang'] ) ) {
        $classes[] = 'lang-' . preg_replace( '/[^\w\-\.]/', '', $attr['lang'] );
    }
    $out = '<pre class="' . esc_attr( implode( ' ', $classes ) ) . '"><code>' . esc_html( $code ) . '</code></pre>';
    return $out;
}

add_filter( 'no_texturize_shortcodes', function( $shortcodes ) {
    $shortcodes[] = 'precode';
    return $shortcodes;
});

//Iframe
add_shortcode('iframe', 'iframe_shortcode');  
function iframe_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'link' => '' ), $attr );
    $link = isset( $atts['link'] ) ? $atts['link'] : '';
    $out = '<a href="javascript:;" data-fancybox data-type="iframe" data-src="'.esc_url( $link ).'">'.$content.'</a>';
    return $out;
}

//警告框
add_shortcode('alert', 'alert_shortcode');  
function alert_shortcode( $atts , $content = '') {
	extract( shortcode_atts( array('style' => '0'), $atts ) );
	$out = '';
	if($style=='1'){
        $out = '<div class="alert alert-primary" role="alert">'.$content.'</div>';
    }else 
	if($style=='2'){
		$out = '<div class="alert alert-secondary" role="alert">'.$content.'</div>';
	}else
	if($style=='3'){
		$out = '<div class="alert alert-info" role="alert">'.$content.'</div>';
	}else
	if($style=='4'){
		$out = '<div class="alert alert-success" role="alert">'.$content.'</div>';
	}else
	if($style=='5'){
		$out = '<div class="alert alert-danger" role="alert">'.$content.'</div>';
	}else
	if($style=='6'){
		$out = '<div class="alert alert-warning" role="alert">'.$content.'</div>';
	}else	
	if($style=='7'){
		$out = '<div class="alert alert-light" role="alert">'.$content.'</div>';
	}else
	if($style=='8'){
		$out = '<div class="alert alert-dark" role="alert">'.$content.'</div>';
	}else{
		$out = '<div class="alert alert-dark" role="alert">'.$content.'</div>';	
	}	
    return $out;  
}
//文章密码保护
add_shortcode('pwd_protected_post','password_protected_post');
function password_protected_post($atts, $content=null){
    extract(shortcode_atts(array('key'=>null), $atts));
    if(isset($_POST['password_key']) && $_POST['password_key']==$key){
        return '
		    <div class="alert alert-default" role="alert"><strong>温馨提示！</strong>以下是密码保护的内容！</div> 
			<div class="password_protected_post_content">'.$content.'</div>
		';
    }elseif(isset($_POST['password_key']) && $_POST['password_key']!=$key){
        return '
			<script>
				alert("密码错误，请仔细核对密码后重试！！！");
				window.location.href="'.get_permalink().'";
			</script>
		';
	
	}else{
        return '

		    <div class="alert alert-warning alert-dismissible fade show" role="alert">
		    <strong>注意！</strong>以下部分内容需要输入密码后才能查看！

		    </div>
		    <div class="row justify-content-center align-items-center">
            <div class="col-md-6">		
			<form class="mt20" action="'.get_permalink().'" method="post">
			<div class="input-group mb-3">
			<input type="password" id="password_key" name="password_key" class="form-control" placeholder="请输入密码查看隐藏内容" aria-label="请输入密码查看隐藏内容" aria-describedby="button-password_key">
			<button class="btn btn-outline-primary mb-0" type="submit" id="button-password_key">确  定</button>
			</div>
			</form>
			</div>
			</div>
		';
    }
}
//音频
add_shortcode('audio', 'audio_shortcode');  
function audio_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array( 'link' => '' ), $attr );
    $link = isset( $atts['link'] ) ? $atts['link'] : '';
    $out = '<audio preload="none" controls="controls"><source type="audio/mpeg" src="'.esc_url( $link ).'"></audio>';
    return $out;
}
add_shortcode('video', 'video_shortcode');  
function video_shortcode( $attr , $content = ' ' ) {
    $atts = shortcode_atts( array(
        'link' => '',
        'mp4' => '',
        'src' => '',
        'url' => '',
        'poster' => '',
        'autoplay' => '0',
        'loop' => '0',
        'muted' => '0',
        'playsinline' => '1',
        'width' => '',
        'height' => ''
    ), $attr );

    $candidates = array($atts['link'], $atts['mp4'], $atts['src'], $atts['url']);
    $link = '';
    foreach ($candidates as $cand) {
        $cand = is_string($cand) ? trim($cand) : '';
        if ($cand !== '') { $link = $cand; break; }
    }
    $link = trim($link, " \t\n\r\0\x0B`\"'" );

    if ($link === '') {
        $raw = trim($content);
        if (stripos($raw, '<iframe') !== false) {
            return $raw;
        }
        if (preg_match('/href=["\']([^"\']+)["\']/', $raw, $m)) {
            $link = trim($m[1]);
        } else {
            $link = trim(strip_tags($raw));
        }
        $link = trim($link, " \t\n\r\0\x0B`\"'");
    }
    if ($link === '') return '';

    $lower = strtolower($link);
    $is_youtube = (bool) preg_match('#(youtube\.com|youtu\.be)#i', $lower);
    $is_bilibili = (bool) preg_match('#bilibili\.com/video/#i', $lower);
    $is_mp4 = ($atts['mp4'] !== '' ) || (bool) preg_match('/\.mp4(\?.*)?$/i', $lower);

    if ($is_youtube) {
        $video_id = '';
        if (preg_match('#youtu\.be/([\w-]+)#i', $link, $m)) {
            $video_id = $m[1];
        } elseif (preg_match('#v=([\w-]+)#i', $link, $m)) {
            $video_id = $m[1];
        } elseif (preg_match('#/embed/([\w-]+)#i', $link, $m)) {
            $video_id = $m[1];
        }
        if ($video_id !== '') {
            $src = 'https://www.youtube.com/embed/' . esc_attr($video_id);
            return '<div class="ratio ratio-16x9"><iframe src="'.esc_url($src).'" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
        } else {
            return '<div class="ratio ratio-16x9"><iframe src="'.esc_url($link).'" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
        }
    }

    if ($is_bilibili) {
        $bvid = '';
        if (preg_match('#/video/(BV[\w]+)#i', $link, $m)) {
            $bvid = $m[1];
        }
        if ($bvid !== '') {
            $src = 'https://player.bilibili.com/player.html?bvid=' . esc_attr($bvid) . '&page=1';
            return '<div class="ratio ratio-16x9"><iframe src="'.esc_url($src).'" allow="autoplay; fullscreen" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
        } else {
            return '<div class="ratio ratio-16x9"><iframe src="'.esc_url($link).'" allow="autoplay; fullscreen" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
        }
    }

    $attrs = array();
    $attrs[] = 'preload="metadata"';
    $attrs[] = 'controls';
    if ($atts['playsinline'] === '1') {
        $attrs[] = 'playsinline';
        $attrs[] = 'webkit-playsinline';
    }
    if ($atts['autoplay'] === '1') {
        $attrs[] = 'autoplay';
    }
    if ($atts['loop'] === '1') {
        $attrs[] = 'loop';
    }
    if ($atts['muted'] === '1') {
        $attrs[] = 'muted';
    }
    if (!empty($atts['poster'])) {
        $attrs[] = 'poster="' . esc_url($atts['poster']) . '"';
    }

    $size = '';
    $w = is_string($atts['width']) ? preg_replace('/[^0-9\.]/', '', $atts['width']) : '';
    $h = is_string($atts['height']) ? preg_replace('/[^0-9\.]/', '', $atts['height']) : '';
    $wh_attr = '';
    if ($w !== '') { $wh_attr .= ' width="' . esc_attr($w) . '"'; }
    if ($h !== '') { $wh_attr .= ' height="' . esc_attr($h) . '"'; }
    if ($w !== '' || $h !== '') { $size = ' style="' . ($w !== '' ? 'width:' . esc_attr($w) . 'px;' : '') . ($h !== '' ? 'height:' . esc_attr($h) . 'px;' : '') . '"'; }

    $video_open = '<video ' . implode(' ', $attrs) . ' class="post-video"' . $wh_attr . $size . '>';
    $source_type = 'video/mp4';
    $source = '<source src="' . esc_url($link) . '" type="' . esc_attr($source_type) . '">';
    $fallback = esc_html__('您的浏览器不支持 HTML5 视频播放。', 'boxmoe');
    return $video_open . $source . $fallback . '</video>';
}

//会员查看内容
function login_to_read($atts, $content=null) {
	extract(shortcode_atts(array("notice" => '
	<div class="alerts error"><strong>该段内容只有登录才可以查看</strong></div>'), $atts));
	if ( is_user_logged_in() && !is_null( $content ) && !is_feed() )
				return $content;
		return $notice;
	}
	add_shortcode('userreading', 'login_to_read');

// 📦 折叠内容短代码
add_shortcode('shiroki_collapse', 'shiroki_collapse_shortcode');
function shiroki_collapse_shortcode( $atts , $content = '' ) {
    $atts = shortcode_atts( array(
        'title' => '折叠内容',
        'open' => '0'
    ), $atts );
    $title = isset( $atts['title'] ) ? $atts['title'] : '折叠内容';
    $open = isset( $atts['open'] ) ? ( $atts['open'] === '1' || $atts['open'] === 'true' ) : false;
    $open_attr = $open ? ' open' : '';
    return '<details class="shiroki-collapse"' . $open_attr . '><summary class="shiroki-collapse-title">' . $title . '</summary><div class="shiroki-collapse-content">' . do_shortcode( $content ) . '</div></details>';
}
	


// 可视化编辑器添加下拉式按钮
function boxmoe_select(){
echo '
<select id="short_code_select">
    <option value="短代码选择！">Boxmoe短代码</option>
	<option value="[h2set]内容[/h2set]">H2设置标</option>	
	<option value="[h2down]内容[/h2down]">H2下载标</option>
	<option value="[downloadbtn link=\'链接\']按钮名称[/downloadbtn]">下载按钮</option>
	<option value="[linksbtn link=\'链接\']按钮名称[/linksbtn]">链接按钮</option>
	<option value="[blockquote1 name=\'签名\']内容[/blockquote1]">引用模块1</option>
	<option value="[blockquote2 name=\'签名\']内容[/blockquote2]">引用模块2</option>
	<option value="[listol]每行一条内容[/listol]">OL列表</option>
	<option value="[yaowan style=\'输入数字1-16共16个模式颜色\']内容[/yaowan]">药丸模块</option>
	<option value="[alert style=\'输入数字1-7共7个模式颜色\']内容[/alert]">警告框模块</option>
	<option value="[precode]内容[/precode]">代码高亮</option>
	<option value="[iframe link=\'链接\']内容[/iframe]">Iframe</option>
	<option value="[userreading]隐藏内容[/userreading]">登录查看一</option>
	<option value="[userreading notice=\'未登录时候显示的内容\']隐藏内容[/userreading]">登录查看二</option>
	<option value="[pwd_protected_post key=\'保护密码\']文章密码保护内容[/pwd_protected_post]">文章密码保护</option>
	<option value="[audio link=\'音频链接\'][/audio]">插入音频</option>
	<option value="[video link=\'视频链接\'][/video]">插入视频</option>
	<option value="[shiroki_collapse title=\'折叠内容\']内容[/shiroki_collapse]">折叠内容</option>
	<option value="[shiroki_collapse title=\'折叠内容\' open=\'1\']内容[/shiroki_collapse]">折叠内容（默认展开）</option>
	<option value="<!--nextpage-->">文章分页</option>
	<option value="<div class=\'timeline timeline-one-side\' data-timeline-content=\'axis\' data-timeline-axis-style=\'dashed\'>
<div class=\'timeline-block\'>
<span class=\'timeline-step badge-success\'>
<i class=\'fa fa-bell\'></i>
</span>
<div class=\'timeline-content\'>
<small class=\'text-muted font-weight-bold\'>2021年1月1日</small
<h5 class=\' mt-3 mb-0\'>主题</h5>
<p class=\' text-sm mt-1 mb-0\'>内容段</p>
</div>
</div>
<!--时间段时间开始-->
<div class=\'timeline-block\'>
<span class=\'timeline-step badge-success\'>
<i class=\'fa fa-clock-o\'></i>
</span>
<div class=\'timeline-content\'>
<small class=\'text-muted font-weight-bold\'>2021年1月1日</small
<h5 class=\' mt-3 mb-0\'>主题</h5>
<p class=\' text-sm mt-1 mb-0\'>内容段</p>
</div>
</div>
<!--时间段时间结束，此段可无限复制往下排列-->


<!--以上时间段区--></div>">时间线1(切换文本代码编辑)</option>
	<option value="<ul class=\'timelines\'>
<!--时间段时间开始-->
  <li class=\'timeline-event\'>
    <label class=\'timeline-event-icon\'></label>
    <div class=\'timeline-event-copy\'>
      <p class=\'timeline-event-thumbnail\'>2020/03/05</p>
      <h3>h3标题</h3>
      <h4>H4标题2</h4>
      <p><strong>加粗小标题</strong><br>内容</p>
    </div>
  </li>
 <!--时间段时间结束，此段可无限复制往下排列--> 
</ul>">时间线2(切换文本代码编辑)</option>
</select>';
}
if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
	add_action('media_buttons', 'boxmoe_select', 11);
}
//ctlr+enter回复
function boxmoe_button() {
echo '<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#short_code_select").change(function(){
			send_to_editor(jQuery("#short_code_select :selected").val());
			return false;
		});
	});
</script>';
}
add_action('admin_head', 'boxmoe_button');

// HTML模式短代码
function html_code_button() {
    wp_enqueue_script( 'html_code_button', get_template_directory_uri() . '/assets/js/quicktags.js', array( 'jquery', 'quicktags' ), '1.0.0', true );
}
add_action('admin_print_footer_scripts', 'html_code_button' );
