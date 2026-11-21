/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * ➕ 添加用户页面交互脚本
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage User_Add
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 添加用户UI控制器
     */
    var ShirokiUserAdd = {

        /**
         * 🚀 初始化
         */
        init: function() {
            this.bindEvents();
            this.initPasswordStrength();
            this.initLangSelection();
            this.initRoleSelection();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🔐 生成密码按钮 */
            $(document).on('click', '#shiroki-generate-pw', this.handleGeneratePassword.bind(this));

            /* 👁️ 切换密码显示 */
            $(document).on('click', '#shiroki-toggle-pw', this.handleTogglePassword.bind(this));
            $(document).on('click', '#shiroki-toggle-pw2', this.handleTogglePassword2.bind(this));

            /* 🔐 密码输入监听 */
            $(document).on('input', '#shiroki_pass1, #shiroki_pass2', function() {
                self.checkPasswordMatch();
            });

            /* 🌐 语言选择 */
            $(document).on('click', '.shiroki-user-add-lang-option', this.handleLangSelection.bind(this));

            /* 🎭 角色选择 */
            $(document).on('click', '.shiroki-user-add-role-option', this.handleRoleSelection.bind(this));

            /* 📧 通知复选框 */
            $(document).on('change', '#shiroki_send_notification', this.handleNotificationToggle.bind(this));

            /* 📝 表单提交 */
            $(document).on('submit', '#shiroki-createuser', this.handleFormSubmit.bind(this));
            $(document).on('submit', '#shiroki-adduser', this.handleAddUserSubmit.bind(this));

            /* 🚫 禁用确认离开提示 */
            $(window).off('beforeunload');
            $(document).on('submit', '#shiroki-createuser, #shiroki-adduser', function() {
                $(window).off('beforeunload');
            });
        },

        /**
         * 🔐 处理生成密码
         */
        handleGeneratePassword: function(e) {
            e.preventDefault();

            var $wrapper = $('#shiroki-pw-wrapper');
            var $input = $('#shiroki_pass1');

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
            var $input = $('#shiroki_pass1');
            var isVisible = $input.attr('type') === 'text';

            if (isVisible) {
                $input.attr('type', 'password');
                $button.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'text');
                $button.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        },

        /**
         * 👁️ 处理切换确认密码显示
         */
        handleTogglePassword2: function(e) {
            e.preventDefault();

            var $button = $(e.currentTarget);
            var $input = $('#shiroki_pass2');
            var isVisible = $input.attr('type') === 'text';

            if (isVisible) {
                $input.attr('type', 'password');
                $button.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'text');
                $button.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        },

        /**
         * 🔐 检查密码是否匹配
         */
        checkPasswordMatch: function() {
            var pass1 = $('#shiroki_pass1').val();
            var pass2 = $('#shiroki_pass2').val();
            var $result = $('#shiroki-pass-match-result');

            if (!pass2) {
                $result.text('').removeClass('match mismatch');
                return;
            }

            if (pass1 === pass2) {
                $result.text('✅ 密码匹配').removeClass('mismatch').addClass('match');
            } else {
                $result.text('❌ 密码不匹配').removeClass('match').addClass('mismatch');
            }
        },

        /**
         * 💪 初始化密码强度检测
         */
        initPasswordStrength: function() {
            var self = this;

            $(document).on('input', '#shiroki_pass1', function() {
                var password = $(this).val();
                self.checkPasswordStrength(password);
            });
        },

        /**
         * 💪 检查密码强度
         */
        checkPasswordStrength: function(password) {
            var $result = $('#shiroki-pass-strength-result');
            var $weakCheckbox = $('#shiroki-pw-weak');

            if (!password) {
                $result.removeClass('short bad good strong').text('');
                $weakCheckbox.hide();
                return;
            }

            var strength = this.calculatePasswordStrength(password);
            var strengthClass = '';
            var strengthText = '';

            if (strength < 2) {
                strengthClass = 'short';
                strengthText = '⚠️ 太短';
            } else if (strength < 3) {
                strengthClass = 'bad';
                strengthText = '👎 弱';
            } else if (strength < 4) {
                strengthClass = 'good';
                strengthText = '👍 中等';
            } else {
                strengthClass = 'strong';
                strengthText = '💪 强';
            }

            $result.removeClass('short bad good strong').addClass(strengthClass).text(strengthText);

            /* ⚠️ 显示弱密码确认 */
            if (strength < 3) {
                $weakCheckbox.show();
            } else {
                $weakCheckbox.hide();
            }
        },

        /**
         * 🔢 计算密码强度
         */
        calculatePasswordStrength: function(password) {
            var strength = 0;

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            return Math.min(strength, 4);
        },

        /**
         * 🌐 初始化语言选择
         */
        initLangSelection: function() {
            /* ✅ 默认选中第一个 */
            var $options = $('.shiroki-user-add-lang-option');
            if ($options.length > 0 && !$options.hasClass('selected')) {
                $options.first().addClass('selected').find('input[type="radio"]').prop('checked', true);
            }
        },

        /**
         * 🌐 处理语言选择
         */
        handleLangSelection: function(e) {
            var $option = $(e.currentTarget);
            var $radio = $option.find('input[type="radio"]');

            /* 🔄 移除其他选中状态 */
            $('.shiroki-user-add-lang-option').removeClass('selected');
            $('.shiroki-user-add-lang-option input[type="radio"]').prop('checked', false);

            /* ✅ 设置当前选中 */
            $option.addClass('selected');
            $radio.prop('checked', true);
        },

        /**
         * 🎭 初始化角色选择
         */
        initRoleSelection: function() {
            /* ✅ 默认选中第一个 */
            var $options = $('.shiroki-user-add-role-option');
            if ($options.length > 0 && !$options.hasClass('selected')) {
                $options.first().addClass('selected').find('input[type="radio"]').prop('checked', true);
            }
        },

        /**
         * 🎭 处理角色选择
         */
        handleRoleSelection: function(e) {
            var $option = $(e.currentTarget);
            var $radio = $option.find('input[type="radio"]');

            /* 🔄 移除其他选中状态 */
            $('.shiroki-user-add-role-option').removeClass('selected');
            $('.shiroki-user-add-role-option input[type="radio"]').prop('checked', false);

            /* ✅ 设置当前选中 */
            $option.addClass('selected');
            $radio.prop('checked', true);
        },

        /**
         * 📧 处理通知开关
         */
        handleNotificationToggle: function(e) {
            var isChecked = $(e.currentTarget).is(':checked');
            /* 📝 可以在这里添加额外的逻辑 */
            console.log('发送通知:', isChecked ? '开启' : '关闭');
        },

        /**
         * 📝 处理表单提交
         */
        handleFormSubmit: function(e) {
            var $form = $(e.currentTarget);
            var isValid = true;
            var errors = [];

            /* ✏️ 验证必填字段 */
            var username = $('#shiroki_user_login').val().trim();
            var email = $('#shiroki_email').val().trim();
            var password = $('#shiroki_pass1').val();
            var password2 = $('#shiroki_pass2').val();

            if (!username) {
                isValid = false;
                errors.push('请输入用户名');
                $('#shiroki_user_login').closest('.shiroki-user-add-field').addClass('error');
            }

            if (!email) {
                isValid = false;
                errors.push('请输入电子邮箱');
                $('#shiroki_email').closest('.shiroki-user-add-field').addClass('error');
            } else if (!this.isValidEmail(email)) {
                isValid = false;
                errors.push('请输入有效的电子邮箱');
                $('#shiroki_email').closest('.shiroki-user-add-field').addClass('error');
            }

            /* 🔐 验证密码 */
            if (!password) {
                isValid = false;
                errors.push('请输入密码');
            }

            /* 🔐 验证密码匹配 */
            if (password !== password2) {
                isValid = false;
                errors.push('两次输入的密码不匹配');
                $('#shiroki_pass2').closest('.shiroki-user-add-password-confirm-wrapper').addClass('error');
            }

            /* ⚠️ 检查弱密码确认 */
            var strength = this.calculatePasswordStrength(password);
            if (strength < 3) {
                var $weakCheckbox = $('#shiroki-pw-weak input[type="checkbox"]');
                if (!$weakCheckbox.is(':checked')) {
                    isValid = false;
                    errors.push('密码强度较弱，请确认使用弱密码');
                }
            }

            if (!isValid) {
                e.preventDefault();
                this.showErrors(errors);
                return false;
            }

            /* 🚀 同步到原生表单字段 */
            this.syncToNativeForm($form);
        },

        /**
         * 🔗 处理添加现有用户表单提交
         */
        handleAddUserSubmit: function(e) {
            var $form = $(e.currentTarget);
            var email = $('#shiroki_adduser_email').val().trim();

            if (!email) {
                e.preventDefault();
                this.showErrors(['请输入电子邮箱或用户名']);
                return false;
            }

            /* 🚀 同步到原生表单字段 */
            this.syncAddUserToNativeForm($form);
        },

        /**
         * 📧 验证邮箱格式
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * ⚠️ 显示错误信息
         */
        showErrors: function(errors) {
            /* 🗑️ 移除旧错误提示 */
            $('.shiroki-user-add-error-notice').remove();

            /* 📦 创建错误提示 */
            var errorHtml = '<div class="shiroki-user-add-error-notice" style="' +
                'background: var(--admin-danger-bg);' +
                'border: 1px solid var(--admin-danger-border);' +
                'border-radius: var(--admin-radius-md);' +
                'padding: var(--admin-space-md) var(--admin-space-lg);' +
                'margin-bottom: var(--admin-space-lg);' +
                'color: var(--admin-danger-text);' +
                'font-size: var(--admin-font-size-md);' +
                '">' +
                '<strong>❌ 请修正以下错误：</strong>' +
                '<ul style="margin: var(--admin-space-sm) 0 0 0; padding-left: var(--admin-space-lg);">';

            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });

            errorHtml += '</ul></div>';

            /* 📦 插入错误提示 */
            $('.shiroki-user-add-content').prepend(errorHtml);

            /* 🎬 滚动到错误位置 */
            $('html, body').animate({
                scrollTop: $('.shiroki-user-add-error-notice').offset().top - 100
            }, 300);
        },

        /**
         * 🔄 同步到原生创建用户表单
         */
        syncToNativeForm: function($customForm) {
            /* 📝 同步字段值到原生表单 */
            var pass1 = $('#shiroki_pass1').val();
            var pass2 = $('#shiroki_pass2').val();

            var fields = {
                'user_login': $('#shiroki_user_login').val(),
                'email': $('#shiroki_email').val(),
                'first_name': $('#shiroki_first_name').val(),
                'last_name': $('#shiroki_last_name').val(),
                'url': $('#shiroki_url').val(),
                'pass1': pass1,
                'pass2': pass2 || pass1,
                'role': $('input[name="role"]:checked').val(),
                'send_user_notification': $('#shiroki_send_notification').is(':checked') ? '1' : ''
            };

            /* 📝 设置原生表单字段值 */
            $.each(fields, function(name, value) {
                var $nativeField = $('#createuser [name="' + name + '"]');
                if ($nativeField.length) {
                    if ($nativeField.is(':checkbox')) {
                        $nativeField.prop('checked', value === '1');
                    } else if ($nativeField.is('select')) {
                        $nativeField.val(value);
                    } else {
                        $nativeField.val(value);
                    }
                }
            });

            /* 🌐 同步语言选择 */
            var localeValue = $('input[name="locale"]:checked').val();
            if (localeValue) {
                $('#createuser [name="locale"]').val(localeValue);
            }

            /* 📝 同步弱密码确认 */
            var $weakCheckbox = $('#shiroki_pw_weak');
            if ($weakCheckbox.length && $weakCheckbox.is(':checked')) {
                $('#createuser input[name="pw_weak"]').prop('checked', true);
            }
        },

        /**
         * 🔄 同步到原生添加用户表单
         */
        syncAddUserToNativeForm: function($customForm) {
            var email = $('#shiroki_adduser_email').val();
            var role = $('#shiroki_adduser_role').val();
            var noconfirmation = $('#shiroki_adduser_noconfirmation').is(':checked');

            /* 📝 设置原生表单字段值 */
            $('oklch(0.8622 0.0525 196.22)user [name="email"]').val(email);
            $('oklch(0.8622 0.0525 196.22)user [name="role"]').val(role);
            $('oklch(0.8622 0.0525 196.22)user [name="noconfirmation"]').prop('checked', noconfirmation);
        }
    };

    /**
     * 🚀 页面加载完成后初始化
     */
    $(document).on('shiroki-user-add-ready', function() {
        ShirokiUserAdd.init();
    });

    /* 🔄 如果事件已经触发，直接初始化 */
    if ($('.shiroki-user-add-wrapper').length > 0) {
        ShirokiUserAdd.init();
    }

})(jQuery);
