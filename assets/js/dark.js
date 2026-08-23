(function () {
	function apply(theme) {
		document.documentElement.classList.toggle('zc-dark', theme === 'dark');
		try {
			localStorage.setItem('zc-theme', theme);
		} catch (e) {}
	}
	function current() {
		try {
			var stored = localStorage.getItem('zc-theme');
			if (stored) {
				return stored;
			}
		} catch (e) {}
		return document.documentElement.classList.contains('zc-dark') ? 'dark' : 'light';
	}
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-zc-theme]');
		if (!btn) {
			return;
		}
		apply(current() === 'dark' ? 'light' : 'dark');
	});
	var bar = document.querySelector('.zc-panel__topbar-actions');
	var toggle = document.querySelector('[data-zc-theme]');
	if (bar && toggle && toggle.parentNode !== bar) {
		bar.insertBefore(toggle, bar.firstChild);
	}
})();
