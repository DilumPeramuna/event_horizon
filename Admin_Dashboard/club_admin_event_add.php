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
    $event_date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $price = trim($_POST['price']);
    $ticket_url = trim($_POST['ticket_url']);
    $description = trim($_POST['description']);
    $uploadPath = "../uploads/";

    // Convert datetime-local to MySQL DATETIME format
    $event_date_mysql = str_replace('T', ' ', $event_date);

    // Check duplicate date/time
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_date = ?");
    $checkStmt->execute([$event_date_mysql]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        $error = "An event already exists at this date & time.";
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

        // Insert event with club_id from session
        $sql = "INSERT INTO events 
            (title, description, event_date, venue, price, ticket_url, main_image, extra_image_1, extra_image_2, extra_image_3, club_id, created_at)
            VALUES (:title, :description, :event_date, :venue, :price, :ticket_url, :main_image, :img1, :img2, :img3, :club_id, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $event_title,
            ':description' => $description,
            ':event_date' => $event_date_mysql,
            ':venue' => $venue,
            ':price' => $price,
            ':ticket_url' => $ticket_url,
            ':main_image' => $main_image,
            ':img1' => $img1,
            ':img2' => $img2,
            ':img3' => $img3,
            ':club_id' => $club_id
        ]);

        header("Location: club_admin_event_manage.php?added=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Event - Club Admin</title>
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
    <a href="club_admin_event_add.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">➕ Add Event</a>
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
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Add New Event</h1>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">
    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Event Title</label>
        <input type="text" name="title" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Event Date & Time</label>
        <input type="datetime-local" name="date" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Venue</label>
        <input type="text" name="venue" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Ticket Price</label>
        <input type="number" name="price" step="0.01" class="w-full border p-2 rounded" placeholder="Enter ticket price" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Ticket Purchase URL</label>
        <input type="url" name="ticket_url" class="w-full border p-2 rounded" placeholder="https://..." required>
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <input type="file" name="main_image" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Images</label>
        <input type="file" name="image1" class="w-full border p-2 rounded mb-2">
        <input type="file" name="image2" class="w-full border p-2 rounded mb-2">
        <input type="file" name="image3" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Event Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded"></textarea>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Add Event
      </button>
    </form>
  </div>
</main>

</body>
</html>
