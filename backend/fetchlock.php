<?php
require_once 'DBconfig.php';

$number = trim($_GET['number'] ?? $_POST['number'] ?? '');

if (!$number) {
    echo json_encode(['status' => false, 'message' => 'Mobile number is required']);
    exit;
}

$conn = getDB();

$stmt = $conn->prepare("SELECT id, DeviceName, DeviceType, DeviceId, number, `lock` FROM deviceinfo WHERE number = ?");
$stmt->bind_param('s', $number);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

$stmt->close();
$conn->close();

if (count($devices) > 0) {
    echo json_encode(['status' => true, 'message' => 'Devices fetched', 'data' => $devices]);
} else {
    echo json_encode(['status' => false, 'message' => 'No devices found', 'data' => []]);
}
