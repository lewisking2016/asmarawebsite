<?php
/**
 * Backups Inspector Utility
 * Scans directories like backup/, asmara-old-backup/, temp_system_backup/
 * to see if there are any zip files or old upload folders.
 */

echo "<h2>Asmara Website - Backup Inspector</h2>";

$directoriesToScan = [
    '/home/asmaraco/backup',
    '/home/asmaraco/asmara-old-backup',
    '/home/asmaraco/temp_system_backup',
    '/home/asmaraco/demo.asmara.co.ke',
];

foreach ($directoriesToScan as $dir) {
    echo "<h3>Listing files in: $dir</h3>";
    if (!is_dir($dir)) {
        echo "<p style='color: red;'>Directory does not exist or is not readable.</p>";
        continue;
    }
    
    $files = [];
    scanFolderRecursively($dir, $files);
    
    if (empty($files)) {
        echo "<p>No files found.</p>";
    } else {
        echo "<ul>";
        foreach ($files as $file) {
            $size = round(filesize($file) / 1024 / 1024, 2);
            echo "<li>" . str_replace($dir, '', $file) . " ($size MB)</li>";
        }
        echo "</ul>";
    }
}

function scanFolderRecursively($dir, &$results) {
    $files = @scandir($dir);
    if ($files === false) return;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            // Avoid deep recursion into wp-admin/wp-includes
            if ($file === 'wp-admin' || $file === 'wp-includes' || $file === 'node_modules' || $file === '.git') {
                continue;
            }
            scanFolderRecursively($path, $results);
        } else {
            // Log ZIP, TAR, SQL, and JPG files
            if (preg_match('/\.(zip|tar|gz|tgz|sql|jpg|jpeg|png)$/i', $file)) {
                $results[] = $path;
            }
        }
    }
}
