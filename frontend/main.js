/**
 * Asmara Restaurant - Premium Visual Interactions & Animations
 * Core UI/UX logic for interactive Afro-Contemporary website
 * Integrated with Lenis Smooth Scroll and GSAP ScrollTrigger for Framer-like feel
 */

function initializeAll() {
  initLenisAndGSAP();
  initScrollProgress();
  initCustomCursor();
  initHeaderScroll();
  initMobileNav();
  initGalleryShowcase();
  initDraggableCarousel();
  initBranchHours();
  initTabFilters();
  initBookingForm();
  initNewsletterForm();
  initTypingAnimation();
  initAnchorScroll();
}

// Execute immediately if DOM is ready, or wait for DOMContentLoaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeAll);
} else {
  initializeAll();
}

/**
 * 0. Anchor scroll handler that accounts for fixed header height
 */
function initAnchorScroll() {
  const header = document.querySelector('header');
  const headerHeight = () => header ? header.getBoundingClientRect().height + 12 : 80;

  function scrollToHash(hash, smooth = true) {
    if (!hash) return;
    const id = hash.replace('#', '');
    const el = document.getElementById(id);
    if (!el) return;
    const top = window.scrollY + el.getBoundingClientRect().top - headerHeight();
    window.scrollTo({ top, behavior: smooth ? 'smooth' : 'auto' });
  }

  // On initial load, if there's a hash, scroll to it with offset
  if (window.location.hash) {
    // delay to allow layout, images and fonts to settle
    setTimeout(() => scrollToHash(window.location.hash, true), 350);
    // also run on load to catch late layout shifts
    window.addEventListener('load', () => setTimeout(() => scrollToHash(window.location.hash, true), 120));
  }

  // Intercept in-page anchor clicks to apply offset
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a || !a.href) return;

    try {
      const url = new URL(a.href);
      const isSamePage = url.origin === window.location.origin && 
                         (url.pathname.replace(/\/index\.php$/, '').replace(/\/$/, '') === 
                          window.location.pathname.replace(/\/index\.php$/, '').replace(/\/$/, ''));

      if (isSamePage && url.hash) {
        // Prevent default browser jump/reload
        e.preventDefault();

        // Close mobile drawer if open
        const drawer = document.getElementById('mobileDrawer');
        const backdrop = document.getElementById('mobileBackdrop');
        const btnToggle = document.getElementById('btnMobileToggle');
        if (drawer && drawer.classList.contains('active')) {
          drawer.classList.remove('active');
          drawer.setAttribute('aria-hidden', 'true');
          if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(() => backdrop.hidden = true, 300);
          }
          if (btnToggle) btnToggle.setAttribute('aria-expanded', 'false');
        }

        scrollToHash(url.hash, true);
        if (history.pushState) history.pushState(null, '', url.hash);
      }
    } catch (err) {
      // Fallback
    }
  }, { passive: false });
}

/**
 * 1. Initialize Lenis Smooth Scroll & GSAP ScrollTrigger
 */
