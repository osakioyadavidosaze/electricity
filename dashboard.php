<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

// Get user account info
$stmt = $pdo->prepare("SELECT a.account_number FROM accounts a WHERE a.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Osaze Energy</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #2563eb; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #1e40af; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Osaze Energy Dashboard</h1>
        <div>
            Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> | 
            <a href="logout.php" style="color: white;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Account Information</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            <p><strong>Account Number:</strong> <?= $account['account_number'] ?? 'Not assigned' ?></p>
            <p><strong>Status:</strong> Active</p>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <a href="#" class="btn">View Bills</a>
            <a href="#" class="btn">Make Payment</a>
            <a href="#" class="btn">Service Request</a>
        </div>
        
        <div class="card">
            <h2>Recent Activity</h2>
            <p>No recent activity</p>
        </div>
    </div>
</body>
</html>