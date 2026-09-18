/**
 * 📺 追番管理 JavaScript
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Anime_Manager
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 📺 追番管理器
     */
    var AnimeManager = {
        /* 📊 状态 */
        currentStatus: 'all',
        currentSearch: '',
        selectedAnime: [],
        isLoading: false,
        animeList: [],
        categoryNames: [],

        /**
         * 🚀 初始化
         */
        init: function() {
            if (typeof window.shirokiAnimeConfig === 'undefined') {
                this.resetLoadingState();
                $('#shiroki-anime-manager-empty').css('display', 'flex');
                return;
            }

            this.categoryNames = (window.shirokiAnimeConfig && window.shirokiAnimeConfig.categoryNames) ? window.shirokiAnimeConfig.categoryNames.slice() : [];

            this.bindEvents();
            this.bindCategoryAutocomplete();
            this.loadAnimeList();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 📊 状态筛选 */
            $(document).on('click', '.shiroki-anime-manager-status-btn', function() {
                var status = $(this).data('status');
                self.currentStatus = status;
                self.currentSearch = '';
                $('#shiroki-anime-manager-search').val('');

                $('.shiroki-anime-manager-status-btn').removeClass('active');
                $(this).addClass('active');

                self.loadAnimeList();
            });

            /* 🔍 搜索 */
            var searchTimeout;
            $(document).on('input', '#shiroki-anime-manager-search', function() {
                clearTimeout(searchTimeout);
                var search = $(this).val();

                searchTimeout = setTimeout(function() {
                    self.currentSearch = search;
                    self.loadAnimeList();
                }, 300);
            });

            /* ✅ 选择番剧 */
            $(document).on('click', '.shiroki-anime-select-circle', function(e) {
                e.stopPropagation();
                var card = $(this).closest('.shiroki-anime-card');
                var animeId = card.data('id');
                self.toggleSelection(animeId, card);
            });

            /* 🎴 卡片点击（选择） */
            $(document).on('click', '.shiroki-anime-card', function(e) {
                if ($(e.target).closest('a, button').length) {
                    return;
                }

                var card = $(this);
                var animeId = card.data('id');
                self.toggleSelection(animeId, card);
            });

            /* 📦 批量操作 */
            $(document).on('click', '.shiroki-anime-manager-bulk-btn', function() {
                var action = $(this).data('action');
                self.handleBulkAction(action);
            });

            /* ➕ 添加番剧 */
            $(document).on('click', '#shiroki-anime-add-btn', function() {
                self.openModal();
            });

            /* ✏️ 编辑番剧 */
            $(document).on('click', '.shiroki-anime-card-btn-edit', function(e) {
                e.stopPropagation();
                var animeId = $(this).closest('.shiroki-anime-card').data('id');
                self.openModal(animeId);
            });

            /* 🗑️ 删除番剧 */
            $(document).on('click', '.shiroki-anime-card-btn-delete', function(e) {
                e.stopPropagation();
                var animeId = $(this).closest('.shiroki-anime-card').data('id');
                self.deleteAnime(animeId);
            });



            /* ♾️ 无限话复选框 */
            $(document).on('change', '#shiroki-anime-is-infinite', function() {
                self.toggleInfinite($(this).is(':checked'));
            });

            /* 🪟 弹窗关闭 */
            $(document).on('click', '#shiroki-anime-modal-close, #shiroki-anime-modal-cancel, .shiroki-anime-modal-overlay', function() {
                self.closeModal();
            });

            /* 💾 保存番剧 */
            $(document).on('click', '#shiroki-anime-modal-save', function() {
                self.saveAnime();
            });

            /* ⌨️ 弹窗表单回车提交 */
            $(document).on('keydown', '#shiroki-anime-form', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    self.saveAnime();
                }
            });
        },

        /**
         * ⏹️ 重置加载状态
         */
        resetLoadingState: function() {
            this.isLoading = false;
            $('#shiroki-anime-manager-loading').hide();
        },

        /**
         * 📡 加载番剧列表
         */
        loadAnimeList: function() {
            var self = this;

            if (self.isLoading) {
                return;
            }

            if (typeof window.shirokiAnimeConfig === 'undefined') {
                self.resetLoadingState();
                $('#shiroki-anime-manager-empty').css('display', 'flex');
                return;
            }

            self.isLoading = true;

            $('#shiroki-anime-manager-loading').css('display', 'flex');
            $('#shiroki-anime-manager-grid').empty().hide();
            $('#shiroki-anime-manager-empty').hide();

            $.ajax({
                url: shirokiAnimeConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_anime_list',
                    nonce: shirokiAnimeConfig.nonce,
                    status: self.currentStatus,
                    search: self.currentSearch
                },
                success: function(response) {
                    try {
                        if (response.success) {
                            self.animeList = response.data.anime || [];
                            self.categoryNames = response.data.categoryNames || self.categoryNames;
                            self.renderAnimeList(self.animeList);
                            self.updateCounts(response.data.counts || {});

                            if (self.animeList.length === 0) {
                                $('#shiroki-anime-manager-empty').css('display', 'flex');
                            } else {
                                $('#shiroki-anime-manager-grid').show();
                            }
                        } else {
                            self.showToast(response.data.message || shirokiAnimeConfig.strings.loadError, 'error');
                            $('#shiroki-anime-manager-empty').css('display', 'flex');
                        }
                    } catch (error) {
                        self.showToast(shirokiAnimeConfig.strings.loadError || '加载失败', 'error');
                        $('#shiroki-anime-manager-empty').css('display', 'flex');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                    $('#shiroki-anime-manager-empty').css('display', 'flex');
                },
                complete: function() {
                    self.resetLoadingState();
                }
            });
        },

        /**
         * 🎨 渲染番剧列表
         */
        renderAnimeList: function(animeList) {
            var self = this;
            var html = '';

            $.each(animeList, function(index, anime) {
                if (!anime || typeof anime !== 'object' || !anime.id || !anime.title) {
                    return;
                }
                html += self.createAnimeCard(anime);
            });

            $('#shiroki-anime-manager-grid').html(html);
        },

        /**
         * 🃏 创建番剧卡片
         */
        createAnimeCard: function(anime) {
            var self = this;

            if (!anime || typeof anime !== 'object') {
                return '';
            }

            var statusConfig = shirokiAnimeConfig.statusConfig[anime.status] || { label: '未知', color: 'gray' };
            var statusClass = 'status-' + anime.status;
            var progress = parseInt(anime.progress || 0, 10);
            var total = parseInt(anime.total || 0, 10);
            var isInfinite = !!anime.is_infinite;
            var progressPercent = isInfinite ? 50 : (total > 0 ? Math.min(100, Math.round((progress / total) * 100)) : 0);
            var progressText = isInfinite ? '进度 ' + progress + ' / ∞' : '进度 ' + progress + ' / ' + (total > 0 ? total : '?');
            var rating = parseFloat(anime.rating || 0);
            var tags = anime.tags || [];
            var protagonists = anime.protagonists || [];
            var voiceActors = anime.voice_actors || [];
            var cover = anime.cover || '';
            var watchLink = anime.watch_link || '';
            var officialLink = anime.official_link || '';
            var description = anime.description || '';
            var categoryName = anime.category_name || '';
            var isPrivate = !!anime.is_private;
            var startDate = anime.start_date || '';
            var endDate = anime.end_date || '';

            var dateHtml = '';
            if (startDate || endDate) {
                var dateText = '';
                if (startDate && endDate) {
                    dateText = self.escapeHtml(startDate) + ' ~ ' + self.escapeHtml(endDate);
                } else if (startDate) {
                    dateText = '上映 ' + self.escapeHtml(startDate);
                } else {
                    dateText = '完结 ' + self.escapeHtml(endDate);
                }
                dateHtml = '<div class="shiroki-anime-date-meta">📅 ' + dateText + '</div>';
            }

            var tagsHtml = self.buildTagGroupHtml(tags, 'shiroki-anime-tag');
            var protagonistsHtml = self.buildTagGroupHtml(protagonists, 'shiroki-anime-tag shiroki-anime-tag-protagonist');
            var voiceActorsHtml = self.buildTagGroupHtml(voiceActors, 'shiroki-anime-tag shiroki-anime-tag-voice');
            var metaTagsHtml = '';

            if (tagsHtml || protagonistsHtml || voiceActorsHtml) {
                metaTagsHtml = '<div class="shiroki-anime-meta-tags">';
                if (tagsHtml) {
                    metaTagsHtml += '<div class="shiroki-anime-tags">' + tagsHtml + '</div>';
                }
                if (protagonistsHtml) {
                    metaTagsHtml += '<div class="shiroki-anime-tags">' + protagonistsHtml + '</div>';
                }
                if (voiceActorsHtml) {
                    metaTagsHtml += '<div class="shiroki-anime-tags">' + voiceActorsHtml + '</div>';
                }
                metaTagsHtml += '</div>';
            }

            var coverHtml = '';
            if (cover) {
                coverHtml = '<div class="shiroki-anime-cover">' +
                    '<img src="' + this.escapeHtml(cover) + '" alt="' + this.escapeHtml(anime.title) + '">' +
                    '<span class="shiroki-anime-status-badge ' + statusClass + '">' + this.escapeHtml(statusConfig.label) + '</span>' +
                    (rating > 0 ? '<div class="shiroki-anime-rating"><span>⭐</span><span>' + rating.toFixed(1) + '</span></div>' : '') +
                    '</div>';
            } else {
                coverHtml = '<div class="shiroki-anime-cover" style="background: linear-gradient(135deg, var(--admin-primary-bg) 0%, var(--admin-purple-bg) 100%); display: flex; align-items: center; justify-content: center;">' +
                    '<span class="shiroki-anime-status-badge ' + statusClass + '" style="position: relative; top: auto; left: auto;">' + this.escapeHtml(statusConfig.label) + '</span>' +
                    '</div>';
            }

            var linkBtns = '';
            if (watchLink) {
                linkBtns += '<a href="' + this.escapeHtml(watchLink) + '" target="_blank" rel="noopener noreferrer" class="shiroki-anime-card-btn shiroki-anime-card-btn-link">' +
                    '<span>▶️</span><span>看番</span></a>';
            }
            if (officialLink) {
                linkBtns += '<a href="' + this.escapeHtml(officialLink) + '" target="_blank" rel="noopener noreferrer" class="shiroki-anime-card-btn shiroki-anime-card-btn-link">' +
                    '<span>🏠</span><span>官网</span></a>';
            }

            return '<div class="shiroki-anime-card' + (isPrivate ? ' is-private' : '') + '" data-id="' + this.escapeHtml(anime.id) + '">' +
                '<div class="shiroki-anime-select-circle">' +
                    '<div class="shiroki-anime-select-inner"></div>' +
                '</div>' +
                coverHtml +
                '<div class="shiroki-anime-info">' +
                    '<div class="shiroki-anime-card-badges">' +
                        (categoryName ? '<div class="shiroki-anime-category-name">' + this.escapeHtml(categoryName) + '</div>' : '') +
                        (isPrivate ? '<div class="shiroki-anime-private-badge">🔒 私密</div>' : '') +
                    '</div>' +
                    '<h3 class="shiroki-anime-card-title">' + this.escapeHtml(anime.title) + '</h3>' +
                    dateHtml +
                    metaTagsHtml +
                    (description ? '<p class="shiroki-anime-description">' + this.escapeHtml(description) + '</p>' : '') +
                    '<div class="shiroki-anime-progress' + (isInfinite ? ' is-infinite' : '') + '">' +
                        '<div class="shiroki-anime-progress-info">' +
                            '<span>' + progressText + '</span>' +
                            '<span>' + (isInfinite ? '∞' : progressPercent + '%') + '</span>' +
                        '</div>' +
                        '<div class="shiroki-anime-progress-bar">' +
                            '<div class="shiroki-anime-progress-fill" style="width: ' + progressPercent + '%;"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="shiroki-anime-card-actions">' +
                    linkBtns +
                    '<button type="button" class="shiroki-anime-card-btn shiroki-anime-card-btn-edit">' +
                        '<span>✏️</span><span>编辑</span>' +
                    '</button>' +
                    '<button type="button" class="shiroki-anime-card-btn shiroki-anime-card-btn-delete">' +
                        '<span>🗑️</span><span>删除</span>' +
                    '</button>' +
                '</div>' +
            '</div>';
        },

        /**
         * ✅ 切换选择状态
         */
        toggleSelection: function(animeId, card) {
            var index = this.selectedAnime.indexOf(animeId);

            if (index === -1) {
                this.selectedAnime.push(animeId);
                card.addClass('selected');
            } else {
                this.selectedAnime.splice(index, 1);
                card.removeClass('selected');
            }

            this.updateBulkActions();
        },

        /**
         * 📦 更新批量操作栏
         */
        updateBulkActions: function() {
            var $bulkActions = $('#shiroki-anime-manager-bulk-actions');
            var count = this.selectedAnime.length;

            if (count > 0) {
                $bulkActions.css('display', 'flex');
                $bulkActions.find('.shiroki-anime-manager-bulk-count-num').text(count);
            } else {
                $bulkActions.hide();
            }
        },

        /**
         * 📦 处理批量操作
         */
        handleBulkAction: function(action) {
            var self = this;

            if (action === 'cancel') {
                self.selectedAnime = [];
                $('.shiroki-anime-card').removeClass('selected');
                self.updateBulkActions();
                return;
            }

            if (action === 'delete') {
                if (self.selectedAnime.length === 0) {
                    self.showToast(shirokiAnimeConfig.strings.emptySelection, 'error');
                    return;
                }

                var confirmMessage = shirokiAnimeConfig.strings.bulkDeleteConfirm.replace('{count}', self.selectedAnime.length);
                if (!confirm(confirmMessage)) {
                    return;
                }

                self.bulkDeleteAnime(self.selectedAnime);
            }
        },

        /**
         * 🗑️ 批量删除
         */
        bulkDeleteAnime: function(ids) {
            var self = this;

            $.ajax({
                url: shirokiAnimeConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_bulk_delete_anime',
                    nonce: shirokiAnimeConfig.nonce,
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        self.selectedAnime = [];
                        $('.shiroki-anime-card').removeClass('selected');
                        self.updateBulkActions();
                        self.loadAnimeList();
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.deleteSuccess, 'success');
                    } else {
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.deleteError, 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                }
            });
        },

        /**
         * 🗑️ 删除单个番剧
         */
        deleteAnime: function(animeId) {
            var self = this;

            if (!confirm(shirokiAnimeConfig.strings.deleteConfirm)) {
                return;
            }

            $.ajax({
                url: shirokiAnimeConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_delete_anime',
                    nonce: shirokiAnimeConfig.nonce,
                    id: animeId
                },
                success: function(response) {
                    if (response.success) {
                        var index = self.selectedAnime.indexOf(animeId);
                        if (index !== -1) {
                            self.selectedAnime.splice(index, 1);
                        }
                        self.updateBulkActions();
                        self.loadAnimeList();
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.deleteSuccess, 'success');
                    } else {
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.deleteError, 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                }
            });
        },

        /**
         * 📝 打开弹窗
         */
        openModal: function(animeId) {
            var self = this;
            var isEdit = !!animeId;
            var anime = isEdit ? self.findAnimeById(animeId) : null;
            var isInfinite = anime ? !!anime.is_infinite : false;
            var isPrivate = anime ? !!anime.is_private : false;

            $('#shiroki-anime-modal-title').text(isEdit ? shirokiAnimeConfig.strings.editAnime : shirokiAnimeConfig.strings.addAnime);
            $('#shiroki-anime-id').val(isEdit ? animeId : '');
            $('#shiroki-anime-title').val(anime ? anime.title : '');
            $('#shiroki-anime-category-name').val(anime ? (anime.category_name || '') : '');
            $('#shiroki-anime-status').val(anime ? anime.status : 'planned');
            $('#shiroki-anime-cover').val(anime ? anime.cover : '');
            $('#shiroki-anime-watch-link').val(anime ? anime.watch_link : '');
            $('#shiroki-anime-official-link').val(anime ? anime.official_link : '');
            $('#shiroki-anime-start-date').val(anime ? (anime.start_date || '') : '');
            $('#shiroki-anime-end-date').val(anime ? (anime.end_date || '') : '');
            $('#shiroki-anime-progress').val(anime ? anime.progress : 0);
            $('#shiroki-anime-total').val(anime ? anime.total : 0);
            $('#shiroki-anime-is-infinite').prop('checked', isInfinite);
            $('#shiroki-anime-is-private').prop('checked', isPrivate);
            $('#shiroki-anime-rating').val(anime ? anime.rating : 0);
            $('#shiroki-anime-tags').val(anime && anime.tags ? self.formatTagsForInput(anime.tags) : '');
            $('#shiroki-anime-protagonists').val(anime && anime.protagonists ? self.formatTagsForInput(anime.protagonists) : '');
            $('#shiroki-anime-voice-actors').val(anime && anime.voice_actors ? self.formatTagsForInput(anime.voice_actors) : '');
            $('#shiroki-anime-description').val(anime ? anime.description : '');

            self.toggleInfinite(isInfinite);
            self.renderCategorySuggestions($('#shiroki-anime-category-name'), $('#shiroki-anime-category-name-suggestions'));
            $('#shiroki-anime-modal').show();
        },

        /**
         * ♾️ 切换无限话状态
         */
        toggleInfinite: function(isInfinite) {
            var $totalInput = $('#shiroki-anime-total');
            var $totalGroup = $totalInput.closest('.shiroki-anime-total-group');

            if (isInfinite) {
                $totalInput.prop('disabled', true).val(0);
                $totalGroup.addClass('is-disabled');
            } else {
                $totalInput.prop('disabled', false);
                $totalGroup.removeClass('is-disabled');
            }
        },

        /**
         * 📝 关闭弹窗
         */
        closeModal: function() {
            $('#shiroki-anime-modal').hide();
            $('#shiroki-anime-form')[0].reset();
            $('#shiroki-anime-id').val('');
            $('#shiroki-anime-is-private').prop('checked', false);
            $('#shiroki-anime-category-name-suggestions').hide().empty();
            this.toggleInfinite(false);
        },

        /**
         * 💾 保存番剧
         */
        saveAnime: function() {
            var self = this;

            var title = $.trim($('#shiroki-anime-title').val());
            if (!title) {
                self.showToast('番剧名称不能为空', 'error');
                $('#shiroki-anime-title').focus();
                return;
            }

            var data = {
                action: 'shiroki_save_anime',
                nonce: shirokiAnimeConfig.nonce,
                id: $('#shiroki-anime-id').val(),
                title: title,
                category_name: $.trim($('#shiroki-anime-category-name').val()),
                status: $('#shiroki-anime-status').val(),
                cover: $('#shiroki-anime-cover').val(),
                watch_link: $('#shiroki-anime-watch-link').val(),
                official_link: $('#shiroki-anime-official-link').val(),
                start_date: $('#shiroki-anime-start-date').val(),
                end_date: $('#shiroki-anime-end-date').val(),
                progress: $('#shiroki-anime-progress').val(),
                total: $('#shiroki-anime-total').val(),
                is_infinite: $('#shiroki-anime-is-infinite').is(':checked') ? '1' : '0',
                is_private: $('#shiroki-anime-is-private').is(':checked') ? '1' : '0',
                rating: $('#shiroki-anime-rating').val(),
                tags: $('#shiroki-anime-tags').val(),
                protagonists: $('#shiroki-anime-protagonists').val(),
                voice_actors: $('#shiroki-anime-voice-actors').val(),
                description: $('#shiroki-anime-description').val()
            };

            var $saveBtn = $('#shiroki-anime-modal-save');
            var originalText = $saveBtn.text();
            $saveBtn.prop('disabled', true).text('保存中...');

            $.ajax({
                url: shirokiAnimeConfig.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        self.closeModal();
                        self.loadAnimeList();
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.saveSuccess, 'success');
                    } else {
                        self.showToast(response.data.message || shirokiAnimeConfig.strings.saveError, 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * 🔍 根据 ID 查找番剧
         */
        findAnimeById: function(animeId) {
            var found = null;
            $.each(this.animeList, function(index, anime) {
                if (anime.id === animeId) {
                    found = anime;
                    return false;
                }
            });
            return found;
        },

        /**
         * 📊 更新状态计数
         */
        updateCounts: function(counts) {
            var self = this;
            $('.shiroki-anime-manager-status-btn').each(function() {
                var $btn = $(this);
                var status = $btn.data('status');
                var count = status === 'all' ? (counts.all || 0) : (counts[status] || 0);
                var label = status === 'all' ? '📁 全部' : (shirokiAnimeConfig.statusConfig[status] ? shirokiAnimeConfig.statusConfig[status].label : status);
                $btn.html(label + ' (' + count + ')');
            });
        },

        /**
         * 📁 绑定分类名称联想
         */
        bindCategoryAutocomplete: function() {
            var self = this;
            var $input = $('#shiroki-anime-category-name');
            var $list = $('#shiroki-anime-category-name-suggestions');

            if (!$input.length || !$list.length) {
                return;
            }

            $input.on('input focus', function() {
                self.renderCategorySuggestions($input, $list);
            });

            $list.on('click', '.shiroki-anime-autocomplete-item', function() {
                $input.val($(this).text());
                $list.hide();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.shiroki-anime-autocomplete-wrap').length) {
                    $list.hide();
                }
            });
        },

        /**
         * 📁 渲染分类名称联想列表
         */
        renderCategorySuggestions: function($input, $list) {
            var keyword = $.trim(String($input.val() || '')).toLowerCase();
            var matches = [];

            $.each(this.categoryNames, function(index, name) {
                var value = String(name || '');
                if (!keyword || value.toLowerCase().indexOf(keyword) !== -1) {
                    matches.push(value);
                }
            });

            matches = matches.slice(0, 8);
            $list.empty();

            if (!matches.length) {
                $list.hide();
                return;
            }

            $.each(matches, function(index, name) {
                $('<button>', {
                    type: 'button',
                    class: 'shiroki-anime-autocomplete-item',
                    text: name
                }).appendTo($list);
            });

            $list.show();
        },

        /**
         * 🏷️ 格式化标签用于输入框
         */
        formatTagsForInput: function(tags) {
            if (!tags) {
                return '';
            }

            if (typeof tags === 'string') {
                return tags;
            }

            if (!Array.isArray(tags)) {
                return '';
            }

            return tags.join('、');
        },

        /**
         * 🏷️ 构建标签 HTML
         */
        buildTagGroupHtml: function(tags, className) {
            var self = this;
            var html = '';
            var tagList = tags;

            if (!tagList) {
                return html;
            }

            if (!Array.isArray(tagList)) {
                if (typeof tagList === 'string') {
                    tagList = tagList.split(/[,，、]+/);
                } else {
                    return html;
                }
            }

            $.each(tagList, function(i, tag) {
                tag = $.trim(String(tag || ''));
                if (!tag) {
                    return;
                }
                html += '<span class="' + className + '">' + self.escapeHtml(tag) + '</span>';
            });

            return html;
        },

        /**
         * 🍞 显示提示
         */
        showToast: function(message, type) {
            var $toast = $('#shiroki-anime-toast');
            var icon = type === 'success' ? '✅' : '❌';

            $toast.removeClass('success error').addClass(type || 'success');
            $toast.find('.shiroki-anime-toast-icon').text(icon);
            $toast.find('.shiroki-anime-toast-message').text(message);
            $toast.css('display', 'flex');

            setTimeout(function() {
                $toast.fadeOut(300);
            }, 3000);
        },

        /**
         * 🛡️ HTML 转义
         */
        escapeHtml: function(text) {
            if (text === null || text === undefined) {
                return '';
            }
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(text)));
            return div.innerHTML;
        }
    };

    /**
     * 🚀 页面加载完成后初始化
     */
    $(document).ready(function() {
        if ($('.shiroki-anime-manager-wrap').length) {
            AnimeManager.init();
        }
    });

})(jQuery);
