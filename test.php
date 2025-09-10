<?php
// Test database and functionality
require_once 'config.php';

echo "<h2>Database Test</h2>";

// Test connection
if (isset($pdo)) {
    echo "✅ Database connected<br>";
    
    // Check/create users table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        echo "✅ Users table exists<br>";
    } catch (PDOException $e) {
        echo "❌ Users table missing. Creating...<br>";
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "✅ Users table created<br>";
    }
    
    // Test API
    echo "<h3>API Test</h3>";
    echo "<form method='POST' action='api.php'>
        <input type='hidden' name='action' value='register'>
        <input type='text' name='full_name' placeholder='Name' required><br><br>
        <input type='email' name='email' placeholder='Email' required><br><br>
        <input type='tel' name='phone' placeholder='Phone' required><br><br>
        <input type='password' name='password' placeholder='Password' required><br><br>
        <button type='submit'>Test Register</button>
    </form>";
    
} else {
    echo "❌ Database connection failed";
}
?>