<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Add image_url column
    $sql = "ALTER TABLE fish_species ADD COLUMN image_url VARCHAR(255) AFTER local_name";
    $conn->exec($sql);
    echo "Column 'image_url' added successfully.\n";

    // Update existing species with placeholder paths
    $updates = [
        'Indian Sardine' => 'assets/images/species/sardine.jpg',
        'Indian Mackerel' => 'assets/images/species/mackerel.jpg',
        'Red Snapper' => 'assets/images/species/snapper.jpg',
        'Giant Tiger Prawn' => 'assets/images/species/prawn.jpg',
        'Mud Crab' => 'assets/images/species/crab.jpg',
        'Milkfish' => 'assets/images/species/milkfish.jpg'
    ];

    $stmt = $conn->prepare("UPDATE fish_species SET image_url = ? WHERE common_name = ?");
    
    foreach ($updates as $name => $path) {
        $stmt->execute([$path, $name]);
        echo "Updated $name with $path\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
