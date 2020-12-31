const cacheVersion = 'CAFE.3.42.22.002';

const filesToCache = [
  '/',
];

self.addEventListener("install", function(event){
	caches.open(cacheVersion).then(function(cache) {
        return cache.addAll(filesToCache)
    })
});

self.addEventListener("fetch", function(event){
	//console.log('Fetch: ${e.request.url}');
	event.respondWith(
	
		caches.match(event.request).then(function(response){
			return response || fetch(event.request);
		})
		
	);
});

self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames
          .filter(function(cacheName) {
            return cacheName !== cacheVersion;
          })
          .map(function(cacheName) {
            caches.delete(cacheName);
          })
      );
    })
  );
});


self.addEventListener('message', function(event) {
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});