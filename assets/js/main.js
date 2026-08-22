/**
 * Zarincode Theme — Core JavaScript (Vanilla, no jQuery dependency)
 * موتور اصلی تعاملات قالب زرین کد
 * @version 1.0.0
 */
(function () {
	'use strict';

	var CFG = window.ZC || {};
	var S = CFG.settings || {};
	var I18N = CFG.i18n || {};

	/* ---------- Utilities ---------- */
	var $  = function (sel, ctx) { return (ctx || document).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

	function on(el, ev, fn, opt) { if (el) el.addEventListener(ev, fn, opt || false); }

	/**
	 * ثبت شنونده‌ی رویداد فقط یک بار روی هر عنصر.
	 *
	 * تابع init() این قالب ممکن است چندین بار اجرا شود (به ازای هر ویجت
	 * المنتور، پس از بارگذاری آجاکسی و در پیش‌نمایش ویرایشگر). بدون این
	 * محافظ، شنونده‌ها روی هم انباشته می‌شوند و رفتارهای «تاگل» عملاً
	 * خنثی می‌شوند (مثلاً آکاردئون باز و بلافاصله بسته می‌شود).
	 *
	 * @param {Element}  el  عنصر هدف.
	 * @param {string}   ev  نام رویداد.
	 * @param {Function} fn  تابع اجرا.
	 * @param {string}   key کلید یکتا برای این اتصال.
	 * @param {Object}   opt گزینه‌های addEventListener.
	 * @return {boolean} اگر قبلاً ثبت شده باشد false برمی‌گرداند.
	 */
	function onOnce(el, ev, fn, key, opt) {
		if (!el) return false;

		var flag = 'zcOn' + (key || ev);

		if (el.dataset && el.dataset[flag]) return false;

		if (el.dataset) el.dataset[flag] = '1';

		el.addEventListener(ev, fn, opt || false);

		return true;
	}

	/**
	 * بررسی اینکه آیا یک عنصر قبلاً مقداردهی اولیه شده است یا نه.
	 *
	 * @param {Element} el  عنصر.
	 * @param {string}  key شناسه‌ی بخش.
	 * @return {boolean} true اگر برای اولین بار است.
	 */
	function claimInit(el, key) {
		if (!el || !el.dataset) return true;

		var flag = 'zcInit' + key;

		if (el.dataset[flag]) return false;

		el.dataset[flag] = '1';

		return true;
	}

	function debounce(fn, wait) {
		var t;
		return function () {
			var a = arguments, c = this;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(c, a); }, wait);
		};
	}

	function throttle(fn, limit) {
		var busy = false;
		return function () {
			if (busy) return;
			busy = true;
			fn.apply(this, arguments);
			setTimeout(function () { busy = false; }, limit);
		};
	}

	/**
	 * درخواست ای‌جکس ساده مبتنی بر fetch
	 */
	function ajax(action, data, method) {
		var body = new FormData();
		body.append('action', action);
		body.append('nonce', CFG.nonce);
		Object.keys(data || {}).forEach(function (k) {
			var v = data[k];
			if (v instanceof File || v instanceof Blob) { body.append(k, v); }
			else if (typeof v === 'object' && v !== null) { body.append(k, JSON.stringify(v)); }
			else { body.append(k, v); }
		});
		return fetch(CFG.ajaxUrl, {
			method: method || 'POST',
			body: body,
			credentials: 'same-origin'
		}).then(function (r) { return r.json(); });
	}
	window.zcAjax = ajax;

	/* ---------- Toast ---------- */
	var Toast = {
		box: null,
		init: function () {
			this.box = document.createElement('div');
			this.box.className = 'zc-toasts';
			document.body.appendChild(this.box);
		},
		show: function (msg, type, time) {
			if (!this.box) this.init();
			var el = document.createElement('div');
			el.className = 'zc-toast zc-toast--' + (type || 'info');
			el.setAttribute('role', 'status');
			el.textContent = msg;
			this.box.appendChild(el);
			setTimeout(function () {
				el.classList.add('is-out');
				setTimeout(function () { el.remove(); }, 320);
			}, time || 3600);
		}
	};
	window.zcToast = function (m, t, d) { Toast.show(m, t, d); };

	/* ---------- Preloader ---------- */
	function initPreloader() {
		var el = $('.zc-preloader');
		if (!el) return;
		var done = function () {
			el.classList.add('is-done');
			setTimeout(function () { el.remove(); }, 550);
		};
		if (document.readyState === 'complete') { setTimeout(done, 220); }
		else { on(window, 'load', function () { setTimeout(done, 220); }); }
		// ایمنی: حداکثر ۴ ثانیه
		setTimeout(done, 4000);
	}

	/* ---------- Sticky Header ---------- */
	function initStickyHeader() {
		if (!document.body.classList.contains('zc-sticky-header')) return;
		var header = $('.zc-header');
		if (!header) return;
		var spacer = $('.zc-header-spacer');
		var offset = header.offsetTop + 60;

		var check = throttle(function () {
			if (window.pageYOffset > offset) {
				if (!header.classList.contains('is-stuck')) {
					header.classList.add('is-stuck');
					if (spacer) spacer.classList.add('is-active');
				}
			} else if (header.classList.contains('is-stuck')) {
				header.classList.remove('is-stuck');
				if (spacer) spacer.classList.remove('is-active');
			}
		}, 100);

		on(window, 'scroll', check, { passive: true });
		check();
	}

	/* ---------- Back To Top ---------- */
	function initBackToTop() {
		var btn = $('.zc-to-top');
		if (!btn) return;
		var toggle = throttle(function () {
			btn.classList.toggle('is-visible', window.pageYOffset > 480);
		}, 180);
		on(window, 'scroll', toggle, { passive: true });
		on(btn, 'click', function (e) {
			e.preventDefault();
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
		toggle();
	}

	/* ---------- Mobile Nav ---------- */
	function initMobileNav() {
		// این بخش روی عناصر یکتای صفحه کار می‌کند و باید تنها یک بار اجرا شود.
		if (!claimInit(document.body, 'MobileNav')) return;

		var nav = $('.zc-mobile-nav');
		var overlay = $('.zc-overlay');
		if (!nav) return;

		function open() {
			nav.classList.add('is-open');
			if (overlay) overlay.classList.add('is-open');
			document.body.style.overflow = 'hidden';
			nav.setAttribute('aria-hidden', 'false');
		}
		function close() {
			nav.classList.remove('is-open');
			if (overlay) overlay.classList.remove('is-open');
			document.body.style.overflow = '';
			nav.setAttribute('aria-hidden', 'true');
		}

		$$('.zc-burger, [data-zc-open="mobile-nav"]').forEach(function (b) { on(b, 'click', open); });
		$$('[data-zc-close="mobile-nav"], .zc-mobile-nav__close').forEach(function (b) { on(b, 'click', close); });
		on(overlay, 'click', close);
		on(document, 'keydown', function (e) { if (e.key === 'Escape') close(); });

		// آکاردئون زیرمنوها
		$$('.zc-mobile-nav .menu-item-has-children > a').forEach(function (a) {
			var toggle = document.createElement('span');
			toggle.className = 'zc-mobile-nav__toggle';
			toggle.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 9 6 6 6-6"/></svg>';
			a.appendChild(toggle);
			on(toggle, 'click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				a.parentNode.classList.toggle('is-open');
			});
		});
	}

	/* ---------- AJAX Search ---------- */
	function initSearch() {
		// این بخش روی عناصر یکتای صفحه کار می‌کند و باید تنها یک بار اجرا شود.
		if (!claimInit(document.body, 'Search')) return;

		var modal = $('.zc-search');
		if (!modal) return;
		var input = $('.zc-search__input', modal);
		var results = $('.zc-search__results', modal);
		var filters = $$('.zc-search__filter', modal);
		var type = 'all';
		var lastQuery = '';
		var controller = null;

		function open() {
			modal.classList.add('is-open');
			document.body.style.overflow = 'hidden';
			setTimeout(function () { if (input) input.focus(); }, 260);
		}
		function close() {
			modal.classList.remove('is-open');
			document.body.style.overflow = '';
		}

		$$('[data-zc-open="search"]').forEach(function (b) {
			on(b, 'click', function (e) { e.preventDefault(); open(); });
		});
		$$('[data-zc-close="search"]').forEach(function (b) { on(b, 'click', close); });
		on(modal, 'click', function (e) { if (e.target === modal) close(); });
		on(document, 'keydown', function (e) {
			if (e.key === 'Escape') close();
			// Ctrl+K / Cmd+K
			if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); open(); }
		});

		filters.forEach(function (f) {
			on(f, 'click', function () {
				filters.forEach(function (x) { x.classList.remove('is-active'); });
				f.classList.add('is-active');
				type = f.dataset.type || 'all';
				if (input && input.value.trim().length > 1) { doSearch(input.value.trim(), true); }
			});
		});

		function render(html) { if (results) results.innerHTML = html; }

		function doSearch(q, force) {
			if (!force && q === lastQuery) return;
			lastQuery = q;
			if (q.length < 2) {
				render('<div class="zc-search__state">' + (I18N.searchHint || 'حداقل ۲ حرف وارد کنید…') + '</div>');
				return;
			}
			render('<div class="zc-search__state"><div class="zc-spinner"></div>' + (I18N.loading || '') + '</div>');

			if (controller) controller.abort();
			controller = new AbortController();

			var body = new FormData();
			body.append('action', 'zc_ajax_search');
			body.append('nonce', CFG.nonce);
			body.append('q', q);
			body.append('type', type);

			fetch(CFG.ajaxUrl, { method: 'POST', body: body, signal: controller.signal, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.success && res.data.html) { render(res.data.html); }
					else { render('<div class="zc-search__state">' + (I18N.noResults || '') + '</div>'); }
				})
				.catch(function (err) {
					if (err.name !== 'AbortError') { render('<div class="zc-search__state">' + (I18N.error || '') + '</div>'); }
				});
		}

		if (input) {
			on(input, 'input', debounce(function () { doSearch(input.value.trim()); }, S.searchDelay || 350));
		}
	}

	/* ---------- Tabs ---------- */
	function initTabs() {
		$$('.zc-tabs').forEach(function (tabs) {
			if (!claimInit(tabs, 'Tabs')) return;

			var btns = $$('.zc-tabs__btn', tabs);
			var panes = $$('.zc-tabs__pane', tabs);
			btns.forEach(function (btn) {
				on(btn, 'click', function () {
					var target = btn.dataset.tab;
					btns.forEach(function (b) { b.classList.remove('is-active'); b.setAttribute('aria-selected', 'false'); });
					panes.forEach(function (p) { p.classList.remove('is-active'); });
					btn.classList.add('is-active');
					btn.setAttribute('aria-selected', 'true');
					var pane = $('.zc-tabs__pane[data-pane="' + target + '"]', tabs);
					if (pane) pane.classList.add('is-active');
					if (btn.dataset.hash) {
						history.replaceState(null, '', '#' + btn.dataset.hash);
					}
				});
			});
			// باز کردن تب از روی هش آدرس
			if (location.hash) {
				var h = location.hash.substring(1);
				var match = btns.filter(function (b) { return b.dataset.tab === h || b.dataset.hash === h; })[0];
				if (match) match.click();
			}
		});
	}

	/* ---------- Accordion ---------- */
	/**
	 * آکاردئون با «واگذاری رویداد» (event delegation).
	 *
	 * توجه مهم: تابع init() ممکن است بارها اجرا شود (مثلاً به ازای هر
	 * ویجت المنتور یا در پیش‌نمایش ویرایشگر). اگر شنونده‌ی کلیک مستقیماً
	 * روی هر دکمه بسته شود، در هر اجرا یک شنونده‌ی تازه اضافه می‌شود و
	 * چند بار پشت سر هم اجرا شدن آن‌ها، باز و بسته شدن را خنثی می‌کند.
	 * به همین دلیل تنها یک شنونده روی document ثبت می‌شود.
	 */
	var accordionBound = false;

	function setAccordionHeight(item, open) {
		var body = $('.zc-accordion__body', item);
		if (!body) return;

		/*
		 * مشکلات max-height ثابت در موبایل:
		 *  ۱) اگر محتوای پاسخ شامل عکس/فونت باشد که پس از باز شدن لود
		 *     می‌شود، ارتفاع واقعی بیشتر از scrollHeight اولیه می‌شود و
		 *     محتوای پایین بریده می‌ماند.
		 *  ۲) هنگام بستن، اگر مستقیماً به ۰ برویم، به‌خاطر اینکه مقدار
		 *     قبلی عددی نیست، transition پایین اجرا نمی‌شود.
		 *
		 * راه‌حل مطمئن: با یک حلقه‌ی بازچینش (reflow) ارتفاع واقعی
		 * محتوا را می‌گیریم، سپس max-height را روی آن می‌گذاریم و در
		 * پایان transition محدودیت را حذف می‌کنیم. هنگام بستن، ابتدا
		 * به ارتفاع فعلی و سپس در دو فریم به صفر می‌رسیم تا انیمیشن
		 * در هر دو جهت به‌درستی اجرا شود.
		 */
		var closeTimer;

		if (open) {
			/* محاسبه‌ی دقیق ارتفاع محتوا، حتی پس از اعمال استایل‌های پویا. */
			body.style.maxHeight = 'none';
			var fullH = body.scrollHeight;
			body.style.maxHeight = fullH + 'px';
			/* از ارتفاع پیش از باز شدن شروع می‌کنیم تا transition اجرا شود. */
			void body.offsetHeight;

			if (body._zcAccT) clearTimeout(body._zcAccT);
			body._zcAccT = setTimeout(function () {
				if (item.classList.contains('is-open')) {
					body.style.maxHeight = 'none';
				}
			}, 450);
		} else {
			if (body._zcAccT) clearTimeout(body._zcAccT);
			/*
			 * بستن: ابتدا ارتفاع فعلی را به‌صورت صریح و بدون transition
			 * قفل می‌کنیم، سپس با transition به صفر می‌رسیم. این روش در
			 * همه‌ی مرورگرها قطعی است.
			 */
			var wasNone = body.style.maxHeight === 'none' || body.style.maxHeight === '';
			body.style.transition = 'none';
			body.style.maxHeight = (body.scrollHeight || 0) + 'px';
			void body.offsetHeight; // force reflow
			body.style.transition = '';
			requestAnimationFrame(function () {
				body.style.maxHeight = '0px';
			});
		}
	}

	function toggleAccordionItem(item, forceOpen) {
		var acc = item.closest('.zc-accordion');
		if (!acc) return;

		var single = acc.dataset.single === 'yes';
		var head   = $('.zc-accordion__head', item);
		var isOpen = item.classList.contains('is-open');
		var open   = (typeof forceOpen === 'boolean') ? forceOpen : !isOpen;

		// در حالت تک‌بازشو، بقیه‌ی آیتم‌ها بسته می‌شوند.
		if (single && open) {
			$$('.zc-accordion__item.is-open', acc).forEach(function (other) {
				if (other === item) return;
				other.classList.remove('is-open');
				setAccordionHeight(other, false);
				var oh = $('.zc-accordion__head', other);
				if (oh) oh.setAttribute('aria-expanded', 'false');
			});
		}

		item.classList.toggle('is-open', open);
		setAccordionHeight(item, open);
		if (head) head.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function initAccordion() {
		// ارتفاع آیتم‌هایی که از ابتدا باز هستند و تنظیم صفات دسترس‌پذیری.
		$$('.zc-accordion').forEach(function (acc) {
			$$('.zc-accordion__item', acc).forEach(function (item) {
				var head = $('.zc-accordion__head', item);
				var body = $('.zc-accordion__body', item);
				var open = item.classList.contains('is-open');

				if (head) {
					head.setAttribute('aria-expanded', open ? 'true' : 'false');
					if (body) {
						if (!body.id) {
							body.id = 'zc-acc-' + Math.random().toString(36).slice(2, 9);
						}
						head.setAttribute('aria-controls', body.id);
					}
				}

				setAccordionHeight(item, open);
			});
		});

		/*
		 * اتصال مستقیم به هر head به‌جای delegation روی document.
		 * این روش مطمئن‌تر است و در همه‌ی مرورگرها (حتی وقتی عنصر
		 * داخل viewport نیست یا روی آن overlay قرار دارد) کلیک را
		 * دریافت و باز/بسته‌کردن را اجرا می‌کند.
		 */
		$$('.zc-accordion__head').forEach(function (head) {
			/* جلوگیری از اتصال تکراری با یک پرچم روی خود عنصر. */
			if (head.getAttribute('data-zc-acc-bound') === '1') return;
			head.setAttribute('data-zc-acc-bound', '1');

			on(head, 'click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var item = head.closest ? head.closest('.zc-accordion__item') : head.parentElement;
				if (!item) return;
				toggleAccordionItem(item);
			});

			// پشتیبانی از صفحه‌کلید (Enter و Space).
			on(head, 'keydown', function (e) {
				if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') return;
				e.preventDefault();
				var item = head.closest ? head.closest('.zc-accordion__item') : head.parentElement;
				if (!item) return;
				toggleAccordionItem(item);
			});
		});

		// با تغییر اندازه‌ی صفحه، ارتفاع آیتم‌های باز بازمحاسبه می‌شود.
		on(window, 'resize', debounce(function () {
			$$('.zc-accordion__item.is-open').forEach(function (item) {
				setAccordionHeight(item, true);
			});
		}, 150));
	}

	/* ---------- Portfolio Filter ---------- */
	function initPortfolioFilter() {
		$$('.zc-pf-filter').forEach(function (bar) {
			if (!claimInit(bar, 'PfFilter')) return;

			var targetId = bar.dataset.zcFilter;
			var grid     = targetId ? document.getElementById(targetId) : null;

			if (!grid) return;

			on(bar, 'click', function (e) {
				var btn = e.target.closest('.zc-pf-filter__btn');
				if (!btn) return;

				var filter = btn.dataset.filter;

				$$('.zc-pf-filter__btn', bar).forEach(function (b) {
					b.classList.toggle('is-active', b === btn);
				});

				$$('.zc-pf-card', grid).forEach(function (card) {
					var cats = (card.dataset.cats || '').split(' ');
					var show = (filter === '*') || cats.indexOf(filter) !== -1;

					card.classList.toggle('is-hidden', !show);

					if (show) {
						card.style.animation = 'none';
						/* اجبار مرورگر به بازپخش انیمیشن */
						void card.offsetWidth;
						card.style.animation = '';
					}
				});
			});
		});
	}

	/* ---------- Quick View ---------- */
	function initQuickView() {
		if (!claimInit(document.body, 'QuickView')) return;

		var modal = null;

		function ensureModal() {
			if (modal) return modal;

			modal = document.createElement('div');
			modal.className = 'zc-qv-modal';
			modal.innerHTML =
				'<div class="zc-qv-modal__backdrop" data-zc-qv-close></div>' +
				'<div class="zc-qv-modal__box" role="dialog" aria-modal="true">' +
				'<button type="button" class="zc-qv-modal__close" data-zc-qv-close aria-label="بستن">&times;</button>' +
				'<div class="zc-qv-modal__content"></div>' +
				'</div>';
			document.body.appendChild(modal);

			on(modal, 'click', function (e) {
				if (e.target.closest('[data-zc-qv-close]')) closeModal();

				// تعویض تصویر اصلی با کلیک روی بندانگشتی‌ها
				var thumb = e.target.closest('.zc-qv__thumb');
				if (thumb) {
					var img = $('.zc-qv__main img', modal);
					if (img && thumb.dataset.full) {
						img.src = thumb.dataset.full;
						img.removeAttribute('srcset');
					}
					$$('.zc-qv__thumb', modal).forEach(function (t) {
						t.classList.toggle('is-active', t === thumb);
					});
				}
			});

			return modal;
		}

		function closeModal() {
			if (!modal) return;
			modal.classList.remove('is-open');
			document.body.style.overflow = '';
		}

		on(document, 'keydown', function (e) {
			if (e.key === 'Escape') closeModal();
		});

		on(document, 'click', function (e) {
			var btn = e.target.closest('[data-zc-quickview]');
			if (!btn) return;

			e.preventDefault();

			var id = btn.dataset.zcQuickview;
			var m  = ensureModal();
			var content = $('.zc-qv-modal__content', m);

			content.innerHTML = '<div class="zc-qv-loading"><span class="zc-spinner"></span></div>';
			m.classList.add('is-open');
			document.body.style.overflow = 'hidden';

			ajax('zc_quick_view', { id: id }).then(function (res) {
				if (res && res.success && res.data && res.data.html) {
					content.innerHTML = res.data.html;
				} else {
					content.innerHTML = '<p class="zc-qv-error">' +
						((res && res.data && res.data.message) || 'خطا در دریافت اطلاعات محصول') + '</p>';
				}
			}).catch(function () {
				content.innerHTML = '<p class="zc-qv-error">خطا در ارتباط با سرور</p>';
			});
		});
	}

	/* ---------- Generic AJAX Actions & Copy ---------- */
	/**
	 * دکمه‌های عمومی آجاکس: data-zc-action="نام_اکشن"
	 * داده‌های اضافی با data-zc-payload='{"key":"value"}' ارسال می‌شود.
	 * تأیید پیش از اجرا با data-zc-confirm="متن پرسش".
	 */
	function initActionButtons() {
		if (!claimInit(document.body, 'ActionBtns')) return;

		on(document, 'click', function (e) {
			var btn = e.target.closest('[data-zc-action]');
			if (!btn || btn.disabled) return;

			e.preventDefault();

			var action  = btn.dataset.zcAction;
			var confirmMsg = btn.dataset.zcConfirm;

			if (confirmMsg && !window.confirm(confirmMsg)) return;

			var payload = {};
			if (btn.dataset.zcPayload) {
				try { payload = JSON.parse(btn.dataset.zcPayload); } catch (err) { payload = {}; }
			}

			var original = btn.innerHTML;
			btn.disabled = true;
			btn.classList.add('is-loading');

			ajax(action, payload).then(function (res) {
				btn.disabled = false;
				btn.classList.remove('is-loading');
				btn.innerHTML = original;

				var msg = (res && res.data && res.data.message) || '';

				if (res && res.success) {
					if (msg) Toast.show(msg, 'success');

					// بروزرسانی کد اتصال بدون بارگذاری مجدد
					if (res.data && res.data.code) {
						var codeEl = document.getElementById('zc-bot-code');
						if (codeEl) codeEl.textContent = res.data.code;
					}

					if (res.data && res.data.reload) {
						setTimeout(function () { window.location.reload(); }, 900);
					}
				} else {
					Toast.show(msg || 'خطایی رخ داد', 'error');
				}
			}).catch(function () {
				btn.disabled = false;
				btn.classList.remove('is-loading');
				btn.innerHTML = original;
				Toast.show('خطا در ارتباط با سرور', 'error');
			});
		});
	}

	/**
	 * کپی متن یک عنصر در کلیپ‌بورد: data-zc-copy="#selector"
	 */
	function initCopyButtons() {
		if (!claimInit(document.body, 'CopyBtns')) return;

		on(document, 'click', function (e) {
			var btn = e.target.closest('[data-zc-copy]');
			if (!btn) return;

			e.preventDefault();

			var target = document.querySelector(btn.dataset.zcCopy);
			if (!target) return;

			var text = (target.textContent || '').trim();

			var done = function () { Toast.show('کپی شد!', 'success'); };

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
			} else {
				fallbackCopy(text, done);
			}
		});

		function fallbackCopy(text, cb) {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			try { document.execCommand('copy'); cb(); } catch (err) { /* noop */ }
			document.body.removeChild(ta);
		}
	}

	/* ---------- Scroll Animations ---------- */
	function initAnimations() {
		if (!S.animations) return;

		var items = $$('[data-zc-anim], [data-zc-stagger]');
		if (!items.length) return;

		if (!('IntersectionObserver' in window)) {
			items.forEach(function (i) { i.classList.add('is-in'); });
			return;
		}

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) return;

				var el = e.target;
				var delay = parseInt(el.dataset.zcDelay || 0, 10);

				/*
				 * تأخیر خودکار بر اساس جایگاه افقی: عناصری که در یک ردیف
				 * کنار هم هستند پشت سر هم ظاهر می‌شوند، نه هم‌زمان.
				 */
				if (!delay && el.dataset.zcAnim && el.parentElement) {
					var sibs = [].slice.call(el.parentElement.children);
					var idx = sibs.indexOf(el);
					if (idx > 0 && sibs.length > 1 && sibs.length <= 12) {
						delay = Math.min(idx * 55, 330);
					}
				}

				setTimeout(function () { el.classList.add('is-in'); }, delay);
				io.unobserve(el);
			});
		}, { threshold: 0.01, rootMargin: '0px 0px -50px 0px' });

		items.forEach(function (i) {
			if (claimInit(i, 'anim')) { io.observe(i); }
		});

		/**
		 * تور ایمنی: اطمینان از اینکه محتوا هرگز به صورت دائمی نامرئی نمی‌ماند.
		 */
		var reveal = function () {
			$$('[data-zc-anim]:not(.is-in), [data-zc-stagger]:not(.is-in)').forEach(function (el) {
				el.classList.add('is-in');
				io.unobserve(el);
			});

			/*
			 * پاس دوم: برخی عناصر باوجود is-in همچنان opacity:0 باقی می‌مانند
			 * (مثلاً وقتی ویجت المنتور بعد از observer دوباره رندر می‌شود یا
			 * ترنزیشن کامل نمی‌شود). این پاس، هر عنصرِ هنوز مخفی را به‌زور
			 * نمایان می‌کند تا هیچ محتوایی برای همیشه دیده نشود.
			 */
			setTimeout(function () {
				$$('[data-zc-anim].is-in').forEach(function (el) {
					var cs = getComputedStyle(el);
					if (parseFloat(cs.opacity) < 0.99) {
						el.style.opacity = '1';
						el.style.transform = 'none';
						el.style.filter = 'none';
						el.style.clipPath = 'none';
					}
				});
			}, 250);
		};

		setTimeout(reveal, 7000);
		on(window, 'beforeprint', reveal);
	}

	/**
	 * پارالاکس سبک برای عناصر تزئینی.
	 *
	 * از requestAnimationFrame و transform استفاده می‌کند تا هیچ
	 * بازچینشی (reflow) رخ ندهد. روی موبایل و در حالت کاهش حرکت خاموش است.
	 */
	function initParallax() {
		if (!S.animations) return;
		if (window.matchMedia('(prefers-reduced-motion:reduce)').matches) return;
		if (window.innerWidth < 900) return;

		var items = $$('[data-zc-parallax]');
		if (!items.length) return;

		var ticking = false;

		function update() {
			var vh = window.innerHeight;

			items.forEach(function (el) {
				var r = el.getBoundingClientRect();
				if (r.bottom < -200 || r.top > vh + 200) return;

				var speed = parseFloat(el.dataset.zcParallax) || 0.15;
				var progress = (r.top + r.height / 2 - vh / 2) / vh;

				el.style.transform = 'translate3d(0,' + (-progress * speed * 100).toFixed(2) + 'px,0)';
			});

			ticking = false;
		}

		function onScroll() {
			if (ticking) return;
			ticking = true;
			window.requestAnimationFrame(update);
		}

		on(window, 'scroll', onScroll, { passive: true });
		on(window, 'resize', onScroll, { passive: true });
		update();
	}

	/**
	 * هاور سه‌بعدی کارت‌ها؛ کارت به سمت اشاره‌گر متمایل می‌شود.
	 */
	function initTilt() {
		if (!S.animations) return;
		if (window.matchMedia('(prefers-reduced-motion:reduce)').matches) return;
		if (!window.matchMedia('(hover:hover)').matches) return;

		$$('[data-zc-tilt]').forEach(function (card) {
			if (!claimInit(card, 'tilt')) return;

			var max = parseFloat(card.dataset.zcTilt) || 7;
			var raf = null;

			function move(e) {
				if (raf) return;

				raf = window.requestAnimationFrame(function () {
					var r = card.getBoundingClientRect();
					var px = (e.clientX - r.left) / r.width - 0.5;
					var py = (e.clientY - r.top) / r.height - 0.5;

					card.style.transform =
						'perspective(900px) rotateX(' + (-py * max).toFixed(2) + 'deg) ' +
						'rotateY(' + (px * max).toFixed(2) + 'deg) translateY(-5px)';

					raf = null;
				});
			}

			function leave() {
				if (raf) { window.cancelAnimationFrame(raf); raf = null; }
				card.style.transform = '';
			}

			card.addEventListener('mousemove', move);
			card.addEventListener('mouseleave', leave);
		});
	}

	/**
	 * نوار پیشرفت خواندن صفحه در بالای پنجره + آشکارسازی هدر.
	 */
	function initScrollFx() {
		var bar = document.querySelector('[data-zc-scrollbar]');
		if (!bar || !claimInit(bar, 'scrollfx')) return;

		var ticking = false;

		function update() {
			var h = document.documentElement.scrollHeight - window.innerHeight;
			var p = h > 0 ? (window.pageYOffset / h) * 100 : 0;
			bar.style.transform = 'scaleX(' + (p / 100).toFixed(4) + ')';
			ticking = false;
		}

		on(window, 'scroll', function () {
			if (ticking) return;
			ticking = true;
			window.requestAnimationFrame(update);
		}, { passive: true });

		update();
	}

	/* ---------- Counters ---------- */
	function initCounters() {
		var els = $$('[data-zc-count]');
		if (!els.length || !('IntersectionObserver' in window)) {
			els.forEach(function (e) { e.textContent = e.dataset.zcCount; });
			return;
		}
		var faDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
		function fa(n) {
			return String(n).replace(/\d/g, function (d) { return faDigits[d]; });
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) return;
				var el = e.target;
				var target = parseFloat(el.dataset.zcCount) || 0;
				var dur = parseInt(el.dataset.zcDuration || 1800, 10);
				var isFa = el.dataset.zcFa !== 'no';
				var start = performance.now();
				function step(now) {
					var p = Math.min((now - start) / dur, 1);
					var eased = 1 - Math.pow(1 - p, 3);
					var val = Math.floor(eased * target);
					var out = val.toLocaleString('en-US');
					el.textContent = isFa ? fa(out) : out;
					if (p < 1) requestAnimationFrame(step);
					else el.textContent = isFa ? fa(target.toLocaleString('en-US')) : target.toLocaleString('en-US');
				}
				requestAnimationFrame(step);
				io.unobserve(el);
			});
		}, { threshold: 0.4 });
		els.forEach(function (e) { io.observe(e); });
	}

	/* ---------- Progress Bars ---------- */
	function initProgress() {
		var bars = $$('.zc-progress__bar[data-value]');
		if (!bars.length) return;
		if (!('IntersectionObserver' in window)) {
			bars.forEach(function (b) { b.style.width = b.dataset.value + '%'; });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.style.width = e.target.dataset.value + '%';
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.3 });
		bars.forEach(function (b) { b.style.width = '0%'; io.observe(b); });
	}

	/* ---------- Sliders ---------- */
	function initSliders() {
		$$('.zc-slider').forEach(function (slider) {
			if (!claimInit(slider, 'Slider')) return;

			var track = $('.zc-slider__track', slider);
			if (!track) return;
			var prev = $('[data-zc-slide="prev"]', slider);
			var next = $('[data-zc-slide="next"]', slider);
			var dotsBox = $('.zc-dots', slider);
			var autoplay = slider.dataset.autoplay === 'yes';
			var interval = parseInt(slider.dataset.interval || 5000, 10);
			var rtl = document.documentElement.dir === 'rtl';
			var timer = null;

			function step() {
				var slide = $('.zc-slider__slide', track);
				if (!slide) return track.clientWidth;
				var gap = parseFloat(getComputedStyle(track).gap) || 24;
				return slide.offsetWidth + gap;
			}
			function go(dir) {
				var amount = step() * dir * (rtl ? -1 : 1);
				track.scrollBy({ left: amount, behavior: 'smooth' });
			}
			on(next, 'click', function () { go(1); reset(); });
			on(prev, 'click', function () { go(-1); reset(); });

			// نقطه‌ها
			if (dotsBox) {
				var slides = $$('.zc-slider__slide', track);
				var perView = Math.max(1, Math.round(track.clientWidth / step()));
				var pages = Math.ceil(slides.length / perView);
				dotsBox.innerHTML = '';
				for (var i = 0; i < pages; i++) {
					var d = document.createElement('button');
					d.type = 'button';
					d.setAttribute('aria-label', 'اسلاید ' + (i + 1));
					if (i === 0) d.classList.add('is-active');
					(function (idx) {
						on(d, 'click', function () {
							track.scrollTo({ left: (rtl ? -1 : 1) * idx * perView * step(), behavior: 'smooth' });
							reset();
						});
					})(i);
					dotsBox.appendChild(d);
				}
				on(track, 'scroll', throttle(function () {
					var pos = Math.abs(track.scrollLeft) / (perView * step());
					var active = Math.round(pos);
					$$('button', dotsBox).forEach(function (b, idx) { b.classList.toggle('is-active', idx === active); });
				}, 120), { passive: true });
			}

			function tick() {
				var atEnd = Math.abs(track.scrollLeft) + track.clientWidth + 5 >= track.scrollWidth;
				if (atEnd) { track.scrollTo({ left: 0, behavior: 'smooth' }); }
				else { go(1); }
			}
			function start() { if (autoplay) timer = setInterval(tick, interval); }
			function reset() { clearInterval(timer); start(); }
			on(slider, 'mouseenter', function () { clearInterval(timer); });
			on(slider, 'mouseleave', start);
			start();

			// درگ با ماوس
			var isDown = false, startX = 0, startScroll = 0;
			on(track, 'mousedown', function (e) {
				isDown = true; startX = e.pageX; startScroll = track.scrollLeft;
				track.style.cursor = 'grabbing'; track.style.scrollSnapType = 'none';
			});
			on(document, 'mouseup', function () {
				if (!isDown) return;
				isDown = false; track.style.cursor = ''; track.style.scrollSnapType = '';
			});
			on(document, 'mousemove', function (e) {
				if (!isDown) return;
				e.preventDefault();
				track.scrollLeft = startScroll - (e.pageX - startX) * 1.4;
			});
		});
	}

	/* ---------- Testimonial video switcher ---------- */
	function initTestimonials() {
		$$('.zc-testi').forEach(function (box) {
			if (!claimInit(box, 'Testi')) return;

			var items = $$('.zc-testi__item', box);
			var media = $('.zc-testi__video img', box);
			var link = $('.zc-testi__video', box);
			items.forEach(function (item) {
				on(item, 'click', function () {
					items.forEach(function (i) { i.classList.remove('is-active'); });
					item.classList.add('is-active');
					if (media && item.dataset.image) { media.src = item.dataset.image; }
					if (link && item.dataset.video) { link.dataset.video = item.dataset.video; }
				});
			});
		});
	}

	/* ---------- Video Lightbox ---------- */
	function initLightbox() {
		function open(src) {
			var box = document.createElement('div');
			box.className = 'zc-lightbox';
			box.innerHTML =
				'<div class="zc-lightbox__inner">' +
					'<button class="zc-lightbox__close" aria-label="بستن">&times;</button>' +
					'<div class="zc-lightbox__frame"></div>' +
				'</div>';
			var frame = $('.zc-lightbox__frame', box);
			if (/youtube|youtu\.be|aparat|vimeo/i.test(src)) {
				frame.innerHTML = '<iframe src="' + src + '" allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>';
			} else {
				frame.innerHTML = '<video src="' + src + '" controls autoplay playsinline></video>';
			}
			document.body.appendChild(box);
			document.body.style.overflow = 'hidden';
			requestAnimationFrame(function () { box.classList.add('is-open'); });

			function close() {
				box.classList.remove('is-open');
				document.body.style.overflow = '';
				setTimeout(function () { box.remove(); }, 300);
			}
			on($('.zc-lightbox__close', box), 'click', close);
			on(box, 'click', function (e) { if (e.target === box) close(); });
			on(document, 'keydown', function esc(e) {
				if (e.key === 'Escape') { close(); document.removeEventListener('keydown', esc); }
			});
		}

		on(document, 'click', function (e) {
			var trigger = e.target.closest('[data-zc-video]');
			if (!trigger) return;
			e.preventDefault();
			var src = trigger.dataset.zcVideo || trigger.dataset.video;
			if (src) open(src);
		});
	}

	/* ---------- Share buttons ---------- */
	function initShare() {
		on(document, 'click', function (e) {
			var btn = e.target.closest('.zc-share__btn');
			if (!btn) return;
			var net = btn.dataset.net;
			if (net === 'copy') {
				e.preventDefault();
				var url = btn.dataset.url || location.href;
				if (navigator.clipboard) {
					navigator.clipboard.writeText(url).then(function () { Toast.show(I18N.copy || 'کپی شد', 'success'); });
				} else {
					var ta = document.createElement('textarea');
					ta.value = url; document.body.appendChild(ta); ta.select();
					document.execCommand('copy'); ta.remove();
					Toast.show(I18N.copy || 'کپی شد', 'success');
				}
				return;
			}
			if (btn.tagName === 'A' && btn.href && net !== 'email') {
				e.preventDefault();
				window.open(btn.href, '_blank', 'width=640,height=540,noopener');
			}
		});
	}

	/* ---------- Live Chat ---------- */
	function initChat() {
		var widget = $('.zc-chat-widget');
		if (!widget) return;

		/*
		 * init() به ازای هر ویجت المنتور یک بار اجرا می‌شود (حدود ۳۰ بار
		 * در صفحه اصلی). بدون این محافظ، هر اجرا یک شنونده‌ی تازه روی
		 * دکمه می‌بست و یک کلیک، کلاس is-open را ده‌ها بار جابه‌جا
		 * می‌کرد؛ نتیجه این بود که پنجره‌ی گفتگو اصلاً باز نمی‌شد.
		 */
		if (!claimInit(widget, 'chatWidget')) { return; }

		var toggle = $('.zc-chat-toggle', widget);
		var body = $('.zc-chat-body', widget);
		var form = $('.zc-chat-form', widget);
		var input = $('.zc-chat-form input', widget);

		on(toggle, 'click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var opening = !widget.classList.contains('is-open');
			widget.classList.toggle('is-open', opening);
			document.body.classList.toggle('zc-chat-open', opening);

			// روی موبایل صفحه‌کلید نباید بلافاصله بالا بیاید و چیدمان را بشکند.
			if (opening && input && window.innerWidth > 640) { input.focus(); }
		});

		$$('[data-zc-close="chat"]', widget).forEach(function (b) {
			on(b, 'click', function (e) {
				e.preventDefault();
				widget.classList.remove('is-open');
				document.body.classList.remove('zc-chat-open');
			});
		});

		// بستن با کلید Escape.
		on(document, 'keydown', function (e) {
			if (e.key === 'Escape' && widget.classList.contains('is-open')) {
				closeChat();
			}
		});

		/*
		 * روی موبایل، ضربه روی پرده‌ی تیره‌ی پشت برگه آن را می‌بندد.
		 * پرده یک شبه‌عنصر است، پس کلیک روی خودِ ویجت و بیرون از
		 * جعبه را بررسی می‌کنیم.
		 */
		on(document, 'click', function (e) {
			if (!widget.classList.contains('is-open')) { return; }
			if (window.innerWidth > 640) { return; }

			var box = $('.zc-chat-box', widget);

			if (box && !box.contains(e.target) && !e.target.closest('.zc-chat-toggle')) {
				closeChat();
			}
		});

		function closeChat() {
			widget.classList.remove('is-open');
			document.body.classList.remove('zc-chat-open');
		}

		function push(text, dir) {
			var m = document.createElement('div');
			m.className = 'zc-chat-msg zc-chat-msg--' + dir;
			m.textContent = text;
			body.appendChild(m);
			body.scrollTop = body.scrollHeight;
			return m;
		}

		on(form, 'submit', function (e) {
			e.preventDefault();
			var msg = input.value.trim();
			if (!msg) return;
			push(msg, 'out');
			input.value = '';
			var typing = push('…', 'in');
			ajax('zc_chat_send', { message: msg }).then(function (res) {
				typing.remove();
				if (res.success) { push(res.data.reply, 'in'); }
				else { push(I18N.error || 'خطا', 'in'); }
			}).catch(function () {
				typing.remove();
				push(I18N.error || 'خطا', 'in');
			});
		});
	}

	/* ---------- AJAX Add to cart / wishlist ---------- */
	function initAjaxActions() {
		on(document, 'click', function (e) {
			// افزودن به علاقه‌مندی
			var wish = e.target.closest('[data-zc-wishlist]');
			if (wish) {
				e.preventDefault();
				if (!CFG.isLogged) { window.location.href = CFG.loginUrl; return; }
				var pid = wish.dataset.zcWishlist;
				wish.classList.add('is-loading');
				ajax('zc_toggle_wishlist', { post_id: pid }).then(function (res) {
					wish.classList.remove('is-loading');
					if (res.success) {
						wish.classList.toggle('is-active', res.data.added);
						Toast.show(res.data.message, 'success');
						var c = $('[data-zc-wishlist-count]');
						if (c) c.textContent = res.data.count;
					} else { Toast.show(res.data.message || I18N.error, 'error'); }
				});
				return;
			}

			// افزودن به سبد با ای‌جکس
			var cart = e.target.closest('[data-zc-addcart]');
			if (cart) {
				e.preventDefault();
				var id = cart.dataset.zcAddcart;
				cart.classList.add('is-loading');
				ajax('zc_add_to_cart', { product_id: id, quantity: cart.dataset.qty || 1 }).then(function (res) {
					cart.classList.remove('is-loading');
					if (res.success) {
						Toast.show(res.data.message, 'success');
						var cc = $('[data-zc-cart-count]');
						if (cc) { cc.textContent = res.data.count; cc.style.display = 'flex'; }
						markProductInCart(id, res.data.cart_url || cart.dataset.cartUrl || '');
						document.body.dispatchEvent(new CustomEvent('zc:cart:updated', { detail: res.data }));
					} else { Toast.show(res.data.message || I18N.error, 'error'); }
				});
			}
		});
	}

	/* ---------- تبدیل دکمه‌ی «خرید» به «مشاهده سبد» ---------- */
	function markProductInCart(id, cartUrl) {
		if (!id) return;
		var url = cartUrl || (window.ZC && ZC.homeUrl ? ZC.homeUrl + 'cart/' : '#');
		$$('[data-zc-addcart="' + id + '"]').forEach(function (btn) {
			if (btn.closest('[data-zc-incart]')) return;
			var a = document.createElement('a');
			a.href = url;
			a.className = btn.className + ' zc-tf-card__cart--incart';
			a.setAttribute('data-zc-incart', id);
			a.setAttribute('aria-label', 'مشاهده سبد خرید');
			a.innerHTML = '<svg class="zc-icon" width="16" height="16" aria-hidden="true" focusable="false"><use href="#zci-cart"></use></svg><span>مشاهده سبد</span>';
			btn.replaceWith(a);
		});
	}

	/* ---------- Generic AJAX Forms ---------- */
	function initForms() {
		$$('form[data-zc-form]').forEach(function (form) {
			if (!claimInit(form, 'Form')) return;

			on(form, 'submit', function (e) {
				e.preventDefault();
				var action = form.dataset.zcForm;
				var btn = form.querySelector('[type=submit]');
				var msgBox = $('.zc-form-msg', form);
				var fd = new FormData(form);
				fd.append('action', action);
				fd.append('nonce', CFG.nonce);

				if (btn) { btn.classList.add('is-loading'); btn.disabled = true; }
				if (msgBox) msgBox.innerHTML = '';

				fetch(CFG.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
						var ok = res.success;
						var text = (res.data && res.data.message) || (ok ? I18N.added : I18N.error);
						if (msgBox) {
							msgBox.innerHTML = '<div class="zc-alert zc-alert--' + (ok ? 'success' : 'error') + '">' + text + '</div>';
						} else {
							Toast.show(text, ok ? 'success' : 'error');
						}
						if (ok) {
							if (res.data.redirect) { setTimeout(function () { location.href = res.data.redirect; }, 900); }
							else if (res.data.reload) { setTimeout(function () { location.reload(); }, 900); }
							else if (form.dataset.zcReset !== 'no') { form.reset(); }
							form.dispatchEvent(new CustomEvent('zc:form:success', { detail: res.data }));
						}
					})
					.catch(function () {
						if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
						Toast.show(I18N.error, 'error');
					});
			});
		});
	}

	/* ---------- OTP / countdown ---------- */
	function initCountdowns() {
		$$('[data-zc-countdown]').forEach(function (el) {
			var sec = parseInt(el.dataset.zcCountdown, 10) || 120;
			var target = el.dataset.target ? $(el.dataset.target) : null;
			var faDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
			function fa(n) { return String(n).replace(/\d/g, function (d) { return faDigits[d]; }); }
			var t = setInterval(function () {
				sec--;
				var m = Math.floor(sec / 60), s = sec % 60;
				el.textContent = fa(m + ':' + (s < 10 ? '0' + s : s));
				if (sec <= 0) {
					clearInterval(t);
					el.textContent = '';
					if (target) { target.style.display = ''; target.disabled = false; }
					el.dispatchEvent(new CustomEvent('zc:countdown:done'));
				}
			}, 1000);
		});
	}

	/* ---------- Filter / Load more (archives) ---------- */
	function initLoadMore() {
		$$('[data-zc-loadmore]').forEach(function (btn) {
			if (!claimInit(btn, 'LoadMore')) return;

			on(btn, 'click', function (e) {
				e.preventDefault();
				var page = parseInt(btn.dataset.page || 1, 10) + 1;
				var max = parseInt(btn.dataset.max || 1, 10);
				var target = $(btn.dataset.target);
				btn.classList.add('is-loading');

				ajax('zc_load_more', {
					page: page,
					query: btn.dataset.query || '',
					tpl: btn.dataset.tpl || 'card'
				}).then(function (res) {
					btn.classList.remove('is-loading');
					if (res.success && target) {
						target.insertAdjacentHTML('beforeend', res.data.html);
						btn.dataset.page = page;
						initAnimations();
						if (page >= max) btn.style.display = 'none';
					} else { btn.style.display = 'none'; }
				});
			});
		});
	}

	/* ---------- Sticky sidebar on course page ---------- */
	function initStickyBox() {
		$$('[data-zc-sticky]').forEach(function (el) {
			var top = parseInt(el.dataset.zcSticky, 10) || 100;
			if (window.innerWidth < 1024) return;
			el.style.position = 'sticky';
			el.style.top = top + 'px';
		});
	}

	/* ---------- Copy code blocks ---------- */
	function initCodeCopy() {
		$$('.zc-entry__content pre').forEach(function (pre) {
			if (!claimInit(pre, 'Copy')) return;

			var btn = document.createElement('button');
			btn.className = 'zc-code-copy';
			btn.type = 'button';
			btn.textContent = 'کپی';
			pre.style.position = 'relative';
			pre.appendChild(btn);
			on(btn, 'click', function () {
				var code = pre.querySelector('code') || pre;
				navigator.clipboard.writeText(code.textContent.replace(/کپی$/, '').trim()).then(function () {
					btn.textContent = 'کپی شد!';
					setTimeout(function () { btn.textContent = 'کپی'; }, 1800);
				});
			});
		});
	}

	/* ---------- Reading progress on single ---------- */
	function initReadingProgress() {
		var bar = $('.zc-reading-progress');
		if (!bar) return;
		var article = $('.zc-entry__content');
		if (!article) return;
		var update = throttle(function () {
			var rect = article.getBoundingClientRect();
			var total = rect.height - window.innerHeight;
			var passed = -rect.top;
			var pct = Math.max(0, Math.min(100, (passed / total) * 100));
			bar.style.width = pct + '%';
		}, 60);
		on(window, 'scroll', update, { passive: true });
		update();
	}

	/* ---------- Quantity inputs ---------- */
	function initQty() {
		on(document, 'click', function (e) {
			var btn = e.target.closest('[data-zc-qty]');
			if (!btn) return;
			e.preventDefault();
			var wrap = btn.closest('.zc-qty');
			var input = $('input', wrap);
			if (!input) return;
			var val = parseInt(input.value, 10) || 1;
			var min = parseInt(input.min, 10) || 1;
			var max = parseInt(input.max, 10) || 9999;
			val = btn.dataset.zcQty === 'plus' ? Math.min(max, val + 1) : Math.max(min, val - 1);
			input.value = val;
			input.dispatchEvent(new Event('change', { bubbles: true }));
		});
	}

	/* ---------- Init ---------- */
	/* ==========================================================================
	   گالری صفحه‌ی محصول و نمونه‌کار
	   ========================================================================== */

	/**
	 * تعویض تصویر اصلی با کلیک روی بندانگشتی‌ها.
	 * از claimInit استفاده می‌کند تا با هر بار اجرای init دوباره بایند نشود.
	 */
	function initPdpGallery() {
		$$('[data-zc-gallery]').forEach(function (box) {
			if (!claimInit(box, 'pdpGallery')) { return; }

			var stage = box.querySelector('.zc-pdp-gallery__img');
			if (!stage) { return; }

			box.addEventListener('click', function (e) {
				var thumb = e.target.closest('.zc-pdp-gallery__thumb');
				if (!thumb || !box.contains(thumb)) { return; }

				var full = thumb.dataset.full;
				if (!full) { return; }

				stage.style.opacity = '0';

				var img = new Image();
				img.onload = function () {
					stage.src = full;
					stage.style.opacity = '1';
				};
				img.onerror = function () { stage.style.opacity = '1'; };
				img.src = full;

				box.querySelectorAll('.zc-pdp-gallery__thumb').forEach(function (t) {
					t.classList.remove('is-active');
				});
				thumb.classList.add('is-active');
			});
		});
	}

	/**
	 * نوار پرش سریع: اسکرول نرم و برجسته‌کردن بخش فعال.
	 */
	function initJumpNav() {
		$$('[data-zc-jump]').forEach(function (nav) {
			if (!claimInit(nav, 'jumpNav')) { return; }

			var links = [].slice.call(nav.querySelectorAll('a'));
			if (!links.length) { return; }

			var targets = links.map(function (a) {
				return document.querySelector(a.getAttribute('href'));
			});

			nav.addEventListener('click', function (e) {
				var a = e.target.closest('a');
				if (!a) { return; }

				var target = document.querySelector(a.getAttribute('href'));
				if (!target) { return; }

				e.preventDefault();

				var offset = nav.getBoundingClientRect().height + 90;
				var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

				window.scrollTo({ top: top, behavior: 'smooth' });
			});

			// برجسته‌کردن بخشی که در دید است.
			if ('IntersectionObserver' in window) {
				var obs = new IntersectionObserver(function (entries) {
					entries.forEach(function (entry) {
						if (!entry.isIntersecting) { return; }

						var i = targets.indexOf(entry.target);
						if (i < 0) { return; }

						links.forEach(function (l) { l.classList.remove('is-active'); });
						links[i].classList.add('is-active');
					});
				}, { rootMargin: '-140px 0px -65% 0px', threshold: 0 });

				targets.forEach(function (t) { if (t) { obs.observe(t); } });
			}
		});
	}

	/* ==========================================================================
	   اطلاعیه‌ها
	   ========================================================================== */

	/**
	 * بستن اطلاعیه (نوار، پنجره شناور یا کارت پنل) و ثبت آن در سرور.
	 *
	 * از واگذاری رویداد (delegation) روی document استفاده می‌کند تا با
	 * اجرای چندباره‌ی init هندلرها روی هم انباشته نشوند.
	 */
	function initAnnouncements() {
		if (!claimInit(document.body, 'announce')) { return; }

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-zc-an-dismiss]');

			if (btn) {
				var id = btn.dataset.zcAnDismiss;
				var bar = btn.closest('.zc-anbar');
				var card = btn.closest('.zc-ancard');
				var modal = btn.closest('[data-zc-anmodal]');

				if (bar) {
					bar.classList.add('is-closing');
					setTimeout(function () {
						bar.remove();
						showNextBar();
					}, 320);
				}

				if (card) {
					card.classList.add('is-closing');
					setTimeout(function () { card.remove(); }, 340);
				}

				if (modal) { closeModal(modal); }

				ajax('zc_announce_dismiss', { id: id });
				return;
			}

			// بستن پنجره بدون ثبت «خواندم».
			var closer = e.target.closest('[data-zc-an-close]');

			if (closer) {
				var m = closer.closest('[data-zc-anmodal]');
				if (m) { closeModal(m); }
			}
		});

		// نمایش پنجره‌ی شناور با کمی تأخیر تا با بارگذاری صفحه تداخل نکند.
		var modal = $('[data-zc-anmodal]');

		if (modal) {
			setTimeout(function () {
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
			}, 1200);

			// بستن با کلید Escape.
			on(document, 'keydown', function (e) {
				if (e.key === 'Escape' && modal.classList.contains('is-open')) {
					closeModal(modal);
				}
			});
		}

		function closeModal(m) {
			m.classList.remove('is-open');
			m.setAttribute('aria-hidden', 'true');
		}

		/** نمایش اطلاعیه‌ی بعدی در صف نوار. */
		function showNextBar() {
			var next = $('.zc-anbar.is-hidden');
			if (next) { next.classList.remove('is-hidden'); }
		}
	}

	/* ==========================================================================
	   ویرایشگر متن غنی (WYSIWYG)
	   ========================================================================== */

	/**
	 * راه‌اندازی ویرایشگرهای متن.
	 *
	 * محتوای ناحیه‌ی contenteditable با هر تغییر در textarea پنهان
	 * کپی می‌شود تا ارسال فرم دقیقاً همان چیزی باشد که کاربر می‌بیند.
	 */
	function initEditors() {
		$$('[data-zc-editor]').forEach(function (ed) {
			if (!claimInit(ed, 'editor')) { return; }

			var area = $('.zc-editor__area', ed);
			var input = $('.zc-editor__input', ed);
			var counter = $('[data-zc-editor-count]', ed);
			var file = $('.zc-editor__file', ed);

			if (!area || !input) { return; }

			/** همگام‌سازی محتوا با فیلد پنهان. */
			function sync() {
				var html = area.innerHTML.trim();

				// ناحیه‌ی خالی در مرورگرها یک <br> می‌گذارد.
				if (html === '<br>' || html === '<div><br></div>') { html = ''; }

				/*
				 * مرورگر گاهی فهرست یا نقل‌قول را داخل پاراگراف می‌گذارد
				 * که HTML نامعتبر است و wpautop آن را خراب می‌کند.
				 */
				html = html.replace(/<p>\s*(<(?:ul|ol|blockquote|pre)[\s\S]*?<\/(?:ul|ol|blockquote|pre)>)\s*<\/p>/gi, '$1');

				input.value = html;

				if (counter) {
					var chars = (area.textContent || '').trim().length;
					counter.textContent = toFa(String(chars));
				}
			}

			/** تبدیل رقم لاتین به فارسی. */
			function toFa(str) {
				return str.replace(/[0-9]/g, function (d) {
					return '۰۱۲۳۴۵۶۷۸۹'[d];
				});
			}

			/*
			 * بدون بلوک ریشه، execCommand کل محتوا را یکجا در فهرست یا
			 * نقل‌قول می‌پیچد. پس پاراگراف را جداکننده‌ی پیش‌فرض می‌کنیم
			 * و نخستین بلوک را در اولین تایپ می‌سازیم.
			 */
			try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (err) {}

			function ensureBlock() {
				if (!area.firstElementChild || area.firstChild.nodeType === 3) {
					document.execCommand('formatBlock', false, 'p');
				}
			}

			area.addEventListener('focus', function () {
				if (area.innerHTML.trim() === '') {
					area.innerHTML = '<p><br></p>';

					var r = document.createRange();
					r.setStart(area.firstChild, 0);
					r.collapse(true);

					var sel = window.getSelection();
					sel.removeAllRanges();
					sel.addRange(r);
				}
			});

			area.addEventListener('input', function () { ensureBlock(); sync(); });
			area.addEventListener('blur', sync);
			sync();

			/*
			 * کلیک روی دکمه‌ی نوار ابزار، ناحیه‌ی متن را از فوکوس خارج و
			 * نشانگر را به ابتدا برمی‌گرداند؛ نتیجه‌اش درج متن در جای
			 * اشتباه بود. با گرفتن mousedown، فوکوس اصلاً جابه‌جا نمی‌شود
			 * و انتخاب کاربر دست‌نخورده می‌ماند.
			 */
			ed.addEventListener('mousedown', function (e) {
				if (e.target.closest('.zc-editor__btn')) { e.preventDefault(); }
			});

			// نوار ابزار
			ed.addEventListener('click', function (e) {
				var btn = e.target.closest('.zc-editor__btn');
				if (!btn || !ed.contains(btn)) { return; }

				e.preventDefault();

				if (btn.hasAttribute('data-zc-editor-img')) {
					if (file) { file.click(); }
					return;
				}

				var cmd = btn.dataset.cmd;
				if (!cmd) { return; }

				// اگر نشانگر بیرون از ویرایشگر بود، به انتهای متن برگردد.
				if (!area.contains(window.getSelection().anchorNode)) {
					area.focus();

					var sel = window.getSelection();
					var r = document.createRange();
					r.selectNodeContents(area);
					r.collapse(false);
					sel.removeAllRanges();
					sel.addRange(r);
				}

				if (cmd === 'createLink') {
					var url = window.prompt(I18N.linkPrompt || 'نشانی پیوند را وارد کنید:', 'https://');
					if (url) { document.execCommand('createLink', false, url); }
				} else if (cmd.indexOf('formatBlock:') === 0) {
					document.execCommand('formatBlock', false, cmd.split(':')[1]);
				} else {
					document.execCommand(cmd, false, null);
				}

				sync();
			});

			/*
			 * چسباندن متن: قالب‌بندی ورد و سایت‌های دیگر حذف می‌شود تا
			 * HTML ناخواسته وارد پیام نشود.
			 */
			area.addEventListener('paste', function (e) {
				e.preventDefault();
				var text = (e.clipboardData || window.clipboardData).getData('text/plain');
				document.execCommand('insertText', false, text);
				sync();
			});

			var maxSize = parseInt(ed.dataset.max, 10) || 0;
			var maxLabel = ed.dataset.maxLabel || '';

			/** بارگذاری تصویر و درج در محل نشانگر. */
			function uploadImage(f) {
				if (!f) { return; }

				if (f.type.indexOf('image/') !== 0) {
					Toast.show('فقط تصویر مجاز است.', 'error');
					return;
				}

				// پیش از رفت‌وبرگشت شبکه، حجم را همین‌جا بسنجیم.
				if (maxSize && f.size > maxSize) {
					Toast.show('حجم تصویر بیش از ' + maxLabel + ' مگابایت است.', 'error');
					return;
				}

				var mark = document.createElement('span');
				mark.className = 'zc-editor__uploading';
				mark.textContent = I18N.uploading || 'در حال بارگذاری…';
				area.appendChild(mark);

				/*
				 * ajax() خودش FormData می‌سازد و File را درست ضمیمه
				 * می‌کند، پس فایل را به‌صورت یک کلید ساده می‌فرستیم.
				 */
				ajax('zc_editor_upload', { file: f }).then(function (res) {
					mark.remove();

					if (res.success) {
						var img = document.createElement('img');
						img.src = res.data.url;
						img.alt = res.data.alt || '';
						img.className = 'zc-editor__img';
						area.appendChild(img);
						area.appendChild(document.createElement('br'));
						sync();
					} else {
						Toast.show(res.data.message || 'خطا در بارگذاری', 'error');
					}
				}).catch(function () {
					mark.remove();
					Toast.show(I18N.error || 'خطا در بارگذاری', 'error');
				});
			}

			if (file) {
				file.addEventListener('change', function () {
					if (this.files && this.files[0]) { uploadImage(this.files[0]); }
					this.value = '';
				});
			}

			// کشیدن و رها کردن تصویر روی ناحیه‌ی متن
			area.addEventListener('dragover', function (e) {
				e.preventDefault();
				ed.classList.add('is-dragover');
			});

			area.addEventListener('dragleave', function () {
				ed.classList.remove('is-dragover');
			});

			area.addEventListener('drop', function (e) {
				e.preventDefault();
				ed.classList.remove('is-dragover');

				var files = e.dataTransfer && e.dataTransfer.files;
				if (files && files.length) { uploadImage(files[0]); }
			});

			// پیش از ارسال فرم حتماً همگام شود.
			var form = ed.closest('form');
			if (form) { on(form, 'submit', sync); }
		});
	}

	/**
	 * ناحیه‌ی کشیدن فایل پیوست (خارج از ویرایشگر).
	 */
	function initDropzones() {
		$$('[data-zc-dropzone]').forEach(function (zone) {
			if (!claimInit(zone, 'dropzone')) { return; }

			var input = $('.zc-dropzone__input', zone);
			var preview = $('.zc-dropzone__preview', zone);

			if (!input) { return; }

			function show(f) {
				if (!preview) { return; }

				preview.hidden = false;
				preview.innerHTML = '';

				var box = document.createElement('div');
				box.className = 'zc-dropzone__item';

				if (f.type.indexOf('image/') === 0) {
					var img = document.createElement('img');
					img.src = URL.createObjectURL(f);
					box.appendChild(img);
				}

				var meta = document.createElement('span');
				meta.textContent = f.name + ' — ' + Math.round(f.size / 1024) + ' KB';
				box.appendChild(meta);

				var del = document.createElement('button');
				del.type = 'button';
				del.className = 'zc-dropzone__del';
				del.textContent = '✕';
				del.addEventListener('click', function () {
					input.value = '';
					preview.hidden = true;
					preview.innerHTML = '';
				});
				box.appendChild(del);

				preview.appendChild(box);
			}

			var maxSize = parseInt(zone.dataset.max, 10) || 0;
			var maxLabel = zone.dataset.maxLabel || '';

			/** بررسی حجم پیش از نمایش پیش‌نمایش. */
			function accept(f) {
				if (maxSize && f.size > maxSize) {
					Toast.show('حجم فایل بیش از ' + maxLabel + ' مگابایت است.', 'error');
					input.value = '';
					return false;
				}

				return true;
			}

			input.addEventListener('change', function () {
				if (this.files && this.files[0] && accept(this.files[0])) { show(this.files[0]); }
			});

			['dragenter', 'dragover'].forEach(function (ev) {
				zone.addEventListener(ev, function (e) {
					e.preventDefault();
					zone.classList.add('is-dragover');
				});
			});

			['dragleave', 'drop'].forEach(function (ev) {
				zone.addEventListener(ev, function (e) {
					e.preventDefault();
					zone.classList.remove('is-dragover');
				});
			});

			zone.addEventListener('drop', function (e) {
				var files = e.dataTransfer && e.dataTransfer.files;

				if (files && files.length && accept(files[0])) {
					input.files = files;
					show(files[0]);
				}
			});
		});
	}

	/* ==========================================================================
	   سامانه‌ی قرارداد: جادوگر، امضا و تأیید پیامکی
	   ========================================================================== */

	/**
	 * جادوگر سه‌مرحله‌ای ساخت قرارداد.
	 */
	function initContractWizard() {
		$$('[data-zc-ct-wizard]').forEach(function (wiz) {
			if (!claimInit(wiz, 'ctwiz')) { return; }

			var form = $('[data-zc-ct-form]', wiz);
			var preview = $('[data-zc-ct-preview]', wiz);
			var agree = $('[data-zc-ct-agree]', wiz);
			var nextBtn = $('[data-zc-ct-next]', wiz);
			var backBtn = $('[data-zc-ct-back]', wiz);
			var signBox = $('[data-zc-ct-sign]', wiz);
			var steps = document.querySelector('[data-zc-ct-steps]');

			/** رفتن به یک گام. */
			function go(n) {
				$$('.zc-ct-pane', wiz).forEach(function (p) {
					p.classList.toggle('is-active', p.dataset.pane === String(n));
				});

				if (steps) {
					$$('.zc-ct-step', steps).forEach(function (st) {
						var i = parseInt(st.dataset.step, 10);
						st.classList.toggle('is-active', i === n);
						st.classList.toggle('is-done', i < n);
					});
				}

				wiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			if (form) {
				on(form, 'submit', function (e) {
					e.preventDefault();

					var msg = $('.zc-form-msg', form);
					var btn = $('button[type=submit]', form);
					var data = { tpl: wiz.dataset.tpl };

					new FormData(form).forEach(function (v, k) { data[k] = v; });

					if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }

					ajax('zc_contract_create', data).then(function (res) {
						if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }

						if (!res.success) {
							if (msg) {
								msg.textContent = res.data.message || 'خطا';
								msg.className = 'zc-form-msg is-error';
							}

							// تمرکز روی فیلد مشکل‌دار.
							if (res.data.field) {
								var bad = form.querySelector('[name="f_' + res.data.field + '"]');
								if (bad) { bad.focus(); }
							}

							return;
						}

						if (msg) { msg.textContent = ''; msg.className = 'zc-form-msg'; }
						if (preview) { preview.innerHTML = res.data.body; }

						// شناسه‌ی قرارداد تازه به بخش امضا داده می‌شود.
						if (signBox) { signBox.dataset.id = res.data.id; }

						go(2);
					}).catch(function () {
						if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }
						Toast.show('خطا در ارتباط با سرور', 'error');
					});
				});
			}

			if (agree && nextBtn) {
				on(agree, 'change', function () { nextBtn.disabled = !agree.checked; });
			}

			if (nextBtn) { on(nextBtn, 'click', function () { go(3); }); }
			if (backBtn) { on(backBtn, 'click', function () { go(1); }); }
		});
	}

	/**
	 * کادر امضای دیجیتال روی canvas.
	 */
	function initSignaturePad() {
		$$('[data-zc-sigpad]').forEach(function (pad) {
			if (!claimInit(pad, 'sigpad')) { return; }

			var canvas = $('.zc-sigpad__canvas', pad);
			var clear = $('[data-zc-sigpad-clear]', pad);

			if (!canvas) { return; }

			var ctx = canvas.getContext('2d');
			var drawing = false;
			var empty = true;

			/*
			 * canvas با اندازه‌ی ثابت ۶۰۰×۲۰۰ ساخته می‌شود ولی روی صفحه
			 * کشیده می‌شود؛ باید مختصات اشاره‌گر را متناسب مقیاس کنیم
			 * وگرنه خط از نوک قلم فاصله می‌گیرد.
			 */
			function pos(e) {
				var r = canvas.getBoundingClientRect();
				var t = e.touches ? e.touches[0] : e;

				return {
					x: (t.clientX - r.left) * (canvas.width / r.width),
					y: (t.clientY - r.top) * (canvas.height / r.height)
				};
			}

			ctx.lineWidth = 2.4;
			ctx.lineCap = 'round';
			ctx.lineJoin = 'round';
			ctx.strokeStyle = '#141A31';

			function start(e) {
				e.preventDefault();
				drawing = true;
				empty = false;
				pad.classList.add('has-sig');

				var p = pos(e);
				ctx.beginPath();
				ctx.moveTo(p.x, p.y);
			}

			function move(e) {
				if (!drawing) { return; }
				e.preventDefault();

				var p = pos(e);
				ctx.lineTo(p.x, p.y);
				ctx.stroke();
			}

			function end() { drawing = false; }

			canvas.addEventListener('mousedown', start);
			canvas.addEventListener('mousemove', move);
			document.addEventListener('mouseup', end);

			canvas.addEventListener('touchstart', start, { passive: false });
			canvas.addEventListener('touchmove', move, { passive: false });
			canvas.addEventListener('touchend', end);

			if (clear) {
				on(clear, 'click', function () {
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					empty = true;
					pad.classList.remove('has-sig');
				});
			}

			// در دسترس بخش امضا.
			pad.zcGetSignature = function () {
				return empty ? '' : canvas.toDataURL('image/png');
			};
		});
	}

	/**
	 * ارسال کد پیامکی و امضای نهایی.
	 */
	function initContractSign() {
		$$('[data-zc-ct-sign]').forEach(function (box) {
			if (!claimInit(box, 'ctsign')) { return; }

			var otpBtn = $('[data-zc-ct-otp]', box);
			var codeIn = $('[data-zc-ct-code]', box);
			var final = $('[data-zc-ct-final]', box);
			var submit = $('[data-zc-ct-submit]', box);
			var timer = $('[data-zc-ct-timer]', box);
			var msg = $('.zc-form-msg', box);
			var pad = $('[data-zc-sigpad]', box);

			/** فعال/غیرفعال کردن دکمه‌ی نهایی. */
			function refresh() {
				if (!submit) { return; }

				var hasCode = codeIn && codeIn.value.trim().length >= 4;
				var hasSig = pad && pad.zcGetSignature && pad.zcGetSignature() !== '';
				var agreed = final && final.checked;

				submit.disabled = !(hasCode && hasSig && agreed);
			}

			if (codeIn) { on(codeIn, 'input', refresh); }
			if (final) { on(final, 'change', refresh); }

			// کشیدن امضا هم باید دکمه را به‌روز کند.
			if (pad) {
				pad.addEventListener('mouseup', refresh);
				pad.addEventListener('touchend', refresh);
				on(pad, 'click', refresh);
			}

			if (otpBtn) {
				on(otpBtn, 'click', function () {
					var id = box.dataset.id;

					if (!id || id === '0') {
						Toast.show('ابتدا پیش‌نویس قرارداد را بسازید.', 'error');
						return;
					}

					otpBtn.disabled = true;

					ajax('zc_contract_otp', { id: id }).then(function (res) {
						if (!res.success) {
							otpBtn.disabled = false;
							Toast.show(res.data.message || 'خطا', 'error');
							return;
						}

						Toast.show(res.data.message, 'success');

						// شمارش معکوس ارسال دوباره.
						var left = res.data.wait || 120;

						var tick = setInterval(function () {
							left--;

							if (timer) {
								timer.textContent = 'ارسال دوباره تا ' + faNum(left) + ' ثانیه‌ی دیگر';
							}

							if (left <= 0) {
								clearInterval(tick);
								otpBtn.disabled = false;

								if (timer) { timer.textContent = 'می‌توانید دوباره کد دریافت کنید.'; }
							}
						}, 1000);
					}).catch(function () {
						otpBtn.disabled = false;
						Toast.show('خطا در ارتباط با سرور', 'error');
					});
				});
			}

			if (submit) {
				on(submit, 'click', function () {
					var id = box.dataset.id;
					var sig = pad && pad.zcGetSignature ? pad.zcGetSignature() : '';

					submit.disabled = true;
					submit.classList.add('is-loading');

					ajax('zc_contract_sign', {
						id: id,
						code: codeIn ? codeIn.value : '',
						signature: sig
					}).then(function (res) {
						submit.classList.remove('is-loading');

						if (!res.success) {
							submit.disabled = false;

							if (msg) {
								msg.textContent = res.data.message || 'خطا';
								msg.className = 'zc-form-msg is-error';
							}

							return;
						}

						if (msg) {
							msg.textContent = res.data.message;
							msg.className = 'zc-form-msg is-success';
						}

						Toast.show(res.data.message, 'success');

						setTimeout(function () {
							window.location.href = res.data.redirect;
						}, 1200);
					}).catch(function () {
						submit.disabled = false;
						submit.classList.remove('is-loading');
						Toast.show('خطا در ارتباط با سرور', 'error');
					});
				});
			}
		});
	}

	/** تبدیل عدد به فارسی. */
	function faNum(n) {
		return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
	}

	/**
	 * گفتگوی اختصاصی قرارداد.
	 */
	function initContractChat() {
		$$('[data-zc-cchat]').forEach(function (root) {
			if (!claimInit(root, 'cchat')) { return; }

			var body = $('[data-zc-cchat-body]', root);
			var form = $('[data-zc-cchat-form]', root);
			var input = $('[data-zc-cchat-input]', root);
			var file = $('[data-zc-cchat-file]', root);
			var preview = $('[data-zc-cchat-preview]', root);
			var contract = root.dataset.contract;

			var last = 0;
			var busy = false;

			/** ساخت حباب پیام. */
			function bubble(m) {
				var wrap = document.createElement('div');
				wrap.className = 'zc-cmsg zc-cmsg--' + m.sender;

				var html = '<div class="zc-cmsg__in">';

				if (m.message) { html += '<div class="zc-cmsg__txt">' + m.message + '</div>'; }

				if (m.att && m.att.url) {
					if (m.att.isImage) {
						html += '<a class="zc-cmsg__img" href="' + m.att.url + '" target="_blank" rel="noopener">'
							+ '<img src="' + (m.att.thumb || m.att.url) + '" alt=""></a>';
					} else {
						html += '<a class="zc-cmsg__file" href="' + m.att.url + '" target="_blank" rel="noopener">'
							+ '<span>' + (m.att.name || 'فایل') + '</span></a>';
					}
				}

				html += '<time class="zc-cmsg__time">' + m.time + '</time></div>';
				wrap.innerHTML = html;

				return wrap;
			}

			/** افزودن پیام‌ها به صفحه. */
			function render(list) {
				if (!list.length) { return; }

				var loading = $('.zc-cchat__loading', body);
				if (loading) { loading.remove(); }

				var lastDate = body.dataset.lastDate || '';

				list.forEach(function (m) {
					// جداکننده‌ی تاریخ.
					if (m.date !== lastDate) {
						var sep = document.createElement('div');
						sep.className = 'zc-cchat__date';
						sep.textContent = m.date;
						body.appendChild(sep);
						lastDate = m.date;
					}

					body.appendChild(bubble(m));
					last = Math.max(last, m.id);
				});

				body.dataset.lastDate = lastDate;
				body.scrollTop = body.scrollHeight;
			}

			/** دریافت پیام‌های تازه. */
			function fetchNew() {
				if (busy || document.hidden) { return; }

				busy = true;

				ajax('zc_cchat_fetch', { contract: contract, after: last }).then(function (res) {
					busy = false;

					if (res.success) {
						render(res.data.messages || []);

						if (!last && !(res.data.messages || []).length) {
							var loading = $('.zc-cchat__loading', body);

							if (loading) {
								loading.textContent = 'هنوز پیامی رد و بدل نشده است. نخستین پیام را بفرستید.';
							}
						}
					}
				}).catch(function () { busy = false; });
			}

			fetchNew();

			// به‌روزرسانی خودکار هر ۱۲ ثانیه.
			var poll = setInterval(fetchNew, 12000);

			window.addEventListener('beforeunload', function () { clearInterval(poll); });

			// پیش‌نمایش پیوست
			if (file && preview) {
				file.addEventListener('change', function () {
					var f = this.files && this.files[0];

					if (!f) { preview.hidden = true; return; }

					preview.hidden = false;
					preview.innerHTML = '<span>' + f.name + '</span>'
						+ '<button type="button" class="zc-cchat__rm">✕</button>';

					$('.zc-cchat__rm', preview).addEventListener('click', function () {
						file.value = '';
						preview.hidden = true;
						preview.innerHTML = '';
					});
				});
			}

			// ارسال با Enter، خط جدید با Shift+Enter
			if (input) {
				input.addEventListener('keydown', function (e) {
					if (e.key === 'Enter' && !e.shiftKey) {
						e.preventDefault();

						if (form) { form.dispatchEvent(new Event('submit', { cancelable: true })); }
					}
				});

				// ارتفاع خودکار
				input.addEventListener('input', function () {
					this.style.height = 'auto';
					this.style.height = Math.min(this.scrollHeight, 130) + 'px';
				});
			}

			if (form) {
				on(form, 'submit', function (e) {
					e.preventDefault();

					var text = input ? input.value.trim() : '';
					var f = file && file.files ? file.files[0] : null;

					if (!text && !f) { return; }

					var payload = { contract: contract, message: text };

					if (f) { payload.file = f; }

					if (input) {
						input.value = '';
						input.style.height = 'auto';
					}

					if (preview) { preview.hidden = true; preview.innerHTML = ''; }
					if (file) { file.value = ''; }

					ajax('zc_cchat_send', payload).then(function (res) {
						if (!res.success) {
							Toast.show(res.data.message || 'ارسال ناموفق بود', 'error');
							return;
						}

						render([res.data.message]);
					}).catch(function () {
						Toast.show('خطا در ارسال پیام', 'error');
					});
				});
			}
		});
	}

	/* ==========================================================================
	   کدهای تخفیف
	   ========================================================================== */

	/**
	 * اعمال کد تخفیف با یک کلیک از فهرست کدهای کاربر.
	 */
	function initMyCoupons() {
		$$('[data-zc-apply-coupon]').forEach(function (btn) {
			if (!claimInit(btn, 'mycoupon')) { return; }

			on(btn, 'click', function () {
				var code = btn.dataset.zcApplyCoupon;

				btn.disabled = true;
				btn.textContent = '…';

				ajax('zc_apply_coupon', { code: code }).then(function (res) {
					if (!res.success) {
						btn.disabled = false;
						btn.textContent = 'اعمال';
						Toast.show(res.data.message || 'اعمال نشد', 'error');
						return;
					}

					Toast.show(res.data.message, 'success');

					// بازخوانی برای نمایش جمع تازه‌ی سبد.
					setTimeout(function () { window.location.reload(); }, 700);
				}).catch(function () {
					btn.disabled = false;
					btn.textContent = 'اعمال';
					Toast.show('خطا در ارتباط با سرور', 'error');
				});
			});
		});
	}

	/**
	 * بررسی کد تخفیف خدمات در فرم درخواست پروژه.
	 *
	 * خدمات از سبد ووکامرس رد نمی‌شوند، پس اعتبارسنجی و محاسبه‌ی
	 * تخفیف جداگانه انجام می‌شود.
	 */
	function initServiceCoupon() {
		$$('[data-zc-svc-coupon]').forEach(function (box) {
			if (!claimInit(box, 'svccoupon')) { return; }

			var input = $('input', box);
			var btn = $('.zc-svc-coupon__btn', box);
			var msg = $('.zc-svc-coupon__msg', box);
			var form = box.closest('form');

			if (!input || !btn) { return; }

			on(btn, 'click', function () {
				var code = input.value.trim();

				if (!code) {
					if (msg) {
						msg.textContent = 'کد تخفیف را وارد کنید.';
						msg.className = 'zc-svc-coupon__msg is-err';
					}

					return;
				}

				// شماره‌ی موبایل برای بررسی قفل کد لازم است.
				var mobileField = form ? form.querySelector('[name=mobile]') : null;

				btn.disabled = true;

				ajax('zc_check_service_coupon', {
					code: code,
					mobile: mobileField ? mobileField.value : ''
				}).then(function (res) {
					btn.disabled = false;

					if (msg) {
						msg.textContent = res.data.message;
						msg.className = 'zc-svc-coupon__msg ' + (res.success ? 'is-ok' : 'is-err');
					}

					box.classList.toggle('is-valid', !!res.success);
				}).catch(function () {
					btn.disabled = false;

					if (msg) {
						msg.textContent = 'خطا در ارتباط با سرور';
						msg.className = 'zc-svc-coupon__msg is-err';
					}
				});
			});
		});
	}

	/* ==========================================================================
	   پرداخت مرحله‌ای قرارداد
	   ========================================================================== */

	/**
	 * پرداخت مراحل قرارداد از کیف پول یا درگاه بانکی.
	 */
	function initContractPay() {
		$$('[data-zc-pay]').forEach(function (box) {
			if (!claimInit(box, 'ctpay')) { return; }

			var contract = box.dataset.contract;
			var msg = $('.zc-pay__msg', box);

			/** نمایش پیام زیر مراحل. */
			function say(text, ok) {
				if (!msg) { return; }

				msg.textContent = text;
				msg.className = 'zc-pay__msg ' + (ok ? 'is-ok' : 'is-err');
			}

			/**
			 * اجرای پرداخت.
			 *
			 * @param {HTMLElement} btn    دکمه.
			 * @param {string}      action اکشن آجاکس.
			 * @param {string}      stage  شماره مرحله.
			 */
			function pay(btn, action, stage) {
				// جلوگیری از پرداخت دوباره با کلیک پیاپی.
				if (btn.disabled) { return; }

				var all = $$('button[data-zc-pay-wallet], button[data-zc-pay-gateway]', box);
				all.forEach(function (b) { b.disabled = true; });

				btn.classList.add('is-loading');
				say('', true);

				ajax(action, { contract: contract, stage: stage }).then(function (res) {
					if (!res.success) {
						all.forEach(function (b) { b.disabled = false; });
						btn.classList.remove('is-loading');
						say(res.data.message || 'پرداخت انجام نشد', false);
						Toast.show(res.data.message || 'پرداخت انجام نشد', 'error');
						return;
					}

					// درگاه بانکی: انتقال به صفحه پرداخت.
					if (res.data.redirect) {
						say(res.data.message || 'در حال انتقال…', true);
						window.location.href = res.data.redirect;
						return;
					}

					say(res.data.message, true);
					Toast.show(res.data.message, 'success');

					// کیف پول: بازخوانی برای نمایش وضعیت تازه.
					setTimeout(function () { window.location.reload(); }, 900);
				}).catch(function () {
					all.forEach(function (b) { b.disabled = false; });
					btn.classList.remove('is-loading');
					say('خطا در ارتباط با سرور', false);
				});
			}

			on(box, 'click', function (e) {
				var w = e.target.closest('[data-zc-pay-wallet]');

				if (w) {
					if (!window.confirm('مبلغ این مرحله از کیف پول شما کسر شود؟')) { return; }

					pay(w, 'zc_pay_stage_wallet', w.dataset.zcPayWallet);
					return;
				}

				var g = e.target.closest('[data-zc-pay-gateway]');

				if (g) { pay(g, 'zc_pay_stage_gateway', g.dataset.zcPayGateway); }
			});
		});
	}

	/* ==========================================================================
	   تأیید مبلغ و فسخ قرارداد
	   ========================================================================== */

	/**
	 * تأیید یا اعتراض به مبلغ اعلام‌شده‌ی پروژه.
	 */
	function initContractQuote() {
		$$('[data-zc-quote]').forEach(function (box) {
			if (!claimInit(box, 'ctquote')) { return; }

			var id = box.dataset.contract;
			var agree = $('[data-zc-quote-agree]', box);
			var approve = $('[data-zc-quote-approve]', box);
			var msg = $('.zc-quote__msg', box);
			var openBtn = $('[data-zc-quote-dispute-open]', box);
			var disputeBox = $('[data-zc-dispute-box]', box);
			var disputeBtn = $('[data-zc-quote-dispute]', box);
			var disputeTxt = $('[data-zc-dispute-text]', box);

			function say(text, ok) {
				if (!msg) { return; }

				msg.textContent = text;
				msg.className = 'zc-quote__msg ' + (ok ? 'is-ok' : 'is-err');
			}

			if (agree && approve) {
				on(agree, 'change', function () { approve.disabled = !agree.checked; });
			}

			if (approve) {
				on(approve, 'click', function () {
					if (!window.confirm('مبلغ اعلام‌شده را تأیید می‌کنید؟ پس از تأیید، مراحل پرداخت فعال می‌شود.')) { return; }

					approve.disabled = true;
					approve.classList.add('is-loading');

					ajax('zc_approve_amount', { contract: id }).then(function (res) {
						approve.classList.remove('is-loading');

						if (!res.success) {
							approve.disabled = false;
							say(res.data.message, false);
							return;
						}

						say(res.data.message, true);
						Toast.show(res.data.message, 'success');
						setTimeout(function () { window.location.reload(); }, 1100);
					}).catch(function () {
						approve.disabled = false;
						approve.classList.remove('is-loading');
						say('خطا در ارتباط با سرور', false);
					});
				});
			}

			if (openBtn && disputeBox) {
				on(openBtn, 'click', function () {
					disputeBox.hidden = !disputeBox.hidden;

					if (!disputeBox.hidden && disputeTxt) { disputeTxt.focus(); }
				});
			}

			if (disputeBtn) {
				on(disputeBtn, 'click', function () {
					var reason = disputeTxt ? disputeTxt.value.trim() : '';

					if (reason.length < 10) {
						say('لطفاً دلیل اعتراض را کامل‌تر بنویسید.', false);
						return;
					}

					disputeBtn.disabled = true;

					ajax('zc_dispute_amount', { contract: id, reason: reason }).then(function (res) {
						disputeBtn.disabled = false;
						say(res.data.message, res.success);

						if (res.success) {
							Toast.show(res.data.message, 'success');
							setTimeout(function () { window.location.reload(); }, 1400);
						}
					}).catch(function () {
						disputeBtn.disabled = false;
						say('خطا در ارتباط با سرور', false);
					});
				});
			}
		});
	}

	/**
	 * ثبت درخواست فسخ قرارداد.
	 */
	function initContractTerminate() {
		$$('[data-zc-terminate]').forEach(function (box) {
			if (!claimInit(box, 'ctterm')) { return; }

			var id = box.dataset.contract;
			var reason = $('[data-zc-term-reason]', box);
			var details = $('[data-zc-term-details]', box);
			var confirm = $('[data-zc-term-confirm]', box);
			var submit = $('[data-zc-term-submit]', box);
			var msg = $('.zc-term-form__msg', box);

			/** دکمه فقط با تیک تأیید و توضیح کافی فعال می‌شود. */
			function refresh() {
				if (!submit) { return; }

				var ok = confirm && confirm.checked
					&& details && details.value.trim().length >= 20;

				submit.disabled = !ok;
			}

			if (confirm) { on(confirm, 'change', refresh); }
			if (details) { on(details, 'input', refresh); }

			if (submit) {
				on(submit, 'click', function () {
					if (!window.confirm('درخواست فسخ قرارداد ثبت شود؟ این اقدام به مجری اطلاع داده می‌شود.')) { return; }

					submit.disabled = true;
					submit.classList.add('is-loading');

					ajax('zc_request_termination', {
						contract: id,
						reason: reason ? reason.value : '',
						details: details ? details.value : '',
						confirm: 1
					}).then(function (res) {
						submit.classList.remove('is-loading');

						if (msg) {
							msg.textContent = res.data.message;
							msg.className = 'zc-term-form__msg ' + (res.success ? 'is-ok' : 'is-err');
						}

						if (!res.success) { submit.disabled = false; return; }

						Toast.show(res.data.message, 'success');
						setTimeout(function () { window.location.reload(); }, 1600);
					}).catch(function () {
						submit.disabled = false;
						submit.classList.remove('is-loading');

						if (msg) {
							msg.textContent = 'خطا در ارتباط با سرور';
							msg.className = 'zc-term-form__msg is-err';
						}
					});
				});
			}
		});
	}

	function init() {
		/*
		 * فعال‌سازی انیمیشن‌ها فقط از سمت جاوااسکریپت.
		 * کلاس zc-anim-on باعث opacity:0 روی عناصر data-zc-anim می‌شود.
		 * اگر آن را از سمت سرور (body_class) اضافه می‌کردیم، در صورت اجرا
		 * نشدن JS محتوای صفحات مخفی می‌ماند. حالا فقط وقتی این کلاس
		 * اضافه می‌شود که JS در حال اجراست، و در غیر این صورت محتوا
		 * از ابتدا نمایان است.
		 */
		if (S.animations) { document.body.classList.add('zc-anim-on'); }

		/*
		 * فقط یک بار اجرا شود. init() ممکن است چندین بار (به ازای هر
		 * ویجت المنتور) فراخوانی شود؛ اجرای تکراری باعث اتصال چندگانه‌ی
		 * listener روی آکاردئون و دیگر تعاملات می‌شود.
		 */
		if (document.body.dataset.zcInitDone === '1') return;
		document.body.dataset.zcInitDone = '1';

		initPreloader();
		initStickyHeader();
		initBackToTop();
		initMobileNav();
		initSearch();
		initTabs();
		initAccordion();
		initPortfolioFilter();
		initQuickView();
		initActionButtons();
		initCopyButtons();
		initAnimations();
		initCounters();
		initProgress();
		initSliders();
		initTestimonials();
		initLightbox();
		initShare();
		initChat();
		initAjaxActions();
		initForms();
		initCountdowns();
		initLoadMore();
		initStickyBox();
		initCodeCopy();
		initReadingProgress();
		initQty();
		initPdpGallery();
		initJumpNav();
		initParallax();
		initTilt();
		initScrollFx();
		initAnnouncements();
		initEditors();
		initDropzones();
		initContractWizard();
		initSignaturePad();
		initContractSign();
		initContractChat();
		initMyCoupons();
		initServiceCoupon();
		initContractPay();
		initContractQuote();
		initContractTerminate();
		initQuiz();
	}

	/* ---------- آزمون و تمرین (Quiz / Practice) ----------
	 * سه نوع سوال: چندگزینه‌ای، جای خالی، کد.
	 * دو حالت: گام‌به‌گام (چالشی) و همهٔ سوالات.
	 */
	function readQuestionAnswer(qEl) {
		var type = qEl.dataset.qtype;
		if (type === 'mc') {
			var r = qEl.querySelector('input[type=radio]:checked');
			return r ? parseInt(r.value, 10) : -1;
		}
		if (type === 'code') {
			var ta = qEl.querySelector('.zc-q__textarea');
			return ta ? ta.value : '';
		}
		var inp = qEl.querySelector('.zc-q__input');
		return inp ? inp.value : '';
	}

	function updateChallengeProgress(quiz) {
		var st = quiz.__zcState || { answered: 0, total: 0 };
		var ptext = quiz.querySelector('.zc-challenge__ptext');
		var pbar = quiz.querySelector('.zc-challenge__pbar i');
		if (ptext) ptext.textContent = st.answered + '/' + st.total;
		if (pbar) {
			var pct = st.total ? Math.round((st.answered / st.total) * 100) : 0;
			pbar.style.width = pct + '%';
		}
	}

	function showChallengeFeedback(qEl, res) {
		var fb = qEl.querySelector('.zc-q__feedback');
		if (fb && res && res.msg) fb.innerHTML = res.msg;
	}

	function renderChallengeResult(quiz, st) {
		var stage = quiz.querySelector('.zc-challenge__stage');
		var msg = quiz.querySelector('.zc-challenge__msg');
		var bar = quiz.querySelector('.zc-challenge__progress');
		if (stage) stage.style.display = 'none';
		if (bar) bar.style.display = 'none';
		var pct = st.total ? Math.round((st.correct / st.total) * 100) : 0;
		var passed = pct >= st.pass;
		var icon = passed ? '🎉' : '📘';
		if (msg) msg.innerHTML = '<div class="zc-challenge__result">' +
			'<div class="zc-challenge__result-icon">' + icon + '</div>' +
			'<h3>' + (st.type === 'practice' ? (passed ? 'تمرین کامل شد!' : 'تمرین تمام شد') : (passed ? 'آزمون تمام شد!' : 'آزمون تمام شد')) + '</h3>' +
			'<p>نمره: <strong>' + pct + '٪</strong> (' + st.correct + '/' + st.total + ' پاسخِ درست در اولین تلاش)</p>' +
			'<p class="zc-challenge__result-msg">' + st.message + '</p>' +
			'</div>';
	}

	function checkQuestion(quiz, qEl, codeOnly) {
		var st = quiz.__zcState || (quiz.__zcState = { correct: 0, answered: 0, total: parseInt(quiz.dataset.qcount, 10) || 0, type: quiz.dataset.type, pass: parseInt(quiz.dataset.pass, 10) || 60 });
		var qi = qEl.dataset.qi;
		var answer = readQuestionAnswer(qEl);

		if (qEl.dataset.qtype === 'blank' && !answer) { Toast.show(I18N.error, 'error'); return; }
		if (qEl.dataset.qtype === 'mc' && answer === -1) { Toast.show('یک گزینه را انتخاب کنید.', 'error'); return; }

		var btn = qEl.querySelector('.zc-q__submit') || qEl.querySelector('.zc-q__checkcode');
		if (btn) btn.classList.add('is-loading');

		ajax('zc_quiz_check', { type: st.type, id: quiz.dataset.id, qi: qi, answer: answer }).then(function (res) {
			if (btn) btn.classList.remove('is-loading');
			if (!res.success) {
				var fb = qEl.querySelector('.zc-q__feedback');
				if (fb) fb.innerHTML = '<div class="zc-alert zc-alert--error">' + (res.data && res.data.message || I18N.error) + '</div>';
				return;
			}
			showChallengeFeedback(qEl, res.data);
			if (!res.data.correct) {
				// اشتباه بود: تلاش اولِ این سوال مصرف شد.
				qEl.dataset.tried = '1';
				return;
			}
			// درست بود.
			if (!qEl.dataset.tried) st.correct++;
			st.answered++;

			if (res.data.done) {
				renderChallengeResult(quiz, st);
				ajax('zc_quiz_finish', { type: st.type, id: quiz.dataset.id, first_correct: st.correct, total: st.total }).then(function (fr) {
					if (fr.success) {
						var msg = quiz.querySelector('.zc-challenge__msg .zc-challenge__result-msg');
						if (msg) msg.textContent = fr.data.message;
						Toast.show(fr.data.message, fr.data.passed ? 'success' : 'info');
					}
				});
				return;
			}
			// سوال بعدی.
			var stage = quiz.querySelector('.zc-challenge__stage');
			if (stage && res.data.next_html) {
				stage.innerHTML = res.data.next_html;
				updateChallengeProgress(quiz);
				maybeAutoRunCode(quiz);
			}
		});
	}

	function runQuestionCode(qEl) {
		var ta = qEl.querySelector('.zc-q__textarea');
		var sel = qEl.querySelector('.zc-q__langsel');
		var out = qEl.querySelector('.zc-q__output');
		if (!ta || !out) return;
		var lang = sel ? sel.value : ta.dataset.lang;
		var stdinEl = qEl.querySelector('.zc-q__stdin');
		var stdin = stdinEl ? stdinEl.value : '';
		var btn = qEl.querySelector('.zc-q__run');
		if (btn) btn.classList.add('is-loading');
		out.hidden = false;
		out.textContent = 'در حال اجرا…';
		ajax('zc_quiz_run', { language: lang, code: ta.value, stdin: stdin }).then(function (res) {
			if (btn) btn.classList.remove('is-loading');
			if (res.success) {
				out.textContent = res.data.output !== '' ? res.data.output : (res.data.error ? 'خطا:\n' + res.data.error : '(خروجی خالی)');
				if (!res.data.ok && res.data.error) out.textContent = 'خطا:\n' + res.data.error;
			} else {
				out.textContent = (res.data && res.data.message) || I18N.error;
			}
		});
	}

	/**
	 * اجرای خودکار کد اگر در تنظیمات فعال باشد (فقط برای سوال فعال در حالت چالشی).
	 */
	function maybeAutoRunCode(quiz) {
		if (quiz.dataset.autorun !== '1') return;
		var stage = quiz.querySelector('.zc-challenge__stage');
		if (!stage || stage.style.display === 'none') return;
		var q = stage.querySelector('.zc-q--code');
		if (q) setTimeout(function () { runQuestionCode(q); }, 300);
	}

	function submitQuizAll(quiz, btn) {
		var st = { type: quiz.dataset.type, total: parseInt(quiz.dataset.qcount, 10) || 0 };
		var answers = [];
		quiz.querySelectorAll('.zc-quiz__all .zc-q').forEach(function (qEl) {
			answers.push(readQuestionAnswer(qEl));
		});
		btn.classList.add('is-loading');
		ajax('zc_quiz_submit', { course_id: quiz.dataset.id, answers: JSON.stringify(answers) }).then(function (res) {
			btn.classList.remove('is-loading');
			var msg = quiz.querySelector('.zc-quiz__msg');
			if (res.success) {
				var html = '<div class="zc-alert zc-alert--' + (res.data.passed ? 'success' : 'error') + '">'
					+ '<strong>' + (res.data.passed ? '✓ ' : '✗ ') + res.data.message + '</strong> '
					+ 'نمره: ' + res.data.score + '٪ (' + res.data.correct + '/' + res.data.total + ')</div>';
				if (msg) msg.innerHTML = html;
				Toast.show(res.data.message, res.data.passed ? 'success' : 'error');
				setTimeout(function () { location.reload(); }, res.data.passed ? 1200 : 1800);
			} else {
				if (msg) msg.innerHTML = '<div class="zc-alert zc-alert--error">' + (res.data && res.data.message || I18N.error) + '</div>';
			}
		});
	}

	function initQuiz() {
		$$('[data-quiz]').forEach(function (quiz) {
			// تعویض حالت گام‌به‌گام / همهٔ سوالات.
			quiz.querySelectorAll('.zc-quiz__mode').forEach(function (mbtn) {
				mbtn.addEventListener('click', function () {
					var mode = mbtn.dataset.mode;
					quiz.querySelectorAll('.zc-quiz__mode').forEach(function (x) { x.classList.toggle('is-active', x === mbtn); });
					var ch = quiz.querySelector('.zc-challenge');
					var al = quiz.querySelector('.zc-quiz__all');
					var sb = quiz.querySelector('.zc-quiz__submitall');
					if (ch) ch.hidden = (mode !== 'challenge');
					if (al) al.hidden = (mode !== 'all');
					if (sb) sb.hidden = (mode !== 'all');
				});
			});

			// رویدادها به‌صورت واگذارشده (delegated) برای عناصر پویا.
			quiz.addEventListener('click', function (e) {
				var runBtn = e.target.closest('.zc-q__run');
				if (runBtn) { runQuestionCode(runBtn.closest('.zc-q')); return; }
				var checkBtn = e.target.closest('.zc-q__submit, .zc-q__checkcode');
				if (checkBtn) { checkQuestion(quiz, checkBtn.closest('.zc-q')); return; }
			});
			quiz.addEventListener('input', function (e) {
				var sel = e.target.closest('.zc-q__langsel');
				if (sel && sel.closest('.zc-q')) {
					var ta = sel.closest('.zc-q').querySelector('.zc-q__textarea');
					if (ta) ta.dataset.lang = sel.value;
				}
			});

			var sbtn = quiz.querySelector('[data-zc-quiz-submit]');
			if (sbtn) sbtn.addEventListener('click', function () { submitQuizAll(quiz, sbtn); });

			// اجرای خودکار سوال اول اگر تنظیم شده باشد.
			maybeAutoRunCode(quiz);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// API عمومی برای المنتور و توسعه‌دهندگان
	window.ZCTheme = {
		ajax: ajax,
		toast: Toast.show.bind(Toast),
		refresh: init,
		$: $, $$: $$,
		debounce: debounce,
		throttle: throttle
	};

	// سازگاری با پیش‌نمایش المنتور
	if (window.elementorFrontend) {
		window.addEventListener('elementor/frontend/init', function () {
			elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
				setTimeout(init, 100);
			});
		});
	}
})();
