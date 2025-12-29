<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

include("../includes/db_connection.php");

// ===== Fetch Event =====
if (!isset($_GET['id'])) {
    header("Location: manage_events.php");
    exit;
}

$event_id = intval($_GET['id']);

// Fetch existing event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: manage_events.php");
    exit;
}

// Fetch clubs for dropdown
$clubs = $pdo->query("SELECT id, club_name FROM clubs")->fetchAll(PDO::FETCH_ASSOC);

// ===== Handle Update =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['date']; 
    $venue = trim($_POST['venue']); // ✅ Venue
    $price = trim($_POST['price']); // ✅ Ticket Price
    $ticket_url = trim($_POST['ticket_url']); // ✅ Place to Buy Tickets
    $club_id = $_POST['club_id'];

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
        SET title=?, description=?, event_date=?, venue=?, price=?, ticket_url=?, main_image=?, extra_image_1=?, extra_image_2=?, extra_image_3=?, club_id=? 
        WHERE id=?");
    $stmt->execute([$title, $description, $event_date, $venue, $price, $ticket_url, $main_image, $img1, $img2, $img3, $club_id, $event_id]);

    header("Location: manage_events.php?updated=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Event - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">

  <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

  <nav class="flex flex-col gap-4 flex-1">

    <a href="dashboard.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Dashboard
    </a>

    <a href="club_add.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Add Club
    </a>

    <a href="manage_clubs.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Manage Clubs
    </a>

    <a href="event_add.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Add Event
    </a>

    <a href="highlights_add.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Add Club Highlight
    </a>

    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg">
      Manage Club Highlights
    </a>

    <a href="manage_events.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">
      Manage Events
    </a>

    <a href="admin_logout.php" class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg mt-2">
      Logout
    </a>

  </nav>

</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">

  <h2 class="text-2xl font-semibold mb-6">Edit Event</h2>

  <div class="max-w-3xl bg-white p-8 shadow-lg rounded-xl mx-auto">

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      
      <div>
        <label class="block mb-1 font-medium">Title</label>
        <input type="text" name="title"
               value="<?= htmlspecialchars($event['title']) ?>"
               class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($event['description']) ?></textarea>
      </div>

      <div>
        <label class="block mb-1 font-medium">Event Date & Time</label>
        <input type="datetime-local" name="date"
               value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>"
               class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <!-- ✅ Venue Field -->
      <div>
        <label class="block mb-1 font-medium">Event Venue</label>
        <input type="text" name="venue"
               value="<?= htmlspecialchars($event['venue']) ?>"
               class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Enter venue (e.g., APIIT Auditorium)" required>
      </div>

      <!-- ✅ Ticket Price Field -->
      <div>
        <label class="block mb-1 font-medium">Ticket Price</label>
        <input type="number" name="price" step="0.01"
               value="<?= htmlspecialchars($event['price']) ?>"
               class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Enter ticket price (e.g., 1500)" required>
      </div>

      <!-- ✅ Ticket URL / Place to Buy Field -->
      <div>
        <label class="block mb-1 font-medium">Place to Buy Tickets (URL)</label>
        <input type="url" name="ticket_url"
               value="<?= htmlspecialchars($event['ticket_url']) ?>"
               class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Enter ticket purchase URL" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Main Image</label>
        <input type="file" name="main_image" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['main_image'] ?></p>
      </div>

      <div>
        <label class="block mb-1 font-medium">Extra Images</label>

        <input type="file" name="image1" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_1'] ?></p>

        <input type="file" name="image2" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_2'] ?></p>

        <input type="file" name="image3" class="w-full border p-2 rounded mb-1">
        <p class="text-sm text-gray-500">Current: <?= $event['extra_image_3'] ?></p>
      </div>

      <div>
        <label class="block mb-1 font-medium">Select Club</label>
        <select name="club_id" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <?php foreach($clubs as $club): ?>
            <option value="<?= $club['id'] ?>" <?= $club['id'] == $event['club_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($club['club_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">
        Update Event
      </button>

    </form>

  </div>
</main>

</body>
</html>
