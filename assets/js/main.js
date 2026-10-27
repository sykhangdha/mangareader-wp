(function () {
	'use strict';

	var root = document.documentElement;
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	var mobile = window.matchMedia('(max-width: 768px)');

	function read(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
	function write(key, value) { try { localStorage.setItem(key, value); } catch (e) {} }

	/* ---------- Reading data kept in this browser: bookmarks, progress, read chapters ---------- */
	var LOCAL_KEY = 'mangaLocal';

	function trimOldest(map, max, field) {
		var keys = Object.keys(map);
		if (keys.length <= max) { return; }
		keys.sort(function (a, b) {
			var x = field ? map[a][field] : map[a], y = field ? map[b][field] : map[b];
			return (x || 0) - (y || 0);
		}).slice(0, keys.length - max).forEach(function (k) { delete map[k]; });
	}

	function localData() {
		var d = null;
		try { d = JSON.parse(localStorage.getItem(LOCAL_KEY)); } catch (e) {}
		if (!d || typeof d !== 'object') { d = {}; }
		d.bookmarks = d.bookmarks || {};
		d.progress = d.progress || {};
		d.read = d.read || {};
		// Drop incomplete entries (an earlier bug saved a bookmark without its details).
		Object.keys(d.bookmarks).forEach(function (k) {
			var b = d.bookmarks[k];
			if (!b || !b.id || !b.url || k === 'undefined') { delete d.bookmarks[k]; }
		});
		return d;
	}

	function localSave(d) {
		trimOldest(d.read, 3000, null);
		trimOldest(d.progress, 40, 'ts');
		trimOldest(d.bookmarks, 300, 'ts');
		try { localStorage.setItem(LOCAL_KEY, JSON.stringify(d)); } catch (e) {}
	}

	window.MangaLocal = {
		get: localData,
		// Links come from storage, so only follow ones that stay on this site.
		// Links come from storage: only follow ones on this site's host, and match this page's http/https
		// (WordPress may store http:// addresses while visitors browse over https://).
		safeUrl: function (u) {
			if (typeof u !== 'string' || !u) { return ''; }
			try {
				var url = new URL(u, window.location.href);
				if ((url.protocol !== 'http:' && url.protocol !== 'https:') || url.host !== window.location.host) { return ''; }
				url.protocol = window.location.protocol;
				return url.href;
			} catch (e) { return ''; }
		},
		isBookmarked: function (id) { return !!localData().bookmarks[String(id)]; },
		toggleBookmark: function (info) {
			if (!info || !info.id || !info.url) { return false; }
			var d = localData(), key = String(info.id), on;
			if (d.bookmarks[key]) {
				delete d.bookmarks[key];
				on = false;
			} else {
				d.bookmarks[key] = { id: info.id, title: info.title, url: info.url, cover: info.cover, ts: Date.now() };
				on = true;
			}
			localSave(d);
			return on;
		},
		saveProgress: function (p) { var d = localData(); p.ts = Date.now(); d.progress[String(p.mangaId)] = p; localSave(d); },
		getProgress: function (mangaId) { return localData().progress[String(mangaId)] || null; },
		clearProgress: function () { var d = localData(); d.progress = {}; localSave(d); },
		markRead: function (chapterId) { var d = localData(); d.read[String(chapterId)] = Date.now(); localSave(d); }
	};

	/* ---------- Theme switcher (FluentUI / MetroUI / MangaStreamUI) ---------- */
	var themeBtn = $('#themeToggle');
	var themeMenu = $('#themeMenu');
	var accentPicker = $('#accentPicker');
	var modePicker = $('#modePicker');
	var options = $$('.theme-option[data-theme-value]');
	var modeOptions = $$('.theme-option[data-mode-value]');
	var swatches = $$('.accent-swatch');
	var DEFAULT_ACCENT = '#0050ef';

	function setThemeMenu(open) {
		if (!themeMenu) { return; }
		themeMenu.hidden = !open;
		themeBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function setTheme(theme, persist) {
		root.setAttribute('data-theme', theme);
		options.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.themeValue === theme ? 'true' : 'false'); });
		if (modePicker) { modePicker.hidden = theme !== 'fluent'; }
		if (accentPicker) { accentPicker.hidden = theme !== 'metro'; }
		if (persist) { write('mangaTheme', theme); }
	}

	function setMode(mode, persist) {
		root.setAttribute('data-mode', mode);
		modeOptions.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.modeValue === mode ? 'true' : 'false'); });
		if (persist) { write('mangaMode', mode); }
	}

	function setAccent(hex, persist) {
		var n = parseInt(hex.slice(1), 16);
		var lum = ((((n >> 16) & 255) * 299) + (((n >> 8) & 255) * 587) + ((n & 255) * 114)) / 1000;
		root.style.setProperty('--metro-accent', hex);
		root.style.setProperty('--metro-on-accent', lum > 150 ? '#000' : '#fff');
		swatches.forEach(function (s) { s.setAttribute('aria-pressed', s.dataset.accent.toLowerCase() === hex.toLowerCase() ? 'true' : 'false'); });
		if (persist) { write('mangaAccent', hex); }
	}

	if (themeBtn && themeMenu) {
		var savedAccent = read('mangaAccent');
		setAccent(/^#[0-9a-f]{6}$/i.test(savedAccent || '') ? savedAccent : DEFAULT_ACCENT, false);
		setTheme(root.getAttribute('data-theme') || 'fluent', false);
		setMode(root.getAttribute('data-mode') || 'light', false);

		themeBtn.addEventListener('click', function () { setThemeMenu(themeMenu.hidden); });
		options.forEach(function (b) { b.addEventListener('click', function () { setTheme(b.dataset.themeValue, true); }); });
		modeOptions.forEach(function (b) { b.addEventListener('click', function () { setMode(b.dataset.modeValue, true); }); });
		swatches.forEach(function (s) { s.addEventListener('click', function () { setAccent(s.dataset.accent, true); }); });
	}

	/* ---------- Mobile menu ---------- */
	var nav = $('#mainNav');
	var menuBtn = $('#mobileMenuToggle');

	function setNav(open) {
		if (!nav) { return; }
		nav.classList.toggle('is-open', open);
		menuBtn.classList.toggle('active', open);
		menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	if (nav && menuBtn) {
		menuBtn.addEventListener('click', function () { setNav(!nav.classList.contains('is-open')); });

		nav.addEventListener('click', function (e) {
			var link = e.target.closest('a');
			if (!link || !mobile.matches) { return; }
			var li = link.parentElement;
			if (li.classList.contains('menu-item-has-children') && link.parentElement.firstElementChild === link) {
				e.preventDefault();
				li.classList.toggle('active');
			} else {
				setNav(false);
			}
		});

		var onBreakpoint = function (e) {
			if (!e.matches) {
				setNav(false);
				$$('.primary-menu li.active').forEach(function (li) { li.classList.remove('active'); });
			}
		};
		if (mobile.addEventListener) { mobile.addEventListener('change', onBreakpoint); } else { mobile.addListener(onBreakpoint); }
	}

	/* ---------- Search modal ---------- */
	var modal = $('#searchModal');
	var lastTrigger = null;

	function openSearch(trigger) {
		if (!modal) { return; }
		lastTrigger = trigger;
		modal.classList.add('active');
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		setTimeout(function () { var i = $('.search-input-modal', modal); if (i) { i.focus(); } }, 80);
	}

	function closeSearch() {
		if (!modal || !modal.classList.contains('active')) { return; }
		modal.classList.remove('active');
		modal.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
		if (lastTrigger) { lastTrigger.focus(); }
	}

	document.addEventListener('click', function (e) {
		var opener = e.target.closest('[data-open-search]');
		if (opener) { openSearch(opener); return; }

		if (modal && (e.target.closest('#searchModalClose') || e.target.classList.contains('search-modal-overlay'))) {
			closeSearch();
			return;
		}
		if (!e.target.closest('.theme-switcher')) { setThemeMenu(false); }
		if (nav && nav.classList.contains('is-open') && !e.target.closest('#mainNav, #mobileMenuToggle')) { setNav(false); }
	});

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		closeSearch();
		setThemeMenu(false);
		setNav(false);
	});
})();
