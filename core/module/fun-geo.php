<?php
/**
 * @link https://gl.baimu.live/
 * @package 白木-灵阈研都-纸鸢社
 * @description Geo生成式搜索引擎优化配置 - 优化站点让Geo更好地获取和理解内容
 */

// 安全设置--------------------------gl.baimu.live--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

require_once __DIR__ . '/fun-geo-content.php';

// 🌍 Geo友好的Meta标签输出--------------------------gl.baimu.live--------------------------
function boxmoe_geo_meta_tags(){
    global $post;
    
    if(!get_boxmoe('boxmoe_geo_meta_switch')){
        return;
    }
    
    $title = '';
    $description = '';
    $image_url = '';
    $content_type = 'website';
    $publish_date = '';
    $modified_date = '';
    $author = '';
    $keywords = '';
    $reading_time = '';
    $word_count = '';
    
    if(is_singular() && !is_front_page() && $post instanceof WP_Post){
        $public_data = boxmoe_geo_public_post_data($post);
        $title = get_the_title();
        $content_type = is_page() ? 'page' : 'article';
        
        if(!$public_data['restricted'] && has_post_thumbnail($post->ID)){
            $image_url = boxmoe_geo_image_url(get_the_post_thumbnail_url($post->ID, 'full'));
        }
        
        if(empty($image_url) && !$public_data['restricted']){
            $image_url = boxmoe_get_first_image_from_content($post->ID);
        }
        
        if(empty($description)){
            if($public_data['excerpt'] !== ''){
                $description = wp_strip_all_tags(strip_shortcodes($public_data['excerpt']));
            } else {
                $description = wp_trim_words(wp_strip_all_tags(strip_shortcodes($public_data['content'])), 50);
            }
        }
        
        $publish_date = get_the_date('c', $post->ID);
        $modified_date = get_the_modified_date('c', $post->ID);
        $author = get_the_author_meta('display_name', $post->post_author);
        
        $post_tags = get_the_tags($post->ID);
        if(is_array($post_tags)){
            foreach($post_tags as $tag){
                if(is_object($tag) && isset($tag->name)){
                    $keywords .= wp_strip_all_tags($tag->name) . ', ';
                }
            }
        }
        $keywords = rtrim($keywords, ', ');
        
        $content = wp_strip_all_tags(strip_shortcodes($public_data['content']));
        if($content !== ''){
            $word_count = mb_strlen($content, 'UTF-8');
            $reading_time = ceil($word_count / 500);
        }
        
    } elseif(is_home() || is_front_page()){
        $title = get_bloginfo('name');
        $description = get_bloginfo('description');
        $content_type = 'website';
        $image_url = boxmoe_geo_image_url(boxmoe_get_logo_url());
    }
    
    if(empty($title)){
        $title = get_bloginfo('name');
    }
    
    echo '<meta name="geo:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="geo:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="geo:type" content="' . esc_attr($content_type) . '">' . "\n";
    
    if(!empty($image_url)){
        echo '<meta name="geo:image" content="' . esc_url($image_url) . '">' . "\n";
    }
    
    if(!empty($publish_date)){
        echo '<meta name="geo:publish_date" content="' . esc_attr($publish_date) . '">' . "\n";
    }
    
    if(!empty($modified_date)){
        echo '<meta name="geo:modified_date" content="' . esc_attr($modified_date) . '">' . "\n";
    }
    
    if(!empty($author)){
        echo '<meta name="geo:author" content="' . esc_attr($author) . '">' . "\n";
    }
    
    if(!empty($keywords)){
        echo '<meta name="geo:keywords" content="' . esc_attr($keywords) . '">' . "\n";
    }
    
    if(!empty($word_count)){
        echo '<meta name="geo:word_count" content="' . esc_attr($word_count) . '">' . "\n";
    }
    
    if(!empty($reading_time)){
        echo '<meta name="geo:reading_time" content="' . esc_attr($reading_time) . '分钟">' . "\n";
    }
    
    echo '<meta name="geo:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta name="geo:url" content="' . esc_url(boxmoe_geo_current_url()) . '">' . "\n";
    echo '<meta name="geo:language" content="' . esc_attr(get_bloginfo('language')) . '">' . "\n";
}

