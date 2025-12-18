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

            loadVessels();
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

// Load vessels
async function loadVessels() {
    try {
        const response = await fetch(`${API_BASE}vessels.php?action=list`);
        const data = await response.json();

        const listEl = document.getElementById('vessels-list');
        if (!listEl) return;

        if (data.success && data.data && data.data.length > 0) {
            listEl.innerHTML = data.data.map(vessel => `
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">${vessel.vessel_name}</h3>
                        <span class="badge badge-${vessel.status === 'active' ? 'success' : 'warning'}">${vessel.status}</span>
                    </div>
                    <div class="card-body">
                        ${vessel.vessel_type ? `<p><strong>Type:</strong> ${vessel.vessel_type}</p>` : ''}
                        ${vessel.registration_number ? `<p><strong>Registration:</strong> ${vessel.registration_number}</p>` : ''}
                        ${vessel.length ? `<p><strong>Length:</strong> ${vessel.length} m</p>` : ''}
                        ${vessel.tonnage ? `<p><strong>Tonnage:</strong> ${vessel.tonnage}</p>` : ''}
                        ${vessel.engine_power ? `<p><strong>Engine Power:</strong> ${vessel.engine_power} HP</p>` : ''}
                        ${vessel.year_built ? `<p><strong>Year Built:</strong> ${vessel.year_built}</p>` : ''}
                    </div>
                </div>
            `).join('');
        } else {
            listEl.innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary); grid-column: 1 / -1;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🚤</div>
                    <p style="font-size: 18px; margin-bottom: 8px; font-weight: 600;">No vessels registered yet</p>
                    <p style="font-size: 14px; margin-bottom: 24px;">Register your first vessel to get started</p>
                    <button onclick="showAddVessel()" class="btn btn-primary">Add Your First Vessel</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load vessels:', error);
        const listEl = document.getElementById('vessels-list');
        if (listEl) {
            listEl.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 40px;">Failed to load vessels. Please try again.</p>';
        }
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
        vessel_name: document.getElementById('vessel-name').value.trim(),
        vessel_type: document.getElementById('vessel-type').value.trim(),
        registration_number: document.getElementById('vessel-regnum').value.trim(),
        length: document.getElementById('vessel-length').value || null,
        tonnage: document.getElementById('vessel-tonnage').value || null,
        engine_power: document.getElementById('vessel-power').value || null,
        year_built: document.getElementById('vessel-year').value || null
    };

    if (!formData.vessel_name) {
        alert('Vessel name is required');
        return;
    }

    try {
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const response = await fetch(`${API_BASE}vessels.php?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        submitBtn.disabled = false;
        submitBtn.textContent = originalText;

        if (data.success) {
            alert('Vessel registered successfully!');
            closeModal();
            loadVessels();
        } else {
            alert(data.error || 'Failed to register vessel');
        }
    } catch (error) {
        alert('Connection error. Please try again.');
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Save Vessel';
    }
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('vessel-modal');
    if (event.target == modal) {
        closeModal();
    }
}

