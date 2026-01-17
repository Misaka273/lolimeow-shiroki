<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 文章新窗口打开开关--------------------------boxmoe.com--------------------------
function boxmoe_article_new_window() {
    return get_boxmoe('boxmoe_article_new_window_switch', true) ? 'target="_blank"' : '';
}

// 🔗 文章编辑按钮新窗口打开
function boxmoe_edit_post_link_new_tab($link) {
    if (get_boxmoe('boxmoe_article_edit_target_blank')) {
        return str_replace('<a ', '<a target="_blank" ', $link);
    }
    return $link;
}
add_filter('edit_post_link', 'boxmoe_edit_post_link_new_tab');

// 开启所有文章形式支持--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_article_support_switch')){
    add_theme_support('post-formats', array('image', 'video', 'audio', 'quote', 'link'));
}

//开启特色文章缩略图
    add_theme_support('post-thumbnails');
	

// 缩略图尺寸设定--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_article_thumbnail_size_switch')){
function boxmoe_article_thumbnail_size($size) {
    $width  = intval(get_boxmoe('boxmoe_article_thumbnail_width')) ?: 300; 
    $height = intval(get_boxmoe('boxmoe_article_thumbnail_height')) ?: 200;
    return array($width, $height); 
}
add_filter('post_thumbnail_size', 'boxmoe_article_thumbnail_size');
}

// 文章缩略图逻辑--------------------------boxmoe.com--------------------------
function boxmoe_article_thumbnail_src() {
    global $post;
    $src='';
    if ($thumbnail_id = get_post_thumbnail_id()) {
        $src=wp_get_attachment_image_url($thumbnail_id, 'full');
    }elseif ($thumbnail_url = get_post_meta(get_the_ID(), '_thumbnail', true)) {
        $src=$thumbnail_url;
    }elseif (preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches)) {
        $src=$matches[1][0]; 
    }else{
        if(get_boxmoe('boxmoe_article_thumbnail_random_api')){
            $src=get_boxmoe('boxmoe_article_thumbnail_random_api_url');
        }else{
            $random_images = glob(get_template_directory().'/assets/images/random/*.{jpg,jpeg,png,gif}', GLOB_BRACE);   
            if (!empty($random_images)) {
                $random_key = array_rand($random_images);
                $src = str_replace(get_template_directory(), get_template_directory_uri(), $random_images[$random_key]);
            } else {
                $src = boxmoe_theme_url().'/assets/images/default-thumbnail.jpg';
            }
        }
    }
    return $src ?: boxmoe_theme_url().'/assets/images/default-thumbnail.jpg';
}

//文章点击数换算K--------------------------boxmoe.com--------------------------
function restyle_text($number){
    if ($number >= 1000) {
                  return round($number / 1000, 2) . 'k';
              } else {
                  return $number;
              }
  }
  //文章点击数--------------------------boxmoe.com--------------------------
  function getPostViews($postID){
      $count_key = 'post_views_count';
      $count = get_post_meta($postID, $count_key, true);
      if($count==''){
          delete_post_meta($postID, $count_key);
          add_post_meta($postID, $count_key, '0');
          return "0 View";
      }
      return restyle_text($count);
  }
  function setPostViews($postID) {
      $count_key = 'post_views_count';
      $count = get_post_meta($postID, $count_key, true);
      if($count==''){
          $count = 0;
          delete_post_meta($postID, $count_key);
          add_post_meta($postID, $count_key, '0');
      }else{
          $count++;
          update_post_meta($postID, $count_key, $count);
      }
  }


//修剪标记--------------------------boxmoe.com--------------------------
function _str_cut($str, $start, $width, $trimmarker) {
	$output = preg_replace('/^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $width . '}).*/s', '\1', $str);
	return $output . $trimmarker;
}

//自定义段长度--------------------------boxmoe.com--------------------------
function custom_excerpt_length( $length ){
return 200;
}
add_filter( 'excerpt_length', 'custom_excerpt_length');

//文章、评论内容缩短--------------------------boxmoe.com--------------------------
function _get_excerpt($limit = 60, $after = '...') { 
    if ( post_password_required() ) {
        $fallback = '无法提供摘要。这是一篇受保护的文章。';
        $text = get_boxmoe('boxmoe_article_password_excerpt_text', $fallback);
        return $text;
    }
    $excerpt = get_the_excerpt();
    if (mb_strlen($excerpt) > $limit) {
        return _str_cut(strip_tags($excerpt), 0, $limit, $after);
    }
    return $excerpt;
}

