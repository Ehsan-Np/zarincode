<?php
/**
 * موتور استفاده‌ی هم‌زمان از چند کد تخفیف
 *
 * ووکامرس به‌صورت بومی اجازه‌ی اعمال چند کوپن روی سبد را می‌دهد، اما
 * پرچم individual_use روی هر کوپن این امکان را می‌بندد. این ماژول:
 *
 *  ۱. سقف تعداد کوپن هم‌زمان را کنترل می‌کند
 *  ۲. مالکیت کوپن‌های اختصاصی را می‌سنجد (کاربر + شماره موبایل)
 *  ۳. سقف مجموع درصد تخفیف را اعمال می‌کند
 *  ۴. برای «خدمات» که از ووکامرس رد نمی‌شوند، کوپن مستقل می‌سازد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   بخش یک: محصولات و دوره‌ها (مسیر ووکامرس)
   ========================================================================== */

/**
 * برداشتن قفل individual_use از کوپن‌های اختصاصی قالب.
 *
 * کوپن‌های دستی مدیر دست‌نخورده می‌مانند؛ فقط کدهای خودکار قالب
 * قابل جمع شدن می‌شوند.
 *
 * @param bool      $individual وضعیت فعلی.
 * @param WC_Coupon $coupon     کوپن.
 * @return bool
 */
function zc_coupon_allow_stacking( $individual, $coupon ) {
	if ( ! zc_opt( 'zc_coupon_stack_enable', true ) ) {
		return $individual;
	}

	$id = $coupon instanceof WC_Coupon ? $coupon->get_id() : 0;

	if ( ! $id ) {
		return $individual;
	}

	// فقط کدهای خودکار قالب.
	if ( ! get_post_meta( $id, '_zc_auto_coupon', true ) ) {
		return $individual;
	}

	$stackable = get_post_meta( $id, '_zc_coupon_stackable', true );

	// پیش‌فرض کدهای قالب: قابل جمع شدن.
	if ( '' === $stackable ) {
		return false;
	}

	return $stackable ? false : true;
}
add_filter( 'woocommerce_coupon_get_individual_use', 'zc_coupon_allow_stacking', 10, 2 );

/**
 * جلوگیری از حذف خودکار کوپن‌های دیگر.
 *
 * وقتی کوپنی individual_use باشد، ووکامرس بقیه را از سبد بیرون
 * می‌اندازد؛ این فیلتر کدهای قابل جمع را از آن فهرست نگه می‌دارد.
 *
 * @param array     $coupons کوپن‌هایی که قرار است حذف شوند.
 * @param WC_Coupon $coupon  کوپن جدید.
 * @return array
 */
function zc_coupon_keep_stackable( $coupons, $coupon = null ) {
	if ( ! zc_opt( 'zc_coupon_stack_enable', true ) || ! is_array( $coupons ) ) {
		return $coupons;
	}

	return array_filter(
		$coupons,
		static function ( $item ) {
			$code = ( $item instanceof WC_Coupon ) ? $item->get_code() : $item;
			$id   = wc_get_coupon_id_by_code( $code );

			if ( ! $id ) {
				return true;
			}

			// کدهای خودکار قالب نباید کنار گذاشته شوند.
			return ! get_post_meta( $id, '_zc_auto_coupon', true );
		}
	);
}
add_filter( 'woocommerce_apply_individual_use_coupon', 'zc_coupon_keep_stackable', 10, 2 );

/**
 * اعتبارسنجی کوپن هنگام اعمال.
 *
 * سه شرط بررسی می‌شود: مالکیت، سقف تعداد و سقف مجموع درصد.
 *
 * @param bool      $valid  وضعیت.
 * @param WC_Coupon $coupon کوپن.
 * @return bool
 * @throws Exception پیام خطا برای نمایش به کاربر.
 */
