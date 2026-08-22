<?php
/**
 * تگ‌های قالب و توابع نمایشی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * نمایش لوگوی سایت.
 *
 * @param string $context محل نمایش (header|footer).
 * @return void
 */
function zc_site_logo( $context = 'header' ) {
	$logo_opt = 'footer' === $context ? zc_opt( 'zc_logo_footer', '' ) : zc_opt( 'zc_logo', '' );
	$logo_url = is_array( $logo_opt ) && isset( $logo_opt['url'] ) ? $logo_opt['url'] : $logo_opt;

	echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="zc-logo" rel="home">';

	if ( $logo_url ) {
		printf(
			'<img src="%1$s" alt="%2$s" width="180" height="50" %3$s>',
			esc_url( $logo_url ),
			esc_attr( get_bloginfo( 'name' ) ),
			'header' === $context ? 'fetchpriority="high"' : 'loading="lazy"'
		);
	} elseif ( has_custom_logo() && 'header' === $context ) {
		$id  = get_theme_mod( 'custom_logo' );
		$img = wp_get_attachment_image_src( $id, 'full' );
		if ( $img ) {
			printf( '<img src="%1$s" alt="%2$s" width="180" height="50" fetchpriority="high">', esc_url( $img[0] ), esc_attr( get_bloginfo( 'name' ) ) );
		}
	} else {
		// لوگوی متنی پیش‌فرض با آیکن.
		echo '<span style="width:44px;height:44px;border-radius:13px;background:var(--zc-grad-gold);display:flex;align-items:center;justify-content:center;color:#241C05;flex-shrink:0">';
		zc_the_icon( 'code', 24 );
		echo '</span>';
		echo '<span class="zc-logo__text">';
		echo '<span class="zc-logo__name"><span class="zc-logo__p1">' . esc_html( zc_opt( 'zc_site_name_1', 'زرین' ) ) . '</span><span class="zc-logo__p2">' . esc_html( zc_opt( 'zc_site_name_2', 'کد' ) ) . '</span></span>';
		echo '<span class="zc-logo__tag">' . esc_html( zc_opt( 'zc_site_tagline', 'ZARINCODE' ) ) . '</span>';
		echo '</span>';
	}

	echo '</a>';
}

/**
 * منوی پیش‌فرض در صورت عدم تنظیم منو.
 *
 * @param string $class کلاس.
 * @return void
 */