// 表格替换--------------------------boxmoe.com--------------------------
function boxmoe_table_replace($text){
	// 🔧 跳过已经由Markdown生成的表格，避免破坏样式
	$md_tables = [];
	$text = preg_replace_callback('/<div class="md-table-wrapper">.*?<\/div>/s', function($matches) use (&$md_tables) {
		$key = '__MD_TABLE_PLACEHOLDER_' . count($md_tables) . '__';
		$md_tables[$key] = $matches[0];
		return $key;
	}, $text);
	
	// 处理其他表格
	$replace = array( '<table>' => '<div class="table-responsive"><table class="table" >','</table>' => '</table></div>' );
	$text = str_replace(array_keys($replace), $replace, $text);
	
	// 恢复Markdown表格
	foreach ($md_tables as $key => $original) {
		$text = str_replace($key, $original, $text);
	}
	
	return $text;
}
add_filter('the_content', 'boxmoe_table_replace');

//防止代码转义--------------------------boxmoe.com--------------------------
function boxmoe_prettify_esc_html($content){
    $regex = '/(<pre\s+[^>]*?class\s*?=\s*?[",\'].*?prettyprint.*?[",\'].*?>)(.*?)(<\/pre>)/sim';
    return preg_replace_callback($regex, 'boxmoe_prettify_esc_callback', $content);}
function boxmoe_prettify_esc_callback($matches){
    $tag_open = $matches[1];
    $content = $matches[2];
    $tag_close = $matches[3];
    $content = esc_html($content);
    return $tag_open . $content . $tag_close;}
add_filter('the_content', 'boxmoe_prettify_esc_html', 2);
add_filter('comment_text', 'boxmoe_prettify_esc_html', 2);

//强制兼容--------------------------boxmoe.com--------------------------
function boxmoe_prettify_replace($text){
	$replace = array( '<pre>' => '<pre class="prettyprint linenums" >','<pre class="prettyprint">' => '<pre class="prettyprint linenums" >' );
	$text = str_replace(array_keys($replace), $replace, $text);
	return $text;}
add_filter('the_content', 'boxmoe_prettify_replace');

// 自动设置特色图片--------------------------boxmoe.com--------------------------
function autoset_featured_image() {
    global $post;
    if (!is_object($post)) return;
    $already_has_thumb = has_post_thumbnail($post->ID);
    if (!$already_has_thumb)  {
        $attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
        if ($attached_image) {
            foreach ($attached_image as $attachment_id => $attachment) {
                set_post_thumbnail($post->ID, $attachment_id);
            }
        }
    }
}
add_action( 'the_post', 'autoset_featured_image' );
add_action( 'save_post', 'autoset_featured_image' );
add_action( 'draft_to_publish', 'autoset_featured_image' );
add_action( 'new_to_publish', 'autoset_featured_image' );
add_action( 'pending_to_publish', 'autoset_featured_image' );
add_action( 'future_to_publish', 'autoset_featured_image' );


// 📌 自适应图片与自定义尺寸支持--------------------------shiroki.com--------------------------
function boxmoe_remove_width_height($content) {
    // 不再移除所有图片的宽高属性，允许用户自定义尺寸
    // 只处理没有手动添加尺寸的图片，保持向后兼容
    preg_match_all('/<[img|IMG].*?src=[\'|"](.*?(?:[\.gif|\.jpg|\.png\.bmp\.webp]))[\'|"].*?[\/]?>/', $content, $images);
    if (!empty($images)) {
        foreach ($images[0] as $index => $value) {
            // 检查图片是否已经有手动添加的 width 或 height 属性
            // 如果没有手动添加尺寸，才移除自动生成的尺寸属性
            // 这样用户手动添加的尺寸会被保留
            $img_tag = $images[0][$index];
            if (preg_match('/width="[^"]+"/', $img_tag) || preg_match('/height="[^"]+"/', $img_tag)) {
                // 图片已经有手动添加的尺寸，跳过处理
                continue;
            }
            // 没有手动添加尺寸，移除自动生成的尺寸属性
            $new_img = preg_replace('/(width|height)="\d*"\s/', "", $img_tag);
            $content = str_replace($img_tag, $new_img, $content);
        }
    }
    return $content;
}
add_filter('the_content', 'boxmoe_remove_width_height', 99);


