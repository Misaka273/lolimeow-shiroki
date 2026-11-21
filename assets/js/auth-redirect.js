(function () {
    'use strict';

    // The header can survive Swup navigation, so capture the page at activation time.
    if (window.shirokiAuthRedirectReady) return;
    window.shirokiAuthRedirectReady = true;

    const config = window.shirokiAuthRedirectConfig || {};
    const generatedLinks = new WeakMap();

    function localUrl(value) {
        if (typeof value !== 'string' || !value.trim() || /[\\\u0000-\u001f\u007f]/.test(value)) return null;
        try {
            const url = new URL(value, window.location.href);
            if (!/^https?:$/.test(url.protocol) || url.origin !== window.location.origin || url.username || url.password) return null;
            return url;
        } catch (error) {
            return null;
        }
    }

    const entryUrls = [config.loginUrl, config.signupUrl, config.defaultLoginUrl].map(localUrl).filter(Boolean);
    const authUrls = entryUrls.concat((config.authUrls || []).map(localUrl).filter(Boolean));

    function routePath(url) {
        let path = url.pathname;
        try { path = decodeURIComponent(path); } catch (error) { /* Keep malformed escapes literal. */ }
        return path.replace(/\/+$/, '');
    }

    function matchesRoute(url, route) {
        if (url.origin !== route.origin || routePath(url) !== routePath(route)) return false;
        // Keep page_id and other routing parameters for sites without pretty permalinks.
        for (const [key, value] of route.searchParams) {
            if (key === 'redirect_to' || key === 'mode') continue;
            if (url.searchParams.get(key) !== value) return false;
        }
        return true;
    }

    function isAuthUrl(url) {
        return authUrls.some(function (route) { return matchesRoute(url, route); });
    }

    function safeReturnUrl(value) {
        const url = localUrl(value);
        return url && !isAuthUrl(url) ? url : null;
    }

    function currentReturnUrl() {
        const current = localUrl(window.location.href);
        if (!current) return null;
        return isAuthUrl(current) ? safeReturnUrl(current.searchParams.get('redirect_to')) : current;
    }

    function prepareAuthLink(event) {
        const target = event.target && (event.target.nodeType === 3 ? event.target.parentElement : event.target);
        const link = target && typeof target.closest === 'function' ? target.closest('a[href]') : null;
        if (!link || link.hasAttribute('download')) return;

        const destination = localUrl(link.getAttribute('href'));
        if (!destination || !entryUrls.some(function (route) { return matchesRoute(destination, route); })) return;
        const action = destination.searchParams.get('action');
        if (/\/wp-login\.php$/.test(destination.pathname) && action && action !== 'login' && action !== 'register') return;

        const previous = generatedLinks.get(link);
        const ownHref = previous && previous.href === link.getAttribute('href');
        const explicitValue = ownHref ? previous.explicitReturn : destination.searchParams.get('redirect_to');
        const explicitReturn = link.hasAttribute('data-auth-return-current') ? null : safeReturnUrl(explicitValue);
        const returnUrl = explicitReturn || currentReturnUrl();

        destination.searchParams.delete('redirect_to');
        if (returnUrl) destination.searchParams.set('redirect_to', returnUrl.href);
        link.setAttribute('href', destination.href);
        link.setAttribute('data-no-swup', '');
        generatedLinks.set(link, { href: destination.href, explicitReturn: explicitReturn && explicitReturn.href });
    }

    // Pointer and context menu activation cover opening an auth page in a new tab;
    // click also covers keyboard activation and links created after page load.
    ['pointerdown', 'click', 'auxclick', 'contextmenu'].forEach(function (eventName) {
        document.addEventListener(eventName, prepareAuthLink, true);
    });
}());
