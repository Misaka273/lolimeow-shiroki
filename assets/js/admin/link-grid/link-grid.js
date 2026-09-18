/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🔗 链接后台网格交互
 */

(function($) {
    'use strict';

    var config = window.shirokiLinkGridConfig || {};
    var app = {
        links: [],
        categories: [],
        search: '',
        category: 0,
        categorySort: 'name',

        init: function() {
            $('body').addClass('shiroki-link-grid-active');
            if (config.screen === 'categories') {
                this.renderCategoriesPage();
                this.loadCategories();
                return;
            }
            if (config.screen === 'add' || config.screen === 'edit') {
                this.renderForm();
                return;
            }
            this.renderListPage();
            this.loadLinks();
        },

        request: function(action, data, done) {
            data = data || {};
            data.action = action;
            data.nonce = config.nonce;
            $.post(config.ajaxUrl, data, done).fail(function() {
                app.toast('网络错误，请稍后重试', 'error');
            });
        },

        shell: function(title, content) {
            var addButton = config.screen === 'list' ? '<a class="shiroki-link-button primary" href="' + config.adminUrl + 'link-add.php">➕ 添加链接</a>' : '';
            $('#shiroki-link-grid-root').html(
                '<div class="shiroki-link-page">' +
                '<div class="shiroki-link-page-header"><div><span class="shiroki-link-page-kicker">🔗 LINK MANAGER</span><h2>' + title + '</h2></div>' +
                addButton + '</div>' +
                content + '<div class="shiroki-link-toast" id="shiroki-link-toast"></div></div>'
            );
        },

        renderListPage: function() {
            this.shell('全部链接',
                '<div class="shiroki-link-toolbar"><div class="shiroki-link-filters" id="shiroki-link-filters"></div>' +
                '<div class="shiroki-link-bulkbar" id="shiroki-link-bulkbar"><span>已选择 <b id="shiroki-link-selected-count">0</b> 个</span><button class="shiroki-link-button danger" id="shiroki-link-bulk-delete">🗑️ 批量删除</button><button class="shiroki-link-button" id="shiroki-link-bulk-cancel">取消选择</button></div>' +
                '<div class="shiroki-link-search"><input id="shiroki-link-search" class="shiroki-link-input search" type="search" placeholder="搜索链接名称、URL或描述"></div></div>' +
                '<div id="shiroki-link-loading" class="shiroki-link-state">⏳ 加载中...</div><div id="shiroki-link-grid" class="shiroki-link-grid"></div>' +
                '<div id="shiroki-link-empty" class="shiroki-link-state" style="display:none">📭 暂无链接</div>'
            );
            $(document).on('input.linkGrid', '#shiroki-link-search', function() {
                app.search = $(this).val();
                app.resizeSearchInput(this);
                app.renderLinks();
            });
            this.resizeSearchInput(document.getElementById('shiroki-link-search'));
            $(document).on('click.linkGrid', '.shiroki-link-filter', function() {
                app.category = $(this).data('category');
                $('.shiroki-link-filter').removeClass('active');
                $(this).addClass('active');
                app.renderLinks();
            });
            $(document).on('click.linkGrid', '.shiroki-link-delete', function() {
                if (!window.confirm('确定要删除这个链接吗？')) return;
                app.request('shiroki_delete_link', {link_id: $(this).data('id')}, function(response) {
                    app.toast(response.data && response.data.message || '操作失败', response.success ? 'success' : 'error');
                    if (response.success) app.loadLinks();
                });
            });
            $(document).on('click.linkGrid', '.shiroki-link-select-circle', function(e) {
                e.stopPropagation();
                var card = $(this).closest('.shiroki-link-card');
                card.toggleClass('selected');
                $(this).toggleClass('selected', card.hasClass('selected'));
                app.updateLinkSelection();
            });
            $(document).on('click.linkGrid', '#shiroki-link-bulk-cancel', function() {
                $('.shiroki-link-card').removeClass('selected');
                $('.shiroki-link-select-circle').removeClass('selected');
                app.updateLinkSelection();
            });
            $(document).on('click.linkGrid', '#shiroki-link-bulk-delete', function() {
                var ids = $('.shiroki-link-card.selected').map(function() { return $(this).data('id'); }).get();
                if (!ids.length || !window.confirm('确定要删除选中的链接吗？')) return;
                app.request('shiroki_bulk_delete_links', {link_ids: ids}, function(response) {
                    app.toast(response.data && response.data.message || '删除失败', response.success ? 'success' : 'error');
                    if (response.success) app.loadLinks();
                });
            });
        },

        loadLinks: function() {
            $('#shiroki-link-loading').show();
            this.request('shiroki_get_links', {search: '', category: 0}, function(response) {
                if (!response.success) return app.toast(response.data.message, 'error');
                app.links = response.data.links || [];
                app.categories = response.data.categories || [];
                $('#shiroki-link-bulkbar').hide();
                app.renderFilters();
                app.renderLinks();
                $('#shiroki-link-loading').hide();
            });
        },

        renderFilters: function() {
            var html = '<button class="shiroki-link-filter active" data-category="0">📁 全部</button>';
            $.each(this.categories, function(_, category) {
                html += '<button class="shiroki-link-filter" data-category="' + category.id + '">' +
                    app.escape(category.name) + ' <small>' + category.count + '</small></button>';
            });
            $('#shiroki-link-filters').html(html);
        },

        renderLinks: function() {
            var search = this.search.toLowerCase();
            var links = $.grep(this.links, function(link) {
                var inCategory = !app.category || $.inArray(parseInt(app.category, 10), link.categories) !== -1;
                var text = (link.name + ' ' + link.url + ' ' + link.description).toLowerCase();
                return inCategory && (!search || text.indexOf(search) !== -1);
            });
            if (!links.length) {
                $('#shiroki-link-grid').empty();
                $('#shiroki-link-empty').show();
                return;
            }
            $('#shiroki-link-empty').hide();
            $('#shiroki-link-grid').html($.map(links, this.card).join(''));
        },

        resizeSearchInput: function(input) {
            if (input) input.style.removeProperty('width');
        },

        card: function(link) {
            var image = link.image ? '<img src="' + app.escape(link.image) + '" alt="' + app.escape(link.name) + '">' : '<span>🌐</span>';
            var categoryLabels = link.category_paths || link.category_names;
            var cats = categoryLabels.length ? categoryLabels.join(' · ') : '未分类';
            return '<article class="shiroki-link-card" data-id="' + link.id + '">' +
                '<div class="shiroki-link-select-circle"><div class="shiroki-link-select-inner"></div></div>' +
                '<div class="shiroki-link-card-head"><div class="shiroki-link-icon">' + image + '</div><div><h3>' + app.escape(link.name) + '</h3><span>' + app.escape(link.url) + '</span></div></div>' +
                '<p class="shiroki-link-description">' + app.escape(link.description || '暂无描述') + '</p>' +
                '<div class="shiroki-link-card-meta"><span>🏷️ ' + app.escape(cats) + '</span><span class="shiroki-link-status ' + (link.visible === 'Y' ? 'visible' : 'hidden') + '">' + (link.visible === 'Y' ? '公开' : '隐藏') + '</span></div>' +
                '<div class="shiroki-link-card-actions"><a class="shiroki-link-button shiroki-link-btn-view" target="_blank" rel="noopener" href="' + app.escape(link.url) + '">🌐 访问</a><a class="shiroki-link-button shiroki-link-btn-edit" href="' + app.escape(link.edit_url) + '">✏️ 编辑</a><button class="shiroki-link-button shiroki-link-btn-delete shiroki-link-delete" data-id="' + link.id + '">🗑️ 删除</button></div>' +
                '</article>';
        },

        renderForm: function() {
            var title = config.screen === 'edit' ? '编辑链接' : '添加链接';
            this.shell(title, '<form id="shiroki-link-form" class="shiroki-link-form"><input type="hidden" name="link_id" value="' + this.editId() + '">' +
                '<div class="shiroki-link-form-grid"><label>链接名称<input name="name" required></label><label>链接 URL<input name="url" type="url" required></label><label class="wide">链接描述<textarea name="description" rows="5"></textarea></label><label>图标 URL<input name="image" type="url"></label><div class="shiroki-link-target-field"><span class="shiroki-link-form-label">打开方式</span><input type="hidden" name="target" value=""><div class="shiroki-link-target-switch selected-index-1" role="radiogroup"><span class="shiroki-link-target-indicator"></span><button type="button" class="shiroki-link-target-option selected" data-value="">当前窗口或标签</button><button type="button" class="shiroki-link-target-option" data-value="_blank">新窗口或标签</button><button type="button" class="shiroki-link-target-option" data-value="_top">不包含框架的当前窗口或标签</button></div></div><label class="wide">自定义 rel<input name="rel_custom" placeholder="可填写多个关系值，用空格分隔"></label><div class="shiroki-link-category-field"><span class="shiroki-link-form-label">链接分类</span><input id="shiroki-link-category-search" class="shiroki-link-category-search" type="search" placeholder="搜索并选择分类"><div id="shiroki-link-form-categories" class="shiroki-link-category-options"></div><small>输入关键词筛选分类，点击分类即可选中</small></div></div><aside class="shiroki-link-relation-card"><div class="shiroki-link-relation-title">🔗 联系关系</div><input type="hidden" name="rel" value=""><div class="shiroki-link-relation-group"><span>身份关系</span><div><button type="button" class="shiroki-link-rel-choice" data-group="identity" data-value="me">我的另一个站点</button></div></div><div class="shiroki-link-relation-group"><span>友情</span><div><button type="button" class="shiroki-link-rel-choice" data-group="friendship" data-value="contact">偶尔联系</button><button type="button" class="shiroki-link-rel-choice" data-group="friendship" data-value="acquaintance">熟人</button><button type="button" class="shiroki-link-rel-choice" data-group="friendship" data-value="friend">朋友</button><button type="button" class="shiroki-link-rel-choice" data-group="friendship" data-value="">无</button></div></div><div class="shiroki-link-relation-group"><span>线下接触</span><div><button type="button" class="shiroki-link-rel-choice" data-group="physical" data-value="met">已见过面</button><button type="button" class="shiroki-link-rel-choice" data-group="physical" data-value="">没见过面</button></div></div><div class="shiroki-link-relation-group"><span>职场关系</span><div><button type="button" class="shiroki-link-rel-choice" data-group="professional" data-value="co-worker">同事</button><button type="button" class="shiroki-link-rel-choice" data-group="professional" data-value="colleague">同行</button><button type="button" class="shiroki-link-rel-choice" data-group="professional" data-value="superior">领导/上级</button></div></div><div class="shiroki-link-relation-group"><span>地理关系</span><div><button type="button" class="shiroki-link-rel-choice" data-group="geographical" data-value="co-resident">同住</button><button type="button" class="shiroki-link-rel-choice" data-group="geographical" data-value="neighbor">邻居</button><button type="button" class="shiroki-link-rel-choice" data-group="geographical" data-value="same-city">同城</button><button type="button" class="shiroki-link-rel-choice" data-group="geographical" data-value="">无</button></div></div><div class="shiroki-link-relation-group"><span>家庭关系</span><div><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="child">子女</button><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="kin">亲戚</button><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="parent">父母</button><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="sibling">兄弟姐妹</button><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="spouse">配偶</button><button type="button" class="shiroki-link-rel-choice" data-group="family" data-value="">无</button></div></div><div class="shiroki-link-relation-group"><span>情感关系</span><div><button type="button" class="shiroki-link-rel-choice" data-group="romantic" data-value="muse">偶像</button><button type="button" class="shiroki-link-rel-choice" data-group="romantic" data-value="crush">暗恋</button><button type="button" class="shiroki-link-rel-choice" data-group="romantic" data-value="date">交往中</button><button type="button" class="shiroki-link-rel-choice" data-group="romantic" data-value="sweetheart">恋人</button></div></div></aside>' +
                '<details class="shiroki-link-advanced"><summary>⚙️ 高级设置</summary><div class="shiroki-link-form-grid"><label>RSS 地址<input name="rss" type="url"></label><label>评级<select name="rating"><option value="0">0 — 不评级</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></label><label class="wide">批注<textarea name="notes" rows="5"></textarea></label></div></details>' +
                '<div class="shiroki-link-form-footer"><label class="shiroki-link-visibility"><input type="checkbox" name="visible" value="Y" checked><span class="shiroki-link-switch"></span><span class="visibility-public">公开显示</span><strong>不公开链接</strong></label><button class="shiroki-link-button primary" type="submit">💾 保存链接</button></div></form>');
            var categoryInput = $('#shiroki-link-category-search');
            categoryInput.attr('placeholder', '搜索或输入新分类');
            categoryInput.wrap('<div class="shiroki-link-category-input-row"></div>');
            $('<button type="button" id="shiroki-link-add-category" class="shiroki-link-add-category">添加新分类</button>').insertAfter(categoryInput);
            $('#shiroki-link-form [name="rel_custom"]').closest('label').appendTo('.shiroki-link-relation-card');
            $('.shiroki-link-relation-group').each(function() {
                var choices = $(this).children('div');
                if (!choices.find('.shiroki-link-rel-choice[data-value=""]').length) {
                    var groupName = choices.find('.shiroki-link-rel-choice').first().data('group');
                    choices.append('<button type="button" class="shiroki-link-rel-choice" data-group="' + groupName + '" data-value="">无</button>');
                }
            });
            var ratingSelect = $('#shiroki-link-form [name="rating"]');
            ratingSelect.after('<div class="shiroki-link-rating-switch" role="radiogroup"><span class="shiroki-link-rating-indicator"></span>' +
                $.map([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], function(value) {
                    return '<button type="button" class="shiroki-link-rating-option" data-value="' + value + '">' + value + '</button>';
                }).join('') + '</div>');
            this.updateRatingOption(0);
            this.setRelationChoices([]);
            this.request('shiroki_get_links', {search: '', category: 0}, function(response) {
                if (!response.success) return;
                var id = app.editId();
                var link = $.grep(response.data.links, function(item) { return item.id === id; })[0];
                var selectedCategories = link ? link.categories.map(String) : [];
                app.renderFormCategoryOptions(response.data.categories, selectedCategories);
                if (link) {
                    $('#shiroki-link-form').find('[name=name]').val(link.name);
                    $('#shiroki-link-form').find('[name=url]').val(link.url);
                    $('#shiroki-link-form').find('[name=image]').val(link.image);
                    $('#shiroki-link-form').find('[name=target]').val(link.target);
                    app.updateTargetOption(link.target);
                    var relParts = (link.rel || '').split(/\s+/).filter(Boolean);
                    app.setRelationChoices(relParts);
                    var relationValues = $('.shiroki-link-rel-choice').map(function() { return $(this).data('value'); }).get();
                    var customRelations = relParts.filter(function(value) { return $.inArray(value, relationValues) === -1; });
                    $('#shiroki-link-form').find('[name=rel_custom]').val(customRelations.join(' '));
                    $('#shiroki-link-form').find('[name=rss]').val(link.rss);
                    app.updateRatingOption(link.rating || 0);
                    $('#shiroki-link-form').find('[name=notes]').val(link.notes);
                    $('#shiroki-link-form').find('[name=description]').val(link.description);
                    $('#shiroki-link-form').find('[name=visible]').prop('checked', link.visible === 'Y');
                }
            });
            $(document).on('input.linkGrid', '#shiroki-link-category-search', function() {
                var query = $(this).val().toLowerCase();
                $('.shiroki-link-category-option').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query) !== -1);
                });
            });
            $(document).on('click.linkGrid', '.shiroki-link-category-option', function() {
                $(this).toggleClass('selected');
            });
            $(document).on('keydown.linkGrid', '#shiroki-link-category-search', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    app.addFormCategory();
                }
            });
            $(document).on('click.linkGrid', '#shiroki-link-add-category', function() {
                app.addFormCategory();
            });
            $(document).on('click.linkGrid', '.shiroki-link-target-option', function() {
                app.updateTargetOption($(this).data('value'));
            });
            $(document).on('click.linkGrid', '.shiroki-link-rating-option', function() {
                app.updateRatingOption($(this).data('value'));
            });
            $(document).on('click.linkGrid', '.shiroki-link-rel-choice', function() {
                var choice = $(this);
                var group = choice.data('group');
                var value = choice.data('value');
                var groupChoices = $('.shiroki-link-rel-choice[data-group="' + group + '"]');
                if (!value) {
                    groupChoices.removeClass('selected');
                    choice.addClass('selected');
                } else {
                    groupChoices.filter('[data-value=""]').removeClass('selected');
                    choice.toggleClass('selected');
                }
                app.syncRelationValue();
            });
            $(document).on('submit.linkGrid', '#shiroki-link-form', function(e) {
                e.preventDefault();
                var data = {};
                $.each($(this).serializeArray(), function(_, item) {
                    data[item.name] = item.value;
                });
                data.categories = $('.shiroki-link-category-option.selected').map(function() { return $(this).data('id'); }).get();
                data.rel = [data.rel, data.rel_custom].filter(Boolean).join(' ');
                data.visible = $('#shiroki-link-form [name=visible]').is(':checked') ? 'Y' : 'N';
                app.request('shiroki_save_link', data, function(response) {
                    app.toast(response.data && response.data.message || '保存失败', response.success ? 'success' : 'error');
                    if (response.success) window.location.href = config.adminUrl + 'link-manager.php';
                });
            });
        },

        editId: function() {
            var params = new URLSearchParams(window.location.search);
            return parseInt(params.get('link_id') || '0', 10);
        },

        renderFormCategoryOptions: function(categories, selectedCategories) {
            selectedCategories = selectedCategories || [];
            $('#shiroki-link-form-categories').html($.map(categories, function(category) {
                var selected = $.inArray(String(category.id), selectedCategories) !== -1;
                return '<button type="button" class="shiroki-link-category-option ' + app.categoryColorClass(category.id) + (selected ? ' selected' : '') + '" data-id="' + category.id + '" title="' + app.escape(category.name) + '">' + app.escape(category.name) + '</button>';
            }).join(''));
        },

        addFormCategory: function() {
            var input = $('#shiroki-link-category-search');
            var name = $.trim(input.val());
            if (!name) return;
            app.request('shiroki_save_link_category', {name: name, description: ''}, function(response) {
                if (!response.success) {
                    app.toast(response.data && response.data.message || '分类添加失败', 'error');
                    return;
                }
                app.request('shiroki_get_link_categories', {}, function(categoryResponse) {
                    if (!categoryResponse.success) return;
                    app.renderFormCategoryOptions(categoryResponse.data.categories, []);
                    $('.shiroki-link-category-option').filter(function() {
                        return $(this).text() === name;
                    }).addClass('selected');
                    input.val('');
                    app.toast('分类已添加', 'success');
                });
            });
        },

        updateTargetOption: function(value) {
            var options = $('.shiroki-link-target-option');
            var index = options.index(options.filter(function() { return $(this).data('value') === value; }));
            if (index < 0) index = 0;
            options.removeClass('selected').eq(index).addClass('selected');
            $('.shiroki-link-target-switch').removeClass('selected-index-1 selected-index-2 selected-index-3').addClass('selected-index-' + (index + 1));
            $('#shiroki-link-form [name=target]').val(value);
        },

        updateRatingOption: function(value) {
            value = Math.max(0, Math.min(10, parseInt(value, 10) || 0));
            var options = $('.shiroki-link-rating-option');
            options.removeClass('selected').filter('[data-value="' + value + '"]').addClass('selected');
            $('.shiroki-link-rating-switch').css('--shiroki-rating-index', value);
            $('#shiroki-link-form [name=rating]').val(String(value));
        },

        updateRelOption: function(value) {
            var options = $('.shiroki-link-rel-option');
            var index = options.index(options.filter(function() { return $(this).data('value') === value; }));
            if (index < 0) index = 0;
            options.removeClass('selected').eq(index).addClass('selected');
            $('.shiroki-link-rel-switch').removeClass('selected-index-1 selected-index-2 selected-index-3 selected-index-4 selected-index-5').addClass('selected-index-' + (index + 1));
            $('#shiroki-link-form [name=rel]').val(value);
        },

        setRelationChoices: function(values) {
            var tokens = values || [];
            $('.shiroki-link-rel-choice').removeClass('selected');
            $('.shiroki-link-rel-choice').each(function() {
                var value = $(this).data('value');
                if (value && $.inArray(value, tokens) !== -1) {
                    $(this).addClass('selected');
                }
            });
            $('.shiroki-link-relation-group').each(function() {
                var group = $(this);
                if (!group.find('.shiroki-link-rel-choice.selected').length) {
                    group.find('.shiroki-link-rel-choice[data-value=""]').addClass('selected');
                }
            });
            this.syncRelationValue();
        },

        syncRelationValue: function() {
            var values = $('.shiroki-link-rel-choice.selected').map(function() {
                return $(this).data('value');
            }).get().filter(Boolean);
            $('#shiroki-link-form [name=rel]').val(values.join(' '));
        },

        renderCategoriesPage: function() {
            this.shell('链接分类', '<div class="shiroki-link-category-layout"><form id="shiroki-category-form" class="shiroki-link-form"><input type="hidden" name="term_id"><label>分类名称<input name="name" required></label><label>分类描述<textarea name="description" rows="4"></textarea></label><button class="shiroki-link-button primary" type="submit">➕ 保存分类</button></form><div><div class="shiroki-link-bulkbar" id="shiroki-category-bulkbar"><span class="shiroki-category-bulk-count">已选择 <b id="shiroki-category-selected-count">0</b> 个</span><button class="shiroki-link-button danger" id="shiroki-category-bulk-delete">🗑️ 批量删除</button></div><div id="shiroki-category-grid" class="shiroki-link-grid"></div></div></div>');
            $('<label>分类别名<input name="slug" placeholder="留空则自动生成"></label>').insertBefore('#shiroki-category-form button[type=submit]');
            $('<div id="shiroki-category-parent-picker" class="shiroki-category-parent-picker"><span>父级分类</span><input type="hidden" name="parent" value="0"><div id="shiroki-category-parent-options" class="shiroki-category-parent-options"></div></div>').insertBefore('#shiroki-category-form button[type=submit]');
            $('<div id="shiroki-category-bulk-actions" class="shiroki-category-bulk-actions shiroki-post-filter-wrapper"></div>').insertAfter('.shiroki-link-page-header');
            $('#shiroki-category-bulkbar').appendTo('#shiroki-category-bulk-actions');
            $('#shiroki-category-grid').before('<div class="shiroki-link-category-list-title"><span>📚</span><strong>所有链接分类</strong><small>共 <b id="shiroki-category-total">0</b> 个分类</small></div>');
            $('.shiroki-link-category-list-title').before('<div class="shiroki-category-toolbar" id="shiroki-category-filters"><label class="shiroki-category-search"><span>🔍</span><input id="shiroki-category-search" type="search" placeholder="搜索分类名称、描述或别名"></label><div class="shiroki-category-sort"><span class="shiroki-category-sort-label">📊 状态筛选：</span><button type="button" class="shiroki-link-filter active" data-sort="name">按名称</button><button type="button" class="shiroki-link-filter" data-sort="date">按添加日期</button><button type="button" class="shiroki-link-filter" data-sort="count">按链接总数</button></div></div>');
            $('#shiroki-category-bulkbar').append('<button type="button" class="shiroki-link-button primary" id="shiroki-category-bulk-edit">✏️ 批量修改</button><button type="button" class="shiroki-link-button" id="shiroki-category-clear-selection">取消选择</button>');
            $('.shiroki-category-sort').appendTo('#shiroki-category-bulk-actions');
            $('.shiroki-category-toolbar .shiroki-category-search').appendTo('.shiroki-link-page-header');
            $('.shiroki-category-toolbar').remove();
            $('#shiroki-link-grid-root').append('<div id="shiroki-category-bulk-modal" class="shiroki-category-bulk-modal" aria-hidden="true"><div class="shiroki-category-bulk-window" role="dialog" aria-modal="true"><div class="shiroki-category-bulk-window-head"><strong>批量修改分类</strong><button type="button" id="shiroki-category-bulk-close" class="shiroki-link-button">关闭</button></div><label class="shiroki-category-search shiroki-category-bulk-search"><span>🔍</span><input id="shiroki-category-bulk-search" type="search" placeholder="搜索分类名称"></label><input type="hidden" id="shiroki-category-bulk-parent" value="-1"><div id="shiroki-category-bulk-options" class="shiroki-category-bulk-options"></div><div class="shiroki-category-bulk-window-actions"><button type="button" id="shiroki-category-bulk-cancel" class="shiroki-link-button">取消</button><button type="button" id="shiroki-category-bulk-apply" class="shiroki-link-button primary">应用修改</button></div></div></div>');
            $(document).on('submit.linkGrid', '#shiroki-category-form', function(e) {
                e.preventDefault();
                var data = {};
                $.each($(this).serializeArray(), function(_, item) { data[item.name] = item.value; });
                app.request('shiroki_save_link_category', data, function(response) {
                    app.toast(response.data && response.data.message || '保存失败', response.success ? 'success' : 'error');
                    if (response.success) {
                        $('#shiroki-category-form')[0].reset();
                        $('#shiroki-category-form button[type=submit]').text('➕ 保存分类');
                        app.loadCategories();
                    }
                });
            });
            $(document).on('click.linkGrid', '.shiroki-category-edit', function(event) {
                var button = $(this);
                var categoryId = parseInt(button.attr('data-id'), 10);
                var category = $.grep(app.categories, function(item) { return item.id === categoryId; })[0];
                if (!category) return;
                event.preventDefault();
                event.stopPropagation();
                var formCard = $('#shiroki-category-form');
                formCard.removeClass('editing-flash');
                void formCard[0].offsetWidth;
                formCard.addClass('editing-flash');
                $('#shiroki-category-form [name=term_id]').val(category.id);
                $('#shiroki-category-form [name=name]').val(category.name);
                $('#shiroki-category-form [name=description]').val(category.description);
                $('#shiroki-category-form [name=slug]').val(category.slug);
                app.renderCategoryParents(category.id, category.parent || 0);
                $('#shiroki-category-form button[type=submit]').text('💾 更新分类');
            });
            $(document).on('click.linkGrid', '.shiroki-category-delete', function() {
                if (!window.confirm('确定要删除这个分类吗？')) return;
                app.request('shiroki_delete_link_category', {term_id: $(this).data('id')}, function(response) {
                    app.toast(response.data && response.data.message || '删除失败', response.success ? 'success' : 'error');
                    if (response.success) app.loadCategories();
                });
            });
            $(document).on('click.linkGrid', '.shiroki-category-select-circle', function(e) {
                e.stopPropagation();
                var card = $(this).closest('.shiroki-link-card');
                card.toggleClass('selected');
                $(this).toggleClass('selected', card.hasClass('selected'));
                app.updateCategorySelection();
            });
            $(document).on('click.linkGrid', '.shiroki-category-parent-option', function() {
                var option = $(this);
                $('.shiroki-category-parent-option').removeClass('selected');
                option.addClass('selected');
                $('#shiroki-category-form [name=parent]').val(String(option.data('id') || 0));
            });
            $(document).on('input.linkGrid', '#shiroki-category-search', function() {
                app.filterCategoryCards($(this).val());
            });
            $(document).on('click.linkGrid', '.shiroki-category-sort .shiroki-link-filter', function() {
                app.categorySort = $(this).data('sort');
                $('.shiroki-category-sort .shiroki-link-filter').removeClass('active');
                $(this).addClass('active');
                app.loadCategories();
            });
            $(document).on('click.linkGrid', '#shiroki-category-clear-selection', function() {
                $('.shiroki-category-card').removeClass('selected');
                $('.shiroki-category-select-circle').removeClass('selected');
                app.updateCategorySelection();
            });
            $(document).on('click.linkGrid', '#shiroki-category-bulk-edit', function() {
                if (!$('.shiroki-category-card.selected').length) return;
                app.renderBulkParentOptions();
                $('#shiroki-category-bulk-search').val('');
                $('#shiroki-category-bulk-parent').val('-1');
                $('#shiroki-category-bulk-modal').addClass('show').attr('aria-hidden', 'false');
            });
            $(document).on('input.linkGrid', '#shiroki-category-bulk-search', function() {
                var query = $.trim($(this).val()).toLowerCase();
                $('.shiroki-category-bulk-option').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query) !== -1);
                });
            });
            $(document).on('click.linkGrid', '.shiroki-category-bulk-option', function() {
                if ($(this).prop('disabled')) return;
                $('.shiroki-category-bulk-option').removeClass('selected');
                $(this).addClass('selected');
                $('#shiroki-category-bulk-parent').val(String($(this).data('id')));
            });
            $(document).on('click.linkGrid', '#shiroki-category-bulk-close, #shiroki-category-bulk-cancel', function() {
                $('#shiroki-category-bulk-modal').removeClass('show').attr('aria-hidden', 'true');
            });
            $(document).on('click.linkGrid', '#shiroki-category-bulk-modal', function(event) {
                if (event.target === this) {
                    $(this).removeClass('show').attr('aria-hidden', 'true');
                }
            });
            $(document).on('click.linkGrid', '#shiroki-category-bulk-apply', function() {
                var parent = parseInt($('#shiroki-category-bulk-parent').val(), 10);
                if (parent < 0) {
                    app.toast('请选择一个分类', 'error');
                    return;
                }
                var data = {
                    parent: parent,
                    term_ids: $('.shiroki-category-card.selected').map(function() { return $(this).data('id'); }).get()
                };
                app.request('shiroki_bulk_update_link_categories', data, function(response) {
                    app.toast(response.data && response.data.message || '批量修改失败', response.success ? 'success' : 'error');
                    if (response.success) {
                        $('#shiroki-category-bulk-modal').removeClass('show').attr('aria-hidden', 'true');
                        $('.shiroki-category-card').removeClass('selected');
                        $('.shiroki-category-select-circle').removeClass('selected');
                        app.loadCategories();
                    }
                });
            });
            $(document).on('click.linkGrid', '#shiroki-category-bulk-delete', function() {
                var ids = $('.shiroki-category-card.selected').map(function() { return $(this).data('id'); }).get();
                if (!ids.length || !window.confirm('确定要删除选中的分类吗？')) return;
                app.request('shiroki_bulk_delete_link_categories', {term_ids: ids}, function(response) {
                    app.toast(response.data && response.data.message || '删除失败', response.success ? 'success' : 'error');
                    if (response.success) app.loadCategories();
                });
            });
        },

        loadCategories: function() {
            this.request('shiroki_get_link_categories', {}, function(response) {
                if (!response.success) return;
                app.categories = response.data.categories || [];
                app.renderCategoryParents();
                $('#shiroki-category-total').text(app.categories.length);
                $('#shiroki-category-bulkbar').hide();
                var categories = app.categories.slice().sort(function(first, second) {
                    if (app.categorySort === 'count') return Number(second.count) - Number(first.count);
                    if (app.categorySort === 'date') return Number(second.id) - Number(first.id);
                    return String(first.name).localeCompare(String(second.name), 'zh-CN');
                });
                $('#shiroki-category-grid').html($.map(categories, function(category) {
                    return '<article class="shiroki-link-card category shiroki-category-card" data-id="' + category.id + '"><div class="shiroki-category-select-circle shiroki-link-select-circle"><div class="shiroki-link-select-inner"></div></div><h3>🏷️ ' + app.escape(category.name) + '</h3><div class="shiroki-category-details"><p><span>父级</span>' + app.escape(category.parent_name || '顶级分类') + '</p><p><span>描述</span>' + app.escape(category.description || '暂无描述') + '</p><p><span>别名</span><code>' + app.escape(category.slug || '') + '</code></p><p><span>链接数量</span><strong>' + category.count + '</strong></p></div><div class="shiroki-link-card-actions"><button class="shiroki-link-button shiroki-link-btn-edit shiroki-category-edit" data-id="' + category.id + '">✏️ 编辑</button><button class="shiroki-link-button shiroki-link-btn-delete shiroki-category-delete" data-id="' + category.id + '">🗑️ 删除</button></div></article>';
                }).join(''));
                app.filterCategoryCards($('#shiroki-category-search').val() || '');
            });
        },

        renderBulkParentOptions: function() {
            var selectedIds = $('.shiroki-category-card.selected').map(function() { return parseInt($(this).data('id'), 10); }).get();
            var options = '<button type="button" class="shiroki-category-bulk-option" data-id="0">顶级分类</button>';
            $.each(this.categories, function(_, category) {
                var disabled = $.inArray(category.id, selectedIds) !== -1 ? ' disabled' : '';
                options += '<button type="button" class="shiroki-category-bulk-option' + disabled + '" data-id="' + category.id + '">' + app.escape(category.name) + '</button>';
            });
            $('#shiroki-category-bulk-options').html(options);
        },

        renderCategoryParents: function(excludeId, selectedId) {
            selectedId = parseInt(selectedId, 10) || 0;
            var options = '<button type="button" class="shiroki-category-parent-option' + (selectedId === 0 ? ' selected' : '') + '" data-id="0">顶级分类</button>';
            $.each(this.categories, function(_, category) {
                if (category.id === excludeId) return;
                options += '<button type="button" class="shiroki-category-parent-option' + (selectedId === category.id ? ' selected' : '') + '" data-id="' + category.id + '"><strong>' + app.escape(category.name) + '</strong><small>' + app.escape(category.parent_name ? '子级 · ' + category.parent_name : '顶级分类') + '</small></button>';
            });
            $('#shiroki-category-parent-options').html(options);
            $('#shiroki-category-form [name=parent]').val(String(selectedId));
            $('#shiroki-category-parent-picker').addClass('loaded');
        },

        filterCategoryCards: function(query) {
            query = $.trim(String(query || '')).toLowerCase();
            $('.shiroki-category-card').each(function() {
                var card = $(this);
                card.toggle(!query || card.text().toLowerCase().indexOf(query) !== -1);
            });
        },

        updateLinkSelection: function() {
            var count = $('.shiroki-link-card.selected').length;
            $('#shiroki-link-selected-count').text(count);
            $('#shiroki-link-bulkbar').css('display', count > 0 ? 'flex' : 'none');
            $('#shiroki-link-filters').css('display', count === 0 ? 'flex' : 'none');
        },

        updateCategorySelection: function() {
            var count = $('.shiroki-category-card.selected').length;
            $('#shiroki-category-selected-count').text(count);
            $('#shiroki-category-bulkbar').css('display', count > 0 ? 'flex' : 'none');
            $('#shiroki-category-filters').css('display', 'flex');
            $('#shiroki-category-bulk-actions .shiroki-category-sort').css('display', count === 0 ? 'flex' : 'none');
        },

        toast: function(message, type) {
            var toast = $('#shiroki-link-toast');
            if (!toast.length) return;
            toast.text(message).removeClass('success error show').addClass((type || 'success') + ' show');
            setTimeout(function() { toast.removeClass('show'); }, 2800);
        },

        escape: function(value) {
            return $('<div>').text(value == null ? '' : value).html();
        },

        categoryColorClass: function(categoryId) {
            var index = ((parseInt(categoryId, 10) - 1) % 20) + 1;
            return 'category-color-' + (index > 0 ? index : 1);
        }
    };

    $(document).on('shiroki-link-grid-ready', function() { app.init(); });
})(jQuery);
