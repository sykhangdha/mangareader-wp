(function () {
	'use strict';

	var cfg = window.mangaView;
	if (!cfg || !cfg.id) { return; }

	// Count a visit at most once every 6 hours per browser (the server checks too).
	var key = 'mangaViewed:' + cfg.id, now = Date.now(), last = 0;
	try { last = parseInt(localStorage.getItem(key), 10) || 0; } catch (e) {}
	if (now - last < 6 * 3600 * 1000) { return; }
	try { localStorage.setItem(key, String(now)); } catch (e) {}

	var body = new URLSearchParams({ action: 'manga_count_view', id: cfg.id });
	if (navigator.sendBeacon) {
		navigator.sendBeacon(cfg.ajaxUrl, body);
	} else if (window.fetch) {
		fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true });
	}
})();
