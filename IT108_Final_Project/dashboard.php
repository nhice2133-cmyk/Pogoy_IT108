<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Fisheries Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="app">
        <!-- Main Application View -->
        <div class="main-container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2>🐟 Smart Fisheries Management</h2>
                </div>
                <div class="nav-menu">
                    <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
                    <a href="record-catch.php" class="nav-link">🎣 Record Catch</a>
                    <a href="vessels.php" class="nav-link">🚤 My Vessels</a>
                    <a href="catch-records.php" class="nav-link">📋 Catch Records</a>
                    <a href="admin.php" class="nav-link" id="nav-admin-link" style="display: none;">🛡️ Admin Panel</a>

                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout(); return false;" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <!-- Dashboard View -->
                <div class="view">
                    <h1>📊 Dashboard</h1>

                    <!-- Welcome Section -->
                    <div class="welcome-card">
                        <h2>Welcome back, <span id="welcome-name">User</span>!</h2>
                        <p>Monitor your fishing activities and track your catches</p>
                    </div>

                    <!-- Statistics Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Total Catches</h3>
                                <span style="font-size: 32px;">🎣</span>
                            </div>
                            <p id="stat-total-catches" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">All time records
                            </p>
                        </div>

                        <div class="stat-card">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Total Quantity</h3>
                                <span style="font-size: 32px;">⚖️</span>
                            </div>
                            <p id="stat-total-quantity" class="stat-value">0 kg</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Total weight
                                caught</p>
                        </div>

                        <div class="stat-card">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Total Value</h3>
                                <span style="font-size: 32px;">💰</span>
                            </div>
                            <p id="stat-total-value" class="stat-value">₱0.00</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Estimated value
                            </p>
                        </div>

                        <div class="stat-card">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Fishing Days</h3>
                                <span style="font-size: 32px;">📅</span>
                            </div>
                            <p id="stat-fishing-days" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Active fishing
                                days</p>
                        </div>
                    </div>

                    <!-- Charts and Recent Activity -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
                        <!-- Monthly Chart -->
                        <div class="chart-container">
                            <h2>📈 Monthly Catch Overview</h2>
                            <canvas id="monthly-chart" style="max-height: 400px;"></canvas>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card" style="padding: 32px;">
                            <h2 style="margin-bottom: 24px; font-size: 20px; font-weight: 700;">Quick Actions</h2>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <a href="record-catch.php" class="btn btn-primary"
                                    style="text-decoration: none; width: 100%;">🎣 Record New Catch</a>
                                <a href="vessels.php" class="btn btn-secondary"
                                    style="text-decoration: none; width: 100%;">🚤 Manage Vessels</a>
                                <a href="catch-records.php" class="btn btn-secondary"
                                    style="text-decoration: none; width: 100%;">📋 View All Records</a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Catches -->
                    <div class="recent-catches">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h2>🕒 Recent Catches</h2>
                            <a href="catch-records.php"
                                style="color: var(--primary-color); text-decoration: none; font-weight: 600;">View All
                                →</a>
                        </div>
                        <div id="recent-catches-list">
                            <p style="text-align: center; color: var(--text-secondary); padding: 40px;">Loading recent
                                catches...</p>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>