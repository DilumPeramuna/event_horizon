<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Handle delete
if (isset($_GET['delete_event'])) {
    $event_id = intval($_GET['delete_event']);
    
    // Verify this event belongs to the logged-in club
    $checkStmt = $pdo->prepare("SELECT club_id FROM events WHERE id = ?");
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event && $event['club_id'] == $club_id) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        header("Location: club_admin_event_manage.php?deleted=success");
        exit();
    }
}

// Fetch events only for this club
$events = $pdo->prepare("
    SELECT e.*,
           (SELECT COUNT(*) FROM event_likes el WHERE el.event_id = e.id) as like_count,
           (SELECT COUNT(*) FROM reminders r WHERE r.event_id = e.id) as reminder_count
    FROM events e
    WHERE e.club_id = ?
    ORDER BY e.created_at DESC
");
$events->execute([$club_id]);
$eventsList = $events->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Events - Club Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-blue-900 to-blue-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-blue-200 text-sm mb-8">Club Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="club_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📊 Dashboard</a>
    <a href="club_admin_club_edit.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">🏢 Edit Club Info</a>
    <a href="club_admin_event_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">➕ Add Event</a>
    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">📅 Manage Events</a>
    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📜 Add Club Highlight</a>
    <a href="club_admin_highlight_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      🗂 Manage Club Highlights
    </a>

    <a href="club_admin_positions_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      👥 Manage Club Positions
    </a>

    <div class="mt-auto pt-4 border-t border-blue-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Manage Events</h2>

  <?php if (isset($_GET['added'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Event added successfully!</div>
  <?php endif; ?>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Event deleted successfully!</div>
  <?php endif; ?>

  <div class="bg-white shadow-md rounded-xl overflow-x-auto">
    <table class="w-full min-w-[1000px] text-left border-collapse">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-3 border-b font-semibold text-gray-700">Title</th>
          <th class="p-3 border-b font-semibold text-gray-700">Description</th>
          <th class="p-3 border-b font-semibold text-gray-700">Event Date</th>
          <th class="p-3 border-b font-semibold text-gray-700">Stats</th>
          <th class="p-3 border-b font-semibold text-gray-700">Main Image</th>
          <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($eventsList)): ?>
          <tr>
            <td colspan="6" class="p-4 text-center text-gray-500">No events found. <a href="club_admin_event_add.php" class="text-blue-600 underline">Add your first event</a></td>
          </tr>
        <?php else: ?>
          <?php 
            // Pre-fetch reviews for this club's events
            $reviewsStmt = $pdo->prepare("
                SELECT r.*, SUBSTRING_INDEX(u.email, '@', 1) as username 
                FROM event_reviews r
                JOIN events e ON r.event_id = e.id
                JOIN users u ON r.user_id = u.id
                WHERE e.club_id = ?
                ORDER BY r.created_at DESC
            ");
            $reviewsStmt->execute([$club_id]);
            $allReviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Group reviews by event_id
            $reviewsByEvent = [];
            foreach ($allReviews as $rev) {
                $reviewsByEvent[$rev['event_id']][] = $rev;
            }
          ?>

          <?php foreach($eventsList as $event): 
             $isPast = (strtotime($event['event_date']) < time());
             $eventReviews = $reviewsByEvent[$event['id']] ?? [];
          ?>
          <!-- Main Event Row -->
          <tr class="border-b hover:bg-gray-50 transition <?= $isPast ? 'bg-gray-50' : '' ?>">
            <td class="p-3 font-medium">
                <?= htmlspecialchars($event['title']) ?>
                <?php if($isPast): ?>
                    <span class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Passed</span>
                <?php endif; ?>
            </td>
            <td class="p-3 text-gray-600 max-w-xs truncate"><?= htmlspecialchars($event['description']) ?></td>
            <td class="p-3"><?= date('M d, Y h:i A', strtotime($event['event_date'])) ?></td>
            <td class="p-3">
                <div class="flex flex-col gap-1 text-sm">
                    <span class="text-pink-600 font-medium whitespace-nowrap">❤️ <?= $event['like_count'] ?> Likes</span>
                    <span class="text-blue-600 font-medium whitespace-nowrap">🔔 <?= $event['reminder_count'] ?> Reminders</span>
                    <?php if($isPast): ?>
                        <span class="text-purple-600 font-medium whitespace-nowrap">💬 <?= count($eventReviews) ?> Reviews</span>
                    <?php endif; ?>
                </div>
            </td>
            <td class="p-3">
              <?php if($event['main_image']): ?>
                <img src="../uploads/<?= $event['main_image'] ?>" alt="Main" class="h-16 object-cover rounded">
              <?php endif; ?>
            </td>
            <td class="p-3 space-x-2">
              <a href="club_admin_event_edit.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="?delete_event=<?= $event['id'] ?>" 
                 class="text-red-600 hover:underline"
                 onclick="return confirm('Are you sure you want to delete this event?')">
                 Delete
              </a>
            </td>
          </tr>

          <!-- Reviews Row (Only for Past Events) -->
          <?php if($isPast): ?>
            <tr class="bg-gray-50/50 border-b-2 border-gray-100">
                <td colspan="6" class="p-4 pl-10">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">📢 User Feedback:</h4>
                    <?php if (!empty($eventReviews)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php foreach($eventReviews as $review): ?>
                                <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm text-sm">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-blue-600"><?= htmlspecialchars($review['username']) ?></span>
                                        <span class="text-xs text-gray-400"><?= date('M d', strtotime($review['created_at'])) ?></span>
                                    </div>
                                    <p class="text-gray-700 leading-snug">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 italic">No reviews received yet.</p>
                    <?php endif; ?>
                </td>
            </tr>
          <?php endif; ?>

          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