function zc_coupon_validate( $valid, $coupon ) {
	if ( ! $coupon instanceof WC_Coupon ) {
		return $valid;
	}

	$id = $coupon->get_id();

	if ( ! $id ) {
		return $valid;
	}

	// ۱) مالکیت کد اختصاصی.
	if ( get_post_meta( $id, '_zc_coupon_user', true ) && ! zc_reward_owns_coupon( $id ) ) {
		throw new Exception(
			esc_html__( 'این کد تخفیف اختصاصی کاربر دیگری است و برای حساب شما معتبر نیست.', 'zarincode' )
		);
	}

	// ۲) کدهای مخصوص خدمات در سبد فروشگاه کار نمی‌کنند.
	if ( 'services' === get_post_meta( $id, '_zc_coupon_scope', true ) ) {
		throw new Exception(
			esc_html__( 'این کد فقط برای سفارش خدمات برنامه‌نویسی است، نه خرید محصول.', 'zarincode' )
		);
	}

	if ( ! WC()->cart ) {
		return $valid;
	}

	$applied = WC()->cart->get_applied_coupons();

	// اگر همین کد از قبل هست، بررسی سقف لازم نیست.
	if ( in_array( $coupon->get_code(), $applied, true ) ) {
		return $valid;
	}

	// ۳) سقف تعداد کوپن هم‌زمان.
	$max = (int) zc_opt( 'zc_coupon_max_count', 3 );

	if ( $max > 0 && count( $applied ) >= $max ) {
		throw new Exception(
			sprintf(
				/* translators: %s: تعداد */
				esc_html__( 'حداکثر %s کد تخفیف را می‌توانید هم‌زمان استفاده کنید.', 'zarincode' ),
				esc_html( zc_fa_num( $max ) )
			)
		);
	}

	// ۴) سقف مجموع درصد تخفیف.
	$cap = (int) zc_opt( 'zc_coupon_max_percent', 70 );

	if ( $cap > 0 && 'percent' === $coupon->get_discount_type() ) {
		$total = (float) $coupon->get_amount();

		foreach ( $applied as $code ) {
			$other = new WC_Coupon( $code );

			if ( 'percent' === $other->get_discount_type() ) {
				$total += (float) $other->get_amount();
			}
		}

		if ( $total > $cap ) {
			throw new Exception(
				sprintf(
					/* translators: %s: درصد */
					esc_html__( 'مجموع تخفیف نمی‌تواند بیشتر از %s٪ باشد.', 'zarincode' ),
					esc_html( zc_fa_num( $cap ) )
				)
			);
		}
	}

	return $valid;
}
add_filter( 'woocommerce_coupon_is_valid', 'zc_coupon_validate', 10, 2 );

/**
 * نمایش کدهای تخفیف در دسترس کاربر در صفحه‌ی سبد و تسویه.
 *
 * کاربر کدهایش را فراموش می‌کند؛ نمایش مستقیم آن‌ها نرخ استفاده را
 * به‌شکل چشمگیری بالا می‌برد.
 *
 * @return void
 */
function zc_coupon_show_available() {
	if ( ! is_user_logged_in() || ! zc_opt( 'zc_coupon_show_list', true ) ) {
		return;
	}

	$codes = zc_user_available_coupons();

	if ( ! $codes ) {
		return;
	}

	$applied = WC()->cart ? WC()->cart->get_applied_coupons() : array();
	?>
	<div class="zc-mycoupons">
		<div class="zc-mycoupons__head">
			<?php zc_the_icon( 'tag', 17 ); ?>
			<strong><?php esc_html_e( 'کدهای تخفیف اختصاصی شما', 'zarincode' ); ?></strong>

			<?php if ( zc_opt( 'zc_coupon_stack_enable', true ) ) : ?>
				<span class="zc-mycoupons__badge">
					<?php
					printf(
						/* translators: %s: تعداد */
						esc_html__( 'تا %s کد هم‌زمان', 'zarincode' ),
						esc_html( zc_fa_num( zc_opt( 'zc_coupon_max_count', 3 ) ) )
					);
					?>
				</span>
			<?php endif; ?>
		</div>

		<div class="zc-mycoupons__list">
			<?php foreach ( $codes as $c ) : ?>
				<?php $is_on = in_array( $c['code'], $applied, true ); ?>

				<div class="zc-mycoupon <?php echo $is_on ? 'is-applied' : ''; ?>">
					<span class="zc-mycoupon__percent"><?php echo esc_html( zc_fa_num( $c['amount'] ) ); ?>٪</span>

					<span class="zc-mycoupon__body">
						<code><?php echo esc_html( $c['code'] ); ?></code>
						<em><?php echo esc_html( $c['label'] ); ?></em>
					</span>

					<?php if ( $is_on ) : ?>
						<span class="zc-mycoupon__on"><?php esc_html_e( 'اعمال شد', 'zarincode' ); ?></span>
					<?php else : ?>
						<button type="button" class="zc-mycoupon__btn" data-zc-apply-coupon="<?php echo esc_attr( $c['code'] ); ?>">
							<?php esc_html_e( 'اعمال', 'zarincode' ); ?>
						</button>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart_table', 'zc_coupon_show_available', 5 );
add_action( 'woocommerce_before_checkout_form', 'zc_coupon_show_available', 5 );

/**
 * فهرست کدهای تخفیف معتبر یک کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return array
 */
function zc_user_available_coupons( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id || ! class_exists( 'WC_Coupon' ) ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => '_zc_coupon_user',
					'value' => $user_id,
				),
			),
		)
	);

	$out = array();

	foreach ( $posts as $post ) {
		$coupon = new WC_Coupon( $post->ID );

		// منقضی یا مصرف‌شده را نشان نمی‌دهیم.
		$expires = $coupon->get_date_expires();

		if ( $expires && $expires->getTimestamp() < time() ) {
			continue;
		}

		$limit = $coupon->get_usage_limit();

		if ( $limit && $coupon->get_usage_count() >= $limit ) {
			continue;
		}

		$out[] = array(
			'code'   => $coupon->get_code(),
			'amount' => (int) $coupon->get_amount(),
			'scope'  => (string) get_post_meta( $post->ID, '_zc_coupon_scope', true ),
			'label'  => $coupon->get_description(),
			'expires' => $expires ? zc_fa_num( zc_jalali_date( 'Y/m/d', $expires->getTimestamp() ) ) : '',
		);
	}

	return $out;
}

