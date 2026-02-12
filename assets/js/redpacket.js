/* 🧧 红包雨飘落动画 - 基于Canvas实现 */

var stopRedpacket, staticRedpacket;

/* 🧧 定义红包和金币SVG图标路径 */
var redpacketSvgs = [
    /* 🧧 自定义红包SVG */
    redpacket_object.redpacket_url,
    /* 🪙 金币SVG */
    redpacket_object.coin_url
];

/* 🖼️ 预加载所有红包图片 */
var redpacketImages = [];
var loadedCount = 0;

function preloadRedpacketImages(callback) {
    for (var i = 0; i < redpacketSvgs.length; i++) {
        var img = new Image();
        img.onload = function() {
            loadedCount++;
            if (loadedCount === redpacketSvgs.length) {
                callback();
            }
        };
        img.src = redpacketSvgs[i];
        redpacketImages.push(img);
    }
}

/* 📄 获取需要避开的文章区域 - 仅在文章详情页生效 */
function getContentArea() {
    /* 🔍 首先检测是否是文章详情页 */
    var isSinglePage = document.body.classList.contains('single') || 
                       document.body.classList.contains('single-post') ||
                       document.querySelector('.single-post') !== null ||
                       document.querySelector('.blog-single') !== null;
    
    /* 🏠 如果不是文章详情页（如首页），则不避开任何区域 */
    if (!isSinglePage) {
        return null;
    }
    
    var contentSelectors = [
        '.blog-single',           /* 📝 文章主体区域 */
        '.post-single',           /* 📝 文章内容 */
        '.single-content',        /* 📝 文章正文 */
        '.post-comments',         /* 💬 评论区 */
        '.comment-form',          /* 📝 评论表单 */
        'article.post',           /* 📝 HTML5文章标签（文章页） */
        '.entry-content',         /* 📝 WordPress文章内容 */
        'main article'            /* 📝 主内容区内的文章 */
    ];
    
    var contentArea = null;
    for (var i = 0; i < contentSelectors.length; i++) {
        var elem = document.querySelector(contentSelectors[i]);
        if (elem) {
            contentArea = elem.getBoundingClientRect();
            break;
        }
    }
    return contentArea;
}

/* 🧧 红包类 */
function Redpacket(x, y, s, r, fn, imgIndex) {
    this.x = x;
    this.y = y;
    this.s = s;
    this.r = r;
    this.fn = fn;
    this.imgIndex = imgIndex;
    this.opacity = 1;
}

Redpacket.prototype.draw = function(cxt) {
    cxt.save();
    /* 📐 红包为长窄形，宽度为高度的0.6倍 */
    var height = 60 * this.s;
    var width = height * 0.6;
    cxt.translate(this.x, this.y);
    cxt.rotate(this.r);
    cxt.globalAlpha = this.opacity;
    if (redpacketImages[this.imgIndex]) {
        cxt.drawImage(redpacketImages[this.imgIndex], -width / 2, -height / 2, width, height);
    }
    cxt.restore();
};

Redpacket.prototype.update = function() {
    this.x = this.fn.x(this.x, this.y);
    this.y = this.fn.y(this.y, this.y);
    this.r = this.fn.r(this.r);
    
    /* 📝 检测是否在文章区域内 */
    var contentArea = getContentArea();
    if (contentArea) {
        /* 📐 计算红包边界框 */
        var height = 60 * this.s;
        var width = height * 0.6;
        var halfWidth = width / 2;
        var halfHeight = height / 2;
        
        var redpacketLeft = this.x - halfWidth;
        var redpacketRight = this.x + halfWidth;
        var redpacketTop = this.y - halfHeight;
        var redpacketBottom = this.y + halfHeight;
        
        /* 🔍 检测是否与文章区域重叠 */
        var isOverlapping = !(
            redpacketRight < contentArea.left ||
            redpacketLeft > contentArea.right ||
            redpacketBottom < contentArea.top ||
            redpacketTop > contentArea.bottom
        );
        
        if (isOverlapping) {
            /* 👻 在文章区域内时降低透明度 */
            this.opacity = 0.15;
        } else {
            /* 👁️ 在文章区域外时恢复正常透明度 */
            this.opacity = 1;
        }
    }
    
    /* 🔄 边界检测，超出屏幕后重置位置 */
    if (this.x > window.innerWidth + 50 || this.x < -50 || this.y > window.innerHeight + 50 || this.y < -50) {
        this.r = getRandomRedpacket('fnr');
        this.opacity = 1;
        if (Math.random() > 0.4) {
            /* ⬇️ 从顶部重新出现 */
            this.x = getRandomRedpacket('x');
            this.y = -50;
            this.s = getRandomRedpacket('s');
            this.r = getRandomRedpacket('r');
            this.imgIndex = Math.floor(Math.random() * redpacketImages.length);
        } else {
            /* ➡️ 从右侧重新出现 */
            this.x = window.innerWidth + 50;
            this.y = getRandomRedpacket('y');
            this.s = getRandomRedpacket('s');
            this.r = getRandomRedpacket('r');
            this.imgIndex = Math.floor(Math.random() * redpacketImages.length);
        }
    }
};

