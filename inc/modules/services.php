<?php
/**
 * سیستم خدمات، نمونه‌کار و درخواست پروژه
 *
 * زرین کد علاوه بر آموزش، خدمات حرفه‌ای هم ارائه می‌دهد:
 * طراحی سایت وردپرس، انجام پروژه‌های برنامه‌نویسی، سئو و بهینه‌سازی
 * محتوا، پشتیبانی و ... این ماژول ساختار داده و منطق آن را مدیریت می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * وضعیت‌های ممکن برای یک درخواست پروژه.
 *
 * @return array
 */
function zc_request_statuses() {
	return apply_filters(
		'zc_request_statuses',
		array(
			'new'         => __( 'جدید', 'zarincode' ),
			'reviewing'   => __( 'در حال بررسی', 'zarincode' ),
			'quoted'      => __( 'قیمت اعلام شد', 'zarincode' ),
			'in_progress' => __( 'در حال انجام', 'zarincode' ),
			'done'        => __( 'تکمیل شده', 'zarincode' ),
			'rejected'    => __( 'رد شده', 'zarincode' ),
		)
	);
}

/**
 * بازه‌های بودجه‌ی پیشنهادی برای فرم درخواست پروژه.
 *
 * @return array
 */
function zc_request_budgets() {
	return apply_filters(
		'zc_request_budgets',
		array(
			'under_5'  => __( 'کمتر از ۵ میلیون تومان', 'zarincode' ),
			'5_15'     => __( '۵ تا ۱۵ میلیون تومان', 'zarincode' ),
			'15_40'    => __( '۱۵ تا ۴۰ میلیون تومان', 'zarincode' ),
			'above_40' => __( 'بیش از ۴۰ میلیون تومان', 'zarincode' ),
			'unknown'  => __( 'هنوز مشخص نیست', 'zarincode' ),
		)
	);
}

/**
 * بازه‌های زمانی تحویل پروژه.
 *
 * @return array
 */
function zc_request_deadlines() {
	return apply_filters(
		'zc_request_deadlines',
		array(
			'urgent'   => __( 'فوری (کمتر از ۲ هفته)', 'zarincode' ),
			'1_month'  => __( 'حدود یک ماه', 'zarincode' ),
			'3_months' => __( 'یک تا سه ماه', 'zarincode' ),
			'flexible' => __( 'انعطاف‌پذیر', 'zarincode' ),
		)
	);
}

/**
 * دریافت بسته‌های قیمتی یک خدمت.
 *
 * ساختار هر بسته: title, price, unit, delivery, features[], popular
 *
 * @param int $service_id شناسه خدمت.
 * @return array
 */
function zc_get_service_packages( $service_id = 0 ) {
	$service_id = $service_id ? (int) $service_id : get_the_ID();
	$packages   = get_post_meta( $service_id, '_zc_packages', true );

	if ( ! is_array( $packages ) ) {
		return array();
	}

	return array_values( array_filter( $packages, static function ( $p ) {
		return ! empty( $p['title'] );
	} ) );
}

/**
 * فهرست خدمات برای استفاده در کشوی انتخاب فرم‌ها.
 *
 * @return array شناسه => عنوان
 */
