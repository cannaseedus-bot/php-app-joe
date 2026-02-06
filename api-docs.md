
**Action**:
1. Save as `api-docs.md` in Notepad++ (UTF-8, no BOM, Unix LF).
2. Upload to `/home/refluxed/public_html/joes-list/api-docs.md` (not web-accessible, for your reference).

#### Step 3: Update Models
Ensure models are compatible with `api.php`. Update `User_model.php` to add `update_header`:

**application/models/User_model.php**:
```php
<?php
class User_model {
    private $pdo;

    public function __construct() {
        require_once BASEPATH . '/config.php';
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
?>