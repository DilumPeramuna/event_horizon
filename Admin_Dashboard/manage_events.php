<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php'); // DB connection

// ===== Handle Deletion =====
if (isset($_GET['delete_event'])) {
    $event_id = intval($_GET['delete_event']);
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    header("Location: manage_events.php?deleted=event");
    exit();
}

// ===== Fetch All Events with Club Names =====
$events = $pdo->query("
    SELECT e.*, c.club_name 
    FROM events e
    LEFT JOIN clubs c ON e.club_id = c.id
    ORDER BY e.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Events - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">

  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Dashboard
    </a>

    <a href="club_add.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Add Club
    </a>

    <a href="manage_clubs.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Manage Clubs
    </a>

    <a href="event_add.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Add Event
    </a>

    <a href="highlights_add.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Add Club Highlight
    </a>

    <a href="highlights_manage.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
       Manage Club Highlights
    </a>
    
    <a href="manage_events.php" 
       class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">
       Manage Events
    </a>
    


    <a href="admin_logout.php" 
       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">
       Logout
    </a>

  </nav>

</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h2 class="text-2xl font-semibold mb-6">Manage Events</h2>

  <div class="bg-white shadow-md rounded-xl overflow-x-auto">
    <table class="w-full min-w-[1200px] text-left border-collapse">
      <thead class="bg-gray-50">
        <tr>
      
          <th class="p-3 border-b">Title</th>
          <th class="p-3 border-b">Description</th>
          <th class="p-3 border-b">Event Date</th>
          <th class="p-3 border-b">Main Image</th>
          <th class="p-3 border-b">Club</th>
          <th class="p-3 border-b">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach($events as $event): ?>
        <tr class="border-b hover:bg-gray-50">

         

          <td class="p-3"><?= htmlspecialchars($event['title']) ?></td>

          <td class="p-3"><?= htmlspecialchars($event['description']) ?></td>

          <td class="p-3"><?= $event['event_date'] ?></td>

          <td class="p-3">
            <?php if($event['main_image']): ?>
              <img src="../uploads/<?= $event['main_image'] ?>" 
                   alt="Main" 
                   class="h-16 object-cover rounded">
            <?php endif; ?>
          </td>

          <td class="p-3"><?= htmlspecialchars($event['club_name'] ?? 'N/A') ?></td>

          <td class="p-3 space-x-2">
            <a href="edit_event.php?id=<?= $event['id'] ?>" 
               class="text-blue-600 hover:underline">Edit</a>

            <a href="?delete_event=<?= $event['id'] ?>" 
               class="text-red-600 hover:underline"
               onclick="return confirm('Are you sure you want to delete this event?')">
               Delete
            </a>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
