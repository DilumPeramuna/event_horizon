<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db_connection.php');

if (!isset($_GET['id'])) {
    echo "Invalid Event ID";
    exit;
}

$event_id = intval($_GET['id']);

// ===== Auto-delete events older than 30 days =====
$deleteOld = $pdo->prepare("DELETE FROM events WHERE event_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$deleteOld->execute();

// ===== Fetch event details =====
$query = "SELECT * FROM events WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found!";
    exit;
}

/* Fetch linked club */
$club = null;
if (!empty($event['club_id'])) {
    $clubQuery = "SELECT * FROM clubs WHERE id = ?";
    $clubStmt = $pdo->prepare($clubQuery);
    $clubStmt->execute([$event['club_id']]);
    $club = $clubStmt->fetch();
}

// Check if event is in the past
$eventPast = (strtotime($event['event_date']) < time());

// ===== Review Handling =====
$reviewError = "";
$reviewSuccess = "";
$userHasReviewed = false;
$reviews = [];

// 1. Handle Submission (MUST be before header include)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $reviewText = trim($_POST['review_text']);
    
    if (empty($reviewText)) {
        $reviewError = "Review cannot be empty.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO event_reviews (user_id, event_id, review_text) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $event_id, $reviewText]);
            // Refresh to see the review and prevent resubmission
            header("Location: event_view.php?id=$event_id&review=success");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate)
                $reviewError = "You have already reviewed this event.";
            } else {
                $reviewError = "Error submitting review.";
            }
        }
    }
}

// 2. Check if user has reviewed
if (isset($_SESSION['user_id'])) {
    $checkRev = $pdo->prepare("SELECT id FROM event_reviews WHERE user_id = ? AND event_id = ?");
    $checkRev->execute([$_SESSION['user_id'], $event_id]);
    if ($checkRev->rowCount() > 0) {
        $userHasReviewed = true;
    }
}

// 3. Fetch Reviews
$revQuery = "
    SELECT r.*, SUBSTRING_INDEX(u.email, '@', 1) as username 
    FROM event_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.event_id = ? 
    ORDER BY r.created_at DESC
";
$revStmt = $pdo->prepare($revQuery);
$revStmt->execute([$event_id]);
$reviews = $revStmt->fetchAll();

// Include Header Last (contains HTML output)
include('../includes/header.php');
?>
