<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Catch - Smart Fisheries Management System</title>
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
                    <a href="record-catch.php" class="nav-link active">🎣 Record Catch</a>
                    <a href="vessels.php" class="nav-link">🚤 My Vessels</a>
                    <a href="catch-records.php" class="nav-link">📋 Catch Records</a>
                    <a href="admin.php" class="nav-link" id="nav-admin-link" style="display: none;">🛡️ Admin Panel</a>

                    <span id="user-info" class="nav-user"></span>
                    <button onclick="handleLogout()" class="btn btn-sm btn-secondary">Logout</button>
                </div>
            </nav>

            <div class="content-area">
                <div class="view">
                    <h1>🎣 Record New Catch</h1>
                    <form id="catch-form" onsubmit="handleCatchSubmit(event)" class="form-card">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date <span style="color: var(--danger-color);">*</span></label>
                                <input type="date" id="catch-date" required>
                            </div>
                            <div class="form-group">
                                <label>Time <span style="color: var(--danger-color);">*</span></label>
                                <input type="time" id="catch-time" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Species <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" id="catch-species" list="species-list" required
                                    placeholder="Enter fish species">
                                <datalist id="species-list"></datalist>
                                <div id="species-image-container" style="display: none; margin-top: 10px; text-align: center;">
                                    <img id="species-image" src="" alt="Species Image" style="max-width: 100%; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Quantity <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" id="catch-quantity" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Unit <span style="color: var(--danger-color);">*</span></label>
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
                                <input type="number" id="catch-price" step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Vessel</label>
                                <select id="catch-vessel">
                                    <option value="">Select vessel</option>
                                    <option value="sample_1">Maria Clara </option>
                                    <option value="sample_2">San Juan </option>
                                    <option value="sample_3">Blue Marlin </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Fishing Gear</label>
                                <select id="catch-gear">
                                    <option value="">Select gear</option>
                                    <option value="sample_gear_1">Gill Net</option>
                                    <option value="sample_gear_2">Hand Line</option>
                                    <option value="sample_gear_3">Spear Gun</option>
                                    <option value="sample_gear_4">Fish Trap</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Fishing Zone</label>
                                <select id="catch-zone">
                                    <option value="">Select zone</option>
                                    <option value="sample_zone_1">Coastal Zone A</option>
                                    <option value="sample_zone_2">Coastal Zone B</option>
                                    <option value="sample_zone_3">Deep Sea Zone</option>
                                    <option value="sample_zone_4">Municipal Waters</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" id="catch-location" placeholder="Fishing location">
                        </div>

                        <div class="form-group">
                            <label>Weather Conditions</label>
                            <input type="text" id="catch-weather" placeholder="e.g., Sunny, Cloudy, Rainy">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea id="catch-notes" rows="3"
                                placeholder="Additional notes about the catch"></textarea>
                        </div>

                        <div id="catch-message" class="message"></div>

                        <div style="display: flex; gap: 12px; margin-top: 24px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Record Catch</button>
                            <a href="dashboard.php" class="btn btn-secondary" style="text-decoration: none;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/dark-mode.js"></script>
    <script src="assets/js/record-catch.js?v=1.1"></script>
</body>

</html>