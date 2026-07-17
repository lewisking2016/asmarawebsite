<?php 
$pageTitle = "Our Story";
$pageDescription = "Learn about Asmara Restaurants, established in 2009. We offer unique dining experiences infusing Eritrean culture and culinary excellence in an afro-contemporary setting.";
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "AboutPage",
  "name" => "About Asmara Restaurant",
  "description" => "Learn about Asmara Restaurants, established in 2009. We offer unique dining experiences infusing Eritrean culture and culinary excellence in an afro-contemporary setting.",
  "url" => "https://asmara.co.ke/about"
];
include 'header.php'; 
?>

  <!-- Premium Hero Section with Parallax -->
  <section class="about-hero" style="min-height: 60vh; padding-top: 200px; padding-bottom: 80px; text-align: center; position: relative; overflow: hidden;">
    <div class="about-hero-bg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
      <img src="images/optimized/Lavington-25.jpg" alt="Asmara Restaurant Story Background" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.4) saturate(1.2);">
    </div>
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.7) 100%); z-index: 1;"></div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 2;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex; color: rgba(255,255,255,0.85);">Our Story</span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem); color: #ffffff; text-shadow: 0 2px 12px rgba(0,0,0,0.5);">ABOUT ASMARA</h1>
      <p style="margin-top: var(--space-sm); color: rgba(255,255,255,0.8); font-size: 1.2rem; text-shadow: 0 1px 6px rgba(0,0,0,0.4);">
        Asmara Restaurants are Eritrean/Continental restaurants established in 2009, offering unique dining experiences infused with culture and culinary excellence.
      </p>
    </div>
  </section>

  <!-- About Content Section -->
  <section class="panel-light" style="border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light);">
    <div class="container grid grid-2" style="align-items: center; gap: var(--space-lg);">
      <div class="reveal-on-scroll scale-up">
        <div class="media-placeholder" style="aspect-ratio: 4/3; overflow: hidden; border-radius: var(--radius-md);">
          <img src="images/optimized/Lavington-42.jpg" alt="Asmara Kenya Established 2009" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
        </div>
      </div>
      <div class="reveal-on-scroll slide-up">
        <div class="section-header" style="text-align: left; margin: 0 0 var(--space-md) 0;">
          <span class="subtitle">About Asmara</span>
          <h2 style="color: #1e150d;">Eritrean and Continental dining in an afro-contemporary setting</h2>
        </div>
        <p style="margin-bottom: var(--space-sm); color: #4a4038; font-size: 1.05rem; line-height: 1.75;">
          Asmara Restaurants are Eritrean/Continental restaurants established in 2009. We offer unique dining experiences infusing culture and culinary excellence in afro-contemporary setting.
        </p>
        <p style="margin-bottom: var(--space-md); color: #4a4038; font-size: 1.05rem; line-height: 1.75;">
          We are based in Pangani along Juja Road, Westlands along General Mathenge Lane, Karen along Ngong' Road, and Lavington along Othaya Road.
        </p>
        <div class="brand-facts-grid" style="margin-bottom: var(--space-md);">
          <div class="fact-card">
            <span class="fact-label">Mission</span>
            <span class="fact-value" style="color: #1e150d;">To consistently provide quality food and service to our guests; exceeding their expectations.</span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Vision</span>
            <span class="fact-value" style="color: #1e150d;">To offer a unique dining experience.</span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Quote</span>
            <span class="fact-value" style="color: #1e150d;">One cannot think well, love well, sleep well, if one has not dined well.</span>
          </div>
          <div class="fact-card">
            <span class="fact-label">Established</span>
            <span class="fact-value" style="color: #1e150d;">2009</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Recognition & Branches Section — Fixed text visibility -->
  <section class="about-recognition" style="padding: clamp(80px, 12vw, 160px) 0; position: relative; overflow: hidden;">
    <!-- Background image -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
      <img src="images/optimized/Lavington-11.jpg" alt="Asmara Dining Award Winning Experience" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.35) saturate(1.1);">
    </div>
    <!-- Dark overlay for text contrast -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(30,21,13,0.55) 100%); z-index: 1;"></div>
    
    <div class="container" style="position: relative; z-index: 2;">
      <div class="grid grid-2" style="align-items: start; gap: var(--space-xl);">
        
        <!-- Awards / Recognition Text -->
        <div class="reveal-on-scroll slide-up">
          <span class="subtitle" style="color: var(--color-primary);">Recognition</span>
          <h2 style="color: #ffffff; font-size: clamp(2rem, 5vw, 3.2rem); line-height: 1.1; margin-bottom: var(--space-md); text-shadow: 0 2px 8px rgba(0,0,0,0.3);">Quality food and service, with a unique dining experience</h2>
          <div style="background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: var(--space-md);">
            <p style="color: #ffffff; font-size: 1.05rem; line-height: 1.75; margin-bottom: var(--space-sm);">
              2015 Winner of Best African Cuisine by UP Magazine reader's poll. 2015 to 2016 runner's up award for best wine list at the Taste Awards.
            </p>
            <p style="color: #ffffff; font-size: 1.05rem; line-height: 1.75;">
              2016 Chef's Delight Award for best Eritrean/Ethiopian Cuisine. 2016 Certificate of Excellence from Trip Advisor.
            </p>
          </div>
        </div>

        <!-- Branch Cards -->
        <div class="brand-facts-grid reveal-on-scroll scale-up" style="gap: var(--space-sm);">
          <?php
          require_once __DIR__ . '/../backend/database/BranchRepository.php';
          $branchRepo = new BranchRepository();
          $allBranches = $branchRepo->getAll();
          
          $fallbacks = [
            'Lavington' => 'Ideally situated in Lavington, this branch offers the ultimate dining experience of Eritrean and Continental dishes for breakfast, lunch, and dinner.',
            'Karen' => 'In the leafy Karen suburb, this restaurant sits on a large and picturesque location with delicious quality food and excellent service.',
            'Westlands' => 'The modern indoor and outdoor setting perfectly complements the afro-contemporary Eritrean and Continental cuisine offered here.',
            'Pangani' => 'A favourite of the Eritrean community and locals, described by The Nairobian as a little piece of Eritrea.'
          ];

          foreach ($allBranches as $b) {
            $name = htmlspecialchars($b['name']);
            $summary = !empty($b['summary']) ? $b['summary'] : ($fallbacks[$b['name']] ?? htmlspecialchars($b['address']));
            ?>
            <div class="fact-card" style="background: rgba(255,255,255,0.95); border: 1px solid rgba(237,23,75,0.1); border-left: 4px solid var(--color-primary); box-shadow: 0 8px 24px rgba(0,0,0,0.12); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
              <span class="fact-label" style="color: var(--color-primary); font-weight: 700;"><?php echo $name; ?></span>
              <span class="fact-value" style="color: #1e150d; font-size: 0.92rem; line-height: 1.6;"><?php echo htmlspecialchars($summary); ?></span>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
  </section>

<?php include 'footer.php'; ?>
