(()=>{
  const state={images:[],index:0,scale:1,rotation:0,tx:0,ty:0,isDragging:false,startX:0,startY:0,canvas:null,img:null,wrapper:null,info:null,actions:null,closeBtn:null,fullscreen:false};
  const createUI=()=>{
    // 🔄 Swup 切换时复用已创建的 UI，避免重复创建
    const existing=document.querySelector('.tk-image-viewer__wrapper');
    if(existing){
      state.wrapper=existing;
      state.canvas=existing.querySelector('.tk-image-viewer__canvas');
      state.img=existing.querySelector('img');
      state.info=existing.querySelector('.tk-image-viewer__info');
      state.actions=existing.querySelector('.tk-image-viewer__actions');
      state.closeBtn=existing.querySelector('.tk-image-viewer__close');
      return;
    }
    const w=document.createElement('div');w.className='tk-image-viewer__wrapper';
    const c=document.createElement('div');c.className='tk-image-viewer__canvas';
    const i=document.createElement('img');
    const close=document.createElement('button');close.className='tk-image-viewer__close';close.setAttribute('aria-label','关闭');close.innerHTML='×';
    const actions=document.createElement('div');actions.className='tk-image-viewer__actions';
    const info=document.createElement('div');info.className='tk-image-viewer__info';
    const btn=(t,fn)=>{const b=document.createElement('button');b.type='button';b.textContent=t;b.addEventListener('click',fn);return b};
    const prev=btn('上一张',()=>switchImage(state.index-1));
    const next=btn('下一张',()=>switchImage(state.index+1));
    const zoomIn=btn('放大',()=>setScale(state.scale+0.1));
    const zoomOut=btn('缩小',()=>setScale(state.scale-0.1));
    const full=btn('全屏',()=>toggleFullscreen());
    const rotate=btn('旋转',()=>setRotation(state.rotation+90));
    const reset=btn('原始大小',()=>resetTransform());
    actions.appendChild(prev);actions.appendChild(next);actions.appendChild(zoomIn);actions.appendChild(zoomOut);actions.appendChild(full);actions.appendChild(rotate);actions.appendChild(reset);
    c.appendChild(i);w.appendChild(c);w.appendChild(close);w.appendChild(actions);w.appendChild(info);
    document.body.appendChild(w);
    state.canvas=c;state.img=i;state.wrapper=w;state.info=info;state.actions=actions;state.closeBtn=close;
  };
  const formatIndex=()=>`${state.index+1}/${state.images.length}`;
  const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));
  const setScale=s=>{state.scale=clamp(s,0.5,3);applyTransform()};
  const setRotation=r=>{state.rotation=(r%360+360)%360;applyTransform()};
  const resetTransform=()=>{state.scale=1;state.rotation=0;state.tx=0;state.ty=0;applyTransform()};
  const applyTransform=()=>{state.img.style.transform=`translate(${state.tx}px,${state.ty}px) scale(${state.scale}) rotate(${state.rotation}deg)`};
  const open=(idx)=>{
    state.index=idx;state.img.src=state.images[state.index].src;resetTransform();updateInfo();
    state.wrapper.classList.add('tk-image-viewer__wrapper--visible');
    document.addEventListener('keydown',onKey);
  };
  const close=()=>{
    state.wrapper.classList.add('tk-image-viewer__wrapper--fade-out');
    setTimeout(()=>{state.wrapper.classList.remove('tk-image-viewer__wrapper--fade-out','tk-image-viewer__wrapper--visible');document.removeEventListener('keydown',onKey);},150);
  };
  const switchImage=(n)=>{
    if(state.images.length<2)return;
    if(n<0)n=0; if(n>=state.images.length)n=state.images.length-1;
    if(n===state.index)return; state.index=n; state.img.src=state.images[state.index].src; resetTransform(); updateInfo();
  };
  const updateInfo=()=>{state.info.textContent=formatIndex()};
  const getFitScale=()=>{
    const nw=state.img.naturalWidth||state.img.width||0;
    const nh=state.img.naturalHeight||state.img.height||0;
    if(!nw||!nh)return 1;
    const vw=window.innerWidth, vh=window.innerHeight;
    return Math.min(vw/nw, vh/nh);
  };
  const ensureImageLoaded=(cb)=>{
    if(state.img.complete && state.img.naturalWidth) return cb();
    state.img.addEventListener('load', cb, {once:true});
  };
  const toggleFullscreen=()=>{
    if(!state.fullscreen){
      state.prev={scale:state.scale,tx:state.tx,ty:state.ty};
      state.fullscreen=true;
      state.wrapper.classList.add('tk-image-viewer__wrapper--fullscreen');
      ensureImageLoaded(()=>{state.tx=0;state.ty=0;state.scale=getFitScale();applyTransform()});
    }else{
      state.fullscreen=false;
      state.wrapper.classList.remove('tk-image-viewer__wrapper--fullscreen');
      if(state.prev){state.scale=state.prev.scale;state.tx=state.prev.tx;state.ty=state.prev.ty;}
      applyTransform();
    }
  };
  const onWheel=e=>{e.preventDefault();const d=e.deltaY<0?0.1:-0.1;setScale(state.scale+d)};
  const onStart=e=>{state.isDragging=true;state.canvas.classList.add('grabbing');const p=getPoint(e);state.startX=p.x;state.startY=p.y};
  const onMove=e=>{if(!state.isDragging)return;const p=getPoint(e);state.tx+=p.x-state.startX;state.ty+=p.y-state.startY;state.startX=p.x;state.startY=p.y;applyTransform()};
  const onEnd=()=>{state.isDragging=false;state.canvas.classList.remove('grabbing')};
  const getPoint=e=>{if(e.touches&&e.touches[0]){return{x:e.touches[0].clientX,y:e.touches[0].clientY}}return{x:e.clientX,y:e.clientY}};
  const onKey=e=>{
    if(e.key==='Escape')return close();
    if(e.key==='+'||e.key==='=')return setScale(state.scale+0.1);
    if(e.key==='-')return setScale(state.scale-0.1);
    if(e.key==='r'||e.key==='R')return setRotation(state.rotation+90);
    if(e.key==='0')return resetTransform();
    if(e.key==='f'||e.key==='F')return toggleFullscreen();
    if(e.key==='ArrowLeft')return switchImage(state.index-1);
    if(e.key==='ArrowRight')return switchImage(state.index+1);
  };
  const bindCanvasEvents=()=>{
    state.canvas.addEventListener('wheel',onWheel,{passive:false});
    state.canvas.addEventListener('mousedown',onStart);
    window.addEventListener('mousemove',onMove);
    window.addEventListener('mouseup',onEnd);
    state.canvas.addEventListener('touchstart',onStart,{passive:false});
    state.canvas.addEventListener('touchmove',onMove,{passive:false});
    state.canvas.addEventListener('touchend',onEnd);
    state.closeBtn.addEventListener('click',close);
    state.wrapper.addEventListener('click',e=>{if(e.target===state.wrapper)close()});
  };
  const collectImages=()=>{
    const container=document.querySelector('.single-content');
    if(!container){state.images=[];return}
    
    // 只收集用户添加的内容图片，排除所有UI组件、图标、头像等
    const imgs=[...container.querySelectorAll('img')].filter(el=>{
      // 只读取直接在文章内容中的图片，排除各种组件
      const parent=el.closest('div');
      if(!parent) return false;
      
      // 检查图片是否在段落或直接内容容器中
      const isInContent = parent.tagName === 'P' || 
                         parent.classList.contains('single-content') || 
                         parent.parentNode.classList.contains('single-content');
      
      if(!isInContent) return false;
      
      // 确保图片有有效的src属性
      const src=el.getAttribute('data-src')||el.currentSrc||el.src;
      if(!src || !src.trim() || src.endsWith('.gif') || src.endsWith('.ico')) return false;
      
      // 排除尺寸过小的图片（可能是图标）
      const width=el.width||parseInt(el.getAttribute('width'))||0;
      const height=el.height||parseInt(el.getAttribute('height'))||0;
      if(width<50 || height<50) return false;
      
      return true;
    });
    
    // 确保每次都重新收集，避免重复
    state.images=imgs.map(el=>({el,src:(el.getAttribute('data-src')||el.currentSrc||el.src)}));
    state.images.forEach((it,idx)=>{
      const target=it.el;
      // 移除之前的事件监听器，避免重复绑定
      target.removeEventListener('click', it._clickHandler);
      const a=target.closest('a');
      if(a){a.removeEventListener('click', it._linkClickHandler);}
      
      // 创建新的事件监听器
      const handler=(e)=>{
        // 检查点击目标是否是卡片组件的链接
        if(e.target.closest('.md-card-link-wrap')){
          return; // 如果是卡片链接，不处理，让它正常跳转
        }
        
        // 检查点击目标是否是任务清单相关元素
        if(e.target.closest('.md-task-item') || e.target.closest('.md-task-emoji') || e.target.closest('.md-task-text')){
          return; // 如果是任务清单相关元素，不处理
        }
        
        // 确保点击的是图片本身或其直接父级链接
        const isImgClick = e.target === target;
        const isLinkClick = a && e.currentTarget === a;
        
        if(isImgClick || isLinkClick){
          e.preventDefault();
          e.stopPropagation();
          open(idx);
        }
      };
      
      // 保存监听器引用，以便后续移除
      it._clickHandler = handler;
      target.addEventListener('click', handler);
      
      if(a){
        it._linkClickHandler = handler;
        a.addEventListener('click', handler);
      }
    });
  };
  const observeMutations=()=>{
    const container=document.querySelector('.single-content');
    if(!container)return;
    // 🔄 断开旧观察器，避免 Swup 切换后观察已移除的容器
    if(state.observer){
      try{state.observer.disconnect();}catch(_){}
    }
    const ob=new MutationObserver(()=>collectImages());
    ob.observe(container,{childList:true,subtree:true});
    state.observer=ob;
  };
  const reinit=()=>{collectImages();observeMutations()};
  const init=()=>{createUI();bindCanvasEvents();collectImages();observeMutations()};
  if(document.readyState=='loading'){document.addEventListener('DOMContentLoaded',init)}else{init()}
  // 🚀 Swup 无刷新切换后重新收集图片
  document.addEventListener('shiroki:content:loaded',reinit);
})();