// 🌍 GEO 选项读取工具--------------------------gl.baimu.live--------------------------
function boxmoe_geo_option_text($key){
    $value = get_boxmoe($key);
    return is_string($value) ? trim(wp_strip_all_tags($value)) : '';
}

function boxmoe_geo_option_lines($key){
    $raw = get_boxmoe($key);
    if(!is_string($raw) || trim($raw) === ''){
        return array();
    }
    $out = array();
    foreach(preg_split('/[\r\n]+/', $raw) as $line){
        $line = trim($line);
        if($line !== ''){
            $out[] = $line;
        }
    }
    return array_values(array_unique($out));
}

// Resolve URLs without fetching remote images during page rendering.
function boxmoe_geo_image_url($url, $base_url = ''){
    if(!is_string($url) || trim($url) === ''){
        return '';
    }
    $url = html_entity_decode(trim($url), ENT_QUOTES, 'UTF-8');
    if(preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) && !preg_match('#^https?://#i', $url)){
        return '';
    }
    $base_url = $base_url !== '' ? $base_url : home_url('/');
    return esc_url_raw(WP_Http::make_absolute_url($url, $base_url), array('http', 'https'));
}

function boxmoe_geo_current_url(){
    $url = function_exists('boxmoe_canonical_url') ? boxmoe_canonical_url() : '';
    if(is_string($url) && $url !== ''){
        return $url;
    }
    if(is_front_page() || is_home()){
        return is_paged() ? get_pagenum_link(max(1, (int) get_query_var('paged'))) : home_url('/');
    }
    if(is_singular()){
        return get_permalink(get_queried_object_id());
    }
    if(is_search()){
        return get_search_link();
    }
    return home_url('/');
}

function boxmoe_geo_json_script($data){
    $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if($json !== false){
        echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }
}

// 🩹 兜底实现：GEO 模块长期处于关闭状态，这两个函数在主题里从未定义过--------------------------gl.baimu.live--------------------------
if(!function_exists('boxmoe_get_logo_url')){
    function boxmoe_get_logo_url(){
        $custom = trim((string) get_boxmoe('boxmoe_org_logo'));
        if($custom !== ''){
            return $custom;
        }
        if(function_exists('boxmoe_get_logo_src')){
            $src = boxmoe_get_logo_src();
            if($src){
                return $src;
            }
        }
        return get_template_directory_uri() . '/assets/images/logo.png';
    }
}

if(!function_exists('boxmoe_get_first_image_from_content')){
    function boxmoe_get_first_image_from_content($post_id){
        $post = get_post($post_id);
        if(!$post){
            return '';
        }
        $public_data = boxmoe_geo_public_post_data($post);
        if($public_data['restricted'] || $public_data['content'] === ''){
            return '';
        }
        if(preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $public_data['content'], $match)){
            return boxmoe_geo_image_url($match[1], get_permalink($post->ID));
        }
        return '';
    }
}

