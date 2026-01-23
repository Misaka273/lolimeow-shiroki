<?php
/**
 * Template Name: 友链分类版-纸鸢社
 * Description: 支持按链接分类显示的友链页面模板
 * Copyright: 白木 © 2025 保留所有权利
 */

// 启用WP原生链接管理功能
add_filter('pre_option_link_manager_enabled', '__return_true');

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
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.shiroki-category-tab {
    padding: 10px 20px;
    background: #f8f9fa;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    position: relative;
}

.shiroki-category-tab:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.shiroki-category-tab.active {
    background: linear-gradient(45deg, #ff6b9d, #fecfef);
    color: #fff;
    box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
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

/* 🌸 友链卡片样式 */
.shiroki-link-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.shiroki-link-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.shiroki-link-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(45deg, #ff6b9d, #fecfef);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.shiroki-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.shiroki-link-card:hover::before {
    transform: scaleX(1);
}

.shiroki-link-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* 🎨 友链图标自适应样式 */
.shiroki-link-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: contain;
    flex-shrink: 0;
}

/* 🎨 网站ICO自适应样式 */
.shiroki-site-icon {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    object-fit: contain;
    border: 1px solid #e0e0e0;
    flex-shrink: 0;
}

.shiroki-link-url {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
    word-break: break-all;
}

.shiroki-link-description {
    font-size: 14px;
    color: #777;
    line-height: 1.5;
    margin-bottom: 15px;
}

.shiroki-link-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #999;
}

.shiroki-link-visit {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: linear-gradient(45deg, #ff6b9d, #fecfef);
    color: #fff;
    border-radius: 15px;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.3s ease;
}

.shiroki-link-visit:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(255, 107, 157, 0.3);
    color: #fff;
}

