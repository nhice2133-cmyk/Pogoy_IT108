<?php
require_once __DIR__ . '/../config/database.php';

class Seeder {
    private $db;
    private $faker;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function run() {
        echo "Starting data seeding...\n";
        
        try {
            $this->db->beginTransaction();

            // 1. Seed Static/Sample Data (Gears, Zones, Sample Vessels)
            $this->seedStaticData();

            // 2. Seed Random Data
            $this->seedUsers(50);
            $this->seedVessels(30);
            $this->seedCatchRecords(10000);

            $this->db->commit();
            echo "Seeding completed successfully!\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }

    private function seedStaticData() {
        echo "Seeding static data (Gears, Zones, Sample Vessels)...\n";
        $this->seedGearsAndZones();
        $this->seedSampleVessels();
    }

    private function seedGearsAndZones() {
        // Gears
        $gears = [
            ['name' => 'Gill Net', 'type' => 'net', 'desc' => 'Vertical netting used to catch fish by gilling'],
            ['name' => 'Hand Line', 'type' => 'line', 'desc' => 'Simple fishing line with hook'],
            ['name' => 'Spear Gun', 'type' => 'spear', 'desc' => 'Fishing with a spear gun'],
            ['name' => 'Fish Trap', 'type' => 'trap', 'desc' => 'Stationary trap for catching fish'],
            ['name' => 'Trawl Net', 'type' => 'net', 'desc' => 'Conical net dragged along the sea bottom'],
            ['name' => 'Long Line', 'type' => 'line', 'desc' => 'Fishing line with multiple hooks']
        ];

        $stmt = $this->db->prepare("INSERT INTO fishing_gear (gear_name, gear_type, description, status) VALUES (?, ?, ?, 'active')");
        $checkStmt = $this->db->prepare("SELECT id FROM fishing_gear WHERE gear_name = ?");

        foreach ($gears as $gear) {
            $checkStmt->execute([$gear['name']]);
            if (!$checkStmt->fetch()) {
                $stmt->execute([$gear['name'], $gear['type'], $gear['desc']]);
            }
        }

        // Zones
        $zones = [
            ['name' => 'Coastal Zone A', 'type' => 'coastal', 'desc' => 'Primary fishing area along the coast'],
            ['name' => 'Coastal Zone B', 'type' => 'coastal', 'desc' => 'Secondary fishing area'],
            ['name' => 'Deep Sea Zone', 'type' => 'offshore', 'desc' => 'Deep water fishing zone'],
            ['name' => 'Municipal Waters', 'type' => 'coastal', 'desc' => 'Waters within municipal jurisdiction'],
            ['name' => 'Inland Fishing Area', 'type' => 'inland', 'desc' => 'River and lake fishing areas']
        ];

        $stmt = $this->db->prepare("INSERT INTO fishing_zones (zone_name, zone_type, area_description, status) VALUES (?, ?, ?, 'open')");
        $checkStmt = $this->db->prepare("SELECT id FROM fishing_zones WHERE zone_name = ?");

        foreach ($zones as $zone) {
            $checkStmt->execute([$zone['name']]);
            if (!$checkStmt->fetch()) {
                $stmt->execute([$zone['name'], $zone['type'], $zone['desc']]);
            }
        }
    }

    private function seedSampleVessels() {
        $fisherId = $this->getOrCreateFisher();
        
        $vessels = [
            ['name' => 'Maria Clara', 'type' => 'Motorized', 'reg_num' => 'VSL-2024-001', 'length' => 12.5, 'tonnage' => 5.2, 'power' => 45.0, 'year' => 2020],
            ['name' => 'San Juan', 'type' => 'Non-motorized', 'reg_num' => 'VSL-2024-002', 'length' => 8.0, 'tonnage' => 1.5, 'power' => null, 'year' => 2022],
            ['name' => 'Blue Marlin', 'type' => 'Commercial', 'reg_num' => 'VSL-2024-003', 'length' => 25.0, 'tonnage' => 15.0, 'power' => 150.0, 'year' => 2019],
            ['name' => 'Isla Verde', 'type' => 'Motorized', 'reg_num' => 'VSL-2024-004', 'length' => 10.0, 'tonnage' => 3.0, 'power' => 30.0, 'year' => 2021],
            ['name' => 'Santa Rosa', 'type' => 'Municipal', 'reg_num' => 'VSL-2024-005', 'length' => 6.5, 'tonnage' => 1.0, 'power' => 10.0, 'year' => 2023]
        ];

        $stmt = $this->db->prepare("INSERT INTO vessels (owner_id, vessel_name, vessel_type, registration_number, length, tonnage, engine_power, year_built, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $checkStmt = $this->db->prepare("SELECT id FROM vessels WHERE registration_number = ?");

        foreach ($vessels as $vessel) {
            $checkStmt->execute([$vessel['reg_num']]);
            if (!$checkStmt->fetch()) {
                $stmt->execute([
                    $fisherId, $vessel['name'], $vessel['type'], $vessel['reg_num'],
                    $vessel['length'], $vessel['tonnage'], $vessel['power'], $vessel['year']
                ]);
            }
        }
    }

    private function getOrCreateFisher() {
        $stmt = $this->db->query("SELECT id FROM users WHERE user_type = 'fisher' LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) return $id;

        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, full_name, user_type, status) VALUES (?, ?, ?, ?, 'fisher', 'active')");
        $stmt->execute(['sample_fisher', 'fisher@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Juan Dela Cruz']);
        return $this->db->lastInsertId();
    }

    private function seedUsers($count) {
        echo "Seeding $count users...\n";
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, full_name, user_type, status) VALUES (?, ?, ?, ?, 'fisher', 'active')");
        
        for ($i = 0; $i < $count; $i++) {
            $username = 'fisher_' . uniqid();
            $email = $username . '@example.com';
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $name = 'Fisher ' . substr(md5(uniqid()), 0, 8);
            
            $stmt->execute([$username, $email, $password, $name]);
        }
    }

    private function seedVessels($count) {
        echo "Seeding $count vessels...\n";
        // Get all fisher IDs
        $stmt = $this->db->query("SELECT id FROM users WHERE user_type = 'fisher'");
        $fisherIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($fisherIds)) return;

        $stmt = $this->db->prepare("INSERT INTO vessels (owner_id, vessel_name, registration_number, vessel_type, status) VALUES (?, ?, ?, 'Motorized', 'active')");

        for ($i = 0; $i < $count; $i++) {
            $ownerId = $fisherIds[array_rand($fisherIds)];
            $name = 'Vessel ' . substr(md5(uniqid()), 0, 6);
            $regNum = 'VSL-' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            $stmt->execute([$ownerId, $name, $regNum]);
        }
    }

    private function seedCatchRecords($count) {
        echo "Seeding $count catch records...\n";
        
        // Get IDs for references
        $fisherIds = $this->db->query("SELECT id FROM users WHERE user_type = 'fisher'")->fetchAll(PDO::FETCH_COLUMN);
        $vesselIds = $this->db->query("SELECT id FROM vessels")->fetchAll(PDO::FETCH_COLUMN);
        $gearIds = $this->db->query("SELECT id FROM fishing_gear")->fetchAll(PDO::FETCH_COLUMN);
        $zoneIds = $this->db->query("SELECT id FROM fishing_zones")->fetchAll(PDO::FETCH_COLUMN);
        $speciesList = $this->db->query("SELECT common_name FROM fish_species")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($fisherIds) || empty($vesselIds)) {
            echo "Skipping catch records: No fishers or vessels found.\n";
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO catch_records (fisher_id, vessel_id, gear_id, zone_id, catch_date, catch_time, species, quantity, unit, price_per_unit, total_value, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'kg', ?, ?, ?)");

        for ($i = 0; $i < $count; $i++) {
            $fisherId = $fisherIds[array_rand($fisherIds)];
            $vesselId = $vesselIds[array_rand($vesselIds)]; // Simplified: random vessel
            $gearId = !empty($gearIds) ? $gearIds[array_rand($gearIds)] : null;
            $zoneId = !empty($zoneIds) ? $zoneIds[array_rand($zoneIds)] : null;
            
            // Random date within last 2 years
            $timestamp = mt_rand(strtotime('-2 years'), time());
            $date = date('Y-m-d', $timestamp);
            $time = date('H:i:s', $timestamp);
            
            $species = !empty($speciesList) ? $speciesList[array_rand($speciesList)] : 'Unknown Fish';
            $quantity = mt_rand(5, 500); // 5 to 500 kg
            $price = mt_rand(50, 300); // 50 to 300 per kg
            $total = $quantity * $price;
            
            $statuses = ['pending', 'verified', 'rejected'];
            $status = $statuses[mt_rand(0, 2)];

            $stmt->execute([
                $fisherId, $vesselId, $gearId, $zoneId, 
                $date, $time, $species, $quantity, 
                $price, $total, $status
            ]);

            if ($i % 1000 == 0) {
                echo "Generated $i records...\n";
            }
        }
    }
}

$seeder = new Seeder();
$seeder->run();
