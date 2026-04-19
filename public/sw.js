/**
 * Service worker kill-switch.
 * Clears all caches and unregisters this SW so existing installs are cleaned up.
 * PWA feature has been removed from the app.
 */
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.map(k => caches.delete(k))))
            .then(() => self.registration.unregister())
            .then(() => self.clients.matchAll())
            .then(clients => clients.forEach(c => c.navigate(c.url)))
    );
});

// ── Everything below is dead code kept only so the file size changes ──────────
const _REMOVED = true;

