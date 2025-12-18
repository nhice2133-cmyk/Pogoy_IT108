<?php
require_once __DIR__ . '/../config/database.php';

class VesselSeeder {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function run() {
        echo "Starting vessel seeding...\n";

        try {
            $this->db->beginTransaction();

            // 1. Ensure we have a fisher user
            $fisherId = $this->getOrCreateFisher();
            echo "Using fisher ID: $fisherId\n";

            // 2. Add sample vessels
            $vessels = [
                [
                    'name' => 'Maria Clara',
                    'type' => 'Motorized',
                    'reg_num' => 'VSL-2024-001',
                    'length' => 12.5,
                    'tonnage' => 5.2,
                    'power' => 45.0,
                    'year' => 2020
                ],
                [
                    'name' => 'San Juan',
                    'type' => 'Non-motorized',
                    'reg_num' => 'VSL-2024-002',
                    'length' => 8.0,
                    'tonnage' => 1.5,
                    'power' => null,
                    'year' => 2022
                ],
                [
                    'name' => 'Blue Marlin',
                    'type' => 'Commercial',
                    'reg_num' => 'VSL-2024-003',
                    'length' => 25.0,
                    'tonnage' => 15.0,
                    'power' => 150.0,
                    'year' => 2019
                ],
                [
                    'name' => 'Isla Verde',
                    'type' => 'Motorized',
                    'reg_num' => 'VSL-2024-004',
                    'length' => 10.0,
                    'tonnage' => 3.0,
                    'power' => 30.0,
                    'year' => 2021
                ],
                [
                    'name' => 'Santa Rosa',
                    'type' => 'Municipal',
                    'reg_num' => 'VSL-2024-005',
                    'length' => 6.5,
                    'tonnage' => 1.0,
                    'power' => 10.0,
                    'year' => 2023
                ]
            ];

            $stmt = $this->db->prepare("INSERT INTO vessels (owner_id, vessel_name, vessel_type, registration_number, length, tonnage, engine_power, year_built, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            
            $checkStmt = $this->db->prepare("SELECT id FROM vessels WHERE registration_number = ?");

            foreach ($vessels as $vessel) {
                // Check if exists
                $checkStmt->execute([$vessel['reg_num']]);
                if ($checkStmt->fetch()) {
                    echo "Vessel {$vessel['name']} ({$vessel['reg_num']}) already exists. Skipping.\n";
                    continue;
                }

                $stmt->execute([
                    $fisherId,
                    $vessel['name'],
                    $vessel['type'],
                    $vessel['reg_num'],
                    $vessel['length'],
                    $vessel['tonnage'],
                    $vessel['power'],
                    $vessel['year']
                ]);
                echo "Added vessel: {$vessel['name']}\n";
            }

            $this->db->commit();
            echo "Vessel seeding completed successfully!\n";

        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }

    private function getOrCreateFisher() {
        // Try to find an existing fisher
        $stmt = $this->db->query("SELECT id FROM users WHERE user_type = 'fisher' LIMIT 1");
        $id = $stmt->fetchColumn();

        if ($id) {
            return $id;
        }

        // Create a new fisher if none exists
        echo "No fisher found. Creating sample fisher...\n";
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, full_name, user_type, status) VALUES (?, ?, ?, ?, 'fisher', 'active')");
        
        $username = 'sample_fisher';
        $email = 'fisher@example.com';
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $name = 'Juan Dela Cruz';

        try {
            $stmt->execute([$username, $email, $password, $name]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // If username/email exists but user_type might be different (unlikely given previous check, but good for safety)
            // Or if there's a race condition
            throw new Exception("Could not create sample fisher: " . $e->getMessage());
        }
    }
}

$seeder = new VesselSeeder();
$seeder->run();
