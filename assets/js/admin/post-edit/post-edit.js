/* 🕊️白木 原创开发 🔗gl.baimu.live */
/* 📝 写文章/编辑文章页面交互增强 - PHP控制布局版本 */
/* 🎨 布局由PHP直接控制，JS只负责交互增强 */

/**
 * 🎯 文章编辑页面交互增强管理器
 */
const ShirokiPostEdit = {
    /* 📦 DOM元素缓存 */
    elements: {},

    /**
     * 🚀 初始化
     */
    init() {
        this.cacheElements();
        this.moveSidebarMetaboxes(); /* ◀️ 将侧栏元框移入自定义三栏 */
        this.moveToolbarElements(); /* ◀️ 将工具栏元素移动到对应容器 */
        this.organizeTimestampFields();
        setTimeout(() => this.organizeTimestampFields(), 500);
        this.initToolbarSticky(); /* ◀️ 工具栏滚动时固定在可视区域 */
        this.bindEvents();
        this.enhanceUI();
    },

    organizeTimestampFields() {
        const timestampWrap = document.querySelector('#timestampdiv .timestamp-wrap');
        if (!timestampWrap || timestampWrap.classList.contains('shiroki-timestamp-organized')) {
            return;
        }

        const getLabel = id => {
            const field = timestampWrap.querySelector(`#${id}`);
            return field ? field.closest('label') : null;
        };
        const year = getLabel('aa');
        const month = getLabel('mm');
        const day = getLabel('jj');
        const hour = getLabel('hh');
        const minute = getLabel('mn');
        if (!year || !month || !day || !hour || !minute) {
            return;
        }

        const dateRow = document.createElement('div');
        const timeRow = document.createElement('div');
        dateRow.className = 'shiroki-timestamp-row shiroki-timestamp-date-row';
        timeRow.className = 'shiroki-timestamp-row shiroki-timestamp-time-row';

        const appendField = (row, label, suffix) => {
            row.appendChild(label);
            if (suffix) {
                const unit = document.createElement('span');
                unit.className = 'shiroki-timestamp-unit';
                unit.textContent = suffix;
                row.appendChild(unit);
            }
        };

        appendField(dateRow, year, '年');
        appendField(dateRow, month, '');
        appendField(dateRow, day, '日');
        appendField(timeRow, hour, '');

        const separator = document.createElement('span');
        separator.className = 'shiroki-timestamp-separator';
        separator.textContent = ':';
        timeRow.appendChild(separator);
        appendField(timeRow, minute, '');

        timestampWrap.replaceChildren(dateRow, timeRow);
        timestampWrap.classList.add('shiroki-timestamp-organized');
    },

    /**
     * 📦 将 WP 侧栏元框移入自定义右侧栏，避免与原生 columns-2 双侧重叠挤窄编辑区
     */
    moveSidebarMetaboxes() {
        const sidebar = document.getElementById('shiroki-editor-sidebar');
        const sideSortables = document.getElementById('side-sortables');
        if (!sidebar || !sideSortables) {
            return;
        }

        if (!sidebar.contains(sideSortables)) {
            sidebar.appendChild(sideSortables);
        }

        /* 🧹 隐藏已腾空的原生侧栏容器，并解除 columns-2 预留边距 */
        const postBody = document.getElementById('post-body');
        const container1 = document.getElementById('postbox-container-1');
        if (postBody) {
            postBody.classList.remove('columns-2');
            postBody.style.width = '100%';
            postBody.style.marginRight = '0';
        }
        if (container1) {
            container1.style.display = 'none';
        }

        const postBodyContent = document.getElementById('post-body-content');
        if (postBodyContent) {
            postBodyContent.style.marginRight = '0';
            postBodyContent.style.width = '100%';
            postBodyContent.style.maxWidth = '100%';
            postBodyContent.style.float = 'none';
        }

        const layout = document.getElementById('shiroki-editor-layout');
        if (layout && (window.innerWidth <= 782 || layout.getBoundingClientRect().width <= 782)) {
            layout.style.setProperty('grid-template-columns', 'minmax(0, 1fr)', 'important');
            layout.style.setProperty('grid-template-areas', '"main" "sidebar"', 'important');
            layout.style.setProperty('width', '100%', 'important');
            layout.style.setProperty('max-width', '100%', 'important');
            const toolbarColumn = layout.querySelector('.shiroki-editor-toolbar-column');
            if (toolbarColumn) {
                toolbarColumn.style.setProperty('display', 'none', 'important');
            }
            sidebar.style.setProperty('width', '100%', 'important');
            sidebar.style.setProperty('max-width', '100%', 'important');
            sidebar.style.setProperty('position', 'relative', 'important');
            sidebar.style.setProperty('height', 'auto', 'important');
        }

        document.body.classList.add('shiroki-post-edit-layout-ready');
    },

    /**
     * 🧰 将工具栏元素移动到对应容器
     */
    moveToolbarElements() {
        /* 📎 移动【添加媒体】按钮到媒体容器 */
        const mediaButton = document.getElementById('insert-media-button');
        const mediaContent = document.getElementById('toolbar-media-content');
        if (mediaButton && mediaContent && !mediaContent.contains(mediaButton)) {
            mediaContent.appendChild(mediaButton);
        }

        /* 🔗 移动短代码选择器到短代码容器 */
        const shortcodeSelect = document.getElementById('short_code_select');
        const shortcodesContent = document.getElementById('toolbar-shortcodes-content');
        if (shortcodeSelect && shortcodesContent && !shortcodesContent.contains(shortcodeSelect)) {
            shortcodesContent.appendChild(shortcodeSelect);
        }

        /* 📝 移动 MD 工具栏到 MD 容器 */
        const mdToolbar = document.querySelector('.boxmoe-md-toolbar');
        const mdContent = document.getElementById('toolbar-md-content');
        if (mdToolbar && mdContent && !mdContent.contains(mdToolbar)) {
            mdContent.appendChild(mdToolbar);
        }

        /* ⌨️ 移动 Quicktags 工具栏到 Quicktags 容器 */
        const quicktagsToolbar = document.getElementById('ed_toolbar');
        const quicktagsContent = document.getElementById('toolbar-quicktags-content');
        if (quicktagsToolbar && quicktagsContent && !quicktagsContent.contains(quicktagsToolbar)) {
            quicktagsContent.appendChild(quicktagsToolbar);
        }

        /* 🔄 延迟重试，确保动态加载的元素也能被移动 */
        setTimeout(() => {
            this.retryMoveToolbarElements();
        }, 500);
    },

    /**
     * 🔄 重试移动工具栏元素（处理动态加载的元素）
     */
    retryMoveToolbarElements() {
        /* 📝 再次尝试移动 MD 工具栏 */
        const mdToolbar = document.querySelector('.boxmoe-md-toolbar');
        const mdContent = document.getElementById('toolbar-md-content');
        if (mdToolbar && mdContent && !mdContent.contains(mdToolbar)) {
            mdContent.appendChild(mdToolbar);
        }

        /* ⌨️ 再次尝试移动 Quicktags 工具栏 */
        const quicktagsToolbar = document.getElementById('ed_toolbar');
        const quicktagsContent = document.getElementById('toolbar-quicktags-content');
        if (quicktagsToolbar && quicktagsContent && !quicktagsContent.contains(quicktagsToolbar)) {
            quicktagsContent.appendChild(quicktagsToolbar);
        }

        /* 🗑️ 删除空的编辑器工具栏容器 */
        this.removeEmptyEditorTools();
    },

    /**
     * 🗑️ 删除空的编辑器工具栏容器
     */
    removeEmptyEditorTools() {
        const editorTools = document.getElementById('wp-content-editor-tools');
        if (editorTools) {
            /* 📎 检查媒体按钮是否还在内部（如果已被移走则删除） */
            const mediaButtons = editorTools.querySelector('#wp-content-media-buttons');
            const hasMediaButton = mediaButtons && mediaButtons.querySelector('#insert-media-button');

            /* 📝 检查 MD 工具栏是否还在内部 */
            const hasMdToolbar = editorTools.querySelector('.boxmoe-md-toolbar');

            /* ⌨️ 检查 Quicktags 工具栏是否还在内部 */
            const hasQuicktags = editorTools.querySelector('#ed_toolbar');

            /* 🗑️ 如果所有工具都已移走，则删除整个容器 */
            if (!hasMediaButton && !hasMdToolbar && !hasQuicktags) {
                editorTools.remove();
            }
        }
    },

    /**
     * 📌 工具栏滚动固定：保持在可视区域，且不超出 #shiroki-editor-layout
     */
    initToolbarSticky() {
        const layout = document.getElementById('shiroki-editor-layout');
        const toolbar = document.getElementById('shiroki-editor-toolbar');
        const column = toolbar ? toolbar.closest('.shiroki-editor-toolbar-column') : null;

        if (!layout || !toolbar || !column) {
            return;
        }

        const desktopQuery = window.matchMedia('(min-width: 1401px)');
        let frameId = 0;
        let isFixed = false;

        const getStickyTop = () => {
            if (!document.body.classList.contains('admin-bar')) {
                return 0;
            }
            return window.innerWidth <= 782 ? 46 : 32;
        };

        const clearFixed = () => {
            if (!isFixed) {
                column.style.minHeight = '';
                return;
            }

            isFixed = false;
            toolbar.classList.remove('is-sticky-fixed');
            toolbar.style.position = '';
            toolbar.style.top = '';
            toolbar.style.left = '';
            toolbar.style.width = '';
            column.style.minHeight = '';
        };

        const applyFixed = (top, left, width, toolbarHeight) => {
            column.style.minHeight = `${toolbarHeight}px`;
            toolbar.classList.add('is-sticky-fixed');
            toolbar.style.position = 'fixed';
            toolbar.style.top = `${top}px`;
            toolbar.style.left = `${left}px`;
            toolbar.style.width = `${width}px`;
            isFixed = true;
        };

        const updateToolbarSticky = () => {
            if (!desktopQuery.matches || getComputedStyle(toolbar).display === 'none') {
                clearFixed();
                return;
            }

            const stickyTop = getStickyTop();
            const layoutRect = layout.getBoundingClientRect();
            const columnRect = column.getBoundingClientRect();
            const toolbarHeight = toolbar.offsetHeight;

            /* 用列占位元素判断，避免 fixed 后 offsetParent 为 null 导致反复切换 */
            const shouldStick = columnRect.top < stickyTop && layoutRect.bottom > stickyTop;

            if (!shouldStick) {
                clearFixed();
                return;
            }

            const top = Math.min(stickyTop, layoutRect.bottom - toolbarHeight);
            applyFixed(top, columnRect.left, columnRect.width, toolbarHeight);
        };

        const scheduleUpdate = () => {
            if (frameId) {
                cancelAnimationFrame(frameId);
            }
            frameId = requestAnimationFrame(updateToolbarSticky);
        };

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate, { passive: true });

        /* 仅监听 layout，避免改 toolbar/column 尺寸时 ResizeObserver 自触发抖动 */
        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(scheduleUpdate);
            observer.observe(layout);
        }

        scheduleUpdate();
        setTimeout(scheduleUpdate, 500);
        setTimeout(scheduleUpdate, 1500);
    },

    /**
     * 📦 缓存DOM元素
     */
    cacheElements() {
        this.elements.titleInput = document.getElementById('title');
        this.elements.contentEditor = document.getElementById('content');
        this.elements.publishBtn = document.getElementById('publish');
        this.elements.savePostBtn = document.getElementById('save-post');
        this.elements.postForm = document.getElementById('post');
    },

    /**
     * 🔗 绑定事件
     */
    bindEvents() {
        /* 📝 标题输入框焦点效果 */
        if (this.elements.titleInput) {
            this.elements.titleInput.addEventListener('focus', () => {
                this.elements.titleInput.parentElement.classList.add('focused');
            });
            this.elements.titleInput.addEventListener('blur', () => {
                this.elements.titleInput.parentElement.classList.remove('focused');
            });
        }

        /* 📝 自动保存提示 */
        this.bindAutoSaveEvents();

        /* 📝 发布按钮确认 */
        if (this.elements.publishBtn) {
            this.elements.publishBtn.addEventListener('click', (e) => {
                const title = this.elements.titleInput ? this.elements.titleInput.value.trim() : '';
                if (!title) {
                    e.preventDefault();
                    this.showNotification('⚠️ 请输入文章标题', 'warning');
                    if (this.elements.titleInput) {
                        this.elements.titleInput.focus();
                    }
                }
            });
        }
    },

    /**
     * 🎨 增强UI效果
     */
    enhanceUI() {
        /* 📝 为元框添加图标 */
        this.addMetaBoxIcons();

        /* 📝 添加输入框动画 */
        this.addInputAnimations();

        /* 📝 增强按钮效果 */
        this.enhanceButtons();
    },

    /**
     * 🏷️ 为元框添加图标
     */
    addMetaBoxIcons() {
        const metaBoxIcons = {
            'submitdiv': '📦',
            'categorydiv': '🏷️',
            'tagsdiv-post_tag': '🏷️',
            'postimagediv': '🖼️',
            'postexcerpt': '📋',
            'pageparentdiv': '📁',
            'authordiv': '👤',
            'commentstatusdiv': '💬',
            'slugdiv': '🔗',
            'trackbacksdiv': '🔗',
            'postcustom': '⚙️',
            'commentstatusdiv': '💬',
            'commentsdiv': '💬',
            'revisionsdiv': '📜'
        };

        Object.keys(metaBoxIcons).forEach(id => {
            const metaBox = document.getElementById(id);
            if (metaBox) {
                const hndle = metaBox.querySelector('.hndle');
                if (hndle && !hndle.querySelector('.shiroki-metabox-icon')) {
                    const icon = document.createElement('span');
                    icon.className = 'shiroki-metabox-icon';
                    icon.textContent = metaBoxIcons[id];
                    hndle.insertBefore(icon, hndle.firstChild);

                    /* 🧹 移除图标后的空白文本节点 */
                    const nextNode = icon.nextSibling;
                    if (nextNode && nextNode.nodeType === Node.TEXT_NODE) {
                        nextNode.textContent = nextNode.textContent.replace(/^\s+/, '');
                    }
                }
            }
        });
    },

    /**
     * ✨ 添加输入框动画
     */
    addInputAnimations() {
        const inputs = document.querySelectorAll('#post input[type="text"], #post textarea, #post select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.closest('.postbox')?.classList.add('active');
            });
            input.addEventListener('blur', () => {
                input.closest('.postbox')?.classList.remove('active');
            });
        });
    },

    /**
     * 🔘 增强按钮效果
     */
    enhanceButtons() {
        /* 📝 为所有按钮添加涟漪效果 */
        const buttons = document.querySelectorAll('#post .button, #post .button-primary, #post .button-secondary');
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.createRipple(e, button);
            });
        });
    },

    /**
     * 🌊 创建涟漪效果
     */
    createRipple(e, button) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: shiroki-ripple 0.6s ease-out;
            pointer-events: none;
        `;

        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    },

    /**
     * 🔄 自动保存事件绑定
     */
    bindAutoSaveEvents() {
        /* 📝 监听WordPress自动保存事件 */
        jQuery(document).on('heartbeat-tick.autosave', (event, data) => {
            if (data && data.autosave) {
                this.showNotification('💾 自动保存成功', 'success');
            }
        });
    },

    /**
     * 📢 显示通知
     */
    showNotification(message, type = 'success') {
        /* 移除现有通知 */
        const existingNotification = document.querySelector('.shiroki-post-edit-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        /* 创建通知元素 */
        const notification = document.createElement('div');
        notification.className = `shiroki-post-edit-notification ${type}`;
        notification.textContent = message;

        const colors = {
            success: 'rgba(104, 211, 145, 0.9)',
            error: 'rgba(245, 101, 101, 0.9)',
            warning: 'rgba(251, 191, 36, 0.9)',
            info: 'rgba(99, 179, 237, 0.9)'
        };

        notification.style.cssText = `
            position: fixed;
            top: 50px;
            right: 20px;
            padding: 12px 24px;
            background: ${colors[type] || colors.success};
            color: white;
            border-radius: var(--admin-radius-md);
            font-weight: 500;
            z-index: 99999;
            animation: shiroki-notification-enter 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: var(--admin-shadow-lg);
        `;

        document.body.appendChild(notification);

        /* 3秒后移除 */
        setTimeout(() => {
            notification.style.animation = 'shiroki-notification-leave 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};

/* 🎬 添加动画样式 */
const style = document.createElement('style');
style.textContent = `
    @keyframes shiroki-notification-enter {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes shiroki-notification-leave {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    @keyframes shiroki-ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    /* 📝 元框激活状态 */
    .postbox.active {
        box-shadow: 0 0 0 2px var(--admin-primary-border), var(--admin-shadow-lg) !important;
    }
    /* 📝 标题输入框聚焦状态 */
    #titlediv.focused {
        box-shadow: 0 0 0 3px var(--admin-primary-shadow);
    }
`;
document.head.appendChild(style);

/* 🚀 DOM加载完成后初始化 */
function initShirokiPostEdit() {
    /* 🔍 检查是否在写文章或编辑文章页面 */
    const body = document.body;
    if (!body.classList.contains('post-new-php') && !body.classList.contains('post-php')) {
        return;
    }

    /* ✅ 初始化 */
    ShirokiPostEdit.init();
}

/* 🎯 页面加载完成后初始化 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShirokiPostEdit);
} else {
    initShirokiPostEdit();
}
