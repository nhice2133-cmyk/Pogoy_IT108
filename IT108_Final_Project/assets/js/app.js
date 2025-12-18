// API Base URL
const API_BASE = 'api/';

// Current user data
let currentUser = null;
let monthlyChart = null;

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupEventListeners();
});

// Check authentication status
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}auth.php?action=check`);
        const data = await response.json();

        if (data.logged_in) {
            // Redirect to dashboard if already logged in
            window.location.href = 'dashboard.php';
        } else {
            showAuthView();
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        showAuthView();
    }
}

// Show authentication view
function showAuthView() {
    document.getElementById('auth-view').style.display = 'flex';
    document.getElementById('main-view').style.display = 'none';
}

// Show main application view
function showMainView() {
    console.log('Showing main view...');
    const authView = document.getElementById('auth-view');
    const mainView = document.getElementById('main-view');

    if (authView) authView.style.display = 'none';
    if (mainView) {
        mainView.style.display = 'block';
        console.log('Main view displayed');
    }

    if (currentUser) {
        const userInfoEl = document.getElementById('user-info');
        if (userInfoEl) {
            userInfoEl.textContent = currentUser.full_name || currentUser.username;
        }
    }

    // Show dashboard by default
    setTimeout(() => {
        showView('dashboard');
    }, 100);
}


// Handle login - make it globally accessible
window.handleLogin = async function (event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    if (!username || !password) {
        showMessage('Please enter both username and password', 'error');
        return;
    }

    // Show loading state
    const form = event ? event.target : document.getElementById('login-form');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const originalText = submitBtn ? submitBtn.textContent : 'Login';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
    }

    try {
        const response = await fetch(`${API_BASE}auth.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();
        console.log('Login response:', data); // Debug log

        // Handle 401 and other errors
        if (!response.ok) {
            const errorMsg = data.error || data.message || `HTTP error! status: ${response.status}`;
            console.error('Login failed:', errorMsg);
            showMessage(errorMsg, 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
            return;
        }

        if (data && data.success === true) {
            currentUser = data.user;
            console.log('Login successful, redirecting to dashboard...');
            // Force redirect - try multiple methods
            if (window.location) {
                window.location.replace('dashboard.php');
            } else {
                window.location.href = 'dashboard.php';
            }
            return; // Exit function
        } else {
            const errorMsg = data.error || data.message || 'Login failed';
            console.error('Login failed:', errorMsg);
            showMessage(errorMsg, 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('Connection error. Please try again.', 'error');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
};


// Handle logout - make it globally accessible
window.handleLogout = async function () {
    try {
        // Call logout API to destroy session
        await fetch(`${API_BASE}auth.php?action=logout`);
        currentUser = null;
        // Redirect to index.php
        window.location.href = 'index.php';
    } catch (error) {
        console.error('Logout error:', error);
        // Still redirect even if there's an error
        window.location.href = 'index.php';
    }
};

// Show message - make it globally accessible
window.showMessage = function (message, type) {
    const msgEl = document.getElementById('auth-message');
    if (msgEl) {
        msgEl.textContent = message;
        msgEl.className = `message ${type}`;
        setTimeout(() => {
            msgEl.className = 'message';
        }, 5000);
    } else {
        console.error('Message element not found');
        // Fallback to alert
        alert(message);
    }
};

// View switching
function showView(viewName, clickedElement) {
    // Hide all views
    document.querySelectorAll('.view').forEach(view => view.style.display = 'none');

    // Remove active from all nav links
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

    // Show the selected view
    const targetView = document.getElementById(`${viewName}-view`);
    if (targetView) {
        targetView.style.display = 'block';
    } else {
        console.error(`View not found: ${viewName}-view`);
    }

    // Activate the clicked nav link (if provided)
    if (clickedElement) {
        clickedElement.classList.add('active');
    } else {
        // Find and activate the nav link for this view
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(`'${viewName}'`)) {
                link.classList.add('active');
            }
        });
    }

    // Load view-specific data
    switch (viewName) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'catch':
            loadCatchForm();
            break;
        case 'vessels':
            loadVessels();
            break;
        case 'records':
            loadRecords();
            break;
    }
}

