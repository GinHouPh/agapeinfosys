/**
 * Site behaviour: the floating bottom navigation, the search overlay,
 * animated counters and the back-to-top control.
 */

const onScroll = () => {
    const toTop = document.querySelector('[data-to-top]');
    if (toTop) {
        toTop.classList.toggle('is-visible', window.scrollY > 600);
    }

    const progress = document.querySelector('[data-scroll-progress]');
    if (progress) {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const ratio = scrollable > 0 ? Math.min(window.scrollY / scrollable, 1) : 0;
        progress.style.transform = `scaleX(${ratio})`;
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
        if (window.innerWidth >= 1280) {
            setOpen(false);
        }
    });
};

const initSearch = () => {
    const openers = document.querySelectorAll('[data-search-open]');
    const panel = document.querySelector('[data-search-panel]');

    if (openers.length === 0 || !panel) {
        return;
    }

    const input = panel.querySelector('input');
    const closers = panel.querySelectorAll('[data-search-close]');

    const setOpen = (open) => {
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', String(!open));
        openers.forEach((opener) => opener.setAttribute('aria-expanded', String(open)));
        document.body.classList.toggle('overflow-hidden', open);

        if (open && input) {
            input.focus();
        }
    };

    openers.forEach((opener) => {
        opener.addEventListener('click', () => setOpen(true));
    });

    closers.forEach((closer) => {
        closer.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.classList.contains('is-open')) {
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
    initSearch();
    initCounters();
    initYear();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