let lenis;
function initLenisAndGSAP() {
  // Skip if CDN dependencies failed to load
  if (window.LENIS_DISABLED || typeof Lenis === 'undefined' || typeof gsap === 'undefined') {
    console.warn('Lenis/GSAP unavailable. Skipping smooth scroll initialization.');
    return;
  }

  // Initialize Lenis
  lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // standard inertia
    smoothWheel: true,
    smoothTouch: false,
    infinite: false,
  });

  // Synchronize ScrollTrigger with Lenis scroll updates
  lenis.on('scroll', ScrollTrigger.update);

  // Hook Lenis to GSAP ticker
  gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
  });
  gsap.ticker.lagSmoothing(0);

  // Register GSAP ScrollTrigger
  gsap.registerPlugin(ScrollTrigger);

  // Hero Parallax Background Effect (only if element exists)
  if (document.querySelector('.hero-parallax-bg')) {
    gsap.to('.hero-parallax-bg', {
      yPercent: 25,
      ease: 'none',
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true
      }
    });
  }

  // Reveal On Scroll GSAP triggers (standalone items)
  const revealElements = gsap.utils.toArray('.reveal-on-scroll');
  revealElements.forEach((elem) => {
    // Avoid double animating cards inside staggered grids
    if (elem.closest('.grid')) return;

    let fromVal = { opacity: 0 };
    let toVal = { opacity: 1, duration: 1.2, ease: 'power3.out', overwrite: 'auto' };

    if (elem.classList.contains('slide-up')) {
      fromVal.y = 50;
      toVal.y = 0;
    } else if (elem.classList.contains('slide-down')) {
      fromVal.y = -50;
      toVal.y = 0;
    } else if (elem.classList.contains('scale-up')) {
      fromVal.scale = 0.95;
      toVal.scale = 1;
    }

    gsap.fromTo(elem, fromVal, {
      ...toVal,
      immediateRender: false,
      scrollTrigger: {
        trigger: elem,
        start: 'top 85%',
        toggleActions: 'play none none none',
        once: true
      }
    });
  });

  // Card grid stagger animations (Dinevo & CoParadiso staggered card entrances)
  gsap.utils.toArray('.grid').forEach((grid) => {
    const cards = grid.querySelectorAll('.card, .dish-card, .branch-card, .footer-column');
    if (cards.length > 0) {
      gsap.fromTo(cards, 
        { y: 80, opacity: 0, scale: 0.96 },
        { 
          y: 0, 
          opacity: 1, 
          scale: 1,
          duration: 1.4, 
          stagger: 0.15, 
          ease: 'power4.out',
          immediateRender: false,
          scrollTrigger: {
            trigger: grid,
            start: 'top 85%',
            toggleActions: 'play none none none',
            once: true
          }
        }
      );
    }
  });
}

/**
 * 2. Scroll Progress Bar
 */
function initScrollProgress() {
  const progressBar = document.createElement('div');
  progressBar.className = 'scroll-progress';
  document.body.appendChild(progressBar);

  window.addEventListener('scroll', () => {
    const totalScroll = document.documentElement.scrollHeight - window.innerHeight;
    if (totalScroll > 0) {
      const percentage = (window.scrollY / totalScroll) * 100;
      progressBar.style.width = `${percentage}%`;
    }
  }, { passive: true });
}

/**
 * 3. Custom Cursor Follower
 */
function initCustomCursor() {
  const finePointer = window.matchMedia('(pointer: fine)').matches;
  if (!finePointer) return;

  const fork = document.createElement('div');
  fork.className = 'cursor-fork';
  fork.innerHTML = `
    <svg viewBox="0 0 1024 1024" aria-hidden="true" focusable="false">
      <path d="M288 608v192c0 17.673 14.327 32 32 32s32-14.327 32-32v0-192c0-17.673-14.327-32-32-32s-32 14.327-32 32v0zM864 800v-736c0-17.673-14.327-32-32-32s-32 14.327-32 32v0 192h-192c-17.673 0-32 14.327-32 32v0c1.626 81.497 12.063 159.737 30.398 234.881l-1.518-7.361c39.12 161.96 113.28 270.52 214.52 313.88 3.715 1.634 8.047 2.585 12.6 2.585 17.668 0 31.991-14.318 32-31.984v-0.001zM800 744.4c-128.68-98.28-153.88-337.68-158.8-424.4h158.8zM479.56 805.24c-2.381 15.526-15.643 27.279-31.65 27.279-17.673 0-32-14.327-32-32 0-2.05 0.193-4.056 0.561-5.999l-0.031 0.199 31.56-189.24c0-70.692-57.308-128-128-128s-128 57.308-128 128v0l31.52 189.24c0.337 1.744 0.53 3.749 0.53 5.799 0 17.673-14.327 32-32 32-16.007 0-29.269-11.753-31.627-27.101l-0.023-0.178-32-192c-0.255-1.533-0.4-3.299-0.4-5.099 0-0.049 0-0.099 0-0.148v0.008c0.138-94.677 68.681-173.304 158.847-189.113l1.153-0.167v-354.72c0-17.673 14.327-32 32-32s32 14.327 32 32v0 354.72c91.319 15.976 159.862 94.603 160 189.265v0.015c0 1.32 0 2.64 0 4z" />
    </svg>
  `;

  const knife = document.createElement('div');
  knife.className = 'cursor-knife';
  knife.innerHTML = `
    <svg viewBox="0 0 1024 1024" aria-hidden="true" focusable="false">
      <path d="M927.48 831.48c-20.142 20.091-47.94 32.513-78.64 32.513s-58.498-12.423-78.642-32.516l-696.838-697.078c-5.786-5.79-9.365-13.787-9.365-22.619 0-15.121 10.488-27.793 24.587-31.137l0.217-0.043c43.494-10.524 93.434-16.573 144.783-16.6h0.017c131.8 0 263.68 40.8 387.8 120.92 127.04 82 200.76 175.28 204 179.24 4.246 5.38 6.81 12.258 6.81 19.735 0 8.835-3.581 16.834-9.37 22.625v0l-81.56 81.48 186.2 186.24c20.069 20.144 32.477 47.933 32.477 78.62s-12.408 58.476-32.48 78.623l0.003-0.003zM756.4 382.24c-50.193-54.577-106.455-102.322-168.061-142.569l-3.379-2.071c-137.72-88.4-279.76-123.68-423.08-105.2l422.12 422.28zM882.24 719.44l-186.24-186.24-66.72 66.8 186.2 186.24c8.543 8.543 20.344 13.826 33.38 13.826 26.071 0 47.206-21.135 47.206-47.206 0-13.036-5.284-24.837-13.826-33.38v0z" />
    </svg>
  `;

  document.body.appendChild(fork);
  document.body.appendChild(knife);

  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let forkX = mouseX - 20;
  let forkY = mouseY - 10;
  let knifeX = mouseX + 20;
  let knifeY = mouseY + 10;

  const render = () => {
    forkX += (mouseX - 20 - forkX) * 0.18;
    forkY += (mouseY - 10 - forkY) * 0.18;
    knifeX += (mouseX + 20 - knifeX) * 0.16;
    knifeY += (mouseY + 10 - knifeY) * 0.16;

    fork.style.transform = `translate3d(${forkX}px, ${forkY}px, 0) translate(-50%, -50%) rotate(-10deg)`;
    knife.style.transform = `translate3d(${knifeX}px, ${knifeY}px, 0) translate(-50%, -50%) rotate(12deg)`;

    requestAnimationFrame(render);
  };

  document.addEventListener('pointermove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
  }, { passive: true });

  document.addEventListener('pointerleave', () => {
    fork.style.opacity = '0';
    knife.style.opacity = '0';
  });

  document.addEventListener('pointerenter', () => {
    fork.style.opacity = '1';
    knife.style.opacity = '1';
  });

  requestAnimationFrame(render);
}

