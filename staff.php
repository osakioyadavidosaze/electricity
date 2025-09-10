<?php
session_start();
require_once 'config.php';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'edit_customer') {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['user_id']]);
            
            if (!empty($_POST['address']) || !empty($_POST['account_type'])) {
                $stmt = $pdo->prepare("UPDATE accounts SET address = ?, account_type = ? WHERE user_id = ?");
                $stmt->execute([$_POST['address'], $_POST['account_type'], $_POST['user_id']]);
            }
            
            $success = "Customer updated successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($action == 'delete_customer') {
        try {
            $stmt = $pdo->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->execute([$_POST['user_id']]);
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_POST['user_id']]);
            
            $success = "Customer deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($action == 'add_customer') {
        try {
            // Add user
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([$_POST['email'], $password, $_POST['full_name'], $_POST['phone']]);
            $user_id = $pdo->lastInsertId();
            
            // Add account
            $account_number = 'AC' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("INSERT INTO accounts (user_id, account_number, account_type, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $account_number, $_POST['account_type'], $_POST['address']]);
            
            $success = "Customer added successfully! Account: $account_number";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($action == 'add_bill') {
        try {
            $stmt = $pdo->prepare("INSERT INTO bills (account_id, amount, units_consumed, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['account_id'], $_POST['amount'], $_POST['units'], $_POST['due_date']]);
            $success = "Bill added successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get customers
$customers = $pdo->query("SELECT u.*, a.account_number, a.account_type, a.address, a.status 
                         FROM users u 
                         LEFT JOIN accounts a ON u.id = a.user_id 
                         WHERE u.role = 'customer' 
                         ORDER BY u.created_at DESC")->fetchAll();

// Get accounts for bill dropdown
$accounts = $pdo->query("SELECT a.id, a.account_number, u.full_name 
                        FROM accounts a 
                        JOIN users u ON a.user_id = u.id")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Panel - Osaze Energy</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .form-group { margin: 10px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #1e40af; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .nav { background: #2563eb; color: white; padding: 15px; margin: -20px -20px 20px -20px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <h2>Staff Panel - Osaze Energy</h2>
            <a href="index.php">Main Site</a>
            <a href="admin.html">Admin Panel</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Customer -->
        <div class="section">
            <h3>Add New Customer</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_customer">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div class="form-group">
                            <label>Full Name:</label>
                            <input type="text" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone:</label>
                            <input type="tel" name="phone" required>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Account Type:</label>
                            <select name="account_type" required>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="industrial">Industrial</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Address:</label>
                            <textarea name="address" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn">Add Customer</button>
            </form>
        </div>

        <!-- Add Bill -->
        <div class="section">
            <h3>Generate Bill</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_bill">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Account:</label>
                        <select name="account_id" required>
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>">
                                    <?= $account['account_number'] ?> - <?= $account['full_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Units Consumed (kWh):</label>
                        <input type="number" name="units" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Amount (₦):</label>
                        <input type="number" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Due Date:</label>
                        <input type="date" name="due_date" required>
                    </div>
                </div>
                <button type="submit" class="btn">Generate Bill</button>
            </form>
        </div>

        <!-- Customer List -->
        <div class="section">
            <h3>Customer List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= $customer['account_number'] ?? 'N/A' ?></td>
                        <td><?= htmlspecialchars($customer['full_name']) ?></td>
                        <td><?= htmlspecialchars($customer['email']) ?></td>
                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                        <td><?= ucfirst($customer['account_type'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($customer['address'] ?? 'N/A') ?></td>
                        <td><?= ucfirst($customer['status'] ?? 'N/A') ?></td>
                        <td><?= date('Y-m-d', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <button onclick="editCustomer(<?= $customer['id'] ?>)" class="btn" style="background: #10b981; margin: 2px;">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this customer?')">
                                <input type="hidden" name="action" value="delete_customer">
                                <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
                                <button type="submit" class="btn" style="background: #ef4444; margin: 2px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Edit Customer Modal -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="background: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 600px; border-radius: 8px;">
                <h3>Edit Customer</h3>
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="edit_customer">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <div class="form-group">
                                <label>Full Name:</label>
                                <input type="text" name="full_name" id="editName" required>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" name="email" id="editEmail" required>
                            </div>
                            <div class="form-group">
                                <label>Phone:</label>
                                <input type="tel" name="phone" id="editPhone" required>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Account Type:</label>
                                <select name="account_type" id="editType">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Address:</label>
                                <textarea name="address" id="editAddress" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn">Update Customer</button>
                    <button type="button" onclick="closeEdit()" class="btn" style="background: #6b7280; margin-left: 10px;">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        const customers = <?= json_encode($customers) ?>;
        
        function editCustomer(userId) {
            const customer = customers.find(c => c.id == userId);
            if (customer) {
                document.getElementById('editUserId').value = customer.id;
                document.getElementById('editName').value = customer.full_name;
                document.getElementById('editEmail').value = customer.email;
                document.getElementById('editPhone').value = customer.phone || '';
                document.getElementById('editType').value = customer.account_type || 'residential';
                document.getElementById('editAddress').value = customer.address || '';
                document.getElementById('editModal').style.display = 'block';
            }
        }
        
        function closeEdit() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>