// 🏛️ 站点身份（Organization）：后台可配置，未配置时回退站点信息--------------------------gl.baimu.live--------------------------
function boxmoe_geo_organization_data(){
    $name = boxmoe_geo_option_text('boxmoe_org_name');
    if($name === ''){
        $name = get_bloginfo('name');
    }

    $description = boxmoe_geo_option_text('boxmoe_org_description');
    if($description === ''){
        $description = get_bloginfo('description');
    }

    $type = boxmoe_geo_option_text('boxmoe_org_type');
    $allowed_types = array('Organization', 'Corporation', 'NGO', 'EducationalOrganization', 'GovernmentOrganization', 'LocalBusiness', 'Person');
    if(!in_array($type, $allowed_types, true)){
        $type = 'Organization';
    }

    $logo = boxmoe_geo_image_url(boxmoe_get_logo_url());

    $org = array(
        '@type' => $type,
        '@id'   => home_url('/#organization'),
        'name'  => $name,
        'url'   => home_url('/'),
    );

    if($description !== ''){
        $org['description'] = $description;
    }

    $alt_names = preg_split('/[,，\r\n]+/u', boxmoe_geo_option_text('boxmoe_org_alt_names'));
    $alt_names = array_values(array_unique(array_filter(array_map('trim', $alt_names), function($name){ return $name !== ''; })));
    if(!empty($alt_names)){
        $org['alternateName'] = (count($alt_names) === 1) ? $alt_names[0] : $alt_names;
    }

    if($logo !== ''){
        $org['logo'] = array(
            '@type'   => 'ImageObject',
            '@id'     => home_url('/#logo'),
            'url'     => $logo,
            'caption' => $name,
        );
    }

    $same_as = array();
    foreach(boxmoe_geo_option_lines('boxmoe_org_sameas') as $url){
        $url = esc_url_raw($url);
        if($url === ''){
            continue;
        }
        // 跳过明显不是网址的输入（没有域名点号），避免污染 sameAs
        $host = wp_parse_url($url, PHP_URL_HOST);
        if(empty($host) || strpos($host, '.') === false){
            continue;
        }
        $same_as[] = $url;
    }
    if(!empty($same_as)){
        $org['sameAs'] = array_values(array_unique($same_as));
    }

    $knows = boxmoe_geo_option_lines('boxmoe_org_knows_about');
    if(!empty($knows)){
        $org['knowsAbout'] = array_values($knows);
    }

    $email = sanitize_email(boxmoe_geo_option_text('boxmoe_org_email'));
    if($email !== ''){
        $org['email'] = $email;
    }

    return $org;
}
// 📄 文章 / 页面节点--------------------------gl.baimu.live--------------------------
function boxmoe_geo_singular_data($post, $org){
    $public_data = boxmoe_geo_public_post_data($post);
    $node = array(
        '@type'      => is_page() ? 'WebPage' : 'Article',
        '@id'        => get_permalink($post->ID) . '#main',
        'url'        => get_permalink($post->ID),
        'name'       => get_the_title($post->ID),
        'isPartOf'   => array('@id' => home_url('/#website')),
        'inLanguage' => str_replace('_', '-', get_bloginfo('language')),
    );

    if(!is_page()){
        $node['headline']      = get_the_title($post->ID);
        $node['datePublished'] = get_the_date('c', $post->ID);
        $node['dateModified']  = get_the_modified_date('c', $post->ID);
        $node['author']        = array(
            '@type' => 'Person',
            'name'  => get_the_author_meta('display_name', $post->post_author),
            'url'   => get_author_posts_url($post->post_author),
        );
        $node['publisher'] = array('@id' => $org['@id']);
    }

    if($public_data['excerpt'] !== ''){
        $description = wp_strip_all_tags(strip_shortcodes($public_data['excerpt']));
    } else {
        $description = wp_trim_words(wp_strip_all_tags(strip_shortcodes($public_data['content'])), 60, '…');
    }
    if($description !== ''){
        $node['description'] = wp_strip_all_tags($description);
    }

    $image = '';
    if(!$public_data['restricted'] && has_post_thumbnail($post->ID)){
        $image = boxmoe_geo_image_url(get_the_post_thumbnail_url($post->ID, 'full'));
    }
    if(empty($image) && !$public_data['restricted']){
        $image = boxmoe_get_first_image_from_content($post->ID);
    }
    if(!empty($image)){
        $node['image'] = $image;
    }

    $categories = array();
    foreach((array) get_the_category($post->ID) as $category){
        if(!is_object($category) || !isset($category->cat_name)){
            continue;
        }
        $name = trim(wp_strip_all_tags($category->cat_name));
        if($name !== ''){
            $categories[] = $name;
        }
    }
    if(!empty($categories)){
        $node['articleSection'] = implode(', ', array_unique($categories));
    }

    $tags = array();
    $post_tags = get_the_tags($post->ID);
    if(is_array($post_tags)){
        foreach($post_tags as $tag){
            if(!is_object($tag) || !isset($tag->name)){
                continue;
            }
            $name = trim(wp_strip_all_tags($tag->name));
            if($name !== ''){
                $tags[] = $name;
            }
        }
    }
    if(!empty($tags)){
        $node['keywords'] = implode(', ', array_unique($tags));
    }
    $plain = wp_strip_all_tags(strip_shortcodes($public_data['content']));
    if($plain !== ''){
        $node['wordCount'] = (int) mb_strlen($plain, 'UTF-8');
    }

    return $node;
}

