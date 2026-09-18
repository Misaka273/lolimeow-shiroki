<?php
/**
 * @link https://gl.baimu.live
 * @package lolimeow
 */

// gl.baimu.live===安全设置=阻止直接访问主题文件
if (!defined('ABSPATH')) {
    echo 'Look your sister';
    exit;
}

// 获取当前页面数据
$page_id = get_the_ID();
$page_content = get_post_field('post_content', $page_id);

// 尝试从页面内容中解析 JSON 数据
$custom_anime_data = array();
if (!empty($page_content)) {
    if (preg_match('/<!--anime-data\s*(.*?)\s*-->/s', $page_content, $matches)) {
        $json_data = json_decode(trim($matches[1]), true);
        if (is_array($json_data) && !empty($json_data)) {
            $custom_anime_data = $json_data;
        }
    }
}

// 尝试从后台追番管理模块读取数据
$admin_anime_data = array();
if (class_exists('Shiroki_Anime_Manager')) {
    $admin_anime_data = Shiroki_Anime_Manager::get_instance()->get_anime_list();
}

// 数据优先级：后台管理数据 > 页面自定义数据
if (!empty($admin_anime_data)) {
    $anime_data = $admin_anime_data;
} elseif (!empty($custom_anime_data)) {
    $anime_data = $custom_anime_data;
} else {
    $anime_data = array();
}

// 清洗数据：移除无效条目并规范字段类型
if (class_exists('Shiroki_Anime_Manager')) {
    $anime_data = Shiroki_Anime_Manager::get_instance()->clean_anime_list($anime_data);
} else {
    $anime_data = array_filter($anime_data, function ($item) {
        return is_array($item) && !empty($item['id']) && !empty($item['title']);
    });
    $anime_data = array_values($anime_data);
}

// 允许通过过滤器扩展追番数据
$anime_data = apply_filters('shiroki_anime_list_data', $anime_data, $page_id);

// 非管理员隐藏私密番剧
if (class_exists('Shiroki_Anime_Manager')) {
    $anime_data = Shiroki_Anime_Manager::get_instance()->filter_anime_for_viewer($anime_data);
}

// 页面绑定的分类名称（留空则展示全部）
$page_category_name = '';
if (class_exists('Shiroki_Anime_Manager')) {
    $page_category_name = get_post_meta($page_id, '_shiroki_anime_page_category_name', true);
}
$page_category_name = trim((string) $page_category_name);

if ($page_category_name !== '') {
    $anime_data = array_values(array_filter($anime_data, function ($item) use ($page_category_name) {
        $category_name = isset($item['category_name']) ? trim((string) $item['category_name']) : '';
        return $category_name === $page_category_name;
    }));
}

// 状态配置
$status_config = array(
    'watching'  => array('label' => '正在追', 'class' => 'status-watching', 'icon' => 'fa-play-circle'),
    'completed' => array('label' => '已看完', 'class' => 'status-completed', 'icon' => 'fa-check-circle'),
    'planned'   => array('label' => '计划看', 'class' => 'status-planned', 'icon' => 'fa-clock-o'),
    'on_hold'   => array('label' => '暂停中', 'class' => 'status-onhold', 'icon' => 'fa-pause-circle'),
    'dropped'   => array('label' => '已弃番', 'class' => 'status-dropped', 'icon' => 'fa-times-circle'),
);

// 计算统计数据
$total_count     = count($anime_data);
$watching_count  = 0;
$completed_count = 0;
$planned_count   = 0;
$on_hold_count   = 0;
$dropped_count   = 0;

foreach ($anime_data as $anime) {
    $status = isset($anime['status']) ? $anime['status'] : 'planned';
    switch ($status) {
        case 'watching':
            $watching_count++;
            break;
        case 'completed':
            $completed_count++;
            break;
        case 'planned':
            $planned_count++;
            break;
        case 'on_hold':
            $on_hold_count++;
            break;
        case 'dropped':
            $dropped_count++;
            break;
    }
}

$stats = array(
    'all'       => $total_count,
    'watching'  => $watching_count,
    'completed' => $completed_count,
    'planned'   => $planned_count,
    'on_hold'   => $on_hold_count,
    'dropped'   => $dropped_count,
);

