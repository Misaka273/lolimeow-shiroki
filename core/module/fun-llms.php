<?php
/**
 * @link https://gl.baimu.live/
 * @package 白木-灵阈研都-纸鸢社
 * @description llms.txt —— 给 AI 读的站点导览文件（https://llmstxt.org/）
 *              内容由后台「GEO / AI 搜索优化 → llms.txt」控制，随文章更新自动变化。
 */

// 安全设置--------------------------gl.baimu.live--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

/**
 * 是否开启 /llms.txt（默认开启）。
 */
function boxmoe_llms_enabled(){
    return (bool) get_boxmoe('boxmoe_llms_switch', true);
}

/**
 * 把一段多行文本拆成干净的条目数组。
 */
function boxmoe_llms_split_lines($raw){
    $out = array();
    if(!is_string($raw) || trim($raw) === ''){
        return $out;
    }
    foreach(preg_split('/[\r\n]+/', $raw) as $line){
        $line = trim($line);
        if($line !== ''){
            $out[] = $line;
        }
    }
    return $out;
}

/**
 * 单篇文章的一行摘要：标题 + 链接 + 摘要。
 */
function boxmoe_llms_post_line($post){
    $public = boxmoe_geo_public_post_data($post);
    if($public['password_protected'] || (isset($post->post_status) && $post->post_status !== 'publish')){
        return '';
    }
    $title = trim(wp_strip_all_tags(get_the_title($post)));
    $title = trim(html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
    if($title === ''){
        return '';
    }
    $link = get_permalink($post);
    $text = '';
    if($public['excerpt'] !== ''){
        $text = $public['excerpt'];
    } else {
        $text = strip_shortcodes($public['content']);
    }
    $text = wp_strip_all_tags($text);
    $text = trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if(mb_strlen($text, 'UTF-8') > 90){
        $text = mb_substr($text, 0, 90, 'UTF-8') . '…';
    }
    return '- [' . $title . '](' . $link . ')' . ($text !== '' ? '：' . $text : '');
}

/**
 * 生成 llms.txt 正文。
 */
function boxmoe_llms_content(){
    $lines = array();

    $name = trim((string) get_bloginfo('name'));
    if($name === ''){
        $name = '未命名站点';
    }

    $intro = trim((string) get_boxmoe('boxmoe_llms_intro'));
    if($intro === ''){
        $intro = trim((string) get_bloginfo('description'));
    }
    if($intro === ''){
        $intro = $name . '的站点导览：收录本站栏目、最新内容与主要页面，供 AI 助手快速了解站点主题。';
    }

    $lines[] = '# ' . $name;
    $lines[] = '';
    if($intro !== ''){
        $lines[] = '> ' . str_replace(array("\r", "\n"), ' ', $intro);
        $lines[] = '';
    }

    $meta = array();
    $meta[] = '站点地址：' . home_url('/');
    $language = get_bloginfo('language');
    if($language !== ''){
        $meta[] = '语言：' . $language;
    }
    $last_modified = get_lastpostmodified('gmt');
    if(!empty($last_modified)){
        $meta[] = '最后更新：' . mysql2date('Y-m-d', $last_modified);
    }
    $lines[] = implode(' | ', $meta);

    // 主要栏目
    $cat_limit = (int) get_boxmoe('boxmoe_llms_cats');
    if($cat_limit <= 0){
        $cat_limit = 20;
    }
    $categories = get_categories(array(
        'hide_empty' => true,
        'parent' => 0,
        'number' => $cat_limit,
        'orderby' => 'count',
        'order' => 'DESC',
    ));
    if(!empty($categories)){
        $lines[] = '';
        $lines[] = '## 主要栏目';
        foreach($categories as $category){
            $desc = trim(wp_strip_all_tags(category_description($category->term_id)));
            $desc = trim(preg_replace('/\s+/u', ' ', $desc));
            $desc = str_replace('::::::', ' / ', $desc);
            if($desc === ''){
                $desc = $category->name . '分类下的全部内容';
            }
            if(mb_strlen($desc, 'UTF-8') > 80){
                $desc = mb_substr($desc, 0, 80, 'UTF-8') . '…';
            }
            $lines[] = '- [' . $category->name . '](' . get_category_link($category->term_id) . ')：' . $desc . '（' . (int) $category->count . ' 篇）';
        }
    }

    // 最近更新
    $post_limit = (int) get_boxmoe('boxmoe_llms_posts');
    if($post_limit <= 0){
        $post_limit = 15;
    }
    $posts = get_posts(array(
        'posts_per_page' => $post_limit,
        'post_status' => 'publish',
        'has_password' => false,
        'orderby' => 'modified',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'suppress_filters' => false,
    ));
    if(!empty($posts)){
        $lines[] = '';
        $lines[] = '## 最近更新';
        foreach($posts as $post){
            $line = boxmoe_llms_post_line($post);
            if($line !== ''){
                $lines[] = $line;
            }
        }
    }

    // 页面
    $page_limit = (int) get_boxmoe('boxmoe_llms_pages');
    if($page_limit <= 0){
        $page_limit = 8;
    }
    $pages = get_posts(array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'has_password' => false,
        'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
        'posts_per_page' => $page_limit,
        'no_found_rows' => true,
        'suppress_filters' => false,
    ));
    if(!empty($pages)){
        $lines[] = '';
        $lines[] = '## 页面';
        foreach($pages as $page){
            $public = boxmoe_geo_public_post_data($page);
            if($public['password_protected'] || (isset($page->post_status) && $page->post_status !== 'publish')){
                continue;
            }
            $title = trim(wp_strip_all_tags(get_the_title($page)));
            if($title === ''){
                continue;
            }
            $lines[] = '- [' . $title . '](' . get_permalink($page) . ')';
        }
    }

    // 站点地图
    if(function_exists('get_sitemap_url')){
        $sitemap = get_sitemap_url('index');
    } else {
        $sitemap = home_url('/wp-sitemap.xml');
    }
    if(!empty($sitemap)){
        $lines[] = '';
        $lines[] = '## 站点地图';
        $lines[] = '- ' . $sitemap;
    }

    // 引用说明
    if(get_boxmoe('boxmoe_llms_allow', true)){
        $lines[] = '';
        $lines[] = '## 引用说明';
        $lines[] = '- 允许搜索引擎与 AI 抓取、索引，并在回答中引用后给出原文链接。';
        $lines[] = '- 商业转载请联系站点管理员。';
    }

    $extra = boxmoe_llms_split_lines(get_boxmoe('boxmoe_llms_extra'));
    if(!empty($extra)){
        $lines[] = '';
        $lines[] = '## 补充说明';
        foreach($extra as $line){
            $lines[] = '- ' . $line;
        }
    }

    return implode("\n", $lines) . "\n";
}

