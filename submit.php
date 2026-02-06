<?php
header('Content-Type: application/json');
session_start();

// Root directory
$rootDir = __DIR__;

// Ensure session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['error' => 'Session expired']);
    exit;
}
$_SESSION['last_activity'] = time();

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate limiting (5 requests per minute per user/IP)
$rateLimitFile = "$rootDir/rate_limit.json";
$rateLimit = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : [];
$userId = $_SESSION['user_id'] ?? 'guest_' . $_SERVER['REMOTE_ADDR'];
$now = time();
$rateLimit[$userId] = array_filter($rateLimit[$userId] ?? [], fn($t) => $now - $t < 60);
if (count($rateLimit[$userId]) >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}
$rateLimit[$userId][] = $now;
file_put_contents($rateLimitFile, json_encode($rateLimit));

// Create user folder if authenticated
function createUserFolder($userId) {
    global $rootDir;
    $userDir = "$rootDir/users/$userId";
    $subDirs = ['submissions', 'likes', 'comments', 'pins', 'bookmarks', 'shares', 'uploads'];
    
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }
    foreach ($subDirs as $dir) {
        $subDir = "$userDir/$dir";
        if (!is_dir($subDir)) {
            mkdir($subDir, 0755, true);
            chmod($subDir, 0755);
        }
    }
}

// Write JSON file with locking
function writeJsonFile($filePath, $data) {
    $file = fopen($filePath, 'w');
    if (!$file) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to open file: ' . $filePath]);
        exit;
    }
    flock($file, LOCK_EX);
    fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
    flock($file, LOCK_UN);
    fclose($file);
    chmod($filePath, 0644);
    // Placeholder for encryption: encrypt file content here if needed
}

// Generate UUID (more reliable than uniqid())
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Validate form data
function validateForm($input, $type) {
    if (in_array($type, ['cool', 'videos', 'shopping', 'blogs', 'news', 'ai'])) {
        if (empty($input['title']) || empty($input['url']) || !filter_var($input['url'], FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'error' => 'Title and valid URL required'];
        }
    } elseif ($type === 'images') {
        if (empty($input['title']) || (empty($_FILES['image']) && empty($input['url']))) {
            return ['valid' => false, 'error' => 'Title and either image file or URL required'];
        }
    } elseif ($type === 'login') {
        if (empty($input['email']) || empty($input['password'])) {
            return ['valid' => false, 'error' => 'Email and password required'];
        }
    } elseif ($type === 'register') {
        if (empty($input['username']) || empty($input['email']) || empty($input['password']) || empty($input['confirm_password'])) {
            return ['valid' => false, 'error' => 'All fields required'];
        }
        if ($input['password'] !== $input['confirm_password']) {
            return ['valid' => false, 'error' => 'Passwords do not match'];
        }
        if (strlen($input['password']) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
        }
    } elseif (in_array($type, ['comment', 'like', 'pin', 'bookmark', 'share'])) {
        if (empty($input['item_id'])) {
            return ['valid' => false, 'error' => 'Item ID required'];
        }
        if ($type === 'comment' && empty($input['comment'])) {
            return ['valid' => false, 'error' => 'Comment text required'];
        }
        if ($type === 'share' && empty($input['platform'])) {
            return ['valid' => false, 'error' => 'Platform required for share'];
        }
    }
    return ['valid' => true];
}

