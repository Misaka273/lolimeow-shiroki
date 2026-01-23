/**
 * 🥰所有链接语法新增SVG图标
 * 灵阈研都-纸鸢社开发
 * https://gl.baimu.live/
 */
document.addEventListener('DOMContentLoaded', function() {
    const svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" class="shiroki-link-icon"><path d="M52.64 4H11.36A7.36 7.36 0 0 0 4 11.36v36.36A12.28 12.28 0 0 0 16.27 60h36.37A7.36 7.36 0 0 0 60 52.64V11.36A7.36 7.36 0 0 0 52.64 4zM30 49.1a6.9 6.9 0 0 1-6.9 6.9h-6.47A8.63 8.63 0 0 1 8 47.37V40.9a6.9 6.9 0 0 1 6.9-6.9h8.2a6.9 6.9 0 0 1 6.9 6.9zm5.41-17.68a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 2.83zm16.84-18.54-1.56 6.73a.42.42 0 0 1-.71.2l-1-1a.73.73 0 0 0-1 0l-6.56 6.56a2 2 0 0 1-2.83-2.83L45.15 16a.73.73 0 0 0 0-1l-1-1a.42.42 0 0 1 .2-.71l6.73-1.56a1 1 0 0 1 1.17 1.15z" style="fill:rgba(128, 128, 128, 0.5)" data-name="Layer 2"/></svg>';

    function addSvgIconToLinks() {
        // 只在文章内容区域查找链接
        const contentArea = document.querySelector('.single-content, .post-content, .entry-content, article');
        if (!contentArea) return;
        
        const links = contentArea.querySelectorAll('a[href]');
        
        links.forEach(link => {
            // 检查是否已经添加过图标
            if (!link.querySelector('.shiroki-link-icon')) {
                // 排除特殊链接 - 检查链接本身的class
                if (link.classList.contains('page-numbers') || 
                    link.classList.contains('prev') || 
                    link.classList.contains('next')) {
                    return;
                }
                
                // 检查父元素
                const parent = link.parentElement;
                if (parent) {
                    // 排除特殊链接 - 检查父元素class
                    if (parent.classList.contains('widget') || 
                        parent.classList.contains('sidebar') || 
                        parent.classList.contains('footer') || 
                        parent.classList.contains('header') || 
                        parent.classList.contains('nav') || 
                        parent.classList.contains('pagination') || 
                        parent.classList.contains('page-numbers') || 
                        parent.classList.contains('page-links') ||
                        parent.classList.contains('linksbtn') ||
                        parent.classList.contains('downloadbtn')) {
                        return;
                    }
                    
                    // 排除特殊链接 - 检查父元素标签
                    if (parent.tagName === 'NAV' || 
                        parent.tagName === 'FOOTER' || 
                        parent.tagName === 'HEADER') {
                        return;
                    }
                }
                
                // 检查祖先元素
                if (link.closest('.widget') || 
                    link.closest('.sidebar') || 
                    link.closest('.footer') || 
                    link.closest('.header') || 
                    link.closest('nav') || 
                    link.closest('footer') || 
                    link.closest('header') || 
                    link.closest('.pagination') || 
                    link.closest('.page-numbers') || 
                    link.closest('.page-links') ||
                    link.closest('.linksbtn') ||
                    link.closest('.downloadbtn')) {
                    return;
                }

                // 🚫 排除文章卡片标题链接
                if (link.closest('.post-title') || 
                    link.closest('.entry-title') ||
                    link.closest('.card-title') ||
                    link.closest('h1') ||
                    link.closest('h2') ||
                    link.closest('h3')) {
                    return;
                }
                
                // 检查链接内部是否有按钮相关的图标或元素
                const hasButtonIcon = link.querySelector('i, .icon, .svg, [class*="icon"], [class*="svg"]');
                const hasButtonClass = link.classList.contains('btn') || 
                                      link.classList.contains('button') ||
                                      link.classList.contains('linksbtn') ||
                                      link.classList.contains('downloadbtn') ||
                                      link.className.includes('btn') ||
                                      link.className.includes('button') ||
                                      link.className.includes('linksbtn') ||
                                      link.className.includes('downloadbtn');
                
                if (hasButtonIcon || hasButtonClass) {
                    return;
                }
                
                // 检查链接的子节点类型
                const childNodes = Array.from(link.childNodes);
                const hasText = childNodes.some(node => node.nodeType === Node.TEXT_NODE && node.textContent.trim().length > 0);
                const hasImg = link.querySelector('img');
                const hasOtherElements = childNodes.some(node => node.nodeType === Node.ELEMENT_NODE && node.tagName !== 'IMG');
                
                // 只处理两种情况：
                // 1. 纯图片链接（Markdown图片语法）：只包含一个img标签
                // 2. 纯文本链接（HTML链接语法）：只包含文本节点，不包含任何元素
                const isMarkdownImage = hasImg && !hasText && !hasOtherElements && childNodes.length === 1;
                const isHtmlLink = !hasImg && hasText && !hasOtherElements;
                
                if (isMarkdownImage || isHtmlLink) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = svgIcon;
                    const svgElement = tempDiv.firstChild;
                    
                    svgElement.style.display = 'inline-block';
                    svgElement.style.width = '16px';
                    svgElement.style.height = '16px';
                    svgElement.style.verticalAlign = 'middle';
                    svgElement.style.transformOrigin = 'center';
                    svgElement.style.transform = 'rotate(90deg) translateY(0)';
                    svgElement.style.transition = 'transform 0.3s ease, fill 0.3s ease';
                    svgElement.style.marginRight = '4px';
                    svgElement.style.marginBottom = '2px';
                    
                    link.addEventListener('mouseenter', function() {
                        svgElement.style.transform = 'rotate(0deg) translateY(0)';
                        svgElement.querySelector('path').style.fill = '#0072ff';
                    });
                    
                    link.addEventListener('mouseleave', function() {
                        svgElement.style.transform = 'rotate(90deg) translateY(0)';
                        svgElement.querySelector('path').style.fill = 'rgba(128, 128, 128, 0.5)';
                    });
                    
                    link.insertBefore(svgElement, link.firstChild);
                }
            }
        });
    }

    addSvgIconToLinks();

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    addSvgIconToLinks();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});