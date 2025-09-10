-- Osaze Energy Database Schema

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Accounts table (electricity accounts)
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    meter_number VARCHAR(50) UNIQUE NOT NULL,
    tariff_plan ENUM('residential', 'commercial', 'industrial') DEFAULT 'residential',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tariffs table
CREATE TABLE tariffs (
    tariff_id INT PRIMARY KEY AUTO_INCREMENT,
    tariff_name VARCHAR(100) NOT NULL,
    rate_per_kwh DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bills table
CREATE TABLE bills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT,
    tariff_id INT,
    billing_period VARCHAR(20),
    units_consumed DECIMAL(10,2),
    amount DECIMAL(10,2),
    due_date DATE,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (tariff_id) REFERENCES tariffs(tariff_id)
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bill_id INT,
    amount DECIMAL(10,2),
    payment_method ENUM('card', 'bank', 'wallet'),
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id)
);

-- Service requests table
CREATE TABLE service_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    service_type ENUM('new', 'transfer', 'meter', 'maintenance'),
    account_number VARCHAR(50),
    address TEXT,
    details TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Usage data table
CREATE TABLE usage_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT,
    reading_date DATE,
    units_consumed DECIMAL(10,2),
    peak_usage DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);

-- Sample data
INSERT INTO users (email, password, full_name, phone, address) VALUES
('john@example.com', 'hashed_password', 'John Doe', '+234-800-1234', 'Lagos, Nigeria'),
('sarah@example.com', 'hashed_password', 'Sarah Johnson', '+234-800-5678', 'Abuja, Nigeria');

INSERT INTO accounts (user_id, account_number, meter_number, tariff_plan) VALUES
(1, 'ACC001', 'MTR001', 'residential'),
(2, 'ACC002', 'MTR002', 'commercial');

-- Outages table
CREATE TABLE outages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area VARCHAR(255) NOT NULL,
    affected_customers INT DEFAULT 0,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_end TIMESTAMP,
    status ENUM('active', 'resolved') DEFAULT 'active',
    description TEXT
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255),
    message TEXT,
    type ENUM('bill', 'outage', 'maintenance', 'general') DEFAULT 'general',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Analytics table
CREATE TABLE analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE,
    total_customers INT,
    total_revenue DECIMAL(15,2),
    total_consumption DECIMAL(15,2),
    active_outages INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tariffs (tariff_name, rate_per_kwh, description) VALUES
('Residential', 15.00, 'Standard residential rate'),
('Commercial', 20.00, 'Small business rate'),
('Industrial', 25.00, 'Large scale operations');

INSERT INTO bills (account_id, tariff_id, billing_period, units_consumed, amount, due_date) VALUES
(1, 1, '2024-12', 156.50, 2450.00, '2024-12-25'),
(2, 2, '2024-12', 320.75, 6415.00, '2024-12-25');

INSERT INTO outages (area, affected_customers, estimated_end, description) VALUES
('Victoria Island', 245, '2024-12-15 14:30:00', 'Transformer maintenance'),
('Lekki Phase 1', 180, '2024-12-15 16:00:00', 'Cable fault repair');

INSERT INTO analytics (date, total_customers, total_revenue, total_consumption, active_outages) VALUES
('2024-12-15', 1247, 2400000.00, 15600.50, 2);