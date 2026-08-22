<?php
/**
 * ویرایشگر متن غنی (WYSIWYG) سبک برای فرم‌های سمت کاربر
 *
 * بدون هیچ وابستگی خارجی و CDN؛ روی contenteditable مرورگر بنا شده
 * و خروجی آن پیش از ذخیره با wp_kses پاکسازی می‌شود.
 *
 * قابلیت‌ها: درشت، مورب، زیرخط، فهرست نقطه‌ای و شماره‌دار، نقل‌قول،
 * قطعه کد، پیوند، و بارگذاری تصویر با کشیدن یا انتخاب فایل.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * تگ‌های مجاز در متن تیکت و پیام‌های کاربر.
 *
 * @return array
 */
function zc_editor_allowed_html() {
	return array(
		'p'      => array( 'style' => array() ),
		'br'     => array(),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'u'      => array(),
		's'      => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
		'blockquote' => array(),
		'code'   => array(),
		'pre'    => array(),
		'h3'     => array(),
		'h4'     => array(),
		'div'    => array( 'class' => array() ),
		'span'   => array( 'class' => array() ),
		'a'      => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
			'rel'    => array(),
		),
		'img'    => array(
			'src'   => array(),
			'alt'   => array(),
			'width' => array(),
			'height' => array(),
			'class' => array(),
		),
	);
}

/**
 * پاکسازی خروجی ویرایشگر.
 *
 * @param string $html محتوا.
 * @return string
 */
function zc_kses_editor( $html ) {
	return wp_kses( (string) $html, zc_editor_allowed_html() );
}

/**
 * بیشینه‌ی حجم مجاز بارگذاری بر حسب بایت.
 *
 * مقدار تنظیم‌شده در پنل هرگز نباید از سقف واقعی سرور
 * (upload_max_filesize / post_max_size) بیشتر اعلام شود، وگرنه
 * کاربر فایلی انتخاب می‌کند که همیشه رد خواهد شد.
 *
 * @return int
 */
function zc_max_upload_size() {
	$opt    = (int) zc_opt( 'zc_ticket_max_size', 5 ) * MB_IN_BYTES;
	$server = (int) wp_max_upload_size();

	if ( $server > 0 ) {
		return min( $opt, $server );
	}

	return $opt;
}

/**
 * بیشینه‌ی حجم مجاز به صورت متن فارسی (مگابایت).
 *
 * @return string
 */
function zc_max_upload_label() {
	$mb = zc_max_upload_size() / MB_IN_BYTES;

	return zc_fa_num( $mb >= 1 ? round( $mb, 1 ) : round( $mb, 2 ) );
}

/**
 * چاپ ویرایشگر متن غنی.
 *
 * یک textarea پنهان داده‌ی واقعی را نگه می‌دارد تا فرم بدون
 * جاوااسکریپت هم کار کند؛ اگر JS فعال باشد، ناحیه‌ی contenteditable
 * جای آن را می‌گیرد و با هر تغییر، textarea را همگام می‌کند.
 *
 * @param string $name  نام فیلد.
 * @param string $value مقدار اولیه.
 * @param array  $args  تنظیمات (height, placeholder, upload).
 * @return void
 */