/**
 * اعمال کد تخفیف با آجاکس.
 *
 * @return void
 */
function zc_ajax_apply_coupon() {
	zc_check_ajax();

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'سبد خرید در دسترس نیست.', 'zarincode' ) ) );
	}

	$code = isset( $_POST['code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';

	if ( ! $code ) {
		wp_send_json_error( array( 'message' => __( 'کد تخفیف را وارد کنید.', 'zarincode' ) ) );
	}

	$result = WC()->cart->apply_coupon( $code );

	if ( ! $result ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();

		$message = ! empty( $notices[0]['notice'] )
			? wp_strip_all_tags( $notices[0]['notice'] )
			: __( 'اعمال کد تخفیف ممکن نبود.', 'zarincode' );

		wp_send_json_error( array( 'message' => $message ) );
	}

	wc_clear_notices();
	WC()->cart->calculate_totals();

	wp_send_json_success(
		array(
			'message' => __( 'کد تخفیف اعمال شد.', 'zarincode' ),
			'total'   => wp_strip_all_tags( WC()->cart->get_total() ),
			'applied' => WC()->cart->get_applied_coupons(),
		)
	);
}
add_action( 'wp_ajax_zc_apply_coupon', 'zc_ajax_apply_coupon' );
add_action( 'wp_ajax_nopriv_zc_apply_coupon', 'zc_ajax_apply_coupon' );

/* ==========================================================================
   بخش دو: خدمات (خارج از ووکامرس)
   ========================================================================== */

/**
 * جدول کوپن‌های خدمات.
 *
 * خدمات از سبد ووکامرس رد نمی‌شوند (فرم درخواست پروژه یک پست
 * zc_request می‌سازد)، پس به سامانه‌ی کوپن مستقل نیاز دارند.
 *
 * @return void
 */
function zc_create_service_coupon_table() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset = $wpdb->get_charset_collate();
	$table   = $wpdb->prefix . 'zc_service_coupons';

	$sql = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		code VARCHAR(32) NOT NULL,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		mobile VARCHAR(20) NOT NULL DEFAULT '',
		percent SMALLINT(3) NOT NULL DEFAULT 0,
		used TINYINT(1) NOT NULL DEFAULT 0,
		request_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		expires_at DATETIME NULL DEFAULT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY code (code),
		KEY user_id (user_id),
		KEY mobile (mobile)
	) {$charset};";

	dbDelta( $sql );
}

/**
 * نام جدول کوپن خدمات.
 *
 * @return string
 */
function zc_service_coupon_table() {
	global $wpdb;

	return $wpdb->prefix . 'zc_service_coupons';
}

/**
 * ساخت کوپن خدمات.
 *
 * @param int $user_id شناسه کاربر.
 * @param int $percent درصد.
 * @param int $days    اعتبار.
 * @return string
 */
