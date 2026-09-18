<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📊 站点数据统计仪表盘页面
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Site_Stats
 * @since 1.0.0
 */

// ◀️ 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 📊 注册站点数据统计菜单
 */
function boxmoe_add_site_stats_menu() {
    // 📊 添加主菜单（默认打开自建统计仪表盘）
    add_menu_page(
        '站点数据',
        '站点数据',
        'manage_options',
        'shiroki-site-stats',
        'boxmoe_render_site_stats_dashboard',
        'dashicons-chart-area',
        3
    );

    // 📈 添加子菜单 - 统计仪表盘（自建统计）
    add_submenu_page(
        'shiroki-site-stats',
        '📈 统计仪表盘',
        '📈 统计仪表盘',
        'manage_options',
        'shiroki-site-stats',
        'boxmoe_render_site_stats_dashboard'
    );

    // 🌐 添加子菜单 - 51LA统计
    add_submenu_page(
        'shiroki-site-stats',
        '🌐 51LA统计',
        '🌐 51LA统计',
        'manage_options',
        'shiroki-51la-stats',
        'boxmoe_render_51la_stats_dashboard'
    );
}
add_action('admin_menu', 'boxmoe_add_site_stats_menu');

/**
 * 📊 渲染统计仪表盘页面
 */
function boxmoe_render_site_stats_dashboard() {
    // 🔐 检查权限
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }

    // 📊 获取统计数据
    $stats = Shiroki_Site_Stats::get_instance();

    // 📈 获取各时间段数据
    $today_views = $stats->get_today_stats('post_view');
    $yesterday_views = $stats->get_yesterday_stats('post_view');
    $week_views = $stats->get_7days_stats('post_view');
    $month_views = $stats->get_30days_stats('post_view');
    $year_views = $stats->get_year_stats('post_view');

    $today_visits = $stats->get_today_stats('site_visit');
    $yesterday_visits = $stats->get_yesterday_stats('site_visit');
    $week_visits = $stats->get_7days_stats('site_visit');
    $month_visits = $stats->get_30days_stats('site_visit');
    $year_visits = $stats->get_year_stats('site_visit');

    $today_downloads = $stats->get_today_stats('download');
    $yesterday_downloads = $stats->get_yesterday_stats('download');
    $week_downloads = $stats->get_7days_stats('download');
    $month_downloads = $stats->get_30days_stats('download');
    $year_downloads = $stats->get_year_stats('download');

    // 🔥 获取排行榜数据
    $hot_posts = $stats->get_hot_posts(10, 30);
    $hot_downloads = $stats->get_hot_downloads(10, 30);
    $user_post_ranking = $stats->get_user_post_ranking(10, 30);
    $user_activity_ranking = $stats->get_user_activity_ranking(10, 30);

    // 📈 获取趋势数据（用于图表）
    $views_trend = $stats->get_trend_data('post_view', 30);
    $visits_trend = $stats->get_trend_data('site_visit', 30);
    $downloads_trend = $stats->get_trend_data('download', 30);

    // 📝 准备图表数据
    $chart_labels = array_keys($views_trend);
    $chart_views = array_values($views_trend);
    $chart_visits = array_values($visits_trend);
    $chart_downloads = array_values($downloads_trend);
    ?>
    <div class="wrap shiroki-stats-dashboard">
        <!-- 🎯 页面标题 -->
        <div class="shiroki-stats-header">
            <h1 class="shiroki-stats-title">📊 站点数据统计</h1>
            <p class="shiroki-stats-subtitle">实时监控站点流量、用户活跃度和内容热度</p>
        </div>

        <!-- 📊 时间筛选器 -->
        <div class="shiroki-stats-filter">
            <button class="shiroki-stats-filter-btn active" data-period="today">今日</button>
            <button class="shiroki-stats-filter-btn" data-period="yesterday">昨日</button>
            <button class="shiroki-stats-filter-btn" data-period="week">7天</button>
            <button class="shiroki-stats-filter-btn" data-period="month">30天</button>
            <button class="shiroki-stats-filter-btn" data-period="year">一年</button>
        </div>

        <!-- 📈 核心指标卡片 -->
        <div class="shiroki-stats-overview">
            <!-- 📖 阅读量 -->
            <div class="shiroki-stats-card shiroki-stats-card-views">
                <div class="shiroki-stats-card-icon">📖</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">阅读量</div>
                    <div class="shiroki-stats-card-value" id="stat-views"><?php echo number_format($today_views); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_views >= $yesterday_views ? 'up' : 'down'; ?>">
                        <?php
                        $views_change = $yesterday_views > 0 ? round((($today_views - $yesterday_views) / $yesterday_views) * 100, 1) : 0;
                        echo $views_change >= 0 ? '↑' : '↓';
                        echo abs($views_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 🌐 访问量 -->
            <div class="shiroki-stats-card shiroki-stats-card-visits">
                <div class="shiroki-stats-card-icon">🌐</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">访问量</div>
                    <div class="shiroki-stats-card-value" id="stat-visits"><?php echo number_format($today_visits); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_visits >= $yesterday_visits ? 'up' : 'down'; ?>">
                        <?php
                        $visits_change = $yesterday_visits > 0 ? round((($today_visits - $yesterday_visits) / $yesterday_visits) * 100, 1) : 0;
                        echo $visits_change >= 0 ? '↑' : '↓';
                        echo abs($visits_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 📥 下载量 -->
            <div class="shiroki-stats-card shiroki-stats-card-downloads">
                <div class="shiroki-stats-card-icon">📥</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">下载量</div>
                    <div class="shiroki-stats-card-value" id="stat-downloads"><?php echo number_format($today_downloads); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_downloads >= $yesterday_downloads ? 'up' : 'down'; ?>">
                        <?php
                        $downloads_change = $yesterday_downloads > 0 ? round((($today_downloads - $yesterday_downloads) / $yesterday_downloads) * 100, 1) : 0;
                        echo $downloads_change >= 0 ? '↑' : '↓';
                        echo abs($downloads_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 👥 活跃用户 -->
            <div class="shiroki-stats-card shiroki-stats-card-users">
                <div class="shiroki-stats-card-icon">👥</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">活跃用户与访客</div>
                    <div class="shiroki-stats-card-value"><?php echo number_format(count($user_activity_ranking)); ?></div>
                    <div class="shiroki-stats-card-change up">30天内</div>
                </div>
            </div>
        </div>

        <!-- 📈 趋势图表 -->
        <div class="shiroki-stats-chart-section">
            <div class="shiroki-stats-card shiroki-stats-chart-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-title">📈 流量趋势</span>
                    <div class="shiroki-stats-chart-legend">
                        <span class="legend-item views"><span class="legend-dot"></span>阅读量</span>
                        <span class="legend-item visits"><span class="legend-dot"></span>访问量</span>
                        <span class="legend-item downloads"><span class="legend-dot"></span>下载量</span>
                    </div>
                </div>
                <div class="shiroki-stats-chart-body">
                    <canvas id="shiroki-stats-chart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- 🔥 排行榜区域 -->
        <div class="shiroki-stats-ranking-section">
            <!-- 📄 文章热度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">🔥</span>
                    <span class="shiroki-stats-card-title">文章热度排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($hot_posts)) : ?>
                        <?php foreach ($hot_posts as $index => $post) : ?>
                            <div class="shiroki-stats-ranking-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="ranking-content">
                                    <a href="<?php echo get_permalink($post['id']); ?>" target="_blank" class="ranking-title">
                                        <?php echo esc_html($post['title']); ?>
                                    </a>
                                    <span class="ranking-meta"><?php echo esc_html($post['author']); ?> · <?php echo number_format($post['views']); ?> 阅读</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 📥 下载热度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">📥</span>
                    <span class="shiroki-stats-card-title">下载热度排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内 · 下载/链接/卡片</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($hot_downloads)) : ?>
                        <?php foreach ($hot_downloads as $index => $download) : ?>
                            <div class="shiroki-stats-ranking-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="ranking-content">
                                    <?php if (!empty($download['url'])) : ?>
                                        <a href="<?php echo esc_url($download['url']); ?>" target="_blank" class="ranking-title" title="<?php echo esc_attr($download['name']); ?>"><?php echo esc_html(mb_strimwidth($download['name'], 0, 36, '...')); ?></a>
                                    <?php else : ?>
                                        <span class="ranking-title"><?php echo esc_html($download['name']); ?></span>
                                    <?php endif; ?>
                                    <span class="ranking-meta"><?php echo number_format($download['downloads']); ?> 次下载</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ✍️ 用户发帖排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">✍️</span>
                    <span class="shiroki-stats-card-title">用户发帖排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($user_post_ranking)) : ?>
                        <?php foreach ($user_post_ranking as $index => $user) : ?>
                            <div class="shiroki-stats-ranking-item user-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <img src="<?php echo esc_url($user['avatar']); ?>" alt="" class="ranking-avatar">
                                <div class="ranking-content">
                                    <span class="ranking-title"><?php echo esc_html($user['name']); ?></span>
                                    <span class="ranking-meta"><?php echo number_format($user['post_count']); ?> 篇文章</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🌟 用户活跃度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">🌟</span>
                    <span class="shiroki-stats-card-title">用户活跃度</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($user_activity_ranking)) : ?>
                        <?php foreach ($user_activity_ranking as $index => $user) : ?>
                            <div class="shiroki-stats-ranking-item user-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <img src="<?php echo esc_url($user['avatar']); ?>" alt="" class="ranking-avatar">
                                <div class="ranking-content">
                                    <span class="ranking-title">
                                        <?php if (!empty($user['is_guest'])) : ?>
                                            <?php echo esc_html($user['name'] . ' #' . intval($user['guest_number'])); ?>
                                            <span class="shiroki-stats-guest-region"><?php echo esc_html($user['region']); ?></span>
                                        <?php else : ?>
                                            <?php echo esc_html($user['name']); ?>
                                        <?php endif; ?>
                                    </span>
                                    <span class="ranking-meta"><?php echo number_format($user['visit_count']); ?> 次访问</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 图表数据 -->
    <script type="text/javascript">
        window.shirokiStatsData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            views: <?php echo json_encode($chart_views); ?>,
            visits: <?php echo json_encode($chart_visits); ?>,
            downloads: <?php echo json_encode($chart_downloads); ?>,
            periods: {
                today: {
                    views: <?php echo $today_views; ?>,
                    visits: <?php echo $today_visits; ?>,
                    downloads: <?php echo $today_downloads; ?>
                },
                yesterday: {
                    views: <?php echo $yesterday_views; ?>,
                    visits: <?php echo $yesterday_visits; ?>,
                    downloads: <?php echo $yesterday_downloads; ?>
                },
                week: {
                    views: <?php echo $week_views; ?>,
                    visits: <?php echo $week_visits; ?>,
                    downloads: <?php echo $week_downloads; ?>
                },
                month: {
                    views: <?php echo $month_views; ?>,
                    visits: <?php echo $month_visits; ?>,
                    downloads: <?php echo $month_downloads; ?>
                },
                year: {
                    views: <?php echo $year_views; ?>,
                    visits: <?php echo $year_visits; ?>,
                    downloads: <?php echo $year_downloads; ?>
                }
            }
        };
    </script>
    <?php
}

