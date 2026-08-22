/**
 * Zarincode Admin JavaScript
 */
(function ($) {
	'use strict';

	$(function () {

		/* ---------- انتخابگر رسانه ---------- */
		$(document).on('click', '.zc-admin-media__btn', function (e) {
			e.preventDefault();
			var btn = $(this);
			var wrap = btn.closest('.zc-admin-media');
			var input = wrap.find('.zc-admin-media__input');
			var preview = wrap.find('.zc-admin-media__preview');

			var frame = wp.media({
				title: 'انتخاب تصویر',
				button: { text: 'استفاده از این تصویر' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				input.val(attachment.url);
				preview.html('<img src="' + attachment.url + '" style="max-width:180px;max-height:70px;margin-top:8px;border-radius:8px">');
			});

			frame.open();
		});

		/* ---------- سازنده سرفصل دوره ---------- */
		var sectionIndex = $('#zc-sections .zc-section-item').length;

		$('#zc-add-section').on('click', function () {
			var html =
				'<div class="zc-section-item" data-index="' + sectionIndex + '">' +
					'<div class="zc-section-item__head">' +
						'<span class="dashicons dashicons-menu zc-drag"></span>' +
						'<input type="text" name="zc_curriculum[' + sectionIndex + '][title]" placeholder="عنوان فصل" class="zc-section-title">' +
						'<button type="button" class="button zc-add-lesson">+ افزودن جلسه</button>' +
						'<button type="button" class="button zc-remove-section"><span class="dashicons dashicons-trash"></span></button>' +
					'</div>' +
					'<div class="zc-lessons-list"></div>' +
				'</div>';
			$('#zc-sections').append(html);
			sectionIndex++;
		});

		$(document).on('click', '.zc-add-lesson', function () {
			var section = $(this).closest('.zc-section-item');
			var si = section.data('index');
			var list = section.find('.zc-lessons-list');
			var li = list.find('.zc-lesson-item').length;

			var html =
				'<div class="zc-lesson-item">' +
					'<span class="dashicons dashicons-video-alt3"></span>' +
					'<input type="text" name="zc_curriculum[' + si + '][lessons][' + li + '][title]" placeholder="عنوان جلسه">' +
					'<input type="text" name="zc_curriculum[' + si + '][lessons][' + li + '][duration]" placeholder="مدت" style="max-width:90px">' +
					'<input type="url" name="zc_curriculum[' + si + '][lessons][' + li + '][video]" placeholder="لینک ویدیو">' +
					'<label class="zc-lesson-free"><input type="checkbox" name="zc_curriculum[' + si + '][lessons][' + li + '][free]" value="1"> رایگان</label>' +
					'<button type="button" class="button zc-remove-lesson"><span class="dashicons dashicons-no-alt"></span></button>' +
				'</div>';
			list.append(html);
		});

		$(document).on('click', '.zc-remove-section', function () {
			if (confirm('این فصل و تمام جلسات آن حذف شود؟')) {
				$(this).closest('.zc-section-item').remove();
			}
		});

		$(document).on('click', '.zc-remove-lesson', function () {
			$(this).closest('.zc-lesson-item').remove();
		});

		/* ---------- نمایش مقدار اسلایدر ---------- */
		$('.zc-admin-range').on('input', function () {
			$(this).next('output').text(this.value);
		});

		/* ---------- نمایش کد رنگ ---------- */
		$('.zc-admin-color').on('input', function () {
			$(this).next('code').text(this.value);
		});

	});
})(jQuery);

	/* ---------- فیلد تکرارشونده (نمادهای اعتماد و ...) ---------- */
	jQuery(function ($) {
		function zcRepeaterRowIndex($rows) {
			return $rows.children('.zc-admin-repeater__row').length;
		}

		function zcRepeaterSyncType($row) {
			var type = $row.find('[data-zc-repeater-type]').val() || 'html';
			$row.find('[data-zc-show]').each(function () {
				var show = String($(this).data('zc-show') || '').split(' ');
				var ok = show.indexOf(type) !== -1;
				$(this).toggle(ok);
				$(this).find('input,select,textarea').prop('disabled', !ok);
			});
		}

		$(document).on('change', '[data-zc-repeater-type]', function () {
			zcRepeaterSyncType($(this).closest('[data-zc-repeater-row]'));
		});

		$(document).on('click', '[data-zc-repeater-add]', function () {
			var $rep = $(this).closest('[data-zc-repeater]');
			var $rows = $rep.find('[data-zc-repeater-rows]');
			var idx = zcRepeaterRowIndex($rows);
			var tplId = 'zc-tpl-' + $rep.data('zc-repeater');
			var tpl = document.getElementById(tplId);
			if (!tpl) return;
			var html = tpl.innerHTML.replace(/{{IDX}}/g, idx);
			var $row = $(html);
			$rows.append($row);
			zcRepeaterSyncType($row);
			// تغییر ایندکس نام فیلدها برای ردیف‌های بعدی
			$rows.children('.zc-admin-repeater__row').each(function (i) {
				$(this).find('[name]').each(function () {
					var n = $(this).attr('name').replace(/\[(\d+)\]/, '[' + i + ']');
					$(this).attr('name', n);
				});
			});
		});

		$(document).on('click', '[data-zc-repeater-remove]', function () {
			if (confirm('این نماد حذف شود؟')) {
				$(this).closest('[data-zc-repeater-row]').remove();
			}
		});

		// همگام‌سازی اولیه
		$('[data-zc-repeater-row]').each(function () { zcRepeaterSyncType($(this)); });
	});
