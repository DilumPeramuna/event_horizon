<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

include("../includes/db_connection.php");

// ===== Fetch Dashboard Stats =====
// Total Clubs
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM clubs");
$TotalClubs = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total Events
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM events");
$TotalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total Reminders
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM reminders");
$TotalReminders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Latest 5 Clubs
$stmt = $pdo->query("SELECT id, club_name, created_at FROM clubs ORDER BY created_at DESC LIMIT 5");
$LatestClubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Latest 5 Events
$stmt = $pdo->query("SELECT id, title, event_date, created_at FROM events ORDER BY created_at DESC LIMIT 5");
$LatestEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Latest 5 Reminders
$stmt = $pdo->query("
    SELECT r.id, u.email AS user_email, e.title AS event_title, r.created_at
    FROM reminders r
    JOIN events e ON r.event_id = e.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$LatestReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderTable($title, $columns, $rows, $type = null) {
    echo '<div class="p-6 mb-10 bg-white shadow-md rounded-xl overflow-x-auto">';
    echo "<h3 class='text-lg font-semibold mb-4'>{$title}</h3>";
    echo '<table class="w-full min-w-[400px] text-left border-collapse">';
    echo '<thead class="bg-gray-50"><tr>';
    foreach ($columns as $col) echo "<th class='p-3'>{$col}</th>";
    echo '</tr></thead><tbody>';

    foreach ($rows as $row) {
        echo '<tr class="border-b hover:bg-gray-50">';
        foreach ($row as $cell) echo "<td class='p-3'>{$cell}</td>";

        if ($type === 'club') {
            $id = $row[0];
            echo "<td class='p-3'><a href='edit_club.php?id={$id}' class='text-blue-600 hover:underline mr-2'>Edit</a>";
            echo "<a href='manage_clubs.php?delete={$id}' class='text-red-600 hover:underline' onclick='return confirm(\"Delete this club?\")'>Delete</a></td>";
        } elseif ($type === 'event') {
            $id = $row[0];
            echo "<td class='p-3'><a href='edit_event.php?id={$id}' class='text-blue-600 hover:underline mr-2'>Edit</a>";
            echo "<a href='manage_events.php?delete={$id}' class='text-red-600 hover:underline' onclick='return confirm(\"Delete this event?\")'>Delete</a></td>";
        }

        echo '</tr>';
    }

    echo '</tbody></table></div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">
      Dashboard
    </a>

    <a href="club_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club
    </a>

    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Clubs
    </a>

    <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Event
    </a>

    <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club Highlight
    </a>

     <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Club Highlights
    </a>

    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Events
    </a>

   

    <a href="admin_logout.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg mt-2">
      Logout
    </a>

  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h2 class="mb-6 text-2xl font-semibold">Dashboard Overview</h2>

  <!-- Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
    <div class="p-4 bg-white shadow-md rounded-xl">
      <p class="text-sm text-gray-500">Total Clubs</p>
      <h3 class="text-2xl font-bold"><?= $TotalClubs; ?></h3>
    </div>

    <div class="p-4 bg-white shadow-md rounded-xl">
      <p class="text-sm text-gray-500">Total Events</p>
      <h3 class="text-2xl font-bold"><?= $TotalEvents; ?></h3>
    </div>

    <div class="p-4 bg-white shadow-md rounded-xl">
      <p class="text-sm text-gray-500">Total Reminders</p>
      <h3 class="text-2xl font-bold"><?= $TotalReminders; ?></h3>
    </div>
  </div>

  <!-- Tables -->
  <?php
    $clubRows = array_map(fn($c) => [ htmlspecialchars($c['club_name']), $c['created_at']], $LatestClubs);
    renderTable('Latest Clubs', ['Club Name','Created At','Actions'], $clubRows, 'club');

    $eventRows = array_map(fn($e) => [htmlspecialchars($e['title']), $e['event_date'], $e['created_at']], $LatestEvents);
    renderTable('Latest Events', ['Event Title','Event Date','Created At','Actions'], $eventRows, 'event');

    $reminderRows = array_map(fn($r) => [ htmlspecialchars($r['user_email']), htmlspecialchars($r['event_title']), $r['created_at']], $LatestReminders);
    renderTable('Latest Reminders', ['User Email','Event Title','Added At'], $reminderRows);
  ?>
</main>

</body>
</html>
