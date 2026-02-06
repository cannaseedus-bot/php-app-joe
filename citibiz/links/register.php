<?php
// register.php simple form posts to api.php?action=register
?>
<!doctype html><html><body>
<form id="reg">
  <input name="username" placeholder="username" required />
  <input name="email" placeholder="email" />
  <input name="password" type="password" placeholder="password" required />
  <button>Register</button>
</form>
<script>
document.getElementById('reg').addEventListener('submit', async e=>{
  e.preventDefault(); const fd = new FormData(e.target); fd.append('action','register');
  const r = await fetch('api.php',{method:'POST', body:fd}); const j = await r.json(); if(j.ok) alert('Registered'); else alert(j.msg||'err');
});
</script>
</body></html>