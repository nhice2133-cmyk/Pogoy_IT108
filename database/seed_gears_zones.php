<?php
require_once __DIR__ . '/../config/database.php';

class GearZoneSeeder {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function run() {
        echo "Starting gear and zone seeding...\n";

        try {
            $this->db->beginTransaction();

            // 1. Add Gears
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
                if ($checkStmt->fetch()) {
                    echo "Gear '{$gear['name']}' already exists. Skipping.\n";
                    continue;
                }

                $stmt->execute([$gear['name'], $gear['type'], $gear['desc']]);
                echo "Added gear: {$gear['name']}\n";
            }

            // 2. Add Zones
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
                if ($checkStmt->fetch()) {
                    echo "Zone '{$zone['name']}' already exists. Skipping.\n";
                    continue;
                }

                $stmt->execute([$zone['name'], $zone['type'], $zone['desc']]);
                echo "Added zone: {$zone['name']}\n";
            }

            $this->db->commit();
            echo "Gear and Zone seeding completed successfully!\n";

        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }
}

$seeder = new GearZoneSeeder();
$seeder->run();
