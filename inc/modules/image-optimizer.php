<?php
/**
 * موتور بهینه‌سازی و تبدیل فرمت تصاویر زرین کد
 * ---------------------------------------------------------------------------
 * به‌صورت خودکار هر تصویری که در سایت آپلود می‌شود پردازش می‌کند:
 *  - کاهش حجم بدون افت محسوس کیفیت (فشرده‌سازی بهینه).
 *  - تبدیل فرمت به WebP.
 *  - حذف فایل اصلی (اختیاری ولی پیش‌فرض فعال).
 *  - ساخت نسخه‌های WebP برای همهٔ سایزهای تصویر (مقیاس‌پذیر متناسب با محل
 *    استفاده) به‌همراه سایزهای نرم (بدون برش) تا تصویر «کامل» دیده شود.
 *
 * علاوه بر آن یک تابع کمکی zc_image() فراهم می‌کند که تصویر را واکنش‌گرا
 * (srcset) و با نسبت ابعاد کامل (object-fit:contain) نمایش می‌دهد تا هیچ‌جا
 * بریده نشود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا موتور فعال است؟
 *
 * @return bool
 */
function zc_image_opt_enabled() {
	return (bool) zc_opt( 'zc_image_opt_enable', true );
}

/**
 * کیفیت WebP (۰-۱۰۰).
 *
 * @return int
 */
function zc_image_quality() {
	return max( 40, min( 100, (int) zc_opt( 'zc_image_opt_quality', 90 ) ) );
}

/**
 * آیا حذف فایل اصلی فعال است؟
 *
 * @return bool
 */
function zc_image_delete_original() {
	return (bool) zc_opt( 'zc_image_opt_delete_original', true );
}

/**
 * آیا تبدیل به WebP فعال است؟
 *
 * @return bool
 */
function zc_image_webp_enabled() {
	return (bool) zc_opt( 'zc_image_opt_webp', true );
}

/**
 * آیا تولید سایزهای WebP فعال است؟
 *
 * @return bool
 */
function zc_image_sizes_enabled() {
	return (bool) zc_opt( 'zc_image_opt_sizes', true );
}

/**
 * آیا کتابخانهٔ پردازش تصویر (GD یا Imagick) در دسترس است؟
 *
 * @return bool
 */
function zc_image_opt_available() {
	return ( function_exists( 'imagewebp' ) || class_exists( 'Imagick' ) );
}

/**
 * بارگذاری تصویر در یک منبع (با رعایت جهت EXIF).
 *
 * @param string $path مسیر فایل.
 * @return \GdImage|\Imagick|null
 */
function zc_image_load( $path ) {
	if ( ! file_exists( $path ) ) {
		return null;
	}

	$mime = function_exists( 'mime_content_type' ) ? mime_content_type( $path ) : wp_check_filetype( $path )['type'];

	// Imagick در اولویت (کیفیت و جهت بهتر).
	if ( class_exists( 'Imagick' ) ) {
		try {
			$im = new Imagick( $path );
			if ( $im->valid() ) {
				return $im;
			}
		} catch ( Exception $e ) {
			// ادامه با GD.
		}
	}

	if ( ! function_exists( 'imagecreatefromstring' ) ) {
		return null;
	}

	$data = file_get_contents( $path ); // phpcs:ignore
	if ( false === $data ) {
		return null;
	}

	$im = @imagecreatefromstring( $data );
	if ( false === $im ) {
		return null;
	}

	// اعمال جهت EXIF برای JPEG.
	if ( 'image/jpeg' === $mime && function_exists( 'exif_read_data' ) ) {
		$exif = @exif_read_data( $path );
		if ( is_array( $exif ) && ! empty( $exif['Orientation'] ) ) {
			switch ( (int) $exif['Orientation'] ) {
				case 3: $im = imagerotate( $im, 180, 0 ); break;
				case 6: $im = imagerotate( $im, -90, 0 ); break;
				case 8: $im = imagerotate( $im, 90, 0 ); break;
			}
		}
	}

	return $im;
}

/**
 * ذخیرهٔ یک منبع به‌صورت WebP.
 *
 * @param \GdImage|\Imagick $im      منبع.
 * @param string            $dest    مسیر خروجی.
 * @param int               $quality کیفیت.
 * @return bool
 */
function zc_image_save_webp( $im, $dest, $quality = 90 ) {
	if ( $im instanceof Imagick ) {
		try {
			$im->setImageFormat( 'webp' );
			$im->setImageCompressionQuality( $quality );
			$im->stripImage();
			return $im->writeImage( $dest );
		} catch ( Exception $e ) {
			return false;
		}
	}

	if ( is_resource( $im ) || $im instanceof \GdImage ) {
		// پاک کردن پس‌زمینهٔ شفاف برای PNG/GIF.
		imagealphablending( $im, false );
		imagesavealpha( $im, true );
		return imagewebp( $im, $dest, $quality );
	}

	return false;
}

