<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Load events from backend data
$eventsFile = __DIR__ . '/../backend/data/events.json';
$events = [];

if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

require_once __DIR__ . '/../backend/database/BranchRepository.php';
require_once __DIR__ . '/../backend/data/event_helpers.php';
$events = asmara_filter_upcoming_events($events);
$branchRepo = new BranchRepository();
$allBranches = $branchRepo->getAll();

$schemaEvents = [];
foreach ($events as $event) {
    $schemaEvents[] = [
        "@type" => "Event",
        "name" => $event['title'],
        "description" => $event['description'] ?? '',
        "location" => [
            "@type" => "Place",
            "name" => "Asmara Restaurant " . ($event['venue'] ?? 'Nairobi'),
            "address" => "Nairobi, Kenya"
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => (float)($event['price_per_person'] ?? 0),
            "priceCurrency" => "KES"
        ]
    ];
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
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.55;">
      <img src="images/optimized/Lavington-15.jpg" alt="Asmara Events Background" style="width: 100%; height: 100%; object-fit: cover; filter: saturate(1.05) contrast(1.03);">
      <div style="position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,0.12), rgba(0,0,0,0.18)); mix-blend-mode: multiply;"></div>
    </div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex;">
        <svg width="20" height="20" viewBox="0 0 24 24" style="margin-right:6px;" aria-hidden="true"><path fill="currentColor" d="M12 2l2.9 6.26L21 9.27l-5 3.64L17.8 21 12 17.77 6.2 21 8 12.91 3 9.27l6.1-1.01L12 2z"/></svg>
        Events & Celebrations
      </span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem);">SPECIAL OCCASIONS</h1>
      <p style="margin-top: var(--space-sm); color: var(--color-text-muted-light); font-size: 1.2rem;">
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
              <button class="tab-btn branch-btn active" data-branch="all" style="padding: 10px 20px; font-size: 0.9rem;">All Locations</button>
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
          <?php $eventImageUrl = asmara_event_image_url($event); ?>
          <?php if (!empty($eventImageUrl)): ?>
          <div style="height: 220px; overflow: hidden; background: #f5f5f5;">
            <img src="<?php echo htmlspecialchars($eventImageUrl); ?>" alt="<?php echo htmlspecialchars($event['title'] ?? 'Event image'); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
          </div>
          <?php endif; ?>
            <div style="background: linear-gradient(135deg, #ed174b 0%, #c41140 100%); color: white; padding: var(--space-md); position: relative; overflow: hidden;">
              <div style="position: absolute; top: -20px; right: -20px; font-size: 80px; opacity: 0.1;">✨</div>
              <h3 class="font-heading-h3" style="color: white; margin: 0 0 var(--space-xs) 0; font-size: 1.5rem;">
                <?php echo htmlspecialchars($event['title'] ?? 'Event'); ?>
              </h3>
              <div style="display: flex; gap: var(--space-sm); flex-wrap: wrap;">
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.8rem; display:flex; align-items:center; gap:6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 3h18v2H3V3zm2 4h14v12H5V7zm2 2v8h10V9H7z"/></svg>
                  <?php echo htmlspecialchars(ucfirst($event['category'] ?? 'Event')); ?>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.8rem; display:flex; align-items:center; gap:6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
                  <?php echo htmlspecialchars(ucfirst($event['venue'] ?? 'TBA')); ?>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.8rem; display:flex; align-items:center; gap:6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm12 8H5v10h14V10z"/></svg>
                  <?php echo htmlspecialchars(asmara_event_date_label($event)); ?>
                </span>
              </div>
            </div>

            <!-- Event Details -->
            <div style="padding: var(--space-md);">
              <?php if (!empty($event['description'])): ?>
              <p class="event-description" style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: var(--space-md); line-height: 1.6;">
                <?php echo htmlspecialchars($event['description']); ?>
              </p>
              <?php endif; ?>

              <!-- Event Specs Grid -->
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md); margin-bottom: var(--space-md);">
                <div style="background: #f5f5f5; padding: var(--space-sm); border-radius: 8px; text-align: center;">
                  <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zM8 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zM16 13c-.29 0-.62.02-.97.05C15.7 13.7 16 14.33 16 15v2h6v-2.5C22 14.17 17.33 13 16 13z"/></svg>
                    Capacity
                  </div>
                  <div style="font-size: 1.3rem; font-weight: 700; color: #ed174b;">
                    <?php echo (int)($event['capacity'] ?? 0); ?> guests
                  </div>
                </div>
                <div style="background: #f5f5f5; padding: var(--space-sm); border-radius: 8px; text-align: center;">
                  <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 4px; display:flex; align-items:center; gap:6px;"> 
                    <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zM12 17a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                    Per Person
                  </div>
                  <div style="font-size: 1.3rem; font-weight: 700; color: #ed174b;">
                    KES <?php echo number_format($event['price_per_person'] ?? 0); ?>
                  </div>
                </div>
              </div>

              <!-- Services -->
              <?php if (!empty($event['services'])): ?>
              <div style="margin-bottom: var(--space-md);">
                <p style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-muted); margin-bottom: 6px; display:flex; align-items:center; gap:8px;"><svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l1.5 4.5L18 8l-4.5 1.5L12 14l-1.5-4.5L6 8l4.5-1.5L12 2z"/></svg> Services Included:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                  <?php 
                    $serviceList = is_array($event['services']) ? $event['services'] : explode(',', $event['services']);
                    foreach (array_slice($serviceList, 0, 3) as $service): 
                  ?>
                  <span class="badge" style="background: #f0f0f0; color: #333; font-size: 0.8rem; padding: 4px 10px;">
                    <?php echo trim($service); ?>
                  </span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Event Footer -->
            <div style="padding: var(--space-md); border-top: 1px solid #e5e5e5; display: flex; gap: var(--space-sm);">
              <a href="/booking" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: var(--space-sm) var(--space-md); background: #ed174b; color: white; border-radius: 6px; font-weight: 600; transition: all 0.3s ease;">
                Reserve Now
              </a>
              <button class="btn btn-secondary" onclick="shareEvent('<?php echo htmlspecialchars($event['title']); ?>', '<?php echo htmlspecialchars($event['description']); ?>')" style="flex: 1; padding: var(--space-sm) var(--space-md); background: #f5f5f5; color: #333; border: 1px solid #e5e5e5; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                Share
              </button>
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
        <a href="tel:+254713610707" class="btn btn-primary" style="display: inline-block; padding: var(--space-sm) var(--space-lg); background: #ed174b; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
          Call Us: <?php echo format_phone('+254713610707'); ?>
        </a>
      </div>

    </div>
  </section>

