/**
 * 🖱️ 自定义鼠标光标控制脚本
 * 白木 🔗gl.baimu.live 开发
 */

(function() {
  'use strict';

  const cursorSettings = window.shirokiCursorConfig || {};
  const cursorConfig = cursorSettings.urls || cursorSettings;
  const themeBase = cursorSettings.themeBase || '';
  const pngFallbacks = Object.assign({
    arrow: themeBase + 'Arrow.png',
    handwriting: themeBase + 'Handwriting.png',
    ibeam: themeBase + 'IBeam.png',
    appstarting: themeBase + 'AppStarting.png'
  }, cursorSettings.pngFallbacks || {});

  const CURSOR_TYPES = [
    { key: 'arrow', cssVar: '--cursor-arrow' },
    { key: 'handwriting', cssVar: '--cursor-handwriting' },
    { key: 'ibeam', cssVar: '--cursor-ibeam' },
    { key: 'appstarting', cssVar: '--cursor-appstarting' }
  ];

  const DEFAULT_HOTSPOTS = {
    arrow: { x: 5, y: 5 },
    handwriting: { x: 6, y: 24 },
    ibeam: { x: 16, y: 15 },
    appstarting: { x: 5, y: 5 }
  };

  const state = {
    pageTransitioning: false,
    navigationLoading: false,
    preloaderActive: false,
    initialLoadFinished: document.readyState === 'complete'
  };

  let cssVarsInitialized = false;
  let syncDomScheduled = false;
  let preloaderTimer = null;

  function isAvifCursorUrl(url) {
    return /\.avif(\?|#|$)/i.test(url);
  }

  function buildCursorValue(url, type, hotspot) {
    const parts = [];
    const seen = new Set();
    const arrowHotspot = DEFAULT_HOTSPOTS.arrow || { x: 5, y: 5 };
    const resolvedUrl = url || pngFallbacks[type] || '';

    function add(cursorUrl, x, y) {
      if (!cursorUrl || seen.has(cursorUrl)) {
        return;
      }

      seen.add(cursorUrl);
      parts.push('url("' + cursorUrl + '") ' + x + ' ' + y);
    }

    add(resolvedUrl, hotspot.x, hotspot.y);

    if (isAvifCursorUrl(resolvedUrl)) {
      add(pngFallbacks[type], hotspot.x, hotspot.y);
    }

    add(pngFallbacks[type], hotspot.x, hotspot.y);

    if (type !== 'arrow') {
      add(pngFallbacks.arrow, arrowHotspot.x, arrowHotspot.y);
    }

    if (!parts.length) {
      add(pngFallbacks.arrow, arrowHotspot.x, arrowHotspot.y);
    }

    parts.push('default');

    return parts.join(', ');
  }

  function warmupCursorImages() {
    const urls = [];

    CURSOR_TYPES.forEach(function(typeConfig) {
      [cursorConfig[typeConfig.key], pngFallbacks[typeConfig.key]].forEach(function(imageUrl) {
        if (imageUrl && urls.indexOf(imageUrl) === -1) {
          urls.push(imageUrl);
        }
      });
    });

    urls.forEach(function(imageUrl) {
      const img = new Image();
      img.decoding = 'async';
      img.src = imageUrl;
    });
  }

  function setCursorCSSVariables() {
    const root = document.documentElement;

    CURSOR_TYPES.forEach(function(typeConfig) {
      const url = cursorConfig[typeConfig.key] || pngFallbacks[typeConfig.key];
      if (!url) {
        return;
      }

      const hotspot = DEFAULT_HOTSPOTS[typeConfig.key] || { x: 0, y: 0 };
      root.style.setProperty(
        typeConfig.cssVar,
        buildCursorValue(url, typeConfig.key, hotspot)
      );
    });
  }

  function shouldShowLoadingCursor() {
    return state.navigationLoading ||
      state.pageTransitioning ||
      state.preloaderActive ||
      !state.initialLoadFinished;
  }

  function syncDomClassesNow() {
    const html = document.documentElement;
    const body = document.body;

    if (!html) {
      return;
    }

    html.classList.add('shiroki-custom-cursor');

    if (body) {
      body.classList.add('custom-cursor-enabled');
    }

    const loading = shouldShowLoadingCursor();

    if (html.classList.contains('custom-cursor-loading') !== loading) {
      html.classList.toggle('custom-cursor-loading', loading);
    }

    if (body && body.classList.contains('custom-cursor-loading') !== loading) {
      body.classList.toggle('custom-cursor-loading', loading);
    }
  }

  function scheduleSyncDomClasses() {
    if (syncDomScheduled) {
      return;
    }

    syncDomScheduled = true;

    requestAnimationFrame(function() {
      syncDomScheduled = false;
      syncDomClassesNow();
    });
  }

  function syncCustomCursor(refreshCss) {
    if (refreshCss || !cssVarsInitialized) {
      setCursorCSSVariables();
      cssVarsInitialized = true;
    }

    scheduleSyncDomClasses();
  }

  function markNavigationLoading() {
    state.navigationLoading = true;
    scheduleSyncDomClasses();
  }

  function clearNavigationLoading() {
    state.navigationLoading = false;
    state.pageTransitioning = false;
    scheduleSyncDomClasses();
  }

  function markInitialLoadFinished() {
    state.initialLoadFinished = true;
    state.navigationLoading = false;
    scheduleSyncDomClasses();
  }

  function setupPreloaderTracking() {
    const preloader = document.querySelector('.preloader, .preloader-sakura');
    if (!preloader) {
      return;
    }

    state.preloaderActive = true;
    scheduleSyncDomClasses();

    if (preloaderTimer) {
      clearTimeout(preloaderTimer);
    }

    window.addEventListener('load', function() {
      preloaderTimer = setTimeout(function() {
        state.preloaderActive = false;
        scheduleSyncDomClasses();
      }, 1500);
    }, { once: true });
  }

  function shouldTreatLinkAsNavigation(link, event) {
    if (!link || event.defaultPrevented) {
      return false;
    }

    const href = link.getAttribute('href');
    if (!href ||
      href === '#' ||
      href.indexOf('#') === 0 ||
      href.indexOf('javascript:') === 0 ||
      href.indexOf('mailto:') === 0 ||
      href.indexOf('tel:') === 0) {
      return false;
    }

    if (link.target === '_blank' ||
      link.hasAttribute('download') ||
      link.hasAttribute('data-no-swup') ||
      link.closest('[data-no-swup]')) {
      return false;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
      return false;
    }

    try {
      const nextUrl = new URL(href, window.location.href);
      return nextUrl.origin === window.location.origin;
    } catch (error) {
      return false;
    }
  }

  function bindRuntimeEvents() {
    if (window.__shirokiCustomCursorBound) {
      return;
    }
    window.__shirokiCustomCursorBound = true;

    document.addEventListener('click', function(event) {
      const link = event.target.closest('a[href]');
      if (shouldTreatLinkAsNavigation(link, event)) {
        markNavigationLoading();
      }
    }, true);

    window.addEventListener('pageshow', function(event) {
      if (event.persisted) {
        state.initialLoadFinished = document.readyState === 'complete';
        state.navigationLoading = false;
        state.pageTransitioning = false;
        cssVarsInitialized = false;
      }

      warmupCursorImages();
      syncCustomCursor(true);
    });

    window.addEventListener('popstate', function() {
      state.navigationLoading = true;
      state.initialLoadFinished = document.readyState === 'complete';
      scheduleSyncDomClasses();
    });

    window.addEventListener('load', function() {
      markInitialLoadFinished();
    }, { once: true });

    document.addEventListener('DOMContentLoaded', function() {
      setupPreloaderTracking();
      syncCustomCursor(true);
    }, { once: true });

    document.addEventListener('shiroki:navigation:start', function() {
      state.pageTransitioning = true;
      state.navigationLoading = true;
      scheduleSyncDomClasses();
    });

    document.addEventListener('shiroki:navigation:end', function() {
      clearNavigationLoading();
      if (document.readyState === 'complete') {
        state.initialLoadFinished = true;
      }
      scheduleSyncDomClasses();
    });

    document.addEventListener('shiroki:content:loaded', function() {
      clearNavigationLoading();
      scheduleSyncDomClasses();
    });

    const htmlClassObserver = new MutationObserver(function() {
      const transitioning = document.documentElement.classList.contains('is-page-transitioning');
      if (transitioning === state.pageTransitioning) {
        return;
      }

      state.pageTransitioning = transitioning;
      scheduleSyncDomClasses();
    });

    htmlClassObserver.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });

    let hasTextSelection = false;
    let selectionSyncScheduled = false;

    function updateTextSelectionState() {
      const selection = window.getSelection();
      const next = !!(selection && !selection.isCollapsed && String(selection).trim());

      if (next === hasTextSelection || !document.body) {
        return;
      }

      hasTextSelection = next;
      document.body.classList.toggle('custom-cursor-text-selected', next);
      scheduleSyncDomClasses();
    }

    function scheduleTextSelectionUpdate() {
      if (selectionSyncScheduled) {
        return;
      }

      selectionSyncScheduled = true;

      requestAnimationFrame(function() {
        selectionSyncScheduled = false;
        updateTextSelectionState();
      });
    }

    document.addEventListener('selectionchange', scheduleTextSelectionUpdate);
    document.addEventListener('mouseup', scheduleTextSelectionUpdate);
    document.addEventListener('keyup', scheduleTextSelectionUpdate);
  }

  window.shirokiSyncCustomCursor = function() {
    syncCustomCursor(false);
  };

  warmupCursorImages();
  bindRuntimeEvents();
  syncCustomCursor(true);

  if (document.readyState !== 'loading') {
    setupPreloaderTracking();
  }

  if (document.readyState === 'complete') {
    markInitialLoadFinished();
  }

})();
