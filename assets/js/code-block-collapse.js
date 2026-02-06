// 📦 代码块折叠功能
document.addEventListener('DOMContentLoaded', function() {
    // 🔍 获取配置参数
    const codeBlockCollapseSwitch = window.boxmoe_code_block_collapse_switch || false;
    const codeBlockCollapseHeight = window.boxmoe_code_block_collapse_height || 80;

    // 🚀 如果未开启折叠功能，则退出
    if (!codeBlockCollapseSwitch) {
        return;
    }

    // 🎯 查找所有代码块元素
    const codeBlocks = document.querySelectorAll('pre, code, .hljs, .prettyprint, .highlight');

    // 📦 为每个代码块添加折叠功能
    codeBlocks.forEach(function(block) {
        // 🔍 跳过已经在折叠容器中的代码块
        if (block.closest('.code-block-collapse-wrapper')) {
            return;
        }

        // 📏 检查代码块是否超过指定高度
        const blockHeight = block.offsetHeight;
        if (blockHeight <= codeBlockCollapseHeight) {
            return;
        }

        // 🎨 创建折叠容器
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-collapse-wrapper';
        
        // 📦 创建展开/收起按钮
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'code-block-collapse-toggle';
        toggleBtn.innerHTML = '<i class="fa fa-angle-down"></i> <span>展开代码</span>';
        toggleBtn.setAttribute('aria-expanded', 'false');
        
        // 🎯 将代码块包装在容器中
        block.parentNode.insertBefore(wrapper, block);
        wrapper.appendChild(block);
        wrapper.appendChild(toggleBtn);
        
        // 📏 设置初始折叠状态
        block.style.maxHeight = codeBlockCollapseHeight + 'px';
        block.style.overflow = 'hidden';
        block.classList.add('code-block-collapsed');
        
        // 🔄 点击切换展开/收起
        toggleBtn.addEventListener('click', function() {
            const isCollapsed = block.classList.contains('code-block-collapsed');
            
            if (isCollapsed) {
                // 📖 展开代码块
                block.style.maxHeight = block.scrollHeight + 'px';
                block.classList.remove('code-block-collapsed');
                block.classList.add('code-block-expanded');
                toggleBtn.innerHTML = '<i class="fa fa-angle-up"></i> <span>收起代码</span>';
                toggleBtn.setAttribute('aria-expanded', 'true');
            } else {
                // 📕 收起代码块
                block.style.maxHeight = codeBlockCollapseHeight + 'px';
                block.classList.remove('code-block-expanded');
                block.classList.add('code-block-collapsed');
                toggleBtn.innerHTML = '<i class="fa fa-angle-down"></i> <span>展开代码</span>';
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    });
});