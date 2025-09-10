// Main JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js')
            .then(registration => console.log('SW registered'))
            .catch(error => console.log('SW registration failed'));
    }
    // Mobile menu toggle
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('nav');
    
    if (hamburger) {
        hamburger.onclick = function() {
            nav.classList.toggle('open');
            hamburger.classList.toggle('active');
        };
    }

    // Login form handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.onsubmit = function(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Login successful!');
                    location.reload();
                } else {
                    alert(data.message || 'Login failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Login failed. Please try again.');
            });
        };
    }

    // Register form handler
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.onsubmit = function(e) {
            e.preventDefault();
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const phone = document.getElementById('registerPhone').value;
            const password = document.getElementById('registerPassword').value;
            
            fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=register&full_name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful! Please sign in.');
                    showSignIn();
                } else {
                    alert(data.message || 'Registration failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Registration failed. Please try again.');
            });
        };
    }
});

// Bill calculator
function calculateBill() {
    const units = document.getElementById('units').value;
    const rate = document.getElementById('tariff').value;
    const result = document.getElementById('result');
    
    if (units && rate) {
        const total = (units * rate).toFixed(2);
        result.innerHTML = `Estimated Bill: ₦${total}`;
    } else {
        result.innerHTML = 'Enter units to calculate your bill';
    }
}

// Smooth scrolling
function scrollTo(target) {
    document.querySelector(target).scrollIntoView({ behavior: 'smooth' });
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'block';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}

// Auth form switching
function showSignUp() {
    document.getElementById('signInForm').style.display = 'none';
    document.getElementById('signUpForm').style.display = 'block';
}

function showSignIn() {
    document.getElementById('signUpForm').style.display = 'none';
    document.getElementById('signInForm').style.display = 'block';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// PWA install prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button
    const installBtn = document.createElement('button');
    installBtn.textContent = '📱 Install App';
    installBtn.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 25px; cursor: pointer; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    installBtn.onclick = () => {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                installBtn.remove();
            }
            deferredPrompt = null;
        });
    };
    document.body.appendChild(installBtn);
});

window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
});