/**
 * 📊 AJAX 获取统计数据
 */
function boxmoe_ajax_get_stats_data() {
    check_ajax_referer('shiroki_stats_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';
    $stats = Shiroki_Site_Stats::get_instance();

    $data = array();

    switch ($period) {
        case 'today':
            $data['views'] = $stats->get_today_stats('post_view');
            $data['visits'] = $stats->get_today_stats('site_visit');
            $data['downloads'] = $stats->get_today_stats('download');
            break;
        case 'yesterday':
            $data['views'] = $stats->get_yesterday_stats('post_view');
            $data['visits'] = $stats->get_yesterday_stats('site_visit');
            $data['downloads'] = $stats->get_yesterday_stats('download');
            break;
        case 'week':
            $data['views'] = $stats->get_7days_stats('post_view');
            $data['visits'] = $stats->get_7days_stats('site_visit');
            $data['downloads'] = $stats->get_7days_stats('download');
            break;
        case 'month':
            $data['views'] = $stats->get_30days_stats('post_view');
            $data['visits'] = $stats->get_30days_stats('site_visit');
            $data['downloads'] = $stats->get_30days_stats('download');
            break;
        case 'year':
            $data['views'] = $stats->get_year_stats('post_view');
            $data['visits'] = $stats->get_year_stats('site_visit');
            $data['downloads'] = $stats->get_year_stats('download');
            break;
    }

    wp_send_json_success($data);
}
add_action('wp_ajax_shiroki_get_stats_data', 'boxmoe_ajax_get_stats_data');

/**
 * 📡 AJAX 刷新 51LA 概览 + 实时数据
 */
function boxmoe_ajax_51la_refresh() {
    check_ajax_referer('shiroki_51la_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $api = Shiroki_51LA_API::get_instance();
    if (!$api->is_configured()) {
        wp_send_json_error(array('message' => '未配置'));
    }

    $config = $api->get_config();
    $mask_id = isset($config['mask_id']) ? $config['mask_id'] : '';

    // 获取概览数据
    $overview = $api->get_overview($mask_id);
    $bean = null;
    if (!is_wp_error($overview) && !empty($overview['bean'])) {
        $bean = $overview['bean'];
    }

    // 获取实时访客数据
    $realtime = $api->get_realtime('ACTIVE_USER', 15, $mask_id);
    $realtime_bean = null;
    if (!is_wp_error($realtime) && !empty($realtime['bean'])) {
        $realtime_bean = $realtime['bean'];
    }

    wp_send_json_success(array(
        'overview' => $bean,
        'realtime' => $realtime_bean,
    ));
}
add_action('wp_ajax_shiroki_51la_refresh', 'boxmoe_ajax_51la_refresh');

/**
 * 📡 AJAX 获取访客明细（支持多日查询）
 */
function boxmoe_ajax_51la_visitor_detail() {
    check_ajax_referer('shiroki_51la_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $api = Shiroki_51LA_API::get_instance();
    if (!$api->is_configured()) {
        wp_send_json_error(array('message' => '未配置'));
    }

    $config = $api->get_config();
    $mask_id = isset($config['mask_id']) ? $config['mask_id'] : '';
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';

    $today = date('Y-m-d');

    // 根据时间段确定日期列表
    $days = array();
    switch ($period) {
        case 'today':
            $days[] = $today;
            break;
        case 'yesterday':
            $days[] = date('Y-m-d', strtotime('-1 day'));
            break;
        case '7days':
            for ($i = 0; $i < 7; $i++) {
                $days[] = date('Y-m-d', strtotime("-{$i} days"));
            }
            break;
        case 'month':
            for ($i = 0; $i < 30; $i++) {
                $days[] = date('Y-m-d', strtotime("-{$i} days"));
            }
            break;
        case 'custom':
            $start = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
            $end   = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
            if ($start && $end && $start <= $end) {
                $current = $start;
                $end_ts = strtotime($end);
                while (strtotime($current) <= $end_ts) {
                    $days[] = $current;
                    $current = date('Y-m-d', strtotime($current . ' +1 day'));
                }
                // 限制最多 31 天
                $days = array_slice($days, 0, 31);
            }
            break;
    }

    if (empty($days)) {
        wp_send_json_error(array('message' => '无效的时间范围'));
    }

    // 逐天获取数据并合并
    $all_records = array();
    foreach ($days as $day) {
        $result = $api->get_visitor_detail($day, $mask_id, 1, 100);
        if (!is_wp_error($result)) {
            $list = !empty($result['data']) ? $result['data'] : (!empty($result['bean']) ? $result['bean'] : array());
            if (is_array($list)) {
                $all_records = array_merge($all_records, $list);
            }
        }
    }

    wp_send_json_success(array(
        'records' => $all_records,
        'total'   => count($all_records),
        'period'  => $period,
        'days'    => count($days),
    ));
}
add_action('wp_ajax_shiroki_51la_visitor_detail', 'boxmoe_ajax_51la_visitor_detail');

/**
 * 🌐 渲染51LA统计仪表盘页面
 */
function boxmoe_render_51la_stats_dashboard() {
    // 🔐 检查权限
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }

    // 💾 获取配置
    $api = Shiroki_51LA_API::get_instance();
    $config = $api->get_config();
    $is_configured = $api->is_configured();

    // 📝 确保配置项存在，避免未定义数组键警告
    $config = wp_parse_args($config, array(
        'access_key' => '',
        'secret_key' => '',
        'mask_id' => '',
        'security_type' => '2'
    ));

    // 📝 获取保存状态
    $saved = isset($_GET['saved']) && $_GET['saved'] === 'true';

    // 📊 如果已配置，尝试获取数据
    $overview_data = null;
    $error_message = '';

    if ($is_configured) {
        $overview_result = $api->get_overview($config['mask_id']);
        if (!is_wp_error($overview_result)) {
            $overview_data = $overview_result;
        } else {
            $error_message = $overview_result->get_error_message();
        }
    }

    // 📝 获取今日趋势数据（按小时）
    $trend_data = null;
    if ($is_configured && empty($error_message)) {
        $trend_result = $api->get_trend(date('Y-m-d'), $config['mask_id']);
        if (!is_wp_error($trend_result)) {
            $trend_data = $trend_result;
        }
    }

    // 📝 获取受访页面数据（最近7天）
    $visited_pages_data = null;
    if ($is_configured && empty($error_message)) {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-6 days'));
        $visited_pages_result = $api->get_visited_pages($start_date, $end_date, $config['mask_id']);
        if (!is_wp_error($visited_pages_result)) {
            $visited_pages_data = $visited_pages_result;
        }
    }

    // 📝 获取访问明细数据
    $visitor_detail_data = null;
    if ($is_configured) {
        $visitor_detail_result = $api->get_visitor_detail(date('Y-m-d'), $config['mask_id'], 1, 50);
        if (!is_wp_error($visitor_detail_result)) {
            $visitor_detail_data = $visitor_detail_result;
        } else {
            error_log('51LA 访问明细获取失败: ' . $visitor_detail_result->get_error_message());
        }
    }

    // 📝 获取实时在线访客数据（最近15分钟活跃用户）
    $realtime_data = null;
    if ($is_configured) {
        $realtime_result = $api->get_realtime('ACTIVE_USER', 15, $config['mask_id']);
        if (!is_wp_error($realtime_result)) {
            $realtime_data = $realtime_result;
        }
    }

    // 📝 获取站点列表
    $site_list_data = null;
    if ($is_configured) {
        $site_list_result = $api->get_site_list();
        if (!is_wp_error($site_list_result)) {
            $site_list_data = $site_list_result;
        } else {
            error_log('51LA 站点列表获取失败（不影响其他功能）: ' . $site_list_result->get_error_message());
        }
    }

    // 📝 准备图表数据（趋势接口返回每小时数据）
    $chart_labels = array();
    $chart_pv = array();
    $chart_uv = array();

    if ($trend_data && !empty($trend_data['data'])) {
        // 按时间正序排列（API返回的是逆序的）
        $trend_items = array_reverse($trend_data['data']);
        foreach ($trend_items as $item) {
            $chart_labels[] = isset($item['time']) ? $item['time'] : '';
            $chart_pv[] = intval($item['pv'] ?? 0);
            $chart_uv[] = intval($item['uv'] ?? 0);
        }
    }
    ?>
    <div class="wrap shiroki-51la-dashboard">
        <!-- 🎯 页面标题 -->
        <div class="shiroki-51la-header">
            <h1 class="shiroki-51la-title">🌐 51LA 统计</h1>
            <p class="shiroki-51la-subtitle">通过 51LA API 获取网站流量分析数据</p>
        </div>

        <?php if ($saved) : ?>
        <!-- ✅ 保存成功提示 -->
        <div class="shiroki-51la-notice success">
            <span class="notice-icon">✅</span>
            <span class="notice-text">配置保存成功！</span>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)) : ?>
        <!-- ❌ 错误提示 -->
        <div class="shiroki-51la-notice error">
            <span class="notice-icon">❌</span>
            <span class="notice-text"><?php echo esc_html($error_message); ?></span>
        </div>
        <?php endif; ?>

        <!-- ⚙️ 配置区域 -->
        <div class="shiroki-51la-card shiroki-51la-config-card <?php echo $is_configured ? 'is-collapsed' : 'is-expanded'; ?>">
            <div class="shiroki-51la-card-header shiroki-51la-config-header" style="cursor: pointer;">
                <span class="shiroki-51la-card-icon">⚙️</span>
                <span class="shiroki-51la-card-title">API 配置</span>
                <span class="shiroki-51la-config-status <?php echo $is_configured ? 'active' : 'inactive'; ?>">
                    <?php echo $is_configured ? '🟢 已配置' : '🔴 未配置'; ?>
                </span>
                <span class="shiroki-51la-toggle-icon"><?php echo $is_configured ? '▼' : '▲'; ?></span>
            </div>
            <div class="shiroki-51la-card-body shiroki-51la-config-body">
                <form method="post" action="" class="shiroki-51la-config-form">
                    <?php wp_nonce_field('shiroki_51la_config_nonce', 'shiroki_51la_config_nonce'); ?>

                    <div class="shiroki-51la-form-row">
                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_access_key">Access Key <span class="required">*</span></label>
                            <input type="text"
                                   name="shiroki_51la_access_key"
                                   id="shiroki_51la_access_key"
                                   value="<?php echo esc_attr($config['access_key']); ?>"
                                   placeholder="请输入 51LA Access Key"
                                   required>
                            <p class="field-description">在 <a href="https://v6.51.la/user/application/openapi" target="_blank">51LA 开放平台</a> 获取</p>
                        </div>

                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_secret_key">Secret Key <span class="required">*</span></label>
                            <input type="password"
                                   name="shiroki_51la_secret_key"
                                   id="shiroki_51la_secret_key"
                                   value="<?php echo esc_attr($config['secret_key']); ?>"
                                   placeholder="请输入 51LA Secret Key"
                                   required>
                            <p class="field-description">用于生成 API 请求签名</p>
                        </div>
                    </div>

                    <div class="shiroki-51la-form-row">
                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_mask_id">掩码 ID (maskId) <span class="required">*</span></label>
                            <input type="text"
                                   name="shiroki_51la_mask_id"
                                   id="shiroki_51la_mask_id"
                                   value="<?php echo esc_attr($config['mask_id']); ?>"
                                   placeholder="请输入 51LA 掩码ID (maskId)"
                                   required>
                            <p class="field-description">在 51LA 后台「应用管理」中查看，必填</p>
                        </div>

                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_security_type">安全等级</label>
                            <select name="shiroki_51la_security_type" id="shiroki_51la_security_type">
                                <option value="1" <?php selected($config['security_type'], '1'); ?>>低安全性（仅 AccessKey）</option>
                                <option value="2" <?php selected($config['security_type'], '2'); ?>>中安全性（推荐）</option>
                                <option value="3" <?php selected($config['security_type'], '3'); ?>>高安全性（双向加密）</option>
                            </select>
                            <p class="field-description">建议选择中等安全性</p>
                        </div>
                    </div>

                    <div class="shiroki-51la-form-actions">
                        <button type="submit" name="shiroki_51la_save_config" class="shiroki-51la-btn shiroki-51la-btn-primary">
                            <span class="btn-icon">💾</span>
                            <span>保存配置</span>
                        </button>
                        <a href="https://v6.51.la/user/application/openapi" target="_blank" class="shiroki-51la-btn shiroki-51la-btn-secondary">
                            <span class="btn-icon">🔗</span>
                            <span>获取 API 密钥</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🗂️ 标签切换菜单 -->
        <div class="shiroki-51la-tabs">
            <button type="button" class="shiroki-51la-tab active" data-tab="overview">
                <span class="tab-icon">📊</span>
                <span class="tab-text">数据概览</span>
            </button>
            <button type="button" class="shiroki-51la-tab" data-tab="visitor-detail">
                <span class="tab-icon">📋</span>
                <span class="tab-text">访客明细</span>
            </button>
            <button type="button" class="shiroki-51la-tab" data-tab="site-list">
                <span class="tab-icon">🏠</span>
                <span class="tab-text">站点列表</span>
            </button>
            <button type="button" class="shiroki-51la-tab" data-tab="guide">
                <span class="tab-icon">📖</span>
                <span class="tab-text">使用说明</span>
            </button>
        </div>

        <?php
        // 📝 准备数据（真实数据或演示数据）
        $api_error = $is_configured && !empty($error_message);

        // 🔍 获取实际数据
        $overview_real_data = $overview_data && !empty($overview_data['bean']) ? $overview_data['bean'] : ($overview_data && !empty($overview_data['data']) ? $overview_data['data'] : null);

        $has_real_data = $is_configured && $overview_real_data;
        $demo_mode = !$is_configured;

        // 📊 统计数据
        $today_pv = $overview_real_data && isset($overview_real_data['curPv']) ? intval($overview_real_data['curPv']) : ($demo_mode ? 1234 : 0);
        $today_uv = $overview_real_data && isset($overview_real_data['curUv']) ? intval($overview_real_data['curUv']) : ($demo_mode ? 567 : 0);
        $month_pv = $overview_real_data && isset($overview_real_data['monthPv']) ? intval($overview_real_data['monthPv']) : ($demo_mode ? 45678 : 0);
        $total_pv = $overview_real_data && isset($overview_real_data['totalPv']) ? intval($overview_real_data['totalPv']) : ($demo_mode ? 1234567 : 0);

        // 📈 趋势数据
        $chart_labels = array();
        $chart_pv = array();
        $chart_uv = array();

        if ($trend_data && !empty($trend_data['data'])) {
            $trend_items = array_reverse($trend_data['data']);
            foreach ($trend_items as $item) {
                if (isset($item['time'])) {
                    $chart_labels[] = $item['time'];
                    $chart_pv[] = intval($item['pv'] ?? 0);
                    $chart_uv[] = intval($item['uv'] ?? 0);
                }
            }
        } elseif ($demo_mode) {
            // 🎨 生成演示数据
            for ($i = 6; $i >= 0; $i--) {
                $chart_labels[] = date('m/d', strtotime("-{$i} days"));
                $chart_pv[] = rand(800, 1500);
                $chart_uv[] = rand(400, 800);
            }
        }
        ?>

        <?php if ($api_error) : ?>
        <!-- ⚠️ API错误提示 -->
        <div class="shiroki-51la-notice warning">
            <span class="notice-icon">⚠️</span>
            <span class="notice-text">API请求失败，显示演示数据。错误：<?php echo esc_html($error_message); ?></span>
        </div>
        <?php endif; ?>

        <!-- 📊 数据概览标签页 -->
        <div class="shiroki-51la-tab-content active" data-tab-content="overview">
            <!-- 📊 数据概览 -->
        <div class="shiroki-51la-overview">
            <!-- 📈 今日 PV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-label">今日 PV</div>
                    <div class="stat-value" id="51la-today-pv">
                        <?php echo number_format($today_pv); ?>
                    </div>
                    <div class="stat-change">
                        浏览量
                    </div>
                </div>
            </div>

            <!-- 👥 今日 UV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-label">今日 UV</div>
                    <div class="stat-value" id="51la-today-uv">
                        <?php echo number_format($today_uv); ?>
                    </div>
                    <div class="stat-change">
                        独立访客
                    </div>
                </div>
            </div>

            <!-- 📊 本月 PV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-label">本月 PV</div>
                    <div class="stat-value" id="51la-month-pv">
                        <?php echo number_format($month_pv); ?>
                    </div>
                    <div class="stat-change">
                        累计浏览
                    </div>
                </div>
            </div>

            <!-- 🌐 总访问量 -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">🌐</div>
                <div class="stat-content">
                    <div class="stat-label">总访问量</div>
                    <div class="stat-value" id="51la-total-pv">
                        <?php echo number_format($total_pv); ?>
                    </div>
                    <div class="stat-change">
                        历史累计
                    </div>
                </div>
            </div>
        </div>

        <!-- ⏱️ 实时在线访客 -->
        <?php
        $realtime_count = 0;
        $realtime_minutes = array();
        if ($realtime_data && !empty($realtime_data['bean'])) {
            $realtime_bean = $realtime_data['bean'];
            $realtime_count = isset($realtime_bean['totalCount']) ? intval($realtime_bean['totalCount']) : 0;
            $realtime_minutes = isset($realtime_bean['minuteList']) && is_array($realtime_bean['minuteList']) ? $realtime_bean['minuteList'] : array();
        }
        ?>
        <div class="shiroki-51la-card shiroki-51la-realtime-card">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">⏱️</span>
                <span class="shiroki-51la-card-title">实时在线访客</span>
                <span class="header-hint">最近 15 分钟</span>
            </div>
            <div class="shiroki-51la-card-body">
                <div class="realtime-display">
                    <div class="realtime-number"><?php echo $is_configured ? number_format($realtime_count) : '--'; ?></div>
                    <div class="realtime-label">当前活跃访客</div>
                    <?php if (!empty($realtime_minutes)) : ?>
                    <div class="realtime-sparkline">
                        <?php
                        $spark_values = array_reverse(array_slice($realtime_minutes, 0, 15));
                        $spark_max = max(1, max($spark_values));
                        foreach ($spark_values as $i => $val) :
                            $h = max(4, round(($val / $spark_max) * 40));
                        ?>
                        <span class="spark-bar" style="height:<?php echo $h; ?>px" title="<?php echo $val; ?> 访客"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif (!$is_configured) : ?>
                    <div class="realtime-sparkline">
                        <?php for ($i = 0; $i < 15; $i++) : ?>
                        <span class="spark-bar" style="height:<?php echo rand(4, 40); ?>px"></span>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 📈 趋势图表 -->
        <div class="shiroki-51la-card shiroki-51la-chart-card">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">📈</span>
                <span class="shiroki-51la-card-title">流量趋势（最近7天）</span>
                <div class="shiroki-51la-chart-legend">
                    <span class="legend-item pv"><span class="legend-dot"></span>浏览量(PV)</span>
                    <span class="legend-item uv"><span class="legend-dot"></span>访客数(UV)</span>
                </div>
            </div>
            <div class="shiroki-51la-chart-body">
                <canvas id="shiroki-51la-chart" height="300"></canvas>
            </div>
        </div>

        <!--  来路分析 -->
        <div class="shiroki-51la-card shiroki-51la-source-card">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">🔗</span>
                <span class="shiroki-51la-card-title">来路分析（最近7天）</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 📝 准备来路数据
                $source_list = array();
                $source_visitor_list = $visitor_detail_data && !empty($visitor_detail_data['data']) ? $visitor_detail_data['data'] : ($visitor_detail_data && !empty($visitor_detail_data['bean']) ? $visitor_detail_data['bean'] : null);
                if ($is_configured && $source_visitor_list && is_array($source_visitor_list)) {
                    $source_agg = array();
                    foreach ($source_visitor_list as $v) {
                        $src = isset($v['srcUrl']) && !empty($v['srcUrl']) ? $v['srcUrl'] : '直接访问';
                        if (!isset($source_agg[$src])) {
                            $source_agg[$src] = array('source' => $src, 'pv' => 0, 'uv' => 0);
                        }
                        $source_agg[$src]['pv'] += isset($v['pv']) ? intval($v['pv']) : 1;
                        $source_agg[$src]['uv'] += 1;
                    }
                    uasort($source_agg, function($a, $b) { return $b['pv'] - $a['pv']; });
                    $source_list = array_slice(array_values($source_agg), 0, 10);
                } elseif ($demo_mode) {
                    // 🎨 演示数据
                    $source_list = array(
                        array('source' => '百度搜索', 'pv' => 4523, 'uv' => 2341, 'ratio' => 35.2),
                        array('source' => '直接访问', 'pv' => 3124, 'uv' => 1892, 'ratio' => 24.3),
                        array('source' => 'Google', 'pv' => 1856, 'uv' => 1234, 'ratio' => 14.5),
                        array('source' => '必应搜索', 'pv' => 1234, 'uv' => 876, 'ratio' => 9.6),
                        array('source' => '搜狗搜索', 'pv' => 987, 'uv' => 654, 'ratio' => 7.7),
                        array('source' => '外链', 'pv' => 654, 'uv' => 432, 'ratio' => 5.1),
                        array('source' => '其他', 'pv' => 432, 'uv' => 298, 'ratio' => 3.6)
                    );
                }

                if (!empty($source_list)) :
                    // 📊 计算总数
                    $total_pv = array_sum(array_column($source_list, 'pv'));
                ?>
                <div class="shiroki-51la-source-table-wrapper">
                    <table class="shiroki-51la-source-table">
                        <thead>
                            <tr>
                                <th class="col-rank">排名</th>
                                <th class="col-source">来路来源</th>
                                <th class="col-pv">浏览量(PV)</th>
                                <th class="col-uv">访客数(UV)</th>
                                <th class="col-ratio">占比</th>
                                <th class="col-bar">趋势</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($source_list as $index => $item) :
                                $rank = $index + 1;
                                $source_name = isset($item['source']) ? $item['source'] : '未知来源';
                                $pv = isset($item['pv']) ? intval($item['pv']) : 0;
                                $uv = isset($item['uv']) ? intval($item['uv']) : 0;
                                $ratio = isset($item['ratio']) ? floatval($item['ratio']) : ($total_pv > 0 ? round($pv / $total_pv * 100, 1) : 0);

                                // 🎨 排名样式
                                $rank_class = '';
                                if ($rank === 1) $rank_class = 'rank-1';
                                elseif ($rank === 2) $rank_class = 'rank-2';
                                elseif ($rank === 3) $rank_class = 'rank-3';
                            ?>
                            <tr>
                                <td class="col-rank">
                                    <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                                </td>
                                <td class="col-source">
                                    <span class="source-name"><?php echo esc_html($source_name); ?></span>
                                </td>
                                <td class="col-pv"><?php echo number_format($pv); ?></td>
                                <td class="col-uv"><?php echo number_format($uv); ?></td>
                                <td class="col-ratio"><?php echo $ratio; ?>%</td>
                                <td class="col-bar">
                                    <div class="ratio-bar">
                                        <div class="ratio-fill" style="width: <?php echo min($ratio, 100); ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="shiroki-51la-empty">
                    <p>暂无来路数据</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 📄 受访页分析（TOP 10） -->
        <div class="shiroki-51la-card shiroki-51la-visited-pages-preview">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">📄</span>
                <span class="shiroki-51la-card-title">受访页面（TOP 10）</span>
                <span class="header-hint">用户访问最多的页面</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 📄 使用真实数据或演示数据
                $visited_pages_top10 = array();
                $visited_pages_real_data = $visited_pages_data && !empty($visited_pages_data['data']) ? $visited_pages_data['data'] : ($visited_pages_data && !empty($visited_pages_data['bean']) ? $visited_pages_data['bean'] : null);

                if ($is_configured && $visited_pages_real_data && is_array($visited_pages_real_data)) {
                    $visited_pages_top10 = array_slice($visited_pages_real_data, 0, 10);
                } elseif ($demo_mode) {
                    // 🎨 演示数据 - 受访页面 TOP 10
                    $visited_pages_top10 = array(
                        array('url' => '/home', 'title' => '首页', 'pv' => 12543, 'uv' => 8234, 'avgVisitTime' => '02:34'),
                        array('url' => '/article/wordpress-theme', 'title' => 'WordPress主题推荐', 'pv' => 8765, 'uv' => 6543, 'avgVisitTime' => '05:12'),
                        array('url' => '/category/tech', 'title' => '技术分类', 'pv' => 6543, 'uv' => 4321, 'avgVisitTime' => '03:45'),
                        array('url' => '/about', 'title' => '关于我们', 'pv' => 5432, 'uv' => 3876, 'avgVisitTime' => '01:56'),
                        array('url' => '/article/seo-guide', 'title' => 'SEO优化指南', 'pv' => 4321, 'uv' => 3210, 'avgVisitTime' => '08:23'),
                        array('url' => '/contact', 'title' => '联系我们', 'pv' => 3456, 'uv' => 2345, 'avgVisitTime' => '01:12'),
                        array('url' => '/article/web-design', 'title' => '网页设计技巧', 'pv' => 2987, 'uv' => 2134, 'avgVisitTime' => '06:45'),
                        array('url' => '/category/life', 'title' => '生活随笔', 'pv' => 2654, 'uv' => 1876, 'avgVisitTime' => '04:32'),
                        array('url' => '/article/php-tutorial', 'title' => 'PHP入门教程', 'pv' => 2345, 'uv' => 1654, 'avgVisitTime' => '12:34'),
                        array('url' => '/guestbook', 'title' => '留言板', 'pv' => 1876, 'uv' => 1234, 'avgVisitTime' => '02:18'),
                    );
                } else {
                    $visited_pages_top10 = array();
                }
                ?>
                <div class="shiroki-51la-pages-list">
                    <?php foreach ($visited_pages_top10 as $index => $item) :
                        $rank = $index + 1;
                        $rank_class = '';
                        if ($rank === 1) $rank_class = 'rank-1';
                        elseif ($rank === 2) $rank_class = 'rank-2';
                        elseif ($rank === 3) $rank_class = 'rank-3';

                        // 🔍 获取字段值（支持多种字段名）
                        $pv = isset($item['pv']) ? intval($item['pv']) : 0;
                        $uv = isset($item['uv']) ? intval($item['uv']) : 0;
                        $avg_time = isset($item['avgVisitTime']) ? $item['avgVisitTime'] : (isset($item['avg_time']) ? $item['avg_time'] : '00:00');
                        $url = isset($item['url']) ? $item['url'] : (isset($item['pageUrl']) ? $item['pageUrl'] : '#');
                        $title = isset($item['title']) ? $item['title'] : (isset($item['pageTitle']) ? $item['pageTitle'] : '未知页面');
                    ?>
                    <div class="page-item">
                        <div class="page-rank">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="page-info">
                            <span class="page-title"><?php echo esc_html($title); ?></span>
                            <span class="page-url"><?php echo esc_html($url); ?></span>
                        </div>
                        <div class="page-stats">
                            <div class="stat-item">
                                <span class="stat-label">浏览量</span>
                                <span class="stat-value"><?php echo number_format($pv); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">访客数</span>
                                <span class="stat-value"><?php echo number_format($uv); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">平均停留</span>
                                <span class="stat-value"><?php echo $avg_time; ?></span>
                            </div>
                        </div>
                        <div class="page-trend">
                            <div class="trend-bar">
                                <div class="trend-fill" style="width: <?php echo min(($pv / 13000) * 100, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 🚪 入口页分析（TOP 10） -->
        <div class="shiroki-51la-card shiroki-51la-entry-pages-preview">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">🚪</span>
                <span class="shiroki-51la-card-title">入口页面（TOP 10）</span>
                <span class="header-hint">用户进入网站的第一页</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 🚪 入口页面数据（从访问明细中按 entryPage 聚合）
                $entry_pages_top10 = array();
                $ep_visitor_list = $visitor_detail_data && !empty($visitor_detail_data['data']) ? $visitor_detail_data['data'] : ($visitor_detail_data && !empty($visitor_detail_data['bean']) ? $visitor_detail_data['bean'] : null);

                if ($is_configured && $ep_visitor_list && is_array($ep_visitor_list)) {
                    $ep_agg = array();
                    $ep_total = 0;
                    foreach ($ep_visitor_list as $v) {
                        $ep_url = isset($v['entryPage']) && !empty($v['entryPage']) ? $v['entryPage'] : '';
                        if (empty($ep_url)) continue;
                        if (!isset($ep_agg[$ep_url])) {
                            $ep_agg[$ep_url] = array('url' => $ep_url, 'entryCount' => 0);
                        }
                        $ep_agg[$ep_url]['entryCount'] += 1;
                        $ep_total += 1;
                    }
                    uasort($ep_agg, function($a, $b) { return $b['entryCount'] - $a['entryCount']; });
                    $ep_top = array_slice($ep_agg, 0, 10, true);
                    foreach ($ep_top as $url => $info) {
                        $entry_pages_top10[] = array(
                            'url'         => $url,
                            'title'       => $url,
                            'entryCount'  => $info['entryCount'],
                            'entryRate'   => $ep_total > 0 ? round($info['entryCount'] / $ep_total * 100, 1) . '%' : '0%',
                            'bounceRate'  => '--',
                        );
                    }
                } elseif ($demo_mode) {
                    // 🎨 演示数据 - 入口页面 TOP 10
                    $entry_pages_top10 = array(
                        array('url' => '/home', 'title' => '首页', 'entryCount' => 9876, 'entryRate' => '78.5%', 'bounceRate' => '32.1%'),
                        array('url' => '/article/wordpress-theme', 'title' => 'WordPress主题推荐', 'entryCount' => 2345, 'entryRate' => '18.6%', 'bounceRate' => '45.2%'),
                        array('url' => '/category/tech', 'title' => '技术分类', 'entryCount' => 876, 'entryRate' => '6.9%', 'bounceRate' => '38.7%'),
                        array('url' => '/about', 'title' => '关于我们', 'entryCount' => 543, 'entryRate' => '4.3%', 'bounceRate' => '52.3%'),
                        array('url' => '/article/seo-guide', 'title' => 'SEO优化指南', 'entryCount' => 432, 'entryRate' => '3.4%', 'bounceRate' => '41.8%'),
                        array('url' => '/contact', 'title' => '联系我们', 'entryCount' => 321, 'entryRate' => '2.5%', 'bounceRate' => '65.4%'),
                        array('url' => '/article/web-design', 'title' => '网页设计技巧', 'entryCount' => 287, 'entryRate' => '2.3%', 'bounceRate' => '35.6%'),
                        array('url' => '/category/life', 'title' => '生活随笔', 'entryCount' => 234, 'entryRate' => '1.9%', 'bounceRate' => '48.9%'),
                        array('url' => '/article/php-tutorial', 'title' => 'PHP入门教程', 'entryCount' => 198, 'entryRate' => '1.6%', 'bounceRate' => '28.3%'),
                        array('url' => '/guestbook', 'title' => '留言板', 'entryCount' => 156, 'entryRate' => '1.2%', 'bounceRate' => '58.7%'),
                    );
                } else {
                    $entry_pages_top10 = array();
                }
                ?>
                <div class="shiroki-51la-pages-list">
                    <?php foreach ($entry_pages_top10 as $index => $item) :
                        $rank = $index + 1;
                        $rank_class = '';
                        if ($rank === 1) $rank_class = 'rank-1';
                        elseif ($rank === 2) $rank_class = 'rank-2';
                        elseif ($rank === 3) $rank_class = 'rank-3';

                        // 🔍 获取字段值（支持多种字段名）
                        $entry_count = isset($item['entryCount']) ? intval($item['entryCount']) : (isset($item['entry_count']) ? intval($item['entry_count']) : 0);
                        $entry_rate = isset($item['entryRate']) ? $item['entryRate'] : (isset($item['entry_rate']) ? $item['entry_rate'] : '0%');
                        $bounce_rate = isset($item['bounceRate']) ? $item['bounceRate'] : (isset($item['bounce_rate']) ? $item['bounce_rate'] : '0%');
                        $url = isset($item['url']) ? $item['url'] : (isset($item['pageUrl']) ? $item['pageUrl'] : '#');
                        $title = isset($item['title']) ? $item['title'] : (isset($item['pageTitle']) ? $item['pageTitle'] : '未知页面');
                    ?>
                    <div class="page-item entry-page-item">
                        <div class="page-rank">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="page-info">
                            <span class="page-title"><?php echo esc_html($title); ?></span>
                            <span class="page-url"><?php echo esc_html($url); ?></span>
                        </div>
                        <div class="page-stats">
                            <div class="stat-item">
                                <span class="stat-label">入口次数</span>
                                <span class="stat-value"><?php echo number_format($entry_count); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">入口占比</span>
                                <span class="stat-value"><?php echo $entry_rate; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">跳出率</span>
                                <span class="stat-value <?php echo floatval($bounce_rate) > 50 ? 'high-bounce' : 'low-bounce'; ?>"><?php echo $bounce_rate; ?></span>
                            </div>
                        </div>
                        <div class="page-trend">
                            <div class="trend-bar">
                                <div class="trend-fill" style="width: <?php echo min(floatval($entry_rate) * 1.2, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 📊 数据分析卡片组 -->
        <?php
        // 从访问明细中聚合分析数据
        $browser_stats = array();
        $region_stats = array();
        $entry_page_stats = array();
        $keyword_stats = array();
        $analysis_visitor_list = $visitor_detail_data && !empty($visitor_detail_data['data']) ? $visitor_detail_data['data'] : ($visitor_detail_data && !empty($visitor_detail_data['bean']) ? $visitor_detail_data['bean'] : null);

        if ($is_configured && $analysis_visitor_list && is_array($analysis_visitor_list)) {
            foreach ($analysis_visitor_list as $v) {
                // 浏览器统计
                $browser = isset($v['browser']) && !empty($v['browser']) ? $v['browser'] : '未知';
                $browser_stats[$browser] = ($browser_stats[$browser] ?? 0) + 1;

                // 地域统计
                $region = isset($v['region']) && !empty($v['region']) ? $v['region'] : '未知';
                $region_stats[$region] = ($region_stats[$region] ?? 0) + 1;

                // 入口页统计
                $entry_page = isset($v['entryPage']) && !empty($v['entryPage']) ? $v['entryPage'] : '';
                if (!empty($entry_page)) {
                    if (!isset($entry_page_stats[$entry_page])) {
                        $entry_page_stats[$entry_page] = array('url' => $entry_page, 'count' => 0);
                    }
                    $entry_page_stats[$entry_page]['count'] += 1;
                }

                // 关键词统计
                $keyword = isset($v['keywords']) && !empty($v['keywords']) ? $v['keywords'] : '';
                if (!empty($keyword)) {
                    $keyword_stats[$keyword] = ($keyword_stats[$keyword] ?? 0) + 1;
                }
            }
            arsort($browser_stats);
            arsort($region_stats);
            uasort($entry_page_stats, function($a, $b) { return $b['count'] - $a['count']; });
            arsort($keyword_stats);
        }
        ?>
        <div class="shiroki-51la-analysis-grid">
            <!-- 🌐 浏览器分布 -->
            <div class="shiroki-51la-card shiroki-51la-analysis-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">🌐</span>
                    <span class="shiroki-51la-card-title">浏览器分布</span>
                    <span class="header-hint">今日</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <?php if (!empty($browser_stats)) :
                        $browser_total = array_sum($browser_stats);
                        $browser_top = array_slice($browser_stats, 0, 5, true);
                        $pie_colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#6366f1'];
                        $grad_parts = array();
                        $cum = 0;
                        $ci = 0;
                        foreach ($browser_top as $name => $count) {
                            $pct = $browser_total > 0 ? $count / $browser_total * 100 : 0;
                            $color = $pie_colors[$ci % count($pie_colors)];
                            $grad_parts[] = $color . ' ' . round($cum, 2) . '% ' . round($cum + $pct, 2) . '%';
                            $cum += $pct;
                            $ci++;
                        }
                        if ($cum < 100) {
                            $grad_parts[] = '#e5e7eb ' . round($cum, 2) . '% 100%';
                        }
                    ?>
                    <div class="pie-chart-wrapper">
                        <div class="pie-chart" style="background: conic-gradient(<?php echo implode(', ', $grad_parts); ?>)"></div>
                        <div class="pie-legend">
                            <?php $ci = 0; foreach ($browser_top as $name => $count) :
                                $pct = $browser_total > 0 ? round($count / $browser_total * 100, 1) : 0;
                                $color = $pie_colors[$ci % count($pie_colors)];
                            ?>
                            <div class="legend-row">
                                <span class="legend-dot" style="background:<?php echo $color; ?>"></span>
                                <span class="legend-label"><?php echo esc_html($name); ?></span>
                                <span class="legend-pct"><?php echo $pct; ?>%</span>
                            </div>
                            <?php $ci++; endforeach; ?>
                        </div>
                    </div>
                    <?php elseif ($is_configured) : ?>
                    <div class="shiroki-51la-empty"><p>暂无数据</p></div>
                    <?php else : ?>
                    <div class="pie-chart-wrapper">
                        <div class="pie-chart" style="background: conic-gradient(#3b82f6 0% 45.2%, #10b981 45.2% 73.8%, #f59e0b 73.8% 86.1%, #ef4444 86.1% 94.8%, #8b5cf6 94.8% 100%)"></div>
                        <div class="pie-legend">
                            <div class="legend-row"><span class="legend-dot" style="background:#3b82f6"></span><span class="legend-label">Chrome</span><span class="legend-pct">45.2%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#10b981"></span><span class="legend-label">Safari</span><span class="legend-pct">28.6%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#f59e0b"></span><span class="legend-label">Firefox</span><span class="legend-pct">12.3%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#ef4444"></span><span class="legend-label">Edge</span><span class="legend-pct">8.7%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#8b5cf6"></span><span class="legend-label">其他</span><span class="legend-pct">5.2%</span></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🌍 地域分布 -->
            <div class="shiroki-51la-card shiroki-51la-analysis-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">🌍</span>
                    <span class="shiroki-51la-card-title">地域分布</span>
                    <span class="header-hint">今日</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <?php if (!empty($region_stats)) :
                        $region_total = array_sum($region_stats);
                        $region_top = array_slice($region_stats, 0, 5, true);
                        $pie_colors_r = ['#6366f1','#ec4899','#14b8a6','#f97316','#64748b','#84cc16','#e11d48'];
                        $grad_parts_r = array();
                        $cum_r = 0;
                        $ci_r = 0;
                        foreach ($region_top as $name => $count) {
                            $pct = $region_total > 0 ? $count / $region_total * 100 : 0;
                            $color = $pie_colors_r[$ci_r % count($pie_colors_r)];
                            $grad_parts_r[] = $color . ' ' . round($cum_r, 2) . '% ' . round($cum_r + $pct, 2) . '%';
                            $cum_r += $pct;
                            $ci_r++;
                        }
                        if ($cum_r < 100) {
                            $grad_parts_r[] = '#e5e7eb ' . round($cum_r, 2) . '% 100%';
                        }
                    ?>
                    <div class="pie-chart-wrapper">
                        <div class="pie-chart" style="background: conic-gradient(<?php echo implode(', ', $grad_parts_r); ?>)"></div>
                        <div class="pie-legend">
                            <?php $ci_r = 0; foreach ($region_top as $name => $count) :
                                $pct = $region_total > 0 ? round($count / $region_total * 100, 1) : 0;
                                $color = $pie_colors_r[$ci_r % count($pie_colors_r)];
                            ?>
                            <div class="legend-row">
                                <span class="legend-dot" style="background:<?php echo $color; ?>"></span>
                                <span class="legend-label"><?php echo esc_html($name); ?></span>
                                <span class="legend-pct"><?php echo $pct; ?>%</span>
                            </div>
                            <?php $ci_r++; endforeach; ?>
                        </div>
                    </div>
                    <?php elseif ($is_configured) : ?>
                    <div class="shiroki-51la-empty"><p>暂无数据</p></div>
                    <?php else : ?>
                    <div class="pie-chart-wrapper">
                        <div class="pie-chart" style="background: conic-gradient(#6366f1 0% 68.5%, #ec4899 68.5% 80.8%, #14b8a6 80.8% 88.9%, #f97316 88.9% 94.5%, #64748b 94.5% 100%)"></div>
                        <div class="pie-legend">
                            <div class="legend-row"><span class="legend-dot" style="background:#6366f1"></span><span class="legend-label">中国</span><span class="legend-pct">68.5%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#ec4899"></span><span class="legend-label">美国</span><span class="legend-pct">12.3%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#14b8a6"></span><span class="legend-label">日本</span><span class="legend-pct">8.1%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#f97316"></span><span class="legend-label">韩国</span><span class="legend-pct">5.6%</span></div>
                            <div class="legend-row"><span class="legend-dot" style="background:#64748b"></span><span class="legend-label">其他</span><span class="legend-pct">5.5%</span></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🚪 入口页 TOP5 -->
            <div class="shiroki-51la-card shiroki-51la-analysis-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">🚪</span>
                    <span class="shiroki-51la-card-title">入口页 TOP5</span>
                    <span class="header-hint">今日</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <?php if (!empty($entry_page_stats)) :
                        $ep_top = array_slice($entry_page_stats, 0, 5, true);
                        $ep_max = max(1, max(array_column($ep_top, 'count')));
                    ?>
                    <div class="analysis-rank-list">
                        <?php $rank = 0; foreach ($ep_top as $ep) : $rank++; ?>
                        <div class="analysis-rank-item">
                            <span class="rank-num <?php echo $rank <= 3 ? 'top' : ''; ?>"><?php echo $rank; ?></span>
                            <div class="rank-info">
                                <span class="rank-name" title="<?php echo esc_attr($ep['url']); ?>"><?php echo esc_html($ep['url']); ?></span>
                            </div>
                            <span class="rank-count"><?php echo $ep['count']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($is_configured) : ?>
                    <div class="shiroki-51la-empty"><p>暂无数据</p></div>
                    <?php else : ?>
                    <div class="analysis-rank-list">
                        <div class="analysis-rank-item"><span class="rank-num top">1</span><div class="rank-info"><span class="rank-name">/</span></div><span class="rank-count">128</span></div>
                        <div class="analysis-rank-item"><span class="rank-num top">2</span><div class="rank-info"><span class="rank-name">/article/latest</span></div><span class="rank-count">85</span></div>
                        <div class="analysis-rank-item"><span class="rank-num top">3</span><div class="rank-info"><span class="rank-name">/category/tech</span></div><span class="rank-count">62</span></div>
                        <div class="analysis-rank-item"><span class="rank-num">4</span><div class="rank-info"><span class="rank-name">/about</span></div><span class="rank-count">41</span></div>
                        <div class="analysis-rank-item"><span class="rank-num">5</span><div class="rank-info"><span class="rank-name">/archives</span></div><span class="rank-count">29</span></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🔑 搜索关键词 -->
            <div class="shiroki-51la-card shiroki-51la-analysis-card shiroki-51la-keyword-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">🔑</span>
                    <span class="shiroki-51la-card-title">搜索关键词</span>
                    <span class="header-hint">今日</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <?php if (!empty($keyword_stats)) :
                        $kw_top = array_slice($keyword_stats, 0, 20, true);
                        $kw_max = max($kw_top);
                        $kw_min = min($kw_top);
                        $cloud_colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316'];
                    ?>
                    <div class="keyword-cloud">
                        <?php $ci = 0; foreach ($kw_top as $kw => $count) :
                            $ratio = $kw_max > $kw_min ? ($count - $kw_min) / ($kw_max - $kw_min) : 0.5;
                            $size = round(12 + $ratio * 16, 1);
                            $color = $cloud_colors[$ci % count($cloud_colors)];
                            $opacity = 0.6 + $ratio * 0.4;
                        ?>
                        <span class="cloud-word" style="font-size:<?php echo $size; ?>px; color:<?php echo $color; ?>; opacity:<?php echo $opacity; ?>;"><?php echo esc_html($kw); ?></span>
                        <?php $ci++; endforeach; ?>
                    </div>
                    <?php elseif ($is_configured) : ?>
                    <div class="shiroki-51la-empty"><p>暂无数据</p></div>
                    <?php else : ?>
                    <div class="keyword-cloud">
                        <span class="cloud-word" style="font-size:26px;color:#3b82f6;opacity:1">wordpress主题</span>
                        <span class="cloud-word" style="font-size:22px;color:#10b981;opacity:0.9">51la统计</span>
                        <span class="cloud-word" style="font-size:19px;color:#f59e0b;opacity:0.85">盒子萌纸鸢版</span>
                        <span class="cloud-word" style="font-size:16px;color:#ef4444;opacity:0.8">纸鸢社</span>
                        <span class="cloud-word" style="font-size:14px;color:#8b5cf6;opacity:0.7">二创开发</span>
                        <span class="cloud-word" style="font-size:13px;color:#ec4899;opacity:0.65">Lolimeow主题</span>
                        <span class="cloud-word" style="font-size:12px;color:#14b8a6;opacity:0.6">数据仪表盘开发</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 📋 数据说明 -->
        <div class="shiroki-51la-details">
            <div class="shiroki-51la-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">ℹ️</span>
                    <span class="shiroki-51la-card-title">数据说明</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <ul class="shiroki-51la-info-list">
                        <li><strong>PV (Page View)</strong>：页面浏览量，每次页面加载计为一次</li>
                        <li><strong>UV (Unique Visitor)</strong>：独立访客数，按用户去重统计</li>
                        <li><strong>IP</strong>：独立 IP 数，按访问者 IP 去重</li>
                        <li><strong>数据更新</strong>：51LA 数据实时更新，可能存在短暂延迟，停留页面每5秒更新一次</li>
                        <?php if ($demo_mode) : ?>
                        <li style="color: var(--admin-warning-text);"><strong>⚠️ 演示模式</strong>：当前显示的是演示数据，配置 API 后将显示真实数据</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($demo_mode || !empty($chart_labels)) : ?>
        <!-- 📊 图表数据 -->
        <script type="text/javascript">
            window.shiroki51LAData = {
                labels: <?php echo json_encode($chart_labels); ?>,
                pv: <?php echo json_encode($chart_pv); ?>,
                uv: <?php echo json_encode($chart_uv); ?>
            };
        </script>
        <?php endif; ?>

        <?php if ($is_configured && !$has_real_data) : ?>
        <!-- ⏳ 加载中 -->
        <div class="shiroki-51la-loading">
            <div class="loading-spinner"></div>
            <p>正在加载数据...</p>
        </div>
        <?php endif; ?>
        </div>

        <!-- 📖 使用说明标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="guide">
            <div class="shiroki-51la-card shiroki-51la-guide-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">📖</span>
                    <span class="shiroki-51la-card-title">51LA 统计使用指南</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <div class="guide-section">
                        <h4>🚀 什么是 51LA 统计？</h4>
                        <p>51LA 是国内知名的免费网站流量统计服务，提供专业的网站访问数据分析功能，帮助您了解网站的访问情况和用户行为。</p>
                    </div>

                    <div class="guide-section">
                        <h4>📝 如何获取 API 密钥？</h4>
                        <ol class="guide-steps">
                            <li>
                                <strong>注册/登录账号</strong>
                                <p>访问 <a href="https://v6.51.la" target="_blank">v6.51.la</a> 注册或登录您的 51LA 账号</p>
                            </li>
                            <li>
                                <strong>添加站点</strong>
                                <p>在控制台中添加您的网站，获取统计代码并部署到网站上</p>
                            </li>
                            <li>
                                <strong>申请 API 权限</strong>
                                <p>进入「用户中心」→「应用管理」→「开放接口」，申请开通 API 权限</p>
                            </li>
                            <li>
                                <strong>获取密钥</strong>
                                <p>系统会自动生成 <code>AccessKey</code> 和 <code>SecretKey</code>，请妥善保存</p>
                            </li>
                            <li>
                                <strong>配置插件</strong>
                                <p>将获取到的密钥填入上方配置表单，点击保存即可</p>
                            </li>
                        </ol>
                    </div>

                    <div class="guide-section">
                        <h4>📊 数据指标说明</h4>
                        <ul class="guide-metrics">
                            <li><strong>PV (Page View)</strong> - 页面浏览量，每次页面加载计为一次</li>
                            <li><strong>UV (Unique Visitor)</strong> - 独立访客数，按用户去重统计</li>
                            <li><strong>IP</strong> - 独立 IP 数，按访问者 IP 去重</li>
                            <li><strong>跳出率</strong> - 只访问一个页面就离开的访客比例</li>
                            <li><strong>平均停留时长</strong> - 访客在网站的平均停留时间</li>
                        </ul>
                    </div>

                    <div class="guide-section">
                        <h4>⚠️ 注意事项</h4>
                        <ul class="guide-tips">
                            <li>API 数据有一定延迟，通常为 5-15 分钟</li>
                            <li>免费版 API 有调用频率限制，请合理使用</li>
                            <li>请妥善保管您的 SecretKey，不要泄露给他人</li>
                            <li>如需更详细的数据分析，请访问 <a href="https://v6.51.la" target="_blank">51LA 官网</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📋 访客明细标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="visitor-detail">
            <div class="shiroki-51la-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">📋</span>
                    <span class="shiroki-51la-card-title">访客明细</span>
                    <span class="header-hint" id="vd-period-label">今日访问记录</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <!-- 📅 时间筛选 -->
                    <div class="shiroki-51la-filter-bar vd-filter-bar">
                        <div class="filter-group">
                            <label>时间范围</label>
                            <div class="filter-buttons" id="vd-time-filter">
                                <button type="button" class="filter-btn active" data-vd-period="today">今日</button>
                                <button type="button" class="filter-btn" data-vd-period="yesterday">昨日</button>
                                <button type="button" class="filter-btn" data-vd-period="7days">最近7日</button>
                                <button type="button" class="filter-btn" data-vd-period="month">本月</button>
                                <button type="button" class="filter-btn" data-vd-period="custom">自定义</button>
                            </div>
                            <div class="custom-date-range" id="vd-custom-date" style="display:none;">
                                <input type="date" id="vd-date-start" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                                <span>至</span>
                                <input type="date" id="vd-date-end" value="<?php echo date('Y-m-d'); ?>">
                                <button type="button" class="shiroki-51la-btn shiroki-51la-btn-primary btn-sm" id="vd-apply-custom">查询</button>
                            </div>
                        </div>
                    </div>
                    <?php
                    $vd_list = $visitor_detail_data && !empty($visitor_detail_data['data']) ? $visitor_detail_data['data'] : ($visitor_detail_data && !empty($visitor_detail_data['bean']) ? $visitor_detail_data['bean'] : null);
                    $vd_total = $visitor_detail_data && isset($visitor_detail_data['total']) ? intval($visitor_detail_data['total']) : 0;
                    $vd_cur_page = $visitor_detail_data && isset($visitor_detail_data['curPage']) ? intval($visitor_detail_data['curPage']) : 1;
                    $vd_pages = $visitor_detail_data && isset($visitor_detail_data['pages']) ? intval($visitor_detail_data['pages']) : 1;
                    ?>
                    <?php if ($is_configured && $vd_list && is_array($vd_list)) : ?>
                    <div class="shiroki-51la-table-wrapper" id="vd-table-wrapper">
                        <table class="shiroki-51la-data-table shiroki-51la-visitor-table">
                            <thead>
                                <tr>
                                    <th class="col-time">时间</th>
                                    <th class="col-region">地区</th>
                                    <th class="col-type">访客类型</th>
                                    <th class="col-ip">IP</th>
                                    <th class="col-src">来路</th>
                                    <th class="col-entry">入口页</th>
                                    <th class="col-browser">浏览器</th>
                                    <th class="col-pv">PV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vd_list as $v) :
                                    $time    = isset($v['time']) ? $v['time'] : (isset($v['dateTime']) ? $v['dateTime'] : '--');
                                    $region  = isset($v['region']) && !empty($v['region']) ? $v['region'] : '--';
                                    $vtype   = isset($v['visitorType']) ? $v['visitorType'] : '--';
                                    $ip      = isset($v['ip']) ? $v['ip'] : '--';
                                    $src     = isset($v['srcUrl']) && !empty($v['srcUrl']) ? $v['srcUrl'] : '';
                                    $entry   = isset($v['entryPage']) && !empty($v['entryPage']) ? $v['entryPage'] : '--';
                                    $browser = isset($v['browser']) ? $v['browser'] : '--';
                                    $pv      = isset($v['pv']) ? intval($v['pv']) : 0;
                                ?>
                                <tr>
                                    <td class="col-time"><?php echo esc_html($time); ?></td>
                                    <td class="col-region"><?php echo esc_html($region); ?></td>
                                    <td class="col-type">
                                        <span class="visitor-badge <?php echo $vtype === '新访客' ? 'new' : 'returning'; ?>">
                                            <?php echo esc_html($vtype); ?>
                                        </span>
                                    </td>
                                    <td class="col-ip"><code><?php echo esc_html($ip); ?></code></td>
                                    <td class="col-src">
                                        <?php if (!empty($src)) : ?>
                                        <a href="<?php echo esc_url($src); ?>" target="_blank" class="src-link" title="<?php echo esc_attr($src); ?>"><?php echo esc_html(mb_strimwidth($src, 0, 40, '...')); ?></a>
                                        <?php else: ?>
                                        <span class="text-muted">直接访问</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-entry" title="<?php echo esc_attr($entry); ?>"><?php echo esc_html(mb_strimwidth($entry, 0, 35, '...')); ?></td>
                                    <td class="col-browser"><?php echo esc_html($browser); ?></td>
                                    <td class="col-pv"><?php echo $pv; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($vd_pages > 1) : ?>
                    <div class="shiroki-51la-pagination">
                        <div class="pagination-info">
                            共 <?php echo number_format($vd_total); ?> 条记录，第 <?php echo $vd_cur_page; ?>/<?php echo $vd_pages; ?> 页
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php elseif ($is_configured) : ?>
                    <div class="shiroki-51la-empty"><p>暂无访客数据</p></div>
                    <?php else : ?>
                    <div class="shiroki-51la-empty"><p>请先配置 API</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 🏠 站点列表标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="site-list">
            <?php
            $sites = $site_list_data && !empty($site_list_data['data']) ? $site_list_data['data'] : array();
            ?>
            <?php if ($is_configured && !empty($sites)) : ?>
            <div class="shiroki-51la-site-grid">
                <?php foreach ($sites as $site) :
                    $name      = isset($site['siteName']) ? $site['siteName'] : '未知站点';
                    $domain    = isset($site['domain']) ? $site['domain'] : '--';
                    $mask      = isset($site['maskId']) ? $site['maskId'] : '--';
                    $created   = isset($site['createTime']) ? $site['createTime'] : '--';
                    $t_pv      = isset($site['todayPv']) ? intval($site['todayPv']) : 0;
                    $t_uv      = isset($site['todayUv']) ? intval($site['todayUv']) : 0;
                    $t_ip      = isset($site['todayIp']) ? intval($site['todayIp']) : 0;
                    $y_pv      = isset($site['yesterdayPv']) ? intval($site['yesterdayPv']) : 0;
                    $y_uv      = isset($site['yesterdayUv']) ? intval($site['yesterdayUv']) : 0;
                    $y_ip      = isset($site['yesterdayIp']) ? intval($site['yesterdayIp']) : 0;
                    $is_active  = ($mask === $config['mask_id']);
                ?>
                <div class="shiroki-51la-site-card <?php echo $is_active ? 'is-active' : ''; ?>">
                    <div class="site-card-header">
                        <div class="site-name"><?php echo esc_html($name); ?></div>
                        <?php if ($is_active) : ?>
                        <span class="site-active-badge">当前</span>
                        <?php endif; ?>
                    </div>
                    <div class="site-domain"><?php echo esc_html($domain); ?></div>
                    <div class="site-meta">
                        <span class="site-mask">ID: <?php echo esc_html($mask); ?></span>
                        <span class="site-created"><?php echo esc_html($created); ?></span>
                    </div>
                    <div class="site-stats-grid">
                        <div class="site-stat">
                            <div class="site-stat-label">今日 PV</div>
                            <div class="site-stat-value"><?php echo number_format($t_pv); ?></div>
                        </div>
                        <div class="site-stat">
                            <div class="site-stat-label">今日 UV</div>
                            <div class="site-stat-value"><?php echo number_format($t_uv); ?></div>
                        </div>
                        <div class="site-stat">
                            <div class="site-stat-label">今日 IP</div>
                            <div class="site-stat-value"><?php echo number_format($t_ip); ?></div>
                        </div>
                        <div class="site-stat">
                            <div class="site-stat-label">昨日 PV</div>
                            <div class="site-stat-value sub"><?php echo number_format($y_pv); ?></div>
                        </div>
                        <div class="site-stat">
                            <div class="site-stat-label">昨日 UV</div>
                            <div class="site-stat-value sub"><?php echo number_format($y_uv); ?></div>
                        </div>
                        <div class="site-stat">
                            <div class="site-stat-label">昨日 IP</div>
                            <div class="site-stat-value sub"><?php echo number_format($y_ip); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php elseif ($is_configured) : ?>
            <div class="shiroki-51la-empty"><p>暂无站点数据</p></div>
            <?php else : ?>
            <div class="shiroki-51la-empty"><p>请先配置 API</p></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($demo_mode || !empty($chart_labels)) : ?>
    <!-- 📊 图表数据 -->
    <script type="text/javascript">
        window.shiroki51LAData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            pv: <?php echo json_encode($chart_pv); ?>,
            uv: <?php echo json_encode($chart_uv); ?>
        };
    </script>
    <?php endif; ?>
    <?php
}
