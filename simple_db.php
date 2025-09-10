<?php
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create simple customers table
    $pdo->exec("DROP TABLE IF EXISTS customers");
    $pdo->exec("CREATE TABLE customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        account VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ Database created successfully!<br>";
    echo "<a href='manage_customers.php'>Manage Customers</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>