/**
 * 4. Header shrink/change on scroll
 */
function initHeaderScroll() {
  const header = document.querySelector('header');
  if (!header) return;

  const checkScroll = () => {
    if (window.scrollY > 40) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', checkScroll, { passive: true });
  checkScroll(); // run once on load
}

/**
 * 5. Mobile Navigation Menu Toggle Drawer
 */
function initMobileNav() {
  const toggle = document.querySelector('.mobile-toggle');
  const drawer = document.querySelector('.mobile-nav');
  const backdrop = document.querySelector('.mobile-nav-backdrop');
  
  if (!toggle || !drawer) return;

  const lines = toggle.querySelectorAll('.mobile-toggle-lines span');

  const setDrawerState = (isOpen) => {
    drawer.classList.toggle('open', isOpen);
    if (backdrop) {
      backdrop.classList.toggle('visible', isOpen);
      backdrop.hidden = !isOpen;
    }
    document.body.style.overflow = isOpen ? 'hidden' : '';
    toggle.setAttribute('aria-expanded', isOpen);
    drawer.setAttribute('aria-hidden', !isOpen);

    // Animate the compact icon into a close mark.
    if (isOpen) {
      lines[0].style.transform = 'translateY(6px) rotate(45deg)';
      lines[1].style.opacity = '0';
      lines[2].style.transform = 'translateY(-6px) rotate(-45deg)';
      toggle.classList.add('is-open');
    } else {
      lines[0].style.transform = 'none';
      lines[1].style.opacity = '1';
      lines[2].style.transform = 'none';
      toggle.classList.remove('is-open');
    }
  };

  const toggleDrawer = () => {
    setDrawerState(!drawer.classList.contains('open'));
  };

  toggle.addEventListener('click', toggleDrawer);

  // Close drawer on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer.classList.contains('open')) {
      setDrawerState(false);
    }
  });

  if (backdrop) {
    backdrop.addEventListener('click', () => setDrawerState(false));
  }

  // Close drawer when clicking nav links
  drawer.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      setDrawerState(false);
    });
  });

  setDrawerState(false);
}

/**
 * 6. Gallery Showcase: drifting rails + focus lightbox
 */
