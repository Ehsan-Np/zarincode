<?php
/**
 * توابع مشترک ساخت داده‌ی المنتور برای محتوای دمو
 *
 * چرا این فایل جدا شد؟
 * پیش‌تر تابع‌های zc_el_id() و zc_el_section() فقط در
 * homepage-elementor.php تعریف شده بودند، اما demo-legal-pages.php هم
 * از آن‌ها استفاده می‌کرد. چون برگه‌های حقوقی *پیش از* صفحه‌ی اصلی
 * ساخته می‌شوند، درون‌ریزی دمو با خطای «Call to undefined function
 * zc_el_section()» متوقف می‌شد. با انتقال همه‌ی کمک‌تابع‌ها به یک
 * فایل مشترک، ترتیب بارگذاری دیگر اهمیتی ندارد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * تولید شناسه یکتای المنتور.
 *
 * @return string
 */
if ( ! function_exists( 'zc_el_id' ) ) :

function zc_el_id() {
	return substr( md5( uniqid( '', true ) ), 0, 7 );
}

endif;


/**
 * ساخت یک section با یک ستون و یک ویجت.
 *
 * @param string $widget_type نوع ویجت.
 * @param array  $settings    تنظیمات.
 * @param array  $section_settings تنظیمات سکشن.
 * @return array
 */
if ( ! function_exists( 'zc_el_section' ) ) :

function zc_el_section( $widget_type, $settings = array(), $section_settings = array() ) {
	return array(
		'id'       => zc_el_id(),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'        => 'boxed',
				'content_width' => array( 'unit' => 'px', 'size' => 1280 ),
				'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			),
			$section_settings
		),
		'elements' => array(
			array(
				'id'       => zc_el_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100, '_inline_size' => null ),
				'elements' => array(
					array(
						'id'         => zc_el_id(),
						'elType'     => 'widget',
						'widgetType' => $widget_type,
						'settings'   => $settings,
					),
				),
			),
		),
		'isInner'  => false,
	);
}

endif;


/**
 * ساخت یک section با چند ویجت در همان ستون.
 *
 * المنتور برای هر بخش پنج لایه‌ی تودرتو می‌سازد (section، container،
 * column، widget-wrap و widget-container). وقتی هر ویجت را در یک
 * بخش جدا بگذاریم، این پنج لایه به ازای هر ویجت تکرار می‌شود و فقط
 * همین «داربست» صد گره به DOM اضافه می‌کند. با گروه‌کردن ویجت‌های
 * هم‌خانواده در یک بخش، همان داربست یک‌بار ساخته می‌شود.
 *
 * @param array $widgets آرایه‌ای از [نوع، تنظیمات].
 * @param array $section_settings تنظیمات بخش.
 * @return array
 */
if ( ! function_exists( 'zc_el_group' ) ) :

function zc_el_group( $widgets, $section_settings = array() ) {
	$children = array();

	foreach ( $widgets as $w ) {
		$children[] = array(
			'id'         => zc_el_id(),
			'elType'     => 'widget',
			'widgetType' => $w[0],
			'settings'   => $w[1] ?? array(),
		);
	}

	return array(
		'id'       => zc_el_id(),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'        => 'boxed',
				'content_width' => array( 'unit' => 'px', 'size' => 1280 ),
				'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			),
			$section_settings
		),
		'elements' => array(
			array(
				'id'       => zc_el_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100, '_inline_size' => null ),
				'elements' => $children,
			),
		),
		'isInner'  => false,
	);
}

endif;


/**
 * ادغام بخش‌های پشت‌سرهم با تنظیمات یکسان.
 *
 * @param array $sections بخش‌ها.
 * @return array
 */
if ( ! function_exists( 'zc_el_merge_sections' ) ) :

function zc_el_merge_sections( $sections ) {
	$out = array();

	foreach ( $sections as $sec ) {
		$prev = $out ? count( $out ) - 1 : -1;

		// بخشی که شناسه‌ی HTML یا کلاس اختصاصی دارد نباید ادغام شود.
		$has_identity = ! empty( $sec['settings']['_element_id'] ) || ! empty( $sec['settings']['css_classes'] );

		if (
			$prev >= 0
			&& ! $has_identity
			&& empty( $out[ $prev ]['settings']['_element_id'] )
			&& $out[ $prev ]['settings'] == $sec['settings'] // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			&& isset( $out[ $prev ]['elements'][0]['elements'], $sec['elements'][0]['elements'] )
		) {
			$out[ $prev ]['elements'][0]['elements'] = array_merge(
				$out[ $prev ]['elements'][0]['elements'],
				$sec['elements'][0]['elements']
			);

			continue;
		}

		$out[] = $sec;
	}

	return $out;
}

