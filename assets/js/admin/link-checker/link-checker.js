/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🔗 友链反链检测 JavaScript
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Link_Checker
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🔗 友链检测管理器
     */
    var LinkChecker = {
        /* 📊 状态 */
        currentStatus: 'all',
        currentCategory: 'all',
        currentSearch: '',
        selectedLinks: [],
        isLoading: false,
        isCheckingAll: false,

        /**
         * 🚀 初始化
         */
        init: function() {
            this.syncInitialFilters();
            this.bindEvents();
            this.createToast();
            this.loadLinks();
        },

        /**
         * 🔄 同步页面初始筛选状态
         */
        syncInitialFilters: function() {
            var activeStatus = $('.shiroki-link-checker-status-options .active[data-status]').first();
            if (activeStatus.length) {
                this.currentStatus = String(activeStatus.data('status'));
            }

            var activeCategory = $('.shiroki-link-checker-category-options .active[data-category]').first();
            if (activeCategory.length) {
                this.currentCategory = String(activeCategory.data('category'));
            }
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 📊 状态筛选 */
            $(document).on('click', '.shiroki-link-checker-status-options .shiroki-link-checker-status-btn, .shiroki-link-checker-status-options > button[data-status]', function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                if (typeof status === 'undefined') {
                    return;
                }

                self.currentStatus = String(status);
                self.currentSearch = '';
                $('#shiroki-link-checker-search').val('');

                $('.shiroki-link-checker-status-options .shiroki-link-checker-status-btn, .shiroki-link-checker-status-options > button[data-status]').removeClass('active');
                $(this).addClass('active');

                self.loadLinks();
            });

            /* 🔍 搜索 */
            var searchTimeout;
            $(document).on('input', '#shiroki-link-checker-search', function() {
                clearTimeout(searchTimeout);
                var search = $(this).val();

                searchTimeout = setTimeout(function() {
                    self.currentSearch = search;
                    self.loadLinks();
                }, 300);
            });

            /* 🏷️ 分类筛选 */
            $(document).on('click', '.shiroki-link-checker-category-options .shiroki-link-checker-category-btn, .shiroki-link-checker-category-options > button[data-category]', function(e) {
                e.preventDefault();
                var category = $(this).data('category');
                if (typeof category === 'undefined') {
                    return;
                }

                self.currentCategory = String(category);
                self.currentSearch = '';
                $('#shiroki-link-checker-search').val('');

                $('.shiroki-link-checker-category-options .shiroki-link-checker-category-btn, .shiroki-link-checker-category-options > button[data-category]').removeClass('active');
                $(this).addClass('active');

                self.loadLinks();
            });

            /* ✅ 选择友链 */
            $(document).on('click', '.shiroki-link-select-circle', function(e) {
                e.stopPropagation();
                var card = $(this).closest('.shiroki-link-card');
                var linkId = card.data('id');

                self.toggleSelection(linkId, card);
            });

            /* 🎴 卡片点击（选择） */
            $(document).on('click', '.shiroki-link-card', function(e) {
                if ($(e.target).closest('a, button').length) {
                    return;
                }

                var card = $(this);
                var linkId = card.data('id');

                self.toggleSelection(linkId, card);
            });

            /* 📦 批量操作 */
            $(document).on('click', '.shiroki-link-checker-bulk-btn', function() {
                var action = $(this).data('action');
                self.handleBulkAction(action);
            });

            /* ⚡ 单个操作按钮 */
            $(document).on('click', '.shiroki-link-btn', function(e) {
                e.stopPropagation();
                var btn = $(this);
                var action = btn.data('action');
                var card = btn.closest('.shiroki-link-card');
                var linkId = card.data('id');

                self.handleSingleAction(action, linkId, card, btn);
            });

            /* 🔍 检测全部 */
            $(document).on('click', '#shiroki-link-checker-check-all', function() {
                self.checkAll();
            });
        },

        /**
         * 📡 加载友链列表
         */
        loadLinks: function() {
            var self = this;

            if (self.isLoading) return;
            self.isLoading = true;

            self.selectedLinks = [];
            self.updateBulkActions();

            $('#shiroki-link-checker-grid').hide().empty();
            $('#shiroki-link-checker-empty').hide();
            $('#shiroki-link-checker-loading').show();

            $.ajax({
                url: shirokiLinkCheckerConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_friend_links',
                    nonce: shirokiLinkCheckerConfig.nonce,
                    status: self.currentStatus,
                    category: self.currentCategory,
                    search: self.currentSearch
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.links.length === 0) {
                            $('#shiroki-link-checker-empty').show();
                        } else {
                            self.renderLinks(response.data.links);
                            $('#shiroki-link-checker-grid').show();
                        }
                    } else {
                        self.showToast(response.data.message || '加载失败', 'error');
                        $('#shiroki-link-checker-empty').show();
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                    $('#shiroki-link-checker-empty').show();
                },
                complete: function() {
                    self.isLoading = false;
                    $('#shiroki-link-checker-loading').hide();
                }
            });
        },

        /**
         * 🎨 渲染友链列表
         */
        renderLinks: function(links) {
            var self = this;
            var html = '';

            $.each(links, function(index, link) {
                html += self.createLinkCard(link);
            });

            $('#shiroki-link-checker-grid').html(html);
        },

        /**
         * 🃏 创建友链卡片
         */
        createLinkCard: function(link) {
            var statusText = '';
            var statusClass = link.status;

            switch (link.status) {
                case 'has':
                    statusText = shirokiLinkCheckerConfig.strings.statusHas;
                    break;
                case 'no':
                    statusText = shirokiLinkCheckerConfig.strings.statusNo;
                    break;
                case 'whitelisted':
                    statusText = shirokiLinkCheckerConfig.strings.statusWhitelisted;
                    break;
                default:
                    statusText = shirokiLinkCheckerConfig.strings.statusUnknown;
                    statusClass = 'unknown';
            }

            var logoHtml = '';
            if (link.image) {
                logoHtml = '<img src="' + this.escapeHtml(link.image) + '" alt="' + this.escapeHtml(link.name) + '">';
            } else {
                logoHtml = '🌐';
            }

            var descHtml = '';
            if (link.description) {
                descHtml = '<p>' + this.escapeHtml(link.description) + '</p>';
            }

            var whitelistBtn = '';
            if (link.is_whitelisted) {
                whitelistBtn = '<button class="shiroki-link-btn shiroki-link-btn-unwhitelist" data-action="unwhitelist"><span class="shiroki-link-btn-icon">❌</span><span class="shiroki-link-btn-text">' + shirokiLinkCheckerConfig.strings.removeWhitelist + '</span></button>';
            } else {
                whitelistBtn = '<button class="shiroki-link-btn shiroki-link-btn-whitelist" data-action="whitelist"><span class="shiroki-link-btn-icon">⚪</span><span class="shiroki-link-btn-text">' + shirokiLinkCheckerConfig.strings.addWhitelist + '</span></button>';
            }

            return `
                <div class="shiroki-link-card" data-id="${link.id}">
                    <div class="shiroki-link-select-circle">
                        <div class="shiroki-link-select-inner"></div>
                    </div>

                    <div class="shiroki-link-header">
                        <div class="shiroki-link-logo">
                            ${logoHtml}
                        </div>
                        <div class="shiroki-link-info">
                            <div class="shiroki-link-name">${this.escapeHtml(link.name)}</div>
                            <div class="shiroki-link-domain">${this.escapeHtml(link.domain)}</div>
                        </div>
                    </div>

                    <div class="shiroki-link-content">
                        ${descHtml}
                        <div class="shiroki-link-url">🔗 ${this.escapeHtml(this.truncateUrl(link.url, 35))}</div>
                    </div>

                    <div class="shiroki-link-meta">
                        <span class="shiroki-link-date">${shirokiLinkCheckerConfig.strings.lastCheck}: ${link.checked_at_text}</span>
                        <span class="shiroki-link-status-tag ${statusClass}">${statusText}</span>
                    </div>

                    <div class="shiroki-link-actions">
                        <button class="shiroki-link-btn shiroki-link-btn-check" data-action="check"><span class="shiroki-link-btn-icon">🔍</span><span class="shiroki-link-btn-text">${shirokiLinkCheckerConfig.strings.check}</span></button>
                        <a href="${link.url}" target="_blank" rel="noopener noreferrer" class="shiroki-link-btn shiroki-link-btn-visit"><span class="shiroki-link-btn-icon">🌐</span><span class="shiroki-link-btn-text">${shirokiLinkCheckerConfig.strings.visit}</span></a>
                        <button class="shiroki-link-btn shiroki-link-btn-copy" data-action="copy"><span class="shiroki-link-btn-icon">📋</span><span class="shiroki-link-btn-text">${shirokiLinkCheckerConfig.strings.copyLink}</span></button>
                        ${whitelistBtn}
                    </div>
                </div>
            `;
        },

        /**
         * ✅ 切换选择状态
         */
        toggleSelection: function(linkId, card) {
            var index = this.selectedLinks.indexOf(linkId);

            if (index === -1) {
                this.selectedLinks.push(linkId);
                card.addClass('selected');
            } else {
                this.selectedLinks.splice(index, 1);
                card.removeClass('selected');
            }

            this.updateBulkActions();
        },

        /**
         * 📦 更新批量操作栏
         */
        updateBulkActions: function() {
            var count = this.selectedLinks.length;

            $('.shiroki-link-checker-bulk-count-num').text(count);

            if (count > 0) {
                $('#shiroki-link-checker-bulk-actions').show();
            } else {
                $('#shiroki-link-checker-bulk-actions').hide();
            }
        },

        /**
         * ⚡ 处理单个操作
         */
        handleSingleAction: function(action, linkId, card, btn) {
            switch (action) {
                case 'check':
                    this.checkSingleLink(linkId, card);
                    break;
                case 'copy':
                    this.copyLink(card);
                    break;
                case 'whitelist':
                    this.toggleWhitelist(linkId, 'add', card);
                    break;
                case 'unwhitelist':
                    this.toggleWhitelist(linkId, 'remove', card);
                    break;
            }
        },

        /**
         * 📦 处理批量操作
         */
        handleBulkAction: function(action) {
            var self = this;

            if (action === 'cancel') {
                self.selectedLinks = [];
                $('.shiroki-link-card').removeClass('selected');
                self.updateBulkActions();
                return;
            }

            if (self.selectedLinks.length === 0) {
                self.showToast(shirokiLinkCheckerConfig.strings.emptySelection, 'error');
                return;
            }

            if (action === 'check') {
                self.checkSelectedLinks();
            } else if (action === 'whitelist') {
                $.each(self.selectedLinks, function(index, linkId) {
                    self.toggleWhitelist(linkId, 'add');
                });
            } else if (action === 'unwhitelist') {
                $.each(self.selectedLinks, function(index, linkId) {
                    self.toggleWhitelist(linkId, 'remove');
                });
            }
        },

        /**
         * 🔍 检测单个友链
         */
        checkSingleLink: function(linkId, card) {
            var self = this;
            var targetCard = card || $('.shiroki-link-card[data-id="' + linkId + '"]');

            targetCard.addClass('checking');
            targetCard.find('.shiroki-link-btn-check').prop('disabled', true);

            $.ajax({
                url: shirokiLinkCheckerConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_check_friend_link',
                    nonce: shirokiLinkCheckerConfig.nonce,
                    link_id: linkId
                },
                success: function(response) {
                    if (response.success) {
                        self.replaceCard(response.data.link);
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showToast(response.data.message || '检测失败', 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                },
                complete: function() {
                    targetCard.removeClass('checking');
                    targetCard.find('.shiroki-link-btn-check').prop('disabled', false);
                }
            });
        },

        /**
         * 🔍 批量检测选中的友链
         */
        checkSelectedLinks: function() {
            var self = this;

            $.each(self.selectedLinks, function(index, linkId) {
                self.checkSingleLink(linkId);
            });
        },

        /**
         * 🔍 检测全部友链
         */
        checkAll: function() {
            var self = this;

            if (self.isCheckingAll) return;

            if (!confirm(shirokiLinkCheckerConfig.strings.confirmCheckAll)) {
                return;
            }

            self.isCheckingAll = true;
            $('#shiroki-link-checker-check-all').prop('disabled', true);
            $('#shiroki-link-checker-grid .shiroki-link-card').addClass('checking');
            $('#shiroki-link-checker-grid .shiroki-link-btn').prop('disabled', true);

            $.ajax({
                url: shirokiLinkCheckerConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_check_all_friend_links',
                    nonce: shirokiLinkCheckerConfig.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        self.loadLinks();
                    } else {
                        self.showToast(response.data.message || '批量检测失败', 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                },
                complete: function() {
                    self.isCheckingAll = false;
                    $('#shiroki-link-checker-check-all').prop('disabled', false);
                    $('#shiroki-link-checker-grid .shiroki-link-card').removeClass('checking');
                    $('#shiroki-link-checker-grid .shiroki-link-btn').prop('disabled', false);
                }
            });
        },

        /**
         * ⚪ 切换白名单状态
         */
        toggleWhitelist: function(linkId, action, card) {
            var self = this;
            var targetCard = card || $('.shiroki-link-card[data-id="' + linkId + '"]');

            targetCard.addClass('checking');
            targetCard.find('.shiroki-link-btn').prop('disabled', true);

            $.ajax({
                url: shirokiLinkCheckerConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_toggle_friend_link_whitelist',
                    nonce: shirokiLinkCheckerConfig.nonce,
                    link_id: linkId,
                    whitelist_action: action
                },
                success: function(response) {
                    if (response.success) {
                        self.replaceCard(response.data.link);
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showToast(response.data.message || '操作失败', 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                },
                complete: function() {
                    targetCard.removeClass('checking');
                    targetCard.find('.shiroki-link-btn').prop('disabled', false);
                }
            });
        },

        /**
         * 🔄 替换单个卡片
         */
        replaceCard: function(link) {
            var card = $('.shiroki-link-card[data-id="' + link.id + '"]');
            var wasSelected = card.hasClass('selected');

            card.replaceWith(this.createLinkCard(link));

            if (wasSelected) {
                $('.shiroki-link-card[data-id="' + link.id + '"]').addClass('selected');
            }

            /* 🔄 如果当前筛选状态不匹配，可能需要移除卡片 */
            if (this.currentStatus !== 'all' && link.status !== this.currentStatus) {
                if (this.currentStatus !== 'whitelisted' || !link.is_whitelisted) {
                    $('.shiroki-link-card[data-id="' + link.id + '"]').remove();
                    var index = this.selectedLinks.indexOf(link.id);
                    if (index !== -1) {
                        this.selectedLinks.splice(index, 1);
                        this.updateBulkActions();
                    }

                    if ($('.shiroki-link-card').length === 0) {
                        $('#shiroki-link-checker-grid').hide();
                        $('#shiroki-link-checker-empty').show();
                    }
                }
            }
        },

        /**
         * 📋 复制链接
         */
        copyLink: function(card) {
            var url = card.find('.shiroki-link-domain').text();
            if (!url) {
                return;
            }

            var fullUrl = 'https://' + url;
            var self = this;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(fullUrl).then(function() {
                    card.addClass('copied');
                    setTimeout(function() {
                        card.removeClass('copied');
                    }, 300);
                    self.showToast('链接已复制', 'success');
                }).catch(function() {
                    self.fallbackCopy(fullUrl, card);
                });
            } else {
                self.fallbackCopy(fullUrl, card);
            }
        },

        /**
         * 📋 兼容复制
         */
        fallbackCopy: function(text, card) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                card.addClass('copied');
                setTimeout(function() {
                    card.removeClass('copied');
                }, 300);
                this.showToast('链接已复制', 'success');
            } catch (err) {
                this.showToast('复制失败，请手动复制', 'error');
            }

            document.body.removeChild(textarea);
        },

        /**
         * ✂️ 截断 URL
         */
        truncateUrl: function(url, maxLength) {
            if (url.length <= maxLength) {
                return url;
            }
            return url.substring(0, maxLength) + '...';
        },

        /**
         * 🛡️ HTML 转义
         */
        escapeHtml: function(text) {
            if (text === null || text === undefined) {
                return '';
            }
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * 🔔 创建 Toast 提示
         */
        createToast: function() {
            if ($('#shiroki-link-checker-toast').length === 0) {
                $('body').append('<div class="shiroki-comment-toast" id="shiroki-link-checker-toast"></div>');
            }
        },

        /**
         * 🔔 显示 Toast 提示
         */
        showToast: function(message, type) {
            var toast = $('#shiroki-link-checker-toast');
            toast.removeClass('success error').addClass(type).text(message).addClass('show');

            setTimeout(function() {
                toast.removeClass('show');
            }, 3000);
        }
    };

    /**
     * 🚀 DOM 加载完成后初始化
     */
    $(document).ready(function() {
        if ($('#shiroki-link-checker-grid').length) {
            LinkChecker.init();
        }
    });

})(jQuery);
