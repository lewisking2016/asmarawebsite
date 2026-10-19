<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Load events from backend data
$eventsFile = __DIR__ . '/../backend/data/events.json';
$events = [];

if (file_exists($eventsFile)) {
    clearstatcache(true, $eventsFile);
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

require_once __DIR__ . '/../backend/database/BranchRepository.php';
require_once __DIR__ . '/../backend/data/event_helpers.php';
$events = asmara_filter_upcoming_events($events);
$allBranches = [];
try {
    $branchRepo = new BranchRepository();
    $allBranches = $branchRepo->getAll();
} catch (\Throwable $e) {
    $allBranches = [];
}
if (!is_array($allBranches) || empty($allBranches)) {
    $allBranches = [
        ['name' => 'Karen'],
        ['name' => 'Lavington'],
        ['name' => 'Pangani'],
        ['name' => 'Westlands']
    ];
}


$schemaEvents = [];
foreach ($events as $event) {
    $schemaEvent = [
        "@type" => "Event",
        "name" => $event['title'],
        "description" => $event['description'] ?? '',
        "location" => [
            "@type" => "Place",
            "name" => "Asmara Restaurant " . (($event['venue'] ?? '') ?: 'Nairobi'),
            "address" => "Nairobi, Kenya"
        ]
    ];
    $startDateObj = asmara_event_start_date_object($event);
    $endDateObj   = asmara_event_end_date_object($event);
    if ($startDateObj) $schemaEvent['startDate'] = $startDateObj->format('Y-m-d');
    if ($endDateObj)   $schemaEvent['endDate']   = $endDateObj->format('Y-m-d');
    if (!empty($event['price_per_person']) && (float)$event['price_per_person'] > 0) {
        $schemaEvent['offers'] = [
            "@type" => "Offer",
            "price" => (float)$event['price_per_person'],
            "priceCurrency" => "KES"
        ];
    }
    $schemaEvents[] = $schemaEvent;
}

$pageTitle = "Events & Celebrations";
$pageDescription = "Discover Asmara Restaurant's upcoming events. From corporate gatherings to wedding celebrations, find the perfect venue for your special occasion.";
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "ItemList",
  "name" => "Asmara Restaurant Events List",
  "description" => "Upcoming events at Asmara Restaurant",
  "url" => "https://asmara.co.ke/events",
  "itemListElement" => $schemaEvents
];
include 'header.php'; 
?>

  <!-- Events Banner -->
  <section class="hero panel-dark" style="min-height: 50vh; padding-top: 180px; padding-bottom: var(--space-lg); text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.6;">
      <img src="images/optimized/Lavington-15.jpg" alt="Asmara Events Background" style="width: 100%; height: 100%; object-fit: cover; filter: saturate(1.05) contrast(1.03);">
      <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.65);"></div>
    </div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex; color: var(--color-primary); font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
        <svg width="20" height="20" viewBox="0 0 24 24" style="margin-right:6px;" aria-hidden="true"><path fill="currentColor" d="M12 2l2.9 6.26L21 9.27l-5 3.64L17.8 21 12 17.77 6.2 21 8 12.91 3 9.27l6.1-1.01L12 2z"/></svg>
        Events & Celebrations
      </span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem); color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.7);">SPECIAL OCCASIONS</h1>
      <p style="margin-top: var(--space-sm); color: #ffffff; font-size: 1.2rem; text-shadow: 0 1px 5px rgba(0,0,0,0.7); line-height: 1.5; font-weight: 500;">
        Host your corporate events, celebrations, and weddings at Asmara. Four beautiful branches ready to make your occasion unforgettable.
      </p>
    </div>
  </section>

  <!-- Events Catalog -->
  <section class="panel-light" style="padding-top: var(--space-xl); padding-bottom: var(--space-xxl);">
    <div class="container">
      
      <?php if (!empty($events)): ?>
        <div style="margin-bottom: var(--space-lg);">
          <h2 class="font-heading-h2" style="margin-bottom: var(--space-sm); text-align: center; font-size: 2rem;">Upcoming Events (<?php echo count($events); ?>)</h2>
          <p style="text-align: center; color: var(--color-text-muted); margin-bottom: var(--space-lg);">Browse our available events and reserve your spot today</p>
        </div>

        <!-- Premium Multi-dimensional Filter Bar -->
        <div class="filter-controls-container" style="background: var(--color-surface-light, #ffffff); padding: 24px; border-radius: var(--radius-md, 8px); box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--color-border-light, #eaeaea); margin-bottom: var(--space-lg); display: flex; flex-direction: column; gap: 20px;">
          <!-- Category Filters -->
          <div>
            <span style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-text-muted-dark, #666); margin-bottom: 10px; font-weight: 700;">Filter by Event Type</span>
            <div class="tab-nav category-filter-row" style="gap: 8px; flex-wrap: wrap; margin: 0; justify-content: flex-start;">
              <button class="tab-btn category-btn active" data-category="all" style="padding: 10px 20px; font-size: 0.9rem;">All Types</button>
              <button class="tab-btn category-btn" data-category="category-corporate" style="padding: 10px 20px; font-size: 0.9rem;">Corporate</button>
              <button class="tab-btn category-btn" data-category="category-social" style="padding: 10px 20px; font-size: 0.9rem;">Social</button>
              <button class="tab-btn category-btn" data-category="category-catering" style="padding: 10px 20px; font-size: 0.9rem;">Catering</button>
              <button class="tab-btn category-btn" data-category="category-wedding" style="padding: 10px 20px; font-size: 0.9rem;">Wedding</button>
            </div>
          </div>

          <!-- Location Filters -->
          <div style="border-top: 1px solid var(--color-border-light, #eaeaea); padding-top: 15px;">
            <span style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-text-muted-dark, #666); margin-bottom: 10px; font-weight: 700;">Filter by Location</span>
            <div class="tab-nav branch-filter-row" style="gap: 8px; flex-wrap: wrap; margin: 0; justify-content: flex-start;">
              <button class="tab-btn branch-btn active" data-branch="all" style="padding: 10px 20px; font-size: 0.9rem;">View All</button>
              <button class="tab-btn branch-btn" data-branch="branch-all" style="padding: 10px 20px; font-size: 0.9rem;">All Branches</button>
              <?php foreach ($allBranches as $b): ?>
                <button class="tab-btn branch-btn" data-branch="branch-<?php echo htmlspecialchars(strtolower($b['name'])); ?>" style="padding: 10px 20px; font-size: 0.9rem;"><?php echo htmlspecialchars($b['name']); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Events Grid -->
        <div class="grid grid-3" style="gap: var(--space-lg);">
          <?php foreach ($events as $event): 
            $categoryClass = strtolower(str_replace(' ', '', $event['category'] ?? 'corporate'));
            $venueClass = strtolower(trim($event['venue'] ?? 'pangani'));
            $classNames = 'card event-card category-' . $categoryClass . ' branch-' . $venueClass;
          ?>
          <div class="<?php echo $classNames; ?>" style="overflow: hidden; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease;">
            
          <!-- Event Header Card -->
          <?php
            $galleryUrls  = asmara_event_gallery_urls($event);
            $hasCover     = !empty($galleryUrls);
            $galleryCount = count($galleryUrls);
          ?>
          <?php if ($hasCover): ?>
          <div class="event-gallery-strip" 
               aria-label="Event photo gallery"
               data-gallery="<?php echo htmlspecialchars(json_encode($galleryUrls), ENT_QUOTES, 'UTF-8'); ?>"
               data-title="<?php echo htmlspecialchars($event['title'] ?? 'Event', ENT_QUOTES, 'UTF-8'); ?>">
            <?php foreach ($galleryUrls as $gi => $gUrl): ?>
            <div class="event-gallery-slide" data-index="<?php echo $gi; ?>">
              <img
                src="<?php echo htmlspecialchars($gUrl); ?>"
                alt="<?php echo htmlspecialchars(($event['title'] ?? 'Event') . ' photo ' . ($gi + 1)); ?>"
                style="width:100%; height:100%; object-fit:cover; display:block; cursor:<?php echo $galleryCount > 1 ? 'zoom-in' : 'default'; ?>;"
                <?php echo $gi === 0 ? 'fetchpriority="high"' : 'loading="lazy" fetchpriority="low"'; ?>
              >
            </div>
            <?php endforeach; ?>
          </div>
          <?php if ($galleryCount > 1): ?>
          <div class="event-gallery-dots" aria-hidden="true">
            <?php for ($d = 0; $d < $galleryCount; $d++): ?>
            <span class="gallery-dot<?php echo $d === 0 ? ' active' : ''; ?>"></span>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
          <?php endif; ?>

            <div style="background: linear-gradient(135deg, #ed174b 0%, #c41140 100%); color: white; padding: 12px 16px; position: relative; overflow: hidden;">
              <div style="position: absolute; top: -15px; right: -15px; font-size: 60px; opacity: 0.1;">✨</div>
              <?php if (($event['status'] ?? 'active') === 'expired'): ?>
              <span style="position:absolute; top:10px; right:12px; background:rgba(245,158,11,0.9); color:#fff; font-size:0.68rem; font-weight:700; padding:2px 8px; border-radius:99px; text-transform:uppercase; letter-spacing:0.05em;">Past Event</span>
              <?php endif; ?>
              <h3 class="font-heading-h3" style="color: white; margin: 0 0 6px 0; font-size: 1.2rem; line-height: 1.3;">
                <?php echo htmlspecialchars($event['title'] ?? 'Event'); ?>
              </h3>
              <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 99px; display:flex; align-items:center; gap:4px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 3h18v2H3V3zm2 4h14v12H5V7zm2 2v8h10V9H7z"/></svg>
                  <?php echo htmlspecialchars(ucfirst($event['category'] ?? 'Event')); ?>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 99px; display:flex; align-items:center; gap:4px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
                  <?php
                    $v = $event['venue'] ?? '';
                    if ($v === 'all') echo 'All Branches';
                    elseif ($v === '') echo 'Venue TBA';
                    else echo htmlspecialchars(ucfirst($v));
                  ?>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 99px; display:flex; align-items:center; gap:4px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/></svg>
                  <?php echo htmlspecialchars(asmara_event_date_range_label($event)); ?>
                </span>
              </div>
            </div>

            <!-- Event Details -->
            <div style="padding: 14px 16px;">
              <?php if (!empty($event['description'])): ?>
              <p class="event-description" style="color: var(--color-text-muted); font-size: 0.88rem; margin-bottom: 10px; line-height: 1.5;">
                <?php echo htmlspecialchars($event['description']); ?>
              </p>
              <?php endif; ?>

              <?php if ($galleryCount <= 1): ?>
              <div class="event-no-gallery-note" style="font-size: 0.78rem; color: #64748b; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 6px 10px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; color:#94a3b8;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>This event has only a single photo — add more to the gallery for richer viewing.</span>
              </div>
              <?php endif; ?>

              <!-- Event Specs Grid -->
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                <div style="background: #f8fafc; padding: 8px 10px; border-radius: 8px; text-align: center; border: 1px solid #f1f5f9;">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-bottom: 2px; display:flex; align-items:center; justify-content:center; gap:4px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zM8 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zM16 13c-.29 0-.62.02-.97.05C15.7 13.7 16 14.33 16 15v2h6v-2.5C22 14.17 17.33 13 16 13z"/></svg>
                    Capacity
                  </div>
                  <div style="font-size: 1.05rem; font-weight: 700; color: #ed174b;">
                    <?php
                      $capF = $event['capacity'] ?? null;
                      echo ($capF === null || $capF === '' || (int)$capF === 0) ? 'TBA' : ((int)$capF . ' guests');
                    ?>
                  </div>
                </div>
                <div style="background: #f8fafc; padding: 8px 10px; border-radius: 8px; text-align: center; border: 1px solid #f1f5f9;">
                  <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-bottom: 2px; display:flex; align-items:center; justify-content:center; gap:4px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zM12 17a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                    Per Person
                  </div>
                  <div style="font-size: 1.05rem; font-weight: 700; color: #ed174b;">
                    <?php
                      $ppF = $event['price_per_person'] ?? null;
                      echo ($ppF === null || $ppF === '' || (float)$ppF === 0.0) ? 'TBA' : ('KES ' . number_format((float)$ppF));
                    ?>
                  </div>
                </div>
              </div>

              <!-- Services -->
              <?php if (!empty($event['services'])): ?>
              <div style="margin-bottom: 12px;">
                <p style="font-size: 0.78rem; font-weight: 600; color: var(--color-text-muted); margin-bottom: 6px; display:flex; align-items:center; gap:6px;"><svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l1.5 4.5L18 8l-4.5 1.5L12 14l-1.5-4.5L6 8l4.5-1.5L12 2z"/></svg> Services Included:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                  <?php 
                    $serviceList = is_array($event['services']) ? $event['services'] : explode(',', $event['services']);
                    foreach (array_slice($serviceList, 0, 3) as $service): 
                  ?>
                  <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <?php echo trim($service); ?>
                  </span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Event Footer -->
            <div style="padding: 10px 16px; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 8px; background: #fafafa;">
              <?php if (!empty($event['registration_link'])): ?>
              <a href="<?php echo htmlspecialchars($event['registration_link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="display: block; text-align: center; text-decoration: none; padding: 9px 12px; background: linear-gradient(135deg, #059669, #047857); color: white; border-radius: 6px; font-size: 0.88rem; font-weight: 700; transition: all 0.2s ease; letter-spacing: 0.02em;">
                🔗 Register / Book Now
              </a>
              <?php endif; ?>
              <div style="display: flex; gap: 8px;">
                <a href="/booking" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: 8px 12px; background: #ed174b; color: white; border-radius: 6px; font-size: 0.88rem; font-weight: 600; transition: all 0.2s ease;">
                  Reserve Now
                </a>
                <button class="btn btn-secondary" onclick="shareEvent('<?php echo htmlspecialchars($event['title']); ?>', '<?php echo htmlspecialchars($event['description']); ?>')" style="padding: 8px 14px; background: #fff; color: #333; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                  Share
                </button>
              </div>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <!-- No Events Message -->
        <div style="text-align: center; padding: var(--space-xxl);">
          <div style="font-size: 60px; margin-bottom: var(--space-md);">
            <svg width="60" height="60" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zM7 10h10v8H7v-8z"/></svg>
          </div>
          <h3 class="font-heading-h3">No Events Currently Listed</h3>
          <p style="color: var(--color-text-muted); margin-bottom: var(--space-lg); max-width: 500px; margin-left: auto; margin-right: auto;">
            Check back soon for upcoming corporate events, celebrations, and catering services. In the meantime, contact us to plan your special occasion.
          </p>
          <a href="/booking" class="btn btn-primary" style="display: inline-block; padding: var(--space-sm) var(--space-lg); background: #ed174b; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
            Make a Reservation
          </a>
        </div>
      <?php endif; ?>

      <!-- CTA Section -->
      <div class="section-green" style="margin-top: var(--space-xxl); padding: var(--space-lg); border-radius: 12px; text-align: center;">
        <h3 class="font-heading-h3" style="margin-bottom: var(--space-sm);">Don't See Your Event Type?</h3>
        <p style="margin-bottom: var(--space-md); color: var(--color-text-muted); margin-left: auto; margin-right: auto;">
          Contact us directly to discuss custom event packages and special arrangements for your unique celebration.
        </p>
        <a href="tel:+254721948020" class="btn btn-primary" style="display: inline-block; padding: var(--space-sm) var(--space-lg); background: #ed174b; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
          Call Us: <?php echo format_phone('+254721948020'); ?>
        </a>
      </div>

    </div>
  </section>

<?php include 'footer.php'; ?>

<!-- ===== Gallery Lightbox (native <dialog>) ===== -->
<dialog id="gallery-lightbox" aria-label="Event photo gallery lightbox" style="
  padding:0; border:none; border-radius:16px; max-width:min(94vw,1100px); width:100%;
  background:#0a0a0a; overflow:hidden; box-shadow:0 30px 80px rgba(0,0,0,.8);
">
  <div style="position:relative; user-select:none;">
    <!-- Close -->
    <button id="lb-close" aria-label="Close gallery" style="
      position:absolute; top:14px; right:14px; z-index:10;
      background:rgba(255,255,255,.15); border:none; color:#fff;
      border-radius:50%; width:40px; height:40px; font-size:22px; cursor:pointer;
      display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px);
    ">&times;</button>
    <!-- Prev / Next -->
    <button id="lb-prev" aria-label="Previous photo" style="
      position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:10;
      background:rgba(255,255,255,.15); border:none; color:#fff; border-radius:50%;
      width:44px; height:44px; font-size:24px; cursor:pointer; backdrop-filter:blur(4px);
    ">&#8249;</button>
    <button id="lb-next" aria-label="Next photo" style="
      position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:10;
      background:rgba(255,255,255,.15); border:none; color:#fff; border-radius:50%;
      width:44px; height:44px; font-size:24px; cursor:pointer; backdrop-filter:blur(4px);
    ">&#8250;</button>
    <!-- Main image -->
    <div style="aspect-ratio:16/9; background:#111; display:flex; align-items:center; justify-content:center;">
      <img id="lb-img" src="" alt="" style="max-width:100%; max-height:90vh; object-fit:contain; display:block; transition:opacity .2s ease;">
    </div>
    <!-- Caption & counter -->
    <div style="padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
      <span id="lb-caption" style="color:#e5e7eb; font-size:15px; font-weight:600;"></span>
      <span id="lb-counter" style="color:#9ca3af; font-size:13px;"></span>
    </div>
    <!-- Thumbnail strip -->
    <div id="lb-thumbs" style="display:flex; gap:6px; padding:0 16px 16px; overflow-x:auto; scrollbar-width:thin;"></div>
  </div>
