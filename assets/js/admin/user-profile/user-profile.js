/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 👤 个人资料页面交互脚本
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage User_Profile
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 个人资料UI控制器
     */
    var ShirokiUserProfile = {

        /**
         * 🚀 初始化
         */
        init: function() {
            this.bindEvents();
            this.initPasswordStrength();
            this.initAvatarUpload();
            this.initFormValidation();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🔐 生成密码按钮 */
            $(document).on('click', '#shiroki-profile-generate-pw', this.handleGeneratePassword.bind(this));

            /* 👁️ 切换密码显示 */
            $(document).on('click', '.shiroki-profile-toggle-pw', this.handleTogglePassword.bind(this));

            /* 🔐 密码输入监听 */
            $(document).on('input', '#shiroki_profile_pass1', function() {
                self.checkPasswordStrength($(this).val());
            });

            /* 📝 表单提交 */
            $(document).on('submit', '#shiroki-profile-form', this.handleFormSubmit.bind(this));

            /* 🖼️ 头像上传 */
            $(document).on('click', '#shiroki-profile-upload-avatar', this.handleAvatarUpload.bind(this));

            /* 🚫 禁用确认离开提示 */
            $(window).off('beforeunload');
            $(document).on('submit', '#shiroki-profile-form', function() {
                $(window).off('beforeunload');
            });
        },

        /**
         * 🔐 处理生成密码
         */
        handleGeneratePassword: function(e) {
            e.preventDefault();

            var $wrapper = $('#shiroki-profile-pw-wrapper');
            var $input = $('#shiroki_profile_pass1');

            /* 📦 显示密码输入区域 */
            $wrapper.addClass('show');

            /* 🔐 生成随机密码 */
            var password = this.generatePassword(24);
            $input.val(password).attr('type', 'text');

            /* 💪 检查密码强度 */
            this.checkPasswordStrength(password);

            /* 📝 更新按钮文本 */
            $(e.currentTarget).text('🔄 重新生成');
        },

        /**
         * 🔐 生成随机密码
         */
        generatePassword: function(length) {
            var charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
            var password = '';
            for (var i = 0; i < length; i++) {
                var randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
            }
            return password;
        },

        /**
         * 👁️ 处理切换密码显示
         */
        handleTogglePassword: function(e) {
            e.preventDefault();
            var $button = $(e.currentTarget);
            var $input = $button.siblings('input');
            var type = $input.attr('type');

            if (type === 'password') {
                $input.attr('type', 'text');
                $button.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'password');
                $button.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        },

        /**
         * 💪 检查密码强度
         */
        checkPasswordStrength: function(password) {
            var strength = 0;
            var $indicator = $('#shiroki-profile-pass-strength');

            if (password.length < 4) {
                $indicator.removeClass('short bad good strong').addClass('short').text('太短');
                return;
            }

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            $indicator.removeClass('short bad good strong');

            if (strength < 2) {
                $indicator.addClass('bad').text('弱');
            } else if (strength < 4) {
                $indicator.addClass('good').text('中等');
            } else {
                $indicator.addClass('strong').text('强');
            }
        },

        /**
         * 💪 初始化密码强度
         */
        initPasswordStrength: function() {
            // 密码强度由 bindEvents 中的 #shiroki_profile_pass1 input 监听实时计算
        },

        /**
         * 🖼️ 初始化头像上传
         */
        initAvatarUpload: function() {
            // 头像上传通过WordPress媒体库处理
        },

        /**
         * 🖼️ 处理头像上传
         */
        handleAvatarUpload: function(e) {
            e.preventDefault();

            var $button = $(e.currentTarget);
            var $avatarPreview = $('#shiroki-profile-avatar-preview');
            var $avatarInput = $('#shiroki_profile_avatar');

            /* 📦 打开WordPress媒体库 */
            var mediaUploader = wp.media({
                title: '选择头像',
                button: {
                    text: '使用此图片'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $avatarPreview.attr('src', attachment.url);
                $avatarInput.val(attachment.url);
            });

            mediaUploader.open();
        },

        /**
         * 📝 初始化表单验证
         */
        initFormValidation: function() {
            // 表单验证逻辑
        },

        /**
         * 📝 处理表单提交
         */
        handleFormSubmit: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $submitBtn = $form.find('.shiroki-profile-submit');
            var originalText = $submitBtn.html();

            /* ⏳ 显示加载状态 */
            $submitBtn.prop('disabled', true).html('💾 保存中...');

            /* 📡 提交表单 */
            $.ajax({
                url: shirokiProfileConfig.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=shiroki_save_profile',
                success: function(response) {
                    if (response.success) {
                        ShirokiUserProfile.showSuccessToast('✅ 个人资料保存成功！');
                    } else {
                        alert('❌ 保存失败：' + (response.data && response.data.message ? response.data.message : '未知错误'));
                    }
                },
                error: function() {
                    alert('❌ 网络错误，请重试');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        },

        /**
         * 🔔 显示成功提示
         */
        showSuccessToast: function(message) {
            var $toast = $('.shiroki-profile-success-toast');
            if ($toast.length === 0) {
                $toast = $('<div class="shiroki-profile-success-toast"><div class="shiroki-profile-success-toast-content"></div></div>');
                $('body').append($toast);
            }

            $toast.find('.shiroki-profile-success-toast-content').text(message);
            $toast.addClass('show');

            setTimeout(function() {
                $toast.removeClass('show');
            }, 3000);
        }
    };

    /**
     * 🚀 初始化个人资料UI
     */
    $(document).ready(function() {
        if ($('.shiroki-profile-wrapper').length > 0) {
            ShirokiUserProfile.init();
        }
    });

})(jQuery);
