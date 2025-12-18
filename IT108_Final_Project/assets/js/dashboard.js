// API Base URL
const API_BASE = 'api/';

// Current user data
let currentUser = null;
let monthlyChart = null;

// Handle logout - MUST be defined FIRST so it's available immediately when script loads
window.handleLogout = function () {
    console.log('Logout button clicked');

    // Call logout API (fire and forget - don't wait for response)
    fetch(`${API_BASE}auth.php?action=logout`, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-cache'
    }).catch(err => {
        console.error('Logout API error (ignored):', err);
    });

    // Force immediate redirect - don't wait for API response
    window.location.href = 'index.php';

    return false; // Prevent any default behavior
};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
});

// Check authentication status
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}auth.php?action=check`);
        const data = await response.json();

        if (data.logged_in) {
            currentUser = data.user;
            updateUserInfo();
            loadDashboard();
        } else {
            // Redirect to login if not authenticated
            window.location.href = 'index.php';
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'index.php';
    }
}

// Update user info in navbar
function updateUserInfo() {
    if (currentUser) {
        const userInfoEl = document.getElementById('user-info');
        const welcomeNameEl = document.getElementById('welcome-name');

        if (userInfoEl) {
            userInfoEl.textContent = currentUser.full_name || currentUser.username;
        }
        if (welcomeNameEl) {
            welcomeNameEl.textContent = currentUser.full_name || currentUser.username;
        }
    }
}


// Load dashboard data
async function loadDashboard() {
    try {
        // Set default values
        updateStatCard('stat-total-catches', '0');
        updateStatCard('stat-total-quantity', '0 kg');
        updateStatCard('stat-total-value', '₱0.00');
        updateStatCard('stat-fishing-days', '0');

        const response = await fetch(`${API_BASE}catch.php?action=stats`);
        const data = await response.json();

        if (data.success) {
            const stats = data.stats || {};

            // Update stat cards
            updateStatCard('stat-total-catches', stats.total_catches || 0);
            updateStatCard('stat-total-quantity', (parseFloat(stats.total_quantity) || 0).toFixed(2) + ' kg');
            updateStatCard('stat-total-value', '₱' + (parseFloat(stats.total_value) || 0).toFixed(2));
            updateStatCard('stat-fishing-days', stats.fishing_days || 0);

            // Load monthly chart
            if (data.monthly && data.monthly.length > 0) {
                loadMonthlyChart(data.monthly);
            } else {
                initializeEmptyChart();
            }

            // Load recent catches
            loadRecentCatches();

            // Show admin link if admin
            if (currentUser && (currentUser.user_type === 'admin' || currentUser.user_type === 'officer')) {
                const adminLink = document.getElementById('nav-admin-link');
                if (adminLink) adminLink.style.display = 'block';
            }
        } else {
            console.error('Failed to load dashboard stats:', data.error);
            showEmptyState();
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
        showEmptyState();
    }
}

// Update stat card
function updateStatCard(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = value;
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
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }, {
                label: 'Value (PHP)',
                data: values,
                borderColor: '#14b8a6',
                backgroundColor: 'rgba(20, 184, 166, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity (kg)',
                        color: '#64748b'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Value (PHP)',
                        color: '#64748b'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
}

// Initialize empty chart
function initializeEmptyChart() {
    const ctx = document.getElementById('monthly-chart');
    if (!ctx) return;

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
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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

        const listEl = document.getElementById('recent-catches-list');
        if (!listEl) return;

        if (data.success && data.data && data.data.length > 0) {
            const recent = data.data.slice(0, 5);

            listEl.innerHTML = recent.map(catchRecord => `
                <div class="card" style="margin-bottom: 16px; transition: all 0.3s;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                ${catchRecord.image_url ? `<img src="${catchRecord.image_url}" alt="${catchRecord.species}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">` : ''}
                                <div>
                                    <strong style="font-size: 18px; color: var(--text-primary); display: block;">${catchRecord.species}</strong>
                                    <span class="badge badge-${catchRecord.status === 'verified' ? 'success' : catchRecord.status === 'rejected' ? 'danger' : 'warning'}">${catchRecord.status}</span>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; color: var(--text-secondary); font-size: 14px; margin-left: ${catchRecord.image_url ? '60px' : '0'};">
                                <div><strong>Quantity:</strong> ${catchRecord.quantity} ${catchRecord.unit}</div>
                                <div><strong>Value:</strong> ₱${parseFloat(catchRecord.total_value || 0).toFixed(2)}</div>
                                <div><strong>Date:</strong> ${catchRecord.catch_date} ${catchRecord.catch_time || ''}</div>
                                ${catchRecord.vessel_name ? `<div><strong>Vessel:</strong> ${catchRecord.vessel_name}</div>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            listEl.innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                    <div style="font-size: 48px; margin-bottom: 16px;">🎣</div>
                    <p style="font-size: 18px; margin-bottom: 8px; font-weight: 600;">No catches recorded yet</p>
                    <p style="font-size: 14px; margin-bottom: 24px;">Start recording your catches to see them here</p>
                    <a href="record-catch.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Record Your First Catch</a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load recent catches:', error);
        const listEl = document.getElementById('recent-catches-list');
        if (listEl) {
            listEl.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 40px;">Failed to load recent catches. Please try again.</p>';
        }
    }
}

// Show empty state
function showEmptyState() {
    const listEl = document.getElementById('recent-catches-list');
    if (listEl) {
        listEl.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                <p style="font-size: 18px; margin-bottom: 8px; font-weight: 600;">No data available</p>
                <p style="font-size: 14px;">Start recording catches to see statistics</p>
            </div>
        `;
    }
}
