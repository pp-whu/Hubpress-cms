/**
 * HuberCMS Admin Panel JavaScript (ES2025)
 *
 * Features:
 * - Sidebar toggle (mobile)
 * - Dark / Light mode with localStorage persistence
 * - CSRF token injection for all fetch() calls
 * - Toast auto-dismiss
 * - Confirm dialogs for destructive actions
 * - Admin search (quick filter)
 * - AJAX form submissions
 *
 * @package HuberCMS
 */

'use strict';

// ============================================================
// Utilities
// ============================================================

/**
 * Returns the CSRF token from the meta tag.
 */
const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

/**
 * Performs a fetch() with CSRF token and JSON support pre-configured.
 *
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<Response>}
 */
const apiFetch = (url, options = {}) => {
    const headers = {
        'X-CSRF-Token': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers ?? {}),
    };

    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    return fetch(url, { ...options, headers });
};

// ============================================================
// Toast Notifications
// ============================================================

/**
 * Programmatically shows a toast notification.
 *
 * @param {'success'|'error'|'warning'|'info'} type
 * @param {string} message
 */
const showToast = (type, message) => {
    const icons = {
        success: 'bi-check-circle-fill',
        error:   'bi-exclamation-triangle-fill',
        warning: 'bi-exclamation-circle-fill',
        info:    'bi-info-circle-fill',
    };

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<i class="bi ${icons[type] ?? icons.info}"></i> ${message}`;
    toast.style.cssText = 'animation: slideInRight 0.3s ease';

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.4s ease forwards';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
};

// ============================================================
// Sidebar Toggle
// ============================================================

const initSidebar = () => {
    const sidebar = document.getElementById('sidebar');
    const toggle  = document.getElementById('sidebarToggle');

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', (e) => {
        if (sidebar.classList.contains('open')
            && !sidebar.contains(e.target)
            && e.target !== toggle) {
            sidebar.classList.remove('open');
        }
    });
};

// ============================================================
// Dark / Light Mode Toggle
// ============================================================

const initThemeToggle = () => {
    const html    = document.documentElement;
    const button  = document.getElementById('themeToggle');
    const STORAGE_KEY = 'hubercms_theme';

    const savedTheme = localStorage.getItem(STORAGE_KEY) ?? 'dark';
    html.setAttribute('data-bs-theme', savedTheme);
    updateToggleIcon(button, savedTheme);

    button?.addEventListener('click', () => {
        const current = html.getAttribute('data-bs-theme');
        const next    = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem(STORAGE_KEY, next);
        updateToggleIcon(button, next);
    });
};

const updateToggleIcon = (button, theme) => {
    if (!button) return;
    const icon = button.querySelector('i');
    if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
};

// ============================================================
// Confirm Delete
// ============================================================

/**
 * Intercepts delete forms / links and shows a confirm dialog.
 */
const initConfirmDelete = () => {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        const message = trigger.dataset.confirm ?? 'Wirklich löschen?';

        if (!confirm(message)) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
};

// ============================================================
// AJAX Form Submissions
// ============================================================

/**
 * Automatically submits forms with [data-ajax] attribute via fetch.
 * Expects JSON response: { success: bool, message: string, redirect?: string }
 */
const initAjaxForms = () => {
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-ajax]');
        if (!form) return;

        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.innerHTML;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Wird gespeichert…';
        }

        try {
            const formData = new FormData(form);
            const response = await apiFetch(form.action || window.location.href, {
                method: form.method.toUpperCase() || 'POST',
                body: formData,
                headers: { 'X-CSRF-Token': getCsrfToken() },
            });

            const data = await response.json();

            if (data.success) {
                showToast('success', data.message ?? 'Erfolgreich gespeichert.');
                if (data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 800);
                }
            } else {
                showToast('error', data.error ?? 'Ein Fehler ist aufgetreten.');
                renderFormErrors(form, data.errors ?? {});
            }
        } catch (err) {
            showToast('error', 'Netzwerkfehler. Bitte versuche es erneut.');
            console.error('AJAX form error:', err);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    });
};

/**
 * Renders validation errors next to form fields.
 *
 * @param {HTMLFormElement} form
 * @param {Record<string, string[]>} errors
 */
const renderFormErrors = (form, errors) => {
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

    for (const [field, messages] of Object.entries(errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;

        input.classList.add('is-invalid');

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = messages[0] ?? '';
        input.after(feedback);
    }
};

// ============================================================
// Admin Quick Search
// ============================================================

const initAdminSearch = () => {
    const searchInput = document.getElementById('adminSearch');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase().trim();
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');

        navLinks.forEach(link => {
            const text = link.textContent.toLowerCase();
            const parent = link.closest('.nav-item');
            if (parent) {
                parent.style.display = query === '' || text.includes(query) ? '' : 'none';
            }
        });
    });
};

// ============================================================
// File Upload with Drag & Drop
// ============================================================

const initFileUpload = () => {
    document.querySelectorAll('[data-dropzone]').forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');

            const files = Array.from(e.dataTransfer?.files ?? []);
            const input = zone.querySelector('input[type="file"]');

            if (input && files.length) {
                const dt = new DataTransfer();
                files.forEach(f => dt.items.add(f));
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
};

// ============================================================
// Auto-dismiss existing flash toasts
// ============================================================

const autoDismissToasts = () => {
    document.querySelectorAll('.toast-notification').forEach((toast, i) => {
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.4s ease forwards';
            setTimeout(() => toast.remove(), 400);
        }, 4000 + i * 300);
    });
};

// ============================================================
// Sidebar — klappbare Untermenüs (WP-Stil)
// ============================================================

const initSidebarSubmenus = () => {
    // Klick-Toggle
    document.querySelectorAll('.sidebar-parent').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const item = link.closest('.sidebar-has-submenu');
            if (!item) return;

            // Alle anderen schließen
            document.querySelectorAll('.sidebar-has-submenu.submenu-open').forEach(other => {
                if (other !== item) other.classList.remove('submenu-open');
            });

            item.classList.toggle('submenu-open');
        });
    });

    // Beim Laden: nur öffnen wenn ein Kind-Link aktiv ist
    document.querySelectorAll('.sidebar-has-submenu').forEach(item => {
        const hasActive = item.querySelector('.sidebar-submenu .nav-link.active');
        if (hasActive) {
            item.classList.add('submenu-open');
        }
    });
};

// ============================================================
// Initialize on DOM ready
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initSidebarSubmenus();
    initThemeToggle();
    initConfirmDelete();
    initAjaxForms();
    initAdminSearch();
    initFileUpload();
    autoDismissToasts();
});
