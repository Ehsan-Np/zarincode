<?php
/**
 * پیکربندی Redux Framework
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * راه‌اندازی Redux.
 *
 * @return void
 */
function zc_redux_init() {
	if ( ! class_exists( 'Redux' ) ) {
		return;
	}

	$opt_name = ZC_PREFIX;

	/* ---------- تنظیمات کلی پنل ---------- */
	$args = array(
		'opt_name'             => $opt_name,
		'display_name'         => __( 'تنظیمات قالب زرین کد', 'zarincode' ),
		'display_version'      => ZC_VERSION,
		/*
		 * پنل ردوکس به‌جای منوی سطح‌بالای جداگانه، به‌عنوان زیرمنوی
		 * «زرین کد» ثبت می‌شود تا در پیشخوان فقط یک ورودی برای قالب
		 * وجود داشته باشد.
		 */
		'menu_type'            => 'submenu',
		'allow_sub_menu'       => false,
		'menu_title'           => __( 'تنظیمات قالب', 'zarincode' ),
		'page_title'           => __( 'پنل تنظیمات قالب زرین کد', 'zarincode' ),
		'google_api_key'       => '',
		'google_update_weekly' => false,
		'async_typography'     => false,
		'admin_bar'            => true,
		'admin_bar_icon'       => 'dashicons-admin-customizer',
		'admin_bar_priority'   => 50,
		'global_variable'      => 'zarincode_options',
		'dev_mode'             => false,
		'update_notice'        => false,
		'customizer'           => true,
		'page_priority'        => 3,
		'page_parent'          => 'zarincode',
		'page_permissions'     => 'manage_options',
		'menu_icon'            => 'dashicons-admin-customizer',
		'last_tab'             => '',
		'page_icon'            => 'icon-themes',
		'page_slug'            => 'zarincode-options',
		'save_defaults'        => true,
		'default_show'         => false,
		'default_mark'         => '*',
		'show_import_export'   => true,
		'transient_time'       => 60 * MINUTE_IN_SECONDS,
		'output'               => true,
		'output_tag'           => true,
		'database'             => 'options',
		'use_cdn'              => false,
		'hints'                => array(
			'icon_position' => 'right',
			'icon_color'    => '#C9A227',
			'icon_size'     => 'normal',
		),
		'footer_credit'        => sprintf(
			/* translators: %s: version */
			__( 'قالب زرین کد نسخه %s | طراحی و توسعه اختصاصی', 'zarincode' ),
			ZC_VERSION
		),
	);

	Redux::set_args( $opt_name, $args );

	/* ---------- بخش خوش‌آمدگویی ---------- */
	Redux::set_section(
		$opt_name,
		array(
			'title'  => __( 'خوش آمدید', 'zarincode' ),
			'id'     => 'zc_welcome',
			'icon'   => 'el el-star',
			'fields' => array(
				array(
					'id'    => 'zc_welcome_info',
					'type'  => 'info',
					'style' => 'success',
					'title' => __( 'به پنل تنظیمات قالب زرین کد خوش آمدید', 'zarincode' ),
					'desc'  => __( 'از منوی سمت راست می‌توانید تمام بخش‌های قالب را از هدر تا فوتر شخصی‌سازی کنید. برای ساخت صفحات از ویجت‌های اختصاصی «زرین کد» در المنتور استفاده کنید.', 'zarincode' ),
				),
				array(
					'id'    => 'zc_docs_info',
					'type'  => 'info',
					'style' => 'info',
					'title' => __( 'راهنمای سریع', 'zarincode' ),
					'desc'  => sprintf(
						'<ol style="line-height:2.2;padding-inline-start:20px">
							<li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li>
						</ol>',
						esc_html__( 'ابتدا افزونه‌های پیشنهادی (المنتور، ووکامرس) را نصب کنید.', 'zarincode' ),
						esc_html__( 'از بخش «درون‌ریزی دمو» محتوای نمونه را وارد کنید.', 'zarincode' ),
						esc_html__( 'کلید API کاوه‌نگار و مرچنت کد زرین‌پال را در بخش مربوطه وارد کنید.', 'zarincode' ),
						esc_html__( 'لوگو، رنگ‌بندی و اطلاعات تماس را تنظیم کنید.', 'zarincode' ),
						esc_html__( 'صفحه اصلی را با المنتور و ویجت‌های زرین کد بسازید.', 'zarincode' )
					),
				),
			),
		)
	);

	/* ---------- ساخت بخش‌ها از روی اسکیما ---------- */
	$schema = zc_settings_schema();

	foreach ( $schema as $section_id => $section ) {
		$fields = array();

		foreach ( $section['fields'] as $field ) {
			$fields[] = zc_map_field_to_redux( $field );
		}

		Redux::set_section(
			$opt_name,
			array(
				'title'  => $section['title'],
				'id'     => 'zc_section_' . $section_id,
				'icon'   => $section['icon'],
				'fields' => $fields,
			)
		);
	}

	/* ---------- بخش پشتیبانی ---------- */
	Redux::set_section(
		$opt_name,
		array(
			'title'  => __( 'پشتیبانی و راهنما', 'zarincode' ),
			'id'     => 'zc_support',
			'icon'   => 'el el-lifebuoy',
			'fields' => array(
				array(
					'id'    => 'zc_support_info',
					'type'  => 'info',
					'style' => 'warning',
					'title' => __( 'نیاز به کمک دارید؟', 'zarincode' ),
					'desc'  => __( 'برای دریافت پشتیبانی، مستندات کامل قالب را در فایل README.md مطالعه کنید یا با تیم پشتیبانی تماس بگیرید.', 'zarincode' ),
				),
				array(
					'id'      => 'zc_system_status',
					'type'    => 'raw',
					'title'   => __( 'وضعیت سیستم', 'zarincode' ),
					'content' => zc_system_status_html(),
				),
			),
		)
	);
}
add_action( 'init', 'zc_redux_init', 5 );

