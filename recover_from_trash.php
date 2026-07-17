<?php
/**
 * Trash Recovery Utility
 * Searches the cPanel .trash directory for the missing menu images
 * and copies them to backend/uploads/menu/
 */

set_time_limit(300);
echo "<h2>Asmara Website - Trash Recovery</h2>";

$targetMenuDir = __DIR__ . '/backend/uploads/menu';
$targetBranchesDir = __DIR__ . '/backend/uploads/branches';

if (!is_dir($targetMenuDir)) mkdir($targetMenuDir, 0755, true);
if (!is_dir($targetBranchesDir)) mkdir($targetBranchesDir, 0755, true);

// The exact filenames we need from the database
$missingFiles = [
    'menu_6a560cf789b72.jpg',
    'menu_6a560d0cdafcf.jpg',
    'menu_6a561431a4557.jpg',
    'menu_6a56120874dab.jpg',
    'menu_6a55f0d7e51a8.jpg',
    'menu_6a56010fc5f0e.jpg',
    'menu_6a560b212bad3.jpg',
    'menu_6a5600e72021f.jpg',
    'menu_6a560a6eb9297.jpg',
    'menu_6a55f2524c020.jpg',
    'menu_6a573b4ade873.jpg',
    'menu_6a560f33e8603.jpg',
    'menu_6a560c4f39e8f.jpg',
    'menu_6a5610692c8c5.jpg',
    'menu_6a5600b4ef94d.jpg',
    'menu_6a573b27b1d8e.jpg',
    'menu_6a55f60cb187c.jpg',
    'menu_6a56131e1659e.jpg',
    'menu_6a56140b088fb.jpg',
    'menu_6a55f92f5cd7d.jpg',
    'menu_6a55fbb330a3c.jpg',
    'menu_6a55f6c2004a2.jpg',
    'menu_6a55f8c5e8bc1.jpg',
    'menu_6a5610d19ca0c.jpg',
    'menu_6a55fc3c6d15e.jpg',
    'menu_6a55fc105904d.jpg',
    'menu_6a55fe8479c6d.jpg',
    'menu_6a5607a07ffd1.jpg',
    'menu_6a5609eed6af3.jpg',
    'menu_6a560bfd106d0.jpg',
];

echo "<p>Looking for " . count($missingFiles) . " missing menu images...</p>";

// Search locations - focus on trash and any other possible locations
$searchRoots = [
    '/home/asmaraco/.trash',
    '/home/asmaraco/.Trash',
    '/home/asmaraco/trash',
];

// Also check if there's a trash inside public_html
if (is_dir('/home/asmaraco/public_html/.trash')) {
    $searchRoots[] = '/home/asmaraco/public_html/.trash';
}

$foundFiles = [];

function searchForFiles($dir, $targetFiles, &$foundFiles) {
    if (!is_dir($dir) || !is_readable($dir)) {
        return;
    }
    
    $files = @scandir($dir);
    if ($files === false) return;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Skip known system dirs that won't have our files
            if (in_array($file, ['.git', 'node_modules', 'mail', '.cpanel', 'perl5'])) continue;
            searchForFiles($path, $targetFiles, $foundFiles);
        } else {
            if (in_array($file, $targetFiles)) {
                $foundFiles[$file] = $path;
            }
        }
    }
}

echo "<h3>Scanning trash directories...</h3>";

foreach ($searchRoots as $root) {
    if (is_dir($root)) {
        echo "<p>Scanning: $root ...</p>";
        searchForFiles($root, $missingFiles, $foundFiles);
    } else {
        echo "<p style='color: orange;'>Not found: $root</p>";
    }
}

// Report what we found
if (!empty($foundFiles)) {
    echo "<h3 style='color: green;'>Found " . count($foundFiles) . " of " . count($missingFiles) . " missing files in trash!</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Filename</th><th>Found At</th><th>Status</th></tr>";
    
    $copiedCount = 0;
    foreach ($foundFiles as $filename => $sourcePath) {
        $destPath = $targetMenuDir . '/' . $filename;
        $status = '';
        
        if (file_exists($destPath)) {
            $status = "<span style='color: blue;'>Already exists</span>";
        } else {
            if (copy($sourcePath, $destPath)) {
                $copiedCount++;
                $status = "<span style='color: green;'>✅ Recovered!</span>";
            } else {
                $status = "<span style='color: red;'>❌ Copy failed</span>";
            }
        }
        
        echo "<tr>";
        echo "<td>$filename</td>";
        echo "<td>" . htmlspecialchars($sourcePath) . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green; font-weight: bold;'>Successfully recovered $copiedCount files!</p>";
    
    // Show which files are still missing
    $stillMissing = array_diff($missingFiles, array_keys($foundFiles));
    if (!empty($stillMissing)) {
        echo "<h3 style='color: orange;'>Still missing (" . count($stillMissing) . " files):</h3><ul>";
        foreach ($stillMissing as $f) {
            echo "<li>$f</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<h3 style='color: red;'>No matching files found in trash directories.</h3>";
    
    // List what's actually in the trash to help debug
    echo "<h4>Contents of trash root directories:</h4>";
    foreach ($searchRoots as $root) {
        if (is_dir($root)) {
            echo "<p><strong>$root:</strong></p><ul>";
            $items = @scandir($root);
            if ($items) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $fullPath = $root . '/' . $item;
                    $type = is_dir($fullPath) ? '(Directory)' : '(File, ' . round(filesize($fullPath)/1024, 2) . ' KB)';
                    echo "<li>$item $type</li>";
                }
            }
            echo "</ul>";
        }
    }
}

echo "<p><em>Please delete this file after use for security.</em></p>";