/* 📋 红包列表管理类 */
RedpacketList = function() {
    this.list = [];
};

RedpacketList.prototype.push = function(redpacket) {
    this.list.push(redpacket);
};

RedpacketList.prototype.update = function() {
    for (var i = 0, len = this.list.length; i < len; i++) {
        this.list[i].update();
    }
};

RedpacketList.prototype.draw = function(cxt) {
    for (var i = 0, len = this.list.length; i < len; i++) {
        this.list[i].draw(cxt);
    }
};

RedpacketList.prototype.size = function() {
    return this.list.length;
};

/* 🎲 随机数生成函数 */
function getRandomRedpacket(option) {
    var ret, random;
    switch (option) {
        case 'x':
            /* 📏 水平位置随机 */
            ret = Math.random() * window.innerWidth;
            break;
        case 'y':
            /* 📏 垂直位置随机 */
            ret = Math.random() * window.innerHeight;
            break;
        case 's':
            /* 📐 大小随机 (0.5 - 1.2) */
            ret = 0.5 + Math.random() * 0.7;
            break;
        case 'r':
            /* 🔄 旋转角度随机 */
            ret = Math.random() * 6;
            break;
        case 'fnx':
            /* ➡️ 水平移动速度 */
            random = -0.5 + Math.random() * 1;
            ret = function(x, y) {
                return x + 0.5 * random - 1.5;
            };
            break;
        case 'fny':
            /* ⬇️ 垂直下落速度 (红包下落比樱花快) */
            random = 2 + Math.random() * 1.5;
            ret = function(x, y) {
                return y + random;
            };
            break;
        case 'fnr':
            /* 🔄 旋转速度 */
            random = Math.random() * 0.05;
            ret = function(r) {
                return r + random;
            };
            break;
    }
    return ret;
}

/* 🚀 启动红包雨 */
function startRedpacket() {
    requestAnimationFrame = window.requestAnimationFrame || 
                           window.mozRequestAnimationFrame || 
                           window.webkitRequestAnimationFrame || 
                           window.msRequestAnimationFrame || 
                           window.oRequestAnimationFrame;
    
    var canvas = document.createElement('canvas');
    var cxt;
    staticRedpacket = true;
    
    canvas.height = window.innerHeight;
    canvas.width = window.innerWidth;
    canvas.setAttribute('style', 'position: fixed; left: 0; top: 0; pointer-events: none; z-index: 9998;');
    canvas.setAttribute('id', 'canvas_redpacket');
    document.getElementsByTagName('body')[0].appendChild(canvas);
    
    cxt = canvas.getContext('2d');
    
    var redpacketList = new RedpacketList();
    
    /* 🧧 创建红包实例 (数量比樱花少一些，因为红包更大) */
    for (var i = 0; i < 35; i++) {
        var redpacket, randomX, randomY, randomS, randomR, randomFnx, randomFny, randomFnR, imgIndex;
        randomX = getRandomRedpacket('x');
        randomY = getRandomRedpacket('y');
        randomR = getRandomRedpacket('r');
        randomS = getRandomRedpacket('s');
        randomFnx = getRandomRedpacket('fnx');
        randomFny = getRandomRedpacket('fny');
        randomFnR = getRandomRedpacket('fnr');
        imgIndex = Math.floor(Math.random() * redpacketImages.length);
        
        redpacket = new Redpacket(randomX, randomY, randomS, randomR, {
            x: randomFnx,
            y: randomFny,
            r: randomFnR
        }, imgIndex);
        
        redpacket.draw(cxt);
        redpacketList.push(redpacket);
    }
    
    /* 🔄 动画循环 */
    stopRedpacket = requestAnimationFrame(function() {
        cxt.clearRect(0, 0, canvas.width, canvas.height);
        redpacketList.update();
        redpacketList.draw(cxt);
        stopRedpacket = requestAnimationFrame(arguments.callee);
    });
}

/* 🛑 停止红包雨 */
function stopRedpacketRain() {
    if (staticRedpacket) {
        var child = document.getElementById('canvas_redpacket');
        if (child && child.parentNode) {
            child.parentNode.removeChild(child);
        }
        window.cancelAnimationFrame(stopRedpacket);
        staticRedpacket = false;
    }
}

/* 🔄 窗口大小改变时调整canvas */
window.onresize = function() {
    var canvasRedpacket = document.getElementById('canvas_redpacket');
    if (canvasRedpacket) {
        canvasRedpacket.width = window.innerWidth;
        canvasRedpacket.height = window.innerHeight;
    }
};

/* 🚀 预加载完成后启动红包雨 */
preloadRedpacketImages(function() {
    startRedpacket();
});
