'use strict';

// ── Dark / Light Mode ──────────────────────────────────
const THEME_KEY = 'hp_theme';

const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    const icon = document.getElementById('hp-theme-icon');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    localStorage.setItem(THEME_KEY, theme);
};

const initTheme = () => {
    const saved = localStorage.getItem(THEME_KEY)
        || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);

    document.getElementById('hp-theme-toggle')?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
};

// ── Mobile Navigation ──────────────────────────────────
const initMobileNav = () => {
    const btn = document.getElementById('hp-hamburger');
    const nav = document.getElementById('hp-mobile-nav');
    if (!btn || !nav) return;

    btn.addEventListener('click', () => {
        nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', nav.classList.contains('open'));
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !nav.contains(e.target)) {
            nav.classList.remove('open');
        }
    });
};

// ── Sticky Navbar shadow on scroll ────────────────────
const initStickyNav = () => {
    const navbar = document.querySelector('.hp-navbar');
    if (!navbar) return;

    window.addEventListener('scroll', () => {
        navbar.style.boxShadow = window.scrollY > 10
            ? '0 4px 24px rgba(0,0,0,.3)'
            : '0 2px 20px rgba(0,0,0,.2)';
    }, { passive: true });
};

// ── Smooth scroll for anchor links ────────────────────
const initSmoothScroll = () => {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', (e) => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
};

// ── Newsletter form ────────────────────────────────────
const initNewsletter = () => {
    document.querySelectorAll('.hp-newsletter-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = form.querySelector('input[type="email"]')?.value;
            if (!email) return;

            const btn = form.querySelector('button');
            const orig = btn.textContent;
            btn.textContent = 'Eingetragen!';
            btn.style.background = '#10b981';
            form.querySelector('input').value = '';

            setTimeout(() => {
                btn.textContent = orig;
                btn.style.background = '';
            }, 3000);
        });
    });
};

// ── Active nav link ────────────────────────────────────
const initActiveNav = () => {
    const path = window.location.pathname;
    document.querySelectorAll('.hp-nav-links a, .hp-mobile-nav a').forEach(a => {
        if (a.getAttribute('href') === path ||
            (path.startsWith('/blog') && a.getAttribute('href') === '/blog')) {
            a.classList.add('active');
        }
    });
};

// ── Back to top button ─────────────────────────────────
const initBackToTop = () => {
    const btn = document.getElementById('hp-back-to-top');
    if (!btn) return;

    window.addEventListener('scroll', () => {
        btn.style.opacity = window.scrollY > 400 ? '1' : '0';
        btn.style.pointerEvents = window.scrollY > 400 ? 'auto' : 'none';
    }, { passive: true });

    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
};

// ── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileNav();
    initStickyNav();
    initSmoothScroll();
    initNewsletter();
    initActiveNav();
    initBackToTop();
});
