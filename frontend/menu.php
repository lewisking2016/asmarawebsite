<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../backend/database/MenuRepository.php';
require_once __DIR__ . '/../backend/database/BranchRepository.php';

$menuRepo = new MenuRepository();
$dbItems = $menuRepo->getAll(true);

$branchRepo = new BranchRepository();
$allBranches = $branchRepo->getAll();

// Get unique categories from items
$categories = [];
foreach ($dbItems as $item) {
    if (!empty($item['category']) && !in_array($item['category'], $categories)) {
        $categories[] = $item['category'];
    }
}

function getCategoryLabel($cat) {
    $labels = [
        'mains' => 'Main Course',
        'appetizers' => 'Starters',
        'desserts' => 'Desserts',
        'beverages' => 'Beverages',
        'starters' => 'Starters',
        'drinks' => 'Drinks'
    ];
    return $labels[strtolower($cat)] ?? ucfirst($cat);
}

$schemaMenuItems = [];
foreach ($dbItems as $item) {
    $schemaMenuItems[] = [
        "@type" => "MenuItem",
        "name" => $item['name'],
        "description" => $item['description'] ?? '',
        "offers" => [
            "@type" => "Offer",
            "price" => (float)$item['price'],
            "priceCurrency" => "KES"
        ]
    ];
}

