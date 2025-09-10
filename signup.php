<?php
require_once 'config.php';

if ($_POST) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $password, $full_name, $phone]);
        echo "<div style='color: green;'>Registration successful!</div>";
    } catch (PDOException $e) {
        echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Osaze Energy</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #1e40af; }
    </style>
</head>
<body>
    <h2>Sign Up</h2>
    <form method="POST">
        <div class="form-group">
            <input type="text" name="full_name" placeholder="Full Name" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="tel" name="phone" placeholder="Phone Number" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Sign Up</button>
    </form>
    <p><a href="doggy.html">Back to Home</a></p>
</body>
</html>