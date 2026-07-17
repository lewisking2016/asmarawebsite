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
        echo "<p style='color: green;'><strong>Found source directory in predefined list:</strong> $sourceRoot</p>";
        break;
    }
}

if (!$sourceRoot) {
    echo "<p>Searching recursively for old uploads folders in /home/asmaraco (this may take a few seconds)...</p>";
    $homeDir = '/home/asmaraco';
    if (is_dir($homeDir)) {
        // Recursive directory iterator to find any folder named 'uploads' containing 'menu' or 'branches'
        try {
            $directory = new RecursiveDirectoryIterator($homeDir, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            // Cap recursion depth to prevent issues
            $iterator->setMaxDepth(4);
            
            foreach ($iterator as $name => $object) {
                if ($object->isDir() && basename($name) === 'uploads') {
                    // Check that it's not the current active uploads folder
                    if (realpath($name) !== realpath($targetRoot)) {
                        // Check if it contains a 'menu' subfolder
                        if (is_dir($name . '/menu')) {
                            $sourceRoot = $name;
                            echo "<p style='color: green;'><strong>Auto-detected source directory recursively:</strong> $sourceRoot</p>";
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>Recursive search warning: " . $e->getMessage() . "</p>";
        }
    }
}

if (!$sourceRoot) {
    echo "<p style='color: red;'><strong>Error:</strong> Could not automatically locate the old uploads folder. Please make sure the old files exist on this server.</p>";
    
    // Debug helper: let's list home directory folders to help locate it
    echo "<h3>Directory Listing of /home/asmaraco for debugging:</h3>";
    $homeDir = '/home/asmaraco';
    if (is_dir($homeDir)) {
        $files = scandir($homeDir);
        echo "<ul>";
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $homeDir . '/' . $file;
            $isDir = is_dir($fullPath) ? ' (Directory)' : ' (File)';
            echo "<li>$file $isDir";
            if (is_dir($fullPath)) {
                // List subfolders
                $subfiles = @scandir($fullPath);
                if ($subfiles) {
                    echo "<ul>";
                    foreach ($subfiles as $sf) {
                        if ($sf === '.' || $sf === '..') continue;
                        if (is_dir($fullPath . '/' . $sf)) {
                            echo "<li>$sf (Directory)</li>";
                        }
                    }
                    echo "</ul>";
                }
            }
            echo "</li>";
        }
        echo "</ul>";
    }
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
