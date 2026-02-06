<div class="profile-header">
    <?php if ($user['header_img']): ?>
        <img src="<?php echo $user['header_img']; ?>" alt="Header">
    <?php endif; ?>
    <img src="<?php echo $user['avatar'] ?? '/default-avatar.png'; ?>" alt="Avatar" class="avatar">
    <h1><?php echo $user['handle']; ?></h1>
</div>

<div class="profile-feed">
    <h2>Feed</h2>
    <?php foreach ($feed as $post): ?>
        <a href="/<?php echo $post['type']; ?>/<?php echo $post['id']; ?>">
            <!-- Render post summary -->
            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
        </a>
    <?php endforeach; ?>
</div>

<div class="notifications">
    <h2>Notifications</h2>
    <?php foreach ($notifications as $notif): ?>
        <p><?php echo htmlspecialchars($notif['message']); ?> <small><?php echo $notif['read'] ? 'Read' : 'New'; ?></small></p>
    <?php endforeach; ?>
</div>