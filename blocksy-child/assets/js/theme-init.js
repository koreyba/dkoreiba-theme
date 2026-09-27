/* DK Design System — theme init. Printed inline in <head> to avoid a flash of the wrong theme. */
(function(){try{var t=localStorage.getItem('dk-theme');if(t)document.documentElement.setAttribute('data-dk-theme',t);}catch(e){}
window.dkToggleTheme=function(){var h=document.documentElement,cur=h.getAttribute('data-dk-theme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'),n=cur==='dark'?'light':'dark';h.setAttribute('data-dk-theme',n);try{localStorage.setItem('dk-theme',n)}catch(e){}};})();
