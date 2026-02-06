<h1>Welcome to Joe's List</h1>
<p>Browse submissions, post your own, connect socially!</p>
<!-- Feed of recent submissions -->
<?php
// Fetch recent from Submission_model
$recent = (new Submission_model())->get_recent(10);
foreach ($recent as $sub): ?>
    <a href="/<?php echo $sub['type']; ?>/<?php echo $sub['id']; ?>">
        <h3><?php echo htmlspecialchars($sub['title']); ?></h3>
        <p><?php echo htmlspecialchars($sub['content']); ?></p>
        <?php if ($sub['image_url']): ?>
            <img src="<?php echo $sub['image_url']; ?>" alt="Submission Image">
        <?php endif; ?>
        <small>Likes: <?php echo $sub['likes']; ?> | Views: <?php echo $sub['views']; ?></small>
    </a>
<?php endforeach; ?>
