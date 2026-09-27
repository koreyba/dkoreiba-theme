/* DK Design System — behaviour: tabs, header theme button */
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

  function initTabs(root,n){
    var panels=[].slice.call(root.children).filter(function(c){return c.classList.contains('dk-tabpanel');});
    if(!panels.length)return;
    var list=document.createElement('div');list.className='dk-tablist';list.setAttribute('role','tablist');
    var tabs=panels.map(function(p,i){
      var h=p.querySelector('h2,h3,h4');var id='dk-tabs'+n+'-'+i;
      p.id=p.id||id+'-p';p.setAttribute('role','tabpanel');p.setAttribute('aria-labelledby',id);
      var b=document.createElement('button');b.type='button';b.className='dk-tab';b.id=id;b.setAttribute('role','tab');b.setAttribute('aria-controls',p.id);
      b.innerHTML=(ICONS[i]?svg(ICONS[i]):'')+'<span></span>';b.lastChild.textContent=h?h.textContent.trim():'Tab '+(i+1);
      list.appendChild(b);return b;
    });
    function select(i,focus){tabs.forEach(function(t,j){var on=i===j;t.setAttribute('aria-selected',String(on));t.tabIndex=on?0:-1;panels[j].hidden=!on;});if(focus)tabs[i].focus();}
    tabs.forEach(function(t,i){
      t.addEventListener('click',function(){select(i);t.scrollIntoView({block:'nearest',inline:'nearest'});});
      t.addEventListener('keydown',function(e){var d={ArrowDown:1,ArrowRight:1,ArrowUp:-1,ArrowLeft:-1}[e.key];if(d){e.preventDefault();select((i+d+tabs.length)%tabs.length,true);}});
    });
    root.insertBefore(list,root.firstChild);root.classList.add('is-ready');select(0);
  }

  // Header theme button is server-rendered by [dk_theme_toggle] (functions.php).
  document.addEventListener('click',function(e){
    if(e.target.closest&&e.target.closest('.dk-theme-btn')&&window.dkToggleTheme)window.dkToggleTheme();
  });

  function init(){
    [].slice.call(document.querySelectorAll('.dk-tabs')).forEach(initTabs);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
