<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vessels - Smart Fisheries Management System</title>
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
                    <a href="vessels.php" class="nav-link active">🚤 My Vessels</a>
                    <a href="catch-records.php" class="nav-link">📋 Catch Records</a>
                    <a href="admin.php" class="nav-link" id="nav-admin-link" style="display: none;">🛡️ Admin Panel</a>

                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout()" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <div class="view">
                    <div class="view-header">
                        <h1>🚤 My Vessels</h1>
                        <button onclick="showAddVessel()" class="btn btn-primary">➕ Add Vessel</button>
                    </div>
                    <div id="vessels-list" class="cards-grid">
                        <p style="text-align: center; color: var(--text-secondary); padding: 40px;">Loading vessels...
                        </p>
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
                    <label>Vessel Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="vessel-name" required placeholder="Enter vessel name">
                </div>
                <div class="form-group">
                    <label>Vessel Type</label>
                    <select id="vessel-type" class="form-control">
                        <option value="" disabled selected>Select vessel type</option>
                        <option value="Motorized">Motorized</option>
                        <option value="Non-motorized">Non-motorized</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Municipal">Municipal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Registration Number</label>
                    <input type="text" id="vessel-regnum" placeholder="Vessel registration number">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Length (m)</label>
                        <input type="number" id="vessel-length" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Tonnage</label>
                        <input type="number" id="vessel-tonnage" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Engine Power (HP)</label>
                        <input type="number" id="vessel-power" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Year Built</label>
                        <input type="number" id="vessel-year" min="1900" max="2024" placeholder="YYYY">
                    </div>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Vessel</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/vessels.js?v=1.1"></script>
</body>

</html>