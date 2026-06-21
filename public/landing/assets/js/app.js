document.querySelector('.menu-btn')?.addEventListener('click', () => {
  document.querySelector('.nav')?.classList.toggle('open');
});

const formatNumber = (value) => new Intl.NumberFormat('es-AR').format(value);

const animateCounter = (el, target, duration = 1400) => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) {
    el.textContent = `${formatNumber(target)}${el.dataset.suffix || ''}`;
    return;
  }

  const start = performance.now();

  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(target * eased);
    el.textContent = `${formatNumber(current)}${el.dataset.suffix || ''}`;

    if (progress < 1) {
      requestAnimationFrame(tick);
    }
  };

  requestAnimationFrame(tick);
};

const communitySection = document.querySelector('.community');
const counters = document.querySelectorAll('.community [data-count]');
let countersAnimated = false;

if (communitySection && counters.length) {
  const observer = new IntersectionObserver((entries) => {
    if (countersAnimated) return;

    for (const entry of entries) {
      if (entry.isIntersecting) {
        countersAnimated = true;
        counters.forEach((counter) => {
          const target = Number(counter.dataset.count || 0);
          animateCounter(counter, target);
        });
        observer.disconnect();
        break;
      }
    }
  }, {
    threshold: 0.35,
  });

  observer.observe(communitySection);
}

const mapSection = document.querySelector('.map');
if (mapSection) {
  const mapObserver = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        mapSection.classList.add('is-visible');
        mapObserver.disconnect();
        break;
      }
    }
  }, {
    threshold: 0.25,
  });

  mapObserver.observe(mapSection);
}
