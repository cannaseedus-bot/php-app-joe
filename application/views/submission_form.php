<form method="post" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required>
    <textarea name="content" placeholder="Content/URL/Description"></textarea>
    <?php if ($type == 'image' || $type == 'product'): ?>
        <input type="file" name="image" accept="image/*">
    <?php endif; ?>
    <?php if ($type == 'product'): ?>
        <select name="status">
            <option value="buy_now">Buy Now</option>
            <option value="auction">Auction</option>
        </select>
    <?php elseif ($type == 'settings'): ?>
        <input type="file" name="avatar" accept="image/*">
        <input type="file" name="header" accept="image/*">
    <?php endif; ?>
    <button type="submit">Submit</button>
</form>