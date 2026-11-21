<?php
/**
 * Template Name: 友链分类版-纸鸢社
 * Description: 支持按链接分类显示的友链页面模板
 * Copyright: 白木 https://gl.baimu.live/
 */

// 启用WP原生链接管理功能
add_filter('pre_option_link_manager_enabled', '__return_true');

// 检查页面是否受密码保护
if (post_password_required()) {
    get_header();
    echo get_the_password_form();
    get_footer();
    exit;
}

// 页面前端展示
get_header();
?>

<!-- 🎨 友链分类样式 -->
<style>
/* 🌸 友链分类容器样式 */
.shiroki-friendlink-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

/* 🌸 分类导航标签样式 */
.shiroki-category-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid oklch(0.9551 0 0);
    padding-bottom: 15px;
}

.shiroki-category-tab {
    padding: 10px 20px;
    background: oklch(0.9816 0.0017 247.84);
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: oklch(0.5103 0 0);
    position: relative;
}

.shiroki-category-tab:hover {
    background: oklch(0.9417 0.0052 247.88);
    transform: translateY(-2px);
}

.shiroki-category-tab.active {
    background: linear-gradient(45deg in srgb, oklch(0.7234 0.1854 1.78), oklch(0.9043 0.0669 338.05));
    color: oklch(1 0 0);
    box-shadow: 0 4px 15px oklch(0.7234 0.1854 1.78 / 0.3);
}

/* 🌸 分类内容区域样式 */
.shiroki-category-content {
    display: none;
    animation: fadeIn 0.5s ease;
}

.shiroki-category-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 🌸 本站信息区域 */
.shiroki-site-info {
    background: oklch(0.9816 0.0017 247.84);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    border: 1px solid oklch(0.9551 0 0);
}

.shiroki-site-info-title {
    font-size: 18px;
    font-weight: 600;
    color: oklch(0.3211 0 0);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.shiroki-site-info-desc {
    font-size: 14px;
    color: oklch(0.6268 0 0);
    margin: 0 0 16px;
}

.shiroki-site-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.shiroki-site-info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: oklch(1 0 0);
    border-radius: 8px;
    border: 1px solid oklch(0.9491 0 0);
    min-width: 0;
}

.shiroki-site-info-label {
    flex-shrink: 0;
    font-size: 13px;
    font-weight: 600;
    color: oklch(0.5103 0 0);
    min-width: 72px;
}

.shiroki-site-info-value {
    flex: 1;
    font-size: 13px;
    color: oklch(0.3211 0 0);
    word-break: break-all;
    line-height: 1.4;
    min-width: 0;
}

.shiroki-site-info-value img {
    vertical-align: middle;
}

.shiroki-site-info-copy {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid oklch(0.9067 0 0);
    border-radius: 6px;
    background: oklch(1 0 0);
    color: oklch(0.5103 0 0);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
}

.shiroki-site-info-copy:hover {
    background: linear-gradient(45deg in srgb, oklch(0.7234 0.1854 1.78), oklch(0.9043 0.0669 338.05));
    border-color: oklch(0 0 0 / 0);
    color: oklch(1 0 0);
}

/* 🌸 友链卡片样式（双栏布局） */
.shiroki-link-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.shiroki-link-card {
    background: oklch(0.9816 0.0017 247.84);
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 10px oklch(0 0 0 / 0.08);
    transition: all 0.3s ease;
    border: 1px solid oklch(0.9551 0 0);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 15px;
    color: oklch(0.3211 0 0);
    text-shadow: none;
}

.shiroki-link-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: blur(15px);
    z-index: 1;
    border-radius: 12px;
    opacity: 0.3;
}

.shiroki-link-card > * {
    position: relative;
    z-index: 2;
}

.shiroki-link-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px oklch(0 0 0 / 0.1);
}

.shiroki-link-card:hover::before {
    filter: blur(10px);
    opacity: 0.4;
}

/* 🌸 友链图标样式 */
.shiroki-link-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
    box-shadow: 0 2px 6px oklch(0 0 0 / 0.1);
}

.shiroki-link-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* 🌸 在线状态指示器 */
.shiroki-link-status {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: oklch(0.6731 0.1624 144.21);
    border: 2px solid oklch(1 0 0);
}

/* 🌸 友链信息样式 */
.shiroki-link-info {
    flex: 1;
    min-width: 0;
}

