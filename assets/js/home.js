(function () {
	'use strict';

	var cfg = window.mangaTheme || {};

	/* ---------- Featured slider ---------- */
	var slider = document.getElementById('featuredSlider');
	if (slider) {
		var total = slider.children.length;
		var dots = document.getElementById('featuredDots');
		var section = slider.closest('.featured-section');
		var current = 0, timer = null;
		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		for (var i = 0; i < total; i++) {
			var dot = document.createElement('button');
			dot.type = 'button';
			dot.className = 'featured-dot' + (i === 0 ? ' active' : '');
			dot.setAttribute('aria-label', 'Slide ' + (i + 1));
			dot.dataset.slide = i;
			dots.appendChild(dot);
		}

		var go = function (n) {
			current = (n + total) % total;
			slider.style.transform = 'translateX(' + (-current * 100) + '%)';
			Array.prototype.forEach.call(dots.children, function (d, idx) { d.classList.toggle('active', idx === current); });
		};
		var stop = function () { clearInterval(timer); timer = null; };
		var start = function () {
			if (total > 1 && !reduceMotion && !timer) { timer = setInterval(function () { go(current + 1); }, 6000); }
		};

		document.getElementById('featuredNext').addEventListener('click', function () { go(current + 1); });
		document.getElementById('featuredPrev').addEventListener('click', function () { go(current - 1); });
		dots.addEventListener('click', function (e) {
			var d = e.target.closest('.featured-dot');
			if (d) { go(parseInt(d.dataset.slide, 10)); }
		});
		section.addEventListener('mouseenter', stop);
		section.addEventListener('mouseleave', start);
		section.addEventListener('focusin', stop);
		section.addEventListener('focusout', start);
		document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });

		if (total < 2) { document.querySelector('.featured-nav').style.display = 'none'; }
		start();
	}

	/* ---------- Sort buttons ---------- */
	var grid = document.getElementById('manga-container');
	var buttons = Array.prototype.slice.call(document.querySelectorAll('.sort-options .sort-btn'));
	var controller = null;

	buttons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			buttons.forEach(function (b) {
				b.classList.toggle('active', b === btn);
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});

			var body = new URLSearchParams({ action: 'manga_library', compact: '1', sort: btn.dataset.sort });
			if (controller) { controller.abort(); }
			controller = new AbortController();

			grid.setAttribute('aria-busy', 'true');
			grid.innerHTML = '<div class="loading-container"><div class="spinner"></div></div>';

			fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', signal: controller.signal })
				.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
				.then(function (html) { grid.innerHTML = html; grid.removeAttribute('aria-busy'); })
				.catch(function (err) {
					if (err.name === 'AbortError') { return; }
					grid.innerHTML = '<p class="no-results">' + (cfg.i18n ? cfg.i18n.error : 'Error') + '</p>';
					grid.removeAttribute('aria-busy');
				});
		});
	});
})();

/* ---------- Continue reading + bookmarks (stored in this browser) ---------- */
(function () {
	'use strict';

	var L = window.MangaLocal;
	var contSection = document.getElementById('railContinue');
	var markSection = document.getElementById('railBookmarks');
	if (!L || !contSection || !markSection) { return; }

	function item(href, cover, title, meta) {
		var a = document.createElement('a');
		a.className = 'rail-item';
		a.href = href;

		var thumb = document.createElement('span');
		thumb.className = 'rail-cover';
		var safeCover = L.safeUrl(cover);
		if (safeCover) {
			var img = document.createElement('img');
			img.src = safeCover;
			img.alt = '';
			img.loading = 'lazy';
			thumb.appendChild(img);
		}
		var t = document.createElement('span');
		t.className = 'rail-title';
		t.textContent = title || '';
		var m = document.createElement('span');
		m.className = 'rail-meta';
		m.textContent = meta || '';

		a.appendChild(thumb);
		a.appendChild(t);
		a.appendChild(m);
		return a;
	}

	function fill(section, rowId, entries) {
		var row = document.getElementById(rowId);
		row.textContent = '';
		entries.forEach(function (el) { row.appendChild(el); });
		section.hidden = entries.length === 0;
	}

	function render() {
		var data = L.get();

		var progress = Object.keys(data.progress).map(function (k) { return data.progress[k]; })
			.filter(function (p) { return L.safeUrl(p.chapterUrl); })
			.sort(function (a, b) { return (b.ts || 0) - (a.ts || 0); })
			.slice(0, 10)
			.map(function (p) {
				return item(L.safeUrl(p.chapterUrl), p.cover, p.title, (p.label || '') + (p.page > 0 ? ' · p.' + (parseInt(p.page, 10) + 1) : ''));
			});
		fill(contSection, 'railContinueRow', progress);

		var marks = Object.keys(data.bookmarks).map(function (k) { return data.bookmarks[k]; })
			.filter(function (b) { return L.safeUrl(b.url); })
			.sort(function (a, b) { return (b.ts || 0) - (a.ts || 0); })
			.slice(0, 12)
			.map(function (b) { return item(L.safeUrl(b.url), b.cover, b.title, ''); });
		fill(markSection, 'railBookmarksRow', marks);
	}

	document.getElementById('railClear').addEventListener('click', function () {
		L.clearProgress();
		render();
	});
	render();
})();
