<?php
if($action === 'login'){
$u = $_POST['username']; $p = $_POST['password'];
$stmt = $pdo->prepare("SELECT * FROM `{$prefix}users` WHERE username = ? LIMIT 1");
$stmt->execute([$u]); $user = $stmt->fetch(PDO::FETCH_ASSOC);
if($user && password_verify($p, $user['password'])){
$_SESSION['user_id'] = $user['id']; json(['ok'=>true]);
} else json(['ok'=>false,'msg'=>'Invalid']);
}


if($action === 'submit_link'){
if(empty($_SESSION['user_id'])) json(['ok'=>false,'msg'=>'login']);
$title = $_POST['title'] ?? null; $url = $_POST['url'] ?? null; $excerpt = $_POST['excerpt'] ?? null; $thumbnail = $_POST['thumbnail'] ?? null; $bg = $_POST['background_color'] ?? null;
if(!$url) json(['ok'=>false,'msg'=>'missing url']);
$stmt = $pdo->prepare("INSERT INTO `{$prefix}links` (user_id,title,url,excerpt,thumbnail,background_color) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'],$title,$url,$excerpt,$thumbnail,$bg]);
$linkId = $pdo->lastInsertId();
// create metrics row
$pdo->prepare("INSERT INTO `{$prefix}link_metrics` (link_id) VALUES (?)")->execute([$linkId]);
json(['ok'=>true,'id'=>$linkId]);
}


if($action === 'vote'){
if(empty($_SESSION['user_id'])) json(['ok'=>false,'msg'=>'login']);
$link = intval($_POST['link_id']); $vote = intval($_POST['vote']);
$stmt = $pdo->prepare("REPLACE INTO `{$prefix}votes` (user_id,link_id,vote) VALUES (?,?,?)");
$stmt->execute([$_SESSION['user_id'],$link,$vote]);
json(['ok'=>true]);
}


if($action === 'get_feed'){
$limit = intval($_GET['limit'] ?? 20);
$stmt = $pdo->prepare("SELECT l.*, u.username, COALESCE(m.visits,0) AS visits, COALESCE(m.clicks,0) AS clicks FROM `{$prefix}links` l LEFT JOIN `{$prefix}users` u ON u.id = l.user_id LEFT JOIN `{$prefix}link_metrics` m ON m.link_id = l.id ORDER BY l.created_at DESC LIMIT ?");
$stmt->execute([$limit]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
json(['ok'=>true,'items'=>$rows]);
}


if($action === 'record_click'){
$link = intval($_POST['link_id']);
$pdo->prepare("UPDATE `{$prefix}link_metrics` SET clicks = clicks + 1 WHERE link_id = ?")->execute([$link]);
// update ctr (very simple)
$pdo->prepare("UPDATE `{$prefix}link_metrics` m JOIN `{$prefix}links` l ON l.id=m.link_id SET m.ctr = (m.clicks / GREATEST(m.visits,1)) WHERE m.link_id = ?")->execute([$link]);
json(['ok'=>true]);
}


json(['ok'=>false,'msg'=>'unknown action']);
?>