<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();
$club_id = getClubId();

if (!isset($_GET['id'])) {
    header("Location: club_admin_positions_manage.php");
    exit;
}

$position_id = intval($_GET['id']);

// Fetch Position
$stmt = $pdo->prepare("SELECT * FROM club_positions WHERE id = ? AND club_id = ?");
$stmt->execute([$position_id, $club_id]);
$position = $stmt->fetch();

if (!$position) {
    echo "Position not found or access denied.";
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $description = trim($_POST['description']);
    $photo = $position['photo']; // Keep old photo by default

    if (empty($name) || empty($role) || empty($description)) {
        $error = "All fields (Name, Position, Description) are required.";
    }

    // Handle Photo Upload
    if (empty($error) && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['photo']['name'];
        $filetype = $_FILES['photo']['type'];
        $filesize = $_FILES['photo']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Only JPG, PNG, and WEBP allowed.";
        } elseif ($filesize > 5 * 1024 * 1024) {
            $error = "File size too large. Max 5MB.";
        } else {
            $new_filename = uniqid() . "_pos_" . $club_id . "." . $ext;
            $upload_path = "../uploads/" . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if (!empty($position['photo']) && file_exists("../uploads/" . $position['photo'])) {
                    unlink("../uploads/" . $position['photo']);
                }
                $photo = $new_filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (empty($error)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE club_positions SET name = ?, role = ?, description = ?, photo = ? WHERE id = ?");
            $updateStmt->execute([$name, $role, $description, $photo, $position_id]);
            $success = "Position updated successfully!";
            
            // Refresh data
            $stmt->execute([$position_id, $club_id]);
            $position = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club Position - EventHorizan</title>
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
  <div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Edit Club Position</h1>
      <a href="club_admin_positions_manage.php" class="text-blue-600 hover:text-blue-800 font-medium">← Back to List</a>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-xl p-8">
      
      <!-- Name -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($position['name']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Role -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Position Title <span class="text-red-500">*</span></label>
        <input type="text" name="role" value="<?= htmlspecialchars($position['role'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Description -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Description / Bio <span class="text-red-500">*</span></label>
        <textarea name="description" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($position['description']) ?></textarea>
      </div>

      <!-- Current Photo -->
      <?php if (!empty($position['photo'])): ?>
        <div class="mb-6">
          <label class="block text-gray-700 font-bold mb-2">Current Photo</label>
          <img src="../uploads/<?= htmlspecialchars($position['photo']) ?>" class="h-32 w-32 object-cover rounded-lg border border-gray-300">
        </div>
      <?php endif; ?>

      <!-- New Photo -->
      <div class="mb-8">
        <label class="block text-gray-700 font-bold mb-2">Change Photo</label>
        <input type="file" name="photo" accept="image/*" class="w-full text-gray-700 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="text-gray-500 text-sm mt-1">Leave blank to keep current photo.</p>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
        Update Position
      </button>
    </form>
  </div>
</main>

</body>
</html>
