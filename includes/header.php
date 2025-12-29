<?php
// File: header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title ?? "EventHorizan"; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #007aff;
      --primary-light: #409cff;
      --primary-dark: #0056d8;
      --glass: rgba(255, 255, 255, 0.7);
      --glass-border: rgba(255, 255, 255, 0.3);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* iOS Button Primary */
    .ios-btn-primary {
      color: white;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      border: none;
      cursor: pointer;
      transition: var(--transition);
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

    /* iOS Button Red (Logout) */
    .ios-btn-red {
      color: white;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border: none;
      cursor: pointer;
      transition: var(--transition);
      backdrop-filter: blur(10px);
      box-shadow: 0 6px 20px rgba(239,68,68,0.25);
      padding: 0.625rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      position: relative;
      overflow: hidden;
    }

    .ios-btn-red::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s ease;
    }

    .ios-btn-red:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(239,68,68,0.4);
    }

    .ios-btn-red:hover::before {
      left: 100%;
    }

    /* User Avatar with Glass Effect */
    .user-avatar {
      width: 2.5rem;
      height: 2.5rem;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
      font-weight: bold;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,122,255,0.3);
      transition: var(--transition);
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .user-avatar:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 20px rgba(0,122,255,0.4);
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-900">

<!-- ======================================
            HEADER NAVBAR
====================================== -->
<header class="fixed top-0 left-0 w-full z-50
               bg-white/30 backdrop-blur-2xl
               border-b border-white/20 shadow-sm">

  <div class="max-w-7xl mx-auto px-6 flex justify-between items-center h-16">

    <!-- LOGO -->
    <div class="flex-shrink-0">
      <a href="/"
         class="flex items-center gap-3 transition-transform duration-200 hover:scale-105">
        <img class="h-12 w-auto drop-shadow-sm" src="/images/apiit.png" alt="APIIT Logo">
      </a>
    </div>

    <!-- DESKTOP NAV -->
    <nav class="hidden md:flex gap-8 items-center font-medium">

      <a href="/"
         class="nav-link relative text-gray-800 hover:text-blue-600 transition
                after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0
                after:bg-blue-600 after:transition-all after:duration-300
                hover:after:w-full">
        Home
      </a>

      <a href="/Pages/clubs.php"
         class="nav-link relative text-gray-800 hover:text-blue-600 transition
                after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0
                after:bg-blue-600 after:transition-all after:duration-300
                hover:after:w-full">
        Clubs
      </a>

      <a href="/Pages/reminders.php"
         class="nav-link relative text-gray-800 hover:text-blue-600 transition
                after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0
                after:bg-blue-600 after:transition-all after:duration-300
                hover:after:w-full">
        Reminders
      </a>

      <!-- USER LOGIN / LOGOUT -->
      <?php if(isset($_SESSION['email'])): ?>
        <?php $firstLetter = strtoupper($_SESSION['email'][0]); ?>

        <div class="flex items-center gap-3 ml-4">
          <div class="user-avatar">
            <?= $firstLetter ?>
          </div>

          <a href="/Pages/logout.php" class="ios-btn-red">
            Logout
          </a>
        </div>

      <?php else: ?>
        <a href="/Pages/login.php" class="ml-4 ios-btn-primary">
          Login
        </a>
      <?php endif; ?>

    </nav>

    <!-- MOBILE MENU BUTTON -->
    <button id="mobile-menu-button" class="md:hidden text-gray-800 hover:text-blue-600 transition">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>

  </div>

  <!-- ======================================
               MOBILE MENU
  ====================================== -->
  <div id="mobile-menu"
       class="hidden md:hidden bg-white/80 backdrop-blur-xl border-b border-white/20 shadow-sm">

    <nav class="px-6 pt-4 pb-6 space-y-4 text-gray-900 font-medium">

      <a href="/"
         class="block text-lg hover:text-blue-600 transition nav-link">
        Home
      </a>

      <a href="/Pages/clubs.php"
         class="block text-lg hover:text-blue-600 transition nav-link">
        Clubs
      </a>

      <a href="/Pages/reminders.php"
         class="block text-lg hover:text-blue-600 transition nav-link">
        Reminders
      </a>

      <?php if(isset($_SESSION['email'])): ?>
        <?php $firstLetter = strtoupper($_SESSION['email'][0]); ?>

        <div class="flex items-center gap-4 mt-4">
          <div class="user-avatar w-11 h-11 text-base">
            <?= $firstLetter ?>
          </div>

          <a href="/Pages/logout.php" class="ios-btn-red flex-1 text-center">
            Logout
          </a>
        </div>

      <?php else: ?>
        <a href="/Pages/login.php" class="block text-center mt-3 ios-btn-primary w-full">
          Login
        </a>
      <?php endif; ?>

    </nav>
  </div>

</header>

<!-- Push content down -->
<div class="pt-24"></div>

<!-- ======================================
            SCRIPT
====================================== -->
<script>
  const menuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  const navLinks = document.querySelectorAll('.nav-link');

  if(menuButton){
    menuButton.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Highlight active page
  const currentPath = window.location.pathname.split("/").pop();
  navLinks.forEach(link => {
    const linkPath = link.getAttribute('href').split("/").pop();

    if (linkPath === currentPath || 
        (currentPath === "" && linkPath === "index.php")) {

      link.classList.add('text-blue-600', 'font-semibold');
      link.classList.remove('text-gray-800');
    }
  });
</script>
