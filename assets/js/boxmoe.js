"use strict";
// 主题初始化
var theme = {
	init: function() {
        theme.menu(), 
        theme.otpVarification(), 
        theme.popovers(), 
        theme.tooltip(), 
        theme.validation()
	},
	menu: () => {
		document.querySelectorAll(".dropdown-menu a.dropdown-toggle")
			.forEach((function(e) {
				e.addEventListener("click", (function(e) {
					if (!this.nextElementSibling.classList.contains("show")) {
						this.closest(".dropdown-menu")
							.querySelectorAll(".show")
							.forEach((function(e) {
								e.classList.remove("show")
							}))
					}
					this.nextElementSibling.classList.toggle("show");
					const t = this.closest("li.nav-item.dropdown.show");
					t && t.addEventListener("hidden.bs.dropdown", (function(e) {
						document.querySelectorAll(".dropdown-submenu .show")
							.forEach((function(e) {
								e.classList.remove("show")
							}))
					})), e.stopPropagation()
				}))
			}))
	},
	popovers: () => {
		[...document.querySelectorAll('[data-bs-toggle="popover"]')].map((e => new bootstrap.Popover(e)))
	},
	tooltip: () => {
		[...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map((e => new bootstrap.Tooltip(e)))
	},
	validation: () => {
		const e = document.querySelectorAll(".needs-validation:not(#loginform)");
		Array.from(e)
			.forEach((e => {
				e.addEventListener("submit", (t => {
					e.checkValidity() || (t.preventDefault(), t.stopPropagation()), e.classList.add("was-validated")
				}), !1)
			}))
	},
	otpVarification: () => {
		document.moveToNextInput = function(e) {
			if (e.value.length === e.maxLength) {
				const t = Array.from(e.parentElement.children)
					.indexOf(e),
					n = e.parentElement.children[t + 1];
				n && n.focus()
			}
		}
	}
};
theme.init();

var navbar = document.querySelector(".navbar");
const navOffCanvasBtn = document.querySelectorAll(".offcanvas-nav-btn"),
    navOffCanvas = document.querySelector(".navbar:not(.navbar-clone) .offcanvas-nav");
let bsOffCanvas;
function toggleOffCanvas() {
    if (bsOffCanvas) {
        if (bsOffCanvas._isShown) {
            bsOffCanvas.hide();
            // 隐藏时移除active类
            navOffCanvasBtn.forEach(btn => btn.classList.remove("active"));
        } else {
            bsOffCanvas.show();
            // 显示时添加active类
            navOffCanvasBtn.forEach(btn => btn.classList.add("active"));
        }
    }
}
navOffCanvas && (bsOffCanvas = new bootstrap.Offcanvas(navOffCanvas, {
    scroll: !0,
    backdrop: true
}), navOffCanvasBtn.forEach((e => {
    e.addEventListener("click", (e => {
        toggleOffCanvas()
    }))
})));

// 监听Offcanvas的显示/隐藏事件，同步按钮状态
navOffCanvas && (navOffCanvas.addEventListener('show.bs.offcanvas', function () {
    navOffCanvasBtn.forEach(btn => btn.classList.add("active"));
}), navOffCanvas.addEventListener('hide.bs.offcanvas', function () {
    navOffCanvasBtn.forEach(btn => btn.classList.remove("active"));
}));
function showToast(message, isSuccess = true) {
    const toastId = 'toast-' + Date.now();
    // 动态读取当前网站设置的Favicon地址
    let siteLogo = '${window.ajax_object.themeurl}/assets/images/msg-tip.png'; // 默认图标
    const faviconLink = document.querySelector('link[rel*="icon"]');
    if (faviconLink && faviconLink.href) {
        siteLogo = faviconLink.href;
    }
    // 获取复制的实际内容，显示为"{文本}"
    const copyContent = '' + message;
    
    // 漫画风背景样式
    const comicBackground = `
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-family: 'Comic Sans MS', cursive, sans-serif;
    `;
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="${comicBackground}">
            <div class="toast-header" style="background: rgba(255, 255, 255, 0.8); border-bottom: none;">
                <img src="${siteLogo}" class="rounded me-2 avatar-xs" alt="网站logo">
                <strong class="me-auto" style="color: #ff6b6b;">温馨提示</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="color: #2d3436;">
                ${copyContent}
            </div>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }   
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    // 移除默认背景类，使用自定义漫画风背景
    toastElement.className = `toast align-items-center border-0`;
    
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
// 搜索框初始化
function initSearchBox() {
    const searchBtns = document.querySelectorAll('.search-btn, .mobile-search-btn');
    const searchForms = document.querySelectorAll('.search-form, .mobile-search-form');
    
    searchBtns.forEach((btn, index) => {
        const form = searchForms[index];
        const input = form.querySelector('input[type="search"]');
        
        if (btn && form && input) {
            btn.addEventListener('click', function(e) {
                if (!form.classList.contains('active')) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('active');
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }
            });

            form.addEventListener('submit', function(e) {
                if (!input.value.trim()) {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', function(e) {
                if (!form.contains(e.target) && !btn.contains(e.target)) {
                    form.classList.remove('active');
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    form.classList.remove('active');
                    input.blur();
                }
            });
        }
    });
}
// 📱 移动端用户按钮初始化
function initMobileUserPanel() {
    const mobileUserBtn = document.querySelector('.mobile-user-btn');
    if(!mobileUserBtn) return;
    
    // 移除所有现有事件监听器（防止多次绑定）
    const newBtn = mobileUserBtn.cloneNode(true);
    mobileUserBtn.parentNode.replaceChild(newBtn, mobileUserBtn);
    
    // 重新获取元素
    const mobileUserBtnEl = newBtn;
    
    // 最可靠的登录状态检测：使用header中添加的元数据
    const isLoggedIn = () => {
        // 优先使用header中添加的明确登录状态元数据（PHP直接输出，最可靠）
        const loggedInMeta = document.querySelector('meta[name="logged-in"]');
        if (loggedInMeta) {
            return loggedInMeta.content === 'true';
        }
        
        // 次优选择：检查WordPress的body类名
        if (document.body.classList.contains('logged-in')) {
            return true;
        }
        
        // 检查是否存在移动端登录用户包装器
        const mobileLoggedWrapper = document.querySelector('.mobile-logged-user-wrapper');
        if (mobileLoggedWrapper) {
            return true;
        }
        
        // 以上都不满足，视为未登录
        return false;
    };
    
    // 检查是否已登录
    if (isLoggedIn()) {
        // 创建下拉菜单容器
        let dropdownMenu = document.querySelector('.mobile-user-dropdown-menu');
        if (!dropdownMenu) {
            dropdownMenu = document.createElement('div');
            dropdownMenu.className = 'mobile-user-dropdown-menu';
            dropdownMenu.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.15);
                z-index: 99999;
                min-width: 220px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid rgba(0,0,0,0.1);
                overflow: hidden;
            `;
            document.body.appendChild(dropdownMenu);
        }
        
        // 检查是否为管理员
        const isAdmin = () => {
            return document.querySelector('.dropdown-menu a[href*="wp-admin"]') !== null ||
                   document.querySelector('.logged-user-wrapper .dropdown-menu a[href*="wp-admin"]') !== null;
        };
        
        // 获取链接的可靠方法
        const getUserCenterLink = () => {
            if (typeof boxmoe_user_center_link_page === 'function') {
                return boxmoe_user_center_link_page();
            }
            const metaLink = document.querySelector('meta[name="user-center-url"]');
            if (metaLink) {
                return metaLink.content;
            }
            // 从页面现有链接提取
            const existingLink = document.querySelector('a[href*="user-center"], .dropdown-item[href*="user-center"]');
            if (existingLink) {
                return existingLink.href;
            }
            return '/user-center/';
        };
        
        const getAdminLink = () => {
            return window.ajax_object?.adminurl || '/wp-admin/';
        };
        
        const getLogoutLink = () => {
            if (typeof wp_logout_url === 'function') {
                return wp_logout_url(home_url());
            }
            const existingLink = document.querySelector('a[href*="logout"], .dropdown-item[href*="logout"]');
            if (existingLink) {
                return existingLink.href;
            }
            return '/wp-login.php?action=logout';
        };
        
        // 渲染菜单内容
        const renderMenu = () => {
            // 检查是否存在用户中心页面模板
            const userCenterExists = document.querySelector('meta[name="user-center-exists"]')?.content === 'true';
            const userCenterLink = getUserCenterLink();
            const adminLink = getAdminLink();
            const logoutLink = getLogoutLink();
            const admin = isAdmin();
            
            dropdownMenu.innerHTML = `
                <div class="mobile-dropdown-content" style="padding: 0;">
                <!-- 新增检查用户中心页面是否存在 -->
                    ${userCenterExists ? `<a href="${userCenterLink}" class="mobile-dropdown-item" style="
                        display: flex;
                        align-items: center;
                        padding: 14px 20px;
                        color: #333;
                        text-decoration: none;
                        font-size: 15px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        background: transparent;
                        border: none;
                        width: 100%;
                        text-align: left;
                        box-sizing: border-box;
                    ">
                        <i class="fa fa-user-circle" style="margin-right: 14px; width: 20px; text-align: center; color: #6b7280;"></i>
                        <span>会员中心</span>
                    </a>` : ''}
                    ${admin ? `
                        <a href="${adminLink}" class="mobile-dropdown-item" style="
                            display: flex;
                            align-items: center;
                            padding: 14px 20px;
                            color: #333;
                            text-decoration: none;
                            font-size: 15px;
                            font-weight: 500;
                            transition: all 0.2s ease;
                            background: transparent;
                            border: none;
                            width: 100%;
                            text-align: left;
                            box-sizing: border-box;
                        ">
                            <i class="fa fa-cog" style="margin-right: 14px; width: 20px; text-align: center; color: #6b7280;"></i>
                            <span>后台管理</span>
                        </a>
                    ` : ''}
                    <a href="${logoutLink}" class="mobile-dropdown-item" style="
                        display: flex;
                        align-items: center;
                        padding: 14px 20px;
                        color: #333;
                        text-decoration: none;
                        font-size: 15px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        background: transparent;
                        border: none;
                        width: 100%;
                        text-align: left;
                        box-sizing: border-box;
                    ">
                        <i class="fa fa-sign-out" style="margin-right: 14px; width: 20px; text-align: center; color: #6b7280;"></i>
                        <span>注销登录</span>
                    </a>
                </div>
            `;
            
            // 添加悬停效果
            const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
            items.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = '#f3f4f6';
                });
                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = 'transparent';
                });
            });
        };
        
        // 初始渲染
        renderMenu();
        
        // 切换菜单显示/隐藏
        const toggleMenu = () => {
            const isVisible = dropdownMenu.style.visibility === 'visible';
            
            if (isVisible) {
                // 关闭菜单
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            } else {
                // 重新渲染菜单（确保链接最新）
                renderMenu();
                // 显示菜单
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.transform = 'translateY(0)';
            }
        };
        
        // 点击按钮切换菜单
        mobileUserBtnEl.addEventListener('click', (e) => {
            e.stopPropagation(); // 阻止事件冒泡
            e.preventDefault(); // 阻止默认行为
            toggleMenu();
        }, false);
        
        // 点击外部关闭菜单
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !mobileUserBtnEl.contains(e.target)) {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            }
        }, false);
        
        // ESC键关闭菜单
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            }
        }, false);
        
        // 暗色模式适配
        const applyDarkMode = () => {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                          document.body.classList.contains('dark-theme');
            
            if (isDark) {
                dropdownMenu.style.backgroundColor = '#1f2937';
                dropdownMenu.style.borderColor = 'rgba(255,255,255,0.1)';
                
                const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
                items.forEach(item => {
                    item.style.color = '#f9fafb';
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = '#374151';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.backgroundColor = 'transparent';
                    });
                });
            } else {
                dropdownMenu.style.backgroundColor = 'white';
                dropdownMenu.style.borderColor = 'rgba(0,0,0,0.1)';
                
                const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
                items.forEach(item => {
                    item.style.color = '#374151';
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = '#f3f4f6';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.backgroundColor = 'transparent';
                    });
                });
            }
        };
        
        // 初始应用暗色模式
        applyDarkMode();
        
        // 监听暗色模式变化
        const observer = new MutationObserver(applyDarkMode);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
        
    } else {
        // 未登录用户：显示下拉菜单
        // 创建下拉菜单容器
        let dropdownMenu = document.querySelector('.mobile-user-dropdown-menu');
        if (!dropdownMenu) {
            dropdownMenu = document.createElement('div');
            dropdownMenu.className = 'mobile-user-dropdown-menu';
            dropdownMenu.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.15);
                z-index: 99999;
                min-width: 220px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid rgba(0,0,0,0.1);
                overflow: hidden;
            `;
            document.body.appendChild(dropdownMenu);
        }
        
        // 获取登录和注册链接
        const getLoginLink = () => {
            if (typeof boxmoe_sign_in_link_page === 'function') {
                return boxmoe_sign_in_link_page();
            }
            const metaLink = document.querySelector('meta[name="login-url"]');
            if (metaLink) {
                return metaLink.content;
            }
            return '/signin/';
        };
        
        const getRegisterLink = () => {
            if (typeof boxmoe_sign_up_link_page === 'function') {
                return boxmoe_sign_up_link_page();
            }
            const metaLink = document.querySelector('meta[name="register-url"]');
            if (metaLink) {
                return metaLink.content;
            }
            return '/signup/';
        };
        
        // 渲染未登录用户菜单
        const renderGuestMenu = () => {
            const loginLink = getLoginLink();
            const registerLink = getRegisterLink();
            
            dropdownMenu.innerHTML = `
                <div class="mobile-dropdown-content" style="padding: 0;">
                    <a href="${loginLink}" class="mobile-dropdown-item" style="
                        display: flex;
                        align-items: center;
                        padding: 14px 20px;
                        color: #333;
                        text-decoration: none;
                        font-size: 15px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        background: transparent;
                        border: none;
                        width: 100%;
                        text-align: left;
                        box-sizing: border-box;
                    ">
                        <i class="fa fa-sign-in" style="margin-right: 14px; width: 20px; text-align: center; color: #6b7280;"></i>
                        <span>登录</span>
                    </a>
                    <a href="${registerLink}" class="mobile-dropdown-item" style="
                        display: flex;
                        align-items: center;
                        padding: 14px 20px;
                        color: #333;
                        text-decoration: none;
                        font-size: 15px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        background: transparent;
                        border: none;
                        width: 100%;
                        text-align: left;
                        box-sizing: border-box;
                    ">
                        <i class="fa fa-user-plus" style="margin-right: 14px; width: 20px; text-align: center; color: #6b7280;"></i>
                        <span>注册</span>
                    </a>
                </div>
            `;
            
            // 添加悬停效果
            const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
            items.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = '#f3f4f6';
                });
                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = 'transparent';
                });
            });
        };
        
        // 初始渲染
        renderGuestMenu();
        
        // 切换菜单显示/隐藏
        const toggleMenu = () => {
            const isVisible = dropdownMenu.style.visibility === 'visible';
            
            if (isVisible) {
                // 关闭菜单
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            } else {
                // 重新渲染菜单
                renderGuestMenu();
                // 显示菜单
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.transform = 'translateY(0)';
            }
        };
        
        // 点击按钮切换菜单
        mobileUserBtnEl.addEventListener('click', (e) => {
            e.stopPropagation(); // 阻止事件冒泡
            e.preventDefault(); // 阻止默认行为
            toggleMenu();
        }, false);
        
        // 点击外部关闭菜单
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !mobileUserBtnEl.contains(e.target)) {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            }
        }, false);
        
        // ESC键关闭菜单
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            }
        }, false);
        
        // 暗色模式适配
        const applyDarkMode = () => {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                          document.body.classList.contains('dark-theme');
            
            if (isDark) {
                dropdownMenu.style.backgroundColor = '#1f2937';
                dropdownMenu.style.borderColor = 'rgba(255,255,255,0.1)';
                
                const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
                items.forEach(item => {
                    item.style.color = '#f9fafb';
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = 'rgba(255,255,255,0.1)';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.backgroundColor = 'transparent';
                    });
                });
            } else {
                dropdownMenu.style.backgroundColor = 'white';
                dropdownMenu.style.borderColor = 'rgba(0,0,0,0.1)';
                
                const items = dropdownMenu.querySelectorAll('.mobile-dropdown-item');
                items.forEach(item => {
                    item.style.color = '#374151';
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = '#f3f4f6';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.backgroundColor = 'transparent';
                    });
                });
            }
        };
        
        // 初始应用暗色模式
        applyDarkMode();
        
        // 监听暗色模式变化
        const observer = new MutationObserver(applyDarkMode);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
    }
}

