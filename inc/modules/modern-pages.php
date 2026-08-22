<?php
/**
 * ماژول صفحات داخلی زرین کد — نسخهٔ مدرن و مینیمال
 * ---------------------------------------------------------------------------
 * این ماژول توابع رندر صفحات کلیدی قالب را با یک سیستم طراحی یکپارچه و
 * مینیمال فراهم می‌کند: تماس با ما، درباره ما، درخواست پروژه، شرایط و قوانین،
 * حریم خصوصی، گارانتی، بازگشت وجه و خدمات ما.
 *
 * تمام چیدمان‌ها مبتنی بر کلاس (pages.css) و کاملاً واکنش‌گرا هستند؛ هیچ
 * استایل درون‌خطیِ grid در این‌جا تولید نمی‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   توابع کمکی
   ========================================================================== */

/**
 * رندر نوار مسیر (Breadcrumb).
 *
 * @param string $current عنوان صفحهٔ فعلی.
 * @return void
 */
function zc_modern_breadcrumb( $current = '' ) {
	echo '<nav class="zc-pbreadcrumb" aria-label="مسیر"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'خانه', 'zarincode' ) . '</a>';
	echo '<span class="sep" aria-hidden="true">‹</span>';
	echo '<span class="current">' . esc_html( $current ) . '</span></nav>';
}

/**
 * رندر هیروی صفحه — مینیمال و روشن.
 *
 * @param string $eyebrow برچسب کوچک بالای عنوان.
 * @param string $title   عنوان (HTML مجاز برای تأکید با <span>).
 * @param string $lead    مقدمه/زیرعنوان.
 * @param array  $chips   چیپ‌های اطلاعاتی (icon => label).
 * @return void
 */
