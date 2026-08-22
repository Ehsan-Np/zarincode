<?php
/**
 * اسکریپت دانلود/بازیابی فونت‌های فارسی قالب زرین کد
 * ---------------------------------------------------------------------------
 * این اسکریپت همه‌ی فونت‌های پشتیبانی‌شده را از مخازن رسمی (بازنشر فونت)
 * دانلود و در assets/fonts قرار می‌دهد. اگر فایل‌های فونت در قالب حاضر
 * نباشند (مثلاً پس از کپی ناقص)، با این اسکریپت قابل بازیابی است.
 *
 * اجرا از خط فرمان:
 *   php scripts/download-fonts.php
 *
 * @package Zarincode
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "این اسکریپت فقط از خط فرمان قابل اجراست.\n" );
}

$base = dirname( __DIR__ );
$dir  = $base . '/assets/fonts';

if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0755, true );
}

/**
 * دانلود یک فایل.
 *
 * @param string $url  آدرس.
 * @param string $dest مسیر مقصد.
 * @return bool
 */
function zc_dl( $url, $dest ) {
	$ctx  = stream_context_create( array( 'http' => array( 'timeout' => 30, 'follow_location' => 1, 'user_agent' => 'Mozilla/5.0' ) ) );
	$data = @file_get_contents( $url, false, $ctx );
	if ( false === $data || strlen( $data ) < 1000 ) {
		echo "  ✗ FAIL: $url\n";
		return false;
	}
	file_put_contents( $dest, $data );
	echo "  ✓ OK: " . basename( $dest ) . " (" . round( strlen( $data ) / 1024 ) . "KB)\n";
	return true;
}

$map = array(
	// Vazirmatn (variable + weights)
	'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn%5Bwght%5D.woff2' => 'Vazirmatn-Variable.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-Regular.woff2'   => 'Vazirmatn-Regular.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-Medium.woff2'    => 'Vazirmatn-Medium.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-Bold.woff2'      => 'Vazirmatn-Bold.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-ExtraBold.woff2' => 'Vazirmatn-ExtraBold.woff2',

	// Samim
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim.woff2'          => 'Samim.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim.woff'           => 'Samim.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim-Medium.woff2'   => 'Samim-Medium.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim-Medium.woff'    => 'Samim-Medium.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim-Bold.woff2'     => 'Samim-Bold.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/samim-font@latest/dist/Samim-Bold.woff'      => 'Samim-Bold.woff',

	// Shabnam
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam.woff2'        => 'Shabnam.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam.woff'         => 'Shabnam.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Thin.woff2'   => 'Shabnam-Thin.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Thin.woff'    => 'Shabnam-Thin.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Light.woff2'  => 'Shabnam-Light.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Light.woff'   => 'Shabnam-Light.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Medium.woff2' => 'Shabnam-Medium.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Medium.woff'  => 'Shabnam-Medium.woff',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Bold.woff2'   => 'Shabnam-Bold.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/shabnam-font@latest/dist/Shabnam-Bold.woff'    => 'Shabnam-Bold.woff',

	// Gandom
	'https://raw.githubusercontent.com/rastikerdar/gandom-font/master/dist/Gandom.woff2' => 'Gandom.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/gandom-font@latest/dist/Gandom.woff'        => 'Gandom.woff',

	// Tanha
	'https://raw.githubusercontent.com/rastikerdar/tanha-font/master/dist/Tanha.woff2' => 'Tanha.woff2',
	'https://cdn.jsdelivr.net/gh/rastikerdar/tanha-font@latest/dist/Tanha.woff'        => 'Tanha.woff',

	// Yekan (woff + woff2 ساخته شده)
	'https://raw.githubusercontent.com/saeedsajadi/yekan-font/master/fonts/Yekan.woff' => 'Yekan.woff',

	// Arad
	'https://raw.githubusercontent.com/MDarvishi5124/Arad/main/Fonts/main/variable/Arad-VF.woff2'                    => 'Arad-Variable.woff2',
	'https://raw.githubusercontent.com/MDarvishi5124/Arad/main/Fonts/main/static/webfont/Arad-Regular.woff2'          => 'Arad-Regular.woff2',

	// Azad
	'https://raw.githubusercontent.com/font-store/font-VizhehAzad/master/dist/VizhehAzad-Regular.woff2' => 'Azad-Regular.woff2',
	'https://raw.githubusercontent.com/font-store/font-VizhehAzad/master/dist/VizhehAzad-Regular.woff'  => 'Azad-Regular.woff',

	// Ario
	'https://raw.githubusercontent.com/MohamadDarvishi/Ario/main/Fonts/Main_Fonts/Webfonts/Ario-Dots1.woff2' => 'Ario-Regular.woff2',
);

echo "دانلود فونت‌های زرین کد به $dir\n";
echo "--------------------------------------------\n";

foreach ( $map as $url => $file ) {
	$dest = $dir . '/' . $file;
	if ( file_exists( $dest ) && filesize( $dest ) > 1000 ) {
		echo "  • skip (موجود): $file\n";
		continue;
	}
	zc_dl( $url, $dest );
}

// تبدیل یکان به woff2 اگر امکان‌پذیر باشد.
if ( file_exists( $dir . '/Yekan.woff' ) && ! file_exists( $dir . '/Yekan.woff2' ) ) {
	echo "تولید Yekan.woff2 از woff ...\n";
	$src = $dir . '/Yekan.woff';
	$data = file_get_contents( $src );
	// ساده: کپی به جای woff2 (تبدیل کامل نیازمند fonttools است).
	// کاربر می‌تواند با fonttools تبدیل کند. woff در مرورگرهای مدرن پشتیبانی می‌شود.
	file_put_contents( $dir . '/Yekan.woff2', $data );
	echo "  ✓ Yekan.woff2 (کپی موقت)\n";
}

echo "\nتمام شد. تعداد فونت‌های پوشه: " . count( glob( $dir . '/*.woff*' ) ) . "\n";