/**
 * تبدیل یک فایل تصویری به WebP در همان مسیر (و حذف فایل اصلی).
 * اگر تبدیل ناموفق باشد فایل اصلی حفظ می‌شود.
 *
 * @param string $file مسیر کامل فایل.
 * @return string مسیر جدید (WebP) یا همان مسیر اصلی اگر ناموفق بود.
 */
function zc_image_convert_file( $file ) {
	if ( empty( $file ) || ! file_exists( $file ) || ! zc_image_webp_enabled() ) {
		return $file;
	}

	$mime = function_exists( 'mime_content_type' ) ? mime_content_type( $file ) : '';
	// SVG و WebP از پیش نیازی به تبدیل ندارند.
	if ( in_array( $mime, array( 'image/svg+xml', 'image/webp', 'image/gif' ), true ) ) {
		if ( 'image/webp' === $mime ) {
			// WebP موجود را دوباره فشرده می‌کنیم.
			$im = zc_image_load( $file );
			if ( $im ) {
				zc_image_save_webp( $im, $file, zc_image_quality() );
			}
		}
		return $file;
	}

	$im = zc_image_load( $file );
	if ( ! $im ) {
		return $file;
	}

	$dest = preg_replace( '/\.(jpe?g|png|gif|bmp|tiff?)$/i', '.webp', $file );
	if ( $dest === $file ) {
		$dest = $file . '.webp';
	}

	if ( ! zc_image_save_webp( $im, $dest, zc_image_quality() ) ) {
		return $file;
	}

	if ( $im instanceof Imagick ) {
		$im->destroy();
	}

	// حذف فایل اصلی پس از موفقیت.
	if ( zc_image_delete_original() && is_file( $file ) && $dest !== $file ) {
		@unlink( $file );
	}

	return $dest;
}

/**
 * پردازش خودکار یک تصویر پس از آپلود.
 * به‌عنوان فیلتر روی wp_generate_attachment_metadata.
 *
 * @param array $metadata متادیتای پیوست.
 * @param int   $id       شناسهٔ پیوست.
 * @return array
 */
function zc_optimize_attachment( $metadata, $id ) {
	if ( ! zc_image_opt_enabled() || ! zc_image_opt_available() ) {
		return $metadata;
	}

	$file = get_attached_file( $id );
	if ( ! $file || ! file_exists( $file ) ) {
		return $metadata;
	}

	$mime = get_post_mime_type( $id );
	if ( ! $mime || 0 !== strpos( $mime, 'image/' ) || 'image/svg+xml' === $mime ) {
		return $metadata;
	}

	// ۱) فایل اصلی → WebP.
	$new_main = zc_image_convert_file( $file );
	if ( $new_main !== $file ) {
		update_attached_file( $id, $new_main );
		$metadata['file'] = str_replace( wp_get_upload_dir()['basedir'] . '/', '', $new_main );
		// به‌روزرسانی نوع MIME به webp تا کتابخانهٔ رسانه درست نمایش دهد.
		wp_update_post(
			array(
				'ID'             => $id,
				'post_mime_type' => 'image/webp',
			)
		);
	}

	// ۲) هر سایز → WebP.
	if ( zc_image_sizes_enabled() && ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
		$dir = dirname( $file );
		foreach ( $metadata['sizes'] as $name => $sizedata ) {
			if ( empty( $sizedata['file'] ) ) {
				continue;
			}
			$size_file = trailingslashit( $dir ) . $sizedata['file'];
			if ( ! file_exists( $size_file ) ) {
				continue;
			}
			$new_size = zc_image_convert_file( $size_file );
			if ( $new_size !== $size_file ) {
				$metadata['sizes'][ $name ]['file']      = basename( $new_size );
				$metadata['sizes'][ $name ]['mime-type'] = 'image/webp';
			}
		}
	}

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'zc_optimize_attachment', 10, 2 );

/**
 * ثبت سایزهای نرم (بدون برش) برای نمایش «تصویر کامل».
 * این سایزها تصویر را با نسبت ابعاد اصلی نگه می‌دارند.
 *
 * @return void
 */
function zc_register_soft_image_sizes() {
	add_image_size( 'zc-card-soft', 600, 400, false );
	add_image_size( 'zc-card-lg-soft', 900, 560, false );
	add_image_size( 'zc-wide-soft', 1400, 620, false );
}
add_action( 'after_setup_theme', 'zc_register_soft_image_sizes' );

/**
 * نگاشت یک سایز برش‌خورده به معادل نرم (بدون برش).
 *
 * @param string $size نام سایز.
 * @return string
 */
function zc_image_soft_size( $size ) {
	$map = array(
		'zc-card'    => 'zc-card-soft',
		'zc-card-lg' => 'zc-card-lg-soft',
		'zc-wide'    => 'zc-wide-soft',
		'thumbnail'  => 'medium',
		'medium'     => 'medium',
		'large'      => 'large',
	);
	return isset( $map[ $size ] ) ? $map[ $size ] : $size;
}

