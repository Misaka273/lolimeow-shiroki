/* 🕊️白木 原创开发 🔗gl.baimu.live */
/* 📝 文章列表网格卡片式布局JavaScript */
/* 🎨 拟态拟物玻璃质感交互逻辑 */

/**
 * 🎯 文章网格管理器
 */
const ShirokiPostGrid = {
    /* 📊 状态管理 */
    state: {
        page: 1,
        perPage: 8, /* ◀️ 默认每页加载数量 */
        hasMore: true,
        isLoading: false,
        isLazyLoading: false, /* ◀️ 懒加载状态标记 */
        selectedItems: new Set(),
        currentFilter: 'all',
        currentCategory: '',
        currentSearch: '',
        currentStatus: 'all',
        postType: 'post',
        totalLoaded: 0 /* ◀️ 已加载文章总数 */
    },

    /* 📦 DOM元素缓存 */
    elements: {},

    /**
     * 🚀 初始化
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.bindBulkCategoryModalEvents();
        this.bindBulkDeleteModalEvents();
        this.bindBulkDraftModalEvents();
        this.loadPosts();
    },

    /**
     * 📦 缓存DOM元素
     */
    cacheElements() {
        this.elements.grid = document.getElementById('shiroki-post-grid');
        this.elements.empty = document.getElementById('shiroki-post-empty');
        this.elements.loading = document.getElementById('shiroki-post-loading');
        this.elements.search = document.getElementById('shiroki-post-search');
        this.elements.categoryFilter = document.getElementById('shiroki-post-category');
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

        /* 🏷️ 分类筛选按钮 */
        document.querySelectorAll('.shiroki-post-category-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                /* ◀️ 如果点击的是"更多"按钮，打开模态框 */
                if (e.target.dataset.action === 'more') {
                    this.openCategoryModal();
                    return;
                }
                
                /* ◀️ 清除所有分类按钮的选中状态（包括模态框中的） */
                document.querySelectorAll('.shiroki-post-category-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.state.currentCategory = e.target.dataset.category;
                this.state.page = 1;
                this.refreshGrid();
                
                /* ◀️ 如果模态框是打开的，关闭它 */
                this.closeCategoryModal();
            });
        });
        
        /* 📦 更多分类模态框 */
        document.querySelector('.shiroki-post-category-more')?.addEventListener('click', () => {
            this.openCategoryModal();
        });

        document.querySelector('.shiroki-post-category-modal-close')?.addEventListener('click', () => {
            this.closeCategoryModal();
        });

        /* 🖱️ 点击背景关闭模态框 */
        document.querySelector('.shiroki-post-category-modal-backdrop')?.addEventListener('click', () => {
            this.closeCategoryModal();
        });

        /* 🖱️ 阻止内容区域点击事件冒泡 */
        document.querySelector('.shiroki-post-category-modal-content')?.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        /* 📊 状态筛选按钮 */
        document.querySelectorAll('.shiroki-post-status-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.shiroki-post-status-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.state.currentStatus = e.target.dataset.status;
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

        /* 📋 批量复制文章内容 */
        document.querySelector('.shiroki-post-bulk-copy-content')?.addEventListener('click', () => {
            this.bulkCopyContent();
        });

        /* 🏷️ 批量修改分类 */
        document.querySelector('.shiroki-post-bulk-edit-category')?.addEventListener('click', () => {
            this.openBulkCategoryModal();
        });

        /* 📝 批量转为草稿 */
        document.querySelector('.shiroki-post-bulk-draft')?.addEventListener('click', () => {
            this.bulkDraft();
        });

        /* 🗑️ 批量移至回收站 */
        document.querySelector('.shiroki-post-bulk-trash')?.addEventListener('click', () => {
            this.bulkTrash();
        });

        /* ❌ 批量彻底删除 */
        document.querySelector('.shiroki-post-bulk-delete')?.addEventListener('click', () => {
            this.bulkDelete();
        });

        /* � 滚动懒加载监听 */
        this.bindScrollListener();
    },

    /**
     * 📜 绑定滚动监听（懒加载）
     */
    bindScrollListener() {
        /* ◀️ 使用 IntersectionObserver 监听底部加载触发器 */
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px 200px 0px', /* ◀️ 提前200px触发加载 */
            threshold: 0
        };

        this.scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.state.hasMore && !this.state.isLoading) {
                    /* ◀️ 触发懒加载 */
                    this.loadMorePosts();
                }
            });
        }, observerOptions);

        /* ◀️ 创建并观察加载触发器元素 */
        this.createLoadTrigger();
    },

    /**
     * 📦 创建懒加载触发器元素
     */
    createLoadTrigger() {
        /* ◀️ 移除旧的触发器 */
        const oldTrigger = document.getElementById('shiroki-post-load-trigger');
        if (oldTrigger) {
            oldTrigger.remove();
        }

        /* ◀️ 创建新的触发器 */
        const trigger = document.createElement('div');
        trigger.id = 'shiroki-post-load-trigger';
        trigger.className = 'shiroki-post-load-trigger';
        trigger.style.cssText = 'height: 20px; margin: 20px 0; visibility: hidden;';

        /* ◀️ 插入到网格容器之后 */
        if (this.elements.grid && this.elements.grid.parentNode) {
            this.elements.grid.parentNode.insertBefore(trigger, this.elements.grid.nextSibling);
            this.scrollObserver?.observe(trigger);
        }
    },

    /**
     * 📜 加载更多文章（懒加载）
     */
    async loadMorePosts() {
        if (this.state.isLoading || !this.state.hasMore) return;

        this.state.page++;
        await this.loadPosts(true); /* ◀️ 传入true表示懒加载模式 */
    },

    /**
     * ⏳ 显示懒加载loading
     */
    showLazyLoading() {
        const trigger = document.getElementById('shiroki-post-load-trigger');
        if (trigger) {
            trigger.innerHTML = '<div class="shiroki-post-lazy-loading">⏳ 加载中...</div>';
            trigger.style.visibility = 'visible';
        }
    },

    /**
     * 🙈 隐藏懒加载loading
     */
    hideLazyLoading() {
        const trigger = document.getElementById('shiroki-post-load-trigger');
        if (trigger) {
            trigger.innerHTML = '';
            trigger.style.visibility = 'hidden';
        }
    },

    /**
     * ✅ 显示加载完成提示
     */
    showLoadComplete(totalPosts) {
        const trigger = document.getElementById('shiroki-post-load-trigger');
        if (trigger) {
            trigger.innerHTML = `<div class="shiroki-post-load-complete">📜 已加载全部 ${totalPosts} 篇文章</div>`;
            trigger.style.visibility = 'visible';
        }
    },

    /**
     * 📡 加载文章列表
     * @param {boolean} isLazyLoad - 是否为懒加载模式
     */
    async loadPosts(isLazyLoad = false) {
        if (this.state.isLoading) return;

        this.state.isLoading = true;
        this.state.isLazyLoading = isLazyLoad;

        /* ◀️ 懒加载时不显示全屏loading，只显示底部loading */
        if (!isLazyLoad) {
            this.showLoading();
        } else {
            this.showLazyLoading();
        }

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_get_posts');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('page', this.state.page);
            formData.append('per_page', this.state.perPage);
            formData.append('post_type', this.state.postType);
            formData.append('status', this.state.currentStatus);

            if (this.state.currentCategory) {
                formData.append('category', this.state.currentCategory);
            }

            if (this.state.currentSearch) {
                formData.append('search', this.state.currentSearch);
            }

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.renderPosts(data.data.posts, isLazyLoad);
                this.state.hasMore = data.data.has_more;
                this.state.totalLoaded = data.data.total_loaded || (this.state.page * this.state.perPage);

                /* ◀️ 隐藏分页器，使用懒加载 */
                if (this.elements.pagination) {
                    this.elements.pagination.style.display = 'none';
                }

                /* ◀️ 如果没有更多文章，显示加载完成提示 */
                if (!data.data.has_more && data.data.total_posts > 0) {
                    this.showLoadComplete(data.data.total_posts);
                }
            } else {
                this.showError(data.data?.message || '加载失败');
            }
        } catch (error) {
            console.error('❌ 加载文章失败:', error);
            this.showError('加载失败，请重试');
        } finally {
            this.state.isLoading = false;
            this.state.isLazyLoading = false;
            this.hideLoading();
            this.hideLazyLoading();
        }
    },

    /**
     * 🎨 渲染文章卡片
     * @param {Array} posts - 文章列表
     * @param {boolean} append - 是否追加模式（懒加载）
     */
    renderPosts(posts, append = false) {
        if (!this.elements.grid) return;

        if (posts.length === 0 && this.state.page === 1) {
            this.elements.grid.innerHTML = '';
            this.elements.empty.style.display = 'flex';
            return;
        }

        this.elements.empty.style.display = 'none';

        /* ◀️ 非追加模式或第一页时清空内容 */
        if (!append || this.state.page === 1) {
            this.elements.grid.innerHTML = '';
        }

        /* ◀️ 计算起始索引，用于动画延迟 */
        const startIndex = append ? this.state.totalLoaded : 0;

        posts.forEach((post, index) => {
            const card = this.createPostCard(post, startIndex + index);
            this.elements.grid.appendChild(card);
        });
    },

    /**
     * 🃏 创建文章卡片
     */
    createPostCard(post, index) {
        const card = document.createElement('div');
        card.className = 'shiroki-post-card';
        card.dataset.id = post.id;
        card.style.animationDelay = `${(index % 8) * 0.05}s`;

        /* 🖼️ 特色图片 */
        const thumbnailHtml = post.thumbnail
            ? `<img src="${post.thumbnail}" alt="${post.title}" loading="lazy">`
            : `<div class="shiroki-post-thumbnail-placeholder">📝</div>`;

        /* 🏷️ 状态标签 - 显示发布状态和特殊状态 */
        const statusTags = this.buildStatusTags(post);

        /* 🔐 密码保护标识 */
        const passwordBadge = post.password_protected ? '<span class="shiroki-post-password-badge">🔒</span>' : '';

        /* 🏷️ 分类勋章 - 显示第一个分类 */
        const categoryBadge = post.categories ? this.buildCategoryBadge(post.categories) : '';

        /* ✅ 检查是否已选中 */
        const isSelected = this.state.selectedItems.has(post.id);
        const editLinkAttributes = shirokiPostConfig.editTargetBlank
            ? ' target="_blank" rel="noopener noreferrer"'
            : '';

        /* 📋 卡片HTML */
        card.innerHTML = `
            <div class="shiroki-post-thumbnail">
                ${thumbnailHtml}
                ${passwordBadge}
                ${categoryBadge}
                <div class="shiroki-post-status">
                    ${statusTags}
                </div>
                <!-- 🔘 选择圆圈（类似媒体库） -->
                <div class="shiroki-post-select-circle ${isSelected ? 'selected' : ''}" data-id="${post.id}">
                    <div class="shiroki-post-select-inner"></div>
                </div>
            </div>
            <div class="shiroki-post-info">
                <div class="shiroki-post-label-title">
                    <span class="shiroki-post-label-header">标题</span>
                    <span class="shiroki-post-label-content" title="${post.title}">${post.title}</span>
                </div>
                <div class="shiroki-post-label-author">
                    <span class="shiroki-post-label-header">作者</span>
                    <span class="shiroki-post-label-content">${post.author}</span>
                </div>
                <div class="shiroki-post-label-category">
                    <span class="shiroki-post-label-header">分类</span>
                    <span class="shiroki-post-label-content">${post.categories || '未分类'}</span>
                </div>
                <div class="shiroki-post-label-date">
                    <span class="shiroki-post-label-header">日期</span>
                    <span class="shiroki-post-label-content">${post.date}</span>
                </div>
            </div>
            <div class="shiroki-post-actions">
                <a href="${post.view_link}" target="_blank" class="shiroki-post-btn shiroki-post-btn-view">👁️ 查看</a>
                <a href="${post.edit_link}"${editLinkAttributes} class="shiroki-post-btn shiroki-post-btn-edit">✏️ 编辑</a>
                ${post.status === 'draft'
                    ? `<button class="shiroki-post-btn shiroki-post-btn-publish" data-action="publish" data-id="${post.id}">🚀 发布</button>`
                    : ''
                }
                <button class="shiroki-post-btn shiroki-post-btn-copy-link" data-action="copy-link" data-link="${post.view_link}">🔗 复制链接</button>
                <button class="shiroki-post-btn shiroki-post-btn-copy-content" data-action="copy-content" data-id="${post.id}">📋 复制文章</button>
                ${post.status === 'trash'
                    ? `<button class="shiroki-post-btn shiroki-post-btn-trash" data-action="restore" data-id="${post.id}">♻️ 还原</button>`
                    : `<button class="shiroki-post-btn shiroki-post-btn-trash" data-action="trash" data-id="${post.id}">🗑️ 回收站</button>`
                }
            </div>
        `;

        /* 🖱️ 卡片点击事件 - 点击卡片切换选中状态（类似媒体库） */
        card.addEventListener('click', (e) => {
            /* ◀️ 如果点击的是按钮或链接，不触发卡片点击 */
            if (e.target.closest('.shiroki-post-btn, a, button')) {
                return;
            }
            this.toggleSelection(post.id, card);
        });

        /* 🔘 选择圆圈点击事件 */
        const selectCircle = card.querySelector('.shiroki-post-select-circle');
        selectCircle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleSelection(post.id, card);
        });

        /* 🗑️ 操作按钮事件 */
        const trashBtn = card.querySelector('[data-action="trash"]');
        const restoreBtn = card.querySelector('[data-action="restore"]');
        const copyLinkBtn = card.querySelector('[data-action="copy-link"]');
        const copyContentBtn = card.querySelector('[data-action="copy-content"]');
        const publishBtn = card.querySelector('[data-action="publish"]');

        if (trashBtn) {
            trashBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.trashPost(post.id);
            });
        }

        if (publishBtn) {
            publishBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.publishPost(post.id);
            });
        }

        if (restoreBtn) {
            restoreBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.restorePost(post.id);
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

        /* 📋 复制文章内容按钮 */
        if (copyContentBtn) {
            copyContentBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const postId = copyContentBtn.dataset.id;
                this.copyPostContent(postId);
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
        /* ◀️ 重置懒加载状态 */
        this.state.page = 1;
        this.state.hasMore = true;
        this.state.totalLoaded = 0;
        /* ◀️ 重新创建加载触发器 */
        this.createLoadTrigger();
        this.loadPosts();
    },

    /**
     * ✅ 切换选择状态（类似媒体库）
     */
    toggleSelection(postId, card) {
        const selectCircle = card.querySelector('.shiroki-post-select-circle');
        
        if (this.state.selectedItems.has(postId)) {
            /* ❌ 取消选中 */
            this.state.selectedItems.delete(postId);
            card.classList.remove('selected');
            if (selectCircle) {
                selectCircle.classList.remove('selected');
            }
        } else {
            /* ✅ 选中 */
            this.state.selectedItems.add(postId);
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
     * ❌ 批量彻底删除（显示确认弹窗）
     */
    bulkDelete() {
        if (this.state.selectedItems.size === 0) return;

        /* ◀️ 复用批量删除的确认弹窗，但修改提示文字为彻底删除 */
        const modal = document.getElementById('shiroki-post-bulk-delete-modal');
        if (modal) {
            /* ◀️ 更新弹窗标题和提示 */
            const titleEl = modal.querySelector('.shiroki-post-bulk-delete-modal-title');
            const messageEl = modal.querySelector('.shiroki-post-bulk-delete-message');
            const hintEl = modal.querySelector('.shiroki-post-bulk-delete-hint');
            const confirmBtn = document.getElementById('shiroki-post-bulk-delete-confirm');

            if (titleEl) titleEl.textContent = '❌ 彻底删除确认';
            if (messageEl) messageEl.innerHTML = `确定要彻底删除选中的 <span class="shiroki-post-bulk-delete-count" id="shiroki-post-bulk-delete-count">${this.state.selectedItems.size}</span> 篇文章吗？`;
            if (hintEl) hintEl.textContent = '⚠️ 警告：彻底删除后无法恢复！';
            if (confirmBtn) {
                confirmBtn.textContent = '❌ 确认彻底删除';
                confirmBtn.dataset.action = 'delete';
            }

            /* ◀️ 更新选中数量 */
            const countEl = document.getElementById('shiroki-post-bulk-delete-count');
            if (countEl) {
                countEl.textContent = this.state.selectedItems.size;
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 🗑️ 打开批量删除确认弹窗
     */
    openBulkDeleteModal() {
        const modal = document.getElementById('shiroki-post-bulk-delete-modal');
        if (modal) {
            /* ◀️ 更新选中数量 */
            const countEl = document.getElementById('shiroki-post-bulk-delete-count');
            if (countEl) {
                countEl.textContent = this.state.selectedItems.size;
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 📦 关闭批量删除确认弹窗
     */
    closeBulkDeleteModal() {
        const modal = document.getElementById('shiroki-post-bulk-delete-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    },

    /**
     * 🔗 绑定批量删除确认弹窗事件（只绑定一次）
     */
    bindBulkDeleteModalEvents() {
        const modal = document.getElementById('shiroki-post-bulk-delete-modal');
        if (!modal || modal.dataset.eventsBound === 'true') return;

        /* ◀️ 绑定确认按钮 */
        const confirmBtn = document.getElementById('shiroki-post-bulk-delete-confirm');
        const cancelBtn = document.getElementById('shiroki-post-bulk-delete-cancel');
        const closeBtn = modal.querySelector('.shiroki-post-bulk-delete-modal-close');
        const backdrop = modal.querySelector('.shiroki-post-bulk-delete-modal-backdrop');

        confirmBtn?.addEventListener('click', () => {
            const action = confirmBtn.dataset.action;
            if (action === 'trash') {
                this.executeBulkTrash();
            } else {
                this.executeBulkDelete();
            }
        });

        cancelBtn?.addEventListener('click', () => {
            this.closeBulkDeleteModal();
        });

        closeBtn?.addEventListener('click', () => {
            this.closeBulkDeleteModal();
        });

        backdrop?.addEventListener('click', () => {
            this.closeBulkDeleteModal();
        });

        /* ◀️ 标记事件已绑定 */
        modal.dataset.eventsBound = 'true';
    },

    /**
     * 🗑️ 执行批量移至回收站
     */
    async executeBulkTrash() {
        this.closeBulkDeleteModal();

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_trash_posts');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_ids', Array.from(this.state.selectedItems).join(','));

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
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
            console.error('❌ 批量移至回收站失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        }
    },

    /**
     * ❌ 执行批量彻底删除
     */
    async executeBulkDelete() {
        this.closeBulkDeleteModal();

        if (!confirm('⚠️ 警告：彻底删除后无法恢复！确定要继续吗？')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_delete_posts');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_ids', Array.from(this.state.selectedItems).join(','));

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification('✅ 已彻底删除');
            } else {
                this.showNotification('❌ 操作失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量彻底删除失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        }
    },

    /**
     * 🗑️ 批量移至回收站（使用确认弹窗）
     */
    bulkTrash() {
        if (this.state.selectedItems.size === 0) return;
        /* ◀️ 复用批量删除的确认弹窗，但修改提示文字 */
        const modal = document.getElementById('shiroki-post-bulk-delete-modal');
        if (modal) {
            /* ◀️ 更新弹窗标题和提示 */
            const titleEl = modal.querySelector('.shiroki-post-bulk-delete-modal-title');
            const messageEl = modal.querySelector('.shiroki-post-bulk-delete-message');
            const hintEl = modal.querySelector('.shiroki-post-bulk-delete-hint');
            const confirmBtn = document.getElementById('shiroki-post-bulk-delete-confirm');

            if (titleEl) titleEl.textContent = '🗑️ 移至回收站确认';
            if (messageEl) messageEl.innerHTML = `确定要将选中的 <span class="shiroki-post-bulk-delete-count" id="shiroki-post-bulk-delete-count">${this.state.selectedItems.size}</span> 篇文章移到回收站吗？`;
            if (hintEl) hintEl.textContent = '文章将被移到回收站，之后可以恢复。';
            if (confirmBtn) {
                confirmBtn.textContent = '🗑️ 确认移动';
                confirmBtn.dataset.action = 'trash';
            }

            /* ◀️ 更新选中数量 */
            const countEl = document.getElementById('shiroki-post-bulk-delete-count');
            if (countEl) {
                countEl.textContent = this.state.selectedItems.size;
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 📝 批量转为草稿
     */
    bulkDraft() {
        if (this.state.selectedItems.size === 0) return;
        this.openBulkDraftModal();
    },

    /**
     * 📝 打开批量转为草稿确认弹窗
     */
    openBulkDraftModal() {
        const modal = document.getElementById('shiroki-post-bulk-draft-modal');
        if (modal) {
            /* ◀️ 更新选中数量 */
            const countEl = document.getElementById('shiroki-post-bulk-draft-count');
            if (countEl) {
                countEl.textContent = this.state.selectedItems.size;
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 📦 关闭批量转为草稿确认弹窗
     */
    closeBulkDraftModal() {
        const modal = document.getElementById('shiroki-post-bulk-draft-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    },

    /**
     * 🔗 绑定批量转为草稿确认弹窗事件（只绑定一次）
     */
    bindBulkDraftModalEvents() {
        const modal = document.getElementById('shiroki-post-bulk-draft-modal');
        if (!modal || modal.dataset.eventsBound === 'true') return;

        /* ◀️ 绑定确认按钮 */
        const confirmBtn = document.getElementById('shiroki-post-bulk-draft-confirm');
        const cancelBtn = document.getElementById('shiroki-post-bulk-draft-cancel');
        const closeBtn = modal.querySelector('.shiroki-post-bulk-draft-modal-close');
        const backdrop = modal.querySelector('.shiroki-post-bulk-draft-modal-backdrop');

        confirmBtn?.addEventListener('click', () => {
            this.executeBulkDraft();
        });

        cancelBtn?.addEventListener('click', () => {
            this.closeBulkDraftModal();
        });

        closeBtn?.addEventListener('click', () => {
            this.closeBulkDraftModal();
        });

        backdrop?.addEventListener('click', () => {
            this.closeBulkDraftModal();
        });

        /* ◀️ 标记事件已绑定 */
        modal.dataset.eventsBound = 'true';
    },

    /**
     * 📝 执行批量转为草稿
     */
    async executeBulkDraft() {
        this.closeBulkDraftModal();

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_draft_posts');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_ids', Array.from(this.state.selectedItems).join(','));

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification(`✅ ${data.data.message}`);
            } else {
                this.showNotification('❌ 操作失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量转为草稿失败:', error);
            this.showNotification('❌ 操作失败', 'error');
        }
    },

    /**
     * 🔗 批量复制链接
     */
    async bulkCopyLinks() {
        if (this.state.selectedItems.size === 0) return;

        try {
            /* 📡 获取选中文章的链接 */
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
                await this.copyToClipboard(text, `✅ 已复制 ${links.length} 篇文章的链接`);
            } else {
                this.showNotification('❌ 未找到文章链接', 'error');
            }
        } catch (error) {
            console.error('❌ 批量复制链接失败:', error);
            this.showNotification('❌ 复制失败', 'error');
        }
    },

    /**
     * 📋 批量复制文章（创建新文章）
     */
    async bulkCopyContent() {
        if (this.state.selectedItems.size === 0) return;

        const count = this.state.selectedItems.size;

        try {
            const selectedIds = Array.from(this.state.selectedItems);
            const formData = new FormData();
            formData.append('action', 'shiroki_clone_posts');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_ids', selectedIds.join(','));

            this.showNotification(`⏳ 正在复制 ${count} 篇文章，请稍候...`);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success && data.data.cloned_count) {
                this.clearSelection();
                this.refreshGrid();
                this.showNotification(`✅ 成功复制 ${data.data.cloned_count} 篇文章`);
            } else {
                this.showNotification('❌ 复制文章失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量复制文章失败:', error);
            this.showNotification('❌ 复制失败', 'error');
        }
    },

    /**
     * 🏷️ 构建状态标签HTML
     */
    buildStatusTags(post) {
        const tags = [];

        /* 📊 基础状态（发布、定时发布、草稿、待审核） */
        if (post.status === 'publish') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-publish">🟢 已发布</span>');
        } else if (post.status === 'future') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-future">🔵 定时发布</span>');
        } else if (post.status === 'draft') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-draft">🟡 草稿</span>');
        } else if (post.status === 'pending') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-pending">🟠 待审核</span>');
        } else if (post.status === 'trash') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-trash">⚪ 已删除</span>');
        }

        /* 🔴 私密状态（额外标签） */
        if (post.status === 'private') {
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-private">🔴 私密</span>');
            tags.push('<span class="shiroki-post-status-tag shiroki-post-status-publish">🟢 已发布</span>');
        }

        /* 🔐 内容保护标签（来自 shiroki-content-paywall 插件） */
        if (post.paywall_enabled && Array.isArray(post.paywall_types) && post.paywall_types.length > 0) {
            if (post.paywall_types.includes('password')) {
                tags.push('<span class="shiroki-post-status-tag shiroki-post-status-password">🔐 加密</span>');
            }
            if (post.paywall_types.includes('pay')) {
                const payLabel = post.paywall_price > 0 ? `💎 付费 ¥${post.paywall_price}` : '💎 付费';
                tags.push(`<span class="shiroki-post-status-tag shiroki-post-status-pay">${payLabel}</span>`);
            }
        }

        return tags.join('');
    },

    /**
     * 🏷️ 构建分类勋章HTML
     */
    buildCategoryBadge(categories) {
        if (!categories || categories === '未分类') {
            return '';
        }
        /* ◀️ 获取第一个分类名称 */
        const firstCategory = categories.split(',')[0].trim();
        if (!firstCategory) {
            return '';
        }
        return `<span class="shiroki-post-category-badge" title="${categories}">${firstCategory}</span>`;
    },

    /**
     * 📦 打开分类模态框
     */
    openCategoryModal() {
        const modal = document.getElementById('shiroki-post-category-modal');
        if (modal) {
            modal.style.display = 'flex';
            /* ◀️ 添加动画类 */
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 📦 关闭分类模态框
     */
    closeCategoryModal() {
        const modal = document.getElementById('shiroki-post-category-modal');
        if (modal) {
            modal.classList.remove('active');
            /* ◀️ 等待动画结束后隐藏 */
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    },

    /**
     * 🏷️ 打开批量修改分类模态框
     */
    openBulkCategoryModal() {
        const modal = document.getElementById('shiroki-post-bulk-category-modal');
        if (modal) {
            /* ◀️ 清除之前的选中状态 */
            modal.querySelectorAll('.shiroki-post-bulk-category-btn').forEach(btn => {
                btn.classList.remove('selected');
            });

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    },

    /**
     * 🔗 绑定批量修改分类模态框事件（只绑定一次）
     */
    bindBulkCategoryModalEvents() {
        const modal = document.getElementById('shiroki-post-bulk-category-modal');
        if (!modal || modal.dataset.eventsBound === 'true') return;

        /* ◀️ 绑定分类按钮点击事件（使用事件委托） */
        const optionsContainer = modal.querySelector('.shiroki-post-bulk-category-options');
        if (optionsContainer) {
            optionsContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.shiroki-post-bulk-category-btn');
                if (btn) {
                    btn.classList.toggle('selected');
                }
            });
        }

        /* ◀️ 绑定分类搜索功能 */
        const searchInput = document.getElementById('shiroki-post-bulk-category-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterBulkCategories(e.target.value);
            });
        }

        /* ◀️ 绑定确认按钮 */
        const confirmBtn = document.getElementById('shiroki-post-bulk-category-confirm');
        const cancelBtn = document.getElementById('shiroki-post-bulk-category-cancel');
        const closeBtn = modal.querySelector('.shiroki-post-bulk-category-modal-close');
        const backdrop = modal.querySelector('.shiroki-post-bulk-category-modal-backdrop');

        confirmBtn?.addEventListener('click', () => {
            this.bulkEditCategory();
        });

        cancelBtn?.addEventListener('click', () => {
            this.closeBulkCategoryModal();
        });

        closeBtn?.addEventListener('click', () => {
            this.closeBulkCategoryModal();
        });

        backdrop?.addEventListener('click', () => {
            this.closeBulkCategoryModal();
        });

        /* ◀️ 标记事件已绑定 */
        modal.dataset.eventsBound = 'true';
    },

    /**
     * 🔍 模糊搜索过滤分类
     */
    filterBulkCategories(searchTerm) {
        const optionsContainer = document.getElementById('shiroki-post-bulk-category-options');
        if (!optionsContainer) return;

        const term = searchTerm.toLowerCase().trim();
        const buttons = optionsContainer.querySelectorAll('.shiroki-post-bulk-category-btn');

        buttons.forEach(btn => {
            const categoryName = btn.textContent.toLowerCase();
            if (term === '' || categoryName.includes(term)) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    },

    /**
     * 📦 关闭批量修改分类模态框
     */
    closeBulkCategoryModal() {
        const modal = document.getElementById('shiroki-post-bulk-category-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                /* ◀️ 清除选中状态 */
                modal.querySelectorAll('.shiroki-post-bulk-category-btn').forEach(btn => {
                    btn.classList.remove('selected');
                    btn.classList.remove('hidden');
                });
                /* ◀️ 清空搜索框 */
                const searchInput = document.getElementById('shiroki-post-bulk-category-search-input');
                if (searchInput) {
                    searchInput.value = '';
                }
            }, 300);
        }
    },

    /**
     * 🏷️ 批量修改分类
     */
    async bulkEditCategory() {
        const modal = document.getElementById('shiroki-post-bulk-category-modal');
        const selectedCategories = [];

        modal?.querySelectorAll('.shiroki-post-bulk-category-btn.selected').forEach(btn => {
            selectedCategories.push(btn.dataset.category);
        });

        if (selectedCategories.length === 0) {
            this.showNotification('❌ 请至少选择一个分类', 'error');
            return;
        }

        const postIds = Array.from(this.state.selectedItems).join(',');
        const categoryIds = selectedCategories.join(',');

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_bulk_edit_category');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_ids', postIds);
            formData.append('category_ids', categoryIds);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.closeBulkCategoryModal();
                this.clearSelection();
                this.refreshGrid();
                this.showNotification(`✅ ${data.data.message}`);
            } else {
                this.showNotification('❌ 修改分类失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 批量修改分类失败:', error);
            this.showNotification('❌ 修改分类失败', 'error');
        }
    },

    /**
     * 🗑️ 单篇文章移到回收站
     */
    async trashPost(postId) {
        if (!confirm('确定要将这篇文章移到回收站吗？')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_trash_post');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_id', postId);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
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
     * 🚀 发布文章
     */
    async publishPost(postId) {
        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_publish_post');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_id', postId);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success) {
                this.refreshGrid();
                this.showNotification('✅ 文章已发布');
            } else {
                this.showNotification('❌ 发布失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 发布文章失败:', error);
            this.showNotification('❌ 发布失败', 'error');
        }
    },

    /**
     * ♻️ 还原文章
     */
    async restorePost(postId) {
        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_restore_post');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_id', postId);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
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
     * 📋 获取状态文本
     */
    getStatusText(status) {
        const statusMap = {
            'publish': '已发布',
            'future': '定时发布',
            'draft': '草稿',
            'pending': '待审核',
            'private': '私密',
            'trash': '已删除'
        };
        return statusMap[status] || status;
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
     * 📋 复制文章（创建新文章）
     */
    async copyPostContent(postId) {
        try {
            const formData = new FormData();
            formData.append('action', 'shiroki_clone_post');
            formData.append('nonce', shirokiPostConfig.nonce);
            formData.append('post_id', postId);

            const response = await fetch(shirokiPostConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.success && data.data.new_post_id) {
                this.showNotification('✅ 文章复制成功，正在跳转到编辑页面...');
                /* 🔄 跳转到新文章的编辑页面 */
                setTimeout(() => {
                    window.location.href = data.data.edit_link;
                }, 1000);
            } else {
                this.showNotification('❌ 复制文章失败: ' + (data.data?.message || '未知错误'), 'error');
            }
        } catch (error) {
            console.error('❌ 复制文章失败:', error);
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
function initShirokiPostGrid() {
    /* 🔍 检查必要的DOM元素是否存在 */
    const grid = document.getElementById('shiroki-post-grid');
    if (!grid) {
        /* ⏳ 如果元素不存在，等待一段时间后重试 */
        setTimeout(initShirokiPostGrid, 500);
        return;
    }
    
    /* ✅ DOM元素已存在，初始化 */
    ShirokiPostGrid.init();
}

/* 🎯 监听PHP触发的就绪事件 */
jQuery(document).on('shiroki-post-grid-ready', function() {
    initShirokiPostGrid();
});

/* 🚀 页面加载完成后也尝试初始化 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShirokiPostGrid);
} else {
    initShirokiPostGrid();
}