// 🎨 优化的懒加载初始化 - 集成静默预加载
function initLazyLoad(container = document) {
    const lazyImages = container.querySelectorAll('img.lazy');
    
    const loadImage = async (img) => {
        // 如果图片已经加载过，直接返回
        if (img.classList.contains('loaded')) {
            return;
        }
        
        let ds = img.dataset && img.dataset.src ? img.dataset.src : '';
        if (!ds) {
            const attrs = ['original','lazy','lazySrc','srcLazy'];
            for (let i=0;i<attrs.length;i++){ const k = 'data-'+attrs[i].replace(/[A-Z]/g, m => '-' + m.toLowerCase()); const v = img.getAttribute(k); if (v) { ds = v; break; } }
            if (!ds && (img.getAttribute('src')||'').includes('/assets/images/loading.gif')) {
                const a = img.closest('a');
                const ah = a ? (a.getAttribute('data-src') || a.getAttribute('href') || '') : '';
                if (/\.(?:jpe?g|png|webp|gif)(\?.*)?$/i.test(ah)) ds = ah;
            }
        }
        if (!ds) { img.classList.remove('lazy'); return; }
        
        let base = ds, query = '';
        const qm = base.match(/^(.*?)(\?.*)$/);
        if (qm) { base = qm[1]; query = qm[2]; }
        if (/\.gif$/i.test(base)) { base = base.replace(/-\d+x\d+(?=\.gif$)/i, ''); }
        const fixed = base + (query || '');
        
        if (img.hasAttribute('srcset')) img.removeAttribute('srcset');
        if (img.hasAttribute('sizes')) img.removeAttribute('sizes');
        
        // 添加占位符
        const imgContainer = img.parentElement;
        if (imgContainer && !imgContainer.querySelector('.shiroki-image-placeholder')) {
            const placeholder = document.createElement('div');
            placeholder.className = 'shiroki-image-placeholder';
            imgContainer.appendChild(placeholder);
        }
        
        // 使用静默预加载器
        try {
            await shirokiImageLoader.preloadImage(fixed);
            img.src = fixed;
            shirokiImageLoader.revealImage(img);
        } catch (error) {
            console.warn('LazyLoad: Failed to preload image:', fixed, error);
            // 降级到原始加载方式
            const onLoad = () => { 
                img.classList.add('loaded'); 
                img.classList.remove('lazy'); 
                img.removeEventListener('load', onLoad); 
            };
            const onError = () => { 
                img.classList.add('loaded'); 
                img.classList.remove('lazy'); 
                img.removeAttribute('loading'); 
                img.removeEventListener('error', onError); 
                if (img.getAttribute('src') !== fixed) img.setAttribute('src', fixed); 
            };
            img.addEventListener('load', onLoad);
            img.addEventListener('error', onError);
            img.src = fixed;
        }
    };
    
    const forceLoadAll = () => {
        document.querySelectorAll('img.lazy').forEach(loadImage);
    };
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    loadImage(img);
                    observer.unobserve(img);
                }
            });
        }, { 
            rootMargin: '200px 0px', 
            threshold: 0.01 
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
        
        const mo = new MutationObserver((mutations) => {
            mutations.forEach(m => {
                m.addedNodes && m.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.querySelectorAll) {
                        node.querySelectorAll('img.lazy').forEach(img => imageObserver.observe(img));
                    }
                });
            });
        });
        
        mo.observe(document.body, { childList: true, subtree: true });
        setTimeout(forceLoadAll, 2000);
        window.addEventListener('load', forceLoadAll, { once: true });
        
        window.addEventListener('scroll', () => {
            document.querySelectorAll('img.lazy').forEach(img => {
                const rect = img.getBoundingClientRect();
                if (rect.top < window.innerHeight + 300) loadImage(img);
            });
        });
    } else {
        lazyImages.forEach(loadImage);
    }
}

// 🎨 静默图片预加载器
class ShirokiImageLoader {
    constructor() {
        this.loadingImages = new Set();
        this.loadedImages = new Set();
        this.cache = new Map();
        this.maxConcurrent = 6; // 最大并发加载数
        this.currentLoading = 0;
        this.queue = [];
    }

    // 处理加载队列
    processQueue() {
        while (this.currentLoading < this.maxConcurrent && this.queue.length > 0) {
            const task = this.queue.shift();
            this.currentLoading++;
            this.executeLoad(task);
        }
    }

    // 执行图片加载
    executeLoad(task) {
        const { src, resolve, reject } = task;
        
        // 记录开始时间
        imageLoadMonitor.recordStart(src);
        
        // 检查缓存
        if (this.cache.has(src)) {
            const cached = this.cache.get(src);
            this.currentLoading--;
            imageLoadMonitor.recordComplete(src, cached.success);
            if (cached.success) {
                resolve();
            } else {
                reject(cached.error);
            }
            this.processQueue();
            return;
        }

        const img = new Image();
        
        img.onload = () => {
            this.loadingImages.delete(src);
            this.loadedImages.add(src);
            this.cache.set(src, { success: true, timestamp: Date.now() });
            this.currentLoading--;
            imageLoadMonitor.recordComplete(src, true);
            resolve();
            this.processQueue();
        };

        img.onerror = () => {
            this.loadingImages.delete(src);
            const error = new Error(`Failed to load image: ${src}`);
            this.cache.set(src, { success: false, error, timestamp: Date.now() });
            this.currentLoading--;
            imageLoadMonitor.recordComplete(src, false);
            reject(error);
            this.processQueue();
        };

        img.src = src;
    }

    // 预加载单张图片
    preloadImage(src) {
        return new Promise((resolve, reject) => {
            if (this.loadedImages.has(src)) {
                resolve();
                return;
            }

            if (this.loadingImages.has(src)) {
                // 如果正在加载，等待完成
                const checkInterval = setInterval(() => {
                    if (this.loadedImages.has(src)) {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 50);
                return;
            }

            this.loadingImages.add(src);
            
            // 添加到队列
            this.queue.push({ src, resolve, reject });
            this.processQueue();
        });
    }

    // 批量预加载图片
    async preloadImages(srcs) {
        const promises = srcs.map(src => this.preloadImage(src));
        return Promise.allSettled(promises);
    }

    // 预加载可见区域的图片
    preloadVisibleImages() {
        const images = document.querySelectorAll('img[data-src]:not(.loaded)');
        const visibleImages = [];
        
        images.forEach(img => {
            const rect = img.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight + 200 && rect.bottom > -200;
            if (isVisible) {
                const src = img.dataset.src || img.src;
                if (src) visibleImages.push(src);
            }
        });

        if (visibleImages.length > 0) {
            return this.preloadImages(visibleImages);
        }
        
        return Promise.resolve([]);
    }

    // 清理过期缓存
    cleanupCache() {
        const now = Date.now();
        const maxAge = 5 * 60 * 1000; // 5分钟
        
        for (const [src, data] of this.cache.entries()) {
            if (now - data.timestamp > maxAge) {
                this.cache.delete(src);
            }
        }
    }

    // 静默加载图片元素
    async loadImageElement(imgElement) {
        const src = imgElement.src || imgElement.dataset.src;
        if (!src) return;

        try {
            await this.preloadImage(src);
            this.revealImage(imgElement);
        } catch (error) {
            console.warn('ShirokiImageLoader: Failed to load image:', src, error);
            this.revealImage(imgElement); // 即使失败也显示
        }
    }

    // 渐显图片 - 优化无限加载时的显示效果
    revealImage(imgElement) {
        // 如果图片已经加载过，不再重复处理
        if (imgElement.classList.contains('loaded')) {
            return;
        }
        
        // 添加加载完成的类
        imgElement.classList.add('loaded');
        
        // 确保图片有正确的显示状态
        imgElement.style.opacity = '1';
        imgElement.style.transform = 'scale(1)';
        
        // 移除占位符
        const placeholder = imgElement.parentElement.querySelector('.shiroki-image-placeholder');
        if (placeholder) {
            setTimeout(() => {
                placeholder.classList.add('loaded');
                setTimeout(() => placeholder.remove(), 300);
            }, 100);
        }
    }
}

// 创建全局图片加载器实例
const shirokiImageLoader = new ShirokiImageLoader();

// 🎨 图片加载性能监控
class ImageLoadMonitor {
    constructor() {
        this.metrics = {
            totalImages: 0,
            loadedImages: 0,
            failedImages: 0,
            startTime: Date.now(),
            loadTimes: []
        };
        this.enabled = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    }

    recordStart(src) {
        if (!this.enabled) return;
        this.metrics.totalImages++;
        this.metrics[src] = { startTime: Date.now() };
    }

    recordComplete(src, success = true) {
        if (!this.enabled) return;
        
        const imageMetrics = this.metrics[src];
        if (imageMetrics && imageMetrics.startTime) {
            const loadTime = Date.now() - imageMetrics.startTime;
            this.metrics.loadTimes.push(loadTime);
            
            if (success) {
                this.metrics.loadedImages++;
            } else {
                this.metrics.failedImages++;
            }
            
        }
    }

    getStats() {
        if (!this.enabled) return null;
        
        const avgLoadTime = this.metrics.loadTimes.length > 0 
            ? Math.round(this.metrics.loadTimes.reduce((a, b) => a + b, 0) / this.metrics.loadTimes.length)
            : 0;
        
        return {
            total: this.metrics.totalImages,
            loaded: this.metrics.loadedImages,
            failed: this.metrics.failedImages,
            successRate: Math.round((this.metrics.loadedImages / this.metrics.totalImages) * 100),
            avgLoadTime: avgLoadTime,
            totalTime: Date.now() - this.metrics.startTime
        };
    }

    printStats() {
        if (!this.enabled) return;
        
        const stats = this.getStats();
        if (stats && stats.total > 0) {
        }
    }
}

// 创建性能监控实例
const imageLoadMonitor = new ImageLoadMonitor();

// 定期清理缓存
setInterval(() => {
    shirokiImageLoader.cleanupCache();
}, 60 * 1000); // 每分钟清理一次

// 页面加载完成后输出统计
window.addEventListener('load', () => {
    setTimeout(() => {
        imageLoadMonitor.printStats();
    }, 3000);
});

// 🎨 简化的Banner图片加载
function initBannerImage() {
    const bannerImg = document.querySelector('.boxmoe_header_banner_img');
    const siteMain = document.querySelector('.boxmoe_header_banner .site-main');
    if (!bannerImg || !siteMain) return;
    
    const img = bannerImg.querySelector('img');
    if (!img) return;

    // 确保Banner容器初始可见
    bannerImg.style.visibility = 'visible';
    bannerImg.style.opacity = '1';
    bannerImg.style.transform = 'scale(1)';

    // 添加占位符
    const placeholder = document.createElement('div');
    placeholder.className = 'shiroki-image-placeholder';
    bannerImg.appendChild(placeholder);

    // 简单的加载完成处理
    const handleLoad = () => {
        // 显示banner容器
        bannerImg.classList.add('loaded');
        // 添加淡入类，让原始图片可见
        img.classList.add('shiroki-fade-in');
        
        // 延迟隐藏占位符
        setTimeout(() => {
            placeholder.classList.add('loaded');
            setTimeout(() => placeholder.remove(), 300);
        }, 100);
        
        // 延迟显示内容
        setTimeout(() => {
            siteMain.classList.add('loaded');
        }, 400);
    };

    // 如果图片已经加载完成
    if (img.complete) {
        handleLoad();
    } else {
        // 监听图片加载完成
        img.addEventListener('load', handleLoad);
        img.addEventListener('error', handleLoad);
    }
}

// 🔄 Banner随机图片自动切换功能
function initBannerRandomSwitch() {
    const bannerImg = document.querySelector('.boxmoe_header_banner_img');
    const originalImg = bannerImg ? bannerImg.querySelector('img') : null;
    
    if (!bannerImg || !originalImg) return;
    
    // 检查Banner模式
    if (!window.shirokiBannerMode) return;
    
    // 确保原始图片有正确的初始样式
    originalImg.style.position = 'absolute';
    originalImg.style.top = '0';
    originalImg.style.left = '0';
    originalImg.style.width = '100%';
    originalImg.style.height = '100%';
    originalImg.style.objectFit = 'cover';
    
    let currentIndex = 0;
    let isSwitching = false;
    let currentImg = originalImg;
    let nextImageLoader = null;
    const switchInterval = 8000; // 8秒切换一次Banner图片
    
    // 获取下一张图片URL
    function getNextImageUrl() {
        if (window.shirokiBannerMode === 'api') {
            // API模式：每次请求API URL（添加时间戳避免缓存）
            const apiUrl = window.shirokiBannerData.apiUrl || '';
            if (apiUrl) {
                const separator = apiUrl.includes('?') ? '&' : '?';
                return apiUrl + separator + 't=' + Date.now();
            }
            return null;
        } else if (window.shirokiBannerMode === 'local') {
            // 本地模式：从图片列表中循环获取
            const images = window.shirokiBannerData.images || [];
            if (images.length < 2) return null;
            
            currentIndex = (currentIndex + 1) % images.length;
            return images[currentIndex];
        }
        return null;
    }
    
    // 预加载下一张图片
    function preloadNextImage() {
        if (nextImageLoader) {
            nextImageLoader.onload = null;
            nextImageLoader.onerror = null;
        }
        
        const nextImageUrl = getNextImageUrl();
        if (!nextImageUrl) return null;
        
        nextImageLoader = new Image();
        nextImageLoader.src = nextImageUrl;
        return nextImageUrl;
    }
    
    // 切换到下一张图片（交叉淡入淡出）
    function switchToNextImage() {
        if (isSwitching) return;
        isSwitching = true;
        
        // 先预加载下一张图片
        const nextImageUrl = preloadNextImage();
        if (!nextImageUrl) {
            isSwitching = false;
            return;
        }
        
        // 等待预加载完成后再开始切换
        const startSwitch = () => {
            // 创建新图片元素
            const newImg = document.createElement('img');
            newImg.src = nextImageUrl;
            newImg.alt = 'banner';
            // 初始不添加shiroki-fade-in类，让图片透明
            bannerImg.appendChild(newImg);
            
            // 旧图片淡出
            currentImg.classList.add('shiroki-fade-out');
            
            // 下一帧添加淡入类，触发过渡动画
            requestAnimationFrame(() => {
                newImg.classList.add('shiroki-fade-in');
            });
            
            // 等待过渡完成后清理旧图片
            setTimeout(() => {
                if (currentImg !== originalImg) {
                    currentImg.remove();
                }
                currentImg = newImg;
                isSwitching = false;
            }, 800); // 等待淡出动画完成
        };
        
        // 如果预加载已完成，立即开始切换
        if (nextImageLoader.complete) {
            startSwitch();
        } else {
            // 等待预加载完成
            nextImageLoader.onload = startSwitch;
            nextImageLoader.onerror = startSwitch;
        }
    }
    
    // 启动定时器
    const intervalId = setInterval(switchToNextImage, switchInterval);
    
    // 页面隐藏时暂停切换，显示时恢复
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(intervalId);
        } else {
            setInterval(switchToNextImage, switchInterval);
        }
    });
}

