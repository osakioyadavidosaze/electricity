// Usage Analytics Charts
function createUsageChart() {
    const ctx = document.getElementById('usageChart');
    if (!ctx) return;
   try {      new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monthly Usage (kWh)',
                    data: [120, 135, 145, 156, 142, 160],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Energy Usage Trend'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'kWh'
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating usage chart:', error);
    }
}

function createCostChart() {
    const ctx = document.getElementById('costChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Cost (₦)',
                data: [1800, 2025, 2175, 2340, 2130, 2400],
                backgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Electricity Costs'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (₦)'
                    }
                }
            }
        }
    });
}

function createPeakUsageChart() {
    const ctx = document.getElementById('peakChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Peak Hours', 'Off-Peak Hours'],
            datasets: [{
                data: [35, 65],
                backgroundColor: ['#ef4444', '#10b981']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Peak vs Off-Peak Usage'
                }
            }
        }
    });
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    createUsageChart();
    createCostChart();
    createPeakUsageChart();
});