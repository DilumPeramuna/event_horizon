<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireSuperAdmin();

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = intval($_POST['club_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Check if club already has admin
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE club_id = ?");
        $checkStmt->execute([$club_id]);
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            $error = "This club already has admin credentials. Please delete the existing one first.";
        } else {
            // Check if username already exists
            $checkUsername = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
            $checkUsername->execute([$username]);
            if ($checkUsername->fetchColumn() > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Create credentials
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, role, club_id, created_at) VALUES (?, ?, 'club_admin', ?, NOW())");
                $stmt->execute([$username, $hashed_password, $club_id]);
                
                $success = "Club admin credentials created successfully!";
            }
        }
    }
}

// Check for pre-selected club
$selected_club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
$welcome_msg = isset($_GET['welcome']) ? "Club created successfully! Now create the admin credentials." : "";

if ($selected_club_id > 0) {
    // Check if this club already has an admin
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE club_id = ?");
    $checkStmt->execute([$selected_club_id]);
    if ($checkStmt->fetchColumn() > 0) {
        $error = "This club already has admin credentials.";
        $selected_club_id = 0; // Reset
    } else {
        $success = $welcome_msg;
    }
}

// Fetch clubs without admin OR the currently selected club (even if it technically doesn't have one yet, it will be in the list)
// Actually, the original query handles "without admin". If our new club has no admin, it will show up.
// We just need to make sure we pre-select it.
$clubsWithoutAdmin = $pdo->query("
    SELECT c.id, c.club_name 
    FROM clubs c
    WHERE c.id NOT IN (SELECT club_id FROM admin_users WHERE club_id IS NOT NULL)
    ORDER BY c.club_name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Credentials - Super Admin</title>
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
    <a href="super_admin_credentials_create.php" class="flex items-center gap-3 p-3 bg-purple-800 rounded-lg font-medium">🔑 Create Credentials</a>
    <a href="super_admin_credentials_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">👥 Manage Credentials</a>
    <div class="mt-auto pt-4 border-t border-purple-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Club Admin Credentials</h1>

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

    <?php if (empty($clubsWithoutAdmin)): ?>
      <div class="p-4 bg-blue-100 text-blue-700 rounded">
        <p>All clubs have admin credentials already. Create a new club first, or delete existing credentials to create new ones.</p>
        <a href="super_admin_club_add.php" class="underline mt-2 block">Add New Club</a>
      </div>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-medium mb-1">Select Club</label>
          <select name="club_id" class="w-full border p-2 rounded" required>
            <option value="">-- Select Club --</option>
            <?php foreach ($clubsWithoutAdmin as $club): ?>
              <option value="<?= $club['id'] ?>" <?= ($selected_club_id == $club['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($club['club_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-medium mb-1">Admin Username</label>
          <input type="text" name="username" class="w-full border p-2 rounded" required placeholder="Enter username for club admin">
        </div>

        <div>
          <label class="block font-medium mb-1">Admin Password</label>
          <input type="password" name="password" class="w-full border p-2 rounded" required placeholder="Enter password">
          <p class="text-sm text-gray-500 mt-1">Choose a strong password with at least 8 characters.</p>
        </div>

        <button type="submit" class="w-full bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 transition font-semibold">
          Create Credentials
        </button>
      </form>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