// 图片懒加载--------------------------boxmoe.com--------------------------
function boxmoe_lazy_content_load_images($content) {
    $content = preg_replace_callback('/<img([^>]*?)src=([\'"])([^\'"]+)\2/i', 
        function($matches) {
            if (strpos($matches[0], 'data-src') !== false) {
                return $matches[0];
            }
            return '<img' . $matches[1] 
                . ' src="' . boxmoe_lazy_load_images() . '"' 
                . ' data-src="' . $matches[3] . '"'
                . ' class="lazy"'
                . ' loading="lazy"';
        },
        $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_lazy_content_load_images', 99);
}

function boxmoe_disable_lazy_for_gifs($content) {
    $content = preg_replace_callback('/<img[^>]*>/i', function($imgTag) {
        $tag = $imgTag[0];
        if (strpos($tag, 'data-src') === false) return $tag;
        if (!preg_match('/data-src=([\'\"])([^\'\"]+)\1/i', $tag, $m)) return $tag;
        $dataSrc = $m[2];
        if (!preg_match('/\.gif(\?.*)?$/i', $dataSrc)) return $tag;
        $base = $dataSrc;
        $query = '';
        if (preg_match('/^(.*?)(\?.*)$/', $dataSrc, $qm)) { $base = $qm[1]; $query = $qm[2]; }
        $base = preg_replace('/-\d+x\d+(?=\.gif$)/i', '', $base);
        $dataSrc = $base . $query;
        $updated = $tag;
        $updated = preg_replace_callback('/\sclass=([\'\"])([^\'\"]*)\blazy\b([^\'\"]*)\1/i', function($cm){
            $cls = trim(preg_replace('/\blazy\b/i', '', $cm[2].$cm[3]));
            return $cls ? ' class="'.$cls.'"' : '';
        }, $updated);
        $updated = preg_replace('/\sloading=([\'\"])lazy\1/i', '', $updated);
        $updated = preg_replace('/\ssrcset=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\ssizes=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\sdata-src=([\'\"])([^\'\"]+)\1/i', '', $updated);
        if (preg_match('/\ssrc=([\'\"])([^\'\"]+)\1/i', $updated)) {
            $updated = preg_replace('/\ssrc=([\'\"])([^\'\"]+)\1/i', ' src="'.$dataSrc.'"', $updated);
        } else {
            $updated = preg_replace('/^<img/i', '<img src="'.$dataSrc.'"', $updated);
        }
        return $updated;
    }, $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_disable_lazy_for_gifs', 100);
}

function boxmoe_disable_lazy_for_images($content) {
    $content = preg_replace_callback('/<img[^>]*>/i', function($imgTag) {
        $tag = $imgTag[0];
        if (strpos($tag, 'data-src') === false) return $tag;
        if (!preg_match('/data-src=([\'\"])([^\'\"]+)\1/i', $tag, $m)) return $tag;
        $dataSrc = $m[2];
        if (preg_match('/\.gif(\?.*)?$/i', $dataSrc)) return $tag;
        $updated = $tag;
        $updated = preg_replace_callback('/\sclass=([\'\"])([^\'\"]*)\blazy\b([^\'\"]*)\1/i', function($cm){
            $cls = trim(preg_replace('/\blazy\b/i', '', $cm[2].$cm[3]));
            return $cls ? ' class="'.$cls.'"' : '';
        }, $updated);
        $updated = preg_replace('/\sloading=(["\'])lazy\1/i', '', $updated);
        $updated = preg_replace('/\ssrcset=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\ssizes=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\sdata-src=([\'\"])([^\'\"]+)\1/i', '', $updated);
        if (preg_match('/\ssrc=([\'\"])([^\'\"]+)\1/i', $updated)) {
            $updated = preg_replace('/\ssrc=([\'\"])([^\'\"]+)\1/i', ' src="'.$dataSrc.'"', $updated);
        } else {
            $updated = preg_replace('/^<img/i', '<img src="'.$dataSrc.'"', $updated);
        }
        return $updated;
    }, $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_disable_lazy_for_images', 101);
}

// fancybox--------------------------boxmoe.com--------------------------
function boxmoe_fancybox_replace ($content) {
    global $post;
    $pattern = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(-\d+x\d+)?(\.(?:bmp|gif|jpeg|png|jpg|webp))('|\")([^\>]*?)>/i";
    $replacement = '<a$1href=$2$3$5$6$7 class="fancybox" data-fancybox="gallery" data-src="$3$5">';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('the_content', 'boxmoe_fancybox_replace', 99);

// fancybox-erphpdown
//add_filter('the_content', 'erphpdownbuy_replace', 99);
function erphpdownbuy_replace ($content) {
	global $post;
	$pattern = "/<a(.*?)class=\"erphpdown-iframe erphpdown-buy\"(.*?)>/i";
	$replacement = '<a$1$2$3$4$5$6 class="fancybox" data-fancybox data-type="iframe" class="erphpdown-buy">';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

// 分页导航函数--------------------------boxmoe.com--------------------------
if ( ! function_exists( 'boxmoe_pagination' ) ) :
function boxmoe_pagination($query = null) {
    $paging_type = get_boxmoe('boxmoe_article_paging_type');
    if($paging_type == 'multi'){
        $p = 1;
        if ( is_singular() ) return;
        global $wp_query, $paged;
        $max_page = $wp_query->max_num_pages;
        echo '<div class="col-lg-12 col-md-12 pagenav">';
        echo '<nav class="d-flex justify-content-center">';
        echo '<ul class="pagination">';
        if ( empty( $paged ) ) $paged = 1;
        if($paged !== 1 ) p_link(0);
        $start = max(1, $paged - $p);
        $end = min($paged + ($p * 1), $max_page);
        if ($start > 1) {
            p_link(1);
            if ($start > 1) echo "<li class=\"page-item\"><a class=\"page-link\">···</a></li>";
        }
        for( $i = $start; $i <= $end; $i++ ) { 
            if ( $i > 0 && $i <= $max_page ) {
                $i == $paged ? print "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\">{$i}</a></li>" : p_link( $i );
            }
        }
        if ($end < $max_page) {
            if ($end < $max_page - 1) echo "<li class=\"page-item\"><a class=\"page-link\">···</a></li>";
            p_link($max_page, '', 1);
        }
        echo '</ul>
        </nav>
      </div>';
    }elseif($paging_type == 'next'){
        global $wp_query;
        $query = $query ?: $wp_query;
        $current = max(1, get_query_var('paged'));
        $total = $query->max_num_pages;
        
        echo '<nav class="pagination-next-prev"><ul class="pagination justify-content-center">';
        if ($current > 1) {
            echo '<li class="page-item">';
            previous_posts_link('<span class="page-link"><i class="fa fa-arrow-left"></i> '.__('上一页', 'boxmoe').'</span>');
            echo '</li>';
        }
        if ($current < $total) {
            echo '<li class="page-item ms-2">';
            next_posts_link('<span class="page-link">'.__('下一页', 'boxmoe').' <i class="fa fa-arrow-right"></i></span>', $total);
            echo '</li>';
        }
        echo '</ul></nav>';
    }elseif($paging_type == 'loadmore'){
    }elseif($paging_type == 'infinite'){
        // 无限加载模式，返回空，由前端处理
    }
}
function p_link( $i, $title = '', $w='' ) {
    if ( $title == '' ) $title = __('页', 'boxmoe-com')." {$i}";
    $itext = $i;
    if( $i == 0 ){
        $itext = __('<i class="fa fa-angle-double-left"></i>', 'boxmoe-com');
    }
    if( $w ){
        $itext = __('<i class="fa fa-angle-double-right"></i>', 'boxmoe-com');
    }
    echo "<li class=\"page-item\"><a class=\"page-link\" href='", esc_html( get_pagenum_link( $i ) ), "'>{$itext}</a></li>";
}
endif;


// 文章点赞数获取
function getPostLikes($postID) {
    $count_key = 'post_likes_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count;
}

function boxmoe_post_like() {
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => 'Invalid post ID']);
        return;
    }

    if (!get_post($post_id)) {
        wp_send_json_error(['message' => '文章不存在']);
        return;
    }

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'post_like_' . $post_id . '_' . md5($user_ip);

    if (false === get_transient($transient_key)) {
        $count = (int)get_post_meta($post_id, 'post_likes_count', true);
        $count++;
        update_post_meta($post_id, 'post_likes_count', $count);
        set_transient($transient_key, '1', DAY_IN_SECONDS);
        
        wp_send_json_success([
            'count' => $count,
            'message' => '点赞成功'
        ]);
    } else {
        wp_send_json_error(['message' => '您已经点过赞了']);
    }
}

add_action('wp_ajax_post_like', 'boxmoe_post_like');
add_action('wp_ajax_nopriv_post_like', 'boxmoe_post_like');

// 检查文章是否被收藏
function isPostFavorited($post_id) {
    if (!is_user_logged_in()) return false;
    
    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        $favorites = array();
    }
    
    return in_array($post_id, $favorites);
}

// 处理文章收藏
function boxmoe_post_favorite() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => '请先登录']);
        return;
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => '无效的文章ID']);
        return;
    }

    if (!get_post($post_id)) {
        wp_send_json_error(['message' => '文章不存在']);
        return;
    }

    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        $favorites = array();
    }

    $is_favorited = in_array($post_id, $favorites);
    
    if ($is_favorited) {
        $favorites = array_diff($favorites, array($post_id));
        $message = '取消收藏成功';
        $status = false;
        // Update post favorites count
        $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
        $count = max(0, $count - 1);
        update_post_meta($post_id, 'post_favorites_count', $count);
    } else {
        $favorites[] = $post_id;
        $message = '收藏成功';
        $status = true;
        // Update post favorites count
        $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
        $count++;
        update_post_meta($post_id, 'post_favorites_count', $count);
    }
    update_user_meta($user_id, 'user_favorites', array_values($favorites));
    wp_send_json_success([
        'message' => $message,
        'status' => $status
    ]);
}