function initGalleryShowcase() {
  const gallery = document.querySelector('[data-gallery-carousel]');
  const rails = document.querySelectorAll('[data-gallery-rail]');
  const cards = Array.from(document.querySelectorAll('[data-gallery-item]'));
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = document.getElementById('galleryLightboxImage');
  const lightboxTitle = document.getElementById('galleryLightboxTitle');
  const lightboxCaption = document.getElementById('galleryLightboxCaption');

  if (!gallery || !rails.length || !cards.length || !lightbox || !lightboxImage || !lightboxTitle || !lightboxCaption) {
    return;
  }

  const items = cards.map((card) => ({
    src: card.dataset.gallerySrc || '',
    title: card.dataset.galleryTitle || '',
    alt: card.dataset.galleryAlt || 'Asmara restaurant gallery image',
  })).filter((item) => item.src);

  if (!items.length) return;

  const openLightbox = (index) => {
    const item = items[(index + items.length) % items.length];
    lightboxImage.src = item.src;
    lightboxImage.alt = item.alt;
    lightboxTitle.textContent = item.title;
    lightboxCaption.textContent = 'Curated from the Asmara gallery collection.';
    lightbox.dataset.currentIndex = String((index + items.length) % items.length);
    lightbox.hidden = false;
    lightbox.setAttribute('aria-hidden', 'false');
    lightbox.classList.add('open');
    document.body.classList.add('gallery-focus-open');
    document.body.style.overflow = 'hidden';
  };

  const closeLightbox = () => {
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    lightbox.hidden = true;
    document.body.classList.remove('gallery-focus-open');
    document.body.style.overflow = '';
  };

  const updateLightbox = (nextIndex) => {
    const item = items[(nextIndex + items.length) % items.length];
    lightboxImage.src = item.src;
    lightboxImage.alt = item.alt;
    lightboxTitle.textContent = item.title;
    lightboxCaption.textContent = 'Curated from the Asmara gallery collection.';
    lightbox.dataset.currentIndex = String((nextIndex + items.length) % items.length);
  };

  rails.forEach((rail, index) => {
    if (!rail.dataset.loopCloned) {
      rail.innerHTML += rail.innerHTML;
      rail.dataset.loopCloned = 'true';
    }
    rail.style.setProperty('--gallery-speed', `${42 + index * 8}s`);
  });

  gallery.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-gallery-item]');
    if (!trigger || !gallery.contains(trigger)) return;
    const index = Number(trigger.dataset.galleryIndex || '0');
    openLightbox(index);
  });

  lightbox.querySelectorAll('[data-gallery-close]').forEach((el) => {
    el.addEventListener('click', closeLightbox);
  });

  const prevButton = lightbox.querySelector('[data-gallery-prev]');
  const nextButton = lightbox.querySelector('[data-gallery-next]');

  if (prevButton) {
    prevButton.addEventListener('click', () => {
      const currentIndex = Number(lightbox.dataset.currentIndex || '0');
      updateLightbox(currentIndex - 1);
    });
  }

  if (nextButton) {
    nextButton.addEventListener('click', () => {
      const currentIndex = Number(lightbox.dataset.currentIndex || '0');
      updateLightbox(currentIndex + 1);
    });
  }

  document.addEventListener('keydown', (e) => {
    if (lightbox.hidden) return;
    if (e.key === 'Escape') {
      closeLightbox();
    }
    if (e.key === 'ArrowLeft') {
      const currentIndex = Number(lightbox.dataset.currentIndex || '0');
      updateLightbox(currentIndex - 1);
    }
    if (e.key === 'ArrowRight') {
      const currentIndex = Number(lightbox.dataset.currentIndex || '0');
      updateLightbox(currentIndex + 1);
    }
  });

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      closeLightbox();
    }
  });
}

/**
 * 7. Featured Food Carousel: Horizontal Swipe/Drag scroll
 */
function initDraggableCarousel() {
  const viewport = document.querySelector('.carousel-viewport');
  const track = document.querySelector('.carousel-track');
  if (!viewport || !track) return;

  // Duplicate the slides once so the loop can run continuously without a hard jump.
  if (!track.dataset.loopCloned) {
    track.innerHTML += track.innerHTML;
    track.dataset.loopCloned = 'true';
  }

  track.classList.add('auto-slide-ltr');

  // Pause the motion while the user is interacting with the strip.
  const pause = () => track.style.animationPlayState = 'paused';
  const resume = () => track.style.animationPlayState = '';

  viewport.addEventListener('mouseenter', pause);
  viewport.addEventListener('mouseleave', resume);
  viewport.addEventListener('touchstart', pause, { passive: true });
  viewport.addEventListener('touchend', resume);
}

