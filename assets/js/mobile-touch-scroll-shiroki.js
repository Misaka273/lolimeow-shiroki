/* 🌟 移动端触摸滑动支持 - 480x690尺寸 */
/* 📱 为注册模块添加触摸滑动功能 */

(function() {
    'use strict';
    
    // 🌟 检测是否为480x690尺寸
    function isMobile480x690() {
        return window.innerWidth === 480 && window.innerHeight === 690;
    }
    
    // 🌟 触摸滑动管理器
    class TouchScrollManager {
        constructor() {
            this.startY = 0;
            this.startX = 0;
            this.scrollTop = 0;
            this.isScrolling = false;
            this.signUpForm = null;
            this.formsContainer = null;
            
            this.init();
        }
        
        init() {
            if (!isMobile480x690()) return;
            
            this.signUpForm = document.querySelector('.sign-up-form');
            this.formsContainer = document.querySelector('.forms-container');
            
            if (!this.signUpForm || !this.formsContainer) return;
            
            this.bindEvents();
            this.setupScrollIndicators();
            console.log('📱 触摸滑动功能已初始化');
        }
        
        bindEvents() {
            // 🌟 触摸事件
            this.signUpForm.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
            this.signUpForm.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
            this.signUpForm.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
            
            // 🌟 鼠标事件（用于模拟触摸）
            this.signUpForm.addEventListener('mousedown', this.handleMouseDown.bind(this));
            this.signUpForm.addEventListener('mousemove', this.handleMouseMove.bind(this));
            this.signUpForm.addEventListener('mouseup', this.handleMouseUp.bind(this));
            this.signUpForm.addEventListener('mouseleave', this.handleMouseUp.bind(this));
            
            // 🌟 滚动事件
            this.signUpForm.addEventListener('scroll', this.handleScroll.bind(this));
            
            // 🌟 窗口大小变化
            window.addEventListener('resize', this.handleResize.bind(this));
        }
        
        handleTouchStart(e) {
            if (!isMobile480x690()) return;
            
            const touch = e.touches[0];
            this.startY = touch.clientY;
            this.startX = touch.clientX;
            this.scrollTop = this.signUpForm.scrollTop;
            this.isScrolling = true;
            
            this.signUpForm.style.transition = 'none';
        }
        
        handleTouchMove(e) {
            if (!this.isScrolling || !isMobile480x690()) return;
            
            const touch = e.touches[0];
            const deltaY = touch.clientY - this.startY;
            const deltaX = touch.clientX - this.startX;
            
            // 🌟 判断是垂直滚动还是水平滑动
            if (Math.abs(deltaY) > Math.abs(deltaX)) {
                // 垂直滚动
                e.preventDefault();
                this.signUpForm.scrollTop = this.scrollTop - deltaY;
                this.updateScrollIndicators();
            }
        }
        
        handleTouchEnd() {
            if (!this.isScrolling) return;
            
            this.isScrolling = false;
            this.signUpForm.style.transition = '';
            this.updateScrollIndicators();
        }
        
        handleMouseDown(e) {
            if (!isMobile480x690()) return;
            
            this.startY = e.clientY;
            this.startX = e.clientX;
            this.scrollTop = this.signUpForm.scrollTop;
            this.isScrolling = true;
            
            this.signUpForm.style.cursor = 'grabbing';
            this.signUpForm.style.transition = 'none';
        }
        
        handleMouseMove(e) {
            if (!this.isScrolling || !isMobile480x690()) return;
            
            const deltaY = e.clientY - this.startY;
            const deltaX = e.clientX - this.startX;
            
            if (Math.abs(deltaY) > Math.abs(deltaX)) {
                this.signUpForm.scrollTop = this.scrollTop - deltaY;
                this.updateScrollIndicators();
            }
        }
        
        handleMouseUp() {
            if (!this.isScrolling) return;
            
            this.isScrolling = false;
            this.signUpForm.style.cursor = '';
            this.signUpForm.style.transition = '';
            this.updateScrollIndicators();
        }
        
        handleScroll() {
            if (!isMobile480x690()) return;
            this.updateScrollIndicators();
        }
        
        handleResize() {
            setTimeout(() => {
                if (isMobile480x690()) {
                    this.updateScrollIndicators();
                }
            }, 100);
        }
        
        // 🌟 设置滚动指示器
        setupScrollIndicators() {
            // 移除现有的指示器
            const existingIndicators = document.querySelectorAll('.scroll-indicator');
            existingIndicators.forEach(indicator => indicator.remove());
            
            if (!isMobile480x690()) return;
            
            // 创建滚动指示器
            const scrollIndicator = document.createElement('div');
            scrollIndicator.className = 'scroll-indicator';
            scrollIndicator.innerHTML = `
                <div class="scroll-bar">
                    <div class="scroll-thumb"></div>
                </div>
                <div class="scroll-hint">↑ 上下滑动查看更多</div>
            `;
            
            // 添加样式
            scrollIndicator.style.cssText = `
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                width: 4px;
                height: 60px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                z-index: 100;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            `;
            
            const scrollBar = scrollIndicator.querySelector('.scroll-bar');
            const scrollThumb = scrollIndicator.querySelector('.scroll-thumb');
            const scrollHint = scrollIndicator.querySelector('.scroll-hint');
            
            scrollBar.style.cssText = `
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                position: relative;
                overflow: hidden;
            `;
            
            scrollThumb.style.cssText = `
                width: 100%;
                background: linear-gradient(to bottom, #5995fd, #4d84e2);
                border-radius: 2px;
                position: absolute;
                top: 0;
                left: 0;
                transition: all 0.2s ease;
            `;
            
            scrollHint.style.cssText = `
                position: absolute;
                right: 10px;
                top: -25px;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 10px;
                white-space: nowrap;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            `;
            
            // 添加到表单
            this.signUpForm.style.position = 'relative';
            this.signUpForm.appendChild(scrollIndicator);
            
            this.scrollIndicator = scrollIndicator;
            this.scrollThumb = scrollThumb;
            this.scrollHint = scrollHint;
            
            // 初始更新
            this.updateScrollIndicators();
        }
        
        // 🌟 更新滚动指示器
        updateScrollIndicators() {
            if (!this.scrollIndicator || !this.scrollThumb) return;
            
            const scrollHeight = this.signUpForm.scrollHeight;
            const clientHeight = this.signUpForm.clientHeight;
            const scrollTop = this.signUpForm.scrollTop;
            
            if (scrollHeight <= clientHeight) {
                // 不需要滚动
                this.scrollIndicator.style.opacity = '0';
                return;
            }
            
            // 显示指示器
            this.scrollIndicator.style.opacity = '1';
            
            // 计算滚动进度
            const scrollProgress = scrollTop / (scrollHeight - clientHeight);
            const thumbHeight = Math.max(20, (clientHeight / scrollHeight) * 60);
            const thumbTop = scrollProgress * (60 - thumbHeight);
            
            // 更新滑块
            this.scrollThumb.style.height = thumbHeight + 'px';
            this.scrollThumb.style.top = thumbTop + 'px';
            
            // 显示提示
            if (scrollTop < 10) {
                this.scrollHint.style.opacity = '1';
                setTimeout(() => {
                    if (this.scrollHint) {
                        this.scrollHint.style.opacity = '0';
                    }
                }, 2000);
            }
        }
    }
    
    // 🌟 初始化触摸滑动
    function initTouchScroll() {
        if (isMobile480x690()) {
            new TouchScrollManager();
        }
    }
    
    // 🌟 页面加载完成后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTouchScroll);
    } else {
        initTouchScroll();
    }
    
    // 🌟 监听窗口大小变化
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (isMobile480x690()) {
                initTouchScroll();
            }
        }, 250);
    });
    
})();