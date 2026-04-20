/**
 * MyMine Service Worker — Safe PWA (v2)
 *
 * Strategy:
 *  - Navigation requests (HTML page loads) → NEVER intercepted.
 *    The browser handles them natively so server-side auth redirects
 *    (login / logout) always work correctly. No "You're Offline" screen.
 *  - Vite build assets (/build/assets/*) → cache-first.
 *    Safe because Vite embeds a content hash in every filename.
 *  - Everything else → network pass-through (no caching, no fallback).
 */

const CACHE = 'mymine-v2';

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => k !== CACHE).map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) return;

    // CRITICAL: never intercept navigation requests (top-level HTML page loads).
    // By returning without calling event.respondWith(), the browser fetches
    // the page from the network normally — auth redirects work perfectly.
    if (request.mode === 'navigate') return;

    // Cache-first for Vite's content-hashed JS/CSS bundles
    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(
            caches.open(CACHE).then(cache =>
                cache.match(request).then(cached => {
                    if (cached) return cached;
                    return fetch(request).then(response => {
                        if (response.ok) cache.put(request, response.clone());
                        return response;
                    });
                })
            )
        );
        return;
    }

    // All other requests (icons, fonts, API calls) — network pass-through
});

