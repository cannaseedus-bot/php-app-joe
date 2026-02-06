<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS Headers
header('Access-Control-Allow-Origin: *'); // Restrict to specific origins in production, e.g., 'http://your-app-domain.com'
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Handle preflight requests
}

session_start();
require_once __DIR__ . '/config.php';

// Initialize PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Inline User_model class
class User_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function validate($email, $pass) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($pass, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function create($username, $email, $password, $handle) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, handle) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $password, $handle]);
    }

    public function get_by_handle($handle) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE handle = ?");
        $stmt->execute([$handle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update_avatar($user_id, $path) {
        $stmt = $this->pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$path, $user_id]);
    }

    public function update_header($user_id, $path) {
        $stmt = $this->pdo->prepare("UPDATE users SET header_img = ? WHERE id = ?");
        return $stmt->execute([$path, $user_id]);
    }
}

// Inline Submission_model class
class Submission_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO submissions (user_id, type, title, content, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['type'], $data['title'], $data['content'], $data['image_url'] ?? null, $data['status'] ?? 'active']);
        return $this->pdo->lastInsertId();
    }

    public function get($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_feed($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_recent($limit = 10) {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function increment_views($id) {
        $stmt = $this->pdo->prepare("UPDATE submissions SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function get_comments($id) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.handle FROM comments c JOIN users u ON c.user_id = u.id WHERE submission_id = ? ORDER BY created_at");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_comment($submission_id, $user_id, $comment) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (submission_id, user_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$submission_id, $user_id, htmlspecialchars($comment)]);
    }

    public function toggle_like($submission_id, $user_id) {
        $check = $this->pdo->prepare("SELECT id FROM likes WHERE submission_id = ? AND user_id = ?");
        $check->execute([$submission_id, $user_id]);
        if ($check->fetch()) {
            $stmt = $this->pdo->prepare("DELETE FROM likes WHERE submission_id = ? AND user_id = ?");
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO likes (submission_id, user_id) VALUES (?, ?)");
        }
        $stmt->execute([$submission_id, $user_id]);
        $this->update_likes_count($submission_id);
    }

    private function update_likes_count($id) {
        $stmt = $this->pdo->prepare("UPDATE submissions SET likes = (SELECT COUNT(*) FROM likes WHERE submission_id = ?) WHERE id = ?");
        $stmt->execute([$id, $id]);
    }

    public function get_submissions($type = null, $user_id = null, $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM submissions WHERE 1=1";
        $params = [];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        if ($user_id) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit; // Cast to int to prevent quoting
        $params[] = (int)$offset; // Cast to int to prevent quoting
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Inline Notification_model class
class Notification_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($to_user_id, $type, $from_user_id, $submission_id, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, type, from_user_id, submission_id, message, is_read) VALUES (?, ?, ?, ?, ?, 0)");
        return $stmt->execute([$to_user_id, $type, $from_user_id, $submission_id, $message]);
    }

    public function get_for_user($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get input (supports form-data, JSON, GET)
$input = array_merge($_GET, $_POST);
// Always parse JSON body if present
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $jsonInput = json_decode($rawInput, true);
    if ($jsonInput) {
        $input = array_merge($input, $jsonInput);
    }
}

// Validate action
$action = $input['action'] ?? '';
if (!$action) {
    die(json_encode(['error' => 'No action specified']));
}

// Helper: Check if user is logged in
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        die(json_encode(['error' => 'Login required']));
    }
    return $_SESSION['user_id'];
}

