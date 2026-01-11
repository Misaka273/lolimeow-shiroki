/**
 * 验证码功能脚本
 * Version: 2.0.0
 * Author: 初叶🍂
 */

class CaptchaManager {
    constructor() {
        this.ajaxUrl = window.ajax_object?.ajaxurl || '/wp-admin/admin-ajax.php';
        this.captchaType = 'normal';
        this.captchaEnabled = true;
        this.captchaLoginEnabled = true;
        this.captchaRegisterEnabled = true;
        this.isCloudflareInitialized = false;
        this.captchaWidgetId = null;
        this.lastSubmitTime = 0; // 防止快速重复提交
        this.submitLock = false; // 提交锁
    }
    
    init() {
        console.log('CaptchaManager 初始化，版本: 2.0.0');
        
        // 获取验证码设置
        this.getCaptchaSettings();
        
        // 检查是否启用了验证码
        if (!this.captchaEnabled) {
            console.log('验证码功能已禁用');
            return;
        }
        
        // 初始化事件监听
        this.initEventListeners();
        
        // 初始化Cloudflare Turnstile（如果需要）
        if (this.captchaType === 'cloudflare') {
            this.initCloudflareTurnstile();
        }
        
        // 验证HTML结构
        this.validateHTMLStructure();
    }
    
    getCaptchaSettings() {
        // 从页面元素获取设置
        const captchaMeta = document.querySelector('meta[name="captcha-settings"]');
        if (captchaMeta) {
            try {
                const settings = JSON.parse(captchaMeta.content);
                this.captchaType = settings.type || 'normal';
                this.captchaEnabled = settings.enabled !== false;
                this.captchaLoginEnabled = settings.loginEnabled !== false;
                this.captchaRegisterEnabled = settings.registerEnabled !== false;
                console.log('验证码设置:', {
                    type: this.captchaType,
                    enabled: this.captchaEnabled,
                    loginEnabled: this.captchaLoginEnabled,
                    registerEnabled: this.captchaRegisterEnabled
                });
            } catch (e) {
                console.warn('Failed to parse captcha settings:', e);
            }
        }
    }
    
    validateHTMLStructure() {
        // 检查必要的HTML元素
        if (this.captchaType === 'normal') {
            const captchaContainer = document.querySelector('.captcha-container');
            if (!captchaContainer) {
                console.warn('警告: 未找到验证码容器 (.captcha-container)');
                this.injectCaptchaHTML();
            }
        }
    }
    
    injectCaptchaHTML() {
        // 如果是登录页面且启用了登录验证码
        const loginForm = document.getElementById('loginform');
        if (loginForm && this.captchaLoginEnabled) {
            const captchaHTML = `
                <div class="captcha-container mb-3" style="display: ${this.captchaEnabled ? 'block' : 'none'}">
                    <label for="captcha-input" class="form-label">验证码</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" 
                               id="captcha-input" 
                               name="captcha_code" 
                               class="form-control captcha-input" 
                               placeholder="请输入验证码" 
                               required
                               maxlength="6"
                               autocomplete="off">
                        <img src="${this.ajaxUrl}?action=generate_captcha_image&t=${Date.now()}" 
                             class="captcha-image border rounded" 
                             alt="验证码" 
                             style="cursor: pointer; height: 38px;">
                        <button type="button" class="btn btn-outline-secondary captcha-refresh" style="height: 38px;">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="form-text">点击图片刷新验证码</div>
                    <input type="hidden" name="captcha_verified" value="0">
                </div>
            `;
            
            // 找到密码输入框后面插入
            const passwordInput = loginForm.querySelector('input[type="password"]');
            if (passwordInput) {
                const parentDiv = passwordInput.closest('.mb-3');
                if (parentDiv) {
                    parentDiv.insertAdjacentHTML('afterend', captchaHTML);
                }
            }
        }
    }
    
