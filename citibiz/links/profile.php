<?php
// profile.php shows user info and their submitted links in a slider
session_start(); $config = include __DIR__ . '/config.php';
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
$prefix = $config['table_prefix'];
if(empty($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$uid = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_FILES['avatar'])){
    $up = __DIR__ . '/uploads/'; if(!is_dir($up)) mkdir($up,0755,true);
    $fn = 'avatar_'.$uid.'_' . time() . '_' . basename($_FILES['avatar']['name']); move_uploaded_file($_FILES['avatar']['tmp_name'],$up.$fn);
    $pdo->prepare("UPDATE `{$prefix}users` SET avatar=? WHERE id=?")->execute([$fn,$uid]);
}
$user = $pdo->prepare("SELECT * FROM `{$prefix}users` WHERE id=?"); $user->execute([$uid]); $user = $user->fetch(PDO::FETCH_ASSOC);
$links = $pdo->prepare("SELECT l.*, COALESCE(m.clicks,0) as clicks FROM `{$prefix}links` l LEFT JOIN `{$prefix}link_metrics` m ON m.link_id=l.id WHERE user_id=? ORDER BY l.created_at DESC"); $links->execute([$uid]); $links = $links->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Profile</title><link rel="stylesheet" href="css/profile.css"></head><body>
<h1><?=htmlspecialchars($user['username'])?>'s Profile</h1>
<form method="post" enctype="multipart/form-data">
  Avatar: <input type="file" name="avatar" /> <button>Upload</button>
</form>
<div class="slider" id="userSlider">
  <?php foreach($links as $l): ?>
    <div class="slide-card">
      <strong><?=htmlspecialchars($l['title']?:$l['url'])?></strong>
      <div><?=htmlspecialchars($l['clicks'])?> clicks</div>
    </div>
  <?php endforeach; ?>
</div>
<script>
// simple auto slider (text only)
let i=0; const slides = document.querySelectorAll('.slide-card'); setInterval(()=>{ if(slides.length==0) return; slides.forEach(s=>s.style.display='none'); slides[i].style.display='block'; i=(i+1)%slides.length; },3000);
</script>
</body></html>