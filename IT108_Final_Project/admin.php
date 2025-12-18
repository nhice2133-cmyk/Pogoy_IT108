<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Smart Fisheries Management System</title>
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
                    <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
                    <a href="record-catch.php" class="nav-link">🎣 Record Catch</a>
                    <a href="vessels.php" class="nav-link">🚤 My Vessels</a>
                    <a href="catch-records.php" class="nav-link">📋 Catch Records</a>
                    <a href="admin.php" class="nav-link active">🛡️ Admin Panel</a>

                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout(); return false;" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <!-- Admin View -->
                <div class="view">
                    <h1>🛡️ Admin Panel</h1>

                    <!-- Admin Stats Grid -->
                    <div class="stats-grid" style="margin-bottom: 32px;">
                        <div class="stat-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Pending Approvals</h3>
                                <span style="font-size: 32px;">⏳</span>
                            </div>
                            <p id="stat-pending-approvals" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Waiting for verification</p>
                        </div>

                        <div class="stat-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Total Users</h3>
                                <span style="font-size: 32px;">👥</span>
                            </div>
                            <p id="stat-total-users" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Registered accounts</p>
                        </div>

                        <div class="stat-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Active Fishers</h3>
                                <span style="font-size: 32px;">🎣</span>
                            </div>
                            <p id="stat-active-fishers" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Currently active</p>
                        </div>

                        <div class="stat-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3>Today's Catches</h3>
                                <span style="font-size: 32px;">📅</span>
                            </div>
                            <p id="stat-todays-catches" class="stat-value">0</p>
                            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Recorded today</p>
                        </div>
                    </div>

                    <!-- Management Toggles -->
                    <div style="display: flex; gap: 16px; margin-bottom: 32px;">
                        <button onclick="toggleSection('admin-pending-catches')" class="btn btn-primary" style="flex: 1; padding: 16px;">
                            <span style="font-size: 24px; display: block; margin-bottom: 8px;">📋</span>
                            Catch Management
                        </button>
                        <button onclick="toggleSection('admin-user-management')" class="btn btn-primary" style="flex: 1; padding: 16px;">
                            <span style="font-size: 24px; display: block; margin-bottom: 8px;">👥</span>
                            User Management
                        </button>
                    </div>

                    <!-- Admin Section: Catch Management -->
                    <div id="admin-pending-catches" style="margin-top: 32px; display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h2>📋 Catch Management</h2>
                            <div style="display: flex; gap: 10px;">
                                <select id="catch-user-filter" class="form-control filter-select" style="width: auto;" onchange="loadPendingCatches()">
                                    <option value="">All Users</option>
                                    <!-- Populated by JS -->
                                </select>
                                <select id="catch-status-filter" class="form-control filter-select" style="width: auto;" onchange="loadPendingCatches()">
                                    <option value="pending">Pending</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="">All Statuses</option>
                                </select>
                            </div>
                        </div>
                        <div class="card" style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Fisher</th>
                                        <th>Species</th>
                                        <th>Quantity</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pending-catches-tbody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Admin Section: User Management -->
                    <div id="admin-user-management" style="margin-top: 32px; display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h2>👥 User Management</h2>
                            <select id="user-status-filter" class="form-control filter-select" style="width: auto;" onchange="loadUsers()">
                                <option value="">All Users</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="card" style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/admin.js"></script>
</body>

</html>