function zc_services_list() {
	$out   = array();
	$items = get_posts(
		array(
			'post_type'      => 'zc_service',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);

	foreach ( $items as $item ) {
		$out[ $item->ID ] = $item->post_title;
	}

	wp_reset_postdata();

	return $out;
}

/**
 * ثبت درخواست پروژه از سوی کاربر (آجاکس).
 *
 * @return void
 */
function zc_ajax_submit_request() {
	zc_check_ajax();

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$mobile  = zc_sanitize_mobile( wp_unslash( $_POST['mobile'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$service = (int) ( $_POST['service'] ?? 0 );
	$budget  = sanitize_text_field( wp_unslash( $_POST['budget'] ?? '' ) );
	$dead    = sanitize_text_field( wp_unslash( $_POST['deadline'] ?? '' ) );
	$desc    = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );

	if ( ! $name || ! $mobile ) {
		wp_send_json_error( array( 'message' => __( 'نام و شماره موبایل الزامی است.', 'zarincode' ) ) );
	}

	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'zarincode' ) ) );
	}

	if ( mb_strlen( $desc ) < 10 ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً توضیحات پروژه را کامل‌تر بنویسید.', 'zarincode' ) ) );
	}

	$service_title = $service ? get_the_title( $service ) : __( 'نامشخص', 'zarincode' );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'zc_request',
			'post_status'  => 'publish',
			/* translators: 1: نام مشتری 2: عنوان خدمت */
			'post_title'   => sprintf( __( 'درخواست %1$s — %2$s', 'zarincode' ), $name, $service_title ),
			'post_content' => $desc,
			'meta_input'   => array(
				'_zc_req_name'     => $name,
				'_zc_req_mobile'   => $mobile,
				'_zc_req_email'    => $email,
				'_zc_req_service'  => $service,
				'_zc_req_budget'   => $budget,
				'_zc_req_deadline' => $dead,
				'_zc_req_status'   => 'new',
				'_zc_req_user'     => get_current_user_id(),
				'_zc_req_ip'       => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
			),
		)
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'ثبت درخواست انجام نشد. دوباره تلاش کنید.', 'zarincode' ) ) );
	}

	/**
	 * پس از ثبت درخواست پروژه.
	 *
	 * @param int   $post_id شناسه درخواست.
	 * @param array $data    داده‌های فرم.
	 */
	do_action(
		'zc_request_submitted',
		$post_id,
		array(
			'name'    => $name,
			'mobile'  => $mobile,
			'email'   => $email,
			'service' => $service_title,
			'budget'  => $budget,
			'desc'    => $desc,
		)
	);

	wp_send_json_success(
		array(
			'message' => __( 'درخواست شما با موفقیت ثبت شد. کارشناسان ما در کمتر از ۲۴ ساعت با شما تماس می‌گیرند.', 'zarincode' ),
			'id'      => $post_id,
		)
	);
}
add_action( 'wp_ajax_zc_submit_request', 'zc_ajax_submit_request' );
add_action( 'wp_ajax_nopriv_zc_submit_request', 'zc_ajax_submit_request' );

/**
 * اطلاع‌رسانی مدیر پس از ثبت درخواست تازه.
 *
 * @param int   $post_id شناسه.
 * @param array $data    داده‌ها.
 * @return void
 */
function zc_notify_admin_new_request( $post_id, $data ) {
	$admin_email = get_option( 'admin_email' );

	$body = sprintf(
		"درخواست پروژه تازه‌ای در سایت ثبت شد.\n\nنام: %s\nموبایل: %s\nایمیل: %s\nخدمت: %s\n\nتوضیحات:\n%s\n\nمشاهده: %s",
		$data['name'],
		$data['mobile'],
		$data['email'] ? $data['email'] : '—',
		$data['service'],
		$data['desc'],
		admin_url( 'post.php?post=' . $post_id . '&action=edit' )
	);

	wp_mail( $admin_email, __( '[زرین کد] درخواست پروژه جدید', 'zarincode' ), $body );

	// اطلاع‌رسانی در پیام‌رسان‌ها در صورت فعال بودن.
	if ( function_exists( 'zc_messenger_notify_admin' ) ) {
		zc_messenger_notify_admin(
			sprintf(
				"🆕 <b>درخواست پروژه جدید</b>\n\n👤 %s\n📱 %s\n🧰 %s\n\n%s",
				esc_html( $data['name'] ),
				esc_html( $data['mobile'] ),
				esc_html( $data['service'] ),
				esc_html( wp_trim_words( $data['desc'], 40 ) )
			)
		);
	}
}
add_action( 'zc_request_submitted', 'zc_notify_admin_new_request', 10, 2 );

