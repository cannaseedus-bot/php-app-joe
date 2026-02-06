<?php
session_start();

class Submission {
    private $model;

    public function __construct() {
        $this->model = new Submission_model();
    }

    public function create($type) {
        if ($_POST) {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => 'Login required']);
                return;
            }
            $data = [
                'user_id' => $_SESSION['user_id'],
                'type' => $type,
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'status' => $_POST['status'] ?? 'active'
            ];
            if (in_array($type, ['image', 'product']) && isset($_FILES['image'])) {
                $filename = uniqid() . '.jpg';
                $upload_path = UPLOAD_PATH . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
                $data['image_url'] = '/uploads/' . $filename;
            }
            $id = $this->model->create($data);
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo load_view('submission_form', ['type' => $type], true);
        }
    }

    public function view($type, $id) {
        $submission = $this->model->get($id);
        if (!$submission || $submission['type'] != $type) show_404();

        $this->model->increment_views($id);
        $comments = $this->model->get_comments($id);

        if ($_POST['action'] == 'like' && isset($_SESSION['user_id'])) {
            $this->model->toggle_like($id, $_SESSION['user_id']);
            (new Notification_model())->create($submission['user_id'], 'like', $_SESSION['user_id'], $id, 'Someone liked your post');
        }

        if ($_POST['action'] == 'comment') {
            $this->model->add_comment($id, $_SESSION['user_id'], $_POST['comment']);
            (new Notification_model())->create($submission['user_id'], 'comment', $_SESSION['user_id'], $id, 'New comment on your post');
        }

        $data = ['submission' => $submission, 'comments' => $comments];
        load_view('layouts/header', $data);
        load_view('submission_view', $data);
        load_view('layouts/footer', $data);
    }
}
?>