$pageTitle = "Our Menu";
$pageDescription = "Explore the Asmara Restaurant menu. A selection of authentic Eritrean dishes and continental highlights available for lunch and dinner.";
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "Menu",
  "name" => "Asmara Restaurant Menu",
  "description" => "A selection of authentic Eritrean dishes and continental highlights.",
  "url" => "https://asmara.co.ke/menu",
  "mainEntityOfPage" => "https://asmara.co.ke/menu",
  "hasMenuItem" => $schemaMenuItems
];
include 'header.php'; 
?>

  <!-- Menu Banner -->
  <section class="hero panel-dark" style="min-height: 50vh; padding-top: 180px; padding-bottom: var(--space-lg); text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.3;">
      <img src="images/optimized/Lavington-2.jpg" alt="Asmara Authentic Menu Background" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex;">Asmara Menu</span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem);">LUNCH AND DINNER</h1>
      <p style="margin-top: var(--space-sm); color: var(--color-text-muted-light); font-size: 1.2rem;">
        Explore our authentic Horn of Africa specialties and contemporary continental dishes, available across our Nairobi locations.
      </p>
    </div>
  </section>

  <!-- Menu Catalog -->
  <section class="panel-light" style="padding-top: var(--space-lg); padding-bottom: var(--space-xxl);">
    <div class="container">
      
      <!-- Premium Multi-dimensional Filter Bar -->
      <div class="filter-controls-container" style="background: var(--color-surface-light, #ffffff); padding: 24px; border-radius: var(--radius-md, 8px); box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--color-border-light, #eaeaea); margin-bottom: var(--space-lg); display: flex; flex-direction: column; gap: 20px;">
        <!-- Category Filters -->
        <div>
          <span style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-text-muted-dark, #666); margin-bottom: 10px; font-weight: 700;">Filter by Category</span>
          <div class="tab-nav category-filter-row" style="gap: 8px; flex-wrap: wrap; margin: 0; justify-content: flex-start;">
            <button class="tab-btn category-btn active" data-category="all" style="padding: 10px 20px; font-size: 0.9rem;">All Categories</button>
            <?php foreach ($categories as $cat): ?>
              <button class="tab-btn category-btn" data-category="category-<?php echo htmlspecialchars($cat); ?>" style="padding: 10px 20px; font-size: 0.9rem;"><?php echo htmlspecialchars(getCategoryLabel($cat)); ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Location Filters -->
        <div style="border-top: 1px solid var(--color-border-light, #eaeaea); padding-top: 15px;">
          <span style="display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-text-muted-dark, #666); margin-bottom: 10px; font-weight: 700;">Filter by Location</span>
          <div class="tab-nav branch-filter-row" style="gap: 8px; flex-wrap: wrap; margin: 0; justify-content: flex-start;">
            <button class="tab-btn branch-btn active" data-branch="all" style="padding: 10px 20px; font-size: 0.9rem;">All Locations</button>
            <?php foreach ($allBranches as $b): ?>
              <button class="tab-btn branch-btn" data-branch="branch-<?php echo htmlspecialchars($b['id']); ?>" style="padding: 10px 20px; font-size: 0.9rem;"><?php echo htmlspecialchars($b['name']); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="grid grid-3" id="menuGridContainer">
        <?php foreach ($dbItems as $item): 
          $itemBranchIds = [];
          $branchNames = [];
          
          if (empty($item['available_branches'])) {
            foreach ($allBranches as $b) {
              $itemBranchIds[] = 'branch-' . $b['id'];
              $branchNames[] = $b['name'];
            }
            $isUniversal = true;
          } else {
            $isUniversal = false;
            $decoded = json_decode($item['available_branches'], true);
            if (is_array($decoded)) {
              foreach ($decoded as $bId) {
                $itemBranchIds[] = 'branch-' . $bId;
                // find branch name
                foreach ($allBranches as $b) {
                  if ($b['id'] == $bId) {
                    $branchNames[] = $b['name'];
                  }
                }
              }
            } else {
              foreach ($allBranches as $b) {
                $itemBranchIds[] = 'branch-' . $b['id'];
                $branchNames[] = $b['name'];
              }
              $isUniversal = true;
            }
          }
          
          $classNames = 'card dish-card category-' . htmlspecialchars($item['category']) . ' ' . implode(' ', $itemBranchIds);
          $imgUrl = $item['image_url'];
        ?>
        <div class="<?php echo $classNames; ?>">
          <div class="media-placeholder" style="aspect-ratio: 16/10; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #100b06 0%, #080503 100%); position: relative; padding: 0;">
            <?php 
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
            <?php if ($showImage): ?>
              <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
            <?php else: ?>
              <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; fill: var(--color-primary, #ed174b); opacity: 0.8;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
              <div class="description" style="position: absolute; bottom: 10px; color: #fff; font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></div>
            <?php endif; ?>
          </div>
          <div class="dish-info">
            <div class="dish-header">
              <h3 class="dish-title"><?php echo htmlspecialchars($item['name']); ?></h3>
              <span class="dish-price">KES <?php echo number_format($item['price']); ?></span>
            </div>
            <p class="dish-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
            <div class="tag-container" style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start; margin-top: 10px;">
              <span class="badge" style="background: rgba(237, 23, 75, 0.08); color: var(--color-primary);"><?php echo htmlspecialchars(getCategoryLabel($item['category'])); ?></span>
              
              <!-- Location Availability Tags -->
              <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center; margin-top: 4px;">
                <span style="font-size: 0.75rem; color: var(--color-text-muted-dark); font-weight: 600; margin-right: 4px; display: inline-flex; align-items: center; gap: 2px;">
                  <svg viewBox="0 0 24 24" style="width: 12px; height: 12px; fill: currentColor;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                  Locations:
                </span>
                <?php if ($isUniversal): ?>
                  <span class="badge" style="background: rgba(40, 167, 69, 0.08); color: #28a745; font-size: 0.75rem; border: none; padding: 2px 6px;">All Locations</span>
                <?php else: ?>
                  <?php foreach ($branchNames as $name): ?>
                    <span class="badge" style="background: rgba(0, 0, 0, 0.04); color: var(--color-text-dark); font-size: 0.75rem; border: none; padding: 2px 6px;"><?php echo htmlspecialchars($name); ?></span>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Interactive Filtering Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const categoryButtons = document.querySelectorAll('.category-btn');
      const branchButtons = document.querySelectorAll('.branch-btn');
      const dishCards = document.querySelectorAll('.dish-card');

      function filterMenu() {
        const activeCategoryBtn = document.querySelector('.category-btn.active');
        const activeBranchBtn = document.querySelector('.branch-btn.active');
        
        const activeCategory = activeCategoryBtn ? activeCategoryBtn.getAttribute('data-category') : 'all';
        const activeBranch = activeBranchBtn ? activeBranchBtn.getAttribute('data-branch') : 'all';

        dishCards.forEach(card => {
          const matchCategory = (activeCategory === 'all' || card.classList.contains(activeCategory));
          const matchBranch = (activeBranch === 'all' || card.classList.contains(activeBranch));

          if (matchCategory && matchBranch) {
            card.style.display = '';
            // Micro-animation fade in
            card.style.opacity = '0';
            card.style.transform = 'scale(0.97)';
            setTimeout(() => {
              card.style.transition = 'opacity 300ms cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 300ms cubic-bezier(0.25, 0.46, 0.45, 0.94)';
              card.style.opacity = '1';
              card.style.transform = 'scale(1)';
            }, 50);
          } else {
            card.style.display = 'none';
          }
        });

        // Trigger GSAP/ScrollTrigger refresh if present
        if (typeof ScrollTrigger !== 'undefined') {
          ScrollTrigger.refresh();
        }
      }

      // Add category click events
      categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          categoryButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          filterMenu();
        });
      });

      // Add branch click events
      branchButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          branchButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          filterMenu();
        });
      });
    });
  </script>

<?php include 'footer.php'; ?>
