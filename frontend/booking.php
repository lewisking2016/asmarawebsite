<?php 
require_once __DIR__ . '/../backend/database/BranchRepository.php';
$branchRepo = new BranchRepository();
$dbBranches = $branchRepo->getAll();

$pageTitle = "Table Reservation";
$pageDescription = "Book a table at Asmara Restaurant in Nairobi. Reserve your spot at our Pangani, Westlands, Karen, or Lavington branches for authentic Eritrean cuisine.";
$pageSchema = [
  "@context" => "https://schema.org",
  "@type" => "WebPage",
  "name" => "Reservation - Asmara Restaurant",
  "description" => "Book a table at Asmara Restaurant in Nairobi.",
  "url" => "https://asmara.co.ke/booking"
];
include 'header.php'; 
?>

  <section class="hero panel-dark" style="min-height: 50vh; margin-top: 40px; padding-top: 180px; padding-bottom: var(--space-lg); text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.3;">
      <img src="images/optimized/Lavington-15.jpg" alt="Asmara Reservation Background" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div class="container" style="max-width: 900px; position: relative; z-index: 1;">
      <span class="hero-tagline" style="justify-content: center; display: inline-flex;">Reservation</span>
      <h1 class="font-display-h1" style="font-size: clamp(2.5rem, 6vw, 5rem);">TABLE BOOKING</h1>
      <p style="margin-top: var(--space-sm); color: var(--color-text-muted-light); font-size: 1.2rem;">
        All reservations made online require an emailed confirmation statement. Use the booking form below or contact a branch directly.
      </p>
    </div>
  </section>

  <section class="panel-light" style="padding-top: var(--space-lg); padding-bottom: var(--space-xxl);">
    <div class="container booking-shell">
      <aside class="booking-sidebar reveal-on-scroll slide-up">
        <div class="booking-side-card">
          <h3>By Phone</h3>
          <p style="color: var(--color-text-muted-dark); margin-bottom: var(--space-sm);">Call the branch that best fits your plans.</p>
          <ul class="booking-contact-list">
            <?php foreach ($dbBranches as $b): ?>
              <li><strong><?php echo htmlspecialchars($b['name']); ?></strong> <?php echo htmlspecialchars(format_phone($b['phone'])); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="booking-side-card">
          <h3>What to expect</h3>
          <ul class="booking-contact-list">
            <li>Online reservations need confirmation by email.</li>
            <li>Tables are held for 20 minutes past the booking time.</li>
            <li>Tell us about allergies or special occasions in the notes field.</li>
          </ul>
        </div>
      </aside>

      <div class="booking-form-card premium-form-card reveal-on-scroll slide-up">
        <!-- Step Indicators -->
        <div class="form-steps">
          <div class="form-step-item active" id="stepIndicator1">
            <div class="step-number">1</div>
            <span class="step-label">Branch & Guests</span>
          </div>
          <div class="form-step-item" id="stepIndicator2">
            <div class="step-number">2</div>
            <span class="step-label">Date & Time</span>
          </div>
          <div class="form-step-item" id="stepIndicator3">
            <div class="step-number">3</div>
            <span class="step-label">Contact Info</span>
          </div>
        </div>

        <?php
          // Load events for optional association with booking
          $eventsFile = __DIR__ . '/../backend/data/events.json';
          $events = [];
          if (file_exists($eventsFile)) {
              $ev = json_decode(file_get_contents($eventsFile), true);
              if (is_array($ev)) $events = $ev;
          }
        ?>

        <form id="bookingForm" novalidate>
          <!-- Hidden standard select elements to maintain absolute compatibility with main.js -->
          <select id="branch" required style="display: none;">
            <?php foreach ($dbBranches as $index => $b): ?>
              <option value="<?php echo htmlspecialchars($b['name']); ?>" <?php echo $index === 0 ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($b['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select id="guests" required style="display: none;">
            <option value="1">1 Guest</option>
            <option value="2" selected>2 Guests</option>
            <option value="3">3 Guests</option>
            <option value="4">4 Guests</option>
            <option value="5">5 Guests</option>
            <option value="6">6 Guests</option>
            <option value="7">7 Guests</option>
            <option value="8">8 Guests</option>
            <option value="9">9+ Guests</option>
          </select>

          <!-- STEP 1: BRANCH & GUESTS -->
          <div class="form-section active" id="section1">
            <div class="form-section-title">
              <span>01</span> Choose Your Branch
            </div>
            <p style="font-size: 0.9rem; color: #555555; margin-bottom: var(--space-sm);">Select which location you would like to visit:</p>
            <div class="branch-options-grid">
              <?php foreach ($dbBranches as $index => $b): ?>
                <div class="branch-option-card <?php echo $index === 0 ? 'selected' : ''; ?>" data-value="<?php echo htmlspecialchars($b['name']); ?>">
                  <div class="branch-option-content">
                    <div class="branch-option-title"><?php echo htmlspecialchars($b['name']); ?></div>
                    <div class="branch-option-desc">Premium Eritrea & Contemporary Dining</div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="form-section-title" style="margin-top: var(--space-md);">
              <span>02</span> Number of Guests
            </div>
            <p style="font-size: 0.9rem; color: #555555; margin-bottom: var(--space-sm);">How many people are dining with us?</p>
            <div class="guest-selector-pills">
              <div class="guest-pill" data-value="1">1</div>
              <div class="guest-pill selected" data-value="2">2</div>
              <div class="guest-pill" data-value="3">3</div>
              <div class="guest-pill" data-value="4">4</div>
              <div class="guest-pill" data-value="5">5</div>
              <div class="guest-pill" data-value="6">6</div>
              <div class="guest-pill" data-value="7">7</div>
              <div class="guest-pill" data-value="8">8</div>
              <div class="guest-pill" data-value="9">9+</div>
            </div>

            <div class="premium-form-actions">
              <div></div> <!-- Spacer -->
              <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next Step</button>
            </div>
          </div>

          <!-- STEP 2: DATE & TIME -->
          <div class="form-section" id="section2">
            <div class="form-section-title">
              <span>03</span> Select Date & Time
            </div>
            
            <div class="grid grid-2 booking-date-time" style="margin-top: var(--space-sm);">
              <div class="modern-input-group">
                <label class="modern-input-label" for="date">Reservation Date *</label>
                <input type="date" id="date" class="modern-input-field" required>
              </div>
              <div class="modern-input-group">
                <label class="modern-input-label" for="time">Preferred Time *</label>
                <input type="time" id="time" class="modern-input-field" required>
              </div>
            </div>

            <?php if (!empty($events)): ?>
            <div class="modern-input-group" style="margin-top: var(--space-sm);">
              <label class="modern-input-label" for="event">Reserve for an Event (Optional)</label>
              <div class="select-shell" style="border: 1.5px solid #eaeaea; border-radius: 12px; background: #ffffff;">
                <select id="event" style="padding: 16px 20px; color: #000000; background: #ffffff;">
                  <option value="">None (Regular Dining)</option>
                  <?php foreach ($events as $ev): ?>
                    <option value="<?php echo htmlspecialchars($ev['id']); ?>"><?php echo htmlspecialchars($ev['title'] . ' — ' . ($ev['venue'] ?? '')); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>

            <div class="premium-form-actions">
              <button type="button" class="btn btn-outline" onclick="goToStep(1)">Back</button>
              <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next Step</button>
            </div>
          </div>

          <!-- STEP 3: CONTACT INFO -->
          <div class="form-section" id="section3">
            <div class="form-section-title">
              <span>04</span> Your Details
            </div>

            <div class="modern-input-group">
              <label class="modern-input-label" for="fullname">Full Name *</label>
              <input type="text" id="fullname" class="modern-input-field" placeholder="E.g. Lewis King" autocomplete="name" required>
            </div>

            <div class="grid grid-2" style="gap: 16px;">
              <div class="modern-input-group">
                <label class="modern-input-label" for="email">Email Address *</label>
                <input type="email" id="email" class="modern-input-field" placeholder="E.g. lewis@domain.com" autocomplete="email" required>
              </div>
              <div class="modern-input-group">
                <label class="modern-input-label" for="phone">Phone Number *</label>
                <input type="tel" id="phone" class="modern-input-field" placeholder="E.g. +254 (0) 722 000 000" autocomplete="tel" required>
              </div>
            </div>

            <div class="modern-input-group">
              <label class="modern-input-label" for="requests">Special Requests (Optional)</label>
              <textarea id="requests" class="modern-input-field" rows="3" placeholder="Mention food allergies, occasion details, or seating preferences..." style="resize: vertical; min-height: 80px;"></textarea>
            </div>

            <div class="premium-form-actions">
              <button type="button" class="btn btn-outline" onclick="goToStep(2)">Back</button>
              <button type="submit" class="btn btn-primary" id="btnSubmitReservation">Confirm Reservation</button>
            </div>
          </div>
        </form>

        <script>
          // Step transition helper
          function goToStep(stepNum) {
            // Validation check when moving forward
            if (stepNum === 2) {
              // Ensure branch is selected
              const selectedBranch = document.querySelector('.branch-option-card.selected');
              if (!selectedBranch) {
                alert('Please select a branch.');
                return;
              }
            } else if (stepNum === 3) {
              // Validate date and time inputs
              const dateVal = document.getElementById('date').value;
              const timeVal = document.getElementById('time').value;
              if (!dateVal || !timeVal) {
                alert('Please choose a date and time.');
                return;
              }
            }

            // Hide all sections
            document.querySelectorAll('.form-section').forEach(sec => sec.classList.remove('active'));
            // Show target section
            document.getElementById('section' + stepNum).classList.add('active');

            // Update indicators
            document.querySelectorAll('.form-step-item').forEach((ind, idx) => {
              if (idx + 1 === stepNum) {
                ind.classList.add('active');
              } else {
                ind.classList.remove('active');
              }
            });
          }

          // Setup Custom Branch Card handlers
          document.querySelectorAll('.branch-option-card').forEach(card => {
            card.addEventListener('click', function() {
              document.querySelectorAll('.branch-option-card').forEach(c => c.classList.remove('selected'));
              this.classList.add('selected');
              
              // Sync to original hidden select element
              const val = this.getAttribute('data-value');
              const select = document.getElementById('branch');
              select.value = val;
              select.dispatchEvent(new Event('change'));
            });
          });

          // Setup Guest Pills handlers
          document.querySelectorAll('.guest-pill').forEach(pill => {
            pill.addEventListener('click', function() {
              document.querySelectorAll('.guest-pill').forEach(p => p.classList.remove('selected'));
              this.classList.add('selected');

              // Sync to original hidden select element
              const val = this.getAttribute('data-value');
              const select = document.getElementById('guests');
              select.value = val;
              select.dispatchEvent(new Event('change'));
            });
          });
        </script>
      </div>
    </div>
  </section>

<?php include 'footer.php'; ?>