add_action('wp_ajax_post_favorite', 'boxmoe_post_favorite');

// 处理删除收藏
function boxmoe_delete_favorite() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => '请先登录']);
        return;
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => '无效的文章ID']);
        return;
    }

    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        wp_send_json_error(['message' => '没有找到收藏记录']);
        return;
    }
    $favorites = array_diff($favorites, array($post_id));
    update_user_meta($user_id, 'user_favorites', array_values($favorites));
    // Update post favorites count
    $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
    $count = max(0, $count - 1);
    update_post_meta($post_id, 'post_favorites_count', $count);
    wp_send_json_success([
        'message' => '删除收藏成功'
    ]);
}

add_action('wp_ajax_delete_favorite', 'boxmoe_delete_favorite');

// 🔄 文章排序逻辑
function boxmoe_custom_post_order($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only apply to home, archive, and search pages
    if (!$query->is_home() && !$query->is_archive() && !$query->is_search()) {
        return;
    }
    
    // 获取当前页码
    $paged = get_query_var('paged') ?: 1;
    
    // 获取当前的置顶文章
    $sticky_posts = get_option('sticky_posts');
    
    // 首页文章分类筛选和文章数量设置
    if ($query->is_home()) {
        // 首页显示文章数量，默认3篇
        $posts_per_page = intval(get_boxmoe('boxmoe_home_posts_per_page', 3));
        $query->set('posts_per_page', $posts_per_page);
        
        $selected_categories_str = get_boxmoe('boxmoe_home_article_categories', '');
        
        if (!empty($selected_categories_str)) {
            // 使用正则表达式分割字符串，支持，、,三个标点符号
            $categories_array = preg_split('/[,，、]+/', $selected_categories_str);
            // 过滤空值并转换为整数
            $categories_ids = array();
            foreach ($categories_array as $cat_id) {
                $cat_id = trim($cat_id);
                if (!empty($cat_id) && is_numeric($cat_id)) {
                    $categories_ids[] = intval($cat_id);
                }
            }
            // 移除重复ID
            $categories_ids = array_unique($categories_ids);
            
            if (!empty($categories_ids)) {
                if (!empty($sticky_posts)) {
                    // 筛选出属于所选分类的置顶文章
                    $filtered_sticky_posts = array();
                    foreach ($sticky_posts as $sticky_post_id) {
                        $post_categories = wp_get_post_categories($sticky_post_id);
                        // 检查文章是否至少属于一个所选分类
                        $has_matching_category = !empty(array_intersect($post_categories, $categories_ids));
                        if ($has_matching_category) {
                            $filtered_sticky_posts[] = $sticky_post_id;
                        }
                    }
                    
                    // 1. 确保置顶文章显示在最前面
                    $query->set('ignore_sticky_posts', 0);
                    
                    // 2. 处理分页和无限加载的情况
                    if ($paged > 1) {
                        // 分页（包括无限加载的后续请求）：将所有置顶文章从查询中排除，避免重复显示
                        $original_post_not_in = $query->get('post__not_in') ?: array();
                        $query->set('post__not_in', array_merge($original_post_not_in, $sticky_posts));
                    } else {
                        // 第一页：将不属于筛选分类的置顶文章排除
                        $original_post_not_in = $query->get('post__not_in') ?: array();
                        $excluded_sticky_posts = array_diff($sticky_posts, $filtered_sticky_posts);
                        $query->set('post__not_in', array_merge($original_post_not_in, $excluded_sticky_posts));
                    }
                }
                
                // 设置分类筛选条件
                $query->set('category__in', $categories_ids);
            }
        } else {
            // 没有设置分类筛选，处理所有置顶文章
            if (!empty($sticky_posts)) {
                if ($paged > 1) {
                    // 分页时忽略置顶文章
                    $query->set('ignore_sticky_posts', 1);
                    $original_post_not_in = $query->get('post__not_in') ?: array();
                    $query->set('post__not_in', array_merge($original_post_not_in, $sticky_posts));
                }
            }
        }
    } else {
        // 归档页和搜索页：只在第一页显示置顶文章，分页时忽略
        if (!empty($sticky_posts)) {
            if ($paged > 1) {
                // 分页时忽略置顶文章
                $query->set('ignore_sticky_posts', 1);
                $original_post_not_in = $query->get('post__not_in') ?: array();
                $query->set('post__not_in', array_merge($original_post_not_in, $sticky_posts));
            }
        }
    }

    if (isset($_GET['orderby'])) {
        $orderby = sanitize_text_field($_GET['orderby']);
        
        // Determine order
        $order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : '';
        
        // Default to DESC if not specified, except for title where we might want ASC default
        if (empty($order)) {
             $order = ($orderby == 'title') ? 'ASC' : 'DESC';
        }

        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $query->set('order', $order);

        switch ($orderby) {
            case 'title':
                $query->set('orderby', 'title');
                break;
            case 'modified':
                $query->set('orderby', 'modified');
                break;
            case 'date':
                $query->set('orderby', 'date');
                break;
            case 'views':
                $query->set('meta_key', 'post_views_count');
                $query->set('orderby', 'meta_value_num');
                break;
            case 'likes':
                $query->set('meta_key', 'post_likes_count');
                $query->set('orderby', 'meta_value_num');
                break;
            case 'favorites':
                $query->set('meta_key', 'post_favorites_count');
                $query->set('orderby', 'meta_value_num');
                break;
        }
    }
}
add_action('pre_get_posts', 'boxmoe_custom_post_order');

