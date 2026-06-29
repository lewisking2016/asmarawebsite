<?php
require_once __DIR__ . '/../backend/database/BranchRepository.php';
require_once __DIR__ . '/../backend/database/MenuRepository.php';
$branchRepo = new BranchRepository();
$menuRepo = new MenuRepository();

$branchKey = $_GET['branch'] ?? 'westlands';
$dbBranch = $branchRepo->getByName(ucfirst($branchKey));
if (!$dbBranch) {
  $dbBranch = $branchRepo->getByName('Westlands');
  $branchKey = 'westlands';
}

$branchMenuItems = $menuRepo->getByBranch($dbBranch['id'], true);

$branchEvents = [];
if (file_exists(__DIR__ . '/../backend/data/events.json')) {
    $eventsData = json_decode(file_get_contents(__DIR__ . '/../backend/data/events.json'), true);
    if (is_array($eventsData)) {
        foreach ($eventsData as $evt) {
            if (isset($evt['venue']) && strtolower(trim($evt['venue'])) === strtolower(trim($dbBranch['name']))) {
                $branchEvents[] = $evt;
            }
        }
    }
}

$slug = strtolower($branchKey);

// Build branch data from the database record
$branch = [
  'title'     => htmlspecialchars($dbBranch['name']) . ' | Asmara Restaurant',
  'subtitle'  => $dbBranch['subtitle'] ?? 'Authentic Eritrean & Continental dining',
  'summary'   => $dbBranch['summary'] ?? htmlspecialchars($dbBranch['address']),
  'address'   => $dbBranch['address'],
  'phone'     => $dbBranch['phone'],
  'email'     => $dbBranch['email'],
  'opening_hours' => $dbBranch['opening_hours'] ?? '10:00 AM - 11:00 PM',
  'capacity'  => $dbBranch['capacity'] ?? 50,
  'keywords'  => $dbBranch['seo_keywords'] ?? 'Asmara ' . $dbBranch['name'] . ', Eritrean restaurant Nairobi',
  'hero_img'  => $dbBranch['hero_image'] ?? 'images/optimized/Lavington-5.jpg',
  'gallery'   => ['Gallery view 1', 'Gallery view 2', 'Gallery view 3', 'Gallery view 4'],
];

// Parse long_description into paragraphs (split on double newline)
$rawDesc = $dbBranch['long_description'] ?? '';
if (!empty($rawDesc)) {
  $branch['long_description'] = array_filter(array_map('trim', preg_split('/\n\s*\n/', $rawDesc)));
} else {
  $branch['long_description'] = [$branch['summary']];
}

// Build a details line from awards/context
$detailsMap = [
  'westlands' => 'Winner of Best African Cuisine by UP Magazine reader\'s poll and Best Eritrean/Ethiopian Cuisine at the Chef\'s Delight Awards.',
  'karen' => 'Offering authentically Eritrean dishes as well as continental dishes in a beautiful serene environment.',
  'lavington' => 'The standard Asmara atmosphere, with clean lines and a cosy modern mood.',
  'pangani' => 'A branch built for the injera lover and the adventurous eater, with rich Eritrean character.'
];
$branch['details'] = $detailsMap[$slug] ?? 'Authentic Eritrean and Continental dining at Asmara.';

// SEO Enrichment
$pageTitle = $branch['title'] . " | Authentic Eritrean Food";
$pageDescription = implode(' ', $branch['long_description']);
$pageKeywords = $branch['keywords'];
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "Restaurant",
  "name" => $branch['title'],
  "description" => implode(' ', $branch['long_description']),
  "url" => "https://asmara.co.ke/branch?branch=" . $branchKey,
  "telephone" => htmlspecialchars($branch['phone']),
  "servesCuisine" => ["Eritrean", "Ethiopian", "African", "Continental"],
  "keywords" => $branch['keywords'],
  "address" => [
    "@type" => "PostalAddress",
    "streetAddress" => $branch['address'],
    "addressLocality" => "Nairobi",
    "addressCountry" => "KE"
  ]
];

