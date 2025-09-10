<?php
require_once 'config.php';

// Create all necessary tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role ENUM('customer', 'staff', 'admin') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        account_number VARCHAR(20) UNIQUE NOT NULL,
        account_type ENUM('residential', 'commercial', 'industrial') DEFAULT 'residential',
        address TEXT,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS bills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        amount DECIMAL(10,2) NOT NULL,
        units_consumed DECIMAL(8,2) NOT NULL,
        due_date DATE,
        status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS service_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        service_type VARCHAR(100),
        account_number VARCHAR(20),
        address TEXT,
        details TEXT,
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(255),
        message TEXT,
        type ENUM('email', 'sms', 'bill', 'system') DEFAULT 'system',
        status ENUM('sent', 'pending', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

echo "<h2>Database Setup</h2>";

foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Table created successfully<br>";
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

// Add missing columns if they don't exist
try {
    $pdo->exec("ALTER TABLE accounts ADD COLUMN IF NOT EXISTS account_type ENUM('residential', 'commercial', 'industrial') DEFAULT 'residential'");
    echo "✅ Account type column added<br>";
} catch (PDOException $e) {
    echo "Account type column exists<br>";
}

// Create default admin user
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin@osaze.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin User', 'admin']);
    echo "✅ Admin user created (admin@osaze.com / admin123)<br>";
} catch (PDOException $e) {
    echo "Admin user exists<br>";
}

echo "<br><a href='index.php'>Go to Website</a> | <a href='staff.php'>Staff Panel</a>";
?>