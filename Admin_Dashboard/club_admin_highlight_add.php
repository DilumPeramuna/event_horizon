<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $uploadPath = "../uploads/";

    if (empty($event_title) || empty($_FILES['main_image']['name'])) {
        $error = "Highlight title and main image are required.";
    } else {
        // Check duplicate title
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM club_highlights WHERE event_title = ?");
        $checkStmt->execute([$event_title]);
        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            $error = "A club highlight with this title already exists.";
        } else {
            // Upload images
            function uploadImage($inputName, $uploadPath) {
                if (!empty($_FILES[$inputName]["name"])) {
                    $fileName = time() . "_" . basename($_FILES[$inputName]["name"]);
                    move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath . $fileName);
                    return $fileName;
                }
                return null;
            }

            $main_image = uploadImage("main_image", $uploadPath);
            $img1 = uploadImage("image1", $uploadPath);
            $img2 = uploadImage("image2", $uploadPath);
            $img3 = uploadImage("image3", $uploadPath);

            // Insert with automatic club_id
            $stmt = $pdo->prepare("INSERT INTO club_highlights
                (club_id, event_title, event_description, main_image, extra_image_1, extra_image_2, extra_image_3, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([$club_id, $event_title, $description, $main_image, $img1, $img2, $img3]);

            // Limit to 3 highlights per club - delete oldest if exceeded
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM club_highlights WHERE club_id = ?");
            $countStmt->execute([$club_id]);
            $totalHighlights = $countStmt->fetchColumn();

            if ($totalHighlights > 3) {
                // Delete oldest highlights beyond the 3 most recent
                $deleteStmt = $pdo->prepare("
                    DELETE FROM club_highlights 
                    WHERE club_id = ? 
                    AND id NOT IN (
                        SELECT id FROM (
                            SELECT id FROM club_highlights 
                            WHERE club_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 3
                        ) AS recent
                    )
                ");
                $deleteStmt->execute([$club_id, $club_id]);
            }

            header("Location: club_admin_highlight_manage.php?added=success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Club Highlight - Club Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-blue-900 to-blue-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-blue-200 text-sm mb-8">Club Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="club_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📊 Dashboard</a>
    <a href="club_admin_club_edit.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">🏢 Edit Club Info</a>
    <a href="club_admin_event_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">➕ Add Event</a>
    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📅 Manage Events</a>
    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">🌟 Add Club Highlight</a>
    <a href="club_admin_highlight_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      🗂 Manage Club Highlights
    </a>

    <a href="club_admin_positions_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">
      👥 Manage Club Positions
    </a>

    <div class="mt-auto pt-4 border-t border-blue-600">
      <a href="admin_logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">🚪 Logout</a>
    </div>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Add Club Highlight</h1>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">
    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">Highlight Title</label>
        <input type="text" name="title" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Main Image <span class="text-red-600">*</span></label>
        <input type="file" name="main_image" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Extra Images</label>
        <input type="file" name="image1" class="w-full border p-2 rounded mb-2">
        <input type="file" name="image2" class="w-full border p-2 rounded mb-2">
        <input type="file" name="image3" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block mb-1 font-medium">Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded"></textarea>
      </div>

      <button class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Add Club Highlight
      </button>
    </form>
  </div>
</main>

</body>
</html>
