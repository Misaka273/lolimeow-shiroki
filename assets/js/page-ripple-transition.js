/**
 * 🎨 页面点击处渐显放大过渡动画组件
 * 💫 从点击位置圆形扩散放大，加载完成后渐隐退出
/* 🟠 横向排列的圆点动画，参考来源 https://juejin.cn/post/7020064738956705823 的弹珠动画
 */

(function() {
  'use strict';

  /* 🔗 页面涟漪过渡动画类 */
  class PageRippleTransition {
    constructor(options = {}) {
      /* ⚙️ 默认配置选项 */
      this.options = {
        duration: options.duration || 800,            /* ◀️ 动画持续时间（毫秒） */
        showTargetUrl: options.showTargetUrl !== false, /* ◀️ 是否显示目标URL */
        onStart: options.onStart || null,             /* ◀️ 开始时的回调函数 */
        onComplete: options.onComplete || null        /* ◀️ 完成时的回调函数 */
      };

      /* 📦 初始化状态 */
      this.isAnimating = false;
      this.container = null;
      this.ripple = null;
      this.content = null;
      this.navigateTimer = null;
      this.clickX = 0;  /* ◀️ 点击位置X坐标 */
      this.clickY = 0;  /* ◀️ 点击位置Y坐标 */

      /* 🚀 初始化组件 */
      this.init();
    }

    /* 🔧 初始化方法 */
    init() {
      this.createElements();
      this.bindEvents();
      this.interceptLinks();
    }

    /* 🎨 创建动画DOM结构 */
    createElements() {
      /* 检查是否已存在 */
      if (document.getElementById('page-ripple-transition')) {
        this.container = document.getElementById('page-ripple-transition');
        this.overlay = this.container.querySelector('.page-ripple-transition__overlay');
        this.ripple = this.container.querySelector('.page-ripple-transition__ripple');
        this.content = this.container.querySelector('.page-ripple-transition__content');
        this.targetUrl = this.container.querySelector('.page-ripple-transition__target');
        this.targetUrlText = this.container.querySelector('.page-ripple-transition__target-url');
        this.percentageEl = this.container.querySelector('.page-ripple-transition__percentage');
        return;
      }
    }

    /* 🎯 绑定事件 */
    bindEvents() {
      /* 页面加载完成事件 */
      window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
          /* 从缓存恢复时重置动画 */
          this.reset();
        }
      });

      /* 页面可见性变化 */
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && this.isAnimating) {
          /* 页面重新可见时显示成功状态 */
          this.showSuccess();
        }
      });
    }

    /* 🔗 拦截页面链接 */
    interceptLinks() {
      const setupLinkInterceptor = () => {
        document.addEventListener('click', (e) => {
          const link = e.target.closest('a');

          if (!link) return;

          /* 获取链接属性 */
          const href = link.getAttribute('href');
          const target = link.getAttribute('target');
          const isExternal = link.hostname !== window.location.hostname;
          const isDownload = link.hasAttribute('download');
          const isAnchor = href && href.startsWith('#');
          const isJavascript = href && href.startsWith('javascript:');
          const isMailto = href && href.startsWith('mailto:');
          const isTel = href && href.startsWith('tel:');

          /* 排除不需要拦截的链接 */
          if (!href ||
              href === '#' ||
              isAnchor ||
              isJavascript ||
              isMailto ||
              isTel ||
              isDownload ||
              target === '_blank' ||
              e.ctrlKey ||
              e.metaKey ||
              e.shiftKey ||
              e.button !== 0) {
            return;
          }

          /* 阻止默认跳转 */
          e.preventDefault();

          /* 记录点击位置 */
          this.clickX = e.clientX;
          this.clickY = e.clientY;

          /* 开始涟漪动画 */
          this.start(href, isExternal);
        });
      };

      /* 如果DOM已加载则立即执行，否则等待DOMContentLoaded */
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLinkInterceptor);
      } else {
        setupLinkInterceptor();
      }
    }

    /* 🚀 开始涟漪动画 */
    start(url, isExternal = false) {
      if (this.isAnimating) return;

      this.isAnimating = true;

      /* 计算涟漪层的位置和大小 */
      this.setRipplePosition();

      /* 设置目标URL显示 - 显示完整URL */
      if (this.options.showTargetUrl && this.targetUrl && this.targetUrlText) {
        /* 如果是相对路径，添加当前域名 */
        let fullUrl = url;
        if (url.startsWith('/')) {
          fullUrl = window.location.origin + url;
        } else if (!url.startsWith('http://') && !url.startsWith('https://')) {
          /* 处理相对路径如 ./ 或 ../ */
          const baseUrl = window.location.href.replace(/\/[^\/]*$/, '/');
          fullUrl = new URL(url, baseUrl).href;
        }
        this.targetUrlText.textContent = fullUrl;
        this.targetUrl.style.display = 'block';
      }

      /* 隐藏成功状态，显示加载状态 */
      if (this.successEl) {
        this.successEl.classList.remove('is-visible');
      }

      /* 重置进度 */
      this.startProgress();

      /* 禁止背景滚动 */
      document.body.style.overflow = 'hidden';

      /* 添加激活类触发动画 */
      this.container.classList.add('is-active');

      /* 执行开始回调 */
      if (typeof this.options.onStart === 'function') {
        this.options.onStart(url);
      }

      /* 延迟跳转 */
      const navigateDelay = Math.min(this.options.duration, 800);

      this.navigateTimer = setTimeout(() => {
        if (isExternal) {
          window.open(url, '_blank');
          this.showSuccess();
          setTimeout(() => this.exit(), 800);
        } else {
          /* 先显示成功状态 */
          this.showSuccess();

          /* 短暂显示成功状态后立即跳转 */
          setTimeout(() => {
            /* 先跳转页面 - 新页面会在后台加载 */
            window.location.href = url;

            /* 跳转后延迟收起涟漪 - 给用户视觉反馈 */
            setTimeout(() => {
              this.exit();
            }, 200); /* ◀️ 短暂延迟让用户看到成功状态 */
          }, 400); /* ◀️ 成功状态显示时间 */
        }
      }, navigateDelay);
    }

    /* 📊 开始进度动画 */
    startProgress() {
      if (!this.percentageEl) return;

      let progress = 0;
      const duration = this.options.duration || 800;
      const interval = 50; /* ◀️ 每50ms更新一次 */
      const increment = 100 / (duration / interval);

      /* 清除之前的定时器 */
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
      }

      /* 重置进度 */
      this.percentageEl.textContent = '0%';

      /* 启动进度动画 */
      this.progressInterval = setInterval(() => {
        progress += increment;

        if (progress >= 100) {
          progress = 100;
          clearInterval(this.progressInterval);
          this.progressInterval = null;
        }

        /* 更新百分比显示 */
        this.percentageEl.textContent = Math.floor(progress) + '%';
      }, interval);
    }

    /* 📍 设置涟漪层位置 */
    setRipplePosition() {
      if (!this.ripple) return;

      /* 获取视口尺寸 */
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      /* 计算从点击位置到屏幕四个角的距离，取最大值作为半径 */
      const distances = [
        Math.sqrt(Math.pow(this.clickX, 2) + Math.pow(this.clickY, 2)),
        Math.sqrt(Math.pow(viewportWidth - this.clickX, 2) + Math.pow(this.clickY, 2)),
        Math.sqrt(Math.pow(this.clickX, 2) + Math.pow(viewportHeight - this.clickY, 2)),
        Math.sqrt(Math.pow(viewportWidth - this.clickX, 2) + Math.pow(viewportHeight - this.clickY, 2))
      ];
      const maxRadius = Math.max(...distances);

      /* 设置涟漪层的大小（直径） */
      const size = maxRadius * 2;
      this.ripple.style.width = size + 'px';
      this.ripple.style.height = size + 'px';

      /* 设置涟漪层的位置（中心点在点击位置） */
      this.ripple.style.left = this.clickX + 'px';
      this.ripple.style.top = this.clickY + 'px';
    }

    /* 🎭 渐隐退出动画 */
    exit() {
      /* 添加退出类触发渐隐动画 */
      this.container.classList.add('is-exiting');
      this.container.classList.remove('is-active');
    }

    /* ✅ 显示成功状态 */
    showSuccess() {
      /* 停止进度动画并显示100% */
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
        this.progressInterval = null;
      }
      if (this.percentageEl) {
        this.percentageEl.textContent = '100%';
      }

      /* 短暂延迟后隐藏加载内容，然后跳转 */
      setTimeout(() => {
        /* 隐藏加载相关内容 */
        const loader = this.container.querySelector('.page-ripple-transition__loader');
        const target = this.container.querySelector('.page-ripple-transition__target');

        if (loader) loader.style.display = 'none';
        if (target) target.style.display = 'none';
      }, 200);

      /* 执行完成回调 */
      if (typeof this.options.onComplete === 'function') {
        this.options.onComplete();
      }
    }

    /* 🔄 重置状态 */
    reset() {
      this.isAnimating = false;

      /* 清除定时器 */
      if (this.navigateTimer) {
        clearTimeout(this.navigateTimer);
        this.navigateTimer = null;
      }

      /* 清除进度定时器 */
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
        this.progressInterval = null;
      }

      /* 移除所有状态类 */
      if (this.container) {
        this.container.classList.remove('is-active');
        this.container.classList.remove('is-exiting');
      }

      /* 恢复背景滚动 */
      document.body.style.overflow = '';

      /* 延迟重置内部状态 */
      setTimeout(() => {
        /* 恢复显示所有元素 */
        const loader = this.container?.querySelector('.page-ripple-transition__loader');
        const target = this.container?.querySelector('.page-ripple-transition__target');

        if (loader) loader.style.display = '';
        if (target) target.style.display = 'none';

        /* 重置进度显示 */
        if (this.percentageEl) {
          this.percentageEl.textContent = '0%';
        }
      }, 300);
    }

    /* 💥 销毁组件 */
    destroy() {
      this.reset();

      if (this.container && this.container.parentNode) {
        this.container.parentNode.removeChild(this.container);
      }

      this.container = null;
      this.overlay = null;
      this.ripple = null;
      this.content = null;
      this.targetUrl = null;
      this.targetUrlText = null;
    }
  }

  /* 🌍 暴露到全局 */
  window.PageRippleTransition = PageRippleTransition;

  /* 🚀 自动初始化 */
  let rippleInstance = null;

  const initRipple = () => {
    /* 检查是否已禁用 */
    if (window.__disablePageRippleTransition) {
      return;
    }

    /* 检查是否为减少动画偏好 */
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
      return;
    }

    /* 创建实例 */
    rippleInstance = new PageRippleTransition({
      duration: 600,
      showTargetUrl: true
    });

    /* 暴露实例到全局 */
    window.pageRippleTransition = rippleInstance;
  };

  /* 等待DOM加载完成 */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRipple);
  } else {
    initRipple();
  }

  /* 🎯 提供手动初始化方法 */
  window.initPageRippleTransition = function(options = {}) {
    if (rippleInstance) {
      rippleInstance.destroy();
    }
    rippleInstance = new PageRippleTransition(options);
    window.pageRippleTransition = rippleInstance;
    return rippleInstance;
  };

})();
