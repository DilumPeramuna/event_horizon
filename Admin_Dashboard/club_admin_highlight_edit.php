<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Validate ID
if (!isset($_GET['id'])) {
    header("Location: club_admin_highlight_manage.php");
    exit;
}

$highlight_id = intval($_GET['id']);

// Fetch highlight and verify ownership
$stmt = $pdo->prepare("SELECT * FROM club_highlights WHERE id = ?");
$stmt->execute([$highlight_id]);
$highlight = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$highlight || $highlight['club_id'] != $club_id) {
    header("Location: club_admin_highlight_manage.php");
    exit;
}

$uploadPath = "../uploads/";

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];

    function uploadImage($input, $current, $uploadPath) {
        if (!empty($_FILES[$input]["name"])) {
            $fileName = time() . "_" . basename($_FILES[$input]["name"]);
            move_uploaded_file($_FILES[$input]["tmp_name"], $uploadPath . $fileName);
            return $fileName;
        }
        return $current;
    }

    $main_image = uploadImage("main_image", $highlight['main_image'], $uploadPath);
    $img1 = uploadImage("image1", $highlight['extra_image_1'], $uploadPath);
    $img2 = uploadImage("image2", $highlight['extra_image_2'], $uploadPath);
    $img3 = uploadImage("image3", $highlight['extra_image_3'], $uploadPath);

    $stmt = $pdo->prepare("UPDATE club_highlights 
        SET event_title=?, event_description=?, main_image=?, extra_image_1=?, extra_image_2=?, extra_image_3=?
        WHERE id=? AND club_id=?");

    $stmt->execute([$title, $description, $main_image, $img1, $img2, $img3, $highlight_id, $club_id]);

    header("Location: club_admin_highlight_manage.php?updated=success");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club Highlight - Club Admin</title>
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
    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📜 Add Club Highlight</a>
    <a href="club_admin_highlight_manage.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">🗂 Manage Club Highlights</a>
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
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Club Highlight</h1>

  <div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl mx-auto">
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Highlight Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($highlight['event_title']) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded"><?= htmlspecialchars($highlight['event_description']) ?></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <?php if($highlight['main_image']): ?>
          <img src="../uploads/<?= $highlight['main_image'] ?>" class="h-20 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="main_image" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Images</label>
        
        <div class="flex gap-3 mb-2">
          <?php if($highlight['extra_image_1']): ?>
            <img src="../uploads/<?= $highlight['extra_image_1'] ?>" class="h-16 w-16 rounded">
          <?php endif; ?>
          <?php if($highlight['extra_image_2']): ?>
            <img src="../uploads/<?= $highlight['extra_image_2'] ?>" class="h-16 w-16 rounded">
          <?php endif; ?>
          <?php if($highlight['extra_image_3']): ?>
            <img src="../uploads/<?= $highlight['extra_image_3'] ?>" class="h-16 w-16 rounded">
          <?php endif; ?>
        </div>

        <input type="file" name="image1" class="w-full mb-2 border p-2 rounded">
        <input type="file" name="image2" class="w-full mb-2 border p-2 rounded">
        <input type="file" name="image3" class="w-full border p-2 rounded">
      </div>

      <button class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Update Club Highlight
      </button>
    </form>
  </div>
</main>

</body>
</html>
