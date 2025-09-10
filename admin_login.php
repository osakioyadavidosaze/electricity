<?php
session_start();
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admin users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_POST) {
    if ($_POST['action'] == 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Default admin check
        if ($email == 'admin@osaze.com' && $password == 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = 'Admin';
            header('Location: admin_panel.php');
            exit;
        }
        
        // Database admin check
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $admin['name'];
                header('Location: admin_panel.php');
                exit;
            }
        } catch (PDOException $e) {}
        
        $error = "Invalid admin credentials";
    }
    
    if ($_POST['action'] == 'register') {
        try {
            $stmt = $pdo->prepare("INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['reg_name'], $_POST['reg_email'], password_hash($_POST['reg_password'], PASSWORD_DEFAULT)]);
            $success = "Admin registered successfully! You can now login.";
        } catch (PDOException $e) {
            $error = strpos($e->getMessage(), 'Duplicate') ? "Email already exists" : "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Osaze Energy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .login-container { 
            max-width: 420px; 
            width: 90%;
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(20px);
            padding: 50px 40px; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.2), 0 0 0 1px rgba(255,255,255,0.1); 
            animation: slideIn 0.8s ease-out;
            position: relative;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            border-radius: 20px 20px 0 0;
            animation: shimmer 2s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .logo { 
            text-align: center; 
            margin-bottom: 40px; 
            color: #2563eb; 
            font-size: 32px; 
            font-weight: 800; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo::before {
            content: '⚡';
            font-size: 40px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .input-group {
            position: relative;
            margin: 25px 0;
        }
        
        .input-group input { 
            width: 100%; 
            padding: 18px 20px 18px 50px; 
            border: 2px solid #e1e5e9; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .input-group input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }
        
        .input-group::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-size: contain;
            opacity: 0.6;
            z-index: 1;
        }
        
        .input-group.email::before {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="%236b7280" viewBox="0 0 24 24"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>');
        }
        
        .input-group.password::before {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="%236b7280" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm-3.75 8.25v-3a3.75 3.75 0 117.5 0v3h-7.5z" clip-rule="evenodd"/></svg>');
        }
        
        button { 
            width: 100%; 
            padding: 18px; 
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        
        button:hover::before {
            left: 100%;
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error { 
            color: #ef4444; 
            text-align: center; 
            margin: 20px 0; 
            padding: 15px; 
            background: rgba(239, 68, 68, 0.1); 
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            animation: slideIn 0.3s ease-out;
        }
        
        .back-link { 
            text-align: center; 
            margin-top: 30px; 
        }
        
        .back-link a { 
            color: #6b7280; 
            text-decoration: none; 
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link a:hover {
            color: #2563eb;
            transform: translateX(-3px);
        }
        
        .demo-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            color: #1e40af;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">Admin Portal</div>
        
        <div class="demo-info">
            <strong>Demo Credentials:</strong><br>
            Email: admin@osaze.com | Password: admin123
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div style="color: #10b981; text-align: center; margin: 20px 0; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px;"><?= $success ?></div>
        <?php endif; ?>
        
        <div id="loginForm">
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="input-group email">
                    <input type="email" name="email" placeholder="Admin Email" value="admin@osaze.com" required>
                </div>
                <div class="input-group password">
                    <input type="password" name="password" placeholder="Admin Password" value="admin123" required>
                </div>
                <button type="submit">Access Admin Panel</button>
            </form>
        </div>
        
        <div id="registerForm" style="display: none;">
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="input-group">
                    <input type="text" name="reg_name" placeholder="Full Name" required>
                </div>
                <div class="input-group email">
                    <input type="email" name="reg_email" placeholder="Admin Email" required>
                </div>
                <div class="input-group password">
                    <input type="password" name="reg_password" placeholder="Password" required>
                </div>
                <button type="submit">Create Admin Account</button>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="#" onclick="toggleForm()" style="color: #6b7280; text-decoration: none;">
                <span id="toggleText">Need an admin account? Register here</span>
            </a>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Back to Main Site</a>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline';
            btn.disabled = true;
        });
        
        function toggleForm() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const toggleText = document.getElementById('toggleText');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                toggleText.textContent = 'Need an admin account? Register here';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                toggleText.textContent = 'Already have an account? Login here';
            }
        }
    </script>
</body>
</html>