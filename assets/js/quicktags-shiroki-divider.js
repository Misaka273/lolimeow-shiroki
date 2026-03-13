/* 🌊 shiroki分割线Quicktags按钮
 * 🕊️白木 开发 🔗gl.baimu.live
 */
(function() {
    /* 🎯 等待DOM加载完成 */
    document.addEventListener('DOMContentLoaded', function() {
        /* 🔍 查找Quicktags工具栏 */
        function initShirokiDividerButton() {
            var quicktagsContainer = document.getElementById('wp-content-wrap');
            if (!quicktagsContainer) return;
            
            /* 🔍 查找文本编辑器工具栏 */
            var toolbar = quicktagsContainer.querySelector('.quicktags-toolbar');
            if (!toolbar) return;
            
            /* 🎨 检查是否已经添加了分割线按钮 */
            if (!toolbar.querySelector('#qt_content_shiroki_divider')) {
                /* 🌊 创建分割线按钮 */
                var dividerButton = document.createElement('input');
                dividerButton.type = 'button';
                dividerButton.id = 'qt_content_shiroki_divider';
                dividerButton.className = 'ed_button button';
                dividerButton.value = '分割线';
                dividerButton.title = '插入粉紫蓝渐变波浪分割线';
                
                /* 🎯 添加点击事件 */
                dividerButton.addEventListener('click', function() {
                    var textarea = document.getElementById('content');
                    if (textarea) {
                        /* 📝 获取光标位置 */
                        var start = textarea.selectionStart;
                        var end = textarea.selectionEnd;
                        var text = textarea.value;
                        
                        /* 🌊 插入分割线HTML注释语法 前后都带换行 */
                        var dividerHtml = '\n<!--shiroki-divider-->\n';
                        textarea.value = text.substring(0, start) + dividerHtml + text.substring(end);
                        
                        /* 🔧 设置新的光标位置 */
                        var newCursorPos = start + dividerHtml.length;
                        textarea.selectionStart = newCursorPos;
                        textarea.selectionEnd = newCursorPos;
                        
                        /* 🎯 触发change事件 */
                        var event = new Event('input', { bubbles: true });
                        textarea.dispatchEvent(event);
                        
                        /* 🎯 聚焦到文本区域 */
                        textarea.focus();
                    }
                });
                
                /* 🌊 添加分隔符 */
                var separator = document.createElement('span');
                separator.className = 'separator';
                
                /* 📌 将按钮添加到工具栏 */
                toolbar.appendChild(separator);
                toolbar.appendChild(dividerButton);
            }
            
            /* 💨 检查是否已经添加了换行按钮 */
            if (!toolbar.querySelector('#qt_content_shiroki_nbsp')) {
                /* 💨 创建换行按钮 */
                var nbspButton = document.createElement('input');
                nbspButton.type = 'button';
                nbspButton.id = 'qt_content_shiroki_nbsp';
                nbspButton.className = 'ed_button button';
                nbspButton.value = '换行';
                nbspButton.title = '插入换行符 &nbsp;';
                
                /* 🎯 添加点击事件 */
                nbspButton.addEventListener('click', function() {
                    var textarea = document.getElementById('content');
                    if (textarea) {
                        /* 📝 获取光标位置 */
                        var start = textarea.selectionStart;
                        var end = textarea.selectionEnd;
                        var text = textarea.value;
                        
                        /* 💨 插入换行符 &nbsp; 前后都带换行 */
                        var nbspHtml = '\n&nbsp;\n';
                        textarea.value = text.substring(0, start) + nbspHtml + text.substring(end);
                        
                        /* 🔧 设置新的光标位置 */
                        var newCursorPos = start + nbspHtml.length;
                        textarea.selectionStart = newCursorPos;
                        textarea.selectionEnd = newCursorPos;
                        
                        /* 🎯 触发change事件 */
                        var event = new Event('input', { bubbles: true });
                        textarea.dispatchEvent(event);
                        
                        /* 🎯 聚焦到文本区域 */
                        textarea.focus();
                    }
                });
                
                /* 📌 将按钮添加到工具栏 */
                toolbar.appendChild(nbspButton);
            }
        }
        
        /* 🔧 初始化按钮 */
        initShirokiDividerButton();
        
        /* 🔄 监听DOM变化，确保在动态加载时也能添加按钮 */
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    initShirokiDividerButton();
                }
            });
        });
        
        /* 🔍 观察文档变化 */
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
    
    /* 🔄 如果页面已经加载完成，立即尝试初始化 */
    if (document.readyState === 'complete') {
        initShirokiDividerButton();
    }
})();