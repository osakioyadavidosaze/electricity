<?php
// Database configuration
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'electricity';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed");
}

function getTariffRates($pdo) {
    static $rates = null;
    
    if ($rates === null) {
        try {
            $stmt = $pdo->query("SELECT account_type, rate_per_kwh FROM tariffs");
            $rates = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rates[$row['account_type']] = $row['rate_per_kwh'];
            }
        } catch(PDOException $e) {
            throw new Exception("Failed to fetch tariff rates: " . $e->getMessage());
        }
    }
    
    return $rates;
}
?>