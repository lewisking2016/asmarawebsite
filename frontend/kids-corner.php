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
  "url" => "https://new.asmara.co.ke/kids-corner"
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
            <div style="font-size: 3rem; margin-bottom: var(--space-sm); color: var(--color-primary);">🎈</div>
            <h4 class="font-heading-h4" style="margin-bottom: var(--space-xs); font-size: 1.25rem;">Fun Equipment</h4>
            <p style="font-size: 0.95rem; color: var(--color-text-muted-dark); line-height: 1.6; margin: 0;">
              Equipped with modern slides, secure climbing structures, swings, and interactive play setups.
            </p>
          </div>

          <!-- Feature 2 -->
          <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 12px; padding: var(--space-lg); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.015); transition: transform 0.3s ease;">
            <div style="font-size: 3rem; margin-bottom: var(--space-sm); color: var(--color-primary);">🛡️</div>
            <h4 class="font-heading-h4" style="margin-bottom: var(--space-xs); font-size: 1.25rem;">Child Safety First</h4>
            <p style="font-size: 0.95rem; color: var(--color-text-muted-dark); line-height: 1.6; margin: 0;">
              Features cushioned soft-turf safety flooring to prevent bumps and scrapes, along with childproof fencing.
            </p>
          </div>

          <!-- Feature 3 -->
          <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 12px; padding: var(--space-lg); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.015); transition: transform 0.3s ease;">
            <div style="font-size: 3rem; margin-bottom: var(--space-sm); color: var(--color-primary);">🎨</div>
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
            <span style="font-size: 2rem;">✨</span>
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
            <span style="font-size: 2rem;">🎂</span>
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

      <!-- Kids Menu Highlights -->
      <div style="background: var(--color-surface-light, #ffffff); border: 1.5px solid var(--color-border-light, #eaeaea); border-radius: 16px; padding: var(--space-xl); box-shadow: 0 10px 30px rgba(0,0,0,0.015); margin-bottom: var(--space-xxl);">
        <h3 class="font-heading-h3" style="text-align: center; margin-bottom: var(--space-lg); font-size: 1.8rem; color: var(--color-text-dark);">Kid-Approved Menu Highlights</h3>
        
        <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-md);">
          
          <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--color-border-light); padding-bottom: var(--space-xs);">
            <div>
              <strong style="color: var(--color-text-dark); font-size: 1.1rem;">Mini Asmara Beef Burgers</strong>
              <p style="font-size: 0.85rem; color: var(--color-text-muted-dark); margin: 4px 0 0 0;">Two small beef burgers served with potato fries</p>
            </div>
            <span style="font-weight: 700; color: var(--color-primary); font-size: 1.1rem;">KES 650</span>
          </div>

          <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--color-border-light); padding-bottom: var(--space-xs);">
            <div>
              <strong style="color: var(--color-text-dark); font-size: 1.1rem;">Crispy Chicken Strips</strong>
              <p style="font-size: 0.85rem; color: var(--color-text-muted-dark); margin: 4px 0 0 0;">Hand-breaded chicken breast fillets with a creamy dip</p>
            </div>
            <span style="font-weight: 700; color: var(--color-primary); font-size: 1.1rem;">KES 700</span>
          </div>

          <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--color-border-light); padding-bottom: var(--space-xs);">
            <div>
              <strong style="color: var(--color-text-dark); font-size: 1.1rem;">Kids Injera Roll (Zigni)</strong>
              <p style="font-size: 0.85rem; color: var(--color-text-muted-dark); margin: 4px 0 0 0;">Mild beef stew wrapped in soft, mini injera pieces</p>
            </div>
            <span style="font-weight: 700; color: var(--color-primary); font-size: 1.1rem;">KES 550</span>
          </div>

          <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--color-border-light); padding-bottom: var(--space-xs);">
            <div>
              <strong style="color: var(--color-text-dark); font-size: 1.1rem;">Cheesy Margherita Pizza (Small)</strong>
              <p style="font-size: 0.85rem; color: var(--color-text-muted-dark); margin: 4px 0 0 0;">Fresh tomato sauce, local mozzarella, and a hint of herbs</p>
            </div>
            <span style="font-weight: 700; color: var(--color-primary); font-size: 1.1rem;">KES 600</span>
          </div>

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
