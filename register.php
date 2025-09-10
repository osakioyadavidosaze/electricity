<?php
require_once 'config.php';

if ($_POST) {
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([$_POST['email'], $password, $_POST['full_name'], $_POST['phone']]);
        
        $success = "Registration successful! You can now login.";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $error = "Email already exists";
        } else {
            $error = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Osaze Energy</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 50px; }
        .register-container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .logo { text-align: center; margin-bottom: 30px; color: #2563eb; font-size: 24px; font-weight: bold; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1e40af; }
        .error { color: red; text-align: center; margin: 10px 0; }
        .success { color: green; text-align: center; margin: 10px 0; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #2563eb; text-decoration: none; margin: 0 10px; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">⚡ Osaze Energy</div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        
        <div class="links">
            <a href="login.php">Login</a> |
            <a href="index.php">Home</a>
        </div>
    </div>
</body>
</html>