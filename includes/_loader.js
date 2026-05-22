document.addEventListener('submit',function(e){
  const form=e.target; if(!form || form.dataset.noLoader==='1') return;
  const btn=form.querySelector('button[type="submit"],button:not([type])');
  if(btn && !btn.dataset.loadingAttached){
    btn.dataset.loadingAttached='1';
    btn.insertAdjacentHTML('afterbegin','<span class="cas-submit-spinner"></span>');
  }
  document.body.classList.add('cas-loading');
});