/**
 * 判断当前请求是不是 /llms.txt。
 */
function boxmoe_llms_is_request(){
    if((bool) get_query_var('boxmoe_llms')){
        return true;
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if($uri === ''){
        return false;
    }
    $path = wp_parse_url($uri, PHP_URL_PATH);
    if(!is_string($path) || $path === ''){
        return false;
    }
    $expected_path = wp_parse_url(home_url('/llms.txt'), PHP_URL_PATH);
    return is_string($expected_path) && strcasecmp(rtrim($path, '/'), rtrim($expected_path, '/')) === 0;
}

/**
 * 输出并结束请求。
 */
function boxmoe_llms_render(){
    if(!boxmoe_llms_is_request()){
        return;
    }
    if(headers_sent()){
        return;
    }

    if(!boxmoe_llms_enabled()){
        global $wp_query;
        if(is_object($wp_query) && method_exists($wp_query, 'set_404')){
            $wp_query->set_404();
        }
        remove_action('template_redirect', 'redirect_canonical');
        status_header(404);
        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo "Not found.\n";
        exit;
    }

    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=3600');

    echo boxmoe_llms_content();
    exit;
}
add_action('template_redirect', 'boxmoe_llms_render', 1);

/**
 * 注册伪静态，并在规则版本变化时刷一次。
 */
function boxmoe_llms_register_rewrite(){
    add_rewrite_rule('^llms\.txt$', 'index.php?boxmoe_llms=1', 'top');

    $version = '1';
    if(get_option('boxmoe_llms_rewrite_version') !== $version){
        flush_rewrite_rules(false);
        update_option('boxmoe_llms_rewrite_version', $version, false);
    }
}
add_action('init', 'boxmoe_llms_register_rewrite', 20);

function boxmoe_llms_query_vars($vars){
    $vars[] = 'boxmoe_llms';
    return $vars;
}
add_filter('query_vars', 'boxmoe_llms_query_vars');

/**
 * 主题切换后重新生成伪静态。
 */
function boxmoe_llms_flush_on_switch(){
    flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'boxmoe_llms_flush_on_switch');
