(function () {
	'use strict';

	var container = document.getElementById('mangaReaderContainer');
	if (!container) { return; }

	function byId(id) { return document.getElementById(id); }

	var topBar = byId('readerTopBar');
	var scroller = byId('readerMainContent');
	var viewer = byId('readerViewer');
	var progressFill = byId('readerProgressFill');
	var indicator = byId('pageIndicator');
	var currentDisplay = byId('currentPageDisplay');
	var panel = byId('readerSettingsPanel');
	var settingsBtn = byId('settingsToggleBtn');
	var fullscreenBtn = byId('fullscreenToggleBtn');
	var zoomDisplay = byId('zoomLevelDisplay');
	var pages = Array.prototype.slice.call(viewer.querySelectorAll('.reader-page'));
	var total = pages.length;
	var nextUrl = container.getAttribute('data-next-url') || '';
	var chapterId = container.getAttribute('data-chapter-id') || '';
	var navigating = false;

	var DEFAULTS = { mode: 'paged', fit: 'width', bg: 'dark', nav: 'click', bar: 'show', dir: 'ltr' };
	// Until the reader picks a background, each UI has its own: Metro black, MangaStream light grey, Fluent follows its mode.
	(function () {
		var theme = document.documentElement.getAttribute('data-theme');
		var mode = document.documentElement.getAttribute('data-mode');
		DEFAULTS.bg = theme === 'metro' ? 'dark' : (theme === 'mangastream' ? 'light' : (/^(dark|light|sepia)$/.test(mode) ? mode : 'dark'));
	})();
	var ALLOWED = {
		mode: ['paged', 'longstrip'],
		fit: ['width', 'height', 'original'],
		bg: ['dark', 'light', 'sepia'],
		nav: ['click', 'buttons'],
		bar: ['show', 'auto'],
		dir: ['ltr', 'rtl']
	};
	var KEYS = { mode: 'readerReadingMode', fit: 'readerImageFit', bg: 'readerBgMode', nav: 'readerNavMode', bar: 'readerBarMode', dir: 'readerDirection' };

	function load(name) {
		var v = null;
		try { v = localStorage.getItem(KEYS[name]); } catch (e) {}
		return ALLOWED[name].indexOf(v) > -1 ? v : DEFAULTS[name];
	}
	function save(name, value) {
		try { localStorage.setItem(KEYS[name], value); } catch (e) {}
	}

	var state = { mode: load('mode'), fit: load('fit'), bg: load('bg'), nav: load('nav'), bar: load('bar'), dir: load('dir'), zoom: 1, page: 0 };
	var savedBright = 100;
	try { savedBright = parseInt(localStorage.getItem('readerBrightness'), 10); } catch (e) {}
	state.bright = (savedBright >= 40 && savedBright <= 100) ? savedBright : 100;

	function measure() {
		// Space taken by the toolbar and the footer strip (used by the "Fit height" size).
		var footer = byId('readerFooter');
		container.style.setProperty('--bar-h', (state.bar === 'auto' ? 0 : topBar.offsetHeight) + 'px');
		container.style.setProperty('--foot-h', (footer ? footer.offsetHeight : 0) + 'px');
	}

	function updateProgress() {
		var pct = 0;
		if (state.mode === 'paged') {
			pct = total ? ((state.page + 1) / total) * 100 : 0;
		} else {
			var max = scroller.scrollHeight - scroller.clientHeight;
			pct = max > 0 ? (scroller.scrollTop / max) * 100 : 0;
		}
		progressFill.style.width = pct + '%';
		trackReading(pct);
	}

	function pageFromScroll() {
		var top = scroller.getBoundingClientRect().top;
		for (var i = 0; i < pages.length; i++) {
			if (pages[i].getBoundingClientRect().bottom > top + 10) { return i; }
		}
		return 0;
	}

	function preload(from) {
		for (var k = from; k <= from + 2 && k < total; k++) {
			var img = pages[k].querySelector('img');
			if (img) { img.loading = 'eager'; }
		}
	}

	function showPage(n) {
		if (!total) { return; }
		state.page = Math.max(0, Math.min(total - 1, n));
		pages.forEach(function (p, i) { p.hidden = i !== state.page; });
		currentDisplay.textContent = state.page + 1;
		scroller.scrollTop = 0;
		scroller.scrollLeft = 0;
		preload(state.page);
		updateProgress();
	}

	function go(delta) {
		if (state.mode !== 'paged') { return; }
		var n = state.page + delta;
		if (n >= total) {
			if (nextUrl) { navigate(nextUrl); }
			return;
		}
		if (n >= 0) { showPage(n); }
	}

	function applyMode(previous) {
		if (previous === 'longstrip' && state.mode === 'paged') {
			state.page = pageFromScroll();
		}
		container.classList.toggle('is-longstrip', state.mode === 'longstrip');
		indicator.hidden = state.mode !== 'paged';

		if (state.mode === 'paged') {
			showPage(state.page);
		} else {
			pages.forEach(function (p) { p.hidden = false; });
			if (previous && pages[state.page]) { pages[state.page].scrollIntoView({ block: 'start' }); }
			updateProgress();
		}
		measure();
	}

	function applyFit() {
		ALLOWED.fit.forEach(function (f) { viewer.classList.toggle('fit-' + f, state.fit === f); });
	}

	function applyZoom() {
		viewer.style.setProperty('--zoom', state.zoom);
		zoomDisplay.textContent = Math.round(state.zoom * 100) + '%';
	}

	function zoomBy(step) {
		state.zoom = Math.min(2.5, Math.max(0.5, Math.round((state.zoom + step) * 100) / 100));
		applyZoom();
	}

	function syncButtons() {
		Array.prototype.forEach.call(container.querySelectorAll('[data-setting]'), function (btn) {
			var on = state[btn.dataset.setting] === btn.dataset.value;
			btn.classList.toggle('active', on);
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
	}

	function setSetting(name, value) {
		var previous = state[name];
		state[name] = value;
		save(name, value);
		if (name === 'mode') { applyMode(previous); }
		if (name === 'fit') { applyFit(); }
		if (name === 'bar') { applyBar(); }
		if (name === 'bg') { container.setAttribute('data-current-bg', value); }
		syncButtons();
	}

	function setPanel(open) {
		panel.classList.toggle('open', open);
		panel.setAttribute('aria-hidden', open ? 'false' : 'true');
		settingsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	settingsBtn.addEventListener('click', function () { setPanel(!panel.classList.contains('open')); });
	byId('closeSettingsPanelBtn').addEventListener('click', function () { setPanel(false); });

	document.addEventListener('click', function (e) {
		if (panel.classList.contains('open') && !panel.contains(e.target) && !settingsBtn.contains(e.target)) {
			setPanel(false);
		}
	});

	Array.prototype.forEach.call(container.querySelectorAll('[data-setting]'), function (btn) {
		btn.addEventListener('click', function () { setSetting(btn.dataset.setting, btn.dataset.value); });
	});

	byId('navArrowLeft').addEventListener('click', function () { go(state.dir === 'rtl' ? 1 : -1); });
	byId('navArrowRight').addEventListener('click', function () { go(state.dir === 'rtl' ? -1 : 1); });
	byId('zoomInBtn').addEventListener('click', function () { zoomBy(0.25); });
	byId('zoomOutBtn').addEventListener('click', function () { zoomBy(-0.25); });
	byId('resetZoomBtn').addEventListener('click', function () { state.zoom = 1; applyZoom(); });

	var brightEl = byId('readerBrightness');
	function applyBrightness() {
		viewer.style.filter = state.bright < 100 ? 'brightness(' + (state.bright / 100) + ')' : '';
	}
	brightEl.addEventListener('input', function () {
		state.bright = parseInt(brightEl.value, 10) || 100;
		applyBrightness();
		try { localStorage.setItem('readerBrightness', String(state.bright)); } catch (e) {}
	});

	/* Chapter links and the chapter dropdown load the next chapter in place, so the page
	   (and the music player) is never reloaded. Delegated, because the bar is re-rendered. */
	topBar.addEventListener('change', function (e) {
		if (e.target.id === 'chapterSelector' && e.target.value) { navigate(e.target.value); }
	});
	topBar.addEventListener('click', function (e) {
		var link = e.target.closest('a[rel="prev"], a[rel="next"]');
		if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button > 0) { return; }
		e.preventDefault();
		navigate(link.href);
	});

	function toggleFullscreen() {
		if (!document.fullscreenEnabled) { return; }
		if (document.fullscreenElement) { document.exitFullscreen(); } else { container.requestFullscreen(); }
	}
	if (document.fullscreenEnabled) {
		fullscreenBtn.addEventListener('click', toggleFullscreen);
	} else {
		fullscreenBtn.style.display = 'none';
	}

	/* Click: left third goes back, the rest goes forward. */
	viewer.addEventListener('click', function (e) {
		var endLink = e.target.closest('.reader-end a[rel="next"]');
		if (endLink && !(e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button > 0)) {
			e.preventDefault();
			navigate(endLink.href);
			return;
		}
		if (state.nav !== 'click') { return; }
		var page = e.target.closest('.reader-page');
		if (!page) { return; }

		if (state.mode === 'paged') {
			var rect = scroller.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var back = state.dir === 'rtl' ? x > rect.width * 2 / 3 : x < rect.width / 3;
			go(back ? -1 : 1);
		} else {
			var idx = pages.indexOf(page);
			if (idx < total - 1) {
				pages[idx + 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
			} else if (nextUrl) {
				navigate(nextUrl);
			}
		}
	});

	/* Swipe in paged mode when not zoomed. */
	var touchX = null, touchY = null;
	scroller.addEventListener('touchstart', function (e) {
		if (e.touches.length === 1) {
			touchX = e.touches[0].clientX;
			touchY = e.touches[0].clientY;
		} else {
			touchX = null;
		}
	}, { passive: true });
	scroller.addEventListener('touchend', function (e) {
		if (touchX === null || state.mode !== 'paged' || state.zoom !== 1) { return; }
		var dx = e.changedTouches[0].clientX - touchX;
		var dy = e.changedTouches[0].clientY - touchY;
		touchX = null;
		if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) { go((dx < 0) !== (state.dir === 'rtl') ? 1 : -1); }
	}, { passive: true });

	var ticking = false;
	scroller.addEventListener('scroll', function () {
		if (state.mode !== 'longstrip' || ticking) { return; }
		ticking = true;
		requestAnimationFrame(function () { updateProgress(); ticking = false; });
	}, { passive: true });

	document.addEventListener('keydown', function (e) {
		if (e.ctrlKey || e.metaKey || e.altKey) { return; }
		var tag = e.target && e.target.tagName;
		if (tag === 'SELECT' || tag === 'INPUT' || tag === 'TEXTAREA') { return; }

		switch (e.key) {
			case 'ArrowRight':
				if (state.mode === 'paged') { go(state.dir === 'rtl' ? -1 : 1); e.preventDefault(); }
				break;
			case 'ArrowLeft':
				if (state.mode === 'paged') { go(state.dir === 'rtl' ? 1 : -1); e.preventDefault(); }
				break;
			case 'ArrowDown':
			case 'ArrowUp':
				if (state.mode === 'longstrip') {
					scroller.scrollBy({ top: (e.key === 'ArrowDown' ? 1 : -1) * scroller.clientHeight * 0.8, behavior: 'smooth' });
					e.preventDefault();
				}
				break;
			case 'f':
			case 'F':
				toggleFullscreen();
				e.preventDefault();
				break;
			case 's':
			case 'S':
				setPanel(!panel.classList.contains('open'));
				e.preventDefault();
				break;
			case 'Escape':
				setPanel(false);
				break;
		}
	});

	/* ---------- Calm UI: arrows fade when idle, optional auto-hiding toolbar ---------- */
	var uiTimer = null, barTimer = null;

	function wakeUi() {
		container.classList.add('ui-active');
		clearTimeout(uiTimer);
		uiTimer = setTimeout(function () { container.classList.remove('ui-active'); }, 2500);
	}

	function sleepBar() {
		var focusInBar = document.activeElement && document.activeElement !== document.body && topBar.contains(document.activeElement);
		if (topBar.matches(':hover') || focusInBar) { barTimer = setTimeout(sleepBar, 1200); return; }
		container.classList.remove('bar-active');
	}

	function wakeBar() {
		container.classList.add('bar-active');
		clearTimeout(barTimer);
		barTimer = setTimeout(sleepBar, 1800);
	}

	function applyBar() {
		container.classList.toggle('bar-auto', state.bar === 'auto');
		if (state.bar === 'auto') { wakeBar(); }
		measure();
	}

	container.addEventListener('mousemove', function (e) {
		wakeUi();
		if (e.clientY < 90) { wakeBar(); } // pointer near the top edge brings the toolbar back
	}, { passive: true });
	container.addEventListener('touchstart', function (e) {
		if (e.touches.length && e.touches[0].clientY < 70) { wakeBar(); } // tap the top edge
	}, { passive: true });
	topBar.addEventListener('focusin', wakeBar);
	document.addEventListener('keydown', function (e) { if (e.key === 'Tab') { wakeBar(); wakeUi(); } });

	/* ---------- Remember progress, mark chapters read, prefetch the next one ---------- */
	var local = window.MangaLocal;
	var saveTimer = null, readMarked = false, prefetched = '', prefetchCache = {};

	function meta(name) { return container.getAttribute('data-' + name) || ''; }

	function saveProgress() {
		if (!local || !meta('manga-id')) { return; }
		clearTimeout(saveTimer);
		saveTimer = setTimeout(function () {
			local.saveProgress({
				mangaId: meta('manga-id'), title: meta('manga-title'), url: meta('manga-url'), cover: meta('manga-cover'),
				chapterId: chapterId, chapterUrl: meta('chapter-url'), label: meta('chapter-label'),
				page: state.mode === 'paged' ? state.page : pageFromScroll(), total: total
			});
		}, 500);
	}

	function prefetchNext() {
		if (!nextUrl || prefetched === nextUrl || !window.fetch) { return; }
		var url = nextUrl;
		prefetched = url;
		fetch(url, { credentials: 'same-origin' })
			.then(function (r) { return r.ok ? r.text() : ''; })
			.then(function (html) {
				if (!html) { return; }
				prefetchCache = {};
				prefetchCache[url] = html;
				var doc = new DOMParser().parseFromString(html, 'text/html');
				Array.prototype.slice.call(doc.querySelectorAll('#readerViewer .reader-page img'), 0, 2).forEach(function (img) {
					var pre = new Image();
					pre.src = img.getAttribute('src');
				});
			})
			.catch(function () {});
	}

	function trackReading(pct) {
		saveProgress();
		if (pct >= 70 && !readMarked && chapterId && local) { readMarked = true; local.markRead(chapterId); }
		if (pct >= 60) { prefetchNext(); }
	}

	// Reopening a half-read chapter jumps back to the page you were on.
	function resume() {
		if (!local) { return; }
		var p = local.getProgress(meta('manga-id'));
		if (!p || String(p.chapterId) !== String(chapterId)) { return; }
		var page = parseInt(p.page, 10);
		if (!(page > 0 && page < total - 1)) { return; }
		if (state.mode === 'paged') { showPage(page); } else if (pages[page]) { pages[page].scrollIntoView({ block: 'start' }); }
	}

	/* ---------- Soft chapter navigation ---------- */
	function countView(id) {
		var cfg = window.mangaView;
		if (!cfg || !id) { return; }
		var key = 'mangaViewed:' + id, now = Date.now(), last = 0;
		try { last = parseInt(localStorage.getItem(key), 10) || 0; } catch (e) {}
		if (now - last < 6 * 3600 * 1000) { return; }
		try { localStorage.setItem(key, String(now)); } catch (e) {}
		var body = new URLSearchParams({ action: 'manga_count_view', id: id });
		if (navigator.sendBeacon) { navigator.sendBeacon(cfg.ajaxUrl, body); }
		else if (window.fetch) { fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true }); }
	}

	function swapChapter(doc, next) {
		var newBar = doc.getElementById('readerTopBar');
		var newViewer = doc.getElementById('readerViewer');
		if (!newBar || !newViewer) { throw new Error('Not a chapter page'); }

		// Only the left and centre parts of the bar change; the buttons on the right keep their listeners.
		['.reader-top-bar-left', '.reader-top-bar-center'].forEach(function (sel) {
			var cur = topBar.querySelector(sel), fresh = newBar.querySelector(sel);
			if (cur && fresh) { cur.innerHTML = fresh.innerHTML; }
		});

		viewer.innerHTML = newViewer.innerHTML;
		var heading = scroller.querySelector('.screen-reader-text');
		var newHeading = doc.querySelector('#readerMainContent .screen-reader-text');
		if (heading && newHeading) { heading.textContent = newHeading.textContent; }

		nextUrl = next.getAttribute('data-next-url') || '';
		chapterId = next.getAttribute('data-chapter-id') || '';
		container.setAttribute('data-next-url', nextUrl);
		container.setAttribute('data-chapter-id', chapterId);
		['data-manga-id', 'data-manga-title', 'data-manga-url', 'data-manga-cover', 'data-chapter-label', 'data-chapter-url'].forEach(function (a) {
			container.setAttribute(a, next.getAttribute(a) || '');
		});
		clearTimeout(saveTimer);
		readMarked = false;
		prefetched = '';
		document.title = doc.title;

		pages = Array.prototype.slice.call(viewer.querySelectorAll('.reader-page'));
		total = pages.length;
		byId('totalPagesDisplay').textContent = total;
		state.page = 0;
		scroller.scrollTop = 0;
		scroller.scrollLeft = 0;
		applyMode(null);
		measure();
		resume();
		countView(chapterId);
	}

	function navigate(url, push) {
		if (navigating) { return; }
		navigating = true;
		container.classList.add('is-loading');

		var cached = prefetchCache[url];
		delete prefetchCache[url];

		(cached ? Promise.resolve(cached) : fetch(url, { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); }))
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var next = doc.getElementById('mangaReaderContainer');
				if (!next) { throw new Error('Not a chapter page'); }
				swapChapter(doc, next);
				if (push !== false) { history.pushState({ reader: true }, '', url); }
			})
			.catch(function () { window.location.href = url; }) // anything unexpected: normal page load
			.then(function () {
				navigating = false;
				container.classList.remove('is-loading');
			});
	}

	window.addEventListener('popstate', function () { navigate(window.location.href, false); });

	window.addEventListener('resize', measure);

	measure();
	container.setAttribute('data-current-bg', state.bg);
	applyFit();
	applyZoom();
	syncButtons();
	applyMode(null);
	applyBar();
	brightEl.value = String(state.bright);
	applyBrightness();
	resume();
})();
