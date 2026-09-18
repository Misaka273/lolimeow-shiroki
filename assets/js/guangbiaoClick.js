/**
 * ✨ 鼠标点击线条特效
 * 白木 🔗gl.baimu.live 开发
 */

(function() {
  'use strict';

  // 🚫 防止 Swup 缓存页面后重复初始化导致特效重叠
  if (window.__shirokiGuangbiaoClickInitialized) {
    return;
  }
  window.__shirokiGuangbiaoClickInitialized = true;

  var COLORS = ['#E23636', '#001AFF', '#00FFEA', '#FF009D', '#FFD166', '#7B61FF', '#00C853', '#FF6D00'];

  var PRESETS = {
    five_lines: {
      type: 'fan',
      duration: 420,
      color: '#1877F2',
      lineHeight: 5,
      shortLength: 5,
      finalLength: 25,
      moveDistance: 18,
      phase1Offset: 0.28,
      lines: [
        { angle: 132.5 },
        { angle: 170 },
        { angle: -152.5 },
        { angle: -115 },
        { angle: -77.5 }
      ]
    },
    firework_lines: {
      type: 'firework',
      burstDuration: 420,
      fallDuration: 960,
      lineHeight: 5,
      shortLength: 5,
      finalLengthMin: 12,
      finalLengthMax: 36,
      moveDistanceMin: 10,
      moveDistanceMax: 26,
      ringRadiusMin: 8,
      ringRadiusMax: 14,
      fallDistance: 64,
      lineCount: 14,
      phase1Offset: 0.28,
      shrinkOffset: 0.46,
      dotOffset: 0.72,
      colors: COLORS
    }
  };

  function createBurstContainer(container, x, y) {
    var burst = document.createElement('div');
    burst.className = 'click-burst';
    burst.style.left = x + 'px';
    burst.style.top = y + 'px';
    container.appendChild(burst);
    return burst;
  }

  function createLineElement(height, radius, color) {
    var el = document.createElement('span');
    el.className = 'click-line';
    el.style.height = height + 'px';
    el.style.marginTop = -(height / 2) + 'px';
    el.style.borderRadius = radius;
    el.style.backgroundColor = color;
    el.style.color = color;
    return el;
  }

  function playFanBurst(preset, burst) {
    var halfHeight = preset.lineHeight / 2;
    var radius = halfHeight + 'px';

    preset.lines.forEach(function(line) {
      var el = createLineElement(preset.lineHeight, radius, preset.color);
      var rotate = 'rotate(' + line.angle + 'deg)';
      burst.appendChild(el);

      el.animate(
        [
          {
            width: '0px',
            transform: rotate + ' translateX(0px)',
            opacity: 0,
            offset: 0
          },
          {
            width: preset.shortLength + 'px',
            transform: rotate + ' translateX(0px)',
            opacity: 1,
            offset: preset.phase1Offset
          },
          {
            width: preset.finalLength + 'px',
            transform: rotate + ' translateX(' + preset.moveDistance + 'px)',
            opacity: 0,
            offset: 1
          }
        ],
        {
          duration: preset.duration,
          easing: 'cubic-bezier(0.25, 0.8, 0.25, 1)',
          fill: 'forwards'
        }
      );
    });

    setTimeout(function() {
      burst.remove();
    }, preset.duration + 50);
  }

  function playFireworkBurst(preset, burst) {
    var halfHeight = preset.lineHeight / 2;
    var radius = halfHeight + 'px';
    var angleStep = 360 / preset.lineCount;
    var maxLifetime = preset.burstDuration + preset.fallDuration + 250;
    var ringRadius = preset.ringRadiusMin + Math.random() * (preset.ringRadiusMax - preset.ringRadiusMin);
    ringRadius = Math.round(ringRadius * 10) / 10;

    for (var i = 0; i < preset.lineCount; i++) {
      (function(index) {
        var layoutAngle = index * angleStep;
        var layoutRad = layoutAngle * Math.PI / 180;
        var originX = Math.cos(layoutRad) * ringRadius;
        var originY = Math.sin(layoutRad) * ringRadius;
        var angle = layoutAngle;
        var color = preset.colors[Math.floor(Math.random() * preset.colors.length)];
        var rotate = 'rotate(' + angle + 'deg)';
        var driftX = Math.random() * 28 - 14;
        var fallY = preset.fallDistance + Math.random() * 36;
        var fallTime = preset.fallDuration + Math.random() * 240 - 120;
        var lineLength = preset.finalLengthMin + Math.random() * (preset.finalLengthMax - preset.finalLengthMin);
        var moveDistance = preset.moveDistanceMin + Math.random() * (preset.moveDistanceMax - preset.moveDistanceMin);
        lineLength = Math.round(lineLength * 10) / 10;
        moveDistance = Math.round(moveDistance * 10) / 10;

        var particle = document.createElement('div');
        particle.className = 'click-particle';
        particle.style.left = originX + 'px';
        particle.style.top = originY + 'px';
        burst.appendChild(particle);

        var el = createLineElement(preset.lineHeight, radius, color);
        el.className = 'click-line click-line--firework';
        particle.appendChild(el);

        var burstAnim = el.animate(
          [
            {
              width: '0px',
              transform: rotate + ' translateX(0px)',
              opacity: 0,
              offset: 0
            },
            {
              width: preset.shortLength + 'px',
              transform: rotate + ' translateX(0px)',
              opacity: 1,
              offset: preset.phase1Offset
            },
            {
              width: lineLength + 'px',
              transform: rotate + ' translateX(' + moveDistance + 'px)',
              opacity: 1,
              offset: 1
            }
          ],
          {
            duration: preset.burstDuration,
            easing: 'cubic-bezier(0.25, 0.8, 0.25, 1)',
            fill: 'forwards'
          }
        );

        burstAnim.finished.then(function() {
          var dotSize = preset.lineHeight;
          var tipTranslate = moveDistance + (lineLength - dotSize);
          var burstTransform = rotate + ' translateX(' + moveDistance + 'px)';
          var dotTransform = rotate + ' translateX(' + tipTranslate + 'px)';
          var fallEasing = 'cubic-bezier(0.33, 1, 0.68, 1)';

          particle.animate(
            [
              {
                transform: 'translate(0px, 0px)',
                offset: 0
              },
              {
                transform: 'translate(' + (driftX * 0.38) + 'px, ' + (fallY * 0.42) + 'px)',
                offset: preset.shrinkOffset
              },
              {
                transform: 'translate(' + (driftX * 0.72) + 'px, ' + (fallY * 0.78) + 'px)',
                offset: preset.dotOffset
              },
              {
                transform: 'translate(' + driftX + 'px, ' + fallY + 'px)',
                offset: 1
              }
            ],
            {
              duration: fallTime,
              easing: fallEasing,
              fill: 'forwards'
            }
          );

          el.animate(
            [
              {
                width: lineLength + 'px',
                borderRadius: radius,
                opacity: 1,
                transform: burstTransform,
                offset: 0
              },
              {
                width: dotSize + 'px',
                borderRadius: '50%',
                opacity: 1,
                transform: dotTransform,
                offset: preset.shrinkOffset
              },
              {
                width: dotSize + 'px',
                borderRadius: '50%',
                opacity: 0.65,
                transform: dotTransform,
                offset: preset.dotOffset
              },
              {
                width: dotSize + 'px',
                borderRadius: '50%',
                opacity: 0,
                transform: dotTransform,
                offset: 1
              }
            ],
            {
              duration: fallTime,
              easing: fallEasing,
              fill: 'forwards'
            }
          );
        });
      })(i);
    }

    setTimeout(function() {
      burst.remove();
    }, maxLifetime);
  }

  function initGuangbiaoClick(style) {
    var preset = PRESETS[style];
    if (!preset) {
      return;
    }

    var container = document.createElement('div');
    container.id = 'guangbiao-click-container';
    document.body.appendChild(container);

    window.addEventListener('mousedown', function(e) {
      if (e.button !== 0) {
        return;
      }

      var burst = createBurstContainer(container, e.clientX, e.clientY);

      if (preset.type === 'firework') {
        playFireworkBurst(preset, burst);
      } else {
        playFanBurst(preset, burst);
      }
    });
  }

  var config = window.shirokiGuangbiaoClickConfig || {};
  var style = config.style || 'five_lines';

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initGuangbiaoClick(style);
    });
  } else {
    initGuangbiaoClick(style);
  }
})();
