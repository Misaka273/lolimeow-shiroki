// 🌊 shiroki分割线TinyMCE插件
(function() {
    // 🎨 使用现代WordPress TinyMCE API
    tinymce.PluginManager.add('shiroki_divider', function(editor, url) {
        // 🔗 添加分割线按钮到TinyMCE工具栏
        editor.ui.registry.addButton('shiroki_divider', {
            text: '分割线',
            tooltip: '插入粉紫蓝渐变波浪分割线',
            icon: 'dashicons-minus',
            onAction: function() {
                // 🎨 插入分割线HTML注释语法
                editor.insertContent('<!--shiroki-divider-->');
            }
        });

        // 🎯 添加菜单项（可选）
        editor.ui.registry.addMenuItem('shiroki_divider', {
            text: '分割线',
            icon: 'dashicons-minus',
            onAction: function() {
                editor.insertContent('<!--shiroki-divider-->');
            }
        });

        // 📝 返回插件信息
        return {
            getMetadata: function() {
                return  {
                    name: 'Shiroki Divider Plugin',
                    url: 'https://www.boxmoe.com'
                };
            }
        };
    });
})();