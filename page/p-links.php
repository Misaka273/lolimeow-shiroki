<?php 
/**
 * Template Name: 友情链接-盒子萌
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 检查页面是否受密码保护
if (post_password_required()) {
    get_header();
    echo get_the_password_form();
    get_footer();
    exit;
}

get_header();
?>
            <div class="col-md-12 mx-auto">
            <div class="blog-single">
            <?php while (have_posts()) : the_post(); ?>
                <div class="post-single">
                      <h1 class="single-title"><?php the_title(); ?></h1>
                      <hr class="horizontal dark">
                      
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
                      
                      // 🎯 如果存在友链申请页面，显示提交友链按钮
                      if (!empty($friendlink_url)) {
                      ?>
                      <div style="text-align: center; margin: 20px 0;">
                          <a href="<?php echo esc_url($friendlink_url); ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; background: linear-gradient(45deg, #ff6b9d, #fecfef); color: #fff; border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);">
                              <span>📝</span>
                              <span>提交友链</span>
                              <span>→</span>
                          </a>
                      </div>
                      <?php
                      }
                      ?>
                      
                    <div class="single-content">
                        <?php the_content(); ?>
                    </div>
                    <div class="bookmark">
                        <?php
                        // 🎯 获取页面内容中的分类名称
                        $post_content = get_the_content();
                        $category_name = '';
                        
                        // 🔍 调试信息 - 输出原始内容（仅在开发环境中显示）
                        // echo '<!-- 原始内容: ' . esc_html($post_content) . ' -->';
                        
                        // 检查内容中是否指定了分类名称
                        if (!empty($post_content)) {
                            // 应用内容过滤器，确保获取格式化后的内容
                            $post_content = apply_filters('the_content', $post_content);
                            
                            // 🔧 多种方式尝试提取分类名称
                            
                            // 方式1: 尝试从内容中提取第一行非空文本
                            $lines = explode("\n", $post_content);
                            foreach ($lines as $line) {
                                $line = trim(strip_tags($line));
                                if (!empty($line)) {
                                    $category_name = $line;
                                    break;
                                }
                            }
                            
                            // 方式2: 如果方式1失败，尝试使用正则表达式提取可能的分类名称
                            if (empty($category_name)) {
                                // 尝试匹配段落标签中的内容
                                if (preg_match('/<p[^>]*>(.*?)<\/p>/i', $post_content, $matches)) {
                                    $potential_name = trim(strip_tags($matches[1]));
                                    if (!empty($potential_name)) {
                                        $category_name = $potential_name;
                                    }
                                }
                            }
                            
                            // 方式3: 如果前两种方式都失败，尝试提取整个内容的第一段
                            if (empty($category_name)) {
                                $paragraphs = preg_split('/\n\s*\n/', $post_content);
                                if (!empty($paragraphs)) {
                                    $first_paragraph = trim(strip_tags($paragraphs[0]));
                                    if (!empty($first_paragraph)) {
                                        $category_name = $first_paragraph;
                                    }
                                }
                            }
                        }
                        
                        // 🔍 调试信息 - 输出提取的分类名称（仅在开发环境中显示）
                        // echo '<!-- 提取的分类名称: ' . esc_html($category_name) . ' -->';
                        
                        // 🚨 临时调试功能 - 如果URL中包含debug=1参数，显示调试信息
                        // if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                        //     echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
                        //     echo '<h4>调试信息</h4>';
                        //     echo '<p><strong>原始内容:</strong> ' . esc_html($post_content) . '</p>';
                        //     echo '<p><strong>提取的分类名称:</strong> ' . esc_html($category_name) . '</p>';
                        //     echo '<p><strong>可用的链接分类:</strong></p>';
                        //     echo '<ul>';
                        //     if ($link_cats) {
                        //         foreach ($link_cats as $cat) {
                        //             echo '<li>' . esc_html($cat->name) . ' (ID: ' . $cat->term_id . ')</li>';
                        //         }
                        //     }
                        //     echo '</ul>';
                        //     echo '</div>';
                        // }
                        
                        // 获取所有链接分类
                        $link_cats = get_terms('link_category');
                        
                        // 如果指定了分类名称，只显示该分类下的链接
                        if (!empty($category_name)) {
                            $target_category = null;
                            
                            foreach ($link_cats as $cat) {
                                // 使用不区分大小写的比较，并去除首尾空格
                                if (strcasecmp(trim($cat->name), trim($category_name)) === 0) {
                                    $target_category = $cat;
                                    break;
                                }
                            }
                            
                            if ($target_category) {
                                // 获取指定分类下的链接
                                $links = get_bookmarks(array(
                                    'category' => $target_category->term_id,
                                    'orderby' => 'rating',
                                    'order' => 'DESC'
                                ));
                                
                                if ($links) {
                                    echo '<h2 class="main-reveal">';
                                    echo '<span>' . esc_html($target_category->name) . ' (' . count($links) . ')</span>';
                                    echo '<p>' . esc_html($target_category->description) . '</p>';
                                    echo '</h2>';
                                    echo '<ul class="main-reveal">';
                                    
                                    foreach ($links as $link) {
                                        ?>
                                        <li class="text-reveal">
                                            <a class="on" href="<?php echo esc_url($link->link_url); ?>" target="_blank">
                                                <div class="icon">
                                                    <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                        <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                    <?php else : ?>
                                                        <!-- 默认图标，使用站点图标或文字首字母 -->
                                                        <div class="default-icon" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                                                            <?php echo esc_html(strtoupper(substr($link->link_name, 0, 1))); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="info">
                                                    <h3>
                                                        <span class="link-title"><?php echo esc_html($link->link_name); ?></span>
                                                        <span class="link-count"><?php echo esc_html(isset($link->link_clicked) ? $link->link_clicked : 0); ?></span>
                                                    </h3>
                                                    <?php if (!empty($link->link_description)) : ?>
                                                        <p title="<?php echo esc_attr($link->link_description); ?>"><?php echo esc_html($link->link_description); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="profile">
                                                    <div class="imgbox">
                                                        <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                            <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>该分类下暂无链接</p>';
                                }
                            } else {
                                echo '<p>未找到名为「' . esc_html($category_name) . '」的链接分类</p>';
                            }
                        } else {
                            // 📂 未指定分类名称，显示所有分类和链接（原有逻辑）
                            
                            // 记录所有已分类链接的ID
                            $categorized_link_ids = array();
                            
                            // 先显示分类中的链接
                            if ($link_cats) {
                                foreach ($link_cats as $cat) {
                                    // 获取每个分类下的链接
                                    $links = get_bookmarks(array(
                                        'category' => $cat->term_id,
                                        'orderby' => 'rating',
                                        'order' => 'DESC'
                                    ));
                                    
                                    if ($links) {
                                        echo '<h2 class="main-reveal">';
                                        echo '<span>' . esc_html($cat->name) . ' (' . count($links) . ')</span>';
                                        echo '<p>' . esc_html($cat->description) . '</p>';
                                        echo '</h2>';
                                        echo '<ul class="main-reveal">';
                                        
                                        foreach ($links as $link) {
                                            // 记录已分类链接的ID
                                            $categorized_link_ids[] = $link->link_id;
                                            ?>
                                            <li class="text-reveal">
                                                <a class="on" href="<?php echo esc_url($link->link_url); ?>" target="_blank">
                                                    <div class="icon">
                                                        <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                            <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                        <?php else : ?>
                                                            <!-- 默认图标，使用站点图标或文字首字母 -->
                                                            <div class="default-icon" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                                                                <?php echo esc_html(strtoupper(substr($link->link_name, 0, 1))); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="info">
                                                        <h3>
                                                            <span class="link-title"><?php echo esc_html($link->link_name); ?></span>
                                                            <span class="link-count"><?php echo esc_html(isset($link->link_clicked) ? $link->link_clicked : 0); ?></span>
                                                        </h3>
                                                        <?php if (!empty($link->link_description)) : ?>
                                                            <p title="<?php echo esc_attr($link->link_description); ?>"><?php echo esc_html($link->link_description); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="profile">
                                                        <div class="imgbox">
                                                            <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                                <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        echo '</ul>';
                                    }
                                }
                            }
                            
                            // 获取真正未分类的链接（不在任何分类中的链接）
                            $all_links = get_bookmarks(array(
                                'orderby' => 'rating',
                                'order' => 'DESC'
                            ));
                            
                            $truly_uncategorized = array();
                            foreach ($all_links as $link) {
                                // 如果链接ID不在已分类链接ID数组中，则添加到真正未分类数组
                                if (!in_array($link->link_id, $categorized_link_ids)) {
                                    $truly_uncategorized[] = $link;
                                }
                            }
                            
                            // 显示真正未分类的链接
                            if ($truly_uncategorized) {
                                echo '<h2 class="main-reveal">';
                                echo '<span>未分类 (' . count($truly_uncategorized) . ')</span>';
                                echo '<p>未归类的友情链接</p>';
                                echo '</h2>';
                                echo '<ul class="main-reveal">';
                                
                                foreach ($truly_uncategorized as $link) {
                                    ?>
                                    <li class="text-reveal">
                                        <a class="on" href="<?php echo esc_url($link->link_url); ?>" target="_blank">
                                            <div class="icon">
                                                <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                    <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                <?php else : ?>
                                                    <!-- 默认图标，使用站点图标或文字首字母 -->
                                                    <div class="default-icon" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                                                        <?php echo esc_html(strtoupper(substr($link->link_name, 0, 1))); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="info">
                                                <h3>
                                                    <span class="link-title"><?php echo esc_html($link->link_name); ?></span>
                                                    <span class="link-count"><?php echo esc_html(isset($link->link_clicked) ? $link->link_clicked : 0); ?></span>
                                                </h3>
                                                <?php if (!empty($link->link_description)) : ?>
                                                    <p title="<?php echo esc_attr($link->link_description); ?>"><?php echo esc_html($link->link_description); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="profile">
                                                <div class="imgbox">
                                                    <?php if (!empty($link->link_image) && filter_var($link->link_image, FILTER_VALIDATE_URL)) : ?>
                                                        <img src="<?php echo esc_url($link->link_image); ?>" alt="<?php echo esc_attr($link->link_name); ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <?php
                                }
                                echo '</ul>';
                            }
                            
                            // 如果没有任何链接，显示提示信息
                            if (empty($all_links)) {
                                echo '<p>暂无链接</p>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if (comments_open()) : ?>
                    <?php comments_template('', true); ?>
                <?php endif; ?>
            </div>
        </div>
<?php
get_footer();
?>


<?php
get_footer();
