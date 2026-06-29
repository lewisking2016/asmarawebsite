<?php
/**
 * Events Management Page
 * Manage restaurant events and special occasions
 */

$page_title = 'Events Management | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';
require_once __DIR__ . '/../data/event_helpers.php';

Auth::requireLogin();

function save_event_image_upload($fieldName, $existingPath = '') {
    if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return $existingPath;
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return $existingPath;
    }

    $uploadDir = __DIR__ . '/../uploads/events';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $originalName = basename($_FILES[$fieldName]['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $allowed, true)) {
        return $existingPath;
    }

    $filename = uniqid('event_') . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
        return $existingPath;
    }

    return '/backend/uploads/events/' . $filename;
}


// Events data (stored as JSON for simplicity or can be extended to database table)
$eventsFile = __DIR__ . '/../data/events.json';
$events = [];
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';
$edit_event = null;

// Ensure data directory exists
if (!is_dir(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0755, true);
}

// Load events from JSON
if (file_exists($eventsFile)) {
    $eventsJson = json_decode(file_get_contents($eventsFile), true);
    $events = $eventsJson ?? [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';
    $uploadedImage = save_event_image_upload('image');

    if ($action_type === 'create') {
        $newEvent = [
            'id' => uniqid('evt_'),
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'capacity' => (int)($_POST['capacity'] ?? 0),
            'price_per_person' => (float)($_POST['price_per_person'] ?? 0),
            'venue' => $_POST['venue'] ?? '',
            'services' => $_POST['services'] ?? '',
            'event_date' => $_POST['event_date'] ?? '',
            'image' => $uploadedImage,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];

        $events[] = $newEvent;
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        Auth::logActivity(Auth::getCurrentUserId(), 'created', 'events', $newEvent['id']);
        $message = 'Event created successfully!';
        $action = 'list';

    } elseif ($action_type === 'update') {
        $event_id = $_POST['id'] ?? '';
        foreach ($events as &$event) {
            if ($event['id'] === $event_id) {
                $event['title'] = $_POST['title'] ?? $event['title'];
                $event['description'] = $_POST['description'] ?? $event['description'];
                $event['category'] = $_POST['category'] ?? $event['category'];
                $event['capacity'] = (int)($_POST['capacity'] ?? $event['capacity']);
                $event['price_per_person'] = (float)($_POST['price_per_person'] ?? $event['price_per_person']);
                $event['venue'] = $_POST['venue'] ?? $event['venue'];
                $event['services'] = $_POST['services'] ?? $event['services'];
                $event['updated_at'] = date('Y-m-d H:i:s');
                $event['event_date'] = $_POST['event_date'] ?? $event['event_date'];
                $event['image'] = save_event_image_upload('image', $event['image'] ?? '');
                break;
            }
        }
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'events', $event_id);
        $message = 'Event updated successfully!';
        $action = 'list';

    } elseif ($action_type === 'delete') {
        $event_id = $_POST['id'] ?? '';
        $events = array_filter($events, function($e) use ($event_id) {
            return $e['id'] !== $event_id;
        });
        $events = array_values($events); // Reindex array
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        Auth::logActivity(Auth::getCurrentUserId(), 'deleted', 'events', $event_id);
        $message = 'Event deleted successfully!';
        $action = 'list';
    }
}

