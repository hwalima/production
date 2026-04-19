/**
 * MyMine PWA Service Worker
 *
 * Strategies:
 *  - /build/*  (Vite hashed assets)  → Cache-first  (immutable, content-hashed)
 *  - /icons/*  /fonts/*              → Cache-first
 *  - Navigation (HTML pages)         → Network-first, cache on success, offline page on failure
 *  - Everything else                 → Network-first, cache fallback
 *
 * POST / non-GET / cross-origin requests are never intercepted.
 */

const CACHE_VERSION = 'mymine-v2';

const PRECACHE = [
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

// ── Install ───────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then(cache => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

// ── Activate ──────────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(k => k !== CACHE_VERSION)
                    .map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // Only intercept GET requests from the same origin
    if (req.method !== 'GET' || url.origin !== self.location.origin) return;

    // Skip deploy/opcache/admin scripts
    if (url.pathname.startsWith('/deploy') || url.pathname.startsWith('/opcache')) return;

    // Never intercept auth routes — always pass directly to the network so the
    // login/logout flow is never served from a stale cache.
    const AUTH_PATHS = ['/login', '/logout', '/register', '/two-factor', '/forgot-password', '/reset-password', '/password'];
    if (AUTH_PATHS.some(p => url.pathname === p || url.pathname.startsWith(p + '/'))) return;

    // ── 1. Vite hashed assets → Cache-first (they never change)
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(cacheFirst(req));
        return;
    }

    // ── 2. manifest / sw → Cache-first (offline.html is network-first so the retry works)
    if (url.pathname === '/manifest.json' || url.pathname === '/sw.js') {
        event.respondWith(cacheFirst(req));
        return;
    }

    // ── 3. Navigation requests (HTML pages) → Network-first, cache on success
    if (req.mode === 'navigate') {
        event.respondWith(networkFirstNav(req));
        return;
    }

    // ── 4. Everything else → Network-first with cache fallback
    event.respondWith(networkFirst(req));
});

// ── Strategies ────────────────────────────────────────────────────────────────

/** Cache-first: return cached copy immediately; fetch and update in background if not cached */
async function cacheFirst(req) {
    const cached = await caches.match(req);
    if (cached) return cached;
    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        return new Response('Asset unavailable offline', { status: 503 });
    }
}

/** Network-first for navigation: try network, cache result, fall back to cache or offline page */
async function networkFirstNav(req) {
    try {
        const response = await fetch(req);
        // Only cache clean 200 HTML responses — never cache redirects or errors,
        // as a cached redirect would prevent the user from reaching the login page.
        if (response.status === 200 && response.type !== 'opaqueredirect') {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        // Offline: check cache first
        const cached = await caches.match(req);
        if (cached) return cached;
        // Last resort: offline page
        return caches.match('/offline.html');
    }
}

/** Network-first for other requests: try network, fall back to cache */
async function networkFirst(req) {
    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        return caches.match(req);
    }
}
