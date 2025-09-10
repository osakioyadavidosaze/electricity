<?php
require_once 'config.php';

// Handle form submissions
if ($_POST) {
    if ($_POST['action'] == 'delete_customer') {
        try {
            $stmt = $pdo->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->execute([$_POST['user_id']]);
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_POST['user_id']]);
            
            echo "<div style='color: green; padding: 10px; background: #f0f8ff;'>Customer deleted successfully!</div>";
        } catch (PDOException $e) {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6;'>Error: " . $e->getMessage() . "</div>";
        }
    }
    
    if ($_POST['action'] == 'edit_customer') {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['user_id']]);
            
            $new_account = trim($_POST['account_number']);
            // Check if new account number already exists
            $check = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ? AND user_id != ?");
            $check->execute([$new_account, $_POST['user_id']]);
            if ($check->fetch()) {
                throw new Exception("Account number already exists");
            }
            
            $stmt = $pdo->prepare("UPDATE accounts SET account_number = ? WHERE user_id = ?");
            $stmt->execute([$new_account, $_POST['user_id']]);
            
            echo "<div style='color: green; padding: 10px; background: #f0f8ff;'>Customer updated successfully!</div>";
        } catch (Exception $e) {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6;'>Error: " . $e->getMessage() . "</div>";
        } catch (PDOException $e) {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6;'>Error: " . $e->getMessage() . "</div>";
        }
    }
    
    if ($_POST['action'] == 'add_customer') {
        try {
            // Add user
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([$_POST['email'], $password, $_POST['full_name'], $_POST['phone']]);
            $user_id = $pdo->lastInsertId();
            
            // Add account
            $account_number = trim($_POST['account_number']);
            
            // Check if account number already exists
            $check = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ?");
            $check->execute([$account_number]);
            if ($check->fetch()) {
                throw new Exception("Account number already exists");
            }
            
            $stmt = $pdo->prepare("INSERT INTO accounts (user_id, account_number) VALUES (?, ?)");
            $stmt->execute([$user_id, $account_number]);
            
            echo "<div style='color: green; padding: 10px; background: #f0f8ff;'>Customer added! Account: $account_number</div>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<div style='color: red; padding: 10px; background: #ffe6e6;'>Error: Email already exists!</div>";
            } else {
                echo "<div style='color: red; padding: 10px; background: #ffe6e6;'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Get all users
try {
    $customers = $pdo->query("SELECT u.id, u.full_name, u.email, u.phone, u.created_at, 
                             a.account_number 
                             FROM users u 
                             LEFT JOIN accounts a ON u.id = a.user_id 
                             ORDER BY u.created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $customers = [];
    echo "<div style='color: red;'>Database error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Staff Panel - Osaze Energy</h1>
    
    <div class="form-section">
        <h3>Add New Customer</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_customer">
            
            <label>Full Name:</label>
            <input type="text" name="full_name" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Phone:</label>
            <input type="tel" name="phone" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <label>Account Number:</label>
            <input type="text" name="account_number" placeholder="Enter custom account number" required>
            
            <button type="submit">Add Customer</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Customer List (<?= count($customers) ?> customers)</h3>
        <table>
            <tr>
                <th>Account Number</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= $customer['account_number'] ?? 'N/A' ?></td>
                <td><?= htmlspecialchars($customer['full_name']) ?></td>
                <td><?= htmlspecialchars($customer['email']) ?></td>
                <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
                <td><?= date('Y-m-d', strtotime($customer['created_at'])) ?></td>
                <td>
                    <button onclick="editCustomer(<?= $customer['id'] ?>)" style="background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; margin-right: 5px;">Edit</button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this customer?')">
                        <input type="hidden" name="action" value="delete_customer">
                        <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
                        <button type="submit" style="background: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 500px; border-radius: 8px;">
            <h3>Edit Customer</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit_customer">
                <input type="hidden" name="user_id" id="editUserId">
                
                <label>Full Name:</label>
                <input type="text" name="full_name" id="editName" required>
                
                <label>Email:</label>
                <input type="email" name="email" id="editEmail" required>
                
                <label>Phone:</label>
                <input type="tel" name="phone" id="editPhone" required>
                
                <label>Account Number:</label>
                <input type="text" name="account_number" id="editAccount" required>
                
                <button type="submit" style="background: #28a745; margin-right: 10px;">Update</button>
                <button type="button" onclick="closeEdit()" style="background: #6c757d;">Cancel</button>
            </form>
        </div>
    </div>
    
    <p><a href="index.php">← Back to Main Site</a></p>
    
    <script>
        const customers = <?= json_encode($customers) ?>;
        
        function editCustomer(userId) {
            const customer = customers.find(c => c.id == userId);
            if (customer) {
                document.getElementById('editUserId').value = customer.id;
                document.getElementById('editName').value = customer.full_name;
                document.getElementById('editEmail').value = customer.email;
                document.getElementById('editPhone').value = customer.phone || '';
                document.getElementById('editAccount').value = customer.account_number || '';
                document.getElementById('editModal').style.display = 'block';
            }
        }
        
        function closeEdit() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.id === 'editModal') {
                closeEdit();
            }
        }
    </script>
</body>
</html>