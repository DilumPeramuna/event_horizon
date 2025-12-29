<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

$error = "";
$success = "";
$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($admin_id === 0) {
    header("Location: super_admin_credentials_manage.php");
    exit();
}

// Fetch existing details
$stmt = $pdo->prepare("
    SELECT a.*, c.club_name 
    FROM admin_users a 
    JOIN clubs c ON a.club_id = c.id 
    WHERE a.id = ? AND a.role = 'club_admin'
");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header("Location: super_admin_credentials_manage.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $error = "Username is required.";
    } else {
        // Check if username already exists for OTHER users
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? AND id != ?");
        $checkStmt->execute([$username, $admin_id]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $error = "Username already exists. Please choose a different one.";
        } else {
            // Update credentials
            if (!empty($password)) {
                // Update username AND password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
                $updateStmt->execute([$username, $hashed_password, $admin_id]);
            } else {
                // Update ONLY username
                $updateStmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                $updateStmt->execute([$username, $admin_id]);
            }
            
            $success = "Credentials updated successfully!";
            // Refresh data
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Credentials - Super Admin</title>
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
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Club Admin Credentials</h1>
        <a href="super_admin_credentials_manage.php" class="text-purple-600 hover:underline">← Back to List</a>
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

    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-medium mb-1 text-gray-600">Club Name</label>
        <input type="text" value="<?= htmlspecialchars($admin['club_name']) ?>" class="w-full border p-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed" disabled>
        <p class="text-xs text-gray-500 mt-1">To change the club, delete these credentials and create new ones.</p>
      </div>

      <div>
        <label class="block font-medium mb-1">Admin Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-purple-500" required>
      </div>

      <div>
        <label class="block font-medium mb-1">New Password</label>
        <input type="password" name="password" class="w-full border p-2 rounded focus:ring-2 focus:ring-purple-500" placeholder="Leave blank to keep current password">
        <p class="text-sm text-gray-500 mt-1">Enter a value only if you want to change the password.</p>
      </div>

      <div class="pt-4 flex gap-4">
        <button type="submit" class="flex-1 bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 transition font-semibold">
          Update Credentials
        </button>
      </div>
    </form>
  </div>
</main>

</body>
</html>
