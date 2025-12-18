<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Fisheries Management System - Cabadbaran City</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="app">
        <!-- Login/Register View -->
        <div id="auth-view" class="auth-container">
            <div class="auth-box">

                <div class="auth-header">
                    <h1>🐟 Smart Fisheries Management</h1>
                    <p>Cabadbaran City</p>
                </div>

                <!-- Login Form -->
                <form id="login-form" onsubmit="return handleLogin(event)" autocomplete="off">
                    <div class="form-group">
                        <input type="text" id="login-username" required placeholder="Username" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <div class="password-wrapper">
                            <input type="password" id="login-password" required placeholder="Password" autocomplete="off">
                            <button type="button" class="password-toggle"
                                onclick="togglePassword('login-password', this)" aria-label="Toggle password visibility"
                                title="Show/Hide password">
                                <span class="toggle-icon">👁</span>
                            </button>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                    <p class="auth-footer">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>
                </form>

                <div id="auth-message" class="message"></div>
            </div>
        </div>

        <!-- Main Application View -->
        <div id="main-view" class="main-container" style="display: none;">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2>🐟 Fisheries Management</h2>
                </div>
                <div class="nav-menu">
                    <a href="#" onclick="showView('dashboard', this); return false;" class="nav-link active">📊
                        Dashboard</a>
                    <a href="#" onclick="showView('catch', this); return false;" class="nav-link">🎣 Record Catch</a>
                    <a href="#" onclick="showView('vessels', this); return false;" class="nav-link">🚤 My Vessels</a>
                    <a href="#" onclick="showView('records', this); return false;" class="nav-link">📋 Catch Records</a>
                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout()" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <!-- Dashboard View -->
                <div id="dashboard-view" class="view" style="display: block;">
                    <h1>Dashboard</h1>

                    <!-- Welcome Section -->
                    <div class="welcome-card">
                        <h2>Welcome back, <span id="welcome-name">User</span>!</h2>
                        <p>Monitor your fishing activities and track your catches</p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Catches</h3>
                            <p id="stat-total-catches" class="stat-value">-</p>
                        </div>
                        <div class="stat-card">
                            <h3>Total Quantity</h3>
                            <p id="stat-total-quantity" class="stat-value">-</p>
                        </div>
                        <div class="stat-card">
                            <h3>Total Value</h3>
                            <p id="stat-total-value" class="stat-value">-</p>
                        </div>
                        <div class="stat-card">
                            <h3>Fishing Days</h3>
                            <p id="stat-fishing-days" class="stat-value">-</p>
                        </div>
                    </div>

                    <div class="chart-container">
                        <h2>Monthly Catch Overview</h2>
                        <canvas id="monthly-chart"></canvas>
                    </div>

                    <div class="recent-catches">
                        <h2>Recent Catches</h2>
                        <div id="recent-catches-list"></div>
                    </div>
                </div>

                <!-- Record Catch View -->
                <div id="catch-view" class="view" style="display: none;">
                    <h1>Record New Catch</h1>
                    <form id="catch-form" onsubmit="handleCatchSubmit(event)" class="form-card">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" id="catch-date" required>
                            </div>
                            <div class="form-group">
                                <label>Time</label>
                                <input type="time" id="catch-time" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Species</label>
                                <input type="text" id="catch-species" list="species-list" required>
                                <datalist id="species-list"></datalist>
                            </div>
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" id="catch-quantity" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Unit</label>
                                <select id="catch-unit" required>
                                    <option value="kg">kg</option>
                                    <option value="pieces">pieces</option>
                                    <option value="tons">tons</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Price per Unit (PHP)</label>
                                <input type="number" id="catch-price" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vessel</label>
                                <select id="catch-vessel">
                                    <option value="">Select vessel</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Fishing Gear</label>
                                <select id="catch-gear">
                                    <option value="">Select gear</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Fishing Zone</label>
                                <select id="catch-zone">
                                    <option value="">Select zone</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" id="catch-location">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Weather Conditions</label>
                                <input type="text" id="catch-weather">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea id="catch-notes" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Record Catch</button>
                    </form>
                </div>

                <!-- Vessels View -->
                <div id="vessels-view" class="view" style="display: none;">
                    <div class="view-header">
                        <h1>My Vessels</h1>
                        <button onclick="showAddVessel()" class="btn btn-primary">Add Vessel</button>
                    </div>
                    <div id="vessels-list" class="cards-grid"></div>
                </div>

                <!-- Records View -->
                <div id="records-view" class="view" style="display: none;">
                    <h1>Catch Records</h1>
                    <div class="table-container">
                        <table id="records-table" class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Species</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                    <th>Vessel</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="records-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Vessel Modal -->
    <div id="vessel-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Vessel</h2>
            <form id="vessel-form" onsubmit="handleVesselSubmit(event)">
                <div class="form-group">
                    <label>Vessel Name</label>
                    <input type="text" id="vessel-name" required>
                </div>
                <div class="form-group">
                    <label>Vessel Type</label>
                    <select id="vessel-type" required>
                        <option value="Motorized">Motorized</option>
                        <option value="Non-motorized">Non-motorized</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Municipal">Municipal</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Vessel</button>
            </form>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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