/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💎 拟态拟物玻璃质感媒体库UI设计 - JavaScript交互模块
 * 🎨 网格卡片式布局交互功能
 * 
 * @package Lolimeow_Shiroki
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 媒体库UI主对象
     */
    const ShirokiMediaLibrary = {
        
        /**
         * 📝 当前状态
         */
        state: {
            page: 1,
            loading: false,
            hasMore: true,
            hasError: false,
            source: 'custom',
            requestId: 0,
            filter: 'all',
            search: '',
            sort: 'date',
            order: 'desc',
            selectedItems: new Set(),
            perPage: 10 /* ◀️ 每页加载数量 */
        },

        /**
         * 🚀 初始化
         */
        init: function() {
            if (this.initialized) return;
            this.cacheElements();
            if (!this.$grid.length) {
                return;
            }
            this.initialized = true;
            this.bindEvents();
            if (typeof shirokiMediaConfig === 'undefined') {
                this.showError('媒体库配置未加载，请刷新页面重试。');
                return;
            }
            this.initUploader();
            this.loadMediaItems();
        },

        currentRequest: null,
        retryTimer: null,
        uploadRefreshTimer: null,

        /**
         * 上传区域沿用 WordPress 的上传 API，列表始终在自定义页面内刷新。
         */
        initUploader: function() {
            const self = this;
            const $area = $('.shiroki-upload-area');
            const $dropzone = $area.find('.shiroki-upload-dropzone');
            const $browse = $area.find('.shiroki-upload-select-btn');
            const $progress = $area.find('.shiroki-upload-progress');
            const $progressText = $area.find('.shiroki-upload-progress-text');
            const $progressFill = $area.find('.shiroki-upload-progress-fill');

            $('.shiroki-upload-btn').on('click', function() {
                $area.toggle();
                if (self.uploader && self.uploader.supported && self.uploader.refresh) {
                    self.uploader.refresh();
                }
            });
            $area.find('.shiroki-upload-close').on('click', function() {
                $area.hide();
            });

            if (typeof wp === 'undefined' || typeof wp.Uploader !== 'function') {
                $browse.on('click', function() {
                    $progressText.text('上传组件未加载，请刷新页面重试。');
                    $progress.show();
                });
                return;
            }

            try {
                this.uploader = new wp.Uploader({
                    browser: $browse,
                    dropzone: $dropzone,
                    params: {
                        action: 'upload-attachment',
                        _wpnonce: shirokiMediaConfig.mediaFormNonce
                    },
                    added: function() {
                        $progressText.text('正在上传...');
                        $progressFill.css('width', '0%');
                        $progress.show();
                    },
                    progress: function(attachment) {
                        const percent = Math.max(0, Math.min(100, Number(attachment.get('percent')) || 0));
                        $progressFill.css('width', percent + '%');
                        $progressText.text('正在上传... ' + percent + '%');
                    },
                    success: function() {
                        $progressFill.css('width', '100%');
                        $progressText.text('上传成功，正在刷新媒体库...');
                        clearTimeout(self.uploadRefreshTimer);
                        self.uploadRefreshTimer = setTimeout(function() {
                            $progress.hide();
                            self.resetAndReload();
                        }, 700);
                    },
                    error: function(message) {
                        $progressText.text(message || '上传失败，请重试。');
                        $progress.show();
                    }
                });
                if (!this.uploader.supported) {
                    $progressText.text('当前浏览器不支持此上传方式。');
                    $progress.show();
                }
            } catch (error) {
                console.error('媒体上传组件初始化失败', error);
                $progressText.text('上传组件未加载，请刷新页面重试。');
                $progress.show();
            }
        },

        /**
         * 📦 缓存DOM元素
         */
        cacheElements: function() {
            this.$grid = $('#shiroki-media-grid');
            this.$empty = $('#shiroki-media-empty');
            this.$error = $('#shiroki-media-error');
            this.$errorMessage = this.$error.find('.shiroki-media-error-message');
            this.$loading = $('#shiroki-media-loading');
            this.$loadMore = $('#shiroki-media-load-more');
            this.$search = $('#shiroki-media-search');
            this.$filters = $('.shiroki-media-filter-btn');
            this.$sortBtns = $('.shiroki-media-sort-btn');
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            const self = this;

            /* 🔍 搜索结果同样分页，避免附件较多时服务器超时 */
            this.$search.on('input', this.debounce(function() {
                self.state.search = $(this).val();
                self.resetAndReload();
            }, 300));

            /* 🏷️ 筛选功能 */
            this.$filters.on('click', function() {
                const filter = $(this).data('filter');
                
                self.$filters.removeClass('active');
                $(this).addClass('active');
                
                self.state.filter = filter;
                self.resetAndReload();
            });

            this.$loadMore.on('click', function() {
                self.loadMediaItems();
            });
            $('#shiroki-media-retry').on('click', function() {
                if (typeof shirokiMediaConfig === 'undefined') {
                    window.location.reload();
                    return;
                }
                self.state.source = 'custom';
                self.clearError();
                self.loadMediaItems();
            });

            /* 📜 无限滚动加载 */
            $(window).on('scroll', this.debounce(function() {
                if (self.isNearBottom() && !self.state.loading && !self.state.hasError && self.state.hasMore) {
                    self.loadMediaItems();
                }
            }, 200));

            /* 🖱️ 卡片点击事件 - 切换选中状态 */
            this.$grid.on('click', '.shiroki-media-card', function(e) {
                // ◀️ 如果点击的是按钮或链接，不触发卡片点击
                if ($(e.target).closest('.shiroki-media-btn, .shiroki-media-label-url, a, button').length) {
                    return;
                }

                const id = parseInt($(this).data('id'), 10);
                const $card = $(this);

                // ◀️ 切换选中状态
                if (self.state.selectedItems.has(id)) {
                    self.state.selectedItems.delete(id);
                    $card.removeClass('selected');
                } else {
                    self.state.selectedItems.add(id);
                    $card.addClass('selected');
                }

                self.updateBulkActions();
            });

            /* 📋 URL复制功能 */
            this.$grid.on('click', '.shiroki-media-label-url', function(e) {
                e.stopPropagation();
                const url = $(this).data('url');
                self.copyToClipboard(url, $(this));
            });

            /* 👁️ 查看按钮 */
            this.$grid.on('click', '.shiroki-media-btn-view', function(e) {
                e.stopPropagation();
                const url = $(this).data('url');
                window.open(url, '_blank');
            });

            /* ✏️ 编辑按钮 */
            this.$grid.on('click', '.shiroki-media-btn-edit', function(e) {
                e.stopPropagation();
                const link = $(this).data('link');
                window.location.href = link;
            });

            /* 🗑️ 删除按钮 */
            this.$grid.on('click', '.shiroki-media-btn-delete', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const $card = $(this).closest('.shiroki-media-card');
                self.deleteMediaItem(id, $card);
            });

            /* 📦 批量操作按钮 */
            $(document).on('click', '.shiroki-media-bulk-btn', function(e) {
                e.preventDefault();
                const action = $(this).data('action');

                if (action === 'delete') {
                    self.bulkDelete();
                } else if (action === 'cancel') {
                    self.clearSelection();
                }
            });

            /* 🔽 排序按钮 */
            this.$sortBtns.on('click', function() {
                const sort = $(this).data('sort');
                let order = $(this).data('order');
                
                // ◀️ 如果点击的是当前激活的按钮，切换排序方向
                if ($(this).hasClass('active')) {
                    order = order === 'asc' ? 'desc' : 'asc';
                    $(this).data('order', order);
                }
                
                // ◀️ 更新按钮状态
                self.$sortBtns.removeClass('active');
                $(this).addClass('active');
                
                // ◀️ 更新排序图标
                self.$sortBtns.each(function() {
                    const btnSort = $(this).data('sort');
                    const btnOrder = $(this).data('order');
                    const icon = btnOrder === 'asc' ? '↑' : '↓';
                    $(this).attr('title', btnOrder === 'asc' ? '升序' : '降序');
                });
                
                // ◀️ 更新状态并重新加载
                self.state.sort = sort;
                self.state.order = order;
                self.resetAndReload();
            });
        },

        resetAndReload: function() {
            // 使旧响应失效，再取消旧请求，避免快速切换条件时渲染过期结果。
            this.state.requestId++;
            clearTimeout(this.retryTimer);
            this.retryTimer = null;
            if (this.currentRequest) {
                this.currentRequest.abort();
                this.currentRequest = null;
            }

            this.state.loading = false;
            this.state.page = 1;
            this.state.hasMore = true;
            this.state.source = 'custom';
            this.state.selectedItems.clear();
            this.updateBulkActions();
            this.$grid.empty();
            this.$grid.css('display', 'grid');
            this.$empty.hide();
            this.hideLoading();
            this.clearError();
            this.loadMediaItems();
        },

        /**
         * 📡 分页加载。主接口失败后使用 WordPress 附件 AJAX 数据填充同一套卡片。
         */
        loadMediaItems: function() {
            if (this.state.loading || this.state.hasError || !this.state.hasMore) {
                return;
            }

            this.state.loading = true;
            this.showLoading();
            const requestId = ++this.state.requestId;
            this.requestMediaPage(requestId, this.state.source, 0);
        },

        requestMediaPage: function(requestId, source, attempt) {
            if (requestId !== this.state.requestId) return;

            const isCore = source === 'core';
            const query = {
                s: this.state.search,
                order: this.state.order.toUpperCase(),
                orderby: this.state.sort === 'name' ? 'title' : 'date',
                posts_per_page: this.state.perPage,
                paged: this.state.page
            };
            if (['image', 'video', 'audio'].includes(this.state.filter)) {
                query.post_mime_type = this.state.filter;
            }

            this.currentRequest = $.ajax({
                url: shirokiMediaConfig.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                timeout: 15000,
                data: isCore ? {
                    action: 'query-attachments',
                    query: query
                } : {
                    action: 'shiroki_get_media_items',
                    nonce: shirokiMediaConfig.nonce,
                    page: this.state.page,
                    per_page: this.state.perPage,
                    search: this.state.search,
                    filter: this.state.filter,
                    sort: this.state.sort,
                    order: this.state.order
                }
            }).done((response, textStatus, xhr) => {
                if (requestId !== this.state.requestId) return;
                this.currentRequest = null;

                const valid = response && response.success &&
                    (isCore ? Array.isArray(response.data) :
                        response.data && Array.isArray(response.data.items));
                if (!valid) {
                    this.handleRequestFailure(requestId, source, attempt,
                        response && response.data && response.data.message);
                    return;
                }

                const items = isCore ? response.data.map((item) => this.normalizeCoreItem(item)) : response.data.items;
                const pages = parseInt(xhr.getResponseHeader('X-WP-TotalPages'), 10);
                const hasMore = isCore
                    ? (Number.isFinite(pages) ? this.state.page < pages : items.length === this.state.perPage)
                    : Boolean(response.data.has_more);

                // 首页意外为空时用核心附件查询核实，避免服务器端过滤器误报空库。
                if (!isCore && this.state.page === 1 && items.length === 0 &&
                    this.canUseCoreQuery()) {
                    this.requestMediaPage(requestId, 'core', 0);
                    return;
                }

                this.renderMediaItems(items);
                this.state.source = source;
                this.state.hasMore = hasMore;
                this.state.page++;
                this.finishLoading();
                if (!this.$grid.children('.shiroki-media-card').length) {
                    this.$grid.hide();
                    this.$empty.show();
                } else {
                    this.$empty.hide();
                    this.$grid.css('display', 'grid');
                }
                this.$loadMore.toggle(this.state.hasMore);
            }).fail((xhr, status) => {
                if (requestId !== this.state.requestId || status === 'abort') return;
                this.currentRequest = null;
                console.error('媒体文件 AJAX 加载失败', source, status, xhr.status);
                this.handleRequestFailure(requestId, source, attempt);
            });
        },

        canUseCoreQuery: function() {
            return !['document', 'file'].includes(this.state.filter);
        },

        handleRequestFailure: function(requestId, source, attempt, serverMessage) {
            if (requestId !== this.state.requestId) return;

            // 网络抖动只重试一次，随后尝试另一条附件查询接口。
            if (attempt < 1) {
                this.retryTimer = setTimeout(() => {
                    this.retryTimer = null;
                    this.requestMediaPage(requestId, source, attempt + 1);
                }, 700);
                return;
            }
            if (source === 'custom' && this.canUseCoreQuery()) {
                this.requestMediaPage(requestId, 'core', 0);
                return;
            }

            this.finishLoading();
            this.showError(typeof serverMessage === 'string' && serverMessage
                ? serverMessage : shirokiMediaConfig.strings.requestFailed);
        },

        finishLoading: function() {
            this.state.loading = false;
            this.hideLoading();
        },

        showError: function(message) {
            this.state.hasError = true;
            this.$errorMessage.text(message);
            this.$error.show();
            this.$loadMore.hide();
            this.hideLoading();
        },

        clearError: function() {
            this.state.hasError = false;
            this.$error.hide();
            this.$errorMessage.empty();
        },

        normalizeCoreItem: function(item) {
            item = item && typeof item === 'object' ? item : {};
            const url = typeof item.url === 'string' ? item.url : '';
            const filename = typeof item.filename === 'string' ? item.filename : '';
            const extension = filename.includes('.') ? (filename.split('.').pop() || '').toLowerCase() : '';
            const mime = typeof item.mime === 'string' ? item.mime : '';
            let type = ['image', 'video', 'audio'].includes(item.type) ? item.type : 'file';
            if (type === 'file' && (/^(application\/pdf|text\/plain|text\/markdown|application\/msword)/.test(mime) ||
                /^(pdf|doc|docx|xls|xlsx|ppt|pptx|txt|md|rtf|odt|ods|odp)$/.test(extension))) {
                type = 'document';
            }
            const sizes = item.sizes && typeof item.sizes === 'object' ? item.sizes : {};
            const thumbnail = (sizes.medium && sizes.medium.url) ||
                (sizes.thumbnail && sizes.thumbnail.url) || '';
            return {
                id: Number(item.id) || 0,
                title: item.title || filename || '未命名文件',
                caption: item.caption || '',
                description: item.description || '',
                url: url,
                thumbnail: thumbnail,
                mime_type: mime,
                file_type: type,
                file_size: item.filesizeHumanReadable || '',
                file_size_bytes: Number(item.filesizeInBytes) || 0,
                dimensions: item.width && item.height ? item.width + '×' + item.height : '',
                icon_svg: '',
                file_extension: extension,
                edit_link: item.editLink ||
                    shirokiMediaConfig.adminUrl + 'post.php?post=' + (Number(item.id) || 0) + '&action=edit'
            };
        },

        /**
         * 🎨 渲染媒体项目
         */
        renderMediaItems: function(items) {
            if (!items || items.length === 0) {
                return;
            }

            const html = items.map((item) => {
                try {
                    return this.createMediaCard(item);
                } catch (error) {
                    console.error('媒体文件卡片渲染失败', error);
                    return '';
                }
            }).join('');
            this.$grid.append(html);
        },

        /**
         * 🃏 创建媒体卡片HTML
         */
        createMediaCard: function(item) {
            if (!item || !Number(item.id)) return '';
            const id = Number(item.id);
            const type = String(item.file_type || 'file');
            const title = String(item.title || '未命名文件');
            const url = String(item.url || '');
            const thumbnailUrl = String(item.thumbnail || '');
            const editLink = String(item.edit_link || '');
            const isImage = type === 'image';
            const thumbnail = isImage && thumbnailUrl
                ? `<img src="${this.escapeHtml(thumbnailUrl)}" alt="${this.escapeHtml(title)}">`
                : `<div class="shiroki-media-file-icon">${item.icon_svg || '📄'}</div>`;
            
            const metaTags = [];
            if (item.dimensions) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-dimensions">📐 ${this.escapeHtml(item.dimensions)}</span>`);
            }
            if (item.file_size) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-size">📦 ${this.escapeHtml(item.file_size)}</span>`);
            }
            // ◀️ 🏷️ 格式勋章（文件扩展名）
            if (item.file_extension) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-extension">${this.escapeHtml(String(item.file_extension).toUpperCase())}</span>`);
            }
            metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-type">${this.getFileTypeLabel(type)}</span>`);

            const description = item.caption || item.description || '暂无描述';
            const shortUrl = this.escapeHtml(this.truncateUrl(url, 30));

            const isSelected = this.state.selectedItems.has(id);

            return `
                <div class="shiroki-media-card ${type === 'video' ? 'shiroki-media-type-video' : ''} ${isSelected ? 'selected' : ''}"
                     data-id="${id}"
                     data-type="${this.escapeHtml(type)}">

                    <!-- 🔘 选择圆圈（右上角） -->
                    <div class="shiroki-media-select-circle" data-id="${id}">
                        <div class="shiroki-media-select-inner"></div>
                    </div>

                    <!-- 🖼️ 缩略图区域 -->
                    <div class="shiroki-media-thumbnail">
                        ${thumbnail}
                        <!-- 🏷️ 元信息标签 -->
                        <div class="shiroki-media-meta">
                            ${metaTags.join('')}
                        </div>
                    </div>
                    
                    <!-- 📋 信息区域 -->
                    <div class="shiroki-media-info">
                        <!-- 🔵 文件名称标签 -->
                        <div class="shiroki-media-label-name">
                            <span class="shiroki-media-label-title">文件名称</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(title)}">
                                ${this.escapeHtml(title)}
                            </span>
                        </div>
                        
                        <!-- 🟢 文件描述标签 -->
                        <div class="shiroki-media-label-desc">
                            <span class="shiroki-media-label-title">文件描述</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(description)}">
                                ${this.escapeHtml(description)}
                            </span>
                        </div>
                        
                        <!-- 🟣 文件URL标签 -->
                        <div class="shiroki-media-label-url" data-url="${this.escapeHtml(url)}">
                            <span class="shiroki-media-label-title">文件URL</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(url)}">
                                ${shortUrl}
                            </span>
                        </div>
                    </div>
                    
                    <!-- ⚡ 操作按钮 -->
                    <div class="shiroki-media-actions">
                        <button class="shiroki-media-btn shiroki-media-btn-view" data-url="${this.escapeHtml(url)}" ${url ? '' : 'disabled'}>
                            👁️ 查看
                        </button>
                        <button class="shiroki-media-btn shiroki-media-btn-edit" data-link="${this.escapeHtml(editLink)}">
                            ✏️ 编辑
                        </button>
                        <button class="shiroki-media-btn shiroki-media-btn-delete" data-id="${id}">
                            🗑️ 删除
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         * 🏷️ 获取文件类型标签
         */
        getFileTypeLabel: function(type) {
            const labels = {
                'image': '🖼️ 图片',
                'video': '🎬 视频',
                'audio': '🎵 音频',
                'document': '📄 文档',
                'file': '📦 文件'
            };
            return labels[type] || '📦 文件';
        },

        /**
         * ✂️ 截断URL显示
         */
        truncateUrl: function(url, maxLength) {
            url = String(url || '');
            if (url.length <= maxLength) return url;
            return url.substring(0, maxLength) + '...';
        },

        /**
         * 🛡️ HTML转义
         */
        escapeHtml: function(text) {
            if (text === null || text === undefined) return '';
            const div = document.createElement('div');
            div.textContent = String(text);
            return div.innerHTML;
        },

        /**
         * 🖼️ 打开WordPress原生媒体编辑弹窗
         */
        openMediaModal: function(id) {
            /* ◀️ 使用WordPress原生的媒体管理器打开编辑弹窗 */
            if (typeof wp !== 'undefined' && wp.media) {
                /* ◀️ 创建编辑附件的媒体管理器 */
                const frame = new wp.media.view.MediaFrame.EditAttachments({
                    controller: {
                        trigger: function() {}
                    },
                    library: wp.media.query({
                        post__in: [id]
                    }),
                    model: wp.media.attachment(id)
                });

                /* ◀️ 打开媒体管理器 */
                frame.open();
            } else {
                /* ◀️ 降级方案：跳转到编辑页面 */
                window.location.href = shirokiMediaConfig.adminUrl + 'post.php?post=' + id + '&action=edit';
            }
        },

        /**
         * 🔄 更新批量操作工具栏
         */
        updateBulkActions: function() {
            const $bulkActions = $('#shiroki-media-bulk-actions');
            const $sortWrapper = $('.shiroki-media-sort-wrapper');
            const count = this.state.selectedItems.size;

            if (count > 0) {
                $sortWrapper.hide();
                $bulkActions.show();
                $bulkActions.find('.shiroki-media-bulk-count-num').text(count);
            } else {
                $sortWrapper.show();
                $bulkActions.hide();
            }
        },

        /**
         * 🗑️ 批量删除
         */
        bulkDelete: function() {
            const ids = Array.from(this.state.selectedItems);
            if (ids.length === 0 || this.bulkDeleting) return;

            if (!confirm('⚠️ 确定要删除选中的 ' + ids.length + ' 个媒体文件吗？此操作不可恢复。')) {
                return;
            }

            this.bulkDeleting = true;
            let completedCount = 0;
            let failedCount = 0;
            const total = ids.length;

            ids.forEach(id => {
                $.ajax({
                    url: shirokiMediaConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_delete_media',
                        nonce: shirokiMediaConfig.nonce,
                        id: id
                    },
                    success: (response) => {
                        if (!response || !response.success) failedCount++;
                    },
                    error: () => { failedCount++; },
                    complete: () => {
                        completedCount++;
                        if (completedCount === total) {
                            this.bulkDeleting = false;
                            this.resetAndReload();
                            if (failedCount) alert('有 ' + failedCount + ' 个媒体文件删除失败，请重试。');
                        }
                    }
                });
            });
        },

        /**
         * ❌ 取消选择
         */
        clearSelection: function() {
            this.state.selectedItems.clear();
            $('.shiroki-media-card').removeClass('selected');
            this.updateBulkActions();
        },

        /**
         * 📋 复制到剪贴板
         */
        copyToClipboard: function(text, $element) {
            if (navigator.clipboard && window.isSecureContext) {
                // ◀️ 使用现代Clipboard API
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopySuccess($element);
                }).catch(() => {
                    this.fallbackCopy(text, $element);
                });
            } else {
                // ◀️ 降级方案
                this.fallbackCopy(text, $element);
            }
        },

        /**
         * 📋 降级复制方案
         */
        fallbackCopy: function(text, $element) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                this.showCopySuccess($element);
            } catch (err) {
                alert(shirokiMediaConfig.strings.copyError);
            }

            document.body.removeChild(textArea);
        },

        /**
         * ✅ 显示复制成功
         */
        showCopySuccess: function($element) {
            $element.addClass('copied');
            
            // ◀️ 临时改变内容
            const $content = $element.find('.shiroki-media-label-content');
            const originalText = $content.text();
            $content.text('✅ 已复制!');
            
            setTimeout(() => {
                $element.removeClass('copied');
                $content.text(originalText);
            }, 1500);
        },

        /**
         * 🗑️ 删除媒体项目
         */
        deleteMediaItem: function(id, $card) {
            if (!confirm('⚠️ 确定要删除这个媒体文件吗？此操作不可恢复。')) {
                return;
            }

            /* ◀️ 禁用卡片交互并显示删除中状态 */
            $card.css('opacity', '0.5').addClass('deleting');

            /* ◀️ 使用WordPress原生的删除attachment的AJAX action */
            $.ajax({
                url: shirokiMediaConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_delete_media',
                    nonce: shirokiMediaConfig.nonce,
                    id: id
                },
                dataType: 'json',
                success: (response) => {
                    if (response && response.success) {
                        /* ◀️ 动画移除卡片 */
                        $card.fadeOut(400, () => {
                            $card.remove();
                            this.resetAndReload();
                        });
                    } else {
                        const errorMsg = response && response.data ? response.data : '未知错误';
                        alert('❌ 删除失败：' + errorMsg);
                        $card.css('opacity', '1').removeClass('deleting');
                    }
                },
                error: (xhr, status, error) => {
                    alert('❌ 删除请求失败，请检查网络连接');
                    $card.css('opacity', '1').removeClass('deleting');
                }
            });
        },

        /**
         * ⏳ 显示加载状态
         */
        showLoading: function() {
            this.$loading.show();
            this.$loadMore.hide();
        },

        /**
         * ✅ 隐藏加载状态
         */
        hideLoading: function() {
            this.$loading.hide();
        },

        /**
         * 📜 检查是否接近底部
         */
        isNearBottom: function() {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            
            return scrollTop + windowHeight >= documentHeight - 200;
        },

        /**
         * ⏱️ 防抖函数
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    /**
     * 🚀 DOM加载完成后初始化
     */
    $(document).ready(function() {
        // ◀️ 仅在媒体库页面初始化
        if ($('body').hasClass('upload-php') && $('#shiroki-media-grid').length) {
            // ◀️ 延迟初始化，确保PHP注入的HTML已存在
            setTimeout(function() {
                ShirokiMediaLibrary.init();
            }, 100);
        }
    });

    /**
     * 🌐 暴露到全局（供其他脚本使用）
     */
    window.ShirokiMediaLibrary = ShirokiMediaLibrary;

})(jQuery);