</dialog>

<style>
/* ===== Gallery Carousel Strip ===== */
.event-gallery-strip {
  height: 160px;
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;        /* Firefox */
  -webkit-overflow-scrolling: touch;
  background: #111;
}
.event-gallery-strip::-webkit-scrollbar { display: none; }

.event-gallery-slide {
  flex: 0 0 100%;
  scroll-snap-align: center;
  height: 160px;
  overflow: hidden;
}

/* Dot indicators */
.event-gallery-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  padding: 8px 0 4px;
  background: #fff;
}
.gallery-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #d1d5db;
  transition: background .25s, transform .25s;
}
.gallery-dot.active {
  background: #ed174b;
  transform: scale(1.3);
}

/* Photo count badge on cards */
.gallery-photo-count {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: rgba(0,0,0,.6);
  color: #fff;
  font-size: 11px;
  padding: 3px 9px;
  border-radius: 99px;
  backdrop-filter: blur(4px);
  pointer-events: none;
}

/* Lightbox */
#gallery-lightbox::backdrop {
  background: rgba(0,0,0,.88);
  backdrop-filter: blur(6px);
}
#lb-thumbs img {
  width: 68px;
  height: 50px;
  object-fit: cover;
  border-radius: 6px;
  cursor: pointer;
  border: 2px solid transparent;
  opacity: .65;
  transition: opacity .2s, border-color .2s;
  flex-shrink: 0;
}
#lb-thumbs img.lb-thumb-active {
  border-color: #ed174b;
  opacity: 1;
}
</style>

