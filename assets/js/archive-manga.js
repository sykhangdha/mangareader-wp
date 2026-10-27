(function () {
	'use strict';

	var form = document.getElementById('libraryFilters');
	var results = document.getElementById('manga-container');
	if (!form || !results) { return; }

	var cfg = window.mangaTheme || {};
	var base = results.getAttribute('data-base') || window.location.pathname;
	var panel = document.getElementById('filterPanel');
	var toggle = document.getElementById('filterToggle');
	var countEl = document.getElementById('filterCount');
	var qInput = form.elements.q;
	var sortSel = form.elements.sort;
	var modeSel = form.elements.gm;
	var seq = 0, controller = null, timer = null;

	function chips(kind) {
		return Array.prototype.slice.call(form.querySelectorAll('.chip[data-kind="' + kind + '"]'));
	}

	function slugs(kind, state) {
		return chips(kind).filter(function (c) { return c.dataset.state === state; }).map(function (c) { return c.dataset.slug; });
	}

	function setState(chip, state) {
		chip.dataset.state = state;
		chip.setAttribute('aria-pressed', state === 'include' ? 'true' : (state === 'exclude' ? 'mixed' : 'false'));
	}

	function collect() {
		var letter = slugs('letter', 'include')[0] || '';
		return {
			q: qInput.value.trim(),
			sort: sortSel.value,
			gm: modeSel ? modeSel.value : 'and',
			g: slugs('genre', 'include'),
			x: slugs('genre', 'exclude'),
			st: slugs('status', 'include'),
			letter: letter
		};
	}

	function toParams(f, page) {
		var p = new URLSearchParams();
		if (f.q) { p.set('q', f.q); }
		if (f.letter) { p.set('letter', f.letter); }
		if (f.g.length) { p.set('g', f.g.join(',')); }
		if (f.x.length) { p.set('x', f.x.join(',')); }
		if (f.st.length) { p.set('st', f.st.join(',')); }
		if (f.gm === 'or') { p.set('gm', 'or'); }
		if (f.sort && f.sort !== 'az') { p.set('sort', f.sort); }
		if (page > 1) { p.set('pg', page); }
		return p;
	}

	function updateCount(f) {
		var n = f.g.length + f.x.length + f.st.length + (f.letter ? 1 : 0);
		if (countEl) {
			countEl.textContent = n;
			countEl.hidden = n === 0;
		}
	}

	function headerOffset() {
		var h = document.getElementById('siteHeader');
		return (h ? h.offsetHeight : 0) + 16;
	}

	function load(page, forceScroll) {
		var f = collect();
		var p = toParams(f, page || 1);
		var body = new URLSearchParams(p);
		body.set('action', 'manga_library');
		body.set('base', base);

		updateCount(f);
		var qs = p.toString();
		history.replaceState(null, '', qs ? base + (base.indexOf('?') < 0 ? '?' : '&') + qs : base);

		if (controller) { controller.abort(); }
		controller = new AbortController();
		var id = ++seq;

		results.classList.add('is-loading');
		results.setAttribute('aria-busy', 'true');

		fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', signal: controller.signal })
			.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
			.then(function (html) {
				if (id !== seq) { return; }
				results.innerHTML = html;
				results.classList.remove('is-loading');
				results.removeAttribute('aria-busy');
				var top = results.getBoundingClientRect().top + window.pageYOffset - headerOffset();
				if (forceScroll || window.pageYOffset > top) { window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' }); }
			})
			.catch(function (err) {
				if (err.name === 'AbortError') { return; }
				results.classList.remove('is-loading');
				results.removeAttribute('aria-busy');
				results.innerHTML = '<p class="no-results">' + (cfg.i18n ? cfg.i18n.error : 'Error') + '</p>';
			});
	}

	function reset() {
		chips('genre').concat(chips('status')).forEach(function (c) { setState(c, ''); });
		chips('letter').forEach(function (c) { setState(c, c.dataset.slug === '' ? 'include' : ''); });
		qInput.value = '';
		if (modeSel) { modeSel.value = 'and'; }
		load(1);
	}

	/* Chips: genres cycle include -> exclude -> off, status toggles, letter is exclusive. */
	form.addEventListener('click', function (e) {
		var chip = e.target.closest('.chip');
		if (chip) {
			var kind = chip.dataset.kind;
			if (kind === 'genre') {
				setState(chip, chip.dataset.state === '' ? 'include' : (chip.dataset.state === 'include' ? 'exclude' : ''));
			} else if (kind === 'status') {
				setState(chip, chip.dataset.state === 'include' ? '' : 'include');
			} else {
				chips('letter').forEach(function (c) { setState(c, c === chip ? 'include' : ''); });
			}
			load(1);
			return;
		}
		if (e.target.closest('[data-action="reset"]')) { reset(); }
	});

	toggle.addEventListener('click', function () {
		var open = panel.hidden;
		panel.hidden = !open;
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
	});

	sortSel.addEventListener('change', function () { load(1); });
	if (modeSel) { modeSel.addEventListener('change', function () { load(1); }); }

	qInput.addEventListener('input', function () {
		clearTimeout(timer);
		timer = setTimeout(function () { load(1); }, 350);
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		clearTimeout(timer);
		load(1);
	});

	/* Pagination and the "reset" button inside results. */
	results.addEventListener('click', function (e) {
		var link = e.target.closest('.pagination a[data-page]');
		if (link) {
			e.preventDefault();
			load(parseInt(link.dataset.page, 10), true);
			return;
		}
		if (e.target.closest('[data-action="reset"]')) { reset(); }
	});
})();
