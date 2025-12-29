<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php'); // DB connection

$error = ""; // to hold validation errors

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $club_name = trim($_POST['club_name']);
    $club_description = trim($_POST['club_description']);
    $short_description = trim($_POST['short_description']);
    $contact_description_1 = trim($_POST['contact_description_1']);
    $contact_number_1 = trim($_POST['contact_number_1']);
    $contact_number_2 = trim($_POST['contact_number_2']);
    $uploadPath = "../uploads/";

    // --- Word count validation (max 15 words) ---
    $wordCount = str_word_count($short_description);
    if ($wordCount > 15) {
        $error = "Short Description cannot exceed 15 words. You entered $wordCount words.";
    } else {
        // --- Check if club name already exists ---
        $checkQuery = "SELECT COUNT(*) FROM clubs WHERE club_name = :club_name";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([':club_name' => $club_name]);
        $clubExists = $checkStmt->fetchColumn();

        if ($clubExists > 0) {
            $error = "A club with the name '$club_name' already exists. Please use a different name.";
        } else {

            // --- Function to upload images ---
            function uploadImage($inputName, $uploadPath) {
                if (!empty($_FILES[$inputName]["name"])) {
                    $fileName = time() . "_" . basename($_FILES[$inputName]["name"]);
                    move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath . $fileName);
                    return $fileName;
                }
                return null;
            }

            // Upload all images
            $main_image = uploadImage("main_image", $uploadPath);
            $img1 = uploadImage("extra_image_1", $uploadPath);
            $img2 = uploadImage("extra_image_2", $uploadPath);
            $img3 = uploadImage("extra_image_3", $uploadPath);

            // Insert into clubs table
            $sql = "INSERT INTO clubs 
                (club_name, club_description, short_description, club_main_image, club_extra_image_1, club_extra_image_2, club_extra_image_3, contact_description_1, contact_number_1, contact_number_2, created_at)
                VALUES (:club_name, :club_description, :short_description, :main_image, :img1, :img2, :img3, :contact_description_1, :contact_number_1, :contact_number_2, NOW())";

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
                ':contact_number_2' => $contact_number_2
            ]);

            header("Location: manage_clubs.php?added=success");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Club - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">
  
  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Dashboard
    </a>

    <a href="club_add.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">
      Add Club
    </a>

    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Clubs
    </a>

    <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Event
    </a>

    <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Add Club Highlight
    </a>

    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Club Highlights
    </a>

    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">
      Manage Events
    </a>

    <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">
      Logout
    </a>

  </nav>

</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-semibold mb-6">Add New Club</h1>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">

    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return validateWordCount();">

      <div>
        <label class="block font-medium mb-1">Club Name</label>
        <input type="text" name="club_name" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Club Description</label>
        <textarea name="club_description" rows="4" class="w-full border p-2 rounded" required></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1">Club Short Description (max 15 words)</label>
        <textarea id="short_description" name="short_description" rows="2" class="w-full border p-2 rounded" required></textarea>
        <p id="word_count" class="text-sm text-gray-600 mt-1">0 / 15 words</p>
      </div>

      <!-- Contact Fields -->
      <div>
        <label class="block font-medium mb-1">Contact Description</label>
        <input type="text" name="contact_description_1" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 1</label>
        <input type="text" name="contact_number_1" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Contact Number 2</label>
        <input type="text" name="contact_number_2" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <input type="file" name="main_image" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 1</label>
        <input type="file" name="extra_image_1" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 2</label>
        <input type="file" name="extra_image_2" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Image 3</label>
        <input type="file" name="extra_image_3" class="w-full border p-2 rounded">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">
        Add Club
      </button>

    </form>
  </div>
</main>

<script>
// Live word count check
const textarea = document.getElementById('short_description');
const wordCountDisplay = document.getElementById('word_count');

textarea.addEventListener('input', () => {
  const words = textarea.value.trim().split(/\s+/).filter(w => w.length > 0);
  wordCountDisplay.textContent = `${words.length} / 15 words`;
  wordCountDisplay.classList.toggle('text-red-600', words.length > 15);
});

// Prevent form submission if >15 words
function validateWordCount() {
  const words = textarea.value.trim().split(/\s+/).filter(w => w.length > 0);
  if (words.length > 15) {
    alert('Short Description cannot exceed 15 words.');
    return false;
  }
  return true;
}
</script>

</body>
</html>
