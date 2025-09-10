<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osaze Energy Mobile App</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Osaze Energy">
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgdmlld0JveD0iMCAwIDE5MiAxOTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxOTIiIGhlaWdodD0iMTkyIiByeD0iMjQiIGZpbGw9IiMyNTYzZWIiLz4KPHN2ZyB4PSI0OCIgeT0iNDgiIHdpZHRoPSI5NiIgaGVpZ2h0PSI5NiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Ik0xMSAyMUgzTDEyIDJMMjEgMTFIMTNWMjFIMTFaIi8+Cjwvc3ZnPgo8L3N2Zz4K">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
        }
        .app-header {
            background: #2563eb;
            color: white;
            padding: 1rem;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .app-content {
            padding: 1rem;
            max-width: 400px;
            margin: 0 auto;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .feature-card:active {
            transform: scale(0.95);
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .install-banner {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin: 1rem 0;
            display: none;
        }
        .install-banner.show {
            display: block;
        }
        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
        }
        .download-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin: 2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .download-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }
        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #1f2937;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .download-btn.pwa {
            background: #2563eb;
        }
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 1rem auto;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <div class="app-header">
        <h1>⚡ Osaze Energy Mobile</h1>
        <p>Manage your electricity account on the go</p>
    </div>

    <div class="install-banner" id="installBanner">
        <h3>📱 Install Our App</h3>
        <p>Get the full experience with our mobile app</p>
        <button class="btn" onclick="installApp()">Install Now</button>
    </div>

    <div class="app-content">
        <div class="download-section">
            <h2>📱 Download Osaze Energy App</h2>
            <p>Get instant access to your electricity account, pay bills, and track usage</p>
            
            <div class="qr-code">📱</div>
            <p><small>Scan QR code to download</small></p>
            
            <div class="download-buttons">
                <a href="#" class="download-btn pwa" onclick="installApp()">
                    📱 Install Web App
                </a>
                <a href="osaze-energy.apk" class="download-btn" download>
                    📥 Download APK (Android)
                </a>
                <a href="#" class="download-btn" style="background: #007AFF;">
                    🍎 Coming to App Store
                </a>
                <a href="#" class="download-btn" style="background: #00A1F1;">
                    🪟 Coming to Microsoft Store
                </a>
            </div>
        </div>

        <div class="feature-grid">
            <div class="feature-card" onclick="location.href='login.php'">
                <div class="feature-icon">💳</div>
                <h3>Pay Bills</h3>
                <p>Quick & secure payments</p>
            </div>
            
            <div class="feature-card" onclick="location.href='dashboard.php'">
                <div class="feature-icon">📊</div>
                <h3>Usage Stats</h3>
                <p>Track your consumption</p>
            </div>
            
            <div class="feature-card" onclick="location.href='#'">
                <div class="feature-icon">⚡</div>
                <h3>Outage Map</h3>
                <p>Real-time updates</p>
            </div>
            
            <div class="feature-card" onclick="location.href='#'">
                <div class="feature-icon">🔧</div>
                <h3>Service Request</h3>
                <p>Report issues quickly</p>
            </div>
        </div>

        <div style="text-align: center; margin: 2rem 0;">
            <a href="index.php" style="color: #6b7280; text-decoration: none;">
                ← Back to Website
            </a>
        </div>
    </div>

    <script>
        let deferredPrompt;
        
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
        
        // PWA install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installBanner').classList.add('show');
        });
        
        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('App installed');
                        document.getElementById('installBanner').style.display = 'none';
                    }
                    deferredPrompt = null;
                });
            } else {
                // Fallback for browsers that don't support PWA
                alert('Add this page to your home screen for the best experience!');
            }
        }
        
        // Hide install banner if already installed
        window.addEventListener('appinstalled', () => {
            document.getElementById('installBanner').style.display = 'none';
        });
    </script>
</body>
</html>