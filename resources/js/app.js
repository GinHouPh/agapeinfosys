/**
 * Site behaviour: the floating bottom navigation, the search overlay,
 * the dark/light/screen theme switch, animated counters and the
 * back-to-top control.
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

const THEME_KEY = 'theme';
const THEME_MODES = ['dark', 'light', 'system'];
const THEME_COLOR = { dark: '#0c0c0c', light: '#f6f6f3' };

const applyTheme = (mode) => {
    const light = mode === 'light' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: light)').matches);
    const root = document.documentElement;

    root.classList.toggle('light', light);
    root.classList.toggle('dark', !light);

    document.querySelectorAll('[data-theme-choice]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.themeChoice === mode));
    });

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', light ? THEME_COLOR.light : THEME_COLOR.dark);
    }
};

const initTheme = () => {
    const choices = document.querySelectorAll('[data-theme-choice]');

    // Pages without the switch (the admin shell) keep their own dark skin.
    if (choices.length === 0) {
        return;
    }

    let mode = 'dark';

    try {
        mode = localStorage.getItem(THEME_KEY) || 'dark';
    } catch (error) {
        mode = 'dark';
    }

    if (!THEME_MODES.includes(mode)) {
        mode = 'dark';
    }

    applyTheme(mode);

    choices.forEach((button) => {
        button.addEventListener('click', () => {
            mode = button.dataset.themeChoice;

            try {
                localStorage.setItem(THEME_KEY, mode);
            } catch (error) {
                // Storage can be unavailable (private mode); the choice
                // still applies for this page view.
            }

            applyTheme(mode);
        });
    });

    // While the mode follows the screen, repaint as the OS flips.
    const scheme = window.matchMedia('(prefers-color-scheme: light)');
    const onSchemeChange = () => {
        if (mode === 'system') {
            applyTheme(mode);
        }
    };

    if (scheme.addEventListener) {
        scheme.addEventListener('change', onSchemeChange);
    } else if (scheme.addListener) {
        scheme.addListener(onSchemeChange);
    }
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

const initAdminSidebar = () => {
    const toggle = document.querySelector('[data-admin-sidebar-toggle]');

    if (!toggle) {
        return;
    }

    const STORAGE_KEY = 'admin-sidebar-collapsed';

    const isCollapsed = () => document.documentElement.getAttribute('data-admin-sidebar') === 'collapsed';

    const apply = (collapsed) => {
        if (collapsed) {
            document.documentElement.setAttribute('data-admin-sidebar', 'collapsed');
        } else {
            document.documentElement.removeAttribute('data-admin-sidebar');
        }

        toggle.setAttribute('aria-expanded', String(!collapsed));

        try {
            localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        } catch (error) {
            // Storage can be unavailable (private mode); the state
            // still applies for this page view.
        }
    };

    apply(isCollapsed());

    toggle.addEventListener('click', () => apply(!isCollapsed()));
};

const initMarketNav = () => {
    const nav = document.querySelector('[data-market-nav]');

    if (!nav) {
        return;
    }

    const toggles = Array.from(nav.querySelectorAll('[data-market-toggle]'));
    const panels = Array.from(nav.querySelectorAll('[data-market-panel]'));

    const setPanel = (key) => {
        panels.forEach((panel) => {
            panel.hidden = panel.dataset.marketPanel !== key;
        });

        toggles.forEach((toggle) => {
            toggle.setAttribute('aria-expanded', String(toggle.dataset.marketToggle === key));
        });
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const key = toggle.dataset.marketToggle;
            setPanel(toggle.getAttribute('aria-expanded') === 'true' ? null : key);
        });
    });

    panels.forEach((panel) => {
        const nodes = Array.from(panel.querySelectorAll('[data-market-node][data-market-key]'));
        const subs = Array.from(panel.querySelectorAll('[data-market-sub]'));

        const showSub = (key) => {
            subs.forEach((sub) => {
                sub.hidden = sub.dataset.marketSub !== key;
            });
        };

        const hasSub = (key) => subs.some((sub) => sub.dataset.marketSub === key);

        nodes.forEach((node) => {
            const key = node.dataset.marketKey;

            // Keep the current submenu open when hovering a category without
            // children, so the pointer can travel down to the sub-items.
            const reveal = () => {
                if (hasSub(key)) {
                    showSub(key);
                }
            };

            node.addEventListener('mouseenter', reveal);
            node.addEventListener('focusin', reveal);
        });

        panel.addEventListener('mouseleave', () => showSub(null));
    });

    document.addEventListener('click', (event) => {
        if (!nav.contains(event.target)) {
            setPanel(null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setPanel(null);
        }
    });
};

const initBrandLogoFields = () => {
    document.querySelectorAll('[data-logo-mode]').forEach((select) => {
        const group = select.closest('[data-logo-group]');

        if (!group) {
            return;
        }

        const fields = Array.from(group.querySelectorAll('[data-logo-field]'));

        const sync = () => {
            fields.forEach((field) => {
                field.hidden = field.dataset.logoField !== select.value;
            });
        };

        select.addEventListener('change', sync);
        sync();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initSearch();
    initTheme();
    initCounters();
    initYear();
    initAdminSidebar();
    initMarketNav();
    initBrandLogoFields();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
