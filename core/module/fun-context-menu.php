<?php
/**
 * @link https://gl.baimu.live/
 * @package 灵阈研都-纸鸢社
 */


// 🥳 右键菜单功能
// 🔗 替换浏览器默认右键菜单
// 💕 仿主题设置风格

if (!defined('ABSPATH')) {
    exit;
}

function boxmoe_add_context_menu() {
    // 仅在前端加载
    if (is_admin()) return;
    ?>
    <style>
    /* 右键菜单容器 */
    #boxmoe-context-menu {
        display: block;
        position: fixed;
        z-index: 999999;
        width: 160px;
        background: #fff;
        box-shadow: 0 4px 15px rgba(125, 147, 178, 0.3);
        border-radius: 8px;
        padding: 6px 0;
        font-family: "Microsoft YaHei", sans-serif;
        font-size: 13px;
        border: 1px solid rgba(0,0,0,0.05);
        user-select: none;
        -webkit-user-select: none;
        opacity: 0;
        transform: scale(0.95);
        visibility: hidden;
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
        pointer-events: none; /* 初始不可点击 */
    }

    #boxmoe-context-menu.show {
        opacity: 1;
        transform: scale(1);
        visibility: visible;
        pointer-events: auto;
    }

    /* 菜单项 */
    .boxmoe-menu-item {
        padding: 8px 16px;
        cursor: pointer;
        color: #333;
        display: flex;
        align-items: center;
        transition: all 0.2s;
        position: relative;
    }

    .boxmoe-menu-item i {
        margin-right: 10px;
        width: 16px;
        text-align: center;
        color: #888;
        font-size: 14px;
    }

    .boxmoe-menu-item:hover {
        background-color: #ecf5ff;
        color: #409EFF;
    }

    .boxmoe-menu-item:hover i {
        color: #409EFF;
    }

    /* 分割线 */
    .boxmoe-menu-separator {
        height: 1px;
        background-color: #eee;
        margin: 4px 0;
    }

    /* 禁用状态 */
    .boxmoe-menu-item.disabled {
        color: #ccc;
        cursor: default;
    }
    .boxmoe-menu-item.disabled:hover {
        background-color: transparent;
        color: #ccc;
    }
    .boxmoe-menu-item.disabled i {
        color: #ccc;
    }
    
    /* 隐藏状态 */
    .boxmoe-menu-item.hidden, .boxmoe-menu-separator.hidden {
        display: none !important;
    }

    /* 🥳 暗黑模式适配 (粉色背景) */
    [data-bs-theme="dark"] #boxmoe-context-menu {
        background-color: #fce4ec;
        border-color: #f8bbd0;
    }
    [data-bs-theme="dark"] .boxmoe-menu-item {
        color: #ad1457;
    }
    [data-bs-theme="dark"] .boxmoe-menu-item:hover {
        background-color: #f8bbd0;
        color: #880e4f;
    }
    [data-bs-theme="dark"] .boxmoe-menu-item:hover i {
        color: #880e4f;
    }
    [data-bs-theme="dark"] .boxmoe-menu-separator {
        background-color: #f8bbd0;
    }

    </style>

    <!-- 引入 FontAwesome (如果主题没有引入的话，这里为了保险起见可以不强行引入，复用主题的) -->
    <!-- 假设主题已经引入了 FontAwesome，直接使用 fa-icon -->

    <div id="boxmoe-context-menu">
        <div class="boxmoe-menu-item hidden" id="ctx-open-new-tab"><i class="fa fa-external-link"></i> 新标签打开</div>
        <div class="boxmoe-menu-item hidden" id="ctx-copy-image"><i class="fa fa-file-image-o"></i> 复制图片</div>
        <div class="boxmoe-menu-item hidden" id="ctx-copy-image-link"><i class="fa fa-link"></i> 复制图片链接</div>
        <div class="boxmoe-menu-separator hidden" id="ctx-sep-media"></div>

        <div class="boxmoe-menu-item" id="ctx-back"><i class="fa fa-arrow-left"></i> 后退</div>
        <div class="boxmoe-menu-item" id="ctx-forward"><i class="fa fa-arrow-right"></i> 前进</div>
        <div class="boxmoe-menu-item" id="ctx-refresh"><i class="fa fa-refresh"></i> 刷新</div>
        <div class="boxmoe-menu-separator"></div>
        <div class="boxmoe-menu-item" id="ctx-copy"><i class="fa fa-copy"></i> 复制</div>
        <div class="boxmoe-menu-item" id="ctx-paste"><i class="fa fa-paste"></i> 粘贴</div>
        <div class="boxmoe-menu-item" id="ctx-select-all"><i class="fa fa-mouse-pointer"></i> 全选</div>
        <div class="boxmoe-menu-item" id="ctx-delete"><i class="fa fa-trash"></i> 删除</div>
        <div class="boxmoe-menu-separator"></div>
        <div class="boxmoe-menu-item" id="ctx-home"><i class="fa fa-home"></i> 返回首页</div>
    </div>
    
    <script>
    (function() {
        var menu = document.getElementById('boxmoe-context-menu');
        var isTextSelected = false;
        var isInputFocused = false;
        var currentLink = null;
        var currentImg = null;
        var clickedElement = null; // 存储触发右键的元素
        
        // 🥳 内部剪贴板变量（升级为 localStorage 以支持跨页）
        // 🔗 监听全局复制/剪切事件
        const updateInternalClipboard = (e) => {
            var selection = window.getSelection().toString();
            if (selection && selection.length > 0) {
                try {
                    localStorage.setItem('boxmoe_clipboard', selection);
                    localStorage.setItem('boxmoe_clipboard_time', Date.now());
                } catch(e) {}
            }
        };
        document.addEventListener('copy', updateInternalClipboard);
        document.addEventListener('cut', updateInternalClipboard);

        function showToast(msg, iconClass) {
            // 🥳 自动匹配图标
            if (!iconClass) {
                if (msg.includes('失败') || msg.includes('Error')) iconClass = 'fa-times-circle';
                else if (msg.includes('复制') || msg.includes('粘贴')) iconClass = 'fa-check-circle';
                else iconClass = 'fa-info-circle';
            }

            var banner = document.querySelector('.copy-banner');
            
            // 🥳 如果没有banner则创建 (兼容 boxmoe.js 未加载情况)
            if (!banner) {
                banner = document.createElement('div');
                banner.className = 'copy-banner';
                document.body.appendChild(banner);
            }

            // 🔗 保存默认文本以便恢复
            if (!banner.dataset.defaultText && banner.innerHTML.trim() !== '') {
                banner.dataset.defaultText = banner.innerHTML;
            }

            // 🥳 更新内容
            banner.innerHTML = '<i class="fa ' + iconClass + '"></i> ' + msg;

            // 🔗 调用全局显示函数
            if (window._copyBannerShow) {
                window._copyBannerShow();
            } else {
                // Fallback animation if boxmoe.js not loaded
                banner.classList.remove('mask-run');
                banner.classList.remove('show');
                void banner.offsetWidth;
                banner.classList.add('mask-run');
                banner.classList.add('show');
                setTimeout(function(){
                    banner.classList.remove('show');
                    banner.classList.remove('mask-run');
                }, 1500);
            }

            // 🥳 延迟恢复默认文本
            setTimeout(function() {
                if (banner.dataset.defaultText) {
                    banner.innerHTML = banner.dataset.defaultText;
                }
            }, 2000);
        }

        function copyText(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            } else {
                // Fallback
                var textarea = document.createElement("textarea");
                textarea.value = text;
                textarea.style.position = "fixed";
                textarea.style.left = "-9999px";
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    return Promise.resolve();
                } catch (err) {
                    document.body.removeChild(textarea);
                    return Promise.reject(err);
                }
            }
        }

        async function copyImage(src) {
            // 🥳 定义兼容模式复制函数 (execCommand)
            const legacyCopyImage = async (blob) => {
                try {
                    const reader = new FileReader();
                    reader.readAsDataURL(blob);
                    await new Promise(resolve => reader.onload = resolve);
                    const dataUrl = reader.result;

                    const div = document.createElement('div');
                    div.contentEditable = true;
                    div.style.position = 'fixed';
                    div.style.left = '-9999px';
                    
                    const img = document.createElement('img');
                    img.src = dataUrl;
                    div.appendChild(img);
                    document.body.appendChild(div);
                    
                    div.focus();
                    const range = document.createRange();
                    range.selectNodeContents(div);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    
                    const success = document.execCommand('copy');
                    document.body.removeChild(div);
                    if (success) {
                        showToast('图片已复制 (兼容模式)', 'fa-file-image-o');
                    } else {
                        throw new Error('ExecCommand failed');
                    }
                } catch (e) {
                     showToast('复制失败：您的浏览器完全不支持复制图片');
                }
            };

            // 🔗 统一写入逻辑
            const writeToClipboard = async (blob) => {
                 // 优先使用现代 API (仅在 HTTPS 或 localhost 可用)
                 if (navigator.clipboard && navigator.clipboard.write) {
                      try {
                          // Chrome 要求必须是 PNG
                          if (blob.type !== 'image/png') throw new Error('Need PNG');
                          await navigator.clipboard.write([new ClipboardItem({[blob.type]: blob})]);
                          showToast('图片已复制', 'fa-file-image-o');
                      } catch (e) {
                          // 如果是因为格式问题，交给 Canvas 转换
                          if (e.message === 'Need PNG') throw e;
                          // 其他 API 错误，降级到 legacy
                          console.warn('Clipboard API failed, fallback to legacy', e);
                          await legacyCopyImage(blob);
                      }
                 } else {
                      // HTTP 环境或旧浏览器
                      await legacyCopyImage(blob);
                 }
            };

            try {
                // 尝试直接 fetch (支持 CORS)
                const response = await fetch(src, {mode: 'cors', credentials: 'omit'});
                if (!response.ok) throw new Error('Network response was not ok');
                const blob = await response.blob();
                
                // 🔗 确保窗口聚焦
                try { window.focus(); } catch(e) {}
                
                await writeToClipboard(blob);

            } catch (err) {
                console.warn('Fetch copy failed/skipped, trying canvas...', err);
                // 尝试 Canvas (统一转为 PNG，处理 CORS 和 格式问题)
                try {
                    const img = new Image();
                    img.crossOrigin = "anonymous";
                    img.src = src;
                    await new Promise((resolve, reject) => {
                        img.onload = resolve;
                        img.onerror = reject;
                    });
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    
                    // 🥳 使用 Promise 包装 toBlob
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                    
                    if (!blob) {
                        showToast('复制失败：无法生成图片数据');
                        return;
                    }

                    // 🔗 再次确保聚焦
                    try { window.focus(); } catch(e) {}

                    await writeToClipboard(blob);

                } catch (e2) {
                    console.error(e2);
                    if (e2.name === 'NotAllowedError') {
                         showToast('复制失败：没有剪贴板权限，请点击页面后重试');
                    } else if (e2.name === 'SecurityError') {
                         showToast('复制失败：图片跨域限制。请 Shift+右键');
                    } else {
                         showToast('复制失败：' + (e2.message || '未知错误'));
                    }
                }
            }
        }

        document.addEventListener('contextmenu', function(e) {
            // 允许 Shift + 右键 呼出系统菜单
            if (e.shiftKey) return;
            
            e.preventDefault();
            
            // 记录点击的元素，关键！
            clickedElement = e.target;
            
            // 检测上下文状态
            var selection = window.getSelection().toString();
            isTextSelected = selection.length > 0;
            
            var target = e.target;
            isInputFocused = (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable);
            
            // 检测链接和图片
            currentLink = target.closest('a') ? target.closest('a').href : null;
            currentImg = target.tagName === 'IMG' ? target.src : null;

            // 更新菜单项状态
            updateMenuState();

            // 计算位置
            var x = e.clientX;
            var y = e.clientY;
            
            // 防止溢出屏幕
            var winWidth = window.innerWidth;
            var winHeight = window.innerHeight;
            var menuWidth = 160; // CSS定义的宽度
            var menuHeight = menu.offsetHeight || 200; // 估算高度

            if (x + menuWidth > winWidth) x = winWidth - menuWidth - 10;
            if (y + menuHeight > winHeight) y = winHeight - menuHeight - 10;

            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
            
            // 显示菜单
            menu.classList.add('show');
        });

        // 点击其他地方关闭菜单
        document.addEventListener('click', function(e) {
            menu.classList.remove('show');
        });

        // 滚动时关闭
        window.addEventListener('scroll', function() {
            menu.classList.remove('show');
        });

        function updateMenuState() {
            // 复制：有选中文本时可用
            setDisabled('ctx-copy', !isTextSelected);
            
            // 粘贴：输入框且剪贴板有内容
            setDisabled('ctx-paste', !isInputFocused);
            
            // 删除：仅在输入框中可用
            setDisabled('ctx-delete', !isInputFocused);

            // 后退/前进：检测历史记录
            setDisabled('ctx-back', window.history.length <= 1); 
            
            // 媒体和链接选项可见性
            setVisible('ctx-open-new-tab', !!currentLink);
            setVisible('ctx-copy-image', !!currentImg);
            setVisible('ctx-copy-image-link', !!currentImg);
            setVisible('ctx-sep-media', !!currentLink || !!currentImg);
        }

        function setDisabled(id, disabled) {
            var el = document.getElementById(id);
            if (disabled) {
                el.classList.add('disabled');
            } else {
                el.classList.remove('disabled');
            }
        }
        
        function setVisible(id, visible) {
             var el = document.getElementById(id);
             if (visible) el.classList.remove('hidden');
             else el.classList.add('hidden');
        }

        // 功能实现
        document.getElementById('ctx-back').addEventListener('click', function() {
            window.history.back();
        });

        document.getElementById('ctx-forward').addEventListener('click', function() {
            window.history.forward();
        });

        document.getElementById('ctx-refresh').addEventListener('click', function() {
            location.reload();
        });

        document.getElementById('ctx-copy').addEventListener('click', function() {
            if (isTextSelected) {
                document.execCommand('copy');
                // 🥳 确保内部剪贴板更新
                updateInternalClipboard();
                showToast('已复制', 'fa-copy');
            }
        });

        async function pasteToElement(text) {
             if (!text) return;
             if (clickedElement.tagName === 'INPUT' || clickedElement.tagName === 'TEXTAREA') {
                 // 尝试使用 execCommand 插入文本（支持撤销）
                 if (!document.execCommand('insertText', false, text)) {
                     // 降级方案：直接操作 value
                     var start = clickedElement.selectionStart;
                     var end = clickedElement.selectionEnd;
                     var val = clickedElement.value;
                     clickedElement.value = val.slice(0, start) + text + val.slice(end);
                     clickedElement.selectionStart = clickedElement.selectionEnd = start + text.length;
                     // 触发 input 事件以通知框架
                     clickedElement.dispatchEvent(new Event('input', { bubbles: true }));
                 }
             } else if (clickedElement.isContentEditable) {
                 document.execCommand('insertText', false, text);
             }
        }

        document.getElementById('ctx-paste').addEventListener('click', async function() {
            // 确保有点击元素且是输入框
            if (clickedElement && (clickedElement.tagName === 'INPUT' || clickedElement.tagName === 'TEXTAREA' || clickedElement.isContentEditable)) {
                // 🥳 恢复焦点
                // 🔗 先聚焦窗口，再聚焦元素，提高兼容性
                try { window.focus(); } catch(e) {}
                clickedElement.focus();
                
                // 尝试原生 paste 命令 (兼容部分旧浏览器或配置过的浏览器)
                try {
                    if (document.execCommand('paste')) return;
                } catch(e) {}

                // 尝试 IE 特有 API
                try {
                    if (window.clipboardData && window.clipboardData.getData) {
                        const text = window.clipboardData.getData('Text');
                        if (text) {
                            await pasteToElement(text);
                            return;
                        }
                    }
                } catch(e) {}

                // 尝试现代 Clipboard API & 智能降级
                try {
                    // 🔗 使用 Clipboard API 读取剪贴板
                    // 先判断当前环境是否支持
                    if (!navigator.clipboard || !navigator.clipboard.readText) {
                         throw new Error('Clipboard API not available');
                    }

                    // 🥳 尝试获取权限状态
                    try {
                        const permission = await navigator.permissions.query({ name: 'clipboard-read' });
                        if (permission.state === 'denied') {
                            throw new Error('NotAllowedError');
                        }
                    } catch(e) {}

                    const text = await navigator.clipboard.readText();
                    await pasteToElement(text);
                    
                } catch (err) {
                    console.error('无法读取剪贴板', err);
                    
                    // 🥳 兼容模式优先：尝试通过隐藏DOM获取系统剪贴板 (针对 HTTPS 下 API 拒绝 或 HTTP 环境)
                    // 🔗 这是一个 Hack，尝试触发浏览器的粘贴行为
                    let legacyText = null;
                    try {
                         legacyText = (() => {
                            const ta = document.createElement('textarea');
                            ta.style.position = 'fixed';
                            ta.style.left = '-9999px';
                            ta.style.top = '0';
                            document.body.appendChild(ta);
                            ta.focus();
                            
                            try {
                                // 尝试执行粘贴
                                document.execCommand('paste');
                                return ta.value;
                            } catch(e) {
                                return null;
                            } finally {
                                document.body.removeChild(ta);
                            }
                        })();
                    } catch(e) {}

                    if (legacyText && legacyText.length > 0) {
                         await pasteToElement(legacyText);
                         return;
                    }

                    // 🥳 智能降级：如果以上都失败，才尝试读取内部跨页剪贴板
                    // ⬅️ 只有当系统剪贴板完全无法获取时，才使用内部缓存
                    try {
                        const internalText = localStorage.getItem('boxmoe_clipboard');
                        if (internalText) {
                            await pasteToElement(internalText);
                            showToast('已粘贴站内内容 (外部内容受浏览器安全限制)', 'fa-paste');
                            return; 
                        }
                    } catch(e) {}

                    // 如果都失败了
                    showToast('粘贴失败：浏览器拒绝访问剪贴板，请尝试 Ctrl+V', 'fa-times-circle');
                }
            }
        });

        document.getElementById('ctx-select-all').addEventListener('click', function() {
            if (clickedElement && (clickedElement.tagName === 'INPUT' || clickedElement.tagName === 'TEXTAREA')) {
                clickedElement.focus();
                clickedElement.select();
            } else if (clickedElement && clickedElement.isContentEditable) {
                clickedElement.focus();
                document.execCommand('selectAll');
            } else {
                var range = document.createRange();
                range.selectNodeContents(document.body);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        });

        document.getElementById('ctx-delete').addEventListener('click', function() {
            if (clickedElement && (clickedElement.tagName === 'INPUT' || clickedElement.tagName === 'TEXTAREA' || clickedElement.isContentEditable)) {
                clickedElement.focus();
                document.execCommand('delete');
            }
        });

        document.getElementById('ctx-home').addEventListener('click', function() {
            window.location.href = '<?php echo home_url(); ?>';
        });
        
        // 新增功能
        document.getElementById('ctx-open-new-tab').addEventListener('click', function() {
            if (currentLink) window.open(currentLink, '_blank');
        });

        document.getElementById('ctx-copy-image-link').addEventListener('click', function() {
            if (currentImg) {
                copyText(currentImg).then(() => showToast('图片链接已复制')).catch(() => showToast('复制失败'));
            }
        });
        
        document.getElementById('ctx-copy-image').addEventListener('click', function() {
            if (currentImg) {
                copyImage(currentImg);
            }
        });

    })();
    </script>
    <?php
}
add_action('wp_footer', 'boxmoe_add_context_menu');
