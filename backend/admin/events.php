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

function save_event_image_upload($fieldName, $existingPath = '', &$uploadError = '') {
    if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return $existingPath;
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        $uploadError = 'Image upload failed (error ' . $_FILES[$fieldName]['error'] . ').';
        return $existingPath;
    }

    $uploadDir = __DIR__ . '/../uploads/events';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

    $ext      = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        $uploadError = 'Please upload a JPG, JPEG, PNG, GIF, or WebP image.';
        return $existingPath;
    }

    // Try GD compression first (produces WebP/JPEG at ~82 quality)
    $compressed = asmara_compress_and_save_image($_FILES[$fieldName]['tmp_name'], $uploadDir);
    if ($compressed !== '') {
        return $compressed;
    }

    // GD not available — plain move
    $filename    = uniqid('event_') . '.' . $ext;
    $destination = $uploadDir . '/' . $filename;
    $moved = move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination);
    if (!$moved && is_file($_FILES[$fieldName]['tmp_name'])) {
        $moved = copy($_FILES[$fieldName]['tmp_name'], $destination);
    }
    if (!$moved) {
        $uploadError = 'Could not save the image on the server.';
        return $existingPath;
    }
    @chmod($destination, 0644);
    return '/backend/uploads/events/' . $filename;
}

/**
 * Handle multi-file gallery upload field.
 * Returns array of saved public paths merged with any existing paths.
 */
