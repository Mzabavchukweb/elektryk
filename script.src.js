/* ============================================
   RS ELECTRICS - Site interactions
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initHeader();
  initMobileNav();
  initScrollAnimations();
  initCableTimeline();
  initForms();
  initParticles();
  initCityPrefill();
  initSmoothScroll();
  initActivePageHighlight();
});

/* ============================================
   HEADER
   ============================================ */

function initHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  window.addEventListener('scroll', () => {
    header.classList.toggle('header-scrolled', window.pageYOffset > 50);
  }, { passive: true });
}

/* ============================================
   MOBILE NAVIGATION
   ============================================ */

function initMobileNav() {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.mobile-nav');
  if (!toggle || !nav) return;

  const close = document.querySelector('.mobile-nav-close');

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
  
  // Event delegation for nav links
  nav.addEventListener('click', (e) => {
    if (e.target.classList.contains('mobile-nav-link')) closeNav();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('active')) closeNav();
  });
}

/* ============================================
   SCROLL ANIMATIONS
   ============================================ */

function initScrollAnimations() {
  const elements = document.querySelectorAll('.animate-on-scroll');
  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
        if (entry.target.classList.contains('stagger-children')) {
          Array.from(entry.target.children).forEach((child, i) => {
            child.style.transitionDelay = `${i * 100}ms`;
          });
        }
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  elements.forEach((el) => observer.observe(el));
}

/* ============================================
   CABLE TIMELINE
   ============================================ */

function initCableTimeline() {
  const timeline = document.querySelector('.cable-timeline');
  if (!timeline) return;

  const container = timeline.querySelector('.cable-container');
  const fill = timeline.querySelector('.cable-fill');
  const markers = timeline.querySelectorAll('.cable-marker');
  const progressText = timeline.querySelector('.cable-progress');

  let lastProgress = 0;
  let lastScrollY = 0;
  let ticking = false;
  let sparkCooldown = false;
  let idleTimer;

  // Create spark elements
  const sparks = Array.from({ length: 3 }, () => {
    const el = document.createElement('div');
    el.className = 'cable-spark';
    el.style.cssText = 'position:absolute;width:40px;height:40px;left:50%;transform:translateX(-50%);pointer-events:none;opacity:0;z-index:10';
    container?.appendChild(el);
    return el;
  });

  function updateTimeline() {
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollTop = window.pageYOffset;
    const progress = scrollHeight > 0 ? Math.min(Math.max(scrollTop / scrollHeight, 0), 1) : 0;
    const progressPercent = Math.round(progress * 100);

    if (fill) fill.style.height = `${progressPercent}%`;
    if (progressText) progressText.textContent = `${progressPercent}%`;

    const scrollDelta = Math.abs(scrollTop - lastScrollY);
    if (scrollDelta > 5 && !sparkCooldown) {
      triggerSpark(progressPercent);
      sparkCooldown = true;
      setTimeout(() => { sparkCooldown = false; }, 100);
    }

    lastScrollY = scrollTop;
    lastProgress = progress;
    updateMarkers();
    ticking = false;
  }

  function triggerSpark(progress) {
    const sparkEl = sparks[Math.floor(Math.random() * sparks.length)];
    if (!sparkEl) return;

    sparkEl.style.top = `${Math.min(progress, 95) + (Math.random() - 0.5) * 10}%`;
    sparkEl.classList.remove('active');
    sparkEl.offsetWidth; // Force reflow
    sparkEl.classList.add('active');
    createSparkBurst(sparkEl);
    setTimeout(() => sparkEl.classList.remove('active'), 600);
  }

  function createSparkBurst(parentEl) {
    if (!container) return;
    for (let i = 0; i < 4; i++) {
      const particle = document.createElement('div');
      const angle = (i / 4) * Math.PI * 2;
      const distance = 20 + Math.random() * 15;

      particle.style.cssText = `position:absolute;width:4px;height:4px;background:#fff;border-radius:50%;top:${parseFloat(parentEl.style.top)}%;left:50%;transform:translateX(-50%);pointer-events:none;z-index:15;box-shadow:0 0 6px #F59E0B,0 0 12px #F59E0B`;
      container.appendChild(particle);

      particle.animate([
        { opacity: 1, transform: 'translate(-50%,-50%) translate(0,0) scale(1)' },
        { opacity: 0, transform: `translate(-50%,-50%) translate(${Math.cos(angle) * distance}px,${Math.sin(angle) * distance}px) scale(0)` }
      ], { duration: 400 + Math.random() * 200, easing: 'cubic-bezier(0,0,0.2,1)' }).onfinish = () => particle.remove();
    }
  }

  function updateMarkers() {
    const viewportCenter = window.innerHeight * 0.4;
    markers.forEach((marker) => {
      const target = document.querySelector(marker.dataset.target);
      if (!target) return;
      const rect = target.getBoundingClientRect();
      marker.classList.remove('active', 'current');
      if (rect.bottom < viewportCenter) marker.classList.add('active');
      else if (rect.top < viewportCenter && rect.bottom > viewportCenter) marker.classList.add('current');
    });
  }

  const triggerIdleSpark = () => {
    if (lastProgress > 0.05 && lastProgress < 0.95) triggerSpark(Math.round(lastProgress * 100));
    idleTimer = setTimeout(triggerIdleSpark, 2000 + Math.random() * 2000);
  };

  // Single scroll listener
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateTimeline);
      ticking = true;
    }
    clearTimeout(idleTimer);
    idleTimer = setTimeout(triggerIdleSpark, 3000);
  }, { passive: true });

  // Event delegation for marker clicks
  timeline.addEventListener('click', (e) => {
    const marker = e.target.closest('.cable-marker');
    if (!marker) return;
    const target = document.querySelector(marker.dataset.target);
    if (!target) return;
    const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
    window.scrollTo({ top: target.offsetTop - headerHeight - 20, behavior: 'smooth' });
  });

  updateTimeline();
  idleTimer = setTimeout(triggerIdleSpark, 3000);
}

