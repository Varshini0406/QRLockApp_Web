<?php
require_once 'DBconfig.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDB();

if ($method === 'GET') {
    $mobile = trim($_GET['mobile'] ?? '');
    if (!$mobile) {
        echo json_encode(['status' => false, 'message' => 'Mobile number required']);
        exit;
    }
    $stmt = $conn->prepare("SELECT Id, Mobile, message, created_at FROM notification WHERE Mobile = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    echo json_encode(['status' => true, 'data' => $notifications]);

} elseif ($method === 'POST') {
    $mobile  = trim($_POST['mobile'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$mobile || !$message) {
        echo json_encode(['status' => false, 'message' => 'Mobile and message are required']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO notification (Mobile, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ss', $mobile, $message);
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Notification sent']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to send notification']);
    }

} else {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
}

$conn->close();
