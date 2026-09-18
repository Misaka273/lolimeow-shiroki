<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📊 站点数据统计核心类
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
 * ⏱️ 添加自定义定时任务间隔（必须在类初始化前注册）
 */
add_filter('cron_schedules', function($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display' => __('每5分钟', 'lolimeow')
    );
    $schedules['one_minute'] = array(
        'interval' => 60,
        'display' => __('每分钟', 'lolimeow')
    );
    return $schedules;
});

/**
 * 📊 站点数据统计类
 */
class Shiroki_Site_Stats {

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📋 数据库表名
     */
    private $table_stats;
    private $table_visits;

    /**
     * 💾 内存缓存 - 统计数据
     */
    private $stats_cache = array();

    /**
     * 💾 内存缓存 - 访问记录
     */
    private $visits_cache = array();

    /**
     * 📝 已记录的访问标识（用于去重）
     */
    private $recorded_visits = array();

    /**
     * 🔢 缓存批量写入阈值
     */
    private $cache_threshold = 10;

    /**
     * 📝 缓存transient键名
     */
    private $cache_key_stats = 'shiroki_stats_cache';
    private $cache_key_visits = 'shiroki_visits_cache';

    /**
     * 📝 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 🚀 构造函数
     */
    private function __construct() {
        global $wpdb;
        $this->table_stats = $wpdb->prefix . 'shiroki_site_stats';
        $this->table_visits = $wpdb->prefix . 'shiroki_site_visits';

        // 💾 从 transient 加载缓存数据
        $this->stats_cache = get_transient($this->cache_key_stats) ?: array();
        $this->visits_cache = get_transient($this->cache_key_visits) ?: array();

        $this->init_hooks();
        $this->maybe_create_tables();
    }

    /**
     * 🔗 初始化钩子
     */
    private function init_hooks() {
        // 📖 文章阅读统计
        add_action('wp', array($this, 'track_post_view'));

        // 🌐 站点访问统计
        add_action('wp', array($this, 'track_site_visit'));

        // 📥 文件下载统计（通过短代码或链接）
        add_action('wp_ajax_shiroki_track_download', array($this, 'ajax_track_download'));
        add_action('wp_ajax_nopriv_shiroki_track_download', array($this, 'ajax_track_download'));

        // 📥 注册下载按钮短码 [downloadbtn]
        add_shortcode('downloadbtn', array($this, 'shortcode_downloadbtn'));

        // 📥 前端下载点击追踪脚本
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_tracking'));

        // 🗑️ 清理旧数据（每月一次）
        add_action('shiroki_cleanup_stats', array($this, 'cleanup_old_data'));
        if (!wp_next_scheduled('shiroki_cleanup_stats')) {
            wp_schedule_event(time(), 'monthly', 'shiroki_cleanup_stats');
        }

        // 💾 定期刷新缓存到数据库（每分钟）
        add_action('shiroki_flush_stats_cache', array($this, 'flush_cache_to_db'));
        if (!wp_next_scheduled('shiroki_flush_stats_cache')) {
            wp_schedule_event(time(), 'one_minute', 'shiroki_flush_stats_cache');
        }

        // 📝 异步刷新缓存事件
        add_action('shiroki_flush_stats_cache_async', array($this, 'flush_cache_to_db'));

        // 📝 页面关闭时调度异步写入（不阻塞页面响应）
        add_action('shutdown', array($this, 'schedule_async_flush'), 5);
    }

