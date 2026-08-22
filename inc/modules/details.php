<?php
/**
 * سامانه جزئیات کامل محصول و نمونه‌کار
 *
 * یک «طرح داده» (schema) واحد برای همه‌ی فیلدهای جزئیات تعریف می‌کند و
 * از روی همان طرح، هم متاباکس پیشخوان ساخته می‌شود، هم ذخیره‌سازی، هم
 * خروجی سمت کاربر.
 *
 * قاعده‌ی اصلی: هر فیلدی که پر شده باشد نمایش داده می‌شود و هر فیلدی
 * که خالی باشد به‌طور کامل از صفحه حذف می‌شود — نه برچسب، نه خط جدا‌کننده،
 * نه بخش خالی.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ۱. طرح داده‌ی فیلدها
   ========================================================================== */

/**
 * فیلدهای جزئیات محصول فروشگاه.
 *
 * نوع‌ها:
 *  text     یک خط متن
 *  url      نشانی اینترنتی
 *  textarea پاراگراف
 *  lines    فهرست؛ هر خط یک آیتم
 *  rows     جدول؛ هر خط «ستون یک | ستون دو»
 *  log      تاریخچه؛ هر خط «نسخه | تاریخ | شرح»
 *
 * @return array
 */
function zc_product_detail_fields() {
	$fields = array(

		/* --- شناسنامه --- */
		'_zc_version'        => array(
			'label' => __( 'نسخه فعلی', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'tag',
			'ltr'   => true,
			'hint'  => __( 'مثلاً ۲.۴.۰', 'zarincode' ),
		),
		'_zc_product_author' => array(
			'label' => __( 'سازنده / تیم', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'user',
		),
		'_zc_release_date'   => array(
			'label' => __( 'تاریخ انتشار', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'calendar',
			'hint'  => __( 'مثلاً ۰۶ مهر ۱۴۰۴', 'zarincode' ),
		),
		'_zc_last_update'    => array(
			'label' => __( 'آخرین بروزرسانی', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'refresh',
			'hint'  => __( 'مثلاً مرداد ۱۴۰۵', 'zarincode' ),
		),
		'_zc_file_format'    => array(
			'label' => __( 'فرمت فایل', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'file',
			'hint'  => __( 'مثلاً ZIP شامل PHP، CSS، JS', 'zarincode' ),
		),
		'_zc_file_size'      => array(
			'label' => __( 'حجم فایل', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'download',
			'hint'  => __( 'مثلاً ۱۲ مگابایت', 'zarincode' ),
		),
		'_zc_license'        => array(
			'label' => __( 'نوع لایسنس', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'shield',
			'hint'  => __( 'مثلاً یک‌بار خرید، استفاده نامحدود', 'zarincode' ),
		),
		'_zc_support_period' => array(
			'label' => __( 'مدت پشتیبانی', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'chat',
			'hint'  => __( 'مثلاً ۶ ماه پشتیبانی رایگان', 'zarincode' ),
		),
		'_zc_update_period'  => array(
			'label' => __( 'مدت بروزرسانی', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'refresh',
			'hint'  => __( 'مثلاً بروزرسانی مادام‌العمر', 'zarincode' ),
		),

		/* --- پیش‌نمایش --- */
		'_zc_preview_url'    => array(
			'label' => __( 'لینک پیش‌نمایش اصلی', 'zarincode' ),
			'type'  => 'url',
			'group' => 'preview',
			'hint'  => __( 'دموی اصلی/انگلیسی محصول.', 'zarincode' ),
		),
		'_zc_preview_fa_url' => array(
			'label' => __( 'لینک پیش‌نمایش فارسی', 'zarincode' ),
			'type'  => 'url',
			'group' => 'preview',
			'hint'  => __( 'دموی فارسی و راست‌چین.', 'zarincode' ),
		),
		'_zc_docs_url'       => array(
			'label' => __( 'لینک مستندات', 'zarincode' ),
			'type'  => 'url',
			'group' => 'preview',
		),
		'_zc_video_url'      => array(
			'label' => __( 'ویدیوی معرفی', 'zarincode' ),
			'type'  => 'url',
			'group' => 'preview',
			'hint'  => __( 'لینک امبد آپارات یا یوتیوب.', 'zarincode' ),
		),
		'_zc_demo_login'     => array(
			'label' => __( 'اطلاعات ورود به دمو', 'zarincode' ),
			'type'  => 'text',
			'group' => 'preview',
			'hint'  => __( 'مثلاً demo / demo123', 'zarincode' ),
		),

		/* --- فهرست‌ها --- */
		'_zc_benefits'       => array(
			'label' => __( 'مزیت‌های خرید', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'هر مزیت در یک خط. کنار دکمه خرید نمایش داده می‌شود.', 'zarincode' ),
		),
		'_zc_features'       => array(
			'label' => __( 'ویژگی‌های کامل محصول', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'هر ویژگی در یک خط.', 'zarincode' ),
		),
		'_zc_included'       => array(
			'label' => __( 'محتویات بسته', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'چه چیزهایی همراه فایل تحویل می‌شود.', 'zarincode' ),
		),
		'_zc_requirements'   => array(
			'label' => __( 'پیش‌نیازها', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
		),

		/* --- جدول‌ها --- */
		'_zc_specs'          => array(
			'label' => __( 'مشخصات فنی', 'zarincode' ),
			'type'  => 'rows',
			'group' => 'tables',
			'hint'  => __( 'هر خط: عنوان | مقدار', 'zarincode' ),
		),
		'_zc_compat'         => array(
			'label' => __( 'سازگاری با', 'zarincode' ),
			'type'  => 'rows',
			'group' => 'tables',
			'hint'  => __( 'هر خط: عنوان | مقدار — مثال: وردپرس | ۶.۰ تا ۶.۷', 'zarincode' ),
		),
		'_zc_changelog'      => array(
			'label' => __( 'تاریخچه تغییرات', 'zarincode' ),
			'type'  => 'log',
			'group' => 'tables',
			'hint'  => __( 'هر خط: نسخه | تاریخ | شرح تغییرات', 'zarincode' ),
		),
	);

	/**
	 * فیلتر فیلدهای جزئیات محصول.
	 *
	 * @param array $fields فیلدها.
	 */
	return apply_filters( 'zc_product_detail_fields', $fields );
}

/**
 * فیلدهای جزئیات نمونه‌کار.
 *
 * @return array
 */
function zc_project_detail_fields() {
	$fields = array(

		'_zc_project_client'    => array(
			'label' => __( 'نام کارفرما', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'user',
		),
		'_zc_project_url'       => array(
			'label' => __( 'لینک پروژه', 'zarincode' ),
			'type'  => 'url',
			'group' => 'identity',
			'hint'  => __( 'نشانی سایت یا اپلیکیشن منتشرشده.', 'zarincode' ),
		),
		'_zc_project_version'   => array(
			'label' => __( 'نسخه پروژه', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'tag',
			'ltr'   => true,
		),
		'_zc_project_date'      => array(
			'label' => __( 'تاریخ انجام', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'calendar',
			'hint'  => __( 'مثلاً بهار ۱۴۰۴', 'zarincode' ),
		),
		'_zc_project_duration'  => array(
			'label' => __( 'مدت انجام', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'clock',
			'hint'  => __( 'مثلاً ۳ ماه', 'zarincode' ),
		),
		'_zc_project_budget'    => array(
			'label' => __( 'بودجه پروژه', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'wallet',
		),
		'_zc_project_team'      => array(
			'label' => __( 'اعضای تیم', 'zarincode' ),
			'type'  => 'text',
			'group' => 'identity',
			'icon'  => 'users',
			'hint'  => __( 'مثلاً ۴ نفر: طراح، دو برنامه‌نویس، کارشناس سئو', 'zarincode' ),
		),
		'_zc_project_repo'      => array(
			'label' => __( 'مخزن کد', 'zarincode' ),
			'type'  => 'url',
			'group' => 'identity',
		),

		'_zc_project_challenge' => array(
			'label' => __( 'چالش پروژه', 'zarincode' ),
			'type'  => 'textarea',
			'group' => 'story',
			'hint'  => __( 'مسئله‌ای که کارفرما داشت.', 'zarincode' ),
		),
		'_zc_project_solution'  => array(
			'label' => __( 'راهکار ما', 'zarincode' ),
			'type'  => 'textarea',
			'group' => 'story',
		),

		'_zc_project_features'  => array(
			'label' => __( 'ویژگی‌های کامل پروژه', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'هر ویژگی در یک خط.', 'zarincode' ),
		),
		'_zc_project_results'   => array(
			'label' => __( 'دستاوردها', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'مثال: افزایش ۳۰۰٪ ترافیک ارگانیک', 'zarincode' ),
		),
		'_zc_project_tech_list' => array(
			'label' => __( 'تکنولوژی‌های استفاده‌شده', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
			'hint'  => __( 'هر مورد در یک خط. مثال: Laravel 11', 'zarincode' ),
		),
		'_zc_project_services'  => array(
			'label' => __( 'خدمات ارائه‌شده', 'zarincode' ),
			'type'  => 'lines',
			'group' => 'lists',
		),

		'_zc_project_stats'     => array(
			'label' => __( 'آمار پروژه', 'zarincode' ),
			'type'  => 'rows',
			'group' => 'tables',
			'hint'  => __( 'هر خط: عنوان | مقدار — مثال: افزایش فروش | ۲۱۰٪', 'zarincode' ),
		),
		'_zc_project_phases'    => array(
			'label' => __( 'مراحل اجرا', 'zarincode' ),
			'type'  => 'log',
			'group' => 'tables',
			'hint'  => __( 'هر خط: مرحله | زمان | شرح', 'zarincode' ),
		),

		'_zc_project_quote'     => array(
			'label' => __( 'نظر کارفرما', 'zarincode' ),
			'type'  => 'textarea',
			'group' => 'quote',
		),
		'_zc_project_quote_by'  => array(
			'label' => __( 'گوینده نظر', 'zarincode' ),
			'type'  => 'text',
			'group' => 'quote',
			'hint'  => __( 'مثلاً مهدی رضایی، مدیرعامل', 'zarincode' ),
		),

		'_zc_project_gallery'   => array(
			'label' => __( 'گالری تصاویر', 'zarincode' ),
			'type'  => 'text',
			'group' => 'media',
			'ltr'   => true,
			'hint'  => __( 'شناسه تصاویر با کاما. مثال: 12,15,18', 'zarincode' ),
		),
	);

	/**
	 * فیلتر فیلدهای جزئیات نمونه‌کار.
	 *
	 * @param array $fields فیلدها.
	 */
	return apply_filters( 'zc_project_detail_fields', $fields );
}

/* ==========================================================================
   ۲. خواندن و تجزیه‌ی مقادیر
   ========================================================================== */

/**
 * مقدار خام یک فیلد.
 *
 * @param int    $post_id شناسه.
 * @param string $key     کلید متا.
 * @return mixed
 */
function zc_detail_raw( $post_id, $key ) {
	$value = get_post_meta( $post_id, $key, true );

	// برخی فیلدهای قدیمی به‌صورت آرایه ذخیره شده‌اند.
	if ( is_array( $value ) ) {
		$value = implode( "\n", array_filter( $value, 'is_scalar' ) );
	}

	if ( ! is_string( $value ) ) {
		return $value;
	}

	/*
	 * مرورگرها محتوای textarea را با CRLF می‌فرستند؛ اگر نرمال نشود،
	 * هر آیتم فهرست یک \r اضافه در انتها می‌گیرد که در HTML دیده
	 * نمی‌شود ولی در مقایسه‌ها و خروجی JSON مشکل می‌سازد.
	 */
	return trim( str_replace( array( "\r\n", "\r" ), "\n", $value ) );
}

/**
 * مقدار آماده‌ی نمایش یک فیلد بر اساس نوع آن.
 *
 * برای انواع فهرستی، آرایه برمی‌گرداند؛ در نبود مقدار، آرایه یا رشته‌ی
 * خالی که با یک شرط ساده قابل بررسی است.
 *
 * @param int    $post_id شناسه.
 * @param string $key     کلید متا.
 * @param string $type    نوع فیلد.
 * @return mixed
 */
function zc_detail_value( $post_id, $key, $type = 'text' ) {
	$raw = zc_detail_raw( $post_id, $key );

	if ( '' === $raw || null === $raw ) {
		return in_array( $type, array( 'lines', 'rows', 'log' ), true ) ? array() : '';
	}

	switch ( $type ) {
		case 'lines':
			return array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) );

		case 'rows':
			$out = array();

			foreach ( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) as $line ) {
				$parts = array_map( 'trim', explode( '|', $line, 2 ) );

				// خط بدون جداکننده هم پذیرفته می‌شود.
				$out[] = array(
					'label' => $parts[0],
					'value' => $parts[1] ?? '',
				);
			}

			return $out;

		case 'log':
			$out = array();

			foreach ( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) as $line ) {
				$parts = array_map( 'trim', explode( '|', $line, 3 ) );

				$out[] = array(
					'version' => $parts[0],
					'date'    => $parts[1] ?? '',
					'text'    => $parts[2] ?? '',
				);
			}

			return $out;

		default:
			return $raw;
	}
}

/**
 * آیا این فیلد مقدار دارد؟
 *
 * @param int    $post_id شناسه.
 * @param string $key     کلید.
 * @param string $type    نوع.
 * @return bool
 */
function zc_has_detail( $post_id, $key, $type = 'text' ) {
	$value = zc_detail_value( $post_id, $key, $type );

	return is_array( $value ) ? ! empty( $value ) : '' !== $value;
}

/**
 * همه‌ی فیلدهای پرشده‌ی یک گروه.
 *
 * خروجی فقط شامل فیلدهایی است که مقدار دارند، پس قالب می‌تواند بدون
 * هیچ شرط اضافه‌ای روی آن حلقه بزند و بخش خالی هرگز ساخته نمی‌شود.
 *
 * @param int    $post_id شناسه.
 * @param array  $schema  طرح فیلدها.
 * @param string $group   نام گروه؛ خالی یعنی همه.
 * @return array
 */
function zc_detail_group( $post_id, $schema, $group = '' ) {
	$out = array();

	foreach ( $schema as $key => $field ) {
		if ( $group && ( $field['group'] ?? '' ) !== $group ) {
			continue;
		}

		$type  = $field['type'] ?? 'text';
		$value = zc_detail_value( $post_id, $key, $type );

		if ( is_array( $value ) ? empty( $value ) : '' === $value ) {
			continue;
		}

		$field['key']   = $key;
		$field['value'] = $value;
		$out[ $key ]    = $field;
	}

	return $out;
}

/* ==========================================================================
   ۳. متاباکس پیشخوان
   ========================================================================== */

/**
 * برچسب فارسی گروه‌ها.
 *
 * @return array
 */
function zc_detail_group_labels() {
	return array(
		'identity' => __( 'شناسنامه', 'zarincode' ),
		'preview'  => __( 'پیش‌نمایش و لینک‌ها', 'zarincode' ),
		'story'    => __( 'شرح پروژه', 'zarincode' ),
		'lists'    => __( 'فهرست‌ها', 'zarincode' ),
		'tables'   => __( 'جدول‌ها و تاریخچه', 'zarincode' ),
		'quote'    => __( 'نظر کارفرما', 'zarincode' ),
		'media'    => __( 'رسانه', 'zarincode' ),
	);
}

/**
 * رسم یک فیلد در متاباکس.
 *
 * @param int    $post_id شناسه.
 * @param string $key     کلید.
 * @param array  $field   تعریف فیلد.
 * @return void
 */
function zc_detail_render_field( $post_id, $key, $field ) {
	$type  = $field['type'] ?? 'text';
	$value = zc_detail_raw( $post_id, $key );
	$dir   = ! empty( $field['ltr'] ) || 'url' === $type ? ' dir="ltr"' : '';
	$id    = 'zcd' . $key;
	$rows  = 'log' === $type ? 6 : ( in_array( $type, array( 'lines', 'rows' ), true ) ? 5 : 3 );
	?>
	<div class="zc-detail-field zc-detail-field--<?php echo esc_attr( $type ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>

		<?php if ( in_array( $type, array( 'textarea', 'lines', 'rows', 'log' ), true ) ) : ?>
			<textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $id ); ?>"
				rows="<?php echo (int) $rows; ?>"<?php echo $dir; // phpcs:ignore ?>><?php echo esc_textarea( $value ); ?></textarea>
		<?php else : ?>
			<input type="<?php echo 'url' === $type ? 'url' : 'text'; ?>"
				name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $id ); ?>"
				value="<?php echo esc_attr( $value ); ?>"<?php echo $dir; // phpcs:ignore ?> />
		<?php endif; ?>

		<?php if ( ! empty( $field['hint'] ) ) : ?>
			<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * رسم کامل متاباکس از روی طرح داده.
 *
 * @param WP_Post $post   نوشته.
 * @param array   $schema طرح.
 * @return void
 */
function zc_detail_render_metabox( $post, $schema ) {
	wp_nonce_field( 'zc_details_save', 'zc_details_nonce' );

	$groups = array();

	foreach ( $schema as $key => $field ) {
		$groups[ $field['group'] ?? 'identity' ][ $key ] = $field;
	}

	$labels = zc_detail_group_labels();
	$first  = true;
	?>
	<div class="zc-details-box">
		<p class="zc-details-note">
			<?php esc_html_e( 'هر فیلدی که پر کنید در صفحه‌ی سایت نمایش داده می‌شود و هر فیلدی که خالی بماند به‌طور کامل حذف می‌شود.', 'zarincode' ); ?>
		</p>

		<div class="zc-details-tabs">
			<?php foreach ( array_keys( $groups ) as $g ) : ?>
				<button type="button" class="zc-details-tab<?php echo $first ? ' is-active' : ''; ?>"
					data-tab="<?php echo esc_attr( $g ); ?>">
					<?php echo esc_html( $labels[ $g ] ?? $g ); ?>
				</button>
				<?php $first = false; ?>
			<?php endforeach; ?>
		</div>

		<?php
		$first = true;

		foreach ( $groups as $g => $fields ) :
			?>
			<div class="zc-details-panel<?php echo $first ? ' is-active' : ''; ?>" data-panel="<?php echo esc_attr( $g ); ?>">
				<?php
				foreach ( $fields as $key => $field ) {
					zc_detail_render_field( $post->ID, $key, $field );
				}
				?>
			</div>
			<?php
			$first = false;
		endforeach;
		?>
	</div>

	<script>
		( function () {
			var box = document.currentScript.closest( '.postbox' ) || document;

			box.querySelectorAll( '.zc-details-tab' ).forEach( function ( tab ) {
				tab.addEventListener( 'click', function () {
					box.querySelectorAll( '.zc-details-tab' ).forEach( function ( t ) { t.classList.remove( 'is-active' ); } );
					box.querySelectorAll( '.zc-details-panel' ).forEach( function ( p ) { p.classList.remove( 'is-active' ); } );
					tab.classList.add( 'is-active' );

					var panel = box.querySelector( '[data-panel="' + tab.dataset.tab + '"]' );
					if ( panel ) { panel.classList.add( 'is-active' ); }
				} );
			} );
		} )();
	</script>
	<?php
}

/**
 * ثبت متاباکس‌های جزئیات.
 *
 * @return void
 */
function zc_register_detail_metaboxes() {
	add_meta_box(
		'zc_product_details',
		__( 'جزئیات کامل محصول (زرین کد)', 'zarincode' ),
		'zc_product_details_metabox',
		'product',
		'normal',
		'high'
	);

	add_meta_box(
		'zc_project_details_full',
		__( 'جزئیات کامل نمونه‌کار (زرین کد)', 'zarincode' ),
		'zc_project_details_metabox',
		'zc_project',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zc_register_detail_metaboxes', 20 );

/**
 * متاباکس محصول.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_product_details_metabox( $post ) {
	zc_detail_render_metabox( $post, zc_product_detail_fields() );
}

/**
 * متاباکس نمونه‌کار.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_project_details_metabox( $post ) {
	zc_detail_render_metabox( $post, zc_project_detail_fields() );
}

/**
 * ذخیره‌ی فیلدهای جزئیات.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_save_detail_fields( $post_id ) {
	if ( ! isset( $_POST['zc_details_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zc_details_nonce'] ) ), 'zc_details_save' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = get_post_type( $post_id );

	if ( 'product' === $type ) {
		$schema = zc_product_detail_fields();
	} elseif ( 'zc_project' === $type ) {
		$schema = zc_project_detail_fields();
	} else {
		return;
	}

	foreach ( $schema as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( 'url' === ( $field['type'] ?? '' ) ) {
			$clean = esc_url_raw( $raw );
		} elseif ( in_array( $field['type'] ?? '', array( 'textarea', 'lines', 'rows', 'log' ), true ) ) {
			$clean = sanitize_textarea_field( $raw );
		} else {
			$clean = sanitize_text_field( $raw );
		}

		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}
}
add_action( 'save_post_product', 'zc_save_detail_fields' );
add_action( 'save_post_zc_project', 'zc_save_detail_fields' );

/* ==========================================================================
   ۴. توابع کمکی نمایش
   ========================================================================== */

/**
 * جدول مشخصات: فقط سطرهای دارای مقدار.
 *
 * @param array  $rows  سطرها (label/value).
 * @param string $class کلاس افزوده.
 * @return void
 */
function zc_render_spec_table( $rows, $class = '' ) {
	$rows = array_filter(
		(array) $rows,
		static function ( $r ) {
			return ! empty( $r['label'] );
		}
	);

	if ( ! $rows ) {
		return;
	}
	?>
	<ul class="zc-spec-table <?php echo esc_attr( $class ); ?>">
		<?php foreach ( $rows as $row ) : ?>
			<li>
				<span class="zc-spec-table__k"><?php echo esc_html( $row['label'] ); ?></span>
				<span class="zc-spec-table__v"><?php echo esc_html( zc_fa_num( $row['value'] ) ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * فهرست تیک‌دار.
 *
 * @param array  $items آیتم‌ها.
 * @param string $class کلاس.
 * @param string $icon  آیکون.
 * @return void
 */
function zc_render_check_list( $items, $class = '', $icon = 'check' ) {
	$items = array_filter( (array) $items );

	if ( ! $items ) {
		return;
	}
	?>
	<ul class="zc-check-list <?php echo esc_attr( $class ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<li><?php zc_the_icon( $icon, 17 ); ?><span><?php echo esc_html( $item ); ?></span></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * تایم‌لاین تاریخچه‌ی تغییرات یا مراحل اجرا.
 *
 * @param array $entries    ردیف‌ها (version/date/text).
 * @param bool  $show_badge نشان «آخرین نسخه» روی ردیف اول؛ برای
 *                          مراحل اجرا باید خاموش باشد.
 * @return void
 */
function zc_render_changelog( $entries, $show_badge = true ) {
	$entries = array_filter(
		(array) $entries,
		static function ( $e ) {
			return ! empty( $e['version'] );
		}
	);

	if ( ! $entries ) {
		return;
	}
	?>
	<ol class="zc-changelog">
		<?php foreach ( $entries as $i => $entry ) : ?>
			<li class="zc-changelog__item<?php echo 0 === $i ? ' is-latest' : ''; ?>">
				<div class="zc-changelog__head">
					<span class="zc-changelog__ver" dir="ltr"><?php echo esc_html( $entry['version'] ); ?></span>

					<?php if ( ! empty( $entry['date'] ) ) : ?>
						<span class="zc-changelog__date"><?php echo esc_html( zc_fa_num( $entry['date'] ) ); ?></span>
					<?php endif; ?>

					<?php if ( 0 === $i && $show_badge ) : ?>
						<span class="zc-changelog__badge"><?php esc_html_e( 'آخرین نسخه', 'zarincode' ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $entry['text'] ) ) : ?>
					<p class="zc-changelog__text"><?php echo esc_html( $entry['text'] ); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
}

/**
 * ساخت آرایه‌ی «شناسنامه» برای جدول کناری از روی گروه identity.
 *
 * @param int   $post_id شناسه.
 * @param array $schema  طرح.
 * @return array سطرهای label/value.
 */
function zc_detail_identity_rows( $post_id, $schema ) {
	$rows = array();

	foreach ( zc_detail_group( $post_id, $schema, 'identity' ) as $field ) {
		if ( 'url' === ( $field['type'] ?? '' ) ) {
			continue; // لینک‌ها جای دیگری به‌صورت دکمه می‌آیند.
		}

		$rows[] = array(
			'label' => $field['label'],
			'value' => $field['value'],
		);
	}

	return $rows;
}
