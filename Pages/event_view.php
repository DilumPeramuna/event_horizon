<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db_connection.php');

if (!isset($_GET['id'])) {
    echo "Invalid Event ID";
    exit;
}

$event_id = intval($_GET['id']);

// ===== Auto-delete events older than 30 days =====
$deleteOld = $pdo->prepare("DELETE FROM events WHERE event_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$deleteOld->execute();

// ===== Fetch event details =====
$query = "SELECT * FROM events WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found!";
    exit;
}

/* Fetch linked club */
$club = null;
if (!empty($event['club_id'])) {
    $clubQuery = "SELECT * FROM clubs WHERE id = ?";
    $clubStmt = $pdo->prepare($clubQuery);
    $clubStmt->execute([$event['club_id']]);
    $club = $clubStmt->fetch();
}

// Check if event is in the past
$eventPast = (strtotime($event['event_date']) < time());

// ===== Review Handling =====
$reviewError = "";
$reviewSuccess = "";
$myReview = null; // Stores users existing review
$reviews = [];

// 1. Handle ACTIONS (Submit/Update/Delete) must be before header usage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // --- DELETE ---
    if (isset($_POST['delete_review'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM event_reviews WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$_SESSION['user_id'], $event_id]);
            header("Location: event_view.php?id=$event_id&review=deleted");
            exit();
        } catch (PDOException $e) {
             $reviewError = "Error deleting review.";
        }
    }

    // --- SUBMIT / UPDATE ---
    if (isset($_POST['submit_review'])) {
        $reviewText = trim($_POST['review_text']);
        
        if (empty($reviewText)) {
            $reviewError = "Review cannot be empty.";
        } else {
            try {
                // Upsert: Create or Update
                $stmt = $pdo->prepare("
                    INSERT INTO event_reviews (user_id, event_id, review_text) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE review_text = VALUES(review_text), created_at = NOW()
                ");
                $stmt->execute([$_SESSION['user_id'], $event_id, $reviewText]);
                
                header("Location: event_view.php?id=$event_id&review=updated");
                exit();
            } catch (PDOException $e) {
                $reviewError = "Error saving review.";
            }
        }
    }
}

// 2. Fetch User's Existing Review (if any)
if (isset($_SESSION['user_id'])) {
    $checkRev = $pdo->prepare("SELECT * FROM event_reviews WHERE user_id = ? AND event_id = ?");
    $checkRev->execute([$_SESSION['user_id'], $event_id]);
    $myReview = $checkRev->fetch(PDO::FETCH_ASSOC);
}

// 3. Fetch All Reviews (including user's own)
$revQuery = "
    SELECT r.*, SUBSTRING_INDEX(u.email, '@', 1) as username 
    FROM event_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.event_id = ? 
    ORDER BY r.created_at DESC
";
$revStmt = $pdo->prepare($revQuery);
$revStmt->execute([$event_id]);
$reviews = $revStmt->fetchAll();

// Include Header Last (contains HTML output)
include('../includes/header.php');
?>


<!-- ========================
       EVENT VIEW STYLES
======================== -->
<style>
:root {
  --primary: #007aff;
  --primary-light: #4da3ff;
  --glass: rgba(255, 255, 255, 0.8);
  --glass-border: rgba(255, 255, 255, 0.4);
  --shadow: rgba(0, 0, 0, 0.08);
  --text-dark: #0f172a;
  --text-muted: #6b7280;
  --radius-lg: 1.5rem;
}

/* === Animations === */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out forwards;
}

.animate-fade-in {
  animation: fadeIn 1s ease-out forwards;
}

.animation-delay-200 {
  animation-delay: 0.2s;
}

.animation-delay-400 {
  animation-delay: 0.4s;
}

/* === Floating shapes === */
.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(147,51,234,0.08));
  animation: float 8s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}

.shape-1 { 
  width: 200px; 
  height: 200px; 
  top: 10%; 
  left: 8%; 
}

.shape-2 { 
  width: 140px; 
  height: 140px; 
  top: 60%; 
  right: 10%; 
  animation-delay: 2s; 
}

.shape-3 { 
  width: 100px; 
  height: 100px; 
  bottom: 10%; 
  left: 15%; 
  animation-delay: 4s; 
}

