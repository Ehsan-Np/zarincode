<?php
/**
 * سیستم اشتراک زرین کد
 * ---------------------------------------------------------------------------
 * این ماژول امکان فروش و مدیریت «اشتراک‌ها» (پلن‌های ماهانه/سالانه/مادام‌العمر)
 * را برای ارائه‌ی خدمات فراهم می‌کند.
 *
 * قابلیت‌ها:
 *  - پست‌تایپ «پلن اشتراک» با متاباکس کامل تنظیمات (قیمت، مدت، محدودیت‌ها).
 *  - محدودیت‌های قابل‌تنظیم برای هر پلن:
 *      • حداکثر دانلود ماهیانه
 *      • حداکثر دانلود روزانه
 *      • حداکثر دانلود کل
 *      • حداکثر دستگاه‌ها
 *      • دسترسی به محتوای ویژه (پریمیوم)
 *      • پشتیبانی اولویت‌دار
 *  - اعطای اشتراک پس از تکمیل سفارش ووکامرس (یا خرید مستقیم با کیف پول).
 *  - ردیابی مصرف دانلودها (روزانه/ماهانه/کل) و اعمال محدودیت‌ها.
 *  - تب «اشتراک من» در پنل کاربری + نمایش پلن‌ها با دکمه‌ی خرید.
 *  - شورت‌کد [zc_subscription_plans] برای صفحه‌ی فروش اشتراک.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ۱) پست‌تایپ «پلن اشتراک»
   ========================================================================== */

/**
 * ثبت پست‌تایپ پلن اشتراک.
 *
 * @return void
 */
