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
    
    if(is_singular() && !is_front_page()){
        $title = get_the_title();
        $content_type = is_page() ? 'page' : 'article';
        
        if(has_post_thumbnail($post->ID)){
            $image_url = get_the_post_thumbnail_url($post->ID, 'full');
            if(!empty($image_url) && strpos($image_url, 'http') !== 0){
                $image_url = site_url($image_url);
            }
        }
        
        if(empty($image_url)){
            $image_url = boxmoe_get_first_image_from_content($post->ID);
        }
        
        if(empty($description)){
            if(!empty($post->post_excerpt)){
                $description = $post->post_excerpt;
            } else {
                $description = wp_trim_words(strip_tags($post->post_content), 50);
            }
        }
        
        $publish_date = get_the_date('c', $post->ID);
        $modified_date = get_the_modified_date('c', $post->ID);
        $author = get_the_author_meta('display_name', $post->post_author);
        
        if(get_the_tags($post->ID)){
            foreach(get_the_tags($post->ID) as $tag){
                $keywords .= $tag->name . ', ';
            }
        }
        $keywords = rtrim($keywords, ', ');
        
        $content = strip_tags($post->post_content);
        $word_count = mb_strlen($content, 'UTF-8');
        $reading_time = ceil($word_count / 500);
        
    } elseif(is_home() || is_front_page()){
        $title = get_bloginfo('name');
        $description = get_bloginfo('description');
        $content_type = 'website';
        $image_url = boxmoe_get_logo_url();
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
    echo '<meta name="geo:url" content="' . esc_url(get_permalink()) . '">' . "\n";
    echo '<meta name="geo:language" content="' . esc_attr(get_bloginfo('language')) . '">' . "\n";
}

// 🌍 Geo友好的结构化数据输出--------------------------gl.baimu.live--------------------------
function boxmoe_geo_structured_data(){
    global $post;
    
    if(!get_boxmoe('boxmoe_geo_structured_switch')){
        return;
    }
    
    $data = array(
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'url' => get_option('home'),
        'description' => get_bloginfo('description'),
        'inLanguage' => get_bloginfo('language'),
        'potentialAction' => array(
            '@type' => 'SearchAction',
            'target' => get_option('home') . '?s={search_term_string}',
            'query-input' => 'required name=search_term_string'
        )
    );
    
    if(is_singular() && !is_front_page()){
        $data['@type'] = is_page() ? 'WebPage' : 'Article';
        $data['headline'] = get_the_title();
        $data['url'] = get_permalink();
        
        if(!empty($post->post_excerpt)){
            $data['description'] = $post->post_excerpt;
        } else {
            $data['description'] = wp_trim_words(strip_tags($post->post_content), 50);
        }
        
        $data['datePublished'] = get_the_date('c', $post->ID);
        $data['dateModified'] = get_the_modified_date('c', $post->ID);
        $data['author'] = array(
            '@type' => 'Person',
            'name' => get_the_author_meta('display_name', $post->post_author)
        );
        
        $data['publisher'] = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => boxmoe_get_logo_url()
        );
        
        if(has_post_thumbnail($post->ID)){
            $image_url = get_the_post_thumbnail_url($post->ID, 'full');
            if(!empty($image_url) && strpos($image_url, 'http') !== 0){
                $image_url = site_url($image_url);
            }
            $data['image'] = $image_url;
        }
        
        $data['mainEntityOfPage'] = array(
            '@type' => 'WebPage',
            '@id' => get_permalink()
        );
        
        if(get_the_category($post->ID)){
            $categories = array();
            foreach(get_the_category($post->ID) as $category){
                $categories[] = $category->cat_name;
            }
            $data['articleSection'] = implode(', ', $categories);
        }
        
        if(get_the_tags($post->ID)){
            $tags = array();
            foreach(get_the_tags($post->ID) as $tag){
                $tags[] = $tag->name;
            }
            $data['keywords'] = implode(', ', $tags);
        }
        
        $content = strip_tags($post->post_content);
        $data['wordCount'] = mb_strlen($content, 'UTF-8');
    }
    
    echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