// Get form data
if (!empty($_FILES)) {
    // Handle image uploads
    $type = filter_var($_POST['type'] ?? '', FILTER_SANITIZE_STRING);
    $input = [
        'title' => filter_var($_POST['title'] ?? '', FILTER_SANITIZE_STRING),
        'url' => filter_var($_POST['url'] ?? '', FILTER_SANITIZE_URL),
        'description' => htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'),
        'tags' => array_map('trim', explode(',', filter_var($_POST['tags'] ?? '', FILTER_SANITIZE_STRING)))
    ];
    $file = $_FILES['image'] ?? null;

    $validation = validateForm($input, $type);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode(['error' => $validation['error']]);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    createUserFolder($_SESSION['user_id']);

    // Handle file upload
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'png', 'jpeg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array(strtolower($ext), $allowed) || !in_array($mime, ['image/jpeg', 'image/png'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
        http_response_code(400);
        echo json_encode(['error' => 'File too large']);
        exit;
    }
    $filename = generateUUID() . '.' . $ext;
    $uploadPath = "$rootDir/users/{$_SESSION['user_id']}/uploads/$filename";
    move_uploaded_file($file['tmp_name'], $uploadPath);
    chmod($uploadPath, 0644);

    $data = [
        'id' => generateUUID(),
        'user_id' => $_SESSION['user_id'],
        'title' => $input['title'],
        'image' => "/users/{$_SESSION['user_id']}/uploads/$filename",
        'url' => $input['url'],
        'description' => $input['description'],
        'tags' => $input['tags'],
        'category' => $type,
        'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        'likes' => 0
    ];
} else {
    // Handle JSON submissions
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $type = filter_var($input['type'] ?? '', FILTER_SANITIZE_STRING);

    $validation = validateForm($input, $type);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode(['error' => $validation['error']]);
        exit;
    }

    if ($type === 'login') {
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        $users = file_exists("$rootDir/users.json") ? json_decode(file_get_contents("$rootDir/users.json"), true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            echo json_encode(['error' => 'Invalid users.json']);
            exit;
        }
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
        $users = file_exists("$rootDir/users.json") ? json_decode(file_get_contents("$rootDir/users.json"), true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            echo json_encode(['error' => 'Invalid users.json']);
            exit;
        }
        if (count(array_filter($users, fn($u) => $u['email'] === $email || $u['username'] === $username)) > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Username or email exists']);
            exit;
        }
        $userId = generateUUID();
        $data = [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => gmdate('Y-m-d\TH:i:s\Z')
        ];
        createUserFolder($userId);
        $_SESSION['user_id'] = $userId;
    } elseif (in_array($type, ['comment', 'like', 'pin', 'bookmark', 'share'])) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        createUserFolder($_SESSION['user_id']);
        $item_id = filter_var($input['item_id'] ?? '', FILTER_SANITIZE_STRING);
        $data = [
            'id' => generateUUID(),
            'user_id' => $_SESSION['user_id'],
            'item_id' => $item_id,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ];
        if ($type === 'comment') {
            $data['comment'] = htmlspecialchars($input['comment'] ?? '', ENT_QUOTES, 'UTF-8');
        } elseif ($type === 'share') {
            $data['platform'] = filter_var($input['platform'] ?? '', FILTER_SANITIZE_STRING);
        }
    } else {
        // Content submissions
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        createUserFolder($_SESSION['user_id']);
        $data = [
            'id' => generateUUID(),
            'user_id' => $_SESSION['user_id'],
            'title' => filter_var($input['title'] ?? '', FILTER_SANITIZE_STRING),
            'url' => filter_var($input['url'] ?? '', FILTER_SANITIZE_URL),
            'description' => htmlspecialchars($input['description'] ?? '', ENT_QUOTES, 'UTF-8'),
            'tags' => array_map('trim', explode(',', filter_var($input['tags'] ?? '', FILTER_SANITIZE_STRING))),
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

// Write to user folder
if ($type === 'register') {
    $jsonFile = "$rootDir/users.json";
    $currentData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid users.json']);
        exit;
    }
    $currentData[] = $data;
    writeJsonFile($jsonFile, $currentData);
} elseif ($type === 'login') {
    // Handled above
} elseif (in_array($type, ['comment', 'like', 'pin', 'bookmark', 'share'])) {
    $jsonFile = "$rootDir/users/{$_SESSION['user_id']}/$type/{$data['item_id']}.json";
    $currentData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON in file: ' . $jsonFile]);
        exit;
    }
    $currentData[] = $data;
    writeJsonFile($jsonFile, $currentData);
} else {
    // Content submissions
    $jsonFile = "$rootDir/$type.json";
    $currentData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON in file: ' . $jsonFile]);
        exit;
    }
    $currentData[] = $data;
    writeJsonFile($jsonFile, $currentData);

    // Also store in user submissions
    $userJsonFile = "$rootDir/users/{$_SESSION['user_id']}/submissions/{$data['id']}.json";
    writeJsonFile($userJsonFile, $data);
}

echo json_encode([
    'success' => true,
    'item_id' => $data['id'] ?? null,
    'user_id' => $data['user_id'] ?? $_SESSION['user_id'] ?? null,
    'username' => $type === 'register' ? $data['username'] : null
]);
?>