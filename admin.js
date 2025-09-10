// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel loaded');
    loadCustomers();
    loadBills();
    
    // Show customers section by default
    showSection('customers');
});

function loadCustomers() {
    console.log('Loading customers...');
    fetch('api.php?action=get_customers')
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Customer data:', data);
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '';
            
            if (data.success && data.customers) {
                data.customers.forEach(customer => {
                    const statusClass = customer.status === 'active' ? 'status-active' : 'status-overdue';
                    const row = `
                        <tr>
                            <td><input type="checkbox" value="${customer.id}"></td>
                            <td>${customer.id}</td>
                            <td>${customer.full_name || customer.name}</td>
                            <td>${customer.email}</td>
                            <td>${customer.account_number || customer.account || 'N/A'}</td>
                            <td><span class="${statusClass}">${customer.status || 'Active'}</span></td>
                            <td>${customer.last_login || customer.created_at || 'Never'}</td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteCustomer(${customer.id})">Delete</button>
                                <button class="btn btn-success" onclick="sendSMS(${customer.id})">SMS</button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6">No customers found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '<tr><td colspan="6">Error loading customers</td></tr>';
        });
}

function loadBills() {
    fetch('api.php?action=get_bills')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('#billTable tbody');
                tbody.innerHTML = '';
                
                data.bills.forEach(bill => {
                    const statusClass = bill.status === 'paid' ? 'status-active' : 
                                       bill.status === 'overdue' ? 'status-overdue' : 'status-pending';
                    
                    const row = `
                        <tr>
                            <td>${bill.id}</td>
                            <td>${bill.full_name}</td>
                            <td>₦${parseFloat(bill.amount).toFixed(2)}</td>
                            <td>${bill.due_date}</td>
                            <td><span class="${statusClass}">${bill.status}</span></td>
                            <td>
                                <button class="btn btn-success" onclick="markPaid(${bill.id})">Mark Paid</button>
                                <button class="btn btn-danger" onclick="sendNotice(${bill.id})">Send Notice</button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(error => console.error('Error loading bills:', error));
}

function markPaid(billId) {
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=mark_bill_paid&bill_id=${billId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bill marked as paid');
            loadBills();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function sendNotice(billId) {
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=send_bill_notice&bill_id=${billId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notice sent successfully');
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Search and Filter Functions
document.getElementById('customerSearch')?.addEventListener('input', filterCustomers);
document.getElementById('statusFilter')?.addEventListener('change', filterCustomers);
document.getElementById('billSearch')?.addEventListener('input', filterBills);
document.getElementById('billStatusFilter')?.addEventListener('change', filterBills);

function filterCustomers() {
    const search = document.getElementById('customerSearch').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#customerTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusMatch = !status || row.textContent.includes(status);
        const searchMatch = !search || text.includes(search);
        row.style.display = searchMatch && statusMatch ? '' : 'none';
    });
}

function filterBills() {
    const search = document.getElementById('billSearch').value.toLowerCase();
    const status = document.getElementById('billStatusFilter').value;
    const rows = document.querySelectorAll('#billTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusMatch = !status || row.textContent.includes(status);
        const searchMatch = !search || text.includes(search);
        row.style.display = searchMatch && statusMatch ? '' : 'none';
    });
}

// Bulk Actions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('#customerTable tbody input[type="checkbox"]');
    const bulkActions = document.getElementById('bulkActions');
    
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    bulkActions.style.display = selectAll.checked ? 'block' : 'none';
}

function exportCustomers() {
    window.open('api.php?action=export_customers', '_blank');
}

function generateReport() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    window.open(`api.php?action=generate_report&date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
}

function toggleAddForm() {
    const form = document.getElementById('addCustomerForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function addCustomer(event) {
    event.preventDefault();
    const name = document.getElementById('newName').value;
    const email = document.getElementById('newEmail').value;
    const account = document.getElementById('newAccount').value;
    
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add_customer_admin&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&account=${encodeURIComponent(account)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer added successfully!');
            document.getElementById('newName').value = '';
            document.getElementById('newEmail').value = '';
            document.getElementById('newAccount').value = '';
            toggleAddForm();
            loadCustomers();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add customer');
    });
}

function deleteCustomer(customerId) {
    if (confirm('Delete this customer? This action cannot be undone.')) {
        fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete_customer_admin&customer_id=${customerId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Customer deleted successfully!');
                loadCustomers();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete customer');
        });
    }
}

// Quick Actions
function quickDisconnect(customerId) {
    if (confirm('Disconnect this customer?')) {
        fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=disconnect_customer&customer_id=${customerId}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            loadCustomers();
        });
    }
}

function sendSMS(customerId) {
    const message = prompt('Enter SMS message:');
    if (message) {
        fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=send_sms&customer_id=${customerId}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => alert(data.message));
    }
}