<?php include 'footer.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const branchButtons = document.querySelectorAll('.branch-btn');
    const eventCards = document.querySelectorAll('.event-card');

    function filterEvents() {
      const activeCategoryBtn = document.querySelector('.category-btn.active');
      const activeBranchBtn = document.querySelector('.branch-btn.active');
      
      const activeCategory = activeCategoryBtn ? activeCategoryBtn.getAttribute('data-category') : 'all';
      const activeBranch = activeBranchBtn ? activeBranchBtn.getAttribute('data-branch') : 'all';

      eventCards.forEach(card => {
        const matchCategory = (activeCategory === 'all' || card.classList.contains(activeCategory));
        const matchBranch = (activeBranch === 'all' || card.classList.contains(activeBranch));

        if (matchCategory && matchBranch) {
          card.style.display = '';
          card.style.opacity = '0';
          card.style.transform = 'scale(0.97)';
          setTimeout(() => {
            card.style.transition = 'opacity 300ms ease, transform 300ms ease';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
          }, 50);
        } else {
          card.style.display = 'none';
        }
      });
    }

    categoryButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        categoryButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterEvents();
      });
    });

    branchButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        branchButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterEvents();
      });
    });
  });

  // Share function
  function shareEvent(title, description) {
    const text = `Check out this event at Asmara Restaurant: ${title} - ${description}`;
    if (navigator.share) {
      navigator.share({
        title: 'Asmara Event',
        text: text,
        url: window.location.href
      });
    } else {
      alert('Event:\n' + title + '\n\n' + description);
    }
  }
</script>
