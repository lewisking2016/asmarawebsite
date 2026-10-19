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

/* --- Status & summary stats for the listing view --------------- */
$totalEvents         = is_array($events) ? count($events) : 0;
$activeEventsCount   = 0;
$inactiveEventsCount = 0;
$expiredEventsCount  = 0;
$featuredEventsCount = 0;
if (is_array($events)) {
    foreach ($events as $e) {
        $st = $e['status'] ?? 'active';
        if ($st === 'active') $activeEventsCount++;
        elseif ($st === 'inactive') $inactiveEventsCount++;
        elseif ($st === 'expired') $expiredEventsCount++;
        if (!empty($e['image'])) $featuredEventsCount++;
    }
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
            'id'               => uniqid('evt_'),
            'title'            => $_POST['title'] ?? '',
            'description'      => $_POST['description'] ?? '',
            'category'         => $_POST['category'] ?? '',
            'capacity'         => $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null,
            'price_per_person' => $_POST['price_per_person'] !== '' ? (float)$_POST['price_per_person'] : null,
            'venue'            => $_POST['venue'] ?? '',
            'services'         => $_POST['services'] ?? '',
            'start_date'       => trim((string)($_POST['start_date'] ?? '')),
            'end_date'         => trim((string)($_POST['end_date']   ?? '')),
            // Backward-compat alias — the singular "event date" is treated as the end date (or start if no end).
            'event_date'       => trim((string)($_POST['end_date']   ?? $_POST['start_date'] ?? '')),
            'image'            => $uploadedImage,
            'gallery'          => $gallery,
            'registration_link'=> trim($_POST['registration_link'] ?? ''),
            'created_at'       => date('Y-m-d H:i:s'),
            'status'           => in_array($_POST['status'] ?? 'active', ['active','inactive','expired'], true)
                                    ? $_POST['status'] : 'active'
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

                // Cover image may be explicitly removed via the "remove_image" hidden input
                $removeCover  = !empty($_POST['remove_image']) && $currentImage !== '';
                $existingImage = $removeCover ? '' : $currentImage;
                $newImage      = save_event_image_upload('image', $existingImage, $uploadError);

                // Gallery: keep existing + append newly uploaded
                $existingGallery = $event['gallery'] ?? [];
                // Handle removed images (from hidden remove_gallery[] inputs)
                $toRemove = $_POST['remove_gallery'] ?? [];
                $existingGallery = array_values(array_filter($existingGallery, fn($p) => !in_array($p, $toRemove, true)));
                $newGallery = save_event_gallery_uploads($existingGallery);

                // If cover was cleared AND a removal was requested, also drop the cover
                // from the gallery array if it happened to be there.
                if ($removeCover) {
                    $newGallery = array_values(array_filter($newGallery, fn($p) => $p !== $currentImage));
                }

                // If cover image is empty and gallery exists, promote first gallery item as cover
                if ($newImage === '' && !empty($newGallery)) {
                    $newImage = $newGallery[0];
                }

                $event['title']             = $_POST['title'] ?? $event['title'];
                $event['description']       = $_POST['description'] ?? $event['description'];
                $event['category']          = $_POST['category'] ?? $event['category'];
                $event['capacity']          = array_key_exists('capacity', $_POST) && $_POST['capacity'] !== ''
                                                ? (int)$_POST['capacity']
                                                : ($_POST['capacity'] === '' ? null : ($event['capacity'] ?? null));
                $event['price_per_person']  = array_key_exists('price_per_person', $_POST) && $_POST['price_per_person'] !== ''
                                                ? (float)$_POST['price_per_person']
                                                : ($_POST['price_per_person'] === '' ? null : ($event['price_per_person'] ?? null));
                $event['venue']             = $_POST['venue'] ?? $event['venue'];
                $event['services']          = $_POST['services'] ?? $event['services'];
                $event['updated_at']        = date('Y-m-d H:i:s');

                // Dates: store both start and end. The legacy event_date
                // mirrors end (or start) for older consumers.
                $startDate = trim((string)($_POST['start_date'] ?? ($event['start_date'] ?? '')));
                $endDate   = trim((string)($_POST['end_date']   ?? ($event['end_date'] ?? '')));
                // If only one of the two is filled, mirror it to the other.
                if ($startDate !== '' && $endDate === '') $endDate = $startDate;
                if ($endDate !== '' && $startDate === '') $startDate = $endDate;
                $event['start_date']        = $startDate;
                $event['end_date']          = $endDate;
                $event['event_date']        = $endDate !== '' ? $endDate : $startDate;

                $event['image']             = $newImage;
                $event['gallery']           = $newGallery;
                $event['registration_link'] = trim($_POST['registration_link'] ?? ($event['registration_link'] ?? ''));
                $newStatus = $_POST['status'] ?? '';
                if (in_array($newStatus, ['active','inactive','expired'], true)) {
                    $event['status'] = $newStatus;
                }
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

            <div class="ad-page-header">
                <span class="ad-page-eyebrow">✦ Events module</span>
                <h1>Events Management</h1>
                <p>Plan, schedule and feature restaurant events — corporate gatherings, social celebrations, weddings and catering services.</p>
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
                    <div class="ad-card ad-card-tinted ad-card-padded-lg" style="max-width: 980px; margin: 0 auto;">
                        <a href="?action=list" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--ad-rose); text-decoration:none; margin-bottom:16px;">← Back to Events</a>

                        <div class="ad-card-header" style="border:0; padding: 4px 0 22px;">
                            <div>
                                <span class="ad-page-eyebrow" style="margin-bottom:8px;"><?php echo $action === 'edit' ? 'Editing' : 'New Event'; ?></span>
                                <h3 style="font-family:'Playfair Display', Georgia, serif; font-size:26px; line-height:1.15; color:var(--ad-ink); margin:0;">
                                    <?php echo $action === 'create' ? 'Add a New Event' : 'Edit Event Details'; ?>
                                </h3>
                                <div class="ad-card-sub" style="margin-top:6px;">Fill out the essentials — you can publish a draft with the bare minimum and add photos later.</div>
                            </div>
                            <?php if ($action === 'edit' && $edit_event): ?>
                                <span class="ad-chip ad-chip-ink"><span class="ad-chip-dot"></span>ID · <?php echo htmlspecialchars(substr($edit_event['id'], -8)); ?></span>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="event-form" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                            <?php if ($action === 'edit' && $edit_event): ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_event['id']); ?>">
                            <?php endif; ?>

                            <div class="ad-field">
                                <label class="ad-label ad-required" for="event-title">Event Title</label>
                                <input id="event-title" class="ad-input" type="text" name="title" placeholder="e.g., Corporate Gala Dinner" value="<?php echo htmlspecialchars($edit_event['title'] ?? ''); ?>" required>
                                <div class="ad-hint">A short, descriptive name visitors will see first.</div>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="event-description">Description</label>
                                <textarea id="event-description" class="ad-textarea" name="description" placeholder="Tell guests what makes this event special&hellip;" rows="4"><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>
                                <div class="ad-hint">Plain text. Keep it between 1–3 sentences.</div>
                            </div>

                            <?php
                                // Resolve current start/end with backward-compat fallback
                                $currentStartDate = $edit_event['start_date'] ?? ($edit_event['event_date'] ?? '');
                                $currentEndDate   = $edit_event['end_date']   ?? ($edit_event['event_date'] ?? '');
                            ?>

                            <div class="ad-grid-2">
                                <div class="ad-field">
                                    <label class="ad-label" for="event-start-date">Start Date</label>
                                    <input id="event-start-date" class="ad-input" type="date" name="start_date" value="<?php echo htmlspecialchars($currentStartDate); ?>">
                                    <div class="ad-hint">When the event begins. For single-day events, leave blank and just set an End Date.</div>
                                </div>

                                <div class="ad-field">
                                    <label class="ad-label" for="event-end-date">End Date</label>
                                    <input id="event-end-date" class="ad-input" type="date" name="end_date" value="<?php echo htmlspecialchars($currentEndDate); ?>">
                                    <div class="ad-hint">When the event finishes. Set the same as Start for a one-day event.</div>
                                </div>
                            </div>

                            <div class="ad-grid-2">
                                <div class="ad-field">
                                    <label class="ad-label" for="event-category">Category</label>
                                    <div class="ad-select-wrap">
                                        <select id="event-category" class="ad-select" name="category" required>
                                            <option value="">Choose a category</option>
                                            <?php
                                                $cats = [
                                                    'corporate' => 'Corporate Events',
                                                    'social'    => 'Social Celebrations',
                                                    'catering'  => 'Catering Services',
                                                    'wedding'   => 'Weddings',
                                                ];
                                                foreach ($cats as $val => $label):
                                            ?>
                                                <option value="<?php echo $val; ?>" <?php echo ($edit_event['category'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-hint">Helps guests filter by the kind of event they&rsquo;re looking for.</div>
                                </div>

                                <div class="ad-field">
                                    <label class="ad-label" for="event-venue">Venue</label>
                                    <div class="ad-select-wrap">
                                        <select id="event-venue" class="ad-select" name="venue">
                                            <option value="">Select venue (optional)</option>
                                            <option value="all"        <?php echo ($edit_event['venue'] ?? '') === 'all'        ? 'selected' : ''; ?>>All Branches</option>
                                            <option value="pangani"    <?php echo ($edit_event['venue'] ?? '') === 'pangani'    ? 'selected' : ''; ?>>Pangani</option>
                                            <option value="westlands"  <?php echo ($edit_event['venue'] ?? '') === 'westlands'  ? 'selected' : ''; ?>>Westlands</option>
                                            <option value="karen"      <?php echo ($edit_event['venue'] ?? '') === 'karen'      ? 'selected' : ''; ?>>Karen</option>
                                            <option value="lavington"  <?php echo ($edit_event['venue'] ?? '') === 'lavington'  ? 'selected' : ''; ?>>Lavington</option>
                                        </select>
                                    </div>
                                    <div class="ad-hint">Pick a specific branch, or &ldquo;All Branches&rdquo; if it&rsquo;s a chain-wide event.</div>
                                </div>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="event-image-input">Cover / Featured Image</label>
                                <div class="ad-cover-uploader">
                                    <div id="event-image-preview" class="ad-cover-preview">
                                        <?php $editEventImageUrl = !empty($edit_event) ? asmara_event_image_url($edit_event) : ''; ?>
                                        <?php if (!empty($editEventImageUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($editEventImageUrl); ?>" alt="Current event cover">
                                        <?php else: ?>
                                            <div class="ad-cover-empty">
                                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M21 15l-5-5-4 4-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <strong>Upload a featured photo</strong>
                                                <span>Drag &amp; drop or click to browse — WebP/JPEG, max 1920px</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ad-cover-actions">
                                        <input type="hidden" name="remove_image" id="event-image-remove-flag" value="">
                                        <label for="event-image-input" class="ad-btn ad-btn-ghost" style="cursor:pointer;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            <?php echo !empty($editEventImageUrl) ? 'Replace photo' : 'Choose photo'; ?>
                                        </label>
                                        <input type="file" name="image" id="event-image-input" accept="image/*" class="ad-cover-file">
                                        <?php if (!empty($editEventImageUrl)): ?>
                                            <button type="button" id="event-image-remove-btn" onclick="removeCoverImage()" class="ad-btn ad-btn-danger">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                                Remove
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ad-hint">The featured photo is the cover that appears at the top of the event card and the first slide in the gallery. Images are auto-compressed on upload.</div>
                                </div>
                            </div>

                            <div class="ad-grid-2">
                                <div class="ad-field">
                                    <label class="ad-label" for="event-capacity">Guest Capacity</label>
                                    <input id="event-capacity" class="ad-input" type="number" name="capacity" placeholder="e.g., 50" min="1" value="<?php $capVal = $edit_event['capacity'] ?? ''; echo htmlspecialchars(($capVal === null || $capVal === 0) ? '' : $capVal); ?>">
                                    <div class="ad-hint">Optional. Skip if the cap is still TBD.</div>
                                </div>

                                <div class="ad-field">
                                    <label class="ad-label" for="event-price">Price Per Person (KES)</label>
                                    <div class="ad-input-group ad-with-leading">
                                        <span class="ad-leading" aria-hidden="true" style="font-size:13px; font-weight:700; color:var(--ad-ink-3);">KES</span>
                                        <input id="event-price" class="ad-input" type="number" name="price_per_person" placeholder="0.00" step="0.01" min="0" value="<?php $ppVal = $edit_event['price_per_person'] ?? ''; echo htmlspecialchars(($ppVal === null || $ppVal === 0) ? '' : $ppVal); ?>">
                                    </div>
                                    <div class="ad-hint">Optional. Leave blank for free events or pricing TBA.</div>
                                </div>
                            </div>

                            <div class="ad-grid-2">
                                <div class="ad-field">
                                    <label class="ad-label" for="event-status">Event Status</label>
                                    <div class="ad-select-wrap">
                                        <select id="event-status" class="ad-select" name="status">
                                            <?php $curStatus = $edit_event['status'] ?? 'active'; ?>
                                            <option value="active"   <?php echo $curStatus === 'active'   ? 'selected' : ''; ?>>🟢 Active — visible to visitors</option>
                                            <option value="expired"  <?php echo $curStatus === 'expired'  ? 'selected' : ''; ?>>🕐 Expired — shown as past event</option>
                                            <option value="inactive" <?php echo $curStatus === 'inactive' ? 'selected' : ''; ?>>🔴 Inactive — hidden from website</option>
                                        </select>
                                    </div>
                                    <div class="ad-hint">Use Inactive to hide an event without deleting it. Expired events stay visible but move to the past events block.</div>
                                </div>

                                <div class="ad-field">
                                    <label class="ad-label" for="event-registration-link">Registration / Booking Link</label>
                                    <div class="ad-input-group ad-with-leading">
                                        <span class="ad-leading" aria-hidden="true">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 14a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M14 10a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        </span>
                                        <input id="event-registration-link" class="ad-input" type="url" name="registration_link" placeholder="https://forms.google.com/&hellip;" value="<?php echo htmlspecialchars($edit_event['registration_link'] ?? ''); ?>">
                                    </div>
                                    <div class="ad-hint">A &ldquo;Register / Book Now&rdquo; button appears on the event card when set.</div>
                                </div>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="event-services">Available Services</label>
                                <textarea id="event-services" class="ad-textarea" name="services" placeholder="Buffet, Cocktail, DJ, Decorations, Live Band&hellip;" rows="3"><?php echo htmlspecialchars($edit_event['services'] ?? ''); ?></textarea>
                                <div class="ad-hint">Comma separated. First three services appear as chips on the public event card.</div>
                            </div>

                            <!-- ===== GALLERY UPLOAD ===== -->
                            <div class="ad-field">
                                <label class="ad-label">Event Gallery Photos</label>
                                <div class="ad-hint" style="margin: 0 0 14px;">
                                    Select multiple photos for the gallery. All images are auto-compressed on upload &mdash; great quality, much smaller files.
                                </div>

                                <?php if (!empty($edit_event['gallery'])): ?>
                                <div style="margin-bottom:18px;">
                                    <span class="ad-chip ad-chip-ink" style="margin-bottom:12px;">Current Gallery &middot; <?php echo count($edit_event['gallery']); ?> photos</span>
                                    <div id="existing-gallery-strip" class="ad-gallery-strip">
                                        <?php foreach ($edit_event['gallery'] as $gPath):
                                            $gUrl = asmara_event_image_url(['image' => $gPath]);
                                            if (!$gUrl) continue;
                                        ?>
                                        <div class="ad-gallery-thumb">
                                            <img src="<?php echo htmlspecialchars($gUrl); ?>" alt="Gallery photo" loading="lazy" fetchpriority="low">
                                            <label class="ad-gallery-remove" title="Remove photo">
                                                <input type="checkbox" name="remove_gallery[]" value="<?php echo htmlspecialchars($gPath); ?>" style="display:none;" onchange="toggleRemoveThumb(this)">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="ad-hint" style="margin-top:10px;">Click the &#x2715; on any photo to mark it for removal on save.</div>
                                </div>
                                <?php endif; ?>

                                <label for="gallery-images-input" class="ad-gallery-drop">
                                    <input type="file" name="gallery_images[]" id="gallery-images-input" accept="image/*" multiple class="ad-cover-file">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                    <strong>Add more photos</strong>
                                    <span>Choose one or more images &mdash; they&rsquo;ll appear in the gallery after the featured photo.</span>
                                </label>

                                <div id="gallery-preview-strip" class="ad-gallery-strip" style="margin-top:14px;"></div>
                            </div>

                            <div class="ad-form-actions">
                                <button type="submit" class="ad-btn ad-btn-primary ad-btn-lg">
                                    <?php echo $action === 'create' ? '+ Create Event' : '✓ Update Event'; ?>
                                </button>
                                <a href="?action=list" class="ad-btn ad-btn-ghost ad-btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- EVENTS LIST WITH SAAS CONTROLS -->

                    <!-- Stats summary tiles -->
                    <div class="ad-stat-row" role="list">
                        <div class="ad-stat-tile" role="listitem">
                            <div class="ad-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="ad-stat-value"><?php echo (int)$totalEvents; ?></div>
                                <div class="ad-stat-label">Total Events</div>
                            </div>
                        </div>
                        <div class="ad-stat-tile is-emerald" role="listitem">
                            <div class="ad-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div>
                                <div class="ad-stat-value"><?php echo (int)$activeEventsCount; ?></div>
                                <div class="ad-stat-label">Active</div>
                            </div>
                        </div>
                        <div class="ad-stat-tile is-amber" role="listitem">
                            <div class="ad-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v6l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div>
                                <div class="ad-stat-value"><?php echo (int)$expiredEventsCount; ?></div>
                                <div class="ad-stat-label">Past Events</div>
                            </div>
                        </div>
                        <div class="ad-stat-tile is-rose" role="listitem">
                            <div class="ad-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 16l4-4 3 3 5-5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="ad-stat-value"><?php echo (int)$featuredEventsCount; ?></div>
                                <div class="ad-stat-label">With Featured Photo</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter / View toolbar -->
                    <div class="ad-toolbar">
                        <div class="ad-toolbar-section">
                            <a href="?action=create" class="ad-btn ad-btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                New Event
                            </a>

                            <label for="event-search-input" class="ad-search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <input id="event-search-input" type="text" placeholder="Search events by title…" onkeyup="filterEvents()">
                            </label>

                            <div class="ad-select-wrap">
                                <select id="event-category-filter" class="ad-select ad-trigger" onchange="filterEvents()">
                                    <option value="">All Categories</option>
                                    <option value="corporate">Corporate Events</option>
                                    <option value="social">Social Celebrations</option>
                                    <option value="catering">Catering Services</option>
                                    <option value="wedding">Weddings</option>
                                </select>
                            </div>

                            <div class="ad-select-wrap">
                                <select id="event-branch-filter" class="ad-select ad-trigger" onchange="filterEvents()">
                                    <option value="">View All Locations</option>
                                    <option value="all">All Branches Only</option>
                                    <option value="pangani">Pangani</option>
                                    <option value="westlands">Westlands</option>
                                    <option value="karen">Karen</option>
                                    <option value="lavington">Lavington</option>
                                </select>
                            </div>
                        </div>

                        <div class="ad-segmented" role="tablist" aria-label="View mode">
                            <button id="btnEventCardView" class="is-active" title="Card view" aria-label="Card view" onclick="setEventViewMode('card')">
                                <svg width="16" height="16" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button id="btnEventListView" title="List view" aria-label="List view" onclick="setEventViewMode('list')">
                                <svg width="16" height="16" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($events)): ?>
                        <div class="ad-empty">
                            <div class="ad-empty-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 16l4-4 3 3 5-5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <h4>No events yet</h4>
                            <p>Add your first event so visitors can see what&rsquo;s coming up at Asmara.</p>
                            <a href="?action=create" class="ad-btn ad-btn-primary">+ Create your first event</a>
                        </div>
                    <?php else: ?>
                        <div class="items-display-wrapper">
                            <div class="events-grid card-view-mode" id="eventsDisplayGrid">
                                <?php foreach ($events as $event):
                                    $eventImageUrl = asmara_event_image_url($event);
                                    $categoryLabel = ucfirst($event['category'] ?? 'Event');
                                    $st = $event['status'] ?? 'active';
                                    $chipClass = $st === 'active' ? 'ad-chip-emerald' : ($st === 'expired' ? 'ad-chip-amber' : 'ad-chip-crimson');
                                    $statusLabel = ucfirst($st);
                                    $galleryUrlsForCount = asmara_event_gallery_urls($event);
                                    $visiblePhotoCount = count($galleryUrlsForCount);

                                    $cap = $event['capacity'] ?? null;
                                    $pp  = $event['price_per_person'] ?? null;
                                    $v   = $event['venue'] ?? '';
                                    $capText = ($cap === null || $cap === '' || (int)$cap === 0) ? 'Capacity TBA' : ((int)$cap . ' guests');
                                    $ppText  = ($pp === null || $pp === '' || (float)$pp === 0.0) ? 'Pricing TBA' : ('KES ' . number_format((float)$pp));
                                    $vText   = ($v === '' || $v === null) ? 'Venue TBA' : (($v === 'all') ? 'All Branches' : ucfirst($v));
                                ?>
                                    <article class="ad-event-card event-card-premium"
                                         data-category="<?php echo htmlspecialchars($event['category']); ?>"
                                         data-branch="<?php echo htmlspecialchars(strtolower($event['venue'] ?? '')); ?>"
                                         data-title="<?php echo htmlspecialchars(strtolower($event['title'])); ?>">

                                        <!-- Media cover / gallery preview -->
                                        <div class="ad-event-card-media event-card-media">
                                            <?php if (!empty($eventImageUrl)): ?>
                                                <img src="<?php echo htmlspecialchars($eventImageUrl); ?>" alt="<?php echo htmlspecialchars($event['title'] ?? 'Event image'); ?>">
                                            <?php else: ?>
                                                <div class="ad-event-empty-media">
                                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M21 15l-5-5-4 4-7-7"/></svg>
                                                    <span>No cover image</span>
                                                </div>
                                            <?php endif; ?>

                                            <span class="ad-event-card-photo-count" title="Total photos">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 16l4-4 3 3 5-5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/></svg>
                                                <?php echo (int)$visiblePhotoCount; ?>
                                            </span>
                                        </div>

                                        <!-- Card Body: Title, description, specs -->
                                        <div class="ad-event-card-body">
                                            <div class="ad-event-card-head">
                                                <div class="ad-event-card-cat">
                                                    <span class="ad-chip"><span class="ad-chip-dot"></span><?php echo htmlspecialchars($categoryLabel); ?></span>
                                                    <span class="ad-chip <?php echo $chipClass; ?>"><span class="ad-chip-dot"></span><?php echo htmlspecialchars($statusLabel); ?></span>
                                                </div>
                                            </div>

                                            <h3 class="ad-event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                            <p class="ad-event-card-desc"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 110)); ?><?php echo strlen($event['description'] ?? '') > 110 ? '…' : ''; ?></p>

                                            <dl class="ad-event-card-details">
                                                <div>
                                                    <dt>
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                        Capacity
                                                    </dt>
                                                    <dd><?php echo htmlspecialchars($capText); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                                        Price
                                                    </dt>
                                                    <dd><?php echo htmlspecialchars($ppText); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        Venue
                                                    </dt>
                                                    <dd><?php echo htmlspecialchars($vText); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/></svg>
                                                        Date
                                                    </dt>
                                                    <dd><?php echo htmlspecialchars(asmara_event_date_range_label($event)); ?></dd>
                                                </div>
                                            </dl>
                                        </div>

                                        <!-- Card Footer: Actions -->
                                        <div class="ad-event-card-footer">
                                            <a href="?action=edit&id=<?php echo htmlspecialchars($event['id']); ?>" class="ad-btn ad-btn-soft ad-btn-block">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                                Edit
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Delete this event?');" class="ad-event-delete-form">
                                                <input type="hidden" name="action_type" value="delete">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                                <button type="submit" class="ad-btn ad-btn-danger ad-btn-block">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M7 7l1 12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>

                                    </article>
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

                                let okBranch = true;
                                if (branchVal === '') {
                                    okBranch = true;
                                } else if (branchVal === 'all') {
                                    okBranch = (branch === 'all');
                                } else {
                                    okBranch = (branch === branchVal || branch === 'all');
                                }

                                const ok = title.includes(searchVal)
                                    && (categoryVal === '' || category === categoryVal)
                                    && okBranch;

                                card.style.display = ok ? '' : 'none';
                            });
                        }

                        function setEventViewMode(mode) {
                            const grid    = document.getElementById('eventsDisplayGrid');
                            const btnCard = document.getElementById('btnEventCardView');
                            const btnList = document.getElementById('btnEventListView');
                            if (!grid) return;

                            if (mode === 'list') {
                                grid.className = 'events-grid list-view-mode';
                                if (btnList) { btnList.classList.add('is-active'); btnList.setAttribute('aria-selected','true'); }
                                if (btnCard) { btnCard.classList.remove('is-active'); btnCard.setAttribute('aria-selected','false'); }
                                localStorage.setItem('asmara_admin_events_view', 'list');
                            } else {
                                grid.className = 'events-grid card-view-mode';
                                if (btnCard) { btnCard.classList.add('is-active'); btnCard.setAttribute('aria-selected','true'); }
                                if (btnList) { btnList.classList.remove('is-active'); btnList.setAttribute('aria-selected','false'); }
                                localStorage.setItem('asmara_admin_events_view', 'card');
                            }
                        }

                        // Restore saved view preference
                        document.addEventListener('DOMContentLoaded', function() {
                            const saved = localStorage.getItem('asmara_admin_events_view');
                            if (saved === 'list') {
                                setEventViewMode('list');
                            }
                        });
                    </script>

                    <script>
                        function toggleRemoveThumb(checkbox) {
                            const wrapper = checkbox.closest('.ad-gallery-thumb') || checkbox.closest('.gallery-thumb-wrapper');
                            if (wrapper) {
                                wrapper.style.opacity = checkbox.checked ? '0.35' : '1';
                                wrapper.style.outline = checkbox.checked ? '2px solid #c81d3f' : 'none';
                                wrapper.style.outlineOffset = checkbox.checked ? '2px' : '0';
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
                                        // A fresh upload cancels a pending removal
                                        const flag = document.getElementById('event-image-remove-flag');
                                        if (flag) flag.value = '';
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

                        function removeCoverImage() {
                            if (!confirm('Remove the featured / cover photo? This will delete the image on save. You can upload a new cover afterwards.')) {
                                return;
                            }
                            const flag = document.getElementById('event-image-remove-flag');
                            const preview = document.getElementById('event-image-preview');
                            const input = document.getElementById('event-image-input');
                            const removeBtn = document.getElementById('event-image-remove-btn');
                            if (flag) flag.value = '1';
                            if (input) input.value = '';
                            if (preview) {
                                preview.innerHTML = '';
                                const placeholder = document.createElement('div');
                                placeholder.style.cssText = 'text-align:center; color:var(--color-text-muted); padding:16px;';
                                placeholder.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin-bottom:8px;"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15l-5-5-4 4-7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><div>Featured photo marked for removal on save</div>';
                                preview.appendChild(placeholder);
                            }
                            if (removeBtn) removeBtn.remove();
                        }
                    </script>

                <?php endif; ?>
            </div>


<?php include 'footer.php'; ?>
