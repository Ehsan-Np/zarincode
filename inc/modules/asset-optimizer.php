<?php
/**
 * بهینه‌ساز دارایی‌ها: فشرده‌سازی و ادغام CSS و JS
 *
 * قالب چندین شیوه‌نامه و اسکریپت دارد. در حالت عادی هرکدام یک درخواست
 * جداگانه‌اند و با فاصله‌ها و توضیحات کامل ارسال می‌شوند. این ماژول
 * نسخه‌ی فشرده و یکپارچه می‌سازد و در پوشه‌ی uploads ذخیره می‌کند تا
 * فقط یک‌بار تولید شود.
 *
 * چرا در uploads و نه در خود قالب؟ چون پوشه‌ی قالب معمولاً قابل نوشتن
 * نیست و با هر بروزرسانی پاک می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا بهینه‌سازی دارایی‌ها فعال است؟
 *
 * @return bool
 */
function zc_assets_optimized() {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! zc_opt( 'zc_optimize_in_debug', false ) ) {
		return false;
	}

	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	return (bool) zc_opt( 'zc_optimize_assets', true );
}

/**
 * مسیر پوشه‌ی کش دارایی‌ها.
 *
 * @param string $file نام فایل.
 * @return array مسیر و نشانی.
 */
function zc_asset_cache_path( $file = '' ) {
	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'zarincode-cache/';
	$url = trailingslashit( $up['baseurl'] ) . 'zarincode-cache/';

	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	return array(
		'dir' => $dir . $file,
		'url' => $url . $file,
	);
}

/**
 * فشرده‌سازی ساده و امن CSS.
 *
 * توضیحات و فاصله‌های غیرضروری حذف می‌شوند. عمداً از تغییرات پرخطر
 * (مانند ادغام قواعد) پرهیز شده تا خروجی همیشه معتبر بماند.
 *
 * @param string $css کد ورودی.
 * @return string
 */
function zc_minify_css( $css ) {
	// حذف توضیحات، اما نگه‌داشتن توضیحات مهم /*! ... */.
	$css = preg_replace( '#/\*(?!!)[^*]*\*+([^/][^*]*\*+)*/#', '', $css );

	// فشرده‌کردن فاصله‌ها.
	$css = preg_replace( '/\s+/', ' ', $css );

	// حذف فاصله‌ی اطراف نشانه‌های ساختاری.
	$css = preg_replace( '/\s*([{}:;,>~])\s*/', '$1', $css );

	// حذف سمی‌کالن پیش از آکولاد بسته.
	$css = str_replace( ';}', '}', $css );

	// صفرهای اضافی.
	$css = preg_replace( '/(:|\s)0\.(\d+)/', '$1.$2', $css );

	return trim( $css );
}

/**
 * فشرده‌سازی محافظه‌کارانه‌ی جاوااسکریپت.
 *
 * تنها توضیحات کامل و فاصله‌های ابتدای خط حذف می‌شوند. حذف خطوط
 * جدید انجام نمی‌شود چون بدون تحلیل کامل نحو، خطر شکستن کد (به‌ویژه
 * درج خودکار سمی‌کالن) وجود دارد. ماشین حالت زیر رشته‌ها، تمپلیت‌ها
 * و کاراکترهای فراری را تشخیص می‌دهد تا توضیحات درون رشته حذف نشوند.
 *
 * @param string $js کد ورودی.
 * @return string
 */
function zc_minify_js( $js ) {
	$out   = '';
	$len   = strlen( $js );
	$i     = 0;
	$state = 'code'; // code | s-quote | d-quote | template | line-comment | block-comment.

	while ( $i < $len ) {
		$c    = $js[ $i ];
		$next = ( $i + 1 < $len ) ? $js[ $i + 1 ] : '';

		if ( 'code' === $state ) {
			if ( '/' === $c && '/' === $next ) {
				$state = 'line-comment';
				$i    += 2;
				continue;
			}

			if ( '/' === $c && '*' === $next ) {
				$state = 'block-comment';
				$i    += 2;
				continue;
			}

			if ( "'" === $c ) {
				$state = 's-quote';
			} elseif ( '"' === $c ) {
				$state = 'd-quote';
			} elseif ( '`' === $c ) {
				$state = 'template';
			}

			$out .= $c;
			++$i;
			continue;
		}

		if ( 'line-comment' === $state ) {
			if ( "\n" === $c ) {
				$state = 'code';
				$out  .= "\n";
			}

			++$i;
			continue;
		}

		if ( 'block-comment' === $state ) {
			if ( '*' === $c && '/' === $next ) {
				$state = 'code';
				$i    += 2;
				continue;
			}

			++$i;
			continue;
		}

		// داخل رشته‌ها: کاراکتر فراری را دست‌نخورده رد می‌کنیم.
		if ( '\\' === $c ) {
			$out .= $c . $next;
			$i   += 2;
			continue;
		}

		if (
			( 's-quote' === $state && "'" === $c )
			|| ( 'd-quote' === $state && '"' === $c )
			|| ( 'template' === $state && '`' === $c )
		) {
			$state = 'code';
		}

		$out .= $c;
		++$i;
	}

	// حذف فاصله‌ی ابتدای خط و خطوط خالی.
	$out = preg_replace( '/^[ \t]+/m', '', $out );
	$out = preg_replace( '/\n{2,}/', "\n", $out );

	return trim( $out );
}

