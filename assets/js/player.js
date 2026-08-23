(function () {
	var root = document.querySelector('.zc-class');
	if (!root || typeof window.ZC === 'undefined') {
		return;
	}
	var course = root.getAttribute('data-course');
	var lesson = root.getAttribute('data-lesson');
	var video = root.querySelector('[data-zc-player]');
	var iframe = root.querySelector('[data-zc-iframe]');
	var lastSent = 0;
	var started = Date.now();

	function post(seconds, duration, complete) {
		var now = Date.now();
		if (!complete && now - lastSent < 8000) {
			return;
		}
		lastSent = now;
		var body = new FormData();
		body.append('action', 'zc_save_watch');
		body.append('nonce', window.ZC.nonce);
		body.append('course_id', course);
		body.append('lesson_key', lesson);
		body.append('seconds', String(Math.floor(seconds || 0)));
		body.append('duration', String(Math.floor(duration || 0)));
		if (complete) {
			body.append('complete', '1');
		}
		fetch(window.ZC.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body });
	}

	if (video) {
		video.addEventListener('timeupdate', function () {
			var dur = video.duration || 0;
			var sec = video.currentTime || 0;
			var pct = dur ? (sec / dur) * 100 : 0;
			var need = parseInt(root.getAttribute('data-threshold') || '80', 10);
			post(sec, dur, pct >= need);
		});
		video.addEventListener('ended', function () {
			post(video.duration || video.currentTime || 0, video.duration || 0, true);
		});
	}

	if (iframe) {
		setInterval(function () {
			var watched = (Date.now() - started) / 1000;
			post(watched, 0, watched >= 30);
		}, 10000);
	}
})();