/**
 * ستون‌های سفارشی فهرست درخواست‌ها در پیشخوان.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_request_columns( $cols ) {
	return array(
		'cb'          => $cols['cb'] ?? '',
		'title'       => __( 'عنوان', 'zarincode' ),
		'zc_customer' => __( 'مشتری', 'zarincode' ),
		'zc_service'  => __( 'خدمت', 'zarincode' ),
		'zc_budget'   => __( 'بودجه', 'zarincode' ),
		'zc_status'   => __( 'وضعیت', 'zarincode' ),
		'date'        => __( 'تاریخ', 'zarincode' ),
	);
}
add_filter( 'manage_zc_request_posts_columns', 'zc_request_columns' );

/**
 * محتوای ستون‌های سفارشی درخواست‌ها.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_request_column_content( $col, $id ) {
	switch ( $col ) {
		case 'zc_customer':
			printf(
				'%s<br><a href="tel:%s" dir="ltr">%s</a>',
				esc_html( get_post_meta( $id, '_zc_req_name', true ) ),
				esc_attr( get_post_meta( $id, '_zc_req_mobile', true ) ),
				esc_html( zc_fa_num( get_post_meta( $id, '_zc_req_mobile', true ) ) )
			);
			break;

		case 'zc_service':
			$sid = (int) get_post_meta( $id, '_zc_req_service', true );
			echo $sid ? esc_html( get_the_title( $sid ) ) : '—';
			break;

		case 'zc_budget':
			$budgets = zc_request_budgets();
			$key     = get_post_meta( $id, '_zc_req_budget', true );
			echo esc_html( $budgets[ $key ] ?? '—' );
			break;

		case 'zc_status':
			$statuses = zc_request_statuses();
			$key      = get_post_meta( $id, '_zc_req_status', true );
			$key      = $key ? $key : 'new';
			printf(
				'<span class="zc-req-status zc-req-status--%s">%s</span>',
				esc_attr( $key ),
				esc_html( $statuses[ $key ] ?? $key )
			);
			break;
	}
}
add_action( 'manage_zc_request_posts_custom_column', 'zc_request_column_content', 10, 2 );

/**
 * شورت‌کد فرم درخواست پروژه.
 *
 * نمونه: [zc_request_form service="12" title="سفارش طراحی سایت"]
 *
 * @param array $atts ویژگی‌ها.
 * @return string
 */
