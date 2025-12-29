<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

// Require Super Admin authentication
requireSuperAdmin();

// Fetch statistics
$totalClubs = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
$totalClubAdmins = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE role = 'club_admin'")->fetchColumn();

// Fetch latest clubs
$latestClubs = $pdo->query("
    SELECT c.id, c.club_name, c.created_at,
           (SELECT COUNT(*) FROM admin_users WHERE club_id = c.id) as has_admin
    FROM clubs c
    ORDER BY c.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin Dashboard - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-purple-900 to-purple-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-purple-200 text-sm mb-8">Super Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="super_admin_dashboard.php" class="flex items-center gap-3 p-3 bg-purple-800 rounded-lg font-medium">
      📊 Dashboard
    </a>

    <a href="super_admin_club_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">
      ➕ Add Club
    </a>

    <a href="super_admin_club_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">
      🏢 Manage Clubs
    </a>

    <div class="mt-auto pt-4 border-t border-purple-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">
        🚪 Logout
      </a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-3xl font-bold mb-2 text-gray-800">Super Admin Dashboard</h1>
  <p class="text-gray-600 mb-8">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</p>

  <!-- Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
    <div class="p-6 bg-white shadow-lg rounded-xl border-l-4 border-purple-500">
      <p class="text-sm text-gray-500 mb-1">Total Clubs</p>
      <h3 class="text-4xl font-bold text-gray-800"><?= $totalClubs ?></h3>
    </div>

    <div class="p-6 bg-white shadow-lg rounded-xl border-l-4 border-blue-500">
      <p class="text-sm text-gray-500 mb-1">Total Club Admins</p>
      <h3 class="text-4xl font-bold text-gray-800"><?= $totalClubAdmins ?></h3>
    </div>
  </div>

  <!-- Latest Clubs -->
  <div class="bg-white shadow-lg rounded-xl p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Latest Clubs</h2>
    
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50">
          <tr>
            <th class="p-3 border-b font-semibold text-gray-700">Club Name</th>
            <th class="p-3 border-b font-semibold text-gray-700">Created At</th>
            <th class="p-3 border-b font-semibold text-gray-700">Admin Status</th>
            <th class="p-3 border-b font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($latestClubs)): ?>
            <tr>
              <td colspan="4" class="p-4 text-center text-gray-500">No clubs found</td>
            </tr>
          <?php else: ?>
            <?php foreach($latestClubs as $club): ?>
              <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-3"><?= htmlspecialchars($club['club_name']) ?></td>
                <td class="p-3"><?= date('M d, Y', strtotime($club['created_at'])) ?></td>
                <td class="p-3">
                  <?php if ($club['has_admin'] > 0): ?>
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">✓ Has Admin</span>
                  <?php else: ?>
                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">⚠ No Admin</span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <a href="super_admin_club_manage.php" class="text-purple-600 hover:underline font-medium">View All</a>
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
