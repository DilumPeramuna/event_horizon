<script src="https://cdn.tailwindcss.com"></script>
<?php
session_start();
require_once('../includes/db_connection.php'); 

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['just_logged_in'] = true;

        header("Location: index.php");
        exit;

    } else {
        $message = "Invalid email or password.";
    }
}

 
?>
<br>
<br>
<br>
<br>
<!-- ==========================
      LOGIN CONTAINER
========================== -->
<div class="max-w-md mx-auto mt-28 mb-20 bg-white/90 backdrop-blur-xl p-10 rounded-3xl shadow-xl border border-gray-200">

    <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-6 tracking-tight">
        Welcome Back
    </h2>

    <?php if ($message): ?>
        <p class="mb-4 text-red-600 font-semibold text-center bg-red-100 border border-red-300 rounded-lg py-2">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="space-y-6">

        <!-- Email -->
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Email</label>
            <input 
                type="email" 
                name="email"
                class="w-full px-4 py-2 bg-white/80 border border-gray-300 rounded-xl 
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                required>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Password</label>
            <input 
                type="password" 
                name="password"
                class="w-full px-4 py-2 bg-white/80 border border-gray-300 rounded-xl 
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                required>
        </div>

        <!-- Submit -->
        <button 
            type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold 
                   shadow-md hover:bg-blue-700 active:scale-95 transition">
            Login
        </button>
    </form>

    <!-- SIGNUP LINK -->
    <p class="mt-6 text-center text-gray-600">
        Don’t have an account? 
        <a href="signup.php" class="text-blue-600 font-semibold hover:underline">
            Sign Up
        </a>
    </p>

</div>

 
