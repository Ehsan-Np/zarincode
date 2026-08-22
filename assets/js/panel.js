/**
 * Zarincode — User Panel JavaScript
 */
(function () {
	'use strict';

	var $  = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	var CFG = window.ZC || {};

	/* ---------- Sidebar toggle (mobile) — الگوی آف‌کنواس ---------- */
	function initSidebar() {
		var toggle = $('.zc-panel__menu-toggle');
		var sidebar = $('#zc-panel-sidebar');
		if (!toggle || !sidebar) return;

		var overlay = document.createElement('div');
		overlay.className = 'zc-panel__overlay';
		document.body.appendChild(overlay);

		function setAria(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			toggle.classList.toggle('is-open', open);
		}

		function open() {
			sidebar.classList.add('is-open');
			overlay.classList.add('is-open');
			document.body.style.overflow = 'hidden';
			setAria(true);
		}

		function close() {
			sidebar.classList.remove('is-open');
			overlay.classList.remove('is-open');
			document.body.style.overflow = '';
			setAria(false);
		}

		toggle.addEventListener('click', function () {
			sidebar.classList.contains('is-open') ? close() : open();
		});
		overlay.addEventListener('click', close);

		// دکمه بستن داخل سربرگ آف‌کنواس
		$$('[data-zc-panel-close]').forEach(function (btn) { btn.addEventListener('click', close); });

		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
		document.addEventListener('click', function (e) {
			if (sidebar.classList.contains('is-open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
				close();
			}
		});
	}

	/* ---------- Quick amount buttons (wallet) ---------- */
	function initQuickAmounts() {
		$$('.zc-quick-amount').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var input = $('#zc-charge-amount');
				if (input) {
					input.value = btn.dataset.amount;
					input.focus();
					$$('.zc-quick-amount').forEach(function (b) { b.style.background = ''; b.style.color = ''; });
					btn.style.background = 'var(--zc-grad-gold)';
					btn.style.color = '#241C05';
				}
			});
		});
	}

	/* ---------- Close ticket ---------- */
	function initTicketActions() {
		$$('[data-zc-close-ticket]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!confirm('آیا از بستن این تیکت مطمئن هستید؟')) return;
				btn.classList.add('is-loading');
				window.zcAjax('zc_close_ticket', { ticket_id: btn.dataset.zcCloseTicket }).then(function (res) {
					btn.classList.remove('is-loading');
					if (res.success) {
						window.zcToast(res.data.message, 'success');
						setTimeout(function () { location.reload(); }, 900);
					} else {
						window.zcToast(res.data.message, 'error');
					}
				});
			});
		});

		// افزودن پاسخ جدید به رشته گفتگو بدون رفرش
		var replyForm = $('form[data-zc-form="zc_reply_ticket"]');
		if (replyForm) {
			replyForm.addEventListener('zc:form:success', function (e) {
				var thread = $('#zc-ticket-thread');
				if (thread && e.detail && e.detail.html) {
					thread.insertAdjacentHTML('beforeend', e.detail.html);
					thread.scrollTop = thread.scrollHeight;
				}
			});
		}
	}

	/* ---------- Cancel booking ---------- */
	function initBookingActions() {
		$$('[data-zc-cancel-booking]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!confirm('آیا از لغو این نوبت مطمئن هستید؟')) return;
				btn.classList.add('is-loading');
				window.zcAjax('zc_cancel_booking', { booking_id: btn.dataset.zcCancelBooking }).then(function (res) {
					btn.classList.remove('is-loading');
					window.zcToast(res.data.message, res.success ? 'success' : 'error');
					if (res.success) setTimeout(function () { location.reload(); }, 800);
				});
			});
		});
	}

	/* ---------- Notifications ---------- */
	function initNotifications() {
		var btn = $('[data-zc-notif]');
		if (!btn) return;
		btn.addEventListener('click', function () {
			window.zcAjax('zc_read_notifications', {}).then(function (res) {
				if (res.success) {
					var badge = btn.querySelector('.zc-hicon__count');
					if (badge) badge.remove();
					$$('.zc-notif.is-unread').forEach(function (n) { n.classList.remove('is-unread'); });
					window.zcToast('همه اعلان‌ها خوانده شد.', 'success');
				}
			});
		});
	}

	/* ---------- Wallet charge redirect ---------- */
	function initWalletForm() {
		var form = $('form[data-zc-form="zc_wallet_charge"]');
		if (!form) return;
		form.addEventListener('zc:form:success', function (e) {
			if (e.detail && e.detail.redirect) {
				window.location.href = e.detail.redirect;
			}
		});
	}

	/* ---------- Number formatting for amount inputs ---------- */
	function initAmountFormat() {
		$$('input[name="amount"]').forEach(function (input) {
			input.addEventListener('input', function () {
				var val = input.value.replace(/[^\d۰-۹]/g, '');
				var fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
				fa.forEach(function (d, i) { val = val.split(d).join(i); });
				if (val) {
					input.value = parseInt(val, 10).toLocaleString('fa-IR');
				}
			});
		});
	}

	/* ---------- Avatar preview ---------- */
	function initAvatarPreview() {
		var input = $('input[name="avatar"]');
		if (!input) return;
		input.addEventListener('change', function () {
			var file = input.files[0];
			if (!file) return;
			if (file.size > 2 * 1024 * 1024) {
				window.zcToast('حجم فایل نباید بیش از ۲ مگابایت باشد.', 'error');
				input.value = '';
				return;
			}
			var img = $('.zc-avatar-upload .zc-avatar img');
			if (img) img.src = URL.createObjectURL(file);
		});
	}

	/* ---------- خرید / تمدید / ارتقا / تنزل اشتراک ---------- */
	function subCouponCode() {
		var inp = $('#zc-sub-coupon');
		return inp ? inp.value.trim() : '';
	}

	function subBuy(planId, giftTo, btn) {
		if (btn && btn.classList.contains('is-loading')) return;
		if (btn) btn.classList.add('is-loading');
		var data = { plan_id: planId, coupon: subCouponCode() };
		if (giftTo) data.gift_to = giftTo;
		window.zcAjax('zc_subscription_buy', data)
			.then(function (res) {
				if (btn) btn.classList.remove('is-loading');
				if (res && res.success) {
					window.zcToast(res.data.message, 'success');
					if (res.data && res.data.redirect) {
						setTimeout(function () { location.href = res.data.redirect; }, 900);
					} else {
						setTimeout(function () { location.reload(); }, 1200);
					}
				} else if (res) {
					window.zcToast(res.data.message, 'error');
				}
			})
			.catch(function () {
				if (btn) btn.classList.remove('is-loading');
				window.zcToast('خطا در ارتباط با سرور', 'error');
			});
	}

	function initSubscription() {
		$$('[data-zc-sub-buy]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				subBuy(btn.dataset.zcSubBuy, '', btn);
			});
		});

		// لغو تنزل
		$$('[data-zc-sub-cancel-downgrade]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (btn.classList.contains('is-loading')) return;
				btn.classList.add('is-loading');
				window.zcAjax('zc_subscription_cancel_downgrade', {}).then(function (res) {
					btn.classList.remove('is-loading');
					if (res && res.success) {
						window.zcToast(res.data.message, 'success');
						setTimeout(function () { location.reload(); }, 800);
					} else if (res) {
						window.zcToast(res.data.message, 'error');
					}
				});
			});
		});
	}

	/* ---------- مودال هدیه ---------- */
	function initGiftModal() {
		var modal = $('#zc-sub-gift-modal');
		if (!modal) return;
		var recipient = $('#zc-sub-gift-recipient');
		var planLine = $('.zc-sub-gift__plan');
		var msg = modal.querySelector('.zc-form-msg');
		var activePlan = null;

		function open(planId, planTitle) {
			activePlan = planId;
			if (planLine) planLine.textContent = planTitle;
			if (recipient) recipient.value = '';
			if (msg) msg.textContent = '';
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			if (recipient) setTimeout(function () { recipient.focus(); }, 80);
		}
		function close() {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}

		$$('[data-zc-sub-gift]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var card = btn.closest('.zc-sub-plan');
				var title = card ? card.querySelector('h3').textContent : '';
				open(btn.dataset.zcSubGift, 'هدیه: ' + title);
			});
		});
		$$('[data-zc-sub-gift-close]').forEach(function (el) { el.addEventListener('click', close); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

		var confirmBtn = $('#zc-sub-gift-confirm');
		if (confirmBtn) {
			confirmBtn.addEventListener('click', function () {
				if (confirmBtn.classList.contains('is-loading')) return;
				if (!recipient || !recipient.value.trim()) {
					window.zcToast('ایمیل یا نام‌کاربری گیرنده را وارد کنید.', 'error');
					return;
				}
				confirmBtn.classList.add('is-loading');
				window.zcAjax('zc_subscription_buy', { plan_id: activePlan, coupon: subCouponCode(), gift_to: recipient.value.trim() })
					.then(function (res) {
						confirmBtn.classList.remove('is-loading');
						if (res && res.success) {
							window.zcToast(res.data.message, 'success');
							close();
							if (res.data && res.data.redirect) {
								setTimeout(function () { location.href = res.data.redirect; }, 900);
							} else {
								setTimeout(function () { location.reload(); }, 1200);
							}
						} else if (res) {
							window.zcToast(res.data.message, 'error');
						}
					})
					.catch(function () { confirmBtn.classList.remove('is-loading'); window.zcToast('خطا', 'error'); });
			});
		}
	}

	function init() {
		initSidebar();
		initQuickAmounts();
		initTicketActions();
		initBookingActions();
		initNotifications();
		initWalletForm();
		initAmountFormat();
		initAvatarPreview();
		initSubscription();
		initGiftModal();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