// 🎨 简化的文章封面图片加载
function initPostCoverImages() {
    const postImages = document.querySelectorAll('.post-list-img img');
    
    postImages.forEach(img => {
        // 为每个图片添加占位符
        const container = img.parentElement;
        if (container && !container.querySelector('.shiroki-image-placeholder')) {
            const placeholder = document.createElement('div');
            placeholder.className = 'shiroki-image-placeholder';
            container.appendChild(placeholder);
        }

        // 简单的加载完成处理
        const handleLoad = () => {
            img.classList.add('loaded');
            
            // 延迟隐藏占位符
            const placeholder = img.parentElement.querySelector('.shiroki-image-placeholder');
            if (placeholder) {
                setTimeout(() => {
                    placeholder.classList.add('loaded');
                    setTimeout(() => placeholder.remove(), 300);
                }, 100);
            }
        };

        // 如果图片已经加载完成
        if (img.complete) {
            handleLoad();
        } else {
            // 监听图片加载完成
            img.addEventListener('load', handleLoad);
            img.addEventListener('error', handleLoad);
        }
    });
}
// Headhesive初始化
function initStickyHeader() {
  const header = document.querySelector('.boxmoe_header .navbar');
  if (!header) return;
  let lastScrollTop = 0;
  const headerHeight = header.offsetHeight;
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (!header) return;

    if (scrollTop > headerHeight) {
      if (scrollTop > lastScrollTop) {
        header.classList.add('scrolled');
        header.classList.remove('boxed', 'mx-auto', 'nav-down');
        header.classList.add('boxed', 'mx-auto', 'nav-up');
      } else {
        header.classList.add('scrolled');
        header.classList.remove('boxed', 'mx-auto', 'nav-up');
        header.classList.add('boxed', 'mx-auto', 'nav-down');
      }

    } else {
      header.classList.remove('boxed', 'mx-auto', 'scrolled', 'nav-up', 'nav-down');
    }  
    lastScrollTop = scrollTop;
  });
}

// 文章导读初始化
function initTableOfContents() {
    const content = document.querySelector('.single-content');
    const tocContainer = document.querySelector('.post-toc-container');
    const tocBtn = document.querySelector('.post-toc-btn');
    const toc = document.querySelector('.post-toc');
    const tocList = document.querySelector('.toc-list');   
    if(!content || !tocBtn || !toc || !tocList) return; 
    const headers = content.querySelectorAll('h1, h2, h3, h4');
    if(headers.length === 0) {
        tocContainer.style.display = 'none';
        return;
    }
    let isScrolling;
    const counters = [0, 0, 0, 0]; 
    let currentLevel = 0;
    headers.forEach((header, index) => {
        const level = parseInt(header.tagName[1]) - 1;     
        counters[level]++;
        for(let i = level + 1; i < 4; i++) counters[i] = 0; 
        
        const numberParts = [];
        for(let i = 0; i <= level; i++) {
            if(counters[i] > 0) numberParts.push(counters[i]);
        }
        const numberStr = numberParts.join('.');

        const link = document.createElement('a');
        const id = `header-${index}`;
        header.id = id;
        link.href = `#${id}`;
                link.textContent = `${numberStr} ${header.textContent}`;
        link.style.paddingLeft = `${level * 10}px`;
        tocList.appendChild(link);
    });
    const showOffset = 350;
    window.addEventListener('scroll', () => {
        const scrollPos = window.scrollY;
        if(scrollPos > showOffset) {
            tocContainer.classList.add('visible');
            tocBtn.classList.add('visible');
        } else {
            tocContainer.classList.remove('visible');
            tocBtn.classList.remove('visible');
            toc.classList.remove('show'); 
        }
        clearTimeout(isScrolling);
        isScrolling = setTimeout(() => {
            const links = tocList.querySelectorAll('a');
            let currentActive = null;
            
            const navHeight = document.querySelector('.navbar')?.offsetHeight || 0;
            const buffer = 20;
            for(let i = 0; i < headers.length; i++) {
                const headerRect = headers[i].getBoundingClientRect();
                if (headerRect.top <= navHeight + buffer && headerRect.bottom > navHeight) {
                    currentActive = links[i];
                    break;
                }
            }
            if (!currentActive) {
                for(let i = headers.length - 1; i >= 0; i--) {
                    const headerRect = headers[i].getBoundingClientRect();
                    if (headerRect.top <= navHeight + buffer) {
                        currentActive = links[i];
                        break;
                    }
                }
            }
            if(currentActive && !currentActive.classList.contains('active')) {
                links.forEach(link => link.classList.remove('active'));
                currentActive.classList.add('active');       
                const tocListRect = tocList.getBoundingClientRect();
                const activeLinkRect = currentActive.getBoundingClientRect();
                if (activeLinkRect.top < tocListRect.top) {
                    tocList.scrollTop -= (tocListRect.top - activeLinkRect.top + 50);
                } else if (activeLinkRect.bottom > tocListRect.bottom) {
                    tocList.scrollTop += (activeLinkRect.bottom - tocListRect.bottom + 50);
                }
            }
        }, 50);
    });
    tocList.addEventListener('click', (e) => {
        if(e.target.tagName === 'A') {
            e.preventDefault();     
            tocList.querySelectorAll('a').forEach(link => {
                link.classList.remove('active');
            });
            e.target.classList.add('active');
            
            const targetId = e.target.getAttribute('href').slice(1);
            const targetHeader = document.getElementById(targetId);
            
            if(targetHeader) {
                const navHeight = document.querySelector('.navbar')?.offsetHeight || 0;
                const targetPosition = targetHeader.getBoundingClientRect().top + window.scrollY - navHeight - 10;       
                const tocListRect = tocList.getBoundingClientRect();
                const clickedLinkRect = e.target.getBoundingClientRect();               
                if (clickedLinkRect.top < tocListRect.top) {
                    tocList.scrollTop += clickedLinkRect.top - tocListRect.top;
                } else if (clickedLinkRect.bottom > tocListRect.bottom) {
                    tocList.scrollTop += clickedLinkRect.bottom - tocListRect.bottom;
                }             
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
    tocBtn.addEventListener('click', () => {
        toc.classList.toggle('show');
        // 🎯 目录打开/关闭时切换按钮动画状态
        if (toc.classList.contains('show')) {
            tocBtn.style.animationPlayState = 'paused';
        } else {
            tocBtn.style.animationPlayState = 'running';
        }
    });
    document.addEventListener('click', (e) => {
        if(!toc.contains(e.target) && !tocBtn.contains(e.target)) {
            toc.classList.remove('show');
            // 🎯 点击外部关闭目录时恢复按钮动画
            tocBtn.style.animationPlayState = 'running';
        }
    });
}

// 标签颜色初始化
function initTagColors() {
    const colors = [
        "#83ea6c", "#1dd7c2", "#85b2f4", "#ffcf00", "#f4c8c6", "#e6f2e4", 
        "#83ea6c", "#1dd7c2", "#85b2f4", "#0dcaf0", "#e8d8ff", "#ffd700", 
        "#ff7f50", "#6495ed", "#b0e0e6", "#ff6347", "#98fb98", "#dda0dd", 
        "#add8e6", "#ff4500", "#d3d3d3", "#00bfff", "#ff1493", "#ff6347", 
        "#8a2be2", "#7fff00", "#d2691e", "#a52a2a", "#9acd32", "#ff8c00", 
        "#dcdcdc", "#dc143c", "#f0e68c", "#ff00ff", "#4b0082", "#8b0000", 
        "#e9967a", "#ff00ff", "#2e8b57", "#3cb371", "#f5deb3", "#ff69b4"
    ];  
    document.querySelectorAll('.blog-post .tagfa').forEach((element, index) => {
        if (index < colors.length) {
            element.style.color = colors[index];
        }
    });   
    document.querySelectorAll('.tag-cloud .tagfa').forEach((element, index) => {
        if (index < colors.length) {
            element.style.color = colors[index];
        }
    });
}

// 一言初始化
function initHitokoto() {
    if (!document.getElementById('hitokoto')) return;
    const hitokotoParam = window.ajax_object ? window.ajax_object.hitokoto : 'a';
    fetch(`https://v1.hitokoto.cn/?c=${hitokotoParam}`)
        .then(response => response.json())
        .then(data => {
            const hitokotoEl = document.getElementById('hitokoto');
            hitokotoEl && (hitokotoEl.textContent = data.hitokoto);
        })
}

// 🔐 登录状态管理
const LoginStatusManager = (() => {
    // 配置项
    const config = {
        checkInterval: 30000, // 30秒检查一次
        retryAttempts: 3, // 重试次数
        retryDelay: 2000, // 重试延迟
        localStorageKey: 'boxmoe_login_status', // 本地存储键名
        localStorageTTL: 604800000 // 本地存储有效期（7天）
    };
    
    // 状态
    let isChecking = false;
    let currentAttempt = 0;
    
    /**
     * 从本地存储获取登录状态
     */
    const getLoginStatusFromLocalStorage = () => {
        try {
            const stored = localStorage.getItem(config.localStorageKey);
            if (!stored) {
                return null;
            }
            
            const data = JSON.parse(stored);
            const now = Date.now();
            
            // 检查是否过期
            if (now - data.timestamp > config.localStorageTTL) {
                localStorage.removeItem(config.localStorageKey);
                return null;
            }
            
            return data;
        } catch (error) {
            console.warn('从本地存储获取登录状态失败:', error);
            localStorage.removeItem(config.localStorageKey);
            return null;
        }
    };
    
    /**
     * 将登录状态保存到本地存储
     */
    const saveLoginStatusToLocalStorage = (isLoggedIn, userInfo = {}) => {
        try {
            const data = {
                is_logged_in: isLoggedIn,
                user_info: userInfo,
                timestamp: Date.now()
            };
            localStorage.setItem(config.localStorageKey, JSON.stringify(data));
        } catch (error) {
            console.warn('将登录状态保存到本地存储失败:', error);
        }
    };
    
    /**
     * 清除本地存储的登录状态
     */
    const clearLoginStatusFromLocalStorage = () => {
        try {
            localStorage.removeItem(config.localStorageKey);
        } catch (error) {
            console.warn('清除本地存储的登录状态失败:', error);
        }
    };
    
    /**
     * 检查登录状态
     */
    const checkLoginStatus = async () => {
        if (isChecking || !window.ajax_object) {
            return;
        }
        
        isChecking = true;
        currentAttempt++;
        
        try {
            // 使用FormData来构建请求体，确保WordPress能正确解析
            const formData = new FormData();
            formData.append('action', 'boxmoe_check_login_status');
            formData.append('nonce', window.ajax_object.nonce);
            
            const response = await fetch(window.ajax_object.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // 无论状态是否变化，都更新UI，确保头像和管理员入口正确显示
                updateLoginUI(data.data.is_logged_in, data.data.user_info);
                saveLoginStatusToLocalStorage(data.data.is_logged_in, data.data.user_info);
                currentAttempt = 0; // 重置重试次数
            } else {
                console.warn('登录状态检查失败:', data.data?.message || '未知错误');
            }
        } catch (error) {
            console.warn('登录状态检查失败:', error);
            
            // 重试机制
            if (currentAttempt < config.retryAttempts) {
                setTimeout(() => {
                    checkLoginStatus();
                }, config.retryDelay);
            } else {
                // 重试次数耗尽，使用本地存储状态
                console.warn('登录状态检查重试次数耗尽，使用本地存储状态');
                const storedStatus = getLoginStatusFromLocalStorage();
                if (storedStatus) {
                    updateLoginUI(storedStatus.is_logged_in, storedStatus.user_info);
                }
                currentAttempt = 0;
            }
        } finally {
            isChecking = false;
        }
    };
    
    /**
     * 更新登录UI
     */
    const updateLoginUI = (isLoggedIn, userInfo = {}) => {
        // 更新全局状态
        if (window.ajax_object) {
            window.ajax_object.is_user_logged_in = isLoggedIn ? 'true' : 'false';
        }
        
        // 确保userInfo有默认值
        userInfo = userInfo || {};
        
        // 重新渲染登录相关UI
        renderLoginUI(isLoggedIn, userInfo);
        
        // 强制刷新所有懒加载图片，确保头像显示
        setTimeout(() => {
            // 手动触发所有懒加载图片加载，确保头像显示
            const lazyImages = document.querySelectorAll('img.lazy');
            lazyImages.forEach(img => {
                if (img.dataset.src) {
                    // 确保图片URL正确，避免404错误
                    const imgSrc = img.dataset.src;
                    if (imgSrc && !imgSrc.startsWith('http')) {
                        // 如果是相对路径，确保它有正确的主题路径
                        const themeUrl = window.ajax_object && window.ajax_object.themeurl ? window.ajax_object.themeurl : '';
                        img.src = themeUrl + imgSrc;
                    } else {
                        img.src = imgSrc;
                    }
                    img.classList.remove('lazy');
                }
            });
        }, 200);
        
        // 如果从登录状态变为未登录状态，清除本地存储
        if (!isLoggedIn) {
            clearLoginStatusFromLocalStorage();
        } else {
            // 如果是登录状态，确保本地存储有最新的用户信息
            saveLoginStatusToLocalStorage(isLoggedIn, userInfo);
        }
    };
    
    /**
     * 渲染登录UI
     */
    const renderLoginUI = (isLoggedIn, userInfo) => {
        try {
            // 检查导航会员注册链接开关是否开启
            const signInLinkSwitch = window.ajax_object && window.ajax_object.sign_in_link_switch === 'true';
            if (!signInLinkSwitch) {
                return;
            }
            
            // 处理移动端用户面板
            const mobileUserBtn = document.querySelector('.mobile-user-btn');
            const mobileUserPanels = document.querySelectorAll('.mobile-user-panel');
            
            // 移除所有现有面板
            mobileUserPanels.forEach(panel => {
                try {
                    panel.remove();
                } catch (error) {
                    console.warn('移除移动端用户面板失败:', error);
                }
            });
            
            // 创建新的用户面板
            const newPanel = document.createElement('div');
            newPanel.className = 'mobile-user-panel';
            
            try {
                // 确保ajax_object和themeurl存在
                const themeUrl = window.ajax_object && window.ajax_object.themeurl ? window.ajax_object.themeurl : '';
                
                if (isLoggedIn) {
                    // 新增检查用户中心页面是否存在
                    const userCenterExists = document.querySelector('meta[name="user-center-exists"]')?.content === 'true';
                    newPanel.innerHTML = `
                        <div class="user-panel-content">
                            <div class="mobile-user-wrapper">
                                <div class="mobile-logged-menu">
                                <!-- 新增检查用户中心页面是否存在 -->
                                ${userCenterExists ? `<a href="${getUserCenterLink()}" class="mobile-menu-item">
                                        <i class="fa fa-user-circle"></i>
                                        <span>会员中心</span></a>` : ''}
                                        ${isAdmin() ? `
                                    <a href="${window.ajax_object?.adminurl || '/wp-admin/'}" class="mobile-menu-item">
                                        <i class="fa fa-cog"></i>
                                        <span>后台管理</span></a>
                                        ` : ''}
                                    <a href="${getLogoutUrl()}" class="mobile-menu-item">
                                        <i class="fa fa-sign-out"></i>
                                        <span>注销登录</span></a>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    newPanel.innerHTML = `
                        <div class="user-panel-content">
                            <div class="mobile-user-wrapper">
                                <div class="mobile-logged-menu">
                                <div class="user-wrapper d-lg-flex">
                            <div class="user-login-wrap">
                            <a href="${getLoginLink()}" class="user-login">
                            <span class="login-text">登录</span></a>
                            </div>
                            <span class="divider">or</span>
                            <div class="user-reg-wrap">
                            <a href="${getRegisterLink()}" class="user-reg">
                            <span class="reg-text">注册</span></a></div>
                            </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('创建用户面板HTML失败:', error);
                return;
            }
            
            if (mobileUserBtn && mobileUserBtn.parentElement) {
                try {
                    mobileUserBtn.parentElement.appendChild(newPanel);
                    
                    // 为移动用户按钮添加点击事件监听器，控制下拉菜单显示/隐藏
                    const toggleMobilePanel = () => {
                        const isVisible = newPanel.style.display === 'block';
                        
                        if (isVisible) {
                            // 隐藏面板
                            newPanel.style.display = 'none';
                        } else {
                            // 显示面板
                            newPanel.style.display = 'block';
                        }
                    };
                    
                    // 初始状态隐藏面板
                    newPanel.style.display = 'none';
                    
                    // 点击按钮切换面板显示状态
                    mobileUserBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        toggleMobilePanel();
                    });
                    
                    // 点击外部关闭面板
                    document.addEventListener('click', (e) => {
                        if (!newPanel.contains(e.target) && !mobileUserBtn.contains(e.target)) {
                            newPanel.style.display = 'none';
                        }
                    });
                    
                } catch (error) {
                    console.warn('添加移动端用户面板失败:', error);
                }
            }
            
            // 处理桌面端用户面板
            const desktopUserWrappers = document.querySelectorAll('.user-wrapper, .logged-user-wrapper');
            
            desktopUserWrappers.forEach(wrapper => {
                try {
                    wrapper.remove();
                } catch (error) {
                    console.warn('移除桌面端用户面板失败:', error);
                }
            });
            
            // 检查导航会员注册链接开关是否开启

            
            // 创建新的桌面用户面板
            const navRightSection = document.querySelector('.nav-right-section');
            if (navRightSection) {
                const newWrapper = document.createElement('div');
                
                try {
                    // 确保ajax_object和themeurl存在
                    const themeUrl = window.ajax_object && window.ajax_object.themeurl ? window.ajax_object.themeurl : '';
                    
                    if (isLoggedIn) {
                        newWrapper.className = 'logged-user-wrapper d-none d-lg-flex';
                        // 获取头像URL
                        const avatarUrl = getUserAvatarUrl(userInfo.user_id || 0, userInfo);
                        
                        // 新增检查用户中心页面是否存在
                        const userCenterExists = document.querySelector('meta[name="user-center-exists"]')?.content === 'true';
                        newWrapper.innerHTML = `
                            <div class="user-info-wrap d-flex align-items-center dropdown">
                                <!-- 新增检查用户中心页面是否存在 -->
                                ${userCenterExists ? `<a href="${getUserCenterLink()}" class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">` : `<div class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">`}
                                    ${avatarUrl ? `
                                    <div class="user-avatar">
                                    <img src="${themeUrl}/assets/images/loading.gif" data-src="${avatarUrl}" alt="avatar" class="img-fluid rounded-3 lazy">
                                </div>` : ''}
                                    <div class="user-info">
                                        <div class="user-name">${userInfo.display_name || '用户'}</div>
                                        <div class="user-email">${userInfo.user_email || ''}</div>
                                </div>
                                <!-- 新增检查用户中心页面是否存在 -->
                                ${userCenterExists ? `</a>` : `</div>`}
                                <ul class="dropdown-menu dropdown-menu-end">
                                <!-- 新增检查用户中心页面是否存在 -->
                                  ${userCenterExists ? `
                                  <li>
                                    <a class="dropdown-item" href="${getUserCenterLink()}">
                                      <i class="fa fa-user-circle"></i>会员中心</a>
                                  </li>` : ''}
                                  ${isAdmin() ? `
                                  <li>
                                    <a class="dropdown-item" target="_blank" href="${window.ajax_object?.adminurl || '/wp-admin/'}">
                                      <i class="fa fa-cog"></i>后台管理</a>
                                  </li>
                                  ` : ''}
                                  <li>
                                    <a class="dropdown-item" href="${getLogoutUrl()}">
                                      <i class="fa fa-sign-out"></i>注销登录</a>
                                  </li>
                                </ul>
                            </div>
                        `;
                    } else {
                        newWrapper.className = 'user-wrapper d-none d-lg-flex';
                        newWrapper.innerHTML = `
                            <div class="user-login-wrap">
                            <a href="${getLoginLink()}" class="user-login">
                            <span class="login-text">登录</span></a>
                            </div>
                            <span class="divider">or</span>
                            <div class="user-reg-wrap">
                            <a href="${getRegisterLink()}" class="user-reg">
                            <span class="reg-text">注册</span></a></div>
                            <img src="${themeUrl}/assets/images/up-new-iocn.png" class="new-tag" alt="up-new-iocn">
                        `;
                    }
                } catch (error) {
                    console.error('创建桌面用户面板HTML失败:', error);
                    return;
                }
                
                try {
                    navRightSection.appendChild(newWrapper);
                } catch (error) {
                    console.warn('添加桌面端用户面板失败:', error);
                }
            }
        } catch (error) {
            console.error('渲染登录UI失败:', error);
        }
    };
    
    /**
     * 辅助函数：获取用户中心链接
     */
    const getUserCenterLink = () => {
        // 优先使用全局函数（由PHP输出到页面）
        if (typeof boxmoe_user_center_link_page === 'function') {
            return boxmoe_user_center_link_page();
        }
        // 尝试从页面元数据中获取
        const userCenterMeta = document.querySelector('meta[name="user-center-url"]');
        if (userCenterMeta) {
            return userCenterMeta.content;
        }
        // 回退到默认链接（仅作为最后的备选）
        return '/user-center';
    };
    
    /**
     * 辅助函数：获取登录链接
     */
    const getLoginLink = () => {
        // 优先使用全局函数（由PHP输出到页面）
        if (typeof boxmoe_sign_in_link_page === 'function') {
            return boxmoe_sign_in_link_page();
        }
        // 尝试从页面元数据中获取
        const loginMeta = document.querySelector('meta[name="login-url"]');
        if (loginMeta) {
            return loginMeta.content;
        }
        // 回退到默认链接（仅作为最后的备选）
        return '/signin';
    };

    /**
     * 辅助函数：获取注册链接
     */
    const getRegisterLink = () => {
        // 优先使用全局函数（由PHP输出到页面）
        if (typeof boxmoe_sign_up_link_page === 'function') {
            return boxmoe_sign_up_link_page();
        }
        // 尝试从页面元数据中获取
        const registerMeta = document.querySelector('meta[name="register-url"]');
        if (registerMeta) {
            return registerMeta.content;
        }
        // 回退到默认链接（仅作为最后的备选）
        return '/signup';
    };
    
    /**
     * 辅助函数：获取注销链接
     */
    const getLogoutUrl = () => {
        // 优先使用WordPress函数
        if (typeof wp_logout_url === 'function') {
            return wp_logout_url(home_url());
        }
        // 回退到默认链接
        return '/wp-login.php?action=logout';
    };
    
    /**
     * 辅助函数：获取用户头像URL
     */
    const getUserAvatarUrl = (userId, userInfo) => {
        try {
            // 确保ajax_object和themeurl存在
            const themeUrl = window.ajax_object && window.ajax_object.themeurl ? window.ajax_object.themeurl : '';
            
            // 优先使用传入的userInfo中的头像信息
            if (userInfo && userInfo.user_avatar) {
                return userInfo.user_avatar;
            }
            
            // 检查localStorage中是否有用户信息
            const storedUserInfo = JSON.parse(localStorage.getItem('user_info'));
            if (storedUserInfo && storedUserInfo.user_avatar) {
                return storedUserInfo.user_avatar;
            }
            
            // 直接调用PHP函数生成头像URL，确保与文章头头像一致
            if (typeof boxmoe_get_avatar_url === 'function') {
                return boxmoe_get_avatar_url(userId, 100);
            }
            
            // 检查是否有默认头像URL
            if (themeUrl) {
                return `${themeUrl}/assets/images/touxiang.jpg`;
            }
            
            // 最终回退
            return '/wp-content/themes/lolimeow-shiroki/assets/images/touxiang.jpg';
        } catch (error) {
            console.warn('获取用户头像URL失败:', error);
            // 确保返回有效的URL
            return '/wp-content/themes/lolimeow-shiroki/assets/images/touxiang.jpg';
        }
    };
    
    /**
     * 辅助函数：检查是否为管理员
     */
    const isAdmin = () => {
        try {
            // 优先从当前用户信息检查
            if (window.ajax_object && window.ajax_object.is_admin === 'true') {
                return true;
            }
            
            // 从本地存储获取管理员状态
            const stored = getLoginStatusFromLocalStorage();
            if (stored && stored.user_info && stored.user_info.is_admin) {
                return stored.user_info.is_admin;
            }
            
            // 检查全局函数
            if (typeof current_user_can === 'function') {
                return current_user_can('administrator');
            }
        } catch (error) {
            console.warn('检查管理员状态失败:', error);
        }
        return false;
    };
    
    /**
     * 初始化登录状态管理
     */
    const init = () => {
        // 页面加载时立即使用PHP渲染的初始状态
        const initialIsLoggedIn = window.ajax_object && window.ajax_object.is_user_logged_in === 'true';
        
        // 立即更新UI，使用初始状态，确保页面加载时就能显示正确的登录状态
        updateLoginUI(initialIsLoggedIn, {});
        
        // 然后检查本地存储状态，可能包含更详细的用户信息
        const storedStatus = getLoginStatusFromLocalStorage();
        if (storedStatus) {
            updateLoginUI(storedStatus.is_logged_in, storedStatus.user_info);
        }
        
        // 初始AJAX检查，获取最新的登录状态
        checkLoginStatus();
        
        // 定期检查，确保登录状态始终最新
        setInterval(() => {
            checkLoginStatus();
        }, config.checkInterval);
        
        // 页面可见性变化时检查，用户返回页面时更新状态
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkLoginStatus();
            }
        });
        
        // 监听网络状态变化，网络恢复时更新状态
        window.addEventListener('online', () => {

            checkLoginStatus();
        });
        
        // 监听页面加载完成事件，确保所有资源加载完成后再次检查
        window.addEventListener('load', () => {
            checkLoginStatus();
        });
    };
    
    return {
        init,
        checkLoginStatus
    };
})();

