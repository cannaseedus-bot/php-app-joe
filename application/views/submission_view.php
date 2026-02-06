<h1><?php echo htmlspecialchars($submission['title']); ?></h1>
<p><?php echo nl2br(htmlspecialchars($submission['content'])); ?></p>
<?php if ($submission['image_url']): ?>
    <img src="<?php echo $submission['image_url']; ?>" alt="Submission Media">
<?php endif; ?>
<?php if ($submission['type'] == 'product'): ?>
    <p>Status: <?php echo ucfirst($submission['status']); ?></p>
    <button>Buy Now</button> <!-- Or auction logic -->
<?php endif; ?>

<div class="actions">
    <button onclick="toggleLike(<?php echo $submission['id']; ?>)">Like (<?php echo $submission['likes']; ?>)</button>
    <button onclick="showCommentModal(<?php echo $submission['id']; ?>)">Comment</button>
</div>

<div class="comments">
    <h3>Comments</h3>
    <?php foreach ($comments as $comment): ?>
        <p><strong><?php echo $comment['handle']; ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?></p>
    <?php endforeach; ?>
    <!-- Comment form loads in modal -->
</div>