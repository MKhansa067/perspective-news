const CACHE_NAME = 'perspective-news-v1';
const urlsToCache = [
    '/',
    '/offline.html', // Pastikan file ini ada di folder public jika mau dipakai
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
    '/icon.svg'
];

// 1. Install Service Worker & Cache File Statis
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// 2. Fetch Resource (Cek Cache dulu, jika tidak ada baru ambil dari Internet)
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Jika ada di cache, kembalikan response cache
                if (response) {
                    return response;
                }
                // Jika tidak, ambil dari internet
                return fetch(event.request);
            })
    );
});

// 3. Activate & Hapus Cache Lama (Saat versi baru di-deploy)
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
