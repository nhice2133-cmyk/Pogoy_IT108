// API Base URL
const API_BASE = 'api/';

// Current user data
let currentUser = null;

// Handle logout
window.handleLogout = function () {
    fetch(`${API_BASE}auth.php?action=logout`, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-cache'
    }).catch(err => {
        console.error('Logout API error (ignored):', err);
    });
    window.location.href = 'index.php';
    return false;
};

// Initialize admin dashboard
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

            // Verify admin access
            if (currentUser.user_type !== 'admin' && currentUser.user_type !== 'officer') {
                alert('Access denied. Admin privileges required.');
                window.location.href = 'dashboard.php';
                return;
            }

            updateUserInfo();
            loadAdminData();
        } else {
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
        if (userInfoEl) {
            userInfoEl.textContent = currentUser.full_name || currentUser.username;
        }
    }
}

// Load all admin data
function loadAdminData() {
    loadAdminStats();
    loadPendingCatches();
    loadUsers();
}

// Toggle section visibility
// Toggle section visibility (Exclusive)
window.toggleSection = function (sectionId) {
    // Hide all sections first
    const sections = ['admin-pending-catches', 'admin-user-management'];
    sections.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });

    // Show the selected section
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = 'block';
    }
};

// Load admin statistics
async function loadAdminStats() {
    try {
        // Fetch catch stats
        const catchResponse = await fetch(`${API_BASE}catch.php?action=admin_stats`);
        const catchData = await catchResponse.json();

        if (catchData.success && catchData.stats) {
            document.getElementById('stat-pending-approvals').textContent = catchData.stats.pending_approvals || 0;
            document.getElementById('stat-todays-catches').textContent = catchData.stats.todays_catches || 0;
        }

        // Fetch user stats
        const userResponse = await fetch(`${API_BASE}users.php?action=admin_stats`);
        const userData = await userResponse.json();

        if (userData.success && userData.stats) {
            document.getElementById('stat-total-users').textContent = userData.stats.total_users || 0;
            document.getElementById('stat-active-fishers').textContent = userData.stats.active_fishers || 0;
        }
    } catch (error) {
        console.error('Failed to load admin stats:', error);
    }
}

// --- Admin Functions ---

async function loadPendingCatches() {
    const tbody = document.getElementById('pending-catches-tbody');
    const statusFilter = document.getElementById('catch-status-filter');
    const userFilter = document.getElementById('catch-user-filter');

    if (!tbody) return;

    const status = statusFilter ? statusFilter.value : 'pending';
    const fisherId = userFilter ? userFilter.value : '';

    // Populate user filter if empty (only once)
    if (userFilter && userFilter.options.length <= 1) {
        try {
            const userResponse = await fetch(`${API_BASE}users.php?action=list`);
            const userData = await userResponse.json();
            if (userData.success && userData.data) {
                const fishers = userData.data.filter(u => u.user_type === 'fisher');
                fishers.forEach(fisher => {
                    const option = document.createElement('option');
                    option.value = fisher.id;
                    option.textContent = fisher.full_name || fisher.username;
                    userFilter.appendChild(option);
                });
            }
        } catch (e) {
            console.error('Failed to load users for filter', e);
        }
    }

    try {
        let url = `${API_BASE}catch.php?action=list`;
        const params = [];
        if (status) params.push(`status=${status}`);
        if (fisherId) params.push(`fisher_id=${fisherId}`);

        if (params.length > 0) {
            url += '&' + params.join('&');
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data) {
            let catches = data.data;

            if (catches.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No records found.</td></tr>';
                return;
            }

            tbody.innerHTML = catches.map(c => `
                <tr>
                    <td>${c.catch_date}</td>
                    <td>${c.fisher_name || 'Unknown'}</td>
                    <td>${c.species}</td>
                    <td>${c.quantity} ${c.unit}</td>
                    <td>₱${parseFloat(c.total_value || 0).toFixed(2)}</td>
                    <td><span class="badge badge-${c.status === 'verified' ? 'success' : c.status === 'rejected' ? 'danger' : 'warning'}">${c.status}</span></td>
                    <td>
                        ${c.status === 'pending' ? `
                        <div class="table-actions">
                            <button onclick="verifyCatch(${c.id}, 'verified')" class="btn-action approve" title="Approve">✓</button>
                            <button onclick="verifyCatch(${c.id}, 'rejected')" class="btn-action reject" title="Reject">✕</button>
                        </div>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load pending catches:', error);
    }
}

async function verifyCatch(id, status) {
    if (!confirm(`Are you sure you want to ${status === 'verified' ? 'approve' : 'reject'} this catch?`)) return;

    try {
        const response = await fetch(`${API_BASE}catch.php?action=verify&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        });
        const data = await response.json();

        if (data.success) {
            alert(`Catch ${status} successfully!`);
            loadPendingCatches();
            loadAdminStats(); // Refresh stats
        } else {
            alert(data.error || 'Operation failed');
        }
    } catch (error) {
        console.error('Verify error:', error);
        alert('Connection error');
    }
}


async function loadUsers() {
    const tbody = document.getElementById('users-tbody');
    const filter = document.getElementById('user-status-filter');
    if (!tbody) return;

    const status = filter ? filter.value : '';

    try {
        const url = `${API_BASE}users.php?action=list${status ? `&status=${status}` : ''}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No users found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(u => `
                <tr>
                    <td>${u.full_name || 'N/A'}</td>
                    <td>${u.username}</td>
                    <td><span class="badge badge-info">${u.user_type}</span></td>
                    <td><span class="badge badge-${u.status === 'active' ? 'success' : 'warning'}">${u.status}</span></td>
                    <td>
                        <div class="table-actions">
                        ${u.status === 'pending' ? `
                            <button onclick="updateUserStatus(${u.id}, 'active')" class="btn-action approve" title="Approve">✓</button>
                            <button onclick="updateUserStatus(${u.id}, 'inactive')" class="btn-action reject" title="Reject">✕</button>
                        ` : u.status === 'active' ? `
                            <button onclick="updateUserStatus(${u.id}, 'inactive')" class="btn-action reject" title="Deactivate">✕</button>
                        ` : `
                            <button onclick="updateUserStatus(${u.id}, 'active')" class="btn-action approve" title="Activate">✓</button>
                        `}
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

async function updateUserStatus(id, status) {
    if (!confirm(`Set user status to ${status}?`)) return;

    try {
        const response = await fetch(`${API_BASE}users.php?action=update&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        });
        const data = await response.json();

        if (data.success) {
            alert('User status updated!');
            loadUsers();
        } else {
            alert(data.error || 'Update failed');
        }
    } catch (error) {
        console.error('Update user error:', error);
        alert('Connection error');
    }
}