// 点赞功能初始化
function initPostLikes() {
    document.querySelectorAll('.like-btn').forEach(btn => {
        const postId = btn.dataset.postId;
        if(localStorage.getItem(`post_${postId}_liked`)) {
            btn.classList.add('liked');
            btn.querySelector('i').classList.add('text-primary');
        }
        
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if(this.classList.contains('processing') || localStorage.getItem(`post_${postId}_liked`)) {
                return;
            }
            
            this.classList.add('processing');
            
            try {
                const formData = new FormData();
                formData.append('action', 'post_like');
                formData.append('post_id', postId);
                
                const response = await fetch(window.ajax_object.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const count = data.data.count;
                    btn.querySelector('.like-count').textContent = count;
                    localStorage.setItem(`post_${postId}_liked`, 'true');
                    btn.classList.add('liked');
                    btn.querySelector('i').classList.add('text-primary');
                } else {
                    console.warn('点赞失败:', data.data.message);
                }
            } catch (error) {
                console.error('点赞请求失败:', error);
            } finally {
                this.classList.remove('processing');
            }
        });
    });
}

// 打赏功能初始化
function initReward() {
    const rewardBtn = document.querySelector('.reward-btn');
    const rewardModal = document.querySelector('.reward-modal');
    const rewardClose = document.querySelector('.reward-close');

    if (rewardBtn && rewardModal) {
        rewardBtn.addEventListener('click', () => {
            rewardModal.classList.add('show');
        });

        rewardModal.addEventListener('click', (e) => {
            if (e.target === rewardModal) {
                rewardModal.classList.remove('show');
            }
        });

        if (rewardClose) {
            rewardClose.addEventListener('click', () => {
                rewardModal.classList.remove('show');
            });
        }
    }
}

// 收藏功能初始化
function initPostFavorites() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const postId = btn.dataset.postId;
        
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if(this.classList.contains('processing')) {
                return;
            }
            
            this.classList.add('processing');
            
            try {
                const formData = new FormData();
                formData.append('action', 'post_favorite');
                formData.append('post_id', postId);
                
                const response = await fetch(window.ajax_object.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const favoriteText = this.querySelector('.favorite-text');
                    if (data.data.status) {
                        this.classList.add('favorited');
                        favoriteText.textContent = '已收藏';
                    } else {
                        this.classList.remove('favorited');
                        favoriteText.textContent = '收藏';
                    }
                } else {
                    console.warn('收藏操作失败:', data.data.message);
                }
            } catch (error) {
                console.error('收藏请求失败:', error);
            } finally {
                this.classList.remove('processing');
            }
        });
    });
}

// 主题切换初始化
const ThemeSwitcher = (() => {
    "use strict";
    const getStoredTheme = () => localStorage.getItem("theme");
    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        return storedTheme || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
    };
    const setTheme = theme => {
        const isAutoDark = theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches;
        document.documentElement.setAttribute("data-bs-theme", isAutoDark ? "dark" : theme);
    };
    const updateActiveState = (theme, focus = false) => {
        const themeSwitcher = document.querySelector(`[data-bs-theme-value="${theme}"]`);
        if (!themeSwitcher) return;

        document.querySelectorAll("[data-bs-theme-value]").forEach(btn => {
            btn.classList.toggle("active", btn === themeSwitcher);
            btn.setAttribute("aria-pressed", btn === themeSwitcher);
        });
        const mainThemeBtn = document.querySelector('.bd-theme i');
        if (mainThemeBtn) {
            mainThemeBtn.className = theme === 'light' ? 'fa fa-sun-o' :
                                   theme === 'dark' ? 'fa fa-moon-o' :
                                   'fa fa-adjust';
        }

        focus && themeSwitcher.focus();
    };
    const init = () => {
        const preferredTheme = getPreferredTheme();
        setTheme(preferredTheme);
        updateActiveState(preferredTheme);
        document.querySelectorAll("[data-bs-theme-value]").forEach(button => {
            button.addEventListener("click", () => {
                const theme = button.dataset.bsThemeValue;
                const current = document.documentElement.getAttribute("data-bs-theme") || "light";
                const nextEffective = theme === "auto" ? (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light") : theme;
                animateThemeToggle(button, current, nextEffective);
                localStorage.setItem("theme", theme);
                setTheme(theme);
                updateActiveState(theme, true);
            });
        });
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", e => {
            const storedTheme = getStoredTheme();
            storedTheme === "auto" && setTheme(getPreferredTheme());
        });
    };

    return { init };
})();

// 代码高亮初始化
function initPrettyPrint() {
    const prettyprintElements = document.querySelectorAll('.prettyprint');
    if (prettyprintElements.length && window.prettyPrint) {
        window.prettyPrint();
        
        // 修复行号显示问题：当行号超过9时，从10开始显示而不是从0开始
        setTimeout(() => {
            const codeBlocks = document.querySelectorAll('.prettyprint ol.linenums');
            codeBlocks.forEach((codeBlock) => {
                // 确保代码块使用我们的CSS计数器样式
                codeBlock.style.counterReset = 'line-number';
                codeBlock.style.listStyleType = 'none';
                
                const lines = codeBlock.querySelectorAll('li');
                lines.forEach((line, index) => {
                    // 移除Prettify库生成的value属性，避免与CSS计数器冲突
                    line.removeAttribute('value');
                    // 移除所有内联样式，使用CSS中定义的样式
                    line.removeAttribute('style');
                    // 确保每一行都正确应用计数器递增
                    line.style.counterIncrement = 'line-number';
                    // 为每一行设置正确的class，避免Prettify库的样式影响
                    line.className = `L${index % 10}`;
                });
            });
        }, 200);
    }
}

function initCodeCopy() {
    const container = document.querySelector('.boxmoe-container');
    if (!container) return;
    const preElements = container.querySelectorAll('pre');
    preElements.forEach((pre, index) => {
        const btnCopy = document.createElement('div');
        btnCopy.className = 'btn-copy';
        const copySpan = document.createElement('span');
        copySpan.className = 'single-copy copy';
        copySpan.setAttribute('title', '点击复制本段代码');
        copySpan.innerHTML = '🧑🏻‍💻</i> 复制代码';
        btnCopy.appendChild(copySpan);
        pre.insertBefore(btnCopy, pre.firstChild);
        const codeList = pre.querySelector('ol.linenums');
        if (codeList) {
            codeList.id = `copy${index}`;
            const lines = Array.from(codeList.querySelectorAll('li')).map(li => li.textContent.replace(/\u00A0/g, ''));
            const text = lines.join('\n').replace(/^\s*\n|\n\s*$/g, '');
            copySpan.setAttribute('data-clipboard-text', text);
        } else {
            const codeEl = pre.querySelector('code');
            const raw = codeEl ? (codeEl.textContent || '') : (pre.textContent || '');
            const btnText = btnCopy.textContent || '';
            const text = raw.replace(btnText, '').replace(/\u00A0/g, '').replace(/^\s*\n|\n\s*$/g, '');
            copySpan.setAttribute('data-clipboard-text', text);
        }
    });
    const clipboard = new ClipboardJS('.copy');
    clipboard.on('success', function(e) {
        e.clearSelection();
        const trigger = e.trigger;
        trigger.innerHTML = '<span style="color:#32cd32"><i class="fa fa-check-square-o" aria-hidden="true"></i> 复制成功</span>';     
        setTimeout(() => {
            trigger.innerHTML = '🧑🏻‍💻</i> 复制代码';
        }, 3000);
        if (window._copyBannerShow) {
            try { window._copyBannerShow(); } catch(_) {}
        }
    });
    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
        alert("复制失败，请手动复制");
    });
}

