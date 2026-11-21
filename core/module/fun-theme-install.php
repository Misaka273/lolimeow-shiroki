<?php
/** Keep this theme's upload directory stable and retain the site's image tree. */
if (!defined('ABSPATH')) {
    exit;
}

function shiroki_theme_directory_slug() {
    return basename(dirname(__DIR__, 2));
}

function shiroki_theme_is_own_installation($slug) {
    $themes = wp_get_themes(array('errors' => null));
    if (!isset($themes[$slug])) {
        return false;
    }
    $theme = $themes[$slug];
    return $theme->exists() && ($slug === shiroki_theme_directory_slug()
        || $theme->get('TextDomain') === 'lolimeow-shiroki'
        || $theme->get('Name') === 'lolimeow-纸鸢版');
}

function shiroki_theme_zip_slug($filename) {
    $filename = basename(str_replace('\\', '/', (string) $filename));
    if (!preg_match('/\.zip$/i', $filename)) {
        return '';
    }
    $slug = substr($filename, 0, -4);
    return $slug !== '' && $slug !== '.' && $slug !== '..'
        && !preg_match('/[\x00-\x1f\x7f\/:*?"<>|\\\\]/', $slug) ? $slug : '';
}

/** Prefer the original filename; upload storage can append a collision suffix. */
function shiroki_theme_install_package_slug($options) {
    if (!empty($_FILES['themezip']['name']) && is_string($_FILES['themezip']['name'])) {
        return shiroki_theme_zip_slug(wp_unslash($_FILES['themezip']['name']));
    }
    $upload = $GLOBALS['file_upload'] ?? null;
    if (is_object($upload) && isset($upload->filename, $upload->package)
        && $upload->package === ($options['package'] ?? '')) {
        return shiroki_theme_zip_slug($upload->filename);
    }
    $path = wp_parse_url($options['package'] ?? '', PHP_URL_PATH);
    return is_string($path) ? shiroki_theme_zip_slug(rawurldecode(basename($path))) : '';
}

add_filter('upgrader_package_options', 'shiroki_theme_install_options');
function shiroki_theme_install_options($options) {
    $extra = $options['hook_extra'] ?? array();
    if (($extra['type'] ?? '') !== 'theme' || ($extra['action'] ?? '') !== 'install') {
        return $options;
    }
    $slug = shiroki_theme_install_package_slug($options);
    if ($slug === '') {
        return $options;
    }
    $options['hook_extra']['shiroki_package_slug'] = $slug;
    if (!shiroki_theme_is_own_installation($slug)) {
        return $options;
    }
    $options['hook_extra']['shiroki_install_target'] = $slug;
    $options['destination'] = get_theme_root($slug);
    $options['clear_destination'] = true;
    $options['abort_if_destination_exists'] = false;
    if (empty($options['hook_extra']['temp_backup'])) {
        $options['hook_extra']['temp_backup'] = array(
            'slug' => $slug,
            'src' => get_theme_root($slug),
            'dir' => 'themes',
        );
    }
    return $options;
}

function shiroki_theme_prepare_error($step) {
    return new WP_Error('shiroki_theme_prepare_failed', '主题安装准备失败，现有主题尚未替换。', $step);
}

/** Lexical checks keep every staging mutation inside the unpacked package. */
function shiroki_theme_staging_contains($path, $root, $allow_root = false) {
    $path = untrailingslashit(wp_normalize_path($path));
    $root = untrailingslashit(wp_normalize_path($root));
    if ($root === '' || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path . '/')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $root . '/')) {
        return false;
    }
    return ($allow_root && $path === $root) || strpos($path, $root . '/') === 0;
}

