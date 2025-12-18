-- Smart Fisheries Management and Catch Monitoring System
-- Database Schema for Cabadbaran City

CREATE DATABASE IF NOT EXISTS fisheries_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fisheries_management;

-- Users table (fishers, admin, officers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('fisher', 'admin', 'officer') DEFAULT 'fisher',
    registration_number VARCHAR(50) UNIQUE,
    license_number VARCHAR(50),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
);

-- Vessels table
CREATE TABLE vessels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    vessel_name VARCHAR(100) NOT NULL,
    vessel_type VARCHAR(50),
    registration_number VARCHAR(50) UNIQUE,
    length DECIMAL(10,2),
    tonnage DECIMAL(10,2),
    engine_power DECIMAL(10,2),
    year_built YEAR,
    status ENUM('active', 'inactive', 'under_repair') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id)
);

-- Fishing gear table
CREATE TABLE fishing_gear (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gear_name VARCHAR(100) NOT NULL,
    gear_type VARCHAR(50),
    description TEXT,
    mesh_size DECIMAL(5,2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fishing locations/zones
CREATE TABLE fishing_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    zone_name VARCHAR(100) NOT NULL,
    coordinates TEXT,
    area_description TEXT,
    zone_type ENUM('coastal', 'offshore', 'inland') DEFAULT 'coastal',
    status ENUM('open', 'closed', 'restricted') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Catch records
CREATE TABLE catch_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fisher_id INT NOT NULL,
    vessel_id INT,
    gear_id INT,
    zone_id INT,
    catch_date DATE NOT NULL,
    catch_time TIME,
    species VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit ENUM('kg', 'pieces', 'tons') DEFAULT 'kg',
    price_per_unit DECIMAL(10,2),
    total_value DECIMAL(12,2),
    catch_location VARCHAR(200),
    weather_conditions VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fisher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vessel_id) REFERENCES vessels(id) ON DELETE SET NULL,
    FOREIGN KEY (gear_id) REFERENCES fishing_gear(id) ON DELETE SET NULL,
    FOREIGN KEY (zone_id) REFERENCES fishing_zones(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_fisher (fisher_id),
    INDEX idx_catch_date (catch_date),
    INDEX idx_status (status)
);

-- Fish species catalog
CREATE TABLE fish_species (
    id INT PRIMARY KEY AUTO_INCREMENT,
    scientific_name VARCHAR(100),
    common_name VARCHAR(100) NOT NULL,
    local_name VARCHAR(100),
    category ENUM('finfish', 'shellfish', 'crustacean', 'mollusk', 'other') DEFAULT 'finfish',
    conservation_status VARCHAR(50),
    min_size_limit DECIMAL(5,2),
    season_restrictions TEXT,
    status ENUM('allowed', 'restricted', 'prohibited') DEFAULT 'allowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reports and analytics
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    generated_by INT,
    report_data JSON,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- System settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
-- Password hash for: admin123
-- To verify: php -r "var_dump(password_verify('admin123', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'));"
INSERT INTO users (username, email, password_hash, full_name, user_type, status) VALUES
('admin', 'admin@fisheries.cabadbaran.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'active');

-- Insert default fishing gear types
INSERT INTO fishing_gear (gear_name, gear_type, description) VALUES
('Gill Net', 'net', 'Vertical netting used to catch fish by gilling'),
('Trawl Net', 'net', 'Conical net dragged along the sea bottom'),
('Hook and Line', 'line', 'Fishing with baited hooks'),
('Fish Trap', 'trap', 'Stationary trap for catching fish'),
('Hand Line', 'line', 'Simple fishing line with hook'),
('Long Line', 'line', 'Fishing line with multiple hooks');

-- Insert default fishing zones
INSERT INTO fishing_zones (zone_name, zone_type, area_description, status) VALUES
('Cabadbaran Coastal Zone A', 'coastal', 'Primary fishing area along the coast', 'open'),
('Cabadbaran Coastal Zone B', 'coastal', 'Secondary fishing area', 'open'),
('Offshore Zone 1', 'offshore', 'Deep water fishing zone', 'open'),
('Inland Fishing Area', 'inland', 'River and lake fishing areas', 'open');

-- Insert common fish species
INSERT INTO fish_species (scientific_name, common_name, local_name, category, status) VALUES
('Sardinella longiceps', 'Indian Sardine', 'Tunsoy', 'finfish', 'allowed'),
('Rastrelliger kanagurta', 'Indian Mackerel', 'Alumahan', 'finfish', 'allowed'),
('Lutjanus campechanus', 'Red Snapper', 'Maya-maya', 'finfish', 'allowed'),
('Penaeus monodon', 'Giant Tiger Prawn', 'Sugpo', 'crustacean', 'allowed'),
('Scylla serrata', 'Mud Crab', 'Alimango', 'crustacean', 'allowed'),
('Chanos chanos', 'Milkfish', 'Bangus', 'finfish', 'allowed');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('system_name', 'Smart Fisheries Management System', 'Name of the system'),
('city_name', 'Cabadbaran City', 'City where the system is deployed'),
('max_catch_per_day', '500', 'Maximum catch allowed per day in kg'),
('reporting_deadline', '24', 'Hours after catch to report'),
('currency', 'PHP', 'Currency used in the system');

