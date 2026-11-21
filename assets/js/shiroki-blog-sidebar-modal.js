/**
 * 移动端侧栏弹窗：同步侧栏小部件，点击跳转链接时关闭弹窗
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

    function bindSidebarNavigation() {
        const modal = document.getElementById('blog-sidebar-modal');
        if (!modal || modal.dataset.sidebarNavigationBound === 'true') {
            return;
        }

        let showing = false;
        let closeAfterShown = false;
        modal.addEventListener('show.bs.modal', function () {
            showing = true;
            closeAfterShown = false;
        });
        modal.addEventListener('shown.bs.modal', function () {
            showing = false;
            if (closeAfterShown && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                closeAfterShown = false;
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) {
                    instance.hide();
                }
            }
        });
        modal.addEventListener('hide.bs.modal', function () {
            showing = false;
            closeAfterShown = false;
        });

        modal.addEventListener('click', function (event) {
            const link = event.target.closest('.blog-sidebar-modal-body a[href]');
            if (!link || event.defaultPrevented || event.button > 0 ||
                event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ||
                link.hasAttribute('download')) {
                return;
            }

            const href = link.getAttribute('href').trim();
            if (!href || href === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) {
                return;
            }

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) {
                    if (showing) {
                        closeAfterShown = true;
                    } else {
                        instance.hide();
                    }
                }
            }
        });

        modal.dataset.sidebarNavigationBound = 'true';
    }

    function boot() {
        bindSidebarNavigation();
        syncModalContent();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('shiroki:content:loaded', boot);
})();
