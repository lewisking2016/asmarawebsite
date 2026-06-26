<?php
require_once __DIR__ . '/../backend/database/BranchRepository.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Map clean URL paths to page identifiers for active-link highlighting
$cleanPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanPath = rtrim($cleanPath, '/');
if ($cleanPath === '' || $cleanPath === '/frontend/index.php') $currentPage = 'index.php';
elseif ($cleanPath === '/about') $currentPage = 'about.php';
elseif ($cleanPath === '/menu') $currentPage = 'menu.php';
elseif ($cleanPath === '/booking') $currentPage = 'booking.php';
elseif ($cleanPath === '/events') $currentPage = 'events.php';

// SEO Defaults
$siteName = "Asmara Restaurant";
$defaultTitle = "Asmara Restaurant - Premium Eritrean & Continental Dining in Nairobi";
$defaultDescription = "Savor authentic Horn of Africa hospitality and contemporary continental cuisine. Visit our spaces in Westlands, Karen, Lavington, and Pangani.";
$baseUrl = "https://asmara.co.ke"; // Replace with actual domain if different

$pageTitle = isset($pageTitle) ? $pageTitle . " | " . $siteName : $defaultTitle;
$pageDescription = isset($pageDescription) ? $pageDescription : $defaultDescription;
$pageKeywords = isset($pageKeywords) ? $pageKeywords : "Eritrean restaurant Nairobi, Asmara restaurant locations, Westlands, Karen, Lavington, Pangani, best African food Kenya, Ethiopian food Nairobi, Zigni, Injera, Kitfo, cultural dining";
$canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : $baseUrl . '/' . $currentPage;
$ogImage = isset($ogImage) ? $ogImage : $baseUrl . '/logo/asmara%20logo.png';

function nav_class($target, $currentPage) {
  return $currentPage === $target ? 'active' : '';
}

function nav_aria_current($target, $currentPage) {
  return $currentPage === $target ? 'aria-current="page"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8'); ?>">
  
  <!-- Canonical Tag -->
  <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- AI / Search Engine Robots -->
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  
  <!-- Structured Data (Schema.org) for AI & Rich Snippets -->
  <?php if (isset($pageSchema)): ?>
  <script type="application/ld+json">
    <?php echo json_encode($pageSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
  </script>
  <?php else: 
    // Default Organization/Restaurant Schema
    $defaultSchema = [
      "@context" => "https://schema.org",
      "@type" => "Restaurant",
      "name" => $siteName,
      "image" => $baseUrl . '/logo/asmara%20logo.png',
      "description" => $defaultDescription,
      "url" => $baseUrl,
      "telephone" => "+254713610707",
      "servesCuisine" => ["Eritrean", "Continental", "African", "Ethiopian"],
      "address" => [
        "@type" => "PostalAddress",
        "addressLocality" => "Nairobi",
        "addressRegion" => "Nairobi County",
        "addressCountry" => "KE"
      ],
      "department" => array_map(function($b) {
        return [
          "@type" => "Restaurant",
          "name" => "Asmara " . $b['name'],
          "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $b['address'],
            "addressLocality" => "Nairobi",
            "addressCountry" => "KE"
          ],
          "telephone" => $b['phone']
        ];
      }, (new BranchRepository())->getAll()),
      "sameAs" => [
        // Add social links here if available
      ]
    ];
  ?>
  <script type="application/ld+json">
    <?php echo json_encode($defaultSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
  </script>
  <?php endif; ?>

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Floating Glassmorphic Header Navigation -->
  <header>
    <div class="navbar">
      <a href="/" class="logo" id="navLogo" aria-label="Asmara Restaurant home">
        <img src="logo/asmara%20logo.png" alt="Asmara Restaurant" class="logo-image">
      </a>

      <nav class="desktop-nav-shell" aria-label="Main Navigation">
        <ul class="nav-links">
          <li><a href="/" id="linkHome" class="<?php echo nav_class('index.php', $currentPage); ?>" <?php echo nav_aria_current('index.php', $currentPage); ?>>Home</a></li>
          <li><a href="/about" id="linkAbout" class="<?php echo nav_class('about.php', $currentPage); ?>" <?php echo nav_aria_current('about.php', $currentPage); ?>>Our Story</a></li>
          <li><a href="/#branches" id="linkBranches">Branches</a></li>
          <li><a href="/events" id="linkEvents" class="<?php echo nav_class('events.php', $currentPage); ?>" <?php echo nav_aria_current('events.php', $currentPage); ?>>Events</a></li>
          <li><a href="/menu" id="linkMenu" class="<?php echo nav_class('menu.php', $currentPage); ?>" <?php echo nav_aria_current('menu.php', $currentPage); ?>>Menu</a></li>
          <li><a href="/booking" id="linkBooking" class="<?php echo nav_class('booking.php', $currentPage); ?>" <?php echo nav_aria_current('booking.php', $currentPage); ?>>Reservation</a></li>
        </ul>
      </nav>

      <div class="nav-actions">
        <a href="/booking" class="btn btn-primary btn-sm <?php echo nav_class('booking.php', $currentPage); ?>" id="btnHeaderReserve" <?php echo nav_aria_current('booking.php', $currentPage); ?>>Reservation</a>
      </div>

      <button class="mobile-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobileDrawer" id="btnMobileToggle">
        <span class="mobile-toggle-lines" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </span>
        <span class="mobile-toggle-copy">Menu</span>
      </button>
    </div>
  </header>

  <div class="mobile-nav-backdrop" id="mobileBackdrop" hidden></div>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-nav" id="mobileDrawer" aria-hidden="true">
    <div class="mobile-nav-panel">
      <div class="mobile-nav-top">
        <div>
          <span class="mobile-nav-kicker">Asmara Nairobi</span>
          <h2>Navigate the house</h2>
        </div>
        <p>Four branches, one booking flow, and the full menu a tap away.</p>
      </div>

      <nav aria-label="Mobile Navigation">
        <ul class="mobile-nav-links">
          <li><a href="/" id="mobileLinkHome" class="<?php echo nav_class('index.php', $currentPage); ?>" <?php echo nav_aria_current('index.php', $currentPage); ?>>Home</a></li>
          <li><a href="/about" id="mobileLinkAbout" class="<?php echo nav_class('about.php', $currentPage); ?>" <?php echo nav_aria_current('about.php', $currentPage); ?>>Our Story</a></li>
          <li><a href="/#branches" id="mobileLinkBranches">Branches</a></li>
          <li><a href="/events" id="mobileLinkEvents" class="<?php echo nav_class('events.php', $currentPage); ?>" <?php echo nav_aria_current('events.php', $currentPage); ?>>Events</a></li>
          <li><a href="/menu" id="mobileLinkMenu" class="<?php echo nav_class('menu.php', $currentPage); ?>" <?php echo nav_aria_current('menu.php', $currentPage); ?>>Menu</a></li>
          <li><a href="/booking" id="mobileLinkBooking" class="<?php echo nav_class('booking.php', $currentPage); ?>" <?php echo nav_aria_current('booking.php', $currentPage); ?>>Reservation</a></li>
        </ul>
      </nav>

      <div class="mobile-nav-card">
        <span class="mobile-nav-card-label">Quick contact</span>
        <a href="tel:+254713610707">+254 713 610 707</a>
        <p>Best for same-day table requests and direct branch questions.</p>
      </div>

      <a href="/booking" class="btn btn-primary mobile-nav-cta <?php echo nav_class('booking.php', $currentPage); ?>" id="mobileBtnReserve" <?php echo nav_aria_current('booking.php', $currentPage); ?>>Reserve a Table</a>
    </div>
  </div>
