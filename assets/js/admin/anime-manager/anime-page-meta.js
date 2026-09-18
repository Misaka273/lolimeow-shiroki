/**
 * 追番页面 Meta Box 脚本
 */
(function($) {
    'use strict';

    var config = window.shirokiAnimePageMeta || {};
    var categoryNames = config.categoryNames || [];
    var templateSlug = config.templateSlug || 'page/page-anime.php';

    function filterCategoryNames(keyword) {
        keyword = $.trim(String(keyword || '')).toLowerCase();
        if (!keyword) {
            return categoryNames.slice(0, 8);
        }

        return categoryNames.filter(function(name) {
            return String(name).toLowerCase().indexOf(keyword) !== -1;
        }).slice(0, 8);
    }

    function renderSuggestions($input, $list) {
        var matches = filterCategoryNames($input.val());
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
    }

    function bindAutocomplete($input, $list) {
        $input.on('input focus', function() {
            renderSuggestions($input, $list);
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
    }

    function toggleMetaBox() {
        var $template = $('#page_template');
        var isAnimeTemplate = $template.length && $template.val() === templateSlug;

        $('#shiroki-anime-page-meta').toggle(isAnimeTemplate);
        $('#shiroki-anime-page-meta-hint').toggle(!isAnimeTemplate);
    }

    $(document).ready(function() {
        var $input = $('#shiroki-anime-page-category-name');
        var $list = $('#shiroki-anime-page-category-suggestions');

        if ($input.length && $list.length) {
            bindAutocomplete($input, $list);
        }

        toggleMetaBox();
        $(document).on('change', '#page_template', toggleMetaBox);
    });
})(jQuery);
