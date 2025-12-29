<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php'); // DB connection

$error = ""; // To store errors

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_title = trim($_POST['title']);
    $event_date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $price = trim($_POST['price']); // ✅ Ticket Price
    $ticket_url = trim($_POST['ticket_url']); // ✅ Place to Buy Tickets
    $description = trim($_POST['description']);
    $club_id = $_POST['club_id'];

    $uploadPath = "../uploads/";

    // Convert datetime-local to MySQL DATETIME format
    $event_date_mysql = str_replace('T', ' ', $event_date);

    // ===== Check duplicate date/time =====
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_date = ?");
    $checkStmt->execute([$event_date_mysql]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        $error = "An event already exists at this date & time.";
    } else {

        // ===== Image Upload Function =====
        function uploadImage($inputName, $uploadPath) {
            if (!empty($_FILES[$inputName]["name"])) {
                $fileName = time() . "_" . basename($_FILES[$inputName]["name"]);
                move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath . $fileName);
                return $fileName;
            }
            return null;
        }

        // Upload images
        $main_image = uploadImage("main_image", $uploadPath);
        $img1 = uploadImage("image1", $uploadPath);
        $img2 = uploadImage("image2", $uploadPath);
        $img3 = uploadImage("image3", $uploadPath);

        // ===== Insert event into database =====
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

        header("Location: manage_events.php?added=success");
        exit();
    }
}

// Fetch clubs
$clubStmt = $pdo->prepare("SELECT id, club_name FROM clubs");
$clubStmt->execute();
$clubs = $clubStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Event - Admin Dashboard</title>
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
    <a href="event_add.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">Add Event</a>
    <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Club Highlight</a>
    <a href="highlights_manage.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Club Highlights</a>
    <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Events</a>
    <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:p-10">
  <h1 class="text-2xl font-semibold mb-6">Add New Event</h1>

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
        <label class="block font-medium mb-1">Event Venue</label>
        <input type="text" name="venue" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Ticket Price</label>
        <input type="number" name="price" step="0.01" class="w-full border p-2 rounded" placeholder="Enter ticket price" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Place to Buy Tickets (URL)</label>
        <input type="url" name="ticket_url" class="w-full border p-2 rounded" placeholder="Enter ticket purchase URL" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Main Image</label>
        <input type="file" name="main_image" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Extra Images</label>
        <input type="file" name="image1" class="w-full border p-2 rounded mb-1">
        <input type="file" name="image2" class="w-full border p-2 rounded mb-1">
        <input type="file" name="image3" class="w-full border p-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Select Club</label>
        <select name="club_id" class="w-full border p-2 rounded" required>
          <option value="">-- Select Club --</option>
          <?php foreach ($clubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['club_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block font-medium mb-1">Event Description</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded"></textarea>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">
        Add Event
      </button>

    </form>
  </div>
</main>

</body>
</html>
