<?php
// login form posts to api.php?action=login and sets session on success
?>
<!doctype html><html><body>
<form id="login">
  <input name="username" placeholder="username" required />
  <input name="password" type="password" placeholder="password" required />
  <button>Login</button>
</form>
<script>
document.getElementById('login').addEventListener('submit', async e=>{
  e.preventDefault(); const fd = new FormData(e.target); fd.append('action','login');
  const r = await fetch('api.php',{method:'POST', body:fd}); const j = await r.json(); if(j.ok) { location.href='index.php'; } else alert(j.msg||'bad');
});
</script>
</body></html>