<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Fetch club info
$clubStmt = $pdo->prepare("SELECT * FROM clubs WHERE id = ?");
$clubStmt->execute([$club_id]);
$club = $clubStmt->fetch(PDO::FETCH_ASSOC);

// Fetch statistics for this club only
$totalEvents = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id = ?");
$totalEvents->execute([$club_id]);
$eventsCount = $totalEvents->fetchColumn();

$totalHighlights = $pdo->prepare("SELECT COUNT(*) FROM club_highlights WHERE club_id = ?");
$totalHighlights->execute([$club_id]);
$highlightsCount = $totalHighlights->fetchColumn();

// Latest 5 events for this club
$latestEvents = $pdo->prepare("
    SELECT id, title, event_date, created_at 
    FROM events 
    WHERE club_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$latestEvents->execute([$club_id]);
$events = $latestEvents->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Club Admin Dashboard - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-blue-900 to-blue-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-1">EventHorizan</h2>
  <p class="text-blue-200 text-sm mb-2">Club Admin</p>
  <p class="text-blue-300 text-xs mb-8 font-semibold"><?= htmlspecialchars($club['club_name']) ?></p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="club_admin_dashboard.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">
      📊 Dashboard
    </a>

    <a href="club_admin_club_edit.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      🏢 Edit Club Info
    </a>

    <a href="club_admin_event_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      ➕ Add Event
    </a>

    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      📅 Manage Events
    </a>

    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      📜 Add Club Highlight
    </a>

    <a href="club_admin_highlight_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      🗂 Manage Club Highlights
    </a>

    <a href="club_admin_positions_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      👥 Manage Club Positions
    </a>

    <div class="mt-auto pt-4 border-t border-blue-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
        🚪 Logout
      </a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-3xl font-bold mb-2 text-gray-800">Club Dashboard</h1>
  <p class="text-gray-600 mb-8">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>! Managing: <strong><?= htmlspecialchars($club['club_name']) ?></strong></p>

  <!-- Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
    <div class="p-6 bg-white shadow-lg rounded-xl border-l-4 border-blue-500">
      <p class="text-sm text-gray-500 mb-1">Total Events</p>
      <h3 class="text-4xl font-bold text-gray-800"><?= $eventsCount ?></h3>
    </div>

    <div class="p-6 bg-white shadow-lg rounded-xl border-l-4 border-green-500">
      <p class="text-sm text-gray-500 mb-1">Total Club Highlights</p>
      <h3 class="text-4xl font-bold text-gray-800"><?= $highlightsCount ?></h3>
    </div>
  </div>

  <!-- Latest Events -->
  <div class="bg-white shadow-lg rounded-xl p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Latest Events</h2>
    
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50">
          <tr>
            <th class="p-3 border-b font-semibold text-gray-700">Event Title</th>
            <th class="p-3 border-b font-semibold text-gray-700">Event Date</th>
            <th class="p-3 border-b font-semibold text-gray-700">Created At</th>
            <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="4" class="p-4 text-center text-gray-500">No events found. <a href="club_admin_event_add.php" class="text-blue-600 underline">Add your first event!</a></td>
            </tr>
          <?php else: ?>
            <?php foreach($events as $event): ?>
              <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-3 font-medium"><?= htmlspecialchars($event['title']) ?></td>
                <td class="p-3"><?= date('M d, Y h:i A', strtotime($event['event_date'])) ?></td>
                <td class="p-3 text-gray-600"><?= date('M d, Y', strtotime($event['created_at'])) ?></td>
                <td class="p-3">
                  <a href="club_admin_event_edit.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

</body>
</html>
