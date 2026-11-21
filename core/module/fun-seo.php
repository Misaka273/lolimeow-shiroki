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


// 百度推送功能--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_baidu_submit_switch')){
function boxmoe_baidu_submit($post_ID) {
    if (get_post_status($post_ID) == 'publish') {
        $WEB_DOMAIN = get_option('home');
        $BAIDU_TOKEN = get_boxmoe('boxmoe_baidu_token');
        $api_url = 'http://data.zz.baidu.com/urls?site='.$WEB_DOMAIN.'&token='.$BAIDU_TOKEN;
        $post_url = get_permalink($post_ID);
        $args = array(
            'headers' => array('Content-Type' => 'text/plain'),
            'body' => $post_url,
            'timeout' => 5
        );       
        $response = wp_remote_post($api_url, $args);
        if($response['response']['code'] == 200){
            add_post_meta($post_ID, 'Baidusubmit', '推送成功', true);
        }      
    }
}
add_action('publish_post', 'boxmoe_baidu_submit');
}

// Bing推送功能--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_bing_submit_switch')){
function boxmoe_bing_submit($post_ID) {
    if (get_post_status($post_ID) == 'publish') {
        $WEB_DOMAIN = get_option('home');
        $BING_API_KEY = get_boxmoe('boxmoe_bing_api_key');
        $api_url = 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrl?apikey='.$BING_API_KEY;
        $post_url = get_permalink($post_ID);
        $args = array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(array('siteUrl' => get_option('home'), 'url' => $post_url)),
            'timeout' => 5
        );       
        $response = wp_remote_post($api_url, $args);
        if($response['response']['code'] == 200){
            add_post_meta($post_ID, 'BingSubmit', '推送成功', true);
        }
    }
}
add_action('publish_post', 'boxmoe_bing_submit');
}

// 360推送功能--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_360_submit_switch')){
function boxmoe_360_submit($post_ID) {
    if (get_post_status($post_ID) == 'publish') {
        $API_KEY_360 = get_boxmoe('boxmoe_360_api_key');
        $api_url = 'http://zhanzhang.so.com/linksubmit/urlsubmit?site_token='.$API_KEY_360;
        $post_url = get_permalink($post_ID);
        $args = array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array('urls' => $post_url)),
            'timeout' => 5
        );
        $response = wp_remote_post($api_url, $args);
        if(!is_wp_error($response) && $response['response']['code'] == 200){
            add_post_meta($post_ID, '360Submit', '推送成功', true);
        }
    }
}
add_action('publish_post', 'boxmoe_360_submit');
}

// 谷歌搜索推送功能--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_google_submit_switch')){
function boxmoe_google_submit($post_ID) {
    if (get_post_status($post_ID) == 'publish') {
        $GOOGLE_API_KEY = get_boxmoe('boxmoe_google_api_key');
        $api_url = 'https://www.google.com/ping?sitemap='.$GOOGLE_API_KEY;
        $post_url = get_permalink($post_ID);
        $args = array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array('urls' => $post_url)),
            'timeout' => 5
        );
        $response = wp_remote_post($api_url, $args);
        if(!is_wp_error($response) && $response['response']['code'] == 200){
            add_post_meta($post_ID, 'GoogleSubmit', '推送成功', true);
        }
    }
}
add_action('publish_post', 'boxmoe_google_submit');
}


// 网站标题连接符--------------------------boxmoe.com--------------------------
function boxmoe_title_link(){
    return get_boxmoe('boxmoe_title_link') ? ' ' . get_boxmoe('boxmoe_title_link'). ' ' : ' - ';
}



// 🎯 网站标题--------------------------boxmoe.com--------------------------
function boxmoe_theme_title(){
	global $new_title;
	if( $new_title ) return $new_title;
	global $paged, $post;
	$html = '';
	$site_name = get_bloginfo('name');
	$site_description = get_bloginfo('description');
	$title_sep = boxmoe_title_link();
	
	// 首页标题允许后台自定义：搜索/AI 场景下核心词应该排在品牌前面
	if( (is_home() || is_front_page()) && (int) $paged < 2 ){
		$custom_home_title = trim( (string) get_boxmoe('boxmoe_home_seo_title') );
		if( $custom_home_title !== '' ){
			return $custom_home_title;
		}
	}
	
	// 获取当前页面标题
	if (is_single() || is_page()) {
		$t = get_the_title();
		// 处理文章/页面副标题
		$subtitle = get_the_subtitle(false);
		if ($subtitle) {
			$t .= $title_sep . $subtitle;
		}
	} elseif (is_home() || is_front_page()) {
		$t = '';
	} elseif (is_category()) {
		$t = single_cat_title('', false);
	} elseif (is_tag()) {
		$t = single_tag_title('', false);
	} elseif (is_author()) {
		$t = get_the_author();
	} elseif (is_search()) {
		$t = sprintf(__('搜索结果：%s', 'boxmoe'), get_search_query());
	} elseif (is_404()) {
		$t = __('404页面未找到', 'boxmoe');
	} elseif (is_archive()) {
		$t = post_type_archive_title('', false);
	} else {
		$t = trim(wp_title('', false));
	}
	
	// 构建最终标题
	if ($t && !(is_home() && !$paged)) {
		$html .= $t . $title_sep . $site_name;
		// 首页不显示副标题，其他页面可以考虑添加
	} else {
		$html .= $site_name;
	}
	
	// 首页显示副标题
	if (is_home() || is_front_page()) {
		if ($site_description && !$paged) {
			$html .= $title_sep . $site_description;
		} elseif ($paged > 1) {
			$html .= $title_sep . '第' . $paged . '页';
		}
	} 
	// 分页显示
	elseif ($paged > 1) {
		$html .= $title_sep . '第' . $paged . '页';
	}
	
	return $html;
}

