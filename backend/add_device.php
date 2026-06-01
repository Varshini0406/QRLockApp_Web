<?php
require_once 'DBconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

$deviceName = trim($_POST['DeviceName'] ?? '');
$deviceType = trim($_POST['DeviceType'] ?? '');
$deviceId   = trim($_POST['DeviceId'] ?? '');
$number     = trim($_POST['number'] ?? '');
$lock       = in_array($_POST['lock'] ?? '', ['Yes','NO','YES','No']) ? $_POST['lock'] : 'NO';

if (!$deviceName || !$deviceType || !$deviceId || !$number) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

$conn = getDB();

// Check for duplicate DeviceId
$chk = $conn->prepare("SELECT id FROM deviceinfo WHERE DeviceId = ? LIMIT 1");
$chk->bind_param('s', $deviceId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    echo json_encode(['status' => false, 'message' => 'A device with this ID is already registered']);
    $chk->close(); $conn->close();
    exit;
}
$chk->close();

$stmt = $conn->prepare("INSERT INTO deviceinfo (DeviceName, DeviceType, DeviceId, number, `lock`) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('sssss', $deviceName, $deviceType, $deviceId, $number, $lock);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Device added successfully', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to add device']);
}

$stmt->close();
$conn->close();