// Preloader初始化
function initPreloader() {
    const preloader = document.querySelector('.preloader');
    if (!preloader) return;
    preloader.style.display = 'flex';
    window.addEventListener('load', () => {
        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500); 
        }, 1000);
    });
}

// 🎭 初始化WOW.js动画效果
function initWow() {
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }
}

function initRunningDays() {
    const start = new Date(window.ajax_object.running_days);
    const daysEl = document.getElementById('runtime-days');
    const hoursEl = document.getElementById('runtime-hours');
    const minutesEl = document.getElementById('runtime-minutes');
    const secondsEl = document.getElementById('runtime-seconds');
    if (!daysEl || !hoursEl || !minutesEl || !secondsEl || !(start instanceof Date) || isNaN(start)) return;
    const update = () => {
        const now = new Date();
        let diff = now.getTime() - start.getTime();
        if (diff < 0) diff = 0;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);
        daysEl.textContent = days;
        hoursEl.textContent = hours;
        minutesEl.textContent = minutes;
        secondsEl.textContent = seconds;
    };
    update();
    setInterval(update, 1000);
}

function animateThemeToggle(btn, cur, nxt){
    try{
        var vw = window.innerWidth||document.documentElement.clientWidth;
        var vh = window.innerHeight||document.documentElement.clientHeight;
        var r = btn && btn.getBoundingClientRect ? btn.getBoundingClientRect() : { left: vw/2, top: 60, width: 0, height: 0 };
        var cx = Math.round(r.left + r.width/2);
        var cy = Math.round(r.top + r.height/2);
        var dx = Math.max(cx, vw - cx);
        var dy = Math.max(cy, vh - cy);
        var radius = Math.ceil(Math.hypot(dx, dy));
        var prevBg = (function(){
            try{
                var cs = window.getComputedStyle(document.body);
                var bgFull = cs.getPropertyValue('background');
                var bgImg = cs.getPropertyValue('background-image');
                var bgCol = cs.getPropertyValue('background-color');
                var val = String(bgFull||'').trim();
                if (val) return val;
                if (String(bgImg||'').trim() && String(bgCol||'').trim()) return String(bgImg).trim() + ', ' + String(bgCol).trim();
                if (String(bgImg||'').trim()) return String(bgImg).trim();
                if (String(bgCol||'').trim()) return String(bgCol).trim();
            }catch(_){}
            try{
                var rs = window.getComputedStyle(document.documentElement);
                var varBg = rs.getPropertyValue('--ish-bg');
                if (varBg && String(varBg).trim()) return String(varBg).trim();
            }catch(_){}
            try{ var s2 = window.getComputedStyle(document.documentElement).backgroundColor; if (s2) return s2; }catch(_){}
            return cur==='dark' ? 'rgb(18, 18, 18)' : 'rgb(255, 255, 255)';
        })();
        var overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.zIndex = '-1';
        overlay.style.pointerEvents = 'none';
        overlay.style.background = prevBg;
        overlay.style.willChange = 'clip-path';
        overlay.style.clipPath = 'circle('+radius+'px at '+cx+'px '+cy+'px)';
        overlay.style.transition = 'clip-path 520ms ease-in-out';
        if (document.body.firstChild) { document.body.insertBefore(overlay, document.body.firstChild); } else { document.body.appendChild(overlay); }
        requestAnimationFrame(function(){ overlay.style.clipPath = 'circle(0px at '+cx+'px '+cy+'px)'; });
        var cleanup = function(){ overlay.removeEventListener('transitionend', cleanup); if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); };
        overlay.addEventListener('transitionend', cleanup);
    }catch(_){}
}

