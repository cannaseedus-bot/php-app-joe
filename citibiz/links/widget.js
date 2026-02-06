/* widget.js - include this on any site to render a small 200x200 card that shows rotating link text */
(function(){
  const host = (new URL(document.currentScript.src)).origin + '/';
  const container = document.currentScript.parentElement;
  const box = document.createElement('div'); box.style.width='200px'; box.style.height='200px'; box.style.overflow='hidden'; box.style.border='1px solid #ddd'; box.style.borderRadius='6px'; box.style.padding='8px';
  const text = document.createElement('div'); text.style.fontSize='14px'; text.style.lineHeight='1.2'; text.style.height='100%'; box.appendChild(text); container.appendChild(box);
  let items = [];
  async function load(){
    const res = await fetch(host + 'api.php?action=get_feed&limit=20');
    const j = await res.json(); items = j.items || [];
    if(items.length) show(0);
  }
  function show(idx){ const it=items[idx]; text.innerHTML = `<a href='${it.url}' target='_blank' style='color:inherit;text-decoration:none'>${it.title||it.url}</a>`; }
  load(); let idx=0; setInterval(()=>{ if(items.length==0) return; idx=(idx+1)%items.length; show(idx); },2500);
})();