// 分享卡片使用文章/页面本身的标题，避免失焦提示文字被当作卡片标题。
function boxmoe_social_title_meta(){
    if (!is_singular()) {
        return;
    }

    $title = trim(wp_strip_all_tags(get_the_title(get_queried_object_id())));
    if ($title === '') {
        return;
    }

    $title = esc_attr($title);
    echo '<meta property="og:title" content="' . $title . '">' . "\n";
    echo '<meta name="twitter:card" content="summary">' . "\n";
    echo '<meta name="twitter:title" content="' . $title . '">' . "\n";
}
add_action('wp_head', 'boxmoe_social_title_meta', 2);

// 获取文章副标题--------------------------boxmoe.com--------------------------
function get_the_subtitle($span=true){
    global $post;
    $post_ID = $post->ID;
    $subtitle = get_post_meta($post_ID, 'subtitle', true);
    if( !empty($subtitle) ){
    	if( $span ){
        	return ' <span>'.$subtitle.'</span>';
        }else{
        	return ' '.$subtitle;
        }
    }else{
        return false;
    }
}



// 网站关键词、描述处理--------------------------boxmoe.com--------------------------
$postmeta_keywords_description = array(
    array(
        "name" => "keywords",

        "std" => "",
        "title" => __('关键字', 'boxmoe').'：'
    ),
    array(
        "name" => "description",
        "std" => "",
        "title" => __('描述', 'boxmoe').'：'
        )
);
if( get_boxmoe('boxmoe_post_keywords_description_switch') ){
    add_action('admin_menu', 'boxmoe_postmeta_keywords_description_create');
    add_action('save_post', 'boxmoe_postmeta_keywords_description_save');
}

function boxmoe_postmeta_keywords_description() {
    global $post, $postmeta_keywords_description;
    foreach($postmeta_keywords_description as $meta_box) {
        $meta_box_value = get_post_meta($post->ID, $meta_box['name'], true);
        if($meta_box_value == "")
            $meta_box_value = $meta_box['std'];
        echo'<p>'.$meta_box['title'].'</p>';
        if( $meta_box['name'] == 'keywords' ){
            echo '<p><input type="text" style="width:98%" value="'.$meta_box_value.'" name="'.$meta_box['name'].'"></p>';
        }else{
            echo '<p><textarea style="width:98%" name="'.$meta_box['name'].'">'.$meta_box_value.'</textarea></p>';
        }
    }
   
    echo '<input type="hidden" name="post_newmetaboxes_noncename" id="post_newmetaboxes_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
}

function boxmoe_postmeta_keywords_description_create() {
    global $theme_name;
    if ( function_exists('add_meta_box') ) {
        add_meta_box( 'postmeta_keywords_description_boxes', __('自定义关键字和描述', 'boxmoe'), 'boxmoe_postmeta_keywords_description', 'post', 'normal', 'high' );
        add_meta_box( 'postmeta_keywords_description_boxes', __('自定义关键字和描述', 'boxmoe'), 'boxmoe_postmeta_keywords_description', 'page', 'normal', 'high' );
    }
}

function boxmoe_postmeta_keywords_description_save( $post_id ) {
    global $postmeta_keywords_description;
   
    if ( !wp_verify_nonce( isset($_POST['post_newmetaboxes_noncename'])?$_POST['post_newmetaboxes_noncename']:'', plugin_basename(__FILE__) ))
        return;
   
    if ( !current_user_can( 'edit_posts', $post_id ))
        return;
                   
    foreach($postmeta_keywords_description as $meta_box) {
        $data = $_POST[$meta_box['name']];
        if(get_post_meta($post_id, $meta_box['name']) == "")
            add_post_meta($post_id, $meta_box['name'], $data, true);
        elseif($data != get_post_meta($post_id, $meta_box['name'], true))
            update_post_meta($post_id, $meta_box['name'], $data);
        elseif($data == "")
            delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
    }
}

