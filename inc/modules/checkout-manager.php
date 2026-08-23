<?php
/**
 * مدیر فیلدهای تسویه حساب زرین کد
 * ---------------------------------------------------------------------------
 * امکان شخصی‌سازی، ویرایش، فعال/غیرفعال‌سازی فیلدهای صفحه تسویه حساب ووکامرس
 * را از پیشخوان فراهم می‌کند. همچنین دیزاین زیبا و سریع سبد خرید و تسویه
 * را اعمال می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/** همگام‌سازی اجازهٔ خرید مهمان با پنل قالب. */
add_filter( 'woocommerce_checkout_registration_required', static function ( $required ) {
	return zc_opt( 'zc_checkout_guest', true ) ? $required : true;
} );
add_filter( 'pre_option_woocommerce_enable_guest_checkout', static function ( $pre ) {
	return zc_opt( 'zc_checkout_guest', true ) ? $pre : 'no';
} );

if ( ! zc_is_woo() ) {
	return;
}

/* ==========================================================================
   ۱) فهرست فیلدهای قابل مدیریت تسویه حساب
   ========================================================================== */

/**
 * تعریف فیلدهای قابل مدیریت (هر دو گروه billing و shipping).
 *
 * @return array
 */
function zc_checkout_field_defs() {
	return array(
		// اطلاعات شخصی.
		'billing_first_name' => array( 'label' => __( 'نام', 'zarincode' ), 'group' => 'billing', 'required' => true ),
		'billing_last_name'  => array( 'label' => __( 'نام خانوادگی', 'zarincode' ), 'group' => 'billing', 'required' => true ),
		'billing_phone'      => array( 'label' => __( 'شماره موبایل', 'zarincode' ), 'group' => 'billing', 'required' => true ),
		'billing_email'      => array( 'label' => __( 'ایمیل', 'zarincode' ), 'group' => 'billing', 'required' => true ),
		'billing_company'    => array( 'label' => __( 'نام شرکت', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_country'    => array( 'label' => __( 'کشور', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_state'      => array( 'label' => __( 'استان', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_city'       => array( 'label' => __( 'شهر', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_address_1'  => array( 'label' => __( 'آدرس', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_address_2'  => array( 'label' => __( 'آدرس (تکمیلی)', 'zarincode' ), 'group' => 'billing', 'required' => false ),
		'billing_postcode'   => array( 'label' => __( 'کد پستی', 'zarincode' ), 'group' => 'billing', 'required' => false ),
	);
}

/**
 * تنظیمات فیلدهای ذخیره‌شده کاربر (از گزینه‌ی قالب).
 *
 * @return array
 */
function zc_checkout_field_settings() {
	$saved = (array) zc_opt( 'zc_checkout_fields', array() );
	return $saved;
}

/**
 * بررسی فعال بودن یک فیلد (با در نظر گرفتن تنظیمات ذخیره‌شده).
 *
 * @param string $key کلید فیلد.
 * @return bool
 */
function zc_checkout_field_enabled( $key ) {
	$settings = zc_checkout_field_settings();
	if ( isset( $settings[ $key ]['enabled'] ) ) {
		return (bool) $settings[ $key ]['enabled'];
	}
	// پیش‌فرض: همه فعال.
	return true;
}

/**
 * ثبت زیرمنوی «مدیریت فیلدهای تسویه».
 *
 * @return void
 */
function zc_checkout_manager_menu() {
	add_submenu_page(
		'zarincode',
		__( 'مدیریت فیلدهای تسویه', 'zarincode' ),
		__( 'فیلدهای تسویه', 'zarincode' ),
		'manage_options',
		'zarincode-checkout-fields',
		'zc_checkout_manager_page'
	);
}
add_action( 'admin_menu', 'zc_checkout_manager_menu' );

/**
 * صفحه‌ی مدیریت فیلدهای تسویه.
 *
 * @return void
 */
function zc_checkout_manager_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// ذخیره.
	if ( isset( $_POST['zc_checkout_fields_save'] ) && check_admin_referer( 'zc_checkout_fields_save' ) ) {
		$raw     = isset( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array(); // phpcs:ignore
		$clean   = array();

		foreach ( zc_checkout_field_defs() as $key => $def ) {
			$f = isset( $raw[ $key ] ) ? $raw[ $key ] : array();
			$clean[ $key ] = array(
				'enabled'  => ! empty( $f['enabled'] ) ? 1 : 0,
				'required' => ! empty( $f['required'] ) ? 1 : 0,
				'label'    => sanitize_text_field( $f['label'] ?? $def['label'] ),
				'placeholder' => sanitize_text_field( $f['placeholder'] ?? '' ),
			);
		}

		$options = get_option( ZC_PREFIX, array() );
		$options['zc_checkout_fields'] = $clean;
		update_option( ZC_PREFIX, $options );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'تنظیمات فیلدهای تسویه ذخیره شد.', 'zarincode' ) . '</p></div>';
	}

	$settings = zc_checkout_field_settings();
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div><h1><?php esc_html_e( 'مدیریت فیلدهای صفحه تسویه حساب', 'zarincode' ); ?></h1></div>
		</div>

		<p class="description"><?php esc_html_e( 'فیلدهای فرم تسویه حساب را فعال/غیرفعال کنید و برچسب یا placeholder آن‌ها را ویرایش کنید. با غیرفعال‌کردن فیلدهای غیرضروری، فرآیند پرداخت سریع‌تر می‌شود.', 'zarincode' ); ?></p>

		<form method="post" class="zc-admin-box">
			<?php wp_nonce_field( 'zc_checkout_fields_save' ); ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width:60px"><?php esc_html_e( 'فعال', 'zarincode' ); ?></th>
						<th style="width:60px"><?php esc_html_e( 'الزامی', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'کلید فیلد', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'برچسب', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'Placeholder', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( zc_checkout_field_defs() as $key => $def ) : ?>
						<?php $f = $settings[ $key ] ?? array(); ?>
						<tr>
							<td><input type="checkbox" name="fields[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( ! empty( $f['enabled'] ) || ! isset( $f['enabled'] ), true ); ?>></td>
							<td><input type="checkbox" name="fields[<?php echo esc_attr( $key ); ?>][required]" value="1" <?php checked( ! empty( $f['required'] ) || ( empty( $f ) && $def['required'] ), true ); ?>></td>
							<td><code><?php echo esc_html( $key ); ?></code></td>
							<td><input type="text" name="fields[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $f['label'] ?? $def['label'] ); ?>" class="regular-text"></td>
							<td><input type="text" name="fields[<?php echo esc_attr( $key ); ?>][placeholder]" value="<?php echo esc_attr( $f['placeholder'] ?? '' ); ?>" class="regular-text"></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p style="margin-top:16px">
				<button type="submit" name="zc_checkout_fields_save" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات', 'zarincode' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/* ==========================================================================
   ۲) اعمال تنظیمات فیلدها روی ووکامرس
   ========================================================================== */

/**
 * اعمال تنظیمات روی فیلدهای تسویه.
 *
 * @param array $fields فیلدها.
 * @param string $type  billing|shipping|account.
 * @return array
 */
function zc_apply_checkout_fields( $fields, $type ) {
	$settings = zc_checkout_field_settings();

	foreach ( $fields as $key => $field ) {
		// فقط فیلدهایی که در لیست مدیریتی‌اند.
		if ( ! isset( $settings[ $key ] ) ) {
			continue;
		}
		$s = $settings[ $key ];

		// غیرفعال.
		if ( empty( $s['enabled'] ) ) {
			unset( $fields[ $key ] );
			continue;
		}

		// برچسب و placeholder.
		if ( ! empty( $s['label'] ) ) {
			$fields[ $key ]['label'] = $s['label'];
		}
		if ( isset( $s['placeholder'] ) ) {
			$fields[ $key ]['placeholder'] = $s['placeholder'];
		}

		// الزامی.
		$fields[ $key ]['required'] = ! empty( $s['required'] );

		// فیلدهای الزامی در همه حالات باید فعال بمانند.
		if ( in_array( $key, array( 'billing_first_name', 'billing_last_name', 'billing_phone' ), true ) ) {
			$fields[ $key ]['required'] = true;
		}
	}

	// حذف فیلد یادداشت سفارش اگر فعال باشد.
	if ( zc_opt( 'zc_checkout_remove_order_notes', true ) ) {
		add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
	}

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'zc_apply_checkout_fields', 30, 2 );
add_filter( 'woocommerce_shipping_fields', 'zc_apply_checkout_fields', 30, 2 );

/**
 * حذف نهایی فیلدهای غیرفعال از فیلتر اصلی (اعتبار برای همه بخش‌ها).
 *
 * @param array $fields فیلدها.
 * @return array
 */
function zc_apply_checkout_fields_master( $fields ) {
	$settings = zc_checkout_field_settings();

	foreach ( $fields as $section => $section_fields ) {
		if ( ! is_array( $section_fields ) ) {
			continue;
		}
		foreach ( $section_fields as $key => $field ) {
			if ( isset( $settings[ $key ] ) && empty( $settings[ $key ]['enabled'] ) ) {
				unset( $fields[ $section ][ $key ] );
			}
		}
	}

	// حذف یادداشت سفارش.
	if ( zc_opt( 'zc_checkout_remove_order_notes', true ) ) {
		unset( $fields['order']['order_comments'] );
	}

	// حالت پرداخت سریع: فقط فیلدهای ضروری.
	if ( zc_opt( 'zc_checkout_quick_pay', false ) ) {
		$keep = array( 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_email' );
		if ( isset( $fields['billing'] ) ) {
			foreach ( array_keys( $fields['billing'] ) as $key ) {
				if ( ! in_array( $key, $keep, true ) ) {
					unset( $fields['billing'][ $key ] );
				}
			}
		}
		unset( $fields['shipping'] );
		unset( $fields['order'] );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'zc_apply_checkout_fields_master', 30 );

/**
 * پر کردن خودکار فیلدها از پروفایل کاربر.
 *
 * @param array $fields فیلدها.
 * @return array
 */
function zc_checkout_auto_fill( $fields ) {
	if ( ! zc_opt( 'zc_checkout_auto_fill', true ) || ! is_user_logged_in() ) {
		return $fields;
	}

	$user = wp_get_current_user();
	$zc_map = array(
		'billing_first_name' => 'first_name',
		'billing_last_name'  => 'last_name',
		'billing_email'      => 'user_email',
	);

	foreach ( $zc_map as $field => $meta ) {
		if ( empty( $fields[ $field ]['default'] ) && ! empty( $user->{$meta} ) ) {
			$fields[ $field ]['default'] = $user->{$meta};
		}
	}

	// موبایل از متای قالب.
	if ( empty( $fields['billing_phone']['default'] ) ) {
		$mobile = get_user_meta( $user->ID, 'zc_mobile', true );
		if ( $mobile ) {
			$fields['billing_phone']['default'] = $mobile;
		}
	}

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'zc_checkout_auto_fill', 25 );

/**
 * حذف فیلد کد تخفیف از فرم تسویه (اگر فعال باشد).
 *
 * @return void
 */
function zc_maybe_remove_checkout_coupon() {
	if ( zc_opt( 'zc_checkout_remove_coupon', false ) ) {
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}
}
add_action( 'init', 'zc_maybe_remove_checkout_coupon' );

/**
 * حالت پرداخت سریع: فقط فیلدهای ضروری.
 *
 * @param array $fields فیلدها.
 * @return array
 */
function zc_checkout_quick_pay_fields( $fields ) {
	if ( ! zc_opt( 'zc_checkout_quick_pay', false ) ) {
		return $fields;
	}

	// فقط نام، نام خانوادگی و موبایل.
	$keep = array( 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_email' );

	foreach ( array_keys( $fields ) as $key ) {
		if ( ! in_array( $key, $keep, true ) ) {
			unset( $fields[ $key ] );
		}
	}

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'zc_checkout_quick_pay_fields', 40 );

/* ==========================================================================
   ۳) دیزاین شیک تسویه — هدر، مراحل و کوپن (بدون override فایل)
   ========================================================================== */

/**
 * هدر و نوار مراحل بالای فرم تسویه.
 *
 * @return void
 */
function zc_checkout_header() {
	?>
	<div class="zc-woo-checkout-head">
		<h1><?php echo esc_html( zc_opt( 'zc_checkout_title', 'تسویه حساب' ) ); ?></h1>
		<p><?php echo esc_html( zc_opt( 'zc_checkout_subtitle', 'در کمتر از یک دقیقه سفارش خود را ثبت و پرداخت کنید' ) ); ?></p>
	</div>

	<?php if ( ! WC()->cart->is_empty() ) : ?>
	<div class="zc-woo-steps" aria-hidden="true">
		<span class="step"><span class="dot"></span><?php esc_html_e( 'سبد خرید', 'zarincode' ); ?></span>
		<span class="sep">‹</span>
		<span class="step active"><span class="dot"></span><?php esc_html_e( 'اطلاعات و پرداخت', 'zarincode' ); ?></span>
		<span class="sep">‹</span>
		<span class="step"><span class="dot"></span><?php esc_html_e( 'تکمیل سفارش', 'zarincode' ); ?></span>
	</div>
	<?php endif; ?>
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'zc_checkout_header', 5 );

/**
 * نوار اطمینان (نمادهای پرداخت امن) پایین فرم تسویه.
 *
 * @return void
 */
function zc_checkout_trust() {
	if ( ! is_checkout() || WC()->cart->is_empty() ) {
		return;
	}
	?>
	<div class="zc-checkout-trust">
		<div class="zc-checkout-trust__item"><?php echo zc_icon( 'shield', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'پرداخت امن زرین‌پال', 'zarincode' ); ?></span></div>
		<div class="zc-checkout-trust__item"><?php echo zc_icon( 'lock', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'رمزنگاری SSL', 'zarincode' ); ?></span></div>
		<div class="zc-checkout-trust__item"><?php echo zc_icon( 'refresh', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'بازگشت وجه تا ۷ روز', 'zarincode' ); ?></span></div>
	</div>
	<?php
}
add_action( 'woocommerce_after_checkout_form', 'zc_checkout_trust', 30 );

/**
 * چیدمان دو ستونه در صفحه تسویه: فرم (راست) + خلاصه سفارش (چپ).
 * کلاس zc-checkout-2col به wrapper اضافه می‌شود.
 *
 * @return void
 */
function zc_checkout_open_cols() {
	echo '<div class="zc-checkout-cols"><div class="zc-checkout-main">';
}
add_action( 'woocommerce_checkout_before_customer_details', 'zc_checkout_open_cols', 5 );

/**
 * بستن ستون فرم و شروع ستون خلاصه سفارش.
 *
 * @return void
 */
function zc_checkout_split_cols() {
	echo '</div><div class="zc-order-summary">';
	echo '<div class="zc-order-summary__head"><span class="ic">' . zc_icon( 'cart', 20 ) . '</span><h2>' . esc_html__( 'خلاصه سفارش', 'zarincode' ) . '</h2></div>';
}
	add_action( 'woocommerce_checkout_after_customer_details', 'zc_checkout_split_cols', 5 );

/**
 * صندوق کد تخفیف بالای فرم تسویه (اختیاری، قابل نمایش/پنهان).
 *
 * @return void
 */
function zc_checkout_coupon_box() {
	if ( ! zc_opt( 'zc_checkout_show_coupon', true ) || zc_opt( 'zc_checkout_remove_coupon', false ) ) {
		return;
	}
	?>
	<div class="zc-coupon-box">
		<button type="button" class="zc-coupon-toggle" data-zc-coupon-toggle><?php esc_html_e( 'کد تخفیف دارید؟', 'zarincode' ); ?></button>
		<form class="woocommerce-form-coupon" method="post" data-zc-coupon-form>
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'کد تخفیف', 'zarincode' ); ?>" id="coupon_code">
			<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'اعمال', 'zarincode' ); ?>"><?php esc_html_e( 'اعمال', 'zarincode' ); ?></button>
			<?php do_action( 'woocommerce_cart_coupon' ); ?>
		</form>
	</div>
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'zc_checkout_coupon_box', 15 );

/**
 * بستن ستون خلاصه سفارش (در صورت دو ستونه بودن).
 *
 * @return void
 */
function zc_checkout_close_cols() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_checkout_form', 'zc_checkout_close_cols', 5 );

/**
 * استایل و اسکریپت صفحه تسویه.
 *
 * @return void
 */
function zc_checkout_assets() {
	if ( ! is_checkout() && ! is_cart() ) {
		return;
	}
	?>
	<style>
		/* ساختار چک‌اوت ووکامرس با grid دو ستونه */
		.woocommerce-checkout #customer_details{width:100%;float:none}
		.woocommerce-checkout .col2-set{display:block}
		#order_review_heading{display:none}
		.woocommerce-checkout #order_review{background:transparent;border:0;padding:0;box-shadow:none}
	</style>
	<script>
	(function () {
		'use strict';
		document.addEventListener('DOMContentLoaded', function () {
			// کوپن
			var t = document.querySelector('.zc-coupon-toggle');
			var f = document.querySelector('.zc-coupon-box form');
			if (t && f) {
				t.addEventListener('click', function () { f.classList.toggle('show'); });
			}
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'zc_checkout_assets' );