// 🎯 文章编辑权限管理模块

// 添加文章编辑权限元框
function boxmoe_add_post_editor_meta_box() {
    // 支持文章类型
    add_meta_box(
        'boxmoe_post_editors',
        '文章编辑者',
        'boxmoe_post_editors_meta_box_callback',
        'post',
        'normal',
        'high'
    );
    
    // 支持页面类型
    add_meta_box(
        'boxmoe_post_editors',
        '页面编辑者',
        'boxmoe_post_editors_meta_box_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'boxmoe_add_post_editor_meta_box');

// 元框回调函数
function boxmoe_post_editors_meta_box_callback($post) {
    // 添加安全字段
    wp_nonce_field('boxmoe_save_post_editors', 'boxmoe_post_editors_nonce');
    
    // 获取已保存的编辑者
    $editors = get_post_meta($post->ID, '_boxmoe_post_editors', true);
    $editors = is_array($editors) ? $editors : array();
    
    // 获取当前用户信息，用于显示创建者
    $post_author = get_user_by('ID', $post->post_author);
    
    ?>
    <div class="boxmoe-post-editors-container">
        <div class="boxmoe-post-creator" style="margin-bottom: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
            <strong>创建者:</strong> <?php echo esc_html($post_author->display_name); ?> (<?php echo esc_html($post_author->user_login); ?>)
        </div>
        
        <div class="boxmoe-search-user-section" style="margin-bottom: 15px;">
            <label for="boxmoe-search-user" style="display: block; margin-bottom: 5px;">搜索用户:</label>
            <input type="text" id="boxmoe-search-user" class="regular-text" placeholder="输入用户ID、用户名或邮箱" style="width: 100%; margin-bottom: 10px;">
            <div id="boxmoe-search-results" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; background: white; display: none;"></div>
        </div>
        
        <div class="boxmoe-selected-editors">
            <label style="display: block; margin-bottom: 10px;">已选编辑者:</label>
            <div id="boxmoe-editors-list" style="margin-bottom: 15px;">
                <?php if (!empty($editors)): ?>
                    <?php foreach ($editors as $editor_id): ?>
                        <?php $editor = get_user_by('ID', $editor_id); ?>
                        <?php if ($editor): ?>
                            <div class="boxmoe-editor-item" data-user-id="<?php echo esc_attr($editor_id); ?>" style="display: inline-block; margin: 5px; padding: 5px 10px; background: #e8f4f8; border: 1px solid #21759b; border-radius: 15px;">
                                <?php echo esc_html($editor->display_name); ?> (<?php echo esc_html($editor->user_login); ?>)
                                <button type="button" class="boxmoe-remove-editor" style="background: none; border: none; color: #d9534f; cursor: pointer; margin-left: 5px;">×</button>
                                <input type="hidden" name="boxmoe_post_editors[]" value="<?php echo esc_attr($editor_id); ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #999;">暂无添加的编辑者</p>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="description">
            添加其他用户作为文章编辑者，他们将能够编辑此文章。
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // 用户搜索功能
        $('#boxmoe-search-user').on('input', function() {
            var search_term = $(this).val();
            var results_container = $('#boxmoe-search-results');
            
            if (search_term.length < 2) {
                results_container.hide();
                return;
            }
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'boxmoe_post_search_users',
                    search_term: search_term,
                    nonce: '<?php echo wp_create_nonce('boxmoe_post_search_users'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var results = '<ul style="margin: 0; padding: 0; list-style: none;">';
                        $.each(response.data, function(index, user) {
                            results += '<li data-user-id="' + user.id + '" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;">' +
                                '<strong>' + user.display_name + '</strong> (' + user.user_login + ') - ' + user.user_email +
                                '</li>';
                        });
                        results += '</ul>';
                        results_container.html(results).show();
                    } else {
                        results_container.html('<p style="padding: 8px; color: #999;">未找到匹配用户</p>').show();
                    }
                }
            });
        });
        
        // 点击搜索结果添加用户
        $(document).on('click', '#boxmoe-search-results li', function() {
            var user_id = $(this).data('user-id');
            var user_name = $(this).find('strong').text();
            var user_login = $(this).text().match(/\((.*?)\)/)[1];
            
            // 检查用户是否已添加
            if ($('#boxmoe-editors-list input[value="' + user_id + '"]').length === 0) {
                var editor_item = '<div class="boxmoe-editor-item" data-user-id="' + user_id + '" style="display: inline-block; margin: 5px; padding: 5px 10px; background: #e8f4f8; border: 1px solid #21759b; border-radius: 15px;">' +
                    user_name + ' (' + user_login + ') ' +
                    '<button type="button" class="boxmoe-remove-editor" style="background: none; border: none; color: #d9534f; cursor: pointer; margin-left: 5px;">×</button>' +
                    '<input type="hidden" name="boxmoe_post_editors[]" value="' + user_id + '">' +
                    '</div>';
                
                $('#boxmoe-editors-list p:contains("暂无添加的编辑者")').remove();
                $('#boxmoe-editors-list').append(editor_item);
            }
            
            // 清空搜索框和结果
            $('#boxmoe-search-user').val('');
            $('#boxmoe-search-results').hide();
        });
        
        // 移除编辑者
        $(document).on('click', '.boxmoe-remove-editor', function() {
            $(this).parent().remove();
            
            // 如果没有编辑者，显示提示信息
            if ($('#boxmoe-editors-list .boxmoe-editor-item').length === 0) {
                $('#boxmoe-editors-list').html('<p style="color: #999;">暂无添加的编辑者</p>');
            }
        });
        
        // 点击页面其他地方关闭搜索结果
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.boxmoe-search-user-section').length) {
                $('#boxmoe-search-results').hide();
            }
        });
    });
    </script>
    <?php
}

