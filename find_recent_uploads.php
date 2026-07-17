<?php
/**
 * Recent Upload Finder
 * Searches the entire cPanel home directory for any image files
 * modified in the last 7 days.
 */

echo "<h2>Asmara Website - Recent Uploads Finder</h2>";

$homeDir = '/home/asmaraco';
$sevenDaysAgo = time() - (7 * 24 * 60 * 60); // 7 days in seconds

$recentFiles = [];
scanForRecentImages($homeDir, $sevenDaysAgo, $recentFiles);

if (empty($recentFiles)) {
    echo "<p style='color: red;'>No files modified in the last 7 days were found on the server.</p>";
} else {
    echo "<p style='color: green;'><strong>Found " . count($recentFiles) . " recently modified image files:</strong></p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>File Path</th><th>Modified Date</th><th>Size</th></tr>";
    foreach ($recentFiles as $file) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($file['path']) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', $file['mtime']) . "</td>";
        echo "<td>" . round($file['size'] / 1024, 2) . " KB</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function scanForRecentImages($dir, $cutoffTime, &$results) {
    $blacklistedNames = [
        '.', '..', '.git', 'node_modules', 'wp-admin', 'wp-includes', 
        'mail', '.cpanel', 'etc', 'ssl', '.cagefs', '.spamassassin', 
        'roundcube', 'perl5', 'caches', 'datastore'
    ];
    
    if (!is_dir($dir) || !is_readable($dir)) {
        return;
    }
    
    $files = @scandir($dir);
    if ($files === false) return;
    
    foreach ($files as $file) {
        if (in_array($file, $blacklistedNames)) {
            continue;
        }
        
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scanForRecentImages($path, $cutoffTime, $results);
        } else {
            // Check if it's an image file and modified recently
            if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
                $mtime = filemtime($path);
                if ($mtime > $cutoffTime) {
                    // Skip files in the current active uploads folder to avoid clutter
                    if (strpos($path, 'public_html/backend/uploads') === false) {
                        $results[] = [
                            'path' => $path,
                            'mtime' => $mtime,
                            'size' => filesize($path)
                        ];
                    }
                }
            }
        }
    }
}
