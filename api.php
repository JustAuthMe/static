<?php
require_once 'config.dist.php';

const UPLOAD_DIR = 'medias/';
define('ROOT', str_replace('api.php', '', $_SERVER['SCRIPT_FILENAME']));

function isJamInternal() {
    return isset($_SERVER['HTTP_X_ACCESS_TOKEN']) && $_SERVER['HTTP_X_ACCESS_TOKEN'] === JAM_INTERNAL_API_KEY;
}

header('Content-Type: application/json');
$http_status = '401 Unauthorized';
$response = ['status' => 'error', 'message' => 'Authentication failed'];

$args = explode('/', $_GET['arg']);

if (isJamInternal()) {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            if (isset($_FILES['file'])) {
                $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ALLOWED_FILE_EXTENSIONS)) {
                    $filename = UPLOAD_DIR . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['file']['tmp_name'], ROOT . $filename);

                    $response = ['status' => 'success', 'url' => 'https://static.justauth.me/' . $filename];
                } else {
                    $response['message'] = 'Forbidden file extension';
                }
            } else {
                $response['message'] = 'No such file';
            }
            break;

        case 'DELETE':
            $filename = ROOT . UPLOAD_DIR . $args[0];

            if (file_exists($filename)) {
                unlink($filename);
                $response = ['status' => 'success'];
            } else {
                $http_status = '404 Not Found';
                $response['message'] = 'File not found';
            }
            break;

        default:
            $filename = UPLOAD_DIR . $args[0];
            $root_filename = ROOT . $filename;

            if (file_exists($root_filename)) {
                $response = [
                    'status' => 'success',
                    'url' => 'https://static.justauth.me/' . $filename,
                    'size' => filesize($root_filename),
                    'updated_at' => filemtime($root_filename)
                ];
            } else {
                $http_status = '404 Not Found';
                $response['message'] = 'File not found';
            }
    }
}

if ($response['status'] == 'success') {
    $http_status = '200 OK';
}

header('HTTP/1.1 ' . $http_status);
echo json_encode($response);