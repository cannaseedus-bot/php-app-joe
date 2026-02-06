<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage() . "<br>Run: CREATE DATABASE " . DB_NAME . ";");
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        handle VARCHAR(50) UNIQUE, -- e.g., @username
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        avatar VARCHAR(255) DEFAULT NULL,
        header_img VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type ENUM('link', 'news', 'blog', 'image', 'video', 'product'),
        title VARCHAR(255),
        content TEXT, -- URL/content/description
        status ENUM('buy_now', 'auction', 'active') DEFAULT 'active', -- for products
        image_url VARCHAR(255) DEFAULT NULL, -- for images/videos/products
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",

    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        submission_id INT,
        user_id INT,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (submission_id) REFERENCES submissions(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",

    "CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        submission_id INT,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (submission_id, user_id),
        FOREIGN KEY (submission_id) REFERENCES submissions(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",

    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type ENUM('like', 'comment'),
        from_user_id INT,
        submission_id INT,
        message TEXT,
        read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (from_user_id) REFERENCES users(id),
        FOREIGN KEY (submission_id) REFERENCES submissions(id)
    )"
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
}

echo "Database installed successfully! Tables created.";
?>