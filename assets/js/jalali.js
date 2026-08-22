/**
 * تاریخ‌گزین شمسی زرین کد
 *
 * روی هر فیلد تاریخ در سایت، پیشخوان، ووکامرس و افزونه‌ها می‌نشیند.
 * وابستگی خارجی ندارد و با jQuery UI هم تداخل نمی‌کند چون فیلد اصلی
 * را به نوع text تبدیل و تقویم بومی مرورگر را غیرفعال می‌کند.
 *
 * راهبرد ذخیره‌سازی: کاربر تاریخ شمسی می‌بیند، اما یک فیلد پنهان
 * هم‌نام با فیلد اصلی مقدار میلادی (Y-m-d) را نگه می‌دارد تا سمت
 * سرور هیچ تغییری لازم نباشد.
 *
 * @package Zarincode
 */

(function () {
	'use strict';

	var CFG = window.ZC_JALALI || {};

	var MONTHS = CFG.months || ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
		'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

	var DAYS = CFG.days || ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

	/* ======================================================================
	   تبدیل تقویم
	   ====================================================================== */

	/**
	 * میلادی به شمسی.
	 *
	 * @param {number} gy سال.
	 * @param {number} gm ماه.
	 * @param {number} gd روز.
	 * @return {Array} [jy, jm, jd]
	 */
	function g2j(gy, gm, gd) {
		var gDM = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
		var jy = (gy <= 1600) ? 0 : 979;

		gy -= (gy <= 1600) ? 621 : 1600;

		var gy2 = (gm > 2) ? (gy + 1) : gy;

		var days = (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
			+ Math.floor((gy2 + 399) / 400) - 80 + gd + gDM[gm - 1];

		jy += 33 * Math.floor(days / 12053);
		days %= 12053;

		jy += 4 * Math.floor(days / 1461);
		days %= 1461;

		if (days > 365) {
			jy += Math.floor((days - 1) / 365);
			days = (days - 1) % 365;
		}

		var jm = (days < 186) ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
		var jd = 1 + ((days < 186) ? (days % 31) : ((days - 186) % 30));

		return [jy, jm, jd];
	}

	/**
	 * شمسی به میلادی.
	 *
	 * @param {number} jy سال.
	 * @param {number} jm ماه.
	 * @param {number} jd روز.
	 * @return {Array} [gy, gm, gd]
	 */
	function j2g(jy, jm, jd) {
		jy += 1595;

		var days = -355668 + (365 * jy) + (Math.floor(jy / 33) * 8)
			+ Math.floor(((jy % 33) + 3) / 4) + jd
			+ ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);

		var gy = 400 * Math.floor(days / 146097);
		days %= 146097;

		if (days > 36524) {
			gy += 100 * Math.floor(--days / 36524);
			days %= 36524;

			if (days >= 365) { days++; }
		}

		gy += 4 * Math.floor(days / 1461);
		days %= 1461;

		if (days > 365) {
			gy += Math.floor((days - 1) / 365);
			days = (days - 1) % 365;
		}

		var gd = days + 1;
		var leap = ((gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0) ? 29 : 28;
		var salA = [0, 31, leap, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var gm = 0;

		for (gm = 0; gm < 13 && gd > salA[gm]; gm++) { gd -= salA[gm]; }

		return [gy, gm, gd];
	}

	/**
	 * تعداد روزهای یک ماه شمسی.
	 *
	 * @param {number} jy سال.
	 * @param {number} jm ماه.
	 * @return {number}
	 */
	function monthDays(jy, jm) {
		if (jm <= 6) { return 31; }
		if (jm <= 11) { return 30; }

		// اسفند: بررسی کبیسه با تبدیل رفت‌وبرگشتی روز ۳۰.
		var g = j2g(jy, 12, 30);
		var back = g2j(g[0], g[1], g[2]);

		return (back[1] === 12 && back[2] === 30) ? 30 : 29;
	}

	/** تبدیل رقم به فارسی. */
	function fa(n) {
		return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
	}

	/** تبدیل رقم فارسی به لاتین. */
	function en(s) {
		return String(s).replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
			.replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
	}

	function pad(n) { return (n < 10 ? '0' : '') + n; }

	/* ======================================================================
	   تاریخ‌گزین
	   ====================================================================== */

	var openPicker = null;

	/**
	 * ساخت تاریخ‌گزین برای یک فیلد.
	 *
	 * @param {HTMLElement} input فیلد ورودی.
	 */
	function attach(input) {
		if (input.dataset.zcJalaliReady) { return; }
		input.dataset.zcJalaliReady = '1';

		/*
		 * فیلد type=date تقویم میلادی مرورگر را باز می‌کند و قابل
		 * جایگزینی نیست؛ پس آن را به text تبدیل می‌کنیم و مقدار
		 * میلادی را در یک فیلد پنهان هم‌نام نگه می‌داریم.
		 */
		var isNative = input.type === 'date';
		var hidden = null;
		var withTime = input.dataset.zcTime === '1';

		if (isNative) {
			hidden = document.createElement('input');
			hidden.type = 'hidden';
			hidden.name = input.name;
			hidden.value = input.value;
			input.removeAttribute('name');
			input.type = 'text';
			input.setAttribute('autocomplete', 'off');
			input.parentNode.insertBefore(hidden, input.nextSibling);

			// نمایش مقدار اولیه به شمسی.
			if (hidden.value) {
				var p = hidden.value.split('-');
				if (p.length === 3) {
					var jj = g2j(+p[0], +p[1], +p[2]);
					input.value = fa(jj[0] + '/' + pad(jj[1]) + '/' + pad(jj[2]));
				}
			}
		}

		input.classList.add('zc-jdate');
		input.setAttribute('inputmode', 'numeric');

		if (!input.placeholder) { input.placeholder = '۱۴۰۴/۰۱/۰۱'; }

		var state = { y: 0, m: 0, d: 0 };

		/** خواندن مقدار فعلی فیلد یا امروز. */
		function readState() {
			var v = en(input.value).replace(/[.\-]/g, '/');
			var m = v.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})/);

			if (m) {
				state = { y: +m[1], m: +m[2], d: +m[3] };
				return;
			}

			var now = new Date();
			var j = g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
			state = { y: j[0], m: j[1], d: 0 };
		}

		var pop = document.createElement('div');
		pop.className = 'zc-jdp';
		pop.setAttribute('dir', 'rtl');
		pop.hidden = true;
		document.body.appendChild(pop);

		/** نوشتن مقدار انتخاب‌شده در فیلدها. */
		function commit(y, m, d) {
			input.value = fa(y + '/' + pad(m) + '/' + pad(d));

			if (hidden) {
				var g = j2g(y, m, d);
				hidden.value = g[0] + '-' + pad(g[1]) + '-' + pad(g[2]);

				// رویداد تغییر برای اسکریپت‌های دیگر (ووکامرس و ...).
				hidden.dispatchEvent(new Event('change', { bubbles: true }));
			}

			input.dispatchEvent(new Event('change', { bubbles: true }));
		}

		/** رسم تقویم. */
		function render() {
			var first = j2g(state.y, state.m, 1);
			var jsDate = new Date(first[0], first[1] - 1, first[2]);

			// شنبه = ۰ در تقویم ایرانی.
			var offset = (jsDate.getDay() + 1) % 7;
			var total = monthDays(state.y, state.m);

			var now = new Date();
			var tj = g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());

			var html = '<div class="zc-jdp__head">'
				+ '<button type="button" class="zc-jdp__nav" data-nav="-1" aria-label="ماه قبل">›</button>'
				+ '<div class="zc-jdp__title">'
				+ '<select class="zc-jdp__m" aria-label="ماه">';

			MONTHS.forEach(function (name, i) {
				html += '<option value="' + (i + 1) + '"' + ((i + 1) === state.m ? ' selected' : '') + '>' + name + '</option>';
			});

			html += '</select><select class="zc-jdp__y" aria-label="سال">';

			for (var y = state.y - 80; y <= state.y + 20; y++) {
				html += '<option value="' + y + '"' + (y === state.y ? ' selected' : '') + '>' + fa(y) + '</option>';
			}

			html += '</select></div>'
				+ '<button type="button" class="zc-jdp__nav" data-nav="1" aria-label="ماه بعد">‹</button>'
				+ '</div><div class="zc-jdp__grid">';

			DAYS.forEach(function (d) { html += '<span class="zc-jdp__dow">' + d + '</span>'; });

			for (var i = 0; i < offset; i++) { html += '<span></span>'; }

			for (var day = 1; day <= total; day++) {
				var cls = 'zc-jdp__day';

				if (day === state.d) { cls += ' is-sel'; }
				if (state.y === tj[0] && state.m === tj[1] && day === tj[2]) { cls += ' is-today'; }

				html += '<button type="button" class="' + cls + '" data-d="' + day + '">' + fa(day) + '</button>';
			}

			html += '</div><div class="zc-jdp__foot">'
				+ '<button type="button" class="zc-jdp__btn" data-today>' + (CFG.todayTxt || 'امروز') + '</button>'
				+ '<button type="button" class="zc-jdp__btn zc-jdp__btn--clear" data-clear>' + (CFG.clear || 'پاک کردن') + '</button>'
				+ '</div>';

			pop.innerHTML = html;
		}

		/** جای‌گذاری کشویی زیر فیلد با در نظر گرفتن لبه‌ی صفحه. */
		function place() {
			var r = input.getBoundingClientRect();
			var top = r.bottom + window.scrollY + 6;

			// اگر پایین صفحه جا نبود، بالای فیلد باز شود.
			if (r.bottom + 330 > window.innerHeight && r.top > 330) {
				top = r.top + window.scrollY - pop.offsetHeight - 6;
			}

			pop.style.top = top + 'px';

			var left = r.left + window.scrollX;
			left = Math.max(8, Math.min(left, window.innerWidth - pop.offsetWidth - 8));
			pop.style.left = left + 'px';
		}

		function open() {
			if (openPicker && openPicker !== close) { openPicker(); }

			readState();
			render();
			pop.hidden = false;
			place();
			openPicker = close;
		}

		function close() {
			pop.hidden = true;
			openPicker = null;
		}

		input.addEventListener('focus', open);
		input.addEventListener('click', open);

		pop.addEventListener('mousedown', function (e) { e.preventDefault(); });

		pop.addEventListener('click', function (e) {
			var day = e.target.closest('[data-d]');

			if (day) {
				state.d = +day.dataset.d;
				commit(state.y, state.m, state.d);
				close();
				return;
			}

			var nav = e.target.closest('[data-nav]');

			if (nav) {
				state.m += +nav.dataset.nav;

				if (state.m < 1) { state.m = 12; state.y--; }
				if (state.m > 12) { state.m = 1; state.y++; }

				render();
				return;
			}

			if (e.target.closest('[data-today]')) {
				var now = new Date();
				var j = g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
				commit(j[0], j[1], j[2]);
				close();
				return;
			}

			if (e.target.closest('[data-clear]')) {
				input.value = '';
				if (hidden) { hidden.value = ''; }
				close();
			}
		});

		pop.addEventListener('change', function (e) {
			if (e.target.classList.contains('zc-jdp__m')) { state.m = +e.target.value; render(); }
			if (e.target.classList.contains('zc-jdp__y')) { state.y = +e.target.value; render(); }
		});

		// تایپ دستی: مقدار پنهان را همگام کن.
		input.addEventListener('blur', function () {
			var v = en(input.value).replace(/[.\-]/g, '/');
			var m = v.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);

			if (m && hidden) {
				var g = j2g(+m[1], +m[2], +m[3]);
				hidden.value = g[0] + '-' + pad(g[1]) + '-' + pad(g[2]);
			} else if (!input.value && hidden) {
				hidden.value = '';
			}
		});

		document.addEventListener('click', function (e) {
			if (!pop.hidden && !pop.contains(e.target) && e.target !== input) { close(); }
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !pop.hidden) { close(); }
		});
	}

	/* ======================================================================
	   یافتن فیلدهای تاریخ
	   ====================================================================== */

	/**
	 * انتخابگرهای پوشش‌داده‌شده.
	 *
	 * شامل فیلدهای بومی، ووکامرس (فیلترها، کوپن، گزارش‌ها)، فیلدهای
	 * قالب و هر فیلدی که کلاس zc-date یا صفت data-zc-date داشته باشد.
	 */
	var SELECTORS = [
		'input[type="date"]',
		'input.zc-date',
		'input[data-zc-date]',
		'.zc-field input[name*="date"]',
		'input#coupon_expiry_date',
		'input.date-picker',
		'input.date-picker-field',
		'input[name="_sale_price_dates_from"]',
		'input[name="_sale_price_dates_to"]'
	].join(',');

	/** اسکن و اتصال. */
	function scan(root) {
		(root || document).querySelectorAll(SELECTORS).forEach(function (el) {
			// فیلدهای مخفی یا فقط‌خواندنی نیاز به تقویم ندارند.
			if (el.type === 'hidden' || el.readOnly) { return; }

			attach(el);
		});
	}

	function boot() {
		scan(document);

		// محتوای آجاکسی (پنل کاربری، متاباکس‌ها، ووکامرس).
		var mo = new MutationObserver(function (muts) {
			muts.forEach(function (mut) {
				mut.addedNodes.forEach(function (n) {
					if (n.nodeType === 1) { scan(n); }
				});
			});
		});

		mo.observe(document.body, { childList: true, subtree: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	// در دسترس برای سایر بخش‌های قالب.
	window.ZCJalali = { g2j: g2j, j2g: j2g, fa: fa, en: en, attach: attach };
}());
