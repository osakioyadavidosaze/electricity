<?php
// Vercel-compatible database configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'electricity';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

// For Vercel, use SQLite as fallback
if (isset($_ENV['VERCEL'])) {
    try {
        $pdo = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables for SQLite
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            account TEXT UNIQUE NOT NULL,
            password TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE email = 'admin@osaze.com'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute(['Admin', 'admin@osaze.com', password_hash('admin123', PASSWORD_DEFAULT)]);
        }
        
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    // Local MySQL connection
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>