    initEventListeners() {
        console.log('初始化事件监听器');
        
        // 验证码刷新按钮
        document.addEventListener('click', (e) => {
            if (e.target.closest('.captcha-refresh')) {
                e.preventDefault();
                this.refreshCaptchaImage();
            }
            
            if (e.target.classList.contains('captcha-image') || 
                e.target.closest('.captcha-image')) {
                e.preventDefault();
                this.refreshCaptchaImage();
            }
        });
        
        // 表单提交验证 - 严格检查
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            // 登录表单验证
            if (form.id === 'loginform' && this.captchaEnabled && this.captchaLoginEnabled) {
                console.log('登录表单提交拦截');
                e.preventDefault();
                this.handleFormSubmit(form, 'login');
                return false;
            }
            
            // 注册表单验证
            if (form.id === 'signupform' && this.captchaEnabled && this.captchaRegisterEnabled) {
                console.log('注册表单提交拦截');
                e.preventDefault();
                this.handleFormSubmit(form, 'register');
                return false;
            }
            
            // 其他表单如果有验证码字段也要验证
            const hasCaptcha = form.querySelector('.captcha-input') || form.querySelector('#captcha-input');
            if (hasCaptcha && this.captchaEnabled) {
                console.log('其他表单提交拦截（包含验证码）');
                e.preventDefault();
                this.handleFormSubmit(form, 'other');
                return false;
            }
        }, true); // 使用捕获阶段确保优先执行
        
        // 发送验证码按钮
        const sendCodeBtn = document.getElementById('sendVerificationCode');
        if (sendCodeBtn) {
            sendCodeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSendVerificationCode();
            });
        }
        
        // 防止表单被其他方式提交
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                const form = activeElement.closest('form');
                
                if (form && (form.id === 'loginform' || form.id === 'signupform')) {
                    // 检查是否是提交按钮
                    if (activeElement.type !== 'submit' && !activeElement.closest('button[type="submit"]')) {
                        e.preventDefault();
                        console.log('阻止回车键直接提交表单');
                    }
                }
            }
        });
    }
    
    async handleFormSubmit(form, type) {
        console.log(`处理${type}表单提交`);
        
        // 防止重复提交
        if (this.submitLock) {
            console.log('表单正在提交中，请稍候');
            return;
        }
        
        // 防快速提交
        const now = Date.now();
        if (now - this.lastSubmitTime < 1000) {
            console.log('提交太频繁，请稍候');
            this.showErrorMessage('操作太频繁，请稍候再试');
            return;
        }
        this.lastSubmitTime = now;
        
        this.submitLock = true;
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.querySelector('.btn-text')?.textContent || submitBtn.textContent;
        
        // 禁用提交按钮
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text') && (submitBtn.querySelector('.btn-text').textContent = '验证中...');
        
        try {
            // 第一步：验证验证码（必须）
            const captchaValid = await this.validateCaptcha(form);
            
            if (!captchaValid) {
                throw new Error('验证码验证失败');
            }
            
            console.log('验证码验证通过');
            
            // 第二步：提交表单到服务器进行二次验证
            const isValid = await this.serverValidateForm(form, type);
            
            if (!isValid) {
                throw new Error('服务器验证失败');
            }
            
            console.log('服务器验证通过，提交表单');
            
            // 第三步：如果所有验证通过，允许表单提交
            this.submitLock = false;
            form.submit();
            
        } catch (error) {
            console.error('表单提交失败:', error);
            
            // 重新启用提交按钮
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text') && (submitBtn.querySelector('.btn-text').textContent = originalText);
            
            // 显示错误信息
            if (error.message === '验证码验证失败') {
                this.showErrorMessage('验证码验证失败，请重试');
            } else {
                this.showErrorMessage('提交失败，请检查网络或重试');
            }
            
            // 刷新验证码
            this.refreshCaptchaImage();
            
            this.submitLock = false;
        }
    }
    
    async serverValidateForm(form, type) {
        // 收集表单数据
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // 获取验证码
        const captchaInput = form.querySelector('.captcha-input') || form.querySelector('#captcha-input');
        const captchaCode = captchaInput ? captchaInput.value : '';
        
        // 构建验证请求
        const action = type === 'login' ? 'validate_login_with_captcha' : 'validate_register_with_captcha';
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: action,
                    captcha_code: captchaCode,
                    form_data: JSON.stringify(data),
                    nonce: this.getNonce(type)
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                console.error('服务器验证失败:', result.message);
                return false;
            }
            
            return true;
        } catch (error) {
            console.error('服务器验证请求失败:', error);
            return false;
        }
    }
    
    refreshCaptchaImage() {
        const captchaImg = document.querySelector('.captcha-image');
        if (captchaImg) {
            console.log('刷新验证码图片');
            
            // 添加加载效果
            captchaImg.style.opacity = '0.5';
            
            // 添加随机参数防止缓存
            const timestamp = Date.now();
            const random = Math.random().toString(36).substring(7);
            const newSrc = `${this.ajaxUrl}?action=generate_captcha_image&t=${timestamp}&r=${random}`;
            
            // 预加载图片
            const tempImg = new Image();
            tempImg.onload = () => {
                captchaImg.src = newSrc;
                captchaImg.style.opacity = '1';
                
                // 清空验证码输入框
                const captchaInput = document.querySelector('.captcha-input');
                if (captchaInput) {
                    captchaInput.value = '';
                    captchaInput.focus();
                }
                
                // 添加成功动画
                captchaImg.parentElement.classList.add('captcha-success');
                setTimeout(() => {
                    captchaImg.parentElement.classList.remove('captcha-success');
                }, 600);
            };
            tempImg.onerror = () => {
                captchaImg.style.opacity = '1';
                this.showErrorMessage('验证码加载失败，请重试');
            };
            tempImg.src = newSrc;
        }
    }
    
    async validateCaptcha(form) {
        console.log('开始验证验证码，类型:', this.captchaType);
        
        // 检查验证码是否启用
        if (!this.captchaEnabled) {
            console.log('验证码已禁用，跳过验证');
            return true;
        }
        
        const captchaType = this.captchaType;
        
        if (captchaType === 'cloudflare') {
            return await this.validateCloudflareCaptcha(form);
        } else {
            return await this.validateNormalCaptcha(form);
        }
    }
    
    async validateNormalCaptcha(form) {
        console.log('验证普通验证码');
        
        const captchaInput = form.querySelector('.captcha-input') || form.querySelector('#captcha-input');
        if (!captchaInput) {
            console.error('未找到验证码输入框');
            this.showErrorMessage('验证码系统错误，请刷新页面');
            return false;
        }
        
        const captchaCode = captchaInput.value.trim();
        
        // 空值检查
        if (!captchaCode) {
            this.showErrorMessage('请输入验证码');
            captchaInput.focus();
            captchaInput.classList.add('is-invalid');
            return false;
        }
        
        // 长度检查
        if (captchaCode.length < 4 || captchaCode.length > 10) {
            this.showErrorMessage('验证码长度不正确');
            captchaInput.focus();
            captchaInput.classList.add('is-invalid');
            return false;
        }
        
        // 格式检查（只允许字母和数字）
        if (!/^[A-Za-z0-9]+$/.test(captchaCode)) {
            this.showErrorMessage('验证码只能包含字母和数字');
            captchaInput.focus();
            captchaInput.classList.add('is-invalid');
            return false;
        }
        
        try {
            console.log('发送验证码验证请求:', captchaCode);
            
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'verify_captcha',
                    captcha_code: captchaCode,
                    nonce: this.getNonce(),
                    timestamp: Date.now()
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP错误: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('验证码验证响应:', data);
            
            if (data.success) {
                console.log('验证码验证成功');
                captchaInput.classList.remove('is-invalid');
                captchaInput.classList.add('is-valid');
                
                // 标记验证通过
                const verifiedInput = form.querySelector('input[name="captcha_verified"]');
                if (verifiedInput) {
                    verifiedInput.value = '1';
                }
                
                return true;
            } else {
                console.log('验证码验证失败:', data.message);
                this.showErrorMessage(data.message || '验证码错误');
                captchaInput.classList.add('is-invalid');
                
                // 刷新验证码
                this.refreshCaptchaImage();
                captchaInput.value = '';
                captchaInput.focus();
                
                return false;
            }
        } catch (error) {
            console.error('验证码验证失败:', error);
            this.showErrorMessage('验证码验证失败，请重试');
            return false;
        }
    }
    
    async validateCloudflareCaptcha(form) {
        console.log('验证Cloudflare验证码');
        
        if (typeof turnstile === 'undefined') {
            this.showErrorMessage('验证服务加载失败，请刷新页面');
            return false;
        }
        
        return new Promise((resolve) => {
            if (this.captchaWidgetId) {
                // 重置验证码
                turnstile.reset(this.captchaWidgetId);
            }
            
            // 渲染验证码
            this.captchaWidgetId = turnstile.render('#captcha-widget', {
                sitekey: this.getCloudflareSiteKey(),
                callback: (token) => {
                    console.log('Cloudflare回调收到token');
                    this.verifyCloudflareToken(token).then(resolve);
                },
                'expired-callback': () => {
                    console.log('Cloudflare验证过期');
                    this.showErrorMessage('验证已过期，请重新验证');
                    resolve(false);
                },
                'error-callback': () => {
                    console.log('Cloudflare验证错误');
                    this.showErrorMessage('验证失败，请重试');
                    resolve(false);
                },
                theme: this.isDarkMode() ? 'dark' : 'light'
            });
        });
    }
    
    async verifyCloudflareToken(token) {
        if (!token) {
            this.showErrorMessage('请完成人机验证');
            return false;
        }
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'verify_cloudflare_captcha',
                    cf_response: token,
                    nonce: this.getNonce()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('Cloudflare验证成功');
                return true;
            } else {
                console.log('Cloudflare验证失败:', data.message);
                this.showErrorMessage(data.message || '人机验证失败');
                return false;
            }
        } catch (error) {
            console.error('Cloudflare验证失败:', error);
            this.showErrorMessage('验证服务异常，请重试');
            return false;
        }
    }
    
    async handleSendVerificationCode() {
        console.log('处理发送验证码');
        
        const emailInput = document.getElementById('signupEmailInput');
        const sendBtn = document.getElementById('sendVerificationCode');
        
        if (!emailInput || !sendBtn) return;
        
        const email = emailInput.value.trim();
        if (!email) {
            this.showErrorMessage('请输入邮箱地址');
            return;
        }
        
        // 验证邮箱格式
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showErrorMessage('请输入有效的邮箱地址');
            return;
        }
        
        // 防止重复点击
        if (sendBtn.disabled) {
            return;
        }
        
        // 保存原始文本
        const originalText = sendBtn.textContent;
        
        // 先验证验证码（如果需要）
        const form = document.getElementById('signupform');
        if (this.captchaEnabled && this.captchaRegisterEnabled) {
            const isValid = await this.validateCaptcha(form);
            if (!isValid) {
                return;
            }
        }
        
        // 禁用发送按钮
        sendBtn.disabled = true;
        sendBtn.textContent = '发送中...';
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'send_verification_code',
                    email: email,
                    nonce: this.getNonce('send_verification_code'),
                    captcha_verified: '1' // 标记已通过验证码验证
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccessMessage(data.data?.message || '验证码已发送到您的邮箱');
                this.startCountdown(sendBtn);
            } else {
                this.showErrorMessage(data.data?.message || '发送失败，请重试');
                sendBtn.disabled = false;
                sendBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('发送验证码失败:', error);
            this.showErrorMessage('发送失败，请重试');
            sendBtn.disabled = false;
            sendBtn.textContent = originalText;
        }
    }
    
    startCountdown(button) {
        let countdown = 60;
        const interval = setInterval(() => {
            button.textContent = `${countdown}秒后重试`;
            countdown--;
            
            if (countdown < 0) {
                clearInterval(interval);
                button.disabled = false;
                button.textContent = '获取验证码';
            }
        }, 1000);
    }
    
    showErrorMessage(message) {
        this.showMessage(message, 'danger');
    }
    
    showSuccessMessage(message) {
        this.showMessage(message, 'success');
    }
    
    showMessage(message, type) {
        console.log(`显示${type}消息:`, message);
        
        // 查找现有的消息容器或创建新的
        let messageContainer = document.querySelector('.captcha-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'captcha-message';
            const captchaContainer = document.querySelector('.captcha-container') || document.querySelector('form');
            if (captchaContainer) {
                captchaContainer.prepend(messageContainer);
            } else {
                document.body.appendChild(messageContainer);
            }
        }
        
        const alertClass = type === 'success' ? 'alert alert-success' : 'alert alert-danger';
        messageContainer.innerHTML = `
            <div class="${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // 5秒后自动消失
        setTimeout(() => {
            const alert = messageContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 300);
            }
        }, 5000);
    }
    
    initCloudflareTurnstile() {
        if (this.isCloudflareInitialized) return;
        
        const siteKey = this.getCloudflareSiteKey();
        if (!siteKey) {
            console.error('Cloudflare Site Key未配置');
            return;
        }
        
        // 等待turnstile库加载
        if (typeof turnstile === 'undefined') {
            console.log('等待Cloudflare Turnstile库加载');
            setTimeout(() => this.initCloudflareTurnstile(), 500);
            return;
        }
        
        // 初始化登录验证码
        const loginWidget = document.getElementById('login-captcha-widget');
        if (loginWidget) {
            turnstile.render(loginWidget, {
                sitekey: siteKey,
                theme: this.isDarkMode() ? 'dark' : 'light'
            });
            console.log('Cloudflare登录验证码初始化完成');
        }
        
        // 初始化注册验证码
        const registerWidget = document.getElementById('register-captcha-widget');
        if (registerWidget) {
            turnstile.render(registerWidget, {
                sitekey: siteKey,
                theme: this.isDarkMode() ? 'dark' : 'light'
            });
            console.log('Cloudflare注册验证码初始化完成');
        }
        
        this.isCloudflareInitialized = true;
    }
    
    getCloudflareSiteKey() {
        const meta = document.querySelector('meta[name="captcha-cloudflare-sitekey"]');
        return meta ? meta.content : '';
    }
    
    getNonce(type = '') {
        // 根据表单类型获取nonce
        switch(type) {
            case 'login':
                return document.querySelector('input[name="login_nonce"]')?.value || '';
            case 'register':
                return document.querySelector('input[name="signup_nonce"]')?.value || '';
            case 'send_verification_code':
                return document.querySelector('input[name="signup_nonce"]')?.value || '';
            default:
                // 尝试查找任何nonce
                return document.querySelector('input[name="login_nonce"]')?.value || 
                       document.querySelector('input[name="signup_nonce"]')?.value ||
                       document.querySelector('input[name="_wpnonce"]')?.value || '';
        }
    }
    
    isDarkMode() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
               window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
}

// 全局实例
let captchaManager = null;

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM加载完成，初始化验证码系统');
    
    // 创建管理器实例
    captchaManager = new CaptchaManager();
    
    // 检查是否启用了验证码
    const captchaEnabled = typeof get_boxmoe !== 'undefined' ? get_boxmoe('captcha_enabled') === '1' : true;
    
    if (captchaEnabled) {
        captchaManager.init();
        
        // 全局导出以便调试
        window.captchaManager = captchaManager;
        
        console.log('验证码系统初始化完成');
    } else {
        console.log('验证码功能已禁用，跳过初始化');
    }
});

// 导出为模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CaptchaManager;
}