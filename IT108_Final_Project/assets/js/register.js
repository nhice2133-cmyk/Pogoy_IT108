// API Base URL
const API_BASE = 'api/';

// Show message
function showMessage(message, type) {
    const msgEl = document.getElementById('auth-message');
    if (msgEl) {
        msgEl.textContent = message;
        msgEl.className = `message ${type}`;
        setTimeout(() => {
            msgEl.className = 'message';
        }, 5000);
    }
}

// Handle registration
async function handleRegister(event) {
    event.preventDefault();
    
    const password = document.getElementById('reg-password').value;
    const passwordConfirm = document.getElementById('reg-password-confirm').value;
    
    // Validate password match
    if (password !== passwordConfirm) {
        showMessage('Passwords do not match!', 'error');
        return;
    }
    
    // Validate password length
    if (password.length < 8) {
        showMessage('Password must be at least 8 characters long', 'error');
        return;
    }
    
    const formData = {
        username: document.getElementById('reg-username').value.trim(),
        email: document.getElementById('reg-email').value.trim(),
        password: password,
        full_name: document.getElementById('reg-fullname').value.trim(),
        phone: document.getElementById('reg-phone').value.trim(),
        address: document.getElementById('reg-address').value.trim(),
        registration_number: document.getElementById('reg-regnum').value.trim()
    };
    
    // Validate required fields
    if (!formData.username || !formData.email || !formData.password || !formData.full_name) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    try {
        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registering...';
        
        const response = await fetch(`${API_BASE}auth.php?action=register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        // Reset button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.success) {
            showMessage('Registration successful! Redirecting to login...', 'success');
            // Redirect to login page after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showMessage(data.error || 'Registration failed', 'error');
        }
    } catch (error) {
        showMessage('Connection error. Please try again.', 'error');
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Register Account';
    }
}


