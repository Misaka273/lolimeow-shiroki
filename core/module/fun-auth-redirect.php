<?php
/** Shared return destinations for the theme's login and registration flows. */
if (!defined('ABSPATH')) {
    exit;
}

/** Match authentication pages with both pretty and query-string permalinks. */
function boxmoe_auth_is_auth_url($url) {
    $parts = wp_parse_url($url);
    if (!is_array($parts)) {
        return false;
    }
    $path = untrailingslashit(rawurldecode($parts['path'] ?? '/'));
    if (basename($path) === 'wp-login.php' || basename($path) === 'admin-ajax.php') {
        return true;
    }

    $auth_urls = array(
        boxmoe_sign_in_link_page(),
        boxmoe_reset_password_link_page(),
    );
    // Old standalone registration pages still redirect to the combined form.
    $signup_page = get_boxmoe('boxmoe_sign_up_link_page');
    if (is_numeric($signup_page) && $signup_page > 0) {
        $auth_urls[] = get_permalink($signup_page);
    }
    parse_str($parts['query'] ?? '', $query);
    foreach (array_filter($auth_urls) as $auth_url) {
        $auth_parts = wp_parse_url($auth_url);
        if ($path !== untrailingslashit(rawurldecode($auth_parts['path'] ?? '/'))) {
            continue;
        }
        parse_str($auth_parts['query'] ?? '', $auth_query);
        unset($auth_query['redirect_to'], $auth_query['mode']);
        $matches = true;
        foreach ($auth_query as $key => $value) {
            if (!isset($query[$key]) || !is_scalar($query[$key]) || (string) $query[$key] !== (string) $value) {
                $matches = false;
                break;
            }
        }
        if ($matches) {
            return true;
        }
    }

    // A legacy form can remain accessible even after its configured binding changes.
    $lookup_url = $url;
    if (strpos($url, '/') === 0) {
        $home = wp_parse_url(home_url('/'));
        $lookup_url = $home['scheme'] . '://' . $home['host']
            . (isset($home['port']) ? ':' . $home['port'] : '') . $url;
    }
    $page_id = url_to_postid($lookup_url);
    return $page_id && in_array(basename((string) get_page_template_slug($page_id)), array(
        'p-signin.php', 'p-signup.php', 'p-reset_password.php',
    ), true);
}

/** Only return to this site's HTTP(S) pages, never back to an auth form. */
function boxmoe_auth_redirect_url($candidate, $fallback = '') {
    if (!is_string($candidate) || $candidate === '') {
        return $fallback;
    }
    // Reject ambiguous browser URL parsing before WordPress sanitizes the URL.
    if (preg_match('/[\x00-\x20\x7f\\\\]/', $candidate) || strpos($candidate, '//') === 0) {
        return $fallback;
    }
    $url = wp_validate_redirect($candidate, '');
    if ($url === '') {
        return $fallback;
    }
    $parts = wp_parse_url($url);
    $home = wp_parse_url(home_url('/'));
    if (!is_array($parts) || isset($parts['user']) || isset($parts['pass'])) {
        return $fallback;
    }
    parse_str($parts['query'] ?? '', $query);
    foreach (array('page_id', 'p', 'pagename', 'attachment_id') as $key) {
        if (isset($query[$key]) && !is_scalar($query[$key])) {
            return $fallback;
        }
    }
    if (isset($parts['host'])) {
        $scheme = strtolower($parts['scheme'] ?? '');
        $home_scheme = strtolower($home['scheme'] ?? '');
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $home_port = $home['port'] ?? ($home_scheme === 'https' ? 443 : 80);
        if (!in_array($scheme, array('http', 'https'), true)
            || $scheme !== $home_scheme
            || strtolower($parts['host']) !== strtolower($home['host'])
            || $port !== $home_port) {
            return $fallback;
        }
    } elseif (isset($parts['scheme']) || strpos($candidate, '/') !== 0) {
        return $fallback;
    }
    return boxmoe_auth_is_auth_url($url) ? $fallback : $url;
}

/** Keep an existing destination when switching forms; otherwise use this page. */
function boxmoe_auth_redirect_source() {
    $explicit = isset($_GET['redirect_to']) ? wp_unslash($_GET['redirect_to']) : '';
    $redirect = boxmoe_auth_redirect_url($explicit);
    if ($redirect !== '') {
        return $redirect;
    }
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $redirect = boxmoe_auth_redirect_url($request_uri);
    if ($redirect !== '') {
        return $redirect;
    }
    return boxmoe_auth_redirect_url(isset($_SERVER['HTTP_REFERER']) ? wp_unslash($_SERVER['HTTP_REFERER']) : '');
}

/** Encode the new query value once; PHP decodes it when reading redirect_to. */
function boxmoe_auth_entry_url($url, $redirect = null) {
    if (empty($url)) {
        return '';
    }
    if ($redirect === null) {
        $redirect = boxmoe_auth_redirect_source();
    }
    $redirect = boxmoe_auth_redirect_url($redirect);
    return $redirect === '' ? $url : add_query_arg('redirect_to', rawurlencode($redirect), $url);
}

/** An explicit source takes precedence for administrators as well as members. */
function boxmoe_auth_success_redirect($candidate, $user = null) {
    $fallback = $user && user_can($user, 'manage_options')
        ? admin_url()
        : boxmoe_user_center_link_page();
    return boxmoe_auth_redirect_url($candidate, $fallback);
}
