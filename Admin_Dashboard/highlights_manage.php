<?php
session_start();

// ===== Admin Authentication =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once('../includes/db_connection.php');

// ===== Handle Delete Club Highlight =====
if (isset($_GET['delete_highlight'])) {
    $event_id = intval($_GET['delete_highlight']);
    $stmt = $pdo->prepare("DELETE FROM club_highlights WHERE id = ?");
    $stmt->execute([$event_id]);
    header("Location: highlights_manage.php?deleted=highlight");
    exit();
}

// ===== Fetch All Club Highlights With Clubs =====
$highlights = $pdo->query("
    SELECT p.*, c.club_name
    FROM club_highlights p
    LEFT JOIN clubs c ON p.club_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Club Highlights - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans bg-gray-100 flex flex-col md:flex-row min-h-screen">

<!-- SIDEBAR -->
<aside class="hidden md:flex flex-col w-64 p-6 bg-black text-white flex-shrink-0">
    <h2 class="text-xl font-bold mb-8">EventHorizan Admin</h2>

    <nav class="flex flex-col gap-4 flex-1">
        <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Dashboard</a>
        <a href="club_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Club</a>
        <a href="manage_clubs.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Clubs</a>
        <a href="event_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Event</a>
        <a href="highlights_add.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Add Club Highlight</a>
        <a href="highlights_manage.php" class="flex items-center gap-3 p-2 bg-gray-800 rounded-lg">Manage Club Highlights</a>
        <a href="manage_events.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800">Manage Events</a>
        <a href="admin_logout.php" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 mt-2">Logout</a>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="flex-1 p-6 md:p-10">
    <h1 class="text-2xl font-semibold mb-6">Manage Club Highlights</h1>

    <div class="bg-white shadow-lg rounded-xl overflow-x-auto">
        <table class="w-full min-w-[1300px] border-collapse text-left">
            <thead class="bg-gray-50">
                <tr>
                   
                    <th class="p-3 border-b">Title</th>
                    <th class="p-3 border-b">Description</th>
                    <th class="p-3 border-b">Main Image</th>
                   
                    <th class="p-3 border-b">Club</th>
                 
                    <th class="p-3 border-b">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($highlights as $event): ?>
                <tr class="border-b hover:bg-gray-50">
                   
                    <td class="p-3"><?= htmlspecialchars($event['event_title']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($event['event_description']) ?></td>
                    <td class="p-3">
                        <?php if($event['main_image']): ?>
                            <img src="../uploads/<?= $event['main_image'] ?>" class="h-16 rounded object-cover">
                        <?php endif; ?>
                    </td>
                     
                    <td class="p-3"><?= htmlspecialchars($event['club_name'] ?? 'N/A') ?></td>
                   
                    <td class="p-3 space-x-3">
                        <a href="highlights_edit.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                        <a href="?delete_highlight=<?= $event['id'] ?>" 
                           onclick="return confirm('Delete this club highlight?')" 
                           class="text-red-600 hover:underline">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($highlights)): ?>
                <tr>
                    <td colspan="8" class="p-3 text-center text-gray-500">No club highlights found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
