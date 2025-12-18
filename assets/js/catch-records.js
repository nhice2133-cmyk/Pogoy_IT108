// API Base URL
const API_BASE = 'api/';

let currentUser = null;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
});

// Check authentication
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}auth.php?action=check`);
        const data = await response.json();

        if (data.logged_in) {
            currentUser = data.user;
            updateUserInfo();

            // Show admin link if admin
            if (currentUser && (currentUser.user_type === 'admin' || currentUser.user_type === 'officer')) {
                const adminLink = document.getElementById('nav-admin-link');
                if (adminLink) adminLink.style.display = 'block';
            }

            loadRecords();
        } else {
            window.location.href = 'index.php';
        }
    } catch (error) {
        window.location.href = 'index.php';
    }
}

// Update user info
function updateUserInfo() {
    if (currentUser) {
        const userInfoEl = document.getElementById('user-info');
        if (userInfoEl) {
            userInfoEl.textContent = currentUser.full_name || currentUser.username;
        }
    }
}

// Handle logout - make it globally accessible
window.handleLogout = async function () {
    try {
        await fetch(`${API_BASE}auth.php?action=logout`);
        window.location.href = 'index.php';
    } catch (error) {
        window.location.href = 'index.php';
    }
};

// Load records
async function loadRecords() {
    try {
        const response = await fetch(`${API_BASE}catch.php?action=list`);
        const data = await response.json();

        const tbody = document.getElementById('records-tbody');
        if (!tbody) return;

        if (data.success && data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(record => `
                <tr>
                    <td>${record.catch_date} ${record.catch_time || ''}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            ${record.image_url ? `<img src="${record.image_url}" alt="${record.species}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">` : ''}
                            <strong>${record.species}</strong>
                        </div>
                    </td>
                    <td>${record.quantity} ${record.unit}</td>
                    <td>₱${parseFloat(record.total_value || 0).toFixed(2)}</td>
                    <td>${record.vessel_name || 'N/A'}</td>
                    <td><span class="badge badge-${record.status === 'verified' ? 'success' : record.status === 'rejected' ? 'danger' : 'warning'}">${record.status}</span></td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                        <p style="font-size: 18px; margin-bottom: 8px; font-weight: 600;">No catch records found</p>
                        <p style="font-size: 14px; margin-bottom: 24px;">Start recording catches to see them here</p>
                        <a href="record-catch.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Record Your First Catch</a>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Failed to load records:', error);
        const tbody = document.getElementById('records-tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">Failed to load records. Please try again.</td></tr>';
        }
    }
}

