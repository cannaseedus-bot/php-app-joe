<?php
session_start();
require_once BASEPATH . '/config.php';

class User {
    private $model;

    public function __construct() {
        $this->model = new User_model();
    }

    public function login() {
        if ($_POST) {
            $user = $this->model->validate($_POST['email'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['handle'] = $user['handle'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Invalid credentials']);
            }
        } else {
            echo load_view('login', [], true);
        }
    }

    public function register() {
        if ($_POST) {
            $handle = '@' . preg_replace('/[^a-zA-Z0-9]/', '', $_POST['username']);
            $result = $this->model->create($_POST['username'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $handle);
            echo json_encode($result ? ['success' => true] : ['error' => 'Registration failed']);
        } else {
            echo load_view('register', [], true);
        }
    }

    public function profile($handle = null) {
        if (!$handle) $handle = $_SESSION['handle'] ?? null;
        if (!$handle) redirect('https://refluxedpc.com/joes-list/');

        $user_data = $this->model->get_by_handle($handle);
        if (!$user_data) show_404();

        $feed = (new Submission_model())->get_user_feed($user_data['id']);
        $notifications = (new Notification_model())->get_for_user($user_data['id']);

        $data = ['user' => $user_data, 'feed' => $feed, 'notifications' => $notifications];
        load_view('layouts/header', $data);
        load_view('profile', $data);
        load_view('layouts/footer', $data);
    }

    public function settings() {
        if ($_POST) {
            if (isset($_FILES['avatar'])) {
                $upload_path = UPLOAD_PATH . 'avatars/' . $_SESSION['user_id'] . '.jpg';
                move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path);
                $this->model->update_avatar($_SESSION['user_id'], '/uploads/avatars/' . $_SESSION['user_id'] . '.jpg');
            }
            echo json_encode(['success' => true]);
        } else {
            echo load_view('submission_form', ['type' => 'settings'], true);
        }
    }

    public function session_check() {
        echo json_encode(['logged_in' => isset($_SESSION['user_id'])]);
    }
}
?>