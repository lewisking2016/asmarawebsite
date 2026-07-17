<?php
/**
 * Image Migration Utility
 * Copies uploaded images (menu and branches) from the old subdomain directory
 * to the new production directory on cPanel.
 */

// Disable time limits for copy operations
set_time_limit(0);

echo "<h2>Asmara Website - Image Migration Tool</h2>";

// Set correct target paths
$targetRoot = __DIR__ . '/backend/uploads';
$targetMenuDir = $targetRoot . '/menu';
$targetBranchesDir = $targetRoot . '/branches';

// Create target dirs if they don't exist
if (!is_dir($targetMenuDir)) {
    mkdir($targetMenuDir, 0755, true);
}
if (!is_dir($targetBranchesDir)) {
    mkdir($targetBranchesDir, 0755, true);
}

// Find possible source directories in cPanel home
$possibleSources = [
    '/home/asmaraco/new.asmara.co.ke/backend/uploads',
    '/home/asmaraco/public_html/new/backend/uploads',
    '/home/asmaraco/new/backend/uploads',
    dirname(__DIR__) . '/new.asmara.co.ke/backend/uploads',
    dirname(__DIR__) . '/new/backend/uploads',
];

$sourceRoot = null;
foreach ($possibleSources as $path) {
    if (is_dir($path)) {
        $sourceRoot = $path;
        echo "<p style='color: green;'><strong>Found source directory:</strong> $sourceRoot</p>";
        break;
    }
}

if (!$sourceRoot) {
    // If not found, let's scan /home/asmaraco/
    $homeDir = '/home/asmaraco';
    if (is_dir($homeDir)) {
        $files = scandir($homeDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $homeDir . '/' . $file . '/backend/uploads';
            if (is_dir($fullPath) && realpath($fullPath) !== realpath($targetRoot)) {
                $sourceRoot = $fullPath;
                echo "<p style='color: green;'><strong>Auto-detected source directory:</strong> $sourceRoot</p>";
                break;
            }
        }
    }
}

if (!$sourceRoot) {
    echo "<p style='color: red;'><strong>Error:</strong> Could not automatically locate the old uploads folder. Please make sure the old files exist on this server.</p>";
    echo "<p>Locations searched:</p><ul>";
    foreach ($possibleSources as $path) {
        echo "<li>$path</li>";
    }
    echo "</ul>";
    exit;
}

// Helper function to copy files
function copyFolderContents($src, $dst, $type) {
    if (!is_dir($src)) {
        echo "<p style='color: orange;'>Source directory for $type ($src) does not exist. Skipping.</p>";
        return;
    }
    
    $dir = opendir($src);
    $copiedCount = 0;
    $skippedCount = 0;
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        
        $srcFile = $src . '/' . $file;
        $dstFile = $dst . '/' . $file;
        
        if (is_file($srcFile)) {
            // Check if file already exists in destination
            if (!file_exists($dstFile)) {
                if (copy($srcFile, $dstFile)) {
                    $copiedCount++;
                } else {
                    echo "<p style='color: red;'>Failed to copy: $file</p>";
                }
            } else {
                $skippedCount++;
            }
        }
    }
    closedir($dir);
    echo "<p><strong>$type Migration:</strong> Copied $copiedCount files, skipped $skippedCount existing files.</p>";
}

// Perform copy
copyFolderContents($sourceRoot . '/menu', $targetMenuDir, 'Menu Images');
copyFolderContents($sourceRoot . '/branches', $targetBranchesDir, 'Branch Images');

echo "<p style='color: green; font-weight: bold;'>Migration complete! Please delete this file (`copy_menu_images.php`) from your server for security.</p>";