.shiroki-link-title {
    font-size: 16px;
    font-weight: 600;
    color: oklch(0.3211 0 0);
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.shiroki-link-url {
    font-size: 12px;
    color: oklch(0.5103 0 0);
    margin-bottom: 8px;
    word-break: break-all;
}

.shiroki-link-description {
    font-size: 13px;
    color: oklch(0.5693 0 0);
    line-height: 1.4;
    margin-bottom: 10px;
}

.shiroki-link-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: oklch(0.683 0 0);
}

.shiroki-link-visit {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: oklch(0.9673 0.0153 269.99);
    color: oklch(0.5864 0.2109 268.6);
    border-radius: 12px;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.3s ease;
}

.shiroki-link-visit:hover {
    background: oklch(0.5864 0.2109 268.6);
    color: oklch(1 0 0);
    transform: none;
    box-shadow: none;
}

/* 🎨 网站ICO自适应样式 */
.shiroki-site-icon {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    object-fit: contain;
    border: 1px solid oklch(0.9067 0 0);
    flex-shrink: 0;
}

/* 🌸 响应式设计 */
@media (max-width: 768px) {
    .shiroki-site-info-grid,
    .shiroki-link-grid {
        grid-template-columns: 1fr;
    }
    
    .shiroki-category-tabs {
        justify-content: center;
    }
    
    .shiroki-category-tab {
        font-size: 13px;
        padding: 8px 15px;
    }
}