// 📝 任务清单交互和自动保存功能 - Emoji版
function initTaskList() {
    const container = document.querySelector('.single-content');
    if (!container) return;
    
    // console.log('初始化任务清单交互功能');
    
    // 获取文章ID的多种方式
    let postId = document.body.getAttribute('data-post-id');
    
    // 如果body没有data-post-id属性，尝试从URL中获取
    if (!postId) {
        // 匹配URL中的数字ID，支持多种URL格式
        const urlMatch = window.location.pathname.match(/\d+/);
        if (urlMatch) {
            postId = urlMatch[0];
        } else {
            // 尝试从当前页面的其他元素获取，比如文章编辑页面
            const editForm = document.querySelector('#post');
            if (editForm) {
                const postIdInput = editForm.querySelector('#post_ID');
                if (postIdInput) {
                    postId = postIdInput.value;
                }
            }
        }
    }
    
    // 前端本地切换任务状态
    const toggleTaskState = (taskItem) => {
        // 如果正在同步中，不允许重复点击
        if (taskItem.classList.contains('md-task-syncing')) {
            return;
        }
        
        const currentStatus = taskItem.dataset.taskStatus;
        let newStatus = '';
        let newEmoji = '';
        
        // 根据当前状态计算下一个状态
        // 状态循环：in-progress → pending → completed → in-progress
        // 对应语法：- [>] → - [ ] → - [x] → - [>]
        switch(currentStatus) {
            case 'in-progress':
                newStatus = 'pending';
                newEmoji = '❌';
                break;
            case 'pending':
                newStatus = 'completed';
                newEmoji = '✅';
                break;
            case 'completed':
                newStatus = 'in-progress';
                newEmoji = '🔄';
                break;
            default:
                newStatus = 'in-progress';
                newEmoji = '🔄';
                break;
        }
        
        // 添加同步中状态
        taskItem.classList.add('md-task-syncing');
        
        // 更新本地状态
        taskItem.dataset.taskStatus = newStatus;
        const emojiSpan = taskItem.querySelector('.md-task-emoji');
        emojiSpan.textContent = newEmoji;
        
        // 添加加载指示器
        let loader = taskItem.querySelector('.md-task-loader');
        if (!loader) {
            loader = document.createElement('span');
            loader.className = 'md-task-loader';
            loader.style.cssText = `
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 2px solid rgba(0, 0, 0, 0.3);
                border-top-color: #000;
                border-radius: 50%;
                margin-left: 5px;
                animation: spin 1s linear infinite;
            `;
            taskItem.appendChild(loader);
        }
        
        // 添加旋转动画样式
        if (!document.getElementById('md-task-spin-style')) {
            const style = document.createElement('style');
            style.id = 'md-task-spin-style';
            style.textContent = `
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        

        
        // 自动保存任务状态到服务器
        saveTaskState(taskItem, currentStatus);
    };
    
    // 显示通知消息
    const showNotification = (message, type = 'info') => {
        // 创建通知容器
        let notificationContainer = document.getElementById('md-task-notification');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'md-task-notification';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 10000;
                max-width: 300px;
            `;
            document.body.appendChild(notificationContainer);
        }
        
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `md-task-notification md-task-notification-${type}`;
        
        // 设置浅蓝渐变背景，不同类型使用不同深浅的浅蓝色
        const lightBlueStart = type === 'error' ? 'rgba(173, 216, 230, 0.6)' : 
                               type === 'success' ? 'rgba(135, 206, 250, 0.6)' : 
                               'rgba(176, 224, 230, 0.6)';
        const lightBlueEnd = type === 'error' ? 'rgba(135, 206, 250, 0.4)' : 
                             type === 'success' ? 'rgba(173, 216, 230, 0.4)' : 
                             'rgba(135, 206, 250, 0.4)'; // 默认浅蓝色
        
        notification.style.cssText = `
            padding: 16px 24px;
            margin-bottom: 10px;
            border-radius: 16px;
            color: rgba(0, 0, 139, 0.95); // 深蓝色字体
            font-size: 15px;
            font-weight: 500;
            box-shadow: 
                0 8px 32px rgba(135, 206, 250, 0.15),
                0 1px 2px rgba(135, 206, 250, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            animation: slideUpFadeIn 0.3s ease-out;
            background: linear-gradient(135deg, ${lightBlueStart}, ${lightBlueEnd}); // 浅蓝渐变
            backdrop-filter: blur(20px); // 高斯模糊效果
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4); // 白色半透明边框
            transition: all 0.3s ease;
        `;
        notification.textContent = message;
        
        // 添加动画样式
        if (!document.getElementById('md-task-notification-style')) {
            const style = document.createElement('style');
            style.id = 'md-task-notification-style';
            style.textContent = `
                @keyframes slideUpFadeIn {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideDownFadeOut {
                    from {
                        transform: translateY(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                }
                
                /* 浅粉色扫光动画 */
                @keyframes pinkShine {
                    0% {
                        left: -100%;
                        opacity: 0.8;
                    }
                    100% {
                        left: 100%;
                        opacity: 0;
                    }
                }
                
                /* 通知元素基础样式 */
                .md-task-notification {
                    position: relative;
                    overflow: hidden;
                }
                
                /* 扫光效果容器 */
                .md-task-shine {
                    position: relative;
                }
                
                /* 扫光动画伪元素 */
                .md-task-shine::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 182, 193, 0.8), transparent);
                    pointer-events: none;
                    z-index: 1;
                    animation: pinkShine 1s ease-in-out forwards;
                }
            `;
            document.head.appendChild(style);
        }
        
        // 添加通知到容器
        notificationContainer.appendChild(notification);
        
        // 延迟500ms后添加扫光效果（确保向上渐显动画完成）
        setTimeout(() => {
            // 添加扫光类，触发粉色扫光动画
            notification.classList.add('md-task-shine');
        }, 500);
        
        // 2秒后开始淡出动画，然后移除
        setTimeout(() => {
            notification.style.animation = 'slideDownFadeOut 0.3s ease-in forwards';
            // 动画结束后移除元素
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    };
    
    // 自动保存任务状态到服务器
    const saveTaskState = async (taskItem, currentStatus) => {
        const taskContent = taskItem.dataset.taskContent;
        
        try {
            // 确保post_id存在
            if (!postId) {
                showNotification('更新任务状态失败: 无法获取文章ID', 'error');
                // 恢复原状态
                removeSyncingState(taskItem, currentStatus);
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_task_status');
            formData.append('post_id', postId);
            formData.append('task_content', taskContent);
            formData.append('current_status', currentStatus);
            // 添加nonce验证
            if (window.task_ajax_object && window.task_ajax_object.nonce) {
                formData.append('nonce', window.task_ajax_object.nonce);
            } else {
                showNotification('更新任务状态失败: 缺少安全验证', 'error');
                removeSyncingState(taskItem, currentStatus);
                return;
            }
            
            // 确保task_ajax_object存在
            if (!window.task_ajax_object || !window.task_ajax_object.ajaxurl) {
                showNotification('更新任务状态失败: 无法获取服务器地址', 'error');
                removeSyncingState(taskItem, currentStatus);
                return;
            }
            
            const response = await fetch(window.task_ajax_object.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {

                // 如果服务器返回了新状态，使用服务器返回的状态
                if (data.data && data.data.new_status) {
                    taskItem.dataset.taskStatus = data.data.new_status;
                    const emojiSpan = taskItem.querySelector('.md-task-emoji');
                    let newEmoji = '';
                    switch(data.data.new_status) {
                        case 'pending':
                            newEmoji = '❌';
                            break;
                        case 'in-progress':
                            newEmoji = '📃';
                            break;
                        case 'completed':
                            newEmoji = '✅';
                            break;
                    }
                    emojiSpan.textContent = newEmoji;
                }
                
                // 移除同步中状态
                removeSyncingState(taskItem);
                
                // 显示成功提示
                showNotification('任务状态更新成功', 'success');
            } else {
                console.warn('更新任务状态失败:', data.data.message);
                
                // 恢复原状态
                removeSyncingState(taskItem, currentStatus);
                
                // 显示错误提示
                showNotification(data.data.message || '更新任务状态失败', 'error');
            }
        } catch (error) {
            console.error('更新任务状态请求失败:', error);
            
            // 恢复原状态
            removeSyncingState(taskItem, currentStatus);
            
            // 显示错误提示
            showNotification('网络错误，更新任务状态失败', 'error');
            
            // 添加重试按钮
            const retryBtn = document.createElement('button');
            retryBtn.textContent = '重试';
            retryBtn.style.cssText = `
                margin-top: 10px;
                padding: 5px 10px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            `;
            retryBtn.onclick = () => {
                retryBtn.remove();
                toggleTaskState(taskItem);
            };
            
            // 在任务项中添加重试按钮
            taskItem.appendChild(retryBtn);
            
            // 3秒后自动移除重试按钮
            setTimeout(() => {
                if (retryBtn.parentNode) {
                    retryBtn.remove();
                }
            }, 5000);
        }
    };
    
    // 移除同步中状态和加载指示器
    const removeSyncingState = (taskItem, revertStatus = null) => {
        // 移除同步中类
        taskItem.classList.remove('md-task-syncing');
        
        // 移除加载指示器
        const loader = taskItem.querySelector('.md-task-loader');
        if (loader) {
            loader.remove();
        }
        
        // 如果需要恢复原状态
        if (revertStatus !== null) {
            taskItem.dataset.taskStatus = revertStatus;
            const emojiSpan = taskItem.querySelector('.md-task-emoji');
            let emoji = '';
            switch(revertStatus) {
                case 'pending':
                    emoji = '❌';
                    break;
                case 'in-progress':
                    emoji = '🔄';
                    break;
                case 'completed':
                    emoji = '✅';
                    break;
                default:
                    emoji = '❌';
                    break;
            }
            emojiSpan.textContent = emoji;
        }
    };
    
    // 任务项点击处理函数
    const handleTaskItemClick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        

        
        // 查找最近的任务项
        const taskItem = this.closest('.md-task-item') || e.target.closest('.md-task-item');
        if (!taskItem) {

            return;
        }
        

        
        // 检查是否为可交互任务项
        if (!taskItem.classList.contains('md-task-item-interactive')) {

            return;
        }
        

        toggleTaskState(taskItem);
    };
    
    // 页面加载时初始化任务状态
    const initTaskStates = () => {
        const taskItems = document.querySelectorAll('.md-task-item');
        // console.log('初始化任务项数量:', taskItems.length);
        
        // 为每个可交互的任务项添加点击事件
        taskItems.forEach(taskItem => {
            if (taskItem.classList.contains('md-task-item-interactive')) {
                // 添加悬停效果
                taskItem.style.cursor = 'pointer';
                taskItem.style.transition = 'opacity 0.2s ease';
                
                // 绑定点击事件
                taskItem.addEventListener('click', handleTaskItemClick);
                
                // 确保初始状态正确
                const status = taskItem.dataset.taskStatus;
                const emojiSpan = taskItem.querySelector('.md-task-emoji');
                let emoji = '';
                switch(status) {
                    case 'pending':
                        emoji = '❌';
                        break;
                    case 'in-progress':
                        emoji = '🔄';
                        break;
                    case 'completed':
                        emoji = '✅';
                        break;
                    default:
                        emoji = '❌';
                        taskItem.dataset.taskStatus = 'pending';
                        break;
                }
                emojiSpan.textContent = emoji;
            } else {
                taskItem.style.cursor = 'default';
            }
        });
    };
    
    // 初始化任务状态和事件
    initTaskStates();
    
    // 为了支持动态生成的任务项，添加事件委托
    container.addEventListener('click', function(e) {
        if (e.target.closest('.md-task-item-interactive')) {
            const taskItem = e.target.closest('.md-task-item-interactive');
            handleTaskItemClick.call(taskItem, e);
        }
    });
}

// 页面加载完成后初始化任务清单
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTaskList);
} else {
    initTaskList();
}

// 🎬 视频播放器初始化
function initVideoPlayer() {
    const videos = document.querySelectorAll('.single-content video');
    if (!videos.length) return;

    videos.forEach(video => {
        // 检查是否已经初始化
        if (video.dataset.videoInitialized) return;
        video.dataset.videoInitialized = 'true';

        // 创建视频容器
        const container = document.createElement('div');
        container.className = 'video-container';
        video.parentNode.insertBefore(container, video);
        container.appendChild(video);

        // 创建播放按钮
        const playBtn = document.createElement('button');
        playBtn.className = 'play-btn';
        playBtn.innerHTML = '<i class="fa fa-play"></i>';
        container.appendChild(playBtn);

        // 创建控制栏
        const controls = document.createElement('div');
        controls.className = 'video-controls';
        container.appendChild(controls);

        // 创建进度条容器
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        controls.appendChild(progressContainer);

        // 创建进度条
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressContainer.appendChild(progressBar);

        // 创建看板娘元素
        const knb = document.createElement('div');
        knb.className = 'progress-knb';
        progressContainer.appendChild(knb);

        // 创建控制按钮组
        const btnGroup = document.createElement('div');
        btnGroup.className = 'video-btn-group';
        controls.appendChild(btnGroup);

        // 创建播放时间
        const timeDisplay = document.createElement('div');
        timeDisplay.className = 'video-time';
        timeDisplay.textContent = '00:00 / 00:00';
        btnGroup.appendChild(timeDisplay);

        // 创建播放/暂停按钮
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'video-btn';
        toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
        btnGroup.appendChild(toggleBtn);

        // 创建音量控制
        const volumeControl = document.createElement('div');
        volumeControl.className = 'volume-control';
        btnGroup.appendChild(volumeControl);

        // 创建音量按钮
        const volumeBtn = document.createElement('button');
        volumeBtn.className = 'video-btn';
        volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
        volumeControl.appendChild(volumeBtn);

        // 创建音量滑块
        const volumeSlider = document.createElement('input');
        volumeSlider.className = 'volume-slider';
        volumeSlider.type = 'range';
        volumeSlider.min = '0';
        volumeSlider.max = '1';
        volumeSlider.step = '0.1';
        volumeSlider.value = '1';
        volumeControl.appendChild(volumeSlider);
        
        // 🎯 创建倍速播放按钮
        const speedBtn = document.createElement('button');
        speedBtn.className = 'video-btn speed-btn';
        speedBtn.innerHTML = '<span>1.0x</span>';
        btnGroup.appendChild(speedBtn);
        
        // 🎯 创建倍速选择菜单
        const speedMenu = document.createElement('div');
        speedMenu.className = 'speed-menu';
        speedMenu.innerHTML = `
            <div class="speed-option" data-speed="0.5">0.5x</div>
            <div class="speed-option" data-speed="0.75">0.75x</div>
            <div class="speed-option active" data-speed="1">1.0x</div>
            <div class="speed-option" data-speed="1.25">1.25x</div>
            <div class="speed-option" data-speed="1.5">1.5x</div>
            <div class="speed-option" data-speed="1.75">1.75x</div>
            <div class="speed-option" data-speed="2">2.0x</div>
            <div class="speed-option" data-speed="2.5">2.5x</div>
            <div class="speed-option" data-speed="3">3.0x</div>
        `;
        controls.appendChild(speedMenu);

        // 创建网页全屏按钮
        const webFullscreenBtn = document.createElement('button');
        webFullscreenBtn.className = 'video-btn web-fullscreen-btn';
        webFullscreenBtn.innerHTML = '<i class="fa fa-arrows-alt"></i>';
        webFullscreenBtn.title = '网页全屏';
        btnGroup.appendChild(webFullscreenBtn);

        // 创建画中画按钮
        const pipBtn = document.createElement('button');
        pipBtn.className = 'video-btn pip-btn';
        pipBtn.innerHTML = '<i class="fa fa-clone"></i>';
        pipBtn.title = '画中画';
        btnGroup.appendChild(pipBtn);

        // 创建镜像画面按钮
        const mirrorBtn = document.createElement('button');
        mirrorBtn.className = 'video-btn mirror-btn';
        mirrorBtn.innerHTML = '<i class="fa fa-refresh"></i>';
        mirrorBtn.title = '镜像画面';
        btnGroup.appendChild(mirrorBtn);

        // 创建全屏按钮
        const fullscreenBtn = document.createElement('button');
        fullscreenBtn.className = 'video-btn fullscreen-btn';
        fullscreenBtn.innerHTML = '<i class="fa fa-expand"></i>';
        fullscreenBtn.title = '全屏';
        btnGroup.appendChild(fullscreenBtn);

        // 🎬 更新播放时间
        function updateTime() {
            const current = formatTime(video.currentTime);
            const duration = formatTime(video.duration);
            timeDisplay.textContent = `${current} / ${duration}`;
        }

        // 🎬 格式化时间
        function formatTime(seconds) {
            if (isNaN(seconds)) return '00:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // 🎬 更新进度条
        function updateProgress() {
            if (isNaN(video.duration)) return;
            const progress = (video.currentTime / video.duration) * 100;
            // 移除CSS过渡效果，提高更新流畅度
            progressBar.style.transition = 'none';
            knb.style.transition = 'none';
            progressBar.style.width = `${progress}%`;
            
            // 更新看板娘位置
            knb.style.left = `${progress}%`;
        }
        
        // 🎬 优化进度条更新机制，使用requestAnimationFrame提高流畅度
        let animationFrameId = null;
        function smoothUpdateProgress() {
            updateProgress();
            animationFrameId = requestAnimationFrame(smoothUpdateProgress);
        }
        
        // 🎬 开始流畅更新进度条
        function startSmoothProgress() {
            if (!animationFrameId) {
                smoothUpdateProgress();
            }
        }
        
        // 🎬 停止流畅更新进度条
        function stopSmoothProgress() {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        }

        // 🎬 播放/暂停切换
        function togglePlay() {
            if (video.paused || video.ended) {
                // 视频结束后重置时间
                if (video.ended) {
                    video.currentTime = 0;
                }
                // 播放视频并处理可能的错误
                video.play().catch(err => {
                    console.error(`Error attempting to play video: ${err.message}`);
                });
                toggleBtn.innerHTML = '<i class="fa fa-pause"></i>';
                playBtn.innerHTML = '<i class="fa fa-pause"></i>';
            } else {
                video.pause();
                toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
                playBtn.innerHTML = '<i class="fa fa-play"></i>';
            }
        }

        // 🎬 点击进度条跳转
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const percent = x / width;
            video.currentTime = percent * video.duration;
        });

        // 🎬 音量控制
        volumeSlider.addEventListener('input', () => {
            video.volume = volumeSlider.value;
            if (video.volume === 0) {
                volumeBtn.innerHTML = '<i class="fa fa-volume-off"></i>';
            } else if (video.volume < 0.5) {
                volumeBtn.innerHTML = '<i class="fa fa-volume-down"></i>';
            } else {
                volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
            }
        });

        // 🎬 音量按钮切换静音
        volumeBtn.addEventListener('click', () => {
            if (video.volume > 0) {
                volumeSlider.value = 0;
                video.volume = 0;
                volumeBtn.innerHTML = '<i class="fa fa-volume-off"></i>';
            } else {
                volumeSlider.value = 1;
                video.volume = 1;
                volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
            }
        });

        // 🎬 网页全屏功能 - 修复版本，改为占满浏览器视口
        let isWebFullscreen = false;
        webFullscreenBtn.addEventListener('click', () => {
            isWebFullscreen = !isWebFullscreen;
            const body = document.body;
            const html = document.documentElement;
            
            if (isWebFullscreen) {
                // 进入网页全屏模式（占满视口）
                // 保存原始样式
                body.dataset.originalOverflow = body.style.overflow;
                html.dataset.originalOverflow = html.style.overflow;
                body.dataset.originalMargin = body.style.margin;
                html.dataset.originalMargin = html.style.margin;
                
                // 设置为占满视口
                body.style.overflow = 'hidden';
                html.style.overflow = 'hidden';
                body.style.margin = '0';
                html.style.margin = '0';
                
                // 更新按钮图标
                webFullscreenBtn.innerHTML = '<i class="fa fa-compress"></i>';
            } else {
                // 退出网页全屏模式
                // 恢复原始样式
                body.style.overflow = body.dataset.originalOverflow || '';
                html.style.overflow = html.dataset.originalOverflow || '';
                body.style.margin = body.dataset.originalMargin || '';
                html.style.margin = html.dataset.originalMargin || '';
                
                // 清除自定义数据属性
                delete body.dataset.originalOverflow;
                delete html.dataset.originalOverflow;
                delete body.dataset.originalMargin;
                delete html.dataset.originalMargin;
                
                // 更新按钮图标
                webFullscreenBtn.innerHTML = '<i class="fa fa-arrows-alt"></i>';
            }
        });
        
        // 🎬 画中画功能
        pipBtn.addEventListener('click', () => {
            if (document.pictureInPictureElement) {
                // 退出画中画
                document.exitPictureInPicture().catch(err => {
                    console.error(`Error attempting to exit picture-in-picture: ${err.message}`);
                });
            } else {
                // 进入画中画
                if (video.requestPictureInPicture) {
                    video.requestPictureInPicture().catch(err => {
                        console.error(`Error attempting to enable picture-in-picture: ${err.message}`);
                    });
                }
            }
        });
        
        // 🎬 监听画中画状态变化
        video.addEventListener('enterpictureinpicture', () => {
            pipBtn.innerHTML = '<i class="fa fa-times"></i>';
        });
        
        video.addEventListener('leavepictureinpicture', () => {
            pipBtn.innerHTML = '<i class="fa fa-clone"></i>';
        });
        
        // 🎬 镜像画面功能
        let isMirrored = false;
        mirrorBtn.addEventListener('click', () => {
            isMirrored = !isMirrored;
            if (isMirrored) {
                video.style.transform = 'scaleX(-1)';
                mirrorBtn.innerHTML = '<i class="fa fa-check"></i>';
            } else {
                video.style.transform = '';
                mirrorBtn.innerHTML = '<i class="fa fa-refresh"></i>';
            }
        });

        // 🎬 全屏切换 - 修复版本
        fullscreenBtn.addEventListener('click', () => {
            // 使用容器元素进行全屏，确保菜单可见
            const targetElement = container;
            
            // 处理不同浏览器的全屏API兼容性
            const fullscreenApi = {
                request: targetElement.requestFullscreen || 
                         targetElement.webkitRequestFullscreen || 
                         targetElement.mozRequestFullScreen || 
                         targetElement.msRequestFullscreen,
                exit: document.exitFullscreen || 
                      document.webkitExitFullscreen || 
                      document.mozCancelFullScreen || 
                      document.msExitFullscreen,
                element: document.fullscreenElement || 
                         document.webkitFullscreenElement || 
                         document.mozFullScreenElement || 
                         document.msFullscreenElement
            };
            
            if (!fullscreenApi.element) {
                // 进入全屏
                if (fullscreenApi.request) {
                    fullscreenApi.request.call(targetElement).catch(err => {
                        console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                }
            } else {
                // 退出全屏
                if (fullscreenApi.exit) {
                    fullscreenApi.exit.call(document);
                }
            }
        });
        
        // 🎬 全屏状态变化 - 修复版本
        function handleFullscreenChange() {
            const fullscreenApi = {
                element: document.fullscreenElement || 
                         document.webkitFullscreenElement || 
                         document.mozFullScreenElement || 
                         document.msFullscreenElement
            };
            
            if (fullscreenApi.element) {
                // 更新普通全屏按钮状态
                fullscreenBtn.innerHTML = '<i class="fa fa-compress"></i>';
                
                // 全屏时添加特殊样式
                container.classList.add('fullscreen');
                // 确保视频在全屏容器中占满空间
                video.style.width = '100%';
                video.style.height = '100%';
                // 确保控制栏可见
                controls.style.opacity = '1';
                controls.style.transform = 'translateY(0)';
            } else {
                // 更新普通全屏按钮状态
                fullscreenBtn.innerHTML = '<i class="fa fa-expand"></i>';
                
                // 退出全屏时移除特殊样式
                container.classList.remove('fullscreen');
                // 恢复视频原始尺寸
                video.style.width = '';
                video.style.height = '';
                // 恢复控制栏的悬停显示效果
                controls.style.opacity = '';
                controls.style.transform = '';
            }
        }
        
        // 添加多浏览器兼容的全屏事件监听
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);
        
        // 🎯 倍速菜单交互逻辑
        let isSpeedMenuOpen = false;
        
        // 🎯 切换倍速菜单显示状态
        speedBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isSpeedMenuOpen = !isSpeedMenuOpen;
            
            if (isSpeedMenuOpen) {
                // 计算倍速按钮的位置，将菜单定位在按钮正上方
                const btnRect = speedBtn.getBoundingClientRect();
                const controlRect = controls.getBoundingClientRect();
                
                // 计算菜单位置：水平居中于按钮，垂直在按钮上方
                const left = btnRect.left - controlRect.left + btnRect.width / 2 - speedMenu.offsetWidth / 2;
                const bottom = controls.offsetHeight - btnRect.top + controlRect.top + btnRect.height + 10;
                
                // 设置菜单位置
                speedMenu.style.left = `${left}px`;
                speedMenu.style.right = 'auto';
                speedMenu.style.bottom = `${bottom}px`;
            }
            
            speedMenu.classList.toggle('show', isSpeedMenuOpen);
        });
        
        // 🎯 倍速选项点击事件
        speedMenu.addEventListener('click', (e) => {
            if (e.target.classList.contains('speed-option')) {
                const speed = parseFloat(e.target.dataset.speed);
                
                // 更新视频播放速度
                video.playbackRate = speed;
                
                // 更新倍速按钮显示
                speedBtn.innerHTML = `<span>${speed}x</span>`;
                
                // 更新选中状态
                speedMenu.querySelectorAll('.speed-option').forEach(option => {
                    option.classList.remove('active');
                });
                e.target.classList.add('active');
                
                // 关闭菜单
                isSpeedMenuOpen = false;
                speedMenu.classList.remove('show');
            }
        });
        
        // 🎯 点击外部关闭倍速菜单
        document.addEventListener('click', (e) => {
            if (!speedBtn.contains(e.target) && !speedMenu.contains(e.target)) {
                isSpeedMenuOpen = false;
                speedMenu.classList.remove('show');
            }
        });

        // 🎬 全屏状态下的菜单自动隐藏逻辑
        let menuHideTimer = null;
        let isMenuVisible = true;
        
        // 🎬 显示控制菜单
        function showControls() {
            if (!container.classList.contains('fullscreen')) return;
            
            clearTimeout(menuHideTimer);
            isMenuVisible = true;
            controls.style.opacity = '1';
            controls.style.transform = 'translateY(0)';
            
            // 显示播放按钮
            playBtn.style.opacity = '1';
            playBtn.style.transform = 'translate(-50%, -50%) scale(1)';
        }
        
        // 🎬 隐藏控制菜单
        function hideControls() {
            if (!container.classList.contains('fullscreen')) return;
            if (video.paused || video.ended) return;
            
            isMenuVisible = false;
            // 只改变透明度，不改变位置，确保点击区域不变
            controls.style.opacity = '0';
            controls.style.transform = 'translateY(0)';
            
            // 隐藏播放按钮
            playBtn.style.opacity = '0';
            playBtn.style.transform = 'translate(-50%, -50%) scale(1)';
        }
        
        // 🎬 延迟隐藏控制菜单
        function delayHideControls() {
            if (!container.classList.contains('fullscreen')) return;
            if (video.paused || video.ended) return;
            
            clearTimeout(menuHideTimer);
            menuHideTimer = setTimeout(hideControls, 3000); // 3秒后自动隐藏
        }
        
        // 🎬 事件监听
        // 使用requestAnimationFrame替代timeupdate事件，提高进度条流畅度
        video.addEventListener('play', () => {
            startSmoothProgress();
            // 播放开始后延迟隐藏菜单
            delayHideControls();
        });
        
        video.addEventListener('pause', () => {
            stopSmoothProgress();
            // 暂停时显示菜单
            showControls();
        });
        
        video.addEventListener('ended', () => {
            stopSmoothProgress();
            toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
            playBtn.innerHTML = '<i class="fa fa-play"></i>';
            // 结束时显示菜单
            showControls();
        });
        
        video.addEventListener('timeupdate', updateTime);
        video.addEventListener('loadedmetadata', updateTime);
        
        // 页面离开时清理资源
        window.addEventListener('beforeunload', () => {
            stopSmoothProgress();
            clearTimeout(menuHideTimer);
        });
        
        // 🎬 鼠标移动事件 - 显示控制菜单
        container.addEventListener('mousemove', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 鼠标离开事件 - 隐藏控制菜单
        container.addEventListener('mouseleave', () => {
            if (container.classList.contains('fullscreen')) {
                hideControls();
            }
        });
        
        // 🎬 控制菜单交互时保持显示
        controls.addEventListener('mousemove', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 控制按钮点击时保持显示
        btnGroup.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 进度条交互时保持显示
        progressContainer.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 倍速菜单交互时保持显示
        speedMenu.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });

        // 🎬 点击视频播放/暂停
        container.addEventListener('click', (e) => {
            // 确保点击的不是控制按钮或播放按钮
            if (!e.target.closest('.video-controls') && 
                !e.target.closest('.video-btn') && 
                !e.target.closest('.play-btn')) {
                togglePlay();
            }
        });
        
        // 🎬 播放/暂停按钮点击事件
        toggleBtn.addEventListener('click', togglePlay);
        
        // 🎬 中间播放按钮点击事件
        playBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // 阻止事件冒泡
            togglePlay();
        });
        
        // 🎬 控制栏事件委托 - 确保即使隐藏也能点击
        controls.addEventListener('click', (e) => {
            // 检查点击的是否是播放/暂停按钮或其子元素
            if (e.target.closest('.video-btn')) {
                // 显示控制菜单
                showControls();
                delayHideControls();
                // 如果点击的是播放/暂停按钮，触发播放/暂停
                if (e.target.closest('.video-btn') === toggleBtn || e.target.closest('.video-btn').querySelector('.fa-play, .fa-pause')) {
                    togglePlay();
                }
            }
        });
        
        // 🎬 确保视频可以交互
        video.style.pointerEvents = 'auto';
        // 只允许必要的事件，禁用原生控件
        video.controls = false;
        
        // 🎬 确保控制栏始终可点击
        controls.style.pointerEvents = 'auto';
        toggleBtn.style.pointerEvents = 'auto';

        // 🎬 加载看板娘图片
        function loadKnbImage() {
            // 检查是否开启了看板娘功能
            if (window.boxmoe_lolijump_switch === '1') {
                const knbImg = window.boxmoe_lolijump_img || 'lolisister1';
                let knbSrc = '';
                if (knbImg.startsWith('http') || knbImg.startsWith('//')) {
                    knbSrc = knbImg;
                } else {
                    knbSrc = `${window.ajax_object.themeurl}/assets/images/top/${knbImg}.gif`;
                }
                knb.style.backgroundImage = `url(${knbSrc})`;
            }
        }

        // 🎬 初始化看板娘
        loadKnbImage();
    });
}

