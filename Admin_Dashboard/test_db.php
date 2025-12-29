<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../includes/db_connection.php');

echo "<h2>Database Connection Test</h2>";
echo "<p>PHP is working!</p>";
echo "<p>Checking database connection...</p>";

try {
    $stmt = $pdo->query("SELECT DATABASE()");
    $db = $stmt->fetchColumn();
    echo "<p style='color: green;'>✓ Connected to database: <strong>" . $db . "</strong></p>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ admin_users table exists</p>";
        
        $stmt = $pdo->query("SELECT * FROM admin_users WHERE role = 'super_admin'");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            echo "<p style='color: green;'>✓ Super admin found: <strong>" . $admin['username'] . "</strong></p>";
            echo "<p>Password hash exists: " . (strlen($admin['password']) > 0 ? 'Yes' : 'No') . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Super admin NOT found in database</p>";
            echo "<p>Run the SQL migration again!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ admin_users table does NOT exist</p>";
        echo "<p>You need to run the migration SQL in phpMyAdmin!</p>";
    }
    
    echo "<hr>";
    echo "<h3>All Tables in Database:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