/** Runs after Theme_Upgrader::check_package(), before core moves the old theme. */
add_filter('upgrader_source_selection', 'shiroki_fix_upgrader_source_selection', 20, 4);
function shiroki_fix_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
    global $wp_filesystem;
    if (is_wp_error($source) || !($upgrader instanceof Theme_Upgrader)) {
        return $source;
    }
    $action = $hook_extra['action'] ?? (isset($hook_extra['theme']) ? 'update' : '');
    $type = $hook_extra['type'] ?? (isset($hook_extra['theme']) ? 'theme' : '');
    if ($type !== 'theme' || !in_array($action, array('install', 'update'), true)) {
        return $source;
    }
    $original_source = $source;
    $target = $hook_extra['shiroki_install_target'] ?? '';
    if ($action === 'update') {
        $target = $hook_extra['theme'] ?? '';
        if ($target === '' || !shiroki_theme_is_own_installation($target)) {
            return $source;
        }
    }
    $source = trailingslashit($source);
    $remote_source = trailingslashit($remote_source);
    $headers = $wp_filesystem->get_contents($source . 'style.css');
    $own_package = is_string($headers) && (preg_match('/^[ \t\/*#@]*Text Domain:\s*lolimeow-shiroki\s*(?:\*\/)?\s*$/mi', $headers)
        || preg_match('/^[ \t\/*#@]*Theme Name:\s*lolimeow-纸鸢版\s*(?:\*\/)?\s*$/mi', $headers));
    if (!$own_package) {
        return $target !== '' ? shiroki_theme_prepare_error('package_identity') : $original_source;
    }
    if ($target === '') {
        $target = $hook_extra['shiroki_package_slug'] ?? shiroki_theme_directory_slug();
    } elseif ($action === 'install' && ($hook_extra['shiroki_package_slug'] ?? '') !== $target) {
        return shiroki_theme_prepare_error('package_target');
    }
    if (shiroki_theme_zip_slug($target . '.zip') !== $target
        || !shiroki_theme_staging_contains($remote_source, trailingslashit($wp_filesystem->wp_content_dir()) . 'upgrade')
        || !shiroki_theme_staging_contains($source, $remote_source, true)) {
        return shiroki_theme_prepare_error('staging_path');
    }

    // Prepare a clean, predictably named source without touching the installation.
    $files = $wp_filesystem->dirlist($source);
    if ($files === false) {
        return shiroki_theme_prepare_error('source_read');
    }
    foreach (array_keys($files) as $name) {
        if ($name === '.' || $name === '..' || basename(str_replace('\\', '/', $name)) !== (string) $name) {
            return shiroki_theme_prepare_error('source_entry');
        }
    }
    $stage = $remote_source . '.shiroki-prepared-' . wp_generate_password(12, false, false);
    if ($wp_filesystem->exists($stage) || !$wp_filesystem->mkdir($stage, FS_CHMOD_DIR)) {
        return shiroki_theme_prepare_error('stage_create');
    }
    $prepared = trailingslashit($stage) . $target;
    if (untrailingslashit(wp_normalize_path($source)) === untrailingslashit(wp_normalize_path($remote_source))) {
        if (!$wp_filesystem->mkdir($prepared, FS_CHMOD_DIR)) {
            return shiroki_theme_prepare_error('theme_stage_create');
        }
        foreach ($files as $name => $file) {
            $from = $source . $name;
            $to = trailingslashit($prepared) . $name;
            $moved = $wp_filesystem->is_dir($from) ? move_dir($from, $to) : $wp_filesystem->move($from, $to);
            if (is_wp_error($moved)) {
                return $moved;
            }
            if (!$moved) {
                return shiroki_theme_prepare_error('source_move');
            }
        }
    } else {
        $moved = move_dir($source, $prepared);
        if (is_wp_error($moved)) {
            return $moved;
        }
        if (!$moved) {
            return shiroki_theme_prepare_error('source_move');
        }
    }

    $installed_root = $wp_filesystem->find_folder(get_theme_root($target));
    if (!$installed_root) {
        return shiroki_theme_prepare_error('theme_root');
    }
    $old_images = trailingslashit($installed_root) . $target . '/assets/images';
    $new_assets = trailingslashit($prepared) . 'assets';
    $new_images = $new_assets . '/images';
    if ($wp_filesystem->is_dir($old_images)) {
        if (!$wp_filesystem->is_readable($old_images)
            || ($wp_filesystem->exists($new_images) && !$wp_filesystem->delete($new_images, true))
            || (!$wp_filesystem->is_dir($new_assets) && !$wp_filesystem->mkdir($new_assets, FS_CHMOD_DIR))
            || !$wp_filesystem->mkdir($new_images, FS_CHMOD_DIR)) {
            return shiroki_theme_prepare_error('images_prepare');
        }
        $copied = copy_dir($old_images, $new_images);
        if (is_wp_error($copied)) {
            return $copied;
        }
        if (!$copied) {
            return shiroki_theme_prepare_error('images_copy');
        }
    }
    return trailingslashit($prepared);
}
