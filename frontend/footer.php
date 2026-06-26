  <!-- Footer Section -->
  <footer>
    <div class="container grid grid-4">
      <div class="footer-column">
        <a href="/" class="logo logo-footer" style="margin-bottom: var(--space-xs);" aria-label="Asmara Restaurant home">
          <img src="logo/asmara%20logo.png" alt="Asmara Restaurant" class="logo-image">
        </a>
        <p>
          Asmara Restaurants are Eritrean/Continental restaurants established in 2009.
        </p>
      </div>
      <div class="footer-column">
        <h4>Contact & Inquiries</h4>
        <p>P.O BOX 51416, 00100</p>
        <p>Nairobi, Kenya</p>
        <?php 
        if (!class_exists('BranchRepository')) {
          require_once __DIR__ . '/../backend/database/BranchRepository.php';
        }
        $footerBranchRepo = new BranchRepository();
        $footerBranches = $footerBranchRepo->getAll();
        foreach ($footerBranches as $b): 
        ?>
          <p><?php echo htmlspecialchars($b['name']); ?>: <?php echo htmlspecialchars($b['phone']); ?></p>
        <?php endforeach; ?>
        <p>sales@asmara.co.ke</p>
      </div>
      <div class="footer-column">
        <h4>Explore</h4>
        <ul class="footer-links">
          <li><a href="/menu">Our Menu</a></li>
          <li><a href="/booking">Reservation</a></li>
          <li><a href="/#branches">Suburbs Map</a></li>
          <li><a href="/about">Our Story</a></li>
          <li><a href="/#events">Events</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h4>Newsletter</h4>
        <p style="margin-bottom: var(--space-xs);">Subscribe for events and menu announcements.</p>
        <form style="display: flex; gap: 8px;" id="newsletterForm">
          <input type="email" name="email" placeholder="Your email address" aria-label="Subscription Email" required style="padding: 12px; font-size: 0.85rem; border-radius: var(--radius-sm); border: 1.5px solid rgba(237, 23, 75, 0.15); background: rgba(237, 23, 75, 0.03); color: var(--color-text-dark);">
          <button type="submit" class="btn btn-primary" style="padding: 12px 18px;"><svg viewBox="0 0 24 24" style="width:14px; height:14px; fill:#fff;"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>
        </form>
      </div>
    </div>
    
    <div class="container footer-bottom">
      <p>&copy; 2026 Asmara Restaurant. All rights reserved.</p>
      <div style="display: flex; gap: var(--space-sm);">
        <a href="#" aria-label="Facebook">Facebook</a>
        <a href="#" aria-label="Instagram">Instagram</a>
        <a href="#" aria-label="LinkedIn">LinkedIn</a>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <!-- Lenis Smooth Scroll CDN -->
  <script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js"></script> 
  
  <!-- GSAP & ScrollTrigger CDNs -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

  <!-- Fallback if Lenis/GSAP fail to load -->
  <script>
    window.addEventListener('load', function() {
      if (typeof Lenis === 'undefined' || typeof gsap === 'undefined') {
        console.warn('Lenis or GSAP failed to load from CDN. Basic scroll behavior active.');
        // Disable Lenis init in main.js by setting a flag
        window.LENIS_DISABLED = true;
      }
    });
  </script>

  <!-- Core Page Logic & Animations -->
  <script src="main.js"></script>
</body>
</html>