// Load dashboard
async function loadDashboard() {
    try {
        // Set default values while loading
        const statElements = {
            'stat-total-catches': '0',
            'stat-total-quantity': '0 kg',
            'stat-total-value': '₱0.00',
            'stat-fishing-days': '0'
        };

        Object.keys(statElements).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = statElements[id];
        });

        const response = await fetch(`${API_BASE}catch.php?action=stats`);
        const data = await response.json();

        if (data.success) {
            const stats = data.stats || {};

            // Update stat cards safely
            const totalCatchesEl = document.getElementById('stat-total-catches');
            const totalQuantityEl = document.getElementById('stat-total-quantity');
            const totalValueEl = document.getElementById('stat-total-value');
            const fishingDaysEl = document.getElementById('stat-fishing-days');

            if (totalCatchesEl) totalCatchesEl.textContent = stats.total_catches || 0;
            if (totalQuantityEl) totalQuantityEl.textContent = (parseFloat(stats.total_quantity) || 0).toFixed(2) + ' kg';
            if (totalValueEl) totalValueEl.textContent = '₱' + (parseFloat(stats.total_value) || 0).toFixed(2);
            if (fishingDaysEl) fishingDaysEl.textContent = stats.fishing_days || 0;

            // Load monthly chart
            if (data.monthly && data.monthly.length > 0) {
                loadMonthlyChart(data.monthly);
            } else {
                // Initialize empty chart if no data
                const ctx = document.getElementById('monthly-chart');
                if (ctx) {
                    if (monthlyChart) {
                        monthlyChart.destroy();
                    }
                    monthlyChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: []
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true
                        }
                    });
                }
            }

            // Load recent catches
            loadRecentCatches();
        } else {
            console.error('Failed to load dashboard stats:', data.error);
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
    }
}

// Load monthly chart
function loadMonthlyChart(monthlyData) {
    const ctx = document.getElementById('monthly-chart');
    if (!ctx) return;

    const labels = monthlyData.map(d => d.month).reverse();
    const quantities = monthlyData.map(d => parseFloat(d.quantity || 0)).reverse();
    const values = monthlyData.map(d => parseFloat(d.value || 0)).reverse();

    if (monthlyChart) {
        monthlyChart.destroy();
    }

    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantity (kg)',
                data: quantities,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4
            }, {
                label: 'Value (PHP)',
                data: values,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Quantity (kg)' }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    title: { display: true, text: 'Value (PHP)' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
}