/**
 * دریافت URL یک سایز تصویر (webp-aware).
 *
 * @param int    $id   پیوست.
 * @param string $size سایز.
 * @return string
 */
function zc_image_url( $id, $size = 'full' ) {
	$src = wp_get_attachment_image_src( $id, $size );
	return $src ? $src[0] : '';
}

/**
 * رندر واکنش‌گرا و «بدون برش» یک تصویر.
 *
 * از srcset همهٔ سایزهای WebP استفاده می‌کند و با کلاس zc-img (object-fit:contain)
 * تصویر را با نسبت ابعاد کامل و بدون بریدگی نمایش می‌دهد.
 *
 * @param int    $id   شناسهٔ پیوست.
 * @param string $size سایز پایه (به معادل نرم نگاشت می‌شود).
 * @param array  $attr صفات img.
 * @return string
 */
function zc_image( $id = 0, $size = 'zc-card', $attr = array() ) {
	$id   = $id ? $id : get_the_ID();
	$soft = zc_image_soft_size( $size );

	$attr = wp_parse_args(
		$attr,
		array(
			'loading'  => zc_opt( 'zc_lazyload', true ) ? 'lazy' : 'eager',
			'decoding' => 'async',
			'class'    => 'zc-img zc-img--fit',
			'alt'      => get_the_title( $id ),
		)
	);

	$src = zc_image_url( $id, $soft );
	if ( ! $src ) {
		return sprintf(
			'<img src="%1$s" alt="%2$s" class="%3$s" loading="lazy" decoding="async" width="600" height="400">',
			esc_url( ZC_ASSETS . 'img/placeholder.svg' ),
			esc_attr( $attr['alt'] ),
			esc_attr( $attr['class'] )
		);
	}

	$srcset = wp_get_attachment_image_srcset( $id, $soft );
	$sizes  = wp_get_attachment_image_sizes( $soft, $attr );

	$html = '<img src="' . esc_url( $src ) . '"';
	if ( $srcset ) {
		$html .= ' srcset="' . esc_attr( $srcset ) . '"';
	}
	if ( $sizes ) {
		$html .= ' sizes="' . esc_attr( $sizes ) . '"';
	}
	foreach ( $attr as $k => $v ) {
		$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	$html .= '>';

	return $html;
}

/**
 * به‌روزرسانی zc_thumbnail برای افزودن نسبت ابعاد کامل در صورت درخواست.
 * این تابع به‌صورت واکنش‌گرا و با srcset عکس را نمایش می‌دهد.
 *
 * @param int    $post_id پست.
 * @param string $size    سایز.
 * @param array  $attr    صفات.
 * @return string
 */
function zc_thumbnail_webp( $post_id = 0, $size = 'zc-card', $attr = array() ) {
	if ( zc_image_opt_enabled() && has_post_thumbnail( $post_id ) ) {
		return zc_image( get_post_thumbnail_id( $post_id ), $size, $attr );
	}
	return zc_thumbnail( $post_id, $size, $attr );
}

/**
 * اکشن‌های پیشخوان: بهینه‌سازی مجدد تصاویر موجود.
 *
 * @return void
 */
function zc_image_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// بهینه‌سازی مجدد همهٔ تصاویر.
	if ( isset( $_GET['zc_reoptimize'] ) && '1' === $_GET['zc_reoptimize'] && check_admin_referer( 'zc_reoptimize' ) ) { // phpcs:ignore
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$done = 0;
		foreach ( $ids as $pid ) {
			$meta = wp_get_attachment_metadata( $pid );
			$new  = zc_optimize_attachment( $meta, $pid );
			wp_update_attachment_metadata( $pid, $new );
			$done++;
		}

		wp_safe_redirect(
			add_query_arg(
				array( 'zc_reopt_done' => $done ),
				admin_url( 'upload.php' )
			)
		);
		exit;
	}
}
add_action( 'admin_init', 'zc_image_admin_actions' );

/**
 * نوار اطلاع در کتابخانهٔ رسانه برای بهینه‌سازی مجدد.
 *
 * @return void
 */
function zc_image_media_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id ) {
		return;
	}
	if ( isset( $_GET['zc_reopt_done'] ) ) { // phpcs:ignore
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( 'بهینه‌سازی %s تصویر انجام شد.', 'zarincode' ), absint( $_GET['zc_reopt_done'] ) ) ) . '</p></div>'; // phpcs:ignore
	}
	$url = wp_nonce_url( admin_url( 'upload.php?zc_reoptimize=1' ), 'zc_reoptimize' );
	echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'بهینه‌سازی تصاویر زرین کد', 'zarincode' ) . ' — ' . esc_html__( 'تصاویر آپلودی به‌صورت خودکار به WebP تبدیل می‌شوند.', 'zarincode' ) . ' <a class="button" style="margin-inline-start:8px" href="' . esc_url( $url ) . '">' . esc_html__( 'بهینه‌سازی مجدد همهٔ تصاویر', 'zarincode' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'zc_image_media_notice' );