/**
 * تبدیل فیلد اسکیما به فرمت Redux.
 *
 * @param array $field فیلد.
 * @return array
 */
function zc_map_field_to_redux( $field ) {
	$out = array(
		'id'    => $field['id'],
		'type'  => $field['type'],
		'title' => $field['title'],
	);

	if ( isset( $field['desc'] ) ) {
		$out['subtitle'] = $field['desc'];
	}
	if ( isset( $field['default'] ) ) {
		$out['default'] = $field['default'];
	}
	if ( isset( $field['rows'] ) ) {
		$out['rows'] = $field['rows'];
	}

	// گزینه‌های پویا.
	if ( isset( $field['options'] ) ) {
		if ( 'pages' === $field['options'] ) {
			$out['options'] = zc_pages_list();
			$out['type']    = 'select';
		} elseif ( 'elementor_templates' === $field['options'] ) {
			$out['options'] = zc_get_elementor_templates();
			$out['type']    = 'select';
		} else {
			$out['options'] = $field['options'];
		}
	}

	// اسلایدر.
	if ( 'slider' === $field['type'] ) {
		$out['min']     = $field['min'] ?? 0;
		$out['max']     = $field['max'] ?? 100;
		$out['step']    = $field['step'] ?? 1;
		$out['display_value'] = 'text';
	}

	// رنگ.
	if ( 'color' === $field['type'] ) {
		$out['transparent'] = false;
		$out['validate']    = 'color';
	}

	// سوییچ.
	if ( 'switch' === $field['type'] ) {
		$out['on']  = __( 'فعال', 'zarincode' );
		$out['off'] = __( 'غیرفعال', 'zarincode' );
	}

	// اسلایدها (نمادها).
	if ( 'slides' === $field['type'] ) {
		$out['placeholder'] = array(
			'title'       => __( 'عنوان', 'zarincode' ),
			'description' => __( 'توضیح', 'zarincode' ),
			'url'         => __( 'لینک', 'zarincode' ),
		);
	}

	return $out;
}

/**
 * HTML وضعیت سیستم.
 *
 * @return string
 */
