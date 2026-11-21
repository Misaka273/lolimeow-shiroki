// 🥳 自定义 Quicktags 图片弹窗
// 🔗 替换默认的 WordPress 图片插入功能
;(function($) {
    // 🔧 初始化函数
    function initCustomImageDialog() {
        // 等待 DOM 加载完成
        $(document).ready(function() {
            // 🌟 创建自定义弹窗 HTML 结构
            var dialogHTML = `
                <div id="shiroki-img-dialog-overlay" class="shiroki-dialog-overlay">
                    <div id="shiroki-img-dialog" class="shiroki-dialog">
                        <div class="shiroki-dialog-header">
                            <h3>插入图片</h3>
                        </div>
                        <div class="shiroki-dialog-content">
                            <div class="shiroki-dialog-field">
                                <label for="shiroki-img-url">图片链接</label>
                                <input type="text" id="shiroki-img-url" name="shiroki-img-url" placeholder="http://" value="http://">
                            </div>
                            <div class="shiroki-dialog-field">
                                <label for="shiroki-img-alt">图片名称</label>
                                <input type="text" id="shiroki-img-alt" name="shiroki-img-alt" placeholder="图片描述">
                            </div>
                        </div>
                        <div class="shiroki-dialog-footer">
                            <button type="button" id="shiroki-img-cancel" class="shiroki-dialog-btn shiroki-dialog-btn-cancel">取消</button>
                            <button type="button" id="shiroki-img-insert" class="shiroki-dialog-btn shiroki-dialog-btn-insert">确定</button>
                        </div>
                    </div>
                </div>
            `;
            
            // 🎨 添加弹窗样式
            var dialogCSS = `
                /* 🎨 弹窗遮罩层 */
                .shiroki-dialog-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: oklch(0 0 0 / 0.5);
                    backdrop-filter: blur(5px);
                    display: none;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                    transition: opacity 0.3s ease;
                }
                
                /* 🎨 弹窗主体 - 拟态玻璃效果 */
                .shiroki-dialog {
                    background: oklch(1 0 0 / 0.85);
                    backdrop-filter: blur(10px);
                    border-radius: 16px;
                    box-shadow: 0 8px 32px oklch(0.3382 0.1582 271.28 / 0.37);
                    border: 1px solid oklch(1 0 0 / 0.18);
                    width: 90%;
                    max-width: 400px;
                    overflow: hidden;
                    z-index: 10001;
                    transform: translateY(0);
                    transition: all 0.3s ease;
                }
                
                /* 🎨 弹窗头部 */
                .shiroki-dialog-header {
                    padding: 16px 20px;
                    background: oklch(1 0 0 / 0.6);
                    border-bottom: 1px solid oklch(1 0 0 / 0.2);
                }
                
                .shiroki-dialog-header h3 {
                    margin: 0;
                    font-size: 18px;
                    color: oklch(0.3211 0 0);
                    font-weight: 600;
                }
                
                /* 🎨 弹窗内容 */
                .shiroki-dialog-content {
                    padding: 20px;
                }
                
                /* 🎨 弹窗字段 */
                .shiroki-dialog-field {
                    margin-bottom: 16px;
                }
                
                .shiroki-dialog-field:last-child {
                    margin-bottom: 0;
                }
                
                .shiroki-dialog-field label {
                    display: block;
                    margin-bottom: 8px;
                    font-size: 14px;
                    color: oklch(0.4495 0 0);
                    font-weight: 500;
                }
                
                .shiroki-dialog-field input {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid oklch(1 0 0 / 0.3);
                    border-radius: 8px;
                    background: oklch(1 0 0 / 0.9);
                    font-size: 14px;
                    color: oklch(0.3211 0 0);
                    box-shadow: inset 0 2px 4px oklch(0 0 0 / 0.05);
                    transition: all 0.3s ease;
                }
                
                .shiroki-dialog-field input:focus {
                    outline: none;
                    border-color: oklch(0.5935 0.2241 277.12 / 0.5);
                    box-shadow: 0 0 0 3px oklch(0.5935 0.2241 277.12 / 0.1);
                }
                /* 🎨 弹窗底部 */
                .shiroki-dialog-footer {
                    padding: 16px 20px;
                    background: oklch(1 0 0 / 0.6);
                    border-top: 1px solid oklch(1 0 0 / 0.2);
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                }
                
                /* 🎨 弹窗按钮 */
                .shiroki-dialog-btn {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    z-index: 10002;
                    position: relative;
                }
                
                /* 🎨 取消按钮 */
                .shiroki-dialog-btn-cancel {
                    background: oklch(1 0 0 / 0.8);
                    color: oklch(0.5103 0 0);
                    border: 1px solid oklch(0 0 0 / 0.1);
                }
                
                .shiroki-dialog-btn-cancel:hover {
                    background: oklch(1 0 0);
                    color: oklch(0.3211 0 0);
                    box-shadow: 0 2px 8px oklch(0 0 0 / 0.1);
                }
                
                /* 🎨 确定按钮 */
                .shiroki-dialog-btn-insert {
                    background: linear-gradient(135deg in srgb, oklch(0.5935 0.2241 277.12 / 0.8), oklch(0.6339 0.2197 294.09 / 0.8));
                    color: oklch(1 0 0);
                }
                
                .shiroki-dialog-btn-insert:hover {
                    background: linear-gradient(135deg in srgb, oklch(0.5935 0.2241 277.12), oklch(0.6339 0.2197 294.09));
                    box-shadow: 0 4px 12px oklch(0.5935 0.2241 277.12 / 0.4);
                }
            `;
            
            // 📌 将弹窗 HTML 添加到页面（只添加一次）
            if ($('#shiroki-img-dialog-overlay').length === 0) {
                $('body').append(dialogHTML);
            }
            
            // 🎨 将样式添加到页面
            if ($('style[data-shiroki-dialog]').length === 0) {
                $('<style data-shiroki-dialog>').text(dialogCSS).appendTo('head');
            }
            
            // 🔧 显示弹窗
            function showDialog() {
                $('#shiroki-img-dialog-overlay').css('display', 'flex');
                // 延迟添加动画类，确保过渡效果正常触发
                setTimeout(function() {
                    $('#shiroki-img-dialog').addClass('show');
                    // 延迟获取焦点，提升用户体验
                    setTimeout(function() {
                        $('#shiroki-img-url').focus();
                    }, 200);
                }, 10);
            }
            
            // 🎯 隐藏弹窗
            function hideDialog() {
                // 添加淡出动画效果
                $('#shiroki-img-dialog').removeClass('show');
                setTimeout(function() {
                    $('#shiroki-img-dialog-overlay').css('display', 'none');
                    // 重置表单
                    $('#shiroki-img-url').val('http://');
                    $('#shiroki-img-alt').val('');
                }, 300);
            }
            
            // 📝 插入图片到编辑器
            function insertImage() {
                var url = $('#shiroki-img-url').val().trim();
                var alt = $('#shiroki-img-alt').val().trim();
                
                if (!url) {
                    alert('请输入图片链接');
                    return;
                }
                
                // 🔍 获取编辑器 textarea 元素
                var $textarea = $('#content');
                if (!$textarea.length) {
                    alert('未找到编辑器');
                    return;
                }
                
                // 🔧 获取光标位置
                var textarea = $textarea[0];
                var start = textarea.selectionStart || 0;
                var end = textarea.selectionEnd || start;
                var content = textarea.value;
                
                // 🌟 构建图片 HTML 语法
                var imgHtml = '<img src="' + url + '" alt="' + alt + '" />';
                
                // 📌 插入图片语法到编辑器
                var newContent = content.substring(0, start) + imgHtml + content.substring(end);
                
                // 🎯 更新编辑器内容 - 使用更高效的方式
                textarea.value = newContent;
                
                // 🔧 优化：只在必要时恢复焦点，减少闪屏
                // 避免使用 focus() 方法导致的页面滚动
                
                // 🎨 触发编辑器内容变化事件
                // 使用更高效的方式触发事件
                $textarea.trigger('input');
                $textarea.trigger('change');
                
                // 🚀 隐藏弹窗
                hideDialog();
            }
            
            // 🎯 使用事件委托绑定弹窗按钮事件
            $(document).on('click', '#shiroki-img-insert', function(e) {
                e.preventDefault();
                e.stopPropagation();
                insertImage();
                return false;
            });
            
            $(document).on('click', '#shiroki-img-cancel', function(e) {
                e.preventDefault();
                e.stopPropagation();
                hideDialog();
                return false;
            });
            
            // ⌨️ 键盘事件：Enter 键插入图片，Escape 键关闭弹窗
            $(document).on('keydown', function(e) {
                if ($('#shiroki-img-dialog-overlay').css('display') === 'flex') {
                    if (e.key === 'Enter') {
                        insertImage();
                    } else if (e.key === 'Escape') {
                        hideDialog();
                    }
                }
            });
            
            // 🌍 点击遮罩层关闭弹窗
            $(document).on('click', '#shiroki-img-dialog-overlay', function(e) {
                if (e.target === this) {
                    hideDialog();
                }
            });
            
            // 🔧 等待 DOM 加载完成，然后替换图片按钮功能
            var checkImgButtonInterval = setInterval(function() {
                var imgButtonEl = document.getElementById('qt_content_img');
                if (imgButtonEl) {
                    // 清除定时器
                    clearInterval(checkImgButtonInterval);
                    
                    // 🎯 移除所有现有的点击事件监听器
                    var newImgButtonEl = imgButtonEl.cloneNode(true);
                    imgButtonEl.parentNode.replaceChild(newImgButtonEl, imgButtonEl);
                    
                    // 🎯 添加我们的自定义点击事件
                    newImgButtonEl.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        showDialog();
                        return false;
                    }, true);
                    
                    // 🎯 同时使用 jQuery 绑定点击事件
                    $(document).on('click', '#qt_content_img', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        showDialog();
                        return false;
                    });
                }
            }, 100);
        });
    }
    
    // 🚀 启动函数
    initCustomImageDialog();
})(jQuery);