include 'header.php';
?>

  <section class="hero panel-dark" style="min-height: 50vh; padding-top: 180px; padding-bottom: var(--space-lg); text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.3;">
      <img src="<?= htmlspecialchars($branch['hero_img']) ?>" alt="<?= htmlspecialchars($branch['title']) ?> background" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex;">Our Locations</span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem);"><?= htmlspecialchars($branch['title']) ?></h1>
      <p style="margin-top: var(--space-sm); color: var(--color-text-muted-light); font-size: 1.2rem;">
        <?= htmlspecialchars($branch['subtitle']) ?>
      </p>
    </div>
  </section>

  <section class="panel-light" style="border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);">
    <div class="container grid grid-2" style="align-items: start; gap: var(--space-lg);">
      <div class="reveal-on-scroll slide-up">
        <div class="section-header" style="text-align: left; margin: 0 0 var(--space-md) 0;">
          <span class="subtitle">Experience Asmara</span>
          <h2 style="color: var(--color-text-dark);">Authentic Eritrean Culture & Dining</h2>
        </div>
        
        <?php foreach ($branch['long_description'] as $paragraph): ?>
          <p style="margin-bottom: var(--space-sm); color: var(--color-text-muted-dark); font-size: 1.1rem; line-height: 1.8;">
            <?= htmlspecialchars($paragraph) ?>
          </p>
        <?php endforeach; ?>
        
        <div style="margin: var(--space-md) 0; padding-left: var(--space-md); border-left: 3px solid var(--color-primary);">
          <p style="color: var(--color-text-dark); font-family: var(--font-heading); font-size: 1.25rem; font-style: italic; line-height: 1.6; margin: 0;">
            "<?= htmlspecialchars($branch['details']) ?>"
          </p>
        </div>
        <div class="brand-facts-grid" style="margin-top: var(--space-lg);">
          <div class="fact-card">
            <span class="fact-label">Address</span>
            <span class="fact-value"><?= htmlspecialchars($branch['address']) ?></span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Phone</span>
            <span class="fact-value"><?= htmlspecialchars(format_phone($branch['phone'])) ?></span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Email</span>
            <span class="fact-value"><?= htmlspecialchars($branch['email']) ?></span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Cuisine</span>
            <span class="fact-value">Premium Eritrean, Ethiopian & Continental</span>
          </div>
        </div>
      </div>

      <div class="reveal-on-scroll scale-up">
        <div class="media-placeholder" style="aspect-ratio: 4/3; min-height: clamp(250px, 45vw, 420px); overflow: hidden; border-radius: var(--radius-md);">
          <?php 
          $heroImg = $branch['hero_img'];
          $showImg = false;
          if (!empty($heroImg)) {
            $testPath = $heroImg;
            if (strpos($heroImg, '/') === 0) {
              $testPath = $_SERVER['DOCUMENT_ROOT'] . $heroImg;
            } else {
              $testPath = __DIR__ . '/' . $heroImg;
            }
            $parts = explode('/', str_replace('\\', '/', $testPath));
            $resolved = [];
            foreach ($parts as $part) {
              if ($part === '..' && !empty($resolved)) array_pop($resolved);
              elseif ($part !== '' && $part !== '.') $resolved[] = $part;
            }
            $testPath = implode('/', $resolved);
            $showImg = file_exists($testPath);
          }
          ?>
          <?php if ($showImg): ?>
          <img src="<?= htmlspecialchars($heroImg) ?>" alt="<?= htmlspecialchars($branch['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>


  <!-- Branch Menu Highlights -->
  <?php if (!empty($branchMenuItems)): ?>
  <section class="panel-light" style="border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light); padding-top: var(--space-xxl); padding-bottom: var(--space-xxl);">
    <div class="container">
      <div class="section-header" style="text-align: center; margin-bottom: var(--space-xl);">
        <span class="subtitle">Menu Highlights</span>
        <h2 style="color: var(--color-text-dark);">Available at <?= htmlspecialchars(explode(' |', $branch['title'])[0]) ?></h2>
        <p style="color: var(--color-text-muted-dark); max-width: 600px; margin: var(--space-sm) auto 0;">
          A curated selection of our finest dishes available to order at this location today.
        </p>
      </div>

      <div class="grid grid-3">
        <?php foreach (array_slice($branchMenuItems, 0, 6) as $item): 
          $imgUrl = $item['image_url'];
          $showImage = false;
          if (!empty($imgUrl)) {
            $testPath = $imgUrl;
            if (strpos($imgUrl, '/') === 0) {
              $testPath = $_SERVER['DOCUMENT_ROOT'] . $imgUrl;
            } else {
              $testPath = __DIR__ . '/' . $imgUrl;
            }
            $parts = explode('/', str_replace('\\', '/', $testPath));
            $resolved = [];
            foreach ($parts as $part) {
              if ($part === '..' && !empty($resolved)) array_pop($resolved);
              elseif ($part !== '' && $part !== '.') $resolved[] = $part;
            }
            $testPath = implode('/', $resolved);
            $showImage = file_exists($testPath);
          }
        ?>
        <div class="card dish-card reveal-on-scroll slide-up">
          <div class="media-placeholder" style="aspect-ratio: 16/10; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #100b06 0%, #080503 100%); position: relative; padding: 0;">
            <?php if ($showImage): ?>
              <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
              <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; fill: var(--color-primary, #ed174b); opacity: 0.8;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
              <div class="description" style="position: absolute; bottom: 10px; color: #fff; font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($item['name']) ?></div>
            <?php endif; ?>
          </div>
          <div class="dish-info">
            <div class="dish-header">
              <h3 class="dish-title"><?= htmlspecialchars($item['name']) ?></h3>
              <span class="dish-price">KES <?= number_format($item['price']) ?></span>
            </div>
            <p class="dish-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>
            <div class="tag-container" style="margin-top: 10px;">
              <span class="badge" style="background: rgba(237, 23, 75, 0.08); color: var(--color-primary);"><?= htmlspecialchars(ucfirst($item['category'])) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="text-align: center; margin-top: var(--space-xl);">
        <a href="/menu" class="btn btn-outline-dark" style="padding: 12px 28px;">View Full Menu</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Branch Events Highlights -->
  <?php if (!empty($branchEvents)): ?>
  <section class="section-green" style="padding-top: var(--space-xxl); padding-bottom: var(--space-xxl);">
    <div class="container">
      <div class="section-header" style="text-align: center; margin-bottom: var(--space-xl);">
        <span class="subtitle">Upcoming Events</span>
        <h2>Events at <?= htmlspecialchars(explode(' |', $branch['title'])[0]) ?></h2>
        <p style="color: var(--color-text-muted); max-width: 600px; margin: var(--space-sm) auto 0;">
          Join us for special occasions, themed dinners, and corporate gatherings happening right here.
        </p>
      </div>

      <div class="grid grid-3">
        <?php foreach ($branchEvents as $event): ?>
        <div class="card event-card reveal-on-scroll slide-up" style="overflow: hidden; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background: var(--color-surface-light, #ffffff);">
          <div style="background: linear-gradient(135deg, #ed174b 0%, #c41140 100%); color: white; padding: var(--space-md);">
            <h3 class="font-heading-h3" style="color: white; margin: 0 0 var(--space-xs) 0; font-size: 1.3rem;">
              <?= htmlspecialchars($event['title'] ?? 'Event') ?>
            </h3>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 0.8rem; border: none; padding: 2px 8px;">
              <?= htmlspecialchars(ucfirst($event['category'] ?? 'Corporate')) ?>
            </span>
          </div>
          <div style="padding: var(--space-md); color: var(--color-text-dark, #333);">
            <p style="font-size: 0.9rem; line-height: 1.6; margin-bottom: var(--space-md); color: #555;">
              <?= htmlspecialchars($event['description'] ?? '') ?>
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm); text-align: center;">
              <div style="background: #f9f9f9; padding: 8px; border-radius: 6px;">
                <div style="font-size: 0.75rem; color: #777;">Capacity</div>
                <div style="font-weight: 700; color: #ed174b;"><?= (int)($event['capacity'] ?? 0) ?> guests</div>
              </div>
              <div style="background: #f9f9f9; padding: 8px; border-radius: 6px;">
                <div style="font-size: 0.75rem; color: #777;">Price</div>
                <div style="font-weight: 700; color: #ed174b;">KES <?= number_format($event['price_per_person'] ?? 0) ?></div>
              </div>
            </div>
          </div>
          <div style="padding: var(--space-md); border-top: 1px solid #eee;">
            <a href="/booking" class="btn btn-primary btn-sm" style="width: 100%; text-align: center; text-decoration: none; display: block; box-sizing: border-box;">Book Event Space</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="panel-dark">
    <div class="container grid grid-2" style="align-items: center; gap: var(--space-lg);">
      <div class="reveal-on-scroll slide-up">
        <div class="section-header" style="text-align: left; margin: 0 0 var(--space-md) 0;">
          <span class="subtitle">Contact</span>
          <h2>Ready for an unforgettable meal?</h2>
          <p style="color: var(--color-text-muted-light); margin-top: var(--space-xs);">
            Join us at our <?= htmlspecialchars(explode(' |', $branch['title'])[0]) ?> branch to experience true African hospitality.
          </p>
        </div>
        <div class="booking-side-card" style="margin-top: var(--space-md);">
          <ul class="booking-contact-list">
            <li><strong>Location:</strong> <?= htmlspecialchars($branch['address']) ?></li>
            <li><strong>Phone:</strong> <?= htmlspecialchars(format_phone($branch['phone'])) ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($branch['email']) ?></li>
          </ul>
        </div>
      </div>
      <div class="reveal-on-scroll scale-up" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg); height: clamp(250px, 45vw, 420px); position: relative; border: 1px solid rgba(255, 255, 255, 0.1);">
        <?php
        $maps = [
          'westlands' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.847551061905!2d36.8041071!3d-1.2639144!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1741a3962637%3A0xe556b69b0fa69dbb!2sAsmara%20Restaurant%20-%20Westlands!5e0!3m2!1sen!2ske!4v1719650000000!5m2!1sen!2ske',
          'lavington' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.824707172089!2d36.7725916!3d-1.2787093!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1a6b0c2e3995%3A0x6b10086d4911d95!2sAsmara%20Restaurant%20Lavington!5e0!3m2!1sen!2ske!4v1719650000000!5m2!1sen!2ske',
          'karen' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.7516849492194!2d36.7381504!3d-1.3255152!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1a26ad019483%3A0x9bbad51a0ee331d2!2sAsmara%20Restaurant%20Karen!5e0!3m2!1sen!2ske!4v1719650000000!5m2!1sen!2ske',
          'pangani' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8359400262114!2d36.8377759!3d-1.2711019!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1704e9c70817%3A0xc3b838706d871ab1!2sAsmara%20Restaurant%20Pangani!5e0!3m2!1sen!2ske!4v1719650000000!5m2!1sen!2ske'
        ];
        $embedUrl = $maps[$slug] ?? $maps['westlands'];
        ?>
        <iframe 
          src="<?= $embedUrl ?>" 
          width="100%" 
          height="100%" 
          style="border:0; display: block;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>

<?php include 'footer.php'; ?>