// 保存编辑者数据
function boxmoe_save_post_editors($post_id) {
    // 检查安全字段
    if (!isset($_POST['boxmoe_post_editors_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['boxmoe_post_editors_nonce'], 'boxmoe_save_post_editors')) {
        return;
    }
    
    // 检查自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 检查用户权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // 保存数据
    if (isset($_POST['boxmoe_post_editors'])) {
        $editors = array_map('intval', $_POST['boxmoe_post_editors']);
        $editors = array_unique($editors); // 移除重复项
        update_post_meta($post_id, '_boxmoe_post_editors', $editors);
    } else {
        delete_post_meta($post_id, '_boxmoe_post_editors');
    }
}
add_action('save_post', 'boxmoe_save_post_editors');

// AJAX 用户搜索函数
function boxmoe_post_search_users() {
    // 检查nonce
    if (!wp_verify_nonce($_POST['nonce'], 'boxmoe_post_search_users')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // 检查用户权限
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    // 获取搜索词
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    
    if (empty($search_term)) {
        wp_send_json_error('Search term empty');
    }
    
    // 搜索用户
    $users = get_users(array(
        'search' => '*' . $search_term . '*',
        'search_columns' => array('ID', 'user_login', 'user_email', 'display_name'),
        'number' => 10
    ));
    
    // 准备结果
    $results = array();
    foreach ($users as $user) {
        $results[] = array(
            'id' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name
        );
    }
    
    wp_send_json_success($results);
}
add_action('wp_ajax_boxmoe_post_search_users', 'boxmoe_post_search_users');

// 修改文章编辑权限，允许指定用户编辑
function boxmoe_post_edit_capability($allcaps, $caps, $args) {
    // 检查是否请求编辑文章权限
    if (isset($args[0]) && $args[0] === 'edit_post' && isset($args[2])) {
        $user_id = $args[1];
        $post_id = $args[2];
        
        // 检查用户是否是文章的编辑者
        $editors = get_post_meta($post_id, '_boxmoe_post_editors', true);
        $editors = is_array($editors) ? $editors : array();
        
        if (in_array($user_id, $editors)) {
            // 添加编辑权限
            $allcaps['edit_posts'] = true;
            $allcaps['edit_post'] = true;
            $allcaps['edit_others_posts'] = true;
        }
    }
    
    return $allcaps;
}
add_filter('user_has_cap', 'boxmoe_post_edit_capability', 10, 3);

// 修改文章列表查询，只显示用户有编辑权限的文章和页面
function boxmoe_restrict_post_list($query) {
    global $pagenow;
    
    // 只在管理后台的文章和页面列表页面生效
    if (is_admin() && in_array($pagenow, array('edit.php', 'edit.php?post_type=page')) && $query->is_main_query() && !current_user_can('edit_others_posts')) {
        $user_id = get_current_user_id();
        
        // 获取当前查询的文章类型
        $post_type = $query->get('post_type');
        $post_type = empty($post_type) ? 'post' : $post_type;
        
        // 获取用户是编辑者的所有内容
        $editor_posts = get_posts(array(
            'post_type' => $post_type,
            'meta_key' => '_boxmoe_post_editors',
            'meta_value' => $user_id,
            'meta_compare' => 'LIKE',
            'fields' => 'ids',
            'posts_per_page' => -1
        ));
        
        // 获取用户自己的内容
        $author_posts = get_posts(array(
            'post_type' => $post_type,
            'author' => $user_id,
            'fields' => 'ids',
            'posts_per_page' => -1
        ));
        
        // 合并内容ID并去重
        $allowed_posts = array_merge($editor_posts, $author_posts);
        $allowed_posts = array_unique($allowed_posts);
        
        // 设置查询条件
        if (!empty($allowed_posts)) {
            $query->set('post__in', $allowed_posts);
        } else {
            // 如果没有内容，返回空结果
            $query->set('post__in', array(0));
        }
    }
}
add_action('pre_get_posts', 'boxmoe_restrict_post_list');

// 🔗 外链跳转处理
function boxmoe_external_link_redirect($content) {
    // 获取设置开关状态
    $notice_switch = get_boxmoe('boxmoe_external_link_notice_switch');
    $direct_switch = get_boxmoe('boxmoe_external_link_direct_switch');
    
    // 如果两个开关都关闭，直接返回原内容
    if (!$notice_switch && !$direct_switch) {
        return $content;
    }
    
    // 获取跳转页面URL
    $redirect_url = '';
    if ($notice_switch) {
        // 查找使用外链提醒版模板的页面
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page/p-goto.php'
        ));
        if (!empty($pages)) {
            $redirect_url = get_permalink($pages[0]->ID);
        }
    } elseif ($direct_switch) {
        // 查找使用外链直跳版模板的页面
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page/p-go.php'
        ));
        if (!empty($pages)) {
            $redirect_url = get_permalink($pages[0]->ID);
        }
    }
    
    // 如果找不到对应页面，直接返回原内容
    if (empty($redirect_url)) {
        return $content;
    }
    
    // 查找所有链接，添加s修饰符以匹配包含换行符的链接
    $pattern = '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is';
    
    // 替换链接为跳转链接
    $content = preg_replace_callback($pattern, function($matches) use ($redirect_url) {
        $href = $matches[1];
        $text = $matches[2];
        // 提取a标签的属性部分，排除href和内容
        $full_tag = $matches[0];
        preg_match('/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*?)>/i', $full_tag, $attr_matches);
        $before_href = isset($attr_matches[1]) ? trim($attr_matches[1]) : '';
        $after_href = isset($attr_matches[3]) ? trim($attr_matches[3]) : '';
        
        // 合并属性
        $attributes = '';
        if (!empty($before_href)) {
            $attributes .= ' ' . $before_href;
        }
        if (!empty($after_href)) {
            $attributes .= ' ' . $after_href;
        }
        $attributes = trim($attributes);
        
        // 检查是否为外部链接
        if (strpos($href, home_url()) === 0 || strpos($href, 'http') !== 0) {
            // 内部链接或相对链接，不处理
            return $matches[0];
        }
        
        // 构建跳转链接
        $encoded_url = urlencode($href);
        $full_redirect_url = "{$redirect_url}?url={$encoded_url}";
        
        // 返回新的链接
        if (!empty($attributes)) {
            return "<a href='{$full_redirect_url}' {$attributes}>{$text}</a>";
        } else {
            return "<a href='{$full_redirect_url}'>{$text}</a>";
        }
    }, $content);
    
    return $content;
}

add_filter('the_content', 'boxmoe_external_link_redirect', 99);
