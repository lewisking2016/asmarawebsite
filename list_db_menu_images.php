<?php
/**
 * DB Image List Utility
 * Lists the image names stored in the database for menu items,
 * and lists what files actually exist in backend/uploads/menu/
 */

require_once __DIR__ . '/backend/database/Connection.php';

echo "<h2>Asmara Website - DB Image List</h2>";

try {
    $db = Database::getInstance();
    $items = $db->getRows("SELECT name, image_url, image FROM menu_items");
    
    echo "<h3>Menu Items in Database:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID / Name</th><th>Image URL Field</th><th>Image Field</th><th>Status</th></tr>";
    
    foreach ($items as $item) {
        $img = !empty($item['image_url']) ? $item['image_url'] : ($item['image'] ?? '');
        $filename = basename($img);
        
        $localExists = file_exists(__DIR__ . '/backend/uploads/menu/' . $filename);
        $status = $localExists ? "<span style='color: green;'>Exists</span>" : "<span style='color: red;'>Missing</span>";
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['image_url'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($item['image'] ?? 'NULL') . "</td>";
        echo "<td>$status ($filename)</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Files currently in backend/uploads/menu/:</h3>";
$dir = __DIR__ . '/backend/uploads/menu';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $size = round(filesize($dir . '/' . $file) / 1024, 2);
        echo "<li>$file ($size KB)</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Directory does not exist: $dir</p>";
}
