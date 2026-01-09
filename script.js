/* ============================================
   ELEKTROMAX - Professional JavaScript
   Figma-quality interactions
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initHeader();
  initMobileNav();
  initScrollAnimations();
  initCableTimeline();
  initForms();
  initParticles();
});

/* ============================================
   HEADER
   ============================================ */

function initHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 50) {
      header.classList.add('header-scrolled');
    } else {
      header.classList.remove('header-scrolled');
    }
    
    lastScroll = currentScroll;
  }, { passive: true });
}

/* ============================================
   MOBILE NAVIGATION
   ============================================ */

function initMobileNav() {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.mobile-nav');
  const close = document.querySelector('.mobile-nav-close');
  const links = document.querySelectorAll('.mobile-nav-link');

  if (!toggle || !nav) return;

  const openNav = () => {
    nav.classList.add('active');
    document.body.style.overflow = 'hidden';
    toggle.setAttribute('aria-expanded', 'true');
  };

  const closeNav = () => {
    nav.classList.remove('active');
    document.body.style.overflow = '';
    toggle.setAttribute('aria-expanded', 'false');
  };

  toggle.addEventListener('click', openNav);
  close?.addEventListener('click', closeNav);
  links.forEach(link => link.addEventListener('click', closeNav));

  // Close on escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('active')) {
      closeNav();
    }
  });
}

/* ============================================
   SCROLL ANIMATIONS
   ============================================ */

function initScrollAnimations() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animated');
          
          // Stagger children animation
          if (entry.target.classList.contains('stagger-children')) {
            const children = entry.target.children;
            Array.from(children).forEach((child, i) => {
              child.style.transitionDelay = `${i * 100}ms`;
            });
          }
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    }
  );

  document.querySelectorAll('.animate-on-scroll').forEach((el) => {
    observer.observe(el);
  });
}

/* ============================================
   CABLE TIMELINE - Enhanced Sparking
   ============================================ */

