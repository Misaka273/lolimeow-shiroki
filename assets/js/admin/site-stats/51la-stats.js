/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🌐 51LA 统计仪表盘交互脚本
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage 51LA_Stats
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 51LA 统计仪表盘控制器
     */
    var Shiroki51LAStats = {

        /**
         * 📊 图表实例
         */
        chart: null,

        /**
         * 🚀 初始化
         */
        init: function() {
            if (!$('.shiroki-51la-dashboard').length) {
                return;
            }

            this.initChart();
            this.bindEvents();
            this.animateNumbers();
            this.startAutoRefresh();
        },

        /**
         * 📈 初始化图表
         */
        initChart: function() {
            var ctx = document.getElementById('shiroki-51la-chart');
            if (!ctx) {
                return;
            }

            var data = window.shiroki51LAData;
            if (!data || !data.labels || data.labels.length === 0) {
                return;
            }

            // 🎨 图表配置
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: '浏览量(PV)',
                            data: data.pv,
                            borderColor: 'oklch(0.7392 0.1154 242.05)',
                            backgroundColor: 'oklch(0.7392 0.1154 242.05 / 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'oklch(0.7392 0.1154 242.05)',
                            pointBorderColor: 'oklch(1 0 0)',
                            pointBorderWidth: 2
                        },
                        {
                            label: '访客数(UV)',
                            data: data.uv,
                            borderColor: 'oklch(0.7096 0.1434 154.53)',
                            backgroundColor: 'oklch(0.7096 0.1434 154.53 / 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'oklch(0.7096 0.1434 154.53)',
                            pointBorderColor: 'oklch(1 0 0)',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'oklch(0 0 0 / 0.8)',
                            titleColor: 'oklch(1 0 0)',
                            bodyColor: 'oklch(1 0 0)',
                            borderColor: 'oklch(1 0 0 / 0.1)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: 'oklch(0 0 0 / 0.5)',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'oklch(0 0 0 / 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: 'oklch(0 0 0 / 0.5)',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000).toFixed(1) + 'k';
                                    }
                                    return value;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // 🌙 暗色模式适配
            this.updateChartTheme();
        },

        /**
         * 🌙 更新图表主题
         */
        updateChartTheme: function() {
            if (!this.chart) {
                return;
            }

            var isDark = $('body').hasClass('wp-dark-mode') || $('.wp-dark-mode').length > 0;
            var textColor = isDark ? 'oklch(1 0 0 / 0.7)' : 'oklch(0 0 0 / 0.5)';
            var gridColor = isDark ? 'oklch(1 0 0 / 0.1)' : 'oklch(0 0 0 / 0.05)';

            this.chart.options.scales.x.ticks.color = textColor;
            this.chart.options.scales.y.ticks.color = textColor;
            this.chart.options.scales.y.grid.color = gridColor;
            this.chart.update();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🌙 监听暗色模式变化 */
            $(document).on('click', '[data-wp-dark-mode-toggle]', function() {
                setTimeout(function() {
                    self.updateChartTheme();
                }, 100);
            });

            /* 🔘 表单提交 */
            $(document).on('submit', '.shiroki-51la-config-form', function(e) {
                // 表单正常提交，页面会刷新
                // 这里可以添加额外的验证逻辑
            });

            /* 🗂️ 标签切换 */
            $(document).on('click', '.shiroki-51la-tab', function(e) {
                e.preventDefault();
                var $tab = $(this);
                var tabId = $tab.data('tab');

                // 🔄 切换标签按钮状态
                $('.shiroki-51la-tab').removeClass('active');
                $tab.addClass('active');

                // 🔄 切换内容显示
                $('.shiroki-51la-tab-content').removeClass('active');
                $('[data-tab-content="' + tabId + '"]').addClass('active');

                // 📈 如果切换到数据概览标签，重新渲染图表
                if (tabId === 'overview' && self.chart) {
                    setTimeout(function() {
                        self.chart.resize();
                    }, 100);
                }

                // 📋 如果切换到访客明细标签，触发动画
                if (tabId === 'visitor-detail') {
                    self.animateExternalStats();
                }
            });

            /* 📅 时间筛选 */
            $(document).on('click', '#time-filter .filter-btn', function() {
                var $btn = $(this);
                var timeRange = $btn.data('time');

                // 🔄 切换按钮状态
                $('#time-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 📅 显示/隐藏自定义日期选择器
                if (timeRange === 'custom') {
                    $('#custom-date-range').slideDown(200);
                } else {
                    $('#custom-date-range').slideUp(200);
                    // 🔄 模拟数据刷新
                    self.refreshExternalData(timeRange);
                }
            });

            /* 📅 应用自定义日期 */
            $(document).on('click', '#apply-custom-date', function() {
                var startDate = $('#date-start').val();
                var endDate = $('#date-end').val();
                if (startDate && endDate) {
                    self.refreshExternalData('custom', startDate, endDate);
                }
            });

            /* 📱 设备类型筛选 - 按钮模式 */
            $(document).on('click', '#device-filter .filter-btn', function() {
                var $btn = $(this);
                var deviceType = $btn.data('device');

                // 🔄 切换按钮状态
                $('#device-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 筛选数据
                self.filterExternalData('device', deviceType);
            });

            /* 👤 访客类型筛选 - 按钮模式 */
            $(document).on('click', '#visitor-filter .filter-btn', function() {
                var $btn = $(this);
                var visitorType = $btn.data('visitor');

                // 🔄 切换按钮状态
                $('#visitor-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 筛选数据
                self.filterExternalData('visitor', visitorType);
            });

            /* 📄 分页 */
            $(document).on('click', '.page-btn:not(:disabled)', function() {
                var $btn = $(this);
                if ($btn.hasClass('active')) return;

                var page = $btn.text();
                if (page === '上一页' || page === '下一页') {
                    // 处理上一页/下一页逻辑
                    return;
                }

                // 🔄 更新分页状态
                $('.page-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 模拟加载数据
                self.loadExternalPage(parseInt(page));
            });

            /* 📥 导出数据 */
            $(document).on('click', '#export-data', function() {
                self.exportExternalData();
            });

            /* 🔗 查看全部外部链接 - 切换到外部链接标签页 */
            $(document).on('click', '.view-more-link', function(e) {
                e.preventDefault();
                var tabId = $(this).data('tab-trigger');

                // 🔄 切换到外部链接标签页
                $('.shiroki-51la-tab').removeClass('active');
                $('.shiroki-51la-tab[data-tab="' + tabId + '"]').addClass('active');

                // 🔄 切换内容显示
                $('.shiroki-51la-tab-content').removeClass('active');
                $('[data-tab-content="' + tabId + '"]').addClass('active');

                // 🎬 触发外部链接统计动画
                self.animateExternalStats();
            });

            /* ⚙️ API 配置卡片折叠/展开 */
            $(document).on('click', '.shiroki-51la-config-header', function(e) {
                // 如果点击的是表单元素，不触发折叠
                if ($(e.target).closest('input, select, button, a').length) {
                    return;
                }

                var $card = $(this).closest('.shiroki-51la-config-card');
                var $icon = $(this).find('.shiroki-51la-toggle-icon');

                if ($card.hasClass('is-collapsed')) {
                    // 展开
                    $card.removeClass('is-collapsed').addClass('is-expanded');
                    $icon.text('▲');
                } else {
                    // 收起
                    $card.removeClass('is-expanded').addClass('is-collapsed');
                    $icon.text('▼');
                }
            });

            /* 📋 访客明细时间筛选 */
            $(document).on('click', '#vd-time-filter .filter-btn', function() {
                var $btn = $(this);
                var period = $btn.data('vd-period');

                $('#vd-time-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                if (period === 'custom') {
                    $('#vd-custom-date').slideDown(200);
                } else {
                    $('#vd-custom-date').slideUp(200);
                    self.loadVisitorDetail(period);
                }
            });

            /* 📋 自定义日期查询 */
            $(document).on('click', '#vd-apply-custom', function() {
                var start = $('#vd-date-start').val();
                var end = $('#vd-date-end').val();
                if (start && end) {
                    self.loadVisitorDetail('custom', start, end);
                }
            });
        },

        /**
         * 🔗 外部链接统计数字动画
         */
        animateExternalStats: function() {
            var self = this;

            $('#external-link-count, #external-ip-count, #external-pv-count, #external-uv-count, #external-new-visitor-count, #external-session-count').each(function() {
                var $this = $(this);
                var text = $this.text().replace(/,/g, '');
                var target = parseInt(text) || 0;

                if (target > 0 && !$this.hasClass('animated')) {
                    $this.addClass('animated');
                    $this.text('0');
                    setTimeout(function() {
                        self.animateNumber($this, target);
                    }, 100);
                }
            });
        },

        /**
         * 🔄 刷新外部链接数据
         */
        refreshExternalData: function(timeRange, startDate, endDate) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟数据刷新
                setTimeout(function() {
                    // 📝 这里可以添加 AJAX 请求获取真实数据
                    self.animateExternalStats();
                    $('#external-links-table tbody').fadeIn(200);
                }, 300);
            });
        },

        /**
         * 🔍 筛选外部链接数据
         */
        filterExternalData: function(filterType, filterValue) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟筛选
                setTimeout(function() {
                    // 📝 这里可以添加筛选逻辑
                    $('#external-links-table tbody').fadeIn(200);
                }, 200);
            });
        },

        /**
         * 📄 加载外部链接分页数据
         */
        loadExternalPage: function(page) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟分页加载
                setTimeout(function() {
                    // 📝 这里可以添加 AJAX 请求获取分页数据
                    // 更新分页信息
                    var startItem = (page - 1) * 10 + 1;
                    var endItem = Math.min(page * 10, 156);
                    $('#page-start').text(startItem);
                    $('#page-end').text(endItem);

                    $('#external-links-table tbody').fadeIn(200);
                }, 300);
            });
        },

        /**
         * 📥 导出外部链接数据
         */
        exportExternalData: function() {
            // 📝 这里可以添加导出逻辑
            alert('数据导出功能开发中...');
        },

        /**
         * 🔢 数字动画
         */
        animateNumbers: function() {
            var self = this;

            $('.stat-value').each(function() {
                var $this = $(this);
                var target = parseInt($this.text().replace(/,/g, '')) || 0;

                if (target > 0) {
                    $this.text('0');
                    setTimeout(function() {
                        self.animateNumber($this, target);
                    }, 300);
                }
            });
        },

        /**
         * 🔢 单个数字动画
         */
        animateNumber: function($element, targetValue) {
            var currentValue = 0;
            var duration = 800;
            var startTime = null;

            $element.addClass('changing');

            function animate(currentTime) {
                if (!startTime) {
                    startTime = currentTime;
                }

                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);

                // 🎯 缓动函数
                var easeOutQuart = 1 - Math.pow(1 - progress, 4);

                var current = Math.floor(targetValue * easeOutQuart);
                $element.text(current.toLocaleString());

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    $element.removeClass('changing');
                }
            }

            requestAnimationFrame(animate);
        },

        /**
         * ⏱️ 启动自动刷新（每5分钟）
         */
        startAutoRefresh: function() {
            var self = this;
            setInterval(function() {
                self.refreshOverviewData();
            }, 5 * 60 * 1000);
        },

        /**
         * 🔄 刷新概览 + 实时数据
         */
        refreshOverviewData: function() {
            if (typeof shiroki51LAConfig === 'undefined') return;

            $.ajax({
                url: shiroki51LAConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_51la_refresh',
                    nonce: shiroki51LAConfig.nonce
                },
                success: function(resp) {
                    if (!resp.success || !resp.data) return;
                    var d = resp.data;

                    // 更新概览数据
                    if (d.overview) {
                        var fields = {
                            '#51la-today-pv':  d.overview.curPv,
                            '#51la-today-uv':  d.overview.curUv,
                            '#51la-month-pv':  d.overview.monthPv,
                            '#51la-total-pv':  d.overview.totalPv
                        };
                        $.each(fields, function(sel, val) {
                            if (val !== undefined && val !== null) {
                                $(sel).text(Number(val).toLocaleString());
                            }
                        });
                    }

                    // 更新实时访客数（向上渐显/渐隐动画）
                    if (d.realtime && d.realtime.totalCount !== undefined) {
                        var newVal = Number(d.realtime.totalCount).toLocaleString();
                        var $el = $('.realtime-number');
                        if ($el.length && $el.text().trim() !== newVal) {
                            var h = $el.outerHeight();
                            $el.css({ position: 'relative', overflow: 'hidden', height: h + 'px' });
                            var $old = $('<span>').css({
                                position: 'absolute', left: 0, right: 0, top: 0,
                                textAlign: 'center', lineHeight: h + 'px'
                            }).text($el.text().trim());
                            var $new = $('<span>').css({
                                position: 'absolute', left: 0, right: 0,
                                top: h + 'px', textAlign: 'center', lineHeight: h + 'px'
                            }).text(newVal);
                            $el.empty().append($old).append($new);
                            $old.animate({ top: -h + 'px', opacity: 0 }, 500, function() { $(this).remove(); });
                            $new.animate({ top: '0px', opacity: 1 }, 500, function() {
                                $el.text(newVal).css({ position: '', overflow: '', height: '' });
                            });
                        }
                    }
                }
            });
        },

        /**
         * 📋 加载访客明细
         */
        loadVisitorDetail: function(period, startDate, endDate) {
            if (typeof shiroki51LAConfig === 'undefined') return;

            var $wrapper = $('#vd-table-wrapper');
            var $label = $('#vd-period-label');
            $wrapper.css('opacity', 0.5);

            var labels = { today: '今日', yesterday: '昨日', '7days': '最近7日', month: '本月', custom: '自定义' };
            $label.text((labels[period] || '') + '访问记录');

            var postData = {
                action: 'shiroki_51la_visitor_detail',
                nonce: shiroki51LAConfig.nonce,
                period: period
            };
            if (period === 'custom' && startDate && endDate) {
                postData.start_date = startDate;
                postData.end_date = endDate;
            }

            $.ajax({
                url: shiroki51LAConfig.ajaxUrl,
                type: 'POST',
                data: postData,
                success: function(resp) {
                    $wrapper.css('opacity', 1);
                    if (!resp.success || !resp.data) {
                        $wrapper.html('<div class="shiroki-51la-empty"><p>暂无访客数据</p></div>');
                        return;
                    }
                    var records = resp.data.records || [];
                    if (records.length === 0) {
                        $wrapper.html('<div class="shiroki-51la-empty"><p>暂无访客数据</p></div>');
                        return;
                    }

                    var html = '<table class="shiroki-51la-data-table shiroki-51la-visitor-table">';
                    html += '<thead><tr><th class="col-time">时间</th><th class="col-region">地区</th>';
                    html += '<th class="col-type">访客类型</th><th class="col-ip">IP</th>';
                    html += '<th class="col-src">来路</th><th class="col-entry">入口页</th>';
                    html += '<th class="col-browser">浏览器</th><th class="col-pv">PV</th></tr></thead><tbody>';

                    for (var i = 0; i < records.length; i++) {
                        var v = records[i];
                        var time = v.time || v.dateTime || '--';
                        var region = v.region || '--';
                        var vtype = v.visitorType || '--';
                        var ip = v.ip || '--';
                        var src = v.srcUrl || '';
                        var entry = v.entryPage || '--';
                        var browser = v.browser || '--';
                        var pv = v.pv || 0;
                        var badgeClass = vtype === '新访客' ? 'new' : 'returning';
                        var srcHtml = src ? '<a href="' + src + '" target="_blank" class="src-link" title="' + src + '">' + (src.length > 40 ? src.substring(0, 40) + '...' : src) + '</a>' : '<span class="text-muted">直接访问</span>';

                        html += '<tr>';
                        html += '<td class="col-time">' + time + '</td>';
                        html += '<td class="col-region">' + region + '</td>';
                        html += '<td class="col-type"><span class="visitor-badge ' + badgeClass + '">' + vtype + '</span></td>';
                        html += '<td class="col-ip"><code>' + ip + '</code></td>';
                        html += '<td class="col-src">' + srcHtml + '</td>';
                        html += '<td class="col-entry">' + (entry.length > 35 ? entry.substring(0, 35) + '...' : entry) + '</td>';
                        html += '<td class="col-browser">' + browser + '</td>';
                        html += '<td class="col-pv">' + pv + '</td>';
                        html += '</tr>';
                    }

                    html += '</tbody></table>';
                    html += '<div class="shiroki-51la-pagination"><div class="pagination-info">共 ' + resp.data.total + ' 条记录</div></div>';
                    $wrapper.html(html);
                },
                error: function() {
                    $wrapper.css('opacity', 1);
                }
            });
        },

        /**
         * 📡 AJAX 获取数据
         */
        fetchData: function(endpoint, params) {
            var self = this;

            return $.ajax({
                url: shiroki51LAConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_51la_get_data',
                    endpoint: endpoint,
                    params: params,
                    nonce: shiroki51LAConfig.nonce
                }
            });
        },

        /**
         * 🔔 显示提示消息
         */
        showToast: function(message, type) {
            var $toast = $('.shiroki-51la-toast');
            if ($toast.length === 0) {
                $toast = $('<div class="shiroki-51la-toast"><div class="toast-content"></div></div>');
                $('body').append($toast);
            }

            $toast.removeClass('success error').addClass(type);
            $toast.find('.toast-content').text(message);
            $toast.fadeIn(300);

            setTimeout(function() {
                $toast.fadeOut(300);
            }, 3000);
        }
    };

    /**
     * 🚀 初始化 51LA 统计仪表盘
     */
    $(document).ready(function() {
        Shiroki51LAStats.init();
    });

})(jQuery);
