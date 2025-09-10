<?php
require_once 'config.php';

// Test database connection
if (isset($pdo)) {
    echo "Database connected successfully!<br>";
    
    // Check if users table exists
    try {
        $stmt = $pdo->query("DESCRIBE users");
        echo "<h3>Users table structure:</h3>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " - " . $row['Type'] . "<br>";
        }
    } catch (PDOException $e) {
        echo "Users table doesn't exist. Creating it...<br>";
        
        // Create users table
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($sql);
            echo "Users table created successfully!<br>";
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "Database connection failed!";
}
?>