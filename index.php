<?php
session_start();
$title = "Home";
include('includes/header.php');
include('includes/db_connection.php');

// =====================
// Reminder popup logic
// =====================
$showPopup = false;
$reminderList = [];

if (isset($_SESSION['user_id']) && isset($_SESSION['just_logged_in'])) {

    unset($_SESSION['just_logged_in']); // show only once
    $user_id = $_SESSION['user_id'];

    $reminderQuery = "
        SELECT e.title, e.event_date
        FROM reminders r
        INNER JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ? AND e.event_date >= NOW()
        ORDER BY e.event_date ASC
    ";

    $stmt = $pdo->prepare($reminderQuery);
    $stmt->execute([$user_id]);
    $reminderList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($reminderList)) {
        $showPopup = true;
    }
}

// =====================
// Delete events older than 30 days
// =====================
$deleteStmt = $pdo->prepare("DELETE FROM events WHERE event_date < NOW() - INTERVAL 30 DAY");
$deleteStmt->execute();

// =====================
// Fetch events
// =====================
$query = "SELECT id, title, event_date FROM events ORDER BY event_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================
// Top clubs
// =====================
$clubQuery = "SELECT clubs.*, events.title AS event_title
              FROM clubs
              LEFT JOIN events ON events.club_id = clubs.id
              GROUP BY clubs.id
              ORDER BY clubs.id DESC
              LIMIT 3";
$clubStmt = $pdo->prepare($clubQuery);
$clubStmt->execute();
$clubs = $clubStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- =========================
     REMINDER POPUP
========================= -->
<?php if ($showPopup): ?>
<div id="reminderPopup" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="p-6 rounded-2xl max-w-md w-full animate-[fadeIn_0.3s_ease]">
        <h2 class="text-2xl font-bold mb-4" style="color: var(--text-dark);">🔔 Upcoming Reminders</h2>

        <ul class="space-y-3 mb-4">
            <?php foreach ($reminderList as $r): ?>
                <li class="reminder-item p-3 rounded-lg shadow-sm">
                    <strong style="color: var(--text-dark);"><?= htmlspecialchars($r['title']) ?></strong><br>
                    <span class="text-sm" style="color: var(--text-muted);">Date: <?= $r['event_date'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <button onclick="document.getElementById('reminderPopup').remove();"
                class="ios-btn-primary w-full">
            OK
        </button>
    </div>
</div>
<?php endif; ?>

<script>
const events = <?php echo json_encode($events); ?>;
</script>

<!-- =========================
     HERO SECTION STYLES & THEME
========================= -->
<style>
/* ======================
   CSS VARIABLES (THEME)
====================== */
:root {
  --primary: #007aff;
  --primary-light: #409cff;
  --primary-dark: #0056d8;
  --glass: rgba(255, 255, 255, 0.7);
  --glass-border: rgba(255, 255, 255, 0.3);
  --glass-shadow: rgba(0, 0, 0, 0.05);
  --border: rgba(0, 0, 0, 0.08);
  --text-dark: #0f172a;
  --text-muted: #6b7280;
  --radius: 1.5rem;
  --radius-lg: 2rem;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --blur: blur(20px);
}

/* ======================
   GLASS MODAL/POPUP
====================== */
#reminderPopup {
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

#reminderPopup > div {
  background: var(--glass);
  backdrop-filter: var(--blur) saturate(180%);
  -webkit-backdrop-filter: var(--blur) saturate(180%);
  border: 1px solid var(--glass-border);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.reminder-item {
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
}

/* ======================
   BASE & TYPOGRAPHY ENHANCEMENTS
====================== */
body {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text-dark);
  -webkit-font-smoothing: antialiased;
  line-height: 1.6;
}

h1, h2, h3 {
  font-weight: 700;
  color: var(--text-dark);
  letter-spacing: -0.025em;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Focus states for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
  border-radius: var(--radius);
}

/* ======================
   PREMIUM HERO SECTION
====================== */
.hero-section {
  padding-top: 4rem;
  padding-bottom: 6rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
  position: relative;
  overflow: hidden;
}

