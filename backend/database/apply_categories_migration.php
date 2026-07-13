<?php
/**
 * Database Migration Script - Menu Categories
 */

require_once __DIR__ . '/Connection.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "Starting migration...\n";

    // 1. Create table menu_categories
    $sql = "CREATE TABLE IF NOT EXISTS menu_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        display_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Table 'menu_categories' verified/created.\n";

    // 2. Insert standard and requested categories
    $categories = [
        ['name' => 'appetizers', 'display_name' => 'Appetizers'],
        ['name' => 'mains', 'display_name' => 'Main Courses'],
        ['name' => 'desserts', 'display_name' => 'Desserts'],
        ['name' => 'drinks', 'display_name' => 'Drinks'],
        ['name' => 'pizzas', 'display_name' => 'Pizzas'],
        ['name' => 'vegetarian', 'display_name' => 'Vegetarian Options']
    ];

    $stmt = $conn->prepare("INSERT INTO menu_categories (name, display_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE display_name = VALUES(display_name)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat['name'], $cat['display_name']]);
        echo "Category '{$cat['name']}' seeded.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
