<?php
// Direct database connection for reliability
$host = 'localhost';
$dbname = 'electricity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Database connection failed']));
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'login':
        if (empty($_POST['email']) || empty($_POST['password'])) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            break;
        }
        
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, password, full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($_POST['password'], $user['password'])) {
                session_start();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                echo json_encode(['success' => true, 'user' => $user['full_name']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Login failed']);
        }
        break;
        
    case 'register':
        $required = ['email', 'password', 'full_name', 'phone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break 2;
            }
        }
        
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            break;
        }
        
        if (strlen($_POST['password']) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            break;
        }
        
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $password, $full_name, $phone]);
            echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed']);
            }
        }
        break;
        
    case 'get_bill':
        session_start();
        $user_id = $_SESSION['user_id'] ?? 0;
        
        $stmt = $pdo->prepare("
            SELECT b.*, a.account_number, t.tariff_name, t.rate_per_kwh 
            FROM bills b 
            JOIN accounts a ON b.account_id = a.id 
            JOIN tariffs t ON b.tariff_id = t.tariff_id 
            WHERE a.user_id = ? 
            ORDER BY b.created_at DESC LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $bill = $stmt->fetch();
        
        echo json_encode($bill ?: ['amount' => 0, 'units_consumed' => 0, 'tariff_name' => 'N/A']);
        break;
        
    case 'service_request':
        session_start();
        $user_id = $_SESSION['user_id'] ?? 0;
        $service_type = $_POST['service_type'];
        $account_number = $_POST['account_number'];
        $address = $_POST['address'];
        $details = $_POST['details'];
        
        $stmt = $pdo->prepare("INSERT INTO service_requests (user_id, service_type, account_number, address, details) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$user_id, $service_type, $account_number, $address, $details])) {
            echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Request failed']);
        }
        break;
        
    case 'get_customers':
        try {
            // Try to get from users/accounts tables first
            $stmt = $pdo->query("
                SELECT u.id, u.full_name as name, u.email, a.account_number as account, a.status, u.created_at 
                FROM users u LEFT JOIN accounts a ON u.id = a.user_id
                WHERE u.role = 'customer' OR u.role IS NULL
            ");
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no customers, try simple customers table
            if (empty($customers)) {
                $stmt = $pdo->query("SELECT id, name, email, account, status, created_at FROM customers ORDER BY created_at DESC");
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode(['success' => true, 'customers' => $customers]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'add_customer_admin':
        try {
            // Create customers table if it doesn't exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                account VARCHAR(50) UNIQUE NOT NULL,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, account, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$_POST['name'], $_POST['email'], $_POST['account']]);
            echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'Email or account number already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'delete_customer_admin':
        try {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$_POST['customer_id']]);
            echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'get_bills':
        $stmt = $pdo->prepare("
            SELECT b.id, u.full_name, b.amount, b.due_date, b.status 
            FROM bills b JOIN accounts a ON b.account_id = a.id 
            JOIN users u ON a.user_id = u.id
        ");
        $stmt->execute();
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'bills' => $bills]);
        break;
        
    case 'mark_bill_paid':
        $bill_id = $_POST['bill_id'];
        $stmt = $pdo->prepare("UPDATE bills SET status = 'paid' WHERE id = ?");
        if ($stmt->execute([$bill_id])) {
            echo json_encode(['success' => true, 'message' => 'Bill marked as paid']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update bill']);
        }
        break;

    case 'send_bill_notice':
        $bill_id = $_POST['bill_id'];
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type) 
            SELECT a.user_id, 'Bill Overdue', 'Your bill is overdue. Please pay to avoid service interruption.', 'bill' 
            FROM bills b JOIN accounts a ON b.account_id = a.id 
            WHERE b.id = ?
        ");
        if ($stmt->execute([$bill_id])) {
            echo json_encode(['success' => true, 'message' => 'Notice sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send notice']);
        }
        break;
        
    case 'get_tariffs':
        $stmt = $pdo->query("SELECT tariff_id, tariff_name, rate_per_kwh FROM tariffs");
        $tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'tariffs' => $tariffs]);
        break;
        
    case 'disconnect_customer':
        $customer_id = $_POST['customer_id'];
        $stmt = $pdo->prepare("UPDATE accounts SET status = 'inactive' WHERE user_id = ?");
        if ($stmt->execute([$customer_id])) {
            echo json_encode(['success' => true, 'message' => 'Customer disconnected']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disconnect']);
        }
        break;
        
    case 'send_sms':
        $customer_id = $_POST['customer_id'];
        $message = $_POST['message'];
        // In real implementation, integrate with SMS service
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$customer_id, 'SMS Notification', $message, 'sms'])) {
            echo json_encode(['success' => true, 'message' => 'SMS sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send SMS']);
        }
        break;
        
    case 'export_customers':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers.csv"');
        
        $stmt = $pdo->query("SELECT u.full_name, u.email, u.phone, a.account_number, a.status 
                            FROM users u LEFT JOIN accounts a ON u.id = a.user_id 
                            WHERE u.role = 'customer'");
        
        echo "Name,Email,Phone,Account Number,Status\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['full_name']},{$row['email']},{$row['phone']},{$row['account_number']},{$row['status']}\n";
        }
        exit;
        
    case 'generate_report':
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bill_report.csv"');
        
        $stmt = $pdo->prepare("SELECT u.full_name, b.amount, b.status, b.created_at 
                              FROM bills b 
                              JOIN accounts a ON b.account_id = a.id 
                              JOIN users u ON a.user_id = u.id 
                              WHERE DATE(b.created_at) BETWEEN ? AND ?");
        $stmt->execute([$date_from, $date_to]);
        
        echo "Customer,Amount,Status,Date\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['full_name']},{$row['amount']},{$row['status']},{$row['created_at']}\n";
        }
        exit;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>