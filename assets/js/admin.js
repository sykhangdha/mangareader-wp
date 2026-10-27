(function ($) {
	'use strict';

	/* Chapter images box */
	var ta = document.getElementById('chapter-image-links');
	if (ta) {
		var preview = document.getElementById('chapter-image-preview');

		var lines = function () {
			return ta.value.split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean);
		};

		var render = function () {
			preview.textContent = '';
			lines().forEach(function (url, i) {
				var item = document.createElement('div');
				item.className = 'manga-thumb';

				var img = document.createElement('img');
				img.src = url;
				img.alt = '';
				img.loading = 'lazy';

				var num = document.createElement('span');
				num.textContent = String(i + 1);

				var del = document.createElement('button');
				del.type = 'button';
				del.textContent = '×';
				del.setAttribute('aria-label', 'Remove page ' + (i + 1));
				del.addEventListener('click', function () {
					var all = lines();
					all.splice(i, 1);
					ta.value = all.join('\n');
					render();
				});

				item.appendChild(img);
				item.appendChild(num);
				item.appendChild(del);
				preview.appendChild(item);
			});
		};

		var timer;
		ta.addEventListener('input', function () {
			clearTimeout(timer);
			timer = setTimeout(render, 400);
		});

		$('#chapter-sort-urls').on('click', function () {
			var all = lines().sort(function (a, b) {
				return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
			});
			ta.value = all.join('\n');
			render();
		});

		var frame;
		$('#chapter-add-media').on('click', function () {
			if (!frame) {
				frame = wp.media({
					title: 'Select chapter pages',
					button: { text: 'Add pages' },
					library: { type: 'image' },
					multiple: true
				});
				frame.on('select', function () {
					var urls = frame.state().get('selection').map(function (a) { return a.get('url'); });
					ta.value = lines().concat(urls).join('\n');
					render();
				});
			}
			frame.open();
		});

		render();
	}

	/* Importer: select all */
	$('#manga-import-all').on('change', function () {
		$('.manga-import-check').prop('checked', this.checked);
	});
})(jQuery);
