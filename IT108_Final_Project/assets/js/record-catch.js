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

            loadFormData();
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

// Load form data (vessels, gear, zones, species)
async function loadFormData() {
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

            // Store species data for image lookup
            const speciesMap = {};

            data.data.forEach(species => {
                datalist.innerHTML += `<option value="${species.common_name}">${species.local_name || species.common_name}</option>`;
                speciesMap[species.common_name] = species.image_url;
            });

            // Handle image display
            const speciesInput = document.getElementById('catch-species');
            const imageContainer = document.getElementById('species-image-container');
            const speciesImage = document.getElementById('species-image');

            speciesInput.addEventListener('input', function () {
                const selectedSpecies = this.value;
                const imageUrl = speciesMap[selectedSpecies];

                if (imageUrl) {
                    speciesImage.src = imageUrl;
                    imageContainer.style.display = 'block';
                } else {
                    imageContainer.style.display = 'none';
                }
            });

            // Trigger input event if value exists (e.g. on reload)
            if (speciesInput.value) {
                speciesInput.dispatchEvent(new Event('input'));
            }
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
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Recording...';

        const response = await fetch(`${API_BASE}catch.php?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        submitBtn.disabled = false;
        submitBtn.textContent = originalText;

        if (data.success) {
            showMessage('Catch recorded successfully! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        } else {
            showMessage(data.error || 'Failed to record catch', 'error');
        }
    } catch (error) {
        showMessage('Connection error. Please try again.', 'error');
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Record Catch';
    }
}

// Show message
function showMessage(message, type) {
    const msgEl = document.getElementById('catch-message');
    if (msgEl) {
        msgEl.textContent = message;
        msgEl.className = `message ${type}`;
        setTimeout(() => {
            msgEl.className = 'message';
        }, 5000);
    }
}

