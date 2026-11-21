<?php
/**
 * Public content policy shared by SEO, GEO and llms.txt.
 */
if(!defined('ABSPATH')){
    exit;
}

/**
 * Do not render protected shortcodes using the current visitor's permissions.
 */
function boxmoe_geo_has_protected_shortcode($content){
    return (bool) preg_match('/\[(?:zuslolimeow|vip)(?=\s|\/?\])/i', (string) $content);
}

/**
 * Return content that is safe to publish to anonymous crawlers and public caches.
 * Passwords and non-public posts never expose content or excerpts, even when the
 * current visitor can read them. Paid posts only expose a hand-written excerpt.
 *
 * @return array{content:string,excerpt:string,restricted:bool,password_protected:bool}
 */
function boxmoe_geo_public_post_data($post){
    $post = get_post($post);
    $data = array(
        'content' => '',
        'excerpt' => '',
        'restricted' => true,
        'password_protected' => false,
    );
    if(!$post){
        return $data;
    }

    $data['password_protected'] = isset($post->post_password) && (string) $post->post_password !== '';
    $public = function_exists('is_post_publicly_viewable')
        ? is_post_publicly_viewable($post)
        : (isset($post->post_status) && $post->post_status === 'publish');
    if($data['password_protected'] || !$public){
        return $data;
    }

    $content = isset($post->post_content) ? (string) $post->post_content : '';
    $excerpt = isset($post->post_excerpt) ? (string) $post->post_excerpt : '';
    $restricted = (bool) get_post_meta($post->ID, 'start_see', true)
        || (bool) get_post_meta($post->ID, 'start_see2', true)
        || boxmoe_geo_has_protected_shortcode($content)
        || boxmoe_geo_has_protected_shortcode($excerpt);

    if(class_exists('Shiroki_CP_Post_Meta')){
        $config = Shiroki_CP_Post_Meta::get_post_config($post->ID);
        $restricted = $restricted || !empty($config['enabled']);
    }

    // Other protection integrations may add restrictions, never remove them.
    $restricted = $restricted || (bool) apply_filters('boxmoe_geo_content_restricted', false, $post);
    $data['restricted'] = $restricted;
    $data['content'] = $restricted ? '' : $content;
    $data['excerpt'] = boxmoe_geo_has_protected_shortcode($excerpt) ? '' : $excerpt;
    return $data;
}
