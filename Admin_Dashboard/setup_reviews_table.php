<?php
require_once(__DIR__ . '/../includes/db_connection.php');

try {
    $sql = "CREATE TABLE IF NOT EXISTS event_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        review_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_review (user_id, event_id)
    )";

    $pdo->exec($sql);
    echo "Table 'event_reviews' created successfully.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
