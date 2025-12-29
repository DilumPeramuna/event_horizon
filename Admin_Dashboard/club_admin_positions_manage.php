<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();
$club_id = getClubId();

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT photo FROM club_positions WHERE id = ? AND club_id = ?");
    $stmt->execute([$delete_id, $club_id]);
    $position = $stmt->fetch();
    
    if ($position) {
        if (!empty($position['photo']) && file_exists("../uploads/" . $position['photo'])) {
            unlink("../uploads/" . $position['photo']);
        }
        
        $deleteStmt = $pdo->prepare("DELETE FROM club_positions WHERE id = ?");
        $deleteStmt->execute([$delete_id]);
        
        $success_msg = "Position removed successfully.";
    }
}

// Fetch Positions
$stmt = $pdo->prepare("SELECT * FROM club_positions WHERE club_id = ? ORDER BY created_at DESC");
$stmt->execute([$club_id]);
$positions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Club Positions - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-blue-900 to-blue-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-1">EventHorizan</h2>
  <p class="text-blue-200 text-sm mb-8">Club Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="club_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
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

    <a href="club_admin_positions_manage.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">
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
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Club Positions</h1>
    <a href="club_admin_position_add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition shadow-md flex items-center gap-2">
      ➕ Add New Position
    </a>
  </div>

  <?php if (isset($success_msg)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline"><?= htmlspecialchars($success_msg) ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white shadow-lg rounded-xl overflow-hidden">
    <table class="w-full text-left border-collapse">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-4 border-b font-semibold text-gray-700">Photo</th>
          <th class="p-4 border-b font-semibold text-gray-700">Name</th>
          <th class="p-4 border-b font-semibold text-gray-700">Description</th>
          <th class="p-4 border-b font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($positions)): ?>
          <tr>
            <td colspan="4" class="p-8 text-center text-gray-500">
              No positions added yet. 
              <a href="club_admin_position_add.php" class="text-blue-600 hover:underline">Add one now!</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($positions as $position): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="p-4">
                <?php if ($position['photo']): ?>
                  <img src="../uploads/<?= htmlspecialchars($position['photo']) ?>" alt="Position Photo" class="h-12 w-12 rounded-full object-cover border border-gray-200">
                <?php else: ?>
                  <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">No Img</div>
                <?php endif; ?>
              </td>
              <td class="p-4 font-medium text-gray-900"><?= htmlspecialchars($position['name']) ?></td>
              <td class="p-4 text-gray-600 text-sm max-w-xs truncate"><?= htmlspecialchars($position['description']) ?></td>
              <td class="p-4 flex gap-3">
                <a href="club_admin_position_edit.php?id=<?= $position['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this position?');">
                  <input type="hidden" name="delete_id" value="<?= $position['id'] ?>">
                  <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Remove</button>
                </form>
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
