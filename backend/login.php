<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$mobile   = trim($_POST['MobileNumber'] ?? '');
$password = $_POST['Password'] ?? '';

if (!$mobile || !$password) {
    echo json_encode(['status' => false, 'message' => 'Mobile number and password are required']);
    exit;
}

$conn = getDB();

$stmt = $conn->prepare("SELECT Id, Username, Mobile, Password, TimeZone, Active FROM signup WHERE Mobile = ? LIMIT 1");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'No account found with this mobile number']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Support both hashed passwords (new) and plaintext (migrated accounts)
$passwordMatch = false;
if (password_get_info($user['Password'])['algo']) {
    // Hashed password
    $passwordMatch = password_verify($password, $user['Password']);
} else {
    // Plaintext fallback - migrate on login
    $passwordMatch = ($password === $user['Password']);
    if ($passwordMatch) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE signup SET Password = ? WHERE Id = ?");
        $upd->bind_param('si', $hashed, $user['Id']);
        $upd->execute();
        $upd->close();
    }
}

if (!$passwordMatch) {
    echo json_encode(['status' => false, 'message' => 'Incorrect password']);
    exit;
}

if ($user['Active'] == 0) {
    echo json_encode(['status' => false, 'message' => 'Account is inactive']);
    exit;
}

unset($user['Password']);
echo json_encode(['status' => true, 'message' => 'Login successful', 'data' => $user]);

$conn->close();
