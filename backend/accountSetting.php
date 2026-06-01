<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$action   = trim($_POST['action'] ?? '');
$mobile   = trim($_POST['mobile'] ?? '');
$username = trim($_POST['Username'] ?? '');
$timezone = trim($_POST['TimeZone'] ?? 'Asia/Kolkata');

if ($action !== 'updateProfile') {
    echo json_encode(['status' => false, 'message' => 'Invalid action']);
    exit;
}

if (!$mobile || !$username) {
    echo json_encode(['status' => false, 'message' => 'Mobile number and username are required']);
    exit;
}

$conn = getDB();

// Prepare statement to update user profile
$stmt = $conn->prepare("UPDATE signup SET Username = ?, TimeZone = ? WHERE Mobile = ?");
$stmt->bind_param('sss', $username, $timezone, $mobile);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