endif;


/**
 * ساخت یک سکشن با چند ویجت در یک ستون.
 *
 * تابع zc_el_section فقط یک ویجت می‌پذیرد؛ برگه‌های حقوقی به
 * چیدمان‌های چندتایی و دو ستونه نیاز دارند.
 *
 * @param array $widgets آرایه‌ای از array( type, settings ).
 * @param array $section_settings تنظیمات سکشن.
 * @return array
 */
if ( ! function_exists( 'zc_el_stack' ) ) :

function zc_el_stack( $widgets, $section_settings = array() ) {
	$elements = array();

	foreach ( $widgets as $w ) {
		$elements[] = array(
			'id'         => zc_el_id(),
			'elType'     => 'widget',
			'widgetType' => $w[0],
			'settings'   => $w[1] ?? array(),
		);
	}

	return array(
		'id'       => zc_el_id(),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'        => 'boxed',
				'content_width' => array( 'unit' => 'px', 'size' => 1180 ),
				'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			),
			$section_settings
		),
		'elements' => array(
			array(
				'id'       => zc_el_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100, '_inline_size' => null ),
				'elements' => $elements,
			),
		),
		'isInner'  => false,
	);
}

endif;


/**
 * سکشن متن ساده با ویجت متن المنتور.
 *
 * از ویجت پایه‌ی text-editor استفاده می‌شود که در نسخه رایگان
 * المنتور موجود است و کاربر می‌تواند آزادانه ویرایش کند.
 *
 * @param string $html محتوا.
 * @param array  $extra تنظیمات اضافی.
 * @return array
 */
if ( ! function_exists( 'zc_el_richtext' ) ) :

function zc_el_richtext( $html, $extra = array() ) {
	return array(
		'text-editor',
		array_merge(
			array(
				'editor'     => $html,
				'_css_classes' => 'zc-legal-text',
			),
			$extra
		),
	);
}

endif;


/**
 * سکشن دوتستونه با ویجت‌های دلخواه در هر ستون.
 *
 * @param array $left_widgets    آرایه‌ای از [نوع, تنظیمات] برای ستون راست (RTL اول).
 * @param array $right_widgets   آرایه‌ای از [نوع, تنظیمات] برای ستون چپ.
 * @param array $section_settings تنظیمات سکشن.
 * @param array $left_pct        عرض ستون اول به درصد (پیش‌فرض 50).
 * @return array
 */
if ( ! function_exists( 'zc_el_two_cols' ) ) :

function zc_el_two_cols( $left_widgets = array(), $right_widgets = array(), $section_settings = array(), $left_pct = 50 ) {
	$mk = function ( $widgets ) {
		$els = array();
		foreach ( $widgets as $w ) {
			$els[] = array(
				'id'         => zc_el_id(),
				'elType'     => 'widget',
				'widgetType' => $w[0],
				'settings'   => $w[1] ?? array(),
			);
		}
		return $els;
	};

	$right_pct = 100 - (int) $left_pct;

	return array(
		'id'       => zc_el_id(),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'        => 'boxed',
				'content_width' => array( 'unit' => 'px', 'size' => 1280 ),
				'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
				'gap'           => array( 'column' => 20, 'row' => 20 ),
				'gap_column'    => array( 'size' => 20 ),
			),
			$section_settings
		),
		'elements' => array(
			array(
				'id'       => zc_el_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => $left_pct, '_inline_size' => $left_pct ),
				'elements' => $mk( $left_widgets ),
				'isInner'  => false,
			),
			array(
				'id'       => zc_el_id(),
				'elType'   => 'column',
				'settings' => array( '_column_size' => $right_pct, '_inline_size' => $right_pct ),
				'elements' => $mk( $right_widgets ),
				'isInner'  => false,
			),
		),
		'isInner'  => false,
	);
}

endif;