// 当前筛选状态
$current_filter = isset($_GET['anime_status']) ? sanitize_text_field($_GET['anime_status']) : 'all';
$current_category = isset($_GET['anime_category']) ? sanitize_text_field(wp_unslash($_GET['anime_category'])) : 'all';
$current_protagonist = isset($_GET['anime_protagonist']) ? sanitize_text_field(wp_unslash($_GET['anime_protagonist'])) : 'all';
$current_voice_actor = isset($_GET['anime_voice_actor']) ? sanitize_text_field(wp_unslash($_GET['anime_voice_actor'])) : 'all';
$current_search = isset($_GET['anime_search']) ? sanitize_text_field(wp_unslash($_GET['anime_search'])) : '';
$anime_per_page = 6;
$current_anime_page = isset($_GET['anime_paged']) ? max(1, (int) $_GET['anime_paged']) : 1;

/**
 * 追番搜索匹配（支持多关键词模糊包含）
 */
if (!function_exists('shiroki_anime_matches_search')) {
    function shiroki_anime_matches_search($haystack, $query) {
        $haystack = mb_strtolower(trim((string) $haystack));
        $query = mb_strtolower(trim((string) $query));

        if ($query === '') {
            return true;
        }

        $tokens = preg_split('/\s+/u', $query);
        if (!is_array($tokens)) {
            return mb_strpos($haystack, $query) !== false;
        }

        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token === '') {
                continue;
            }
            if (mb_strpos($haystack, $token) === false) {
                return false;
            }
        }

        return true;
    }
}

/**
 * 构建卡片搜索文本
 */
if (!function_exists('shiroki_anime_build_search_text')) {
    function shiroki_anime_build_search_text($anime) {
        $parts = array();

        if (!empty($anime['title'])) {
            $parts[] = $anime['title'];
        }

        if (!empty($anime['category_name'])) {
            $parts[] = $anime['category_name'];
        }

        foreach (array('tags', 'protagonists', 'voice_actors') as $field) {
            if (empty($anime[$field])) {
                continue;
            }

            $values = $anime[$field];
            if (is_string($values)) {
                $values = preg_split('/[,，、]+/u', $values);
            }

            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $value) {
                $value = trim((string) $value);
                if ($value !== '') {
                    $parts[] = $value;
                }
            }
        }

        return implode(' ', $parts);
    }
}

/**
 * 判断番剧是否匹配当前筛选项
 */
if (!function_exists('shiroki_anime_item_matches_filters')) {
    function shiroki_anime_item_matches_filters($anime, $current_filter, $current_category, $current_protagonist, $current_voice_actor, $current_search) {
        $status = isset($anime['status']) ? $anime['status'] : 'planned';
        $tags = isset($anime['tags']) && is_array($anime['tags']) ? $anime['tags'] : array();
        $protagonists = isset($anime['protagonists']) && is_array($anime['protagonists']) ? $anime['protagonists'] : array();
        $voice_actors = isset($anime['voice_actors']) ? $anime['voice_actors'] : array();

        if (is_string($voice_actors) && $voice_actors !== '') {
            $voice_actors = preg_split('/[,，、]+/u', $voice_actors);
        }
        if (!is_array($voice_actors)) {
            $voice_actors = array();
        }

        $matches_status = ($current_filter === 'all' || $current_filter === $status);
        $matches_category = ($current_category === 'all' || in_array($current_category, $tags, true));
        $matches_protagonist = ($current_protagonist === 'all' || in_array($current_protagonist, $protagonists, true));
        $matches_voice_actor = ($current_voice_actor === 'all' || in_array($current_voice_actor, $voice_actors, true));
        $matches_search = shiroki_anime_matches_search(shiroki_anime_build_search_text($anime), $current_search);

        return $matches_status && $matches_category && $matches_protagonist && $matches_voice_actor && $matches_search;
    }
}

// 统计当前筛选下可见条目，用于分页
$anime_visible_indices = array();
foreach ($anime_data as $index => $anime) {
    if (shiroki_anime_item_matches_filters($anime, $current_filter, $current_category, $current_protagonist, $current_voice_actor, $current_search)) {
        $anime_visible_indices[] = $index;
    }
}

