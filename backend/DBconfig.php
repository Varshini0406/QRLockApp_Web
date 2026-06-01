<?php
// DBconfig.php - Database Configuration
// Load from environment variables if available, else fallback to constants
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');        // Change this in production!
define('DB_NAME', getenv('DB_NAME') ?: 'qrlockapp');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Database connection failed']);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// CORS - restrict to your domain in production
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
