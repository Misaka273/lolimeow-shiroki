/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🧩 小工具管理页面 JavaScript
 * 🎨 拟态拟物玻璃质感设计 - 拖拽式侧边栏配置
 */

(function($) {
    'use strict';

    /**
         * 🎯 小工具管理器
         */
        var WidgetManager = {
        /* 📊 状态 */
        availableWidgets: [],
        activeWidgets: {},
        sidebars: {},
        currentEditingWidget: null,
        isLoading: false,
        hasChanges: false,
        isDragging: false,
        dragJustFinished: false,
        isDraggingFromLibrary: false,
        confirmCallback: null,

        /**
         * 🚀 初始化
         */
        init: function() {
            this.bindEvents();
            this.loadWidgets();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🔍 搜索小工具 */
            $(document).on('input', '#shiroki-widget-search', function() {
                var query = $(this).val().toLowerCase();
                self.filterWidgets(query);
            });

            /* ➕ 点击添加小工具 */
            $(document).on('click', '.shiroki-widget-item-add', function(e) {
                e.stopPropagation();
                var idBase = $(this).closest('.shiroki-widget-item').data('id-base');
                var sidebarId = $('.shiroki-widget-sidebar-content').first().data('sidebar');
                self.activateWidget(idBase, sidebarId);
            });

            /* ⚙️ 点击设置小工具 */
            $(document).on('click', '.shiroki-widget-btn-setting', function(e) {
                e.stopPropagation();
                var widgetId = $(this).closest('.shiroki-widget-active-item').data('widget-id');
                self.openWidgetModal(widgetId);
            });

            /* 🗑️ 点击删除小工具 */
            $(document).on('click', '.shiroki-widget-btn-remove', function(e) {
                e.stopPropagation();
                var $item = $(this).closest('.shiroki-widget-active-item');
                var widgetId = $item.data('widget-id');
                self.deactivateWidget(widgetId, $item);
            });

            /* 🖱️ 双击编辑小工具 */
            $(document).on('dblclick', '.shiroki-widget-active-item', function(e) {
                /* 🚫 如果点击的是按钮，不触发编辑 */
                if ($(e.target).closest('button').length) {
                    return;
                }
                /* 🚫 如果刚刚完成拖拽，不触发编辑 */
                if (self.dragJustFinished) {
                    return;
                }
                var widgetId = $(this).data('widget-id');
                self.openWidgetModal(widgetId);
            });

            /* 🔄 重置 */
            $(document).on('click', '#shiroki-widget-reset', function() {
                self.resetWidgets();
            });

            /* 🪟 Modal事件 */
            $(document).on('click', '#shiroki-widget-modal-close, #shiroki-widget-modal-cancel', function() {
                self.closeWidgetModal();
            });

            $(document).on('click', '#shiroki-widget-modal-confirm', function() {
                self.saveWidgetSettings();
            });

            $(document).on('click', '#shiroki-widget-modal-delete', function() {
                self.deleteWidgetFromModal();
            });

            $(document).on('click', '.shiroki-widget-modal-backdrop', function() {
                self.closeWidgetModal();
            });

            /* ⌨️ ESC键关闭Modal */
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if ($('#shiroki-widget-modal').is(':visible')) {
                        self.closeWidgetModal();
                    }
                    if ($('#shiroki-confirm-modal').is(':visible')) {
                        self.closeConfirmModal();
                    }
                }
            });

            /* ⚠️ 确认对话框事件 */
            $(document).on('click', '#shiroki-confirm-modal-close, #shiroki-confirm-modal-cancel, #shiroki-confirm-modal .shiroki-widget-modal-backdrop', function() {
                self.closeConfirmModal();
            });

            $(document).on('click', '#shiroki-confirm-modal-confirm', function() {
                if (self.confirmCallback) {
                    self.confirmCallback();
                    self.confirmCallback = null;
                }
                self.closeConfirmModal();
            });

            /* 🪁 媒体上传 - 选择媒体 */
            $(document).on('click', '.shiroki-widget-media-btn[data-action="select"]', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $uploader = $btn.closest('.shiroki-widget-media-uploader');
                var mediaType = $btn.data('type') || 'image';
                var multiple = $btn.data('multiple') === true || $btn.data('multiple') === 'true';
                self.openMediaUploader($uploader, mediaType, multiple);
            });

            /* 📷 媒体上传 - 移除媒体 */
            $(document).on('click', '.shiroki-widget-media-btn[data-action="remove"]', function(e) {
                e.preventDefault();
                var $uploader = $(this).closest('.shiroki-widget-media-uploader');
                $uploader.find('input[name="attachment_id"]').val('');
                $uploader.find('input[type="text"]').val('');
                $uploader.find('.shiroki-widget-media-remove').hide();
                var $preview = $uploader.next('.shiroki-widget-media-preview');
                if ($preview.length) {
                    $preview.hide().find('img').attr('src', '');
                }
            });

        },

        /**
         * 📡 加载小工具数据
         */
        loadWidgets: function() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            $.ajax({
                url: shirokiWidgetConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_widgets',
                    nonce: shirokiWidgetConfig.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.availableWidgets = response.data.available_widgets;
                        this.activeWidgets = response.data.active_widgets;
                        this.sidebars = response.data.sidebars;
                        this.renderLibrary();
                        this.renderSidebars();
                        this.initSortable();
                    } else {
                        this.showError(response.data.message || '加载失败');
                    }
                }.bind(this),
                error: function() {
                    this.showError('网络错误，请稍后重试');
                }.bind(this),
                complete: function() {
                    this.isLoading = false;
                    this.hideLoading();
                }.bind(this)
            });
        },

        /**
         * 🎨 渲染小工具库
         */
        renderLibrary: function() {
            var $library = $('#shiroki-widget-library');

            if (!this.availableWidgets || this.availableWidgets.length === 0) {
                $library.html('<div class="shiroki-widget-empty"><div class="shiroki-widget-empty-icon">📦</div><div class="shiroki-widget-empty-text">暂无可用小工具</div></div>');
                return;
            }

            var html = this.availableWidgets.map(function(widget) {
                return `
                    <div class="shiroki-widget-item" data-id-base="${widget.id}" data-name="${this.escapeHtml(widget.name)}">
                        <div class="shiroki-widget-item-icon">${widget.icon}</div>
                        <div class="shiroki-widget-item-info">
                            <div class="shiroki-widget-item-name">${this.escapeHtml(widget.name)}</div>
                            <div class="shiroki-widget-item-desc">${this.escapeHtml(widget.description || '')}</div>
                        </div>
                        <div class="shiroki-widget-item-add" title="添加到侧边栏">➕</div>
                    </div>
                `;
            }.bind(this)).join('');

            $library.html(html);
        },

        /**
         * 🎨 渲染侧边栏
         */
        renderSidebars: function() {
            var self = this;

            $('.shiroki-widget-sidebar-content').each(function() {
                var sidebarId = $(this).data('sidebar');
                var widgets = self.activeWidgets[sidebarId] || [];
                var $content = $(this);

                /* 📊 更新计数 */
                $(`.shiroki-widget-count-num[data-sidebar="${sidebarId}"]`).text(widgets.length);

                if (widgets.length === 0) {
                    $content.html(`
                        <div class="shiroki-widget-drop-zone">
                            <span class="shiroki-widget-drop-hint">📥 拖拽小工具到此处</span>
                        </div>
                    `);
                    return;
                }

                var html = widgets.map(function(widget) {
                    return self.renderActiveWidget(widget);
                }).join('');

                $content.html(html);
            });
        },

        /**
         * 🎨 渲染激活的小工具
         */
        renderActiveWidget: function(widget) {
            /* 📝 显示标题（如果有）或名称 */
            var displayTitle = widget.title || widget.name || '未命名';
            var subTitle = widget.title ? widget.name : '';

            return `
                <div class="shiroki-widget-active-item" data-widget-id="${widget.widget_id}" data-id-base="${widget.id_base}">
                    <div class="shiroki-widget-active-icon">${widget.icon}</div>
                    <div class="shiroki-widget-active-info">
                        <div class="shiroki-widget-active-name">${this.escapeHtml(displayTitle)}</div>
                        ${subTitle ? `<div class="shiroki-widget-active-title">${this.escapeHtml(subTitle)}</div>` : ''}
                    </div>
                    <div class="shiroki-widget-active-actions">
                        <button class="shiroki-widget-btn-setting" title="设置">⚙️</button>
                        <button class="shiroki-widget-btn-remove" title="删除">🗑️</button>
                    </div>
                </div>
            `;
        },

        /**
         * 🔄 初始化拖拽排序
         */
        initSortable: function() {
            var self = this;

            /* 📦 小工具库可拖拽 */
            $('#shiroki-widget-library').sortable({
                connectWith: '.shiroki-widget-sidebar-content',
                items: '.shiroki-widget-item',
                helper: 'clone',
                appendTo: 'body',
                zIndex: 100000,
                delay: 150,
                distance: 10,
                start: function(e, ui) {
                    ui.helper.addClass('dragging');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('dragging');
                }
            });

            /* 📦 侧边栏内容可排序 */
            $('.shiroki-widget-sidebar-content').sortable({
                connectWith: '.shiroki-widget-sidebar-content',
                items: '.shiroki-widget-active-item',
                delay: 150,
                distance: 10,
                start: function(e, ui) {
                    self.isDragging = true;
                    ui.item.addClass('dragging');
                },
                receive: function(e, ui) {
                    var $container = $(this);
                    var sidebarId = $container.data('sidebar');
                    var idBase = ui.item.data('id-base');

                    /* 🔄 如果是从库中拖拽过来的 */
                    if (ui.sender && ui.sender.attr('id') === 'shiroki-widget-library') {
                        /* ⏳ 显示加载状态 */
                        ui.item.addClass('shiroki-widget-adding');

                        /* 📝 标记为从库中拖拽，防止update事件重复保存 */
                        self.isDraggingFromLibrary = true;

                        self.activateWidget(idBase, sidebarId, function(widget) {
                            /* 🔄 创建新的小工具元素 */
                            var $newWidget = $(self.renderActiveWidget(widget));

                            /* 🗑️ 如果容器中有放置区域，先清空它，然后添加新元素 */
                            var $dropZone = $container.find('.shiroki-widget-drop-zone');
                            if ($dropZone.length) {
                                $dropZone.replaceWith($newWidget);
                            } else {
                                /* 🔄 替换克隆的元素 */
                                ui.item.replaceWith($newWidget);
                            }

                            /* 🎬 添加入场动画 */
                            $newWidget.hide().fadeIn(200);

                            /* 📊 将新小工具添加到数据中 */
                            if (!self.activeWidgets[sidebarId]) {
                                self.activeWidgets[sidebarId] = [];
                            }
                            self.activeWidgets[sidebarId].push(widget);

                            self.updateSidebarCount(sidebarId);
                            /* ✅ PHP端已经保存，不需要再次保存 */

                            /* 🔄 清除标记 */
                            self.isDraggingFromLibrary = false;
                        });
                    } else {
                        /* 🔄 只是排序变化，需要保存 */
                        self.saveAllChanges(true);
                    }
                },
                update: function(e, ui) {
                    var sidebarId = $(this).data('sidebar');
                    self.updateSidebarCount(sidebarId);

                    /* 📝 检查是否从库中拖拽过来的，如果是则不保存（已在receive中处理） */
                    if (self.isDraggingFromLibrary) {
                        return;
                    }

                    /* 💾 自动保存更改（静默模式） */
                    self.saveAllChanges(true);
                },
                stop: function(e, ui) {
                    self.isDragging = false;
                    self.dragJustFinished = true;
                    ui.item.removeClass('dragging');

                    /* 🔄 拖拽结束后刷新sortable */
                    $(this).sortable('refresh');

                    /* ⏳ 300ms后清除拖拽完成标志 */
                    setTimeout(function() {
                        self.dragJustFinished = false;
                    }, 300);
                },
                over: function() {
                    $(this).addClass('drag-over');
                },
                out: function() {
                    $(this).removeClass('drag-over');
                }
            });
        },

        /**
         * 🔍 过滤小工具
         */
        filterWidgets: function(query) {
            $('.shiroki-widget-item').each(function() {
                var name = $(this).data('name').toLowerCase();
                if (name.indexOf(query) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        },

        /**
         * ➕ 激活小工具
         */
        activateWidget: function(idBase, sidebarId, callback) {
            $.ajax({
                url: shirokiWidgetConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_activate_widget',
                    nonce: shirokiWidgetConfig.nonce,
                    id_base: idBase,
                    sidebar_id: sidebarId
                },
                success: function(response) {
                    if (response.success) {
                        if (callback) {
                            callback(response.data.widget);
                        }
                    } else {
                        alert(response.data.message || '添加失败');
                    }
                }.bind(this),
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        },

        /**
         * 🗑️ 停用小工具
         */
        deactivateWidget: function(widgetId, $item) {
            var sidebarId = $item.closest('.shiroki-widget-sidebar-content').data('sidebar');
            var self = this;
            
            /* 📝 根据侧边栏显示不同的确认消息 */
            var isPermanent = sidebarId === 'wp_inactive_widgets';
            var confirmTitle = isPermanent ? '🗑️ 彻底删除小工具' : '📤 移出侧边栏';
            var confirmMsg = isPermanent 
                ? '<p>确定要<strong>彻底删除</strong>这个小工具吗？</p><p style="color: var(--admin-danger-text); margin-top: 8px;">⚠️ 此操作不可恢复！</p>' 
                : '<p>确定要将此小工具从侧边栏中移除吗？</p><p style="color: var(--admin-text-muted); margin-top: 8px; font-size: 12px;">💡 移除后可在"未启用的小工具"中找到</p>';
            
            this.showConfirm(confirmMsg, function() {
                $.ajax({
                    url: shirokiWidgetConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_deactivate_widget',
                        nonce: shirokiWidgetConfig.nonce,
                        widget_id: widgetId,
                        sidebar_id: sidebarId
                    },
                    success: function(response) {
                        if (response.success) {
                            $item.fadeOut(200, function() {
                                $(this).remove();
                                self.updateSidebarCount(sidebarId);
                                /* 🔄 重新加载数据以同步未启用的小工具列表 */
                                self.loadWidgets();
                            });
                        } else {
                            alert(response.data.message || '删除失败');
                        }
                    },
                    error: function() {
                        alert('网络错误，请稍后重试');
                    }
                });
            }, confirmTitle);
        },

        /**
         * 📊 更新侧边栏计数
         */
        updateSidebarCount: function(sidebarId) {
            var count = $(`.shiroki-widget-sidebar-content[data-sidebar="${sidebarId}"] .shiroki-widget-active-item`).length;
            $(`.shiroki-widget-count-num[data-sidebar="${sidebarId}"]`).text(count);

            /* 📭 如果为空，显示放置区域 */
            if (count === 0) {
                $(`.shiroki-widget-sidebar-content[data-sidebar="${sidebarId}"]`).html(`
                    <div class="shiroki-widget-drop-zone">
                        <span class="shiroki-widget-drop-hint">📥 拖拽小工具到此处</span>
                    </div>
                `);
            }
        },

        /**
         * 🪟 打开小工具设置Modal
         */
        openWidgetModal: function(widgetId) {
            var widget = this.findWidgetById(widgetId);

            /* 🔄 如果在数据中找不到，从DOM创建临时对象 */
            if (!widget) {
                var $item = $(`.shiroki-widget-active-item[data-widget-id="${widgetId}"]`);
                if ($item.length === 0) return;

                /* 📝 从DOM获取显示的名称和副标题 */
                var displayName = $item.find('.shiroki-widget-active-name').text();
                var subTitle = $item.find('.shiroki-widget-active-title').text();

                /* 🔄 判断哪个是标题，哪个是名称 */
                var title = '', name = displayName;
                if (subTitle) {
                    /* 如果有副标题，说明displayName是标题，subTitle是名称 */
                    title = displayName;
                    name = subTitle;
                }

                widget = {
                    widget_id: widgetId,
                    id_base: $item.data('id-base'),
                    name: name,
                    title: title,
                    instance: {}
                };
            }

            this.currentEditingWidget = widget;

            /* 📝 生成设置表单 */
            var formHtml = this.generateWidgetForm(widget);
            $('#shiroki-widget-modal-body').html(formHtml);

            /* 📝 更新Modal标题为小工具名称 */
            var modalTitle = widget.name || '小工具设置';
            $('.shiroki-widget-modal-title').text('⚙️ ' + modalTitle);

            /* 🪟 显示Modal */
            $('#shiroki-widget-modal').fadeIn(200);
        },

        /**
         * 🔍 查找小工具
         */
        findWidgetById: function(widgetId) {
            for (var sidebarId in this.activeWidgets) {
                var widgets = this.activeWidgets[sidebarId];
                for (var i = 0; i < widgets.length; i++) {
                    if (widgets[i].widget_id === widgetId) {
                        return widgets[i];
                    }
                }
            }
            return null;
        },

        /**
         * 📝 生成小工具表单
         */
        generateWidgetForm: function(widget) {
            var html = '';
            var instance = widget.instance || {};

            /* 📝 标题字段 */
            html += `
                <div class="shiroki-widget-form-group">
                    <label class="shiroki-widget-form-label">标题</label>
                    <input type="text" class="shiroki-widget-form-input" name="title" value="${this.escapeHtml(widget.title || instance.title || '')}" placeholder="小工具标题">
                </div>
            `;

            /* 📝 根据小工具类型添加更多字段 */
            switch (widget.id_base) {
                case 'text':
                case 'custom_html':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">内容</label>
                            <textarea class="shiroki-widget-form-textarea" name="text" placeholder="输入内容...">${this.escapeHtml(instance.text || '')}</textarea>
                        </div>
                    `;
                    break;

                case 'recent-posts':
                case 'recent-comments':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数量</label>
                            <input type="number" class="shiroki-widget-form-input" name="number" value="${instance.number || 5}" min="1" max="20">
                        </div>
                    `;
                    break;

                case 'categories':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="count" ${instance.count ? 'checked' : ''}>
                                <span>显示文章数</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="hierarchical" ${instance.hierarchical ? 'checked' : ''}>
                                <span>层级显示</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="dropdown" ${instance.dropdown ? 'checked' : ''}>
                                <span>下拉菜单显示</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'archives':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="count" ${instance.count ? 'checked' : ''}>
                                <span>显示文章数</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="dropdown" ${instance.dropdown ? 'checked' : ''}>
                                <span>下拉菜单显示</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'pages':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">排序方式</label>
                            <select class="shiroki-widget-form-input" name="sortby">
                                <option value="post_title" ${instance.sortby === 'post_title' ? 'selected' : ''}>页面标题</option>
                                <option value="menu_order" ${instance.sortby === 'menu_order' ? 'selected' : ''}>页面顺序</option>
                                <option value="ID" ${instance.sortby === 'ID' ? 'selected' : ''}>页面ID</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="exclude" ${instance.exclude ? 'checked' : ''}>
                                <span>排除首页</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'meta':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 元数据小工具显示登录/登出链接、RSS订阅等</p>
                        </div>
                    `;
                    break;

                case 'search':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 搜索小工具显示站点搜索表单</p>
                        </div>
                    `;
                    break;

                case 'calendar':
                case 'wp_widget_calendar':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 日历小工具显示文章发布日历</p>
                        </div>
                    `;
                    break;

                case 'audio':
                case 'media_audio':
                case 'wp_widget_media_audio':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">音频文件</label>
                            <div class="shiroki-widget-media-uploader" data-field="url">
                                <input type="hidden" name="attachment_id" value="${instance.attachment_id || ''}">
                                <input type="text" class="shiroki-widget-form-input" name="url" value="${instance.url || ''}" placeholder="音频文件URL">
                                <button type="button" class="shiroki-widget-media-btn" data-action="select" data-type="audio">🎵 选择音频</button>
                                <button type="button" class="shiroki-widget-media-btn shiroki-widget-media-remove" data-action="remove" style="${instance.url ? '' : 'display:none'}">🗑️ 移除</button>
                            </div>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="preload" ${instance.preload !== 'none' ? 'checked' : ''}>
                                <span>预加载音频</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="loop" ${instance.loop ? 'checked' : ''}>
                                <span>循环播放</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'video':
                case 'media_video':
                case 'wp_widget_media_video':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">视频文件</label>
                            <div class="shiroki-widget-media-uploader" data-field="url">
                                <input type="hidden" name="attachment_id" value="${instance.attachment_id || ''}">
                                <input type="text" class="shiroki-widget-form-input" name="url" value="${instance.url || ''}" placeholder="视频文件URL">
                                <button type="button" class="shiroki-widget-media-btn" data-action="select" data-type="video">🎬 选择视频</button>
                                <button type="button" class="shiroki-widget-media-btn shiroki-widget-media-remove" data-action="remove" style="${instance.url ? '' : 'display:none'}">🗑️ 移除</button>
                            </div>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="loop" ${instance.loop ? 'checked' : ''}>
                                <span>循环播放</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'gallery':
                case 'media_gallery':
                case 'wp_widget_media_gallery':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">选择图片</label>
                            <div class="shiroki-widget-media-uploader" data-field="ids">
                                <input type="hidden" name="attachment_id" value="${instance.attachment_id || ''}">
                                <input type="text" class="shiroki-widget-form-input" name="ids" value="${instance.ids || ''}" placeholder="图片ID，多个用逗号分隔">
                                <button type="button" class="shiroki-widget-media-btn" data-action="select" data-multiple="true">📷 选择图片</button>
                            </div>
                            <p class="shiroki-widget-form-hint" style="margin-top: 8px; font-size: 12px;">💡 按住 Ctrl/Cmd 键可选择多张图片</p>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">列数</label>
                            <select class="shiroki-widget-form-input" name="columns">
                                <option value="1" ${instance.columns === '1' ? 'selected' : ''}>1列</option>
                                <option value="2" ${instance.columns === '2' ? 'selected' : ''}>2列</option>
                                <option value="3" ${instance.columns === '3' ? 'selected' : ''}>3列</option>
                                <option value="4" ${instance.columns === '4' ? 'selected' : ''}>4列</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">图片尺寸</label>
                            <select class="shiroki-widget-form-input" name="size">
                                <option value="thumbnail" ${instance.size === 'thumbnail' ? 'selected' : ''}>缩略图</option>
                                <option value="medium" ${instance.size === 'medium' ? 'selected' : ''}>中等</option>
                                <option value="large" ${instance.size === 'large' ? 'selected' : ''}>大图</option>
                                <option value="full" ${instance.size === 'full' ? 'selected' : ''}>原图</option>
                            </select>
                        </div>
                    `;
                    break;

                case 'rss':
                case 'rss_errors':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 RSS小工具用于显示外部RSS订阅源</p>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">RSS地址</label>
                            <input type="text" class="shiroki-widget-form-input" name="url" value="${instance.url || ''}" placeholder="https://gl.baimu.live/feed">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数量</label>
                            <input type="number" class="shiroki-widget-form-input" name="items" value="${instance.items || 10}" min="1" max="20">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_summary" ${instance.show_summary ? 'checked' : ''}>
                                <span>显示摘要</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_author" ${instance.show_author ? 'checked' : ''}>
                                <span>显示作者</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_date" ${instance.show_date ? 'checked' : ''}>
                                <span>显示日期</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'block':
                case 'wp_block':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">区块内容 (HTML)</label>
                            <textarea class="shiroki-widget-form-textarea" name="content" rows="10" placeholder="在此输入区块内容，支持HTML...">${this.escapeHtml(instance.content || '')}</textarea>
                        </div>
                    `;
                    break;

                case 'links':
                case 'link':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">选择链接分类</label>
                            <select class="shiroki-widget-form-input" name="category">
                                <option value="">所有链接</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">排序依据</label>
                            <select class="shiroki-widget-form-input" name="orderby">
                                <option value="name" ${instance.orderby === 'name' ? 'selected' : ''}>链接标题</option>
                                <option value="rating" ${instance.orderby === 'rating' ? 'selected' : ''}>链接评级</option>
                                <option value="id" ${instance.orderby === 'id' ? 'selected' : ''}>链接ID</option>
                                <option value="rand" ${instance.orderby === 'rand' ? 'selected' : ''}>随机</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="images" ${instance.images ? 'checked' : ''}>
                                <span>显示链接图片</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="name" ${instance.name !== false ? 'checked' : ''}>
                                <span>显示链接名</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="description" ${instance.description ? 'checked' : ''}>
                                <span>显示链接描述</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="rating" ${instance.rating ? 'checked' : ''}>
                                <span>显示链接评级</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示链接数</label>
                            <input type="number" class="shiroki-widget-form-input" name="limit" value="${instance.limit || -1}" min="-1" placeholder="-1 表示显示所有">
                        </div>
                    `;
                    break;

                case 'tag_cloud':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">分类法</label>
                            <select class="shiroki-widget-form-input" name="taxonomy">
                                <option value="post_tag" ${instance.taxonomy === 'post_tag' ? 'selected' : ''}>标签</option>
                                <option value="category" ${instance.taxonomy === 'category' ? 'selected' : ''}>分类</option>
                            </select>
                        </div>
                    `;
                    break;

                case 'nav_menu':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">选择菜单</label>
                            <select class="shiroki-widget-form-input" name="nav_menu">
                                <option value="">-- 选择菜单 --</option>
                            </select>
                        </div>
                    `;
                    break;

                case 'media_image':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">图片</label>
                            <div class="shiroki-widget-media-uploader" data-field="url">
                                <input type="hidden" name="attachment_id" value="${instance.attachment_id || ''}">
                                <input type="text" class="shiroki-widget-form-input" name="url" value="${instance.url || ''}" placeholder="图片URL">
                                <button type="button" class="shiroki-widget-media-btn" data-action="select">📷 选择图片</button>
                                <button type="button" class="shiroki-widget-media-btn shiroki-widget-media-remove" data-action="remove" style="${instance.url ? '' : 'display:none'}">🗑️ 移除</button>
                            </div>
                            <div class="shiroki-widget-media-preview" style="${instance.url ? '' : 'display:none'}">
                                <img src="${instance.url || ''}" alt="预览">
                            </div>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">替代文本</label>
                            <input type="text" class="shiroki-widget-form-input" name="alt" value="${instance.alt || ''}" placeholder="图片描述">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">链接地址</label>
                            <input type="text" class="shiroki-widget-form-input" name="link_url" value="${instance.link_url || ''}" placeholder="https://...">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">图片尺寸</label>
                            <select class="shiroki-widget-form-input" name="size">
                                <option value="thumbnail" ${instance.size === 'thumbnail' ? 'selected' : ''}>缩略图</option>
                                <option value="medium" ${instance.size === 'medium' ? 'selected' : ''}>中等</option>
                                <option value="large" ${instance.size === 'large' ? 'selected' : ''}>大图</option>
                                <option value="full" ${instance.size === 'full' ? 'selected' : ''}>原图</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="caption" ${instance.caption ? 'checked' : ''}>
                                <span>显示标题</span>
                            </label>
                        </div>
                    `;
                    break;

                /* 🎨 Boxmoe 主题自定义小工具 */
                case 'widget_ads':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">广告代码</label>
                            <textarea class="shiroki-widget-form-textarea" name="code" rows="12" placeholder="输入广告HTML代码...">${this.escapeHtml(instance.code || '')}</textarea>
                            <p class="shiroki-widget-form-hint">💡 支持HTML代码，包括图片链接等</p>
                        </div>
                    `;
                    break;

                case 'widget_postlist':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">排序方式</label>
                            <select class="shiroki-widget-form-input" name="orderby">
                                <option value="comment_count" ${instance.orderby === 'comment_count' ? 'selected' : ''}>评论数</option>
                                <option value="date" ${instance.orderby === 'date' || !instance.orderby ? 'selected' : ''}>发布时间</option>
                                <option value="rand" ${instance.orderby === 'rand' ? 'selected' : ''}>随机</option>
                            </select>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">分类限制</label>
                            <input type="text" class="shiroki-widget-form-input" name="cat" value="${this.escapeHtml(instance.cat || '')}" placeholder="格式：1,2 或 -1,-2">
                            <p class="shiroki-widget-form-hint">💡 格式：1,2 表示限制ID为1,2分类；-1,-2 表示排除</p>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数目</label>
                            <input type="number" class="shiroki-widget-form-input" name="limit" value="${instance.limit || 6}" min="1">
                        </div>
                    `;
                    break;

                case 'widget_comments':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数目</label>
                            <input type="number" class="shiroki-widget-form-input" name="limit" value="${instance.limit || 8}" min="1">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">排除用户ID</label>
                            <input type="number" class="shiroki-widget-form-input" name="outer" value="${instance.outer || 1}" min="0">
                            <p class="shiroki-widget-form-hint">💡 排除指定用户的评论，-1表示不排除</p>
                        </div>
                    `;
                    break;

                case 'category_widget':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_count" ${instance.show_count ? 'checked' : ''}>
                                <span>显示文章数目</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'boxmoe_widget_archive':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_count" ${instance.show_count ? 'checked' : ''}>
                                <span>显示文章数量</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'widget_tags':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数量</label>
                            <input type="number" class="shiroki-widget-form-input" name="count" value="${instance.count || 24}" min="1">
                        </div>
                    `;
                    break;

                case 'widget_userinfo':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">昵称</label>
                            <input type="text" class="shiroki-widget-form-input" name="nickname" value="${this.escapeHtml(instance.nickname || '')}" placeholder="留空使用用户资料">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">个人简介</label>
                            <textarea class="shiroki-widget-form-textarea" name="bio" rows="3" placeholder="留空使用用户资料">${this.escapeHtml(instance.bio || '')}</textarea>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">用户ID</label>
                            <input type="number" class="shiroki-widget-form-input" name="avatarid" value="${instance.avatarid || ''}" placeholder="留空使用默认">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">头像链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="avatar_url" value="${this.escapeHtml(instance.avatar_url || '')}" placeholder="留空使用用户默认头像">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">QQ号</label>
                            <input type="text" class="shiroki-widget-form-input" name="qq" value="${this.escapeHtml(instance.qq || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">邮箱</label>
                            <input type="email" class="shiroki-widget-form-input" name="email" value="${this.escapeHtml(instance.email || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">GitHub链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="github" value="${this.escapeHtml(instance.github || '')}" placeholder="https://github.com/...">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">Gitee链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="gitee" value="${this.escapeHtml(instance.gitee || '')}" placeholder="https://gitee.com/...">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">微信号</label>
                            <input type="text" class="shiroki-widget-form-input" name="wechat" value="${this.escapeHtml(instance.wechat || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_posts_count" ${instance.show_posts_count !== false ? 'checked' : ''}>
                                <span>显示文章数量</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_comments_count" ${instance.show_comments_count !== false ? 'checked' : ''}>
                                <span>显示评论数量</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_users_count" ${instance.show_users_count !== false ? 'checked' : ''}>
                                <span>显示用户数量</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'widget_currentuser':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">用户ID</label>
                            <input type="number" class="shiroki-widget-form-input" name="avatarid" value="${instance.avatarid || ''}" placeholder="留空则使用当前登录用户">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">头像链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="avatar_url" value="${this.escapeHtml(instance.avatar_url || '')}" placeholder="留空则使用用户默认头像">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">昵称</label>
                            <input type="text" class="shiroki-widget-form-input" name="nickname" value="${this.escapeHtml(instance.nickname || '')}" placeholder="留空则使用用户资料中的昵称">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">个人简介</label>
                            <textarea class="shiroki-widget-form-textarea" name="bio" rows="3" placeholder="留空则使用用户资料中的个人简介">${this.escapeHtml(instance.bio || '')}</textarea>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">QQ号</label>
                            <input type="text" class="shiroki-widget-form-input" name="qq" value="${this.escapeHtml(instance.qq || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">邮箱</label>
                            <input type="email" class="shiroki-widget-form-input" name="email" value="${this.escapeHtml(instance.email || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">GitHub链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="github" value="${this.escapeHtml(instance.github || '')}" placeholder="https://github.com/...">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">Gitee链接</label>
                            <input type="url" class="shiroki-widget-form-input" name="gitee" value="${this.escapeHtml(instance.gitee || '')}" placeholder="https://gitee.com/...">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">微信号</label>
                            <input type="text" class="shiroki-widget-form-input" name="wechat" value="${this.escapeHtml(instance.wechat || '')}" placeholder="点击可复制">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_posts_count" ${instance.show_posts_count !== false ? 'checked' : ''}>
                                <span>显示文章数量</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_comments_count" ${instance.show_comments_count !== false ? 'checked' : ''}>
                                <span>显示评论数量</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_users_count" ${instance.show_users_count !== false ? 'checked' : ''}>
                                <span>显示用户数量</span>
                            </label>
                        </div>
                    `;
                    break;

                case 'boxmoe_widget_search':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 搜索小工具显示站点搜索表单，样式跟随主题设置</p>
                        </div>
                    `;
                    break;

                case 'widget_random_posts':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">分类限制</label>
                            <input type="text" class="shiroki-widget-form-input" name="cat" value="${this.escapeHtml(instance.cat || '')}" placeholder="格式：1,2 或 -1,-2">
                            <p class="shiroki-widget-form-hint">💡 格式：1,2 表示限制ID为1,2分类；-1,-2 表示排除</p>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">显示数目</label>
                            <input type="number" class="shiroki-widget-form-input" name="limit" value="${instance.limit || 6}" min="1">
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_thumb" ${instance.show_thumb !== false ? 'checked' : ''}>
                                <span>显示缩略图</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_date" ${instance.show_date !== false ? 'checked' : ''}>
                                <span>显示发布日期</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                <input type="checkbox" name="show_excerpt" ${instance.show_excerpt ? 'checked' : ''}>
                                <span>显示文章摘要</span>
                            </label>
                        </div>
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">摘要长度</label>
                            <input type="number" class="shiroki-widget-form-input" name="excerpt_length" value="${instance.excerpt_length || 20}" min="1">
                        </div>
                    `;
                    break;

                case 'widget_postauthor':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <p class="shiroki-widget-form-hint">💡 显示当前文章作者的详细信息，仅在文章页面显示</p>
                        </div>
                    `;
                    break;

                case 'widget_clock':
                    html += `
                        <div class="shiroki-widget-form-group">
                            <label class="shiroki-widget-form-label">时区</label>
                            <select class="shiroki-widget-form-input" name="timezone">
                                <option value="Asia/Shanghai" ${instance.timezone === 'Asia/Shanghai' || !instance.timezone ? 'selected' : ''}>北京时间</option>
                                <option value="Asia/Tokyo" ${instance.timezone === 'Asia/Tokyo' ? 'selected' : ''}>东京时间</option>
                                <option value="Asia/Seoul" ${instance.timezone === 'Asia/Seoul' ? 'selected' : ''}>首尔时间</option>
                                <option value="Asia/Hong_Kong" ${instance.timezone === 'Asia/Hong_Kong' ? 'selected' : ''}>香港时间</option>
                                <option value="Europe/London" ${instance.timezone === 'Europe/London' ? 'selected' : ''}>伦敦时间</option>
                                <option value="Europe/Paris" ${instance.timezone === 'Europe/Paris' ? 'selected' : ''}>巴黎时间</option>
                                <option value="America/New_York" ${instance.timezone === 'America/New_York' ? 'selected' : ''}>纽约时间</option>
                                <option value="America/Los_Angeles" ${instance.timezone === 'America/Los_Angeles' ? 'selected' : ''}>洛杉矶时间</option>
                                <option value="UTC" ${instance.timezone === 'UTC' ? 'selected' : ''}>UTC时间</option>
                            </select>
                            <p class="shiroki-widget-form-hint">💡 留空标题将显示时区名称</p>
                        </div>
                    `;
                    break;

                default:
                    /* 📝 通用字段 - 显示所有非内部字段 */
                    var hasFields = false;
                    for (var key in instance) {
                        if (key === 'title' || key[0] === '_' || key === 'filter') continue;
                        var value = instance[key];
                        if (typeof value === 'boolean') {
                            hasFields = true;
                            html += `
                                <div class="shiroki-widget-form-group">
                                    <label class="shiroki-widget-form-label shiroki-widget-checkbox-label">
                                        <input type="checkbox" name="${key}" ${value ? 'checked' : ''}>
                                        <span>${this.escapeHtml(key)}</span>
                                    </label>
                                </div>
                            `;
                        } else if (typeof value === 'string' || typeof value === 'number') {
                            hasFields = true;
                            html += `
                                <div class="shiroki-widget-form-group">
                                    <label class="shiroki-widget-form-label">${this.escapeHtml(key)}</label>
                                    <input type="text" class="shiroki-widget-form-input" name="${key}" value="${this.escapeHtml(value)}">
                                </div>
                            `;
                        }
                    }
                    /* 📝 如果没有其他字段，显示提示 */
                    if (!hasFields) {
                        html += `
                            <div class="shiroki-widget-form-group">
                                <p class="shiroki-widget-form-hint">💡 此小工具暂无其他可配置选项</p>
                            </div>
                        `;
                    }
            }

            return html;
        },

        /**
         * 💾 保存小工具设置
         */
        saveWidgetSettings: function() {
            if (!this.currentEditingWidget) {
                return;
            }

            var settings = {};
            $('#shiroki-widget-modal-body').find('input, textarea, select').each(function() {
                var name = $(this).attr('name');
                var type = $(this).attr('type');
                var tagName = $(this).prop('tagName').toLowerCase();

                if (!name) return;

                if (type === 'checkbox') {
                    settings[name] = $(this).is(':checked');
                } else if (tagName === 'select') {
                    settings[name] = $(this).val();
                } else {
                    settings[name] = $(this).val();
                }
            });

            /* ⏳ 禁用按钮 */
            $('#shiroki-widget-modal-confirm').prop('disabled', true).text('保存中...');

            $.ajax({
                url: shirokiWidgetConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_update_widget_settings',
                    nonce: shirokiWidgetConfig.nonce,
                    widget_id: this.currentEditingWidget.widget_id,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        /* 🔄 更新本地数据 */
                        this.currentEditingWidget.title = settings.title;
                        if (!this.currentEditingWidget.instance) {
                            this.currentEditingWidget.instance = {};
                        }
                        this.currentEditingWidget.instance = $.extend(this.currentEditingWidget.instance, settings);

                        /* 🔄 更新UI */
                        var $item = $(`.shiroki-widget-active-item[data-widget-id="${this.currentEditingWidget.widget_id}"]`);
                        var displayTitle = settings.title || this.currentEditingWidget.name || '未命名';
                        var subTitle = settings.title ? this.currentEditingWidget.name : '';

                        $item.find('.shiroki-widget-active-name').text(displayTitle);
                        if (subTitle) {
                            if ($item.find('.shiroki-widget-active-title').length === 0) {
                                $item.find('.shiroki-widget-active-info').append(`<div class="shiroki-widget-active-title">${this.escapeHtml(subTitle)}</div>`);
                            } else {
                                $item.find('.shiroki-widget-active-title').text(subTitle);
                            }
                        } else {
                            $item.find('.shiroki-widget-active-title').remove();
                        }

                        this.closeWidgetModal();
                        /* ✅ 小工具设置已保存，侧边栏顺序未变，不需要保存 */
                    } else {
                        alert(response.data.message || '保存失败');
                    }
                }.bind(this),
                error: function() {
                    alert('网络错误，请稍后重试');
                },
                complete: function() {
                    $('#shiroki-widget-modal-confirm').prop('disabled', false).text('保存设置');
                }
            });
        },

        /**
         * 🗑️ 从Modal删除小工具
         */
        deleteWidgetFromModal: function() {
            if (!this.currentEditingWidget) return;

            var widgetId = this.currentEditingWidget.widget_id;
            var $item = $(`.shiroki-widget-active-item[data-widget-id="${widgetId}"]`);

            this.deactivateWidget(widgetId, $item);
            this.closeWidgetModal();
        },

        /**
         * ❌ 关闭Modal
         */
        closeWidgetModal: function() {
            $('#shiroki-widget-modal').fadeOut(200);
            this.currentEditingWidget = null;
        },

        /**
         * ⚠️ 显示确认对话框
         * @param {string} message - 确认消息
         * @param {function} callback - 确认后的回调函数
         * @param {string} title - 对话框标题（可选）
         */
        showConfirm: function(message, callback, title) {
            this.confirmCallback = callback;
            title = title || '⚠️ 确认操作';
            $('#shiroki-confirm-modal .shiroki-widget-modal-title').text(title);
            $('#shiroki-confirm-modal .shiroki-confirm-message').html(message);
            $('#shiroki-confirm-modal').fadeIn(200);
        },

        /**
         * ❌ 关闭确认对话框
         */
        closeConfirmModal: function() {
            $('#shiroki-confirm-modal').fadeOut(200);
            this.confirmCallback = null;
        },

        /**
         * 📷 打开媒体上传器
         * @param {jQuery} $uploader - 上传器容器
         * @param {string} mediaType - 媒体类型：image, audio, video
         * @param {boolean} multiple - 是否多选
         */
        openMediaUploader: function($uploader, mediaType, multiple) {
            var self = this;
            mediaType = mediaType || 'image';
            multiple = multiple || false;

            /* 🔄 使用自定义媒体弹窗 */
            if (typeof window.ShirokiMediaModal !== 'undefined') {
                window.ShirokiMediaModal.open({
                    mode: 'callback',
                    callback: function(media) {
                        /* 📝 更新表单字段 */
                        $uploader.find('input[name="attachment_id"]').val(media.id);
                        
                        /* 📝 获取目标字段名 */
                        var fieldName = $uploader.data('field') || 'url';
                        var $textInput = $uploader.find('input[type="text"]');
                        
                        if (multiple && fieldName === 'ids') {
                            /* 📝 多选模式：追加ID */
                            var currentIds = $textInput.val();
                            var newIds = currentIds ? currentIds + ',' + media.id : media.id;
                            $textInput.val(newIds);
                        } else {
                            /* 📝 单选模式：替换URL */
                            $textInput.val(media.url);
                            
                            /* 👁️ 显示预览（仅图片） */
                            if (mediaType === 'image') {
                                var $preview = $uploader.next('.shiroki-widget-media-preview');
                                if ($preview.length) {
                                    $preview.find('img').attr('src', media.url);
                                    $preview.show();
                                }
                            }
                            
                            /* 🗑️ 显示移除按钮 */
                            $uploader.find('.shiroki-widget-media-remove').show();
                        }
                    }
                });
            } else {
                /* 🔄 降级：使用原生WordPress媒体库 */
                if (this.mediaFrame) {
                    this.mediaFrame.dispose();
                }

                var libraryType = mediaType === 'image' ? 'image' : (mediaType === 'audio' ? 'audio' : 'video');
                var titleMap = {
                    'image': '选择图片',
                    'audio': '选择音频',
                    'video': '选择视频'
                };

                this.mediaFrame = wp.media({
                    title: titleMap[mediaType] || '选择媒体',
                    button: {
                        text: '使用此文件'
                    },
                    multiple: multiple,
                    library: {
                        type: libraryType
                    }
                });

                this.mediaFrame.on('select', function() {
                    var selection = this.mediaFrame.state().get('selection');
                    var fieldName = $uploader.data('field') || 'url';
                    var $textInput = $uploader.find('input[type="text"]');
                    
                    if (multiple && fieldName === 'ids') {
                        /* 📝 多选模式 */
                        var ids = [];
                        selection.each(function(attachment) {
                            ids.push(attachment.id);
                        });
                        var currentIds = $textInput.val();
                        var newIds = currentIds ? currentIds + ',' + ids.join(',') : ids.join(',');
                        $textInput.val(newIds);
                    } else {
                        /* 📝 单选模式 */
                        var attachment = selection.first().toJSON();
                        $uploader.find('input[name="attachment_id"]').val(attachment.id);
                        $textInput.val(attachment.url);

                        /* 👁️ 显示预览（仅图片） */
                        if (mediaType === 'image') {
                            var $preview = $uploader.next('.shiroki-widget-media-preview');
                            if ($preview.length) {
                                $preview.find('img').attr('src', attachment.url);
                                $preview.show();
                            }
                        }

                        $uploader.find('.shiroki-widget-media-remove').show();
                    }
                }.bind(this));

                this.mediaFrame.open();
            }
        },

        /**
         * 💾 保存所有更改
         * @param {boolean} silent - 是否静默保存（不显示提示）
         */
        saveAllChanges: function(silent) {
            var self = this;
            var sidebars = {};
            silent = silent || false;

            /* 📊 收集所有侧边栏的小工具顺序 */
            $('.shiroki-widget-sidebar-content').each(function() {
                var sidebarId = $(this).data('sidebar');
                var widgets = [];

                $(this).find('.shiroki-widget-active-item').each(function() {
                    widgets.push({
                        widget_id: $(this).data('widget-id')
                    });
                });

                sidebars[sidebarId] = widgets;
            });

            /* 📡 保存每个侧边栏 */
            var savePromises = [];
            for (var sidebarId in sidebars) {
                (function(sid, widgets) {
                    var promise = $.ajax({
                        url: shirokiWidgetConfig.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'shiroki_save_widget_order',
                            nonce: shirokiWidgetConfig.nonce,
                            sidebar_id: sid,
                            widgets: widgets
                        }
                    });
                    savePromises.push(promise);
                })(sidebarId, sidebars[sidebarId]);
            }

            /* 🔄 等待所有保存完成 */
            $.when.apply($, savePromises).then(function() {
                if (!silent) {
                    alert(shirokiWidgetConfig.strings.saveSuccess);
                }
                self.hasChanges = false;
            }, function() {
                if (!silent) {
                    alert(shirokiWidgetConfig.strings.saveError);
                }
            });
        },

        /**
         * 🔄 重置小工具
         */
        resetWidgets: function() {
            var self = this;
            var confirmMsg = '<p>确定要重置所有小工具配置吗？</p><p style="color: var(--admin-danger-text); margin-top: 8px;">⚠️ 这将清空所有侧边栏的小工具设置，此操作不可恢复！</p>';
            
            this.showConfirm(confirmMsg, function() {
                $.ajax({
                    url: shirokiWidgetConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_reset_widgets',
                        nonce: shirokiWidgetConfig.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            self.hasChanges = false;
                            self.loadWidgets();
                        } else {
                            alert(response.data.message || '重置失败');
                        }
                    },
                    error: function() {
                        alert('网络错误，请稍后重试');
                    }
                });
            }, '🔄 重置所有小工具');
        },

        /**
         * 📝 标记有更改
         */
        markAsChanged: function() {
            this.hasChanges = true;
            /* 📝 保存按钮已移除，此函数保留用于兼容性 */
        },

        /**
         * ⏳ 显示加载状态
         */
        showLoading: function() {
            $('#shiroki-widget-loading').show();
        },

        /**
         * ⏳ 隐藏加载状态
         */
        hideLoading: function() {
            $('#shiroki-widget-loading').hide();
        },

        /**
         * ❌ 显示错误
         */
        showError: function(message) {
            $('#shiroki-widget-library').html(`<div class="shiroki-widget-empty"><div class="shiroki-widget-empty-icon">❌</div><div class="shiroki-widget-empty-text">${message}</div></div>`);
        },

        /**
         * 🛡️ 转义HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * 🚀 初始化
     */
    $(document).on('shiroki-widget-manager-ready', function() {
        WidgetManager.init();
    });

})(jQuery);