// 🌍 Geo友好的面包屑导航--------------------------gl.baimu.live--------------------------
function boxmoe_geo_breadcrumb_schema(){
    if(!get_boxmoe('boxmoe_geo_breadcrumb_switch')){
        return;
    }
    
    if(!is_singular()){
        return;
    }
    
    global $post;
    
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
        'item' => get_option('home')
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
    
    echo '<script type="application/ld+json">' . json_encode($breadcrumb, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

// 🌍 Geo友好的FAQ结构化数据--------------------------gl.baimu.live--------------------------
function boxmoe_geo_faq_schema(){
    if(!get_boxmoe('boxmoe_geo_faq_switch')){
        return;
    }
    
    if(!is_singular()){
        return;
    }
    
    global $post;
    $content = $post->post_content;
    
    preg_match_all('/<h3[^>]*>(.*?)<\/h3>/i', $content, $headings);
    if(empty($headings[1])){
        return;
    }
    
    $faq_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array()
    );
    
    foreach($headings[1] as $heading){
        $faq_data['mainEntity'][] = array(
            '@type' => 'Question',
            'name' => strip_tags($heading),
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text' => ''
            )
        );
    }
    
    if(!empty($faq_data['mainEntity'])){
        echo '<script type="application/ld+json">' . json_encode($faq_data, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}

// 🌍 Geo友好的文章摘要优化--------------------------gl.baimu.live--------------------------
function boxmoe_geo_content_summary(){
    if(!get_boxmoe('boxmoe_geo_summary_switch')){
        return;
    }
    
    if(!is_singular()){
        return;
    }
    
    global $post;
    
    $summary = '';
    
    if(!empty($post->post_excerpt)){
        $summary = $post->post_excerpt;
    } else {
        $content = strip_tags($post->post_content);
        $sentences = preg_split('/[。！？.!?]/', $content);
        $summary = '';
        $count = 0;
        foreach($sentences as $sentence){
            if(trim($sentence) && $count < 3){
                $summary .= trim($sentence) . '。';
                $count++;
            }
        }
    }
    
    if(!empty($summary)){
        echo '<meta name="geo:summary" content="' . esc_attr($summary) . '">' . "\n";
    }
}

// 🌍 Geo友好的内容质量评分--------------------------gl.baimu.live--------------------------
function boxmoe_geo_content_quality(){
    if(!get_boxmoe('boxmoe_geo_quality_switch') || !is_singular()){
        return;
    }
    
    global $post;
    $score = 0;
    $content = strip_tags($post->post_content);
    $content_length = mb_strlen($content, 'UTF-8');
    
    if($content_length > 500){
        $score += 20;
    }
    if($content_length > 1000){
        $score += 20;
    }
    if($content_length > 2000){
        $score += 20;
    }
    
    if(has_post_thumbnail($post->ID)){
        $score += 10;
    }
    
    if(!empty($post->post_excerpt)){
        $score += 10;
    }
    
    if(get_the_tags($post->ID)){
        $score += 10;
    }
    
    if(get_the_category($post->ID)){
        $score += 10;
    }
    
    $quality_score = min($score, 100);
    
    echo '<meta name="geo:quality_score" content="' . esc_attr($quality_score) . '">' . "\n";
    echo '<meta name="geo:content_length" content="' . esc_attr($content_length) . '">' . "\n";
}

// 🌍 Geo友好的主题分类--------------------------gl.baimu.live--------------------------
function boxmoe_geo_topic_classification(){
    if(!get_boxmoe('boxmoe_geo_topic_switch') || !is_singular()){
        return;
    }
    
    global $post;
    
    $topics = array();
    
    $categories = get_the_category($post->ID);
    if($categories){
        foreach($categories as $category){
            $topics[] = $category->cat_name;
        }
    }
    
    $tags = get_the_tags($post->ID);
    if($tags){
        foreach($tags as $tag){
            $topics[] = $tag->name;
        }
    }
    
    if(!empty($topics)){
        echo '<meta name="geo:topics" content="' . esc_attr(implode(', ', $topics)) . '">' . "\n";
    }
}

// 🌍 Geo友好的相关内容--------------------------gl.baimu.live--------------------------
function boxmoe_geo_related_content(){
    if(!get_boxmoe('boxmoe_geo_related_switch') || !is_singular()){
        return;
    }
    
    global $post;
    
    $related_posts = get_posts(array(
        'post__not_in' => array($post->ID),
        'posts_per_page' => 5,
        'category__in' => wp_get_post_categories($post->ID),
        'orderby' => 'rand'
    ));
    
    if(!empty($related_posts)){
        $related_urls = array();
        foreach($related_posts as $related_post){
            $related_urls[] = get_permalink($related_post->ID);
        }
        echo '<meta name="geo:related_articles" content="' . esc_attr(implode(', ', $related_urls)) . '">' . "\n";
    }
}

// 🌍 Geo友好的多语言支持--------------------------gl.baimu.live--------------------------
function boxmoe_geo_language_support(){
    if(!get_boxmoe('boxmoe_geo_language_switch')){
        return;
    }
    
    $language = get_bloginfo('language');
    echo '<meta name="geo:language" content="' . esc_attr($language) . '">' . "\n";
    
    if(function_exists('pll_current_language') && function_exists('pll_the_translations')){
        $current_lang = pll_current_language();
        echo '<meta name="geo:current_language" content="' . esc_attr($current_lang) . '">' . "\n";
        
        $translations = pll_the_translations();
        if(!empty($translations)){
            $alternate_urls = array();
            foreach($translations as $lang => $translation){
                if($lang !== $current_lang && isset($translation->ID)){
                    $alternate_urls[] = $lang . ':' . get_permalink($translation->ID);
                }
            }
            if(!empty($alternate_urls)){
                echo '<meta name="geo:alternate_languages" content="' . esc_attr(implode('; ', $alternate_urls)) . '">' . "\n";
            }
        }
    }
}

// 🌍 Geo友好的移动端优化--------------------------gl.baimu.live--------------------------
function boxmoe_geo_mobile_optimization(){
    if(!get_boxmoe('boxmoe_geo_mobile_switch')){
        return;
    }
    
    echo '<meta name="geo:mobile_friendly" content="true">' . "\n";
    echo '<meta name="geo:viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '<meta name="geo:responsive" content="true">' . "\n";
}

// 🌍 Geo友好的性能优化--------------------------gl.baimu.live--------------------------
function boxmoe_geo_performance_optimization(){
    if(!get_boxmoe('boxmoe_geo_performance_switch')){
        return;
    }
    
    echo '<meta name="geo:performance" content="optimized">' . "\n";
    echo '<meta name="geo:loading" content="fast">' . "\n";
    echo '<meta name="geo:cache" content="enabled">' . "\n";
}

// 🌍 Geo友好的本地化信息--------------------------gl.baimu.live--------------------------
function boxmoe_geo_localization(){
    if(!get_boxmoe('boxmoe_geo_local_switch')){
        return;
    }
    
    $geo_region = get_boxmoe('boxmoe_geo_region');
    if(!empty($geo_region)){
        echo '<meta name="geo:region" content="' . esc_attr($geo_region) . '">' . "\n";
    }
    
    $geo_city = get_boxmoe('boxmoe_geo_city');
    if(!empty($geo_city)){
        echo '<meta name="geo:city" content="' . esc_attr($geo_city) . '">' . "\n";
    }
    
    $geo_country = get_boxmoe('boxmoe_geo_country');
    if(!empty($geo_country)){
        echo '<meta name="geo:country" content="' . esc_attr($geo_country) . '">' . "\n";
    }
}

// 🌍 Geo友好的作者信息--------------------------gl.baimu.live--------------------------
function boxmoe_geo_author_info(){
    if(!get_boxmoe('boxmoe_geo_author_switch') || !is_singular()){
        return;
    }
    
    global $post;
    $author_id = $post->post_author;
    
    echo '<meta name="geo:author_name" content="' . esc_attr(get_the_author_meta('display_name', $author_id)) . '">' . "\n";
    echo '<meta name="geo:author_url" content="' . esc_url(get_author_posts_url($author_id)) . '">' . "\n";
    
    $author_description = get_the_author_meta('description', $author_id);
    if(!empty($author_description)){
        echo '<meta name="geo:author_description" content="' . esc_attr($author_description) . '">' . "\n";
    }
}

// 🌍 Geo友好的社交媒体信息--------------------------gl.baimu.live--------------------------
function boxmoe_geo_social_info(){
    if(!get_boxmoe('boxmoe_geo_social_switch') || !is_singular()){
        return;
    }
    
    global $post;
    
    $share_count = get_post_meta($post->ID, 'share_count', true);
    if(!empty($share_count)){
        echo '<meta name="geo:share_count" content="' . esc_attr($share_count) . '">' . "\n";
    }
    
    $view_count = get_post_meta($post->ID, 'views', true);
    if(!empty($view_count)){
        echo '<meta name="geo:view_count" content="' . esc_attr($view_count) . '">' . "\n";
    }
    
    $comment_count = get_comments_number($post->ID);
    if($comment_count > 0){
        echo '<meta name="geo:comment_count" content="' . esc_attr($comment_count) . '">' . "\n";
    }
}

// 🌍 Geo友好的所有Meta标签集成输出--------------------------gl.baimu.live--------------------------
function boxmoe_geo_all_meta_output(){
    boxmoe_geo_meta_tags();
    boxmoe_geo_structured_data();
    boxmoe_geo_breadcrumb_schema();
    boxmoe_geo_faq_schema();
    boxmoe_geo_content_summary();
    boxmoe_geo_content_quality();
    boxmoe_geo_topic_classification();
    boxmoe_geo_related_content();
    boxmoe_geo_language_support();
    boxmoe_geo_mobile_optimization();
    boxmoe_geo_performance_optimization();
    boxmoe_geo_localization();
    boxmoe_geo_author_info();
    boxmoe_geo_social_info();
}
add_action('wp_head', 'boxmoe_geo_all_meta_output', 20);
