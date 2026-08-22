<?php
/**
 * صفحهٔ مدیریت «کامپایلر و زبان‌ها» زرین کد
 * ---------------------------------------------------------------------------
 * این صفحه مدیریت دقیق و کاملِ زبان‌های برنامه‌نویسی سرویس اجرای کد را فراهم
 * می‌کند:
 *  - انتخاب زبان‌های فعال (سراسری) از بین زبان‌های پایه و سفارشی.
 *  - افزودن / ویرایش / حذف زبان‌های سفارشی (نام کامپایلر سرویس، پسوند، کد شروع).
 *
 * تنظیمات این صفحه در همان آرایهٔ گزینهٔ قالب (ZC_PREFIX) ذخیره می‌شود و با
 * پنل تنظیمات (ردوکس یا فالبک) یکپارچه است.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت زیرمنوی «کامپایلر و زبان‌ها».
 *
 * @return void
 */
function zc_code_manager_menu() {
	add_submenu_page(
		'zarincode',
		__( 'کامپایلر و زبان‌ها', 'zarincode' ),
		__( 'کامپایلر و زبان‌ها', 'zarincode' ),
		'manage_options',
		'zarincode-code',
		'zc_code_manager_page'
	);
}
add_action( 'admin_menu', 'zc_code_manager_menu' );

/**
 * ذخیره‌سازی تنظیمات صفحه.
 *
 * @return void
 */
function zc_code_manager_save() {
	if ( empty( $_POST['zc_code_manager'] ) ) { // phpcs:ignore
		return;
	}
	check_admin_referer( 'zc_code_manager', 'zc_code_manager_nonce' );

	$opts = get_option( ZC_PREFIX, array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	// زبان‌های فعال سراسری.
	$enabled = isset( $_POST['zc_code_enabled_langs'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['zc_code_enabled_langs'] ) ) : array(); // phpcs:ignore
	$opts['zc_quiz_enabled_langs'] = array_values( array_filter( $enabled ) );

	// زبان‌های سفارشی.
	$custom = array();
	if ( ! empty( $_POST['zc_code_custom'] ) && is_array( $_POST['zc_code_custom'] ) ) { // phpcs:ignore
		foreach ( wp_unslash( $_POST['zc_code_custom'] ) as $row ) { // phpcs:ignore
			$label    = sanitize_text_field( $row['label'] ?? '' );
			$compiler = sanitize_text_field( $row['compiler'] ?? '' );
			if ( '' === $label || '' === $compiler ) {
				continue;
			}
			$key = sanitize_key( $row['key'] ?? '' );
			if ( '' === $key ) {
				$key = sanitize_key( $label );
			}
			if ( '' === $key ) {
				continue;
			}
			// اطمینان از یکتا بودن کلید.
			while ( isset( $custom[ $key ] ) || isset( zc_quiz_base_langs()[ $key ] ) ) {
				$key = $key . 'x';
			}
			$custom[ $key ] = array(
				'key'      => $key,
				'label'    => $label,
				'compiler' => $compiler,
				'ext'      => sanitize_key( $row['ext'] ?? 'txt' ) ? sanitize_key( $row['ext'] ?? 'txt' ) : 'txt',
				'starter'  => (string) ( $row['starter'] ?? '' ),
			);
		}
	}
	$opts['zc_quiz_custom_langs'] = array_values( $custom );

	update_option( ZC_PREFIX, $opts );

	add_settings_error(
		'zc_code_manager',
		'zc_code_saved',
		__( 'تنظیمات کامپایلر و زبان‌ها ذخیره شد.', 'zarincode' ),
		'updated'
	);
}
add_action( 'admin_init', 'zc_code_manager_save' );

/**
 * رندر صفحهٔ مدیریت.
 *
 * @return void
 */
function zc_code_manager_page() {
	$opts     = wp_parse_args( get_option( ZC_PREFIX, array() ), zc_default_options() );
	$defs     = zc_quiz_language_defs();
	$enabled  = zc_quiz_enabled_langs();
	$custom   = zc_quiz_custom_langs();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'کامپایلر و زبان‌ها', 'zarincode' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'زبان‌های برنامه‌نویسی قابل‌اجرا در سوالات کدنویسی و بخش تمرین را مدیریت کنید. نام کامپایلر باید با سرویس اجرای کد (پیش‌فرض Wandbox) سازگار باشد.', 'zarincode' ); ?>
			<?php
			printf(
				'<a href="%s" class="button button-small" style="margin-inline-start:8px">%s</a>',
				esc_url( admin_url( 'themes.php?page=zarincode-options' ) ),
				esc_html__( 'بازکردن تنظیمات قالب', 'zarincode' )
			);
			?>
		</p>

		<?php settings_errors( 'zc_code_manager' ); ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'zc_code_manager', 'zc_code_manager_nonce' ); ?>
			<input type="hidden" name="zc_code_manager" value="1">

			<h2 style="margin-top:20px"><?php esc_html_e( '۱) زبان‌های فعال (سراسری)', 'zarincode' ); ?></h2>
			<p class="description"><?php esc_html_e( 'این زبان‌ها در انتخابِ «زبان‌های مجاز» هر دوره و هر تمرین در دسترس مدیر خواهند بود و فقط همین‌ها در ادیتور کد نمایش داده می‌شوند.', 'zarincode' ); ?></p>
			<table class="widefat striped" style="max-width:760px">
				<thead><tr><th style="width:40px"><?php esc_html_e( 'فعال', 'zarincode' ); ?></th><th><?php esc_html_e( 'زبان', 'zarincode' ); ?></th><th><?php esc_html_e( 'نام کامپایلر (سرویس)', 'zarincode' ); ?></th><th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $defs as $k => $l ) : ?>
					<tr>
						<td><input type="checkbox" name="zc_code_enabled_langs[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( (string) $k, $enabled, true ) ); ?>></td>
						<td><strong><?php echo esc_html( $l['label'] ); ?></strong></td>
						<td><code><?php echo esc_html( $l['compiler'] ); ?></code></td>
						<td><?php echo isset( zc_quiz_base_langs()[ $k ] ) ? esc_html__( 'پایه', 'zarincode' ) : esc_html__( 'سفارشی', 'zarincode' ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:30px"><?php esc_html_e( '۲) زبان‌های سفارشی', 'zarincode' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'برای افزودن زبان دلخواه، ردیف جدید بسازید و «نام کامپایلر» سرویس را وارد کنید. برای زبان‌های Wandbox می‌توانید نام کامپایلر را از فهرست آن‌ها کپی کنید.', 'zarincode' ); ?>
			</p>

			<div id="zc-custom-langs">
				<?php
				$i = 0;
				foreach ( $custom as $c ) {
					zc_code_lang_row( $i, $c );
					$i++;
				}
				?>
			</div>

			<p>
				<button type="button" class="button" id="zc-code-lang-add">+ <?php esc_html_e( 'افزودن زبان سفارشی', 'zarincode' ); ?></button>
			</p>

			<?php submit_button( __( 'ذخیرهٔ همهٔ تغییرات', 'zarincode' ) ); ?>
		</form>
	</div>

	<script type="text/html" id="zc-tpl-code-lang">
		<?php zc_code_lang_row( '__IDX__', array( 'key' => '', 'label' => '', 'compiler' => '', 'ext' => 'txt', 'starter' => '' ) ); ?>
	</script>
	<script>
	(function () {
		var wrap = document.getElementById('zc-custom-langs');
		var addBtn = document.getElementById('zc-code-lang-add');
		var tpl = document.getElementById('zc-tpl-code-lang').innerHTML;
		var idx = <?php echo (int) $i; ?>;
		addBtn.addEventListener('click', function () {
			var html = tpl.replace(/__IDX__/g, idx++).replace(/\n/g, '');
			wrap.insertAdjacentHTML('beforeend', html);
		});
		wrap.addEventListener('click', function (e) {
			if (e.target.classList.contains('zc-code-lang-remove')) e.target.closest('.zc-code-lang').remove();
		});
	})();
	</script>
	<style>
		.zc-code-lang{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px;margin-bottom:10px;max-width:820px}
		.zc-code-lang__fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px}
		.zc-code-lang__fields label{display:block;font-size:.75rem;color:#555}
		.zc-code-lang__fields input,.zc-code-lang__fields textarea{width:100%;margin-top:3px}
	</style>
	<?php
}

