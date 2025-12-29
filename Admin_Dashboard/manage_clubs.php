<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php');

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: manage_clubs.php?deleted=success");
    exit();
}

// Fetch all clubs
$stmt = $pdo->query("SELECT id, club_name, club_description, created_at FROM clubs ORDER BY created_at DESC");
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Clubs - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">

  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Dashboard</a>

    <a href="club_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Club</a>

    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">Manage Clubs</a>

    <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Event</a>

    <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club Highlight
    </a>

    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Club Highlights
    </a>

    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Events</a>

    <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">Logout</a>

  </nav>

</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-semibold mb-6">Manage Clubs</h1>

  <div class="bg-white shadow-lg rounded-xl p-6 overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[600px]">
      <thead class="bg-gray-50">
        <tr>
 
          <th class="p-3 border-b">Club Name</th>
          <th class="p-3 border-b">Description</th>
 
          <th class="p-3 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>

        <?php foreach($clubs as $club): ?>
          <tr class="border-b hover:bg-gray-50">
 
            <td class="p-3"><?= htmlspecialchars($club['club_name']) ?></td>
            <td class="p-3"><?= htmlspecialchars($club['club_description']) ?></td>
            

            <td class="p-3 space-x-2">
              <a href="edit_club.php?id=<?= $club['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              <a href="?delete_id=<?= $club['id'] ?>" 
                 onclick="return confirm('Are you sure you want to delete this club?');"
                 class="text-red-600 hover:underline">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if(empty($clubs)): ?>
          <tr>
            <td colspan="5" class="p-3 text-center text-gray-500">No clubs found</td>
          </tr>
        <?php endif; ?>

      </tbody>
    </table>
  </div>
</main>

</body>
</html>