function zc_system_status_html() {
	$checks = array(
		array( __( 'نسخه PHP', 'zarincode' ), PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ) ),
		array( __( 'نسخه وردپرس', 'zarincode' ), get_bloginfo( 'version' ), version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ),
		array( __( 'المنتور', 'zarincode' ), zc_is_elementor() ? __( 'فعال', 'zarincode' ) : __( 'غیرفعال', 'zarincode' ), zc_is_elementor() ),
		array( __( 'ووکامرس', 'zarincode' ), zc_is_woo() ? __( 'فعال', 'zarincode' ) : __( 'غیرفعال', 'zarincode' ), zc_is_woo() ),
		array( __( 'حافظه PHP', 'zarincode' ), WP_MEMORY_LIMIT, true ),
		array( __( 'پیامک کاوه‌نگار', 'zarincode' ), zc_sms()->is_ready() ? __( 'پیکربندی شده', 'zarincode' ) : __( 'پیکربندی نشده', 'zarincode' ), zc_sms()->is_ready() ),
		array( __( 'درگاه زرین‌پال', 'zarincode' ), zc_opt( 'zc_zarinpal_merchant' ) ? __( 'پیکربندی شده', 'zarincode' ) : __( 'پیکربندی نشده', 'zarincode' ), (bool) zc_opt( 'zc_zarinpal_merchant' ) ),
	);

	$html = '<table style="width:100%;border-collapse:collapse;font-family:Vazirmatn,sans-serif">';

	foreach ( $checks as $check ) {
		$html .= sprintf(
			'<tr><td style="padding:9px;border-bottom:1px solid #eee">%s</td>
			<td style="padding:9px;border-bottom:1px solid #eee;font-weight:700">%s</td>
			<td style="padding:9px;border-bottom:1px solid #eee;color:%s">%s</td></tr>',
			esc_html( $check[0] ),
			esc_html( $check[1] ),
			$check[2] ? '#16A34A' : '#DC2626',
			$check[2] ? '✓' : '✕'
		);
	}

	$html .= '</table>';

	return $html;
}

/**
 * استایل RTL و فارسی برای پنل Redux.
 *
 * @return void
 */
