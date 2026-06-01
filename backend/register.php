<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$username = trim($_POST['Username'] ?? '');
$mobile   = trim($_POST['Mobile'] ?? '');
$password = $_POST['password'] ?? '';
$repass   = $_POST['re_password'] ?? '';
$timezone = trim($_POST['TimeZone'] ?? 'Asia/Kolkata');

if (!$username || !$mobile || !$password || !$repass) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

if (!preg_match('/^\d{10}$/', $mobile)) {
    echo json_encode(['status' => false, 'message' => 'Enter a valid 10-digit mobile number']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

if ($password !== $repass) {
    echo json_encode(['status' => false, 'message' => 'Passwords do not match']);
    exit;
}

$conn = getDB();

// Check duplicate mobile
$chk = $conn->prepare("SELECT Id FROM signup WHERE Mobile = ? LIMIT 1");
$chk->bind_param('s', $mobile);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    echo json_encode(['status' => false, 'message' => 'This mobile number is already registered']);
    $chk->close(); $conn->close();
    exit;
}
$chk->close();

$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO signup (Username, Mobile, Password, TimeZone, Active) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param('ssss', $username, $mobile, $hashed, $timezone);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['status' => false, 'message' => 'Registration failed, please try again']);
}

$stmt->close();
$conn->close();
