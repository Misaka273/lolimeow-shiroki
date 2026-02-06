<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
?>
        <div class="<?php echo boxmoe_layout_setting(); ?> blog-post">
        
        <div class="mb-3 d-flex flex-wrap sort-nav">
          <?php 
          $current_orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date';
          $current_order = isset($_GET['order']) ? strtoupper($_GET['order']) : '';
          
          // Defaults if order not set
          if (empty($current_order)) {
              $current_order = ($current_orderby == 'title') ? 'ASC' : 'DESC';
          }

          $sort_links = [
              'title' => '按名称',
              'modified' => '按更新时间',
              'date' => '按发布时间',
              'views' => '按阅读量',
              'likes' => '按点赞',
              'favorites' => '按收藏'
          ];
          
          foreach ($sort_links as $key => $label) {
              $active_class = ($current_orderby == $key) ? 'btn-primary' : 'btn-outline-secondary';
              
              // Determine next order
              $next_order = 'DESC';
              if ($current_orderby == $key) {
                  $next_order = ($current_order == 'DESC') ? 'ASC' : 'DESC';
              } else {
                   $next_order = ($key == 'title') ? 'ASC' : 'DESC';
              }

              // Add icon
              $icon = '';
              if ($current_orderby == $key) {
                  $icon = ($current_order == 'DESC') ? ' <i class="fa fa-angle-down"></i>' : ' <i class="fa fa-angle-up"></i>';
              }

              echo '<a href="' . esc_url(add_query_arg(['orderby' => $key, 'order' => $next_order])) . '" class="btn btn-sm me-2 mb-2 sort-btn ' . $active_class . '">' . $label . $icon . '</a> ';
          }
          ?>
        </div>

        <?php 
        $article_layout_style = get_boxmoe('boxmoe_article_layout_style', 'single');
        $kanban_image = get_boxmoe('boxmoe_article_card_kanban_image', '');
        $kanban_scope = get_boxmoe('boxmoe_article_card_kanban_scope', 'home');
        
        // 检查是否显示看板娘
        $show_kanban = false;
        if (!empty($kanban_image)) {
            if ($kanban_scope == 'home' && is_home()) {
                $show_kanban = true;
            } elseif ($kanban_scope == 'all') {
                $show_kanban = true;
            }
        }
        
        // 📌 动态生成看板娘CSS
        if ($show_kanban) {
            echo '<style>';
            echo '.list-one.post-list:before, .list-three.post-list:before {';
            echo 'background-image: url('.esc_url($kanban_image).');';
            echo '}';
            echo '.post-list.blog-glass:after {';
            echo 'background-image: url('.esc_url($kanban_image).');';
            echo '}';
            echo '</style>';
        }
        
        // 根据布局设置选择容器类和列宽
        echo '<div class="row g-4">';
        
        // 获取当前页码
        $paged = get_query_var('paged') ?: 1;
        
        // 获取当前的置顶文章
        $sticky_posts = get_option('sticky_posts');
        
        // 初始化已显示文章ID数组
        $displayed_post_ids = array();
        
        while ( have_posts() ) : the_post(); 
            // 获取当前文章ID
            $post_id = get_the_ID();
            
            // 检查文章是否已经显示过
            if (in_array($post_id, $displayed_post_ids)) {
                continue; // 跳过已显示的文章
            }
            
            // 将当前文章ID添加到已显示数组
            $displayed_post_ids[] = $post_id;
        ?>
          <div class="<?php echo ($article_layout_style == 'three') ? 'col-lg-4 col-md-6 col-sm-6' : 'col-lg-12'; ?>">
          <article class="post-list <?php echo ($article_layout_style == 'three') ? 'list-three' : 'list-one'; ?> <?php echo boxmoe_border_setting(); ?>">
            <!-- 😏一个巨无霸的透明伪容器，实现点击整个卡片跳转 -->
            <a class="post-card-link" <?php echo boxmoe_article_new_window(); ?> rel="noopener noreferrer" href="<?php echo get_the_permalink(); ?>" title="<?php echo get_the_title().get_the_subtitle(false).boxmoe_title_link().get_bloginfo('name')?>"></a>
            
            <!-- ✨ 悬停光晕效果 - 从左侧边缘扩散 -->
            <div class="card-glow-effect"></div>
            
            <?php if ( post_password_required() ) : ?>
            <span class="post-protected-badge">密码保护</span>
            <?php elseif ( get_post_status() === 'private' ) : ?>
            <span class="post-private-badge">私密文章</span>
            <?php elseif ( is_sticky() ) : ?>
            <span class="post-sticky-badge">置顶</span>
            <?php endif; ?>
            <div class="post-list-img">
              <figure class="zoom-img">
                <a <?php echo boxmoe_article_new_window(); ?> rel="noopener noreferrer" href="<?php echo get_the_permalink(); ?>" title="<?php echo get_the_title().get_the_subtitle(false).boxmoe_title_link().get_bloginfo('name')?>">
                  <img src="<?php echo boxmoe_lazy_load_images(); ?>" data-src="<?php echo boxmoe_article_thumbnail_src(); ?>?id=<?php echo get_the_ID(); ?>" alt="<?php the_title(); ?>" class="img-fluid rounded-3 lazy"></a>
              </figure>
            </div>
            <div class="post-list-content">
              <div class="category">
                <div class="tags">
                  <?php 
                  $categories = get_the_category();
                  if (!empty($categories)) {
                      $first_category = $categories[0]; ?>
                      <a href="<?php echo esc_url(get_category_link($first_category->term_id)); ?>" title="查看《<?php echo esc_attr($first_category->name); ?>》分类下的所有文章" rel="category tag">
                        <i class="tagfa fa fa-dot-circle-o"></i><?php echo esc_html($first_category->name); ?>
                      </a>
                  <?php } ?>
                </div>
              </div>
              <div class="mt-2 mb-2">
                <h3 class="post-title h6">
                  <a <?php echo boxmoe_article_new_window(); ?> rel="noopener noreferrer" href="<?php echo get_the_permalink(); ?>" title="<?php echo get_the_title().get_the_subtitle(false).boxmoe_title_link().get_bloginfo('name')?>"><?php echo get_the_title(); ?></a></h3>
                <p class="post-content small"><?php echo _get_excerpt(100); ?></p></div>
              <div class="post-meta align-items-center small">
                <div class="post-list-avatar">
                <img src="<?php echo boxmoe_lazy_load_images(); ?>" data-src="<?php echo boxmoe_get_avatar_url(get_the_author_meta('ID'), 50); ?>" alt="avatar" class="avatar lazy">
                    </div>
                <div class="post-meta-info">
                  <div class="post-meta-stats">
                    <span class="list-post-view">
                      <i class="fa fa-street-view"></i><?php echo getPostViews(get_the_ID()); ?></span>
                    <span class="list-post-comment">
                      <i class="fa fa-comments-o"></i><?php echo get_comments_number(); ?></span>
                  </div>
                  <span class="list-post-author">
                    <i class="fa fa-at"></i><?php echo get_the_author(); ?>
                    <?php if(!get_boxmoe('boxmoe_hide_publish_date_switch')): ?>
                    <span class="dot"></span><?php echo get_the_date(); ?></span>
                    <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
          </div>
        <?php 
        endwhile; 
        echo '</div>';
        ?>
          <div class="col-lg-12 col-md-12 pagenav">
            <?php boxmoe_pagination(); ?>            
          </div>
          <!-- 无限加载相关结构 -->
          <?php if (get_boxmoe('boxmoe_article_paging_type') == 'infinite') : ?>
          <div id="infinite-load-container" class="infinite-load-container">
            <div id="infinite-loader" class="infinite-loader" style="display: none;">
              <div class="loader-inner">
                <div class="loader-line-wrap">
                  <div class="loader-line"></div>
                </div>
                <div class="loader-line-wrap">
                  <div class="loader-line"></div>
                </div>
                <div class="loader-line-wrap">
                  <div class="loader-line"></div>
                </div>
              </div>
            </div>
            <div id="infinite-end-message" class="infinite-end-message" style="display: none;">
              <nav class="d-flex justify-content-center">
                <ul class="pagination">
                  <li class="page-item active"><a class="page-link" href="#">🎉 很高兴你翻到了这里，但是真的没有惹... 🔝</a></li>
                </ul>
              </nav>
            </div>
          </div>
          <?php endif; ?>
        </div>