.shape-4 {
  width: 120px;
  height: 120px;
  top: 20%;
  right: 20%;
  animation-delay: 1s;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

/* === Main container === */
.event-view-container {
  position: relative;
  overflow: hidden;
}

.event-main-card {
  background: var(--glass);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid var(--glass-border);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  position: relative;
  z-index: 1;
}

/* === Hero Section === */
.event-hero-section {
  position: relative;
  overflow: hidden;
  border-radius: 1.5rem;
  margin-bottom: 2rem;
}

.hero-content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: white;
  padding: 2rem;
  z-index: 2;
}

.hero-title {
  font-size: 3rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.hero-image {
  width: 100%;
  height: 500px;
  object-fit: cover;
  display: block;
}

/* === Content Grid === */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

@media (max-width: 768px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

/* === Info Cards === */
.info-card {
  background: var(--glass);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid var(--glass-border);
  border-radius: 1.5rem;
  padding: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  margin-bottom: 1.5rem;
}

.info-card:hover {
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

/* === Event Gallery (Final Square Version) === */
.event-image-card {
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-radius: 1.5rem;
  padding: 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;
  aspect-ratio: 1 / 1;       /* fixed square */
  max-width: 340px;          /* bigger size */
  margin-inline: auto;
}

.event-image-card img {
  width: 100%;
  height: 100%;
  aspect-ratio: 1 / 1;       /* perfect square */
  object-fit: cover;
  border-radius: 1.25rem;
  border: 2px solid rgba(255, 255, 255, 0.35);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-image-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-image-card:hover {
  transform: translateY(-6px) scale(1.02);
  border-color: var(--primary);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.event-image-card:hover::before {
  opacity: 1;
}



.event-image-card:hover img {
  transform: scale(1.05);
}

/* === iOS Button === */
.ios-btn-primary {
  color: white;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  border: none;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
  box-shadow: 0 6px 20px rgba(0,122,255,0.25);
  padding: 0.625rem 1.5rem;
  border-radius: 50px;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  position: relative;
  overflow: hidden;
}

.ios-btn-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s ease;
}

.ios-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0,122,255,0.4);
}

.ios-btn-primary:hover::before {
  left: 100%;
}

/* === Back Button === */
.event-back-btn {
  background: #f8fafc;
  color: var(--text-dark);
  border: 2px solid #e5e7eb;
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.event-back-btn:hover {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59,130,246,0.3);
}

/* === Section Titles === */
.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--text-dark) 0%, #374151 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  position: relative;
  padding-bottom: 0.5rem;
  margin-bottom: 1.5rem;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  border-radius: 2px;
}

/* === Tag Styling === */
.tag {
  display: inline-block;
  background: rgba(59, 130, 246, 0.1);
  color: var(--primary);
  padding: 0.25rem 0.75rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-right: 0.5rem;
  margin-bottom: 0.5rem;
}

/* === Detail Items === */
.detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: rgba(59, 130, 246, 0.05);
  border-radius: 0.75rem;
  border-left: 4px solid var(--primary);
}

.detail-icon {
  width: 24px;
  height: 24px;
  margin-right: 0.75rem;
  color: var(--primary);
}

.detail-content {
  flex: 1;
}

.detail-label {
  font-weight: 600;
  color: var(--text-dark);
  font-size: 0.9rem;
}

.detail-value {
  color: var(--text-muted);
  font-size: 0.95rem;
}

/* === Responsive === */
@media (max-width: 768px) {
  .shape-1 { width: 150px; height: 150px; }
  .shape-2 { width: 100px; height: 100px; }
  .shape-3 { width: 80px; height: 80px; }
  .shape-4 { width: 100px; height: 100px; }
  
  .hero-title { font-size: 2rem; }
  .hero-image { height: 300px; }
  
  .event-image-card img {
    height: 220px;
    aspect-ratio: 16/9;
  }
}

@media (min-width: 768px) {
  .event-image-card img {
    height: 280px;
    aspect-ratio: 16/10;
  }
}

@media (min-width: 1024px) {
  .event-image-card img {
    height: 300px;
    aspect-ratio: 16/9;
  }
}
</style>

