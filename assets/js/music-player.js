(function () {
	'use strict';

	var root = document.getElementById('lofiPlayer');
	if (!root) { return; }

	var tracks = [];
	try { tracks = JSON.parse(root.getAttribute('data-tracks')) || []; } catch (e) {}
	if (!tracks.length) { return; }

	function $(id) { return document.getElementById(id); }
	var toggle = $('lofiToggle'), panel = $('lofiPanel'), videoWrap = $('lofiVideo'), titleEl = $('lofiTitle');
	var prevBtn = $('lofiPrev'), playBtn = $('lofiPlay'), nextBtn = $('lofiNext');
	var volumeEl = $('lofiVolume'), select = $('lofiSelect'), statusEl = $('lofiStatus');

	var LABEL = {
		play: root.getAttribute('data-play') || 'Play',
		pause: root.getAttribute('data-pause') || 'Pause',
		error: root.getAttribute('data-error') || 'This track could not be played.'
	};

	function read(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
	function write(k, v) { try { localStorage.setItem(k, String(v)); } catch (e) {} }

	var audio = new Audio();
	audio.preload = 'none';

	var yt = null, ytReady = false, ytRequested = false, ytQueue = [], loadedId = '';
	var index = parseInt(read('lofiTrack'), 10);
	if (!(index >= 0 && index < tracks.length)) { index = 0; }
	var vol = parseInt(read('lofiVolume'), 10);
	if (!(vol >= 0 && vol <= 100)) { vol = 40; }
	var playing = false, failures = 0;

	/* ---------- UI ---------- */
	function setStatus(msg) { statusEl.textContent = msg || ''; }

	function setPlaying(on) {
		playing = on;
		root.classList.toggle('is-playing', on);
		playBtn.setAttribute('aria-label', on ? LABEL.pause : LABEL.play);
		write('lofiPlaying', on ? 1 : 0);
		if (on) { failures = 0; setStatus(''); }
	}

	function setOpen(open) {
		panel.classList.toggle('open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	/* ---------- YouTube (loaded only when a YouTube track is played) ---------- */
	function withYouTube(cb) {
		if (ytReady) { cb(); return; }
		ytQueue.push(cb);
		if (ytRequested) { return; }
		ytRequested = true;

		var tag = document.createElement('script');
		tag.src = 'https://www.youtube.com/iframe_api';
		tag.async = true;
		document.head.appendChild(tag);

		var previous = window.onYouTubeIframeAPIReady;
		window.onYouTubeIframeAPIReady = function () {
			if (typeof previous === 'function') { previous(); }
			yt = new window.YT.Player('lofiYt', {
				width: '100%',
				height: '100%',
				playerVars: { playsinline: 1, rel: 0 },
				events: {
					onReady: function () {
						ytReady = true;
						yt.setVolume(vol);
						var queued = ytQueue;
						ytQueue = [];
						queued.forEach(function (fn) { fn(); });
					},
					onStateChange: function (e) {
						var S = window.YT.PlayerState;
						if (e.data === S.PLAYING) { setPlaying(true); }
						else if (e.data === S.PAUSED) { setPlaying(false); }
						else if (e.data === S.ENDED) { go(1); }
					},
					onError: onTrackError
				}
			});
		};

		setTimeout(function () { if (!ytReady) { setStatus(LABEL.error); } }, 10000);
	}

	/* ---------- Playback ---------- */
	function onTrackError() {
		setPlaying(false);
		setStatus(LABEL.error);
		failures++;
		if (tracks.length > 1 && failures < tracks.length) {
			setTimeout(function () { go(1); }, 1500);
		}
	}

	function play() {
		var t = tracks[index];
		if (t.type === 'audio') {
			var p = audio.play();
			if (p && p.catch) { p.catch(function () { setPlaying(false); }); }
		} else {
			withYouTube(function () {
				var cur = tracks[index];
				if (cur.type !== 'youtube') { return; }
				if (loadedId === cur.src) { yt.playVideo(); } else { loadedId = cur.src; yt.loadVideoById(cur.src); }
			});
		}
	}

	function pause() {
		audio.pause();
		if (ytReady) { try { yt.pauseVideo(); } catch (e) {} }
	}

	function loadTrack(i, autoplay) {
		pause();
		setPlaying(false);
		index = (i + tracks.length) % tracks.length;
		write('lofiTrack', index);
		write('lofiTime', 0);

		var t = tracks[index];
		titleEl.textContent = t.title;
		select.value = String(index);
		videoWrap.hidden = t.type !== 'youtube';
		setStatus('');

		if (t.type === 'audio') {
			audio.src = t.src;
		} else {
			audio.removeAttribute('src');
			audio.load();
		}
		if (autoplay) { play(); }
	}

	function go(step) { loadTrack(index + step, true); }

	/* ---------- Events ---------- */
	audio.addEventListener('playing', function () { setPlaying(true); });
	audio.addEventListener('pause', function () { if (!audio.ended) { setPlaying(false); } });
	audio.addEventListener('ended', function () { go(1); });
	audio.addEventListener('error', function () { if (audio.getAttribute('src')) { onTrackError(); } });

	toggle.addEventListener('click', function () { setOpen(!panel.classList.contains('open')); });
	playBtn.addEventListener('click', function () { if (playing) { pause(); } else { play(); } });
	prevBtn.addEventListener('click', function () { go(-1); });
	nextBtn.addEventListener('click', function () { go(1); });
	select.addEventListener('change', function () { loadTrack(parseInt(select.value, 10), true); });

	volumeEl.addEventListener('input', function () {
		vol = parseInt(volumeEl.value, 10);
		audio.volume = vol / 100;
		if (ytReady) { yt.setVolume(vol); }
		write('lofiVolume', vol);
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && panel.classList.contains('open')) { setOpen(false); }
	});

	/* ---------- Init ---------- */
	// Read these first: setPlaying()/loadTrack() below overwrite the saved values.
	var wasPlaying = read('lofiPlaying') === '1';
	var savedTime = parseFloat(read('lofiTime')) || 0;

	tracks.forEach(function (t, i) {
		var opt = document.createElement('option');
		opt.value = String(i);
		opt.textContent = t.title;
		select.appendChild(opt);
	});
	volumeEl.value = String(vol);
	audio.volume = vol / 100;
	setPlaying(false);
	loadTrack(index, false);

	// Remember where an audio track was when the page is left (opening a normal page stops the sound).
	window.addEventListener('pagehide', function () {
		if (tracks[index].type === 'audio' && audio.currentTime > 0) { write('lofiTime', Math.floor(audio.currentTime)); }
	});

	// Pick up where it left off. Browsers may block this until you press play once.
	if (wasPlaying && tracks[index].type === 'audio') {
		if (savedTime > 0) {
			audio.addEventListener('loadedmetadata', function once() {
				audio.removeEventListener('loadedmetadata', once);
				try { audio.currentTime = savedTime; } catch (e) {}
			});
		}
		play();
	}
})();
