<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osaze Energy - Reliable Power Solutions</title>
    <link rel="icon" href="https://img.icons8.com/ios-filled/50/004aad/flash-on.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
</head>
<body>
    <div class="topbar">
        ⚡ Reliable Power Solutions - 24/7 Customer Support
    </div>
    
    <header>
        <div class="logo">
            <img src="https://img.icons8.com/ios-filled/50/004aad/flash-on.png" alt="Osaze Energy">
            <span>Osaze Energy</span>
        </div>
        <nav id="nav">
            <a href="#home">Home</a>
            <a href="#services">Services</a>
            <a href="#tariffs">Tariffs</a>
            <a href="#calculator">Calculator</a>
            <a href="mobile/">📱 Mobile App</a>
            <a href="#contact">Contact</a>
            <?php if ($isLoggedIn): ?>
                <a href="#dashboard">Dashboard</a>
                <a href="logout.php">Logout (<?= htmlspecialchars($userName) ?>)</a>
            <?php else: ?>
                <a href="#" onclick="openModal('loginModal')">Sign In</a>
                <a href="admin_login.php">Admin</a>
            <?php endif; ?>
        </nav>
        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section" id="home">
            <div class="hero-text">
                <h1>Powering Your Future</h1>
                <p>Reliable, affordable electricity solutions for homes and businesses. Join thousands of satisfied customers.</p>
                <div class="hero-actions">
                    <button onclick="scrollTo('#calculator')">Calculate Bill</button>
                    <button class="secondary" onclick="location.href='mobile/'">📱 Get Mobile App</button>
                </div>
            </div>
            <div class="hero-img">
                <img src="https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=400" alt="Energy Solutions">
            </div>
        </section>

        <!-- Features -->
        <section class="features" id="services">
            <div class="feature-card">
                <div class="icon">⚡</div>
                <h4>24/7 Power Supply</h4>
                <p>Uninterrupted electricity with backup systems</p>
            </div>
            <div class="feature-card">
                <div class="icon">💰</div>
                <h4>Competitive Rates</h4>
                <p>Best prices in the market with flexible plans</p>
            </div>
            <div class="feature-card">
                <div class="icon">📱</div>
                <h4>Smart Monitoring</h4>
                <p>Track usage and manage bills online</p>
            </div>
        </section>

        <!-- Calculator -->
        <section class="calculator" id="calculator">
            <h2>Bill Calculator</h2>
            <div class="calc-input">
                <label>Units Used (kWh):</label>
                <input type="number" id="units" placeholder="Enter units">
                <label>Tariff Plan:</label>
                <select id="tariff">
                    <option value="15">Residential (₦15/kWh)</option>
                    <option value="20">Commercial (₦20/kWh)</option>
                    <option value="25">Industrial (₦25/kWh)</option>
                </select>
                <button onclick="calculateBill()" class="btn">Calculate</button>
            </div>
            <div class="calc-result" id="result">Enter units to calculate your bill</div>
        </section>

        <!-- Tariffs -->
        <section class="tariff-section" id="tariffs">
            <h2>Our Tariff Plans</h2>
            <div class="tariff-plans">
                <div class="tariff-card">
                    <h3>Residential</h3>
                    <p class="price">₦15/kWh</p>
                    <p>Perfect for homes and apartments</p>
                </div>
                <div class="tariff-card popular">
                    <h3>Commercial</h3>
                    <p class="price">₦20/kWh</p>
                    <p>Ideal for small businesses</p>
                </div>
                <div class="tariff-card">
                    <h3>Industrial</h3>
                    <p class="price">₦25/kWh</p>
                    <p>For large-scale operations</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Auth Modal -->
    <?php if (!$isLoggedIn): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <div id="signInForm">
                <h2>Sign In</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <input type="email" id="loginEmail" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="loginPassword" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn">Sign In</button>
                </form>
                <div class="auth-toggle">
                    Don't have an account? <a onclick="showSignUp()">Sign Up</a>
                </div>
            </div>
            <div id="signUpForm" style="display: none;">
                <h2>Sign Up</h2>
                <form id="registerForm">
                    <div class="form-group">
                        <input type="text" id="registerName" placeholder="Full Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="registerEmail" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="registerPhone" placeholder="Phone Number" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="registerPassword" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn">Sign Up</button>
                </form>
                <div class="auth-toggle">
                    Already have an account? <a onclick="showSignIn()">Sign In</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Contact Us</h4>
                <p>📞 +234-800-OSAZE-1</p>
                <p>✉️ info@osaze-energy.com</p>
                <p>📍 Lagos, Nigeria</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="#home">Home</a>
                <a href="#services">Services</a>
                <a href="#tariffs">Tariffs</a>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <a href="admin.html">Admin Panel</a>
                <a href="#contact">Contact Support</a>
            </div>
        </div>
        <p style="margin-top: 2rem; border-top: 1px solid #374151; padding-top: 1rem;">
            © 2024 Osaze Energy. All rights reserved.
        </p>
    </footer>

    <script src="app.js"></script>
</body>
</html>