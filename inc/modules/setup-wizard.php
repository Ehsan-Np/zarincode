<?php
/**
 * ویزارد راه‌اندازی اولیه.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * منو.
 *
 * @return void
 */
function zc_wizard_menu() {
	add_submenu_page( 'zarincode', __( 'راه‌اندازی سریع', 'zarincode' ), __( 'راه‌اندازی', 'zarincode' ), 'manage_options', 'zarincode-wizard', 'zc_wizard_page' );
}
add_action( 'admin_menu', 'zc_wizard_menu', 12 );

/**
 * اعلان ویزارد برای نصب تازه.
 *
 * @return void
 */
function zc_wizard_notice() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'zc_wizard_done' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'zarincode_page_zarincode-wizard' === $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p>%s <a class="button button-primary" href="%s">%s</a></p></div>',
		esc_html__( 'نصب زرین کد تقریباً آماده است. ویزارد راه‌اندازی را کامل کنید.', 'zarincode' ),
		esc_url( admin_url( 'admin.php?page=zarincode-wizard' ) ),
		esc_html__( 'شروع راه‌اندازی', 'zarincode' )
	);
}
add_action( 'admin_notices', 'zc_wizard_notice' );

/**
 * ذخیره مرحله.
 *
 * @return void
 */
function zc_wizard_save() {
	if ( empty( $_POST['zc_wizard_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zc_wizard_nonce'] ) ), 'zc_wizard' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$opts = get_option( ZC_PREFIX, array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	$map = array(
		'zc_site_name_1'       => 'sanitize_text_field',
		'zc_site_name_2'       => 'sanitize_text_field',
		'zc_phone'             => 'sanitize_text_field',
		'zc_mobile'            => 'sanitize_text_field',
		'zc_email'             => 'sanitize_email',
		'zc_kavenegar_api'     => 'sanitize_text_field',
		'zc_kavenegar_sender'  => 'sanitize_text_field',
		'zc_zarinpal_merchant' => 'sanitize_text_field',
		'zc_admin_login_secret'=> 'sanitize_text_field',
	);
	foreach ( $map as $key => $cb ) {
		if ( isset( $_POST[ $key ] ) ) {
			$opts[ $key ] = call_user_func( $cb, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore
		}
	}
	$opts['zc_sms_enable']     = ! empty( $_POST['zc_sms_enable'] );
	$opts['zc_wallet_enable']  = ! empty( $_POST['zc_wallet_enable'] );
	$opts['zc_pwa_enable']     = ! empty( $_POST['zc_pwa_enable'] );
	$opts['zc_dark_enable']    = ! empty( $_POST['zc_dark_enable'] );
	update_option( ZC_PREFIX, $opts );

	if ( ! empty( $_POST['zc_create_pages'] ) ) {
		zc_wizard_ensure_pages();
	}
	if ( ! empty( $_POST['zc_wizard_finish'] ) ) {
		update_option( 'zc_wizard_done', 1, false );
	}
	add_settings_error( 'zc_wizard', 'saved', __( 'تنظیمات ذخیره شد.', 'zarincode' ), 'updated' );
}
add_action( 'admin_init', 'zc_wizard_save' );

/**
 * ساخت برگه‌های ضروری.
 *
 * @return void
 */
function zc_wizard_ensure_pages() {
	$needed = array(
		'panel' => array( __( 'پنل کاربری', 'zarincode' ), 'templates/template-panel.php' ),
		'login' => array( __( 'ورود و ثبت‌نام', 'zarincode' ), 'templates/template-login.php' ),
	);
	$opts = get_option( ZC_PREFIX, array() );
	foreach ( $needed as $key => $cfg ) {
		$opt_key = 'login' === $key ? 'zc_login_page' : 'zc_panel_page';
		if ( ! empty( $opts[ $opt_key ] ) && get_post( $opts[ $opt_key ] ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $cfg[0],
				'post_name'    => $key,
				'post_content' => '',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $cfg[1] );
			$opts[ $opt_key ] = $id;
		}
	}
	update_option( ZC_PREFIX, $opts );
}

/**
 * صفحه ویزارد.
 *
 * @return void
 */
function zc_wizard_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	settings_errors( 'zc_wizard' );
	$step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : 'brand'; // phpcs:ignore
	$steps = array(
		'brand'   => __( 'هویت برند', 'zarincode' ),
		'connect' => __( 'پیامک و پرداخت', 'zarincode' ),
		'pages'   => __( 'صفحات و امنیت', 'zarincode' ),
	);
	if ( ! isset( $steps[ $step ] ) ) {
		$step = 'brand';
	}
	?>
	<div class="wrap zc-admin-wrap">
		<h1><?php esc_html_e( 'راه‌اندازی سریع زرین کد', 'zarincode' ); ?></h1>
		<ol>
			<?php foreach ( $steps as $key => $label ) : ?>
				<li<?php echo $key === $step ? ' style="font-weight:700"' : ''; ?>>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-wizard&step=' . $key ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
		<form method="post">
			<?php wp_nonce_field( 'zc_wizard', 'zc_wizard_nonce' ); ?>
			<?php if ( 'brand' === $step ) : ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'نام سایت (بخش اول)', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_site_name_1" value="<?php echo esc_attr( zc_opt( 'zc_site_name_1', 'زرین' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'نام سایت (بخش دوم)', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_site_name_2" value="<?php echo esc_attr( zc_opt( 'zc_site_name_2', 'کد' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'تلفن', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_phone" value="<?php echo esc_attr( zc_opt( 'zc_phone' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'موبایل / واتساپ', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_mobile" value="<?php echo esc_attr( zc_opt( 'zc_mobile' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_email" type="email" value="<?php echo esc_attr( zc_opt( 'zc_email' ) ); ?>"></td></tr>
				</table>
				<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-wizard&step=connect' ) ); ?>"><?php esc_html_e( 'بعدی', 'zarincode' ); ?></a>
				<button class="button"><?php esc_html_e( 'ذخیره این مرحله', 'zarincode' ); ?></button></p>
			<?php elseif ( 'connect' === $step ) : ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'فعال‌سازی پیامک', 'zarincode' ); ?></th><td><label><input type="checkbox" name="zc_sms_enable" value="1" <?php checked( zc_opt( 'zc_sms_enable', true ) ); ?>> <?php esc_html_e( 'کاوه‌نگار', 'zarincode' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'API کاوه‌نگار', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_kavenegar_api" type="password" value="<?php echo esc_attr( zc_opt( 'zc_kavenegar_api' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'شماره فرستنده', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_kavenegar_sender" value="<?php echo esc_attr( zc_opt( 'zc_kavenegar_sender' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'مرچنت زرین‌پال', 'zarincode' ); ?></th><td><input class="regular-text" name="zc_zarinpal_merchant" value="<?php echo esc_attr( zc_opt( 'zc_zarinpal_merchant' ) ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'کیف پول', 'zarincode' ); ?></th><td><label><input type="checkbox" name="zc_wallet_enable" value="1" <?php checked( zc_opt( 'zc_wallet_enable', true ) ); ?>> <?php esc_html_e( 'فعال', 'zarincode' ); ?></label></td></tr>
				</table>
				<p><button class="button button-primary"><?php esc_html_e( 'ذخیره و ادامه', 'zarincode' ); ?></button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-wizard&step=pages' ) ); ?>"><?php esc_html_e( 'بعدی', 'zarincode' ); ?></a></p>
			<?php else : ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'ساخت برگه ورود و پنل', 'zarincode' ); ?></th><td><label><input type="checkbox" name="zc_create_pages" value="1" checked> <?php esc_html_e( 'اگر وجود ندارند ساخته شوند', 'zarincode' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'کلید درِ پشتی wp-login', 'zarincode' ); ?></th><td>
						<input class="regular-text" name="zc_admin_login_secret" value="<?php echo esc_attr( zc_opt( 'zc_admin_login_secret' ) ); ?>" placeholder="مثلاً یک رشته تصادفی">
						<p class="description"><?php esc_html_e( 'پس از تنظیم، ورود اضطراری فقط با /wp-login.php?zc_admin=کلید شما ممکن است. خالی = غیرفعال.', 'zarincode' ); ?></p>
					</td></tr>
					<tr><th><?php esc_html_e( 'PWA', 'zarincode' ); ?></th><td><label><input type="checkbox" name="zc_pwa_enable" value="1" <?php checked( zc_opt( 'zc_pwa_enable', true ) ); ?>> <?php esc_html_e( 'مانیفست و سرویس‌ورکر', 'zarincode' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'حالت تیره', 'zarincode' ); ?></th><td><label><input type="checkbox" name="zc_dark_enable" value="1" <?php checked( zc_opt( 'zc_dark_enable', true ) ); ?>> <?php esc_html_e( 'دکمه سوییچ در پنل', 'zarincode' ); ?></label></td></tr>
				</table>
				<p><button class="button button-primary" name="zc_wizard_finish" value="1"><?php esc_html_e( 'پایان راه‌اندازی', 'zarincode' ); ?></button></p>
			<?php endif; ?>
		</form>
	</div>
	<?php
}
