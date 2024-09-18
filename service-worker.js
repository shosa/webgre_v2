const CACHE_NAME = 'webgre-v2';
const urlsToCache = [
  '/webgre/css/sb-admin-2.css',
  '/webgre/css/sb-admin-2.min.css',
  '/webgre/vendor/fontawesome-free/css/all.min.css',
  '/webgre/vendor/datatables/dataTables.bootstrap4.min.css',
  '/webgre/img/logo.png',
  '/webgre/img/logoMini.png',
  '/webgre/img/roundLogo.png'
    // Aggiungi altre risorse che vuoi cacheare
];
// Installa il service worker e cachea le risorse
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(urlsToCache);
    })
  );
});
// Gestisce le richieste di rete
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Cache hit - ritorna la risposta dal cache
        if (response) {
          return response;
        }
        return fetch(event.request);
    })
  );
});