// Get edit event
if ($action === 'edit' && isset($_GET['id'])) {
    foreach ($events as $event) {
        if ($event['id'] === $_GET['id']) {
            $edit_event = $event;
            break;
        }
    }
}

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Events Management</h1>
                <p class="page-subtitle">Manage restaurant events and special occasions</p>
            </div>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'create' || $action === 'edit'): ?>
                    <!-- ADD/EDIT FORM -->
                    <div class="form-container">
                        <a href="?action=list" class="back-link">← Back to Events</a>

                        <h3><?php echo $action === 'create' ? 'Add New Event' : 'Edit Event'; ?></h3>
                        
                        <form method="POST" class="event-form" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                            <?php if ($action === 'edit' && $edit_event): ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_event['id']); ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Event Title *</label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    placeholder="e.g., Corporate Gala Dinner"
                                    value="<?php echo htmlspecialchars($edit_event['title'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea 
                                    name="description" 
                                    placeholder="Describe this event..."
                                    rows="4"
                                ><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-row">
                            <div class="form-group">
                                <label>Event Image</label>
                                <input type="file" name="image" accept="image/*">
                                <p style="font-size:12px; color:var(--color-text-muted); margin-top:8px;">Upload a JPG, PNG, GIF, or WebP image for the event card.</p>
                            </div>
                                <div class="form-group">
                                    <label>Category *</label>
                                    <select name="category" required>
                                        <option value="">Select category</option>
                                        <option value="corporate" <?php echo ($edit_event['category'] ?? '') === 'corporate' ? 'selected' : ''; ?>>Corporate Events</option>
                                        <option value="social" <?php echo ($edit_event['category'] ?? '') === 'social' ? 'selected' : ''; ?>>Social Celebrations</option>
                                        <option value="catering" <?php echo ($edit_event['category'] ?? '') === 'catering' ? 'selected' : ''; ?>>Catering Services</option>
                                        <option value="wedding" <?php echo ($edit_event['category'] ?? '') === 'wedding' ? 'selected' : ''; ?>>Weddings</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Venue *</label>
                                    <select name="venue" required>
                                        <option value="">Select venue</option>
                                        <option value="pangani" <?php echo ($edit_event['venue'] ?? '') === 'pangani' ? 'selected' : ''; ?>>Pangani</option>
                                        <option value="westlands" <?php echo ($edit_event['venue'] ?? '') === 'westlands' ? 'selected' : ''; ?>>Westlands</option>
                                        <option value="karen" <?php echo ($edit_event['venue'] ?? '') === 'karen' ? 'selected' : ''; ?>>Karen</option>
                                        <option value="lavington" <?php echo ($edit_event['venue'] ?? '') === 'lavington' ? 'selected' : ''; ?>>Lavington</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Guest Capacity *</label>
                                    <input 
                                        type="number" 
                                        name="capacity" 
                                        placeholder="e.g., 50"
                                        min="1"
                                        value="<?php echo htmlspecialchars($edit_event['capacity'] ?? ''); ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Price Per Person (KES) *</label>
                                    <input 
                                        type="number" 
                                        name="price_per_person" 
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        value="<?php echo htmlspecialchars($edit_event['price_per_person'] ?? ''); ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Event Date *</label>
                                <input 
                                    type="date" 
                                    name="event_date" 
                                    value="<?php echo htmlspecialchars($edit_event['event_date'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label>Available Services</label>
                                <textarea 
                                    name="services" 
                                    placeholder="List services: Buffet, Cocktail, DJ, Decorations, etc."
                                    rows="3"
                                ><?php echo htmlspecialchars($edit_event['services'] ?? ''); ?></textarea>
                            </div>

                            <div class="action-buttons" style="gap: 12px; margin-top: 24px;">
                                <button type="submit" class="action-btn">
                                    <?php echo $action === 'create' ? '+ Create Event' : '✓ Update Event'; ?>
                                </button>
                                <a href="?action=list" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- EVENTS LIST WITH SAAS CONTROLS -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <a href="?action=create" class="action-btn">+ New Event</a>

                            <input 
                                type="text" 
                                id="event-search-input" 
                                placeholder="Search events..." 
                                class="filter-search-input"
                                onkeyup="filterEvents()"
                            >

                            <select id="event-category-filter" class="filter-select" onchange="filterEvents()">
                                <option value="">All Categories</option>
                                <option value="corporate">Corporate Events</option>
                                <option value="social">Social Celebrations</option>
                                <option value="catering">Catering Services</option>
                                <option value="wedding">Weddings</option>
                            </select>
                        </div>

                        <div class="view-toggle-container">
                            <button class="view-toggle-btn active" id="btnEventCardView" title="Card View" onclick="setEventViewMode('card')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button class="view-toggle-btn" id="btnEventListView" title="List View" onclick="setEventViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($events)): ?>
                        <div class="empty-state">
                            <p>No events found. <a href="?action=create">Create your first event</a></p>
                        </div>
                    <?php else: ?>
                        <div class="items-display-wrapper">
                            <div class="events-grid card-view-mode" id="eventsDisplayGrid">
                                <?php foreach ($events as $event): ?>
                                    <div class="event-card-premium" 
                                         data-category="<?php echo htmlspecialchars($event['category']); ?>"
                                         data-title="<?php echo htmlspecialchars(strtolower($event['title'])); ?>">
                                        <div class="event-card-header">
                                            <div class="event-card-icon">
                                                <?php
                                                $icon = '🎉';
                                                if ($event['category'] === 'corporate') $icon = '🏢';
                                                elseif ($event['category'] === 'wedding') $icon = '💍';
                                                elseif ($event['category'] === 'catering') $icon = '🍽️';
                                                echo $icon;
                                                ?>
                                            </div>
                                            <div>
                                                <span class="status-badge" style="background:rgba(0,0,0,0.05); color:var(--color-text); padding:4px 10px; font-size:11px;"><?php echo ucfirst($event['category']); ?></span>
                                                <span class="status-badge status-completed"><?php echo ucfirst($event['status'] ?? 'active'); ?></span>
                                            </div>
                                        </div>

                                        <div class="event-card-body">
                                            <div class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <p class="event-card-description"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 100)); ?><?php echo strlen($event['description'] ?? '') > 100 ? '...' : ''; ?></p>
                                            
                                            <div class="event-card-details">
                                                <div class="event-detail-row">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    <span><?php echo $event['capacity']; ?> guests</span>
                                                </div>
                                                <div class="event-detail-row">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                                    <span>KES <?php echo number_format($event['price_per_person'], 2); ?>/person</span>
                                                </div>
                                                <div class="event-detail-row">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span><?php echo ucfirst($event['venue']); ?></span>
                                                </div>
                                                <div class="event-detail-row">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/></svg>
                                                    <span><?php echo htmlspecialchars(asmara_event_date_label($event)); ?></span>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="event-card-footer">
                                            <div style="display:flex; gap:8px; width:100%;">
                                                <a href="?action=edit&id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-secondary" style="flex:1; padding:10px; font-size:13px; text-align:center;">Edit</a>
                                                <form method="POST" style="display:inline; flex:1;" onsubmit="return confirm('Delete this event?');">
                                                    <input type="hidden" name="action_type" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                                    <button type="submit" class="btn btn-secondary" style="width:100%; padding:10px; font-size:13px; color:var(--color-danger); border-color:rgba(239,68,68,0.15);">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <script>
                        function filterEvents() {
                            const searchVal = document.getElementById('event-search-input').value.toLowerCase();
                            const categoryVal = document.getElementById('event-category-filter').value;
                            const cards = document.querySelectorAll('.event-card-premium');

                            cards.forEach(card => {
                                const title = card.getAttribute('data-title');
                                const category = card.getAttribute('data-category');

                                const matchesSearch = title.includes(searchVal);
                                const matchesCategory = categoryVal === '' || category === categoryVal;

                                if (matchesSearch && matchesCategory) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }

                        function setEventViewMode(mode) {
                            const grid = document.getElementById('eventsDisplayGrid');
                            const btnCard = document.getElementById('btnEventCardView');
                            const btnList = document.getElementById('btnEventListView');

                            if (mode === 'list') {
                                grid.className = 'events-grid list-view-mode';
                                btnList.classList.add('active');
                                btnCard.classList.remove('active');
                            } else {
                                grid.className = 'events-grid card-view-mode';
                                btnCard.classList.add('active');
                                btnList.classList.remove('active');
                            }
                        }
                    </script>

                <?php endif; ?>
            </div>


<?php include 'footer.php'; ?>
