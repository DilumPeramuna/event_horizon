<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php'); // DB connection

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $club_id = $_POST['club_id'];
    $uploadPath = "../uploads/";

    // ===== Validate required fields =====
    if (empty($event_title)) {
        $error = "Highlight title is required.";
    } elseif (empty($_FILES['main_image']['name'])) {
        $error = "Main Image is required.";
    } elseif (empty($club_id)) {
        $error = "Please select a club.";
    } else {
        // ===== Check if highlight title already exists =====
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM club_highlights WHERE event_title = ?");
        $checkStmt->execute([$event_title]);
        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            $error = "A club highlight with this title already exists. Please use a different name.";
        } else {
            // ===== Upload Function =====
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

            // ===== Insert into club_highlights =====
            $sql = "INSERT INTO club_highlights
                (club_id, event_title, event_description, main_image, extra_image_1, extra_image_2, extra_image_3, created_at)
                VALUES (:club_id, :event_title, :description, :main_image, :img1, :img2, :img3, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':club_id' => $club_id,
                ':event_title' => $event_title,
                ':description' => $description,
                ':main_image' => $main_image,
                ':img1' => $img1,
                ':img2' => $img2,
                ':img3' => $img3
            ]);

            header("Location: highlights_manage.php?added=success");
            exit;
        }
    }
}

// ===== Fetch clubs =====
$clubStmt = $pdo->prepare("SELECT id, club_name FROM clubs");
$clubStmt->execute();
$clubs = $clubStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Club Highlight - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">
    <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Dashboard</a>
    <a href="club_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Club</a>
    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Clubs</a>
    <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Event</a>
    <a href="highlights_add.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">Add Club Highlight</a>
    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Club Highlights</a>
    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Events</a>
    <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-semibold mb-6">Add Club Highlight</h1>

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
        <input type="file" name="image1" class="w-full border p-2 rounded mb-1">
        <input type="file" name="image2" class="w-full border p-2 rounded mb-1">
        <input type="file" name="image3" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block mb-1 font-medium">Select Club</label>
        <select name="club_id" class="w-full border p-2 rounded" required>
          <option value="">-- Select Club --</option>
          <?php foreach ($clubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['club_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium">Highlight Description</label>
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
