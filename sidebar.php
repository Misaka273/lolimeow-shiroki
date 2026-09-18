<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
?>
<?php if(get_boxmoe('boxmoe_blog_layout')=='two'): ?>
    <?php 
    $article_layout = get_boxmoe('boxmoe_article_layout_style');
    $sidebar_class = ($article_layout == 'three') ? 'col-lg-3' : 'col-lg-4';
    ?>
    <div class="<?php echo $sidebar_class; ?> blog-sidebar d-none d-lg-block">
          <div class="position-sticky top">
            <div class="blog-sidebar-inner">
              <?php shiroki_render_blog_sidebar_widgets(); ?>
            </div>
          </div>
        </div>
<?php endif; ?>
