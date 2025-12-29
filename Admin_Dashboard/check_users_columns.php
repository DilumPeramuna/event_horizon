<?php
require_once(__DIR__ . '/../includes/db_connection.php');

try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in users table: " . implode(", ", $columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
