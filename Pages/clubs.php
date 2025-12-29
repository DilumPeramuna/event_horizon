<?php
// File: clubs.php
$title = "Clubs";
include('../includes/header.php');
include('../includes/db_connection.php');

/* ----------------------------------------------------
   SEARCH + PAGINATION SETTINGS
---------------------------------------------------- */
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$search_param = "%" . $search . "%";

/* ----------------------------------------------------
   COUNT TOTAL CLUBS
---------------------------------------------------- */
$countQuery = "
    SELECT COUNT(DISTINCT clubs.id) AS total
    FROM clubs
    LEFT JOIN events ON events.club_id = clubs.id
    WHERE clubs.club_name LIKE ?
       OR events.title LIKE ?
";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([$search_param, $search_param]);
$totalClubs = $countStmt->fetchColumn();

$totalPages = ceil($totalClubs / $limit);

/* ----------------------------------------------------
   FETCH CLUBS
---------------------------------------------------- */
$query = "
    SELECT clubs.*, events.title AS event_title
    FROM clubs
    LEFT JOIN events ON events.club_id = clubs.id
    WHERE clubs.club_name LIKE ?
       OR events.title LIKE ?
    GROUP BY clubs.id
    ORDER BY clubs.id DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute([$search_param, $search_param]);
$clubs = $stmt->fetchAll();
?>

<!-- ============================
        GLOBAL STYLES
============================ -->
<style>
:root {
  --primary: #007aff;
  --shadow-soft: rgba(0, 122, 255, 0.12);
  --shadow-hover: rgba(0, 122, 255, 0.22);
}

/* ========= HERO SECTION ========= */
.clubs-hero {
  background: linear-gradient(to bottom, #ffffff, #f8fafc);
  padding: 6rem 1rem;
  position: relative;
  overflow: hidden;
  text-align: center;
}

.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(110,162,255,0.25), rgba(185,204,255,0.15));
  animation: float 8s ease-in-out infinite;
  filter: blur(2px);
}

.shape-1 { width: 260px; height: 260px; top: 8%; left: 5%; animation-delay: 0s; }
.shape-2 { width: 180px; height: 180px; bottom: 20%; right: 8%; animation-delay: 2s; }
.shape-3 { width: 140px; height: 140px; bottom: 10%; left: 25%; animation-delay: 3s; }
.shape-4 { width: 200px; height: 200px; top: 18%; right: 20%; animation-delay: 1s; }

@keyframes float {
  0%, 100% { transform: translateY(0) translateX(0); }
  50% { transform: translateY(-25px) translateX(10px); }
}

.club-search {
  border-radius: 9999px;
  border: 1px solid #d0d7e2;
  background: #fff;
  transition: 0.25s;
}
.club-search:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

/* ========= CLUB CARDS ========= */
.club-grid-wrapper {
  padding: 3rem 0;
}

.club-card {
  background: #ffffff;
  border-radius: 0.8rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06), 0 0 0 1.5px var(--shadow-soft);
  overflow: hidden;
  transition: 0.35s ease;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  height: 500px;
  width: 90%;
}

.club-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 26px rgba(0,0,0,0.08), 0 0 0 2px var(--shadow-hover);
}

.club-image-top {
  width: 100%;
  height: 230px;
  object-fit: cover;
}

.club-content {
  padding: 1.4rem;
}

.club-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 0.6rem;
}

.club-tag {
  background: #f1f5f9;
  padding: 0.25rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #475569;
}

.club-footer {
  padding-bottom: 1.4rem;
  display: flex;
  justify-content: center;
}

.ios-btn-primary {
  background: linear-gradient(135deg, #007aff, #0056d8);
  padding: 0.6rem 1.6rem;
  border-radius: 9999px;
  color: white;
  font-weight: 600;
  transition: 0.25s;
}
.ios-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,122,255,0.35);
}
</style>

<!-- ============================
         HERO SECTION
