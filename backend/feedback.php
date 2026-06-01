<?php
require_once 'DBconfig.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDB();

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT Id, Username, Review, Rating FROM Feedback ORDER BY Id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    echo json_encode(['status' => true, 'data' => $feedback]);

} elseif ($method === 'POST') {
    // If Id provided, attempt delete (only own reviews enforced client-side; add auth for production)
    if (!empty($_POST['Id'])) {
        $id = (int)$_POST['Id'];
        $username = trim($_POST['Username'] ?? '');
        if (!$username) {
            echo json_encode(['status' => false, 'message' => 'Username required to delete']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM Feedback WHERE Id = ? AND Username = ?");
        $stmt->bind_param('is', $id, $username);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['status' => true, 'message' => 'Review deleted']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Delete failed or not authorised']);
        }
    } else {
        // Insert review
        $username = trim($_POST['Username'] ?? '');
        $review   = trim($_POST['Review'] ?? '');
        $rating   = (int)($_POST['Rating'] ?? 0);
        if (!$username || !$review || $rating < 1 || $rating > 5) {
            echo json_encode(['status' => false, 'message' => 'All fields required and rating must be 1–5']);
            exit;
        }
        // Also fetch all reviews for the response
        $stmt = $conn->prepare("INSERT INTO Feedback (Username, Review, Rating) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $username, $review, $rating);
        if ($stmt->execute()) {
            // Return updated list
            $all = $conn->prepare("SELECT Id, Username, Review, Rating FROM Feedback ORDER BY Id DESC");
            $all->execute();
            $result = $all->get_result();
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = $row;
            }
            echo json_encode(['status' => true, 'message' => 'Review submitted', 'data' => $feedback]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to submit review']);
        }
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
}

$conn->close();
