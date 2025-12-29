<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<h3>Session Status:</h3>";
echo "<ul>";
echo "<li>Session ID: " . session_id() . "</li>";
echo "<li>Admin Logged In: " . (isset($_SESSION['admin_logged_in']) ? 'YES' : 'NO') . "</li>";

if (isset($_SESSION['admin_logged_in'])) {
    echo "<li>Admin ID: " . ($_SESSION['admin_id'] ?? 'not set') . "</li>";
    echo "<li>Admin Username: " . ($_SESSION['admin_username'] ?? 'not set') . "</li>";
    echo "<li>Admin Role: " . ($_SESSION['admin_role'] ?? 'not set') . "</li>";
    echo "<li>Club ID: " . ($_SESSION['club_id'] ?? 'NULL (super admin)') . "</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3>Test Auth Helper:</h3>";
require_once('../includes/auth_helper.php');

echo "<p>isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";
echo "<p>getAdminRole(): " . (getAdminRole() ?? 'NULL') . "</p>";

echo "<hr>";
echo "<h3>Test Database Connection:</h3>";
try {
    require_once('../includes/db_connection.php');
    echo "<p style='color: green;'>✓ Database connected</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) FROM clubs");
    $count = $stmt->fetchColumn();
    echo "<p>Total clubs in database: " . $count . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='admin_logout.php'>Logout</a> | <a href='admin_login.php'>Login Page</a> | <a href='super_admin_dashboard.php'>Try Dashboard Again</a>";
?>
