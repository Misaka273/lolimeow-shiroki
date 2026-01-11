// 🎵 音乐播放器错误处理脚本
// 🔗 处理音乐源加载失败和歌词获取失败问题

class ShirokiMusicErrorHandler {
    constructor() {
        this.errorCount = 0;
        this.maxErrors = 5;
        this.errorMessages = [];
        this.init();
    }

    init() {
        // 监听页面加载完成
        document.addEventListener('DOMContentLoaded', () => {
            this.setupErrorListeners();
            this.setupLoadingIndicator();
        });
    }

    // 🎵 设置错误监听器
    setupErrorListeners() {
        // 监听全局错误
        window.addEventListener('error', (event) => {
            this.handleError(event.error || event.message, event.filename);
        });

        // 监听Promise错误
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError(event.reason, 'Promise');
        });

        // 监听APlayer特定错误
        this.monitorAPlayerErrors();
    }

    // 🎵 监控APlayer错误
    monitorAPlayerErrors() {
        // 定期检查APlayer状态
        setInterval(() => {
            const aplayerElements = document.querySelectorAll('.aplayer');
            aplayerElements.forEach(element => {
                if (element.aplayer) {
                    // 检查是否有音频错误
                    const audio = element.aplayer.audio;
                    if (audio && audio.error) {
                        this.handleError(new Error(`音频加载错误: ${audio.error.message}`), 'APlayer');
                    }
                }
            });
        }, 3000);
    }

    // 🎵 处理错误
    handleError(error, source = 'Unknown') {
        this.errorCount++;
        const errorMessage = error instanceof Error ? error.message : String(error);
        
        // 记录错误
        this.errorMessages.push({
            message: errorMessage,
            source: source,
            timestamp: new Date().toISOString(),
            count: this.errorCount
        });

        // 检查是否是音乐相关错误
        if (this.isMusicRelatedError(errorMessage)) {
            console.warn(`🎵 音乐播放器错误 [${source}]:`, errorMessage);
            
            // 显示用户友好的错误提示
            this.showUserFriendlyError(errorMessage, source);
            
            // 尝试自动修复
            this.attemptAutoFix(errorMessage);
        }
    }

    // 🎵 检查是否是音乐相关错误
    isMusicRelatedError(message) {
        const musicErrorKeywords = [
            'no supported source',
            'Failed to fetch',
            'network error',
            'APlayer',
            'meting',
            'audio',
            'music',
            'lyrics',
            'lrc'
        ];
        
        return musicErrorKeywords.some(keyword => 
            message.toLowerCase().includes(keyword.toLowerCase())
        );
    }

    // 🎵 显示用户友好的错误提示
    showUserFriendlyError(errorMessage, source) {
        let friendlyMessage = '音乐播放器遇到问题';
        
        if (errorMessage.includes('no supported source')) {
            friendlyMessage = '音乐源加载失败，请尝试切换API源';
        } else if (errorMessage.includes('Failed to fetch')) {
            friendlyMessage = '网络请求失败，请检查网络连接或尝试切换API源';
        } else if (errorMessage.includes('lyrics') || errorMessage.includes('lrc')) {
            friendlyMessage = '歌词加载失败，但音乐播放不受影响';
        }
        
        this.showNotification(friendlyMessage, 'warning');
    }

    // 🎵 尝试自动修复
    attemptAutoFix(errorMessage) {
        if (errorMessage.includes('no supported source') || errorMessage.includes('Failed to fetch')) {
            // 尝试切换API源
            if (window.shirokiMusicAPIManager) {
                setTimeout(() => {
                    window.shirokiMusicAPIManager.switchAPI();
                }, 1000);
            }
        }
    }

    // 🎵 设置加载指示器
    setupLoadingIndicator() {
        // 监控APlayer加载状态
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.classList.contains('aplayer')) {
                            this.addLoadingIndicator(node);
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

    // 🎵 添加加载指示器
    addLoadingIndicator(aplayerElement) {
        // 检查是否已经添加过指示器
        if (aplayerElement.querySelector('.shiroki-loading-indicator')) {
            return;
        }

        // 创建加载指示器
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'shiroki-loading-indicator';
        loadingIndicator.innerHTML = '🎵 加载音乐中...';
        loadingIndicator.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 9999;
            pointer-events: none;
        `;

        // 添加到播放器
        const aplayerBody = aplayerElement.querySelector('.aplayer-body');
        if (aplayerBody) {
            aplayerBody.style.position = 'relative';
            aplayerBody.appendChild(loadingIndicator);
            
            // 5秒后自动隐藏
            setTimeout(() => {
                if (loadingIndicator.parentNode) {
                    loadingIndicator.parentNode.removeChild(loadingIndicator);
                }
            }, 5000);
        }
    }

    // 🎵 显示通知
    showNotification(message, type = 'info') {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `shiroki-music-notification shiroki-notification-${type}`;
        notification.textContent = message;
        
        // 设置样式
        let bgColor = 'rgba(0, 0, 0, 0.8)';
        if (type === 'warning') {
            bgColor = 'rgba(255, 152, 0, 0.8)';
        } else if (type === 'error') {
            bgColor = 'rgba(244, 67, 54, 0.8)';
        } else if (type === 'success') {
            bgColor = 'rgba(76, 175, 80, 0.8)';
        }
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 10000;
            max-width: 300px;
            transform: translateX(0);
            transition: transform 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
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

    // 🎵 获取错误报告
    getErrorReport() {
        return {
            errorCount: this.errorCount,
            errors: this.errorMessages,
            timestamp: new Date().toISOString()
        };
    }
}

// 🎵 初始化音乐错误处理器
document.addEventListener('DOMContentLoaded', () => {
    window.shirokiMusicErrorHandler = new ShirokiMusicErrorHandler();
    
    // 添加全局错误报告函数
    window.getShirokiMusicErrorReport = () => {
        return window.shirokiMusicErrorHandler.getErrorReport();
    };
});