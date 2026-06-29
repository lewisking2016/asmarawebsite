<?php
/**
 * AI Data API Endpoint
 * Exposes clean, structured JSON data representing branches, menus, and events 
 * for search engine indexers, scraper bots, and AI agents.
 */
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../backend/database/BranchRepository.php';
require_once __DIR__ . '/../backend/database/MenuRepository.php';
require_once __DIR__ . '/../backend/data/event_helpers.php';

$branchRepo = new BranchRepository();
$menuRepo = new MenuRepository();

// 1. Get Branches
$rawBranches = $branchRepo->getAll();
$branches = [];
foreach ($rawBranches as $b) {
    $branches[] = [
        'id' => (int)$b['id'],
        'name' => $b['name'],
        'address' => $b['address'],
        'phone' => $b['phone'],
        'email' => $b['email'],
        'opening_hours' => $b['opening_hours'] ?? '10:00 AM - 11:00 PM',
        'capacity' => (int)($b['capacity'] ?? 50),
        'subtitle' => $b['subtitle'] ?? '',
        'summary' => $b['summary'] ?? '',
        'description' => $b['long_description'] ?? ''
    ];
}

// 2. Get Menu Items
$rawMenu = $menuRepo->getAll(true); // available items only
$menu = [];
foreach ($rawMenu as $item) {
    $itemBranchIds = [];
    if (empty($item['available_branches'])) {
        foreach ($branches as $b) {
            $itemBranchIds[] = $b['id'];
        }
    } else {
        $decoded = json_decode($item['available_branches'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $id) {
                $itemBranchIds[] = (int)$id;
            }
        }
    }

    $menu[] = [
        'id' => (int)$item['id'],
        'name' => $item['name'],
        'description' => $item['description'] ?? '',
        'category' => $item['category'],
        'price' => (float)$item['price'],
        'available_branch_ids' => $itemBranchIds
    ];
}

// 3. Get Events
$eventsFile = __DIR__ . '/../backend/data/events.json';
$events = [];
if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (is_array($eventsData)) {
        foreach ($eventsData as $evt) {
            $events[] = [
                'title' => $evt['title'] ?? '',
                'description' => $evt['description'] ?? '',
                'category' => $evt['category'] ?? 'corporate',
                'venue' => $evt['venue'] ?? 'All Locations',
                'capacity' => (int)($evt['capacity'] ?? 0),
                'price_per_person' => (float)($evt['price_per_person'] ?? 0),
                'services' => is_array($evt['services'] ?? '') ? $evt['services'] : explode(',', $evt['services'] ?? ''),
                'event_date' => $evt['event_date'] ?? '',
                'image' => $evt['image'] ?? ''
            ];
        }
    }
}

$events = asmara_filter_upcoming_events($events);
// Output final structured payload
echo json_encode([
    'restaurant' => 'Asmara Restaurant',
    'last_updated' => date('c'),
    'branches' => $branches,
    'menu_items' => $menu,
    'events' => $events
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