@media (min-width: 1024px) {
  .hero-section {
    height: 650px;
  }
}

.hero-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 800px;
  height: 900px;
  background: radial-gradient(circle, rgba(0,122,255,0.08) 0%, rgba(0,122,255,0) 70%);
  border-radius: 50%;
}

.hero-section::after {
  content: '';
  position: absolute;
  bottom: -30%;
  left: -10%;
  width: 600px;
  height: 650px;
  background: radial-gradient(circle, rgba(0,122,255,0.05) 0%, rgba(0,122,255,0) 70%);
  border-radius: 50%;
}

/* ======================
   ENHANCED 3D CAROUSEL WITH GLASS EFFECT
====================== */
.carousel-wrapper {
  perspective: 1200px;
  width: 500px;
  height: 280px;
  position: relative;
  margin-left: 10rem;
  margin-top: -18rem;
}

@media (max-width: 1023px) {
  .carousel-wrapper {
    margin-left: auto;
    margin-right: auto;
    margin-top: 2rem;
    margin-bottom: 4rem; /* Add space below carousel on mobile */
  }
}

.carousel-3d {
  position: absolute;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  animation: spin 10s linear infinite;
  transform: rotateX(-10deg);
}

.carousel-3d img {
  position: absolute;
  width: 160px;
  height: 200px;
  left: 50%;
  top: 50%;
  object-fit: cover;
  border-radius: 1rem;
  transform-origin: 50% 50%;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
  -webkit-box-reflect: below 8px linear-gradient(transparent, rgba(0, 0, 0, 0.25));
  backface-visibility: hidden;
  transition: transform 0.4s ease;
}

.carousel-3d img:hover {
  transform: scale(1.1) translateZ(20px);
  z-index: 2;
}

@keyframes spin {
  from { transform: rotateX(-10deg) rotateY(0deg); }
  to { transform: rotateX(-10deg) rotateY(360deg); }
}

.carousel-wrapper::after {
  content: "";
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 260px;
  height: 40px;
  background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.25) 0%, transparent 70%);
  filter: blur(6px);
}

/* ======================
   ENHANCED LIQUID GLASS BUTTONS
====================== */
.ios-btn-primary {
  display: inline-block;
  padding: 1rem 2rem;
  border-radius: 50px;
  font-weight: 600;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  color: white;
  transition: var(--transition);
  border: none;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 25px rgba(0, 122, 255, 0.25);
  text-decoration: none;
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
  box-shadow: 0 12px 35px rgba(0, 122, 255, 0.35);
}

.ios-btn-primary:hover::before {
  left: 100%;
}

.ios-btn-glass {
  display: inline-block;
  padding: 1rem 2rem;
  border-radius: 50px;
  font-weight: 600;
  background: var(--glass);
  border: 1px solid var(--glass-border);
  backdrop-filter: var(--blur);
  -webkit-backdrop-filter: var(--blur);
  transition: var(--transition);
  color: var(--text-dark);
  position: relative;
  overflow: hidden;
  text-decoration: none;
}

.ios-btn-glass::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0.1) 100%);
  opacity: 0;
  transition: var(--transition);
}

.ios-btn-glass:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
}

.ios-btn-glass:hover::before {
  opacity: 1;
}

/* Hero typography */
.hero-heading {
  font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 3.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--text-dark) 0%, #374151 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.1;
  margin-bottom: 1.5rem;
  letter-spacing: -0.02em;
}

.hero-subheading {
  font-size: 1.25rem;
  color: var(--text-muted);
  max-width: 480px;
  margin: 0 auto 2rem 0;
  font-weight: 400;
  line-height: 1.6;
}

/* Consolidated Responsive Styles */
@media (max-width: 1200px) {
  .hero-section .container {
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
  }
}

@media (max-width: 1024px) {
  .hero-section .container {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 3rem;
  }
  
  .hero-subheading {
    margin: 0 auto 2rem auto;
  }
  
  .hero-heading {
    font-size: 3rem;
  }

  .carousel-wrapper {
    width: 400px;
    height: 240px;
    margin-left: auto;
    margin-right: auto;
    margin-top: -1.5rem;
  }
  
  .carousel-3d img {
    width: 140px;
    height: 180px;
  }
}

