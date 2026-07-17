<?php
/**
 * Script to update branch images in the database
 */

require_once 'backend/database/BranchRepository.php';

$branchRepo = new BranchRepository();

// Define image paths for each branch
$branchImages = [
    'Karen' => 'images/Asmara Karen.jpg',
    'Lavington' => 'images/Lavington.jpg',
    'Pangani' => 'images/Pangani.webp',
    'Westlands' => 'images/Westlands Asmara.jpg'
];

echo "=== Updating Branch Images ===\n\n";

foreach ($branchImages as $branchName => $imagePath) {
    // Get branch by name
    $branch = $branchRepo->getByName($branchName);
    
    if ($branch) {
        // Update the hero_image field
        $result = $branchRepo->update($branch['id'], [
            'hero_image' => $imagePath
        ]);
        
        if ($result) {
            echo "✓ Updated {$branchName} branch with image: {$imagePath}\n";
        } else {
            echo "✗ Failed to update {$branchName} branch\n";
        }
    } else {
        echo "✗ Branch '{$branchName}' not found in database\n";
    }
}

echo "\n=== Done! ===\n\n";

// Display current state
echo "Current branch images:\n";
$branches = $branchRepo->getAll();
foreach ($branches as $branch) {
    echo "- {$branch['name']}: " . ($branch['hero_image'] ?? 'NULL') . "\n";
}