function save_event_gallery_uploads(array $existingGallery = []): array {
    if (empty($_FILES['gallery_images']['name'][0])) {
        return $existingGallery;
    }

    $uploadDir = __DIR__ . '/../uploads/events';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $saved   = $existingGallery;

    foreach ($_FILES['gallery_images']['tmp_name'] as $i => $tmpPath) {
        if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) continue;

        // Compress via GD
        $compressed = asmara_compress_and_save_image($tmpPath, $uploadDir);
        if ($compressed !== '') {
            $saved[] = $compressed;
            continue;
        }

        // Plain move fallback
        $filename = uniqid('evt_g_') . '.' . $ext;
        $dest     = $uploadDir . '/' . $filename;
        if (move_uploaded_file($tmpPath, $dest) || copy($tmpPath, $dest)) {
            @chmod($dest, 0644);
            $saved[] = '/backend/uploads/events/' . $filename;
        }
    }
    return $saved;
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

    if ($action_type === 'create') {
        $uploadError   = '';
        $uploadedImage = save_event_image_upload('image', '', $uploadError);
        $gallery       = save_event_gallery_uploads();
        // If no separate cover image was uploaded, use first gallery image as cover
        if ($uploadedImage === '' && !empty($gallery)) {
            $uploadedImage = $gallery[0];
        }
        $newEvent = [
            'id'              => uniqid('evt_'),
            'title'           => $_POST['title'] ?? '',
            'description'     => $_POST['description'] ?? '',
            'category'        => $_POST['category'] ?? '',
            'capacity'        => (int)($_POST['capacity'] ?? 0),
            'price_per_person'=> (float)($_POST['price_per_person'] ?? 0),
            'venue'           => $_POST['venue'] ?? '',
            'services'        => $_POST['services'] ?? '',
            'event_date'      => $_POST['event_date'] ?? '',
            'image'           => $uploadedImage,
            'gallery'         => $gallery,
            'created_at'      => date('Y-m-d H:i:s'),
            'status'          => 'active'
        ];

        $events[] = $newEvent;
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        Auth::logActivity(Auth::getCurrentUserId(), 'created', 'events', $newEvent['id']);
        $message = 'Event created successfully!';
        if ($uploadError !== '') $message .= ' ' . $uploadError;
        $action = 'list';

    } elseif ($action_type === 'update') {
        $event_id = $_POST['id'] ?? '';
        foreach ($events as &$event) {
            if ($event['id'] === $event_id) {
                $uploadError  = '';
                $currentImage = $event['image'] ?? '';
                $newImage     = save_event_image_upload('image', $currentImage, $uploadError);

                // Gallery: keep existing + append newly uploaded
                $existingGallery = $event['gallery'] ?? [];
                // Handle removed images (from hidden remove_gallery[] inputs)
                $toRemove = $_POST['remove_gallery'] ?? [];
                $existingGallery = array_values(array_filter($existingGallery, fn($p) => !in_array($p, $toRemove, true)));
                $newGallery = save_event_gallery_uploads($existingGallery);

                // If cover image was cleared/unchanged and no dedicated cover, use first gallery photo
                if ($newImage === '' && !empty($newGallery)) {
                    $newImage = $newGallery[0];
                }

                $event['title']            = $_POST['title'] ?? $event['title'];
                $event['description']      = $_POST['description'] ?? $event['description'];
                $event['category']         = $_POST['category'] ?? $event['category'];
                $event['capacity']         = (int)($_POST['capacity'] ?? $event['capacity']);
                $event['price_per_person'] = (float)($_POST['price_per_person'] ?? $event['price_per_person']);
                $event['venue']            = $_POST['venue'] ?? $event['venue'];
                $event['services']         = $_POST['services'] ?? $event['services'];
                $event['updated_at']       = date('Y-m-d H:i:s');
                $event['event_date']       = $_POST['event_date'] ?? $event['event_date'];
                $event['image']            = $newImage;
                $event['gallery']          = $newGallery;
                break;
            }
        }
        unset($event);
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'events', $event_id);
        $message = 'Event updated successfully!';
        if (!empty($uploadError)) $message .= ' ' . $uploadError;
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
                                <label>Cover Image</label>
                                <div style="display:grid; gap:12px;">
                                    <div id="event-image-preview" style="aspect-ratio: 16/9; overflow:hidden; border-radius:12px; border:1px solid var(--color-border); background:#f8fafc; display:flex; align-items:center; justify-content:center;">
                                        <?php $editEventImageUrl = !empty($edit_event) ? asmara_event_image_url($edit_event) : ''; ?>
                                        <?php if (!empty($editEventImageUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($editEventImageUrl); ?>" alt="Current event cover" style="width:100%; height:100%; object-fit:cover; display:block;">
                                        <?php else: ?>
                                            <div style="text-align:center; color:var(--color-text-muted); padding:16px;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin-bottom:8px;"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15l-5-5-4 4-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <div>No cover image yet</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" name="image" id="event-image-input" accept="image/*">
                                </div>
                                <p style="font-size:12px; color:var(--color-text-muted); margin-top:8px;">Images are automatically compressed &amp; optimised on upload (WebP/JPEG, max 1920px).</p>
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

                            <!-- ===== GALLERY UPLOAD ===== -->
                            <div class="form-group">
                                <label>Event Gallery Photos</label>
                                <p style="font-size:12px; color:var(--color-text-muted); margin-bottom:10px;">
                                    Select multiple photos for the gallery. All images are auto-compressed on upload &mdash; great quality, much smaller files.
                                </p>

                                <?php if (!empty($edit_event['gallery'])): ?>
                                <div style="margin-bottom:14px;">
                                    <span style="font-size:12px; font-weight:600; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:.5px;">Current Gallery (<?php echo count($edit_event['gallery']); ?> photos)</span>
                                    <div id="existing-gallery-strip" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
                                        <?php foreach ($edit_event['gallery'] as $gPath):
                                            $gUrl = asmara_event_image_url(['image' => $gPath]);
                                            if (!$gUrl) continue;
                                        ?>
                                        <div class="gallery-thumb-wrapper" style="position:relative; width:110px; height:80px; border-radius:8px; overflow:hidden; border:1px solid var(--color-border);">
                                            <img src="<?php echo htmlspecialchars($gUrl); ?>" alt="Gallery photo" style="width:100%; height:100%; object-fit:cover; display:block;" loading="lazy" fetchpriority="low">
                                            <label style="position:absolute; top:4px; right:4px; background:rgba(239,68,68,.85); border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#fff; font-size:11px; font-weight:700;" title="Remove photo">
                                                <input type="checkbox" name="remove_gallery[]" value="<?php echo htmlspecialchars($gPath); ?>" style="display:none;" onchange="toggleRemoveThumb(this)">
                                                &#x2715;
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <p style="font-size:11px; color:var(--color-text-muted); margin-top:6px;">Click &#x2715; on any photo to remove it on save.</p>
                                </div>
                                <?php endif; ?>

                                <input type="file" name="gallery_images[]" id="gallery-images-input" accept="image/*" multiple style="display:block; margin-bottom:10px;">
                                <div id="gallery-preview-strip" style="display:flex; flex-wrap:wrap; gap:10px; min-height:0;"></div>
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

                            <select id="event-branch-filter" class="filter-select" onchange="filterEvents()">
                                <option value="">All Branches</option>
                                <option value="pangani">Pangani</option>
                                <option value="westlands">Westlands</option>
                                <option value="karen">Karen</option>
                                <option value="lavington">Lavington</option>
                            </select>

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
                                    <?php $eventImageUrl = asmara_event_image_url($event); ?>
                                    <div class="event-card-premium" 
                                         data-category="<?php echo htmlspecialchars($event['category']); ?>"
                                         data-branch="<?php echo htmlspecialchars(strtolower($event['venue'] ?? '')); ?>"
                                         data-title="<?php echo htmlspecialchars(strtolower($event['title'])); ?>">
                                        <div class="event-card-header">
                                            <?php if (!empty($eventImageUrl)): ?>
                                                <div style="width:100%; height:180px; overflow:hidden; border-radius:12px; margin-bottom:12px; background:#f8fafc; position:relative;">
                                                    <img src="<?php echo htmlspecialchars($eventImageUrl); ?>" alt="<?php echo htmlspecialchars($event['title'] ?? 'Event image'); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                                                    <?php $galleryCount = count($event['gallery'] ?? []); ?>
                                                    <?php if ($galleryCount > 1): ?>
                                                    <span style="position:absolute; bottom:8px; right:8px; background:rgba(0,0,0,.6); color:#fff; font-size:11px; padding:3px 8px; border-radius:99px;">&#128247; <?php echo $galleryCount; ?> photos</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
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
                            const searchVal   = document.getElementById('event-search-input').value.toLowerCase();
                            const categoryVal = document.getElementById('event-category-filter').value;
                            const branchVal   = document.getElementById('event-branch-filter') ? document.getElementById('event-branch-filter').value : '';
                            const cards       = document.querySelectorAll('.event-card-premium');

                            cards.forEach(card => {
                                const title    = card.getAttribute('data-title');
                                const category = card.getAttribute('data-category');
                                const branch   = card.getAttribute('data-branch') || '';

                                const ok = title.includes(searchVal)
                                    && (categoryVal === '' || category === categoryVal)
                                    && (branchVal   === '' || branch   === branchVal);

                                card.style.display = ok ? '' : 'none';
                            });
                        }

                        function setEventViewMode(mode) {
                            const grid    = document.getElementById('eventsDisplayGrid');
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

                        function toggleRemoveThumb(checkbox) {
                            const wrapper = checkbox.closest('.gallery-thumb-wrapper');
                            if (wrapper) {
                                wrapper.style.opacity = checkbox.checked ? '0.35' : '1';
                                wrapper.style.outline = checkbox.checked ? '2px solid #ef4444' : '';
                            }
                        }
                    </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Cover image preview
                            const coverInput   = document.getElementById('event-image-input');
                            const coverPreview = document.getElementById('event-image-preview');
                            if (coverInput && coverPreview) {
                                coverInput.addEventListener('change', function() {
                                    const file = this.files && this.files[0];
                                    if (!file) return;
                                    const reader = new FileReader();
                                    reader.onload = e => {
                                        coverPreview.innerHTML = '';
                                        const img = document.createElement('img');
                                        img.src = e.target.result;
                                        img.alt = 'Cover preview';
                                        Object.assign(img.style, { width:'100%', height:'100%', objectFit:'cover', display:'block' });
                                        coverPreview.appendChild(img);
                                    };
                                    reader.readAsDataURL(file);
                                });
                            }

                            // Gallery multi-image preview strip
                            const galleryInput = document.getElementById('gallery-images-input');
                            const galleryStrip = document.getElementById('gallery-preview-strip');
                            if (galleryInput && galleryStrip) {
                                galleryInput.addEventListener('change', function() {
                                    galleryStrip.innerHTML = '';
                                    Array.from(this.files).forEach(file => {
                                        const thumb = document.createElement('div');
                                        thumb.style.cssText = 'width:110px;height:80px;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;flex-shrink:0;';
                                        const img = document.createElement('img');
                                        img.alt = file.name;
                                        img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
                                        const reader = new FileReader();
                                        reader.onload = e => { img.src = e.target.result; };
                                        reader.readAsDataURL(file);
                                        thumb.appendChild(img);
                                        galleryStrip.appendChild(thumb);
                                    });
                                });
                            }
                        });
                    </script>

                <?php endif; ?>
            </div>


<?php include 'footer.php'; ?>