function zc_service_coupon_create( $user_id, $percent, $days = 14 ) {
	global $wpdb;

	$user_id = (int) $user_id;
	$percent = max( 1, min( 100, (int) $percent ) );
	$mobile  = zc_user_mobile( $user_id );

	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$table    = zc_service_coupon_table();

	do {
		$code = 'SRV-';

		for ( $i = 0; $i < 5; $i++ ) {
			$code .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE code = %s", $code ) );
	} while ( $exists );

	$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'code'       => $code,
			'user_id'    => $user_id,
			'mobile'     => $mobile,
			'percent'    => $percent,
			'used'       => 0,
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + ( $days * DAY_IN_SECONDS ) ),
			'created_at' => current_time( 'mysql' ),
		),
		array( '%s', '%d', '%s', '%d', '%d', '%s', '%s' )
	);

	return $ok ? $code : '';
}

/**
 * اعتبارسنجی کوپن خدمات.
 *
 * @param string $code   کد.
 * @param string $mobile شماره‌ی واردشده در فرم.
 * @return array|WP_Error
 */
function zc_service_coupon_check( $code, $mobile = '' ) {
	global $wpdb;

	$code  = strtoupper( trim( (string) $code ) );
	$table = zc_service_coupon_table();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE code = %s", $code ) );
	// phpcs:enable

	if ( ! $row ) {
		return new WP_Error( 'not_found', __( 'کد تخفیف یافت نشد.', 'zarincode' ) );
	}

	if ( $row->used ) {
		return new WP_Error( 'used', __( 'این کد تخفیف قبلاً استفاده شده است.', 'zarincode' ) );
	}

	if ( strtotime( $row->expires_at ) < time() ) {
		return new WP_Error( 'expired', __( 'اعتبار این کد تخفیف به پایان رسیده است.', 'zarincode' ) );
	}

	/*
	 * قفل شماره: کد فقط برای همان شماره‌ای که برایش صادر شده معتبر
	 * است — چه کاربر وارد شده باشد چه به‌صورت مهمان فرم را پر کند.
	 */
	$mobile = zc_sanitize_mobile( $mobile );

	if ( $row->mobile && $mobile && $row->mobile !== $mobile ) {
		return new WP_Error( 'mobile', __( 'این کد برای شماره‌ی موبایل دیگری صادر شده است.', 'zarincode' ) );
	}

	if ( $row->user_id && is_user_logged_in() && (int) $row->user_id !== get_current_user_id() ) {
		return new WP_Error( 'owner', __( 'این کد اختصاصی کاربر دیگری است.', 'zarincode' ) );
	}

	return array(
		'id'      => (int) $row->id,
		'code'    => $row->code,
		'percent' => (int) $row->percent,
		'expires' => zc_fa_num( zc_jalali_date( 'Y/m/d', strtotime( $row->expires_at ) ) ),
	);
}

/**
 * مصرف کوپن خدمات.
 *
 * @param string $code       کد.
 * @param int    $request_id شناسه درخواست.
 * @return bool
 */
function zc_service_coupon_use( $code, $request_id ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return (bool) $wpdb->update(
		zc_service_coupon_table(),
		array(
			'used'       => 1,
			'request_id' => (int) $request_id,
		),
		array( 'code' => strtoupper( trim( $code ) ) ),
		array( '%d', '%d' ),
		array( '%s' )
	);
}

/**
 * بررسی آجاکسی کوپن خدمات در فرم درخواست پروژه.
 *
 * @return void
 */
function zc_ajax_check_service_coupon() {
	zc_check_ajax();

	$code   = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';

	$result = zc_service_coupon_check( $code, $mobile );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: درصد */
				__( 'کد معتبر است: %s٪ تخفیف روی هزینه‌ی خدمات', 'zarincode' ),
				zc_fa_num( $result['percent'] )
			),
			'percent' => $result['percent'],
			'expires' => $result['expires'],
		)
	);
}
add_action( 'wp_ajax_zc_check_service_coupon', 'zc_ajax_check_service_coupon' );
add_action( 'wp_ajax_nopriv_zc_check_service_coupon', 'zc_ajax_check_service_coupon' );

/**
 * ثبت کد تخفیف روی درخواست پروژه.
 *
 * @param int   $post_id شناسه درخواست.
 * @param array $data    داده‌های فرم.
 * @return void
 */
function zc_service_request_coupon( $post_id, $data = array() ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	$code = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';

	if ( ! $code ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification
	$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';

	$check = zc_service_coupon_check( $code, $mobile );

	if ( is_wp_error( $check ) ) {
		return;
	}

	update_post_meta( $post_id, '_zc_req_coupon', $check['code'] );
	update_post_meta( $post_id, '_zc_req_discount', $check['percent'] );

	zc_service_coupon_use( $check['code'], $post_id );
}
add_action( 'zc_request_submitted', 'zc_service_request_coupon', 10, 2 );
