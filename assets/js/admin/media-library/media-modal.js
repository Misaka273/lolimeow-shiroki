/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🪟 媒体弹窗交互模块
 * 🎨 拟态拟物玻璃质感媒体弹窗
 * 
 * @package Lolimeow_Shiroki
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 媒体弹窗对象
     */
    const ShirokiMediaModal = {
        
        /**
         * 📝 当前状态
         */
        state: {
            isOpen: false,
            page: 1,
            loading: false,
            hasMore: true,
            filter: 'all',
            date: 'all',
            search: '',
            isSearch: false, // ⬅️ 是否为搜索模式
            selectedItems: new Set(),
            currentEditor: 'content',
            mode: 'editor', // ⬅️ 'editor' 或 'callback'
            callback: null  // ⬅️ 回调函数（用于小工具等场景）
        },

        /**
         * 📊 懒加载配置
         */
        lazyLoadConfig: {
            perPage: 21,    // ⬅️ 每次加载数量（首次和后续都是21条）
            threshold: 100  // ⬅️ 距离底部触发加载的阈值（像素）
        },

        /**
         * 🚀 初始化
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.overrideMediaButton();
        },

        /**
         * 📦 缓存DOM元素
         */
        cacheElements: function() {
            this.$modal = $('#shiroki-media-modal');
            this.$overlay = this.$modal.find('.shiroki-media-modal-overlay');
            this.$closeBtn = this.$modal.find('.shiroki-media-modal-close');
            this.$tabs = this.$modal.find('.shiroki-media-tab');
            this.$filterBtns = this.$modal.find('.shiroki-media-filter-btn');
            this.$searchInput = this.$modal.find('.shiroki-media-search-input');
            this.$grid = this.$modal.find('#shiroki-media-modal-grid');
            this.$empty = this.$modal.find('#shiroki-media-modal-empty');
            this.$loading = this.$modal.find('#shiroki-media-modal-loading');
            this.$btnUpload = this.$modal.find('#shiroki-media-btn-upload');
            this.$btnLibrary = this.$modal.find('#shiroki-media-btn-library');
            this.$btnCancel = this.$modal.find('#shiroki-media-btn-cancel');
            this.$btnInsert = this.$modal.find('#shiroki-media-btn-insert');
            
            /* ◀️ 详情抽屉元素 */
            this.$detailDrawer = $('#shiroki-media-detail-drawer');
            this.$detailOverlay = $('#shiroki-media-detail-overlay');
            this.$detailClose = $('#shiroki-media-detail-close');
            this.$detailPreview = $('#shiroki-media-detail-preview');
            this.$detailTitle = $('#shiroki-media-detail-title');
            this.$detailCaption = $('#shiroki-media-detail-caption');
            this.$detailAlt = $('#shiroki-media-detail-alt');
            this.$detailAltField = $('#shiroki-media-detail-alt-field');
            this.$detailDescription = $('#shiroki-media-detail-description');
            this.$detailFilename = $('#shiroki-media-detail-filename');
            this.$detailMime = $('#shiroki-media-detail-mime');
            this.$detailDate = $('#shiroki-media-detail-date');
            this.$detailSize = $('#shiroki-media-detail-size');
            this.$detailDimensions = $('#shiroki-media-detail-dimensions');
            this.$detailDimensionsItem = $('#shiroki-media-detail-dimensions-item');
            this.$detailUrl = $('#shiroki-media-detail-url');
            this.$detailCopyUrl = $('#shiroki-media-detail-copy-url');
            this.$detailView = $('#shiroki-media-detail-view');
            this.$detailDelete = $('#shiroki-media-detail-delete');

            /* ◀️ 当前查看的附件ID */
            this.currentDetailId = null;
            
            /* ◀️ 原始数据（用于检测变更） */
            this.originalDetailData = {};
            
            /* ◀️ 是否正在保存 */
            this.isSaving = false;
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            const self = this;

            /* ◀️ 关闭弹窗 */
            this.$closeBtn.on('click', function() {
                self.close();
            });
            
            this.$overlay.on('click', function() {
                self.close();
            });
            
            this.$btnCancel.on('click', function() {
                self.close();
            });

            /* ◀️ ESC键关闭 */
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.state.isOpen) {
                    self.close();
                }
            });

            /* ◀️ 分类标签切换 */
            this.$tabs.on('click', function() {
                const filter = $(this).data('filter');
                self.$tabs.removeClass('active');
                $(this).addClass('active');
                self.state.filter = filter;
                self.state.page = 1;
                self.state.hasMore = true;
                self.state.isSearch = false; // ⬅️ 重置搜索模式
                self.loadMediaItems();
            });

            /* ◀️ 日期筛选 */
            this.$filterBtns.on('click', function() {
                const date = $(this).data('date');
                self.$filterBtns.removeClass('active');
                $(this).addClass('active');
                self.state.date = date;
                self.state.page = 1;
                self.state.hasMore = true;
                self.state.isSearch = false; // ⬅️ 重置搜索模式
                self.loadMediaItems();
            });

            /* ◀️ 搜索功能 - 搜索时加载所有结果 */
            this.$searchInput.on('input', this.debounce(function() {
                const searchValue = $(this).val().trim();
                self.state.search = searchValue;
                self.state.page = 1;
                self.state.hasMore = true;
                self.state.isSearch = searchValue.length > 0; // ⬅️ 有搜索内容时进入搜索模式
                self.loadMediaItems();
            }, 300));

            /* ◀️ 媒体项点击选择 */
            this.$grid.on('click', '.shiroki-media-modal-item', function(e) {
                /* ◀️ 如果点击的是详情抽屉区域，不处理 */
                if ($(e.target).closest('.shiroki-media-detail-drawer').length) {
                    return;
                }

                const id = parseInt($(this).data('id'));
                const $item = $(this);

                /* ◀️ 切换选中状态 */
                if (self.state.selectedItems.has(id)) {
                    self.state.selectedItems.delete(id);
                    $item.removeClass('selected');
                } else {
                    /* ◀️ 单选模式：先清除其他选中 */
                    self.state.selectedItems.clear();
                    self.$grid.find('.shiroki-media-modal-item').removeClass('selected');

                    self.state.selectedItems.add(id);
                    $item.addClass('selected');
                }

                self.updateInsertButton();
            });

            /* ◀️ 媒体项鼠标悬停显示提示气泡 */
            this.$grid.on('mouseenter', '.shiroki-media-modal-item', function(e) {
                self.showTooltip(e, '双击可查看详情');
            });

            this.$grid.on('mouseleave', '.shiroki-media-modal-item', function() {
                self.hideTooltip();
            });

            this.$grid.on('mousemove', '.shiroki-media-modal-item', function(e) {
                self.updateTooltipPosition(e);
            });

            /* ◀️ 媒体项双击打开详情抽屉 */
            this.$grid.on('dblclick', '.shiroki-media-modal-item', function(e) {
                e.preventDefault();
                e.stopPropagation();

                /* ◀️ 隐藏提示气泡 */
                self.hideTooltip();

                const id = parseInt($(this).data('id'));
                self.openDetailDrawer(id);
            });
            
            /* ◀️ 关闭详情抽屉 */
            this.$detailClose.on('click', function() {
                self.closeDetailDrawer();
            });
            
            this.$detailOverlay.on('click', function() {
                self.closeDetailDrawer();
            });
            
            /* ◀️ ESC键关闭详情抽屉 */
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.$detailDrawer.hasClass('active')) {
                    self.closeDetailDrawer();
                }
            });
            
            /* ◀️ 复制URL按钮 */
            this.$detailCopyUrl.on('click', function() {
                self.copyUrlToClipboard();
            });

            /* ◀️ 删除附件 */
            this.$detailDelete.on('click', function() {
                if (self.currentDetailId) {
                    self.deleteAttachment(self.currentDetailId);
                }
            });

            /* ◀️ 上传按钮 */
            this.$btnUpload.on('click', function() {
                self.openUploader();
            });

            /* ◀️ 媒体库按钮 */
            this.$btnLibrary.on('click', function() {
                self.loadMediaItems();
            });

            /* ◀️ 插入按钮 */
            this.$btnInsert.on('click', function() {
                self.insertToEditor();
            });

            /* ◀️ 无限滚动（懒加载）- 搜索模式下禁用 */
            this.$grid.parent().on('scroll', this.debounce(function() {
                /* ◀️ 搜索模式下不启用懒加载 */
                if (self.state.isSearch) return;

                if (self.isNearBottom() && !self.state.loading && self.state.hasMore) {
                    self.loadMoreItems();
                }
            }, 200));
        },

        /**
         * 🔗 覆盖原生的【添加媒体】按钮
         */
        overrideMediaButton: function() {
            const self = this;
            
            /* ◀️ 禁用WordPress原生的媒体编辑器 */
            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                /* ◀️ 保存原生的open方法 */
                if (!wp.media.editor._originalOpen) {
                    wp.media.editor._originalOpen = wp.media.editor.open;
                }
                
                /* ◀️ 覆盖open方法 */
                wp.media.editor.open = function(editorId, options) {
                    /* ◀️ 阻止原生弹窗打开，改为打开自定义弹窗 */
                    self.state.currentEditor = editorId || 'content';
                    self.open();
                    return this;
                };
            }
            
            /* ◀️ 绑定新的点击事件（使用新的按钮class） */
            $(document).on('click', '.shiroki-media-trigger, #shiroki-insert-media-button', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                
                /* ◀️ 获取编辑器ID */
                const editorId = $(this).data('editor') || 'content';
                self.state.currentEditor = editorId;
                
                /* ◀️ 打开自定义弹窗 */
                self.open();
                
                return false;
            });
        },

        /**
         * 🪟 打开弹窗
         * @param {Object} options - 可选配置
         * @param {string} options.mode - 模式：'editor' 或 'callback'
         * @param {Function} options.callback - 选择后的回调函数
         */
        open: function(options) {
            options = options || {};
            
            this.state.isOpen = true;
            this.state.selectedItems.clear();
            this.state.mode = options.mode || 'editor';
            this.state.callback = options.callback || null;
            
            /* 📝 更新按钮文字 */
            if (this.state.mode === 'callback') {
                this.$btnInsert.text('选择图片');
            } else {
                this.$btnInsert.text('插入到编辑器');
            }
            
            this.updateInsertButton();
            this.$modal.fadeIn(200);
            $('body').addClass('shiroki-media-modal-open');
            this.loadMediaItems();
        },

        /**
         * 🪟 关闭弹窗
         */
        close: function() {
            this.state.isOpen = false;
            this.$modal.fadeOut(200);
            $('body').removeClass('shiroki-media-modal-open');
        },

        /**
         * 📡 加载媒体项目
         */
        loadMediaItems: function() {
            if (this.state.loading) return;

            this.state.loading = true;
            this.$loading.show();
            this.$grid.empty();
            this.$empty.hide();

            const self = this;
            const postId = $('#post_ID').val() || 0;

            /* ◀️ 重置为第1页 */
            this.state.page = 1;

            $.ajax({
                url: shirokiMediaModalConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_media_modal_items',
                    nonce: shirokiMediaModalConfig.nonce,
                    page: 1,
                    filter: this.state.filter,
                    date: this.state.date,
                    search: this.state.search,
                    is_search: this.state.isSearch,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        self.renderItems(response.data.items);

                        /* ◀️ 搜索模式下加载所有结果，不启用懒加载 */
                        if (self.state.isSearch) {
                            self.state.hasMore = false;
                        } else {
                            self.state.hasMore = response.data.has_more;
                            self.state.page = 2; // ⬅️ 下次从第2页开始加载
                        }

                        if (response.data.items.length === 0) {
                            self.$empty.show();
                        }
                    }
                },
                error: function() {
                    self.$grid.html('<div class="shiroki-media-error">加载失败，请重试</div>');
                },
                complete: function() {
                    self.state.loading = false;
                    self.$loading.hide();
                }
            });
        },

        /**
         * 📡 加载更多项目（懒加载）
         */
        loadMoreItems: function() {
            /* ◀️ 搜索模式下不启用懒加载 */
            if (this.state.loading || !this.state.hasMore || this.state.isSearch) return;

            this.state.loading = true;

            const self = this;
            const postId = $('#post_ID').val() || 0;

            $.ajax({
                url: shirokiMediaModalConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_media_modal_items',
                    nonce: shirokiMediaModalConfig.nonce,
                    page: this.state.page,
                    filter: this.state.filter,
                    date: this.state.date,
                    search: this.state.search,
                    is_search: false, // ⬅️ 懒加载时不是搜索模式
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        self.renderItems(response.data.items, true);
                        self.state.hasMore = response.data.has_more;
                        self.state.page++;
                    }
                },
                complete: function() {
                    self.state.loading = false;
                }
            });
        },

        /**
         * 🎨 渲染媒体项目
         */
        renderItems: function(items, append) {
            if (!append) {
                this.$grid.empty();
            }

            const html = items.map(item => this.createItemHtml(item)).join('');
            this.$grid.append(html);
        },

        /**
         * 🃏 创建媒体项HTML
         */
        createItemHtml: function(item) {
            const isImage = item.file_type === 'image';
            const thumbnail = isImage && item.thumbnail
                ? `<img src="${item.thumbnail}" alt="${this.escapeHtml(item.title)}">`
                : `<div class="shiroki-media-item-icon">${item.icon_svg}</div>`;

            const isSelected = this.state.selectedItems.has(item.id);

            return `
                <div class="shiroki-media-modal-item ${isSelected ? 'selected' : ''}" 
                     data-id="${item.id}"
                     data-url="${item.url}"
                     data-type="${item.file_type}"
                     data-title="${this.escapeHtml(item.title)}"
                     data-mime="${item.mime_type}">
                    <div class="shiroki-media-item-thumbnail">
                        ${thumbnail}
                    </div>
                    <div class="shiroki-media-item-info">
                        <div class="shiroki-media-item-type">${item.file_type}</div>
                        <div class="shiroki-media-item-title">${this.escapeHtml(item.title)}</div>
                    </div>
                </div>
            `;
        },

        /**
         * 📤 打开上传器
         */
        openUploader: function() {
            const self = this;
            
            /* ◀️ 创建文件输入 */
            const $input = $('<input type="file" multiple style="display:none">');
            $('body').append($input);
            
            $input.on('change', function(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    self.uploadFiles(files);
                }
                $input.remove();
            });
            
            $input.click();
        },

        /**
         * 📤 上传文件
         */
        uploadFiles: function(files) {
            const self = this;
            const postId = $('#post_ID').val() || 0;

            Array.from(files).forEach(function(file) {
                const formData = new FormData();
                formData.append('action', 'shiroki_upload_media_modal');
                formData.append('nonce', shirokiMediaModalConfig.nonce);
                formData.append('file', file);
                formData.append('post_id', postId);

                $.ajax({
                    url: shirokiMediaModalConfig.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            /* ◀️ 添加新上传的项目到网格 */
                            const html = self.createItemHtml(response.data.item);
                            self.$grid.prepend(html);
                            self.$empty.hide();
                        }
                    },
                    error: function() {
                        console.error('上传失败');
                    }
                });
            });
        },

        /**
         * 📝 插入到编辑器
         */
        insertToEditor: function() {
            const self = this;
            const selectedIds = Array.from(this.state.selectedItems);
            
            if (selectedIds.length === 0) return;

            /* ◀️ 获取选中的媒体项 */
            selectedIds.forEach(function(id) {
                const $item = self.$grid.find(`[data-id="${id}"]`);
                
                /* ◀️ 从data属性获取完整数据 */
                const url = $item.data('url');
                const type = $item.data('type');
                const title = $item.data('title');
                const mime = $item.data('mime');
                
                /* ◀️ 如果是回调模式，调用回调函数 */
                if (self.state.mode === 'callback' && typeof self.state.callback === 'function') {
                    self.state.callback({
                        id: id,
                        url: url,
                        type: type,
                        title: title,
                        mime: mime
                    });
                } else {
                    /* ◀️ 默认：插入到编辑器 */
                    let insertContent = '';
                    
                    if (type === 'image') {
                        /* 🖼️ 图片 */
                        insertContent = `<img src="${url}" alt="${title}" />`;
                    } else if (type === 'video') {
                        /* 🎬 视频 - 使用WordPress视频短代码 */
                        insertContent = `[video src="${url}"]`;
                    } else if (type === 'audio') {
                        /* 🎵 音频 - 使用WordPress音频短代码 */
                        insertContent = `[audio src="${url}"]`;
                    } else {
                        /* 📄 其他文件 - 使用downloadbtn短代码 */
                        insertContent = `[downloadbtn link='${url}']${title}[/downloadbtn]`;
                    }
                    
                    /* ◀️ 插入到编辑器 */
                    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                        wp.media.editor.insert(insertContent);
                    }
                }
            });

            /* ◀️ 关闭弹窗 */
            this.close();
        },

        /**
         * 🔘 更新插入按钮状态
         */
        updateInsertButton: function() {
            const hasSelection = this.state.selectedItems.size > 0;
            this.$btnInsert.prop('disabled', !hasSelection);
        },

        /**
         * 📜 检查是否接近底部
         */
        isNearBottom: function() {
            const $container = this.$grid.parent();
            const scrollTop = $container.scrollTop();
            const containerHeight = $container.height();
            const scrollHeight = $container[0].scrollHeight;
            
            return scrollTop + containerHeight >= scrollHeight - 100;
        },

        /**
         * 🛡️ HTML转义
         */
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
        },

        /**
         * 📋 打开详情抽屉
         */
        openDetailDrawer: function(id) {
            const self = this;
            this.currentDetailId = id;
            
            /* ◀️ 从DOM获取媒体项数据 */
            const $item = this.$grid.find(`[data-id="${id}"]`);
            if ($item.length === 0) return;
            
            const url = $item.data('url');
            const type = $item.data('type');
            const title = $item.data('title');
            const mime = $item.data('mime');
            
            /* ◀️ 设置预览 */
            if (type === 'image') {
                this.$detailPreview.html(`<img src="${url}" alt="${this.escapeHtml(title)}">`);
            } else {
                /* ◀️ 获取图标SVG */
                const iconSvg = this.getFileIconSvg(mime);
                this.$detailPreview.html(`<div class="shiroki-media-detail-preview-icon">${iconSvg}</div>`);
            }
            
            /* ◀️ 设置表单字段 */
            this.$detailTitle.val(title);
            this.$detailUrl.val(url);
            
            /* ◀️ 显示/隐藏替代文本字段（仅图片） */
            if (type === 'image') {
                this.$detailAltField.show();
            } else {
                this.$detailAltField.hide();
            }
            
            /* ◀️ 获取完整的附件详情 */
            $.ajax({
                url: shirokiMediaModalConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_media_detail',
                    nonce: shirokiMediaModalConfig.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        /* ◀️ 填充表单数据 */
                        self.$detailTitle.val(data.title);
                        self.$detailCaption.val(data.caption);
                        self.$detailAlt.val(data.alt);
                        self.$detailDescription.val(data.description);
                        
                        /* ◀️ 填充文件信息 */
                        self.$detailFilename.text(data.filename);
                        self.$detailMime.text(data.mime_type);
                        self.$detailDate.text(data.date);
                        self.$detailSize.text(data.file_size);
                        
                        /* ◀️ 显示/隐藏尺寸信息 */
                        if (data.dimensions) {
                            self.$detailDimensions.text(data.dimensions);
                            self.$detailDimensionsItem.show();
                        } else {
                            self.$detailDimensionsItem.hide();
                        }
                        
                        /* ◀️ 更新查看链接 */
                        self.$detailView.attr('href', data.permalink);
                        
                        /* ◀️ 保存原始数据用于检测变更 */
                        self.originalDetailData = {
                            title: data.title,
                            caption: data.caption,
                            alt: data.alt,
                            description: data.description
                        };
                    }
                }
            });
            
            /* ◀️ 显示抽屉 */
            this.$detailOverlay.addClass('active');
            this.$detailDrawer.addClass('active');
        },

        /**
         * 📋 关闭详情抽屉（带自动保存）
         */
        closeDetailDrawer: function() {
            const self = this;
            
            /* ◀️ 检查是否有变更 */
            if (this.currentDetailId && this.hasDetailChanged()) {
                /* ◀️ 有变更，先保存 */
                this.saveDetailData(function(success) {
                    /* ◀️ 保存完成后关闭抽屉 */
                    self.doCloseDetailDrawer();
                    
                    /* ◀️ 同步更新网格中的显示 */
                    if (success) {
                        self.syncGridItemData();
                    }
                });
            } else {
                /* ◀️ 无变更，直接关闭 */
                this.doCloseDetailDrawer();
            }
        },
        
        /**
         * 📋 实际关闭抽屉
         */
        doCloseDetailDrawer: function() {
            this.$detailDrawer.removeClass('active');
            this.$detailOverlay.removeClass('active');
            this.currentDetailId = null;
            this.originalDetailData = {};
        },
        
        /**
         * 🔍 检查详情是否有变更
         */
        hasDetailChanged: function() {
            if (!this.currentDetailId) return false;
            
            const currentData = {
                title: this.$detailTitle.val(),
                caption: this.$detailCaption.val(),
                alt: this.$detailAlt.val(),
                description: this.$detailDescription.val()
            };
            
            return (
                currentData.title !== (this.originalDetailData.title || '') ||
                currentData.caption !== (this.originalDetailData.caption || '') ||
                currentData.alt !== (this.originalDetailData.alt || '') ||
                currentData.description !== (this.originalDetailData.description || '')
            );
        },
        
        /**
         * 💾 保存详情数据
         */
        saveDetailData: function(callback) {
            if (this.isSaving || !this.currentDetailId) {
                if (callback) callback(false);
                return;
            }
            
            this.isSaving = true;
            const self = this;
            
            const data = {
                id: this.currentDetailId,
                title: this.$detailTitle.val(),
                caption: this.$detailCaption.val(),
                alt: this.$detailAlt.val(),
                description: this.$detailDescription.val()
            };
            
            $.ajax({
                url: shirokiMediaModalConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_save_media_detail',
                    nonce: shirokiMediaModalConfig.nonce,
                    id: data.id,
                    title: data.title,
                    caption: data.caption,
                    alt: data.alt,
                    description: data.description
                },
                success: function(response) {
                    self.isSaving = false;
                    
                    if (response.success) {
                        /* ◀️ 更新原始数据 */
                        self.originalDetailData = {
                            title: data.title,
                            caption: data.caption,
                            alt: data.alt,
                            description: data.description
                        };
                        
                        /* ◀️ 显示保存成功提示 */
                        self.showSaveNotification('保存成功');
                        
                        if (callback) callback(true);
                    } else {
                        self.showSaveNotification('保存失败：' + response.data, 'error');
                        if (callback) callback(false);
                    }
                },
                error: function() {
                    self.isSaving = false;
                    self.showSaveNotification('保存失败，请重试', 'error');
                    if (callback) callback(false);
                }
            });
        },
        
        /**
         * 🔄 同步网格项数据
         */
        syncGridItemData: function() {
            if (!this.currentDetailId) return;
            
            const $item = this.$grid.find(`[data-id="${this.currentDetailId}"]`);
            if ($item.length === 0) return;
            
            /* ◀️ 更新标题 */
            const newTitle = this.$detailTitle.val();
            $item.data('title', newTitle);
            $item.find('.shiroki-media-item-title').text(newTitle);
        },
        
        /**
         * 🔔 显示保存通知
         */
        showSaveNotification: function(message, type) {
            /* ◀️ 创建通知元素 */
            const $notification = $(`
                <div class="shiroki-media-save-notification ${type || 'success'}">
                    ${message}
                </div>
            `);
            
            /* ◀️ 添加到抽屉 */
            this.$detailDrawer.append($notification);
            
            /* ◀️ 动画显示 */
            setTimeout(function() {
                $notification.addClass('show');
            }, 10);
            
            /* ◀️ 自动隐藏 */
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 2000);
        },

        /**
         * 📋 复制URL到剪贴板
         */
        copyUrlToClipboard: function() {
            const url = this.$detailUrl.val();
            if (!url) return;

            const self = this;

            /* ◀️ 使用现代Clipboard API */
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    self.showCopySuccess();
                }).catch(function() {
                    /* ◀️ 降级方案 */
                    self.fallbackCopyUrl(url);
                });
            } else {
                /* ◀️ 降级方案 */
                this.fallbackCopyUrl(url);
            }
        },

        /**
         * 📋 降级复制方案
         */
        fallbackCopyUrl: function(url) {
            const self = this;
            const $input = this.$detailUrl;

            /* ◀️ 选择文本 */
            $input.select();
            $input[0].setSelectionRange(0, 99999);

            try {
                /* ◀️ 执行复制命令 */
                const successful = document.execCommand('copy');
                if (successful) {
                    self.showCopySuccess();
                } else {
                    self.showSaveNotification('复制失败，请手动复制', 'error');
                }
            } catch (err) {
                self.showSaveNotification('复制失败，请手动复制', 'error');
            }

            /* ◀️ 取消选择 */
            window.getSelection().removeAllRanges();
        },

        /**
         * ✅ 显示复制成功状态
         */
        showCopySuccess: function() {
            const $btn = this.$detailCopyUrl;
            const originalHtml = $btn.html();

            /* ◀️ 更改按钮状态 */
            $btn.addClass('copied');
            $btn.html(`
                <svg viewBox="0 0 24 24" width="16" height="16">
                    <path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                <span>已复制</span>
            `);

            /* ◀️ 显示成功提示 */
            this.showSaveNotification('链接已复制到剪贴板', 'success');

            /* ◀️ 2秒后恢复按钮状态 */
            setTimeout(() => {
                $btn.removeClass('copied');
                $btn.html(originalHtml);
            }, 2000);
        },

        /**
         * 💬 显示提示气泡
         */
        showTooltip: function(e, text) {
            /* ◀️ 如果已存在则先移除 */
            this.hideTooltip();

            /* ◀️ 创建气泡元素 */
            this.$tooltip = $(`<div class="shiroki-media-tooltip">${text}</div>`);
            $('body').append(this.$tooltip);

            /* ◀️ 设置初始位置 */
            this.updateTooltipPosition(e);

            /* ◀️ 显示气泡 */
            setTimeout(() => {
                if (this.$tooltip) {
                    this.$tooltip.addClass('show');
                }
            }, 10);
        },

        /**
         * 💬 隐藏提示气泡
         */
        hideTooltip: function() {
            if (this.$tooltip) {
                this.$tooltip.remove();
                this.$tooltip = null;
            }
        },

        /**
         * 💬 更新气泡位置
         */
        updateTooltipPosition: function(e) {
            if (!this.$tooltip) return;
            
            const offsetX = 15;
            const offsetY = 15;
            
            let left = e.clientX + offsetX;
            let top = e.clientY + offsetY;
            
            /* ◀️ 检查是否超出视口右边界 */
            const tooltipWidth = this.$tooltip.outerWidth();
            const tooltipHeight = this.$tooltip.outerHeight();
            
            if (left + tooltipWidth > $(window).width()) {
                left = e.clientX - tooltipWidth - offsetX;
            }
            
            /* ◀️ 检查是否超出视口下边界 */
            if (top + tooltipHeight > $(window).height()) {
                top = e.clientY - tooltipHeight - offsetX;
            }
            
            this.$tooltip.css({
                left: left,
                top: top
            });
        },

        /**
         * 🗑️ 删除附件
         */
        deleteAttachment: function(id) {
            if (!confirm('确定要永久删除这个附件吗？此操作不可恢复。')) {
                return;
            }

            const self = this;

            $.ajax({
                url: shirokiMediaModalConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_delete_media',
                    nonce: shirokiMediaModalConfig.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        /* ◀️ 从网格中移除 */
                        self.$grid.find(`[data-id="${id}"]`).remove();

                        /* ◀️ 从选中集合中移除 */
                        self.state.selectedItems.delete(id);
                        self.updateInsertButton();

                        /* ◀️ 关闭抽屉 */
                        self.closeDetailDrawer();
                        
                        /* ◀️ 检查是否为空 */
                        if (self.$grid.children().length === 0) {
                            self.$empty.show();
                        }
                    } else {
                        alert('删除失败：' + response.data);
                    }
                },
                error: function() {
                    alert('删除失败，请重试');
                }
            });
        },

        /**
         * 🔣 获取文件类型SVG图标
         */
        getFileIconSvg: function(mimeType) {
            /* 🖼️ 图片 */
            if (mimeType && mimeType.indexOf('image/') === 0) {
                return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7392 0.1154 242.05)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
            }
            
            /* 🎬 视频 */
            if (mimeType && mimeType.indexOf('video/') === 0) {
                return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7565 0.143 355.9)" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>';
            }
            
            /* 🎵 音频 */
            if (mimeType && mimeType.indexOf('audio/') === 0) {
                return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.6637 0.1634 296.96)" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
            }
            
            /* 📄 PDF */
            if (mimeType === 'application/pdf') {
                return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7421 0.1503 21.35)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
            }
            
            /* 📊 文档 */
            if (mimeType && mimeType.indexOf('application/') === 0) {
                return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.786 0.1379 154.52)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
            }
            
            /* 📦 默认 */
            return '<svg viewBox="0 0 24 24" fill="none" stroke="oklch(0.7457 0.0304 254.72)" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>';
        }
    };

    /**
     * 🚀 DOM加载完成后初始化
     */
    $(document).ready(function() {
        /* ◀️ 检查媒体模态框元素是否存在 */
        if ($('#shiroki-media-modal').length > 0) {
            ShirokiMediaModal.init();
        }
    });

    /**
     * 🌐 暴露到全局
     */
    window.ShirokiMediaModal = ShirokiMediaModal;

})(jQuery);
