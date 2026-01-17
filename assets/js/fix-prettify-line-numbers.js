// 🎯 修复Prettify代码块行号显示问题


(function() {
    // 等待页面加载完成
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixPrettifyLineNumbers);
    } else {
        fixPrettifyLineNumbers();
    }

    function fixPrettifyLineNumbers() {
        // 检查是否存在prettify相关元素
        const prettyprintElements = document.querySelectorAll('.prettyprint.linenums');
        if (prettyprintElements.length === 0) {
            return;
        }

        // 重写PR.prettyPrint函数，确保行号正确生成
        if (window.PR) {
            // 保存原始的prettyPrint函数
            const originalPrettyPrint = window.PR.prettyPrint;
            
            window.PR.prettyPrint = function() {
                // 调用原始函数
                originalPrettyPrint.apply(this, arguments);
                
                // 确保CSS计数器正常工作
                ensureCSSCounterWorks();
                
                // 🌟 动态调整行号宽度
                adjustLineNumberWidth();
            };
        }

        // 立即修复已渲染的行号
        ensureCSSCounterWorks();
        
        // 🌟 动态调整行号宽度以适应不同位数的行号
        adjustLineNumberWidth();
    }

    // 🌟 动态调整行号宽度函数
    function adjustLineNumberWidth() {
        const codeBlocks = document.querySelectorAll('.prettyprint.linenums');
        
        codeBlocks.forEach(function(block) {
            const ol = block.querySelector('ol.linenums');
            if (ol) {
                const lines = ol.querySelectorAll('li');
                if (lines.length > 0) {
                    // 获取最大行号
                    const maxLineNumber = lines.length;
                    // 计算需要的位数
                    const digits = Math.max(2, Math.floor(Math.log10(maxLineNumber)) + 1);
                    // 根据位数计算所需宽度（每个数字约8px，加上padding和边距）
                    const requiredWidth = Math.max(35, digits * 8 + 10);
                    
                    // 动态设置行号宽度
                    const styleId = 'dynamic-line-number-style-' + Math.random().toString(36).substr(2, 9);
                    let styleEl = document.getElementById(styleId);
                    
                    if (!styleEl) {
                        styleEl = document.createElement('style');
                        styleEl.id = styleId;
                        document.head.appendChild(styleEl);
                    }
                    
                    styleEl.textContent = `
                        #${block.id || 'prettify-' + Math.random().toString(36).substr(2, 9)} .linenums li:before {
                            width: ${requiredWidth}px !important;
                        }
                        #${block.id || 'prettify-' + Math.random().toString(36).substr(2, 9)} .linenums li {
                            padding-left: ${requiredWidth + 15}px !important;
                        }
                    `;
                }
            }
        });
    }

    function ensureCSSCounterWorks() {
        // 查找所有带有行号的代码块
        const codeBlocks = document.querySelectorAll('.prettyprint.linenums');
        
        codeBlocks.forEach(function(block) {
            const ol = block.querySelector('ol.linenums');
            if (ol) {
                const lines = ol.querySelectorAll('li');
                
                // 确保CSS计数器正常工作
                ol.style.counterReset = 'line-number';
                ol.style.listStyleType = 'none';
                
                // 修复每个li元素
                lines.forEach(function(line, index) {
                    // 移除冲突的value属性，避免与CSS计数器冲突
                    line.removeAttribute('value');
                    
                    // 移除内联样式，使用CSS中定义的样式
                    line.removeAttribute('style');
                    
                    // 确保行号递增
                    line.style.counterIncrement = 'line-number';
                    
                    // 保持L0-L9的循环样式，用于交替行高亮
                    line.className = `L${index % 10}`;
                });
            }
        });
    }
})();