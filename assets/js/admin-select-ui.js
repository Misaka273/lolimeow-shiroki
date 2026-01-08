jQuery(document).ready(function($) {
    // 🎨 扁平圆角风格 Select 模拟器
    // 仅针对非多选、非隐藏、可见的 Select 元素进行美化
    // 排除特定插件可能冲突的区域
    
    function initBoxmoeSelect() {
        $('select:not([multiple]):not(.boxmoe-select-hidden)').each(function() {
            var $this = $(this);
            
            // 检查是否已经被当前插件美化过（已在 wrapper 中）
            if ($this.closest('.boxmoe-select-wrapper').length > 0) {
                return;
            }
            
            // 排除已经被其他插件美化过的 select (如 select2)
            if ($this.hasClass('select2-hidden-accessible') || $this.hasClass('chosen-select')) {
                return;
            }
            
            // 排除特定区域的 select 元素
            if (
                // 排除日期选择器中的 select 元素
                $this.hasClass('pt_month') || $this.hasClass('pt_year') || // 快速编辑中的日期选择器
                $this.closest('#timestampdiv').length > 0 || // 文章编辑页面中的日期选择器
                $this.attr('name') === 'mm' || $this.attr('name') === 'jj' || $this.attr('name') === 'aa' || // 日期相关的 name 属性
                // 排除快速编辑中的状态选择器
                $this.attr('name') === 'post_status' || 
                $this.closest('.inline-edit-row').length > 0 || // 所有快速编辑行中的选择器
                // 排除WPJAM插件的select元素，因为它们使用自己的JavaScript框架
                $this.closest('.wpjam-page').length > 0 || // 排除WPJAM插件页面中的所有select
                $this.closest('.wpjam-field').length > 0 || // 排除WPJAM字段
                $this.closest('.has-dependents').length > 0 || // 排除有依赖关系的字段
                $this.closest('[data-show_if]').length > 0 || // 排除有条件显示的字段
                $this.attr('name') === 'gravatar' || $this.attr('name') === 'google_fonts' || // 排除特定的WPJAM选项
                // 排除文章编辑页中添加分类区域的 select 元素
                $this.closest('.category-add').length > 0 || // 排除添加分类区域的select
                $this.closest('#category-adder').length > 0 // 排除链接分类添加区域的select
            ) {
                return;
            }

            // 获取当前选中的选项文本
            var selectedText = $this.find('option:selected').text();
            
            // 获取原生 Select 的宽度（在隐藏之前）
            var originWidth = $this.outerWidth();
            var originStyleWidth = $this[0].style.width;

            // 隐藏原生 Select
            $this.addClass('boxmoe-select-hidden');
            
            // 创建包裹容器
            var $wrapper = $('<div class="boxmoe-select-wrapper"></div>');
            
            // 设置宽度：优先使用计算宽度，确保与原生一致
            if (originStyleWidth) {
                 $wrapper.css('width', originStyleWidth);
            } else if (originWidth > 0) {
                 // 稍微增加一点缓冲，因为模拟框的 padding 可能不同
                 $wrapper.css('width', originWidth + 20 + 'px');
            } else {
                 $wrapper.css('min-width', '80px'); // 兜底最小宽度
            }

            $this.after($wrapper);
            $wrapper.append($this);
            
            // 创建显示框 (Trigger)
            var $trigger = $('<div class="boxmoe-select-trigger"></div>');
            $trigger.text(selectedText);
            $wrapper.append($trigger);
            
            // 创建下拉列表 (Dropdown)
            var $dropdown = $('<div class="boxmoe-select-dropdown"></div>');
            var $list = $('<ul></ul>');
            
            $this.find('option').each(function() {
                var $option = $(this);
                var $li = $('<li></li>');
                $li.text($option.text());
                $li.attr('data-value', $option.val());
                
                if ($option.is(':selected')) {
                    $li.addClass('selected');
                }
                
                $list.append($li);
            });
            
            $dropdown.append($list);
            $wrapper.append($dropdown);
            
            // 事件绑定
            
            // 点击 Trigger 切换下拉显示
            $trigger.on('click', function(e) {
                e.stopPropagation();
                
                // 关闭其他已打开的下拉
                $('.boxmoe-select-wrapper.open').not($wrapper).removeClass('open');
                
                $wrapper.toggleClass('open');
            });
            
            // 点击选项
            $list.on('click', 'li', function(e) {
                e.stopPropagation();
                var $li = $(this);
                var value = $li.attr('data-value');
                var text = $li.text();
                
                // 更新 Trigger 文本
                $trigger.text(text);
                
                // 更新下拉选中状态
                $list.find('li.selected').removeClass('selected');
                $li.addClass('selected');
                
                // 同步到原生 Select 并触发 change 事件
                $this.val(value).trigger('change');
                
                // 关闭下拉
                $wrapper.removeClass('open');
            });
            
            // 点击外部关闭
            $(document).on('click', function() {
                $wrapper.removeClass('open');
            });

            // 监听原生 Select 的 change 事件（如果是外部触发的）
            $this.on('change', function() {
                var newText = $(this).find('option:selected').text();
                $trigger.text(newText);
                var newVal = $(this).val();
                $list.find('li').removeClass('selected');
                $list.find('li[data-value="' + newVal + '"]').addClass('selected');
            });
        });
    }

    // 初始化
    initBoxmoeSelect();
    
    // 监听 Ajax 完成事件 (针对部分动态加载的 select)
    $(document).ajaxComplete(function() {
        setTimeout(initBoxmoeSelect, 500);
    });
    
    // 监听 WordPress 小部件事件 (针对动态添加/更新的小部件)
    $(document).on('widget-added widget-updated', function(e, widget) {
        // 延迟执行，确保小部件内容已完全加载
        setTimeout(function() {
            // 在当前小部件内重新初始化下拉框
            initBoxmoeSelect();
        }, 200);
    });
});