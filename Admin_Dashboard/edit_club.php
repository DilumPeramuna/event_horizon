<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php');

if (!isset($_GET['id'])) {
    header("Location: manage_clubs.php");
    exit();
}

$club_id = intval($_GET['id']);

// Fetch club data
$stmt = $pdo->prepare("SELECT * FROM clubs WHERE id = :id");
$stmt->execute([':id' => $club_id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    header("Location: manage_clubs.php");
    exit();
}

$uploadPath = "../uploads/";

// Function to upload image
function uploadImage($inputName, $uploadPath, $currentImage) {
    if (!empty($_FILES[$inputName]["name"])) {
        $fileName = time() . "_" . basename($_FILES[$inputName]["name"]);
        move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath . $fileName);
        return $fileName;
    }
    return $currentImage; // Keep old image if none uploaded
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_name = $_POST['club_name'];
    $club_description = $_POST['club_description'];
    $short_description = trim($_POST['short_description']);
    $contact_description_1 = trim($_POST['contact_description_1']);
    $contact_number_1 = trim($_POST['contact_number_1']);
    $contact_number_2 = trim($_POST['contact_number_2']);

    // --- Validate 15-word limit ---
    $wordCount = str_word_count($short_description);
    if ($wordCount > 15) {
        echo "<script>alert('Short Description cannot exceed 15 words. Current: $wordCount words');</script>";
    } else {
        // Upload images
        $main_image = uploadImage("club_main_image", $uploadPath, $club['club_main_image']);
        $img1 = uploadImage("club_extra_image_1", $uploadPath, $club['club_extra_image_1']);
        $img2 = uploadImage("club_extra_image_2", $uploadPath, $club['club_extra_image_2']);
        $img3 = uploadImage("club_extra_image_3", $uploadPath, $club['club_extra_image_3']);

        // Update club record
        $sql = "UPDATE clubs SET 
                    club_name = :club_name,
                    club_description = :club_description,
                    short_description = :short_description,
                    club_main_image = :main_image,
                    club_extra_image_1 = :img1,
                    club_extra_image_2 = :img2,
                    club_extra_image_3 = :img3,
                    contact_description_1 = :contact_description_1,
                    contact_number_1 = :contact_number_1,
                    contact_number_2 = :contact_number_2
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':club_name' => $club_name,
            ':club_description' => $club_description,
            ':short_description' => $short_description,
            ':main_image' => $main_image,
            ':img1' => $img1,
            ':img2' => $img2,
            ':img3' => $img3,
            ':contact_description_1' => $contact_description_1,
            ':contact_number_1' => $contact_number_1,
            ':contact_number_2' => $contact_number_2,
            ':id' => $club_id
        ]);

        header("Location: manage_clubs.php?updated=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
function validateWordLimit() {
    const textarea = document.querySelector('textarea[name="short_description"]');
    const words = textarea.value.trim().split(/\s+/).filter(Boolean);
    if (words.length > 15) {
        alert("Short Description cannot exceed 15 words. You currently have " + words.length + " words.");
        return false;
    }
    return true;
}
</script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Dashboard
    </a>

    <a href="club_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club
    </a>

    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">
      Manage Clubs
    </a>

    <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Event
    </a>

    <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club Highlight
    </a>

    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Events
    </a>

    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Club Highlights
    </a>

    <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">
      Logout
    </a>

  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-semibold mb-6">Edit Club</h1>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">

    <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateWordLimit();" class="space-y-4">

      <div>
        <label class="block font-medium mb-1">Club Name</label>
        <input type="text" name="club_name" class="w-full border p-2 rounded"
               value="<?= htmlspecialchars($club['club_name']) ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Club Description</label>
        <textarea name="club_description" rows="4" class="w-full border p-2 rounded" required><?= htmlspecialchars($club['club_description']) ?></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1">Club Short Description (max 15 words)</label>
        <textarea name="short_description" rows="2" class="w-full border p-2 rounded" required><?= htmlspecialchars($club['short_description'] ?? '') ?></textarea>
      </div>

      <!-- Contact Fields -->
      <div>
        <label class="block font-medium mb-1">Contact Description</label>
        <input type="text" name="contact_description_1" class="w-full border p-2 rounded"
               value="<?= htmlspecialchars($club['contact_description_1'] ?? '') ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 1</label>
        <input type="text" name="contact_number_1" class="w-full border p-2 rounded"
               value="<?= htmlspecialchars($club['contact_number_1'] ?? '') ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 2</label>
        <input type="text" name="contact_number_2" class="w-full border p-2 rounded"
               value="<?= htmlspecialchars($club['contact_number_2'] ?? '') ?>">
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <?php if($club['club_main_image']): ?>
          <img src="../uploads/<?= $club['club_main_image'] ?>" class="w-32 mb-2">
        <?php endif; ?>
        <input type="file" name="club_main_image" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 1</label>
        <?php if($club['club_extra_image_1']): ?>
          <img src="../uploads/<?= $club['club_extra_image_1'] ?>" class="w-32 mb-2">
        <?php endif; ?>
        <input type="file" name="club_extra_image_1" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 2</label>
        <?php if($club['club_extra_image_2']): ?>
          <img src="../uploads/<?= $club['club_extra_image_2'] ?>" class="w-32 mb-2">
        <?php endif; ?>
        <input type="file" name="club_extra_image_2" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 3</label>
        <?php if($club['club_extra_image_3']): ?>
          <img src="../uploads/<?= $club['club_extra_image_3'] ?>" class="w-32 mb-2">
        <?php endif; ?>
        <input type="file" name="club_extra_image_3" class="w-full border p-2 rounded">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">
        Update Club
      </button>

    </form>
  </div>
</main>

</body>
</html>