@media (max-width: 768px) {
  .hero-section {
    padding-top: 0;
    padding-bottom: 4rem;
  }
  
  .hero-section .container {
    padding-top: 1.5rem; /* Reduced for less top space */
  }
  
  .hero-heading {
    font-size: 2.5rem;
  }
  
  .hero-subheading {
    font-size: 1.125rem;
  }
  
  .carousel-wrapper {
    width: 300px;
    height: 250px;
    margin-left: auto;
    margin-right: auto;
    margin-top: -2rem; /* Moved higher */
    margin-bottom: 8rem; /* Space to keep text in place */
  }
  
  .carousel-3d img {
    width: 120px;
    height: 160px;
  }
  
  .carousel-wrapper::after {
    width: 200px;
  }

  .calendar-day {
    min-height: 160px;
    padding: 0.75rem;
  }
}

@media (max-width: 640px) {
  .hero-section .container {
    padding-top: 1rem; /* Even less top space */
  }
  
  .hero-heading {
    font-size: 2rem;
  }
  
  .hero-subheading {
    font-size: 1rem;
  }
  
  .carousel-wrapper {
    width: 260px;
    height: 220px;
    margin-left: auto;
    margin-right: auto;
    margin-top: -2.5rem; /* Even higher on smaller screens */
    margin-bottom: 9rem; /* Space to keep text in place */
  }
  
  .carousel-3d img {
    width: 100px;
    height: 140px;
  }
}

@media (max-width: 480px) {
  .hero-heading {
    font-size: 2rem;
  }
}
</style>

<!-- =========================
     HERO SECTION
========================= -->
<section class="hero-section relative overflow-hidden -mt-24">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl pt-32">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <!-- Left Content -->
      <div class="text-center lg:text-left order-2 lg:order-1">
        <h1 class="hero-heading">
          Discover Vibrant Campus Life
        </h1>
        <p class="hero-subheading">
          Explore events and clubs at APIIT with ease — and seamlessly add reminders to your Outlook calendar.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
          <a href="#calendar" class="ios-btn-primary">Explore Events</a>
          <a href="#clubs" class="ios-btn-glass">Find a Club</a>
        </div>
      </div>

      <!-- Right Carousel -->
      <div class="carousel-wrapper order-1 lg:order-2">
        <div class="carousel-3d" id="carousel3d">
          <img src="images/h1.jpeg" alt="Campus Life 1">
          <img src="images/h2.webp" alt="Campus Life 2">
          <img src="images/h3.webp" alt="Campus Life 3">
          <img src="images/h4.webp" alt="Campus Life 4">
          <img src="images/h2.webp" alt="Campus Life 5">
          <img src="images/h3.webp" alt="Campus Life 6">
          <img src="images/h2.webp" alt="Campus Life 7">
          <img src="images/h3.webp" alt="Campus Life 8">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =========================
     3D CAROUSEL ANIMATION SCRIPT
========================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.getElementById('carousel3d');
  if (!carousel) return;
  
  const images = carousel.querySelectorAll('img');
  const total = images.length;
  
  if (total === 0) return;
  
  const angle = 360 / total;
  const radius = 220;
  
  images.forEach((img, i) => {
    const rot = angle * i;
    img.style.transform = `rotateY(${rot}deg) translateZ(${radius}px)`;
  });
  
  // Adjust radius for smaller screens
  function adjustCarousel() {
    const radius = window.innerWidth < 768 ? 160 : window.innerWidth < 1024 ? 185 : 220;
    images.forEach((img, i) => {
      const rot = angle * i;
      img.style.transform = `rotateY(${rot}deg) translateZ(${radius}px)`;
    });
  }
  
  window.addEventListener('resize', adjustCarousel);
  adjustCarousel();
});
</script>


<!-- =========================
     TITLE
========================= -->
<h1 id="calendar" class="text-center text-5xl md:text-5xl font-extrabold tracking-tight mt-12 text-gray-900 drop-shadow-sm">
  Event Horizon
