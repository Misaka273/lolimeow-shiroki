/**
 * ✨ 鼠标移动流光特效
 * 白木 🔗gl.baimu.live 开发
 */

(function() {
  'use strict';

  let fallDirection = 1;

  function initGuangbiaoTX() {
    const container = document.createElement("div");
    container.id = "guangbiao-container";
    document.body.appendChild(container);

    let x1 = 0;
    let y1 = 0;

    const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
    const dist_to_draw = 50;
    const delay = 1000;
    const fsize = ["1.1rem", "1.4rem", ".8rem", "1.7rem"];
    const colors = ["#E23636", "#001affff", "#00ffeaff", "#ff009dff", "#ff9595ff", "#004370ff"];

    const rand = function(min, max) {
      return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    const selRand = function(arr) {
      return arr[rand(0, arr.length - 1)];
    };

    const distanceTo = function(x1, y1, x2, y2) {
      return Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2);
    };

    const shouldDraw = function(x, y) {
      return distanceTo(x1, y1, x, y) >= dist_to_draw;
    };

    const addStar = function(x, y) {
      const star = document.createElement("div");
      star.className = "star";
      star.style.top = (y + rand(-20, 20)) + "px";
      star.style.left = x + "px";
      star.style.color = selRand(colors);
      star.style.fontSize = selRand(fsize);
      container.appendChild(star);

      const fs = 10 + 5 * parseFloat(getComputedStyle(star).fontSize);

      star.animate(
        {
          transform: [
            "translate(" + rand(-5, 5) + "px, " + ((y + fs > vh ? vh - y : fs) * fallDirection * 0.3) + "px)",
            "translate(" + rand(-20, 20) + "px, " + ((y + fs > vh ? vh - y : fs) * fallDirection) + "px) rotateX(" + rand(1, 500) + "deg) rotateY(" + rand(1, 500) + "deg)"
          ],
          opacity: [1, 0]
        },
        {
          duration: delay,
          fill: "forwards"
        }
      );

      setTimeout(function() {
        star.remove();
      }, delay);
    };

    window.addEventListener("mousemove", function(e) {
      const clientX = e.clientX;
      const clientY = e.clientY;
      if (shouldDraw(clientX, clientY)) {
        addStar(clientX, clientY);
        x1 = clientX;
        y1 = clientY;
      }
    });
  }

  // 🚀 初始化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGuangbiaoTX);
  } else {
    initGuangbiaoTX();
  }
})();