// 🚀 回到顶部功能实现
function initBackToTop() {
    // 使用事件委托来确保即使元素动态生成也能正常工作
    document.addEventListener('click', function(e) {
        // 检查点击的是否是看板元素或其子元素
        const target = e.target.closest('#lolijump');
        if (target) {
            e.preventDefault();
            // 使用setTimeout确保事件冒泡完成
            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 0);
        }
    });
}

// 🔄 无限加载功能实现
function initInfiniteScroll() {
    // 检查是否启用了无限加载
    const isInfinite = document.querySelector('#infinite-load-container');
    if (!isInfinite) return;

    // 初始化变量
    let currentPage = 1;
    let isLoading = false;
    let hasMorePosts = true;
    const postsContainer = document.querySelector('.blog-post .row.g-4');
    const loader = document.getElementById('infinite-loader');
    const endMessage = document.getElementById('infinite-end-message');
    const pagenav = document.querySelector('.pagenav');

    // 隐藏传统分页
    if (pagenav) {
        pagenav.style.display = 'none';
    }

    // 加载更多文章的函数
    async function loadMorePosts() {
        if (isLoading || !hasMorePosts) return;

        isLoading = true;
        loader.style.display = 'flex';

        currentPage++;

        try {
            // 构建请求URL
            const url = new URL(window.location.href);
            url.searchParams.set('paged', currentPage);

            // 获取页面内容
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // 提取新文章
            const newPosts = doc.querySelectorAll('.blog-post .row.g-4 > div');

            if (newPosts.length === 0) {
                hasMorePosts = false;
                loader.style.display = 'none';
                endMessage.style.display = 'block';
                isLoading = false;
                return;
            }

            // 创建新文章容器
            const fragment = document.createDocumentFragment();
            
            // 获取已显示文章的ID
            const displayedPostIds = new Set();
            document.querySelectorAll('.post-list').forEach(postEl => {
                const postId = postEl.querySelector('.post-card-link')?.href?.match(/\/([0-9]+)\//)?.[1];
                if (postId) {
                    displayedPostIds.add(postId);
                }
            });
            
            // 为每篇新文章添加动画类，跳过已显示的文章
            let addedPostsCount = 0;
            newPosts.forEach(post => {
                // 从链接中提取文章ID
                const postLink = post.querySelector('.post-card-link');
                if (!postLink) return;
                
                const postUrl = postLink.href;
                const postIdMatch = postUrl.match(/\/([0-9]+)\//);
                const postId = postIdMatch ? postIdMatch[1] : null;
                
                // 检查文章是否已经显示过
                if (postId && displayedPostIds.has(postId)) {
                    return; // 跳过已显示的文章
                }
                
                // 设置初始状态
                post.style.opacity = '0';
                post.style.transform = 'translateY(50px)';
                
                // 添加到文档片段
                fragment.appendChild(post);
                addedPostsCount++;
                
                // 将新文章ID添加到已显示集合
                if (postId) {
                    displayedPostIds.add(postId);
                }
            });

            // 如果没有添加新文章，结束加载
            if (addedPostsCount === 0) {
                loader.style.display = 'none';
                isLoading = false;
                return;
            }

            // 将新文章添加到页面
            postsContainer.appendChild(fragment);

            // 添加CSS动画样式（如果不存在）
            if (!document.getElementById('infinite-scroll-styles')) {
                const style = document.createElement('style');
                style.id = 'infinite-scroll-styles';
                style.textContent = `
                    /* 加载动画样式 */
                    .infinite-loader {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        padding: 20px 0;
                    }
                    
                    .loader-inner {
                        width: 60px;
                        height: 60px;
                        position: relative;
                    }
                    
                    
                    .loader-line:nth-child(2) {
                        animation: pulse 1.5s ease-in-out infinite 0.5s;
                    }
                    
                    .loader-line:nth-child(3) {
                        animation: pulse 1.5s ease-in-out infinite 1s;
                    }
                    
                    @keyframes pulse {
                        0%, 100% {
                            opacity: 0.5;
                            transform: scaleY(1);
                        }
                        50% {
                            opacity: 1;
                            transform: scaleY(1.2);
                        }
                    }
                    
                    /* 文章上移渐显动画 */
                    .blog-post .row.g-4 > div {
                        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
                    }
                `;
                document.head.appendChild(style);
            }

            // 触发动画
            setTimeout(() => {
                const addedPosts = postsContainer.querySelectorAll('.row.g-4 > div');
                const lastPosts = Array.from(addedPosts).slice(-newPosts.length);
                
                lastPosts.forEach(post => {
                    post.style.opacity = '1';
                    post.style.transform = 'translateY(0)';
                });
                
                // 🎭 初始化新文章卡片的滚动放大效果
                if (typeof window.shirokiInitPostCards === 'function') {
                    window.shirokiInitPostCards();
                }
            }, 50);

            // 🎨 只对新添加的文章初始化懒加载图片，避免已加载图片闪烁
            if (typeof initLazyLoad === 'function') {
                // 获取新添加的文章元素
                const addedPosts = postsContainer.querySelectorAll('.row.g-4 > div');
                const lastPosts = Array.from(addedPosts).slice(-addedPostsCount);
                
                // 为新文章中的图片设置初始状态，防止闪烁
                lastPosts.forEach(post => {
                    const images = post.querySelectorAll('img.lazy');
                    images.forEach(img => {
                        // 确保新图片有正确的初始状态
                        if (!img.classList.contains('loaded')) {
                            img.style.opacity = '0';
                            img.style.transform = 'scale(0.8)';
                        }
                    });
                });
                
                // 使用修改后的initLazyLoad函数，只处理新文章中的图片
                lastPosts.forEach(post => {
                    if (typeof initLazyLoad === 'function') {
                        initLazyLoad(post);
                    }
                });
                
                // 立即加载新文章中的可见图片，提供更好的用户体验
                setTimeout(() => {
                    lastPosts.forEach(post => {
                        const images = post.querySelectorAll('img.lazy');
                        images.forEach(img => {
                            const rect = img.getBoundingClientRect();
                            // 如果图片在视口中或接近视口，立即加载
                            if (rect.top < window.innerHeight + 200 && rect.bottom > -200) {
                                // 触发图片加载
                                const loadEvent = new Event('load');
                                img.dispatchEvent(loadEvent);
                            }
                        });
                    });
                }, 100);
            }

            // 初始化新文章中的标签颜色
            if (typeof initTagColors === 'function') {
                initTagColors();
            }

        } catch (error) {
            console.error('无限加载已完成/失败:', error);
            hasMorePosts = false;
            endMessage.style.display = 'block';
        } finally {
            loader.style.display = 'none';
            isLoading = false;
        }
    }

    // 📜 滚动事件监听
    function handleScroll() {
        if (isLoading || !hasMorePosts) return;

        const scrollPosition = window.scrollY + window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        // 🔄 当滚动到页面50%时加载更多
        if (scrollPosition >= documentHeight * 0.5) {
            loadMorePosts();
        }
    }

    // 添加滚动事件监听器
    window.addEventListener('scroll', handleScroll);

    // 初始检查，确保页面内容不足时自动加载
    setTimeout(() => {
        handleScroll();
    }, 500);
}

// DOM加载完成后初始化
document.addEventListener("DOMContentLoaded", () => {
    const run = fn => { try { fn(); } catch(_) {} };
    run(initPreloader);
    run(initWow);
    run(initSearchBox);
    run(initLazyLoad);
    run(initMobileUserPanel);
    run(initBannerImage);
    run(initBannerRandomSwitch);
    run(initPostCoverImages);
    run(initStickyHeader);
    run(initTableOfContents);
    run(initTagColors);
    run(initHitokoto);
    run(initPostLikes);
    run(initReward);
    run(initPostFavorites);
    run(ThemeSwitcher.init);
    run(initPrettyPrint);
    run(initCodeCopy);
    run(initRunningDays);
    run(initTaskList);
    run(initVideoPlayer);
    run(initBackToTop);
    run(initInfiniteScroll);
    (function initGifFix(){
        try{
            const imgs = document.querySelectorAll('.single-content img');
            imgs.forEach(img => {
                const ds = img.dataset && img.dataset.src ? img.dataset.src : '';
                const src = img.getAttribute('src') || '';
                const target = (ds && /\.gif(\?.*)?$/i.test(ds)) ? ds : src;
                if (!target || !/\.gif(\?.*)?$/i.test(target)) return;
                let base = target; let query = '';
                const qm = base.match(/^(.*?)(\?.*)$/);
                if (qm) { base = qm[1]; query = qm[2]; }
                base = base.replace(/-\d+x\d+(?=\.gif$)/i, '');
                const fixed = base + query;
                if (img.hasAttribute('srcset')) img.removeAttribute('srcset');
                if (img.hasAttribute('sizes')) img.removeAttribute('sizes');
                if (img.classList.contains('lazy')) img.classList.remove('lazy');
                if (img.getAttribute('loading') === 'lazy') img.removeAttribute('loading');
                if (img.getAttribute('src') !== fixed) img.setAttribute('src', fixed);
            });
        }catch(_){}
    })();
    Fancybox.bind("[data-fancybox]", {});
    document.querySelectorAll('.switch-account-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const guestInputs = document.querySelector('.guest-inputs');
            if(guestInputs) {
                guestInputs.classList.toggle('active');
                btn.classList.toggle('active');
            }
        });
    });
    (function initCopyNoticeBanner(){
        let banner = document.querySelector('.copy-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.className = 'copy-banner';
            banner.innerHTML = '<i class="fa fa-copy"></i> 您拷贝了本站内容，记得注释来源哦~(￣y▽,￣)╭ 👍🏻';
            document.body.appendChild(banner);
        }
        let timer = null;
        const show = function(){
            if (timer) { try { clearTimeout(timer); } catch(_) {} }
            banner.classList.remove('mask-run');
            void banner.offsetWidth;
            banner.classList.add('mask-run');
            banner.classList.add('show');
            timer = setTimeout(function(){
                banner.classList.remove('show');
                banner.classList.remove('mask-run');
            }, 1500);
        };
        window._copyBannerShow = show;
        document.addEventListener('copy', show);
    })();
});

// 🎨 主题切换动画效果
function animateThemeToggle(btn, cur, nxt){
    try{
        var vw = window.innerWidth||document.documentElement.clientWidth;
        var vh = window.innerHeight||document.documentElement.clientHeight;
        var r = btn && btn.getBoundingClientRect ? btn.getBoundingClientRect() : { left: vw/2, top: 60, width: 0, height: 0 };
        var cx = Math.round(r.left + r.width/2);
        var cy = Math.round(r.top + r.height/2);
        var dx = Math.max(cx, vw - cx);
        var dy = Math.max(cy, vh - cy);
        var radius = Math.ceil(Math.hypot(dx, dy));
        var prevBg = (function(){
            try{
                var cs = window.getComputedStyle(document.body);
                var bgFull = cs.getPropertyValue('background');
                var bgImg = cs.getPropertyValue('background-image');
                var bgCol = cs.getPropertyValue('background-color');
                var val = String(bgFull||'').trim();
                if (val) return val;
                if (String(bgImg||'').trim() && String(bgCol||'').trim()) return String(bgImg).trim() + ', ' + String(bgCol).trim();
                if (String(bgImg||'').trim()) return String(bgImg).trim();
                if (String(bgCol||'').trim()) return String(bgCol).trim();
            }catch(_){}
            try{
                var rs = window.getComputedStyle(document.documentElement);
                var varBg = rs.getPropertyValue('--ish-bg');
                if (varBg && String(varBg).trim()) return String(varBg).trim();
            }catch(_){}
            try{ var s2 = window.getComputedStyle(document.documentElement).backgroundColor; if (s2) return s2; }catch(_){}
            return cur==='dark' ? 'rgb(18, 18, 18)' : 'rgb(255, 255, 255)';
        })();
        var overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.zIndex = '-1';
        overlay.style.pointerEvents = 'none';
        overlay.style.background = prevBg;
        overlay.style.willChange = 'clip-path';
        overlay.style.clipPath = 'circle('+radius+'px at '+cx+'px '+cy+'px)';
        overlay.style.transition = 'clip-path 520ms ease-in-out';
        if (document.body.firstChild) { document.body.insertBefore(overlay, document.body.firstChild); } else { document.body.appendChild(overlay); }
        requestAnimationFrame(function(){ overlay.style.clipPath = 'circle(0px at '+cx+'px '+cy+'px)'; });
        var cleanup = function(){ overlay.removeEventListener('transitionend', cleanup); if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); };
        overlay.addEventListener('transitionend', cleanup);
    }catch(_){}
}

