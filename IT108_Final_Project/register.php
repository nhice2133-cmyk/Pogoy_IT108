<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Fisheries Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="app">
        <div class="auth-container">
            <div class="auth-box auth-box-wide">
                <div style="position: absolute; top: 20px; right: 20px;">

                </div>
                <div class="auth-header">
                    <h1>🐟 Smart Fisheries Management</h1>
                    <p>Cabadbaran City</p>
                    <h2 style="margin-top: 20px; font-size: 24px; color: var(--text-primary);">Create New Account</h2>
                </div>

                <form id="register-form" onsubmit="handleRegister(event)" class="register-form">
                    <div class="form-group">
                        <label>Full Name <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" id="reg-fullname" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label>Username <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" id="reg-username" required placeholder="Choose a username">
                    </div>

                    <div class="form-group">
                        <label>Email <span style="color: var(--danger-color);">*</span></label>
                        <input type="email" id="reg-email" required placeholder="your.email@example.com">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" id="reg-phone" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label>Registration Number</label>
                            <input type="text" id="reg-regnum" placeholder="Fisher registration number">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="reg-address" rows="3" placeholder="Enter your address"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password <span style="color: var(--danger-color);">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="reg-password" required minlength="8"
                                    placeholder="Minimum 8 characters">
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('reg-password', this)"
                                    aria-label="Toggle password visibility" title="Show/Hide password">
                                    <span class="toggle-icon">👁</span>
                                </button>
                            </div>
                            <small
                                style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 4px;">Password
                                must be at least 8 characters long</small>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password <span style="color: var(--danger-color);">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="reg-password-confirm" required minlength="8"
                                    placeholder="Re-enter password">
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('reg-password-confirm', this)"
                                    aria-label="Toggle password visibility" title="Show/Hide password">
                                    <span class="toggle-icon">👁</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="auth-message" class="message"></div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">Register Account</button>
                    </div>

                    <p class="auth-footer">
                        Already have an account? <a href="index.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/register.js"></script>
    <script>
        // Password toggle function
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('.toggle-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = '🔒';
                button.setAttribute('aria-label', 'Hide password');
                button.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                icon.textContent = '👁';
                button.setAttribute('aria-label', 'Show password');
                button.setAttribute('title', 'Show password');
            }
        }
    </script>
</body>

</html>