<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Create customers table
$pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    account VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle form submissions
if ($_POST) {
    if ($_POST['action'] == 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, account, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['email'], $_POST['account'], $_POST['status']]);
            $success = "Customer added successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] == 'edit') {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, email = ?, account = ?, status = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['email'], $_POST['account'], $_POST['status'], $_POST['id']]);
            $success = "Customer updated successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] == 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $success = "Customer deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all customers
$customers = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Osaze Energy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: #2563eb; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .status-active { color: #10b981; font-weight: bold; }
        .status-inactive { color: #ef4444; font-weight: bold; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 10px; }
        
        .user-menu { position: relative; display: inline-block; }
        .user-info { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            padding: 8px 15px; 
            background: rgba(255,255,255,0.1); 
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }
        .user-info:hover { background: rgba(255,255,255,0.2); }
        .user-avatar { 
            width: 35px; 
            height: 35px; 
            background: linear-gradient(135deg, #10b981, #059669); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-weight: bold; 
            font-size: 14px;
        }
        .user-details { color: white; }
        .user-name { font-weight: 600; font-size: 14px; }
        .user-role { font-size: 12px; opacity: 0.8; }
        .dropdown-arrow { 
            color: white; 
            margin-left: 5px; 
            transition: transform 0.3s ease;
        }
        .user-menu.active .dropdown-arrow { transform: rotate(180deg); }
        
        .dropdown-menu { 
            position: absolute; 
            top: 100%; 
            right: 0; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
            min-width: 200px; 
            opacity: 0; 
            visibility: hidden; 
            transform: translateY(-10px); 
            transition: all 0.3s ease;
            z-index: 1001;
        }
        .user-menu.active .dropdown-menu { 
            opacity: 1; 
            visibility: visible; 
            transform: translateY(0);
        }
        
        .dropdown-item { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            padding: 12px 15px; 
            color: #374151; 
            text-decoration: none; 
            transition: all 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background: #f9fafb; color: #2563eb; }
        .dropdown-item:first-child { border-radius: 8px 8px 0 0; }
        .dropdown-item:last-child { border-radius: 0 0 8px 8px; }
        
        .dropdown-icon { 
            width: 16px; 
            height: 16px; 
            opacity: 0.7;
        }
        
        .logout-item { color: #ef4444 !important; }
        .logout-item:hover { background: #fef2f2 !important; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ Admin Panel - Osaze Energy</h1>
        <div class="user-menu" onclick="toggleUserMenu()">
            <div class="user-info">
                <div class="user-avatar">A</div>
                <div class="user-details">
                    <div class="user-name"><?= $_SESSION['admin_name'] ?></div>
                    <div class="user-role">Administrator</div>
                </div>
                <span class="dropdown-arrow">▼</span>
            </div>
            <div class="dropdown-menu">
                <a href="#" class="dropdown-item" onclick="showProfile()">
                    <svg class="dropdown-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    Profile Settings
                </a>
                <a href="index.php" class="dropdown-item">
                    <svg class="dropdown-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    Switch to Main Site
                </a>
                <a href="login.php" class="dropdown-item">
                    <svg class="dropdown-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Switch to Customer
                </a>
                <a href="admin_logout.php" class="dropdown-item logout-item">
                    <svg class="dropdown-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    Sign Out
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Customer -->
        <div class="card">
            <h3>Add New Customer</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <input type="text" name="name" placeholder="Customer Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="text" name="account" placeholder="Account Number" required>
                    <select name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-success">Add Customer</button>
                </div>
            </form>
        </div>

        <!-- Customer List -->
        <div class="card">
            <h3>Customer Management (<?= count($customers) ?> customers)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= $customer['id'] ?></td>
                        <td><?= htmlspecialchars($customer['name']) ?></td>
                        <td><?= htmlspecialchars($customer['email']) ?></td>
                        <td><?= htmlspecialchars($customer['account']) ?></td>
                        <td><span class="status-<?= $customer['status'] ?>"><?= ucfirst($customer['status']) ?></span></td>
                        <td><?= date('Y-m-d H:i', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="editCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name']) ?>', '<?= htmlspecialchars($customer['email']) ?>', '<?= htmlspecialchars($customer['account']) ?>', '<?= $customer['status'] ?>')">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this customer?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Customer</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-grid">
                    <input type="text" name="name" id="editName" placeholder="Customer Name" required>
                    <input type="email" name="email" id="editEmail" placeholder="Email Address" required>
                    <input type="text" name="account" id="editAccount" placeholder="Account Number" required>
                    <select name="status" id="editStatus" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editCustomer(id, name, email, account, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editAccount').value = account;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function toggleUserMenu() {
            const userMenu = document.querySelector('.user-menu');
            userMenu.classList.toggle('active');
        }
        
        function showProfile() {
            alert('Profile settings coming soon!');
            toggleUserMenu();
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                userMenu.classList.remove('active');
            }
        });
        
        window.onclick = function(event) {
            if (event.target.id === 'editModal') {
                closeModal();
            }
        }
    </script>
</body>
</html>