// 🌈 Banner打字动画效果
// 🔒 使用立即执行函数表达式(IIFE)确保动画只执行一次，防止重复执行
(function() {

    
    // 🔒 全局唯一标记，确保无论脚本加载多少次，动画只执行一次
    if (window.boxmoeBannerAnimationExecuted) {
        return;
    }
    window.boxmoeBannerAnimationExecuted = true;
    
    // 🔒 移除可能存在的旧动画实例
    if (window.boxmoeBannerAnimation) {
        window.boxmoeBannerAnimation.stop();
    }
    
    // 🔒 禁用外部脚本对动画的干扰
    // 防止其他脚本意外重置或修改动画状态
    Object.defineProperty(window, 'boxmoeBannerAnimationExecuted', {
        writable: false,
        configurable: false,
        value: true
    });
    
    // 🔒 在任何情况下都只允许一个动画容器
    function ensureSingleBannerElement() {
        // 🔍 查找所有banner元素
        const allBannerElements = document.querySelectorAll('.boxmoe-typing-animation');
        
        // 🔒 如果存在多个元素，只保留第一个，移除其他重复元素
        if (allBannerElements.length > 1) {
            for (let i = 1; i < allBannerElements.length; i++) {
                allBannerElements[i].remove();
            }
        }
        
        // 🔒 确保只有一个元素
        return document.querySelector('.boxmoe-typing-animation');
    }
    
    // 🔒 动画初始化函数
    function initBannerTypingAnimation() {
        // 🔒 获取唯一的banner元素
        const target = ensureSingleBannerElement(); // ⬅️ 获取打字动画容器
        if (!target) {
            return;
        }
        
        // 🔒 确保元素没有被初始化过
        if (target.__bannerAnimationInitialized) {
            return;
        }
        
        // 🔒 确保元素没有动画标记
        if (target.hasAttribute('data-banner-animation-done')) {
            return;
        }
        

        
        // 🔒 设置可修改的元素标记，防止外部脚本干扰
        Object.defineProperty(target, '__bannerAnimationInitialized', {
            writable: true,
            configurable: true,
            value: true
        });
        
        // 🔒 设置动画完成标记
        target.setAttribute('data-banner-animation-done', 'true');
        
        const text = target.getAttribute('data-text'); // ⬅️ 获取要显示的文字
        if (!text) {
            return;
        }

        // 🎨 彩虹打字banner欢迎语的配色方案
        const colors = [
            '#FF0000', '#FF7F00', '#7ef0ffff', '#67b5ffff', '#0000FF', '#4B0082', '#9400D3',
            '#FF1493', '#00CED1', '#ff8a8aff', '#003375ff', '#FF69B4'
        ];

        // 将字符串转换为字符数组，正确处理emoji表情
        let textArray;
        try {
            textArray = Array.from(text);
        } catch (e) {
            // 使用传统方式生成字符数组作为备选
            textArray = text.split('');
        }
        
        // 🔧 创建一个统一的参考文本，用于比较
        const referenceText = textArray.join('');
        
        let isDeleting = false;
        let charIndex = 0;
        let lastColor = '';

        // 🎲 获取随机颜色，避免与上一个颜色相同
        function getRandomColor() {
            let newColor;
            do {
                newColor = colors[Math.floor(Math.random() * colors.length)];
            } while (newColor === lastColor);
            lastColor = newColor;
            return newColor;
        }

        // ⌨️ 打字动画逻辑
        let animationRunning = false; // 🔒 确保只有一个动画实例在运行
        let currentText = ''; // 🔍 跟踪当前已显示的文字
        
        // 🔒 检查并清理重复内容
        function cleanDuplicateText() {
            return false; // 临时禁用，避免干扰动画正常执行
        }
        
        // 🔒 确保动画状态变量不会被外部修改
        let animationState = {
            isDeleting: false,
            charIndex: 0,
            currentText: ''
        };
        
        // 🛡️ 使用Object.defineProperty保护关键状态变量
        if (!window.hasOwnProperty('__boxmoeBannerAnimationState')) {
            Object.defineProperty(window, '__boxmoeBannerAnimationState', {
                get: () => animationState,
                configurable: false
            });
        }
        
        function type() {
            // 🔒 防止动画函数被多次调用
            if (animationRunning) {
                return;
            }
            
            animationRunning = true;
            
            try {
                // 🔄 同步内部状态和DOM实际状态
                const spans = target.querySelectorAll('span');
                const actualSpansCount = spans.length;
                

                
                // 🎯 输入模式
                if (!animationState.isDeleting) {
                    if (animationState.charIndex < textArray.length) {
                        // ⌨️ 打字阶段
                        const nextChar = textArray[animationState.charIndex];
                        
                        // 直接添加字符，简化逻辑
                        const span = document.createElement('span');
                        span.style.color = getRandomColor();
                        span.textContent = nextChar;
                        target.appendChild(span);
                        
                        // 更新状态
                        animationState.currentText += nextChar;
                        animationState.charIndex++;
                        
                        // ⏱️ 继续打字
                        setTimeout(() => {
                            animationRunning = false;
                            type();
                        }, 200);
                    } else {
                        // ✅ 打字完成，准备删除
                        animationState.isDeleting = true;
                        
                        // 🔒 保存关键状态到DOM，防止变量被外部修改
                        target.setAttribute('data-animation-state', JSON.stringify({
                            isDeleting: true,
                            charIndex: animationState.charIndex
                        }));
                        
                        // ⏱️ 等待3秒后开始删除
                        setTimeout(() => {
                            // 🔄 从DOM恢复状态，确保isDeleting为true
                            const savedState = target.getAttribute('data-animation-state');
                            if (savedState) {
                                try {
                                    const parsedState = JSON.parse(savedState);
                                    animationState.isDeleting = parsedState.isDeleting;
                                    animationState.charIndex = parsedState.charIndex;
                                } catch (e) {
                                    // 忽略解析错误，继续执行
                                }
                            }
                            
    
                            animationRunning = false;
                            type();
                        }, 3000);
                    }
                }
                // 🗑️ 删除模式
                else {
                    // 🔒 确保charIndex与实际span数量同步
                    animationState.charIndex = actualSpansCount;
                    

                    
                    if (animationState.charIndex > 0) {
                        // 🗑️ 删除阶段
                        if (actualSpansCount > 0) {
                            spans[actualSpansCount - 1].remove();
                            animationState.currentText = animationState.currentText.slice(0, -1);
                        }
                        
                        // 🔒 确保charIndex正确递减
                        animationState.charIndex = Math.max(0, animationState.charIndex - 1);
                        
                        // ⏱️ 继续删除
                        setTimeout(() => {
                            animationRunning = false;
                            type();
                        }, 100);
                    } else {
                        // 🧹 删除完成，准备重新开始
                        
                        // 重置所有状态
                        animationState.isDeleting = false;
                        animationState.charIndex = 0;
                        animationState.currentText = '';
                        
                        // 🔒 清除DOM中的状态
                        target.removeAttribute('data-animation-state');
                        
                        // ⏱️ 等待500ms后重新开始
                        setTimeout(() => {
                            animationRunning = false;
                            type();
                        }, 500);
                    }
                }
            } catch (e) {
                animationRunning = false;
                // 🔄 出错后重置状态，防止卡死
                setTimeout(() => {
                    // 🔒 重置所有状态
                    animationState.isDeleting = false;
                    animationState.charIndex = 0;
                    animationState.currentText = '';
                    target.innerHTML = '';
                    target.removeAttribute('data-animation-state');
                    type();
                }, 1000);
            }
        }

        // 🔒 启动动画前的准备：
        // 只有在第一次启动时才清空容器，避免影响正在运行的动画
        if (!window.boxmoeCurrentAnimationInstance) {
            // 🔍 检查容器是否已有内容
            const existingSpans = target.querySelectorAll('span');
            if (existingSpans.length === 0) {
                target.innerHTML = '';
                charIndex = 0;
                isDeleting = false;
                currentText = '';
            }
            
            window.boxmoeCurrentAnimationInstance = true;
            type(); // ⬅️ 启动动画
        }
    }
    
    // 🔒 全局动画控制对象
    window.boxmoeBannerAnimation = {
        isRunning: false,
        target: null,
        animationTimeout: null,
        
        // 🔒 停止并清空动画
        stop: function() {
            if (this.target) {
                // 清空容器
                this.target.innerHTML = '';
                
                // 重置状态
                this.target.__bannerAnimationInitialized = false;
                this.target.removeAttribute('data-banner-animation-done');
            }
            
            // 清除任何挂起的超时
            if (this.animationTimeout) {
                clearTimeout(this.animationTimeout);
                this.animationTimeout = null;
            }
            
            // 重置全局状态
            window.boxmoeCurrentAnimationInstance = false;
            this.isRunning = false;
        },
        
        // 🔒 启动动画
        start: function() {
            if (!this.isRunning) {
                // 重置全局状态
                window.boxmoeCurrentAnimationInstance = false;
                // 初始化动画
                initBannerTypingAnimation();
                this.isRunning = true;
            }
        }
    };
    
    // 🔒 初始化动画并保存目标元素
    function initBannerTypingAnimationWithSave() {
        initBannerTypingAnimation();
        // 保存目标元素
        window.boxmoeBannerAnimation.target = document.querySelector('.boxmoe-typing-animation');
    }
    
    // 🔒 在DOM加载完成后执行动画初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBannerTypingAnimationWithSave);
    } else {
        // 🔒 如果DOM已经加载完成，立即执行
        initBannerTypingAnimationWithSave();
    }
    
    // 🔒 监听页面可见性变化
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // 🔒 当页面隐藏时，停止并清空动画
            window.boxmoeBannerAnimation.stop();
        } else {
            // 🔒 当页面显示时，重新启动动画
            window.boxmoeBannerAnimation.start();
        }
    });
})();

// 🔐 初始化登录状态管理
document.addEventListener('DOMContentLoaded', function() {
    if (typeof LoginStatusManager !== 'undefined') {
        LoginStatusManager.init();
    }
});

// 🎯 修复用户信息容器从其他标签页回到页面时的渲染问题
document.addEventListener('DOMContentLoaded', function() {
    // 🎨 获取所有用户信息容器元素
    const userInfoWraps = document.querySelectorAll('.user-info-wrap');
    
    // 🔄 监听页面可见性变化
    document.addEventListener('visibilitychange', function() {
        // 📱 当页面从隐藏变为可见时，强制重绘用户信息容器
        if (!document.hidden && userInfoWraps.length > 0) {
            userInfoWraps.forEach(wrap => {
                // 🎨 强制重绘方法：先移除再添加transform属性
                const currentTransform = window.getComputedStyle(wrap).transform;
                const currentBackdropFilter = window.getComputedStyle(wrap).backdropFilter;
                
                // 🔄 临时移除backdrop-filter，然后重新应用
                wrap.style.backdropFilter = 'none';
                wrap.style.transform = 'scale(0.99)';
                
                // ⏱️ 短暂延迟后恢复原始状态
                setTimeout(() => {
                    wrap.style.backdropFilter = currentBackdropFilter || 'blur(10px)';
                    wrap.style.transform = currentTransform || 'translateZ(0)';
                    
                    // 🔄 再次强制重绘，确保效果应用
                    wrap.offsetHeight;
                }, 50);
            });
        }
    });
    
    // 🔄 监听页面获得焦点事件（从其他标签页切换回来时）
    window.addEventListener('focus', function() {
        if (userInfoWraps.length > 0) {
            userInfoWraps.forEach(wrap => {
                // 🎨 强制重绘方法：触发reflow
                wrap.offsetHeight; // 触发reflow
                
                // 🔄 临时修改样式强制重绘
                const originalOpacity = wrap.style.opacity;
                wrap.style.opacity = '0.99';
                
                // ⏱️ 短暂延迟后恢复原始状态
                setTimeout(() => {
                    wrap.style.opacity = originalOpacity || '';
                    
                    // 🔄 再次强制重绘
                    wrap.offsetHeight;
                }, 50);
            });
        }
    });
    
    // 🔄 监听页面加载完成事件，确保初始渲染正确
    window.addEventListener('load', function() {
        if (userInfoWraps.length > 0) {
            userInfoWraps.forEach(wrap => {
                // 🎨 强制重绘确保backdrop-filter正确应用
                wrap.offsetHeight;
                
                // 🔄 确保子元素正确显示
                const userAvatar = wrap.querySelector('.user-avatar');
                const userInfo = wrap.querySelector('.user-info');
                
                if (userAvatar) {
                    userAvatar.style.zIndex = '2';
                    userAvatar.offsetHeight;
                }
                
                if (userInfo) {
                    userInfo.style.zIndex = '2';
                    userInfo.offsetHeight;
                }
            });
        }
    });
});

// 🎯 修复用户信息下拉菜单悬停问题
(function() {
    try {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDropdownMenus);
        } else {
            initDropdownMenus();
        }
    } catch (error) {
        console.error('下拉菜单初始化错误:', error);
    }

    function initDropdownMenus() {
        try {
            const loggedUserWrappers = document.querySelectorAll('.logged-user-wrapper');
            
            if (!loggedUserWrappers || loggedUserWrappers.length === 0) {
                // console.log('未找到用户信息容器');
                return;
            }
            
            loggedUserWrappers.forEach(function(wrapper) {
                try {
                    const userInfoWrap = wrapper.querySelector('.user-info-wrap');
                    const dropdownMenu = wrapper.querySelector('.dropdown-menu');
                    
                    if (!userInfoWrap || !dropdownMenu) {
                        console.warn('用户信息容器或下拉菜单未找到');
                        return;
                    }
                    
                    let hoverTimeout;
                    
                    // 🔄 鼠标进入用户信息容器时显示下拉菜单
                    userInfoWrap.addEventListener('mouseenter', function() {
                        try {
                            clearTimeout(hoverTimeout);
                            dropdownMenu.style.display = 'block';
                            dropdownMenu.style.opacity = '1';
                            dropdownMenu.style.visibility = 'visible';
                        } catch (e) {
                            console.error('显示下拉菜单错误:', e);
                        }
                    });
                    
                    // 🔄 鼠标离开用户信息容器时，延迟隐藏下拉菜单
                    userInfoWrap.addEventListener('mouseleave', function() {
                        try {
                            clearTimeout(hoverTimeout);
                            hoverTimeout = setTimeout(function() {
                                try {
                                    // 检查鼠标是否在下拉菜单上
                                    if (!isElementHovered(dropdownMenu)) {
                                        hideDropdownMenu(dropdownMenu);
                                    }
                                } catch (e) {
                                    console.error('检查悬停状态错误:', e);
                                    hideDropdownMenu(dropdownMenu);
                                }
                            }, 100);
                        } catch (e) {
                            console.error('mouseleave事件处理错误:', e);
                        }
                    });
                    
                    // 🔄 鼠标进入下拉菜单时保持显示
                    dropdownMenu.addEventListener('mouseenter', function() {
                        try {
                            clearTimeout(hoverTimeout);
                            dropdownMenu.style.display = 'block';
                            dropdownMenu.style.opacity = '1';
                            dropdownMenu.style.visibility = 'visible';
                        } catch (e) {
                            console.error('下拉菜单mouseenter错误:', e);
                        }
                    });
                    
                    // 🔄 鼠标离开下拉菜单时隐藏
                    dropdownMenu.addEventListener('mouseleave', function() {
                        try {
                            clearTimeout(hoverTimeout);
                            hoverTimeout = setTimeout(function() {
                                try {
                                    // 检查鼠标是否在用户信息容器上
                                    if (!isElementHovered(userInfoWrap)) {
                                        hideDropdownMenu(dropdownMenu);
                                    }
                                } catch (e) {
                                    console.error('检查用户信息容器悬停状态错误:', e);
                                    hideDropdownMenu(dropdownMenu);
                                }
                            }, 100);
                        } catch (e) {
                            console.error('下拉菜单mouseleave错误:', e);
                        }
                    });
                    
                } catch (error) {
                    console.error('处理用户信息容器错误:', error);
                }
            });
            
        } catch (error) {
            console.error('下拉菜单初始化错误:', error);
        }
    }
    
    function isElementHovered(element) {
        try {
            if (!element) return false;
            return element.matches && element.matches(':hover');
        } catch (e) {
            return false;
        }
    }
    
    function hideDropdownMenu(menu) {
        try {
            if (menu) {
                menu.style.display = 'none';
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
            }
        } catch (e) {
            console.error('隐藏下拉菜单错误:', e);
        }
    }
})();

