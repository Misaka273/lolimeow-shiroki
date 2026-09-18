// 📋 统一复制功能脚本
document.addEventListener('DOMContentLoaded', function() {
    function showCopyBanner(message, iconClass) {
        iconClass = iconClass || 'fa-check-circle';
        var banner = document.querySelector('.copy-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.className = 'copy-banner';
            document.body.appendChild(banner);
        }
        if (!banner.dataset.defaultText && banner.innerHTML.trim() !== '') {
            banner.dataset.defaultText = banner.innerHTML;
        }
        banner.innerHTML = '<i class="fa ' + iconClass + '"></i> ' + message;
        if (typeof window._copyBannerShow === 'function') {
            window._copyBannerShow();
        } else {
            banner.classList.remove('mask-run', 'show');
            void banner.offsetWidth;
            banner.classList.add('mask-run', 'show');
            setTimeout(function() {
                banner.classList.remove('show', 'mask-run');
            }, 1500);
        }
        setTimeout(function() {
            if (banner.dataset.defaultText) {
                banner.innerHTML = banner.dataset.defaultText;
            }
        }, 2000);
    }

    function getSiteInfoCopyMessage(copyBtn, copyText) {
        var label = copyBtn.getAttribute('data-copy-label');
        if (label) {
            return '已复制「' + label + '」';
        }
        return '已复制：' + copyText;
    }

    document.addEventListener('click', function(e) {
        var copyBtn = e.target.closest('.copy-btn');
        if (!copyBtn) return;

        e.preventDefault();
        e.stopPropagation();

        var copyText = copyBtn.getAttribute('data-copy-text');
        if (!copyText) return;

        var isSiteInfo = copyBtn.closest('.shiroki-site-info, .fl-site-info');

        function copySuccess() {
            if (isSiteInfo) {
                showCopyBanner(getSiteInfoCopyMessage(copyBtn, copyText));
                return;
            }
            if (typeof showToast === 'function') {
                showToast(copyText, true);
            } else {
                alert('已复制：' + copyText);
            }
        }

        function copyFail() {
            if (isSiteInfo) {
                showCopyBanner('复制失败，请手动复制', 'fa-times-circle');
            } else {
                alert('复制失败，请手动复制');
            }
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(copyText)
                .then(copySuccess)
                .catch(function(err) {
                    console.error('Clipboard API error:', err);
                    fallbackCopyTextToClipboard(copyText, copySuccess, copyFail);
                });
        } else {
            fallbackCopyTextToClipboard(copyText, copySuccess, copyFail);
        }
    });

    function fallbackCopyTextToClipboard(text, successCallback, failCallback) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            if (document.execCommand('copy')) {
                successCallback();
            } else {
                failCallback();
            }
        } catch (err) {
            console.error('execCommand error:', err);
            failCallback();
        } finally {
            document.body.removeChild(textArea);
        }
    }
});