<script>
  /* ===== Dot-sync for every gallery strip ===== */
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.event-gallery-strip').forEach(strip => {
      const dotsContainer = strip.nextElementSibling;
      if (!dotsContainer || !dotsContainer.classList.contains('event-gallery-dots')) return;
      const dots  = dotsContainer.querySelectorAll('.gallery-dot');
      const slides = strip.querySelectorAll('.event-gallery-slide');

      // Use IntersectionObserver per slide for accurate snap detection
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const idx = Array.from(slides).indexOf(entry.target);
            dots.forEach((d, i) => d.classList.toggle('active', i === idx));
          }
        });
      }, { root: strip, threshold: 0.6 });

      slides.forEach(s => observer.observe(s));

      // Click to open lightbox
      strip.addEventListener('click', function(e) {
        const slide = e.target.closest('.event-gallery-slide');
        if (!slide) return;
        const raw = strip.getAttribute('data-gallery');
        if (!raw) return;
        try {
          const imgs = JSON.parse(raw);
          if (!Array.isArray(imgs) || imgs.length <= 1) return;
          const idx = parseInt(slide.getAttribute('data-index'), 10) || 0;
          const title = strip.getAttribute('data-title') || 'Event';
          openGalleryLightbox(imgs, idx, title);
        } catch(err) {}
      });
    });

    /* ===== Filter ===== */
    const categoryButtons = document.querySelectorAll('.category-btn');
    const branchButtons   = document.querySelectorAll('.branch-btn');
    const eventCards      = document.querySelectorAll('.event-card');

    function filterEvents() {
      const activeCat    = document.querySelector('.category-btn.active');
      const activeBranch = document.querySelector('.branch-btn.active');
      const selCat    = activeCat    ? activeCat.getAttribute('data-category')    : 'all';
      const selBranch = activeBranch ? activeBranch.getAttribute('data-branch') : 'all';

      eventCards.forEach(card => {
        const okCat    = selCat    === 'all' || card.classList.contains(selCat);
        let okBranch = false;
        if (selBranch === 'all') {
          okBranch = true;
        } else if (selBranch === 'branch-all') {
          okBranch = card.classList.contains('branch-all');
        } else {
          okBranch = card.classList.contains(selBranch) || card.classList.contains('branch-all');
        }
        if (okCat && okBranch) {
          card.style.display   = '';
          card.style.opacity   = '0';
          card.style.transform = 'scale(0.97)';
          setTimeout(() => {
            card.style.transition = 'opacity 300ms ease, transform 300ms ease';
            card.style.opacity    = '1';
            card.style.transform  = 'scale(1)';
          }, 50);
        } else {
          card.style.display = 'none';
        }
      });
    }

    categoryButtons.forEach(btn => btn.addEventListener('click', function() {
      categoryButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      filterEvents();
    }));

    branchButtons.forEach(btn => btn.addEventListener('click', function() {
      branchButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      filterEvents();
    }));
  });

  /* ===== Lightbox ===== */
  let _lb_images = [];
  let _lb_index  = 0;
  let _lb_title  = '';

  function openGalleryLightbox(images, startIndex, title) {
    _lb_images = images;
    _lb_index  = startIndex;
    _lb_title  = title;

    const dlg   = document.getElementById('gallery-lightbox');
    const strip = document.getElementById('lb-thumbs');

    // Build thumbnail strip
    strip.innerHTML = '';
    images.forEach((src, i) => {
      const t = document.createElement('img');
      t.src        = src;
      t.alt        = title + ' photo ' + (i + 1);
      t.loading    = 'lazy';
      t.fetchPriority = 'low';
      t.dataset.idx = i;
      t.addEventListener('click', () => lbGoto(i));
      strip.appendChild(t);
    });

    lbRender(startIndex);
    dlg.showModal();
  }

  function lbRender(idx) {
    if (!_lb_images || _lb_images.length === 0) return;
    _lb_index = (idx + _lb_images.length) % _lb_images.length;
    const img     = document.getElementById('lb-img');
    const caption = document.getElementById('lb-caption');
    const counter = document.getElementById('lb-counter');
    const thumbs  = document.querySelectorAll('#lb-thumbs img');

    if (img) {
      img.style.opacity = '0';
      setTimeout(() => {
        img.src           = _lb_images[_lb_index];
        img.alt           = _lb_title + ' photo ' + (_lb_index + 1);
        img.style.opacity = '1';
      }, 100);
    }

    if (caption) caption.textContent = _lb_title;
    if (counter) counter.textContent = (_lb_index + 1) + ' / ' + _lb_images.length;

    thumbs.forEach((t, i) => t.classList.toggle('lb-thumb-active', i === _lb_index));

    // Scroll active thumb into view
    if (thumbs[_lb_index]) {
      thumbs[_lb_index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  }

  function lbGoto(idx)  { lbRender(idx); }
  function lbNext()     { lbRender(_lb_index + 1); }
  function lbPrev()     { lbRender(_lb_index - 1); }

  const lbCloseBtn = document.getElementById('lb-close');
  if (lbCloseBtn) lbCloseBtn.addEventListener('click', () => {
    const dlg = document.getElementById('gallery-lightbox');
    if (dlg) dlg.close();
  });

  const lbNextBtn = document.getElementById('lb-next');
  if (lbNextBtn) lbNextBtn.addEventListener('click', lbNext);

  const lbPrevBtn = document.getElementById('lb-prev');
  if (lbPrevBtn) lbPrevBtn.addEventListener('click', lbPrev);

  // Keyboard navigation
  document.addEventListener('keydown', e => {
    const dlg = document.getElementById('gallery-lightbox');
    if (!dlg || !dlg.open) return;
    if (e.key === 'ArrowRight') lbNext();
    if (e.key === 'ArrowLeft')  lbPrev();
    if (e.key === 'Escape')     dlg.close();
  });

  // Touch swipe support
  let _touch_start = 0;
  const lbDialog = document.getElementById('gallery-lightbox');
  if (lbDialog) {
    lbDialog.addEventListener('touchstart', e => {
      _touch_start = e.touches[0].clientX;
    }, { passive: true });

    lbDialog.addEventListener('touchend', e => {
      const delta = _touch_start - e.changedTouches[0].clientX;
      if (Math.abs(delta) > 40) delta > 0 ? lbNext() : lbPrev();
    });

    // Close on backdrop click
    lbDialog.addEventListener('click', e => {
      if (e.target === lbDialog) lbDialog.close();
    });
  }

  // Share function
  function shareEvent(title, description) {
    const text = `Check out this event at Asmara Restaurant: ${title} - ${description}`;
    if (navigator.share) {
      navigator.share({ title: 'Asmara Event', text: text, url: window.location.href });
    } else {
      alert('Event:\n' + title + '\n\n' + description);
    }
  }
</script>
