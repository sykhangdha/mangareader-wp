(function () {
	'use strict';

	// WordPress also puts the class "single-manga" on <body>, so pick the box that carries the series data.
	var root = document.querySelector('.single-manga[data-manga-id]');
	if (!root) { return; }

	/* ---------- Chapter order ---------- */
	var grid = root.querySelector('.chapters-grid');
	var sortButtons = Array.prototype.slice.call(root.querySelectorAll('.chapter-sort-btn'));
	var order = 'desc'; // the server renders newest first

	sortButtons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			sortButtons.forEach(function (b) {
				b.classList.toggle('active', b === btn);
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});
			if (grid && btn.dataset.sort !== order) {
				Array.prototype.slice.call(grid.children).reverse().forEach(function (el) { grid.appendChild(el); });
				order = btn.dataset.sort;
			}
		});
	});

	/* ---------- Bookmark, continue reading and read marks (stored in this browser) ---------- */
	var L = window.MangaLocal;
	if (!L) { return; }

	var info = { id: root.dataset.mangaId, title: root.dataset.title, url: root.dataset.url, cover: root.dataset.cover };
	var data = L.get();

	Array.prototype.forEach.call(root.querySelectorAll('.chapter-card-link[data-chapter-id]'), function (a) {
		if (data.read[a.dataset.chapterId]) { a.classList.add('is-read'); }
	});

	var bookmark = document.getElementById('bookmarkBtn');
	if (bookmark) {
		var text = bookmark.querySelector('span');
		var paint = function (on) {
			bookmark.setAttribute('aria-pressed', on ? 'true' : 'false');
			text.textContent = on ? bookmark.dataset.on : bookmark.dataset.off;
		};
		paint(!!data.bookmarks[String(info.id)]);
		bookmark.addEventListener('click', function () { paint(L.toggleBookmark(info)); });
	}

	var cont = document.getElementById('continueBtn');
	var progress = data.progress[String(info.id)];
	if (cont && progress && L.safeUrl(progress.chapterUrl)) {
		cont.href = L.safeUrl(progress.chapterUrl);
		cont.textContent = cont.dataset.label + ': ' + (progress.label || '') + (progress.page > 0 ? ' · p.' + (parseInt(progress.page, 10) + 1) : '');
		cont.hidden = false;
		var first = document.getElementById('readFirstBtn');
		if (first) { first.classList.remove('btn-primary'); first.classList.add('btn-secondary'); }
	}
})();
