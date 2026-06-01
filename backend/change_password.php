<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$mobile   = trim($_POST['mobile'] ?? '');
$newpass  = $_POST['new_password'] ?? '';
$confpass = $_POST['confirm_password'] ?? '';

if (!$mobile || !$newpass || !$confpass) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

if ($newpass !== $confpass) {
    echo json_encode(['status' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (strlen($newpass) < 6) {
    echo json_encode(['status' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

$conn = getDB();

// Verify account exists
$chk = $conn->prepare("SELECT Id FROM signup WHERE Mobile = ? LIMIT 1");
$chk->bind_param('s', $mobile);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'Account not found']);
    $chk->close(); $conn->close();
    exit;
}
$chk->close();

$hashed = password_hash($newpass, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE signup SET Password = ? WHERE Mobile = ?");
$stmt->bind_param('ss', $hashed, $mobile);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to update password']);
}

$stmt->close();
$conn->close();
