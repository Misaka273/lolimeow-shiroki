/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🔑 个人资料页面 - 应用程序密码
 * 🎨 拟态拟物玻璃质感设计
 *
 * 依赖 shirokiAppPasswords（由 fun-admin.php 通过 wp_localize_script 输出）：
 *   restBase / nonce / timezone / available / localBypass / items
 *
 * @package Lolimeow_Shiroki
 * @subpackage User_Profile
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    var cfg = window.shirokiAppPasswords || null;
    var state = {
        items: [],
        busy: false
    };

    /**
     * 🚀 初始化
     */
    function init() {
        if (!cfg || !cfg.available) {
            return;
        }

        var $card = $('#shiroki-app-passwords');
        if (!$card.length) {
            return;
        }

        var $list = $('#shiroki-ap-list');
        var $empty = $('#shiroki-ap-empty');
        var $head = $('#shiroki-ap-list-head');
        var $name = $('#shiroki-ap-name');
        var $createBtn = $('#shiroki-ap-create-btn');
        var $notice = $('#shiroki-ap-notice');
        var $new = $('#shiroki-ap-new');
        var $revokeAll = $('#shiroki-ap-revoke-all');

        var createLabel = $createBtn.text();

        state.items = (cfg.items || []).slice();

        /* 🕒 把 REST 返回的 UTC 时间格式化到站点时区 */
        function parseDate(value) {
            if (!value) {
                return null;
            }

            var text = String(value);

            // REST 返回的 created / last_used 不带时区，按 GMT 处理
            if (!/(Z|[+-]\d{2}:?\d{2})$/.test(text)) {
                text += 'Z';
            }

            var date = new Date(text);
            return isNaN(date.getTime()) ? null : date;
        }

        function formatDate(value) {
            var date = parseDate(value);
            if (!date) {
                return '—';
            }

            var options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };

            if (cfg.timezone) {
                options.timeZone = cfg.timezone;
            }

            try {
                return new Intl.DateTimeFormat('zh-CN', options).format(date);
            } catch (e) {
                delete options.timeZone;
                try {
                    return new Intl.DateTimeFormat('zh-CN', options).format(date);
                } catch (e2) {
                    return date.toLocaleString();
                }
            }
        }

        function toggle($el, visible) {
            $el.toggleClass('is-hidden', !visible);
        }

        /**
         * 🧱 渲染一条应用程序密码
         */
        function buildRow(item) {
            var $row = $('<div class="shiroki-ap-item"></div>').attr('data-uuid', item.uuid || '');

            $('<span class="shiroki-ap-item-icon" aria-hidden="true">🔑</span>').appendTo($row);

            var $body = $('<div class="shiroki-ap-item-body"></div>');

            $('<div class="shiroki-ap-item-name"></div>')
                .text(item.name || '(未命名)')
                .appendTo($body);

            var $meta = $('<div class="shiroki-ap-item-meta"></div>');

            $('<span class="shiroki-ap-meta"></span>')
                .text('创建于 ' + formatDate(item.created))
                .appendTo($meta);

            $('<span class="shiroki-ap-meta"></span>')
                .text(item.last_used ? '最后使用 ' + formatDate(item.last_used) : '从未使用')
                .appendTo($meta);

            if (item.last_ip) {
                $('<span class="shiroki-ap-meta"></span>')
                    .text('IP ' + item.last_ip)
                    .appendTo($meta);
            }

            $meta.appendTo($body);
            $body.appendTo($row);

            $('<button type="button" class="shiroki-ap-revoke">撤销</button>')
                .attr('aria-label', '撤销应用程序密码：' + (item.name || ''))
                .appendTo($row);

            return $row;
        }

        function render() {
            $list.empty();

            if (!state.items.length) {
                toggle($head, false);
                toggle($empty, true);
                return;
            }

            toggle($head, true);
            toggle($empty, false);

            state.items.forEach(function (item) {
                $list.append(buildRow(item));
            });

            toggle($revokeAll, state.items.length > 1);
        }

        function setBusy(busy) {
            state.busy = busy;
            $createBtn
                .prop('disabled', busy)
                .text(busy ? '⏳ 添加中…' : createLabel);
        }

        function showNotice(message, type) {
            $notice
                .removeClass('is-error is-success')
                .addClass(type === 'error' ? 'is-error' : 'is-success')
                .text(message);
            toggle($notice, true);
        }

        function hideNotice() {
            $notice.text('').removeClass('is-error is-success');
            toggle($notice, false);
        }

        function errorMessage(xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                return xhr.responseJSON.message;
            }

            if (xhr && xhr.status === 403) {
                return '安全校验失败，请刷新页面后重试。';
            }

            if (xhr && xhr.status === 501) {
                return '当前环境未启用应用程序密码。';
            }

            return '请求失败，请稍后重试。';
        }

        /**
         * 📡 调用应用程序密码 REST 接口
         */
        function request(method, suffix, data) {
            return $.ajax({
                url: cfg.restBase + (suffix || ''),
                method: method,
                dataType: 'json',
                contentType: data ? 'application/json; charset=utf-8' : undefined,
                data: data ? JSON.stringify(data) : undefined,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', cfg.nonce);
                }
            });
        }

        /**
         * 📋 复制文本（优先使用剪贴板 API，失败时回退）
         */
        function legacyCopy(text) {
            return new Promise(function (resolve, reject) {
                var $temp = $('<textarea></textarea>')
                    .val(text)
                    .css({
                        position: 'fixed',
                        top: '-1000px',
                        left: '-1000px'
                    })
                    .appendTo('body');

                var input = $temp[0];
                input.focus();
                input.select();

                try {
                    var ok = document.execCommand('copy');
                    $temp.remove();
                    ok ? resolve() : reject(new Error('copy failed'));
                } catch (error) {
                    $temp.remove();
                    reject(error);
                }
            });
        }

        function copyText(text) {
            if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
                return navigator.clipboard.writeText(text).catch(function () {
                    return legacyCopy(text);
                });
            }

            return legacyCopy(text);
        }

        /**
         * ✅ 创建成功后展示一次性密码
         */
        function showNewPassword(response) {
            $new.empty();

            $('<div class="shiroki-ap-new-title"></div>')
                .text('✅ 「' + (response.name || '') + '」的应用程序密码已生成')
                .appendTo($new);

            var $row = $('<div class="shiroki-ap-password-row"></div>');

            var $input = $('<input type="text" readonly="readonly" class="shiroki-ap-password">')
                .val(response.password || '');

            var $copy = $('<button type="button" class="shiroki-ap-copy">复制</button>');

            $copy.on('click', function () {
                copyText($input.val()).then(function () {
                    $copy.text('已复制 ✓');
                    setTimeout(function () {
                        $copy.text('复制');
                    }, 2000);
                }).catch(function () {
                    $input.trigger('focus').trigger('select');
                });
            });

            $row.append($input, $copy);
            $row.appendTo($new);

            $('<p class="shiroki-ap-new-hint"></p>')
                .text('请立即复制并保存，关闭页面后无法再次查看该密码。')
                .appendTo($new);

            toggle($new, true);

            $input.trigger('focus').trigger('select');
        }

        /* ➕ 添加应用程序密码 */
        $createBtn.on('click', function () {
            if (state.busy) {
                return;
            }

            var name = String($name.val() || '').trim();

            if (!name) {
                showNotice('请先填写应用名称。', 'error');
                $name.trigger('focus');
                return;
            }

            hideNotice();
            setBusy(true);

            request('POST', '', { name: name })
                .done(function (response) {
                    $name.val('');
                    state.items.unshift(response);
                    render();
                    showNewPassword(response);
                })
                .fail(function (xhr) {
                    showNotice(errorMessage(xhr), 'error');
                })
                .always(function () {
                    setBusy(false);
                });
        });

        /* ↵ 应用名称输入框回车即创建 */
        $name.on('keydown', function (event) {
            if (event.which === 13 || event.keyCode === 13) {
                event.preventDefault();
                $createBtn.trigger('click');
            }
        });

        /* 🗑️ 撤销单条应用程序密码 */
        $list.on('click', '.shiroki-ap-revoke', function () {
            if (state.busy) {
                return;
            }

            var $button = $(this);
            var $item = $button.closest('.shiroki-ap-item');
            var uuid = $item.attr('data-uuid');

            if (!uuid) {
                return;
            }

            if (!window.confirm('确定撤销这个应用程序密码吗？使用它的应用将立即无法访问。')) {
                return;
            }

            hideNotice();
            $button.prop('disabled', true).text('撤销中…');

            request('DELETE', '/' + encodeURIComponent(uuid))
                .done(function () {
                    state.items = state.items.filter(function (item) {
                        return item.uuid !== uuid;
                    });

                    render();
                    toggle($new, false);
                    $new.empty();
                    showNotice('应用程序密码已撤销。', 'success');
                })
                .fail(function (xhr) {
                    showNotice(errorMessage(xhr), 'error');
                    $button.prop('disabled', false).text('撤销');
                });
        });

        /* 🧹 撤销全部 */
        $revokeAll.on('click', function () {
            if (state.busy || !state.items.length) {
                return;
            }

            if (!window.confirm('确定撤销全部 ' + state.items.length + ' 个应用程序密码吗？此操作无法恢复。')) {
                return;
            }

            var $button = $(this);
            hideNotice();
            $button.prop('disabled', true).text('撤销中…');

            request('DELETE', '')
                .done(function (response) {
                    if (response && response.deleted) {
                        state.items = [];
                        render();
                        toggle($new, false);
                        $new.empty();
                        showNotice('全部应用程序密码已撤销。', 'success');
                    }
                })
                .fail(function (xhr) {
                    showNotice(errorMessage(xhr), 'error');
                })
                .always(function () {
                    $button.prop('disabled', false).text('撤销全部');
                });
        });

        /* 🎬 首次渲染 */
        render();
    }

    $(document).ready(init);

})(jQuery);
