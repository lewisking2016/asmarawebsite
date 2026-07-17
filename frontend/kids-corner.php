<?php
require_once __DIR__ . '/../backend/database/BranchRepository.php';
$branchRepo = new BranchRepository();
$dbBranches = $branchRepo->getAll();

$pageTitle = "Kids Corner - Family Play Area in Westlands";
$pageDescription = "Bring the whole family to Asmara Restaurant in Westlands! Our Kids Corner is a safe, supervised, and fun play area for children of all ages.";
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "WebPage",
  "name" => "Kids Corner - Asmara Restaurant",
  "description" => "Vibrant and secure children's play area at Asmara Westlands.",
  "url" => "https://asmara.co.ke/kids-corner"
];
include 'header.php';
?>

  <!-- Hero Banner (Text-Only Style) -->
  <section class="hero panel-dark" style="min-height: 45vh; padding-top: 180px; padding-bottom: var(--space-xl); text-align: center; position: relative; overflow: hidden; background: linear-gradient(135deg, #1e150d 0%, #2a1e14 100%);">
    <div style="position: absolute; inset: 0; background: radial-gradient(circle at 50% 50%, rgba(237, 23, 75, 0.12) 0%, transparent 80%); pointer-events: none;"></div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex; color: var(--color-primary); font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
        Family Friendly Dining
      </span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 4.5rem); color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.6); margin-top: var(--space-xs);">KIDS CORNER</h1>
      <p style="margin-top: var(--space-sm); color: #ffffff; font-size: 1.25rem; text-shadow: 0 1px 5px rgba(0,0,0,0.6); line-height: 1.6; font-weight: 500; max-width: 750px; margin-left: auto; margin-right: auto;">
        A safe, vibrant, and fun playground located at our <strong style="color: var(--color-primary);">Westlands</strong> branch, designed to keep children entertained while you enjoy your meal.
      </p>
    </div>
  </section>

  <!-- Content Section -->
  <section class="panel-light" style="padding-top: var(--space-xxl); padding-bottom: var(--space-xxl);">
    <div class="container" style="max-width: 1000px;">
      
      <!-- Introduction Card -->
      <div style="background: var(--color-surface-light, #ffffff); padding: var(--space-xl); border-radius: 16px; border: 1.5px solid var(--color-border-light, #eaeaea); box-shadow: 0 15px 40px rgba(0,0,0,0.02); margin-bottom: var(--space-xxl); text-align: center;">
        <span class="badge badge-gold" style="font-size: 0.85rem; padding: 6px 14px; margin-bottom: var(--space-md); display: inline-block;">Westlands Exclusive</span>
        <h2 class="font-heading-h2" style="font-size: 2.2rem; color: var(--color-text-dark); margin-bottom: var(--space-sm);">Safe Play, Relaxed Dining</h2>
        <p style="color: var(--color-text-muted-dark); font-size: 1.1rem; line-height: 1.8; max-width: 800px; margin: 0 auto;">
          At Asmara, we believe dining out should be a pleasant experience for the entire family. Our dedicated Kids Corner provides a supervised, safe space where children can explore, play, and meet friends under the watchful eyes of our dedicated staff.
        </p>
      </div>

      <!-- Play Area Features -->
      <div style="margin-bottom: var(--space-xxl);">
        <h3 class="font-heading-h3" style="text-align: center; margin-bottom: var(--space-xl); font-size: 1.8rem; color: var(--color-text-dark);">What We Offer</h3>
        
        <div class="grid grid-3" style="gap: var(--space-lg);">
          
          <!-- Feature 1 -->
          <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 12px; padding: var(--space-lg); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.015); transition: transform 0.3s ease;">
            <div style="margin-bottom: var(--space-sm); color: var(--color-primary);">
              <svg width="48" height="48" viewBox="0 0 256 256" fill="currentColor"><path d="M220.27,158l-46.65-27.08A42,42,0,0,0,174,128a50,50,0,0,0-50-50h-1.64A50,50,0,0,0,74,128a42,42,0,0,0,.38,2.92L27.73,158a14,14,0,0,0,0,24.27l71.42,41.24a14,14,0,0,0,14,0L128,214.8l14.85,8.71a14,14,0,0,0,14,0l71.42-41.24a14,14,0,0,0,0-24.27ZM124,90.37a34,34,0,0,1,33.63,29.24L128,136.15,98.37,119.61A34,34,0,0,1,124,90.37ZM109.15,212.3a2,2,0,0,1-2,0L35.73,171.06a2,2,0,0,1,0-3.47l43.48-25.22L120,166.22v41.37Zm12.85-46.08L84.38,145.07l.15.09L128,171.75l43.47-26.59.15-.09L134,145.22Zm26,46.08V171l40.79-24.63,43.48,25.22a2,2,0,0,1,0,3.47L148.85,212.3A2,2,0,0,1,148,212.3Z"/></svg>
            </div>
            <h4 class="font-heading-h4" style="margin-bottom: var(--space-xs); font-size: 1.25rem;">Fun Equipment</h4>
            <p style="font-size: 0.95rem; color: var(--color-text-muted-dark); line-height: 1.6; margin: 0;">
              Equipped with modern slides, secure climbing structures, swings, and interactive play setups.
            </p>
          </div>

          <!-- Feature 2 -->
          <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 12px; padding: var(--space-lg); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.015); transition: transform 0.3s ease;">
            <div style="margin-bottom: var(--space-sm); color: var(--color-primary);">
              <svg width="48" height="48" viewBox="0 0 256 256" fill="currentColor"><path d="M208,40H48A16,16,0,0,0,32,56v58.77c0,89.62,75.82,119.34,91,124.39a15.53,15.53,0,0,0,10,0c15.2-5.05,91-34.77,91-124.39V56A16,16,0,0,0,208,40Zm0,74.79c0,78.42-66.35,104.62-80,109.18-13.53-4.52-80-30.69-80-109.18V56H208Zm-30.47,3.74a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L116,169.06l50.34-50.34A8,8,0,0,1,177.53,118.53Z"/></svg>
            </div>
            <h4 class="font-heading-h4" style="margin-bottom: var(--space-xs); font-size: 1.25rem;">Child Safety First</h4>
            <p style="font-size: 0.95rem; color: var(--color-text-muted-dark); line-height: 1.6; margin: 0;">
              Features cushioned soft-turf safety flooring to prevent bumps and scrapes, along with childproof fencing.
            </p>
          </div>

          <!-- Feature 3 -->
          <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 12px; padding: var(--space-lg); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.015); transition: transform 0.3s ease;">
            <div style="margin-bottom: var(--space-sm); color: var(--color-primary);">
              <svg width="48" height="48" viewBox="0 0 256 256" fill="currentColor"><path d="M200.77,53.89A103.27,103.27,0,0,0,128,24h-1.07A104,104,0,0,0,24,128c0,43.41,16.22,71.44,32,88.66C72.91,234.58,94.22,240,128,240a8,8,0,0,0,8-8,40,40,0,0,1,40-40h.32c13.93-.1,36-2,50.81-16.82C240.68,161.54,240.12,101.71,200.77,53.89ZM216,164.07c-10.54,10.54-28.06,12.31-39.63,12.43A56,56,0,0,0,120,232c-38.63,0-58-8.06-72.72-24.4C33.85,193,24,169.67,24,128A88,88,0,0,1,126.93,40h.94a87.24,87.24,0,0,1,61.56,25.28C224.93,104.51,225.32,154.79,216,164.07ZM140,76a12,12,0,1,1-12-12A12,12,0,0,1,140,76Zm-44,24a12,12,0,1,1-12-12A12,12,0,0,1,96,100Zm0,48a12,12,0,1,1-12-12A12,12,0,0,1,96,148Zm88-44a12,12,0,1,1-12-12A12,12,0,0,1,184,104Z"/></svg>
            </div>
            <h4 class="font-heading-h4" style="margin-bottom: var(--space-xs); font-size: 1.25rem;">Creative Arts Corner</h4>
            <p style="font-size: 0.95rem; color: var(--color-text-muted-dark); line-height: 1.6; margin: 0;">
              An indoor creative space with coloring books, building blocks, and puzzles for quiet, imaginative play.
            </p>
          </div>

        </div>
      </div>

      <!-- Weekend Activities & Birthday Parties -->
      <div class="grid grid-2" style="gap: var(--space-lg); margin-bottom: var(--space-xxl);">
        
        <!-- Weekend Events Card -->
        <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 16px; padding: var(--space-xl); box-shadow: 0 10px 30px rgba(0,0,0,0.015);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: var(--space-sm);">
            <span style="color: var(--color-primary); display: inline-flex;">
              <svg width="32" height="32" viewBox="0 0 256 256" fill="currentColor"><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32Zm0,176H48V48H208ZM168,96a8,8,0,0,1-4.58,7.23L148.58,110l6.84,14.77a8,8,0,0,1-3.58,10.73,8.19,8.19,0,0,1-3.57.84,8,8,0,0,1-7.16-4.42L134,118.15l-7.11,13.77a8,8,0,0,1-14.31-7.15L119.42,110l-14.84-6.77a8,8,0,0,1,6.65-14.56L118,92.52V76a8,8,0,0,1,16,0V92.52l6.77-3.85A8,8,0,0,1,168,96Zm-28,60a12,12,0,1,1-12,12A12,12,0,0,1,140,156Zm-56,0a12,12,0,1,1-12,12A12,12,0,0,1,84,156Zm112,0a12,12,0,1,1-12,12A12,12,0,0,1,196,156Z"/></svg>
            </span>
            <h3 class="font-heading-h3" style="margin: 0; font-size: 1.5rem; color: var(--color-text-dark);">Weekend Activities</h3>
          </div>
          <p style="color: var(--color-text-muted-dark); line-height: 1.6; font-size: 0.95rem; margin-bottom: var(--space-md);">
            Every Saturday and Sunday afternoon, we run special hosted activities to bring extra smiles to your little ones:
          </p>
          <ul style="padding-left: 20px; color: var(--color-text-muted-dark); font-size: 0.95rem; line-height: 1.8; margin: 0;">
            <li>Creative face painting sessions</li>
            <li>Balloon art modelling</li>
            <li>Supervised group games and storytelling</li>
            <li>Weekend cartoon and movie screenings</li>
          </ul>
        </div>

        <!-- Birthday Parties Card -->
        <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 16px; padding: var(--space-xl); box-shadow: 0 10px 30px rgba(0,0,0,0.015);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: var(--space-sm);">
            <span style="color: var(--color-primary); display: inline-flex;">
              <svg width="32" height="32" viewBox="0 0 256 256" fill="currentColor"><path d="M216,72H180V64a28,28,0,0,0-28-28H104A28,28,0,0,0,76,64v8H40A16,16,0,0,0,24,88V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72ZM92,64a12,12,0,0,1,12-12h48a12,12,0,0,1,12,12v8H92ZM216,200H40V88H216V200Zm-40-80a8,8,0,0,1-8,8H140v28a8,8,0,0,1-16,0V128H96a8,8,0,0,1,0-16h32V84a8,8,0,0,1,16,0v28h28A8,8,0,0,1,176,120Z"/></svg>
            </span>
            <h3 class="font-heading-h3" style="margin: 0; font-size: 1.5rem; color: var(--color-text-dark);">Birthday Parties</h3>
          </div>
          <p style="color: var(--color-text-muted-dark); line-height: 1.6; font-size: 0.95rem; margin-bottom: var(--space-md);">
            Host your child's special day at Asmara Westlands! We offer tailored birthday packages to make planning hassle-free:
          </p>
          <ul style="padding-left: 20px; color: var(--color-text-muted-dark); font-size: 0.95rem; line-height: 1.8; margin: 0;">
            <li>Custom kids menus & buffet arrangements</li>
            <li>Play area reservation and balloon decoration</li>
            <li>Dedicated party coordinator & safety supervisors</li>
            <li>Optional entertainment booking (magicians, clowns)</li>
          </ul>
        </div>

      </div>

      <!-- Action Footer -->
      <div class="section-green" style="padding: var(--space-xl); border-radius: 16px; text-align: center; background: var(--color-primary); color: #ffffff;">
        <h3 class="font-heading-h3" style="margin-bottom: var(--space-sm); color: #ffffff; font-size: 1.6rem; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Planning a Family Visit?</h3>
        <p style="margin-bottom: var(--space-lg); color: #ffffff; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6; opacity: 0.9;">
          Book a table at our Westlands branch to secure a spot close to the Kids Corner, or get in touch with us to request birthday party packages.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="/booking" class="btn btn-outline" style="border-color: white; color: white; background: transparent; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">Book a Table</a>
          <a href="tel:+254721948020" class="btn btn-outline" style="border-color: white; color: white; background: transparent; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">Call Westlands: 0721 948 020</a>
        </div>
      </div>

    </div>
  </section>

<?php include 'footer.php'; ?>
