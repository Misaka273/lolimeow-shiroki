/**
 * 🎯 侧边栏滚动固定效果
 * ◀️ 实现侧边栏在滚动时的固定效果，确保在页面中间固定，到达底部时停止
 */

(function() {
    'use strict';

    let sidebar = null;
    let mainContent = null;
    let footer = null;
    let sidebarHeight = 0;
    let sidebarTop = 0;
    let footerTop = 0;
    let windowHeight = 0;
    let scrollPosition = 0;
    let isFixed = false;
    let listenersBound = false;

    // 🎯 获取最新元素引用
    function refreshElements() {
        sidebar = document.querySelector('.blog-sidebar .position-sticky.top');
        mainContent = document.querySelector('.col-lg-8');
        footer = document.querySelector('footer');
    }

    // 🎯 计算元素位置和尺寸
    function calculateDimensions() {
        if (!sidebar || !footer) return;
        sidebarHeight = sidebar.offsetHeight;
        sidebarTop = sidebar.getBoundingClientRect().top + window.pageYOffset;
        footerTop = footer.getBoundingClientRect().top + window.pageYOffset;
        windowHeight = window.innerHeight;
    }

    // 🎯 处理滚动事件
    function handleScroll() {
        if (!sidebar || !footer) return;
        scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

        // 🎯 计算侧边栏应该停止固定的位置
        const stopPosition = footerTop - sidebarHeight - 20; // 20px 是边距

        // 🎯 如果滚动位置超过了侧边栏的顶部位置
        if (scrollPosition > sidebarTop) {
            // 🎯 如果还没有到达停止位置，保持固定
            if (scrollPosition < stopPosition) {
                if (!isFixed) {
                    sidebar.style.position = 'sticky';
                    sidebar.style.top = '2%';
                    sidebar.style.bottom = 'auto';
                    isFixed = true;
                }
            } else {
                // 🎯 到达停止位置，取消固定
                if (isFixed) {
                    sidebar.style.position = 'absolute';
                    sidebar.style.top = `${stopPosition}px`;
                    sidebar.style.bottom = 'auto';
                    isFixed = false;
                }
            }
        } else {
            // 🎯 没有超过侧边栏顶部位置，恢复原始状态
            if (isFixed) {
                sidebar.style.position = 'sticky';
                sidebar.style.top = '2%';
                sidebar.style.bottom = 'auto';
                isFixed = false;
            }
        }
    }

    // 🎯 处理窗口大小变化
    function handleResize() {
        calculateDimensions();
        handleScroll();
    }

    // 🎯 防抖函数
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // 🎯 初始化
    function initShirokiSidebarSticky() {
        if (window.matchMedia('(max-width: 991px)').matches) {
            return;
        }

        refreshElements();

        // 如果没有侧边栏、主内容或页脚，直接返回
        if (!sidebar || !mainContent || !footer) return;

        calculateDimensions();
        handleScroll();

        // 🎯 添加事件监听器（仅绑定一次）
        if (!listenersBound) {
            listenersBound = true;
            window.addEventListener('scroll', handleScroll);
            window.addEventListener('resize', debounce(handleResize, 200));

            // 🎯 添加页面可见性变化监听，确保页面切换时重新计算
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    refreshElements();
                    calculateDimensions();
                    handleScroll();
                }
            });
        }
    }

    function boot() {
        // 🎯 延迟初始化，确保所有元素都已加载
        setTimeout(initShirokiSidebarSticky, 100);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // 🚀 Swup 无刷新切换后重新初始化
    document.addEventListener('shiroki:content:loaded', boot);
})();