function initCableTimeline() {
  const timeline = document.querySelector('.cable-timeline');
  if (!timeline) return;

  const fill = timeline.querySelector('.cable-fill');
  const markers = timeline.querySelectorAll('.cable-marker');
  const progressText = timeline.querySelector('.cable-progress');
  const spark = timeline.querySelector('.cable-spark');
  const connectorSpark = timeline.querySelector('.connector-spark');

  let lastProgress = 0;
  let lastScrollY = 0;
  let ticking = false;
  let sparkCooldown = false;

  // Create multiple spark elements for intense effect
  const sparks = [];
  for (let i = 0; i < 3; i++) {
    const sparkEl = document.createElement('div');
    sparkEl.className = 'cable-spark';
    sparkEl.style.cssText = `
      position: absolute;
      width: 40px;
      height: 40px;
      left: 50%;
      transform: translateX(-50%);
      pointer-events: none;
      opacity: 0;
      z-index: 10;
    `;
    timeline.querySelector('.cable-container').appendChild(sparkEl);
    sparks.push(sparkEl);
  }

  function updateTimeline() {
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollTop = window.pageYOffset;
    const progress = scrollHeight > 0 ? Math.min(Math.max(scrollTop / scrollHeight, 0), 1) : 0;
    const progressPercent = Math.round(progress * 100);

    // Update fill
    if (fill) {
      fill.style.height = `${progressPercent}%`;
    }

    // Update progress text
    if (progressText) {
      progressText.textContent = `${progressPercent}%`;
    }

    // Intense sparking on scroll
    const scrollDelta = Math.abs(scrollTop - lastScrollY);
    if (scrollDelta > 5 && !sparkCooldown) {
      triggerSpark(progressPercent);
      sparkCooldown = true;
      setTimeout(() => { sparkCooldown = false; }, 100);
    }

    lastScrollY = scrollTop;
    lastProgress = progress;

    // Update markers
    updateMarkers(markers);
    
    ticking = false;
  }

  function triggerSpark(progress) {
    // Get a random spark element
    const sparkEl = sparks[Math.floor(Math.random() * sparks.length)];
    if (!sparkEl) return;

    // Position at fill level with slight randomness
    const randomOffset = (Math.random() - 0.5) * 10;
    sparkEl.style.top = `${Math.min(progress, 95) + randomOffset}%`;
    
    // Trigger animation
    sparkEl.classList.remove('active');
    sparkEl.offsetWidth; // Force reflow
    sparkEl.classList.add('active');

    // Create spark burst effect
    createSparkBurst(sparkEl);

    setTimeout(() => {
      sparkEl.classList.remove('active');
    }, 600);
  }

  function createSparkBurst(parentEl) {
    const rect = parentEl.getBoundingClientRect();
    const container = timeline.querySelector('.cable-container');
    
    // Create mini particles
    for (let i = 0; i < 4; i++) {
      const particle = document.createElement('div');
      const angle = (i / 4) * Math.PI * 2;
      const distance = 20 + Math.random() * 15;
      
      particle.style.cssText = `
        position: absolute;
        width: 4px;
        height: 4px;
        background: #fff;
        border-radius: 50%;
        top: ${parseFloat(parentEl.style.top)}%;
        left: 50%;
        transform: translateX(-50%);
        pointer-events: none;
        z-index: 15;
        box-shadow: 0 0 6px #F59E0B, 0 0 12px #F59E0B;
      `;
      
      container.appendChild(particle);
      
      // Animate particle
      particle.animate([
        { 
          opacity: 1,
          transform: `translate(-50%, -50%) translate(0, 0) scale(1)`
        },
        { 
          opacity: 0,
          transform: `translate(-50%, -50%) translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0)`
        }
      ], {
        duration: 400 + Math.random() * 200,
        easing: 'cubic-bezier(0, 0, 0.2, 1)'
      }).onfinish = () => particle.remove();
    }
  }

  function updateMarkers(markers) {
    const viewportCenter = window.innerHeight * 0.4;

    markers.forEach((marker) => {
      const targetId = marker.dataset.target;
      const target = document.querySelector(targetId);
      if (!target) return;

      const rect = target.getBoundingClientRect();
      
      marker.classList.remove('active', 'current');

      if (rect.bottom < viewportCenter) {
        marker.classList.add('active');
      } else if (rect.top < viewportCenter && rect.bottom > viewportCenter) {
        marker.classList.add('current');
      }
    });
  }

  // Scroll listener with RAF throttling
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateTimeline);
      ticking = true;
    }
  }, { passive: true });

  // Marker click handlers
  markers.forEach(marker => {
    marker.addEventListener('click', () => {
      const target = document.querySelector(marker.dataset.target);
      if (!target) return;

      const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
      const targetPos = target.offsetTop - headerHeight - 20;

      window.scrollTo({
        top: targetPos,
        behavior: 'smooth'
      });
    });
  });

  // Periodic spark animation when idle
  let idleTimer;
  const triggerIdleSpark = () => {
    if (lastProgress > 0.05 && lastProgress < 0.95) {
      const currentProgress = Math.round(lastProgress * 100);
      triggerSpark(currentProgress);
    }
    idleTimer = setTimeout(triggerIdleSpark, 2000 + Math.random() * 2000);
  };

  window.addEventListener('scroll', () => {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(triggerIdleSpark, 3000);
  }, { passive: true });

  // Initial update
  updateTimeline();
  idleTimer = setTimeout(triggerIdleSpark, 3000);
}

/* ============================================
   FORMS
   ============================================ */

function initForms() {
  const forms = document.querySelectorAll('form');

  forms.forEach(form => {
    form.addEventListener('submit', handleSubmit);
    
    // Add input animations
    const inputs = form.querySelectorAll('.form-input, .form-textarea');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        input.parentElement?.classList.add('focused');
      });
      input.addEventListener('blur', () => {
        input.parentElement?.classList.remove('focused');
        if (input.value) {
          input.parentElement?.classList.add('filled');
        } else {
          input.parentElement?.classList.remove('filled');
        }
      });
    });

    // Phone formatting
    const phoneInputs = form.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
      input.addEventListener('input', formatPhone);
    });
  });
}

function handleSubmit(e) {
  e.preventDefault();
  
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
  if (!btn) return;

  // Validate
  if (!validateForm(form)) return;

  // Show loading
  const originalContent = btn.innerHTML;
  btn.classList.add('btn-loading');
  btn.innerHTML = `<span class="btn-text" style="opacity:0">Wysyłanie</span>`;
  btn.disabled = true;

  // Simulate submission
  setTimeout(() => {
    btn.classList.remove('btn-loading');
    btn.innerHTML = originalContent;
    btn.disabled = false;
    
    showMessage(form, 'success', 'Dziękujemy! Skontaktujemy się wkrótce.');
    form.reset();
  }, 1500);
}

