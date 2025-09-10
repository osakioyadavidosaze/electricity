<?php
// Simple database fix
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate tables
    $pdo->exec("DROP TABLE IF EXISTS accounts");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create users table
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role ENUM('customer', 'staff', 'admin') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create accounts table
    $pdo->exec("CREATE TABLE accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        account_number VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    echo "✅ Database fixed successfully!<br>";
    echo "<a href='staff_simple.php'>Go to Staff Panel</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>