/**
 * 8. Branch Hours Collapsible Accordions
 */
function initBranchHours() {
  const toggles = document.querySelectorAll('.branch-hours-toggle');
  toggles.forEach(btn => {
    btn.addEventListener('click', () => {
      const container = btn.nextElementSibling;
      if (!container) return;

      const isOpen = container.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen);
      btn.innerHTML = isOpen ? 'Hide Contact Details ▲' : 'Show Contact Details ▼';
    });
  });
}

/**
 * 9. Tab Filtering with Fade Transition
 */
function initTabFilters() {
  const tabContainers = document.querySelectorAll('[data-tab-container]');

  tabContainers.forEach(container => {
    const buttons = container.querySelectorAll('[data-tab-target]');
    const items = container.querySelectorAll('[data-tab-item]');
    const grid = container.querySelector('#branchesGrid');

    // Initialize "All Locations" view on page load
    if (grid) {
      grid.classList.add('show-all-branches');
    }

    // Hide all side content on initial load (they should only show for specific locations)
    items.forEach(item => {
      if (item.classList.contains('branch-side-content')) {
        item.classList.add('hidden');
        item.style.display = 'none';
      }
    });

    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Toggle button states
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.getAttribute('data-tab-target');

        // Add class to grid to control layout
        if (grid) {
          if (filter === 'all') {
            grid.classList.add('show-all-branches');
          } else {
            grid.classList.remove('show-all-branches');
          }
        }

        items.forEach(item => {
          const categories = item.getAttribute('data-tab-item').split(' ');
          
          if (categories.includes(filter)) {
            // Unhide item container
            item.classList.remove('hidden');
            item.style.display = '';
            
            // Fade-in trigger
            item.style.opacity = '0';
            item.style.transform = 'scale(0.96)';
            setTimeout(() => {
              item.style.transition = 'opacity 300ms cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 300ms cubic-bezier(0.25, 0.46, 0.45, 0.94)';
              item.style.opacity = '1';
              item.style.transform = 'scale(1)';
            }, 10);
          } else {
            // Apply scale/fade out and hide
            item.style.opacity = '0';
            item.style.transform = 'scale(0.96)';
            item.classList.add('hidden');
            setTimeout(() => {
              item.style.display = 'none';
            }, 300);
          }
        });

        // Trigger GSAP ScrollTrigger refresh so positions recalculate after items toggle
        setTimeout(() => {
          ScrollTrigger.refresh();
        }, 320);
      });
    });
  });
}

/**
 * 10. Booking Form Submission & Success Overlay Simulation
 */
function initBookingForm() {
  const form = document.querySelector('#bookingForm');
  if (!form) return;

  // Enforce future dates
  const dateInput = form.querySelector('input[type="date"]');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = form.querySelector('#fullname').value.trim();
    const email = form.querySelector('#email').value.trim();
    const phone = form.querySelector('#phone').value.trim();
    const branch = form.querySelector('#branch').value;
    const guests = form.querySelector('#guests').value;
    const date = form.querySelector('#date').value;
    const time = form.querySelector('#time').value;
    const requests = form.querySelector('#requests') ? form.querySelector('#requests').value.trim() : '';

    if (!name || !email || !phone || !date || !time) {
      showToast('Please fill in all required fields.', 'error');
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Securing Table...';

    // Real API call to the PHP backend
    fetch('../backend/booking_submit.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        fullname: name,
        email: email,
        phone: phone,
        branch: branch,
        guests: guests,
        date: date,
        time: time,
        requests: requests,
        event_id: form.querySelector('#event') ? form.querySelector('#event').value : ''
      })
    })
    .then(response => response.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;

      if (data.success) {
        // Find containing card
        const parentCard = form.closest('.card') || form.closest('.booking-form-card');
        if (parentCard) {
          // Create full overlay success view
          const overlay = document.createElement('div');
          overlay.className = 'success-overlay';
          overlay.innerHTML = `
            <div style="font-size: 3rem; color: var(--color-secondary); margin-bottom: var(--space-sm);">✓</div>
            <h3 style="color: #ffffff; font-family: var(--font-heading); margin-bottom: var(--space-sm);">Reservation Confirmed</h3>
            <p style="color: var(--color-text-light); max-width: 500px; margin: 0 auto var(--space-md) auto;">
              Thank you, <strong>${data.name}</strong>. A table for <strong>${data.guests} people</strong> has been secured at <strong>Asmara ${data.branch}</strong> on <strong>${data.date}</strong> at <strong>${data.time}</strong>.
            </p>
            <p style="font-size: 0.85rem; color: var(--color-text-muted-light); margin-bottom: var(--space-md);">
              Confirmation Code: <strong style="font-family: var(--font-mono); color: var(--color-secondary);">${data.code}</strong>
            </p>
            <button class="btn btn-outline" onclick="location.reload()" style="margin-top: var(--space-sm);">Book Another Table</button>
          `;
          parentCard.style.position = 'relative';
          parentCard.appendChild(overlay);
        }
        form.reset();
        showToast('Reservation confirmed!', 'success');
      } else {
        showToast(data.message || 'Failed to confirm reservation.', 'error');
      }
    })
    .catch(error => {
      console.error('Error submitting booking:', error);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      showToast('Connection error. Please try again later.', 'error');
    });
  });
}