/* 🌸 暗色模式适配 */
@media (prefers-color-scheme: dark) {
    /* 🌆 全局容器 */
    .shiroki-friendlink-container {
        color: oklch(0.9067 0 0) !important;
    }
    
    /* 🌆 页面标题区域 */
    .shiroki-page-header h1 {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-page-header p {
        color: oklch(0.7572 0 0) !important;
    }
    
    /* 🌆 分类导航标签 */
    .shiroki-category-tab {
        background: oklch(0.2972 0 0) !important;
        color: oklch(0.7572 0 0) !important;
    }
    
    .shiroki-category-tab:hover {
        background: oklch(0.36 0 0) !important;
    }
    
    .shiroki-category-tab.active {
        background: linear-gradient(45deg in srgb, oklch(0.7234 0.1854 1.78), oklch(0.9043 0.0669 338.05)) !important;
        color: oklch(1 0 0) !important;
    }
    
    /* 🌆 本站信息区域 */
    .shiroki-site-info {
        background: oklch(0.2972 0 0) !important;
        border-color: oklch(0.3715 0 0) !important;
    }
    
    .shiroki-site-info-title {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-site-info-desc {
        color: oklch(0.683 0 0) !important;
    }
    
    .shiroki-site-info-item {
        background: oklch(0.3211 0 0) !important;
        border-color: oklch(0.3867 0 0) !important;
    }
    
    .shiroki-site-info-label {
        color: oklch(0.683 0 0) !important;
    }
    
    .shiroki-site-info-value {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-site-info-copy {
        background: oklch(0.36 0 0) !important;
        border-color: oklch(0.4495 0 0) !important;
        color: oklch(0.7572 0 0) !important;
    }
    
    /* 🌆 分类标题 */
    .shiroki-category-content h2 {
        color: oklch(0.9067 0 0) !important;
    }
    
    /* 🌆 友链卡片 */
    .shiroki-link-card {
        background: oklch(0.2972 0 0) !important;
        border-color: oklch(0.3715 0 0) !important;
        color: oklch(0.9067 0 0) !important;
        text-shadow: none !important;
    }
    
    .shiroki-link-card::before {
        opacity: 0.2 !important;
    }
    
    .shiroki-link-title {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-link-description {
        color: oklch(0.7572 0 0) !important;
    }
    
    .shiroki-link-category {
        color: oklch(0.683 0 0) !important;
    }
    
    .shiroki-link-visit {
        background: oklch(0.3551 0.0276 284.91) !important;
        color: oklch(0.7572 0 0) !important;
    }
    
    .shiroki-link-visit:hover {
        background: oklch(0.5864 0.2109 268.6) !important;
        color: oklch(1 0 0) !important;
    }
    
    .shiroki-link-card:hover::before {
        filter: blur(10px) !important;
        opacity: 0.3 !important;
    }
    
    /* 🌆 友链申请区域 */
    .shiroki-apply-section h2 {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-apply-section > div {
        background: oklch(0.2972 0 0) !important;
        box-shadow: 0 4px 15px oklch(0 0 0 / 0.2) !important;
    }
    
    .shiroki-apply-section > div > p {
        color: oklch(0.7572 0 0) !important;
    }
    
    /* 🌆 申请信息框 */
    .shiroki-apply-section > div > div {
        background: oklch(0.3211 0 0) !important;
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-apply-section > div > div p {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-apply-section > div > div p strong {
        color: oklch(0.9067 0 0) !important;
    }
    
    .shiroki-apply-section > div > div p span {
        color: oklch(0.7572 0 0) !important;
    }
    
    /* 🌆 提交按钮区域 */
    .shiroki-apply-section a {
        background: linear-gradient(45deg in srgb, oklch(0.7234 0.1854 1.78), oklch(0.9043 0.0669 338.05)) !important;
        color: oklch(1 0 0) !important;
    }
    
    /* 🌆 未创建提交页提示 */
    .shiroki-apply-section > div > div[style*="background: oklch(0.9075 0.037 11.63)"] {
        background: oklch(0.3155 0.0237 18.49) !important;
        border: 1px solid oklch(0.3956 0.0439 19.21) !important;
        color: oklch(0.8528 0.0271 17.7) !important;
    }
    
    /* 🌆 页脚信息 */
    .shiroki-friendlink-container > div:last-child p {
        color: oklch(0.5103 0 0) !important;
    }
    
    /* 🌆 空状态提示 */
    .shiroki-link-grid > div {
        color: oklch(0.7572 0 0) !important;
    }
}
</style>

<!-- 🎯 友链分类页面主体 -->
<div class="shiroki-friendlink-container">
    <!-- 📝 页面标题 -->
    <div class="shiroki-page-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: oklch(0.3211 0 0); font-size: 28px; margin-bottom: 15px;"><?php the_title(); ?></h1>
        <p style="color: oklch(0.5103 0 0); font-size: 16px; line-height: 1.6;">
            精心整理的友情链接，按类别展示，发现更多精彩网站
        </p>
    </div>

    <?php
    $shiroki_site_name = get_option('blogname');
    $shiroki_site_url = home_url('/');
    $shiroki_site_desc = get_option('blogdescription');
    $shiroki_site_icon = get_site_icon_url();
    $shiroki_site_info_items = [
        ['label' => '网站名称', 'value' => $shiroki_site_name, 'copy' => $shiroki_site_name],
        ['label' => '网站地址', 'value' => $shiroki_site_url, 'copy' => $shiroki_site_url],
        ['label' => '网站描述', 'value' => $shiroki_site_desc, 'copy' => $shiroki_site_desc],
        ['label' => '网站 ICO', 'value' => $shiroki_site_icon, 'copy' => $shiroki_site_icon, 'is_icon' => true],
    ];
    ?>
    <!-- 📋 本站信息（申请友链时请添加以下信息） -->
    <div class="shiroki-site-info">
        <h2 class="shiroki-site-info-title"><span>📋</span><span>本站信息</span></h2>
        <p class="shiroki-site-info-desc">申请友链前，请先在您的网站添加本站链接。点击右侧按钮可快速复制对应信息。</p>
        <div class="shiroki-site-info-grid">
            <?php foreach ($shiroki_site_info_items as $item) : ?>
            <div class="shiroki-site-info-item">
                <span class="shiroki-site-info-label"><?php echo esc_html($item['label']); ?></span>
                <span class="shiroki-site-info-value">
                    <?php if (!empty($item['is_icon']) && !empty($item['value'])) : ?>
                        <img src="<?php echo esc_url($item['value']); ?>" alt="<?php echo esc_attr($shiroki_site_name); ?> ICO" class="shiroki-site-icon">
                        <span><?php echo esc_html($item['value']); ?></span>
                    <?php else : ?>
                        <?php echo esc_html($item['value']); ?>
                    <?php endif; ?>
                </span>
                <?php if (!empty($item['copy'])) : ?>
                <button type="button" class="shiroki-site-info-copy copy-btn" data-copy-text="<?php echo esc_attr($item['copy']); ?>" data-copy-label="<?php echo esc_attr($item['label']); ?>" title="复制<?php echo esc_attr($item['label']); ?>">📋</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 🏷️ 分类导航标签 -->
    <div class="shiroki-category-tabs">
        <button class="shiroki-category-tab active" data-category="all">
            <span>🌐 全部友链</span>
        </button>
        <?php
        // 🎯 动态获取所有链接分类
        $link_categories = get_terms([
            'taxonomy' => 'link_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        // 🎯 为每个分类创建标签
        if (!empty($link_categories) && !is_wp_error($link_categories)) {
            foreach ($link_categories as $category) {
        ?>
        <button class="shiroki-category-tab" data-category="<?php echo esc_attr($category->slug); ?>">
            <span> <?php echo esc_html($category->name); ?></span>
        </button>
        <?php
            }
        }
        ?>
    </div>

    <!-- 📂 分类内容区域 -->
    <div class="shiroki-category-contents">
        <!-- 🌐 全部友链 -->
        <div class="shiroki-category-content active" id="category-all">
            <h2 style="color: oklch(0.3867 0 0); font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>🌐</span>
                <span>全部友链</span>
            </h2>
            <div class="shiroki-link-grid">
                <?php
                // 获取所有友情链接
                $friendlinks = get_bookmarks([
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_invisible' => 1
                ]);

                if (!empty($friendlinks)) :
                    foreach ($friendlinks as $link) :
                        // 获取链接分类
                $link_cats = get_the_terms($link->link_id, 'link_category');
                $category_class = !empty($link_cats) ? 'category-' . $link_cats[0]->slug : 'category-other';
                        
                        // 获取链接描述
                        $description = !empty($link->link_description) ? $link->link_description : '暂无描述';
                        
                        // 获取链接图像
                        $link_image = !empty($link->link_image) ? $link->link_image : get_template_directory_uri() . '/assets/images/default-thumbnail.jpg';
                ?>
                <div class="shiroki-link-card <?php echo esc_attr($category_class); ?>" data-bg="<?php echo esc_url($link_image); ?>">
                    <div class="shiroki-link-avatar">
                        <?php if (!empty($link_image)) : ?>
                            <img src="<?php echo esc_url($link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                        <?php else : ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg in srgb, oklch(0.6271 0.1638 271.53) 0%, oklch(0.5012 0.1384 304.73) 100%); display: flex; align-items: center; justify-content: center; color: oklch(1 0 0); font-size: 24px; font-weight: bold;">
                                <?php echo esc_html(strtoupper(substr($link->link_name, 0, 1))); ?>
                            </div>
                        <?php endif; ?>
                        <div class="shiroki-link-status"></div>
                    </div>
                    <div class="shiroki-link-info">
                        <div class="shiroki-link-title">
                            <span><?php echo esc_html($link->link_name); ?></span>
                        </div>
                        <div class="shiroki-link-description"><?php echo esc_html($description); ?></div>
                        <div class="shiroki-link-meta">
                            <span class="shiroki-link-category">
                                <?php 
                                if (!empty($link_cats)) {
                                    echo esc_html($link_cats[0]->name);
                                } else {
                                    echo '未分类';
                                }
                                ?>
                            </span>
                            <a href="<?php echo esc_url($link->link_url); ?>" target="_blank" class="shiroki-link-visit">
                                <span>访问</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    endforeach;
                else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: oklch(0.5103 0 0);">
                    <p style="font-size: 16px; margin-bottom: 15px;">暂无友情链接</p>
                    <p style="font-size: 14px;">欢迎提交友链申请～</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        // 🎯 动态生成每个分类的内容区域
        if (!empty($link_categories) && !is_wp_error($link_categories)) {
            foreach ($link_categories as $category) {
                // 🎯 获取该分类下的所有链接
                $category_links = get_bookmarks([
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_invisible' => 1,
                    'category' => $category->term_id
                ]);
        ?>
        <!-- 🎯 <?php echo esc_html($category->name); ?> 分类 -->
        <div class="shiroki-category-content" id="category-<?php echo esc_attr($category->slug); ?>">
            <h2 style="color: oklch(0.3867 0 0); font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>- </span>
                <span><?php echo esc_html($category->name); ?></span>
            </h2>
            <div class="shiroki-link-grid">
                <?php
                if (!empty($category_links)) :
                    foreach ($category_links as $link) :
                        // 获取链接描述
                        $description = !empty($link->link_description) ? $link->link_description : '暂无描述';
                        
                        // 获取链接图像
                        $link_image = !empty($link->link_image) ? $link->link_image : get_template_directory_uri() . '/assets/images/default-thumbnail.jpg';
                ?>
                <div class="shiroki-link-card" data-bg="<?php echo esc_url($link_image); ?>">
                    <div class="shiroki-link-avatar">
                        <?php if (!empty($link_image)) : ?>
                            <img src="<?php echo esc_url($link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                        <?php else : ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg in srgb, oklch(0.6271 0.1638 271.53) 0%, oklch(0.5012 0.1384 304.73) 100%); display: flex; align-items: center; justify-content: center; color: oklch(1 0 0); font-size: 24px; font-weight: bold;">
                                <?php echo esc_html(strtoupper(substr($link->link_name, 0, 1))); ?>
                            </div>
                        <?php endif; ?>
                        <div class="shiroki-link-status"></div>
                    </div>
                    <div class="shiroki-link-info">
                        <div class="shiroki-link-title">
                            <span><?php echo esc_html($link->link_name); ?></span>
                        </div>
                        <div class="shiroki-link-description"><?php echo esc_html($description); ?></div>
                        <div class="shiroki-link-meta">
                            <span class="shiroki-link-category"><?php echo esc_html($category->name); ?></span>
                            <a href="<?php echo esc_url($link->link_url); ?>" target="_blank" class="shiroki-link-visit">
                                <span>访问</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    endforeach;
                else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: oklch(0.5103 0 0);">
                    <p style="font-size: 16px; margin-bottom: 15px;">暂无<?php echo esc_html($category->name); ?>类友链</p>
                    <p style="font-size: 14px;">欢迎提交<?php echo esc_html($category->name); ?>类友链申请～</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
            }
        }
        ?>
    </div>

    <!-- 📝 友链申请区域 -->
    <div class="shiroki-apply-section" style="max-width: 800px; margin: 60px auto 0; border-top: 1px solid oklch(0.9491 0 0); padding-top: 40px;">
        <h2 style="font-size: 22px; margin-bottom: 20px; text-align: center;">申请友链</h2>
        <div style="padding: 30px; border-radius: 12px;">
            <div style="margin-bottom: 20px; line-height: 1.8;">
                <p>欢迎申请友链！请先在您的网站添加本站链接（信息见页面顶部「本站信息」），再提交申请。</p>
                <p>提交申请后，我们会在审核通过后第一时间邮件通知您。</p>
            </div>
            
            <div style="text-align: center; margin-top: 25px;">
                <?php
                // 🔍 检查友链申请模板文件是否存在
                $template_file = locate_template('page/page-friendlink.php');
                $friendlink_url = '';
                
                // 🎯 如果模板文件存在，尝试获取使用该模板的页面链接
                if (!empty($template_file) && file_exists($template_file)) {
                    // 查找使用该模板的页面
                    $friendlink_pages = get_pages([
                        'meta_key' => '_wp_page_template',
                        'meta_value' => 'page/page-friendlink.php',
                        'number' => 1
                    ]);
                    
                    if (!empty($friendlink_pages)) {
                        $friendlink_url = get_permalink($friendlink_pages[0]->ID);
                    }
                }
                
                // 🎯 如果存在友链申请页面，则显示跳转按钮
                if (!empty($friendlink_url)) {
                ?>
                    <a href="<?php echo esc_url($friendlink_url); ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; background: linear-gradient(45deg in srgb, oklch(0.7234 0.1854 1.78), oklch(0.9043 0.0669 338.05)); color: oklch(1 0 0); border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.3s ease;">
                        <span>📝</span>
                        <span>申请友链</span>
                        <span>→</span>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

    <!-- 📋 页脚信息 -->
    <div style="text-align: center; margin-top: 40px; padding: 20px; color: oklch(0.683 0 0); font-size: 14px;">
        <p>© <?php echo date('Y'); ?> <?php echo get_option('blogname'); ?> | 友链分类页面由 灵阈研都 🕊️ 纸鸢社 提供</p>
    </div>
</div>

<!-- 🎯 分类切换JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 🎯 获取所有分类标签和内容区域
    const categoryTabs = document.querySelectorAll('.shiroki-category-tab');
    const categoryContents = document.querySelectorAll('.shiroki-category-content');
    
    // 🎯 为每个标签添加点击事件
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // 移除所有标签的active类
            categoryTabs.forEach(t => t.classList.remove('active'));
            // 移除所有内容区域的active类
            categoryContents.forEach(c => c.classList.remove('active'));
            
            // 为当前点击的标签添加active类
            this.classList.add('active');
            
            // 获取目标分类ID
            const targetCategory = this.getAttribute('data-category');
            const targetContent = document.getElementById('category-' + targetCategory);
            
            // 显示对应的内容区域
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
    
    // 🎯 设置友链卡片背景图片
    const linkCards = document.querySelectorAll('.shiroki-link-card');
    linkCards.forEach(card => {
        // 获取背景图片URL
        const bgUrl = card.getAttribute('data-bg');
        if (bgUrl) {
            // 创建一个新的样式规则，设置::before伪元素的背景图片
            const style = document.createElement('style');
            const uniqueClass = `card-bg-${Math.random().toString(36).substr(2, 9)}`;
            card.classList.add(uniqueClass);
            style.textContent = `
                .${uniqueClass}::before {
                    background-image: url('${bgUrl}');
                }
            `;
            document.head.appendChild(style);
        }
        
        // 🎯 友链卡片悬停效果增强
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>

<?php
// 加载页脚
get_footer();
?>