/**
 * رندر یک ردیف زبان سفارشی.
 *
 * @param int   $i   ایندکس.
 * @param array $row مقادیر.
 * @return void
 */
function zc_code_lang_row( $i, $row ) {
	?>
	<div class="zc-code-lang">
		<div class="zc-code-lang__fields">
			<label><?php esc_html_e( 'کلید (لاتین، یکتا)', 'zarincode' ); ?>
				<input type="text" name="zc_code_custom[<?php echo esc_attr( $i ); ?>][key]" value="<?php echo esc_attr( $row['key'] ?? '' ); ?>" placeholder="my-lang">
			</label>
			<label><?php esc_html_e( 'نام نمایش', 'zarincode' ); ?>
				<input type="text" name="zc_code_custom[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>" placeholder="My Language">
			</label>
			<label><?php esc_html_e( 'نام کامپایلر سرویس', 'zarincode' ); ?>
				<input type="text" name="zc_code_custom[<?php echo esc_attr( $i ); ?>][compiler]" value="<?php echo esc_attr( $row['compiler'] ?? '' ); ?>" placeholder="my-compiler-v1">
			</label>
			<label><?php esc_html_e( 'پسوند فایل', 'zarincode' ); ?>
				<input type="text" name="zc_code_custom[<?php echo esc_attr( $i ); ?>][ext]" value="<?php echo esc_attr( $row['ext'] ?? 'txt' ); ?>" placeholder="txt">
			</label>
		</div>
		<label style="display:block;margin-top:8px"><?php esc_html_e( 'کد شروع ادیتور (اختیاری)', 'zarincode' ); ?>
			<textarea name="zc_code_custom[<?php echo esc_attr( $i ); ?>][starter]" rows="2" class="large-text code"><?php echo esc_textarea( $row['starter'] ?? '' ); ?></textarea>
		</label>
		<p style="margin:8px 0 0;text-align:left">
			<button type="button" class="button-link-delete zc-code-lang-remove"><?php esc_html_e( 'حذف این زبان', 'zarincode' ); ?></button>
		</p>
	</div>
	<?php
}
