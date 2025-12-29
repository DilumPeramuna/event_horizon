  <script src="https://cdn.tailwindcss.com"></script>
<?php
require_once('../includes/db_connection.php');
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<span class='text-red-600 font-semibold'>Invalid email address.</span>";

    } elseif ($password !== $confirm_password) {
        $message = "<span class='text-red-600 font-semibold'>Passwords do not match.</span>";

    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashedPassword]);

            $message = "
                <span class='text-green-600 font-semibold'>
                    Account created successfully! 
                    You can now <a href='login.php' class='underline text-green-700'>login</a>.
                </span>
            ";

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "<span class='text-red-600 font-semibold'>Email already registered.</span>";
            } else {
                $message = "<span class='text-red-600 font-semibold'>Error: " . $e->getMessage() . "</span>";
            }
        }
    }
}

 
?>

<!-- ==========================
          SIGNUP UI
========================== -->
<br><br>
<div class="max-w-md mx-auto mt-28 mb-20 bg-white/90 backdrop-blur-xl p-10 rounded-3xl shadow-xl border border-gray-200">

    <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-6 tracking-tight">
        Create an Account
    </h2>

    <?php if ($message): ?>
        <p class="mb-4 text-center bg-gray-100 border border-gray-300 rounded-xl py-3">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="space-y-6">

        <!-- Email Field -->
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

        <!-- Confirm Password -->
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
            <input 
                type="password" 
                name="confirm_password"
                class="w-full px-4 py-2 bg-white/80 border border-gray-300 rounded-xl 
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                required>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold 
                   shadow-md hover:bg-blue-700 active:scale-95 transition">
            Sign Up
        </button>
    </form>

    <!-- Login Link -->
    <p class="mt-6 text-center text-gray-600">
        Already have an account? 
        <a href="login.php" class="text-blue-600 font-semibold hover:underline">
            Login
        </a>
    </p>
</div>

 