/**
 * Global Toast System
 */
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.style.position = 'fixed';
  toast.style.bottom = '32px';
  toast.style.right = '32px';
  toast.style.padding = '16px 32px';
  toast.style.borderRadius = '99px';
  toast.style.zIndex = '9999';
  toast.style.fontFamily = 'var(--font-mono)';
  toast.style.fontSize = '0.8rem';
  toast.style.textTransform = 'uppercase';
  toast.style.letterSpacing = '0.05em';
  toast.style.fontWeight = '600';
  toast.style.boxShadow = '0 20px 40px rgba(0,0,0,0.5)';
  toast.style.transition = 'transform 400ms cubic-bezier(0.25, 1, 0.5, 1), opacity 400ms cubic-bezier(0.25, 1, 0.5, 1)';
  toast.style.transform = 'translateY(100px)';
  toast.style.opacity = '0';

  if (type === 'error') {
    toast.style.backgroundColor = '#ed174b';
    toast.style.color = '#ffffff';
    toast.style.border = '1px solid rgba(255,255,255,0.1)';
  } else {
    toast.style.backgroundColor = '#fbf8f0';
    toast.style.color = '#1e150d';
  }

  toast.innerHTML = message;
  document.body.appendChild(toast);

  // Trigger entering transition
  setTimeout(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
  }, 30);

  // Exit transition
  setTimeout(() => {
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
  }, 4000);
}

/**
 * 11. Typing Animation Effect for Hero Section
 */
function initTypingAnimation() {
  const target = document.getElementById('typingText');
  if (!target) return;

  const words = ["ERITREAN.", "CONTINENTAL.", "AFRO-CONTEMPORARY.", "HOSPITALITY."];
  let wordIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  let delay = 150;

  function type() {
    const currentWord = words[wordIndex];
    
    if (isDeleting) {
      target.textContent = currentWord.substring(0, charIndex - 1);
      charIndex--;
      delay = 80;
    } else {
      target.textContent = currentWord.substring(0, charIndex + 1);
      charIndex++;
      delay = 140;
    }

    if (!isDeleting && charIndex === currentWord.length) {
      isDeleting = true;
      delay = 2000; // pause at full word
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      wordIndex = (wordIndex + 1) % words.length;
      delay = 600; // pause before typing next word
    }

    setTimeout(type, delay);
  }

  type();
}

/**
 * 12. Newsletter Form Submission Handling
 */
function initNewsletterForm() {
  const form = document.querySelector('#newsletterForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const emailInput = form.querySelector('input[type="email"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    if (!emailInput || !submitBtn) return;

    const email = emailInput.value.trim();
    if (!email) {
      showToast('Please enter a valid email address.', 'error');
      return;
    }

    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '...';

    fetch('../backend/newsletter_submit.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;

      if (data.success) {
        showToast(data.message, 'success');
        form.reset();
      } else {
        showToast(data.message || 'Subscription failed.', 'error');
      }
    })
    .catch(error => {
      console.error('Error subscribing to newsletter:', error);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
      showToast('Connection error. Please try again.', 'error');
    });
  });
}