function zc_modern_hero( $eyebrow, $title, $lead, $chips = array() ) {
	echo '<header class="zc-ph">';
	zc_modern_breadcrumb( wp_strip_all_tags( $title ) );
	if ( $eyebrow ) {
		echo '<span class="zc-ph__eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	echo '<h1 class="zc-ph__title">' . wp_kses_post( $title ) . '</h1>';
	if ( $lead ) {
		echo '<p class="zc-ph__lead">' . esc_html( $lead ) . '</p>';
	}
	if ( ! empty( $chips ) ) {
		echo '<div class="zc-ph__meta">';
		foreach ( $chips as $icon => $label ) {
			echo '<span class="zc-ph__chip">' . zc_icon( $icon, 17 ) . esc_html( $label ) . '</span>'; // phpcs:ignore
		}
		echo '</div>';
	}
	echo '</header>';
}

/**
 * بازکنندهٔ بخش (Section) با سرتیتر شیک.
 *
 * @param string $kicker برچسب کوچک.
 * @param string $title  عنوان.
 * @param string $lead   مقدمه.
 * @param bool   $center مرکزچین کردن.
 * @return void
 */
function zc_modern_sec_open( $kicker = '', $title = '', $lead = '', $center = false ) {
	echo '<section class="zc-sec' . ( $center ? ' zc-sec--center' : '' ) . '">';
	if ( $kicker || $title || $lead ) {
		echo '<div class="zc-sec__head">';
		if ( $kicker ) {
			echo '<span class="zc-sec__kicker">' . esc_html( $kicker ) . '</span>';
		}
		if ( $title ) {
			echo '<h2 class="zc-sec__title">' . wp_kses_post( $title ) . '</h2>';
		}
		if ( $lead ) {
			echo '<p class="zc-sec__lead">' . esc_html( $lead ) . '</p>';
		}
		echo '</div>';
	}
}

/**
 * بستن بخش.
 *
 * @return void
 */
function zc_modern_sec_close() {
	echo '</section>';
}

/**
 * رندر کارت‌های اطلاعات تماس.
 *
 * @param array $items آرایهٔ [icon, title, text, link].
 * @return void
 */
function zc_modern_contact_cards( $items ) {
	if ( empty( $items ) ) {
		return;
	}
	echo '<div class="zc-grid zc-grid--4" style="margin-top:30px">';
	foreach ( $items as $it ) {
		$link = isset( $it['link'] ) ? $it['link'] : '';
		echo '<div class="zc-ccard">';
		echo '<div class="zc-ccard__icon">' . zc_icon( $it['icon'] ?? 'info', 24 ) . '</div>'; // phpcs:ignore
		echo '<h3 class="zc-ccard__title">' . esc_html( $it['title'] ?? '' ) . '</h3>';
		echo '<p class="zc-ccard__text">' . esc_html( $it['text'] ?? '' ) . '</p>';
		if ( $link ) {
			echo '<a class="zc-ccard__link" href="' . esc_url( $link ) . '">' . esc_html__( 'در تماس باشید', 'zarincode' ) . '</a>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * رندر فرم تماس با ما.
 *
 * @return void
 */
function zc_modern_contact_form() {
	echo '<div class="zc-mform">';
	echo '<div class="zc-mform__head">';
	echo '<div class="zc-mform__head-icon">' . zc_icon( 'send', 22 ) . '</div>'; // phpcs:ignore
	echo '<div><h3>' . esc_html__( 'ارسال پیام به تیم زرین کد', 'zarincode' ) . '</h3>';
	echo '<p>' . esc_html__( 'پیام شما حداکثر تا یک روز کاری پاسخ داده می‌شود.', 'zarincode' ) . '</p></div>';
	echo '</div>';

	if ( shortcode_exists( 'zc_contact' ) ) {
		echo do_shortcode( '[zc_contact]' );
	} else {
		?>
		<form class="zc-reqform__form" data-zc-form="zc_contact_submit" novalidate>
			<div class="zc-reqform__grid">
				<div class="zc-field">
					<label><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<input type="text" name="name" required placeholder="<?php esc_attr_e( 'نام شما', 'zarincode' ); ?>">
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'ایمیل', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<input type="email" name="email" required dir="ltr" placeholder="you@example.com">
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'شماره تماس', 'zarincode' ); ?></label>
					<input type="tel" name="phone" dir="ltr" placeholder="09xxxxxxxxx">
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'دپارتمان', 'zarincode' ); ?></label>
					<select name="department">
						<option value=""><?php esc_html_e( 'انتخاب کنید', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'پشتیبانی فنی', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'فروش و مشاوره', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'همکاری', 'zarincode' ); ?></option>
					</select>
				</div>
				<div class="zc-field" style="grid-column:1/-1">
					<label><?php esc_html_e( 'موضوع', 'zarincode' ); ?></label>
					<input type="text" name="subject" placeholder="<?php esc_attr_e( 'موضوع پیام', 'zarincode' ); ?>">
				</div>
				<div class="zc-field" style="grid-column:1/-1">
					<label><?php esc_html_e( 'پیام شما', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<textarea name="message" rows="5" required placeholder="<?php esc_attr_e( 'پیام خود را بنویسید…', 'zarincode' ); ?>"></textarea>
				</div>
			</div>
			<div class="zc-form-msg"></div>
			<button type="submit" class="zc-btn zc-btn--gold zc-btn--block">
				<?php zc_the_icon( 'send', 18 ); ?>
				<?php esc_html_e( 'ارسال پیام', 'zarincode' ); ?>
			</button>
		</form>
		<?php
	}
	echo '</div>';
}

/**
 * رندر فرم درخواست پروژه/مشاوره.
 *
 * @return void
 */
function zc_modern_request_form() {
	echo '<div class="zc-mform">';
	echo '<div class="zc-mform__head">';
	echo '<div class="zc-mform__head-icon">' . zc_icon( 'gift', 22 ) . '</div>'; // phpcs:ignore
	echo '<div><h3>' . esc_html__( 'فرم درخواست پروژه / مشاوره', 'zarincode' ) . '</h3>';
	echo '<p>' . esc_html__( 'مشخصات پروژه یا نیاز خود را شرح دهید.', 'zarincode' ) . '</p></div>';
	echo '</div>';

	if ( shortcode_exists( 'zc_request_form' ) ) {
		echo do_shortcode( '[zc_request_form]' );
	} else {
		?>
		<form class="zc-reqform__form" data-zc-form="zc_submit_request" novalidate>
			<div class="zc-reqform__grid">
				<div class="zc-field">
					<label><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<input type="text" name="name" required placeholder="<?php esc_attr_e( 'نام شما', 'zarincode' ); ?>">
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<input type="tel" name="mobile" required dir="ltr" placeholder="09xxxxxxxxx">
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'نوع خدمت', 'zarincode' ); ?></label>
					<select name="service">
						<option value=""><?php esc_html_e( 'انتخاب کنید', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'طراحی سایت', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'اپلیکیشن موبایل', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'نرم‌افزار ویندوز', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'سئو و بهینه‌سازی', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'مشاوره فنی', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'سایر', 'zarincode' ); ?></option>
					</select>
				</div>
				<div class="zc-field">
					<label><?php esc_html_e( 'بودجه تقریبی', 'zarincode' ); ?></label>
					<select name="budget">
						<option value=""><?php esc_html_e( 'انتخاب کنید', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'زیر ۱۰ میلیون تومان', 'zarincode' ); ?></option>
						<option><?php esc_html_e( '۱۰ تا ۳۰ میلیون تومان', 'zarincode' ); ?></option>
						<option><?php esc_html_e( '۳۰ تا ۱۰۰ میلیون تومان', 'zarincode' ); ?></option>
						<option><?php esc_html_e( 'بیش از ۱۰۰ میلیون تومان', 'zarincode' ); ?></option>
					</select>
				</div>
				<div class="zc-field" style="grid-column:1/-1">
					<label><?php esc_html_e( 'توضیح پروژه', 'zarincode' ); ?> <span style="color:#DC2626">*</span></label>
					<textarea name="message" rows="5" required placeholder="<?php esc_attr_e( 'جزئیات پروژه یا نیاز خود را بنویسید…', 'zarincode' ); ?>"></textarea>
				</div>
			</div>
			<div class="zc-form-msg"></div>
			<button type="submit" class="zc-btn zc-btn--gold zc-btn--block">
				<?php zc_the_icon( 'send', 18 ); ?>
				<?php esc_html_e( 'ارسال درخواست', 'zarincode' ); ?>
			</button>
		</form>
		<?php
	}
	echo '</div>';
}

/**
 * رندر آمار کلیدی.
 *
 * @param array $stats آرایهٔ [num, label].
 * @return void
 */
function zc_modern_kstats( $stats ) {
	if ( empty( $stats ) ) {
		return;
	}
	echo '<div class="zc-kstats">';
	foreach ( $stats as $st ) {
		echo '<div class="zc-kstat">';
		echo '<div class="zc-kstat__num">' . esc_html( $st['num'] ?? '0' ) . '</div>';
		echo '<div class="zc-kstat__label">' . esc_html( $st['label'] ?? '' ) . '</div>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * رندر کارت‌های ویژگی/ارزش.
 *
 * @param array $items آرایهٔ [icon, title, text].
 * @param int   $cols  تعداد ستون (۲/۳/۴).
 * @return void
 */
function zc_modern_vcards( $items, $cols = 3 ) {
	if ( empty( $items ) ) {
		return;
	}
	$cols = in_array( (int) $cols, array( 2, 3, 4 ), true ) ? (int) $cols : 3;
	echo '<div class="zc-grid zc-grid--' . (int) $cols . '">';
	foreach ( $items as $it ) {
		echo '<div class="zc-vcard">';
		echo '<div class="zc-vcard__icon">' . zc_icon( $it['icon'] ?? 'check', 26 ) . '</div>'; // phpcs:ignore
		echo '<h3 class="zc-vcard__title">' . esc_html( $it['title'] ?? '' ) . '</h3>';
		echo '<p class="zc-vcard__text">' . esc_html( $it['text'] ?? '' ) . '</p>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * رندر پنل اطلاعات جانبی.
 *
 * @param string $title عنوان.
 * @param string $sub   زیرعنوان.
 * @param array  $items آرایهٔ [icon, title, text].
 * @return void
 */
function zc_modern_aside( $title, $sub, $items ) {
	echo '<div class="zc-aside">';
	echo '<h2 class="zc-aside__title">' . wp_kses_post( $title ) . '</h2>';
	if ( $sub ) {
		echo '<p class="zc-aside__sub">' . esc_html( $sub ) . '</p>';
	}
	foreach ( $items as $it ) {
		echo '<div class="zc-aside__item">';
		echo '<div class="zc-aside__item-icon">' . zc_icon( $it['icon'] ?? 'check', 18 ) . '</div>'; // phpcs:ignore
		echo '<div><div class="zc-aside__item-title">' . esc_html( $it['title'] ?? '' ) . '</div>';
		echo '<div class="zc-aside__item-text">' . esc_html( $it['text'] ?? '' ) . '</div></div>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * رندر مراحل/گام‌ها.
 *
 * @param array $items آرایهٔ [title, text].
 * @return void
 */
function zc_modern_steps( $items ) {
	if ( empty( $items ) ) {
		return;
	}
	echo '<div class="zc-steps">';
	$i = 1;
	foreach ( $items as $it ) {
		echo '<div class="zc-step">';
		echo '<div class="zc-step__num">' . esc_html( zc_fa_num( $i ) ) . '</div>';
		echo '<h3 class="zc-step__title">' . esc_html( $it['title'] ?? '' ) . '</h3>';
		echo '<p class="zc-step__text">' . esc_html( $it['text'] ?? '' ) . '</p>';
		echo '</div>';
		$i++;
	}
	echo '</div>';
}

/**
 * رندر CTA پایانی.
 *
 * @param string $title عنوان.
 * @param string $sub   زیرعنوان.
 * @param array  $btns  دکمه‌ها: [text, url, style].
 * @return void
 */
function zc_modern_cta( $title, $sub, $btns = array() ) {
	echo '<section class="zc-cta">';
	echo '<h2 class="zc-cta__title">' . esc_html( $title ) . '</h2>';
	if ( $sub ) {
		echo '<p class="zc-cta__sub">' . esc_html( $sub ) . '</p>';
	}
	echo '<div class="zc-cta__actions">';
	foreach ( $btns as $btn ) {
		printf(
			'<a href="%s" class="zc-btn zc-btn--%s">%s</a>',
			esc_url( $btn['url'] ?? '#' ),
			esc_attr( $btn['style'] ?? 'gold' ),
			esc_html( $btn['text'] ?? '' )
		);
	}
	echo '</div>';
	echo '</section>';
}

/**
 * رندر محتوای حقوقی داخل کارت.
 *
 * @param string $html محتوای HTML حقوقی.
 * @return void
 */
function zc_modern_legal( $html ) {
	echo '<div class="zc-legal-wrap"><div class="zc-legal">';
	echo wp_kses_post( $html );
	echo '</div></div>';
}

/* ==========================================================================
   رندر صفحات
   ========================================================================== */

/**
 * صفحهٔ «تماس با ما».
 *
 * @return void
 */
function zc_render_contact_page() {
	zc_modern_hero(
		__( 'همیشه در کنار شما هستیم', 'zarincode' ),
		__( 'با <span>زرین کد</span> در تماس باشید', 'zarincode' ),
		__( 'سؤال، پیشنهاد یا ایده‌ی پروژه‌ای دارید؟ تیم پشتیبانی و مشاوران ما آماده‌ی پاسخ‌گویی به شما هستند.', 'zarincode' ),
		array(
			'clock' => __( 'شنبه تا چهارشنبه ۹ تا ۱۸', 'zarincode' ),
			'send'  => __( 'پاسخ زیر ۲۴ ساعت', 'zarincode' ),
			'users' => __( 'پشتیبانی تخصصی', 'zarincode' ),
		)
	);

	zc_modern_sec_open( __( 'راه‌های ارتباط', 'zarincode' ), __( 'چطور می‌توانیم <span>کمک کنیم</span>؟', 'zarincode' ), __( 'از هر راهی که برایتان راحت‌تر است، با ما در ارتباط باشید.', 'zarincode' ) );
	zc_modern_contact_cards(
		array(
			array( 'icon' => 'phone', 'title' => __( 'تماس تلفنی', 'zarincode' ), 'text' => __( 'شنبه تا چهارشنبه، ۹ تا ۱۸', 'zarincode' ), 'link' => 'tel:07142380267' ),
			array( 'icon' => 'send', 'title' => __( 'واتساپ و پیامک', 'zarincode' ), 'text' => __( 'پاسخ در کمتر از ۲۴ ساعت کاری', 'zarincode' ), 'link' => 'https://wa.me/989024561001' ),
			array( 'icon' => 'mail', 'title' => __( 'ایمیل', 'zarincode' ), 'text' => 'info@' . zc_site_domain(), 'link' => 'mailto:info@' . zc_site_domain() ),
			array( 'icon' => 'pin', 'title' => __( 'آدرس', 'zarincode' ), 'text' => __( 'فارس، کازرون، دهستان انارستان', 'zarincode' ) ),
		)
	);
	zc_modern_sec_close();

	// فرم + اطلاعات جانبی
	echo '<div class="zc-split">';
	echo '<div>';
	zc_modern_contact_form();
	echo '</div>';
	echo '<div>';
	zc_modern_aside(
		__( 'اطلاعات <span>تماس</span>', 'zarincode' ),
		__( 'راه‌های ارتباطی متنوعی برای شما فراهم کرده‌ایم.', 'zarincode' ),
		array(
			array( 'icon' => 'phone', 'title' => __( 'تلفن ثابت', 'zarincode' ), 'text' => '071-42380267' ),
			array( 'icon' => 'send', 'title' => __( 'موبایل', 'zarincode' ), 'text' => '09024561001' ),
			array( 'icon' => 'mail', 'title' => __( 'ایمیل', 'zarincode' ), 'text' => 'info@' . zc_site_domain() ),
			array( 'icon' => 'clock', 'title' => __( 'ساعت کاری', 'zarincode' ), 'text' => __( 'شنبه تا چهارشنبه ۹ تا ۱۸', 'zarincode' ) ),
			array( 'icon' => 'pin', 'title' => __( 'آدرس', 'zarincode' ), 'text' => __( 'استان فارس، شهرستان کازرون', 'zarincode' ) ),
		)
	);
	echo '</div>';
	echo '</div>';

	zc_modern_cta(
		__( 'نیاز به مشاوره دارید؟', 'zarincode' ),
		__( 'کارشناسان ما آماده‌اند تا به پرسش‌های شما پاسخ دهند.', 'zarincode' ),
		array(
			array( 'text' => __( 'درخواست پروژه', 'zarincode' ), 'url' => home_url( '/request/' ), 'style' => 'gold' ),
			array( 'text' => __( 'مشاهده خدمات', 'zarincode' ), 'url' => home_url( '/services/' ), 'style' => 'navy' ),
		)
	);
}
add_action( 'zc_modern_page_content', 'zc_render_contact_page' );

/**
 * صفحهٔ «درباره ما».
 *
 * @return void
 */
function zc_render_about_page() {
	zc_modern_hero(
		__( 'داستان زرین کد', 'zarincode' ),
		__( 'ما <span>می‌سازیم</span>، می‌آموزیم و رشد می‌کنیم', 'zarincode' ),
		__( 'زرین کد مجموعه‌ای است که با یک هدف ساده شروع شد: کمک به ایرانی‌ها برای یادگیری برنامه‌نویسی و ساختن کسب‌وکارهای دیجیتال.', 'zarincode' ),
		array(
			'users' => '+۱۲,۰۰۰ ' . __( 'دانشجو', 'zarincode' ),
			'award' => '+۳۵۰ ' . __( 'پروژه موفق', 'zarincode' ),
			'star'  => '۹۷٪ ' . __( 'رضایت', 'zarincode' ),
		)
	);

	// آمار
	zc_modern_kstats(
		array(
			array( 'num' => '۱۲,۰۰۰+', 'label' => __( 'دانشجوی فعال', 'zarincode' ) ),
			array( 'num' => '۳۵۰+', 'label' => __( 'پروژه تحویل‌شده', 'zarincode' ) ),
			array( 'num' => '۱۸۰+', 'label' => __( 'محصول آماده', 'zarincode' ) ),
			array( 'num' => '۹ سال', 'label' => __( 'سابقه فعالیت', 'zarincode' ) ),
		)
	);

	// داستان + مأموریت
	echo '<div class="zc-split zc-split--even">';
	echo '<div class="zc-legal-wrap"><div class="zc-legal">';
	echo '<h3>' . esc_html__( 'ماموریت ما', 'zarincode' ) . '</h3>';
	echo '<p>' . esc_html__( 'مأموریت زرین کد، توانمندسازی افراد و کسب‌وکارها با مهارت‌های دیجیتال و فنی است. ما با ترکیب آموزش پروژه‌محور و ارائه‌ی خدمات تخصصی، مسیر رشد را برای هر فرد و برند فراهم می‌کنیم.', 'zarincode' ) . '</p>';
	echo '<h3>' . esc_html__( 'دیدگاه ما', 'zarincode' ) . '</h3>';
	echo '<p>' . esc_html__( 'ما باور داریم هر ایرانی می‌تواند با یادگیری درست و ابزار مناسب، به موفقیت برسد. زرین کد پلی است میان دانش، مهارت و بازار کار.', 'zarincode' ) . '</p>';
	echo '</div></div>';

	echo '<div>';
	zc_modern_aside(
		__( 'چرا <span>زرین کد</span>؟', 'zarincode' ),
		__( 'تفاوت ما در کیفیت، پشتیبانی و نگاه واقعی به نیازهای شماست.', 'zarincode' ),
		array(
			array( 'icon' => 'code', 'title' => __( 'آموزش پروژه‌محور', 'zarincode' ), 'text' => __( 'یادگیری با ساخت محصول واقعی', 'zarincode' ) ),
			array( 'icon' => 'headphone', 'title' => __( 'پشتیبانی مستقیم', 'zarincode' ), 'text' => __( 'تیم فنی همیشه در کنار شما', 'zarincode' ) ),
			array( 'icon' => 'shield', 'title' => __( 'کیفیت تضمین‌شده', 'zarincode' ), 'text' => __( 'استاندارد و حرفه‌ای', 'zarincode' ) ),
		)
	);
	echo '</div>';
	echo '</div>';

	// ارزش‌ها
	zc_modern_sec_open( __( 'ارزش‌های ما', 'zarincode' ), __( 'آنچه به آن <span>باور</span> داریم', 'zarincode' ) );
	zc_modern_vcards(
		array(
			array( 'icon' => 'users', 'title' => __( 'تیم متخصص', 'zarincode' ), 'text' => __( 'جمعی از بهترین‌های صنعت نرم‌افزار', 'zarincode' ) ),
			array( 'icon' => 'refresh', 'title' => __( 'روزآمد', 'zarincode' ), 'text' => __( 'همگام با آخرین فناوری‌های روز', 'zarincode' ) ),
			array( 'icon' => 'heart', 'title' => __( 'تعهد', 'zarincode' ), 'text' => __( 'به کیفیت و موفقیت مشتریان', 'zarincode' ) ),
		),
		3
	);
	zc_modern_sec_close();

	zc_modern_cta(
		__( 'آماده‌اید با زرین کد شروع کنید؟', 'zarincode' ),
		__( 'همین حالا با ما در تماس باشید یا از دوره‌های ما دیدن کنید.', 'zarincode' ),
		array(
			array( 'text' => __( 'تماس با ما', 'zarincode' ), 'url' => home_url( '/contact-us/' ), 'style' => 'gold' ),
			array( 'text' => __( 'مشاهده دوره‌ها', 'zarincode' ), 'url' => get_post_type_archive_link( 'zc_course' ), 'style' => 'navy' ),
		)
	);
}
add_action( 'zc_modern_page_content', 'zc_render_about_page' );

/**
 * صفحهٔ «درخواست پروژه و مشاوره».
 *
 * @return void
 */
function zc_render_request_page() {
	zc_modern_hero(
		__( 'شروع همکاری', 'zarincode' ),
		__( 'درخواست <span>پروژه</span> یا مشاوره', 'zarincode' ),
		__( 'فرم زیر را پر کنید؛ کارشناسان ما حداکثر تا یک روز کاری با شما تماس می‌گیرند و برآورد دقیق زمان و هزینه ارائه می‌دهند.', 'zarincode' ),
		array(
			'headphone' => __( 'مشاوره رایگان', 'zarincode' ),
			'shield'    => __( 'قرارداد رسمی', 'zarincode' ),
			'check'     => __( 'تحویل مرحله‌ای', 'zarincode' ),
		)
	);

	// مراحل همکاری
	zc_modern_sec_open( __( 'فرآیند همکاری', 'zarincode' ), __( 'چطور <span>پیش می‌رویم</span>؟', 'zarincode' ), __( 'مسیر همکاری ما شفاف، مرحله‌به‌مرحله و قابل پیگیری است.', 'zarincode' ) );
	zc_modern_steps(
		array(
			array( 'title' => __( 'مشاوره رایگان', 'zarincode' ), 'text' => __( 'نیاز شما را می‌شنویم و راهکار پیشنهاد می‌دهیم.', 'zarincode' ) ),
			array( 'title' => __( 'قرارداد و برآورد', 'zarincode' ), 'text' => __( 'مبلغ و زمان دقیق اعلام و قرارداد رسمی بسته می‌شود.', 'zarincode' ) ),
			array( 'title' => __( 'اجرای مرحله‌ای', 'zarincode' ), 'text' => __( 'گزارش پیشرفت و نسخهٔ قابل مشاهده تحویل می‌گیرید.', 'zarincode' ) ),
			array( 'title' => __( 'تحویل و پشتیبانی', 'zarincode' ), 'text' => __( 'تحویل کد و پشتیبانی پس از تحویل.', 'zarincode' ) ),
		)
	);
	zc_modern_sec_close();

	// فرم + پنل جانبی
	echo '<div class="zc-split">';
	echo '<div>';
	zc_modern_request_form();
	echo '</div>';
	echo '<div>';
	zc_modern_aside(
		__( 'چرا <span>زرین کد</span>؟', 'zarincode' ),
		__( 'اعتماد شما، سرمایه‌ی ماست.', 'zarincode' ),
		array(
			array( 'icon' => 'headphone', 'title' => __( 'مشاوره رایگان', 'zarincode' ), 'text' => __( 'پیش از شروع، بدون هزینه', 'zarincode' ) ),
			array( 'icon' => 'file', 'title' => __( 'قرارداد رسمی', 'zarincode' ), 'text' => __( 'شفاف و مطابق قانون', 'zarincode' ) ),
			array( 'icon' => 'check', 'title' => __( 'تحویل مرحله‌ای', 'zarincode' ), 'text' => __( 'پیگیری در هر مرحله', 'zarincode' ) ),
			array( 'icon' => 'shield', 'title' => __( 'ضمانت بازگشت وجه', 'zarincode' ), 'text' => __( 'تا ۷ روز پس از خرید', 'zarincode' ) ),
		)
	);
	zc_modern_kstats(
		array(
			array( 'num' => '۳۵۰+', 'label' => __( 'پروژه موفق', 'zarincode' ) ),
			array( 'num' => '۲۴ ساعت', 'label' => __( 'زمان پاسخ', 'zarincode' ) ),
			array( 'num' => '۹۷٪', 'label' => __( 'رضایت کارفرما', 'zarincode' ) ),
		)
	);
	echo '</div>';
	echo '</div>';
}
add_action( 'zc_modern_page_content', 'zc_render_request_page' );

/**
 * صفحهٔ «خدمات ما».
 *
 * @return void
 */
function zc_render_services_page() {
	zc_modern_hero(
		__( 'خدمات حرفه‌ای', 'zarincode' ),
		__( 'خدمات <span>تخصصی</span> زرین کد', 'zarincode' ),
		__( 'از طراحی سایت و اپلیکیشن تا سئو و مشاوره فنی؛ تیم زرین کد هر نیاز دیجیتال شما را برطرف می‌کند.', 'zarincode' ),
		array(
			'code'  => __( 'برنامه‌نویسی', 'zarincode' ),
			'chart' => __( 'سئو و مارکتینگ', 'zarincode' ),
			'phone' => __( 'موبایل و وب', 'zarincode' ),
		)
	);

	// کارت خدمات
	$zc_svcs      = function_exists( 'zc_services_list' ) ? zc_services_list() : array();
	$zc_svc_items = array();
	$zc_icons     = array( 'code', 'chart', 'phone', 'edit', 'plugin', 'headphone', 'shield', 'refresh' );
	$i            = 0;
	foreach ( $zc_svcs as $sid => $title ) {
		if ( $i >= 8 ) {
			break;
		}
		$zc_svc_items[] = array(
			'icon'  => $zc_icons[ $i % count( $zc_icons ) ],
			'title' => $title,
			'text'  => __( 'خدمات تخصصی زرین کد با بالاترین کیفیت و پشتیبانی کامل.', 'zarincode' ),
		);
		$i++;
	}

	if ( empty( $zc_svc_items ) ) {
		$zc_svc_items = array(
			array( 'icon' => 'code', 'title' => __( 'طراحی سایت', 'zarincode' ), 'text' => __( 'سایت شرکتی، فروشگاهی و آموزشی', 'zarincode' ) ),
			array( 'icon' => 'chart', 'title' => __( 'سئو و بهینه‌سازی', 'zarincode' ), 'text' => __( 'رسیدن به صفحه اول گوگل', 'zarincode' ) ),
			array( 'icon' => 'phone', 'title' => __( 'اپلیکیشن موبایل', 'zarincode' ), 'text' => __( 'اندروید و iOS با فلاتر', 'zarincode' ) ),
			array( 'icon' => 'plugin', 'title' => __( 'افزونه و قالب', 'zarincode' ), 'text' => __( 'وردپرس و ووکامرس', 'zarincode' ) ),
		);
	}

	zc_modern_sec_open( __( 'تخصص‌های ما', 'zarincode' ), __( 'خدماتی که در آن <span>حرفه‌ای</span> هستیم', 'zarincode' ) );
	zc_modern_vcards( $zc_svc_items, 4 );
	zc_modern_sec_close();

	// فرآیند
	zc_modern_sec_open( __( 'روند کار', 'zarincode' ), __( 'قدم‌های <span>پروژه</span> شما', 'zarincode' ) );
	zc_modern_steps(
		array(
			array( 'title' => __( 'دریافت نیازمندی', 'zarincode' ), 'text' => __( 'بررسی کامل نیاز شما', 'zarincode' ) ),
			array( 'title' => __( 'پیشنهاد و برآورد', 'zarincode' ), 'text' => __( 'زمان و هزینه شفاف', 'zarincode' ) ),
			array( 'title' => __( 'اجرا و تحویل', 'zarincode' ), 'text' => __( 'اجرای مرحله‌ای و باکیفیت', 'zarincode' ) ),
			array( 'title' => __( 'پشتیبانی', 'zarincode' ), 'text' => __( 'همراهی پس از تحویل', 'zarincode' ) ),
		)
	);
	zc_modern_sec_close();

	zc_modern_cta(
		__( 'نیاز به خدمات تخصصی دارید؟', 'zarincode' ),
		__( 'همین حالا درخواست خود را ثبت کنید یا مشاوره رایگان بگیرید.', 'zarincode' ),
		array(
			array( 'text' => __( 'درخواست پروژه', 'zarincode' ), 'url' => home_url( '/request/' ), 'style' => 'gold' ),
			array( 'text' => __( 'تماس با ما', 'zarincode' ), 'url' => home_url( '/contact-us/' ), 'style' => 'navy' ),
		)
	);
}
add_action( 'zc_modern_page_content', 'zc_render_services_page' );

/**
 * رندر صفحات حقوقی (قوانین، حریم خصوصی، گارانتی، بازگشت وجه).
 *
 * @param string $eyebrow برچسب کوچک.
 * @param string $title   عنوان.
 * @param string $lead    مقدمه.
 * @param string $html    محتوای حقوقی.
 * @return void
 */
function zc_modern_legal_page( $eyebrow, $title, $lead, $html ) {
	zc_modern_hero( $eyebrow, $title, $lead, array( 'shield' => __( 'سند حقوقی رسمی', 'zarincode' ) ) );
	echo '<div style="margin-top:34px">';
	zc_modern_legal( $html );
	echo '</div>';
}

/**
 * صفحهٔ «شرایط و قوانین».
 *
 * @return void
 */
function zc_render_terms_page() {
	zc_modern_legal_page(
		__( 'سند حقوقی رسمی', 'zarincode' ),
		__( 'شرایط و <span>قوانین</span> استفاده', 'zarincode' ),
		__( 'لطفاً پیش از استفاده از خدمات زرین کد، این سند را با دقت مطالعه فرمایید.', 'zarincode' ),
		function_exists( 'zc_legal_terms_html' ) ? zc_legal_terms_html() : ''
	);
}
add_action( 'zc_modern_page_content', 'zc_render_terms_page' );

/**
 * صفحهٔ «حریم خصوصی».
 *
 * @return void
 */
function zc_render_privacy_page() {
	zc_modern_legal_page(
		__( 'تعهد ما به شما', 'zarincode' ),
		__( 'سیاست <span>حریم خصوصی</span>', 'zarincode' ),
		__( 'حفاظت از اطلاعات شما مطابق قوانین جمهوری اسلامی ایران، اولویت ماست.', 'zarincode' ),
		function_exists( 'zc_legal_privacy_html' ) ? zc_legal_privacy_html() : ''
	);
}
add_action( 'zc_modern_page_content', 'zc_render_privacy_page' );

/**
 * صفحهٔ «گارانتی و تضمین کیفیت».
 *
 * @return void
 */
function zc_render_warranty_page() {
	zc_modern_legal_page(
		__( 'تعهد کتبی زرین کد', 'zarincode' ),
		__( 'گارانتی و <span>تضمین کیفیت</span>', 'zarincode' ),
		__( 'پشت هر محصول و خدمتی که ارائه می‌دهیم، تعهد کتبی ما ایستاده است.', 'zarincode' ),
		function_exists( 'zc_legal_warranty_html' ) ? zc_legal_warranty_html() : ''
	);
}
add_action( 'zc_modern_page_content', 'zc_render_warranty_page' );

/**
 * صفحهٔ «شرایط بازگشت وجه».
 *
 * @return void
 */
function zc_render_refund_page() {
	zc_modern_legal_page(
		__( 'اعتماد شما ارزشمند است', 'zarincode' ),
		__( 'شرایط <span>بازگشت وجه</span>', 'zarincode' ),
		__( 'حقوق شما در خریدهای دیجیتال، شفاف و مشخص است.', 'zarincode' ),
		function_exists( 'zc_legal_refund_html' ) ? zc_legal_refund_html() : ''
	);
}
add_action( 'zc_modern_page_content', 'zc_render_refund_page' );
