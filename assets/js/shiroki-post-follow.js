/**
 * 🥰文章订阅核心功能
 * 灵阈研都-纸鸢社开发
 * https://gl.baimu.live/
 */

jQuery(document).ready(function($) {
    'use strict';

    console.log('shiroki-post-follow: 文档已加载');
    console.log('shiroki-post-follow: jQuery版本 = ' + $.fn.jquery);

    var $followBtn = $('.shiroki-follow-btn');
    if ($followBtn.length === 0) {
        console.log('shiroki-post-follow: 未找到订阅按钮');
        return;
    }

    console.log('shiroki-post-follow: 找到订阅按钮，数量 = ' + $followBtn.length);
    var postId = $followBtn.data('post-id');
    var isFollowed = $followBtn.hasClass('followed');

    if (typeof shirokiPostFollow === 'undefined') {
        console.error('shiroki-post-follow: shirokiPostFollow 对象未定义，请检查脚本加载');
        console.log('shiroki-post-follow: 可用的全局变量:', Object.keys(window).filter(k => k.includes('shiroki')));
        return;
    }

    console.log('shiroki-post-follow: AJAX URL = ' + shirokiPostFollow.ajax_url);
    console.log('shiroki-post-follow: Nonce = ' + shirokiPostFollow.nonce);

    $followBtn.on('click', function(e) {
        e.preventDefault();
        console.log('shiroki-post-follow: 按钮被点击');

        var $this = $(this);
        var action = $this.hasClass('followed') ? 'unfollow' : 'follow';
        console.log('shiroki-post-follow: 操作 = ' + action);

        $.ajax({
            url: shirokiPostFollow.ajax_url,
            type: 'POST',
            data: {
                action: 'shiroki_follow_post',
                nonce: shirokiPostFollow.nonce,
                post_id: postId,
                follow_action: action
            },
            beforeSend: function() {
                $this.prop('disabled', true).addClass('loading');
                console.log('shiroki-post-follow: 发送请求...');
            },
            success: function(response) {
                console.log('shiroki-post-follow: 收到响应', response);
                if (response.success) {
                    if (action === 'follow') {
                        $this.addClass('followed');
                        $this.find('.follow-text').text('已订阅');
                        $this.find('.follow-icon').removeClass('fa-bell-o').addClass('fa-bell');
                    } else {
                        $this.removeClass('followed');
                        $this.find('.follow-text').text('订阅');
                        $this.find('.follow-icon').removeClass('fa-bell').addClass('fa-bell-o');
                    }
                    $this.find('.follow-count').text(response.data.follow_count);
                    shirokiShowMessage(response.data.message, 'success');
                } else {
                    shirokiShowMessage(response.data.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                shirokiShowMessage('网络错误，请重试', 'error');
            },
            complete: function() {
                $this.prop('disabled', false).removeClass('loading');
            }
        });
    });

    function shirokiShowMessage(message, type) {
        var $message = $('<div class="shiroki-message shiroki-message-' + type + '">' + message + '</div>');
        $('body').append($message);
        
        setTimeout(function() {
            $message.addClass('show');
        }, 10);

        setTimeout(function() {
            $message.removeClass('show');
            setTimeout(function() {
                $message.remove();
            }, 300);
        }, 3000);
    }
});