$anime_total_visible = count($anime_visible_indices);
$anime_total_pages = max(1, (int) ceil($anime_total_visible / $anime_per_page));
$current_anime_page = min($current_anime_page, $anime_total_pages);
$anime_page_indices = array_slice($anime_visible_indices, ($current_anime_page - 1) * $anime_per_page, $anime_per_page);
$anime_page_index_map = array_flip($anime_page_indices);

// 汇总可选标签
$category_tags = array();
$protagonist_tags = array();
$voice_actor_tags = array();

foreach ($anime_data as $anime) {
    $item_tags = isset($anime['tags']) && is_array($anime['tags']) ? $anime['tags'] : array();
    $item_protagonists = isset($anime['protagonists']) && is_array($anime['protagonists']) ? $anime['protagonists'] : array();
    $item_voice_actors = isset($anime['voice_actors']) ? $anime['voice_actors'] : array();
    if (is_string($item_voice_actors) && $item_voice_actors !== '') {
        $item_voice_actors = preg_split('/[,，、]+/u', $item_voice_actors);
    }
    if (!is_array($item_voice_actors)) {
        $item_voice_actors = array();
    }

    foreach ($item_tags as $tag) {
        $tag = trim((string) $tag);
        if ($tag !== '') {
            $category_tags[$tag] = $tag;
        }
    }

    foreach ($item_protagonists as $tag) {
        $tag = trim((string) $tag);
        if ($tag !== '') {
            $protagonist_tags[$tag] = $tag;
        }
    }

    foreach ($item_voice_actors as $tag) {
        $tag = trim((string) $tag);
        if ($tag !== '') {
            $voice_actor_tags[$tag] = $tag;
        }
    }
}

natcasesort($category_tags);
natcasesort($protagonist_tags);
natcasesort($voice_actor_tags);
$category_tags = array_values($category_tags);
$protagonist_tags = array_values($protagonist_tags);
$voice_actor_tags = array_values($voice_actor_tags);

// 懒加载占位图
$lazy_placeholder = boxmoe_lazy_load_images();

/**
 * 渲染追番卡片标签组（最多展示 2 个，超出收纳为 +）
 */