============================ -->
<section class="clubs-hero">

  <!-- floating shapes -->
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
  <div class="floating-shape shape-3"></div>
  <div class="floating-shape shape-4"></div>

  <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-3">
    Discover Your Community
  </h1>

  <p class="text-slate-600 text-lg md:text-xl mb-8 max-w-2xl mx-auto">
    Join vibrant student organizations where passions ignite, skills develop, and lifelong connections form.
  </p>

  <!-- Search -->
  <form method="GET" class="flex justify-center mb-8 gap-3">
    <div class="relative w-full max-w-md">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
             placeholder="Find your perfect club..."
             class="club-search px-6 py-3 pl-12 w-full">
      <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    </div>
    <button class="ios-btn-primary px-8">Explore</button>
  </form>

  <!-- Stats -->
  <div class="flex justify-center gap-10 mt-6 text-center">
    <div>
      <div class="text-2xl font-bold text-blue-600">25+</div>
      <div class="text-sm text-slate-500">Active Clubs</div>
    </div>
    <div>
      <div class="text-2xl font-bold text-blue-600">1,200+</div>
      <div class="text-sm text-slate-500">Members</div>
    </div>
    <div>
      <div class="text-2xl font-bold text-blue-600">50+</div>
      <div class="text-sm text-slate-500">Events Monthly</div>
    </div>
  </div>

</section>

<!-- ============================
        CLUB LIST SECTION
============================ -->
<div class="club-grid-wrapper max-w-7xl mx-auto px-4">

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

    <?php foreach ($clubs as $club): ?>
    <?php
      $shortDesc = !empty($club['short_description'])
          ? $club['short_description']
          : $club['club_description'];
    ?>
    <div class="club-card">

      <img src="../uploads/<?= htmlspecialchars($club['club_main_image']) ?>"
           alt="<?= htmlspecialchars($club['club_name']) ?>"
           class="club-image-top">

      <div class="club-content">
         

        <h3 class="font-semibold text-lg mb-1">
          <?= htmlspecialchars($club['club_name']) ?>
        </h3>

        <p class="text-slate-600 text-sm mb-4">
          <?= htmlspecialchars($shortDesc) ?>
        </p>
      </div>

      <div class="club-footer">
        <a href="club_view.php?id=<?= $club['id'] ?>" class="ios-btn-primary">
          Explore Club
        </a>
      </div>

    </div>
    <?php endforeach; ?>

  </div>

  <!-- PAGINATION -->
  <div class="mt-14 flex justify-center items-center gap-3 select-none">

      <!-- Prev Button -->
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>"
          class="px-5 py-2 rounded-full text-sm font-medium
                  bg-white border border-slate-200 text-slate-600
                  hover:bg-slate-100 hover:shadow-md hover:-translate-y-0.5
                  transition-all duration-200 ease-out">
          Prev
        </a>
      <?php endif; ?>


      <!-- Page Numbers -->
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>

        <?php if ($i == $page): ?>
          <!-- ACTIVE PAGE -->
          <span class="px-5 py-2 rounded-full text-sm font-medium
                      bg-gradient-to-r from-blue-500 to-blue-600
                      text-white shadow-lg transform scale-105
                      transition-all duration-200">
            <?= $i ?>
          </span>

        <?php else: ?>
          <!-- NORMAL PAGE -->
          <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
            class="px-5 py-2 rounded-full text-sm font-medium
                    bg-white border border-slate-200 text-slate-600
                    hover:bg-slate-100 hover:shadow-md hover:-translate-y-0.5
                    transition-all duration-200 ease-out">
            <?= $i ?>
          </a>
        <?php endif; ?>

      <?php endfor; ?>


      <!-- Next Button -->
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>"
          class="px-5 py-2 rounded-full text-sm font-medium
                  bg-white border border-slate-200 text-slate-600
                  hover:bg-slate-100 hover:shadow-md hover:-translate-y-0.5
                  transition-all duration-200 ease-out">
          Next
        </a>
      <?php endif; ?>

  </div>


</div>

<?php include('../includes/footer.php'); ?>
