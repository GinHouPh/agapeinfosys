/**
 * Site behaviour: sticky header state, mobile navigation, animated counters
 * and the back-to-top control.
 */

const onScroll = () => {
    const header = document.querySelector('[data-site-header]');

    if (header) {
        header.classList.toggle('is-stuck', window.scrollY > 24);
    }

    const toTop = document.querySelector('[data-to-top]');
    if (toTop) {
        toTop.classList.toggle('is-visible', window.scrollY > 600);
    }
};

const initNav = () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (!toggle || !panel) {
        return;
    }

    const setOpen = (open) => {
        panel.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggle.addEventListener('click', () => setOpen(!panel.classList.contains('is-open')));

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            setOpen(false);
        }
    });
};

const animateCounter = (element) => {
    const target = Number(element.dataset.count ?? 0);
    const duration = 1600;
    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = String(Math.round(target * eased));

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
};

const initCounters = () => {
    const counters = document.querySelectorAll('[data-count]');

    if (counters.length === 0 || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.4 },
    );

    counters.forEach((counter) => observer.observe(counter));
};

const initYear = () => {
    document.querySelectorAll('[data-year]').forEach((node) => {
        node.textContent = String(new Date().getFullYear());
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initCounters();
    initYear();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
