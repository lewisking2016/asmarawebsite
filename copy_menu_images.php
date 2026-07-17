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
$foundFilesMap = [];

// Custom safe recursive file and directory scanner
function scanForFiles($dir, &$foundPaths, &$foundFilesMap) {
    // Avoid infinite loops, system directories, and standard blacklisted folders
    $blacklistedNames = [
        '.', '..', '.git', 'node_modules', 'wp-admin', 'wp-includes', 
        'mail', '.cpanel', 'etc', 'ssl', '.cagefs', '.spamassassin', 
        'roundcube', 'perl5', 'caches', 'datastore'
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
            }
            // Recurse
            scanForFiles($path, $foundPaths, $foundFilesMap);
        } else {
            // If it's a menu image file, catalog it
            if (strpos($file, 'menu_') === 0 && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $file)) {
                // Ensure it is not in the current active uploads directory
                if (strpos($path, 'public_html/backend/uploads') === false) {
                    $foundFilesMap[$file] = $path;
                }
            }
        }
    }
}

echo "<p>Scanning server recursively for any old menu images (this may take a few seconds)...</p>";
scanForFiles('/home/asmaraco', $foundPaths, $foundFilesMap);

if (!empty($foundPaths)) {
    echo "<p style='color: green;'><strong>Found candidate source uploads directories:</strong></p><ul>";
    foreach ($foundPaths as $index => $path) {
        echo "<li>[$index] $path</li>";
    }
    echo "</ul>";
}

if (!empty($foundFilesMap)) {
    echo "<p style='color: green;'><strong>Found " . count($foundFilesMap) . " old menu image files on the server!</strong></p>";
    
    // Copy the individual files found
    $copiedCount = 0;
    $errorsCount = 0;
    foreach ($foundFilesMap as $filename => $sourcePath) {
        $destPath = $targetMenuDir . '/' . $filename;
        if (!file_exists($destPath)) {
            if (copy($sourcePath, $destPath)) {
                $copiedCount++;
            } else {
                echo "<p style='color: red;'>Failed to copy file: $filename from $sourcePath</p>";
                $errorsCount++;
            }
        }
    }
    echo "<p style='color: green;'><strong>File Copy complete:</strong> Successfully copied $copiedCount files, $errorsCount errors.</p>";
} else {
    echo "<p style='color: orange;'>No files starting with 'menu_' were found outside the active folder.</p>";
}

// Perform folder copy from candidate folders if any were found
if (!empty($foundPaths)) {
    // Helper function to copy files
    function copyFolderContents($src, $dst, $type) {
        if (!is_dir($src)) {
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
                if (!file_exists($dstFile)) {
                    if (copy($srcFile, $dstFile)) {
                        $copiedCount++;
                    }
                } else {
                    $skippedCount++;
                }
            }
        }
        closedir($dir);
        echo "<p><strong>$type Folder Sync:</strong> Copied $copiedCount files from $src.</p>";
    }

    foreach ($foundPaths as $sourceRoot) {
        copyFolderContents($sourceRoot . '/menu', $targetMenuDir, 'Menu');
        copyFolderContents($sourceRoot . '/branches', $targetBranchesDir, 'Branches');
    }
}

if (empty($foundFilesMap) && empty($foundPaths)) {
    echo "<p style='color: red; font-weight: bold;'>Error: No old upload directories or individual menu images (menu_*.jpg) were found anywhere in /home/asmaraco.</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>Migration complete! Please delete this file (`copy_menu_images.php`) from your server for security.</p>";
}