// 网站关键词输出--------------------------boxmoe.com--------------------------
function boxmoe_keywords() {
    global $s, $post;
    $keywords = '';
    if ( is_singular() ) {
      if ( get_the_tags( $post->ID ) ) {
        foreach ( get_the_tags( $post->ID ) as $tag ) $keywords .= $tag->name . ', ';
      }
      foreach ( get_the_category( $post->ID ) as $category ) $keywords .= $category->cat_name . ', ';
      if(get_boxmoe('boxmoe_post_keywords_description_switch') ) {
          $the = trim(get_post_meta($post->ID, 'keywords', true));
          if( $the ) $keywords = $the;
      }else{
          $keywords = substr_replace( $keywords , '' , -2);
      }
      
    } elseif ( is_home () )    { $keywords =get_boxmoe('boxmoe_keywords');
    } elseif ( is_tag() )      { $keywords = single_tag_title('', false);
    } elseif ( is_category() ) { $keywords = single_cat_title('', false);
  
      if(get_boxmoe('cat_keyworks_s') ){
          $description = trim(strip_tags(category_description()));
          if( $description && strstr($description, '::::::') ){
              $desc = explode('::::::', $description);
              if( $desc[0] && !empty($desc[0]) ) {
                  $keywords = trim($desc[0]);
              }
          }
      }
  
    } elseif ( is_search() )   { $keywords = esc_html( $s, 1 );
    } else { $keywords = trim( wp_title('', false) );
    }
    if ( $keywords ) {
      echo "<meta name=\"keywords\" content=\"$keywords\">\n";
    }
  }


// 网站描述输出--------------------------boxmoe.com--------------------------
function boxmoe_description() {
    global $s, $post;
    $description = '';
    $blog_name = get_bloginfo('name');
    if ( is_singular() ) {
      $public_data = boxmoe_geo_public_post_data($post);
      $text = $public_data['excerpt'] !== '' ? $public_data['excerpt'] : $public_data['content'];
      $description = trim(wp_strip_all_tags(strip_shortcodes($text)));
      if ( !( $description ) ) $description = $blog_name . "-" . trim( wp_title('', false) );
      if(!$public_data['restricted'] && get_boxmoe('boxmoe_auto_keywords_description_switch') ) {
          $the = trim(get_post_meta($post->ID, 'description', true));
          if( $the ) $description = $the;
      }

    } elseif ( is_home () )    { $description =get_boxmoe('boxmoe_description');
    } elseif ( is_tag() )      { $description = $blog_name . "'" . single_tag_title('', false) . "'";
    } elseif ( is_category() ) { 
  
      $description = trim(strip_tags(category_description()));
  
      if(get_boxmoe('cat_keyworks_s') && $description && strstr($description, '::::::') ){
          $desc = explode('::::::', $description);
          $description = trim($desc[1]);
      }
  
    } elseif ( is_archive() )  { $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
    } elseif ( is_search() )   { $description = $blog_name . ": '" . esc_html( $s, 1 ) . "' ".__('的搜索結果', 'boxmoe');
    } else { $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
    }
    $description = trim(wp_strip_all_tags(strip_shortcodes((string) $description)));
    $description = preg_replace('/\s+/u', ' ', $description);
    $description = mb_substr( $description, 0, 120, 'utf-8' );
    echo '<meta name="description" itemprop="description" content="' . esc_attr($description) . '">' . "\n";
  }


// 规范网址（canonical）：主题统一输出，覆盖首页/分类/标签/作者/日期归档与分页--------------------------gl.baimu.live--------------------------
function boxmoe_canonical_url(){
    $url = '';

    if ( is_front_page() || is_home() ) {
        $url = home_url('/');
    } elseif ( is_singular() ) {
        $url = get_permalink();
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) && isset( $term->term_id ) ) {
            $link = get_term_link( $term );
            if ( ! is_wp_error( $link ) ) {
                $url = $link;
            }
        }
    } elseif ( is_author() ) {
        $url = get_author_posts_url( get_queried_object_id() );
    } elseif ( is_post_type_archive() ) {
        $post_type = get_query_var( 'post_type' );
        if ( is_array( $post_type ) ) {
            $post_type = reset( $post_type );
        }
        $url = get_post_type_archive_link( $post_type );
    } elseif ( is_date() ) {
        if ( is_day() ) {
            $url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
        } elseif ( is_month() ) {
            $url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
        } elseif ( is_year() ) {
            $url = get_year_link( get_query_var( 'year' ) );
        }
    }

    if ( empty( $url ) || is_wp_error( $url ) ) {
        return '';
    }

    if ( is_paged() ) {
        $url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
    }

    return $url;
}

function boxmoe_canonical_tag(){
    if ( is_404() || is_search() || is_preview() || is_feed() ) {
        return;
    }

    // 核心的 rel_canonical 只在单页输出，主题统一接管后要移除，避免同一页面两条 canonical
    remove_action( 'wp_head', 'rel_canonical' );

    $url = boxmoe_canonical_url();
    if ( $url === '' ) {
        return;
    }

    echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
}
add_action( 'wp_head', 'boxmoe_canonical_tag', 3 );
