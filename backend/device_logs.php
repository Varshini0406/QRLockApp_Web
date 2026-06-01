<?php
require_once 'DBconfig.php';

$conn = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $deviceId = $_GET['DeviceID'] ?? '';
    if ($deviceId) {
        $stmt = $conn->prepare("SELECT Id, DeviceID, Action, Timestamp FROM device_logs WHERE DeviceID = ? ORDER BY Timestamp DESC LIMIT 100");
        $stmt->bind_param('s', $deviceId);
    } else {
        $stmt = $conn->prepare("SELECT Id, DeviceID, Action, Timestamp FROM device_logs ORDER BY Timestamp DESC LIMIT 100");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    echo json_encode(['status' => 'success', 'logs' => $logs]);

} elseif ($method === 'POST') {
    $deviceId = trim($_POST['DeviceID'] ?? '');
    $action   = trim($_POST['Action'] ?? '');
    if (!$deviceId || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'DeviceID and Action are required']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO device_logs (DeviceID, Action, Timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param('ss', $deviceId, $action);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Log added', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add log']);
    }

} elseif ($method === 'PUT') {
    parse_str(file_get_contents('php://input'), $data);
    $id     = $data['Id'] ?? '';
    $action = $data['Action'] ?? '';
    if (!$id || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'Id and Action are required']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE device_logs SET Action = ? WHERE Id = ?");
    $stmt->bind_param('si', $action, $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Log updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update log']);
    }

} elseif ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['Id'] ?? '';
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Id is required']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM device_logs WHERE Id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Log deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete log']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}

$conn->close();