</h1>

<!-- =========================
     CALENDAR
========================= -->
<div class="calendar-section max-w-6xl mx-auto mt-12">
    <div class="calendar-container">
        <div class="calendar-nav">
            <button id="prevMonth" class="ios-chip">
                &lt;
            </button>

            <h2 id="monthYear" class="calendar-month-label"></h2>

            <button id="nextMonth" class="ios-chip">
                &gt;
            </button>
        </div>

        <div class="calendar-weekdays">
            <div class="desktop-only text-sm font-semibold text-gray-400">Sun</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Mon</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Tue</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Wed</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Thu</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Fri</div>
            <div class="desktop-only text-sm font-semibold text-gray-400">Sat</div>
            <div class="mobile-only text-xs font-bold text-gray-400">S</div>
            <div class="mobile-only text-xs font-bold text-gray-400">M</div>
            <div class="mobile-only text-xs font-bold text-gray-400">T</div>
            <div class="mobile-only text-xs font-bold text-gray-400">W</div>
            <div class="mobile-only text-xs font-bold text-gray-400">T</div>
            <div class="mobile-only text-xs font-bold text-gray-400">F</div>
            <div class="mobile-only text-xs font-bold text-gray-400">S</div>
        </div>

        <div id="calendarDays" class="calendar-grid"></div>
    </div>

    <!-- Mobile Only Selected Date Details -->
    <div id="dayDetails" class="day-details-container mt-8 animate-[fadeIn_0.5s_ease] hidden">
        <h3 id="selectedDateLabel" class="text-xl font-bold text-gray-900 mb-4"></h3>
        <div id="eventList" class="space-y-3"></div>
    </div>
</div>

<script>
const calendarDays = document.getElementById("calendarDays");
const monthYear = document.getElementById("monthYear");
const dayDetails = document.getElementById("dayDetails");
const selectedDateLabel = document.getElementById("selectedDateLabel");
const eventList = document.getElementById("eventList");

let currentDate = new Date();
let selectedDate = new Date();

function renderCalendar() {
    const month = currentDate.getMonth();
    const year = currentDate.getFullYear();
    const isMobile = window.innerWidth <= 768;

    monthYear.textContent = currentDate.toLocaleString("default", { month: "long", year: "numeric" });
    calendarDays.innerHTML = "";

    const firstDay = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();

    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    // Update details list visibility
    if (isMobile) {
        dayDetails.classList.remove('hidden');
    } else {
        dayDetails.classList.add('hidden');
    }

    for (let i = 0; i < firstDay; i++) {
        calendarDays.innerHTML += `<div class="calendar-empty"></div>`;
    }

    for (let day = 1; day <= lastDate; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = dateStr === todayStr;
        const selectedDateStr = selectedDate ? `${selectedDate.getFullYear()}-${String(selectedDate.getMonth() + 1).padStart(2, '0')}-${String(selectedDate.getDate()).padStart(2, '0')}` : null;
        const isSelected = dateStr === selectedDateStr;

        const todaysEvents = events.filter(e => e.event_date.startsWith(dateStr));
        
        let dayClass = isMobile 
            ? `calendar-day-mobile ${isToday ? 'is-today' : ''} ${isSelected ? 'is-selected' : ''}`
            : `calendar-day-desktop ${isToday ? 'is-today' : ''}`;

        if (isMobile) {
            let dotsHTML = '';
            if (todaysEvents.length > 0) {
                dotsHTML = `<div class="event-dots">${todaysEvents.slice(0, 3).map(() => '<span class="dot"></span>').join('')}</div>`;
            }
            calendarDays.innerHTML += `
                <div class="${dayClass}" onclick="selectDate(${year}, ${month}, ${day})">
                    <span class="day-number">${day}</span>
                    ${dotsHTML}
                </div>`;
        } else {
            let eventHTML = todaysEvents.map(e => {
                const time = new Date(e.event_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                return `<div class="calendar-event" onclick="window.location.href='Pages/event_view.php?id=${e.id}'">
                          <div class="event-title">${e.title}</div>
                          <div class="event-time">${time}</div>
                        </div>`;
            }).join('');
            
            calendarDays.innerHTML += `
                <div class="${dayClass}">
                    <div class="calendar-day-number">${day}</div>
                    <div class="calendar-events-container">${eventHTML}</div>
                </div>`;
        }
    }
    
    if (isMobile) updateDetails();
}

function selectDate(y, m, d) {
    selectedDate = new Date(y, m, d);
    renderCalendar();
}

function updateDetails() {
    const options = { weekday: 'long', month: 'long', day: 'numeric' };
    selectedDateLabel.textContent = selectedDate.toLocaleDateString('en-US', options);
    
    const dateStr = `${selectedDate.getFullYear()}-${String(selectedDate.getMonth() + 1).padStart(2, '0')}-${String(selectedDate.getDate()).padStart(2, '0')}`;
    const todaysEvents = events.filter(e => e.event_date.startsWith(dateStr));
    
    if (todaysEvents.length === 0) {
        eventList.innerHTML = `<p class="text-gray-400 text-sm italic">No events scheduled for this day.</p>`;
    } else {
        eventList.innerHTML = todaysEvents.map(e => {
            const time = new Date(e.event_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            return `
                <div class="event-card-ios" onclick="window.location.href='Pages/event_view.php?id=${e.id}'">
                    <div class="event-accent"></div>
                    <div class="flex-grow">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-gray-900">${e.title}</h4>
                            <span class="text-xs font-semibold text-blue-600">${time}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tap for details</p>
                    </div>
                </div>
            `;
        }).join('');
    }
}

document.getElementById("prevMonth").onclick = () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); };
document.getElementById("nextMonth").onclick = () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); };
window.addEventListener('resize', renderCalendar);
renderCalendar();
</script>

