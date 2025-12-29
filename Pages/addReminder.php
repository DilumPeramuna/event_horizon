<?php
// File: addReminder.php
session_start();
$title = "Event Reminder";
include('../includes/header.php');
include('../includes/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['event_id'])) {
    echo "Invalid Event!";
    exit;
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

// Check if reminder already exists
$check = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND event_id = ?");
$check->execute([$user_id, $event_id]);

if ($check->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO reminders (user_id, event_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $event_id]);
}

// Fetch event details
$eventStmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$eventStmt->execute([$event_id]);
$event = $eventStmt->fetch();

if (!$event) {
    echo "Event not found!";
    exit;
}

// Optional: fetch linked club
$club = null;
if (!empty($event['club_id'])) {
    $clubStmt = $pdo->prepare("SELECT * FROM clubs WHERE id = ?");
    $clubStmt->execute([$event['club_id']]);
    $club = $clubStmt->fetch();
}
?>

<!-- =============================
       MAIN SUCCESS CARD
============================= -->
<div class="max-w-4xl mx-auto bg-white/90 backdrop-blur-xl p-10 mt-6 rounded-3xl shadow-xl border border-gray-200">

    <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-xl mb-6 shadow-sm">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            ✅ Reminder Added Successfully!
        </h2>
        <p class="text-sm mt-1">You will be notified when the event date is near.</p>
    </div>

    <!-- EVENT MAIN IMAGE -->
    <img src="../uploads/<?= $event['main_image'] ?>"
         class="w-full h-72 object-cover rounded-2xl shadow-lg mb-6" />

    <!-- EVENT TITLE -->
    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 mb-3">
        <?= $event['title'] ?>
    </h1>

    <!-- EVENT DATE -->
    <p class="text-gray-600 text-sm mb-2">
        📅 <span class="font-semibold text-gray-900">Event Date:</span> <?= $event['event_date'] ?>
    </p>

    <!-- DESCRIPTION -->
    <p class="text-gray-800 text-lg leading-relaxed mb-8 whitespace-pre-line">
        <?= nl2br($event['description']) ?>
    </p>

    <!-- EXTRA IMAGES -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <?php if (!empty($event['extra_image_1'])): ?>
            <img src="../uploads/<?= $event['extra_image_1'] ?>" 
                 class="rounded-xl h-40 w-full object-cover shadow-md hover:scale-[1.02] transition">
        <?php endif; ?>

        <?php if (!empty($event['extra_image_2'])): ?>
            <img src="../uploads/<?= $event['extra_image_2'] ?>" 
                 class="rounded-xl h-40 w-full object-cover shadow-md hover:scale-[1.02] transition">
        <?php endif; ?>

        <?php if (!empty($event['extra_image_3'])): ?>
            <img src="../uploads/<?= $event['extra_image_3'] ?>" 
                 class="rounded-xl h-40 w-full object-cover shadow-md hover:scale-[1.02] transition">
        <?php endif; ?>
    </div>

    <!-- LINKED CLUB -->
    <?php if ($club): ?>
        <p class="text-blue-600 font-semibold mb-6 text-lg">
            🔗 Linked Club: 
            <a href="club_view.php?id=<?= $club['id'] ?>" 
               class="underline hover:text-blue-800 transition">
                <?= $club['club_name'] ?>
            </a>
        </p>
    <?php endif; ?>

    <!-- BUTTONS -->
    <div class="flex gap-4 mt-4">

        <a href="reminders.php" 
           class="px-5 py-2.5 bg-green-600 text-white rounded-xl shadow-md 
                  hover:bg-green-700 transition font-semibold">
            View My Reminders
        </a>

        <a href="index.php" 
           class="px-5 py-2.5 bg-gray-600 text-white rounded-xl shadow-md 
                  hover:bg-gray-700 transition font-semibold">
            Back to Calendar
        </a>

    </div>

</div>

<?php include('../includes/footer.php'); ?>
