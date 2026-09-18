/* 🕊️白木 原创开发 🔗gl.baimu.live */
/* 📝 页面列表网格卡片式布局JavaScript */
/* 🎨 拟态拟物玻璃质感交互逻辑 */

/**
 * 🎯 页面网格管理器
 */
const ShirokiPageGrid = {
    /* 📊 状态管理 */
    state: {
        page: 1,
        perPage: 20,
        hasMore: true,
        isLoading: false,
        selectedItems: new Set(),
        currentFilter: 'all',
        currentParent: '',
        currentSearch: '',
        currentStatus: 'all'
    },

    /* 📦 DOM元素缓存 */
    elements: {},

    /**
     * 🚀 初始化
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.bindBulkParentModalEvents();
        this.loadPages();
    },

    /**
     * 📦 缓存DOM元素
     */
    cacheElements() {
        this.elements.grid = document.getElementById('shiroki-post-grid');
        this.elements.empty = document.getElementById('shiroki-post-empty');
        this.elements.loading = document.getElementById('shiroki-post-loading');
        this.elements.search = document.getElementById('shiroki-post-search');
        this.elements.bulkActions = document.getElementById('shiroki-post-bulk-actions');
        this.elements.bulkCount = document.querySelector('.shiroki-post-bulk-count-num');
        this.elements.pagination = document.getElementById('shiroki-post-pagination');
    },

    /**
     * 🔗 绑定事件
     */
    bindEvents() {
        /* 🔍 搜索功能 */
        if (this.elements.search) {
            let searchTimeout;
            this.elements.search.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.state.currentSearch = e.target.value;
                    this.state.page = 1;
                    this.refreshGrid();
                }, 500);
            });
        }

        /* 📑 父页面筛选按钮 */
        document.querySelectorAll('#shiroki-post-parent-options .shiroki-post-category-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.currentTarget;
                document.querySelectorAll('#shiroki-post-parent-options .shiroki-post-category-btn').forEach(b => b.classList.remove('active'));
                target.classList.add('active');
                this.state.currentParent = target.dataset.parent;
                this.state.page = 1;
                this.refreshGrid();
            });
        });

        /* 📊 状态筛选按钮 */
        document.querySelectorAll('.shiroki-post-status-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.currentTarget;
                document.querySelectorAll('.shiroki-post-status-btn').forEach(b => b.classList.remove('active'));
                target.classList.add('active');
                this.state.currentStatus = target.dataset.status;
                this.state.page = 1;
                this.refreshGrid();
            });
        });

        /* 📦 批量操作 */
        document.querySelector('.shiroki-post-bulk-delete')?.addEventListener('click', () => {
            this.bulkDelete();
        });

        document.querySelector('.shiroki-post-bulk-cancel')?.addEventListener('click', () => {
            this.clearSelection();
        });

        /* 🔗 批量复制链接 */
        document.querySelector('.shiroki-post-bulk-copy-links')?.addEventListener('click', () => {
            this.bulkCopyLinks();
        });

        /* 📋 批量复制页面内容 */
        document.querySelector('.shiroki-post-bulk-copy-content')?.addEventListener('click', () => {
            this.bulkCopyContent();
        });

        /* 📑 批量修改父级页面 */
        document.querySelector('.shiroki-post-bulk-set-parent')?.addEventListener('click', () => {
            this.bulkSetParent();
        });

        /* 📄 分页 */
        if (this.elements.pagination) {
            this.elements.pagination.addEventListener('click', (e) => {
                if (e.target.classList.contains('shiroki-post-page-btn')) {
                    const page = parseInt(e.target.dataset.page);
                    if (page && page !== this.state.page) {
                        this.state.page = page;
                        this.loadPages();
                    }
                }
            });
        }
    },

    /**
     * 📡 加载页面列表
     */
    async loadPages() {
        if (this.state.isLoading) return;

        this.state.isLoading = true;
        this.showLoading();

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_get_pages');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page', this.state.page);
            formData.append('per_page', this.state.perPage);
            formData.append('status', this.state.currentStatus);

            if (this.state.currentParent !== '') {
                formData.append('parent', this.state.currentParent);
            }

            if (this.state.currentSearch) {
                formData.append('search', this.state.currentSearch);
            }

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.renderPages(data.data.pages);
                this.renderPagination(data.data.total_pages, data.data.current_page);
                this.state.hasMore = data.data.has_more;
            } else {
                console.error('❌ AJAX错误:', data);
                const errorMsg = (data.data && data.data.message) ? data.data.message : '加载失败';
                this.showError(errorMsg);
            }
        } catch (error) {
            console.error('❌ 加载页面失败:', error);
            console.error('错误详情:', error.message);
            this.showError('加载失败: ' + error.message);
        } finally {
            this.state.isLoading = false;
            this.hideLoading();
        }
    },

    /**
     * 🎨 渲染页面卡片
     */
    renderPages(pages) {
        if (!this.elements.grid) return;

        if (pages.length === 0 && this.state.page === 1) {
            this.elements.grid.innerHTML = '';
            this.elements.empty.style.display = 'flex';
            return;
        }

        this.elements.empty.style.display = 'none';

        if (this.state.page === 1) {
            this.elements.grid.innerHTML = '';
        }

        pages.forEach((page, index) => {
            const card = this.createPageCard(page, index);
            this.elements.grid.appendChild(card);
        });
    },

    /**
     * 🃏 创建页面卡片
     */
    createPageCard(page, index) {
        const card = document.createElement('div');
        card.className = 'shiroki-post-card';
        card.dataset.id = page.id;
        card.style.animationDelay = `${(index % 8) * 0.05}s`;

        /* 🖼️ 特色图片 */
        const thumbnailHtml = page.thumbnail
            ? `<img src="${page.thumbnail}" alt="${page.title}" loading="lazy">`
            : `<div class="shiroki-post-thumbnail-placeholder">📄</div>`;

        /* 🏷️ 状态标签 - 显示发布状态和特殊状态 */
        const statusTags = this.buildStatusTags(page);

        /* 🔐 密码保护标识 */
        const passwordBadge = page.password_protected ? '<span class="shiroki-post-password-badge">🔒</span>' : '';

        /* 📑 父页面勋章 - 显示父页面名称 */
        const parentBadge = page.parent ? this.buildParentBadge(page.parent) : '';

        /* 👶 子页面数量标识 */
        const childBadge = page.child_count > 0 ? `<span class="shiroki-post-category-badge" title="有 ${page.child_count} 个子页面">👶 ${page.child_count}</span>` : '';

        /* ✅ 检查是否已选中 */
        const isSelected = this.state.selectedItems.has(page.id);

        /* 📋 卡片HTML */
        card.innerHTML = `
            <div class="shiroki-post-thumbnail">
                ${thumbnailHtml}
                ${passwordBadge}
                ${parentBadge}
                ${childBadge}
                <div class="shiroki-post-status">
                    ${statusTags}
                </div>
                <!-- 🔘 选择圆圈（类似媒体库） -->
                <div class="shiroki-post-select-circle ${isSelected ? 'selected' : ''}" data-id="${page.id}">
                    <div class="shiroki-post-select-inner"></div>
                </div>
            </div>
            <div class="shiroki-post-info">
                <div class="shiroki-post-label-title">
                    <span class="shiroki-post-label-header">标题</span>
                    <span class="shiroki-post-label-content" title="${page.title}">${page.title}</span>
                </div>
                <div class="shiroki-post-label-author">
                    <span class="shiroki-post-label-header">作者</span>
                    <span class="shiroki-post-label-content">${page.author}</span>
                </div>
                <div class="shiroki-post-label-category">
                    <span class="shiroki-post-label-header">父页面</span>
                    <span class="shiroki-post-label-content">${page.parent || '顶级页面'}</span>
                </div>
                <div class="shiroki-post-label-date">
                    <span class="shiroki-post-label-header">日期</span>
                    <span class="shiroki-post-label-content">${page.date}</span>
                </div>
            </div>
            <div class="shiroki-post-actions">
                <a href="${page.view_link}" target="_blank" class="shiroki-post-btn shiroki-post-btn-view">👁️ 查看</a>
                <a href="${page.edit_link}" class="shiroki-post-btn shiroki-post-btn-edit">✏️ 编辑</a>
                <button class="shiroki-post-btn shiroki-post-btn-copy-link" data-action="copy-link" data-link="${page.view_link}">🔗 复制链接</button>
                <button class="shiroki-post-btn shiroki-post-btn-copy-content" data-action="copy-content" data-id="${page.id}">📋 复制页面</button>
                ${page.status === 'trash'
                    ? `<button class="shiroki-post-btn shiroki-post-btn-trash" data-action="restore" data-id="${page.id}">♻️ 还原</button>`
                    : `<button class="shiroki-post-btn shiroki-post-btn-trash" data-action="trash" data-id="${page.id}">🗑️ 回收站</button>`
                }
            </div>
        `;

        /* 🖱️ 卡片点击事件 - 点击卡片切换选中状态（类似媒体库） */
        card.addEventListener('click', (e) => {
            /* ◀️ 如果点击的是按钮或链接，不触发卡片点击 */
            if (e.target.closest('.shiroki-post-btn, a, button')) {
                return;
            }
            this.toggleSelection(page.id, card);
        });

        /* 🔘 选择圆圈点击事件 */
        const selectCircle = card.querySelector('.shiroki-post-select-circle');
        selectCircle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleSelection(page.id, card);
        });

        /* 🗑️ 操作按钮事件 */
        const trashBtn = card.querySelector('[data-action="trash"]');
        const restoreBtn = card.querySelector('[data-action="restore"]');
        const copyLinkBtn = card.querySelector('[data-action="copy-link"]');
        const copyContentBtn = card.querySelector('[data-action="copy-content"]');

        if (trashBtn) {
            trashBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.trashPage(page.id);
            });
        }

        if (restoreBtn) {
            restoreBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.restorePage(page.id);
            });
        }

        /* 🔗 复制链接按钮 */
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const link = copyLinkBtn.dataset.link;
                this.copyToClipboard(link, '✅ 链接已复制到剪贴板');
            });
        }

        /* 📋 复制页面按钮 */
        if (copyContentBtn) {
            copyContentBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const pageId = copyContentBtn.dataset.id;
                this.copyPageContent(pageId);
            });
        }

        return card;
    },

    /**
     * 📄 渲染分页
     */
    renderPagination(totalPages, currentPage) {
        if (!this.elements.pagination) return;

        if (totalPages <= 1) {
            this.elements.pagination.innerHTML = '';
            return;
        }

        let html = '';

        /* ⬅️ 上一页 */
        html += `<button class="shiroki-post-page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>上一页</button>`;

        /* 📄 页码 */
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 ||
                i === totalPages ||
                (i >= currentPage - 2 && i <= currentPage + 2)
            ) {
                html += `<button class="shiroki-post-page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (
                i === currentPage - 3 ||
                i === currentPage + 3
            ) {
                html += `<span class="shiroki-post-page-ellipsis">...</span>`;
            }
        }

        /* ➡️ 下一页 */
        html += `<button class="shiroki-post-page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>下一页</button>`;

        this.elements.pagination.innerHTML = html;
    },

    /**
     * 🔄 刷新网格
     */
    refreshGrid() {
        this.state.selectedItems.clear();
        this.updateBulkActions();
        this.loadPages();
    },

    /**
     * ✅ 切换选择状态（类似媒体库）
     */
    toggleSelection(pageId, card) {
        const selectCircle = card.querySelector('.shiroki-post-select-circle');
        
        if (this.state.selectedItems.has(pageId)) {
            /* ❌ 取消选中 */
            this.state.selectedItems.delete(pageId);
            card.classList.remove('selected');
            if (selectCircle) {
                selectCircle.classList.remove('selected');
            }
        } else {
            /* ✅ 选中 */
            this.state.selectedItems.add(pageId);
            card.classList.add('selected');
            if (selectCircle) {
                selectCircle.classList.add('selected');
            }
        }
        this.updateBulkActions();
    },

    /**
     * 📦 更新批量操作显示（类似媒体库）
     */
    updateBulkActions() {
        const count = this.state.selectedItems.size;
        
        /* 📊 更新计数 */
        if (this.elements.bulkCount) {
            this.elements.bulkCount.textContent = count;
        }
        
        /* 🎛️ 显示/隐藏批量操作工具栏 */
        const filterWrapper = document.querySelector('.shiroki-post-filter-wrapper');
        const search = document.querySelector('.shiroki-post-search');
        
        if (count > 0) {
            /* 📦 显示批量操作，隐藏筛选 */
            if (this.elements.bulkActions) {
                this.elements.bulkActions.style.display = 'flex';
            }
            if (filterWrapper) {
                filterWrapper.style.display = 'none';
            }
            if (search) {
                search.style.display = 'none';
            }
        } else {
            /* 📦 隐藏批量操作，显示筛选 */
            if (this.elements.bulkActions) {
                this.elements.bulkActions.style.display = 'none';
            }
            if (filterWrapper) {
                filterWrapper.style.display = 'flex';
            }
            if (search) {
                search.style.display = '';
            }
        }
    },

    /**
     * 🗑️ 批量删除
     */
    async bulkDelete() {
        if (this.state.selectedItems.size === 0) return;
        
        /* 🔒 防止重复提交 */
        if (this.state.isDeleting) {
            return;
        }
        this.state.isDeleting = true;

        /* 🪟 显示自定义确认对话框 */
        const confirmed = await this.showConfirmDialog({
            title: '🗑️ 批量删除确认',
            message: `确定要将选中的 <strong>${this.state.selectedItems.size}</strong> 个页面移到回收站吗？`,
            confirmText: '🗑️ 确认删除',
            cancelText: '❌ 取消',
            confirmClass: 'danger'
        });

        if (!confirmed) {
            this.state.isDeleting = false;
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_trash_pages');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_ids', Array.from(this.state.selectedItems).join(','));

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification('✅ 已成功移到回收站');
            } else {
                this.showNotification('❌ 操作失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量删除失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        } finally {
            /* 🔓 释放锁 */
            this.state.isDeleting = false;
        }
    },

    /**
     * 🪟 显示自定义确认对话框
     */
    showConfirmDialog(options) {
        return new Promise((resolve) => {
            const { title, message, confirmText, cancelText, confirmClass = 'primary' } = options;
            
            /* 📝 创建对话框 */
            const dialog = document.createElement('div');
            dialog.className = 'shiroki-confirm-dialog';
            dialog.innerHTML = `
                <div class="shiroki-confirm-dialog-overlay"></div>
                <div class="shiroki-confirm-dialog-content">
                    <div class="shiroki-confirm-dialog-header">
                        <span class="shiroki-confirm-dialog-title">${title}</span>
                        <button class="shiroki-confirm-dialog-close">✕</button>
                    </div>
                    <div class="shiroki-confirm-dialog-body">
                        <p class="shiroki-confirm-dialog-message">${message}</p>
                    </div>
                    <div class="shiroki-confirm-dialog-footer">
                        <button class="shiroki-confirm-dialog-btn shiroki-confirm-dialog-confirm ${confirmClass}">${confirmText}</button>
                        <button class="shiroki-confirm-dialog-btn shiroki-confirm-dialog-cancel">${cancelText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
            
            /* 🎬 显示动画 */
            requestAnimationFrame(() => {
                dialog.classList.add('active');
            });
            
            /* 🔗 绑定事件 */
            const closeDialog = (result) => {
                dialog.classList.remove('active');
                setTimeout(() => {
                    document.body.removeChild(dialog);
                    resolve(result);
                }, 300);
            };
            
            dialog.querySelector('.shiroki-confirm-dialog-overlay')?.addEventListener('click', () => closeDialog(false));
            dialog.querySelector('.shiroki-confirm-dialog-close')?.addEventListener('click', () => closeDialog(false));
            dialog.querySelector('.shiroki-confirm-dialog-cancel')?.addEventListener('click', () => closeDialog(false));
            dialog.querySelector('.shiroki-confirm-dialog-confirm')?.addEventListener('click', () => closeDialog(true));
        });
    },

    /**
     * 🔗 批量复制链接
     */
    async bulkCopyLinks() {
        if (this.state.selectedItems.size === 0) return;

        try {
            /* 📡 获取选中页面的链接 */
            const selectedIds = Array.from(this.state.selectedItems);
            const links = [];
            
            /* 🔍 从已渲染的卡片中获取链接 */
            selectedIds.forEach(id => {
                const card = document.querySelector(`.shiroki-post-card[data-id="${id}"]`);
                if (card) {
                    const copyLinkBtn = card.querySelector('[data-action="copy-link"]');
                    if (copyLinkBtn) {
                        links.push(copyLinkBtn.dataset.link);
                    }
                }
            });

            if (links.length > 0) {
                const text = links.join('\n');
                await this.copyToClipboard(text, `✅ 已复制 ${links.length} 个页面的链接`);
            } else {
                this.showNotification('❌ 未找到页面链接', 'error');
            }
        } catch (error) {
            console.error('❌ 批量复制链接失败:', error);
            this.showNotification('❌ 复制失败', 'error');
        }
    },

    /**
     * 📋 批量复制页面（创建新页面）
     */
    async bulkCopyContent() {
        if (this.state.selectedItems.size === 0) return;
        
        /* 🔒 防止重复提交 */
        if (this.state.isCopying) {
            return;
        }
        this.state.isCopying = true;

        const count = this.state.selectedItems.size;

        try {
            const selectedIds = Array.from(this.state.selectedItems);
            const formData = new FormData();
            formData.append('action', 'shiroki_clone_pages');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_ids', selectedIds.join(','));

            this.showNotification(`⏳ 正在复制 ${count} 个页面，请稍候...`);

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success && data.data.cloned_count) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification(`✅ 成功复制 ${data.data.cloned_count} 个页面`);
            } else {
                this.showNotification('❌ 复制页面失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量复制页面失败:', error);
            this.showNotification('❌ 复制失败', 'error');
        } finally {
            /* 🔓 释放锁 */
            this.state.isCopying = false;
        }
    },

    /**
     * 📑 批量修改父级页面
     */
    bulkSetParent() {
        if (this.state.selectedItems.size === 0) return;
        this.openBulkParentModal();
    },

    /**
     * 🪟 打开批量修改父页面模态框
     */
    openBulkParentModal() {
        const modal = document.getElementById('shiroki-post-bulk-parent-modal');
        if (!modal) return;

        /* 📝 生成父页面选项 */
        this.renderBulkParentOptions();

        /* 🎨 显示模态框 */
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    },

    /**
     * 📝 渲染父页面选项
     */
    renderBulkParentOptions() {
        const container = document.getElementById('shiroki-post-bulk-parent-options');
        if (!container) return;

        /* 📝 生成选项HTML */
        let html = `<button class="shiroki-post-bulk-parent-btn" data-parent="0">📄 设为顶级页面（无父级）</button>`;

        /* 🔍 从页面卡片中获取所有页面 */
        document.querySelectorAll('.shiroki-post-card').forEach(card => {
            const id = parseInt(card.dataset.id);
            const titleEl = card.querySelector('.shiroki-post-label-content');
            const title = titleEl ? titleEl.textContent : '';

            /* ◀️ 排除已选中的页面（不能将自己设为父级） */
            if (!this.state.selectedItems.has(id)) {
                html += `<button class="shiroki-post-bulk-parent-btn" data-parent="${id}">📁 ${title}</button>`;
            }
        });

        container.innerHTML = html;

        /* 🔗 绑定选项点击事件 */
        container.querySelectorAll('.shiroki-post-bulk-parent-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                /* 🎯 单选模式 */
                container.querySelectorAll('.shiroki-post-bulk-parent-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
            });
        });
    },

    /**
     * 🔗 绑定批量修改父页面模态框事件
     */
    bindBulkParentModalEvents() {
        const modal = document.getElementById('shiroki-post-bulk-parent-modal');
        if (!modal || modal.dataset.eventsBound === 'true') return;

        /* 🔍 搜索功能 */
        const searchInput = document.getElementById('shiroki-post-bulk-parent-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const keyword = e.target.value.toLowerCase();
                modal.querySelectorAll('.shiroki-post-bulk-parent-btn').forEach(btn => {
                    const text = btn.textContent.toLowerCase();
                    btn.classList.toggle('hidden', !text.includes(keyword));
                });
            });
        }

        /* ❌ 关闭按钮 */
        const closeBtn = modal.querySelector('.shiroki-post-bulk-parent-modal-close');
        const backdrop = modal.querySelector('.shiroki-post-bulk-parent-modal-backdrop');
        const cancelBtn = document.getElementById('shiroki-post-bulk-parent-cancel');

        const closeModal = () => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                /* 🔄 重置选择 */
                modal.querySelectorAll('.shiroki-post-bulk-parent-btn.selected').forEach(btn => {
                    btn.classList.remove('selected');
                });
                if (searchInput) searchInput.value = '';
                modal.querySelectorAll('.shiroki-post-bulk-parent-btn.hidden').forEach(btn => {
                    btn.classList.remove('hidden');
                });
            }, 300);
        };

        closeBtn?.addEventListener('click', closeModal);
        backdrop?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);

        /* ✅ 确认按钮 */
        const confirmBtn = document.getElementById('shiroki-post-bulk-parent-confirm');
        confirmBtn?.addEventListener('click', () => {
            const selectedBtn = modal.querySelector('.shiroki-post-bulk-parent-btn.selected');
            if (!selectedBtn) {
                this.showNotification('❌ 请先选择一个父页面', 'error');
                return;
            }

            const parentId = parseInt(selectedBtn.dataset.parent);
            this.executeBulkSetParent(parentId);
            closeModal();
        });

        modal.dataset.eventsBound = 'true';
    },

    /**
     * 🚀 执行批量修改父页面
     */
    async executeBulkSetParent(parentId) {
        if (this.state.selectedItems.size === 0) return;

        /* 🔒 防止重复提交 */
        if (this.state.isSettingParent) {
            return;
        }
        this.state.isSettingParent = true;

        const count = this.state.selectedItems.size;

        try {
            const selectedIds = Array.from(this.state.selectedItems);
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_set_parent');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_ids', selectedIds.join(','));
            formData.append('parent_id', parentId);

            this.showNotification(`⏳ 正在修改 ${count} 个页面的父级，请稍候...`);

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success && data.data.updated_count) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification(`✅ ${data.data.message}`);
            } else {
                this.showNotification('❌ 修改父级失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量修改父级失败:', error);
            this.showNotification('❌ 修改失败', 'error');
        } finally {
            /* 🔓 释放锁 */
            this.state.isSettingParent = false;
        }
    },

    /**
     * 🏷️ 构建状态标签HTML
     */
    buildStatusTags(page) {
        const tags = [];
        
        /* 📊 基础状态（发布、草稿、待审核） */
        if (page.status === 'publish') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-publish">🟢 已发布</span>');
        } else if (page.status === 'draft') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-draft">🟡 草稿</span>');
        } else if (page.status === 'pending') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-pending">🟠 待审核</span>');
        } else if (page.status === 'trash') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-trash">⚪ 已删除</span>');
        }
        
        /* 🔴 私密状态（额外标签） */
        if (page.status === 'private') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-private">🔴 私密</span>');
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-publish">🟢 已发布</span>');
        }
        
        return tags.join('');
    },

    /**
     * 🏷️ 构建父页面勋章HTML
     */
    buildParentBadge(parent) {
        if (!parent) {
            return '';
        }
        return `<span class="shiroki-post-category-badge" title="父页面: ${parent}">📑 ${parent}</span>`;
    },

    /**
     * 🗑️ 单个页面移到回收站
     */
    async trashPage(pageId) {
        if (!confirm('确定要将这个页面移到回收站吗？')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_trash_page');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_id', pageId);

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.refreshGrid();
                this.showNotification('✅ 已移到回收站');
            } else {
                this.showNotification('❌ 操作失败', 'error');
            }
        } catch (error) {
            console.error('❌ 删除失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        }
    },

    /**
     * ♻️ 还原页面
     */
    async restorePage(pageId) {
        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_restore_page');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_id', pageId);

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.refreshGrid();
                this.showNotification('✅ 已还原');
            } else {
                this.showNotification('❌ 操作失败', 'error');
            }
        } catch (error) {
            console.error('❌ 还原失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        }
    },

    /**
     * 🔄 清除选择（类似媒体库）
     */
    clearSelection() {
        this.state.selectedItems.clear();
        document.querySelectorAll('.shiroki-post-card').forEach(card => {
            card.classList.remove('selected');
            const selectCircle = card.querySelector('.shiroki-post-select-circle');
            if (selectCircle) {
                selectCircle.classList.remove('selected');
            }
        });
        this.updateBulkActions();
    },

    /**
     * 📋 复制到剪贴板
     */
    async copyToClipboard(text, successMessage) {
        try {
            await navigator.clipboard.writeText(text);
            this.showNotification(successMessage || '✅ 已复制到剪贴板');
        } catch (err) {
            /* 🔄 降级方案：使用传统方法 */
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                this.showNotification(successMessage || '✅ 已复制到剪贴板');
            } catch (err) {
                this.showNotification('❌ 复制失败', 'error');
            }
            document.body.removeChild(textarea);
        }
    },

    /**
     * 📋 复制页面（创建新页面）
     */
    async copyPageContent(pageId) {
        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_clone_page');
            formData.append('nonce', shirokiPageConfig.nonce);
            formData.append('page_id', pageId);

            const response = await fetch(shirokiPageConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success && data.data.new_page_id) {
                this.showNotification('✅ 页面复制成功，正在跳转到编辑页面...');
                /* 🔄 跳转到新页面的编辑页面 */
                setTimeout(() => {
                    window.location.href = data.data.edit_link;
                }, 1000);
            } else {
                this.showNotification('❌ 复制页面失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 复制页面失败:', error);
            this.showNotification('❌ 复制失败', 'error');
        }
    },

    /**
     * ⏳ 显示加载状态
     */
    showLoading() {
        if (this.elements.loading) {
            this.elements.loading.style.display = 'flex';
        }
    },

    /**
     * ⏳ 隐藏加载状态
     */
    hideLoading() {
        if (this.elements.loading) {
            this.elements.loading.style.display = 'none';
        }
    },

    /**
     * ❌ 显示错误
     */
    showError(message) {
        if (this.elements.grid) {
            this.elements.grid.innerHTML = `<div class="shiroki-post-error">${message}</div>`;
        }
    },

    /**
     * 📢 显示通知
     */
    showNotification(message, type = 'success') {
        /* 创建通知元素 */
        const notification = document.createElement('div');
        notification.className = `shiroki-post-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 50px;
            right: 20px;
            padding: 12px 24px;
            background: ${type === 'success' ? 'rgba(104, 211, 145, 0.9)' : 'rgba(245, 101, 101, 0.9)'};
            color: white;
            border-radius: 8px;
            font-weight: 500;
            z-index: 99999;
            animation: shiroki-notification-enter 0.3s ease;
            backdrop-filter: blur(10px);
        `;

        document.body.appendChild(notification);

        /* 3秒后移除 */
        setTimeout(() => {
            notification.style.animation = 'shiroki-notification-leave 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};

/* 🎬 添加通知动画样式 */
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
`;
document.head.appendChild(style);

/* 🚀 DOM加载完成后初始化 - 带重试机制 */
let shirokiPageGridInitialized = false;

function initShirokiPageGrid() {
    /* 🔍 防止重复初始化 */
    if (shirokiPageGridInitialized) {
        return;
    }
    
    /* 🔍 检查必要的DOM元素是否存在 */
    const grid = document.getElementById('shiroki-post-grid');
    if (!grid) {
        /* ⏳ 如果元素不存在，等待一段时间后重试 */
        setTimeout(initShirokiPageGrid, 500);
        return;
    }
    
    /* 🔍 检查配置对象是否存在 */
    if (typeof shirokiPageConfig === 'undefined') {
        console.error('❌ shirokiPageConfig 未定义');
        return;
    }
    
    /* ✅ 标记为已初始化，防止重复绑定事件 */
    shirokiPageGridInitialized = true;
    
    /* ✅ DOM元素已存在，初始化 */
    ShirokiPageGrid.init();
}

/* 🎯 监听PHP触发的就绪事件 */
jQuery(document).on('shiroki-page-grid-ready', function() {
    initShirokiPageGrid();
});

/* 🚀 页面加载完成后也尝试初始化 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShirokiPageGrid);
} else {
    initShirokiPageGrid();
}
