<?php
/**
 * @link gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器弹窗菜单模块
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 🎵 输出音乐播放器弹窗HTML
function boxmoe_music_player_popup_html() {
    // 获取主题设置
    $music_switch = get_boxmoe('music_on', false);
    
    // 如果音乐播放器未启用，则返回
    if (!$music_switch) {
        return;
    }
    
    // 获取音乐播放器设置
    $server = get_boxmoe('music_server', 'netease');
    $id = get_boxmoe('music_id', '6814606449');
    $order = get_boxmoe('music_order', 'list');
    $api = get_boxmoe('music_api', '');
    
    // 构建API属性
    $api_attr = '';
    if (!empty($api)) {
        $api_attr = ' api="' . esc_attr($api) . '"';
    }
    
    // 只输出弹窗菜单，不重复输出播放器核心标签
    $html = '';
    $html .= '<div id="music-popup-menu" class="music-popup-menu">
        <div class="music-popup-content">
            <div class="music-popup-header">
                <h3>💿 音乐控制窗口</h3>
                <button class="music-popup-close">×</button>
            </div>
            <div class="music-popup-body">
                <!-- 三列布局：歌词区域、音乐控制区域、歌单区域 -->
                <div class="music-layout">
                    <!-- 左侧：歌词区域 -->
                    <div class="music-lyrics-section">
                        <div class="section-header">
                            <h4>🎼 歌词</h4>
                            <div class="section-buttons">
                                <button id="lyrics-refresh" class="toggle-btn" title="刷新歌词">🔄</button>
                            </div>
                        </div>
                        <div class="lyrics-container">
                            <div id="popup-lyrics" class="lyrics-content">
                                <p class="lyrics-line">😥 暂无歌词</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 中间的音乐控制区域 -->
                    <div class="music-control-section">
                        <!-- 歌曲信息 -->
                        <div class="music-section">
                            <h4>🪗 歌曲信息</h4>
                            <div class="music-info">
                                <div class="music-cover"><img id="popup-cover" src="" alt="封面"></div>
                                <div class="music-details">
                                    <div id="popup-title" class="music-title">歌曲名</div>
                                    <div id="popup-artist" class="music-artist">歌手</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 播放控制 -->
                        <div class="music-section">
                            <h4>🎚️ 播放控制</h4>
                            <div class="music-controls">
                                <button id="popup-prev" class="control-btn">⏮️ 上一首</button>
                                <button id="popup-play" class="control-btn">▶️ 播放</button>
                                <button id="popup-next" class="control-btn">⏭️ 下一首</button>
                                <!-- <button id="popup-random" class="control-btn">🔀 随机</button> -->
                                <!-- <button id="popup-loop" class="control-btn">🔁 循环</button> -->
                            </div>
                        </div>
                        
                        <!-- 播放进度 -->
                        <div class="music-section">
                            <h4>🎶 播放进度</h4>
                            <div class="music-progress">
                                <div class="progress-info">
                                    <span id="popup-current-time">00:00</span>
                                    <span id="popup-total-time">00:00</span>
                                </div>
                                <div class="progress-bar">
                                    <div id="popup-progress-inner" class="progress-inner"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 音量控制 -->
                        <div class="music-section">
                            <h4>🔊 音量控制</h4>
                            <div class="volume-control">
                                <button id="popup-volume-mute" class="volume-btn">🔊</button>
                                <div class="volume-bar">
                                    <div id="popup-volume-inner" class="volume-inner"></div>
                                </div>
                                <span id="popup-volume-percent">100%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 右侧的歌单区域 -->
                    <div class="music-playlist-section">
                        <div class="section-header">
                            <h4>📋 歌单</h4>
                        </div>
                        <div class="playlist-container">
                            <div id="popup-playlist" class="playlist-content">
                                <div class="playlist-loading">加载中...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    $html .= '<style>
        /* 优化歌词显示样式 */
        .aplayer-lrc { 
            height: 140px; 
            overflow: auto; 
        }
        .aplayer-lrc-line { 
            transition: all 0.3s ease; 
        }
        .aplayer-lrc-current { 
            color: #8b3dff !important; 
            font-weight: 600; 
            transform: scale(1.05); 
        }
        .aplayer-lrc-current-prev, .aplayer-lrc-current-next { 
            color: rgba(255, 255, 255, 0.6); 
        }
        /* 暗色模式适配 */
        .dark-theme .aplayer-lrc-current { 
            color: #a78bfa !important; 
        }
        
        /* 音乐弹窗菜单样式 */
        .music-popup-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        
        .music-popup-menu.active {
            display: flex;
        }
        
        .music-popup-content {
            background-color: #2c2f36;
            border-radius: 16px;
            padding: 24px;
            width: 90%;
            max-width: 1200px; /* 增加宽度以容纳三列布局 */
            max-height: 80%;
            overflow: hidden; /* 移除主容器的滚动条 */
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 2px solid #4a4d55;
        }
        
        /* 🎵 三列布局样式 */
        .music-layout {
            display: flex;
            gap: 20px;
            min-height: 500px;
            max-height: calc(80vh - 120px); /* 减去头部和内边距的高度 */
        }
        
        /* 左侧歌词区域 */
        .music-lyrics-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 16px;
            overflow: hidden;
            min-height: 0; /* 确保flex子元素能够正确收缩 */
        }
        
        /* 中间控制区域 */
        .music-control-section {
            flex: 1.2;
            display: flex;
            flex-direction: column;
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 16px;
            overflow-y: auto; /* 允许控制区域在内容过多时滚动 */
            max-height: 100%;
        }
        
        /* 右侧歌单区域 */
        .music-playlist-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 16px;
            overflow: hidden;
            min-height: 0; /* 确保flex子元素能够正确收缩 */
        }
        
        /* 区域标题样式 */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-header h4 {
            color: #a78bfa;
            margin: 0;
            font-size: 16px;
        }
        
        .section-buttons {
            display: flex;
            gap: 8px;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            color: #a78bfa;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .toggle-btn:hover {
            background-color: rgba(167, 139, 250, 0.2);
        }
        
        /* 歌词容器样式 */
        .lyrics-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 8px;
            /* 自定义滚动条样式 */
            scrollbar-width: thin;
            scrollbar-color: rgba(167, 139, 250, 0.5) rgba(255, 255, 255, 0.1);
        }
        
        /* Webkit浏览器滚动条样式 */
        .lyrics-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .lyrics-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }
        
        .lyrics-container::-webkit-scrollbar-thumb {
            background: rgba(167, 139, 250, 0.5);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        
        .lyrics-container::-webkit-scrollbar-thumb:hover {
            background: rgba(167, 139, 250, 0.8);
        }
        
        .lyrics-content {
            text-align: center;
            padding: 16px 0;
        }
        
        .lyrics-line {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.8;
            margin: 8px 0;
            transition: all 0.3s ease;
        }
        
        .lyrics-line.active {
            color: #a78bfa;
            font-size: 16px;
            font-weight: 600;
        }
        
        .lyrics-loading {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        
        /* 歌单容器样式 */
        .playlist-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 8px;
            /* 自定义滚动条样式 */
            scrollbar-width: thin;
            scrollbar-color: rgba(167, 139, 250, 0.5) rgba(255, 255, 255, 0.1);
        }
        
        /* Webkit浏览器滚动条样式 */
        .playlist-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .playlist-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }
        
        .playlist-container::-webkit-scrollbar-thumb {
            background: rgba(167, 139, 250, 0.5);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        
        .playlist-container::-webkit-scrollbar-thumb:hover {
            background: rgba(167, 139, 250, 0.8);
        }
        
        .playlist-content {
            padding: 8px 0;
        }
        
        .playlist-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .playlist-item:hover {
            background-color: rgba(167, 139, 250, 0.1);
        }
        
        .playlist-item.active {
            background-color: rgba(167, 139, 250, 0.2);
            border-left: 3px solid #a78bfa;
        }
        
        .playlist-item-number {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            margin-right: 12px;
            min-width: 20px;
        }
        
        .playlist-item-info {
            flex: 1;
        }
        
        .playlist-item-title {
            color: #fff;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .playlist-item-artist {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
        }
        
        .playlist-item-duration {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            margin-left: 12px;
        }
        
        .playlist-loading {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            padding: 20px;
        }
        
        .music-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #4a4d55;
        }
        
        .music-popup-header h3 {
            color: #8b3dff;
            margin: 0;
            font-size: 20px;
        }
        
        .music-popup-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .music-popup-close:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: rotate(90deg);
        }
        
        .music-section {
            margin-bottom: 24px;
        }
        
        .music-section h4 {
            color: #a78bfa;
            margin: 0 0 12px 0;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* 歌曲信息样式 */
        .music-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .music-cover {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .music-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .music-details {
            flex: 1;
        }
        
        .music-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .music-artist {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        
        /* 播放控制按钮样式 */
        .music-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .control-btn {
            background: linear-gradient(135deg, #8b3dff, #a78bfa);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 61, 255, 0.4);
        }
        
        .control-btn:active {
            transform: translateY(0);
        }
        
        /* 🎵 播放进度样式 */
        .music-progress {
            margin: 12px 0;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .progress-bar {
            height: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .progress-inner {
            height: 100%;
            background: linear-gradient(90deg, #8b3dff, #a78bfa);
            border-radius: 4px;
            width: 0%;
            transition: width 0.1s ease;
            position: relative;
            box-shadow: 0 0 10px rgba(139, 61, 255, 0.5);
        }
        
        /* 🎵 添加进度条动态效果 */
        .progress-inner::after {
            content: \"\";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.3) 50%, 
                rgba(255, 255, 255, 0) 100%);
            animation: progressShine 2s infinite;
        }
        
        /* 🎵 进度条闪光动画 */
        @keyframes progressShine {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        /* 🎵 进度条悬停效果 */
        .progress-bar:hover {
            transform: scaleY(1.2);
            transition: transform 0.2s ease;
        }
        
        .progress-bar:hover .progress-inner {
            box-shadow: 0 0 15px rgba(139, 61, 255, 0.8);
        }
        
        /* 音量控制样式 */
        .volume-control {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .volume-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .volume-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .volume-bar {
            flex: 1;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            cursor: pointer;
            overflow: hidden;
        }
        
        .volume-inner {
            height: 100%;
            background: linear-gradient(90deg, #8b3dff, #a78bfa);
            border-radius: 2px;
            width: 100%;
            transition: width 0.1s ease;
        }
        
        #popup-volume-percent {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            min-width: 40px;
        }
        
        /* 🌆 暗色模式适配 */
        .dark-theme .music-popup-content {
            background-color: #1a1d23;
            border-color: #33363d;
        }
        
        .dark-theme .music-popup-header {
            border-bottom-color: #33363d;
        }
        
        /* 🌆 暗色模式下进度条样式优化 */
        .dark-theme .progress-bar {
            background-color: rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4);
        }
        
        .dark-theme .progress-inner {
            background: linear-gradient(90deg, #a78bfa, #c4b5fd);
            box-shadow: 0 0 15px rgba(167, 139, 250, 0.6);
        }
        
        .dark-theme .progress-bar:hover .progress-inner {
            box-shadow: 0 0 20px rgba(167, 139, 250, 0.9);
        }
        
        /* 🌆 暗色模式下三列布局样式 */
        .dark-theme .music-lyrics-section,
        .dark-theme .music-playlist-section {
            background-color: rgba(255, 255, 255, 0.03);
        }
        
        .dark-theme .music-control-section {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .dark-theme .section-header {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }
        
        .dark-theme .playlist-item:hover {
            background-color: rgba(167, 139, 250, 0.08);
        }
        
        .dark-theme .playlist-item.active {
            background-color: rgba(167, 139, 250, 0.15);
        }
        
        /* 📱 移动端适配样式 */
        @media (max-width: 768px) {
            .music-popup-content {
                width: 95%;
                max-width: none;
                padding: 16px;
                max-height: 90vh;
                border-radius: 12px;
            }
            
            .music-popup-header h3 {
                font-size: 18px;
            }
            
            /* 🎵 移动端上下布局 */
            .music-layout {
                flex-direction: column;
                gap: 16px;
                min-height: auto;
                max-height: calc(90vh - 80px);
                overflow-y: auto;
            }
            
            /* 🎵 移动端各区域样式调整 */
            .music-lyrics-section,
            .music-control-section,
            .music-playlist-section {
                flex: none;
                padding: 12px;
                border-radius: 8px;
            }
            
            /* 🎵 音乐控制区域在移动端置顶 */
            .music-control-section {
                order: -1; /* ◀️ 将控制区域移到最前面 */
                background-color: rgba(139, 61, 255, 0.1); /* ◀️ 突出显示控制区域 */
                border: 1px solid rgba(139, 61, 255, 0.2);
            }
            
            /* 🎵 歌词区域在移动端排第二 */
            .music-lyrics-section {
                order: 0; /* ◀️ 歌词区域排第二 */
                max-height: 200px; /* ◀️ 限制歌词区域高度 */
            }
            
            /* 🎵 歌单区域在移动端排最后 */
            .music-playlist-section {
                order: 1; /* ◀️ 歌单区域排最后 */
                max-height: 200px; /* ◀️ 限制歌单区域高度 */
            }
            
            /* 🎵 移动端歌曲信息样式调整 */
            .music-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
            }
            
            .music-cover {
                width: 120px;
                height: 120px;
            }
            
            /* 🎵 移动端播放控制按钮样式调整 */
            .music-controls {
                justify-content: center;
                gap: 8px;
            }
            
            .control-btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            /* 🎵 移动端进度条和音量控制样式调整 */
            .music-progress,
            .volume-control {
                margin: 8px 0;
            }
            
            .progress-bar,
            .volume-bar {
                height: 6px;
            }
            
            /* 🎵 移动端区域标题样式调整 */
            .section-header h4 {
                font-size: 14px;
            }
            
            /* 🎵 移动端歌词样式调整 */
            .lyrics-line {
                font-size: 13px;
                line-height: 1.6;
                margin: 6px 0;
            }
            
            .lyrics-line.active {
                font-size: 15px;
            }
            
            /* 🎵 移动端歌单项样式调整 */
            .playlist-item {
                padding: 6px 8px;
                margin: 2px 0;
            }
            
            .playlist-item-number {
                font-size: 11px;
                margin-right: 8px;
                min-width: 16px;
            }
        }
    </style>'
    ;
    $html .= '<script>
        // 🎵 音乐弹窗菜单功能
            document.addEventListener("DOMContentLoaded", function() {
                // 🎵 添加初始化标志，防止重复初始化
                let isInitialized = false;
                
                // 获取元素
                const popupMenu = document.getElementById("music-popup-menu");
                const closeBtn = document.querySelector(".music-popup-close");
            
            // 添加调试信息
            // console.log("🎵 音乐弹窗菜单功能初始化");
            // console.log("🎵 popupMenu元素:", popupMenu);
            // console.log("🎵 closeBtn元素:", closeBtn);
            
            // 显示弹窗的函数
            function showPopupMenu() {
                // console.log("🎵 显示弹窗菜单");
                popupMenu.classList.add("active");
                updatePopupInfo();
                // 添加调试样式，确保弹窗可见
                popupMenu.style.display = "flex";
                
                // 为当前 APlayer 实例添加 timeupdate 事件监听，实现实时进度更新
                const aplayer = getAplayerInstance();
                if (aplayer && aplayer.audio) {
                    // 先移除可能存在的事件监听，避免重复监听
                    aplayer.audio.removeEventListener("timeupdate", handleTimeUpdate);
                    // 添加新的事件监听
                    aplayer.audio.addEventListener("timeupdate", handleTimeUpdate);
                    // console.log("🎵 添加 timeupdate 事件监听");
                    
                    // 初始化歌词和歌单
                    initLyrics(aplayer);
                    initPlaylist(aplayer);
                }
            }
            
            // 🎵 初始化歌词功能
            function initLyrics(aplayer) {
                // console.log("🎵 初始化歌词功能");
                
                // 延迟执行，确保APlayer完全加载
                setTimeout(() => {
                    // 显示加载中
                    const lyricsElement = document.getElementById("popup-lyrics");
                    if (lyricsElement) {
                        lyricsElement.innerHTML = "<div class=\"lyrics-loading\">加载歌词中...</div>";
                    }
                    
                    // 使用强制刷新函数获取歌词
                    forceRefreshLyrics(aplayer);
                }, 500); // 延迟500ms执行
            }
            
            // 🎵 从URL获取歌词
            function fetchLyricsFromURL(url) {
                return new Promise((resolve, reject) => {
                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("网络响应错误");
                            }
                            return response.text();
                        })
                        .then(text => {
                            // 解析歌词文本
                            const lyrics = parseLyricsText(text);
                            resolve(lyrics);
                        })
                        .catch(error => {
                            reject(error);
                        });
                });
            }
            
            // 🎵 解析歌词文本
            function parseLyricsText(text) {
                if (!text || typeof text !== "string") return null;
                
                // 如果是JSON格式，尝试解析
                if (text.trim().startsWith("{") || text.trim().startsWith("[")) {
                    try {
                        const parsed = JSON.parse(text);
                        return parsed;
                    } catch (e) {
                        // console.error("🎵 解析JSON歌词失败:", e);
                    }
                }
                
                // 如果是LRC格式，解析为时间戳数组
                const lines = text.split("\n");
                const lyrics = [];
                
                lines.forEach(line => {
                    const match = line.match(/^\[(\d{2}):(\d{2})\.(\d{2,3})\](.*)$/);
                    if (match) {
                        const minutes = parseInt(match[1]);
                        const seconds = parseInt(match[2]);
                        const milliseconds = parseInt(match[3]);
                        const time = minutes * 60 + seconds + milliseconds / 1000;
                        const text = match[4].trim();
                        if (text) {
                            lyrics.push([time, text]);
                        }
                    } else if (line.trim() && !line.startsWith("[")) {
                        // 没有时间戳的歌词行
                        lyrics.push([0, line.trim()]);
                    }
                });
                
                return lyrics;
            }
            
            // 🎵 从HTML中提取歌词文本
            function extractLyricsFromHTML(html) {
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = html;
                const lyricsLines = tempDiv.querySelectorAll(".aplayer-lrc-line");
                const lyricsText = [];
                
                lyricsLines.forEach(line => {
                    const text = line.textContent.trim();
                    if (text) {
                        lyricsText.push(text);
                    }
                });
                
                return lyricsText.join("\n");
            }
            
            // 🎵 显示歌词
            function displayLyrics(lyrics) {
                const lyricsElement = document.getElementById("popup-lyrics");
                if (!lyricsElement) return;
                
                let lyricsHTML = "";
                
                if (typeof lyrics === "string") {
                    // 简单文本歌词
                    const lines = lyrics.split("\n");
                    lines.forEach(line => {
                        if (line.trim()) {
                            lyricsHTML += "<p class=\"lyrics-line\">" + line.trim() + "</p>";
                        }
                    });
                } else if (Array.isArray(lyrics)) {
                    // 歌词数组
                    lyrics.forEach(line => {
                        if (typeof line === "string" && line.trim()) {
                            lyricsHTML += "<p class=\"lyrics-line\">" + line.trim() + "</p>";
                        } else if (line && line[1]) {
                            // 带时间戳的歌词 [时间, 文本]
                            lyricsHTML += "<p class=\"lyrics-line\" data-time=\"" + line[0] + "\">" + line[1] + "</p>";
                        }
                    });
                } else if (lyrics && lyrics.lyrics) {
                    // APlayer歌词对象
                    lyrics.lyrics.forEach(line => {
                        if (line[1]) {
                            lyricsHTML += "<p class=\"lyrics-line\" data-time=\"" + line[0] + "\">" + line[1] + "</p>";
                        }
                    });
                } else if (lyrics && typeof lyrics === "object") {
                    // 其他歌词对象格式
                    Object.keys(lyrics).forEach(key => {
                        const value = lyrics[key];
                        if (typeof value === "string" && value.trim()) {
                            lyricsHTML += "<p class=\"lyrics-line\">" + value.trim() + "</p>";
                        } else if (Array.isArray(value) && value[1]) {
                            // 带时间戳的歌词 [时间, 文本]
                            lyricsHTML += "<p class=\"lyrics-line\" data-time=\"" + value[0] + "\">" + value[1] + "</p>";
                        }
                    });
                }
                
                lyricsElement.innerHTML = lyricsHTML || "<p class=\"lyrics-line\">暂无歌词</p>";
            }
            
            // 🎵 显示无歌词提示
            function displayNoLyrics() {
                const lyricsElement = document.getElementById("popup-lyrics");
                if (!lyricsElement) return;
                
                lyricsElement.innerHTML = "<p class=\"lyrics-line\">暂无歌词</p>";
            }
            
            // 🎵 更新当前歌词高亮
            function updateCurrentLyrics(currentTime) {
                const lyricsElement = document.getElementById("popup-lyrics");
                if (!lyricsElement) return;
                
                const lyricsLines = lyricsElement.querySelectorAll(".lyrics-line[data-time]");
                if (lyricsLines.length === 0) return;
                
                let activeIndex = -1;
                
                // 找到当前时间应该高亮的歌词
                for (let i = 0; i < lyricsLines.length; i++) {
                    const time = parseFloat(lyricsLines[i].getAttribute("data-time"));
                    if (time <= currentTime) {
                        activeIndex = i;
                    } else {
                        break;
                    }
                }
                
                // 更新高亮状态
                lyricsLines.forEach((line, index) => {
                    if (index === activeIndex) {
                        line.classList.add("active");
                        // 滚动到当前歌词
                        line.scrollIntoView({ behavior: "smooth", block: "center" });
                    } else {
                        line.classList.remove("active");
                    }
                });
            }
            
            // 🎵 初始化歌单功能
            function initPlaylist(aplayer) {
                // console.log("🎵 初始化歌单功能");
                
                const playlistElement = document.getElementById("popup-playlist");
                if (!playlistElement) return;
                
                // 获取歌单数据
                let playlist = null;
                
                // 方法1：从APlayer实例获取歌单
                if (aplayer.list && aplayer.list.audios) {
                    playlist = aplayer.list.audios;
                }
                
                // 显示歌单
                if (playlist && playlist.length > 0) {
                    displayPlaylist(playlist, aplayer.list.index);
                } else {
                    displayNoPlaylist();
                }
            }
            
            // 🎵 显示歌单
            function displayPlaylist(playlist, currentIndex) {
                const playlistElement = document.getElementById("popup-playlist");
                if (!playlistElement) return;
                
                let playlistHTML = "";
                
                playlist.forEach((item, index) => {
                    const isActive = index === currentIndex;
                    const duration = item.duration ? formatTime(item.duration) : "";
                    
                    playlistHTML += 
                        "<div class=\"playlist-item " + (isActive ? "active" : "") + "\" data-index=\"" + index + "\">" +
                            "<div class=\"playlist-item-number\">" + (index + 1) + "</div>" +
                            "<div class=\"playlist-item-info\">" +
                                "<div class=\"playlist-item-title\">" + (item.name || "未知歌曲") + "</div>" +
                                "<div class=\"playlist-item-artist\">" + (item.artist || "未知歌手") + "</div>" +
                            "</div>" +
                            "<div class=\"playlist-item-duration\">" + duration + "</div>" +
                        "</div>";
                });
                
                playlistElement.innerHTML = playlistHTML;
                
                // 添加点击事件
                const playlistItems = playlistElement.querySelectorAll(".playlist-item");
                playlistItems.forEach(item => {
                    item.addEventListener("click", function() {
                        const index = parseInt(this.getAttribute("data-index"));
                        const aplayer = getAplayerInstance();
                        if (aplayer) {
                            aplayer.list.switch(index);
                            updatePopupInfo();
                            initLyrics(aplayer);
                        }
                    });
                });
            }
            
            // 🎵 显示无歌单提示
            function displayNoPlaylist() {
                const playlistElement = document.getElementById("popup-playlist");
                if (!playlistElement) return;
                
                playlistElement.innerHTML = "<div class=\"playlist-loading\">暂无歌单</div>";
            }
            
            // 🎵 更新歌单当前播放项
            function updatePlaylistCurrentItem(currentIndex) {
                const playlistElement = document.getElementById("popup-playlist");
                if (!playlistElement) return;
                
                const playlistItems = playlistElement.querySelectorAll(".playlist-item");
                playlistItems.forEach((item, index) => {
                    if (index === currentIndex) {
                        item.classList.add("active");
                    } else {
                        item.classList.remove("active");
                    }
                });
            }
            
            // 🎵 处理 APlayer timeupdate 事件，实现实时进度更新
            function handleTimeUpdate() {
                // 只有当弹窗可见时才更新，减少性能消耗
                if (popupMenu.classList.contains("active")) {
                    updatePopupInfo();
                }
            }
            
            // 🎵 获取APlayer实例的函数 - 优化版
            function getAplayerInstance() {
                // 🎵 尝试多种方式获取APlayer实例，增加重试机制
                let aplayer = null;
                
                // 🎵 方法1：直接从DOM元素获取
                const aplayerElement = document.querySelector(".aplayer");
                if (aplayerElement && aplayerElement.aplayer) {
                    aplayer = aplayerElement.aplayer;
                    // console.log("🎵 通过DOM元素获取到APlayer实例:", aplayer);
                }
                
                // 🎵 方法2：从全局变量获取
                if (!aplayer && window.aplayers && window.aplayers.length > 0) {
                    aplayer = window.aplayers[0];
                    // console.log("🎵 通过全局变量获取到APlayer实例:", aplayer);
                }
                
                // 🎵 方法3：从Meting实例获取
                if (!aplayer) {
                    const metingElements = document.querySelectorAll("meting-js");
                    for (let i = 0; i < metingElements.length; i++) {
                        if (metingElements[i].aplayer) {
                            aplayer = metingElements[i].aplayer;
                            // console.log("🎵 通过Meting实例获取到APlayer实例:", aplayer);
                            break;
                        }
                    }
                }
                
                // 🎵 方法4：直接从window对象获取
                if (!aplayer && window.aplayer) {
                    aplayer = window.aplayer;
                    // console.log("🎵 通过window.aplayer获取到APlayer实例:", aplayer);
                }
                
                // 🎵 方法5：尝试从audio元素反向查找
                if (!aplayer) {
                    const audioElements = document.querySelectorAll("audio");
                    for (let i = 0; i < audioElements.length; i++) {
                        const audio = audioElements[i];
                        if (audio.parentElement && audio.parentElement.classList.contains("aplayer")) {
                            const aplayerEl = audio.parentElement;
                            if (aplayerEl.aplayer) {
                                aplayer = aplayerEl.aplayer;
                                // console.log("🎵 通过audio元素反向查找获取到APlayer实例:", aplayer);
                                break;
                            }
                        }
                    }
                }
                
                if (!aplayer) {
                    // console.log("🎵 无法获取APlayer实例，检查播放器是否初始化");
                } else {
                    // 🎵 验证APlayer实例是否有效
                    if (!aplayer.audio || typeof aplayer.audio.currentTime === "undefined") {
                        // console.log("🎵 APlayer实例可能未完全初始化");
                        return null;
                    }
                }
                
                return aplayer;
            }
            
            // 🎵 初始化播放控制按钮 - 优化版
            function initPlayControls() {
                // console.log("🎵 初始化播放控制按钮事件监听器");
                
                // 🎵 获取所有播放控制按钮
                const prevBtn = document.getElementById("popup-prev");
                const playBtn = document.getElementById("popup-play");
                const nextBtn = document.getElementById("popup-next");
                const randomBtn = document.getElementById("popup-random");
                const loopBtn = document.getElementById("popup-loop");
                
                // 🎵 为每个按钮单独检查并初始化，避免重复初始化
                if (prevBtn && !prevBtn.dataset.initialized) {
                    prevBtn.dataset.initialized = "true";
                    prevBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const aplayer = getAplayerInstance();
                        if (aplayer && aplayer.list) {
                            const currentIndex = aplayer.list.index;
                            const prevIndex = currentIndex > 0 ? currentIndex - 1 : aplayer.list.audios.length - 1;
                            aplayer.list.switch(prevIndex);
                            // console.log("🎵 切换到上一首，当前索引:", prevIndex);
                            // 🎵 立即更新UI
                            setTimeout(() => updatePopupInfo(), 100);
                        } else {
                            // console.log("🎵 无法获取APlayer实例或播放列表");
                        }
                    });
                    // console.log("🎵 上一首按钮初始化完成");
                }
                
                if (playBtn && !playBtn.dataset.initialized) {
                    playBtn.dataset.initialized = "true";
                    playBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const aplayer = getAplayerInstance();
                        if (aplayer) {
                            aplayer.toggle();
                            // 🎵 更新按钮文字
                            if (aplayer.paused) {
                                playBtn.innerHTML = "▶️ 播放";
                            } else {
                                playBtn.innerHTML = "⏸️ 暂停";
                            }
                            // console.log("🎵 播放/暂停切换，当前状态:", aplayer.paused ? "已暂停" : "播放中");
                        } else {
                            // console.log("🎵 无法获取APlayer实例");
                        }
                    });
                    // console.log("🎵 播放/暂停按钮初始化完成");
                }
                
                if (nextBtn && !nextBtn.dataset.initialized) {
                    nextBtn.dataset.initialized = "true";
                    nextBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const aplayer = getAplayerInstance();
                        if (aplayer && aplayer.list) {
                            const currentIndex = aplayer.list.index;
                            const nextIndex = (currentIndex + 1) % aplayer.list.audios.length;
                            aplayer.list.switch(nextIndex);
                            // console.log("🎵 切换到下一首，当前索引:", nextIndex);
                            // 🎵 立即更新UI
                            setTimeout(() => updatePopupInfo(), 100);
                        } else {
                            // console.log("🎵 无法获取APlayer实例或播放列表");
                        }
                    });
                    // console.log("🎵 下一首按钮初始化完成");
                }
                
                if (randomBtn && !randomBtn.dataset.initialized) {
                    randomBtn.dataset.initialized = "true";
                    randomBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const aplayer = getAplayerInstance();
                        if (aplayer) {
                            aplayer.setOption("order", aplayer.options.order === "random" ? "list" : "random");
                            // 🎵 更新按钮样式
                            if (aplayer.options.order === "random") {
                                randomBtn.style.background = "linear-gradient(135deg, #ff6b6b, #ee5a52)";
                            } else {
                                randomBtn.style.background = "linear-gradient(135deg, #8b3dff, #a78bfa)";
                            }
                            // console.log("🎵 随机播放切换，当前模式:", aplayer.options.order);
                        } else {
                            // console.log("🎵 无法获取APlayer实例");
                        }
                    });
                    // console.log("🎵 随机播放按钮初始化完成");
                }
                
                if (loopBtn && !loopBtn.dataset.initialized) {
                    loopBtn.dataset.initialized = "true";
                    loopBtn.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const aplayer = getAplayerInstance();
                        if (aplayer) {
                            let newLoop = "all";
                            if (aplayer.options.loop === "all") {
                                newLoop = "one";
                            } else if (aplayer.options.loop === "one") {
                                newLoop = "none";
                            } else if (aplayer.options.loop === "none") {
                                newLoop = "all";
                            }
                            aplayer.setOption("loop", newLoop);
                            // 🎵 更新按钮样式
                            if (newLoop === "one") {
                                loopBtn.style.background = "linear-gradient(135deg, #4ecdc4, #45b7aa)";
                            } else if (newLoop === "none") {
                                loopBtn.style.background = "linear-gradient(135deg, #95a5a6, #7f8c8d)";
                            } else {
                                loopBtn.style.background = "linear-gradient(135deg, #8b3dff, #a78bfa)";
                            }
                            // console.log("🎵 循环模式切换，当前模式:", newLoop);
                        } else {
                            // console.log("🎵 无法获取APlayer实例");
                        }
                    });
                    // console.log("🎵 循环播放按钮初始化完成");
                }
            }
            
            // 初始化进度条控制
            function initProgressControl() {
                const progressBar = document.querySelector(".progress-bar");
                const progressInner = document.getElementById("popup-progress-inner");
                
                progressBar.addEventListener("click", function(e) {
                    const rect = progressBar.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    const aplayer = getAplayerInstance();
                    if (aplayer) aplayer.seek(aplayer.duration * percent);
                });
            }
            
            // 🎵 初始化音量控制
            function initVolumeControl() {
                const volumeBar = document.querySelector(".volume-bar");
                const volumeInner = document.getElementById("popup-volume-inner");
                const volumeMute = document.getElementById("popup-volume-mute");
                const volumePercent = document.getElementById("popup-volume-percent");
                
                // 🎵 添加变量跟踪用户设置的音量，防止被自动更新覆盖
                let userSetVolume = null;
                let isUpdatingVolume = false;
                let isDragging = false;
                
                // 🎵 设置音量的辅助函数
                function setVolume(percent) {
                    const aplayer = getAplayerInstance();
                    if (!aplayer) return;
                    
                    // 🎵 确保音量值在0-1之间
                    percent = Math.max(0, Math.min(1, percent));
                    
                    // 🎵 使用正确的API设置音量
                    if (aplayer.audio && typeof aplayer.audio.volume !== "undefined") {
                        aplayer.audio.volume = percent;
                    } else if (typeof aplayer.volume === "function") {
                        aplayer.volume(percent);
                    } else if (typeof aplayer.volume !== "undefined") {
                        aplayer.volume = percent;
                    }
                    
                    // 🎵 立即更新UI显示
                    volumeInner.style.width = percent * 100 + "%";
                    volumePercent.textContent = Math.round(percent * 100) + "%";
                    
                    // 🎵 更新静音按钮状态
                    if (percent === 0) {
                        volumeMute.textContent = "🔇";
                    } else {
                        volumeMute.textContent = "🔊";
                    }
                    
                    // 记录用户设置的音量值
                    userSetVolume = percent;
                    
                    // console.log("🎵 设置音量:", Math.round(percent * 100) + "%");
                }
                
                // 🎵 处理音量条点击和拖拽
                function handleVolumeInteraction(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const rect = volumeBar.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    
                    isUpdatingVolume = true;
                    setVolume(percent);
                    
                    // 🎵 延迟重置更新标志，防止定期更新覆盖用户设置
                    setTimeout(() => {
                        isUpdatingVolume = false;
                    }, 2000);
                }
                
                // 🎵 点击音量条调整音量
                volumeBar.addEventListener("mousedown", function(e) {
                    isDragging = true;
                    handleVolumeInteraction(e);
                });
                
                // 🎵 拖拽调整音量
                document.addEventListener("mousemove", function(e) {
                    if (isDragging) {
                        handleVolumeInteraction(e);
                    }
                });
                
                // 🎵 释放拖拽
                document.addEventListener("mouseup", function() {
                    if (isDragging) {
                        isDragging = false;
                    }
                });
                
                // 🎵 点击静音按钮切换静音
                volumeMute.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const aplayer = getAplayerInstance();
                    if (aplayer) {
                        isUpdatingVolume = true;
                        
                        // 🎵 切换静音状态
                        if (aplayer.audio) {
                            aplayer.audio.muted = !aplayer.audio.muted;
                        } else if (typeof aplayer.toggleMute === "function") {
                            aplayer.toggleMute();
                        }
                        
                        const isMuted = aplayer.audio ? aplayer.audio.muted : aplayer.muted;
                        
                        if (isMuted) {
                            volumeMute.textContent = "🔇";
                            volumeInner.style.width = "0%";
                            volumePercent.textContent = "0%";
                        } else {
                            volumeMute.textContent = "🔊";
                            // 🎵 恢复之前的音量或默认音量
                            const targetVolume = userSetVolume !== null ? userSetVolume : 0.7;
                            setVolume(targetVolume);
                        }
                        
                        // 延迟重置更新标志
                        setTimeout(() => {
                            isUpdatingVolume = false;
                        }, 2000);
                        
                        // console.log("🎵 切换静音状态，当前静音:", isMuted);
                    }
                });
                
                // 🎵 导出函数供updatePopupInfo使用
                window.shirokiVolumeControl = {
                    isUpdatingVolume: () => isUpdatingVolume,
                    getUserSetVolume: () => userSetVolume,
                    setUserSetVolume: (vol) => { userSetVolume = vol; }
                };
            }
            
            // 更新弹窗信息
            function updatePopupInfo() {
                const aplayer = getAplayerInstance();
                if (!aplayer) {
                    // console.log("🎵 无法获取APlayer实例");
                    return;
                }
                
                // console.log("🎵 更新弹窗信息，当前歌曲索引:", aplayer.list.index);
                
                // 更新歌曲信息
                const currentAudio = aplayer.list.audios[aplayer.list.index];
                if (currentAudio) {
                    // console.log("🎵 当前歌曲信息:", currentAudio);
                    document.getElementById("popup-title").textContent = currentAudio.name || "未知歌曲";
                    document.getElementById("popup-artist").textContent = currentAudio.artist || "未知歌手";
                    document.getElementById("popup-cover").src = currentAudio.cover || "";
                }
                
                // 更新播放时间和进度条
                // console.log("🎵 APlayer实例详细信息:", {
                //     currentTime: aplayer.currentTime,
                //     duration: aplayer.duration,
                //     paused: aplayer.paused,
                //     audio: aplayer.audio,
                //     hasAudio: !!aplayer.audio,
                //     audioCurrentTime: aplayer.audio ? aplayer.audio.currentTime : "N/A"
                // });
                
                // 尝试多种方式获取当前时间和总时长
                let currentTime = 0;
                let duration = 0;
                
                // 方法1：直接从APlayer实例获取
                if (!isNaN(aplayer.currentTime)) {
                    currentTime = aplayer.currentTime;
                }
                // 方法2：从audio元素获取
                else if (aplayer.audio && !isNaN(aplayer.audio.currentTime)) {
                    currentTime = aplayer.audio.currentTime;
                }
                
                // 同样尝试多种方式获取总时长
                if (!isNaN(aplayer.duration)) {
                    duration = aplayer.duration;
                }
                else if (aplayer.audio && !isNaN(aplayer.audio.duration)) {
                    duration = aplayer.audio.duration;
                }
                
                // console.log("🎵 最终使用的时间值 - 当前:", currentTime, "总时长:", duration);
                
                // 更新播放时间
                document.getElementById("popup-current-time").textContent = formatTime(currentTime);
                document.getElementById("popup-total-time").textContent = formatTime(duration);
                
                // 更新进度条
                let progress = 0;
                if (duration > 0) {
                    progress = (currentTime / duration) * 100;
                }
                // console.log("🎵 更新进度条宽度为:", progress + "%");
                document.getElementById("popup-progress-inner").style.width = progress + "%";
                
                // 更新歌词高亮
                updateCurrentLyrics(currentTime);
                
                // 更新歌单当前播放项
                updatePlaylistCurrentItem(aplayer.list.index);
                
                // 更新播放按钮状态
                const playBtn = document.getElementById("popup-play");
                if (aplayer.paused) {
                    playBtn.innerHTML = "▶️ 播放";
                } else {
                    playBtn.innerHTML = "⏸️ 暂停";
                }
                
                // 🎵 更新音量状态
                const volumeMute = document.getElementById("popup-volume-mute");
                const volumeInner = document.getElementById("popup-volume-inner");
                const volumePercent = document.getElementById("popup-volume-percent");
                
                // 🎵 检查是否正在更新音量，如果是则跳过音量部分的自动更新
                const isVolumeUpdating = window.shirokiVolumeControl && window.shirokiVolumeControl.isUpdatingVolume();
                if (isVolumeUpdating) {
                    // console.log("🎵 音量正在由用户控制，跳过音量自动更新");
                    // 🎵 只更新静音按钮状态，不更新音量条
                    const isMuted = aplayer.audio ? aplayer.audio.muted : aplayer.muted;
                    if (isMuted) {
                        volumeMute.textContent = "🔇";
                    } else {
                        volumeMute.textContent = "🔊";
                    }
                } else {
                    // 🎵 获取音量值，确保是有效数字
                    let volumeValue = 1;
                    let isMuted = false;
                    
                    // 🎵 尝试多种方式获取音量和静音状态
                    if (aplayer.audio && typeof aplayer.audio.volume !== "undefined") {
                        volumeValue = aplayer.audio.volume;
                        isMuted = aplayer.audio.muted;
                    } else if (typeof aplayer.volume === "number") {
                        volumeValue = aplayer.volume;
                        isMuted = aplayer.muted || false;
                    } else if (typeof aplayer.options.volume === "number") {
                        volumeValue = aplayer.options.volume;
                        isMuted = aplayer.muted || false;
                    }
                    
                    // 🎵 确保音量值在0-1之间
                    volumeValue = Math.max(0, Math.min(1, volumeValue));
                    
                    // console.log("🎵 当前音量:", volumeValue, "是否静音:", isMuted);
                    
                    if (isMuted) {
                        volumeMute.textContent = "🔇";
                        volumeInner.style.width = "0%";
                        volumePercent.textContent = "0%";
                    } else {
                        volumeMute.textContent = "🔊";
                        const volumePercentValue = Math.round(volumeValue * 100);
                        volumeInner.style.width = volumePercentValue + "%";
                        volumePercent.textContent = volumePercentValue + "%";
                        
                        // 🎵 更新用户设置的音量值，用于静音恢复
                        if (window.shirokiVolumeControl && volumeValue > 0) {
                            window.shirokiVolumeControl.setUserSetVolume(volumeValue);
                        }
                    }
                }
            }
            
            // 格式化时间
            function formatTime(seconds) {
                if (isNaN(seconds)) return "00:00";
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return mins.toString().padStart(2, "0") + ":" + secs.toString().padStart(2, "0");
            }
            
            // 🎵 初始化函数 - 优化版
            function initPopupMenu() {
                // 🎵 检查是否已经初始化过，防止重复初始化
                if (isInitialized) {
                    // console.log("🎵 弹窗菜单已经初始化过，跳过重复初始化");
                    return;
                }
                
                // console.log("🎵 初始化弹窗菜单事件监听器");
                isInitialized = true;
                
                // 🎵 等待APlayer完全加载后再初始化控制按钮
                const waitForAPlayerAndInit = () => {
                    const aplayer = getAplayerInstance();
                    if (aplayer) {
                        // console.log("🎵 APlayer已加载，开始初始化控制按钮");
                        
                        // 1. 为APlayer元素添加点击事件
                        document.addEventListener("click", function(e) {
                            // 检查点击目标是否是APlayer相关元素
                            const isPlayerElement = e.target.closest(".aplayer") || 
                                                  e.target.closest(".aplayer-body") || 
                                                  e.target.closest(".aplayer-pic") || 
                                                  e.target.closest(".aplayer-info") || 
                                                  e.target.closest(".aplayer-button") || 
                                                  e.target.closest(".aplayer-bar");
                            
                            if (isPlayerElement) {
                                // console.log("🎵 点击了APlayer元素:", e.target);
                                showPopupMenu();
                            }
                        });
                        
                        // 2. 为关闭按钮添加单独的点击事件
                        if (closeBtn) {
                            closeBtn.addEventListener("click", function(e) {
                                e.stopPropagation();
                                e.preventDefault();
                                // console.log("🎵 点击关闭按钮");
                                popupMenu.classList.remove("active");
                                popupMenu.style.display = "none";
                            });
                        }
                        
                        // 3. 为弹窗添加点击事件，点击外部关闭
                        popupMenu.addEventListener("click", function(e) {
                            // 如果点击的是弹窗背景（不是内容区域），则关闭
                            if (e.target === popupMenu) {
                                // console.log("🎵 点击弹窗外部背景");
                                popupMenu.classList.remove("active");
                                popupMenu.style.display = "none";
                            }
                        });
                        
                        // 4. 添加ESC键关闭功能
                        document.addEventListener("keydown", function(e) {
                            if (e.key === "Escape" && popupMenu.classList.contains("active")) {
                                // console.log("🎵 按下ESC键关闭弹窗");
                                popupMenu.classList.remove("active");
                                popupMenu.style.display = "none";
                            }
                        });
                        
                        // 初始化播放控制
                        initPlayControls();
                        
                        // 初始化进度条控制
                        initProgressControl();
                        
                        // 初始化音量控制
                        initVolumeControl();
                        
                        // 初始化歌词和歌单切换按钮
                        initToggleButtons();
                        
                        // console.log("🎵 弹窗菜单初始化完成");
                    } else {
                        // 🎵 如果APlayer还未加载，延迟重试
                        // console.log("🎵 APlayer未加载，500ms后重试");
                        setTimeout(waitForAPlayerAndInit, 500);
                    }
                };
                
                // 🎵 开始等待APlayer加载
                waitForAPlayerAndInit();
                
                // 🎵 定期更新播放信息，作为 timeupdate 事件的补充
                setInterval(function() {
                    // 🎵 检查弹窗是否可见，不可见时不更新
                    if (!popupMenu.classList.contains("active")) {
                        return;
                    }
                    
                    // 🎵 检查是否正在更新音量，如果是则跳过本次更新
                    if (window.shirokiVolumeControl && window.shirokiVolumeControl.isUpdatingVolume()) {
                        // console.log("🎵 音量正在由用户控制，跳过本次更新");
                        return;
                    }
                    
                    updatePopupInfo();
                }, 1000); // ◀️ 缩短更新间隔到1秒，确保进度条及时更新
            }
            
            // 🎵 初始化切换按钮
            function initToggleButtons() {
                // 歌词切换按钮
                const lyricsToggle = document.getElementById("lyrics-toggle");
                if (lyricsToggle) {
                    lyricsToggle.addEventListener("click", function() {
                        const lyricsSection = document.querySelector(".music-lyrics-section");
                        if (lyricsSection) {
                            lyricsSection.style.display = lyricsSection.style.display === "none" ? "flex" : "none";
                        }
                    });
                }
                
                // 歌词刷新按钮
                const lyricsRefresh = document.getElementById("lyrics-refresh");
                if (lyricsRefresh) {
                    lyricsRefresh.addEventListener("click", function() {
                        const aplayer = getAplayerInstance();
                        if (aplayer) {
                            // console.log("🎵 手动刷新歌词");
                            forceRefreshLyrics(aplayer);
                        }
                    });
                }
                
                // 歌单切换按钮
                const playlistToggle = document.getElementById("playlist-toggle");
                if (playlistToggle) {
                    playlistToggle.addEventListener("click", function() {
                        const playlistSection = document.querySelector(".music-playlist-section");
                        if (playlistSection) {
                            playlistSection.style.display = playlistSection.style.display === "none" ? "flex" : "none";
                        }
                    });
                }
            }
            
            // 🎵 强制刷新歌词
            function forceRefreshLyrics(aplayer) {
                // console.log("🎵 强制刷新歌词");
                
                // 显示加载中
                const lyricsElement = document.getElementById("popup-lyrics");
                if (lyricsElement) {
                    lyricsElement.innerHTML = "<div class=\"lyrics-loading\">加载歌词中...</div>";
                }
                
                // 尝试多种方式获取歌词
                let lyrics = null;
                
                // 方法1：从当前歌曲获取歌词
                if (aplayer.list && aplayer.list.audios && aplayer.list.audios[aplayer.list.index]) {
                    const currentAudio = aplayer.list.audios[aplayer.list.index];
                    // console.log("🎵 当前歌曲信息:", currentAudio);
                    
                    // 检查是否有歌词URL
                    if (currentAudio.lrc) {
                        if (typeof currentAudio.lrc === "string" && currentAudio.lrc.startsWith("http")) {
                            // 从URL获取歌词
                            fetchLyricsFromURL(currentAudio.lrc).then(lrcContent => {
                                if (lrcContent) {
                                    displayLyrics(lrcContent);
                                } else {
                                    displayNoLyrics();
                                }
                            }).catch(error => {
                                // console.error("🎵 获取歌词失败:", error);
                                displayNoLyrics();
                            });
                            return;
                        } else {
                            lyrics = currentAudio.lrc;
                        }
                    }
                }
                
                // 方法2：从MetingJS API获取歌词
                if (!lyrics && aplayer.list && aplayer.list.audios && aplayer.list.audios[aplayer.list.index]) {
                    const currentAudio = aplayer.list.audios[aplayer.list.index];
                    if (currentAudio.id) {
                        // 构建歌词API URL
                        const server = currentAudio.server || "netease";
                        const type = "lrc";
                        const id = currentAudio.id;
                        // 🎵 使用当前活动的API源
                        const currentAPI = window.meting_api || "https://api.i-meto.com/meting/api";
                        const lyricsUrl = `${currentAPI}?server=${server}&type=${type}&id=${id}`;
                        // console.log("🎵 尝试从API获取歌词:", lyricsUrl);
                        
                        fetchLyricsFromURL(lyricsUrl).then(lrcContent => {
                            if (lrcContent) {
                                displayLyrics(lrcContent);
                            } else {
                                displayNoLyrics();
                            }
                        }).catch(error => {
                            // console.error("🎵 从API获取歌词失败:", error);
                            displayNoLyrics();
                        });
                        return;
                    }
                }
                
                // 如果获取到歌词，显示它
                if (lyrics) {
                    displayLyrics(lyrics);
                } else {
                    displayNoLyrics();
                }
            }
            
            // 🎵 添加全局测试函数，方便调试
            window.testMusicPopup = function() {
                // console.log("🎵 测试音乐弹窗功能");
                showPopupMenu();
            };
            
            // 🎵 添加重新初始化播放控制按钮的函数，用于修复按钮失效问题
            window.reinitMusicControls = function() {
                // console.log("🎵 重新初始化播放控制按钮");
                
                // 🎵 清除所有按钮的初始化标记
                const buttons = [
                    document.getElementById("popup-prev"),
                    document.getElementById("popup-play"),
                    document.getElementById("popup-next"),
                    document.getElementById("popup-random"),
                    document.getElementById("popup-loop")
                ];
                
                buttons.forEach(btn => {
                    if (btn) {
                        delete btn.dataset.initialized;
                        // console.log("🎵 清除按钮初始化标记:", btn.id);
                    }
                });
                
                // 🎵 重新初始化播放控制按钮
                initPlayControls();
                
                // 🎵 更新弹窗信息
                updatePopupInfo();
                
                // console.log("🎵 播放控制按钮重新初始化完成");
            };
            
            // 🎵 添加自动修复机制，定期检查按钮是否正常工作
            let lastCheckTime = Date.now();
            setInterval(function() {
                // 🎵 每30秒检查一次按钮状态
                if (Date.now() - lastCheckTime > 30000) {
                    const aplayer = getAplayerInstance();
                    if (aplayer) {
                        // 🎵 检查播放按钮是否正常工作
                        const playBtn = document.getElementById("popup-play");
                        if (playBtn && playBtn.dataset.initialized) {
                            // console.log("🎵 播放控制按钮状态检查通过");
                        } else {
                            // console.log("🎵 检测到播放控制按钮可能失效，尝试重新初始化");
                            window.reinitMusicControls();
                        }
                    }
                    lastCheckTime = Date.now();
                }
            }, 10000); // 每10秒检查一次时间间隔
            
            // 立即尝试初始化
            initPopupMenu();
            
            // 使用更可靠的方式监听播放器元素
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1) {
                                // 检查是否包含APlayer相关元素
                                if (node.matches(".aplayer") || node.querySelector(".aplayer")) {
                                    // console.log("🎵 检测到APlayer元素，重新初始化事件监听器");
                                    initPopupMenu();
                                }
                            }
                        });
                    }
                });
            });
            
            // 观察整个文档的变化
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true
            });
            
            // 同时设置定时器，确保最终能初始化
            setTimeout(() => {
                // console.log("🎵 定时器触发，再次初始化事件监听器");
                initPopupMenu();
            }, 2000);
        });
    </script>'
    ;
    
    echo $html;
}

// 在页面底部输出音乐播放器弹窗
add_action('wp_footer', 'boxmoe_music_player_popup_html');