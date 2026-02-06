<?php
header('Content-Type: application/json');
session_start();

// Root directory for JSON files
$rootDir = __DIR__;

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get form data
if (!empty($_FILES)) {
    // Handle file uploads (e.g., images)
    $type = $_POST['type'] ?? '';
    $title = filter_var($_POST['title'] ?? '', FILTER_SANITIZE_STRING);
    $url = filter_var($_POST['url'] ?? '', FILTER_SANITIZE_URL);
    $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
    $tags = array_map('trim', explode(',', filter_var($_POST['tags'] ?? '', FILTER_SANITIZE_STRING)));
    $file = $_FILES['image'] ?? null;

    if (!$type || !$title || !$file) {
        http_response_code(400);
        echo json_encode(['error' => 'Type, title, and image required']);
        exit;
    }

    // Handle file upload
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'png', 'jpeg'];
    if (!in_array(strtolower($ext), $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }
    $filename = uniqid() . '.' . $ext;
    $uploadPath = $rootDir . '/uploads/' . $filename;
    if (!is_dir($rootDir . '/uploads/')) {
        mkdir($rootDir . '/uploads/', 0755, true);
    }
    move_uploaded_file($file['tmp_name'], $uploadPath);

    $data = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'] ?? null,
        'title' => $title,
        'image' => '/uploads/' . $filename,
        'url' => $url,
        'description' => $description,
        'tags' => $tags,
        'category' => $type,
        'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        'likes' => 0
    ];
} else {
    // Handle non-file submissions
    $input = json_decode(file_get_contents('php://input'), true);
    $type = filter_var($input['type'] ?? '', FILTER_SANITIZE_STRING);

    if (!$type) {
        http_response_code(400);
        echo json_encode(['error' => 'Type required']);
        exit;
    }

    if ($type === 'login') {
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            exit;
        }
        $users = file_exists("$rootDir/users.json") ? json_decode(file_get_contents("$rootDir/users.json"), true) : [];
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                echo json_encode(['success' => true, 'user_id' => $user['id'], 'username' => $user['username']]);
                exit;
            }
        }
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    if ($type === 'register') {
        $username = filter_var($input['username'] ?? '', FILTER_SANITIZE_STRING);
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        if (!$username || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields required']);
            exit;
        }
        $users = file_exists("$rootDir/users.json") ? json_decode(file_get_contents("$rootDir/users.json"), true) : [];
        if (array_filter($users, fn($u) => $u['email'] === $email || $u['username'] === $username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username or email exists']);
            exit;
        }
        $data = [
            'id' => uniqid(),
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => gmdate('Y-m-d\TH:i:s\Z')
        ];
    } elseif ($type === 'comment') {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $item_id = filter_var($input['item_id'] ?? '', FILTER_SANITIZE_STRING);
        $comment = filter_var($input['comment'] ?? '', FILTER_SANITIZE_STRING);
        if (!$item_id || !$comment) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID and comment required']);
            exit;
        }
        $data = [
            'id' => uniqid(),
            'item_id' => $item_id,
            'user_id' => $_SESSION['user_id'],
            'comment' => $comment,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ];
    } elseif (in_array($type, ['pin', 'bookmark', 'share'])) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $item_id = filter_var($input['item_id'] ?? '', FILTER_SANITIZE_STRING);
        if (!$item_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID required']);
            exit;
        }
        $data = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user_id'],
            'item_id' => $item_id,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ];
        if ($type === 'share') {
            $data['platform'] = filter_var($input['platform'] ?? '', FILTER_SANITIZE_STRING);
        }
    } else {
        // Content submissions (cool, videos, etc.)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $title = filter_var($input['title'] ?? '', FILTER_SANITIZE_STRING);
        $url = filter_var($input['url'] ?? '', FILTER_SANITIZE_URL);
        $description = filter_var($input['description'] ?? '', FILTER_SANITIZE_STRING);
        $tags = array_map('trim', explode(',', filter_var($input['tags'] ?? '', FILTER_SANITIZE_STRING)));
        if (!$title || !$url) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and URL required']);
            exit;
        }
        $data = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'tags' => $tags,
            'category' => $type,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'likes' => 0
        ];
        if ($type === 'shopping') {
            $data['price'] = filter_var($input['price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        } elseif ($type === 'news') {
            $data['source'] = filter_var($input['source'] ?? '', FILTER_SANITIZE_STRING);
        }
    }
}

// Append to JSON file
$jsonFile = "$rootDir/$type.json";
$currentData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
$currentData[] = $data;

$file = fopen($jsonFile, 'w') or die(json_encode(['error' => 'File error']));
flock($file, LOCK_EX);
fwrite($file, json_encode($currentData, JSON_PRETTY_PRINT));
flock($file, LOCK_UN);
fclose($file);

echo json_encode([
    'success' => true,
    'item_id' => $data['id'] ?? null,
    'user_id' => $data['user_id'] ?? null,
    'username' => $type === 'register' ? $data['username'] : null
]);
?>