// API actions
header('Content-Type: application/json');
switch ($action) {
    case 'login':
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        if (!$email || !$password) {
            echo json_encode(['error' => 'Email and password required']);
            exit;
        }
        $user_model = new User_model();
        $user = $user_model->validate($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['handle'] = $user['handle'];
            echo json_encode(['success' => true, 'handle' => $user['handle']]);
        } else {
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;

    case 'register':
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        if (!$username || !$email || !$password) {
            echo json_encode(['error' => 'Username, email, and password required']);
            exit;
        }
        $handle = '@' . preg_replace('/[^a-zA-Z0-9]/', '', $username);
        $user_model = new User_model();
        $result = $user_model->create($username, $email, password_hash($password, PASSWORD_DEFAULT), $handle);
        if ($result) {
            // Auto-login after register
            $user = $user_model->validate($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['handle'] = $user['handle'];
            }
            echo json_encode(['success' => true, 'handle' => $handle]);
        } else {
            echo json_encode(['error' => 'Registration failed - check for duplicate email/username']);
        }
        break;

    case 'update_profile':
        $user_id = require_login();
        $user_model = new User_model();
        if (isset($_FILES['avatar'])) {
            $upload_path = UPLOAD_PATH . 'avatars/' . $user_id . '.jpg';
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                $user_model->update_avatar($user_id, '/uploads/avatars/' . $user_id . '.jpg');
            } else {
                echo json_encode(['error' => 'Avatar upload failed']);
                exit;
            }
        }
        if (isset($_FILES['header'])) {
            $upload_path = UPLOAD_PATH . 'headers/' . $user_id . '.jpg';
            if (move_uploaded_file($_FILES['header']['tmp_name'], $upload_path)) {
                $user_model->update_header($user_id, '/uploads/headers/' . $user_id . '.jpg');
            } else {
                echo json_encode(['error' => 'Header upload failed']);
                exit;
            }
        }
        echo json_encode(['success' => true]);
        break;

    case 'submit_content':
        $user_id = require_login();
        $type = $input['type'] ?? '';
        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';
        $status = $input['status'] ?? 'active';
        if (!$type || !$title || !$content) {
            echo json_encode(['error' => 'Type, title, and content required']);
            exit;
        }
        if (!in_array($type, ['link', 'news', 'blog', 'image', 'video', 'product'])) {
            echo json_encode(['error' => 'Invalid submission type']);
            exit;
        }
        $data = [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'status' => $status
        ];
        if (in_array($type, ['image', 'product']) && isset($_FILES['image'])) {
            $filename = uniqid() . '.jpg';
            $upload_path = UPLOAD_PATH . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $data['image_url'] = '/uploads/' . $filename;
            } else {
                echo json_encode(['error' => 'Image upload failed']);
                exit;
            }
        }
        $submission_model = new Submission_model();
        $id = $submission_model->create($data);
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'add_comment':
        $user_id = require_login();
        $submission_id = (int)($input['submission_id'] ?? 0);
        $comment = $input['comment'] ?? '';
        if (!$submission_id || !$comment) {
            echo json_encode(['error' => 'Submission ID and comment required']);
            exit;
        }
        $submission_model = new Submission_model();
        $submission = $submission_model->get($submission_id);
        if (!$submission) {
            echo json_encode(['error' => 'Invalid submission ID']);
            exit;
        }
        $submission_model->add_comment($submission_id, $user_id, $comment);
        $notification_model = new Notification_model();
        $notification_model->create($submission['user_id'], 'comment', $user_id, $submission_id, 'New comment on your post');
        echo json_encode(['success' => true]);
        break;

    case 'toggle_like':
        $user_id = require_login();
        $submission_id = (int)($input['submission_id'] ?? 0);
        if (!$submission_id) {
            echo json_encode(['error' => 'Submission ID required']);
            exit;
        }
        $submission_model = new Submission_model();
        $submission = $submission_model->get($submission_id);
        if (!$submission) {
            echo json_encode(['error' => 'Invalid submission ID']);
            exit;
        }
        $submission_model->toggle_like($submission_id, $user_id);
        $notification_model = new Notification_model();
        $notification_model->create($submission['user_id'], 'like', $user_id, $submission_id, 'Someone liked your post');
        echo json_encode(['success' => true]);
        break;

    case 'get_notifications':
        $user_id = require_login();
        $notification_model = new Notification_model();
        $notifications = $notification_model->get_for_user($user_id);
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        break;

    case 'get_submissions':
        $type = $input['type'] ?? null;
        $user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
        $limit = (int)($input['limit'] ?? 10);
        $offset = (int)($input['offset'] ?? 0);
        $submission_model = new Submission_model();
        $submissions = $submission_model->get_submissions($type, $user_id, $limit, $offset);
        echo json_encode(['success' => true, 'submissions' => $submissions]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

### Test After Update
Run the curl again (without --resolve if DNS is fixed, or with it):