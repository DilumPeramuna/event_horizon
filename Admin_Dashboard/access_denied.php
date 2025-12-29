<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied - EventHorizan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-10 rounded-xl shadow-lg w-full max-w-md text-center">
  <div class="mb-6">
    <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
    </svg>
  </div>

  <h1 class="text-2xl font-bold text-gray-800 mb-2">Access Denied</h1>
  <p class="text-gray-600 mb-6">You do not have permission to access this page.</p>

  <div class="space-y-2">
    <a href="admin_login.php" class="block w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition">
      Back to Login
    </a>
    <a href="admin_logout.php" class="block w-full bg-gray-200 text-gray-700 p-3 rounded-lg hover:bg-gray-300 transition">
      Logout
    </a>
  </div>
</div>

</body>
</html>
