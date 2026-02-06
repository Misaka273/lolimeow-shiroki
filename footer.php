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
        <!-- 📊 SVG 圆形阅读进度指示器 -->
        <div class="post-toc-btn">
            <svg class="toc-progress-svg" viewBox="0 0 100 100">
                <defs>
                    <!-- 🌈 渐变定义 -->
                    <linearGradient id="toc-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="toc-gradient-dark" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#8b5cf6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#a78bfa;stop-opacity:1" />
                    </linearGradient>
                    <filter id="toc-shadow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur in="SourceAlpha" stdDeviation="2"></feGaussianBlur>
                        <feOffset dx="0" dy="1" result="offsetblur"></feOffset>
                        <feComponentTransfer>
                            <feFuncA type="linear" slope="0.3"></feFuncA>
                        </feComponentTransfer>
                        <feMerge>
                            <feMergeNode></feMergeNode>
                            <feMergeNode in="SourceGraphic"></feMergeNode>
                        </feMerge>
                    </filter>
                </defs>
                <!-- ☀️ 背景圆环 -->
                <circle class="toc-progress-bg" cx="50" cy="50" r="44" filter="url(#toc-shadow)"></circle>
                <!-- 🌈 进度圆环 -->
                <circle class="toc-progress-bar" cx="50" cy="50" r="44" stroke="url(#toc-gradient)"></circle>
                <!-- 📖 中心图标 -->
                <foreignObject x="30" y="30" width="40" height="40">
                    <div xmlns="http://www.w3.org/1999/xhtml" class="toc-icon-wrapper">
                        <i class="fa fa-list"></i>
                    </div>
                </foreignObject>
            </svg>
            <!-- 📈 进度百分比 -->
            <span class="toc-progress-text">0%</span>
        </div>
        <!-- 🎯 轮盘展开式目录导读 -->
        <div class="toc-wheel-menu">
            <div class="toc-wheel-item" data-action="toc">
                <span class="wheel-item-text">目录</span>
            </div>
            <div class="toc-wheel-item" data-action="top">
                <span class="wheel-item-text">顶部</span>
            </div>
            <div class="toc-wheel-item" data-action="bottom">
                <span class="wheel-item-text">底部</span>
            </div>
        </div>
        <!-- 📋 文章目录面板 -->
        <div class="post-toc">
            <div class="toc-title">📖 文章导读</div>
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