function zc_wysiwyg_editor( $name, $value = '', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'height'      => 200,
			'placeholder' => __( 'متن خود را بنویسید…', 'zarincode' ),
			'upload'      => true,
			'id'          => 'zc-ed-' . sanitize_key( $name ) . '-' . wp_rand( 100, 999 ),
		)
	);

	$buttons = array(
		array( 'cmd' => 'bold', 'icon' => 'B', 'label' => __( 'درشت', 'zarincode' ), 'tag' => 'strong' ),
		array( 'cmd' => 'italic', 'icon' => 'I', 'label' => __( 'مورب', 'zarincode' ), 'tag' => 'em' ),
		array( 'cmd' => 'underline', 'icon' => 'U', 'label' => __( 'زیرخط', 'zarincode' ), 'tag' => 'u' ),
		array( 'cmd' => 'insertUnorderedList', 'icon' => '•', 'label' => __( 'فهرست نقطه‌ای', 'zarincode' ) ),
		array( 'cmd' => 'insertOrderedList', 'icon' => '۱.', 'label' => __( 'فهرست شماره‌دار', 'zarincode' ) ),
		array( 'cmd' => 'formatBlock:blockquote', 'icon' => '❝', 'label' => __( 'نقل‌قول', 'zarincode' ) ),
		array( 'cmd' => 'formatBlock:pre', 'icon' => '&lt;/&gt;', 'label' => __( 'کد', 'zarincode' ) ),
		array( 'cmd' => 'createLink', 'icon' => '🔗', 'label' => __( 'پیوند', 'zarincode' ) ),
		array( 'cmd' => 'removeFormat', 'icon' => '✕', 'label' => __( 'پاک‌کردن قالب', 'zarincode' ) ),
	);
	?>
	<div class="zc-editor" data-zc-editor
		data-name="<?php echo esc_attr( $name ); ?>"
		data-max="<?php echo esc_attr( zc_max_upload_size() ); ?>"
		data-max-label="<?php echo esc_attr( zc_max_upload_label() ); ?>">

		<div class="zc-editor__bar" role="toolbar" aria-label="<?php esc_attr_e( 'ابزار ویرایش متن', 'zarincode' ); ?>">
			<?php foreach ( $buttons as $btn ) : ?>
				<button type="button" class="zc-editor__btn"
					data-cmd="<?php echo esc_attr( $btn['cmd'] ); ?>"
					title="<?php echo esc_attr( $btn['label'] ); ?>"
					aria-label="<?php echo esc_attr( $btn['label'] ); ?>">
					<?php echo wp_kses( $btn['icon'], array() ); ?>
				</button>
			<?php endforeach; ?>

			<?php if ( $args['upload'] && is_user_logged_in() ) : ?>
				<span class="zc-editor__sep"></span>

				<button type="button" class="zc-editor__btn zc-editor__btn--img" data-zc-editor-img
					title="<?php esc_attr_e( 'افزودن تصویر', 'zarincode' ); ?>"
					aria-label="<?php esc_attr_e( 'افزودن تصویر', 'zarincode' ); ?>">
					<?php zc_the_icon( 'image', 16 ); ?>
				</button>

				<input type="file" class="zc-editor__file" accept="image/png,image/jpeg,image/gif,image/webp" hidden>
			<?php endif; ?>
		</div>

		<div class="zc-editor__area" contenteditable="true" role="textbox" aria-multiline="true"
			style="min-height:<?php echo (int) $args['height']; ?>px"
			data-placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"><?php echo zc_kses_editor( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

		<textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>"
			class="zc-editor__input" hidden aria-hidden="true"><?php echo esc_textarea( $value ); ?></textarea>

		<div class="zc-editor__foot">
			<span class="zc-editor__hint"><?php esc_html_e( 'می‌توانید تصویر را مستقیم داخل متن بکشید.', 'zarincode' ); ?></span>
			<span class="zc-editor__count" data-zc-editor-count>۰</span>
		</div>
	</div>
	<?php
}

/* ==========================================================================
   بارگذاری تصویر از داخل ویرایشگر
   ========================================================================== */

/**
 * دریافت تصویر ویرایشگر و افزودن به کتابخانه رسانه.
 *
 * @return void
 */
function zc_ajax_editor_upload() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'برای بارگذاری تصویر باید وارد شوید.', 'zarincode' ) ) );
	}

	if ( empty( $_FILES['file'] ) ) {
		wp_send_json_error( array( 'message' => __( 'فایلی دریافت نشد.', 'zarincode' ) ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$file = $_FILES['file'];

	$max = zc_max_upload_size();

	if ( ! empty( $file['size'] ) && $file['size'] > $max ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: %s: مگابایت */
					__( 'حجم تصویر بیش از %s مگابایت است.', 'zarincode' ),
					zc_max_upload_label()
				),
			)
		);
	}

	// فقط تصویر.
	$type = wp_check_filetype( $file['name'] ?? '' );
	$ok   = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );

	if ( ! in_array( strtolower( (string) $type['ext'] ), $ok, true ) ) {
		wp_send_json_error( array( 'message' => __( 'فقط تصویر مجاز است (JPG، PNG، GIF، WebP).', 'zarincode' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$id = media_handle_upload( 'file', 0 );

	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => $id->get_error_message() ) );
	}

	// نشانه‌گذاری برای مدیریت بعدی.
	update_post_meta( $id, '_zc_editor_upload', 1 );

	wp_send_json_success(
		array(
			'id'  => $id,
			'url' => wp_get_attachment_image_url( $id, 'large' ),
			'alt' => get_the_title( $id ),
		)
	);
}
add_action( 'wp_ajax_zc_editor_upload', 'zc_ajax_editor_upload' );