/* ============================================
   FORMS
   ============================================ */

function initForms() {
  // Formularze są tylko wizualne - bez funkcjonalności wysyłki
  document.querySelectorAll('form').forEach(form => {
    // Tylko formatowanie telefonu i style (focus/blur) - bez logiki wysyłki
    form.addEventListener('focusin', (e) => {
      if (e.target.matches('.form-input')) e.target.parentElement?.classList.add('focused');
    });
    form.addEventListener('focusout', (e) => {
      if (e.target.matches('.form-input')) {
        e.target.parentElement?.classList.remove('focused');
        e.target.parentElement?.classList.toggle('filled', !!e.target.value);
      }
    });
    form.addEventListener('input', (e) => {
      if (e.target.type === 'tel') formatPhone(e);
    });
    
    // Blokuj domyślne wysłanie formularza (formularze są tylko wizualne)
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      return false;
    });
  });
}

function formatPhone(e) {
  let value = e.target.value.replace(/\D/g, '').slice(0, 9);
  if (value.length > 6) value = `${value.slice(0, 3)} ${value.slice(3, 6)} ${value.slice(6)}`;
  else if (value.length > 3) value = `${value.slice(0, 3)} ${value.slice(3)}`;
  e.target.value = value;
}

/* ============================================
   PARTICLES
   ============================================ */

function initParticles() {
  const container = document.querySelector('.particles-container');
  if (!container || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  for (let i = 0; i < 12; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    const size = 1 + Math.random() * 2;
    particle.style.cssText = `width:${size}px;height:${size}px;left:${Math.random() * 100}%;animation-delay:${Math.random() * 10}s;animation-duration:${8 + Math.random() * 6}s`;
    container.appendChild(particle);
  }
}

/* ============================================
   SMOOTH SCROLL
   ============================================ */

function initSmoothScroll() {
  document.addEventListener('click', (e) => {
    const anchor = e.target.closest('a[href^="#"]');
    if (!anchor) return;
    const href = anchor.getAttribute('href');
    if (href === '#') return;
    const target = document.querySelector(href);
    if (!target) return;
    e.preventDefault();
    const scrollPaddingTop = parseInt(getComputedStyle(document.documentElement).scrollPaddingTop) || 180;
    window.scrollTo({ top: Math.max(0, target.offsetTop - scrollPaddingTop), behavior: 'smooth' });
  });
}

/* ============================================
   ACTIVE PAGE HIGHLIGHT
   ============================================ */

function initActivePageHighlight() {
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
    const href = link.getAttribute('href');
    const isActive = currentPath.endsWith(href) || (currentPath === '/' && href === 'index.html') || (currentPath.endsWith('/') && href === 'index.html');
    link.classList.toggle('active', isActive);
  });
}

/* ============================================
   CITY PREFILL FROM URL PARAMETER
   ============================================ */

function initCityPrefill() {
  const miastoSlug = new URLSearchParams(window.location.search).get('miasto');
  if (!miastoSlug) return;

  const cityMap = {
    'gorzow-wielkopolski': 'Gorzów Wielkopolski',
    'kostrzyn-nad-odra': 'Kostrzyn nad Odrą',
    'strzelce-krajenskie': 'Strzelce Krajeńskie',
    'miedzyrzecz': 'Międzyrzecz',
    'sulecin': 'Sulęcin',
    'skwierzyna': 'Skwierzyna',
    'debno': 'Dębno',
    'mysliborz': 'Myślibórz',
    'drezdenko': 'Drezdenko',
    'witnica': 'Witnica',
    'slubice': 'Słubice',
    'rzepin': 'Rzepin'
  };

  const cityName = cityMap[miastoSlug];
  if (!cityName) return;

  const prefix = `Miasto: ${cityName}`;
  
  [document.querySelector('input[name="subject"]'), document.querySelector('textarea[name="message"], textarea[name="wiadomosc"]')]
    .filter(Boolean)
    .forEach(field => {
      if (!field.value.startsWith('Miasto:')) {
        field.value = field.value.trim() ? `${prefix}\n${field.value.trim()}` : prefix;
      }
    });
}
