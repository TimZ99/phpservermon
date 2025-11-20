import './bootstrap';
import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser'

window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;

const root = document.documentElement;
const prefersDarkQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

const resolveTheme = () => {
    const preference = root?.dataset.themePreference || 'auto';

    if (preference === 'auto') {
        if (prefersDarkQuery) {
            return prefersDarkQuery.matches ? 'night' : 'day';
        }

        return 'day';
    }

    return preference;
};

const applyTheme = () => {
    if (!root) {
        return;
    }

    const resolved = resolveTheme();
    root.dataset.themeResolved = resolved;
    root.dataset.bsTheme = resolved === 'night' ? 'dark' : 'light';
    root.classList.toggle('theme-night', resolved === 'night');
    root.classList.toggle('theme-day', resolved !== 'night');
};

applyTheme();

if (prefersDarkQuery) {
    const onSystemThemeChange = () => {
        if ((root?.dataset.themePreference || 'auto') === 'auto') {
            applyTheme();
        }
    };

    if (prefersDarkQuery.addEventListener) {
        prefersDarkQuery.addEventListener('change', onSystemThemeChange);
    } else if (prefersDarkQuery.addListener) {
        prefersDarkQuery.addListener(onSystemThemeChange);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const headerInner = document.querySelector('.page-header-inner');
    const navbar = document.querySelector('nav.navbar');

    if (!headerInner || !navbar || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(([entry]) => {
        if (entry && entry.isIntersecting) {
            headerInner.classList.remove('is-sticky');
        } else {
            headerInner.classList.add('is-sticky');
        }
    });

    observer.observe(navbar);
});
