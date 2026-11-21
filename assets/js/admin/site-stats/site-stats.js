/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📊 站点数据统计仪表盘交互脚本
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Site_Stats
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 统计仪表盘控制器
     */
    var ShirokiSiteStats = {

        /**
         * 📊 图表实例
         */
        chart: null,

        /**
         * 🚀 初始化
         */
        init: function() {
            if (!$('.shiroki-stats-dashboard').length) {
                return;
            }

            this.initChart();
            this.bindEvents();
            this.animateNumbers();
        },

        /**
         * 📈 初始化图表
         */
        initChart: function() {
            var ctx = document.getElementById('shiroki-stats-chart');
            if (!ctx) {
                return;
            }

            var data = window.shirokiStatsData;
            if (!data) {
                return;
            }

            // 🎨 图表配置
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels.map(function(date) {
                        var d = new Date(date);
                        return (d.getMonth() + 1) + '/' + d.getDate();
                    }),
                    datasets: [
                        {
                            label: '阅读量',
                            data: data.views,
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
                            label: '访问量',
                            data: data.visits,
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
                        },
                        {
                            label: '下载量',
                            data: data.downloads,
                            borderColor: 'oklch(0.7244 0.1534 56.07)',
                            backgroundColor: 'oklch(0.7244 0.1534 56.07 / 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'oklch(0.7244 0.1534 56.07)',
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

            /* 🔘 时间筛选器 */
            $(document).on('click', '.shiroki-stats-filter-btn', function() {
                var $btn = $(this);
                var period = $btn.data('period');

                /* 🎯 更新按钮状态 */
                $('.shiroki-stats-filter-btn').removeClass('active');
                $btn.addClass('active');

                /* 📊 更新统计数据 */
                self.updateStats(period);
            });

            /* 🌙 监听暗色模式变化 */
            $(document).on('click', '[data-wp-dark-mode-toggle]', function() {
                setTimeout(function() {
                    self.updateChartTheme();
                }, 100);
            });
        },

        /**
         * 📊 更新统计数据
         */
        updateStats: function(period) {
            var self = this;
            var data = window.shirokiStatsData;

            if (!data || !data.periods || !data.periods[period]) {
                return;
            }

            var periodData = data.periods[period];

            /* 🎬 数字动画更新 */
            this.animateNumber($('#stat-views'), periodData.views);
            this.animateNumber($('#stat-visits'), periodData.visits);
            this.animateNumber($('#stat-downloads'), periodData.downloads);

            /* 📈 更新变化指示器 */
            this.updateChangeIndicator(period);
        },

        /**
         * 🔢 数字动画
         */
        animateNumber: function($element, targetValue) {
            var currentValue = parseInt($element.text().replace(/,/g, '')) || 0;
            var duration = 500;
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

                var current = Math.floor(currentValue + (targetValue - currentValue) * easeOutQuart);
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
         * 📈 更新变化指示器
         */
        updateChangeIndicator: function(period) {
            var data = window.shirokiStatsData;
            if (!data || !data.periods) {
                return;
            }

            var current = data.periods[period];
            var previous = this.getPreviousPeriodData(period);

            if (!previous) {
                return;
            }

            // 📊 阅读量变化
            this.updateIndicator($('.shiroki-stats-card-views .shiroki-stats-card-change'), current.views, previous.views);

            // 🌐 访问量变化
            this.updateIndicator($('.shiroki-stats-card-visits .shiroki-stats-card-change'), current.visits, previous.visits);

            // 📥 下载量变化
            this.updateIndicator($('.shiroki-stats-card-downloads .shiroki-stats-card-change'), current.downloads, previous.downloads);
        },

        /**
         * 📊 获取上一周期数据
         */
        getPreviousPeriodData: function(period) {
            var data = window.shirokiStatsData;
            if (!data || !data.periods) {
                return null;
            }

            var periods = ['today', 'yesterday', 'week', 'month', 'year'];
            var currentIndex = periods.indexOf(period);

            if (currentIndex <= 0) {
                return data.periods.yesterday;
            }

            return data.periods[periods[currentIndex - 1]];
        },

        /**
         * 📈 更新指示器
         */
        updateIndicator: function($element, current, previous) {
            var change = previous > 0 ? ((current - previous) / previous) * 100 : 0;
            var isUp = change >= 0;

            $element.removeClass('up down').addClass(isUp ? 'up' : 'down');
            $element.html((isUp ? '↑' : '↓') + Math.abs(change).toFixed(1) + '% 较' + this.getPeriodName());
        },

        /**
         * 📝 获取周期名称
         */
        getPeriodName: function() {
            var $activeBtn = $('.shiroki-stats-filter-btn.active');
            var period = $activeBtn.data('period');

            var names = {
                'today': '昨日',
                'yesterday': '前日',
                'week': '上周',
                'month': '上月',
                'year': '去年'
            };

            return names[period] || '上期';
        },

        /**
         * 🔢 初始数字动画
         */
        animateNumbers: function() {
            var self = this;

            $('.shiroki-stats-card-value').each(function() {
                var $this = $(this);
                var target = parseInt($this.text().replace(/,/g, '')) || 0;

                $this.text('0');

                setTimeout(function() {
                    self.animateNumber($this, target);
                }, 300);
            });
        },

        /**
         * 📥 AJAX 获取统计数据
         */
        fetchStatsData: function(period) {
            var self = this;

            $.ajax({
                url: shirokiStatsConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_stats_data',
                    period: period,
                    nonce: shirokiStatsConfig.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateStatsDisplay(response.data);
                    }
                },
                error: function() {
                    self.showToast('❌ 数据加载失败', 'error');
                }
            });
        },

        /**
         * 📊 更新统计显示
         */
        updateStatsDisplay: function(data) {
            this.animateNumber($('#stat-views'), data.views);
            this.animateNumber($('#stat-visits'), data.visits);
            this.animateNumber($('#stat-downloads'), data.downloads);
        },

        /**
         * 🔔 显示提示消息
         */
        showToast: function(message, type) {
            var $toast = $('.shiroki-stats-toast');
            if ($toast.length === 0) {
                $toast = $('<div class="shiroki-stats-toast"><div class="shiroki-stats-toast-content"></div></div>');
                $('body').append($toast);
            }

            $toast.removeClass('success error').addClass(type);
            $toast.find('.shiroki-stats-toast-content').text(message);
            $toast.addClass('show');

            setTimeout(function() {
                $toast.removeClass('show');
            }, 3000);
        }
    };

    /**
     * 🚀 初始化统计仪表盘
     */
    $(document).ready(function() {
        ShirokiSiteStats.init();
    });

})(jQuery);
