<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ? AND role = 'club_admin'");
    $stmt->execute([$delete_id]);
    header("Location: super_admin_credentials_manage.php?deleted=success");
    exit();
}

// Fetch all club admin credentials
$stmt = $pdo->query("
    SELECT a.id, a.username, a.created_at, c.club_name
    FROM admin_users a
    JOIN clubs c ON a.club_id = c.id
    WHERE a.role = 'club_admin'
    ORDER BY a.created_at DESC
");
$credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Credentials - Super Admin</title>
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
    <a href="super_admin_club_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🏢 Manage Clubs</a>
    <a href="super_admin_credentials_create.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🔑 Create Credentials</a>
    <a href="super_admin_credentials_manage.php" class="flex items-center gap-3 p-3 bg-purple-800 rounded-lg font-medium">👥 Manage Credentials</a>
    <div class="mt-auto pt-4 border-t border-purple-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage Club Admin Credentials</h1>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">Credentials deleted successfully!</div>
  <?php endif; ?>

  <div class="bg-white shadow-lg rounded-xl p-6 overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[500px]">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-3 border-b font-semibold text-gray-700">Club Name</th>
          <th class="p-3 border-b font-semibold text-gray-700">Username</th>
          <th class="p-3 border-b font-semibold text-gray-700">Created At</th>
          <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($credentials)): ?>
          <tr>
            <td colspan="4" class="p-4 text-center text-gray-500">No club admin credentials found</td>
          </tr>
        <?php else: ?>
          <?php foreach($credentials as $cred): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="p-3 font-medium"><?= htmlspecialchars($cred['club_name']) ?></td>
              <td class="p-3"><?= htmlspecialchars($cred['username']) ?></td>
              <td class="p-3 text-gray-600"><?= date('M d, Y', strtotime($cred['created_at'])) ?></td>
              <td class="p-3 flex items-center gap-3">
                <a href="super_admin_credentials_edit.php?id=<?= $cred['id'] ?>" 
                   class="text-blue-600 hover:underline">Edit</a>
                <span class="text-gray-300">|</span>
                <a href="?delete_id=<?= $cred['id'] ?>" 
                   onclick="return confirm('Delete these credentials? The club admin will no longer be able to log in.');"
                   class="text-red-600 hover:underline">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-6">
    <a href="super_admin_credentials_create.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
      + Create New Credentials
    </a>
  </div>
</main>

</body>
</html>
