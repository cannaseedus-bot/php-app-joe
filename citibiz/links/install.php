<?php
// install.php with Auction post type support
$config = include __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host={$config['db_host']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$config['db_name']}`");

    $prefix = $config['table_prefix'];

    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}users` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) NOT NULL UNIQUE,
        email VARCHAR(191) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','moderator','user') DEFAULT 'user',
        avatar VARCHAR(255) DEFAULT NULL,
        header VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Links table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}links` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        url VARCHAR(2048) NOT NULL,
        excerpt TEXT DEFAULT NULL,
        thumbnail VARCHAR(255) DEFAULT NULL,
        favicon VARCHAR(255) DEFAULT NULL,
        type ENUM('text','image','website','youtube','auction') DEFAULT 'website',
        background_color VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES `{$prefix}users`(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Auction table (for type='auction')
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}auctions` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        link_id INT UNSIGNED NOT NULL,
        starting_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        current_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        reserve_price DECIMAL(12,2) DEFAULT NULL,
        bid_increment DECIMAL(12,2) NOT NULL DEFAULT 1.00,
        end_time DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (link_id) REFERENCES `{$prefix}links`(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Bids table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}bids` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auction_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        bid_amount DECIMAL(12,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (auction_id) REFERENCES `{$prefix}auctions`(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES `{$prefix}users`(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "<h2>Install complete</h2>\n";
    echo "<p>Tables created in database: {$config['db_name']}</p>\n";
    echo "<p>Next: visit <a href='index.php'>index.php</a> to view the feed and auction system.</p>\n";

} catch (PDOException $e) {
    echo "<h3>Installation failed:</h3>" . htmlspecialchars($e->getMessage());
}
?>