// 🌍 Geo友好的结构化数据输出：Organization + WebSite + Article/WebPage（单个 @graph）--------------------------gl.baimu.live--------------------------
function boxmoe_geo_structured_data(){
    if(!get_boxmoe('boxmoe_geo_structured_switch', true)){
        return;
    }

    $org   = boxmoe_geo_organization_data();
    $graph = array($org);

    // WebSite 节点每页都输出，保证文章页里的 isPartOf / publisher 引用不会悬空
    $website = array(
        '@type'       => 'WebSite',
        '@id'         => home_url('/#website'),
        'url'         => home_url('/'),
        'name'        => get_bloginfo('name'),
        'inLanguage'  => str_replace('_', '-', get_bloginfo('language')),
        'publisher'   => array('@id' => $org['@id']),
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => array(
                '@type'       => 'EntryPoint',
                'urlTemplate' => home_url('/?s={search_term_string}'),
            ),
            'query-input' => 'required name=search_term_string',
        ),
    );
    $site_description = trim((string) get_bloginfo('description'));
    if($site_description !== ''){
        $website['description'] = $site_description;
    }
    $graph[] = $website;

    if(is_singular()){
        global $post;
        if($post instanceof WP_Post){
            $graph[] = boxmoe_geo_singular_data($post, $org);
        }
    }

    boxmoe_geo_json_script(array('@context' => 'https://schema.org', '@graph' => $graph));
}

// 🌍 Geo友好的面包屑导航--------------------------gl.baimu.live--------------------------
function boxmoe_geo_breadcrumb_schema(){
    if(!get_boxmoe('boxmoe_geo_breadcrumb_switch', true)){
        return;
    }
    
    if(!is_singular()){
        return;
    }
    
    global $post;
    if(!($post instanceof WP_Post)){
        return;
    }
    
    $breadcrumb = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );
    
    $position = 1;
    
    $breadcrumb['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $position,
        'name' => '首页',
        'item' => home_url('/')
    );
    $position++;
    
    if(is_single() || is_page()){
        $categories = get_the_category($post->ID);
        if($categories){
            foreach($categories as $category){
                $breadcrumb['itemListElement'][] = array(
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $category->cat_name,
                    'item' => get_category_link($category->cat_ID)
                );
                $position++;
                break;
            }
        }
        
        $breadcrumb['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title(),
            'item' => get_permalink()
        );
    }
    
    boxmoe_geo_json_script($breadcrumb);
}

// 🌍 Geo友好的FAQ结构化数据--------------------------gl.baimu.live--------------------------
function boxmoe_geo_faq_schema(){
    if(!get_boxmoe('boxmoe_geo_faq_switch', false)){
        return;
    }
    
    if(!is_singular()){
        return;
    }
    
    global $post;
    if(!($post instanceof WP_Post)){
        return;
    }
    $public_data = boxmoe_geo_public_post_data($post);
    $content = $public_data['content'];
    
    // 以 h3 为界切段：三级标题 + 紧随的正文，才构成一组有效问答
    preg_match_all('/<h3[^>]*>(.*?)<\/h3>(.*?)(?=<h[1-3][^>]*>|$)/is', $content, $matches, PREG_SET_ORDER);
    if(empty($matches)){
        return;
    }
    
    $questions = array();
    foreach($matches as $match){
        $question = trim(wp_strip_all_tags($match[1]));
        $answer = trim(wp_strip_all_tags(strip_shortcodes($match[2])));
        if($question === '' || $answer === ''){
            continue;
        }
        if(mb_strlen($answer, 'UTF-8') > 300){
            $answer = mb_substr($answer, 0, 300, 'UTF-8') . '…';
        }
        $questions[] = array(
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text' => $answer
            )
        );
    }
    
    if(empty($questions)){
        return;
    }
    
    boxmoe_geo_json_script(array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $questions));
}

// 🌍 Geo友好的所有Meta标签集成输出--------------------------gl.baimu.live--------------------------
function boxmoe_geo_all_meta_output(){
    boxmoe_geo_meta_tags();
    boxmoe_geo_structured_data();
    boxmoe_geo_breadcrumb_schema();
    boxmoe_geo_faq_schema();
}
add_action('wp_head', 'boxmoe_geo_all_meta_output', 20);
