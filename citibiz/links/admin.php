<?php
// admin.php - basic moderation
session_start();
$config = include __DIR__ . '/config.php';
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
$prefix = $config['table_prefix'];
// VERY simple auth check (in production add better checks)
if(empty($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$u = $pdo->prepare("SELECT role FROM `{$prefix}users` WHERE id = ?"); $u->execute([$_SESSION['user_id']]); $role = $u->fetchColumn();
if($role !== 'admin' && $role !== 'moderator'){ echo 'Access denied'; exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['action'])){
    if($_POST['action']==='delete' && !empty($_POST['id'])){
        $pdo->prepare("DELETE FROM `{$prefix}links` WHERE id = ?")->execute([intval($_POST['id'])]);
    }
    if($_POST['action']==='update' && !empty($_POST['id'])){
        $stmt = $pdo->prepare("UPDATE `{$prefix}links` SET title=?, url=?, excerpt=? WHERE id = ?");
        $stmt->execute([$_POST['title'], $_POST['url'], $_POST['excerpt'], intval($_POST['id'])]);
    }
}
$links = $pdo->query("SELECT l.*, u.username FROM `{$prefix}links` l LEFT JOIN `{$prefix}users` u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin</title></head><body>
<h2>Moderation Panel</h2>
<table border="1" cellpadding="6"><tr><th>ID</th><th>User</th><th>Title</th><th>URL</th><th>Actions</th></tr>
<?php foreach($links as $ln): ?>
<tr>
  <form method="post"><td><?=htmlspecialchars($ln['id'])?><input type="hidden" name="id" value="<?=htmlspecialchars($ln['id'])?>"></td>
  <td><?=htmlspecialchars($ln['username'])?></td>
  <td><input name="title" value="<?=htmlspecialchars($ln['title'])?>" /></td>
  <td><input name="url" value="<?=htmlspecialchars($ln['url'])?>" /></td>
  <td>
    <button name="action" value="update">Save</button>
    <button name="action" value="delete" onclick="return confirm('Delete?')">Delete</button>
  </td>
  </form>
</tr>
<?php endforeach; ?></table>
</body></html>