function zc_redux_admin_style() {
	$screen = get_current_screen();
	if ( ! $screen || false === strpos( $screen->id, 'zarincode' ) ) {
		return;
	}
	?>
	<style>
		/* ============ پایه: فونت و جهت ============ */
		.redux-container,
		.redux-container input,
		.redux-container textarea,
		.redux-container select,
		.redux-container button{
			font-family:Vazirmatn,Tahoma,sans-serif!important
		}
		.redux-container{direction:rtl}

		#redux-header{background:linear-gradient(135deg,#141A31,#0B2187)!important}
		#redux-header .display_header h2{color:#F5D061!important;font-weight:800}
		.redux-group-tab h3{color:#141A31}

		/* ============================================================
		   اصلاح چیدمان راست‌چین
		   ------------------------------------------------------------
		   ردوکس به صورت پیش‌فرض .redux-sidebar را float:left می‌کند و
		   به .redux-main حاشیه‌ی چپ (margin-left) می‌دهد. در حالت
		   راست‌چین این باعث می‌شود منو سمت چپ بیفتد و ستون محتوا از
		   سمت چپ به اندازه‌ی عرض منو فاصله بگیرد و کامل نشود.
		   اینجا جهت شناورسازی و حاشیه‌ها معکوس می‌شود.
		   ============================================================ */
		.rtl .redux-container .redux-sidebar,
		.redux-container.rtl .redux-sidebar{
			float:right!important;
			margin-left:0!important;
			margin-right:0!important
		}

		.rtl .redux-container .redux-main,
		.redux-container.rtl .redux-main{
			margin-left:0!important;
			margin-right:191px!important;   /* عرض منوی کناری ردوکس */
			border-right:1px solid #e3e3e3;
			border-left:0
		}

		/* حالت جمع‌شده‌ی منو */
		.rtl .redux-container.fully-expanded .redux-main,
		.rtl .wp-collapse-menu .redux-container .redux-main{
			margin-right:191px!important;
			margin-left:0!important
		}

		/* نوار اقدام: دکمه‌ها راست‌چین و با فاصله‌ی مناسب */
		.rtl .redux-container .redux-action_bar{float:left;direction:rtl}
		.rtl .redux-container .redux-action_bar input,
		.rtl .redux-container .redux-action_bar .button{margin-left:6px;margin-right:0}
		.rtl .redux-container .redux-ajax-loading{float:right}
		.rtl #redux-footer .redux-action_bar{float:left}
		.rtl #redux-header .display_header{text-align:right}
		.rtl #redux-header .display_header .redux-dev-mode-notice{float:left}

		/* منوی گروه‌ها */
		.rtl .redux-container .redux-group-menu li a{text-align:right}
		.rtl .redux-container .redux-group-menu li a:before,
		.rtl .redux-container .redux-group-menu .extra_icon{
			float:right;margin-left:8px;margin-right:0
		}
		.rtl .redux-container .redux-group-menu li.hasSubSections .extraIconSubsections{
			float:left;transform:scaleX(-1)
		}

		.redux-container .redux-group-menu li.active > a{
			background:linear-gradient(135deg,#F5D061,#C9A227)!important;
			color:#241C05!important;font-weight:700
		}
		.redux-container .redux-action_bar .button-primary{
			background:#C9A227!important;border-color:#C9A227!important;
			color:#241C05!important;font-weight:700
		}

		/* ============ فیلدها ============ */
		.redux-main{background:#fff}
		.redux-field-container{direction:rtl;text-align:right}
		.redux-container input[type=text],
		.redux-container textarea,
		.redux-container select{border-radius:8px}

		.rtl .redux-container .redux_field_th{text-align:right;float:right}
		.rtl .redux-container .redux-info-field,
		.rtl .redux-container .redux-field-info{text-align:right}
		.rtl .redux-container .description,
		.rtl .redux-container .redux-th-description{text-align:right}

		/* سوییچ‌ها و چک‌باکس‌ها */
		.rtl .redux-container .cb-enable,
		.rtl .redux-container .cb-disable{float:right}
		.rtl .redux-container .checkbox-container li{float:right;margin-left:14px;margin-right:0}

		/* آپلود رسانه */
		.rtl .redux-container .upload_button_div{text-align:right}
		.rtl .redux-container .screenshot{float:right;margin-left:10px;margin-right:0}

		/* اسلایدر و رنگ */
		.rtl .redux-container .redux-slider-container{direction:ltr}
		.rtl .redux-container .wp-picker-container{direction:ltr;text-align:left;display:inline-block}

		/* جدول فیلدها */
		.rtl .redux-container table.form-table > tbody > tr > th{text-align:right}

		/* اعداد و مقادیر انگلیسی همیشه چپ‌چین بمانند */
		.redux-container input[type=email],
		.redux-container input[type=url],
		.redux-container input[type=number],
		.redux-container .redux-field-container code{direction:ltr;text-align:left}

		/* ============ واکنش‌گرایی ============ */
		@media only screen and (max-width:600px){
			.rtl .redux-container .redux-main,
			.redux-container.rtl .redux-main{
				margin-right:0!important;border-right:0
			}
			.rtl .redux-container .redux-sidebar{float:none!important;width:100%}
		}
	</style>
	</style>
	<?php
}
add_action( 'admin_head', 'zc_redux_admin_style' );

/**
 * اعلان نصب Redux.
 *
 * @return void
 */
function zc_redux_notice() {
	if ( zc_has_redux() || ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=redux-framework' ), 'install-plugin_redux-framework' );
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'قالب زرین کد:', 'zarincode' ); ?></strong>
			<?php esc_html_e( 'در حال حاضر از پنل تنظیمات داخلی استفاده می‌شود. برای دسترسی به پنل کامل Redux Framework، آن را نصب کنید.', 'zarincode' ); ?>
			<a href="<?php echo esc_url( $url ); ?>" class="button button-secondary" style="margin-inline-start:8px"><?php esc_html_e( 'نصب Redux Framework', 'zarincode' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'zc_redux_notice' );

/**
 * جلوگیری از نمایش اخطارهای منسوخ‌شدگی ردوکس در پیشخوان.
 *
 * ردوکس در نسخه‌های جدید PHP چند اخطار Deprecated تولید می‌کند که
 * مربوط به کد خود افزونه است و کارکرد سایت را مختل نمی‌کند؛ اما بالای
 * صفحه‌ی تنظیمات نمایش داده می‌شود و ظاهر پنل را خراب می‌کند.
 *
 * @return void
 */
function zc_redux_suppress_core_deprecations() {
	$screen = get_current_screen();

	if ( ! $screen || false === strpos( (string) $screen->id, 'zarincode' ) ) {
		return;
	}

	// فقط روی صفحه‌ی تنظیمات قالب و تنها برای اخطارهای منسوخ‌شدگی.
	// خطاهای واقعی همچنان ثبت و نمایش داده می‌شوند.
	$level = error_reporting(); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

	error_reporting( $level & ~E_DEPRECATED & ~E_USER_DEPRECATED ); // phpcs:ignore
}
add_action( 'current_screen', 'zc_redux_suppress_core_deprecations' );
