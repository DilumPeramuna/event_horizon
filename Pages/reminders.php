<?php
// File: reminders.php
session_start();
include('../includes/db_connection.php'); // DB first

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================
// 1️⃣ Delete reminders with past events
// ==========================
$delPast = $pdo->prepare("
    DELETE r 
    FROM reminders r
    INNER JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ? AND e.event_date < NOW()
");
$delPast->execute([$user_id]);

// ==========================
// 2️⃣ Manual deletion before header
// ==========================
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reminder_id = intval($_GET['delete']);

    $check = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $check->execute([$reminder_id, $user_id]);

    if ($check->rowCount() > 0) {
        $del = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
        $del->execute([$reminder_id, $user_id]);
        header("Location: reminders.php?deleted=success");
        exit;
    } else {
        header("Location: reminders.php?deleted=fail");
        exit;
    }
}

$title = "My Reminders";
include('../includes/header.php');

// ==========================
// 3️⃣ Fetch reminders
// ==========================
$query = "
SELECT r.id AS reminder_id, r.created_at AS reminder_added, e.* 
FROM reminders r
INNER JOIN events e ON r.event_id = e.id
WHERE r.user_id = ?
ORDER BY r.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll();
?>

<!-- ==========================
     PAGE STYLES
========================== -->
<style>
:root {
  --primary: #007aff;
  --primary-light: #4da3ff;
  --primary-dark: #0056d8;
  --glass: rgba(255, 255, 255, 0.7);
  --glass-border: rgba(255, 255, 255, 0.35);
  --text-dark: #0f172a;
  --text-muted: #6b7280;
  --radius-lg: 1.25rem;
  --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Reminder Card */
.reminder-card {
  background: var(--glass);
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(14px) saturate(170%);
  -webkit-backdrop-filter: blur(14px) saturate(170%);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.reminder-card:hover {
  transform: translateY(-6px) scale(1.02);
  border-color: var(--primary-light);
  box-shadow: 0 25px 55px rgba(0,0,0,0.15);
}

.reminder-card::before {
  content: '';
  position: absolute;
  top:0; left:0; right:0; bottom:0;
  background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1));
  opacity: 0;
  transition: var(--transition);
}

.reminder-card:hover::before {
  opacity: 1;
}

/* Image */
.reminder-img {
  height: 190px;
  width: 100%;
  object-fit: cover;
  border-radius: var(--radius-lg);
  transition: var(--transition);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.reminder-card:hover .reminder-img {
  transform: scale(1.05);
}

/* Delete Button */
.delete-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  padding: 0.4rem 0.9rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 0.75rem;
  position: absolute;
  top: 0.85rem;
  right: 0.85rem;
  box-shadow: 0 6px 18px rgba(255,0,0,0.25);
  transition: var(--transition);
  z-index: 5;
}

.delete-btn:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(255,0,0,0.25);
}

/* Titles */
.reminder-title {
  font-size: 1.2rem;
  font-weight: 700;
  margin-top: 0.65rem;
  color: var(--text-dark);
  transition: var(--transition);
}

/* .reminder-card:hover .reminder-title {
  color: var(--primary-dark);
} */

.reminder-date {
  font-size: 0.9rem;
  color: var(--text-muted);
  font-weight: 500;
}

/* Empty state */
.no-reminders-box {
  background: var(--glass);
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(14px);
  padding: 2rem;
  border-radius: var(--radius-lg);
  text-align: center;
  font-size: 1.2rem;
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
</style>

<!-- ==========================
       MAIN CONTENT
========================== -->
<div class="max-w-6xl mx-auto mt-12 mb-20 px-6">

    <h1 class="text-4xl font-extrabold text-center mb-12 text-gray-900 tracking-tight">
        My Event Reminders
    </h1>

    <!-- STATUS MESSAGES -->
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'success'): ?>
        <p class="text-green-600 text-center mb-6 font-semibold">Reminder deleted successfully!</p>
    <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 'fail'): ?>
        <p class="text-red-600 text-center mb-6 font-semibold">Failed to delete reminder.</p>
    <?php endif; ?>

    <!-- EMPTY STATE -->
    <?php if (empty($reminders)): ?>
        <div class="no-reminders-box">
            You have no reminders yet.
        </div>

    <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

        <?php foreach ($reminders as $event): ?>
        <div class="reminder-card">

            <!-- DELETE -->
            <a href="reminders.php?delete=<?= $event['reminder_id'] ?>"
               onclick="return confirm('Delete this reminder?');"
               class="delete-btn">Delete</a>

            <!-- CARD CONTENT -->
            <a href="event_view.php?id=<?= $event['id'] ?>">
                <img src="../uploads/<?= $event['main_image'] ?>" class="reminder-img">

                <h2 class="reminder-title">
                    <?= htmlspecialchars($event['title']) ?>
                </h2>
            </a>

            <p class="reminder-date mt-1">
                <span class="font-semibold text-gray-900">Event Date:</span>
                <?= $event['event_date'] ?>
            </p>

        </div>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
