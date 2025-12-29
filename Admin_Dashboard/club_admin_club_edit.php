<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Fetch club data
$stmt = $pdo->prepare("SELECT * FROM clubs WHERE id = ?");
$stmt->execute([$club_id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    header("Location: club_admin_dashboard.php");
    exit();
}

$uploadPath = "../uploads/";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_name = $_POST['club_name'];
    $club_description = $_POST['club_description'];
    $short_description = trim($_POST['short_description']);
    $contact_description_1 = trim($_POST['contact_description_1']);
    $contact_number_1 = trim($_POST['contact_number_1']);
    $contact_number_2 = trim($_POST['contact_number_2']);

    // Word count validation
    $wordCount = str_word_count($short_description);
    if ($wordCount > 15) {
        $error = "Short Description cannot exceed 15 words. Current: $wordCount words";
    } else {
        // Upload images
        function uploadImage($inputName, $uploadPath, $currentImage) {
            if (!empty($_FILES[$inputName]["name"])) {
                $fileName = time() . "_" . basename($_FILES[$inputName]["name"]);
                move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath . $fileName);
                return $fileName;
            }
            return $currentImage;
        }

        $main_image = uploadImage("club_main_image", $uploadPath, $club['club_main_image']);
        $img1 = uploadImage("club_extra_image_1", $uploadPath, $club['club_extra_image_1']);
        $img2 = uploadImage("club_extra_image_2", $uploadPath, $club['club_extra_image_2']);
        $img3 = uploadImage("club_extra_image_3", $uploadPath, $club['club_extra_image_3']);

        // Update club record
        $stmt = $pdo->prepare("UPDATE clubs SET 
            club_name = ?, club_description = ?, short_description = ?, club_main_image = ?, 
            club_extra_image_1 = ?, club_extra_image_2 = ?, club_extra_image_3 = ?, 
            contact_description_1 = ?, contact_number_1 = ?, contact_number_2 = ?
            WHERE id = ?");
        
        $stmt->execute([
            $club_name, $club_description, $short_description, $main_image, 
            $img1, $img2, $img3, 
            $contact_description_1, $contact_number_1, $contact_number_2,
            $club_id
        ]);

        header("Location: club_admin_dashboard.php?updated=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club - Club Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-gradient-to-b from-blue-900 to-blue-700 text-white flex-shrink-0">
  <h2 class="text-xl font-bold mb-2">EventHorizan</h2>
  <p class="text-blue-200 text-sm mb-8">Club Admin</p>

  <nav class="flex flex-col gap-3 flex-1">
    <a href="club_admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📊 Dashboard</a>
    <a href="club_admin_club_edit.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">🏢 Edit Club Info</a>
    <a href="club_admin_event_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">➕ Add Event</a>
    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📅 Manage Events</a>
    <a href="club_admin_highlight_add.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-800 transition">📜 Add Club Highlight</a>
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
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Club Information</h1>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">
    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateWordLimit();" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Club Name</label>
        <input type="text" name="club_name" class="w-full border p-2 rounded" value="<?= htmlspecialchars($club['club_name']) ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Club Description</label>
        <textarea name="club_description" rows="4" class="w-full border p-2 rounded" required><?= htmlspecialchars($club['club_description']) ?></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1">Short Description (max 15 words)</label>
        <textarea id="short_description" name="short_description" rows="2" class="w-full border p-2 rounded" required><?= htmlspecialchars($club['short_description'] ?? '') ?></textarea>
        <p id="word_count" class="text-sm text-gray-600 mt-1">0 / 15 words</p>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Description</label>
        <input type="text" name="contact_description_1" class="w-full border p-2 rounded" value="<?= htmlspecialchars($club['contact_description_1'] ?? '') ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 1</label>
        <input type="text" name="contact_number_1" class="w-full border p-2 rounded" value="<?= htmlspecialchars($club['contact_number_1'] ?? '') ?>" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 2 (Optional)</label>
        <input type="text" name="contact_number_2" class="w-full border p-2 rounded" value="<?= htmlspecialchars($club['contact_number_2'] ?? '') ?>">
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <?php if($club['club_main_image']): ?>
          <img src="../uploads/<?= $club['club_main_image'] ?>" class="w-32 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="club_main_image" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 1</label>
        <?php if($club['club_extra_image_1']): ?>
          <img src="../uploads/<?= $club['club_extra_image_1'] ?>" class="w-32 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="club_extra_image_1" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 2</label>
        <?php if($club['club_extra_image_2']): ?>
          <img src="../uploads/<?= $club['club_extra_image_2'] ?>" class="w-32 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="club_extra_image_2" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 3</label>
        <?php if($club['club_extra_image_3']): ?>
          <img src="../uploads/<?= $club['club_extra_image_3'] ?>" class="w-32 mb-2 rounded">
        <?php endif; ?>
        <input type="file" name="club_extra_image_3" class="w-full border p-2 rounded">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Update Club
      </button>
    </form>
  </div>
</main>

<script>
const textarea = document.getElementById('short_description');
const wordCountDisplay = document.getElementById('word_count');

// Initialize word count
const initialWords = textarea.value.trim().split(/\s+/).filter(w => w.length > 0);
wordCountDisplay.textContent = `${initialWords.length} / 15 words`;

textarea.addEventListener('input', () => {
  const words = textarea.value.trim().split(/\s+/).filter(w => w.length > 0);
  wordCountDisplay.textContent = `${words.length} / 15 words`;
  wordCountDisplay.classList.toggle('text-red-600', words.length > 15);
});

function validateWordLimit() {
  const words = textarea.value.trim().split(/\s+/).filter(w => w.length > 0 );
  if (words.length > 15) {
    alert("Short Description cannot exceed 15 words. You currently have " + words.length + " words.");
    return false;
  }
  return true;
}
</script>

</body>
</html>
