<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_name = trim($_POST['club_name']);
    $username = trim($_POST['admin_username']);
    $password = trim($_POST['admin_password']);
    
    if (empty($club_name) || empty($username) || empty($password)) {
        $error = "All fields (Club Name, Admin Username, Password) are required.";
    } else {
        // Validate Uniqueness
        $checkClub = $pdo->prepare("SELECT COUNT(*) FROM clubs WHERE club_name = ?");
        $checkClub->execute([$club_name]);
        
        $checkUser = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $checkUser->execute([$username]);
        
        if ($checkClub->fetchColumn() > 0) {
            $error = "A club with this name already exists.";
        } elseif ($checkUser->fetchColumn() > 0) {
            $error = "Username '$username' is already taken. Please choose another.";
        } else {
            // Begin Transaction
            $pdo->beginTransaction();
            
            try {
                // 1. Insert Club
                $stmt = $pdo->prepare("INSERT INTO clubs (club_name, created_at) VALUES (?, NOW())");
                $stmt->execute([$club_name]);
                $club_id = $pdo->lastInsertId();
                
                // 2. Insert Admin Credentials
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $adminStmt = $pdo->prepare("INSERT INTO admin_users (username, password, role, club_id, created_at) VALUES (?, ?, 'club_admin', ?, NOW())");
                $adminStmt->execute([$username, $hashedPassword, $club_id]);
                
                $pdo->commit();
                
                header("Location: super_admin_club_manage.php?added=success");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to create club and credentials. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Club - Super Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-purple-900 to-purple-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-purple-200 text-sm mb-8">Super Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="super_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">📊 Dashboard</a>
    <a href="super_admin_club_add.php" class="flex items-center gap-3 p-3 bg-purple-800 rounded-lg font-medium">➕ Add Club</a>
    <a href="super_admin_club_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🏢 Manage Clubs</a>
    <div class="mt-auto pt-4 border-t border-purple-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h2 class="text-2xl font-bold mb-2 text-gray-800">Add New Club</h2>
  <p class="text-gray-600 mb-6">Create a new club with just a name. The Club Admin will add all other details (description, images, contact info).</p>

  <div class="max-w-2xl bg-white p-8 shadow-lg rounded-xl">
    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
      <!-- Club Name -->
      <div>
        <label class="block mb-2 font-semibold text-gray-700">1. Club Name <span class="text-red-500">*</span></label>
        <input type="text" name="club_name" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., Music Club" required>
      </div>

      <hr class="border-gray-200">

      <div>
        <h3 class="font-bold text-gray-800 mb-3">2. Create Admin Credentials</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Admin Username <span class="text-red-500">*</span></label>
                <input type="text" name="admin_username" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g. music_admin" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                <input type="password" name="admin_password" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="********" required>
            </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 transition font-semibold text-lg shadow-lg hover:shadow-xl">
        Create Club
      </button>
    </form>
  </div>
</main>


</body>
</html>
