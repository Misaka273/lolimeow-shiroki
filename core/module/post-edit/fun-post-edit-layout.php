<?php
/* 🕊️白木 原创开发 🔗gl.baimu.live */
/* 📝 写文章/编辑文章页面双栏布局 - PHP直接控制布局 */
/* 🎨 左侧编辑区 | 右侧元框 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🎯 文章编辑页面双栏布局管理类
 */
class Shiroki_Post_Edit_Layout {

    /**
     * 📦 单例实例
     */
    private static $instance = null;

    /**
     * 🚀 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 🔧 构造函数
     */
    private function __construct() {
        /* 🔍 只在写文章和编辑文章页面生效 */
        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php'))) {
            return;
        }

        /* 📝 添加布局样式 */
        add_action('admin_head', array($this, 'inject_layout_styles'));

        /* 📝 在页面开始处输出双栏布局容器 */
        add_action('edit_form_after_title', array($this, 'open_layout_wrapper'), 1);

        /* 📝 在页面结束处关闭布局容器 */
        add_action('edit_form_after_editor', array($this, 'close_layout_wrapper'), 99);
    }

    /**
     * 🎨 注入布局样式
     */
    public function inject_layout_styles() {
        echo '<style>';
        echo '/* 🕊️白木 原创开发 🔗gl.baimu.live */';
        echo '/* 📝 双栏布局样式 - PHP直接控制 */';

        /* 🎨 三栏布局主容器 */
        echo '.shiroki-editor-layout {';
        echo '  display: grid;';
        echo '  grid-template-columns: minmax(0, 320px) minmax(0, 1fr) minmax(0, 340px);';
        echo '  grid-template-areas: "toolbar main sidebar";';
        echo '  gap: var(--admin-space-lg);';
        echo '  width: 100%;';
        echo '  max-width: 100%;';
        echo '  margin: 0;';
        echo '  box-sizing: border-box;';
        echo '  position: relative;';
        echo '  min-height: calc(100vh - 100px);';
        echo '}';

        echo '.post-new-php #wpbody-content,';
        echo '.post-php #wpbody-content {';
        echo '  overflow-x: clip;';
        echo '  overflow-y: visible;';
        echo '}';

        echo '.post-new-php #post-body,';
        echo '.post-php #post-body,';
        echo '.post-new-php #post-body-content,';
        echo '.post-php #post-body-content,';
        echo '.post-new-php #post,';
        echo '.post-php #post {';
        echo '  overflow: visible;';
        echo '}';

        /* 📦 解除 WP 原生 columns-2 对编辑区的挤压 */
        echo '.post-new-php #post-body.columns-2 #post-body-content,';
        echo '.post-php #post-body.columns-2 #post-body-content {';
        echo '  margin-right: 0;';
        echo '  width: 100%;';
        echo '  float: none;';
        echo '}';

        echo '.post-new-php #post-body-content,';
        echo '.post-php #post-body-content,';
        echo '.post-new-php #poststuff,';
        echo '.post-php #poststuff,';
        echo '.post-new-php #postbox-container-2,';
        echo '.post-php #postbox-container-2 {';
        echo '  width: 100% !important;';
        echo '  max-width: 100% !important;';
        echo '  min-width: 0;';
        echo '  margin-right: 0 !important;';
        echo '  float: none !important;';
        echo '  box-sizing: border-box;';
        echo '}';
        echo 'body.shiroki-post-edit-layout-ready #postbox-container-1 {';
        echo '  display: none !important;';
        echo '}';
        
        /* 🧰 工具栏列占位 + 工具栏本体 */
        echo '.shiroki-editor-toolbar-column {';
        echo '  grid-area: toolbar;';
        echo '  align-self: start;';
        echo '  width: 100%;';
        echo '  max-width: 100%;';
        echo '  min-width: 0;';
        echo '}';

        echo '.shiroki-editor-toolbar {';
        echo '  width: 100%;';
        echo '  max-width: 100%;';
        echo '  min-width: 0;';
        echo '  height: fit-content;';
        echo '  max-height: calc(100vh - 80px);';
        echo '  background: var(--admin-current-glass);';
        echo '  backdrop-filter: var(--admin-glass-blur);';
        echo '  border: var(--admin-border-glass);';
        echo '  border-radius: var(--admin-radius-lg);';
        echo '  box-shadow: var(--admin-shadow-md);';
        echo '  overflow: visible;';
        echo '  z-index: 10;';
        echo '  box-sizing: border-box;';
        echo '}';

        echo '.shiroki-editor-toolbar.is-sticky-fixed {';
        echo '  position: fixed;';
        echo '  z-index: 100;';
        echo '}';
        
        /* 📝 中间编辑区域 */
        echo '.shiroki-editor-main {';
        echo '  grid-area: main;';
        echo '  display: flex;';
        echo '  flex-direction: column;';
        echo '  gap: var(--admin-space-lg);';
        echo '  min-width: 0;';
        echo '  width: 100%;';
        echo '  align-self: start;';
        echo '}';

        /* 📦 右侧元框区域 */
        echo '.shiroki-editor-sidebar {';
        echo '  grid-area: sidebar;';
        echo '  position: sticky;';
        echo '  top: 32px;';
        echo '  align-self: start;';
        echo '  height: calc(100vh - 80px);';
        echo '  overflow-y: auto;';
        echo '  overflow-x: hidden;';
        echo '  min-width: 0;';
        echo '  width: 100%;';
        echo '}';

        /* 📝 编辑区强制铺满，避免 field-sizing 把空内容压窄 */
        echo '.shiroki-editor-main .editor-inner,';
        echo '.shiroki-editor-main #postdivrich,';
        echo '.shiroki-editor-main #wp-content-wrap,';
        echo '.shiroki-editor-main #wp-content-editor-container {';
        echo '  width: 100% !important;';
        echo '  max-width: 100% !important;';
        echo '  min-width: 0 !important;';
        echo '  box-sizing: border-box !important;';
        echo '}';
        echo '.post-new-php .wp-editor-area,';
        echo '.post-php .wp-editor-area,';
        echo '.post-new-php textarea#content,';
        echo '.post-php textarea#content {';
        echo '  width: 100% !important;';
        echo '  max-width: 100% !important;';
        echo '  min-width: 0 !important;';
        echo '  box-sizing: border-box !important;';
        echo '}';

        /* 📦 侧栏 #side-sortables 圆角容器 - 强制覆盖（完整规则见 post-edit.css 末尾） */
        echo '.shiroki-editor-sidebar #side-sortables {';
        echo '  display: flex;';
        echo '  flex-direction: column;';
        echo '  gap: 0;';
        echo '  background: var(--admin-current-glass);';
        echo '  backdrop-filter: var(--admin-glass-blur);';
        echo '  border: var(--admin-border-glass);';
        echo '  border-radius: var(--admin-radius-lg);';
        echo '  box-shadow: var(--admin-shadow-md);';
        echo '  overflow: hidden;';
        echo '  overflow-x: clip;';
        echo '  overflow-y: clip;';
        echo '  box-sizing: border-box;';
        echo '  isolation: isolate;';
        echo '  contain: paint;';
        echo '  clip-path: inset(0 round var(--admin-radius-lg));';
        echo '  -webkit-clip-path: inset(0 round var(--admin-radius-lg));';
        echo '}';

        echo '.post-new-php .shiroki-editor-sidebar #side-sortables > .postbox,';
        echo '.post-php .shiroki-editor-sidebar #side-sortables > .postbox {';
        echo '  background: transparent !important;';
        echo '  border: none !important;';
        echo '  border-radius: 0 !important;';
        echo '  box-shadow: none !important;';
        echo '  overflow: hidden !important;';
        echo '  margin-bottom: 0 !important;';
        echo '  max-width: 100% !important;';
        echo '  box-sizing: border-box !important;';
        echo '}';

        echo '.post-new-php .shiroki-editor-sidebar #side-sortables .postbox .inside,';
        echo '.post-php .shiroki-editor-sidebar #side-sortables .postbox .inside {';
        echo '  background: transparent !important;';
        echo '  border-radius: 0 !important;';
        echo '  overflow: hidden !important;';
        echo '  max-width: 100% !important;';
        echo '  box-sizing: border-box !important;';
        echo '  padding: var(--admin-space-lg);';
        echo '}';

        echo '.post-new-php .shiroki-editor-sidebar #side-sortables .postbox .postbox-header,';
        echo '.post-php .shiroki-editor-sidebar #side-sortables .postbox .postbox-header,';
        echo '.post-new-php .shiroki-editor-sidebar #side-sortables .postbox > .hndle,';
        echo '.post-php .shiroki-editor-sidebar #side-sortables .postbox > .hndle {';
        echo '  background: transparent !important;';
        echo '  border-radius: 0 !important;';
        echo '  border-bottom: 1px solid var(--admin-current-border);';
        echo '  color: var(--admin-current-text-primary);';
        echo '  font-weight: var(--admin-font-weight-semibold);';
        echo '  padding: var(--admin-space-md) var(--admin-space-lg);';
        echo '}';

        echo '.shiroki-editor-sidebar .postbox {';
        echo '  margin-bottom: 0;';
        echo '}';

        /* 📦 侧边栏滚动条美化 */
        echo '.shiroki-editor-sidebar::-webkit-scrollbar {';
        echo '  width: 4px;';
        echo '}';
        echo '.shiroki-editor-sidebar::-webkit-scrollbar-thumb {';
        echo '  background: var(--admin-current-border);';
        echo '  border-radius: var(--admin-radius-full);';
        echo '}';

        /* 🧰 工具栏内部样式 */
        echo '.shiroki-editor-toolbar .toolbar-inner {';
        echo '  padding: var(--admin-space-md);';
        echo '  max-height: calc(100vh - 80px);';
        echo '  overflow-x: hidden;';
        echo '  overflow-y: auto;';
        echo '}';

        echo '.shiroki-editor-toolbar .toolbar-section {';
        echo '  margin-bottom: var(--admin-space-md);';
        echo '  padding-bottom: var(--admin-space-md);';
        echo '  border-bottom: 1px solid var(--admin-current-border);';
        echo '}';

        echo '.shiroki-editor-toolbar .toolbar-section:last-child {';
        echo '  margin-bottom: 0;';
        echo '  padding-bottom: 0;';
        echo '  border-bottom: none;';
        echo '}';

        echo '.shiroki-editor-toolbar .toolbar-title {';
        echo '  font-size: var(--admin-font-size-sm);';
        echo '  font-weight: var(--admin-font-weight-semibold);';
        echo '  color: var(--admin-current-text-secondary);';
        echo '  margin: 0 0 var(--admin-space-sm) 0;';
        echo '  padding: 0;';
        echo '}';

        echo '.shiroki-editor-toolbar .toolbar-content {';
        echo '  display: flex;';
        echo '  flex-direction: column;';
        echo '  gap: var(--admin-space-xs);';
        echo '}';

        /* 🧰 工具栏元素样式 */
        echo '#toolbar-media-content #wp-content-media-buttons {';
        echo '  background: transparent;';
        echo '  border: none;';
        echo '  padding: 0;';
        echo '  margin: 0;';
        echo '  display: flex;';
        echo '  flex-direction: column;';
        echo '  gap: var(--admin-space-xs);';
        echo '}';

        echo '#toolbar-media-content .insert-media {';
        echo '  background: var(--admin-primary-bg);';
        echo '  border: 1px solid var(--admin-primary-border);';
        echo '  border-radius: var(--admin-radius-md);';
        echo '  color: var(--admin-primary-text);';
        echo '  padding: var(--admin-space-sm) var(--admin-space-md);';
        echo '  font-size: var(--admin-font-size-sm);';
        echo '  width: 100%;';
        echo '  text-align: center;';
        echo '  transition: all var(--admin-transition-fast);';
        echo '}';

        echo '#toolbar-media-content .insert-media:hover {';
        echo '  background: var(--admin-primary-bg-hover);';
        echo '}';

        echo '#toolbar-shortcodes-content #short_code_select {';
        echo '  width: 100%;';
        echo '  background: var(--admin-current-bg);';
        echo '  border: var(--admin-border-glass);';
        echo '  border-radius: var(--admin-radius-md);';
        echo '  padding: var(--admin-space-sm);';
        echo '  font-size: var(--admin-font-size-sm);';
        echo '  color: var(--admin-current-text-primary);';
        echo '}';

        echo '#toolbar-md-content .boxmoe-md-toolbar {';
        echo '  background: transparent;';
        echo '  border: none;';
        echo '  padding: 0;';
        echo '  margin: 0;';
        echo '  display: flex;';
        echo '  flex-wrap: wrap;';
        echo '  gap: var(--admin-space-xs);';
        echo '}';

        echo '#toolbar-md-content .md-btn {';
        echo '  background: var(--admin-current-bg);';
        echo '  border: var(--admin-border-glass);';
        echo '  border-radius: var(--admin-radius-sm);';
        echo '  padding: var(--admin-space-xs) var(--admin-space-sm);';
        echo '  font-size: var(--admin-font-size-xs);';
        echo '  color: var(--admin-current-text-primary);';
        echo '  cursor: pointer;';
        echo '  transition: all var(--admin-transition-fast);';
        echo '}';

        echo '#toolbar-md-content .md-btn:hover {';
        echo '  background: var(--admin-primary-bg);';
        echo '  border-color: var(--admin-primary-border);';
        echo '  color: var(--admin-primary-text);';
        echo '}';

        echo '#toolbar-md-content .md-separator {';
        echo '  color: var(--admin-current-text-muted);';
        echo '  margin: 0 var(--admin-space-xs);';
        echo '}';

        echo '#toolbar-quicktags-content #ed_toolbar {';
        echo '  background: transparent;';
        echo '  border: none;';
        echo '  padding: 0;';
        echo '  margin: 0;';
        echo '  display: flex;';
        echo '  flex-wrap: wrap;';
        echo '  gap: var(--admin-space-xs);';
        echo '  position: relative !important;';
        echo '  top: auto !important;';
        echo '  width: 100% !important;';
        echo '}';

        echo '#toolbar-quicktags-content .ed_button {';
        echo '  background: var(--admin-current-bg);';
        echo '  border: var(--admin-border-glass);';
        echo '  border-radius: var(--admin-radius-sm);';
        echo '  padding: var(--admin-space-xs) var(--admin-space-sm);';
        echo '  font-size: var(--admin-font-size-xs);';
        echo '  color: var(--admin-current-text-primary);';
        echo '  cursor: pointer;';
        echo '  transition: all var(--admin-transition-fast);';
        echo '  margin: 0;';
        echo '  height: auto;';
        echo '  line-height: 1.4;';
        echo '}';

        echo '#toolbar-quicktags-content .ed_button:hover {';
        echo '  background: var(--admin-primary-bg);';
        echo '  border-color: var(--admin-primary-border);';
        echo '  color: var(--admin-primary-text);';
        echo '}';

        echo '#toolbar-quicktags-content .qt-dfw {';
        echo '  display: none;';
        echo '}';

        /* 📱 响应式设计 */
        echo '@media screen and (max-width: 1400px) {';
        echo '  .shiroki-editor-layout {';
        echo '    grid-template-columns: minmax(0, 1fr) minmax(0, 300px);';
        echo '    grid-template-areas: "main sidebar";';
        echo '    gap: var(--admin-space-lg);';
        echo '  }';
        echo '  .shiroki-editor-toolbar-column,';
        echo '  .shiroki-editor-toolbar {';
        echo '    display: none;';
        echo '  }';
        echo '  .shiroki-editor-main {';
        echo '    grid-area: main;';
        echo '  }';
        echo '}';

        echo '@media screen and (max-width: 1024px) {';
        echo '  .shiroki-editor-layout {';
        echo '    grid-template-columns: 1fr;';
        echo '    grid-template-areas: "main" "sidebar";';
        echo '    gap: var(--admin-space-lg);';
        echo '  }';
        echo '  .shiroki-editor-sidebar {';
        echo '    position: relative;';
        echo '    top: auto;';
        echo '    height: auto;';
        echo '  }';
        echo '  .post-new-php .wp-editor-area,';
        echo '  .post-php .wp-editor-area,';
        echo '  .post-new-php textarea#content,';
        echo '  .post-php textarea#content {';
        echo '    width: 100% !important;';
        echo '    max-width: 100% !important;';
        echo '    field-sizing: fixed !important;';
        echo '  }';
        echo '}';

        echo '@media screen and (max-width: 782px) {';
        echo '  .post-new-php #wpcontent,';
        echo '  .post-php #wpcontent,';
        echo '  .post-new-php #wpbody,';
        echo '  .post-php #wpbody,';
        echo '  .post-new-php #wpbody-content,';
        echo '  .post-php #wpbody-content,';
        echo '  .post-new-php #post,';
        echo '  .post-php #post,';
        echo '  .post-new-php #poststuff,';
        echo '  .post-php #poststuff,';
        echo '  .post-new-php #post-body,';
        echo '  .post-php #post-body,';
        echo '  .post-new-php #post-body-content,';
        echo '  .post-php #post-body-content,';
        echo '  .post-new-php #postbox-container-2,';
        echo '  .post-php #postbox-container-2 {';
        echo '    width: 100% !important;';
        echo '    max-width: 100% !important;';
        echo '    min-width: 0 !important;';
        echo '    margin-left: 0 !important;';
        echo '    margin-right: 0 !important;';
        echo '    float: none !important;';
        echo '    box-sizing: border-box !important;';
        echo '  }';
        echo '  .shiroki-editor-layout {';
        echo '    grid-template-columns: 1fr;';
        echo '    grid-template-areas: "main" "sidebar";';
        echo '    gap: var(--admin-space-lg);';
        echo '  }';
        echo '  .shiroki-editor-sidebar {';
        echo '    position: relative;';
        echo '    top: auto;';
        echo '    height: auto;';
        echo '    width: 100% !important;';
        echo '    max-width: 100% !important;';
        echo '    min-width: 0 !important;';
        echo '    margin-inline: 0;';
        echo '    justify-self: stretch;';
        echo '  }';
        echo '}';

        echo 'body.mobile.shiroki-post-edit-layout-ready.post-php .shiroki-editor-layout,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-new-php .shiroki-editor-layout {';
        echo '  grid-template-columns: minmax(0, 1fr) !important;';
        echo '  grid-template-areas: "main" "sidebar" !important;';
        echo '  width: 100% !important;';
        echo '  max-width: 100% !important;';
        echo '}';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-php .shiroki-editor-toolbar-column,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-php .shiroki-editor-toolbar,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-new-php .shiroki-editor-toolbar-column,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-new-php .shiroki-editor-toolbar {';
        echo '  display: none !important;';
        echo '}';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-php .shiroki-editor-main,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-php .shiroki-editor-sidebar,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-new-php .shiroki-editor-main,';
        echo 'body.mobile.shiroki-post-edit-layout-ready.post-new-php .shiroki-editor-sidebar {';
        echo '  width: 100% !important;';
        echo '  max-width: 100% !important;';
        echo '  min-width: 0 !important;';
        echo '  margin-inline: 0 !important;';
        echo '}';

        echo '</style>';

        /* ⚡ 尽早把侧栏元框迁入三栏，减少布局闪烁 */
        echo '<script>';
        echo '(function(){';
        echo '  function moveSide(){';
        echo '    var sidebar=document.getElementById("shiroki-editor-sidebar");';
        echo '    var side=document.getElementById("side-sortables");';
        echo '    if(!sidebar||!side) return false;';
        echo '    if(!sidebar.contains(side)){ sidebar.appendChild(side); }';
        echo '    var pb=document.getElementById("post-body");';
        echo '    if(pb){ pb.classList.remove("columns-2"); pb.style.width="100%"; pb.style.marginRight="0"; }';
        echo '    var c1=document.getElementById("postbox-container-1");';
        echo '    if(c1){ c1.style.display="none"; }';
        echo '    var pbc=document.getElementById("post-body-content");';
        echo '    if(pbc){ pbc.style.marginRight="0"; pbc.style.width="100%"; pbc.style.maxWidth="100%"; pbc.style.float="none"; }';
        echo '    if(sidebar && (window.innerWidth<=782 || sidebar.parentElement.getBoundingClientRect().width<=782)){';
        echo '      var layout=document.getElementById("shiroki-editor-layout");';
        echo '      if(layout){';
        echo '        layout.style.setProperty("grid-template-columns","minmax(0, 1fr)","important");';
        echo '        layout.style.setProperty("grid-template-areas","\"main\" \"sidebar\"","important");';
        echo '        layout.style.setProperty("width","100%","important");';
        echo '        layout.style.setProperty("max-width","100%","important");';
        echo '        var toolbarColumn=layout.querySelector(".shiroki-editor-toolbar-column");';
        echo '        if(toolbarColumn){ toolbarColumn.style.setProperty("display","none","important"); }';
        echo '      }';
        echo '      sidebar.style.setProperty("width","100%","important");';
        echo '      sidebar.style.setProperty("max-width","100%","important");';
        echo '      sidebar.style.setProperty("position","relative","important");';
        echo '      sidebar.style.setProperty("height","auto","important");';
        echo '    }';
        echo '    document.body.classList.add("shiroki-post-edit-layout-ready");';
        echo '    return true;';
        echo '  }';
        echo '  if(!moveSide()){';
        echo '    document.addEventListener("DOMContentLoaded", moveSide);';
        echo '  }';
        echo '})();';
        echo '</script>';
    }

    /**
     * 📦 打开布局包装器
     */
    public function open_layout_wrapper() {
        /* 🔗 输出三栏布局容器 */
        echo '<div id="shiroki-editor-layout" class="shiroki-editor-layout">';

        /* 🧰 工具栏区域 - 独立的第一栏 */
        echo '  <div class="shiroki-editor-toolbar-column">';
        echo '  <div id="shiroki-editor-toolbar" class="shiroki-editor-toolbar">';
        echo '    <div class="toolbar-inner">';
        echo '      <div class="toolbar-section toolbar-media">';
        echo '        <h4 class="toolbar-title">📎 媒体</h4>';
        echo '        <div class="toolbar-content" id="toolbar-media-content"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-shortcodes">';
        echo '        <h4 class="toolbar-title">⚡ 短代码</h4>';
        echo '        <div class="toolbar-content" id="toolbar-shortcodes-content"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-md">';
        echo '        <h4 class="toolbar-title">📝 MD工具</h4>';
        echo '        <div class="toolbar-content" id="toolbar-md-content"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-quicktags">';
        echo '        <h4 class="toolbar-title">🔧 Quicktags</h4>';
        echo '        <div class="toolbar-content" id="toolbar-quicktags-content"></div>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div><!-- /#shiroki-editor-toolbar -->';
        echo '  </div><!-- /.shiroki-editor-toolbar-column -->';

        /* 📝 中间编辑区域开始 */
        echo '  <div id="shiroki-editor-main" class="shiroki-editor-main">';
        echo '    <div class="editor-inner">';
        /* 这里会包含标题和编辑器，由WordPress默认输出 */
    }

    /**
     * 📦 关闭布局包装器
     */
    public function close_layout_wrapper() {
        /* 📝 关闭中间编辑区域 */
        echo '    </div><!-- /.editor-inner -->';
        echo '  </div><!-- /#shiroki-editor-main -->';

        /* 📦 右侧元框区域 */
        echo '  <div id="shiroki-editor-sidebar" class="shiroki-editor-sidebar">';
        echo '    <!-- 元框将由JS移动到这里 -->';
        echo '  </div><!-- /#shiroki-editor-sidebar -->';

        /* 🔗 关闭双栏布局容器 */
        echo '</div><!-- /#shiroki-editor-layout -->';

        /* 📱 移动端工具栏浮动按钮 */
        echo '<button type="button" id="shiroki-toolbar-float-btn" class="shiroki-toolbar-float-btn">';
        echo '  <span>🧰</span> 语法/附件';
        echo '</button>';

        /* 📱 移动端工具栏模态框 */
        echo '<div id="shiroki-toolbar-mobile-modal" class="shiroki-toolbar-mobile-modal" style="display: none;">';
        echo '  <div class="shiroki-toolbar-mobile-modal-backdrop"></div>';
        echo '  <div class="shiroki-toolbar-mobile-modal-content">';
        echo '    <div class="shiroki-toolbar-mobile-modal-header">';
        echo '      <span class="shiroki-toolbar-mobile-modal-title">🧰 添加语法/添加附件媒体</span>';
        echo '      <button type="button" class="shiroki-toolbar-mobile-modal-close">✕</button>';
        echo '    </div>';
        echo '    <div class="shiroki-toolbar-mobile-modal-body">';
        echo '      <div class="toolbar-section toolbar-media">';
        echo '        <h4 class="toolbar-title">🗃️ 媒体</h4>';
        echo '        <div class="toolbar-content" id="toolbar-media-content-mobile"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-shortcodes">';
        echo '        <h4 class="toolbar-title">⚡ 短代码</h4>';
        echo '        <div class="toolbar-content" id="toolbar-shortcodes-content-mobile"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-md">';
        echo '        <h4 class="toolbar-title">📝 MD工具</h4>';
        echo '        <div class="toolbar-content" id="toolbar-md-content-mobile"></div>';
        echo '      </div>';
        echo '      <div class="toolbar-section toolbar-quicktags">';
        echo '        <h4 class="toolbar-title">🔧 Quicktags</h4>';
        echo '        <div class="toolbar-content" id="toolbar-quicktags-content-mobile"></div>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        /* 📱 移动端工具栏交互脚本 */
        echo '<script>';
        echo '(function() {';
        echo '  var floatBtn = document.getElementById("shiroki-toolbar-float-btn");';
        echo '  var modal = document.getElementById("shiroki-toolbar-mobile-modal");';
        echo '  var closeBtn = modal.querySelector(".shiroki-toolbar-mobile-modal-close");';
        echo '  var backdrop = modal.querySelector(".shiroki-toolbar-mobile-modal-backdrop");';
        echo '  var modalBody = modal.querySelector(".shiroki-toolbar-mobile-modal-body");';
        echo '  var isModalOpen = false;';
        echo '  var isContentCloned = false;';

        echo '  /* ◀️ 克隆工具栏内容到移动端模态框 */';
        echo '  function cloneToolbarContent() {';
        echo '    if (isContentCloned) return;';
        echo '    var mediaContent = document.getElementById("toolbar-media-content");';
        echo '    var shortcodesContent = document.getElementById("toolbar-shortcodes-content");';
        echo '    var mdContent = document.getElementById("toolbar-md-content");';
        echo '    var quicktagsContent = document.getElementById("toolbar-quicktags-content");';
        echo '    var mediaMobile = document.getElementById("toolbar-media-content-mobile");';
        echo '    var shortcodesMobile = document.getElementById("toolbar-shortcodes-content-mobile");';
        echo '    var mdMobile = document.getElementById("toolbar-md-content-mobile");';
        echo '    var quicktagsMobile = document.getElementById("toolbar-quicktags-content-mobile");';

        echo '    if (mediaContent && mediaMobile) {';
        echo '      mediaMobile.innerHTML = "";';
        echo '      mediaMobile.appendChild(mediaContent.cloneNode(true));';
        echo '    }';
        echo '    if (shortcodesContent && shortcodesMobile) {';
        echo '      shortcodesMobile.innerHTML = "";';
        echo '      shortcodesMobile.appendChild(shortcodesContent.cloneNode(true));';
        echo '    }';
        echo '    if (mdContent && mdMobile) {';
        echo '      mdMobile.innerHTML = "";';
        echo '      mdMobile.appendChild(mdContent.cloneNode(true));';
        echo '    }';
        echo '    if (quicktagsContent && quicktagsMobile) {';
        echo '      quicktagsMobile.innerHTML = "";';
        echo '      quicktagsMobile.appendChild(quicktagsContent.cloneNode(true));';
        echo '    }';
        echo '    isContentCloned = true;';
        echo '  }';

        echo '  /* ◀️ 打开模态框 */';
        echo '  function openModal() {';
        echo '    cloneToolbarContent();';
        echo '    modal.style.display = "flex";';
        echo '    setTimeout(function() { modal.classList.add("active"); }, 10);';
        echo '    floatBtn.classList.add("active");';
        echo '    floatBtn.innerHTML = "<span>✕</span> 关闭";';
        echo '    isModalOpen = true;';
        echo '  }';

        echo '  /* ◀️ 关闭模态框 */';
        echo '  function closeModal() {';
        echo '    modal.classList.remove("active");';
        echo '    setTimeout(function() { ';
        echo '      modal.style.display = "none";';
        echo '    }, 300);';
        echo '    floatBtn.classList.remove("active");';
        echo '    floatBtn.innerHTML = "<span>🧰</span> 工具箱";';
        echo '    isModalOpen = false;';
        echo '  }';

        echo '  /* ◀️ 绑定浮动按钮事件 */';
        echo '  if (floatBtn) {';
        echo '    floatBtn.addEventListener("click", function() {';
        echo '      if (isModalOpen) {';
        echo '        closeModal();';
        echo '      } else {';
        echo '        openModal();';
        echo '      }';
        echo '    });';
        echo '  }';

        echo '  if (closeBtn) closeBtn.addEventListener("click", closeModal);';
        echo '  if (backdrop) backdrop.addEventListener("click", closeModal);';

        echo '  /* ◀️ 使用事件委托处理模态框内的按钮点击 */';
        echo '  if (modalBody) {';
        echo '    modalBody.addEventListener("click", function(e) {';
        echo '      var target = e.target;';
        
        echo '      /* 🔍 查找被点击的按钮 */';
        echo '      var btn = target.closest("button, input[type=button], .shiroki-shortcode-btn, .md-btn, .ed_button");';
        echo '      if (!btn) return;';
        
        echo '      /* 🎯 获取按钮信息 */';
        echo '      var btnId = btn.id;';
        echo '      var btnClass = btn.className;';
        echo '      var btnCode = btn.getAttribute("data-code");';
        echo '      var btnValue = btn.value;';
        
        echo '      /* 📱 短代码按钮处理 */';
        echo '      if (btn.classList.contains("shiroki-shortcode-btn")) {';
        echo '        if (btnCode && typeof send_to_editor === "function") {';
        echo '          send_to_editor(btnCode);';
        echo '        }';
        echo '      }';
        
        echo '      /* 📝 MD工具按钮处理 - 通过class匹配 */';
        echo '      else if (btn.classList.contains("md-btn")) {';
        echo '        var originalMdToolbar = document.querySelector(".boxmoe-md-toolbar");';
        echo '        if (originalMdToolbar) {';
        echo '          var classList = btn.className.split(" ");';
        echo '          var selector = classList.map(function(c) { return "." + c; }).join("");';
        echo '          var originalBtn = originalMdToolbar.querySelector(selector);';
        echo '          if (originalBtn) {';
        echo '            originalBtn.click();';
        echo '          }';
        echo '        }';
        echo '      }';
        
        echo '      /* 🔧 Quicktags按钮处理 - 通过ID匹配 */';
        echo '      else if (btn.classList.contains("ed_button") && btnId) {';
        echo '        var originalBtn = document.getElementById(btnId);';
        echo '        if (originalBtn) {';
        echo '          originalBtn.click();';
        echo '        }';
        echo '      }';
        
        echo '      /* 📎 添加媒体按钮处理 */';
        echo '      else if (btnId === "shiroki-insert-media-button" || btn.classList.contains("shiroki-media-trigger")) {';
        echo '        var originalBtn = document.getElementById("shiroki-insert-media-button");';
        echo '        if (originalBtn) {';
        echo '          originalBtn.click();';
        echo '        }';
        echo '      }';
        
        echo '      /* 🎬 延迟关闭窗口，让操作先执行 */';
        echo '      setTimeout(function() {';
        echo '        closeModal();';
        echo '      }, 150);';
        echo '    });';
        echo '  }';
        echo '})();';
        echo '</script>';
    }
}

/* 🚀 初始化 */
Shiroki_Post_Edit_Layout::get_instance();
