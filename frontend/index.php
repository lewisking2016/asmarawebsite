<?php
require_once __DIR__ . '/../backend/database/BranchRepository.php';
$branchRepo = new BranchRepository();
$dbBranches = $branchRepo->getAll();

include 'header.php';

$galleryImagePaths = glob(__DIR__ . '/images/optimized/*.jpg');
natsort($galleryImagePaths);
$galleryImagePaths = array_values($galleryImagePaths);
$galleryRowCount = 3;
$galleryChunkSize = $galleryRowCount > 0 ? max(1, (int) ceil(count($galleryImagePaths) / $galleryRowCount)) : 1;
$galleryRows = $galleryImagePaths ? array_chunk($galleryImagePaths, $galleryChunkSize) : [];

function gallery_asset_path($absolutePath) {
  $optimizedPath = __DIR__ . '/images/optimized/' . basename($absolutePath);
  if (file_exists($optimizedPath)) {
    return 'images/optimized/' . basename($absolutePath);
  }

  return 'images/' . basename($absolutePath);
}

function asset_url($relativePath) {
  $segments = array_map('rawurlencode', explode('/', $relativePath));
  return implode('/', $segments);
}

function hero_video_poster_path($relativePath) {
  $posterName = pathinfo(basename($relativePath), PATHINFO_FILENAME) . '.jpg';
  $posterPath = __DIR__ . '/hero-videos/posters/' . $posterName;
  if (file_exists($posterPath)) {
    return 'hero-videos/posters/' . $posterName;
  }

  return null;
}

function gallery_label_from_index($index) {
  return 'Asmara Moment';
}

function gallery_size_class($index) {
  $sizeClasses = ['gallery-card--wide', 'gallery-card--tall', 'gallery-card--square', 'gallery-card--large', 'gallery-card--panoramic'];
  return $sizeClasses[$index % count($sizeClasses)];
}

$heroVideos = [
  ['src' => 'hero-videos/grill-dishes.mp4', 'label' => 'Grill dishes'],
];

