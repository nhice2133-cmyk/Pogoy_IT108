<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catch Records - Smart Fisheries Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="app">
        <div class="main-container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2>🐟 Smart Fisheries Management</h2>
                </div>
                <div class="nav-menu">
                    <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
                    <a href="record-catch.php" class="nav-link">🎣 Record Catch</a>
                    <a href="vessels.php" class="nav-link">🚤 My Vessels</a>
                    <a href="catch-records.php" class="nav-link active">📋 Catch Records</a>
                    <a href="admin.php" class="nav-link" id="nav-admin-link" style="display: none;">🛡️ Admin Panel</a>

                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout()" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <div class="view">
                    <h1>📋 Catch Records</h1>
                    <div class="table-container">
                        <table id="records-table" class="data-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Species</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                    <th>Vessel</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="records-tbody">
                                <tr>
                                    <td colspan="6"
                                        style="text-align: center; padding: 40px; color: var(--text-secondary);">Loading
                                        records...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/catch-records.js?v=1.1"></script>
</body>

</html>