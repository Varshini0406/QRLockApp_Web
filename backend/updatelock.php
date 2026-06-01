<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$id   = $_POST['id'] ?? '';
$lock = $_POST['lock'] ?? '';

if (!$id || !$lock) {
    echo json_encode(['status' => false, 'message' => 'Device ID and lock state are required']);
    exit;
}

if (!in_array($lock, ['Yes', 'NO', 'YES', 'No'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid lock state']);
    exit;
}

$conn = getDB();

$stmt = $conn->prepare("UPDATE deviceinfo SET `lock` = ? WHERE id = ?");
$stmt->bind_param('si', $lock, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => true, 'message' => 'Lock state updated']);
} else {
    echo json_encode(['status' => false, 'message' => 'Update failed or device not found']);
}

$stmt->close();
$conn->close();
