<?php
require_once(__DIR__ . '/../includes/db_connection.php');

try {
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "Keys in users table:\n";
        print_r(array_keys($row));
    } else {
        echo "Users table is empty. checking columns via schema.\n";
        $stmt = $pdo->query("DESCRIBE users");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        print_r($cols);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
