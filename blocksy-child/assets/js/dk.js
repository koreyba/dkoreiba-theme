/* DK Design System — behaviour: tabs (accordion on phones), header theme button */
(function(){
  var ICONS=[
    '<path d="M12 21c-5-3.5-8-7-8-10.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 8 3.5C20 14 17 17.5 12 21z"/>',
    '<circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/>',
    '<path d="M12 20V10M12 10c0-4 3-6 7-6 0 4-3 6-7 6zM12 14c0-3-2.5-5-6-5 0 3 2.5 5 6 5z"/>',
    '<path d="M3 12c3-5 6-5 9 0s6 5 9 0"/><path d="M3 17c3-5 6-5 9 0s6 5 9 0" opacity=".5"/>',
    '<path d="M4 20 10 9l4 6 3-4 3 9"/><circle cx="17" cy="5" r="2"/>',
    '<path d="M4 12a8 8 0 0 1 14-5.3M20 12a8 8 0 0 1-14 5.3"/><path d="M18 3v4h-4M6 21v-4h4"/>'
  ];
  function svg(inner){return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'+inner+'</svg>';}

  var MOBILE=window.matchMedia('(max-width:860px)');
  var CHEV='<path d="m6 9 6 6 6-6"/>';

  /* .dk-tabs > .dk-tabpanel[h2|h3 + content]: tabs on desktop, accordion on phones.
     One state (current panel); on phones it can be -1 (all collapsed). */
  function initTabs(root,n){
    var panels=[].slice.call(root.children).filter(function(c){return c.classList.contains('dk-tabpanel');});
    if(!panels.length)return;
    var current=0;
    var list=document.createElement('div');list.className='dk-tablist';list.setAttribute('role','tablist');
    var tabs=[],heads=[];
    panels.forEach(function(p,i){
      var h=p.querySelector('h2,h3,h4');var id='dk-tabs'+n+'-'+i;var title=h?h.textContent.trim():'Tab '+(i+1);
      var count=p.querySelectorAll('li').length;
      p.id=p.id||id+'-p';p.setAttribute('role','tabpanel');p.setAttribute('aria-labelledby',id);
      var t=document.createElement('button');t.type='button';t.className='dk-tab';t.id=id;t.setAttribute('role','tab');t.setAttribute('aria-controls',p.id);
      t.innerHTML=(ICONS[i]?svg(ICONS[i]):'')+'<span></span>';t.lastChild.textContent=title;
      list.appendChild(t);tabs.push(t);
      var a=document.createElement('button');a.type='button';a.className='dk-acc-trigger';a.setAttribute('aria-controls',p.id);
      a.innerHTML=(ICONS[i]?svg(ICONS[i]):'')+'<span class="dk-acc-title"></span>'+(count?'<span class="dk-acc-count">'+count+'</span>':'')+'<span class="dk-acc-chev">'+svg(CHEV)+'</span>';
      a.querySelector('.dk-acc-title').textContent=title;
      root.insertBefore(a,p);heads.push(a);
    });
    function render(){
      if(!MOBILE.matches&&current<0)current=0;
      panels.forEach(function(p,j){var on=j===current;p.hidden=!on;
        tabs[j].setAttribute('aria-selected',String(on));tabs[j].tabIndex=on?0:-1;
        heads[j].setAttribute('aria-expanded',String(on));});
    }
    function select(i,focus){current=i;render();if(focus)tabs[i].focus();}
    tabs.forEach(function(t,i){
      t.addEventListener('click',function(){select(i);t.scrollIntoView({block:'nearest',inline:'nearest'});});
      t.addEventListener('keydown',function(e){var d={ArrowDown:1,ArrowRight:1,ArrowUp:-1,ArrowLeft:-1}[e.key];if(d){e.preventDefault();select((i+d+tabs.length)%tabs.length,true);}});
    });
    heads.forEach(function(a,i){
      a.addEventListener('click',function(){
        current=current===i?-1:i;render();
        // keep the tapped row in view when a panel above it collapses (sticky header ≈ 90px)
        var top=a.getBoundingClientRect().top;if(top<90)window.scrollBy(0,top-90);
      });
    });
    if(MOBILE.addEventListener)MOBILE.addEventListener('change',render);
    root.insertBefore(list,root.firstChild);root.classList.add('is-ready');render();
  }

  // YouTube facade (functions.php): load the player only when asked for (no autoplay).
  document.addEventListener('click',function(e){
    var b=e.target.closest&&e.target.closest('.dk-yt');if(!b)return;
    var f=document.createElement('iframe');
    f.src='https://www.youtube-nocookie.com/embed/'+encodeURIComponent(b.getAttribute('data-yt'))+'?rel=0&playsinline=1';
    f.title='YouTube video';f.allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    f.setAttribute('allowfullscreen','');f.className='dk-yt-frame';
    b.replaceWith(f);f.focus();
  });

  // Header theme button is server-rendered by [dk_theme_toggle] (functions.php).
  document.addEventListener('click',function(e){
    if(e.target.closest&&e.target.closest('.dk-theme-btn')&&window.dkToggleTheme)window.dkToggleTheme();
  });

  function init(){
    [].slice.call(document.querySelectorAll('.dk-tabs')).forEach(initTabs);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
