<?php
require_once 'DBconfig.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$conn = getDB();

switch ($action) {

    case 'fetchUsers':
        $stmt = $conn->prepare(
            "SELECT u.Id, u.Name, u.AccessType, u.LastSeen, u.DeviceId, d.DeviceName
             FROM Users u
             LEFT JOIN deviceinfo d ON d.id = u.DeviceId
             ORDER BY u.Id DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['status' => true, 'data' => $users]);
        break;

    case 'addUser':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'POST required']); exit;
        }
        $name       = trim($_POST['Name'] ?? '');
        $accessType = trim($_POST['AccessType'] ?? 'Permanent');
        $deviceId   = (int)($_POST['DeviceId'] ?? 0);
        if (!$name) {
            echo json_encode(['status' => false, 'message' => 'Name is required']); exit;
        }
        $stmt = $conn->prepare("INSERT INTO Users (Name, AccessType, DeviceId) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $name, $accessType, $deviceId);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'User added', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to add user']);
        }
        break;

    case 'removeUsers':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'POST required']); exit;
        }
        // FIXED: validate every ID is a positive integer — no interpolation
        $raw = $_POST['UserIds'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', $raw)), fn($v) => $v > 0);
        if (empty($ids)) {
            echo json_encode(['status' => false, 'message' => 'No valid user IDs provided']); exit;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("DELETE FROM Users WHERE Id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'User(s) removed']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to remove user(s)']);
        }
        break;

    case 'updateAccess':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'POST required']); exit;
        }
        $userId     = (int)($_POST['UserId'] ?? 0);
        $accessType = $_POST['AccessType'] ?? '';
        if (!$userId || !in_array($accessType, ['Permanent', 'Temporary'])) {
            echo json_encode(['status' => false, 'message' => 'Invalid input']); exit;
        }
        $stmt = $conn->prepare("UPDATE Users SET AccessType = ? WHERE Id = ?");
        $stmt->bind_param('si', $accessType, $userId);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'Access type updated']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Update failed']);
        }
        break;

    default:
        echo json_encode(['status' => false, 'message' => 'Unknown action']);
}

$conn->close();