<!-- ========================
       EVENT VIEW CARD
======================== -->
<div class="event-view-container max-w-5xl mx-auto mt-16 mb-28 px-4">
    <!-- Floating shapes -->
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
    <div class="floating-shape shape-4"></div>

    <!-- Hero Section -->
    <div class="event-hero-section animate-fade-in">
        <?php if (!empty($event['main_image'])): ?>
            <img src="../uploads/<?= htmlspecialchars($event['main_image']) ?>" 
                 class="hero-image">
            <div class="hero-content">
                <div class="flex justify-between items-end mb-2">
                    <h1 class="hero-title"><?= htmlspecialchars($event['title']) ?></h1>
                </div>

                <div class="flex flex-wrap">
                    <?php if($club): ?>
                        <a href="club_view.php?id=<?= $club['id'] ?>" class="tag hover:bg-white/20 transition">
                             🏢 <?= htmlspecialchars($club['club_name']) ?>
                        </a>
                    <?php endif; ?>
                    <span class="tag"><?= htmlspecialchars($event['venue']) ?></span>
                    <span class="tag"><?= $event['event_date'] ?></span>
                    <span class="tag"><?= !empty($event['price']) ? 'LKR ' . number_format($event['price'], 2) : 'Free Entry' ?></span>
                    <?php if ($eventPast): ?>
                        <span class="tag" style="background: rgba(239,68,68,0.1); color: #ef4444;">Event Passed</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="hero-image bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center relative">
                <div class="absolute inset-0 flex flex-col justify-end p-8">
                    <div class="flex justify-between items-end">
                        <div class="flex flex-col gap-2">
                             <?php if($club): ?>
                                <a href="club_view.php?id=<?= $club['id'] ?>" class="text-white/80 hover:text-white text-sm font-semibold flex items-center gap-2 w-fit">
                                     🏢 <?= htmlspecialchars($club['club_name']) ?>
                                </a>
                            <?php endif; ?>
                            <h1 class="hero-title text-white"><?= htmlspecialchars($event['title']) ?></h1>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Instagram Style Interaction Bar -->
    <div class="flex items-center gap-4 mb-8 px-2 animate-fade-in animation-delay-200">
        <?php 
        $isLiked = false;
        $likeCount = 0;
        
        // Fetch Like Count
        $countQuery = $pdo->prepare("SELECT COUNT(*) FROM event_likes WHERE event_id = ?");
        $countQuery->execute([$event_id]);
        $likeCount = $countQuery->fetchColumn();

        if (isset($_SESSION['user_id'])) {
            $likeCheck = $pdo->prepare("SELECT id FROM event_likes WHERE user_id = ? AND event_id = ?");
            $likeCheck->execute([$_SESSION['user_id'], $event_id]);
            $isLiked = $likeCheck->rowCount() > 0;
        }
        ?>
        <div class="flex flex-col">
            <button onclick="toggleLike(<?= $event_id ?>)" 
                    id="likeBtn"
                    class="focus:outline-none transition-transform active:scale-125">
                <i class="mdi <?= $isLiked ? 'mdi-heart text-red-500' : 'mdi-heart-outline text-gray-800' ?> text-4xl"></i>
            </button>
            <span id="likeCountText" class="text-sm font-bold text-gray-800 ml-1">
                <?= number_format($likeCount) ?> likes
            </span>
        </div>

        <div class="h-10 w-px bg-gray-200 mx-2"></div>
        
        <div class="flex-1">
             <p class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-1">Spread the word</p>
             <div class="flex gap-2">
                 <button onclick="shareEvent()" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                     <i class="mdi mdi-share-variant"></i> Share Event
                 </button>
             </div>
        </div>
    </div>

    <div class="content-grid">
        <!-- Main Content Column -->
        <div class="main-content">
            <!-- Event Description -->
            <div class="info-card animate-fade-in-up">
                <h2 class="section-title">About This Event</h2>
                <p class="text-gray-700 text-lg leading-relaxed whitespace-pre-line">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </p>
            </div>

            <!-- EVENT REVIEWS SECTION (Only for Past Events) -->
            <?php if ($eventPast): ?>
            <div class="info-card animate-fade-in-up animation-delay-200">
                <h2 class="section-title">Event Reviews</h2>

                <!-- Messages -->
                <?php if (isset($_GET['review'])): ?>
                    <?php if ($_GET['review'] == 'success'): ?>
                        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm font-medium">
                            ✅ Thanks for your review!
                        </div>
                    <?php elseif ($_GET['review'] == 'updated'): ?>
                        <div class="bg-blue-100 text-blue-700 p-3 rounded-lg mb-4 text-sm font-medium">
                            ✅ Review updated successfully!
                        </div>
                    <?php elseif ($_GET['review'] == 'deleted'): ?>
                        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-medium">
                            🗑️ Review deleted.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($reviewError): ?>
                    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-medium">
                         ⚠️ <?= htmlspecialchars($reviewError) ?>
                    </div>
                <?php endif; ?>

                <!-- Submission / Edit Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" class="mb-8 bg-white/40 p-4 rounded-xl border border-white/50">
                        <div class="mb-3">
                            <label class="block text-gray-700 font-semibold mb-2 text-sm">
                                <?= $myReview ? 'Edit your review' : 'Share your experience' ?>
                            </label>
                            <textarea name="review_text" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white/80"
                                      placeholder="How was the event? What did you like?" required><?= $myReview ? htmlspecialchars($myReview['review_text']) : '' ?></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="submit" name="submit_review" 
                                    class="<?= $myReview ? 'bg-blue-600 hover:bg-blue-700 shadow-lg text-white font-medium py-2 px-4 rounded-lg transition transform hover:-translate-y-0.5 active:scale-95 text-sm' : 'ios-btn-primary text-sm' ?>">
                                <?= $myReview ? 'Update Review' : 'Post Review' ?>
                            </button>
                            
                            <?php if ($myReview): ?>
                                <button type="submit" name="delete_review" 
                                        class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 font-medium py-2 px-4 rounded-lg transition text-sm"
                                        onclick="return confirm('Are you sure you want to delete your review?');">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="bg-gray-100 text-gray-600 p-4 rounded-lg mb-6 text-center border dashed border-gray-300">
                        <p class="text-sm">Please <a href="login.php" class="text-blue-600 font-bold hover:underline">Log In</a> to leave a review.</p>
                    </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <div class="space-y-4">
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="bg-white/60 backdrop-blur-sm p-4 rounded-xl border border-white/60 shadow-sm transition hover:shadow-md">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-xs uppercase shadow-sm">
                                        <?= substr($rev['username'], 0, 2) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-gray-800"><?= htmlspecialchars($rev['username']) ?></h4>
                                        <span class="text-xs text-gray-500"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                    </div>
                                </div>
                                <p class="text-gray-700 text-sm leading-relaxed pl-11">
                                    <?= nl2br(htmlspecialchars($rev['review_text'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 opacity-60">
                            <i class="mdi mdi-comment-outline text-4xl text-gray-300 mb-2 block"></i>
                            <p class="text-gray-500 italic text-sm">No reviews yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Event Gallery -->
            <?php if (!empty($event['extra_image_1']) || !empty($event['extra_image_2']) || !empty($event['extra_image_3'])): ?>
            <div class="info-card animate-fade-in-up animation-delay-200">
                <h2 class="section-title">Event Gallery</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (!empty($event['extra_image_1'])): ?>
                    <div class="event-image-card">
                        <img src="../uploads/<?= htmlspecialchars($event['extra_image_1']) ?>" 
                             alt="Event Image 1" />
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($event['extra_image_2'])): ?>
                    <div class="event-image-card">
                        <img src="../uploads/<?= htmlspecialchars($event['extra_image_2']) ?>" 
                             alt="Event Image 2" />
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($event['extra_image_3'])): ?>
                    <div class="event-image-card">
                        <img src="../uploads/<?= htmlspecialchars($event['extra_image_3']) ?>" 
                             alt="Event Image 3" />
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Column -->
        <div class="sidebar">
            <!-- Event Details -->
            <div class="info-card animate-fade-in-up">
                <h2 class="section-title">Event Details</h2>
                
                <div class="space-y-3">
                    <!-- Date & Time -->
                    <div class="detail-item">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="detail-content">
                            <div class="detail-label">Date & Time</div>
                            <div class="detail-value"><?= $event['event_date'] ?></div>
                        </div>
                    </div>

                    <!-- Venue -->
                    <div class="detail-item">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div class="detail-content">
                            <div class="detail-label">Venue</div>
                            <div class="detail-value"><?= htmlspecialchars($event['venue']) ?></div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="detail-item">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <div class="detail-content">
                            <div class="detail-label">Price</div>
                            <div class="detail-value"><?= !empty($event['price']) ? 'LKR ' . number_format($event['price'], 2) : 'Free Entry' ?></div>
                        </div>
                    </div>

                    <!-- Ticket Link -->
                    <?php if (!empty($event['ticket_url'])): ?>
                    <div class="detail-item">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <div class="detail-content">
                            <div class="detail-label">Tickets</div>
                            <div class="detail-value">
                                <a href="<?= htmlspecialchars($event['ticket_url']) ?>" 
                                   target="_blank" 
                                   class="text-blue-600 underline hover:text-blue-700 transition">
                                    Buy Tickets Online
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="info-card animate-fade-in-up animation-delay-200">
                <h2 class="section-title">Quick Actions</h2>
                <div class="space-y-3">
                    <!-- Go Back -->
                    <a href="index.php" class="event-back-btn w-full text-center block">
                        Go Back
                    </a>

                    <!-- Add Reminder -->
                    <?php if ($eventPast): ?>
                        <span class="w-full text-center px-5 py-2.5 bg-gray-400 text-white rounded-full shadow cursor-not-allowed block">
                            Event Passed
                        </span>
                    <?php elseif (isset($_SESSION['user_id'])): 
                        // Check if reminder exists
                        $remCheck = $pdo->prepare("SELECT id FROM reminders WHERE user_id = ? AND event_id = ?");
                        $remCheck->execute([$_SESSION['user_id'], $event_id]);
                        $hasReminder = $remCheck->rowCount() > 0;
                    ?>
                        <button onclick="toggleReminder(<?= $event_id ?>)" 
                                id="reminderBtn"
                                class="w-full text-center px-5 py-2.5 rounded-full shadow-md transition font-semibold block
                                <?= $hasReminder ? 'bg-green-600 text-white hover:bg-green-700' : 'ios-btn-primary' ?>">
                            <?= $hasReminder ? 'Reminder Set ✅' : 'Add Reminder' ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="ios-btn-primary w-full text-center block">
                            Add Reminder
                        </a>
                    <?php endif; ?>

                    <!-- View Club -->
                    <?php if ($club): ?>
                    <a href="club_view.php?id=<?= $club['id'] ?>" class="ios-btn-primary w-full text-center block" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        View Club
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLike(eventId) {
    const btnBox = document.getElementById('likeBtn');
    const likeText = document.getElementById('likeCountText');
    
    fetch('event_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_like', event_id: eventId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const icon = btnBox.querySelector('i');
            
            if (data.status === 'liked') {
                icon.classList.remove('mdi-heart-outline', 'text-gray-800');
                icon.classList.add('mdi-heart', 'text-red-500');
            } else {
                icon.classList.remove('mdi-heart', 'text-red-500');
                icon.classList.add('mdi-heart-outline', 'text-gray-800');
            }
            
            // Update count from backend data.count
            if (data.count !== undefined) {
                likeText.textContent = data.count + (data.count === 1 ? ' like' : ' likes');
            }
        } else {
            if (data.message === 'Login required') window.location.href = 'login.php';
        }
    });
}

function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: 'Event Details',
            text: 'Check out this event on EventHorizan!',
            url: window.location.href
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Event link copied to clipboard!');
        });
    }
}

function toggleReminder(eventId) {
    fetch('event_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_reminder', event_id: eventId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('reminderBtn');
            if (data.status === 'added') {
                btn.textContent = 'Reminder Set ✅';
                btn.classList.remove('ios-btn-primary');
                btn.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700');
            } else {
                btn.textContent = 'Add Reminder';
                btn.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700');
                btn.classList.add('ios-btn-primary');
            }
        } else {
            if (data.message === 'Login required') window.location.href = 'login.php';
        }
    });
}
</script>