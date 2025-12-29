<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Verify ownership
    $checkStmt = $pdo->prepare("SELECT club_id FROM club_highlights WHERE id = ?");
    $checkStmt->execute([$delete_id]);
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event && $event['club_id'] == $club_id) {
        $stmt = $pdo->prepare("DELETE FROM club_highlights WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: club_admin_highlight_manage.php?deleted=success");
        exit();
    }
}

// Fetch highlights for this club only
$stmt = $pdo->prepare("SELECT * FROM club_highlights WHERE club_id = ? ORDER BY created_at DESC");
$stmt->execute([$club_id]);
$highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Club Highlights - Club Admin</title>
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
    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📅 Manage Events</a>
    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📜 Add Club Highlight</a>
    <a href="club_admin_highlight_manage.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">
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
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Manage Club Highlights</h2>

  <?php if (isset($_GET['added'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Club highlight added successfully!</div>
  <?php endif; ?>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Club highlight deleted successfully!</div>
  <?php endif; ?>

  <div class="bg-white shadow-md rounded-xl overflow-x-auto">
    <table class="w-full min-w-[800px] text-left border-collapse">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-3 border-b font-semibold text-gray-700">Title</th>
          <th class="p-3 border-b font-semibold text-gray-700">Description</th>
          <th class="p-3 border-b font-semibold text-gray-700">Main Image</th>
          <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($highlights)): ?>
          <tr>
            <td colspan="4" class="p-4 text-center text-gray-500">No club highlights found. <a href="club_admin_highlight_add.php" class="text-blue-600 underline">Add one!</a></td>
          </tr>
        <?php else: ?>
          <?php foreach($highlights as $event): ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-3 font-medium"><?= htmlspecialchars($event['event_title']) ?></td>
            <td class="p-3 text-gray-600 max-w-xs truncate"><?= htmlspecialchars($event['event_description']) ?></td>
            <td class="p-3">
              <?php if($event['main_image']): ?>
                <img src="../uploads/<?= $event['main_image'] ?>" alt="Main" class="h-16 object-cover rounded">
              <?php endif; ?>
            </td>
            <td class="p-3 space-x-2">
              <a href="club_admin_highlight_edit.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="?delete_id=<?= $event['id'] ?>" 
                 class="text-red-600 hover:underline"
                 onclick="return confirm('Delete this club highlight?')">
                 Delete
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
