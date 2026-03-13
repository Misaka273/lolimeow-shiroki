/* 🌊 shiroki分割线TinyMCE插件
 * 🕊️白木 开发 🔗gl.baimu.live
 */
(function() {
    /* 🎨 使用现代WordPress TinyMCE API */
    tinymce.PluginManager.add('shiroki_divider', function(editor, url) {
        /* 🔗 添加分割线按钮到TinyMCE工具栏 */
        editor.ui.registry.addButton('shiroki_divider', {
            text: '分割线',
            tooltip: '插入粉紫蓝渐变波浪分割线',
            icon: 'dashicons-minus',
            onAction: function() {
                /* 🎨 插入分割线HTML注释语法 前后都带换行 */
                editor.insertContent('\n<!--shiroki-divider-->\n');
            }
        });

        /* 🎯 添加菜单项（可选） */
        editor.ui.registry.addMenuItem('shiroki_divider', {
            text: '分割线',
            icon: 'dashicons-minus',
            onAction: function() {
                editor.insertContent('\n<!--shiroki-divider-->\n');
            }
        });

        /* 📝 返回插件信息 */
        return {
            getMetadata: function() {
                return  {
                    name: 'Shiroki Divider Plugin',
                    url: 'https://www.boxmoe.com'
                };
            }
        };
    });

    /* 💨 注册换行符插件 */
    tinymce.PluginManager.add('shiroki_nbsp', function(editor, url) {
        /* 🔗 添加换行按钮到TinyMCE工具栏 */
        editor.ui.registry.addButton('shiroki_nbsp', {
            text: '换行',
            tooltip: '插入换行符 &nbsp;',
            icon: 'dashicons-editor-break',
            onAction: function() {
                /* 💨 插入换行符 &nbsp; 前后都带换行 */
                editor.insertContent('\n&nbsp;\n');
            }
        });

        /* 🎯 添加菜单项（可选） */
        editor.ui.registry.addMenuItem('shiroki_nbsp', {
            text: '换行',
            icon: 'dashicons-editor-break',
            onAction: function() {
                editor.insertContent('\n&nbsp;\n');
            }
        });

        /* 📝 返回插件信息 */
        return {
            getMetadata: function() {
                return  {
                    name: 'Shiroki NBSP Plugin',
                    url: 'https://gl.baimu.live'
                };
            }
        };
    });
})();