    /**
     * 🗄️ 创建数据库表
     */
    private function maybe_create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 📊 统计数据表
        $sql_stats = "CREATE TABLE IF NOT EXISTS {$this->table_stats} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            stat_type varchar(50) NOT NULL COMMENT '统计类型: post_view, site_visit, download',
            object_id bigint(20) unsigned DEFAULT 0 COMMENT '对象ID(文章ID或文件ID)',
            object_type varchar(50) DEFAULT '' COMMENT '对象类型: post, page, file',
            stat_date date NOT NULL COMMENT '统计日期',
            stat_count bigint(20) unsigned DEFAULT 1 COMMENT '统计数量',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_stat (stat_type, object_id, stat_date),
            KEY idx_stat_type (stat_type),
            KEY idx_stat_date (stat_date),
            KEY idx_object_id (object_id)
        ) {$charset_collate};";

        // 🌐 访问记录表（用于去重和详细分析）
        $sql_visits = "CREATE TABLE IF NOT EXISTS {$this->table_visits} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            visit_type varchar(50) NOT NULL COMMENT '访问类型: page_view, unique_visit',
            object_id bigint(20) unsigned DEFAULT 0,
            user_id bigint(20) unsigned DEFAULT 0,
            user_ip varchar(100) DEFAULT '',
            user_agent text,
            referrer text,
            visit_time datetime DEFAULT CURRENT_TIMESTAMP,
            visit_date date NOT NULL,
            PRIMARY KEY (id),
            KEY idx_visit_date (visit_date),
            KEY idx_user_id (user_id),
            KEY idx_object_id (object_id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_stats);
        dbDelta($sql_visits);
    }

    /**
     * 📖 记录文章阅读
     */
    public function track_post_view() {
        // 🎯 只在单篇文章页面记录
        if (!is_singular('post') && !is_singular('page')) {
            return;
        }

        // 🚫 不记录爬虫访问
        if ($this->is_bot()) {
            return;
        }

        // 🚫 不记录已登录用户（可选，根据需要调整）
        // if (is_user_logged_in()) return;

        $post_id = get_the_ID();
        $today = current_time('Y-m-d');
        $cache_key = "post_view_{$post_id}_{$today}";

        // 📊 累加到内存缓存
        if (isset($this->stats_cache[$cache_key])) {
            $this->stats_cache[$cache_key]['count']++;
        } else {
            $this->stats_cache[$cache_key] = array(
                'stat_type' => 'post_view',
                'object_id' => $post_id,
                'object_type' => get_post_type($post_id),
                'stat_date' => $today,
                'count' => 1
            );
        }

        // 🌐 同时记录为站点访问
        $this->record_site_visit('page_view', $post_id);

        // 💾 保存缓存到 transient（不立即写入数据库）
        set_transient($this->cache_key_stats, $this->stats_cache, 3600);
    }

    /**
     * 🌐 记录站点访问
     */
    public function track_site_visit() {
        // 🚫 不记录爬虫访问
        if ($this->is_bot()) {
            return;
        }

        // 🚫 不记录后台访问
        if (is_admin()) {
            return;
        }

        $this->record_site_visit('unique_visit', 0);
    }

    /**
     * 📝 记录访问详情
     */
    private function record_site_visit($visit_type, $object_id = 0) {
        $user_id = get_current_user_id();
        $user_ip = $this->get_client_ip();
        $today = current_time('Y-m-d');

        // 📊 生成唯一标识用于去重
        $visit_key = "{$visit_type}_{$user_ip}_{$user_id}_{$today}";

        // 🔄 检查是否已在此请求中记录
        if (isset($this->recorded_visits[$visit_key])) {
            return;
        }

        // 🔄 检查数据库中是否已存在（使用transient缓存查询结果）
        $cache_key = 'shiroki_visit_check_' . md5($visit_key);
        $existing = get_transient($cache_key);

        if ($existing === false) {
            global $wpdb;
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_visits}
                WHERE visit_type = %s AND user_ip = %s AND user_id = %d AND visit_date = %s
                LIMIT 1",
                $visit_type,
                $user_ip,
                $user_id,
                $today
            ));
            // 💾 缓存查询结果5分钟
            set_transient($cache_key, $existing ? '1' : '0', 300);
        }

        if ($existing === '1' || $existing) {
            $this->recorded_visits[$visit_key] = true;
            return;
        }

        // 📝 标记为已记录
        $this->recorded_visits[$visit_key] = true;

        // 💾 添加到访问缓存
        $this->visits_cache[] = array(
            'visit_type' => $visit_type,
            'object_id' => $object_id,
            'user_id' => $user_id,
            'user_ip' => $user_ip,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
            'visit_time' => current_time('mysql'),
            'visit_date' => $today
        );

        // 📊 累加站点访问统计到缓存
        $site_visit_key = "site_visit_0_{$today}";
        if (isset($this->stats_cache[$site_visit_key])) {
            $this->stats_cache[$site_visit_key]['count']++;
        } else {
            $this->stats_cache[$site_visit_key] = array(
                'stat_type' => 'site_visit',
                'object_id' => 0,
                'object_type' => 'site',
                'stat_date' => $today,
                'count' => 1
            );
        }

        // 💾 保存缓存到 transient（不立即写入数据库）
        set_transient($this->cache_key_visits, $this->visits_cache, 3600);
        set_transient($this->cache_key_stats, $this->stats_cache, 3600);
    }

    /**
     * 💾 将缓存数据批量写入数据库
     */
    public function flush_cache_to_db() {
        global $wpdb;

        // 🔄 重新加载最新的 transient 数据（避免异步任务执行时数据不一致）
        $latest_stats_cache = get_transient($this->cache_key_stats) ?: array();
        $latest_visits_cache = get_transient($this->cache_key_visits) ?: array();

        // 🔄 合并内存缓存和 transient 数据（内存中的数据优先）
        $this->stats_cache = array_merge($latest_stats_cache, $this->stats_cache);
        $this->visits_cache = array_merge($latest_visits_cache, $this->visits_cache);

        // 📝 写入统计数据
        if (!empty($this->stats_cache)) {
            $stats_values = array();

            foreach ($this->stats_cache as $item) {
                // 🔒 逐行预处理，确保每个值都正确转义
                $stats_values[] = $wpdb->prepare(
                    "(%s, %d, %s, %s, %d)",
                    $item['stat_type'],
                    $item['object_id'],
                    $item['object_type'],
                    $item['stat_date'],
                    $item['count']
                );
            }

            if (!empty($stats_values)) {
                $sql = "INSERT INTO {$this->table_stats} (stat_type, object_id, object_type, stat_date, stat_count)
                        VALUES " . implode(', ', $stats_values) . "
                        ON DUPLICATE KEY UPDATE stat_count = stat_count + VALUES(stat_count)";
                $wpdb->query($sql);
            }

            // 🗑️ 清空缓存并删除 transient
            $this->stats_cache = array();
            delete_transient($this->cache_key_stats);
        }

        // 📝 写入访问记录
        if (!empty($this->visits_cache)) {
            $visits_values = array();

            foreach ($this->visits_cache as $item) {
                // 🔒 逐行预处理，确保每个值都正确转义
                $visits_values[] = $wpdb->prepare(
                    "(%s, %d, %d, %s, %s, %s, %s, %s)",
                    $item['visit_type'],
                    $item['object_id'],
                    $item['user_id'],
                    $item['user_ip'],
                    $item['user_agent'],
                    $item['referrer'],
                    $item['visit_time'],
                    $item['visit_date']
                );
            }

            if (!empty($visits_values)) {
                $sql = "INSERT INTO {$this->table_visits}
                        (visit_type, object_id, user_id, user_ip, user_agent, referrer, visit_time, visit_date)
                        VALUES " . implode(', ', $visits_values);
                $wpdb->query($sql);
            }

            // 🗑️ 清空缓存并删除 transient
            $this->visits_cache = array();
            delete_transient($this->cache_key_visits);
        }
    }

    /**
     * ⏰ 调度异步刷新任务
     */
    public function schedule_async_flush() {
        // 📝 如果有缓存数据，调度一个单次事件立即执行
        if (!empty($this->stats_cache) || !empty($this->visits_cache)) {
            // 🔍 检查是否已有未执行的相同事件，避免重复调度
            if (!wp_next_scheduled('shiroki_flush_stats_cache_async')) {
                // 使用 WP Cron 调度单次异步任务
                wp_schedule_single_event(time(), 'shiroki_flush_stats_cache_async');
            }
        }
    }

    /**
     * 📥 AJAX 记录下载
     */
    public function ajax_track_download() {
        check_ajax_referer('shiroki_stats_nonce', 'nonce');

        $file_id   = isset($_POST['file_id'])   ? intval($_POST['file_id'])          : 0;
        $file_url  = isset($_POST['file_url'])  ? esc_url_raw($_POST['file_url'])    : '';
        $file_name = isset($_POST['file_name']) ? sanitize_text_field($_POST['file_name']) : '';

        $object_id = 0;

        // 📎 媒体库附件：直接用 attachment ID
        if ($file_id > 0) {
            $object_id = $file_id;
            if (empty($file_name)) {
                $attachment = get_post($file_id);
                $file_name  = $attachment ? $attachment->post_title : '';
            }
        }
        // 🔗 外部链接 / 无附件ID：用 URL 哈希作为标识
        elseif (!empty($file_url)) {
            $object_id = $this->url_to_download_id($file_url);
            if (empty($file_name)) {
                $file_name = basename(wp_parse_url($file_url, PHP_URL_PATH));
            }
            $this->save_download_url_mapping($object_id, $file_url, $file_name);
        }

        if ($object_id !== 0) {
            // 💾 同时写入 transient 缓存（供批量刷新备份）
            $this->record_download($object_id, $file_name);

            // 🚀 直接写入数据库，不依赖 WP-Cron
            global $wpdb;
            $today = current_time('Y-m-d');
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$this->table_stats}
                    (stat_type, object_id, object_type, stat_date, stat_count)
                 VALUES ('download', %s, 'file', %s, 1)
                 ON DUPLICATE KEY UPDATE stat_count = stat_count + 1",
                $object_id,
                $today
            ));
        }

        wp_send_json_success(array('message' => '下载已记录'));
    }

    /**
     * 📥 记录文件下载
     */
    public function record_download($file_id, $file_name = '') {
        $today = current_time('Y-m-d');
        $cache_key = "download_{$file_id}_{$today}";

        // 💾 累加到内存缓存
        if (isset($this->stats_cache[$cache_key])) {
            $this->stats_cache[$cache_key]['count']++;
        } else {
            $this->stats_cache[$cache_key] = array(
                'stat_type' => 'download',
                'object_id' => $file_id,
                'object_type' => 'file',
                'stat_date' => $today,
                'count' => 1
            );
        }

        // 💾 保存缓存到 transient（不立即写入数据库）
        set_transient($this->cache_key_stats, $this->stats_cache, 3600);
    }

    /**
     * 📊 获取统计数据
     */
    public function get_stats($stat_type, $start_date, $end_date, $object_id = 0) {
        global $wpdb;

        $sql = "SELECT SUM(stat_count) as total FROM {$this->table_stats}
                WHERE stat_type = %s AND stat_date BETWEEN %s AND %s";
        $params = array($stat_type, $start_date, $end_date);

        if ($object_id > 0) {
            $sql .= " AND object_id = %d";
            $params[] = $object_id;
        }

        return intval($wpdb->get_var($wpdb->prepare($sql, $params)));
    }

    /**
     * 📊 获取今日统计
     */
    public function get_today_stats($stat_type) {
        $today = current_time('Y-m-d');
        return $this->get_stats($stat_type, $today, $today);
    }

    /**
     * 📊 获取昨日统计
     */
    public function get_yesterday_stats($stat_type) {
        $yesterday = date('Y-m-d', strtotime('-1 day', current_time('timestamp')));
        return $this->get_stats($stat_type, $yesterday, $yesterday);
    }

    /**
     * 📊 获取7天统计
     */
    public function get_7days_stats($stat_type) {
        $end = current_time('Y-m-d');
        $start = date('Y-m-d', strtotime('-6 days', current_time('timestamp')));
        return $this->get_stats($stat_type, $start, $end);
    }

    /**
     * 📊 获取30天统计
     */
    public function get_30days_stats($stat_type) {
        $end = current_time('Y-m-d');
        $start = date('Y-m-d', strtotime('-29 days', current_time('timestamp')));
        return $this->get_stats($stat_type, $start, $end);
    }

    /**
     * 📊 获取年度统计
     */
    public function get_year_stats($stat_type) {
        $end = current_time('Y-m-d');
        $start = date('Y-m-d', strtotime('-1 year', current_time('timestamp')));
        return $this->get_stats($stat_type, $start, $end);
    }

    /**
     * 📈 获取趋势数据（用于图表）
     */
    public function get_trend_data($stat_type, $days = 30) {
        global $wpdb;

        $end = current_time('Y-m-d');
        $start = date('Y-m-d', strtotime("-" . ($days - 1) . " days", current_time('timestamp')));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT stat_date, stat_count FROM {$this->table_stats}
            WHERE stat_type = %s AND stat_date BETWEEN %s AND %s
            ORDER BY stat_date ASC",
            $stat_type,
            $start,
            $end
        ));

        // 📊 填充缺失日期
        $data = array();
        $current = strtotime($start);
        $end_ts = strtotime($end);

        while ($current <= $end_ts) {
            $date = date('Y-m-d', $current);
            $data[$date] = 0;
            $current = strtotime('+1 day', $current);
        }

        foreach ($results as $row) {
            $data[$row->stat_date] = intval($row->stat_count);
        }

        return $data;
    }

    /**
     * 🔥 获取热门文章
     */
    public function get_hot_posts($limit = 10, $days = 30) {
        global $wpdb;

        $start = date('Y-m-d', strtotime("-{$days} days", current_time('timestamp')));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT object_id, SUM(stat_count) as total_views
            FROM {$this->table_stats}
            WHERE stat_type = 'post_view' AND stat_date >= %s
            GROUP BY object_id
            ORDER BY total_views DESC
            LIMIT %d",
            $start,
            $limit
        ));

        $posts = array();
        foreach ($results as $row) {
            $post = get_post($row->object_id);
            if ($post) {
                $posts[] = array(
                    'id' => $row->object_id,
                    'title' => $post->post_title,
                    'views' => $row->total_views,
                    'author' => get_the_author_meta('display_name', $post->post_author),
                    'date' => $post->post_date
                );
            }
        }

        return $posts;
    }

    /**
     * 📥 获取热门下载
     */
    public function get_hot_downloads($limit = 10, $days = 30) {
        global $wpdb;

        $start = date('Y-m-d', strtotime("-{$days} days", current_time('timestamp')));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT object_id, SUM(stat_count) as total_downloads
            FROM {$this->table_stats}
            WHERE stat_type = 'download' AND stat_date >= %s
            GROUP BY object_id
            ORDER BY total_downloads DESC
            LIMIT %d",
            $start,
            $limit
        ));

        $downloads = array();
        foreach ($results as $row) {
            $object_id = $row->object_id;
            $name = '';
            $url  = '';

            // 📎 优先尝试作为媒体库附件（仅当确实是附件时）
            if ($object_id > 0) {
                $attachment = get_post($object_id);
                if ($attachment && $attachment->post_type === 'attachment') {
                    $name = $attachment->post_title;
                    $url  = wp_get_attachment_url($object_id);
                }
            }

            // 🔗 回退：查 URL 哈希映射（外部链接 / 直链媒体文件）
            if (empty($name)) {
                $mapping = $this->get_download_url_mapping($object_id);
                if ($mapping) {
                    $name = $mapping['name'];
                    $url  = $mapping['url'];
                }
            }

            if (empty($name)) {
                $name = '文件 #' . $object_id;
            }

            $downloads[] = array(
                'id'        => $object_id,
                'name'      => $name,
                'url'       => $url,
                'downloads' => intval($row->total_downloads)
            );
        }

        return $downloads;
    }

    /**
     * 👤 获取用户发帖排行
     */
    public function get_user_post_ranking($limit = 10, $days = 30) {
        global $wpdb;

        $start = date('Y-m-d H:i:s', strtotime("-{$days} days", current_time('timestamp')));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_author, COUNT(ID) as post_count
            FROM {$wpdb->posts}
            WHERE post_type = 'post' AND post_status = 'publish'
            AND post_date >= %s
            GROUP BY post_author
            ORDER BY post_count DESC
            LIMIT %d",
            $start,
            $limit
        ));

        $users = array();
        foreach ($results as $row) {
            $user = get_userdata($row->post_author);
            if ($user) {
                $users[] = array(
                    'id' => $row->post_author,
                    'name' => $user->display_name,
                    'avatar' => get_avatar_url($row->post_author, array('size' => 48)),
                    'post_count' => $row->post_count
                );
            }
        }

        return $users;
    }

    /**
     * 🌐 获取用户活跃度排行
     */
    public function get_user_activity_ranking($limit = 10, $days = 30) {
        global $wpdb;

        $start = date('Y-m-d', strtotime("-{$days} days", current_time('timestamp')));

        // 📊 统计登录用户和访客的访问次数
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, user_ip, COUNT(*) as visit_count
            FROM {$this->table_visits}
            WHERE visit_date >= %s
            GROUP BY user_id, user_ip
            ORDER BY visit_count DESC
            LIMIT %d",
            $start,
            $limit
        ));

        $users = array();
        $guest_number = 0;
        foreach ($results as $row) {
            $user_id = intval($row->user_id);
            if ($user_id > 0) {
                $user = get_userdata($user_id);
                if (!$user) {
                    continue;
                }

                $users[] = array(
                    'id' => $user_id,
                    'name' => $user->display_name,
                    'avatar' => get_avatar_url($user_id, array('size' => 48)),
                    'visit_count' => intval($row->visit_count),
                    'is_guest' => false,
                    'guest_number' => 0,
                    'region' => ''
                );
            } else {
                $guest_number++;
                $users[] = array(
                    'id' => 0,
                    'name' => '访客',
                    'avatar' => get_avatar_url(0, array('size' => 48)),
                    'visit_count' => intval($row->visit_count),
                    'is_guest' => true,
                    'guest_number' => $guest_number,
                    'region' => $this->get_ip_region($row->user_ip)
                );
            }
        }

        return $users;
    }

    /**
     * 🌍 获取访客地区
     */
    private function get_ip_region($ip) {
        $ip = trim((string) $ip);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return '未知地区';
        }

        $cache_key = 'shiroki_ip_region_' . md5($ip);
        $cached_region = get_transient($cache_key);
        if ($cached_region !== false) {
            return $cached_region;
        }

        $response = wp_remote_get(
            'https://ipwho.is/' . rawurlencode($ip) . '?lang=zh-CN',
            array(
                'timeout' => 2,
                'redirection' => 2,
            )
        );

        if (is_wp_error($response)) {
            set_transient($cache_key, '未知地区', DAY_IN_SECONDS);
            return '未知地区';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['success'])) {
            set_transient($cache_key, '未知地区', DAY_IN_SECONDS);
            return '未知地区';
        }

        $region = isset($body['region']) ? sanitize_text_field($body['region']) : '';
        $city = isset($body['city']) ? sanitize_text_field($body['city']) : '';
        $location = trim($region . ($region && $city ? ' · ' : '') . $city);
        $location = $location ?: '未知地区';

        set_transient($cache_key, $location, DAY_IN_SECONDS);
        return $location;
    }

    /**
     * 🤖 检测爬虫
     */
    private function is_bot() {
        $bots = array(
            'bot', 'crawl', 'spider', 'slurp', 'baiduspider', 'googlebot',
            'bingbot', 'yandexbot', 'sogou', '360spider', 'sosospider'
        );

        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

        foreach ($bots as $bot) {
            if (strpos($user_agent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 🌐 获取客户端IP
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                return sanitize_text_field($ip);
            }
        }
        return '0.0.0.0';
    }

    /**
     * 📥 [downloadbtn] 短码：渲染下载按钮
     *
     * 用法: [downloadbtn link='https://...']按钮文字[/downloadbtn]
     */
    public function shortcode_downloadbtn($atts, $content = '') {
        $atts = shortcode_atts(array('link' => ''), $atts, 'downloadbtn');
        $link = esc_url($atts['link']);
        $text = esc_html($content ?: '下载');

        if (empty($link)) return '';

        return sprintf(
            '<a href="%s" rel="noopener" target="_blank" class="download_btn" '
            . 'data-bs-toggle="tooltip" data-bs-placement="top" '
            . 'title="该资源来源于网络如有侵权,请联系删除.">%s</a>',
            $link, $text
        );
    }

    /**
     * 📥 前端链接点击追踪（仅统计指定语法生成的链接）
     */
    public function enqueue_frontend_tracking() {
        if (is_admin() || !is_singular(array('post', 'page'))) {
            return;
        }

        add_action('wp_footer', function() {
            $ajax_url = admin_url('admin-ajax.php');
            $nonce    = wp_create_nonce('shiroki_stats_nonce');
?>
<script>
(function() {
    var AJAX_URL = '<?php echo esc_js($ajax_url); ?>';
    var NONCE    = '<?php echo esc_js($nonce); ?>';

    function shouldTrackLink(link) {
        if (!link.closest('.single-content')) {
            return false;
        }

        if (link.classList.contains('download_btn')) {
            return true;
        }

        if (link.classList.contains('links_btn')) {
            return true;
        }

        if (link.classList.contains('shiroki-md-link')) {
            return true;
        }

        // MD 卡片：名称 / 头像链接 / 描述 / 链接 / 勋章
        if (link.classList.contains('md-card-link-wrap')) {
            return true;
        }

        if (link.closest('.post-download-box')) {
            return true;
        }

        return false;
    }

    function resolveTrackUrl(link) {
        var href = (link.getAttribute('href') || '').trim();
        if (!href || href === '#' || href.indexOf('javascript:') === 0) {
            return '';
        }

        var targetUrl = href;

        if (targetUrl.indexOf('url=') !== -1) {
            try {
                var parsed = new URL(targetUrl, window.location.origin);
                var embedded = parsed.searchParams.get('url');
                if (embedded) {
                    targetUrl = decodeURIComponent(embedded);
                }
            } catch (err) {}
        }

        if (/^(mailto:|tel:|javascript:)/i.test(targetUrl)) {
            return '';
        }

        return targetUrl;
    }

    function getTrackName(link) {
        var cardTitle = link.querySelector('.md-card-title');
        if (cardTitle) {
            return (cardTitle.textContent || '').trim();
        }

        return (link.textContent || link.getAttribute('title') || '').trim();
    }

    function trackLinkClick(link, targetUrl) {
        var fileName = getTrackName(link);
        var body = 'action=shiroki_track_download'
                 + '&nonce='     + encodeURIComponent(NONCE)
                 + '&file_url='  + encodeURIComponent(targetUrl)
                 + '&file_name=' + encodeURIComponent(fileName);

        fetch(AJAX_URL, {
            method:      'POST',
            headers:     {'Content-Type': 'application/x-www-form-urlencoded'},
            body:        body,
            keepalive:   true,
            credentials: 'same-origin'
        }).catch(function() {});
    }

    document.addEventListener('click', function(e) {
        var link = e.target.closest('a');
        if (!link || !shouldTrackLink(link)) {
            return;
        }

        var targetUrl = resolveTrackUrl(link);
        if (!targetUrl) {
            return;
        }

        trackLinkClick(link, targetUrl);
    });
})();
</script>
<?php
        });
    }

    /**
     * 🔢 URL 转下载标识 ID（无符号 32 位，兼容 unsigned bigint 列）
     */
    private function url_to_download_id($url) {
        return sprintf('%u', crc32($url));
    }

    /**
     * 💾 保存下载链接 URL ↔ 哈希 ID 映射（用于排行榜展示）
     */
    private function save_download_url_mapping($hash_id, $url, $name = '') {
        $mappings = get_option('shiroki_download_url_map', array());
        $fallback_name = basename(wp_parse_url($url, PHP_URL_PATH));

        if (!isset($mappings[$hash_id])) {
            $mappings[$hash_id] = array(
                'url'  => $url,
                'name' => $name ?: $fallback_name,
            );
        } else {
            $mappings[$hash_id]['url'] = $url;
            if (!empty($name)) {
                $mappings[$hash_id]['name'] = $name;
            } elseif (empty($mappings[$hash_id]['name'])) {
                $mappings[$hash_id]['name'] = $fallback_name;
            }
        }

        update_option('shiroki_download_url_map', $mappings, false);
    }

    /**
     * 📖 查询下载链接 URL ↔ 哈希 ID 映射
     */
    private function get_download_url_mapping($hash_id) {
        $mappings = get_option('shiroki_download_url_map', array());
        return isset($mappings[$hash_id]) ? $mappings[$hash_id] : null;
    }

    /**
     * 🗑️ 清理旧数据
     */
    public function cleanup_old_data() {
        global $wpdb;

        // 🗑️ 保留最近2年的详细访问记录
        $two_years_ago = date('Y-m-d', strtotime('-2 years', current_time('timestamp')));
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_visits} WHERE visit_date < %s",
            $two_years_ago
        ));

        // 🗑️ 保留最近3年的统计数据
        $three_years_ago = date('Y-m-d', strtotime('-3 years', current_time('timestamp')));
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_stats} WHERE stat_date < %s",
            $three_years_ago
        ));
    }
}

/**
 * 🚀 初始化站点统计
 */
function shiroki_init_site_stats() {
    Shiroki_Site_Stats::get_instance();
}
add_action('init', 'shiroki_init_site_stats', 5);
