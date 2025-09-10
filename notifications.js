// Push Notifications Service
class NotificationService {
    constructor() {
        this.init();
    }

    async init() {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            await this.registerServiceWorker();
            await this.requestPermission();
        }
    }

    async registerServiceWorker() {
        const registration = await navigator.serviceWorker.register('/sw.js');
        console.log('Service Worker registered');
        return registration;
    }

    async requestPermission() {
        const permission = await Notification.requestPermission();
        return permission === 'granted';
    }

    showNotification(title, options = {}) {
        if (Notification.permission === 'granted') {
            new Notification(title, {
                icon: '/icon-192.png',
                badge: '/badge-72.png',
                ...options
            });
        }
    }

    // Simulate real-time notifications
    startRealTimeUpdates() {
        setInterval(() => {
            this.checkForUpdates();
        }, 30000); // Check every 30 seconds
    }

    async checkForUpdates() {
        try {
            const response = await fetch('/api.php?action=get_notifications');
            const notifications = await response.json();
            
            notifications.forEach(notification => {
                if (!notification.is_read) {
                    this.showNotification(notification.title, {
                        body: notification.message,
                        tag: notification.type
                    });
                }
            });
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    }
}

// Initialize notifications
const notificationService = new NotificationService();
notificationService.startRealTimeUpdates();

// Bill reminder notifications
function checkBillDue() {
    const billDueDate = new Date('2024-12-25');
    const today = new Date();
    const daysUntilDue = Math.ceil((billDueDate - today) / (1000 * 60 * 60 * 24));
    
    if (daysUntilDue <= 3 && daysUntilDue > 0) {
        notificationService.showNotification('Bill Due Soon', {
            body: `Your electricity bill is due in ${daysUntilDue} days. Pay now to avoid late fees.`,
            tag: 'bill-reminder'
        });
    }
}

// Usage alert notifications
function checkUsageThreshold() {
    const currentUsage = 2.4; // kW
    const threshold = 3.0; // kW
    
    if (currentUsage > threshold) {
        notificationService.showNotification('High Usage Alert', {
            body: `Current usage (${currentUsage}kW) exceeds your threshold (${threshold}kW)`,
            tag: 'usage-alert'
        });
    }
}

// Check notifications on page load
document.addEventListener('DOMContentLoaded', () => {
    checkBillDue();
    checkUsageThreshold();
});