$galleryFeaturedPath = 'images/optimized/Lavington-2.jpg';
$branchImageMap = [
  'westlands' => 'images/optimized/Lavington-5.jpg',
  'karen' => 'images/optimized/Lavington-10.jpg',
  'lavington' => 'images/optimized/Lavington-20.jpg',
  'pangani' => 'images/optimized/Lavington-30.jpg',
];
?>

  <section class="hero hero-video-minimal panel-dark">
    <div class="hero-video-minimal-media" aria-hidden="true">
      <video class="hero-video-minimal-player" autoplay muted loop playsinline preload="metadata" poster="<?php echo htmlspecialchars(asset_url('hero-videos/posters/grill-dishes.jpg'), ENT_QUOTES, 'UTF-8'); ?>">
        <source src="<?php echo htmlspecialchars(asset_url('hero-videos/grill-dishes.mp4'), ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
      </video>
      <div class="hero-video-minimal-overlay"></div>
    </div>

    <div class="container hero-video-minimal-shell">
      <div class="hero-video-minimal-copy reveal-on-scroll slide-up">
        <span class="hero-tagline">Asmara Restaurants</span>
        <h1>Elegant Eritrean<br>dining in Nairobi</h1>
        <p>
          Since 2009, Asmara has served calm, well-run dining rooms<br class="hero-break"> across Westlands, Karen, Lavington, and Pangani.
        </p>
        <div class="hero-actions">
          <a href="booking.php" class="btn btn-primary" id="heroBtnBook">Reserve a Table</a>
          <a href="menu.php" class="btn btn-outline" id="heroBtnMenu">View Menu</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Brief Asmara Intro -->
  <section id="about-intro" class="asmara-intro panel-light" style="border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);">
    <div class="container asmara-intro-compact reveal-on-scroll slide-up">
      <span class="subtitle">Asmara in Brief</span>
      <h2 style="color: var(--color-text-dark);">Four Nairobi locations, one standard</h2>
      <p class="asmara-intro-lead">
        Asmara keeps the focus on good food, steady service, and a clean dining environment built for everyday business lunches and relaxed dinners.
      </p>
      <p class="asmara-intro-copyline">
        The brand balances Eritrean clay-pot cooking with a calm restaurant experience, so each branch feels familiar, clear, and easy to return to.
      </p>

      <div class="asmara-brief-grid">
        <article class="asmara-brief-card">
          <span class="asmara-brief-label">Since 2009</span>
          <h3>Established and steady</h3>
          <p>Over the years, Asmara has kept a consistent dining standard across the city.</p>
        </article>
        <article class="asmara-brief-card">
          <span class="asmara-brief-label">Four branches</span>
          <h3>Easy to find</h3>
          <p>Westlands, Karen, Lavington, and Pangani give guests a branch that suits where they are.</p>
        </article>
        <article class="asmara-brief-card">
          <span class="asmara-brief-label">Cuisine mix</span>
          <h3>Eritrean plus continental</h3>
          <p>The menu covers clay-pot specialties, grilled dishes, lighter plates, and familiar continental choices.</p>
        </article>
        <article class="asmara-brief-card">
          <span class="asmara-brief-label">Dining style</span>
          <h3>Calm and corporate</h3>
          <p>Designed for business lunches, family meals, and guests who prefer a quieter dining room.</p>
        </article>
      </div>
    </div>
  </section>
    <!-- Branches / Locations tabbed grid -->
  <section id="branches" class="panel-light" data-tab-container style="border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);">
    <div class="container">
      <div class="section-header">
        <span class="subtitle">Find Your Space</span>
        <h2 style="color: var(--color-text-dark);">Our branches across Nairobi</h2>
      </div>

      <!-- Tab Navigation -->
      <div class="tab-nav">
        <button class="tab-btn active" data-tab-target="all" id="btnBranchAll">All Locations</button>
        <?php foreach ($dbBranches as $b): 
          $slug = strtolower($b['name']);
        ?>
          <button class="tab-btn" data-tab-target="<?php echo $slug; ?>" id="btnBranch<?php echo ucfirst($slug); ?>"><?php echo htmlspecialchars($b['name']); ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Outposts Grid -->
      <div class="grid grid-2" id="branchesGrid">
        
        <?php foreach ($dbBranches as $b): 
          $slug = strtolower($b['name']);
          $branchImg = $branchImageMap[$slug] ?? $galleryFeaturedPath;
          
          $summary = !empty($b['summary']) ? $b['summary'] : htmlspecialchars($b['address']);

          // Get the first paragraph of long_description for the rich text panel
          $rawDesc = $b['long_description'] ?? '';
          if (!empty($rawDesc)) {
            $paragraphs = array_filter(array_map('trim', preg_split('/\n\s*\n/', $rawDesc)));
            $richDesc = implode(' ', $paragraphs);
          } else {
            $richDesc = $summary;
          }
        ?>
        <!-- <?php echo htmlspecialchars($b['name']); ?> Card -->
        <div class="card branch-card" data-tab-item="<?php echo $slug; ?> all">
          <div class="branch-card-media">
            <?php if (!empty($branchImg)): ?>
              <img src="<?php echo htmlspecialchars(asset_url(gallery_asset_path($branchImg)), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($b['name']); ?> branch preview" loading="eager" decoding="async">
            <?php endif; ?>
            <span class="badge badge-gold branch-card-badge"><?php echo htmlspecialchars($b['name']); ?></span>
          </div>
          <div class="branch-card-body">
            <div class="branch-header">
              <h3 style="color: var(--color-text-dark);"><?php echo htmlspecialchars($b['name']); ?> | Asmara</h3>
            </div>
            <p class="branch-summary"><?php echo htmlspecialchars($summary); ?></p>
            <ul class="branch-info-list">
              <li><strong>Location:</strong> <?php echo htmlspecialchars($b['address']); ?></li>
              <li><strong>Phone:</strong> <?php echo htmlspecialchars($b['phone']); ?></li>
            </ul>
            <div class="branch-card-actions">
              <a href="branch.php?branch=<?php echo $slug; ?>" class="btn btn-outline-dark">View More</a>
            </div>
          </div>
        </div>
        
        <!-- <?php echo htmlspecialchars($b['name']); ?> Rich Text (Shows Beside Card) -->
        <div class="branch-side-content reveal-on-scroll slide-up hidden" data-tab-item="<?php echo $slug; ?>" style="padding: var(--space-md); border-left: 4px solid var(--color-primary); background: var(--color-surface-light); border-radius: var(--radius-sm); box-shadow: 0 10px 30px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center;">
          <h2 style="color: var(--color-text-dark); margin-bottom: var(--space-sm); font-family: 'Playfair Display', Georgia, serif; font-size: 2rem; font-weight: 600; line-height: 1.2;">The <?php echo htmlspecialchars($b['name']); ?> Experience</h2>
          <p style="font-size: 1.1rem; color: var(--color-text-muted-dark); line-height: 1.8; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif;">
            <?php echo htmlspecialchars($richDesc); ?>
          </p>
        </div>
        <?php endforeach; ?>

      </div>
    </div>
  </section>

  <!-- Events & Catering Section -->
  <section id="events" class="panel-light" style="border-top: 1px solid var(--color-border-light); padding-top: var(--space-xxl); padding-bottom: var(--space-xxl);">
    <div class="container">
      <div class="section-header" style="text-align: center; margin-bottom: var(--space-xxl);">
        <span class="subtitle">Celebrations & Gatherings</span>
        <h2 style="color: var(--color-text-dark); font-size: clamp(2rem, 5vw, 3.5rem);">Host Your Moments</h2>
        <p style="color: var(--color-text-muted-dark); font-size: clamp(1rem, 1.2vw, 1.1rem); max-width: 600px; margin: var(--space-md) auto 0;">From intimate gatherings to corporate events, Asmara provides the perfect setting for your special occasions across all four locations.</p>
      </div>

      <div class="grid grid-3" style="gap: var(--space-lg); margin-bottom: var(--space-lg);">
        <div class="card reveal-on-scroll scale-up" style="padding: var(--space-lg); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); background-color: var(--color-surface-light); transition: all 0.3s ease;">
          <div style="width: 48px; height: 48px; background: rgba(237, 23, 75, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-md);">
            <svg viewBox="0 0 24 24" style="width: 24px; height: 24px; fill: var(--color-primary);" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.3-1.54c-.3-.36-.77-.58-1.27-.58-.9 0-1.66.72-1.66 1.62 0 .9.75 1.62 1.66 1.62.5 0 .99-.22 1.3-.58l1.3 1.54 2.75-3.54c.29-.37.23-.92-.15-1.21-.37-.29-.92-.23-1.21.15z"/></svg>
          </div>
          <h3 style="color: var(--color-text-dark); font-size: 1.2rem; margin-bottom: var(--space-sm); font-weight: 600;">Corporate Events</h3>
          <p style="color: var(--color-text-muted-dark); line-height: 1.6; font-size: 0.95rem;">Professional settings for conferences, product launches, team building, and business dinners across all branches.</p>
        </div>

        <div class="card reveal-on-scroll scale-up" style="padding: var(--space-lg); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); background-color: var(--color-surface-light); transition: all 0.3s ease;">
          <div style="width: 48px; height: 48px; background: rgba(237, 23, 75, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-md);">
            <svg viewBox="0 0 24 24" style="width: 24px; height: 24px; fill: var(--color-primary);" aria-hidden="true"><path d="M20 3H4V1h16v2zm1 5H3c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2v-9c0-1.1-.9-2-2-2zm0 11H3v-9h18v9zm-3-7H7c-1.1 0-2 .9-2 2v1h12v-1c0-1.1-.9-2-2-2z"/></svg>
          </div>
          <h3 style="color: var(--color-text-dark); font-size: 1.2rem; margin-bottom: var(--space-sm); font-weight: 600;">Social Celebrations</h3>
          <p style="color: var(--color-text-muted-dark); line-height: 1.6; font-size: 0.95rem;">Birthdays, anniversaries, engagements, and milestone celebrations in elegant, contemporary spaces.</p>
        </div>

        <div class="card reveal-on-scroll scale-up" style="padding: var(--space-lg); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); background-color: var(--color-surface-light); transition: all 0.3s ease;">
          <div style="width: 48px; height: 48px; background: rgba(237, 23, 75, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-md);">
            <svg viewBox="0 0 24 24" style="width: 24px; height: 24px; fill: var(--color-primary);" aria-hidden="true"><path d="M11.99 5.52c-1.11 0-1.98.9-1.98 2s.87 2 1.98 2c1.11 0 1.98-.9 1.98-2s-.87-2-1.98-2zm6.93 2.53C18.5 7.07 16.64 6 14.4 6c-.89 0-1.78.19-2.6.49-1.86.62-3.35 1.88-4.3 3.43-1.9 3.12-1.71 7.04.48 9.95.72.9 1.66 1.72 2.75 2.28 1.03.51 2.17.77 3.35.77s2.32-.26 3.35-.77c1.09-.56 2.03-1.38 2.75-2.28 2.19-2.91 2.38-6.83.48-9.95zm-1.56 8.99c-.52.65-1.27 1.23-2.16 1.66-.84.41-1.76.62-2.71.62s-.87-.21-1.71-.62c-.89-.43-1.64-1.01-2.16-1.66-1.59-2.11-1.73-5.09-.32-7.35.63-1.03 1.54-1.84 2.66-2.35 1.1-.5 2.25-.75 3.41-.75 1.16 0 2.31.25 3.41.75 1.12.51 2.03 1.32 2.66 2.35 1.41 2.26 1.27 5.24-.32 7.35z"/></svg>
          </div>
          <h3 style="color: var(--color-text-dark); font-size: 1.2rem; margin-bottom: var(--space-sm); font-weight: 600;">Catering Services</h3>
          <p style="color: var(--color-text-muted-dark); line-height: 1.6; font-size: 0.95rem;">Customized Eritrean and Continental menus for off-site events, meetings, and intimate dinner parties.</p>
        </div>
      </div>

      <div style="text-align: center; padding: var(--space-lg); background: rgba(237, 23, 75, 0.04); border-radius: var(--radius-md); border: 1px solid rgba(237, 23, 75, 0.08);">
        <p style="color: var(--color-text-muted-dark); margin-bottom: var(--space-sm); font-size: 0.95rem;">Ready to plan your event?</p>
        <a href="booking.php" class="btn btn-primary">Contact Us for Events</a>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