/**
 * ساخت (یا خواندن) فایل ترکیبی فشرده.
 *
 * @param array  $handles دسته‌ها.
 * @param string $type    css یا js.
 * @param string $key     نام کش.
 * @return string|false نشانی فایل.
 */
function zc_build_bundle( $handles, $type, $key ) {
	global $wp_styles, $wp_scripts;

	$reg   = ( 'css' === $type ) ? $wp_styles : $wp_scripts;
	$parts = array();
	$stamp = ZC_VERSION;

	foreach ( $handles as $handle ) {
		if ( empty( $reg->registered[ $handle ] ) ) {
			continue;
		}

		$src = $reg->registered[ $handle ]->src;

		if ( ! $src ) {
			continue;
		}

		// تنها فایل‌های محلی خود قالب ادغام می‌شوند.
		$path = zc_local_path_from_url( $src );

		if ( ! $path || ! file_exists( $path ) ) {
			continue;
		}

		$parts[] = array(
			'handle' => $handle,
			'path'   => $path,
		);

		$stamp .= '|' . $handle . filemtime( $path );
	}

	if ( ! $parts ) {
		return false;
	}

	$name  = $key . '-' . substr( md5( $stamp ), 0, 10 ) . '.' . $type;
	$cache = zc_asset_cache_path( $name );

	if ( file_exists( $cache['dir'] ) ) {
		return $cache['url'];
	}

	$buffer = '';

	foreach ( $parts as $part ) {
		$code = file_get_contents( $part['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( 'css' === $type ) {
			// نشانی‌های نسبی باید نسبت به مکان جدید فایل اصلاح شوند.
			$code    = zc_rebase_css_urls( $code, $part['path'] );
			$buffer .= zc_minify_css( $code ) . "\n";
		} else {
			$buffer .= zc_minify_js( $code ) . "\n;\n";
		}
	}

	// پاکسازی نسخه‌های قدیمی همین دسته.
	foreach ( (array) glob( zc_asset_cache_path()['dir'] . $key . '-*.' . $type ) as $old ) {
		wp_delete_file( $old );
	}

	file_put_contents( $cache['dir'], $buffer ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_put_contents

	return $cache['url'];
}

/**
 * تبدیل نشانی دارایی به مسیر فایل روی دیسک.
 *
 * @param string $src نشانی.
 * @return string|false
 */
function zc_local_path_from_url( $src ) {
	if ( 0 === strpos( $src, '//' ) ) {
		$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
	}

	// فقط دارایی‌های خود قالب.
	if ( false === strpos( $src, ZC_URI ) ) {
		return false;
	}

	$rel = str_replace( ZC_URI, '', strtok( $src, '?' ) );

	return ZC_DIR . ltrim( $rel, '/' );
}

/**
 * اصلاح نشانی‌های نسبی داخل CSS پس از جابه‌جایی فایل.
 *
 * @param string $css  کد.
 * @param string $path مسیر اصلی فایل.
 * @return string
 */
function zc_rebase_css_urls( $css, $path ) {
	$dir = dirname( $path );

	return preg_replace_callback(
		'/url\(\s*[\'"]?(?!data:|https?:|\/\/|#)([^\'")]+)[\'"]?\s*\)/i',
		function ( $m ) use ( $dir ) {
			$rel = trim( $m[1] );

			/*
			 * نشانی نسبی باید واقعاً حل شود، نه اینکه صرفاً ابتدای آن
			 * حذف گردد. مسیر «../fonts/x.woff2» یعنی یک پوشه بالاتر؛
			 * پاک‌کردن ساده‌ی نقطه‌ها آن را به مسیر اشتباه تبدیل
			 * می‌کرد و فونت‌ها ۴۰۴ می‌شدند.
			 */
			$abs   = $dir . '/' . $rel;
			$parts = array();

			foreach ( explode( '/', str_replace( '\\', '/', $abs ) ) as $seg ) {
				if ( '.' === $seg || '' === $seg ) {
					continue;
				}

				if ( '..' === $seg ) {
					array_pop( $parts );
					continue;
				}

				$parts[] = $seg;
			}

			$resolved = '/' . implode( '/', $parts );

			return 'url(' . str_replace( untrailingslashit( ZC_DIR ), untrailingslashit( ZC_URI ), $resolved ) . ')';
		},
		$css
	);
}

/**
 * جایگزینی شیوه‌نامه‌های قالب با یک فایل فشرده.
 *
 * @return void
 */
function zc_bundle_theme_assets() {
	if ( ! zc_assets_optimized() ) {
		return;
	}

	global $wp_styles, $wp_scripts;

	/* ---------- شیوه‌نامه‌ها ---------- */
	$css_handles = array();

	foreach ( (array) $wp_styles->queue as $handle ) {
		if ( 0 === strpos( $handle, 'zc-' ) ) {
			$css_handles[] = $handle;
		}
	}

	if ( count( $css_handles ) > 1 ) {
		$url = zc_build_bundle( $css_handles, 'css', 'zc-bundle' );

		if ( $url ) {
			$inline = array();

			foreach ( $css_handles as $handle ) {
				$extra = $wp_styles->get_data( $handle, 'after' );

				if ( $extra ) {
					$inline = array_merge( $inline, (array) $extra );
				}

				wp_dequeue_style( $handle );
			}

			wp_register_style( 'zc-bundle', $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'zc-bundle' );

			foreach ( $inline as $code ) {
				wp_add_inline_style( 'zc-bundle', $code );
			}
		}
	}

	/* ---------- اسکریپت‌ها ---------- */
	$js_handles = array();

	foreach ( (array) $wp_scripts->queue as $handle ) {
		if ( 0 !== strpos( $handle, 'zc-' ) ) {
			continue;
		}

		// اسکریپتی که وابستگی بیرونی دارد جدا می‌ماند.
		$deps = $wp_scripts->registered[ $handle ]->deps ?? array();

		$outside = array_filter(
			$deps,
			function ( $d ) {
				return 0 !== strpos( $d, 'zc-' );
			}
		);

		if ( $outside ) {
			continue;
		}

		$js_handles[] = $handle;
	}

	if ( count( $js_handles ) > 1 ) {
		$url = zc_build_bundle( $js_handles, 'js', 'zc-bundle' );

		if ( $url ) {
			$before = array();
			$after  = array();

			foreach ( $js_handles as $handle ) {
				$b = $wp_scripts->get_data( $handle, 'before' );
				$a = $wp_scripts->get_data( $handle, 'after' );

				if ( $b ) {
					$before = array_merge( $before, (array) $b );
				}

				if ( $a ) {
					$after = array_merge( $after, (array) $a );
				}

				/*
				 * داده‌ی «wp_localize_script» در extra['data'] ذخیره می‌شود و
				 * در باندلِ مجزا گم می‌شد (مثلاً متغیر ZC در صفحات پنل که
				 * دو اسکریپت zc-main و zc-panel کنار هم می‌آمدند). آن را به
				 * لیست inline های قبل از باندل اضافه می‌کنیم تا حفظ شود.
				 */
				if ( isset( $wp_scripts->registered[ $handle ]->extra['data'] ) && $wp_scripts->registered[ $handle ]->extra['data'] ) {
					$before[] = $wp_scripts->registered[ $handle ]->extra['data'];
				}

				wp_dequeue_script( $handle );
			}

			wp_register_script( 'zc-bundle', $url, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_script( 'zc-bundle' );

			foreach ( $before as $code ) {
				if ( is_string( $code ) && '' !== trim( $code ) ) {
					wp_add_inline_script( 'zc-bundle', $code, 'before' );
				}
			}

			foreach ( $after as $code ) {
				if ( is_string( $code ) && '' !== trim( $code ) ) {
					wp_add_inline_script( 'zc-bundle', $code, 'after' );
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'zc_bundle_theme_assets', 999 );

/**
 * پاک‌کردن کش دارایی‌ها.
 *
 * @return void
 */
function zc_flush_asset_cache() {
	$dir = zc_asset_cache_path()['dir'];

	foreach ( (array) glob( $dir . '*' ) as $file ) {
		wp_delete_file( $file );
	}
}
add_action( 'switch_theme', 'zc_flush_asset_cache' );
add_action( 'zc_options_saved', 'zc_flush_asset_cache' );

/**
 * پاک‌کردن کش دارایی‌ها پس از ذخیره‌ی تنظیمات قالب.
 * Redux و پنل فالبک هر دو گزینه‌ها را در ZC_PREFIX ذخیره می‌کنند؛ هر به‌روزرسانی
 * باعث پاک‌شدن کش می‌شود تا تغییرات (رنگ، فونت، ظاهر) بلافاصله در فرانت اعمال شود.
 */
add_action( 'update_option_' . ZC_PREFIX, 'zc_flush_asset_cache', 10, 3 );
add_action( 'pre_update_option_' . ZC_PREFIX, function ( $value, $old, $option ) {
	zc_flush_asset_cache();
	return $value;
}, 10, 3 );

// سازگاری با ذخیره‌سازی Redux.
if ( defined( 'ZC_PREFIX' ) ) {
	add_action( 'redux/options/' . ZC_PREFIX . '/saved', 'zc_flush_asset_cache' );
	add_action( 'redux/options/' . ZC_PREFIX . '/reset', 'zc_flush_asset_cache' );
}
