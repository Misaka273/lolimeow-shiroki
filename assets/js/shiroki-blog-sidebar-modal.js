/**
 * 移动端侧栏弹窗：Swup 切换后同步主内容区侧栏小部件
 */
(function () {
    'use strict';

    function syncModalContent() {
        const source = document.querySelector('#swup-container .blog-sidebar-inner');
        const target = document.querySelector('#blog-sidebar-modal .blog-sidebar-modal-body');
        if (!source || !target) {
            return;
        }
        target.innerHTML = source.innerHTML;
    }

    function boot() {
        syncModalContent();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('shiroki:content:loaded', boot);
})();