function zc_register_subscription_cpt() {
	register_post_type(
		'zc_subscription',
		array(
			'labels'       => array(
				'name'          => __( 'پلن‌های اشتراک', 'zarincode' ),
				'singular_name' => __( 'پلن اشتراک', 'zarincode' ),
				'add_new'       => __( 'افزودن پلن', 'zarincode' ),
				'add_new_item'  => __( 'افزودن پلن جدید', 'zarincode' ),
				'edit_item'     => __( 'ویرایش پلن', 'zarincode' ),
				'new_item'      => __( 'پلن جدید', 'zarincode' ),
				'view_item'     => __( 'مشاهده پلن', 'zarincode' ),
				'all_items'     => __( 'همه پلن‌ها', 'zarincode' ),
				'search_items'  => __( 'جستجوی پلن', 'zarincode' ),
				'not_found'     => __( 'پلنی یافت نشد', 'zarincode' ),
				'menu_name'     => __( 'اشتراک‌ها', 'zarincode' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-id-alt',
			'menu_position'=> 27,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest' => false,
			'has_archive'  => false,
		)
	);
}
add_action( 'init', 'zc_register_subscription_cpt' );

/* ==========================================================================
   ۲) متاباکس تنظیمات پلن
   ========================================================================== */

/**
 * ثبت متاباکس پلن اشتراک.
 *
 * @return void
 */
function zc_register_subscription_metabox() {
	add_meta_box(
		'zc_subscription_settings',
		__( 'تنظیمات پلن اشتراک', 'zarincode' ),
		'zc_subscription_metabox',
		'zc_subscription',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zc_register_subscription_metabox' );

/**
 * متاباکس پلن اشتراک.
 *
 * @param \WP_Post $post پست.
 * @return void
 */
function zc_subscription_metabox( $post ) {
	wp_nonce_field( 'zc_subscription_save', 'zc_subscription_nonce' );

	$d = zc_subscription_plan_data( $post->ID );
	?>
	<style>
		.zc-sub-table{width:100%;border-collapse:collapse}
		.zc-sub-table th,.zc-sub-table td{padding:10px 12px;border-bottom:1px solid #eee;vertical-align:middle;text-align:right}
		.zc-sub-table th{width:230px;font-weight:600}
		.zc-sub-table input[type=text],.zc-sub-table input[type=number],.zc-sub-table select{width:100%;max-width:320px}
		.zc-sub-table .zc-sub-hint{color:#6b7280;font-size:12px;display:block;margin-top:3px}
		.zc-sub-row{display:flex;flex-wrap:wrap;gap:12px}
		.zc-sub-col{flex:1 1 200px}
	</style>
	<h3 style="margin-top:0"><?php esc_html_e( 'قیمت و فروش', 'zarincode' ); ?></h3>
	<table class="zc-sub-table">
		<tr>
			<th><label for="_zc_sub_price"><?php esc_html_e( 'قیمت (تومان)', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_price" name="_zc_sub_price" min="0" step="1000"
					value="<?php echo esc_attr( $d['price'] ); ?>" placeholder="0">
				<span class="zc-sub-hint"><?php esc_html_e( 'قیمت پلن برای نمایش و خرید مستقیم با کیف پول.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_product_id"><?php esc_html_e( 'محصول ووکامرس مرتبط', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_product_id" name="_zc_sub_product_id" min="0" step="1"
					value="<?php echo esc_attr( $d['product_id'] ); ?>" placeholder="<?php esc_attr_e( 'شناسه محصول (اختیاری)', 'zarincode' ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( 'اگر پر شود، خرید از طریق همین محصول ووکامرس انجام می‌شود و پس از پرداخت اشتراک فعال می‌گردد. اگر خالی باشد، خرید مستقیم با کیف پول امکان‌پذیر است.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'مدت اعتبار', 'zarincode' ); ?></th>
			<td>
				<div class="zc-sub-row">
					<div class="zc-sub-col">
						<input type="number" id="_zc_sub_duration_value" name="_zc_sub_duration_value" min="0"
							value="<?php echo esc_attr( $d['duration_value'] ); ?>" placeholder="<?php esc_attr_e( 'عدد', 'zarincode' ); ?>">
					</div>
					<div class="zc-sub-col">
						<select id="_zc_sub_duration_unit" name="_zc_sub_duration_unit">
							<option value="day" <?php selected( $d['duration_unit'], 'day' ); ?>><?php esc_html_e( 'روز', 'zarincode' ); ?></option>
							<option value="month" <?php selected( $d['duration_unit'], 'month' ); ?>><?php esc_html_e( 'ماه', 'zarincode' ); ?></option>
							<option value="year" <?php selected( $d['duration_unit'], 'year' ); ?>><?php esc_html_e( 'سال', 'zarincode' ); ?></option>
							<option value="lifetime" <?php selected( $d['duration_unit'], 'lifetime' ); ?>><?php esc_html_e( 'مادام‌العمر', 'zarincode' ); ?></option>
						</select>
					</div>
				</div>
			</td>
		</tr>
	</table>

	<h3><?php esc_html_e( 'محدودیت‌های قابل تنظیم', 'zarincode' ); ?></h3>
	<table class="zc-sub-table">
		<tr>
			<th><label for="_zc_sub_limit_monthly"><?php esc_html_e( 'حداکثر دانلود ماهیانه', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_limit_monthly" name="_zc_sub_limit_monthly" min="0"
					value="<?php echo esc_attr( $d['limit_monthly'] ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( '۰ یعنی نامحدود.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_limit_daily"><?php esc_html_e( 'حداکثر دانلود روزانه', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_limit_daily" name="_zc_sub_limit_daily" min="0"
					value="<?php echo esc_attr( $d['limit_daily'] ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( '۰ یعنی نامحدود.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_limit_total"><?php esc_html_e( 'حداکثر دانلود کل (طول اشتراک)', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_limit_total" name="_zc_sub_limit_total" min="0"
					value="<?php echo esc_attr( $d['limit_total'] ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( '۰ یعنی نامحدود.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_limit_devices"><?php esc_html_e( 'حداکثر دستگاه‌های فعال', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_limit_devices" name="_zc_sub_limit_devices" min="0"
					value="<?php echo esc_attr( $d['limit_devices'] ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( '۰ یعنی نامحدود.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'دسترسی به محتوای ویژه', 'zarincode' ); ?></th>
			<td>
				<label style="display:inline-flex;gap:6px;align-items:center">
					<input type="checkbox" name="_zc_sub_premium" value="1" <?php checked( $d['premium'], '1' ); ?>>
					<?php esc_html_e( 'دسترسی به دوره‌ها و محتوای پریمیوم', 'zarincode' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'پشتیبانی اولویت‌دار', 'zarincode' ); ?></th>
			<td>
				<label style="display:inline-flex;gap:6px;align-items:center">
					<input type="checkbox" name="_zc_sub_support" value="1" <?php checked( $d['support'], '1' ); ?>>
					<?php esc_html_e( 'پشتیبانی سریع و اولویت‌دار', 'zarincode' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_features"><?php esc_html_e( 'ویژگی‌های پلن (هر خط یک مورد)', 'zarincode' ); ?></label></th>
			<td>
				<textarea id="_zc_sub_features" name="_zc_sub_features" rows="6" class="large-text"><?php echo esc_textarea( $d['features_raw'] ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_badge"><?php esc_html_e( 'نشان (بدج) پلن', 'zarincode' ); ?></label></th>
			<td>
				<input type="text" id="_zc_sub_badge" name="_zc_sub_badge" value="<?php echo esc_attr( $d['badge'] ); ?>" placeholder="<?php esc_attr_e( 'مثلاً پرفروش‌ترین', 'zarincode' ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="_zc_sub_order"><?php esc_html_e( 'سطح پلن (برای ارتقا/تنزل)', 'zarincode' ); ?></label></th>
			<td>
				<input type="number" id="_zc_sub_order" name="_zc_sub_order" min="0"
					value="<?php echo esc_attr( $d['order'] ); ?>">
				<span class="zc-sub-hint"><?php esc_html_e( 'هرچه عدد بزرگ‌تر باشد، پلن بالاتر است؛ ارتقا به عدد بزرگ‌تر و تنزل به عدد کوچک‌تر مجاز است.', 'zarincode' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'فعال بودن پلن', 'zarincode' ); ?></th>
			<td>
				<label style="display:inline-flex;gap:6px;align-items:center">
					<input type="checkbox" name="_zc_sub_enabled" value="1" <?php checked( $d['enabled'], '1' ); ?>>
					<?php esc_html_e( 'پلن برای خرید فعال است', 'zarincode' ); ?>
				</label>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * خواندن تنظیمات یک پلن.
 *
 * @param int $plan_id شناسه پلن.
 * @return array
 */
function zc_subscription_plan_data( $plan_id = 0 ) {
	$plan_id = (int) $plan_id;

	return array(
		'price'          => (float) get_post_meta( $plan_id, '_zc_sub_price', true ),
		'product_id'     => (int) get_post_meta( $plan_id, '_zc_sub_product_id', true ),
		'duration_value' => (int) get_post_meta( $plan_id, '_zc_sub_duration_value', true ),
		'duration_unit'  => get_post_meta( $plan_id, '_zc_sub_duration_unit', true ) ?: 'month',
		'limit_monthly'  => (int) get_post_meta( $plan_id, '_zc_sub_limit_monthly', true ),
		'limit_daily'    => (int) get_post_meta( $plan_id, '_zc_sub_limit_daily', true ),
		'limit_total'    => (int) get_post_meta( $plan_id, '_zc_sub_limit_total', true ),
		'limit_devices'  => (int) get_post_meta( $plan_id, '_zc_sub_limit_devices', true ),
		'premium'        => get_post_meta( $plan_id, '_zc_sub_premium', true ),
		'support'        => get_post_meta( $plan_id, '_zc_sub_support', true ),
		'features_raw'   => (string) get_post_meta( $plan_id, '_zc_sub_features', true ),
		'badge'          => get_post_meta( $plan_id, '_zc_sub_badge', true ),
		'order'          => (int) get_post_meta( $plan_id, '_zc_sub_order', true ),
		'enabled'        => get_post_meta( $plan_id, '_zc_sub_enabled', true ),
	);
}

/**
 * ذخیره‌سازی تنظیمات پلن.
 *
 * @param int $post_id شناسه پست.
 * @return void
 */
function zc_subscription_save_meta( $post_id ) {
	if ( ! isset( $_POST['zc_subscription_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['zc_subscription_nonce'] ), 'zc_subscription_save' ) ) { // phpcs:ignore
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'zc_subscription' !== ( get_post_type( $post_id ) ?? '' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_zc_sub_price'          => 'float',
		'_zc_sub_product_id'     => 'int',
		'_zc_sub_duration_value' => 'int',
		'_zc_sub_duration_unit'  => 'str',
		'_zc_sub_limit_monthly'  => 'int',
		'_zc_sub_limit_daily'    => 'int',
		'_zc_sub_limit_total'    => 'int',
		'_zc_sub_limit_devices'  => 'int',
		'_zc_sub_features'       => 'textarea',
		'_zc_sub_badge'          => 'str',
		'_zc_sub_order'          => 'int',
	);

	foreach ( $fields as $key => $type ) {
		$val = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore

		switch ( $type ) {
			case 'int':
				$val = (int) $val;
				break;
			case 'float':
				$val = (float) $val;
				break;
			case 'textarea':
				$val = sanitize_textarea_field( $val );
				break;
			default:
				$val = sanitize_text_field( $val );
		}

		update_post_meta( $post_id, $key, $val );
	}

	// چک‌باکس‌ها.
	update_post_meta( $post_id, '_zc_sub_premium', isset( $_POST['_zc_sub_premium'] ) ? '1' : '' ); // phpcs:ignore
	update_post_meta( $post_id, '_zc_sub_support', isset( $_POST['_zc_sub_support'] ) ? '1' : '' ); // phpcs:ignore
	update_post_meta( $post_id, '_zc_sub_enabled', isset( $_POST['_zc_sub_enabled'] ) ? '1' : '' ); // phpcs:ignore
}
add_action( 'save_post', 'zc_subscription_save_meta' );

/* ==========================================================================
   ۳) توابع کمکی
   ========================================================================== */

/**
 * آیا پلن فعال است؟
 *
 * @param int $plan_id شناسه پلن.
 * @return bool
 */
function zc_subscription_plan_enabled( $plan_id ) {
	return ( '1' === get_post_meta( $plan_id, '_zc_sub_enabled', true ) );
}

/**
 * لیست پلن‌های فعال.
 *
 * @return \WP_Post[]
 */
function zc_subscription_plans( $all = false ) {
	return get_posts(
		array(
			'post_type'      => 'zc_subscription',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => $all ? array() : array( array( 'key' => '_zc_sub_enabled', 'value' => '1' ) ), // phpcs:ignore
		)
	);
}

/**
 * محاسبه‌ی متن مدت اعتبار.
 *
 * @param int $plan_id شناسه پلن.
 * @return string
 */
function zc_subscription_duration_text( $plan_id ) {
	$d = zc_subscription_plan_data( $plan_id );

	if ( 'lifetime' === $d['duration_unit'] ) {
		return __( 'مادام‌العمر', 'zarincode' );
	}
	$units = array(
		'day'   => array( __( 'روز', 'zarincode' ), __( 'روز', 'zarincode' ) ),
		'month' => array( __( 'ماه', 'zarincode' ), __( 'ماه', 'zarincode' ) ),
		'year'  => array( __( 'سال', 'zarincode' ), __( 'سال', 'zarincode' ) ),
	);
	$unit = $units[ $d['duration_unit'] ][ $d['duration_value'] === 1 ? 0 : 1 ] ?? '';

	return sprintf( '%s %s', zc_fa_num( $d['duration_value'] ), $unit );
}

/**
 * ویژگی‌های پلن به‌صورت آرایه.
 *
 * @param int $plan_id شناسه پلن.
 * @return array
 */
function zc_subscription_features( $plan_id ) {
	$raw = (string) get_post_meta( $plan_id, '_zc_sub_features', true );
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$out = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}

/**
 * افزودن امکانات پایه به ویژگی‌های پلن (محدودیت‌ها).
 *
 * @param int $plan_id شناسه پلن.
 * @return array
 */
function zc_subscription_features_with_limits( $plan_id ) {
	$d = zc_subscription_plan_data( $plan_id );
	$f = zc_subscription_features( $plan_id );

	if ( $d['premium'] ) {
		$f[] = __( 'دسترسی به محتوای ویژه', 'zarincode' );
	}
	if ( $d['support'] ) {
		$f[] = __( 'پشتیبانی اولویت‌دار', 'zarincode' );
	}
	$f[] = 0 === $d['limit_monthly']
		? __( 'دانلود ماهیانه نامحدود', 'zarincode' )
		: sprintf( __( 'تا %s دانلود در ماه', 'zarincode' ), zc_fa_num( $d['limit_monthly'] ) );
	$f[] = 0 === $d['limit_daily']
		? __( 'دانلود روزانه نامحدود', 'zarincode' )
		: sprintf( __( 'تا %s دانلود در روز', 'zarincode' ), zc_fa_num( $d['limit_daily'] ) );

	return array_unique( $f );
}

/**
 * دریافت پلن مرتبط با یک محصول ووکامرس.
 *
 * @param int $product_id شناسه محصول.
 * @return int 0 در صورت نبودن.
 */
function zc_subscription_plan_by_product( $product_id ) {
	$q = get_posts(
		array(
			'post_type'      => 'zc_subscription',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_zc_sub_product_id', 'value' => (int) $product_id ), ), // phpcs:ignore
		)
	);
	return $q ? (int) $q[0] : 0;
}

/* ==========================================================================
   ۴) اعطا و مدیریت اشتراک کاربر
   ========================================================================== */

/**
 * دریافت سوابق اشتراک کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return array  رکورد اشتراک یا آرایه‌ی خالی.
 */
function zc_subscription_get_user( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	return get_user_meta( $user_id, 'zc_subscription', true ) ?: array();
}

/**
 * آیا اشتراک کاربر فعال است؟
 *
 * @param int $user_id شناسه کاربر.
 * @return bool
 */
function zc_subscription_is_active( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$rec     = zc_subscription_get_user( $user_id );

	if ( empty( $rec['plan_id'] ) ) {
		return false;
	}
	// مادام‌العمر.
	if ( empty( $rec['expires'] ) ) {
		return true;
	}
	return (int) $rec['expires'] > time();
}

/**
 * اعطای اشتراک به کاربر.
 *
 * @param int   $user_id شناسه کاربر.
 * @param int   $plan_id شناسه پلن.
 * @param int   $order_id سفارش مرتبط (اختیاری).
 * @return array|false  رکورد جدید یا false.
 */
function zc_subscription_grant( $user_id, $plan_id, $order_id = 0 ) {
	$user_id = (int) $user_id;
	$plan_id = (int) $plan_id;
	if ( ! $user_id || ! $plan_id ) {
		return false;
	}

	$d      = zc_subscription_plan_data( $plan_id );
	$expires = 0;
	if ( 'lifetime' !== $d['duration_unit'] ) {
		$expires = zc_subscription_calc_expiry( $d['duration_value'], $d['duration_unit'] );
	}

	$rec = array(
		'plan_id'      => $plan_id,
		'start'        => time(),
		'expires'      => $expires,
		'purchased_at' => time(),
		'order_id'     => (int) $order_id,
		'status'       => 'active',
	);

	update_user_meta( $user_id, 'zc_subscription', $rec );

	// شروع مجدد سنجه‌های مصرف.
	update_user_meta( $user_id, 'zc_sub_usage_total', 0 );
	update_user_meta( $user_id, 'zc_sub_usage_daily', array( 'date' => gmdate( 'Ymd' ), 'count' => 0 ) );
	update_user_meta( $user_id, 'zc_sub_usage_monthly', array( 'month' => gmdate( 'Ym' ), 'count' => 0 ) );

	/**
	 * پس از اعطای اشتراک.
	 *
	 * @param int   $user_id شناسه کاربر.
	 * @param int   $plan_id شناسه پلن.
	 * @param array $rec     رکورد اشتراک.
	 */
	do_action( 'zc_subscription_granted', $user_id, $plan_id, $rec );

	return $rec;
}

/**
 * محاسبه‌ی زمان انقضای اشتراک.
 *
 * @param int    $value مقدار.
 * @param string $unit  روز/ماه/سال.
 * @return int تایم‌استمپ.
 */
function zc_subscription_calc_expiry( $value, $unit ) {
	$value = max( 1, (int) $value );
	$now   = time();

	if ( 'day' === $unit ) {
		return $now + $value * DAY_IN_SECONDS;
	}
	if ( 'year' === $unit ) {
		return $now + $value * YEAR_IN_SECONDS;
	}
	// ماه ≈ ۳۰ روز.
	return $now + $value * MONTH_IN_SECONDS;
}

/* ==========================================================================
   ۵) ووکامرس — اعطای اشتراک پس از خرید
   ========================================================================== */

/**
 * اعطای اشتراک پس از تکمیل/در حال پردازش بودن سفارش.
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_subscription_on_order( $order_id ) {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		$plan_id    = zc_subscription_plan_by_product( $product_id );

		if ( ! $plan_id || ! zc_subscription_plan_enabled( $plan_id ) ) {
			continue;
		}

		// هدیه: دریافت‌کننده مشخص شده است.
		$gift_for = $order->get_meta( '_zc_gift_for' );

		if ( $gift_for ) {
			$recipient = zc_subscription_find_recipient( $gift_for );
			if ( $recipient ) {
				zc_subscription_gift_grant( $recipient, $plan_id, $order_id );
				$order->add_order_note(
					sprintf(
						/* translators: 1: plan 2: recipient */
						__( 'هدیه‌ی اشتراک «%1$s» به %2$s ارسال شد.', 'zarincode' ),
						get_the_title( $plan_id ),
						$gift_for
					)
				);

				// اطلاع به فرستنده.
				do_action( 'zc_subscription_gift_sent', $user_id, $plan_id );
			} else {
				$order->add_order_note( sprintf( __( 'گیرنده‌ی هدیه (%s) یافت نشد.', 'zarincode' ), $gift_for ) );
			}
		} else {
			// تمدید / ارتقا / تنزل / جدید.
			$rec = zc_subscription_apply_plan( $user_id, $plan_id, $order_id );
			if ( $rec && isset( $rec['plan_id'] ) ) {
				$order->add_order_note( sprintf( __( 'اشتراک پلن «%s» اعمال شد.', 'zarincode' ), get_the_title( $rec['plan_id'] ) ) );
			}
		}

		break;
	}
}
add_action( 'woocommerce_order_status_completed', 'zc_subscription_on_order' );
add_action( 'woocommerce_order_status_processing', 'zc_subscription_on_order' );

/* ==========================================================================
   ۶) ردیابی مصرف دانلود و اعمال محدودیت‌ها
   ========================================================================== */

/**
 * دریافت مصرف دانلود کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return array {
 *     int $daily
 *     int $monthly
 *     int $total
 * }
 */
function zc_subscription_usage( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$daily   = get_user_meta( $user_id, 'zc_sub_usage_daily', true );
	$monthly = get_user_meta( $user_id, 'zc_sub_usage_monthly', true );

	$today = gmdate( 'Ymd' );
	$month = gmdate( 'Ym' );

	if ( ! is_array( $daily ) || $daily['date'] !== $today ) {
		$daily = array( 'date' => $today, 'count' => 0 );
		update_user_meta( $user_id, 'zc_sub_usage_daily', $daily );
	}
	if ( ! is_array( $monthly ) || $monthly['month'] !== $month ) {
		$monthly = array( 'month' => $month, 'count' => 0 );
		update_user_meta( $user_id, 'zc_sub_usage_monthly', $monthly );
	}

	return array(
		'daily'   => (int) $daily['count'],
		'monthly' => (int) $monthly['count'],
		'total'   => (int) get_user_meta( $user_id, 'zc_sub_usage_total', true ),
	);
}

/**
 * بررسی اینکه آیا یک محدودیت از یک نوع مجاز است یا خیر.
 *
 * @param int    $plan_id شناسه پلن.
 * @param string $type    daily|monthly|total.
 * @param int    $used    مقدار مصرف‌شده.
 * @return array { bool $allowed, int $limit }
 */
function zc_subscription_limit_allowed( $plan_id, $type, $used ) {
	$d     = zc_subscription_plan_data( $plan_id );
	$limit = 0;

	if ( 'daily' === $type ) {
		$limit = (int) $d['limit_daily'];
	} elseif ( 'monthly' === $type ) {
		$limit = (int) $d['limit_monthly'];
	} else {
		$limit = (int) $d['limit_total'];
	}

	if ( 0 === $limit ) {
		return array( 'allowed' => true, 'limit' => 0 );
	}

	return array( 'allowed' => $used < $limit, 'limit' => $limit );
}

/**
 * ثبت یک دانلود برای کاربرِ دارای اشتراک.
 *
 * @param int $user_id شناسه کاربر.
 * @return array { bool $allowed, string $reason }
 */
function zc_subscription_track_download( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id || ! zc_subscription_is_active( $user_id ) ) {
		return array( 'allowed' => true, 'reason' => '' );
	}

	$rec     = zc_subscription_get_user( $user_id );
	$plan_id = (int) $rec['plan_id'];
	$usage   = zc_subscription_usage( $user_id );

	// بررسی سهمیه‌ها.
	foreach ( array( 'daily', 'monthly', 'total' ) as $type ) {
		$check = zc_subscription_limit_allowed( $plan_id, $type, $usage[ $type ] );
		if ( ! $check['allowed'] ) {
			return array( 'allowed' => false, 'reason' => $type );
		}
	}

	// اعمال و ثبت مصرف.
	$daily   = get_user_meta( $user_id, 'zc_sub_usage_daily', true );
	$monthly = get_user_meta( $user_id, 'zc_sub_usage_monthly', true );
	$daily['count']   = (int) $daily['count'] + 1;
	$monthly['count'] = (int) $monthly['count'] + 1;
	update_user_meta( $user_id, 'zc_sub_usage_daily', $daily );
	update_user_meta( $user_id, 'zc_sub_usage_monthly', $monthly );
	update_user_meta( $user_id, 'zc_sub_usage_total', (int) get_user_meta( $user_id, 'zc_sub_usage_total', true ) + 1 );

	return array( 'allowed' => true, 'reason' => '' );
}

/**
 * هوک دانلود ووکامرس — اعمال محدودیت اشتراک.
 * هنگام دانلود فایل، اگر کاربرِ دارای اشتراک سهمیه را رد کرده باشد، دانلود
 * مسدود و به پنل هدایت می‌شود.
 *
 * آرگومان‌های هوک «woocommerce_download_product»:
 *   (1) ایمیل کاربر، (2) کلید سفارش، (3) شناسه محصول،
 *   (4) شناسه کاربر، (5) شناسه دانلود، (6) شناسه سفارش.
 *
 * @param string $email       ایمیل.
 * @param string $order_key   کلید سفارش.
 * @param int    $product_id  شناسه محصول.
 * @param int    $user_id     شناسه کاربر.
 * @param string $download_id شناسه دانلود.
 * @param int    $order_id    شناسه سفارش.
 * @return void
 */
function zc_subscription_wc_download( $email, $order_key, $product_id, $user_id, $download_id, $order_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id || ! zc_subscription_is_active( $user_id ) ) {
		return;
	}

	$result = zc_subscription_track_download( $user_id );
	if ( $result['allowed'] ) {
		return;
	}

	$labels = array(
		'daily'   => __( 'سقف دانلود روزانه', 'zarincode' ),
		'monthly' => __( 'سقف دانلود ماهیانه', 'zarincode' ),
		'total'   => __( 'سقف دانلود کل', 'zarincode' ),
	);
	$reason = $labels[ $result['reason'] ] ?? __( 'محدودیت اشتراک', 'zarincode' );
	$msg    = sprintf( __( 'به سقف «%s» اشتراک خود رسیده‌اید. برای ادامه، پلن خود را ارتقا دهید.', 'zarincode' ), $reason );

	// اعلان تلگرام/بله در صورت پیکربندی.
	$rec = zc_subscription_get_user( $user_id );
	zc_subscription_notify( 'limit', $user_id, (int) $rec['plan_id'], array( 'text' => $msg ) );

	// ثبت پیام و هدایت به پنل.
	update_user_meta( $user_id, 'zc_subscription_notice', $msg );
	wp_safe_redirect( add_query_arg( 'tab', 'subscription', zc_panel_url() ) );
	exit;
}

// هوک‌های ووکامرس (پشتیبان، بدون آسیب در نبود ووکامرس).
add_action( 'woocommerce_download_product', 'zc_subscription_wc_download', 20, 2 );

/**
 * نمایش پیام ذخیره‌شده (مثلاً سقف دانلود) در تب اشتراک پنل.
 *
 * @return string|false
 */
function zc_subscription_take_notice( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$msg     = get_user_meta( $user_id, 'zc_subscription_notice', true );
	if ( $msg ) {
		delete_user_meta( $user_id, 'zc_subscription_notice' );
	}
	return $msg ? $msg : false;
}

/* ==========================================================================
   ۷) تب پنل کاربری «اشتراک من»
   ========================================================================== */

/**
 * افزودن تب اشتراک به پنل کاربری.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_panel_tab_subscription( $tabs ) {
	$tabs['subscription'] = array(
		'label' => __( 'اشتراک من', 'zarincode' ),
		'icon'  => 'star',
		'order' => 25,
		'badge' => zc_subscription_is_active() ? '' : '',
	);
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_panel_tab_subscription' );

/* ==========================================================================
   ۸) خرید مستقیم با کیف پول (AJAX)
   ========================================================================== */

/**
 * خرید / تمدید / ارتقا / هدیه اشتراک (کیف پول یا هدایت به ووکامرس).
 *
 * @return void
 */
function zc_ajax_subscription_buy() {
	check_ajax_referer( 'zc_nonce', 'nonce' );

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$plan_id = isset( $_POST['plan_id'] ) ? (int) $_POST['plan_id'] : 0; // phpcs:ignore
	$coupon  = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : ''; // phpcs:ignore
	$gift_to = isset( $_POST['gift_to'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_to'] ) ) : ''; // phpcs:ignore

	$plan = get_post( $plan_id );
	if ( ! $plan || 'zc_subscription' !== $plan->post_type || ! zc_subscription_plan_enabled( $plan_id ) ) {
		wp_send_json_error( array( 'message' => __( 'پلن نامعتبر است.', 'zarincode' ) ) );
	}

	$d     = zc_subscription_plan_data( $plan_id );
	$price = (float) $d['price'];

	// اگر هدیه است، گیرنده الزامی است.
	if ( $gift_to && ! zc_subscription_find_recipient( $gift_to ) ) {
		wp_send_json_error( array( 'message' => __( 'کاربرِ گیرنده یافت نشد. ایمیل یا نام‌کاربری را بررسی کنید.', 'zarincode' ) ) );
	}

	// اگر پلن محصول ووکامرس دارد، خرید از طریق ووکامرس انجام می‌شود.
	if ( $d['product_id'] ) {
		$url = add_query_arg( 'add-to-cart', (int) $d['product_id'], function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : get_permalink( (int) $d['product_id'] ) );

		if ( $gift_to ) {
			// ذخیره‌ی هدیه برای انتقال به سفارش در تسویه.
			zc_subscription_set_pending_gift( $plan_id, $gift_to );
		}

		wp_send_json_success( array( 'redirect' => $url ) );
	}

	// خرید مستقیم با کیف پول.
	if ( ! function_exists( 'zc_wallet_balance' ) || ! zc_opt( 'zc_wallet_enable', true ) ) {
		wp_send_json_error( array( 'message' => __( 'پرداخت مستقیم فعال نیست. لطفاً با پشتیبانی تماس بگیرید.', 'zarincode' ) ) );
	}

	// اعمال کد تخفیف.
	$final_price = $price;
	if ( $coupon ) {
		$c = zc_subscription_coupon( $coupon, $price );
		if ( ! $c['valid'] ) {
			wp_send_json_error( array( 'message' => $c['message'] ) );
		}
		$final_price = $c['amount'];
	}

	if ( $final_price > 0 && zc_wallet_balance( $user_id ) < $final_price ) {
		wp_send_json_error( array( 'message' => __( 'موجودی کیف پول کافی نیست.', 'zarincode' ) ) );
	}

	// کسر از کیف پول.
	if ( $final_price > 0 && function_exists( 'zc_wallet_withdraw' ) ) {
		zc_wallet_withdraw( $user_id, $final_price, sprintf( __( 'خرید اشتراک «%s»', 'zarincode' ), $plan->post_title ) );
	}

	// هدیه یا خرید مستقیم.
	if ( $gift_to ) {
		$recipient = zc_subscription_find_recipient( $gift_to );
		$rec       = zc_subscription_gift_grant( $recipient, $plan_id, 0 );
		if ( ! $rec ) {
			wp_send_json_error( array( 'message' => __( 'خطا در ارسال هدیه.', 'zarincode' ) ) );
		}
		do_action( 'zc_subscription_gift_sent', $user_id, $plan_id );
		$message = sprintf( __( 'هدیه‌ی «%s» با موفقیت ارسال شد.', 'zarincode' ), $plan->post_title );
	} else {
		$rec = zc_subscription_apply_plan( $user_id, $plan_id );
		if ( ! $rec ) {
			wp_send_json_error( array( 'message' => __( 'خطا در فعال‌سازی اشتراک.', 'zarincode' ) ) );
		}

		if ( (int) $rec['plan_id'] === $plan_id && zc_subscription_pending_plan( $user_id ) === $plan_id ) {
			$message = sprintf( __( 'تنزل به «%s» از پایان دوره‌ی فعلی اعمال می‌شود.', 'zarincode' ), $plan->post_title );
		} else {
			$message = sprintf( __( 'اشتراک «%s» با موفقیت فعال شد.', 'zarincode' ), $plan->post_title );
		}
	}

	wp_send_json_success(
		array(
			'message'  => $message,
			'redirect' => add_query_arg( 'tab', 'subscription', zc_panel_url() ),
		)
	);
}
add_action( 'wp_ajax_zc_subscription_buy', 'zc_ajax_subscription_buy' );

/**
 * لغو تنزلِ زمان‌بندی‌شده.
 *
 * @return void
 */
function zc_ajax_subscription_cancel_downgrade() {
	check_ajax_referer( 'zc_nonce', 'nonce' );

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	zc_subscription_cancel_downgrade( $user_id );

	wp_send_json_success( array( 'message' => __( 'تنزل زمان‌بندی‌شده لغو شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_subscription_cancel_downgrade', 'zc_ajax_subscription_cancel_downgrade' );

/* ==========================================================================
   ۸.۵) ایجاد پلن‌های نمونه (دمو)
   ========================================================================== */

/**
 * ساخت محصول ساده‌ی ووکامرس برای یک پلن.
 *
 * @param string $name  نام.
 * @param float  $price قیمت.
 * @return int  شناسه محصول یا ۰.
 */
function zc_create_subscription_product( $name, $price ) {
	if ( ! function_exists( 'wc_get_product' ) || ! class_exists( 'WC_Product_Simple' ) ) {
		return 0;
	}

	$product    = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_regular_price( (string) $price );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_sold_individually( true );
	$product->set_virtual( true );
	$product->set_downloadable( false );

	return $product->save();
}

/**
 * ساخت پلن اشتراک نمونه (برای دمو).
 *
 * @param array $args مشخصات پلن.
 * @return int
 */
function zc_create_subscription_plan( $args ) {
	$defaults = array(
		'post_title' => '',
		'post_content' => '',
		'price'      => 0,
		'product_id' => 0,
		'duration_value' => 1,
		'duration_unit'  => 'month',
		'limit_monthly'  => 0,
		'limit_daily'    => 0,
		'limit_total'    => 0,
		'limit_devices'  => 0,
		'premium'        => '1',
		'support'        => '1',
		'features'       => '',
		'badge'          => '',
		'enabled'        => '1',
	);
	$args = wp_parse_args( $args, $defaults );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'zc_subscription',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $args['post_title'] ),
			'post_content' => wp_kses_post( $args['post_content'] ),
			'menu_order'   => isset( $args['order'] ) ? (int) $args['order'] : 0,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	foreach ( array( 'price', 'product_id', 'duration_value', 'duration_unit', 'limit_monthly', 'limit_daily', 'limit_total', 'limit_devices', 'premium', 'support', 'features', 'badge', 'order', 'enabled' ) as $key ) {
		update_post_meta( $post_id, '_zc_sub_' . $key, $args[ $key ] );
	}

	return $post_id;
}

/**
 * درون‌ریزی پلن‌های اشتراک نمونه.
 *
 * @return void
 */
function zc_install_demo_subscriptions() {
	$existing = get_posts(
		array(
			'post_type'      => 'zc_subscription',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		return;
	}

	$plans = array(
		array(
			'title'     => __( 'پلن ماهانه', 'zarincode' ),
			'desc'      => __( 'برای شروع و آشنایی با امکانات اختصاصی زرین کد.', 'zarincode' ),
			'price'     => 199000,
			'duration'  => array( 1, 'month' ),
			'limits'    => array( 'monthly' => 30, 'daily' => 5, 'total' => 0, 'devices' => 1 ),
			'premium'   => '1',
			'support'   => '0',
			'features'  => __( "دسترسی به محتوای ویژه\nپشتیبانی استاندارد", 'zarincode' ),
		),
		array(
			'title'     => __( 'پلن سالانه (پیشنهادی)', 'zarincode' ),
			'desc'      => __( 'پربازده‌ترین انتخاب برای کاربران حرفه‌ای.', 'zarincode' ),
			'price'     => 1890000,
			'duration'  => array( 1, 'year' ),
			'limits'    => array( 'monthly' => 100, 'daily' => 20, 'total' => 0, 'devices' => 3 ),
			'premium'   => '1',
			'support'   => '1',
			'features'  => __( "دسترسی به محتوای ویژه\nپشتیبانی اولویت‌دار\n۳ دستگاه فعال", 'zarincode' ),
			'badge'     => __( 'پیشنهاد ویژه', 'zarincode' ),
		),
		array(
			'title'     => __( 'پلن مادام‌العمر', 'zarincode' ),
			'desc'      => __( 'یک‌بار پرداخت کنید، برای همیشه استفاده کنید.', 'zarincode' ),
			'price'     => 5900000,
			'duration'  => array( 1, 'lifetime' ),
			'limits'    => array( 'monthly' => 0, 'daily' => 0, 'total' => 0, 'devices' => 5 ),
			'premium'   => '1',
			'support'   => '1',
			'features'  => __( "دسترسی به محتوای ویژه\nپشتیبانی اولویت‌دار\n۵ دستگاه فعال\nدانلود نامحدود", 'zarincode' ),
			'badge'     => __( 'بهترین ارزش', 'zarincode' ),
		),
	);

	$i = 1;
	foreach ( $plans as $p ) {
		$product_id = zc_create_subscription_product( $p['title'], $p['price'] );
		zc_create_subscription_plan(
			array(
				'post_title'    => $p['title'],
				'post_content'  => $p['desc'],
				'price'         => $p['price'],
				'product_id'    => $product_id,
				'order'         => $i,
				'duration_value'=> $p['duration'][0],
				'duration_unit' => $p['duration'][1],
				'limit_monthly' => $p['limits']['monthly'],
				'limit_daily'   => $p['limits']['daily'],
				'limit_total'   => $p['limits']['total'],
				'limit_devices' => $p['limits']['devices'],
				'premium'       => $p['premium'],
				'support'       => $p['support'],
				'features'      => $p['features'],
				'badge'         => $p['badge'] ?? '',
				'enabled'       => '1',
			)
		);
		$i++;
	}
}

/* ==========================================================================
   ۹) شورت‌کد نمایش پلن‌ها
   ========================================================================== */

/**
 * شورت‌کد [zc_subscription_plans] — کارت پلن‌ها برای صفحه‌ی فروش.
 *
 * @return string
 */
function zc_subscription_plans_shortcode() {
	ob_start();
	zc_render_subscription_plans();
	return ob_get_clean();
}
add_shortcode( 'zc_subscription_plans', 'zc_subscription_plans_shortcode' );

/**
 * رندر کارت پلن‌های اشتراک (برای پنل و صفحه‌ی فروش).
 *
 * @return void
 */
function zc_render_subscription_plans() {
	$plans = zc_subscription_plans();
	$user  = get_current_user_id();
	$has   = zc_subscription_is_active( $user );
	$cur   = zc_subscription_get_user( $user );
	$cur_tier = ( $has && ! empty( $cur['plan_id'] ) ) ? zc_subscription_plan_tier( $cur['plan_id'] ) : -1;
	$pending = zc_subscription_pending_plan( $user );
	$is_lifetime = $has && empty( $cur['expires'] );

	if ( ! $plans ) {
		echo '<div class="zc-empty"><div class="zc-empty__icon">' . zc_icon( 'star', 40 ) . '</div>';
		echo '<h3>' . esc_html__( 'هنوز پلن اشتراکی تعریف نشده است.', 'zarincode' ) . '</h3></div>';
		return;
	}

	// کد تخفیف (برای خرید مستقیم با کیف پول).
	?>
	<div class="zc-sub-coupon" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px;padding:14px 16px;background:var(--zc-line-2,#F1F4F8);border-radius:14px">
		<span style="font-size:.85rem;color:var(--zc-muted,#6B7280);display:inline-flex;gap:6px;align-items:center">
			<?php echo zc_icon( 'ticket', 17 ); // phpcs:ignore ?><?php esc_html_e( 'کد تخفیف:', 'zarincode' ); ?>
		</span>
		<input type="text" id="zc-sub-coupon" class="zc-sub-coupon__input" placeholder="<?php esc_attr_e( 'کد تخفیف', 'zarincode' ); ?>"
			style="flex:1;min-width:140px;max-width:240px;padding:9px 14px;border:1px solid var(--zc-line,#E8ECF2);border-radius:10px;font-size:.85rem">
		<span class="zc-sub-coupon__msg" style="font-size:.82rem;color:var(--zc-gold-3)"></span>
	</div>

	<div class="zc-sub-plans" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px">
		<?php foreach ( $plans as $plan ) : ?>
			<?php
			$d       = zc_subscription_plan_data( $plan->ID );
			$tier    = zc_subscription_plan_tier( $plan->ID );
			$is_cur  = $has && (int) $cur['plan_id'] === $plan->ID;
			$is_up   = $has && ! $is_cur && $tier > $cur_tier;
			$is_down = $has && ! $is_cur && $tier < $cur_tier;
			?>
			<div class="zc-sub-plan<?php echo $is_cur ? ' is-current' : ''; ?>"
				style="background:#fff;border:1px solid var(--zc-line,#E8ECF2);border-radius:20px;padding:28px 24px;display:flex;flex-direction:column;position:relative;box-shadow:0 6px 22px rgba(20,26,49,.06)">
				<?php if ( $d['badge'] ) : ?>
					<span style="position:absolute;top:14px;inset-inline-end:14px;background:var(--zc-grad-gold,#C9A227);color:#241C05;font-size:.72rem;font-weight:800;padding:4px 12px;border-radius:999px"><?php echo esc_html( $d['badge'] ); ?></span>
				<?php endif; ?>

				<h3 style="margin:0 0 4px;font-size:1.15rem"><?php echo esc_html( $plan->post_title ); ?></h3>
				<span style="color:var(--zc-muted,#6B7280);font-size:.82rem"><?php echo esc_html( zc_subscription_duration_text( $plan->ID ) ); ?></span>

				<div style="margin:16px 0;display:flex;align-items:baseline;gap:4px">
					<strong style="font-size:1.7rem;color:var(--zc-gold-3,#8C6D1F)"><?php echo esc_html( zc_fa_num( number_format( $d['price'] ) ) ); ?></strong>
					<span style="font-size:.8rem;color:var(--zc-muted)"><?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></span>
				</div>

				<ul style="list-style:none;margin:0 0 20px;padding:0;display:flex;flex-direction:column;gap:9px;flex:1">
					<?php foreach ( zc_subscription_features_with_limits( $plan->ID ) as $feature ) : ?>
						<li style="display:flex;gap:8px;align-items:flex-start;font-size:.87rem;color:var(--zc-text,#1F2437)">
							<?php echo zc_icon( 'check', 17 ); // phpcs:ignore ?>
							<span><?php echo esc_html( $feature ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( $is_cur ) : ?>
					<?php if ( $is_lifetime ) : ?>
						<span class="zc-btn zc-btn--gold zc-btn--block" style="pointer-events:none"><?php esc_html_e( 'اشتراک فعلی شما', 'zarincode' ); ?></span>
					<?php else : ?>
						<button type="button" class="zc-btn zc-btn--gold zc-btn--block" data-zc-sub-buy="<?php echo (int) $plan->ID; ?>">
							<?php zc_the_icon( 'refresh', 17 ); ?><?php esc_html_e( 'تمدید اشتراک', 'zarincode' ); ?>
						</button>
					<?php endif; ?>
				<?php else : ?>
					<button type="button" class="zc-btn zc-btn--<?php echo $is_up ? 'navy' : 'gold'; ?> zc-btn--block" data-zc-sub-buy="<?php echo (int) $plan->ID; ?>">
						<?php
						if ( $is_up ) {
							zc_the_icon( 'arrow-up', 17 );
							esc_html_e( 'ارتقا به این پلن', 'zarincode' );
						} elseif ( $is_down ) {
							zc_the_icon( 'arrow-down', 17 );
							esc_html_e( 'تنزل (از دوره بعد)', 'zarincode' );
						} else {
							zc_the_icon( 'cart', 17 );
							esc_html_e( 'خرید اشتراک', 'zarincode' );
						}
						?>
					</button>
				<?php endif; ?>

				<?php if ( $is_cur && $pending && $pending !== $plan->ID ) : ?>
					<button type="button" class="zc-btn zc-btn--ghost zc-btn--sm zc-btn--block" data-zc-sub-cancel-downgrade style="margin-top:8px">
						<?php esc_html_e( 'لغو تنزل', 'zarincode' ); ?>
					</button>
				<?php endif; ?>

				<button type="button" class="zc-btn zc-btn--ghost zc-btn--sm zc-btn--block" data-zc-sub-gift="<?php echo (int) $plan->ID; ?>" style="margin-top:8px">
					<?php zc_the_icon( 'gift', 15 ); ?><?php esc_html_e( 'هدیه دادن', 'zarincode' ); ?>
				</button>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- مودال هدیه -->
	<div class="zc-modal" id="zc-sub-gift-modal" aria-hidden="true">
		<div class="zc-modal__overlay" data-zc-sub-gift-close></div>
		<div class="zc-modal__box" role="dialog" aria-modal="true" aria-labelledby="zc-sub-gift-title">
			<div class="zc-modal__head">
				<h3 id="zc-sub-gift-title"><?php esc_html_e( 'هدیه دادن اشتراک', 'zarincode' ); ?></h3>
				<button type="button" class="zc-modal__close" data-zc-sub-gift-close aria-label="<?php esc_attr_e( 'بستن', 'zarincode' ); ?>">
					<?php zc_the_icon( 'close', 20 ); ?>
				</button>
			</div>
			<div class="zc-modal__body">
				<p style="margin:0 0 14px;color:var(--zc-muted,#6B7280);font-size:.88rem">
					<?php esc_html_e( 'ایمیل یا نام‌کاربری کاربرِ گیرنده را وارد کنید. اشتراک به حساب او اضافه می‌شود.', 'zarincode' ); ?>
				</p>
				<input type="text" id="zc-sub-gift-recipient" placeholder="<?php esc_attr_e( 'ایمیل یا نام‌کاربری گیرنده', 'zarincode' ); ?>"
					style="width:100%;padding:12px 14px;border:1px solid var(--zc-line,#E8ECF2);border-radius:12px;font-size:.92rem">
				<div class="zc-sub-gift__plan" style="margin-top:12px;font-size:.88rem;color:var(--zc-text,#1F2437)"></div>
				<div class="zc-form-msg" style="margin-top:10px"></div>
			</div>
			<div class="zc-modal__foot">
				<button type="button" class="zc-btn zc-btn--ghost" data-zc-sub-gift-close><?php esc_html_e( 'انصراف', 'zarincode' ); ?></button>
				<button type="button" class="zc-btn zc-btn--gold" id="zc-sub-gift-confirm">
					<?php zc_the_icon( 'gift', 16 ); ?><?php esc_html_e( 'خرید و ارسال هدیه', 'zarincode' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}

/* ==========================================================================
   ۱۰) رتبه/سطح پلن (برای ارتقا و تنزل)
   ========================================================================== */

/**
 * سطح پلن (هرچه بیشتر، سطح بالاتر).
 *
 * @param int $plan_id شناسه پلن.
 * @return int
 */
function zc_subscription_plan_tier( $plan_id ) {
	$plan_id = (int) $plan_id;
	$order   = (int) get_post_meta( $plan_id, '_zc_sub_order', true );

	if ( $order > 0 ) {
		return $order;
	}

	return (int) get_post_field( 'menu_order', $plan_id );
}

/**
 * مدت‌زمان یک پلن بر حسب ثانیه.
 *
 * @param array $d داده پلن (از zc_subscription_plan_data).
 * @return int
 */
function zc_subscription_duration_seconds( $d ) {
	$value = max( 1, (int) $d['duration_value'] );

	if ( 'day' === $d['duration_unit'] ) {
		return $value * DAY_IN_SECONDS;
	}
	if ( 'year' === $d['duration_unit'] ) {
		return $value * YEAR_IN_SECONDS;
	}
	// ماه ≈ ۳۰ روز.
	return $value * MONTH_IN_SECONDS;
}

/* ==========================================================================
   ۱۱) تمدید، ارتقا، تنزل
   ========================================================================== */

/**
 * تمدید اشتراک (پلن یکسان) — از پایان دوره‌ی فعلی، نه از حالا.
 *
 * @param int $user_id  شناسه کاربر.
 * @param int $plan_id  شناسه پلن.
 * @param int $order_id سفارش (اختیاری).
 * @return array
 */
function zc_subscription_extend( $user_id, $plan_id, $order_id = 0 ) {
	$rec     = zc_subscription_get_user( $user_id );
	$d       = zc_subscription_plan_data( $plan_id );
	$base    = ( ! empty( $rec['expires'] ) && (int) $rec['expires'] > time() ) ? (int) $rec['expires'] : time();

	if ( 'lifetime' === $d['duration_unit'] ) {
		$rec['expires'] = 0;
	} else {
		$rec['expires'] = $base + zc_subscription_duration_seconds( $d );
	}

	$rec['plan_id']      = (int) $plan_id;
	$rec['order_id']     = (int) $order_id;
	$rec['purchased_at'] = time();
	$rec['status']       = 'active';

	update_user_meta( $user_id, 'zc_subscription', $rec );
	delete_user_meta( $user_id, 'zc_sub_pending_plan' );

	/**
	 * پس از تمدید اشتراک.
	 *
	 * @param int   $user_id شناسه کاربر.
	 * @param int   $plan_id شناسه پلن.
	 * @param array $rec     رکورد اشتراک.
	 */
	do_action( 'zc_subscription_renewed', $user_id, $plan_id, $rec );

	return $rec;
}

/**
 * ارتقای اشتراک به پلن بالاتر.
 * مدت باقی‌مانده‌ی پلن قبلی به دوره‌ی جدید اضافه می‌شود (Proration).
 *
 * @param int $user_id  شناسه کاربر.
 * @param int $plan_id  شناسه پلن جدید.
 * @param int $order_id سفارش (اختیاری).
 * @return array
 */
function zc_subscription_upgrade( $user_id, $plan_id, $order_id = 0 ) {
	$rec = zc_subscription_get_user( $user_id );
	$d   = zc_subscription_plan_data( $plan_id );

	$remaining = 0;
	if ( ! empty( $rec['expires'] ) && (int) $rec['expires'] > time() ) {
		$remaining = (int) $rec['expires'] - time();
	}

	$expires = 0;
	if ( 'lifetime' !== $d['duration_unit'] ) {
		$expires = time() + zc_subscription_duration_seconds( $d ) + $remaining;
	}

	$rec = array(
		'plan_id'      => (int) $plan_id,
		'start'        => time(),
		'expires'      => $expires,
		'purchased_at' => time(),
		'order_id'     => (int) $order_id,
		'status'       => 'active',
	);

	update_user_meta( $user_id, 'zc_subscription', $rec );
	delete_user_meta( $user_id, 'zc_sub_pending_plan' );

	// شروع مجدد سنجه‌های مصرف (چون امکانات جدیدی اضافه شده).
	update_user_meta( $user_id, 'zc_sub_usage_total', 0 );
	update_user_meta( $user_id, 'zc_sub_usage_daily', array( 'date' => gmdate( 'Ymd' ), 'count' => 0 ) );
	update_user_meta( $user_id, 'zc_sub_usage_monthly', array( 'month' => gmdate( 'Ym' ), 'count' => 0 ) );

	/**
	 * پس از ارتقای اشتراک.
	 *
	 * @param int   $user_id شناسه کاربر.
	 * @param int   $plan_id شناسه پلن.
	 * @param array $rec     رکورد اشتراک.
	 */
	do_action( 'zc_subscription_upgraded', $user_id, $plan_id, $rec );

	return $rec;
}

/**
 * زمان‌بندی تنزل اشتراک به پلن پایین‌تر (از پایان دوره‌ی فعلی).
 *
 * @param int $user_id شناسه کاربر.
 * @param int $plan_id شناسه پلن جدید.
 * @return void
 */
function zc_subscription_schedule_downgrade( $user_id, $plan_id ) {
	update_user_meta( $user_id, 'zc_sub_pending_plan', (int) $plan_id );

	/**
	 * پس از زمان‌بندی تنزل.
	 *
	 * @param int $user_id شناسه کاربر.
	 * @param int $plan_id شناسه پلن.
	 */
	do_action( 'zc_subscription_downgrade_scheduled', $user_id, $plan_id );
}

/**
 * لغو زمان‌بندی تنزل.
 *
 * @param int $user_id شناسه کاربر.
 * @return void
 */
function zc_subscription_cancel_downgrade( $user_id ) {
	delete_user_meta( $user_id, 'zc_sub_pending_plan' );
}

/**
 * پلنِ زمان‌بندی‌شده برای تنزل (اگر باشد).
 *
 * @param int $user_id شناسه کاربر.
 * @return int
 */
function zc_subscription_pending_plan( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return (int) get_user_meta( $user_id, 'zc_sub_pending_plan', true );
}

/**
 * اعمال یک پلن برای کاربر (بر اساس وضعیت فعلی): تمدید / ارتقا / تنزل / جدید.
 *
 * @param int $user_id  شناسه کاربر.
 * @param int $plan_id  شناسه پلن.
 * @param int $order_id سفارش (اختیاری).
 * @return array|false
 */
function zc_subscription_apply_plan( $user_id, $plan_id, $order_id = 0 ) {
	if ( ! zc_subscription_plan_enabled( $plan_id ) ) {
		return false;
	}

	if ( ! zc_subscription_is_active( $user_id ) ) {
		return zc_subscription_grant( $user_id, $plan_id, $order_id );
	}

	$rec = zc_subscription_get_user( $user_id );
	$cur = (int) $rec['plan_id'];

	if ( $cur === $plan_id ) {
		// تمدید دوره.
		return zc_subscription_extend( $user_id, $plan_id, $order_id );
	}

	if ( zc_subscription_plan_tier( $plan_id ) > zc_subscription_plan_tier( $cur ) ) {
		// ارتقا به پلن بالاتر.
		return zc_subscription_upgrade( $user_id, $plan_id, $order_id );
	}

	// تنزل به پلن پایین‌تر (از پایان دوره‌ی فعلی).
	zc_subscription_schedule_downgrade( $user_id, $plan_id );
	return zc_subscription_get_user( $user_id );
}

/* ==========================================================================
   ۱۲) کد تخفیف
   ========================================================================== */

/**
 * محاسبه‌ی تخفیفِ یک کد کوپن ووکامرس برای قیمت اشتراک.
 *
 * @param string $code  کد تخفیف.
 * @param float  $price قیمت پایه.
 * @return array {
 *     bool   $valid
 *     string $message
 *     float  $discount
 *     float  $amount
 * }
 */
function zc_subscription_coupon( $code, $price ) {
	$out = array( 'valid' => false, 'message' => __( 'کد تخفیف معتبر نیست.', 'zarincode' ), 'discount' => 0, 'amount' => (float) $price );

	$code = sanitize_text_field( $code );
	if ( '' === $code || ! class_exists( 'WC_Coupon' ) ) {
		return $out;
	}

	$coupon = new \WC_Coupon( $code );
	if ( ! $coupon->get_id() || 'publish' !== $coupon->get_status() ) {
		return $out;
	}

	// بررسی محدودیت استفاده.
	$limit = $coupon->get_usage_limit();
	if ( $limit > 0 && $coupon->get_usage_count() >= $limit ) {
		$out['message'] = __( 'این کد تخفیف به پایان رسیده است.', 'zarincode' );
		return $out;
	}

	$type     = $coupon->get_discount_type();
	$discount = 0;

	if ( 'percent' === $type ) {
		$discount = (float) $price * ( (float) $coupon->get_amount() / 100 );
	} else {
		$discount = (float) $coupon->get_amount();
	}

	$discount = max( 0, min( (float) $price, (float) $discount ) );

	$out = array(
		'valid'    => true,
		'message'  => sprintf( __( 'کد تخفیف %s اعمال شد.', 'zarincode' ), $code ),
		'discount' => $discount,
		'amount'   => (float) $price - $discount,
	);

	return $out;
}

/* ==========================================================================
   ۱۳) هدیه دادن اشتراک
   ========================================================================== */

/**
 * یافتن کاربرِ گیرنده‌ی هدیه (بر اساس ایمیل یا نام‌کاربری).
 *
 * @param string $recipient ایمیل یا نام‌کاربری.
 * @return int 0 اگر یافت نشود.
 */
function zc_subscription_find_recipient( $recipient ) {
	$recipient = sanitize_text_field( $recipient );
	if ( ! $recipient ) {
		return 0;
	}

	if ( is_email( $recipient ) ) {
		$u = get_user_by( 'email', $recipient );
		if ( $u ) {
			return (int) $u->ID;
		}
	}

	$u = get_user_by( 'login', $recipient );
	return $u ? (int) $u->ID : 0;
}

/**
 * ثبت هدیه‌ی در انتظار خرید (برای سفارش ووکامرس).
 *
 * @param int    $plan_id شناسه پلن.
 * @param string $to      گیرنده (ایمیل/نام‌کاربری).
 * @return void
 */
function zc_subscription_set_pending_gift( $plan_id, $to ) {
	set_transient( 'zc_gift_pending_' . get_current_user_id(), array( 'plan_id' => (int) $plan_id, 'to' => sanitize_text_field( $to ) ), HOUR_IN_SECONDS );
}

/**
 * دریافت و پاک‌کردن هدیه‌ی در انتظار.
 *
 * @param int $user_id شناسه کاربر.
 * @return array
 */
function zc_subscription_take_pending_gift( $user_id ) {
	$gift = get_transient( 'zc_gift_pending_' . (int) $user_id );
	if ( $gift ) {
		delete_transient( 'zc_gift_pending_' . (int) $user_id );
	}
	return is_array( $gift ) ? $gift : array();
}

/**
 * اعطای هدیه به گیرنده.
 *
 * @param int $recipient_id شناسه کاربر گیرنده.
 * @param int $plan_id      شناسه پلن.
 * @param int $order_id     سفارش (اختیاری).
 * @return array|false
 */
function zc_subscription_gift_grant( $recipient_id, $plan_id, $order_id = 0 ) {
	$rec = zc_subscription_apply_plan( $recipient_id, $plan_id, $order_id );

	if ( $rec ) {
		/**
		 * پس از دریافت هدیه.
		 *
		 * @param int   $recipient_id شناسه کاربر.
		 * @param int   $plan_id      شناسه پلن.
		 * @param array $rec          رکورد اشتراک.
		 */
		do_action( 'zc_subscription_gift_received', $recipient_id, $plan_id, $rec );
	}

	return $rec;
}

/* ==========================================================================
   ۱۴) اطلاع‌رسانی تلگرام و بله
   ========================================================================== */

/**
 * ارسال اعلان‌های اشتراک به تلگرام و بله (در صورت پیکربندی).
 *
 * @param string $event   رویداد.
 * @param int    $user_id شناسه کاربر.
 * @param int    $plan_id شناسه پلن.
 * @param array  $extra   اطلاعات اضافه.
 * @return void
 */
function zc_subscription_notify( $event, $user_id = 0, $plan_id = 0, $extra = array() ) {
	$user = $user_id ? get_userdata( $user_id ) : false;
	$name = $user ? $user->display_name : (string) ( $extra['name'] ?? '' );
	$plan = $plan_id ? get_the_title( $plan_id ) : (string) ( $extra['plan'] ?? '' );

	$titles = array(
		'granted'            => 'اشتراک جدید فعال شد',
		'renewed'            => 'اشتراک تمدید شد',
		'upgraded'           => 'اشتراک ارتقا یافت',
		'downgrade_scheduled'=> 'تنزل اشتراک زمان‌بندی شد',
		'expired'            => 'اشتراک منقضی شد',
		'gift_received'      => 'هدیه‌ی اشتراک دریافت شد',
		'gift_sent'          => 'هدیه‌ی اشتراک ارسال شد',
		'limit'              => 'هشدار سقف دانلود',
	);

	$lines = array(
		'title'   => $titles[ $event ] ?? 'رویداد اشتراک',
		'user'    => $name,
		'plan'    => $plan,
		'extra'   => (string) ( $extra['text'] ?? '' ),
	);

	$message  = '🟢 زرین کد — ' . $lines['title'] . "\n";
	$message .= '————————————' . "\n";
	if ( $lines['user'] ) {
		$message .= '👤 کاربر: ' . $lines['user'] . "\n";
	}
	if ( $lines['plan'] ) {
		$message .= '📦 پلن: ' . $lines['plan'] . "\n";
	}
	if ( $lines['extra'] ) {
		$message .= $lines['extra'] . "\n";
	}
	$message .= '🕒 ' . date_i18n( 'Y/m/d H:i' );

	// تلگرام.
	if ( function_exists( 'zc_telegram_send' ) && zc_opt( 'zc_telegram_enable', false ) ) {
		zc_telegram_send( $message );
	}

	// بله.
	if ( function_exists( 'zc_bale_send' ) && zc_opt( 'zc_bale_enable', false ) ) {
		zc_bale_send( $message );
	}
}

/**
 * اتصال رویدادهای اشتراک به اطلاع‌رسانی.
 *
 * @return void
 */
function zc_subscription_register_notifications() {
	$events = array(
		'zc_subscription_granted'             => 'granted',
		'zc_subscription_renewed'             => 'renewed',
		'zc_subscription_upgraded'            => 'upgraded',
		'zc_subscription_downgrade_scheduled' => 'downgrade_scheduled',
		'zc_subscription_gift_received'       => 'gift_received',
	);

	foreach ( $events as $hook => $event ) {
		add_action(
			$hook,
			function () use ( $event ) {
				$args     = func_get_args();
				$user_id  = isset( $args[0] ) ? (int) $args[0] : 0;
				$plan_id  = isset( $args[1] ) ? (int) $args[1] : 0;
				zc_subscription_notify( $event, $user_id, $plan_id );
			},
			10,
			3
		);
	}

	// هدیه ارسال شد (از طرف کاربر فرستنده).
	add_action( 'zc_subscription_gift_sent', 'zc_subscription_notify_gift_sent', 10, 2 );
}
add_action( 'init', 'zc_subscription_register_notifications' );

/**
 * اعلان هدیه از سمت فرستنده.
 *
 * @param int $gift_by شناسه فرستنده.
 * @param int $plan_id شناسه پلن.
 * @return void
 */
function zc_subscription_notify_gift_sent( $gift_by, $plan_id ) {
	zc_subscription_notify( 'gift_sent', $gift_by, $plan_id );
}

/* ==========================================================================
   ۱۵) کرون بررسی انقضا و اعمال تنزل
   ========================================================================== */

/**
 * زمان‌بندی کرون روزانه.
 *
 * @return void
 */
function zc_subscription_schedule_cron() {
	if ( ! wp_next_scheduled( 'zc_subscription_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'zc_subscription_daily' );
	}
}
add_action( 'init', 'zc_subscription_schedule_cron' );

/**
 * بررسی روزانه: انقضای اشتراک‌ها، اعمال تنزلِ زمان‌بندی‌شده، اعلان.
 *
 * @return void
 */
function zc_subscription_daily_check() {
	$users = get_users(
		array(
			'fields'     => 'ID',
			'meta_key'   => 'zc_subscription',
			'number'     => 2000,
		)
	);

	foreach ( $users as $user_id ) {
		$rec = zc_subscription_get_user( $user_id );
		if ( empty( $rec['plan_id'] ) || empty( $rec['expires'] ) ) {
			continue;
		}

		// اعمال تنزل زمان‌بندی‌شده هنگام انقضا.
		$pending = zc_subscription_pending_plan( $user_id );

		if ( (int) $rec['expires'] <= time() ) {
			if ( $pending && zc_subscription_plan_enabled( $pending ) ) {
				zc_subscription_grant( $user_id, $pending, 0 );
				update_user_meta( $user_id, 'zc_sub_pending_plan', '' );
				zc_subscription_notify( 'upgraded', $user_id, $pending );
			} else {
				$rec['status'] = 'expired';
				update_user_meta( $user_id, 'zc_subscription', $rec );
				zc_subscription_notify( 'expired', $user_id, (int) $rec['plan_id'] );
			}
		}
	}
}
add_action( 'zc_subscription_daily', 'zc_subscription_daily_check' );

/* ==========================================================================
   ۱۶) گزارش درآمد اشتراک‌ها
   ========================================================================== */

/**
 * آمار و گزارش درآمد اشتراک‌ها.
 *
 * @return array
 */
function zc_subscription_report() {
	$plans   = zc_subscription_plans( true );
	$plan_map = array();
	foreach ( $plans as $p ) {
		$plan_map[ $p->ID ] = $p->post_title;
	}

	$plan_revenue  = array();
	$monthly       = array();
	$total         = 0;
	$order_count   = 0;
	$product_ids   = array();

	foreach ( $plans as $p ) {
		$pid = (int) zc_subscription_plan_data( $p->ID )['product_id'];
		if ( $pid ) {
			$product_ids[ $pid ] = $p->ID;
		}
	}

	// درآمد از سفارش‌های ووکامرسِ حاوی پلن.
	if ( function_exists( 'wc_get_orders' ) ) {
		$orders = wc_get_orders(
			array(
				'limit'  => 5000,
				'status' => array( 'completed', 'processing' ),
			)
		);

		foreach ( $orders as $order ) {
			$matched = false;
			foreach ( $order->get_items() as $item ) {
				$pid = $item->get_product_id();
				if ( isset( $product_ids[ $pid ] ) ) {
					$matched = true;
					$plan_id = $product_ids[ $pid ];
					$plan_revenue[ $plan_id ] = (float) ( $plan_revenue[ $plan_id ] ?? 0 ) + (float) $order->get_total();
					break;
				}
			}
			if ( $matched ) {
				$total       += (float) $order->get_total();
				$order_count++;
				$key          = gmdate( 'Y-m', (int) $order->get_date_created()->getTimestamp() );
				$monthly[ $key ] = (float) ( $monthly[ $key ] ?? 0 ) + (float) $order->get_total();
			}
		}
	}

	// درآمد از کیف پول (کسرِ «خرید اشتراک»).
	global $wpdb;
	$table = $wpdb->prefix . 'zc_transactions';
	$tbl_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore
	if ( $tbl_exists ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wallet_tx = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE type = %s ORDER BY id DESC", 'withdraw' ) );
		foreach ( (array) $wallet_tx as $tx ) {
			if ( false !== mb_strpos( (string) $tx->description, 'اشتراک' ) ) {
				$total       += abs( (float) $tx->amount );
				$order_count++;
				$key          = gmdate( 'Y-m', strtotime( (string) $tx->created_at ) );
				$monthly[ $key ] = (float) ( $monthly[ $key ] ?? 0 ) + abs( (float) $tx->amount );
			}
		}
	}

	// تعداد مشترک فعال و ماهِ جاری.
	$active_count = 0;
	$month_new    = 0;
	$month_key    = gmdate( 'Ym' );
	$users        = get_users( array( 'fields' => 'ID', 'meta_key' => 'zc_subscription', 'number' => 5000 ) );

	foreach ( $users as $uid ) {
		if ( zc_subscription_is_active( $uid ) ) {
			$active_count++;
			$rec = zc_subscription_get_user( $uid );
			if ( gmdate( 'Ym', (int) $rec['purchased_at'] ) === $month_key ) {
				$month_new++;
			}
		}
	}

	// MRR: جمع ارزش ماهانه‌ی همه‌ی اشتراک‌های فعال.
	$mrr = 0;
	foreach ( $users as $uid ) {
		if ( ! zc_subscription_is_active( $uid ) ) {
			continue;
		}
		$rec  = zc_subscription_get_user( $uid );
		$d    = zc_subscription_plan_data( $rec['plan_id'] );
		$secs = zc_subscription_duration_seconds( $d );
		if ( $secs > 0 ) {
			$mrr += (float) $d['price'] * ( MONTH_IN_SECONDS / $secs );
		}
	}

	// مرتب‌سازی ماه‌های اخیر.
	krsort( $monthly );
	$monthly = array_slice( $monthly, 0, 12, true );

	$plan_revenue_clean = array();
	foreach ( $plan_revenue as $pid => $rev ) {
		$plan_revenue_clean[ $plan_map[ $pid ] ?? ( '#' . $pid ) ] = $rev;
	}

	return array(
		'total_revenue' => $total,
		'order_count'   => $order_count,
		'plan_revenue'  => $plan_revenue_clean,
		'monthly'       => $monthly,
		'active_count'  => $active_count,
		'month_new'     => $month_new,
		'mrr'           => $mrr,
	);
}

/* ==========================================================================
   ۱۷) گزارش درآمد — صفحه مدیریتی
   ========================================================================== */

/**
 * ثبت زیرمنوی «گزارش اشتراک‌ها» در پیشخوان.
 *
 * @return void
 */
function zc_subscription_report_menu() {
	add_submenu_page(
		'edit.php?post_type=zc_subscription',
		__( 'گزارش درآمد اشتراک‌ها', 'zarincode' ),
		__( 'گزارش درآمد', 'zarincode' ),
		'manage_options',
		'zc-subscription-report',
		'zc_subscription_report_page'
	);
}
add_action( 'admin_menu', 'zc_subscription_report_menu' );

/**
 * صفحه‌ی گزارش درآمد.
 *
 * @return void
 */
function zc_subscription_report_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = zc_subscription_report();

	// خروجی CSV.
	if ( isset( $_GET['zc_sub_csv'] ) ) {
		$plan_name = isset( $_GET['plan'] ) ? (int) $_GET['plan'] : 0;
		zc_subscription_report_csv( $plan_name );
		exit;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'گزارش درآمد اشتراک‌ها', 'zarincode' ); ?></h1>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:20px 0">
			<?php
			$cards = array(
				array( __( 'درآمد کل', 'zarincode' ), number_format( $report['total_revenue'] ), 'green' ),
				array( __( 'درآمد ماهانه (MRR)', 'zarincode' ), number_format( $report['mrr'] ), 'blue' ),
				array( __( 'مشترک فعال', 'zarincode' ), zc_fa_num( $report['active_count'] ), 'purple' ),
				array( __( 'مشترک جدید این ماه', 'zarincode' ), zc_fa_num( $report['month_new'] ), 'orange' ),
				array( __( 'تعداد خرید', 'zarincode' ), zc_fa_num( $report['order_count'] ), 'gray' ),
			);
			foreach ( $cards as $c ) :
				?>
				<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.04)">
					<div style="color:#6b7280;font-size:.85rem"><?php echo esc_html( $c[0] ); ?></div>
					<div style="font-size:1.6rem;font-weight:800;margin-top:6px;color:#1f2437"><?php echo esc_html( $c[1] ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px">
				<h2 style="margin-top:0;font-size:1.1rem"><?php esc_html_e( 'درآمد هر پلن', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'پلن', 'zarincode' ); ?></th><th><?php esc_html_e( 'درآمد', 'zarincode' ); ?></th></tr></thead>
					<tbody>
					<?php if ( $report['plan_revenue'] ) : ?>
						<?php foreach ( $report['plan_revenue'] as $name => $rev ) : ?>
							<tr><td><?php echo esc_html( $name ); ?></td><td><?php echo esc_html( number_format( $rev ) ); ?></td></tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="2"><?php esc_html_e( 'هنوز درآمدی ثبت نشده است.', 'zarincode' ); ?></td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px">
				<h2 style="margin-top:0;font-size:1.1rem"><?php esc_html_e( 'درآمد ماهانه', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'ماه', 'zarincode' ); ?></th><th><?php esc_html_e( 'درآمد', 'zarincode' ); ?></th></tr></thead>
					<tbody>
					<?php if ( $report['monthly'] ) : ?>
						<?php foreach ( $report['monthly'] as $key => $rev ) : ?>
							<tr><td><?php echo esc_html( $key ); ?></td><td><?php echo esc_html( number_format( $rev ) ); ?></td></tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="2"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=zc_subscription&page=zc-subscription-report&zc_sub_csv=1' ) ); ?>">
				<?php esc_html_e( 'خروجی CSV سفارش‌ها', 'zarincode' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * خروجی CSV سفارش‌های اشتراک.
 *
 * @param int $plan_filter فیلتر پلن (اختیاری).
 * @return void
 */
function zc_subscription_report_csv( $plan_filter = 0 ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="zarincode-subscriptions-' . gmdate( 'Ymd-His' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, "\xEF\xBB\xBF" ); // BOM برای اکسل.
	fputcsv( $out, array( 'سفارش', 'کاربر', 'پلن', 'مبلغ', 'تاریخ', 'وضعیت' ) );

	$plans   = zc_subscription_plans( true );
	$product_map = array();
	foreach ( $plans as $p ) {
		$pid = (int) zc_subscription_plan_data( $p->ID )['product_id'];
		if ( $pid ) {
			$product_map[ $pid ] = $p->post_title;
		}
	}

	if ( function_exists( 'wc_get_orders' ) ) {
		$orders = wc_get_orders( array( 'limit' => 5000, 'status' => array( 'completed', 'processing' ) ) );
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$pid = $item->get_product_id();
				if ( isset( $product_map[ $pid ] ) && ( ! $plan_filter || $plan_filter === $product_map[ $pid ] ) ) {
					$user = $order->get_user();
					fputcsv(
						$out,
						array(
							'#' . $order->get_id(),
							$user ? $user->user_email : $order->get_billing_email(),
							$product_map[ $pid ],
							$order->get_total(),
							$order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y/m/d' ) : '',
							$order->get_status(),
						)
					);
					break;
				}
			}
		}
	}

	fclose( $out );
}

/* ==========================================================================
   ۱۸) انتقال هدیه در تسویه‌ی ووکامرس
   ========================================================================== */

/**
 * ثبت گیرنده‌ی هدیه روی سفارش هنگام ایجاد در تسویه.
 *
 * @param \WC_Order $order  سفارش.
 * @param array     $data   داده‌ها.
 * @return void
 */
function zc_subscription_gift_on_checkout( $order, $data ) {
	$user_id = $order->get_user_id();

	if ( $user_id ) {
		$pending = zc_subscription_take_pending_gift( $user_id );
		if ( ! empty( $pending['to'] ) ) {
			$order->update_meta_data( '_zc_gift_for', sanitize_text_field( $pending['to'] ) );
		}
	}
}
add_action( 'woocommerce_checkout_create_order', 'zc_subscription_gift_on_checkout', 10, 2 );

/* ==========================================================================
   ۱۹) ستون‌های مدیریتی پلن‌ها
   ========================================================================== */

/**
 * افزودن ستون‌های سفارشی به فهرست پلن‌ها.
 *
 * @param array $columns ستون‌ها.
 * @return array
 */
function zc_subscription_admin_columns( $columns ) {
	$out = array();
	foreach ( $columns as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['zc_sub_tier']  = __( 'سطح', 'zarincode' );
			$out['zc_sub_price'] = __( 'قیمت', 'zarincode' );
			$out['zc_sub_users'] = __( 'مشترک فعال', 'zarincode' );
			$out['zc_sub_state'] = __( 'وضعیت', 'zarincode' );
		}
	}
	return $out;
}
add_filter( 'manage_zc_subscription_posts_columns', 'zc_subscription_admin_columns' );

/**
 * نمایش محتوای ستون‌های سفارشی.
 *
 * @param string $column  نام ستون.
 * @param int    $post_id شناسه پست.
 * @return void
 */
function zc_subscription_admin_columns_content( $column, $post_id ) {
	$d = zc_subscription_plan_data( $post_id );

	if ( 'zc_sub_tier' === $column ) {
		echo esc_html( zc_fa_num( zc_subscription_plan_tier( $post_id ) ) );
	} elseif ( 'zc_sub_price' === $column ) {
		echo esc_html( number_format( $d['price'] ) ) . ' ' . esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) );
	} elseif ( 'zc_sub_users' === $column ) {
		$count = 0;
		$users = get_users( array( 'fields' => 'ID', 'meta_key' => 'zc_subscription', 'number' => 5000 ) );
		foreach ( $users as $uid ) {
			if ( zc_subscription_is_active( $uid ) && (int) zc_subscription_get_user( $uid )['plan_id'] === $post_id ) {
				$count++;
			}
		}
		echo esc_html( zc_fa_num( $count ) );
	} elseif ( 'zc_sub_state' === $column ) {
		if ( zc_subscription_plan_enabled( $post_id ) ) {
			echo '<span style="color:#16a34a">' . esc_html__( 'فعال', 'zarincode' ) . '</span>';
		} else {
			echo '<span style="color:#dc2626">' . esc_html__( 'غیرفعال', 'zarincode' ) . '</span>';
		}
	}
}
add_action( 'manage_zc_subscription_posts_custom_column', 'zc_subscription_admin_columns_content', 10, 2 );

/**
 * ستون سطح قابل مرتب‌سازی.
 *
 * @param array $columns ستون‌ها.
 * @return array
 */
function zc_subscription_sortable_columns( $columns ) {
	$columns['zc_sub_tier']  = 'zc_sub_tier';
	$columns['zc_sub_price'] = 'zc_sub_price';
	return $columns;
}
add_filter( 'manage_edit-zc_subscription_sortable_columns', 'zc_subscription_sortable_columns' );
