<?php
// Test backend connection
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connection: SUCCESS</h2>";
    
    // Create customers table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        account VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "<p>✅ Customers table ready</p>";
    
    // Test API endpoint
    echo "<h3>Testing API:</h3>";
    echo "<script>
        fetch('api.php?action=get_customers')
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            document.getElementById('api-result').innerHTML = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            document.getElementById('api-result').innerHTML = 'API Error: ' + error;
        });
    </script>";
    echo "<pre id='api-result'>Loading...</pre>";
    
    echo "<br><a href='admin.html'>Go to Admin Panel</a>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection: FAILED</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP MySQL is running</p>";
}
?>