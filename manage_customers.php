<?php
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    <title>Customer Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .form-section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; }
        input, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a8b; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Management System</h1>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Customer Form -->
        <div class="form-section">
            <h3>Add New Customer</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <label>Name:</label>
                <input type="text" name="name" required>
                
                <label>Email:</label>
                <input type="email" name="email" required>
                
                <label>Account Number:</label>
                <input type="text" name="account" required>
                
                <label>Status:</label>
                <select name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                
                <button type="submit">Add Customer</button>
            </form>
        </div>

        <!-- Customer List -->
        <div class="form-section">
            <h3>Customer List (<?= count($customers) ?> customers)</h3>
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
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this customer?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>