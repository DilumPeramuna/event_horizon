<?php
// File: event_actions.php
session_start();
require_once('../includes/db_connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

$action = $input['action'];
$event_id = intval($input['event_id']);

try {
    if ($action === 'toggle_like') {
        // Check if liked
        $check = $pdo->prepare("SELECT id FROM event_likes WHERE user_id = ? AND event_id = ?");
        $check->execute([$user_id, $event_id]);
        
        if ($check->rowCount() > 0) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM event_likes WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            $status = 'unliked';
        } else {
            // Like
            $stmt = $pdo->prepare("INSERT INTO event_likes (user_id, event_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $event_id]);
            $status = 'liked';
        }

        // Get new count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM event_likes WHERE event_id = ?");
        $countStmt->execute([$event_id]);
        $new_count = $countStmt->fetchColumn();

        echo json_encode(['success' => true, 'status' => $status, 'count' => $new_count]);

    } elseif ($action === 'toggle_reminder') {
        // Check if reminder exists
        $check = $pdo->prepare("SELECT id FROM reminders WHERE user_id = ? AND event_id = ?");
        $check->execute([$user_id, $event_id]);

        if ($check->rowCount() > 0) {
            // Remove reminder
            $stmt = $pdo->prepare("DELETE FROM reminders WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            $status = 'removed';
        } else {
            // Add reminder
            $stmt = $pdo->prepare("INSERT INTO reminders (user_id, event_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $event_id]);
            $status = 'added';
        }
        
        echo json_encode(['success' => true, 'status' => $status]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
