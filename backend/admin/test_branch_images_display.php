<?php
require_once __DIR__ . '/../database/BranchRepository.php';

$branchRepo = new BranchRepository();
$branches = $branchRepo->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Branch Images Display Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test-card { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; }
        h2 { margin-top: 0; color: #333; }
        .image-container { margin: 15px 0; }
        img { max-width: 400px; border: 2px solid #ddd; border-radius: 8px; display: block; margin: 10px 0; }
        .success { border-color: #10b981 !important; }
        .error { border-color: #ef4444 !important; }
        .info { background: #f0f9ff; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 14px; }
        .path { font-family: monospace; background: #f3f4f6; padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Branch Images Display Test</h1>
    
    <?php foreach ($branches as $branch): ?>
        <div class="test-card">
            <h2><?= htmlspecialchars($branch['name']) ?> Branch</h2>
            
            <div class="info">
                <strong>Database Path:</strong> <span class="path"><?= htmlspecialchars($branch['hero_image'] ?? 'NULL') ?></span>
            </div>
            
            <?php if (!empty($branch['hero_image'])): ?>
                <?php
                    $dbPath = $branch['hero_image'];
                    
                    // Test 1: Relative path from admin (what's used in actual page)
                    if (strpos($dbPath, 'images/') === 0) {
                        $parts = explode('/', $dbPath);
                        $filename = array_pop($parts);
                        $relativePath = '../../frontend/' . implode('/', $parts) . '/' . rawurlencode($filename);
                    } else {
                        $relativePath = $dbPath;
                    }
                    
                    // Test 2: Check if file exists
                    $physicalPath = __DIR__ . '/../../frontend/' . $dbPath;
                    $fileExists = file_exists($physicalPath);
                ?>
                
                <div class="info">
                    <strong>Physical Path:</strong> <span class="path"><?= htmlspecialchars($physicalPath) ?></span><br>
                    <strong>File Exists:</strong> <?= $fileExists ? '✓ YES' : '✗ NO' ?><br>
                    <strong>Relative URL:</strong> <span class="path"><?= htmlspecialchars($relativePath) ?></span>
                </div>
                
                <div class="image-container">
                    <strong>Image Preview:</strong>
                    <img src="<?= htmlspecialchars($relativePath) ?>" 
                         alt="<?= htmlspecialchars($branch['name']) ?>" 
                         id="img-<?= $branch['id'] ?>"
                         onload="this.classList.add('success')" 
                         onerror="this.classList.add('error'); this.alt='Failed to load';">
                </div>
            <?php else: ?>
                <div class="info" style="background: #fff3cd; color: #856404;">
                    No image set in database
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <script>
        // Check all images after page load
        window.addEventListener('load', function() {
            document.querySelectorAll('img').forEach(function(img) {
                if (img.complete && img.naturalHeight === 0) {
                    img.classList.add('error');
                    console.error('Failed to load:', img.src);
                } else if (img.complete) {
                    img.classList.add('success');
                    console.log('Successfully loaded:', img.src);
                }
            });
        });
    </script>
</body>
</html>
