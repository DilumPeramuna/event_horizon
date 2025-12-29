<?php
session_start();
require_once('../includes/auth_helper.php');
require_once('../includes/db_connection.php');

requireClubAdmin();

$club_id = getClubId();

// Fetch event
if (!isset($_GET['id'])) {
    header("Location: club_admin_event_manage.php");
    exit;
}

$event_id = intval($_GET['id']);

// Fetch event and verify ownership
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event || $event['club_id'] != $club_id) {
    // Event doesn't exist or doesn't belong to this club
    header("Location: club_admin_event_manage.php");
    exit;
}

// Handle Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['date']; 
    $venue = trim($_POST['venue']);
    $price = trim($_POST['price']);
    $ticket_url = trim($_POST['ticket_url']);
    $uploadPath = "../uploads/";

    function uploadImage($inputName, $currentFile, $uploadPath) {
        if (!empty($_FILES[$inputName]['name'])) {
            $fileName = time() . "_" . basename($_FILES[$inputName]['name']);
            move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadPath . $fileName);
            return $fileName;
        }
        return $currentFile;
    }

    $main_image = uploadImage("main_image", $event['main_image'], $uploadPath);
    $img1 = uploadImage("image1", $event['extra_image_1'], $uploadPath);
    $img2 = uploadImage("image2", $event['extra_image_2'], $uploadPath);
    $img3 = uploadImage("image3", $event['extra_image_3'], $uploadPath);

    // Convert datetime-local → MySQL format
    $event_date = str_replace('T', ' ', $event_date);

    $stmt = $pdo->prepare("UPDATE events 
        SET title=?, description=?, event_date=?, venue=?, price=?, ticket_url=?, main_image=?, extra_image_1=?, extra_image_2=?, extra_image_3=? 
        WHERE id=? AND club_id=?");
    $stmt->execute([$title, $description, $event_date, $venue, $price, $ticket_url, $main_image, $img1, $img2, $img3, $event_id, $club_id]);

    header("Location: club_admin_event_manage.php?updated=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Event - Club Admin</title>
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
    <a href="club_admin_event_manage.php" class="flex items-center gap-3 p-3 bg-blue-800 rounded-lg font-medium">📅 Manage Events</a>
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
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Event</h2>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded"><?= htmlspecialchars($event['description']) ?></textarea>
      </div>

      <div>
        <label class="block mb-1 font-medium">Event Date & Time</label>
        <input type="datetime-local" name="date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Venue</label>
        <input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Ticket Price</label>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($event['price']) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Ticket Purchase URL</label>
        <input type="url" name="ticket_url" value="<?= htmlspecialchars($event['ticket_url']) ?>" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Main Image</label>
        <input type="file" name="main_image" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['main_image'] ?></p>
      </div>

      <div>
        <label class="block mb-1 font-medium">Extra Images</label>
        <input type="file" name="image1" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_1'] ?? 'None' ?></p>
        
        <input type="file" name="image2" class="w-full border p-2 rounded mb-1 mt-2">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_2'] ?? 'None' ?></p>
        
        <input type="file" name="image3" class="w-full border p-2 rounded mb-1 mt-2">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_3'] ?? 'None' ?></p>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        Update Event
      </button>
    </form>
  </div>
</main>

</body>
</html>
