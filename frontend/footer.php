  <!-- Footer Section -->
  <footer>
    <div class="container grid grid-4">
      <div class="footer-column">
        <a href="/" class="logo logo-footer" style="margin-bottom: var(--space-xs);" aria-label="Asmara Restaurant home">
          <img src="logo/asmara%20logo.png" alt="Asmara Restaurant" class="logo-image">
        </a>
        <h4 style="margin-top: 12px; margin-bottom: 8px;">About Asmara</h4>
        <p>
          Asmara Restaurants are Eritrean/Continental restaurants established in 2009. We offer unique dining experiences infusing culture and culinary excellence in afro-contemporary setting. We are based in Pangani, Westlands along General Mathenge, Karen along Ngong’ Road &amp; Lavington along Othaya Road.
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
          <p><?php echo htmlspecialchars($b['name']); ?>: <?php echo htmlspecialchars(format_phone($b['phone'])); ?></p>
        <?php endforeach; ?>
        <p>sales@asmara.co.ke</p>
      </div>
      <div class="footer-column">
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="/">Home</a></li>
          <li><a href="/about">Our Story</a></li>
          <li><a href="/#branches">Branches</a></li>
          <li><a href="/events">Events</a></li>
          <li><a href="/menu">Menu</a></li>
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
    
    <div class="container footer-bottom" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-sm);">
      <p>&copy; 2026 Asmara Restaurant. All rights reserved. | Developed with excellence by <a href="https://bridgapp.africa/" target="_blank" rel="noopener" style="color: var(--color-primary); font-weight: 600; text-decoration: none;">BridgApp Africa</a></p>
      <div style="display: flex; gap: 16px; align-items: center;" class="footer-social-icons">
        <a href="https://www.instagram.com/asmararestaurantskenya?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" rel="noopener" aria-label="Instagram" style="color: var(--color-text-dark); display: inline-flex; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)'; this.style.color='#ed174b';" onmouseout="this.style.transform='scale(1)'; this.style.color='var(--color-text-dark)';">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
        </a>
        <a href="https://www.facebook.com/AsmaraRestaurantsKenya/" target="_blank" rel="noopener" aria-label="Facebook" style="color: var(--color-text-dark); display: inline-flex; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)'; this.style.color='#ed174b';" onmouseout="this.style.transform='scale(1)'; this.style.color='var(--color-text-dark)';">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.592 9 4.415V8z"/></svg>
        </a>
        <a href="https://ke.linkedin.com/company/asmara-restaurants-kenya" target="_blank" rel="noopener" aria-label="LinkedIn" style="color: var(--color-text-dark); display: inline-flex; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)'; this.style.color='#ed174b';" onmouseout="this.style.transform='scale(1)'; this.style.color='var(--color-text-dark)';">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/></svg>
        </a>
        <a href="https://www.tiktok.com/@asmararestaurantskenya" target="_blank" rel="noopener" aria-label="TikTok" style="color: var(--color-text-dark); display: inline-flex; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)'; this.style.color='#ed174b';" onmouseout="this.style.transform='scale(1)'; this.style.color='var(--color-text-dark)';">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.96-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.56-1.31 1.55-1.3 2.54.01 1.09.61 2.13 1.56 2.65.9.49 2.03.49 2.93.01.88-.47 1.46-1.42 1.49-2.42.03-4.66.01-9.32.01-13.98z"/></svg>
        </a>
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
