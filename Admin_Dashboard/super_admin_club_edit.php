<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

if (!isset($_GET['id'])) {
    header("Location: super_admin_club_manage.php");
    exit();
}

$club_id = intval($_GET['id']);
$error = "";
$success = "";

// Fetch club and admin data
$stmt = $pdo->prepare("
    SELECT c.club_name, c.id, a.username, a.id as admin_id 
    FROM clubs c 
    LEFT JOIN admin_users a ON c.id = a.club_id 
    WHERE c.id = ?
");
$stmt->execute([$club_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header("Location: super_admin_club_manage.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_name = trim($_POST['club_name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($club_name)) {
        $error = "Club name is required.";
    } else {
        // 1. Update Club Name
        $updateClub = $pdo->prepare("UPDATE clubs SET club_name = ? WHERE id = ?");
        $updateClub->execute([$club_name, $club_id]);
        
        // 2. Handle Admin Credentials
        if (!empty($username)) {
            // Check for duplicate username (excluding current user if they exist)
            $checkSql = "SELECT COUNT(*) FROM admin_users WHERE username = ?";
            $params = [$username];
            
            if ($data['admin_id']) {
                $checkSql .= " AND id != ?";
                $params[] = $data['admin_id'];
            }
            
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute($params);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Username '$username' already exists. Club name updated, but credentials were not.";
            } else {
                if ($data['admin_id']) {
                    // Update existing admin
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $adminStmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
                        $adminStmt->execute([$username, $hash, $data['admin_id']]);
                    } else {
                        $adminStmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                        $adminStmt->execute([$username, $data['admin_id']]);
                    }
                } else {
                    // Create new admin
                    if (empty($password)) {
                        $error = "Password is required when creating new credentials.";
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $adminStmt = $pdo->prepare("INSERT INTO admin_users (username, password, role, club_id, created_at) VALUES (?, ?, 'club_admin', ?, NOW())");
                        $adminStmt->execute([$username, $hash, $club_id]);
                    }
                }
            }
        }
        
        if (empty($error)) {
            $success = "Club details updated successfully!";
            // Refresh data
            $stmt->execute([$club_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club Details - Super Admin</title>
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
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Club Details</h1>
        <a href="super_admin_club_manage.php" class="text-purple-600 hover:underline">← Back to List</a>
    </div>

  <div class="max-w-2xl bg-white p-8 shadow-lg rounded-xl">
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
      
      <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
          <label class="block font-semibold mb-2 text-gray-700">Club Name</label>
          <input type="text" name="club_name" value="<?= htmlspecialchars($data['club_name']) ?>" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" required>
      </div>

      <hr class="border-gray-200">

      <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
          <h3 class="font-bold text-purple-800 mb-4 flex items-center gap-2">
            🔑 Club Admin Credentials
          </h3>
          
          <div class="mb-4">
            <label class="block font-medium mb-2 text-gray-700">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g. music_admin">
          </div>

          <div>
            <label class="block font-medium mb-2 text-gray-700">Password</label>
            <input type="password" name="password" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="<?= $data['username'] ? 'Leave blank to keep current password' : 'Enter password for new admin' ?>">
            <?php if(!$data['username']): ?>
                <p class="text-xs text-red-500 mt-1">* Required when creating a new admin</p>
            <?php endif; ?>
          </div>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 transition font-semibold text-lg shadow">
          Save Details
        </button>
      </div>
    </form>
  </div>
</main>

</body>
</html>
