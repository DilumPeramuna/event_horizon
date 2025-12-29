<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();
$club_id = getClubId();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $description = trim($_POST['description']);
    $photo = null;

    if (empty($name) || empty($role) || empty($description)) {
        $error = "All fields are required.";
    }

    // Handle Photo Upload
    if (empty($error)) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
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
                    $photo = $new_filename;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        } else {
            $error = "Photo is required.";
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO club_positions (club_id, name, role, description, photo, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$club_id, $name, $role, $description, $photo]);
            $success = "Position added successfully!";
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
<title>Add Club Position - EventHorizan</title>
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
      <h1 class="text-3xl font-bold text-gray-800">Add Club Position</h1>
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
        <a href="club_admin_positions_manage.php" class="underline font-bold ml-2">View All Positions</a>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-xl p-8">
      
      <!-- Name -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. John Doe">
      </div>

      <!-- Role -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Position Title <span class="text-red-500">*</span></label>
        <input type="text" name="role" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. President, Secretary">
      </div>

      <!-- Description -->
      <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Description / Bio <span class="text-red-500">*</span></label>
        <textarea name="description" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief description about the position holder..."></textarea>
      </div>

      <!-- Photo -->
      <div class="mb-8">
        <label class="block text-gray-700 font-bold mb-2">Photo <span class="text-red-500">*</span></label>
        <input type="file" name="photo" accept="image/*" required class="w-full text-gray-700 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="text-gray-500 text-sm mt-1">Recommended size: 500x500px. Max: 5MB.</p>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
        Add Position
      </button>
    </form>
  </div>
</main>

</body>
</html>