if (!function_exists('shiroki_render_anime_card_tag_group')) {
    function shiroki_render_anime_card_tag_group($label, $tags, $type) {
        if (empty($tags) || !is_array($tags)) {
            return;
        }

        $tags = array_values(array_filter(array_map('trim', $tags), function ($tag) {
            return $tag !== '';
        }));

        if (empty($tags)) {
            return;
        }

        $visible = array_slice($tags, 0, 2);
        $extra = array_slice($tags, 2);
        $type = sanitize_html_class($type);
        $type_class = 'anime-tag-' . $type;
        ?>
        <div class="anime-tag-group">
            <span class="anime-tag-group-label"><?php echo esc_html($label); ?></span>
            <div class="anime-tags anime-tags-<?php echo esc_attr($type); ?>">
                <?php foreach ($visible as $tag) : ?>
                    <span class="anime-tag <?php echo esc_attr($type_class); ?>"><?php echo esc_html($tag); ?></span>
                <?php endforeach; ?>
                <?php if (!empty($extra)) : ?>
                    <button
                        type="button"
                        class="anime-tag-more-btn <?php echo esc_attr($type_class); ?>"
                        data-tag-label="<?php echo esc_attr($label); ?>"
                        data-tag-type="<?php echo esc_attr($type); ?>"
                        aria-label="<?php echo esc_attr(sprintf('查看更多%s标签', $label)); ?>"
                    >
                        <span class="anime-tag-more-symbol" aria-hidden="true">+</span>
                        <span class="anime-tag-more-data" hidden>
                            <?php foreach ($extra as $tag) : ?>
                                <span class="anime-tag-more-item"><?php echo esc_html($tag); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>
<div class="col-lg-10 mx-auto">
    <div class="blog-single anime-page-wrap <?php echo boxmoe_border_setting(); ?>">
        <?php while (have_posts()) : the_post(); ?>
            <div class="post-single">
                <h1 class="single-title"><?php the_title(); ?></h1>
                <hr class="horizontal dark">

                <?php if (!empty($page_content) && empty($custom_anime_data)) : ?>
                    <div class="single-content anime-page-desc">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

                <!-- 统计概览 + 搜索 -->
                <div class="anime-toolbar">
                <div class="anime-stats-bar" role="toolbar" aria-label="按观看状态筛选">
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'all') ? 'active' : ''; ?>" data-filter="all" aria-pressed="<?php echo ($current_filter === 'all') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['all']); ?></span>
                        <span class="stats-label">全部</span>
                    </button>
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'watching') ? 'active' : ''; ?>" data-filter="watching" aria-pressed="<?php echo ($current_filter === 'watching') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['watching']); ?></span>
                        <span class="stats-label">正在追</span>
                    </button>
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'completed') ? 'active' : ''; ?>" data-filter="completed" aria-pressed="<?php echo ($current_filter === 'completed') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['completed']); ?></span>
                        <span class="stats-label">已看完</span>
                    </button>
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'planned') ? 'active' : ''; ?>" data-filter="planned" aria-pressed="<?php echo ($current_filter === 'planned') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['planned']); ?></span>
                        <span class="stats-label">计划看</span>
                    </button>
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'on_hold') ? 'active' : ''; ?>" data-filter="on_hold" aria-pressed="<?php echo ($current_filter === 'on_hold') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['on_hold']); ?></span>
                        <span class="stats-label">暂停中</span>
                    </button>
                    <button type="button" class="anime-stats-item <?php echo ($current_filter === 'dropped') ? 'active' : ''; ?>" data-filter="dropped" aria-pressed="<?php echo ($current_filter === 'dropped') ? 'true' : 'false'; ?>">
                        <span class="stats-number"><?php echo esc_html($stats['dropped']); ?></span>
                        <span class="stats-label">已弃番</span>
                    </button>
                </div>

                <div class="anime-search-bar">
                    <div class="anime-search-field anime-search-field--floating<?php echo ($current_search !== '') ? ' is-filled' : ''; ?>">
                        <i class="fa fa-search anime-search-icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            id="anime-search-input"
                            class="anime-search-input"
                            name="anime_search"
                            value="<?php echo esc_attr($current_search); ?>"
                            placeholder=" "
                            autocomplete="off"
                            enterkeyhint="search"
                        >
                        <label class="anime-search-label" for="anime-search-input">搜索番剧名称、分类、主角、声优</label>
                        <button type="button" class="anime-search-clear" aria-label="清除搜索" <?php echo ($current_search === '') ? 'hidden' : ''; ?>>&times;</button>
                    </div>
                </div>
                </div>

                <?php if (!empty($category_tags)) : ?>
                <div class="anime-tag-filter-bar">
                    <div class="anime-tag-filter-label">分类标签</div>
                    <div class="anime-tag-filter-options">
                        <button type="button" class="anime-tag-filter-item <?php echo ($current_category === 'all') ? 'active' : ''; ?>" data-filter-type="category" data-filter="all">全部</button>
                        <?php foreach ($category_tags as $tag) : ?>
                            <button type="button" class="anime-tag-filter-item <?php echo ($current_category === $tag) ? 'active' : ''; ?>" data-filter-type="category" data-filter="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($protagonist_tags)) : ?>
                <div class="anime-tag-filter-bar">
                    <div class="anime-tag-filter-label">主角标签</div>
                    <div class="anime-tag-filter-options">
                        <button type="button" class="anime-tag-filter-item <?php echo ($current_protagonist === 'all') ? 'active' : ''; ?>" data-filter-type="protagonist" data-filter="all">全部</button>
                        <?php foreach ($protagonist_tags as $tag) : ?>
                            <button type="button" class="anime-tag-filter-item <?php echo ($current_protagonist === $tag) ? 'active' : ''; ?>" data-filter-type="protagonist" data-filter="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($voice_actor_tags)) : ?>
                <div class="anime-tag-filter-bar">
                    <div class="anime-tag-filter-label">声优标签</div>
                    <div class="anime-tag-filter-options">
                        <button type="button" class="anime-tag-filter-item <?php echo ($current_voice_actor === 'all') ? 'active' : ''; ?>" data-filter-type="voice" data-filter="all">全部</button>
                        <?php foreach ($voice_actor_tags as $tag) : ?>
                            <button type="button" class="anime-tag-filter-item <?php echo ($current_voice_actor === $tag) ? 'active' : ''; ?>" data-filter-type="voice" data-filter="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 追番列表 -->
                <div class="anime-list">
                    <div class="row g-4">
                        <?php
                        $has_visible = false;
                        $has_pool = false;
                        $anime_glass_curve_svg = '';
                        $anime_glass_curve_path = get_stylesheet_directory() . '/assets/images/anime-glass-curve.svg';
                        if (file_exists($anime_glass_curve_path)) {
                            $anime_glass_curve_svg = file_get_contents($anime_glass_curve_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                        }
                        foreach ($anime_data as $index => $anime) :
                            $status        = isset($anime['status']) ? $anime['status'] : 'planned';
                            $status_info   = isset($status_config[$status]) ? $status_config[$status] : $status_config['planned'];
                            $title         = isset($anime['title']) ? $anime['title'] : '未命名';
                            $cover         = isset($anime['cover']) ? $anime['cover'] : '';
                            $progress      = isset($anime['progress']) ? intval($anime['progress']) : 0;
                            $total         = isset($anime['total']) ? intval($anime['total']) : 0;
                            $is_infinite   = isset($anime['is_infinite']) ? (bool) $anime['is_infinite'] : false;
                            $rating        = isset($anime['rating']) ? floatval($anime['rating']) : 0;
                            $tags          = isset($anime['tags']) && is_array($anime['tags']) ? $anime['tags'] : array();
                            $protagonists  = isset($anime['protagonists']) && is_array($anime['protagonists']) ? $anime['protagonists'] : array();
                            $voice_actors  = isset($anime['voice_actors']) ? $anime['voice_actors'] : array();
                            if (is_string($voice_actors) && $voice_actors !== '') {
                                $voice_actors = preg_split('/[,，、]+/u', $voice_actors);
                            }
                            if (!is_array($voice_actors)) {
                                $voice_actors = array();
                            }
                            $voice_actors = array_values(array_filter(array_map('trim', $voice_actors)));
                            $watch_link    = isset($anime['watch_link']) ? $anime['watch_link'] : '';
                            $official_link = isset($anime['official_link']) ? $anime['official_link'] : '';
                            $start_date    = isset($anime['start_date']) ? sanitize_text_field($anime['start_date']) : '';
                            $end_date      = isset($anime['end_date']) ? sanitize_text_field($anime['end_date']) : '';
                            $description   = isset($anime['description']) ? $anime['description'] : '';

                            $is_private    = !empty($anime['is_private']);
                            $search_text   = shiroki_anime_build_search_text($anime);
                            $has_date_meta = !empty($start_date) || !empty($end_date);
                            $date_tooltip  = '';
                            if ($has_date_meta) {
                                $date_lines = array();
                                if (!empty($start_date)) {
                                    $date_lines[] = '上映时间：' . $start_date;
                                }
                                if (!empty($end_date)) {
                                    $date_lines[] = '完结时间：' . $end_date;
                                }
                                $date_tooltip = implode("\n", $date_lines);
                            }

                            // 计算进度百分比（无限话固定 50%）
                            if ($is_infinite) {
                                $progress_percent = 50;
                                $progress_text    = '进度 ' . esc_html($progress) . ' / ∞';
                                $progress_percent_text = '∞';
                            } else {
                                $progress_percent = ($total > 0) ? min(100, round(($progress / $total) * 100)) : 0;
                                $progress_text    = '进度 ' . esc_html($progress) . ' / ' . esc_html($total > 0 ? $total : '?');
                                $progress_percent_text = esc_html($progress_percent) . '%';
                            }

                            // 判断是否在筛选中（池：忽略状态与搜索，供前台状态栏无刷新切换）
                            $in_pool = shiroki_anime_item_matches_filters($anime, 'all', $current_category, $current_protagonist, $current_voice_actor, '');
                            $is_visible = shiroki_anime_item_matches_filters($anime, $current_filter, $current_category, $current_protagonist, $current_voice_actor, $current_search);
                            $is_page_hidden = $is_visible && !isset($anime_page_index_map[$index]);
                            if ($is_visible) {
                                $has_visible = true;
                            }
                            if ($in_pool) {
                                $has_pool = true;
                            }
                            ?>
                            <div class="col-lg-4 col-md-6 col-sm-6 anime-item <?php echo esc_attr($is_visible ? 'is-visible' : 'is-hidden'); ?><?php echo $is_page_hidden ? ' is-page-hidden' : ''; ?><?php echo $is_private ? ' is-private' : ''; ?>" data-status="<?php echo esc_attr($status); ?>"<?php echo $in_pool ? ' data-anime-pool="1"' : ''; ?> data-search-text="<?php echo esc_attr(mb_strtolower($search_text)); ?>">
                                <div class="anime-card"<?php echo !empty($date_tooltip) ? ' data-shiroki-title="' . esc_attr($date_tooltip) . '"' : ''; ?>>
                                    <div class="anime-cover">
                                        <?php if (!empty($cover)) : ?>
                                            <img src="<?php echo esc_url($lazy_placeholder); ?>" data-src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($title); ?>" class="img-fluid lazy">
                                        <?php else : ?>
                                            <div class="anime-cover-placeholder" aria-hidden="true">
                                                <i class="fa fa-film"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="anime-cover-meta">
                                            <div class="anime-status-badge <?php echo esc_attr($status_info['class']); ?>">
                                                <i class="fa <?php echo esc_attr($status_info['icon']); ?>"></i>
                                                <?php echo esc_html($status_info['label']); ?>
                                            </div>
                                            <?php if (!empty($watch_link) || !empty($official_link)) : ?>
                                                <div class="anime-cover-link-tags">
                                                    <?php if (!empty($watch_link)) : ?>
                                                        <a href="<?php echo esc_url($watch_link); ?>" class="anime-cover-link-tag anime-cover-link-tag--watch" target="_blank" rel="noopener noreferrer" data-no-swup>
                                                            <i class="fa fa-play-circle"></i>
                                                            <span>看番</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($official_link)) : ?>
                                                        <a href="<?php echo esc_url($official_link); ?>" class="anime-cover-link-tag anime-cover-link-tag--official" target="_blank" rel="noopener noreferrer" data-no-swup>
                                                            <i class="fa fa-home"></i>
                                                            <span>官网</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($rating > 0) : ?>
                                            <div class="anime-rating">
                                                <i class="fa fa-star"></i>
                                                <span><?php echo esc_html(number_format($rating, 1)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="anime-info<?php echo !empty($cover) ? ' anime-info--glass' : ''; ?>">
                                        <?php if (!empty($cover) && !empty($anime_glass_curve_svg)) : ?>
                                            <div class="glass-curve" aria-hidden="true">
                                                <?php echo $anime_glass_curve_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($description)) : ?>
                                            <p class="anime-desc"><?php echo esc_html($description); ?></p>
                                        <?php endif; ?>
                                        <?php if ($is_private && current_user_can('manage_options')) : ?>
                                            <div class="anime-private-badge">🔒 私密</div>
                                        <?php endif; ?>
                                        <h3 class="anime-title">
                                            <?php if (!empty($official_link)) : ?>
                                                <a href="<?php echo esc_url($official_link); ?>" target="_blank" rel="noopener noreferrer" data-no-swup><?php echo esc_html($title); ?></a>
                                            <?php else : ?>
                                                <?php echo esc_html($title); ?>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if (!empty($start_date) || !empty($end_date)) : ?>
                                            <div class="anime-date-meta">
                                                <i class="fa fa-calendar"></i>
                                                <?php if (!empty($start_date) && !empty($end_date)) : ?>
                                                    <span><?php echo esc_html($start_date); ?> ~ <?php echo esc_html($end_date); ?></span>
                                                <?php elseif (!empty($start_date)) : ?>
                                                    <span>上映 <?php echo esc_html($start_date); ?></span>
                                                <?php else : ?>
                                                    <span>完结 <?php echo esc_html($end_date); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php shiroki_render_anime_card_tag_group('分类', $tags, 'category'); ?>
                                        <?php shiroki_render_anime_card_tag_group('主角', $protagonists, 'protagonist'); ?>
                                        <?php shiroki_render_anime_card_tag_group('声优', $voice_actors, 'voice'); ?>
                                        <div class="anime-progress <?php echo $is_infinite ? 'is-infinite' : ''; ?>">
                                            <div class="anime-progress-info">
                                                <span class="anime-progress-text"><?php echo $progress_text; ?></span>
                                                <span class="anime-progress-percent"><?php echo $progress_percent_text; ?></span>
                                            </div>
                                            <div class="anime-progress-bar">
                                                <div class="anime-progress-fill" style="width: <?php echo esc_attr($progress_percent); ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$has_pool) : ?>
                        <div class="anime-empty-state" id="anime-empty-state">
                            <i class="fa fa-television"></i>
                            <p><?php echo ($current_search !== '') ? '未找到匹配的番剧' : '暂无番剧'; ?></p>
                        </div>
                    <?php else : ?>
                        <div class="anime-empty-state anime-search-empty" id="anime-search-empty" <?php echo $has_visible ? 'hidden' : ''; ?>>
                            <i class="fa fa-search"></i>
                            <p>未找到匹配的番剧</p>
                        </div>
                    <?php endif; ?>
                </div>

                <nav
                    class="anime-pagination"
                    id="anime-pagination"
                    aria-label="追番列表分页"
                    data-per-page="<?php echo esc_attr($anime_per_page); ?>"
                    data-current-page="<?php echo esc_attr($current_anime_page); ?>"
                    data-total-pages="<?php echo esc_attr($anime_total_pages); ?>"
                    data-total-items="<?php echo esc_attr($anime_total_visible); ?>"
                    <?php echo ($anime_total_pages <= 1) ? 'hidden' : ''; ?>
                >
                    <div class="anime-pagination-info" id="anime-pagination-info">
                        共 <?php echo esc_html($anime_total_visible); ?> 条 · 第 <?php echo esc_html($current_anime_page); ?> / <?php echo esc_html($anime_total_pages); ?> 页
                    </div>
                    <div class="anime-pagination-controls">
                        <button
                            type="button"
                            class="anime-page-btn"
                            data-action="prev"
                            aria-label="上一页"
                            <?php echo ($current_anime_page <= 1) ? 'disabled' : ''; ?>
                        >上一页</button>
                        <span class="anime-pagination-current" id="anime-pagination-current"><?php echo esc_html($current_anime_page); ?></span>
                        <button
                            type="button"
                            class="anime-page-btn"
                            data-action="next"
                            aria-label="下一页"
                            <?php echo ($current_anime_page >= $anime_total_pages) ? 'disabled' : ''; ?>
                        >下一页</button>
                        <label class="anime-pagination-jump" for="anime-page-input">
                            <span class="anime-pagination-jump-label">跳至</span>
                            <input
                                type="number"
                                class="anime-page-input"
                                id="anime-page-input"
                                min="1"
                                max="<?php echo esc_attr($anime_total_pages); ?>"
                                value="<?php echo esc_attr($current_anime_page); ?>"
                                inputmode="numeric"
                            >
                            <span class="anime-pagination-jump-label">页</span>
                        </label>
                        <button type="button" class="anime-page-btn anime-page-btn-go" id="anime-page-go">确定</button>
                    </div>
                </nav>

                <?php if (comments_open()) : ?>
                    <?php comments_template('', true); ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="anime-tag-popup" id="anime-tag-popup" hidden>
    <div class="anime-tag-popup-backdrop" data-close-popup></div>
    <div class="anime-tag-popup-panel" role="dialog" aria-modal="true" aria-labelledby="anime-tag-popup-title">
        <div class="anime-tag-popup-header">
            <span class="anime-tag-popup-title" id="anime-tag-popup-title"></span>
            <button type="button" class="anime-tag-popup-close" aria-label="关闭" data-close-popup>&times;</button>
        </div>
        <div class="anime-tag-popup-masonry" id="anime-tag-popup-masonry"></div>
    </div>
</div>
