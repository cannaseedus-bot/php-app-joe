<?php
class Submission_model {
    private $pdo;

    public function __construct() {
        global $pdo; // From config or init
        $this->pdo = $pdo ?? new PDO(...); // Simplified
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
        // Check if exists, insert or delete
        $check = $this->pdo->prepare("SELECT id FROM likes WHERE submission_id = ? AND user_id = ?");
        $check->execute([$submission_id, $user_id]);
        if ($check->fetch()) {
            // Unlike
            $stmt = $this->pdo->prepare("DELETE FROM likes WHERE submission_id = ? AND user_id = ?");
        } else {
            // Like
            $stmt = $this->pdo->prepare("INSERT INTO likes (submission_id, user_id) VALUES (?, ?)");
        }
        $stmt->execute([$submission_id, $user_id]);
        // Update likes count
        $this->update_likes_count($submission_id);
    }

    private function update_likes_count($id) {
        $stmt = $this->pdo->prepare("UPDATE submissions SET likes = (SELECT COUNT(*) FROM likes WHERE submission_id = ?) WHERE id = ?");
        $stmt->execute([$id, $id]);
    }
}
?>