/* 🌸 响应式设计 */
@media (max-width: 768px) {
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
        color: #e0e0e0 !important;
    }
    
    /* 🌆 页面标题区域 */
    .shiroki-page-header h1 {
        color: #e0e0e0 !important;
    }
    
    .shiroki-page-header p {
        color: #b0b0b0 !important;
    }
    
    /* 🌆 分类导航标签 */
    .shiroki-category-tab {
        background: #2d2d2d !important;
        color: #b0b0b0 !important;
    }
    
    .shiroki-category-tab:hover {
        background: #3d3d3d !important;
    }
    
    .shiroki-category-tab.active {
        background: linear-gradient(45deg, #ff6b9d, #fecfef) !important;
        color: #fff !important;
    }
    
    /* 🌆 分类标题 */
    .shiroki-category-content h2 {
        color: #e0e0e0 !important;
    }
    
    /* 🌆 友链卡片 */
    .shiroki-link-card {
        background: #2d2d2d !important;
        border-color: #404040 !important;
    }
    
    .shiroki-link-title {
        color: #e0e0e0 !important;
    }
    
    .shiroki-link-url {
        color: #b0b0b0 !important;
    }
    
    .shiroki-link-description {
        color: #999 !important;
    }
    
    /* 🌆 友链申请区域 */
    .shiroki-apply-section h2 {
        color: #e0e0e0 !important;
    }
    
    .shiroki-apply-section > div {
        background: #2d2d2d !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
    }
    
    .shiroki-apply-section > div > p {
        color: #b0b0b0 !important;
    }
    
    /* 🌆 申请信息框 */
    .shiroki-apply-section > div > div {
        background: #333 !important;
        color: #e0e0e0 !important;
    }
    
    .shiroki-apply-section > div > div p {
        color: #e0e0e0 !important;
    }
    
    .shiroki-apply-section > div > div p strong {
        color: #e0e0e0 !important;
    }
    
    .shiroki-apply-section > div > div p span {
        color: #b0b0b0 !important;
    }
    
    /* 🌆 提交按钮区域 */
    .shiroki-apply-section a {
        background: linear-gradient(45deg, #ff6b9d, #fecfef) !important;
        color: #fff !important;
    }
    
    /* 🌆 未创建提交页提示 */
    .shiroki-apply-section > div > div[style*="background: #f8d7da"] {
        background: #3d2d2d !important;
        border: 1px solid #5c3d3d !important;
        color: #e0c8c8 !important;
    }
    
    /* 🌆 页脚信息 */
    .shiroki-friendlink-container > div:last-child p {
        color: #666 !important;
    }
    
    /* 🌆 空状态提示 */
    .shiroki-link-grid > div {
        color: #b0b0b0 !important;
    }
}
</style>

<!-- 🎯 友链分类页面主体 -->
<div class="shiroki-friendlink-container">
    <!-- 📝 页面标题 -->
    <div class="shiroki-page-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #333; font-size: 28px; margin-bottom: 15px;"><?php the_title(); ?></h1>
        <p style="color: #666; font-size: 16px; line-height: 1.6;">
            精心整理的友情链接，按类别展示，发现更多精彩网站
        </p>
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
            <h2 style="color: #444; font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
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
                <div class="shiroki-link-card <?php echo esc_attr($category_class); ?>">
                    <div class="shiroki-link-title">
                        <?php if (!empty($link_image)) : ?>
                            <img src="<?php echo esc_url($link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>" class="shiroki-link-icon">
                        <?php else : ?>
                            <span style="font-size: 20px;">- </span>
                        <?php endif; ?>
                        <span><?php echo esc_html($link->link_name); ?></span>
                    </div>
                    <div class="shiroki-link-url"><?php echo esc_url($link->link_url); ?></div>
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
                            <span>→</span>
                        </a>
                    </div>
                </div>
                <?php
                    endforeach;
                else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
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
            <h2 style="color: #444; font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
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
                <div class="shiroki-link-card">
                    <div class="shiroki-link-title">
                        <?php if (!empty($link_image)) : ?>
                            <img src="<?php echo esc_url($link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>" class="shiroki-link-icon">
                        <?php else : ?>
                            <span style="font-size: 20px;">- </span>
                        <?php endif; ?>
                        <span><?php echo esc_html($link->link_name); ?></span>
                    </div>
                    <div class="shiroki-link-url"><?php echo esc_url($link->link_url); ?></div>
                    <div class="shiroki-link-description"><?php echo esc_html($description); ?></div>
                    <div class="shiroki-link-meta">
                        <span class="shiroki-link-category"><?php echo esc_html($category->name); ?></span>
                        <a href="<?php echo esc_url($link->link_url); ?>" target="_blank" class="shiroki-link-visit">
                            <span>访问</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
                <?php
                    endforeach;
                else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
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
    <div class="shiroki-apply-section" style="max-width: 800px; margin: 60px auto 0; border-top: 1px solid #eee; padding-top: 40px;">
        <h2 style="font-size: 22px; margin-bottom: 20px; text-align: center;">申请友链</h2>
        <div style="padding: 30px; border-radius: 12px;">
            <div style="margin-bottom: 20px; line-height: 1.8;">
                <p>欢迎申请友链！请在您的网站添加本站链接后再提交申请：</p>
                <div style="padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p style="margin: 5px 0; display: flex; align-items: center; gap: 10px;">
                        <strong>网站名称：</strong><?php echo get_option('blogname'); ?>
                    </p>
                    <p style="margin: 5px 0; display: flex; align-items: center; gap: 10px;">
                        <strong>网站地址：</strong><?php echo get_option('siteurl'); ?>
                    </p>
                    <p style="margin: 5px 0; display: flex; align-items: flex-start; gap: 10px;">
                        <strong>网站描述：</strong>
                        <span style="font-size: 13px; line-height: 1.5;"><?php echo get_option('blogdescription'); ?></span>
                    </p>
                    <p style="margin: 5px 0; display: flex; align-items: center; gap: 10px;">
                        <strong>网站ICO：</strong>
                        <img src="<?php echo get_site_icon_url(); ?>" alt="<?php echo esc_attr(get_option('blogname')); ?> ICO" class="shiroki-site-icon">
                        <span style="font-size: 13px;"><?php echo get_site_icon_url(); ?></span>
                    </p>
                </div>
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
                    <a href="<?php echo esc_url($friendlink_url); ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; background: linear-gradient(45deg, #ff6b9d, #fecfef); color: #fff; border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.3s ease;">
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
    <div style="text-align: center; margin-top: 40px; padding: 20px; color: #999; font-size: 14px;">
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
    
    // 🎯 友链卡片悬停效果增强
    const linkCards = document.querySelectorAll('.shiroki-link-card');
    linkCards.forEach(card => {
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