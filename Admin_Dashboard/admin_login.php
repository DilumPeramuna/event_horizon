<?php
session_start();

// If already logged in, redirect based on role
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if ($_SESSION['admin_role'] === 'super_admin') {
        header("Location: super_admin_dashboard.php");
    } else {
        header("Location: club_admin_dashboard.php");
    }
    exit();
}

require_once('../includes/db_connection.php');

$error = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check database for user
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['club_id'] = $admin['club_id'];

        // Redirect based on role
        if ($admin['role'] === 'super_admin') {
            header("Location: super_admin_dashboard.php");
        } else {
            header("Location: club_admin_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-black flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
  <div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">EventHorizan</h2>
    <p class="text-gray-500 mt-1">Admin Portal</p>
  </div>

  <?php if(!empty($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label for="username" class="block mb-1 font-medium text-gray-700">Username</label>
      <input type="text" name="username" id="username" required
             class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
             placeholder="Enter your username">
    </div>

    <div>
      <label for="password" class="block mb-1 font-medium text-gray-700">Password</label>
      <input type="password" name="password" id="password" required
             class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
             placeholder="Enter your password">
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Login
    </button>
  </form>

  <div class="mt-6 text-center text-sm text-gray-500">
    <p>Super Admin or Club Admin</p>
  </div>
</div>

</body>
</html>
