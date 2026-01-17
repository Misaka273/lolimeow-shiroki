/**
 * 🎭 文章卡片滚动放大效果 - 白木开发的交互
 * 实现页面下滑时，文章卡片从小到大的动画效果
 * 当用户下滑停在一半时，动画也会暂停，直到完全可见才完成放大
 * 支持无限加载模式下新加载的文章卡片
 */

document.addEventListener('DOMContentLoaded', function() {
    // 🎯 检测是否为文章列表页面
    if (!document.querySelector('.blog-post')) {
        return;
    }

    // 🎨 初始化所有文章卡片
    const shirokiInitPostCards = function(container = document) {
        // 获取所有文章卡片
        const postCards = container.querySelectorAll('.post-list');
        
        // 为每个卡片设置初始状态
        postCards.forEach((card, index) => {
            // 跳过已经处理过的卡片
            if (card.classList.contains('shiroki-card-animating') || card.classList.contains('shiroki-card-visible')) {
                return;
            }
            
            // 设置初始缩放状态
            card.style.transform = 'scale(0.9)';
            card.style.opacity = '0.7';
            card.style.transition = 'transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.6s ease';
            card.classList.add('shiroki-card-animating');
        });
    };

    // 🔄 滚动处理函数
    const shirokiHandleScroll = function() {
        const postCards = document.querySelectorAll('.post-list.shiroki-card-animating');
        
        postCards.forEach(card => {
            // 获取卡片的位置信息
            const cardRect = card.getBoundingClientRect();
            const cardTop = cardRect.top;
            const cardHeight = cardRect.height;
            const windowHeight = window.innerHeight;
            
            // 计算卡片可见程度
            let visiblePercent = 0;
            
            // 当卡片顶部进入视口时开始计算
            if (cardTop < windowHeight) {
                // 计算从卡片顶部进入视口到底部完全进入视口的进度
                const entryProgress = Math.min(1, (windowHeight - cardTop) / cardHeight);
                visiblePercent = entryProgress;
            }
            
            // 如果卡片开始进入视口但未完全可见
            if (visiblePercent > 0 && visiblePercent < 1) {
                // 根据可见程度计算缩放值 (0.9 到 1.0)
                const scale = 0.9 + (visiblePercent * 0.1);
                // 根据可见程度计算透明度 (0.7 到 1.0)
                const opacity = 0.7 + (visiblePercent * 0.3);
                
                // 应用变换
                card.style.transform = `scale(${scale})`;
                card.style.opacity = opacity;
            }
            
            // 如果卡片完全可见
            if (visiblePercent >= 1) {
                card.style.transform = 'scale(1)';
                card.style.opacity = '1';
                card.classList.remove('shiroki-card-animating');
                card.classList.add('shiroki-card-visible');
            }
        });
    };

    // 🔄 监听无限加载事件，处理新加载的文章卡片
    const shirokiObserveNewPosts = function() {
        // 创建一个观察器来监听DOM变化
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length) {
                    // 检查是否有新添加的文章卡片
                    let hasNewPosts = false;
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // 检查是否是文章卡片或包含文章卡片的容器
                            if (node.classList && node.classList.contains('post-list')) {
                                hasNewPosts = true;
                            } else if (node.querySelector) {
                                const postLists = node.querySelectorAll('.post-list');
                                if (postLists.length > 0) {
                                    hasNewPosts = true;
                                }
                            }
                        }
                    });
                    
                    // 如果有新文章，初始化它们
                    if (hasNewPosts) {
                        // 延迟一点时间，确保DOM完全更新
                        setTimeout(function() {
                            shirokiInitPostCards();
                            // 立即执行一次滚动处理，处理已在视口中的新卡片
                            shirokiHandleScroll();
                        }, 100);
                    }
                }
            });
        });
        
        // 开始观察文章容器
        const postsContainer = document.querySelector('.blog-post .row.g-4');
        if (postsContainer) {
            observer.observe(postsContainer, {
                childList: true,
                subtree: true
            });
        }
        
        return observer;
    };

    // 🚀 初始化
    shirokiInitPostCards();
    
    // 📜 添加滚动监听
    let ticking = false;
    function requestTick() {
        if (!ticking) {
            window.requestAnimationFrame(shirokiHandleScroll);
            ticking = true;
            setTimeout(() => { ticking = false; }, 100);
        }
    }
    
    window.addEventListener('scroll', requestTick);
    
    // 🔄 初始执行一次，处理已在视口中的卡片
    shirokiHandleScroll();
    
    // 🔄 窗口大小改变时重新计算
    window.addEventListener('resize', function() {
        setTimeout(shirokiHandleScroll, 100);
    });
    
    // 👀 启动DOM观察器，监听无限加载的新文章
    const observer = shirokiObserveNewPosts();
    
    // 🔄 暴露初始化函数到全局，供无限加载脚本调用
    window.shirokiInitPostCards = shirokiInitPostCards;
});