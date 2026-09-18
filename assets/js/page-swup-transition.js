/**
 * 🚀 Swup 无刷新页面切换
 * 淡入淡出 + 位移动画
 * 保留 Banner 区域与导航栏，仅替换 #swup-container 主内容区
 */
(function () {
    'use strict';

    // 安全检测：未启用 Swup 或不支持所需 API 时直接退出
    if (typeof window.Swup === 'undefined') {
        return;
    }

    // 🚫 防止 Swup 被重复初始化（如 SwupScriptsPlugin 重新执行本脚本）
    if (window.__shirokiSwupInitialized) {
        return;
    }
    window.__shirokiSwupInitialized = true;

    // 🚫 整页排除：body 带有 data-no-swup 时不启用 Swup
    const body = document.body;
    if (body && (body.hasAttribute('data-no-swup') || body.classList.contains('no-swup'))) {
        return;
    }

    // 🔗 收集必须整页刷新的独立布局 URL（登录/注册/会员中心/重置密码）
    function collectFullReloadUrls() {
        const urls = [];
        if (window.shirokiSwupConfig && Array.isArray(window.shirokiSwupConfig.fullReloadUrls)) {
            window.shirokiSwupConfig.fullReloadUrls.forEach(function (url) {
                if (url) {
                    urls.push(url);
                }
            });
        }
        ['login-url', 'register-url', 'user-center-url'].forEach(function (name) {
            const meta = document.querySelector('meta[name="' + name + '"]');
            const content = meta && meta.getAttribute('content');
            if (content) {
                urls.push(content);
            }
        });
        return urls;
    }

    function parsePageUrl(url) {
        try {
            return new URL(url, window.location.origin);
        } catch (error) {
            return null;
        }
    }

    function normalizePathname(pathname) {
        return (String(pathname || '').replace(/\/+$/, '') || '/').toLowerCase();
    }

    // 🚫 登录页等独立 HTML 没有 #swup-container，无刷新替换会错显当前页内容
    function isFullReloadUrl(url) {
        const target = parsePageUrl(url);
        if (!target) {
            return false;
        }
        const list = collectFullReloadUrls();
        for (let i = 0; i < list.length; i++) {
            const item = parsePageUrl(list[i]);
            if (!item || item.origin !== target.origin) {
                continue;
            }
            const pathA = normalizePathname(item.pathname);
            const pathB = normalizePathname(target.pathname);
            if (pathA !== pathB) {
                continue;
            }
            const pageIdA = item.searchParams.get('page_id') || item.searchParams.get('p') || '';
            const pageIdB = target.searchParams.get('page_id') || target.searchParams.get('p') || '';
            if (pathA === '/' && (pageIdA || pageIdB)) {
                if (pageIdA && pageIdA === pageIdB) {
                    return true;
                }
                continue;
            }
            return true;
        }
        return false;
    }

    // 🚫 排除不需要 Swup 处理的链接
    const excludedSelectors = [
        '[data-no-swup]',
        '[target="_blank"]',
        '[href^="mailto:"]',
        '[href^="tel:"]',
        '[href^="javascript:"]',
        '[href^="#"]',
        '.user-login',
        '.user-reg',
        '[href*="/wp-admin"]',
        '[href*="/wp-login.php"]',
        '[href*="/wp-register.php"]',
        '[href*="/wp-content/uploads"]',
        '[href*=".pdf"]',
        '[href*=".zip"]',
        '[href*=".rar"]',
        '[href*=".doc"]',
        '[href*=".docx"]',
        '[href*=".xls"]',
        '[href*=".xlsx"]',
        '[href*="/customize.php"]',
        '[href*="/wp-json/"]',
        '[href*="/user_center"]',
        '[href*="/signin"]',
        '[href*="/signup"]',
        '[href*="/reset_password"]',
        '[download]'
    ];

    // 匹配所有带 href 的链接，排除列表中的选择器；Swup 会自动过滤跨域链接
    const linkSelector = 'a[href]:not(' + excludedSelectors.join('):not(') + ')';

    // 🧩 Swup 插件配置
    const plugins = [];

    if (typeof window.SwupHeadPlugin !== 'undefined') {
        plugins.push(new window.SwupHeadPlugin());
    }

    if (typeof window.SwupProgressPlugin !== 'undefined') {
        plugins.push(new window.SwupProgressPlugin({
            className: 'swup-progress-bar',
            transition: 300,
            delay: 0
        }));
    }

    if (typeof window.SwupBodyClassPlugin !== 'undefined') {
        plugins.push(new window.SwupBodyClassPlugin());
    }

    if (typeof window.SwupScriptsPlugin !== 'undefined') {
        plugins.push(new window.SwupScriptsPlugin({
            optin: false,
            head: true,
            body: true
        }));
    }

    // ⚙️ Swup 实例化
    let swup;
    let userScrolledDuringVisit = false;
    function markUserScrolled() {
        userScrolledDuringVisit = true;
    }
    try {
        swup = new window.Swup({
            containers: ['#swup-container'],
            linkSelector: linkSelector,
            animateHistoryBrowsing: false,
            cache: true,
            ignoreVisit: function (url, { el }) {
                // 额外排除含有 data-no-swup 的元素
                if (el && el.closest('[data-no-swup]')) {
                    return true;
                }
                if (el && (el.closest('.user-login') || el.closest('.user-reg'))) {
                    return true;
                }
                if (url && isFullReloadUrl(url)) {
                    return true;
                }
                return false;
            },
            skipPopStateHandling: function (event) {
                // 跳过包含锚点的历史记录处理，交由浏览器原生处理
                return !!(event.state && event.state.url && event.state.url.indexOf('#') !== -1);
            },
            plugins: plugins
        });
    } catch (error) {
        console.warn('[Swup] 初始化失败，已回退到普通跳转。', error);
        return;
    }

    // 🔄 内容替换后重新初始化主题组件
    function reinitThemeComponents() {
        const run = function (fn) {
            try {
                fn();
            } catch (error) {
                if (window.console && console.warn) {
                    console.warn('[Swup] 组件重初始化失败：', error);
                }
            }
        };

        // boxmoe.js 中的全局初始化函数（按原初始化顺序调用）
        if (typeof window.initWow === 'function') run(window.initWow);
        if (typeof window.initLazyLoad === 'function') run(window.initLazyLoad);
        if (typeof window.initSearchBox === 'function') run(window.initSearchBox);
        if (typeof window.initMobileUserPanel === 'function') run(window.initMobileUserPanel);
        if (typeof window.initPostCoverImages === 'function') run(window.initPostCoverImages);
        if (typeof window.initStickyHeader === 'function') run(window.initStickyHeader);
        if (typeof window.initTableOfContents === 'function') run(window.initTableOfContents);
        if (typeof window.initTagColors === 'function') run(window.initTagColors);
        if (typeof window.initHitokoto === 'function') run(window.initHitokoto);
        if (typeof window.initPostLikes === 'function') run(window.initPostLikes);
        if (typeof window.initReward === 'function') run(window.initReward);
        if (typeof window.initPostFavorites === 'function') run(window.initPostFavorites);
        if (typeof window.initPrettyPrint === 'function') run(window.initPrettyPrint);
        if (typeof window.initCodeCopy === 'function') run(window.initCodeCopy);
        if (typeof window.initTaskList === 'function') run(window.initTaskList);
        if (typeof window.initVideoPlayer === 'function') run(window.initVideoPlayer);
        if (typeof window.initInfiniteScroll === 'function') run(window.initInfiniteScroll);
        if (typeof window.initBackToTop === 'function') run(window.initBackToTop);
        if (typeof window.initPostUpdateTimer === 'function') run(window.initPostUpdateTimer);
        if (typeof window.initRunningDays === 'function') run(window.initRunningDays);
        if (typeof window.initShirokiAnimePage === 'function') run(window.initShirokiAnimePage);

        // 主题切换器重新初始化
        if (window.ThemeSwitcher && typeof window.ThemeSwitcher.init === 'function') {
            run(window.ThemeSwitcher.init);
        }

        // Fancybox 重新绑定
        if (window.Fancybox && typeof window.Fancybox.bind === 'function') {
            run(function () {
                window.Fancybox.bind('[data-fancybox]', {});
            });
        }

        // Bootstrap 下拉菜单重新初始化
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Dropdown) {
            run(function () {
                document.querySelectorAll('.dropdown-toggle').forEach(function (el) {
                    if (!el.dataset.bsToggle) {
                        el.dataset.bsToggle = 'dropdown';
                    }
                    new window.bootstrap.Dropdown(el);
                });
            });
        }

        // 切换账号按钮事件委托（兼容 Swup 动态加载的表单）
        run(function () {
            if (document.body.dataset.shirokiSwitchAccountBound === 'true') {
                return;
            }
            document.body.dataset.shirokiSwitchAccountBound = 'true';
            document.body.addEventListener('click', function (e) {
                const btn = e.target.closest('.switch-account-btn');
                if (!btn) return;
                const guestInputs = document.querySelector('.guest-inputs');
                if (guestInputs) {
                    guestInputs.classList.toggle('active');
                    btn.classList.toggle('active');
                }
            });
        });

        // 🌟 通知其他独立脚本进行重初始化
        document.dispatchEvent(new CustomEvent('shiroki:content:loaded', {
            bubbles: true,
            cancelable: true
        }));

        if (typeof window.shirokiSyncCustomCursor === 'function') {
            run(window.shirokiSyncCustomCursor);
        }
    }

    // 📌 Swup 事件钩子
    if (swup.hooks) {
        // 切换开始时：立即回滚顶部并监听用户滚动
        swup.hooks.on('visit:start', function () {
            document.documentElement.classList.add('is-page-transitioning');
            document.dispatchEvent(new CustomEvent('shiroki:navigation:start', {
                bubbles: true,
                cancelable: true
            }));
            userScrolledDuringVisit = false;
            window.addEventListener('scroll', markUserScrolled, { passive: true });

            // 临时覆盖 CSS smooth-scroll，确保点击后立即回顶
            const originalScrollBehavior = document.documentElement.style.scrollBehavior;
            document.documentElement.style.scrollBehavior = 'auto';
            window.scrollTo({top: 0, left: 0, behavior: 'auto'});
            document.documentElement.style.scrollBehavior = originalScrollBehavior;
        });

        // 接管 Swup 默认滚动：由我们在内容替换阶段统一处理
        swup.hooks.replace('scroll:top', function () {
            return !!userScrolledDuringVisit;
        });
        swup.hooks.replace('scroll:anchor', function (visit, args) {
            let hash = args.hash || visit?.to?.hash || '';
            if (hash.charAt(0) === '#') {
                hash = hash.substring(1);
            }
            if (!hash) {
                return false;
            }
            const element = document.getElementById(decodeURIComponent(hash)) ||
                document.querySelector("a[name='" + CSS.escape(hash) + "']");
            if (element) {
                element.scrollIntoView({ behavior: 'auto' });
                return true;
            }
            return false;
        });

        // 内容替换前：无锚点且用户未滚动时再次确保回顶
        swup.hooks.before('content:replace', function (visit) {
            const hash = visit?.to?.hash || '';
            if (hash && hash !== '#') {
                return;
            }
            if (!userScrolledDuringVisit) {
                window.scrollTo({top: 0, left: 0, behavior: 'auto'});
            }
        });

        // 内容替换后：用户已滚动时阻止默认回滚，保留当前位置
        swup.hooks.on('content:scroll', function (visit) {
            if (userScrolledDuringVisit && visit && visit.scroll) {
                visit.scroll.reset = false;
                visit.scroll.target = undefined;
            }
        });

        // 内容替换完成后重初始化主题组件
        swup.hooks.on('content:replace', function () {
            reinitThemeComponents();

            // 🌈 重置 Banner 打字动画，防止 Swup 缓存导致欢迎语重复
            if (window.boxmoeBannerAnimation && typeof window.boxmoeBannerAnimation.reset === 'function') {
                try {
                    window.boxmoeBannerAnimation.reset();
                } catch (error) {
                    if (window.console && console.warn) {
                        console.warn('[Swup] Banner 动画重置失败：', error);
                    }
                }
            }
        });

        // 切换动画结束后移除状态与滚动监听
        swup.hooks.on('visit:end', function () {
            document.documentElement.classList.remove('is-page-transitioning');
            document.dispatchEvent(new CustomEvent('shiroki:navigation:end', {
                bubbles: true,
                cancelable: true
            }));
            window.removeEventListener('scroll', markUserScrolled, { passive: true });
        });

        // 页面可见时刷新登录状态
        swup.hooks.on('page:view', function () {
            if (window.LoginStatusManager && typeof window.LoginStatusManager.checkLoginStatus === 'function') {
                try {
                    window.LoginStatusManager.checkLoginStatus();
                } catch (_) {}
            }
        });
    } else if (swup.on) {
        // 兼容旧版事件 API
        swup.on('content:replace', reinitThemeComponents);
    }

    // 🔍 拦截搜索表单提交，使其走 Swup 无刷新切换
    function initSwupSearchForms() {
        document.querySelectorAll('form.search-form[role="search"], form.mobile-search-form[role="search"]').forEach(function (form) {
            // 避免重复绑定
            if (form.dataset.shirokiSwupBound === 'true') {
                return;
            }
            form.dataset.shirokiSwupBound = 'true';

            form.addEventListener('submit', function (event) {
                // 仅处理 GET 方法的搜索表单
                if (form.method && form.method.toLowerCase() !== 'get') {
                    return;
                }
                // 如果表单在 Swup 排除区域内，不拦截
                if (form.closest('[data-no-swup]')) {
                    return;
                }

                event.preventDefault();

                const action = form.action || window.location.origin + '/';
                const formData = new FormData(form);
                const params = new URLSearchParams();
                formData.forEach(function (value, key) {
                    if (typeof value === 'string' && value.length > 0) {
                        params.append(key, value);
                    }
                });

                const query = params.toString();
                const url = action + (action.indexOf('?') === -1 ? '?' : '&') + query + window.location.hash;

                if (typeof swup.navigate === 'function') {
                    swup.navigate(url);
                } else if (typeof swup.loadPage === 'function') {
                    swup.loadPage({ url: url });
                } else {
                    window.location.assign(url);
                }
            });
        });
    }

    // 首次初始化搜索表单
    initSwupSearchForms();

    // Swup 内容替换后，新页面中的搜索表单需要重新绑定
    if (swup.hooks) {
        swup.hooks.on('content:replace', initSwupSearchForms);
    }
})();
