<?php
class Home {
    public function index() {
        $data['title'] = 'Joe\'s List - Home';
        load_view('layouts/header', $data);
        load_view('home', $data);
        load_view('layouts/footer', $data);
    }
}
?>