// Load recent catches
async function loadRecentCatches() {
    try {
        const response = await fetch(`${API_BASE}catch.php?action=list`);
        const data = await response.json();

        if (data.success && data.data) {
            const recent = data.data.slice(0, 5);
            const listEl = document.getElementById('recent-catches-list');

            if (recent.length === 0) {
                listEl.innerHTML = '<p style="color: var(--text-secondary);">No catches recorded yet.</p>';
                return;
            }

            listEl.innerHTML = recent.map(catchRecord => `
                <div class="card" style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>${catchRecord.species}</strong> - ${catchRecord.quantity} ${catchRecord.unit}
                            <br><small style="color: var(--text-secondary);">${catchRecord.catch_date} ${catchRecord.catch_time || ''}</small>
                        </div>
                        <span class="badge badge-${catchRecord.status === 'verified' ? 'success' : 'warning'}">${catchRecord.status}</span>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load recent catches:', error);
    }
}

// Load catch form
async function loadCatchForm() {
    // Set today's date as default
    document.getElementById('catch-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('catch-time').value = new Date().toTimeString().slice(0, 5);

    // Load vessels
    try {
        const response = await fetch(`${API_BASE}vessels.php?action=list`);
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('catch-vessel');
            select.innerHTML = '<option value="">Select vessel</option>';
            data.data.forEach(vessel => {
                select.innerHTML += `<option value="${vessel.id}">${vessel.vessel_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load vessels:', error);
    }

    // Load gear
    try {
        const response = await fetch(`${API_BASE}gear.php?action=list`);
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('catch-gear');
            select.innerHTML = '<option value="">Select gear</option>';
            data.data.forEach(gear => {
                select.innerHTML += `<option value="${gear.id}">${gear.gear_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load gear:', error);
    }

    // Load zones
    try {
        const response = await fetch(`${API_BASE}zones.php?action=list`);
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('catch-zone');
            select.innerHTML = '<option value="">Select zone</option>';
            data.data.forEach(zone => {
                select.innerHTML += `<option value="${zone.id}">${zone.zone_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load zones:', error);
    }

    // Load species
    try {
        const response = await fetch(`${API_BASE}species.php?action=list`);
        const data = await response.json();
        if (data.success) {
            const datalist = document.getElementById('species-list');
            datalist.innerHTML = '';
            data.data.forEach(species => {
                datalist.innerHTML += `<option value="${species.common_name}">${species.local_name || species.common_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load species:', error);
    }
}

// Handle catch submit
async function handleCatchSubmit(event) {
    event.preventDefault();

    const formData = {
        catch_date: document.getElementById('catch-date').value,
        catch_time: document.getElementById('catch-time').value,
        species: document.getElementById('catch-species').value,
        quantity: parseFloat(document.getElementById('catch-quantity').value),
        unit: document.getElementById('catch-unit').value,
        price_per_unit: parseFloat(document.getElementById('catch-price').value) || 0,
        vessel_id: document.getElementById('catch-vessel').value || null,
        gear_id: document.getElementById('catch-gear').value || null,
        zone_id: document.getElementById('catch-zone').value || null,
        catch_location: document.getElementById('catch-location').value,
        weather_conditions: document.getElementById('catch-weather').value,
        notes: document.getElementById('catch-notes').value
    };

    try {
        const response = await fetch(`${API_BASE}catch.php?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            alert('Catch recorded successfully!');
            event.target.reset();
            loadCatchForm();
        } else {
            alert(data.error || 'Failed to record catch');
        }
    } catch (error) {
        alert('Connection error. Please try again.');
    }
}

// Load vessels
async function loadVessels() {
    try {
        const response = await fetch(`${API_BASE}vessels.php?action=list`);
        const data = await response.json();

        if (data.success) {
            const listEl = document.getElementById('vessels-list');

            if (data.data.length === 0) {
                listEl.innerHTML = '<p style="color: var(--text-secondary);">No vessels registered yet.</p>';
                return;
            }

            listEl.innerHTML = data.data.map(vessel => `
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">${vessel.vessel_name}</h3>
                        <span class="badge badge-${vessel.status === 'active' ? 'success' : 'warning'}">${vessel.status}</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Type:</strong> ${vessel.vessel_type || 'N/A'}</p>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load vessels:', error);
    }
}

// Show add vessel modal
function showAddVessel() {
    document.getElementById('vessel-modal').style.display = 'block';
    document.getElementById('vessel-form').reset();
}

// Close modal
function closeModal() {
    document.getElementById('vessel-modal').style.display = 'none';
}

// Handle vessel submit
async function handleVesselSubmit(event) {
    event.preventDefault();

    const formData = {
        vessel_name: document.getElementById('vessel-name').value,
        vessel_type: document.getElementById('vessel-type').value,
        registration_number: null, // Simplified: No longer required
        length: null,
        tonnage: null,
        engine_power: null,
        year_built: null
    };

    try {
        const response = await fetch(`${API_BASE}vessels.php?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            alert('Vessel registered successfully!');
            closeModal();
            loadVessels();
        } else {
            alert(data.error || 'Failed to register vessel');
        }
    } catch (error) {
        alert('Connection error. Please try again.');
    }
}

// Load records
async function loadRecords() {
    try {
        const response = await fetch(`${API_BASE}catch.php?action=list`);
        const data = await response.json();

        if (data.success) {
            const tbody = document.getElementById('records-tbody');

            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-secondary);">No records found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(record => `
                <tr>
                    <td>${record.catch_date} ${record.catch_time || ''}</td>
                    <td>${record.species}</td>
                    <td>${record.quantity} ${record.unit}</td>
                    <td>₱${parseFloat(record.total_value || 0).toFixed(2)}</td>
                    <td>${record.vessel_name || 'N/A'}</td>
                    <td><span class="badge badge-${record.status === 'verified' ? 'success' : record.status === 'rejected' ? 'danger' : 'warning'}">${record.status}</span></td>
                    <td>
                        <button onclick="viewRecord(${record.id})" class="btn btn-sm btn-secondary">View</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load records:', error);
    }
}

// View record
function viewRecord(id) {
    // Implement view record functionality
    alert('View record ' + id);
}

// Setup event listeners
function setupEventListeners() {
    // Close modal when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById('vessel-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
}

