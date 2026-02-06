<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base path
define('BASEPATH', __DIR__);

// Simple view loader function (replaces Loader.php)
function load_view($view, $data = [], $return = false) {
    extract($data);
    if ($return) {
        ob_start();
        include BASEPATH . "/application/views/$view.php";
        return ob_get_clean();
    }
    include BASEPATH . "/application/views/$view.php";
}

// Simple redirect function
function redirect($url) {
    header("Location: $url");
    exit;
}

// Simple 404 function
function show_404() {
    http_response_code(404);
    echo "404 - Page Not Found";
    exit;
}

// Simple autoloader
spl_autoload_register(function ($class) {
    $path = BASEPATH . '/application/' . str_replace('_', '/', $class) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

// Parse URL
$request = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim(parse_url($request, PHP_URL_PATH), '/'));

// Adjust for subdirectory if needed
$base_segments = explode('/', trim(parse_url('https://refluxedpc.com/joes-list/', PHP_URL_PATH), '/'));
$segments = array_slice($segments, count($base_segments));

$controller = !empty($segments[0]) ? ucfirst($segments[0]) : 'Home';
$method = !empty($segments[1]) ? $segments[1] : 'index';
$param = !empty($segments[2]) ? $segments[2] : null;

$controller_file = BASEPATH . "/application/controllers/$controller.php";
if (file_exists($controller_file)) {
    require $controller_file;
    $controller_obj = new $controller();
    // Inject load_view into controller
    $controller_obj->load = function ($view, $data = [], $return = false) {
        return load_view($view, $data, $return);
    };
    if (method_exists($controller_obj, $method)) {
        call_user_func_array([$controller_obj, $method], [$param]);
    } else {
        show_404();
    }
} else {
    show_404();
}
?>