function zc_default_menu( $class = 'zc-nav__list' ) {
	$items = array(
		__( 'صفحه اصلی', 'zarincode' ) => home_url( '/' ),
		__( 'دوره‌ها', 'zarincode' )   => get_post_type_archive_link( 'zc_course' ),
		__( 'فروشگاه', 'zarincode' )   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '#',
		__( 'آموزش‌ها', 'zarincode' )  => get_post_type_archive_link( 'zc_tutorial' ),
		__( 'بلاگ', 'zarincode' )      => get_permalink( get_option( 'page_for_posts' ) ),
		__( 'درباره ما', 'zarincode' ) => '#',
		__( 'تماس با ما', 'zarincode' ) => '#',
	);

	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $label => $url ) {
		if ( ! $url ) {
			continue;
		}
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * لینک‌های فالبک فوتر.
 *
 * @param array $links لینک‌ها.
 * @return void
 */
function zc_footer_fallback_links( $links ) {
	echo '<ul class="zc-footer__links">';
	foreach ( $links as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * لیست شبکه‌های اجتماعی فعال.
 *
 * @return array
 */
function zc_social_links() {
	$networks = array( 'telegram', 'instagram', 'twitter', 'linkedin', 'youtube', 'aparat', 'whatsapp', 'bale', 'github' );
	$out      = array();
	foreach ( $networks as $net ) {
		$url = zc_opt( 'zc_social_' . $net, '' );
		if ( $url ) {
			$out[ $net ] = $url;
		}
	}
	if ( empty( $out ) ) {
		$out = array(
			'telegram'  => '#',
			'instagram' => '#',
			'twitter'   => '#',
			'linkedin'  => '#',
			'github'    => '#',
		);
	}
	return $out;
}

/**
 * آیکن شبکه اجتماعی.
 *
 * @param string $net نام.
 * @param int    $size اندازه.
 * @return string
 */
function zc_social_icon( $net, $size = 18 ) {
	$paths = array(
		'telegram'  => '<path d="M21.9 4.3 2.8 11.6c-1 .4-1 1.1-.2 1.4l4.8 1.5 1.8 5.6c.2.6.4.8 1 .8.5 0 .7-.2 1-.5l2.4-2.4 5 3.7c.9.5 1.6.2 1.8-.9l3.3-15.4c.3-1.3-.5-1.9-1.8-1.1z" fill="currentColor" stroke="none"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5.4"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/>',
		'twitter'   => '<path d="M17.5 3h3.3l-7.2 8.3L22 21h-6.6l-5.2-6.7L4.2 21H.9l7.7-8.9L.5 3h6.8l4.7 6.2zM16.3 19h1.8L7.8 4.9H5.9z" fill="currentColor" stroke="none"/>',
		'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7.5 10v7M7.5 7v.1M11.5 17v-4a2.5 2.5 0 0 1 5 0v4"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10.5 9.5 5 2.5-5 2.5z" fill="currentColor" stroke="none"/>',
		'aparat'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.2"/><circle cx="7" cy="7" r="1.4" fill="currentColor" stroke="none"/><circle cx="17" cy="17" r="1.4" fill="currentColor" stroke="none"/><circle cx="17" cy="7" r="1.4" fill="currentColor" stroke="none"/><circle cx="7" cy="17" r="1.4" fill="currentColor" stroke="none"/>',
		'whatsapp'  => '<path d="M20.5 11.6a8.4 8.4 0 0 1-12.4 7.4L3.5 20.5l1.6-4.4a8.4 8.4 0 1 1 15.4-4.5z"/><path d="M8.8 8.4c.3-.1.7 0 .9.4l.7 1.3c.1.3.1.6-.1.8l-.4.5c-.2.2-.2.4-.1.6.5.9 1.2 1.6 2.1 2.1.2.1.4.1.6-.1l.5-.4c.2-.2.5-.2.8-.1l1.3.7c.4.2.5.6.4.9-.3.8-1.1 1.3-2 1.2-2.9-.3-5.4-2.8-5.7-5.7-.1-.9.3-1.7 1-2.2z" fill="currentColor" stroke="none"/>',
		'bale'      => '<path d="M12 2.5 4 6.2v6.1c0 4.6 3.3 8.6 8 9.7 4.7-1.1 8-5.1 8-9.7V6.2z"/><path d="m8.6 12.2 2.3 2.3 4.5-4.7"/>',
		'github'    => '<path d="M9 19c-4.3 1.4-4.3-2.2-6-2.6m12 5.2v-3.6a3.1 3.1 0 0 0-.9-2.4c2.9-.3 6-1.4 6-6.4a5 5 0 0 0-1.4-3.4 4.6 4.6 0 0 0-.1-3.5s-1.1-.3-3.6 1.4a12.3 12.3 0 0 0-6.4 0C6.1 1.9 5 2.2 5 2.2a4.6 4.6 0 0 0-.1 3.5A5 5 0 0 0 3.5 9.2c0 4.9 3 6.1 5.9 6.4a3.1 3.1 0 0 0-.9 2.4v3.6"/>',
		'facebook'  => '<path d="M14 8h3V4.5h-3A4.5 4.5 0 0 0 9.5 9v2.5H7V15h2.5v6.5H13V15h3l.5-3.5H13V9c0-.6.4-1 1-1z" fill="currentColor" stroke="none"/>',
	);

	if ( ! isset( $paths[ $net ] ) ) {
		return '';
	}

	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%2$s</svg>',
		(int) $size,
		$paths[ $net ]
	);
}

/**
 * سربرگ بخش (heading) با استایل طرح مرجع.
 *
 * @param string $title    عنوان.
 * @param string $subtitle زیرعنوان.
 * @param string $align    چیدمان.
 * @param bool   $arrow    نمایش فلش.
 * @return void
 */
function zc_section_heading( $title, $subtitle = '', $align = 'center', $arrow = true ) {
	echo '<div class="zc-heading' . ( 'center' !== $align ? ' zc-heading--start' : '' ) . '" data-zc-anim="up">';
	if ( $arrow ) {
		echo '<div class="zc-heading__arrow">';
		zc_the_icon( 'chevron', 30 );
		echo '</div>';
	}
	echo '<h2 class="zc-heading__title">' . wp_kses_post( $title ) . '</h2>';
	if ( $subtitle ) {
		echo '<p class="zc-heading__sub">' . esc_html( $subtitle ) . '</p>';
	}
	echo '</div>';
}

/**
 * ستاره‌های امتیاز.
 *
 * @param float $rating امتیاز از ۵.
 * @param bool  $show_num نمایش عدد.
 * @return string
 */
function zc_stars( $rating, $show_num = true ) {
	$rating = max( 0, min( 5, (float) $rating ) );

	/*
	 * پیش‌تر برای هر امتیاز پنج تگ <svg> جداگانه (هرکدام با یک <path>)
	 * چاپ می‌شد؛ یعنی ۱۰ گره DOM برای هر کارت. در صفحه‌ای با سی کارت
	 * محصول و دوره، همین یک قلم حدود ۳۰۰ گره اضافه می‌کرد.
	 *
	 * حالا ستاره‌ها با یک گرادیان CSS روی همان یک عنصر کشیده می‌شوند:
	 * درصد پرشدگی از طریق متغیر --zc-star به CSS داده می‌شود. علاوه بر
	 * سبک‌شدن DOM، امتیازهای اعشاری (مثلاً ۴٫۳) هم دقیق نمایش داده
	 * می‌شوند، در حالی که روش قبلی رُند می‌کرد.
	 */
	$percent = round( ( $rating / 5 ) * 100, 1 );

	$out = sprintf(
		'<span class="zc-stars" style="--zc-star:%1$s%%" role="img" aria-label="%2$s"></span>',
		esc_attr( $percent ),
		esc_attr(
			sprintf(
				/* translators: %s: امتیاز */
				__( 'امتیاز %s از ۵', 'zarincode' ),
				zc_fa_num( number_format( $rating, 1 ) )
			)
		)
	);

	if ( $show_num && $rating > 0 ) {
		$out .= '<span class="zc-rating-num">' . esc_html( zc_fa_num( number_format( $rating, 1 ) ) ) . '</span>';
	}

	return $out;
}

/**
 * متای پست (تاریخ، نویسنده، بازدید).
 *
 * @param array $show بخش‌های نمایشی.
 * @return void
 */
function zc_post_meta( $show = array( 'date', 'author', 'comments', 'views' ) ) {
	echo '<div class="zc-entry__meta">';

	if ( in_array( 'author', $show, true ) ) {
		printf(
			'<span>%s<a href="%s">%s</a></span>',
			zc_icon( 'user', 16 ), // phpcs:ignore
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
	if ( in_array( 'date', $show, true ) ) {
		printf(
			'<span>%s<time datetime="%s">%s</time></span>',
			zc_icon( 'calendar', 16 ), // phpcs:ignore
			esc_attr( get_the_date( 'c' ) ),
			esc_html( zc_fa_num( get_the_date() ) )
		);
	}
	if ( in_array( 'comments', $show, true ) && comments_open() ) {
		printf(
			'<span>%s%s %s</span>',
			zc_icon( 'chat', 16 ), // phpcs:ignore
			esc_html( zc_fa_num( get_comments_number() ) ),
			esc_html__( 'دیدگاه', 'zarincode' )
		);
	}
	if ( in_array( 'views', $show, true ) ) {
		printf(
			'<span>%s%s %s</span>',
			zc_icon( 'eye', 16 ), // phpcs:ignore
			esc_html( zc_fa_num( zc_get_views( get_the_ID() ) ) ),
			esc_html__( 'بازدید', 'zarincode' )
		);
	}
	if ( in_array( 'reading', $show, true ) ) {
		printf(
			'<span>%s%s %s</span>',
			zc_icon( 'clock', 16 ), // phpcs:ignore
			esc_html( zc_fa_num( zc_reading_time() ) ),
			esc_html__( 'دقیقه مطالعه', 'zarincode' )
		);
	}

	echo '</div>';
}

/**
 * زمان تقریبی مطالعه.
 *
 * @param int $post_id شناسه.
 * @return int
 */
function zc_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	// متن فارسی: تخمین بر اساس تعداد کاراکتر.
	if ( $words < 50 ) {
		$words = mb_strlen( wp_strip_all_tags( $content ) ) / 5;
	}
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * افزایش شمارنده بازدید.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_set_views( $post_id ) {
	$key   = 'zc_views';
	$count = (int) get_post_meta( $post_id, $key, true );
	update_post_meta( $post_id, $key, $count + 1 );
}

/**
 * دریافت تعداد بازدید.
 *
 * @param int $post_id شناسه.
 * @return int
 */
function zc_get_views( $post_id ) {
	return (int) get_post_meta( $post_id, 'zc_views', true );
}

/**
 * ثبت بازدید در نمایش تک‌نوشته.
 *
 * @return void
 */
function zc_track_views() {
	if ( is_singular() && ! is_user_logged_in() ) {
		global $post;
		if ( $post ) {
			zc_set_views( $post->ID );
		}
	}
}
add_action( 'wp_head', 'zc_track_views' );

/**
 * صفحه‌بندی سفارشی.
 *
 * @param WP_Query|null $query کوئری.
 * @return void
 */
function zc_pagination( $query = null ) {
	global $wp_query;
	$q = $query ? $query : $wp_query;

	if ( $q->max_num_pages < 2 ) {
		return;
	}

	/*
	 * در چیدمان راست‌به‌چپ، «قبلی» به سمت راست و «بعدی» به سمت چپ
	 * اشاره می‌کند. متن پنهان (screen-reader-text) هم اضافه می‌شود تا
	 * دکمه برای صفحه‌خوان‌ها و موتورهای جست‌وجو معنا داشته باشد.
	 */
	$prev = zc_icon( 'arrow-right', 18 )
		. '<span class="screen-reader-text">' . esc_html__( 'صفحه قبلی', 'zarincode' ) . '</span>';

	$next = zc_icon( 'arrow-left', 18 )
		. '<span class="screen-reader-text">' . esc_html__( 'صفحه بعدی', 'zarincode' ) . '</span>';

	$links = paginate_links(
		array(
			'total'     => $q->max_num_pages,
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'mid_size'  => 1,
			'prev_text' => $prev,
			'next_text' => $next,
			'type'      => 'array',
		)
	);

	if ( ! $links ) {
		return;
	}

	echo '<nav class="zc-pagination" role="navigation" aria-label="' . esc_attr__( 'صفحه‌بندی', 'zarincode' ) . '">';

	foreach ( $links as $link ) {
		/*
		 * wp_kses_post() تگ <svg> را نمی‌شناسد و حذفش می‌کند؛ نتیجه
		 * دکمه‌های قبلی/بعدیِ کاملاً خالی بود. zc_kses_icon() همان
		 * پاک‌سازی امن را انجام می‌دهد ولی آیکن را نگه می‌دارد.
		 */
		echo zc_kses_icon( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</nav>';
}


/**
 * آیکن مناسب برای هر دکمه‌ی اشتراک‌گذاری.
 *
 * برخی مقصدها (مانند ایمیل، واتساپ یا کپی لینک) شبکه‌ی اجتماعی
 * نیستند و در کتابخانه‌ی zc_social_icon() آیکن ندارند؛ برای آن‌ها از
 * آیکن‌های عمومی قالب استفاده می‌شود.
 *
 * @param string $net  نام مقصد.
 * @param int    $size اندازه.
 * @return string
 */
function zc_share_icon( $net, $size = 18 ) {
	// نگاشت مقصدهای غیر شبکه‌اجتماعی به آیکن‌های عمومی قالب.
	$fallback = array(
		'email'    => 'mail',
		'copy'     => 'code',
		'print'    => 'download',
		'whatsapp' => 'chat',
	);

	if ( isset( $fallback[ $net ] ) ) {
		$icon = zc_icon( $fallback[ $net ], $size );

		if ( $icon ) {
			return $icon;
		}
	}

	$social = zc_social_icon( $net, $size );

	if ( $social ) {
		return $social;
	}

	// آخرین گزینه: آیکن ارسال.
	return zc_icon( 'send', $size );
}

/**
 * دکمه‌های اشتراک‌گذاری.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_share_buttons( $post_id = 0, $force = false ) {
	if ( ! zc_opt( 'zc_share_enable', true ) ) {
		return;
	}

	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( get_the_title( $post_id ) );

	$networks = array(
		'telegram' => array( 'label' => 'تلگرام', 'url' => "https://t.me/share/url?url={$url}&text={$title}" ),
		'whatsapp' => array( 'label' => 'واتساپ', 'url' => "https://api.whatsapp.com/send?text={$title}%20{$url}" ),
		'bale'     => array( 'label' => 'بله', 'url' => "https://web.bale.ai/share/url?url={$url}&text={$title}" ),
		'twitter'  => array( 'label' => 'ایکس', 'url' => "https://twitter.com/intent/tweet?url={$url}&text={$title}" ),
		'linkedin' => array( 'label' => 'لینکدین', 'url' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}" ),
		'facebook' => array( 'label' => 'فیسبوک', 'url' => "https://www.facebook.com/sharer/sharer.php?u={$url}" ),
		'email'    => array( 'label' => 'ایمیل', 'url' => "mailto:?subject={$title}&body={$url}" ),
	);

	/*
	 * جلوگیری از چاپ دوباره: هم فیلتر the_content و هم قالب‌های تک‌نوشته
	 * ممکن است این تابع را صدا بزنند. تنها اولین فراخوانی برای هر نوشته
	 * خروجی می‌دهد؛ مگر اینکه صراحتاً درخواست چاپ مجدد شده باشد.
	 */
	if ( function_exists( 'zc_share_rendered' ) && ! $force ) {
		if ( zc_share_rendered() ) {
			return;
		}

		zc_share_rendered( true );
	}

	echo '<div class="zc-share">';
	echo '<span class="zc-share__label">' . zc_icon( 'send', 17 ) . esc_html__( 'اشتراک‌گذاری:', 'zarincode' ) . '</span>'; // phpcs:ignore
	echo '<div class="zc-share__list">';

	foreach ( $networks as $net => $data ) {
		if ( ! zc_opt( 'zc_share_' . $net, true ) ) {
			continue;
		}
		printf(
			'<a href="%1$s" class="zc-share__btn" data-net="%2$s" target="_blank" rel="noopener nofollow" aria-label="%3$s" title="%3$s">%4$s</a>',
			esc_url( $data['url'] ),
			esc_attr( $net ),
			esc_attr( $data['label'] ),
			zc_share_icon( $net, 18 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	printf(
		'<button type="button" class="zc-share__btn" data-net="copy" data-url="%1$s" aria-label="%2$s" title="%2$s">%3$s</button>',
		esc_url( get_permalink( $post_id ) ),
		esc_attr__( 'کپی لینک', 'zarincode' ),
		zc_icon( 'code', 18 ) // phpcs:ignore
	);

	echo '</div></div>';
}

/**
 * نوشته‌های قبلی و بعدی.
 *
 * @return void
 */
function zc_post_navigation() {
	if ( ! zc_opt( 'zc_prevnext_enable', true ) ) {
		return;
	}

	$prev = get_previous_post();
	$next = get_next_post();

	if ( ! $prev && ! $next ) {
		return;
	}

	echo '<div class="zc-postnav">';

	if ( $prev ) {
		printf(
			'<a href="%1$s" class="zc-postnav__item"><span class="zc-postnav__arrow">%2$s</span><span><span class="zc-postnav__label">%3$s</span><span class="zc-postnav__title">%4$s</span></span></a>',
			esc_url( get_permalink( $prev ) ),
			zc_icon( 'arrow-left', 20 ), // phpcs:ignore
			esc_html__( 'نوشته قبلی', 'zarincode' ),
			esc_html( get_the_title( $prev ) )
		);
	} else {
		echo '<span></span>';
	}

	if ( $next ) {
		printf(
			'<a href="%1$s" class="zc-postnav__item zc-postnav__item--next"><span class="zc-postnav__arrow">%2$s</span><span><span class="zc-postnav__label">%3$s</span><span class="zc-postnav__title">%4$s</span></span></a>',
			esc_url( get_permalink( $next ) ),
			zc_icon( 'arrow-left', 20 ), // phpcs:ignore
			esc_html__( 'نوشته بعدی', 'zarincode' ),
			esc_html( get_the_title( $next ) )
		);
	}

	echo '</div>';
}

/**
 * نوشته‌های مرتبط.
 *
 * @param int    $count تعداد.
 * @param string $type  نوع پست.
 * @return void
 */
function zc_related_posts( $count = 3, $type = '' ) {
	if ( ! zc_opt( 'zc_related_enable', true ) ) {
		return;
	}

	$post_id = get_the_ID();
	$type    = $type ? $type : get_post_type( $post_id );
	$tax     = 'post' === $type ? 'category' : ( 'zc_course' === $type ? 'zc_course_cat' : ( 'product' === $type ? 'product_cat' : 'category' ) );
	$terms   = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'           => $type,
		'posts_per_page'      => $count,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( ! empty( $terms ) ) {
		$args['tax_query'] = array( // phpcs:ignore
			array(
				'taxonomy' => $tax,
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		);
	}

	$related = new WP_Query( $args );

	if ( ! $related->have_posts() ) {
		return;
	}

	echo '<section class="zc-related zc-section zc-section--sm">';
	zc_section_heading( __( 'مطالب <span>مرتبط</span>', 'zarincode' ), '', 'start', false );
	echo '<div class="zc-grid zc-grid--' . (int) $count . '">';

	while ( $related->have_posts() ) {
		$related->the_post();
		get_template_part( 'template-parts/content/card', 'post' );
	}

	echo '</div></section>';
	wp_reset_postdata();
}

/**
 * رندر بخش المنتور در محل مشخص (هدر/فوتر سفارشی).
 *
 * @param string $location محل.
 * @return bool
 */
function zc_render_elementor_location( $location ) {
	$tpl_id = (int) zc_opt( 'zc_' . $location . '_template', 0 );

	if ( ! $tpl_id || ! zc_is_elementor() ) {
		return false;
	}

	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $tpl_id ); // phpcs:ignore
	return true;
}

/**
 * نمایش هیرو صفحات داخلی.
 *
 * @param string $title عنوان.
 * @param string $sub   زیرعنوان.
 * @return void
 */
function zc_page_hero( $title = '', $sub = '' ) {
	if ( ! $title ) {
		if ( is_home() ) {
			$title = get_the_title( get_option( 'page_for_posts' ) ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'بلاگ', 'zarincode' );
		} elseif ( is_archive() ) {
			$title = get_the_archive_title();
		} elseif ( is_search() ) {
			$title = sprintf( __( 'نتایج جستجو برای: %s', 'zarincode' ), get_search_query() );
		} elseif ( is_404() ) {
			$title = __( 'صفحه یافت نشد', 'zarincode' );
		} else {
			$title = get_the_title();
		}
	}
	?>
	<div class="zc-page-hero">
		<div class="zc-container zc-page-hero__in">
			<span class="zc-page-hero__eyebrow"><?php esc_html_e( 'زرین کد', 'zarincode' ); ?></span>
			<h1><?php echo wp_kses_post( $title ); ?></h1>
			<?php if ( $sub ) : ?>
				<p class="zc-page-hero__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
			<?php zc_breadcrumb(); ?>
		</div>
	</div>
	<?php
}

/**
 * خروجی نوار پیشرفت مطالعه.
 *
 * @return void
 */
function zc_reading_progress_bar() {
	echo '<div style="position:fixed;top:0;inset-inline:0;height:3px;z-index:999;background:transparent"><div class="zc-reading-progress" style="height:100%;width:0;background:var(--zc-grad-gold);transition:width .1s"></div></div>';
}
