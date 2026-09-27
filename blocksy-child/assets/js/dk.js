/* DK Design System v1 — behaviour: tabs, theme toggle, header CTA */
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

  function themeButton(){
    var b=document.createElement('button');b.type='button';b.className='dk-theme-btn';b.setAttribute('aria-label','Сменить тему');
    b.innerHTML='<svg class="dk-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg><svg class="dk-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/></svg>';
    b.addEventListener('click',function(){if(window.dkToggleTheme)window.dkToggleTheme();});
    return b;
  }

  function initHeader(){
    var isDk=!!document.querySelector('.dk-page');
    ['desktop','mobile'].forEach(function(dev){
      var slot=document.querySelector('#header [data-device="'+dev+'"] [data-column="end"] [data-items]');
      if(!slot||slot.querySelector('.dk-hdr-tools'))return;
      var box=document.createElement('div');box.className='dk-hdr-tools';
      if(isDk)box.appendChild(themeButton());
      if(dev==='desktop'){var a=document.createElement('a');a.className='dk-hdr-cta';a.href='/contact/';a.textContent='Связаться со мной';box.appendChild(a);}
      if(!box.children.length)return;
      if(dev==='mobile')slot.insertBefore(box,slot.firstChild);else slot.appendChild(box);
    });
  }

  function init(){
    [].slice.call(document.querySelectorAll('.dk-tabs')).forEach(initTabs);
    initHeader();
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
