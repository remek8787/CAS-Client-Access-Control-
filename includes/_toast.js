window.casToast=function(message,type){
  const zone=document.getElementById('casToastZone'); if(!zone) return alert(message);
  const el=document.createElement('div'); el.className='cas-toast '+(type||'success');
  el.textContent=(type==='danger'?'❌ ':'✅ ')+message; zone.appendChild(el);
  setTimeout(()=>{el.style.opacity='0';el.style.transform='translateY(8px)';},4200);
  setTimeout(()=>el.remove(),4800);
};
