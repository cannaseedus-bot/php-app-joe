<?php
class Notification_model {
    private $pdo;

    public function __construct() {
        // PDO init as above
    }

    public function create($to_user_id, $type, $from_user_id, $submission_id, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, type, from_user_id, submission_id, message) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$to_user_id, $type, $from_user_id, $submission_id, $message]);
    }

    public function get_for_user($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>