function zc_request_form_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'service' => 0,
			'title'   => '',
			'compact' => 'yes',
		),
		$atts,
		'zc_request_form'
	);

	$service_id = (int) $atts['service'];
	$services   = zc_services_list();
	$budgets    = zc_request_budgets();
	$deadlines  = zc_request_deadlines();
	$user       = wp_get_current_user();

	ob_start();
	?>
	<form class="zc-reqform__form zc-reqform__form--inline" data-zc-form="zc_submit_request" novalidate>

		<?php if ( $atts['title'] ) : ?>
			<h3 class="zc-reqform__inline-title"><?php echo esc_html( $atts['title'] ); ?></h3>
		<?php endif; ?>

		<div class="zc-reqform__grid">
			<div class="zc-field">
				<label for="zc-rq-name-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span>*</span></label>
				<input type="text" id="zc-rq-name-<?php echo (int) $service_id; ?>" name="name" required
					value="<?php echo esc_attr( $user->exists() ? $user->display_name : '' ); ?>" />
			</div>

			<div class="zc-field">
				<label for="zc-rq-mobile-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?> <span>*</span></label>
				<input type="tel" id="zc-rq-mobile-<?php echo (int) $service_id; ?>" name="mobile" required dir="ltr"
					inputmode="numeric" maxlength="11" placeholder="09xxxxxxxxx"
					value="<?php echo esc_attr( $user->exists() ? get_user_meta( $user->ID, 'zc_mobile', true ) : '' ); ?>" />
			</div>

			<div class="zc-field">
				<label for="zc-rq-email-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></label>
				<input type="email" id="zc-rq-email-<?php echo (int) $service_id; ?>" name="email" dir="ltr"
					value="<?php echo esc_attr( $user->exists() ? $user->user_email : '' ); ?>" />
			</div>

			<div class="zc-field">
				<label for="zc-rq-service-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'خدمت موردنظر', 'zarincode' ); ?></label>
				<select id="zc-rq-service-<?php echo (int) $service_id; ?>" name="service">
					<?php foreach ( $services as $sid => $title ) : ?>
						<option value="<?php echo (int) $sid; ?>" <?php selected( $service_id, $sid ); ?>>
							<?php echo esc_html( $title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-field">
				<label for="zc-rq-budget-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'بودجه تقریبی', 'zarincode' ); ?></label>
				<select id="zc-rq-budget-<?php echo (int) $service_id; ?>" name="budget">
					<?php foreach ( $budgets as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-field">
				<label for="zc-rq-dead-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'زمان تحویل', 'zarincode' ); ?></label>
				<select id="zc-rq-dead-<?php echo (int) $service_id; ?>" name="deadline">
					<?php foreach ( $deadlines as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-field zc-field--full">
				<label for="zc-rq-desc-<?php echo (int) $service_id; ?>"><?php esc_html_e( 'توضیحات پروژه', 'zarincode' ); ?> <span>*</span></label>
				<textarea id="zc-rq-desc-<?php echo (int) $service_id; ?>" name="description" rows="5" required
					placeholder="<?php esc_attr_e( 'هدف پروژه و امکانات موردنیاز را بنویسید…', 'zarincode' ); ?>"></textarea>
			</div>

			<div class="zc-field zc-field--full">
				<label for="zc-rq-coupon-<?php echo (int) $service_id; ?>">
					<?php esc_html_e( 'کد تخفیف خدمات', 'zarincode' ); ?>
				</label>

				<div class="zc-svc-coupon" data-zc-svc-coupon>
					<input type="text" id="zc-rq-coupon-<?php echo (int) $service_id; ?>" name="coupon"
						dir="ltr" autocomplete="off"
						placeholder="<?php esc_attr_e( 'مثلاً SRV-A7K2M', 'zarincode' ); ?>">

					<button type="button" class="zc-svc-coupon__btn">
						<?php esc_html_e( 'بررسی کد', 'zarincode' ); ?>
					</button>
				</div>

				<span class="zc-svc-coupon__msg"></span>
			</div>
		</div>

		<div class="zc-reqform__actions">
			<button type="submit" class="zc-btn zc-btn--gold zc-btn--lg">
				<?php zc_the_icon( 'send', 18 ); ?>
				<span><?php esc_html_e( 'ارسال درخواست', 'zarincode' ); ?></span>
			</button>

			<p class="zc-reqform__note">
				<?php zc_the_icon( 'shield', 15 ); ?>
				<?php esc_html_e( 'اطلاعات شما محرمانه است.', 'zarincode' ); ?>
			</p>
		</div>

		<div class="zc-form-msg" role="status" aria-live="polite"></div>
	</form>
	<?php

	return ob_get_clean();
}
add_shortcode( 'zc_request_form', 'zc_request_form_shortcode' );

/**
 * خواندن یک فیلد چندخطی خدمت به‌صورت آرایه.
 *
 * فیلدهای «مراحل اجرا»، «پرسش‌های متداول»، «آمار» و «ابزارها» همگی
 * متن چندخطی هستند؛ این تابع آن‌ها را نرمال و به آرایه تبدیل می‌کند.
 * خطوط خالی حذف می‌شوند تا هیچ آیتم توخالی رندر نشود.
 *
 * @param int    $post_id شناسه خدمت.
 * @param string $key     کلید متا.
 * @return array
 */
function zc_service_lines( $post_id, $key ) {
	$raw = get_post_meta( $post_id, $key, true );

	if ( is_array( $raw ) ) {
		return array_values( array_filter( array_map( 'trim', $raw ) ) );
	}

	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return array();
	}

	// مرورگر با CRLF می‌فرستد؛ نرمال می‌کنیم.
	$raw = str_replace( array( "\r\n", "\r" ), "\n", $raw );

	return array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) );
}
