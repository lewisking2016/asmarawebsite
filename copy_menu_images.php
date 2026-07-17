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
$foundPaths = [];

// Custom safe recursive directory scanner
function scanForUploads($dir, &$foundPaths) {
    // Avoid infinite loops, system directories, and standard blacklisted folders
    $blacklistedNames = [
        '.', '..', '.git', 'node_modules', 'wp-admin', 'wp-includes', 
        'mail', '.cpanel', 'etc', 'ssl', '.cagefs', '.spamassassin', 
        '.trash', 'roundcube', 'perl5', 'caches', 'datastore'
    ];
    
    if (!is_dir($dir) || !is_readable($dir)) {
        return;
    }
    
    $files = @scandir($dir);
    if ($files === false) {
        return;
    }
    
    foreach ($files as $file) {
        if (in_array($file, $blacklistedNames)) {
            continue;
        }
        
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            // Check if this directory is named 'uploads' and contains a 'menu' folder
            if ($file === 'uploads' && is_dir($path . '/menu')) {
                // Ensure it is not the current active uploads folder
                $targetRealPath = realpath(__DIR__ . '/backend/uploads');
                $foundRealPath = realpath($path);
                if ($foundRealPath && $foundRealPath !== $targetRealPath) {
                    $foundPaths[] = $path;
                }
            } else {
                // Recursively scan subfolder
                scanForUploads($path, $foundPaths);
            }
        }
    }
}

echo "<p>Scanning server directories for old uploads folder safely...</p>";
scanForUploads('/home/asmaraco', $foundPaths);

if (!empty($foundPaths)) {
    echo "<p style='color: green;'><strong>Found candidate source uploads directories:</strong></p><ul>";
    foreach ($foundPaths as $index => $path) {
        echo "<li>[$index] $path</li>";
    }
    echo "</ul>";
    
    // Choose the first match as sourceRoot
    $sourceRoot = $foundPaths[0];
    echo "<p style='color: green;'><strong>Selected source directory:</strong> $sourceRoot</p>";
}

if (!$sourceRoot) {
    echo "<p style='color: red;'><strong>Error:</strong> Could not automatically locate the old uploads folder. Please check if the folders were deleted or if they exist in a different directory.</p>";
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
    echo "<p><strong>$type Migration:</strong> Copied $copiedCount files from $src, skipped $skippedCount existing files.</p>";
}

// Perform copy from all candidate folders found (just in case)
foreach ($foundPaths as $sourceRoot) {
    echo "<h4>Migrating from: $sourceRoot</h4>";
    copyFolderContents($sourceRoot . '/menu', $targetMenuDir, 'Menu Images');
    copyFolderContents($sourceRoot . '/branches', $targetBranchesDir, 'Branch Images');
}

echo "<p style='color: green; font-weight: bold;'>Migration complete! Please delete this file (`copy_menu_images.php`) from your server for security.</p>";