function validateForm(form) {
  let valid = true;
  const inputs = form.querySelectorAll('.form-input, .form-textarea');

  inputs.forEach(input => {
    clearError(input);
    
    if (input.required && !input.value.trim()) {
      showError(input, 'To pole jest wymagane');
      valid = false;
    } else if (input.type === 'email' && input.value && !isValidEmail(input.value)) {
      showError(input, 'Podaj prawidłowy adres e-mail');
      valid = false;
    } else if (input.type === 'tel' && input.value && !isValidPhone(input.value)) {
      showError(input, 'Podaj prawidłowy numer telefonu');
      valid = false;
    }
  });

  const checkbox = form.querySelector('input[name="privacy"]');
  if (checkbox && !checkbox.checked) {
    showMessage(form, 'error', 'Musisz zaakceptować politykę prywatności');
    valid = false;
  }

  return valid;
}

function showError(input, message) {
  input.classList.add('form-input-error');
  
  const error = document.createElement('span');
  error.className = 'form-error-message';
  error.innerHTML = `
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"/>
      <line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    ${message}
  `;
  input.parentElement.appendChild(error);
}

function clearError(input) {
  input.classList.remove('form-input-error');
  const error = input.parentElement.querySelector('.form-error-message');
  if (error) error.remove();
}

function showMessage(form, type, message) {
  const existing = form.querySelector('.form-message');
  if (existing) existing.remove();

  const msg = document.createElement('div');
  msg.className = `form-message form-message-${type}`;
  msg.style.cssText = `
    grid-column: 1 / -1;
    padding: var(--space-4);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    text-align: center;
    margin-bottom: var(--space-4);
    background: ${type === 'success' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)'};
    color: ${type === 'success' ? 'var(--success-500)' : 'var(--error-500)'};
    border: 1px solid ${type === 'success' ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)'};
  `;
  msg.textContent = message;

  form.insertBefore(msg, form.firstChild);

  if (type === 'success') {
    setTimeout(() => msg.remove(), 5000);
  }
}

function formatPhone(e) {
  let value = e.target.value.replace(/\D/g, '');
  if (value.length > 9) value = value.slice(0, 9);
  
  if (value.length > 6) {
    value = `${value.slice(0, 3)} ${value.slice(3, 6)} ${value.slice(6)}`;
  } else if (value.length > 3) {
    value = `${value.slice(0, 3)} ${value.slice(3)}`;
  }
  
  e.target.value = value;
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
  return phone.replace(/\D/g, '').length >= 9;
}

/* ============================================
   PARTICLES
   ============================================ */

function initParticles() {
  const container = document.querySelector('.particles-container');
  if (!container) return;

  // Respect reduced motion
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  for (let i = 0; i < 12; i++) {
    createParticle(container, i);
  }
}

function createParticle(container, index) {
  const particle = document.createElement('div');
  particle.className = 'particle';
  
  const size = 1 + Math.random() * 2;
  const posX = Math.random() * 100;
  const delay = Math.random() * 10;
  const duration = 8 + Math.random() * 6;

  particle.style.cssText = `
    width: ${size}px;
    height: ${size}px;
    left: ${posX}%;
    animation-delay: ${delay}s;
    animation-duration: ${duration}s;
  `;

  container.appendChild(particle);
}

/* ============================================
   SMOOTH SCROLL
   ============================================ */

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href === '#') return;
    
    const target = document.querySelector(href);
    if (!target) return;

    e.preventDefault();
    
    const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
    const targetPos = target.offsetTop - headerHeight - 20;

    window.scrollTo({
      top: targetPos,
      behavior: 'smooth'
    });
  });
});

/* ============================================
   ACTIVE PAGE HIGHLIGHT
   ============================================ */

(() => {
  const currentPath = window.location.pathname;
  const links = document.querySelectorAll('.nav-link, .mobile-nav-link');
  
  links.forEach(link => {
    const href = link.getAttribute('href');
    if (
      currentPath.endsWith(href) || 
      (currentPath === '/' && href === 'index.html') ||
      (currentPath.endsWith('/') && href === 'index.html')
    ) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
})();
