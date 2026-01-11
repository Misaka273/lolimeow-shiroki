// 🎵 音乐播放器API切换和错误处理脚本
// 🔗 解决音乐源加载失败问题

class ShirokiMusicAPIManager {
    constructor() {
        this.apis = window.shirokiMusicAPIs || [];
        this.currentApiIndex = 0;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.init();
    }

    init() {
        // 🎵 监听音乐播放器加载错误
        document.addEventListener('DOMContentLoaded', () => {
            this.setupErrorHandling();
            this.setupAPISwitcher();
        });
    }

    // 🎵 设置错误处理
    setupErrorHandling() {
        // 监听APlayer错误
        window.addEventListener('error', (event) => {
            if (event.message && event.message.includes('no supported source')) {
                console.warn('🎵 检测到音乐源加载失败，尝试切换API');
                this.switchAPI();
            }
        });

        // 监听未捕获的Promise错误
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.message && event.reason.message.includes('Failed to fetch')) {
                console.warn('🎵 检测到API请求失败，尝试切换API');
                this.switchAPI();
            }
        });
    }

    // 🎵 设置API切换器
    setupAPISwitcher() {
        // 添加API切换按钮到播放器
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.classList.contains('aplayer')) {
                            this.addAPISwitchButton(node);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // 🎵 添加API切换按钮
    addAPISwitchButton(aplayerElement) {
        // 检查是否已经添加过按钮
        if (aplayerElement.querySelector('.shiroki-api-switch')) {
            return;
        }

        // 创建切换按钮
        const switchButton = document.createElement('div');
        switchButton.className = 'shiroki-api-switch';
        switchButton.innerHTML = '🔄 切换源';
        switchButton.title = '点击切换音乐API源';
        switchButton.style.cssText = `
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            z-index: 9999;
            opacity: 0.7;
            transition: opacity 0.3s;
        `;

        // 添加悬停效果
        switchButton.addEventListener('mouseenter', () => {
            switchButton.style.opacity = '1';
        });

        switchButton.addEventListener('mouseleave', () => {
            switchButton.style.opacity = '0.7';
        });

        // 添加点击事件
        switchButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.switchAPI();
            this.showNotification('正在切换音乐源...');
        });

        // 将按钮添加到播放器
        const aplayerBody = aplayerElement.querySelector('.aplayer-body');
        if (aplayerBody) {
            aplayerBody.style.position = 'relative';
            aplayerBody.appendChild(switchButton);
        }
    }

    // 🎵 切换API
    async switchAPI() {
        if (this.apis.length <= 1) {
            this.showNotification('没有其他可用的音乐源');
            return;
        }

        // 🎵 如果当前使用的是自定义API，则切换到第一个备用API
        let currentApiIndex = this.currentApiIndex;
        if (window.shirokiCustomAPI && this.apis[currentApiIndex] === window.shirokiCustomAPI) {
            // 从自定义API切换到第一个备用API
            currentApiIndex = 0;
        } else {
            // 更新当前API索引
            currentApiIndex = (currentApiIndex + 1) % this.apis.length;
        }
        
        // 🎵 如果下一个是自定义API，则跳过它（除非当前就是自定义API）
        if (!window.shirokiCustomAPI || this.apis[currentApiIndex] !== window.shirokiCustomAPI) {
            this.currentApiIndex = currentApiIndex;
        } else {
            // 跳过自定义API，继续寻找下一个
            this.currentApiIndex = (currentApiIndex + 1) % this.apis.length;
        }
        
        const newAPI = this.apis[this.currentApiIndex];

        // 更新全局API设置
        window.meting_api = newAPI;

        // 显示通知
        this.showNotification(`切换到音乐源: ${this.getAPIName(newAPI)}`);

        // 重新加载播放器
        this.reloadPlayer();
    }

    // 🎵 重新加载播放器
    reloadPlayer() {
        // 查找所有meting-js元素
        const metingElements = document.querySelectorAll('meting-js');
        
        metingElements.forEach(element => {
            // 保存原始属性
            const server = element.getAttribute('server');
            const type = element.getAttribute('type');
            const id = element.getAttribute('id');
            const fixed = element.getAttribute('fixed');
            const order = element.getAttribute('order');
            const preload = element.getAttribute('preload');
            const listFolded = element.getAttribute('list-folded');
            const lrcType = element.getAttribute('lrc-type');

            // 销毁旧的播放器
            if (element.aplayer) {
                element.aplayer.destroy();
            }

            // 更新API属性
            element.setAttribute('api', window.meting_api);

            // 重新初始化
            element.connectedCallback();
        });
    }

    // 🎵 获取API名称
    getAPIName(url) {
        if (url.includes('api.injahow.cn')) return 'Injahow API';
        if (url.includes('api.i-meto.com')) return 'iMeto API';
        if (url.includes('meting-api.ihuan.me')) return 'Ihuan API';
        if (url.includes('api-meting.github.io')) return 'GitHub API';
        // 🎵 检查是否是自定义API
        if (window.shirokiCustomAPI && url === window.shirokiCustomAPI) return '自定义API';
        return '未知API';
    }

    // 🎵 显示通知
    showNotification(message) {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = 'shiroki-music-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 10000;
            max-width: 300px;
            transform: translateX(0);
            transition: transform 0.3s ease;
        `;

        // 添加到页面
        document.body.appendChild(notification);

        // 3秒后自动移除
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// 🎵 初始化音乐API管理器
document.addEventListener('DOMContentLoaded', () => {
    new ShirokiMusicAPIManager();
});