<style>
/* ======================
   LIQUID GLASS CALENDAR (RESTORED)
====================== */
.calendar-section {
  background: transparent;
  padding-top: 4rem;
  padding-bottom: 6rem;
  position: relative;
}

.calendar-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(248,250,252,0.9) 100%);
  backdrop-filter: var(--blur);
  -webkit-backdrop-filter: var(--blur);
  z-index: -1;
  border-radius: var(--radius-lg);
}

.calendar-container {
  background: var(--glass);
  backdrop-filter: var(--blur) saturate(180%);
  -webkit-backdrop-filter: var(--blur) saturate(180%);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  position: relative;
  width: 100%;
}

.calendar-nav {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.ios-chip {
  padding: 0.75rem 1rem;
  border-radius: 50px;
  background: var(--glass);
  border: 1px solid var(--glass-border);
  backdrop-filter: var(--blur);
  -webkit-backdrop-filter: var(--blur);
  transition: var(--transition);
  cursor: pointer;
  font-weight: 600;
  color: var(--text-dark);
  min-width: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-month-label {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--text-dark);
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 1rem;
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: var(--radius);
  padding: 1rem;
  border: 1px solid var(--glass-border);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.75rem;
}

/* Desktop Cell Style */
.calendar-day-desktop {
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  padding: 1rem;
  min-height: 160px;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  display: flex;
  flex-direction: column;
  transition: var(--transition);
}

.calendar-day-desktop:hover {
  transform: translateY(-2px);
  border-color: var(--primary);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.calendar-day-number {
  font-weight: 700;
  font-size: 1.125rem;
  margin-bottom: 0.5rem;
  text-align: center;
}

.calendar-events-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  overflow-y: auto;
  max-height: 120px;
}

.calendar-event {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 6px;
  padding: 0.4rem;
  cursor: pointer;
}

.event-title { font-size: 0.75rem; font-weight: 700; line-height: 1.2; }
.event-time { font-size: 0.65rem; color: var(--text-muted); }

/* Day States (Today & Selection) */
.is-today { 
  background: rgba(59, 130, 246, 0.1) !important; 
  border: 2px solid rgba(59, 130, 246, 0.5) !important; 
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

.is-selected {
  border: 2px solid var(--primary) !important;
  background: rgba(59, 130, 246, 0.05) !important;
}

.calendar-day-mobile {
  aspect-ratio: 1/1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  background: var(--glass);
  backdrop-filter: blur(5px);
  border: 1px solid var(--glass-border);
  transition: all 0.2s;
}

.calendar-day-mobile .day-number {
  color: var(--text-dark);
}

.event-dots { display: flex; gap: 2px; margin-top: 2px; }
.dot { width: 4px; height: 4px; background: var(--primary); border-radius: 50%; opacity: 0.6; }

/* Utility */
.desktop-only { display: block; }
.mobile-only { display: none; }

@media (max-width: 768px) {
  .desktop-only { display: none; }
  .mobile-only { display: block; }
  .calendar-grid { gap: 4px; }
  .calendar-container { padding: 1rem; }
  .calendar-month-label { font-size: 1.5rem; }
}

/* Event Details List (Mobile) */
.event-card-ios {
  display: flex;
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  gap: 1rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  border: 1px solid #f2f2f7;
}
.event-accent { width: 4px; background: var(--primary); border-radius: 4px; }

/* ======================
   CLUB CARDS WITH GLASS EFFECT
====================== */
#clubs {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  position: relative;
  overflow: hidden;
  padding-top: 4rem;
  padding-bottom: 6rem;
}

.club-card {
  border: 1px solid var(--glass-border);
  background: var(--glass);
  backdrop-filter: var(--blur);
  -webkit-backdrop-filter: var(--blur);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  text-align: center;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.club-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
  opacity: 0;
  transition: var(--transition);
}

.club-card:hover {
  transform: translateY(-6px) scale(1.02);
  border-color: var(--primary);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.club-card:hover::before {
  opacity: 1;
}

.club-card img {
  position: relative;
  z-index: 2;
  transition: var(--transition);
  border-radius: 0.75rem;
}

.club-card:hover img {
  transform: scale(1.05);
}

.club-card h3 {
  font-weight: 600;
  color: var(--text-dark);
  position: relative;
  z-index: 2;
}

.club-card p {
  color: var(--text-muted);
  position: relative;
  z-index: 2;
  flex-grow: 1;
}

.club-card button {
  position: relative;
  z-index: 2;
  margin-top: auto;
}
</style>

<!-- =========================
     TOP CLUBS (Redesigned)
========================= -->
<div id="clubs" class="max-w-6xl mx-auto mt-24 px-4">
    <h2 class="text-4xl font-extrabold mb-12 text-center text-gray-900 tracking-tight drop-shadow-sm">
        Explore Our Clubs
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($clubs as $club): ?>
        <?php 
            $shortDesc = !empty($club['short_description']) 
                         ? $club['short_description'] 
                         : $club['club_description'];
            
            // Default tags - you can customize these based on club name or add to database
            $tags = ['Social', 'Campus Life'];
            if (stripos($club['club_name'], 'tech') !== false || stripos($club['club_name'], 'resolv') !== false) {
                $tags = ['Tech & Innovation', 'Problem Solving'];
            } elseif (stripos($club['club_name'], 'rotaract') !== false || stripos($club['club_name'], 'service') !== false) {
                $tags = ['Community Service', 'Leadership'];
            } elseif (stripos($club['club_name'], 'art') !== false || stripos($club['club_name'], 'culture') !== false) {
                $tags = ['Arts & Culture', 'Social'];
            }
        ?>

        <div class="club-card">
            <img src="uploads/<?= htmlspecialchars($club['club_main_image']) ?>" 
                 alt="<?= htmlspecialchars($club['club_name']) ?>"
                 class="w-full h-56 object-cover rounded-xl mb-4">

           

            <h3 class="text-xl font-bold text-gray-900 mb-3">
                <?= htmlspecialchars($club['club_name']) ?>
            </h3>

            <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                <?= htmlspecialchars($shortDesc) ?>
            </p>

            <button onclick="window.location.href='Pages/club_view.php?id=<?= $club['id'] ?>'"
                    class="ios-btn-primary w-full">
                Explore Club
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>



<?php include('includes/footer.php'); ?>
