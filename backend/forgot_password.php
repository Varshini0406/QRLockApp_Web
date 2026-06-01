<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$mobile = trim($_POST['Mobile'] ?? '');

if (!$mobile) {
    echo json_encode(['status' => false, 'message' => 'Mobile number is required']);
    exit;
}

$conn = getDB();

$stmt = $conn->prepare("SELECT Id, Username FROM signup WHERE Mobile = ? AND Active = 1 LIMIT 1");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // In production: generate OTP, store it, send via SMS
    // For now, confirm the number exists so the web app can proceed to reset
    echo json_encode(['status' => true, 'message' => 'Account verified. You may now reset your password.']);
} else {
    echo json_encode(['status' => false, 'message' => 'No active account found with this number']);
}

$stmt->close();
$conn->close();
