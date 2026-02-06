<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Joe\'s List'; ?></title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/profile/<?php echo $_SESSION['handle']; ?>">Profile</a>
                <a href="#" onclick="showModal('settings')">Settings</a>
                <a href="/user/logout">Logout</a>
            <?php else: ?>
                <a href="#" onclick="showModal('login')">Login</a>
                <a href="#" onclick="showModal('register')">Register</a>
            <?php endif; ?>
            <!-- Submission links -->
            <a href="#" onclick="showModal('link')">Submit Link</a>
            <a href="#" onclick="showModal('news')">Submit News</a>
            <a href="#" onclick="showModal('blog')">Submit Blog</a>
            <a href="#" onclick="showModal('image')">Upload Image</a>
            <!-- Add for video, product -->
        </nav>
    </header>
    <main>