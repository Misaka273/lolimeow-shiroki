/**
 * 搜索框表单验证自定义处理
 * 阻止浏览器默认验证提示框，使用气泡样式提示
 */
(function() {
    'use strict';
    
    // 等待DOM加载完成
    document.addEventListener('DOMContentLoaded', function() {
        // 获取所有搜索表单
        var searchForms = document.querySelectorAll('.search-form');
        
        searchForms.forEach(function(form) {
            var searchInput = form.querySelector('.search-input');
            
            if (searchInput) {
                // 创建气泡提示元素
                var tooltip = document.createElement('div');
                tooltip.className = 'search-tooltip';
                tooltip.textContent = '✍🏻请输入内容🤪';
                tooltip.style.cssText = `
                    position: absolute;
                    bottom: 100%;
                    left: 50%;
                    transform: translateX(-50%) translateY(-8px);
                    background: #ff6b6b;
                    color: #fff;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-size: 0.85rem;
                    white-space: nowrap;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    z-index: 1000;
                    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
                `;
                
                // 添加气泡箭头
                var arrow = document.createElement('div');
                arrow.style.cssText = `
                    position: absolute;
                    top: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    border: 6px solid transparent;
                    border-top-color: #ff6b6b;
                `;
                tooltip.appendChild(arrow);
                
                // 将气泡添加到搜索框容器
                var wrap = form.querySelector('.search-wrap');
                if (wrap) {
                    wrap.style.position = 'relative';
                    wrap.appendChild(tooltip);
                }
                
                // 阻止默认的表单验证提示
                searchInput.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    showTooltip();
                });
                
                // 输入时隐藏提示
                searchInput.addEventListener('input', function() {
                    hideTooltip();
                    this.classList.remove('custom-error');
                });
                
                // 表单提交时验证
                form.addEventListener('submit', function(e) {
                    if (searchInput.value.trim() === '') {
                        e.preventDefault();
                        showTooltip();
                        searchInput.classList.add('custom-error');
                        searchInput.focus();
                    }
                });
                
                // 显示气泡提示
                function showTooltip() {
                    tooltip.style.opacity = '1';
                    tooltip.style.visibility = 'visible';
                    tooltip.style.transform = 'translateX(-50%) translateY(-12px)';
                    
                    // 3秒后自动隐藏
                    setTimeout(function() {
                        hideTooltip();
                    }, 3000);
                }
                
                // 隐藏气泡提示
                function hideTooltip() {
                    tooltip.style.opacity = '0';
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.transform = 'translateX(-50%) translateY(-8px)';
                }
            }
        });
    });
    
    // 添加错误样式
    var style = document.createElement('style');
    style.textContent = `
        .search-form .search-input.custom-error {
            border-color: #ff6b6b !important;
        }
        
        .search-form.search-style-comic .search-input.custom-error {
            border-color: #ff6b6b !important;
            box-shadow: 0 4px 16px rgba(255, 107, 107, 0.2) !important;
        }
        
        .search-form.search-style-glass .search-input.custom-error {
            border-color: #ff6b6b !important;
            box-shadow: 
                0 4px 16px rgba(255, 107, 107, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
        }
    `;
    document.head.appendChild(style);
})();
