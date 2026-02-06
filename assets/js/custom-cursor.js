/**
 * 🖱️ 自定义鼠标光标控制脚本
 * 白木 🔗gl.baimu.live 开发
 */

(function() {
  'use strict';

  // 🎯 配置对象 - 将由 PHP 注入
  const cursorConfig = window.shirokiCursorConfig || {
    arrow: '',
    handwriting: '',
    ibeam: '',
    appstarting: ''
  };

  // 🚀 初始化自定义光标
  function initCustomCursor() {
    const body = document.body;
    
    // ◀️ 添加启用标记类名
    body.classList.add('custom-cursor-enabled');
    
    // 🎨 设置 CSS 变量
    setCursorCSSVariables();
    
    // ⏳ 监听页面加载状态
    monitorLoadingState();
    
    // 📝 监听输入框焦点状态
    monitorInputFocus();
  }

  // 🎨 设置光标 CSS 变量
  function setCursorCSSVariables() {
    const root = document.documentElement;
    
    // ◀️ 默认光标 - Arrow
    if (cursorConfig.arrow) {
      root.style.setProperty('--cursor-arrow', `url("${cursorConfig.arrow}"), auto`);
    }
    
    // ◀️ 文本输入光标 - Handwriting
    if (cursorConfig.handwriting) {
      root.style.setProperty('--cursor-handwriting', `url("${cursorConfig.handwriting}"), text`);
    }
    
    // ◀️ 文本框选光标 - IBeam
    if (cursorConfig.ibeam) {
      root.style.setProperty('--cursor-ibeam', `url("${cursorConfig.ibeam}"), text`);
    }
    
    // ◀️ 加载中光标 - AppStarting
    if (cursorConfig.appstarting) {
      root.style.setProperty('--cursor-appstarting', `url("${cursorConfig.appstarting}"), wait`);
    }
  }

  // ⏳ 监听页面加载状态
  function monitorLoadingState() {
    const body = document.body;
    
    // ◀️ 页面开始加载时显示加载光标
    if (document.readyState === 'loading') {
      body.classList.add('custom-cursor-loading');
    }
    
    // ◀️ DOM 加载完成
    document.addEventListener('DOMContentLoaded', function() {
      // ◀️ 延迟移除加载状态，确保资源开始加载
      setTimeout(() => {
        body.classList.remove('custom-cursor-loading');
      }, 100);
    });
    
    // ◀️ 监听资源加载状态
    let loadingResources = 0;
    
    // 🔍 监听图片加载
    document.addEventListener('load', function(e) {
      if (e.target.tagName === 'IMG' || e.target.tagName === 'IFRAME') {
        checkLoadingComplete();
      }
    }, true);
    
    // 🔍 监听 AJAX 请求
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {
      loadingResources++;
      body.classList.add('custom-cursor-loading');
      
      this.addEventListener('loadend', function() {
        loadingResources--;
        if (loadingResources <= 0) {
          checkLoadingComplete();
        }
      });
      
      originalXHROpen.apply(this, arguments);
    };
    
    // 🔍 监听 Fetch API
    const originalFetch = window.fetch;
    window.fetch = function() {
      loadingResources++;
      body.classList.add('custom-cursor-loading');
      
      return originalFetch.apply(this, arguments).finally(() => {
        loadingResources--;
        if (loadingResources <= 0) {
          checkLoadingComplete();
        }
      });
    };
    
    // ◀️ 页面完全加载后移除加载状态
    window.addEventListener('load', function() {
      setTimeout(() => {
        body.classList.remove('custom-cursor-loading');
      }, 500);
    });
  }

  // ✅ 检查加载是否完成
  function checkLoadingComplete() {
    setTimeout(() => {
      document.body.classList.remove('custom-cursor-loading');
    }, 200);
  }

  // 📝 监听输入框焦点状态
  function monitorInputFocus() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="search"], input[type="url"], input[type="tel"], input[type="number"], textarea, [contenteditable="true"]');
    
    inputs.forEach(input => {
      // ◀️ 获得焦点时添加标记
      input.addEventListener('focus', function() {
        this.classList.add('cursor-input-focused');
      });
      
      // ◀️ 失去焦点时移除标记
      input.addEventListener('blur', function() {
        this.classList.remove('cursor-input-focused');
      });
    });
  }

  // 🚀 启动初始化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomCursor);
  } else {
    initCustomCursor();
  }

})();
