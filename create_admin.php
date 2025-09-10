<?php
require_once 'config.php';

// Create admin and staff users
try {
    // Admin user
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin@osaze.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin User', 'admin']);
    
    // Staff user
    $stmt->execute(['staff@osaze.com', password_hash('staff123', PASSWORD_DEFAULT), 'Staff User', 'staff']);
    
    echo "<h2>✅ Admin accounts created!</h2>";
    echo "<p><strong>Admin Login:</strong> admin@osaze.com / admin123</p>";
    echo "<p><strong>Staff Login:</strong> staff@osaze.com / staff123</p>";
    echo "<br><a href='login.php'>Go to Login</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>