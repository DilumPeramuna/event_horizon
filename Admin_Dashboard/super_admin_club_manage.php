<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: super_admin_club_manage.php?deleted=success");
    exit();
}

// Fetch all clubs with admin status
$stmt = $pdo->query("
    SELECT c.id, c.club_name, c.club_description, c.created_at,
           (SELECT COUNT(*) FROM admin_users WHERE club_id = c.id) as has_admin,
           (SELECT username FROM admin_users WHERE club_id = c.id LIMIT 1) as admin_username
    FROM clubs c
    ORDER BY c.created_at DESC
");
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Clubs - Super Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-purple-900 to-purple-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-purple-200 text-sm mb-8">Super Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="super_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">📊 Dashboard</a>
    <a href="super_admin_club_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">➕ Add Club</a>
    <a href="super_admin_club_manage.php" class="flex items-center gap-3 p-3 bg-purple-800 rounded-lg font-medium">🏢 Manage Clubs</a>
    <div class="mt-auto pt-4 border-t border-purple-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage Club Details</h1>

  <?php if (isset($_GET['added'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Club added successfully!</div>
  <?php endif; ?>
  
  <?php if (isset($_GET['deleted'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Club deleted successfully!</div>
  <?php endif; ?>

  <div class="bg-white shadow-lg rounded-xl p-6 overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[600px]">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-3 border-b font-semibold text-gray-700">Club Name</th>
          <th class="p-3 border-b font-semibold text-gray-700">Admin Status</th>
          <th class="p-3 border-b font-semibold text-gray-700">Admin Username</th>
          <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clubs)): ?>
          <tr>
            <td colspan="4" class="p-4 text-center text-gray-500">No clubs found</td>
          </tr>
        <?php else: ?>
          <?php foreach($clubs as $club): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="p-3 font-medium"><?= htmlspecialchars($club['club_name']) ?></td>
              <td class="p-3">
                <?php if ($club['has_admin'] > 0): ?>
                  <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">✓ Active</span>
                <?php else: ?>
                  <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">⚠ No Admin</span>
                <?php endif; ?>
              </td>
              <td class="p-3 text-gray-600">
                <?= $club['admin_username'] ? htmlspecialchars($club['admin_username']) : '-' ?>
              </td>
              <td class="p-3 flex items-center gap-3">
                <a href="super_admin_club_edit.php?id=<?= $club['id'] ?>" 
                   class="text-blue-600 hover:underline">Edit</a>
                <span class="text-gray-300">|</span>
                <a href="?delete_id=<?= $club['id'] ?>" 
                   onclick="return confirm('Delete this club? This will also delete all associated events and admin credentials.');"
                   class="text-red-600 hover:underline">Delete</a>
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
