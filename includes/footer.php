<script src="https://cdn.tailwindcss.com"></script>

<style>
  :root {
    --primary: #007aff;
    --primary-light: #409cff;
    --glass: rgba(255, 255, 255, 0.75);
    --glass-border: rgba(255, 255, 255, 0.4);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --text-dark: #0f172a;
    --text-muted: #6b7280;
  }

  /* Footer Link Hover Effects */
  .footer-link {
    position: relative;
    transition: var(--transition);
    cursor: pointer;
    color: var(--text-muted);
  }

  .footer-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1.5px;
    background: var(--primary);
    transition: width 0.3s ease;
  }

  .footer-link:hover {
    color: var(--primary);
  }

  .footer-link:hover::after {
    width: 100%;
  }

  /* Footer Section Title */
  .footer-section-title {
    position: relative;
    padding-bottom: 0.75rem;
    font-weight: 700;
    color: var(--text-dark);
    font-size: 1.125rem;
  }

  .footer-section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2.5px;
    background: var(--primary);
    border-radius: 2px;
  }

  .glass-footer {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-top: 1px solid var(--glass-border);
  }
</style>

<footer class="mt-20 glass-footer">

  <!-- MAIN FOOTER CONTENT -->
  <div class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-12">

    <!-- LOGO + INFO -->
    <div class="flex flex-col items-center md:items-start text-center md:text-left space-y-4">
      <img src="/images/apiit.png" alt="Logo" class="w-32 drop-shadow-sm brightness-110">
      <p class="text-sm text-slate-600 max-w-xs">
        Your ultimate hub for campus events and vibrant student life at APIIT.
      </p>
      <div class="space-y-2 text-gray-600 text-sm">
        <p class="flex items-center justify-center md:justify-start gap-2">
            <span class="opacity-70">📍</span> Colombo, Sri Lanka
        </p>
        <p class="flex items-center justify-center md:justify-start gap-2">
            <span class="opacity-70">📞</span> +94 77 748 9289
        </p>
        <p class="flex items-center justify-center md:justify-start gap-2">
            <span class="opacity-70">✉️</span> support@eventhorizon.lk
        </p>
      </div>
    </div>

    <!-- EXPLORE SECTION -->
    <div class="flex flex-col items-center md:items-start">
      <h3 class="footer-section-title mb-6">Explore</h3>
      <ul class="space-y-3 text-sm">
        <li><a href="/index.php#calendar" class="footer-link">Upcoming Events</a></li>
        <li><a href="/index.php#calendar" class="footer-link">Event Calendar</a></li>
        <li><a href="/Pages/clubs.php" class="footer-link">All Clubs</a></li>
        <li><a href="/index.php#clubs" class="footer-link">Student Communities</a></li>
      </ul>
    </div>

    <!-- QUICK LINKS SECTION -->
    <div class="flex flex-col items-center md:items-start">
      <h3 class="footer-section-title mb-6">Quick Links</h3>
      <ul class="space-y-3 text-sm">
        <li><a href="/index.php" class="footer-link">Home</a></li>
        <li><a href="/Pages/login.php" class="footer-link">Student Login</a></li>
        <li><a href="/Pages/signup.php" class="footer-link">Create Account</a></li>
        <li><a href="mailto:support@eventhorizon.lk" class="footer-link">Contact Support</a></li>
      </ul>
    </div>

    <!-- STAFF PORTAL SECTION -->
    <div class="flex flex-col items-center md:items-start">
      <h3 class="footer-section-title mb-6">Portal</h3>
      <ul class="space-y-3 text-sm">
        <li><a href="/Admin_Dashboard/admin_login.php" class="footer-link font-medium text-blue-600">Staff & Club Login</a></li>
        <li><a href="#" class="footer-link">Terms of Service</a></li>
        <li><a href="#" class="footer-link">Privacy Policy</a></li>
      </ul>
    </div>

  </div>

  <!-- COPYRIGHT BAR -->
  <div class="border-t border-white/20 bg-white/40 py-6">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-slate-500 text-xs text-center md:text-left">
        &copy; 2025 Event Horizon • APIIT Campus Life. All rights reserved.
      </p>
      <div class="flex gap-6 text-slate-400 text-xs">
         <span class="hover:text-blue-500 cursor-pointer transition-colors">Instagram</span>
         <span class="hover:text-blue-500 cursor-pointer transition-colors">LinkedIn</span>
         <span class="hover:text-blue-500 cursor-pointer transition-colors">Twitter</span>
      </div>
    </div>
  </div>

</footer>
