<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 * @author 专收爆米花
 * @author 白木 <https://gl.baimu.live/864> (二次创作)
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}?>
        </div>
        </section>
<footer class="mt-7">
    <hr class="horizontal dark">
    <!-- 🎪 底部栏小部件区域 -->
    <div class="footer-widgets py-5">
      <div class="container">
        <?php if (is_active_sidebar('widget_footer_widgets')) : ?>
          <div class="row footer-widgets-row">
            <?php dynamic_sidebar('widget_footer_widgets'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
      <div class="container pb-4">
        <div class="row align-items-center">
        <?php echo boxmoe_load_assets_footer(); ?>
      </div>
    </footer>
    <div class="body-background"></div>
    <div class="floating-action-menu">
      <nav class="floating-menu-items">
        <ul>
          <?php if(get_boxmoe('boxmoe_blog_layout')=='two'): ?>
          <li class="d-lg-none">
            <button class="float-btn" title="打开侧栏" data-bs-toggle="offcanvas" href="#blog-sidebar" aria-controls="blog-sidebar">
              <i class="fa fa-outdent"></i>
            </button>
          </li>
          <?php endif; ?>
          <?php if(get_boxmoe('boxmoe_lolijump_switch')): ?>
            <li>
            <a id="lolijump" href="#" title="返回顶部">
                <?php
                $lolijump_img = get_boxmoe('boxmoe_lolijump_img');
                $lolijump_src = '';
                if (strpos($lolijump_img, 'http') === 0 || strpos($lolijump_img, '//') === 0) {
                    $lolijump_src = $lolijump_img;
                } else {
                    $lolijump_src = boxmoe_theme_url() . '/assets/images/top/' . $lolijump_img . '.gif';
                }
                ?>
              <img src="<?php echo $lolijump_src; ?>" alt="返回顶部"></a>
            </li>
          <?php endif; ?>

          <!-- 🎬 视频播放器看板娘配置 -->
          <script>
            // 传递看板娘配置到前端
            window.boxmoe_lolijump_switch = '<?php echo get_boxmoe('boxmoe_lolijump_switch') ? 1 : 0; ?>';
            window.boxmoe_lolijump_img = '<?php echo get_boxmoe('boxmoe_lolijump_img'); ?>';
          </script>
        </ul>
      </nav>
    </div>
    <?php 
    ob_start();
    wp_footer();
    $wp_footer_output = ob_get_clean();
    echo preg_replace('/\n/', "\n    ", trim($wp_footer_output))."\n    ";
    ?>
    <?php echo get_boxmoe('boxmoe_diy_code_footer'); ?>
    <!-- 📋 文章目录容器 -->
    <div class="post-toc-container">
        <div class="post-toc-btn">
            <i class="fa fa-list"></i>
        </div>
        <div class="post-toc">
            <div class="toc-title">文章导读</div>
            <div class="toc-list"></div>
        </div>
    </div>
  </body>
</html>
<style>
.toast {
  transition: transform .3s ease-out !important;
    transform: translateX(100%);
}

.toast.fade.show {
    transform: translateX(0);
}
</style>
