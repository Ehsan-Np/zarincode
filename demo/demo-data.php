<?php
/**
 * داده‌های محتوای دمو قالب زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * متن نمونه فارسی.
 *
 * @param int $paragraphs تعداد پاراگراف.
 * @return string
 */
function zc_demo_text( $paragraphs = 3 ) {
	$parts = array(
		'در این دوره آموزشی به صورت کاملاً پروژه‌محور و گام به گام، از مفاهیم پایه شروع می‌کنیم و تا سطح حرفه‌ای پیش می‌رویم. هدف ما این است که پس از اتمام دوره، بتوانید پروژه‌های واقعی بازار کار را به تنهایی پیاده‌سازی کنید.',
		'تمام مباحث با مثال‌های عملی و کاربردی توضیح داده شده‌اند. در هر فصل، یک پروژه کوچک پیاده‌سازی می‌کنیم و در انتهای دوره، یک پروژه بزرگ و کامل را از صفر تا صد با هم می‌سازیم.',
		'کدهای نوشته شده در این دوره کاملاً استاندارد، تمیز و مطابق با اصول برنامه‌نویسی حرفه‌ای هستند. همچنین به بهترین شیوه‌ها (Best Practices) و الگوهای طراحی رایج در صنعت پرداخته می‌شود.',
		'پشتیبانی این دوره به صورت مستقیم توسط مدرس و از طریق سیستم تیکتینگ سایت انجام می‌شود. هر سوالی که در طول یادگیری برایتان پیش بیاید، در کوتاه‌ترین زمان پاسخ داده خواهد شد.',
		'محتوای دوره به صورت مداوم بروزرسانی می‌شود و شما با یک بار خرید، به تمام بروزرسانی‌های آینده به صورت رایگان دسترسی خواهید داشت. دسترسی شما به دوره مادام‌العمر است.',
		'ما معتقدیم یادگیری برنامه‌نویسی باید لذت‌بخش و کاربردی باشد. به همین دلیل تمرکز اصلی این دوره بر روی حل مسئله و ساخت محصول واقعی است، نه صرفاً حفظ کردن دستورات.',
	);

	shuffle( $parts );
	$selected = array_slice( $parts, 0, min( $paragraphs, count( $parts ) ) );

	return "<p>" . implode( "</p>\n\n<p>", $selected ) . "</p>";
}

/**
 * بدنه‌ی کامل و غنی یک دورهٔ نمونه.
 *
 * @return string
 */
function zc_demo_course_content() {
	return '<p>دنیای امروز پر از فرصت است و مهارتِ درست، شما را یک قدم جلوتر می‌اندازد. این دوره با نگاهی واقع‌گرایانه به نیازهای بازار کار طراحی شده است؛ شما فقط یک آموزش ویدیویی نمی‌بینید، بلکه یک مسیر کامل و هدفمند را طی می‌کنید.</p>

<h2>در این دوره چه یاد می‌گیرید؟</h2>
<ul>
<li>مفاهیم پایه و پیشرفته به‌صورت گام‌به‌گام و قابل درک</li>
<li>پیاده‌سازی چند پروژه‌ی واقعی از صفر تا استقرار</li>
<li>آشنایی با بهترین شیوه‌های کدنویسی و معماری مدرن</li>
<li>استفاده از ابزارهای حرفه‌ای و روز دنیا</li>
<li>آمادگی کامل برای ورود به بازار کار و مصاحبه‌های شغلی</li>
</ul>

<h2>این دوره مناسب چه کسانی است؟</h2>
<p>اگر تازه‌کار هستید و می‌خواهید از نقطه‌ی صفر شروع کنید، اگر چند سالی است کدنویسی می‌کنید اما می‌خواهید به سطح حرفه‌ای برسید، یا اگر قصد فریلنسری و مهاجرت شغلی دارید — این مسیر برای شما ساخته شده است. پیش‌نیاز خاصی ندارد و تمام مباحث از پایه توضیح داده می‌شود.</p>

<h2>چرا زرین کد؟</h2>
<p>تیم ما سال‌ها تجربه‌ی تدریس و اجرای پروژه‌های واقعی را کنار هم دارد. همین موضوع باعث شده محتوای دوره‌ها دقیقاً همان چیزی باشد که بازار کار می‌خواهد. پشتیبانی مستقیم مدرس، به‌روزرسانی مادام‌العمر و گواهی پایان دوره، فقط بخشی از مزایای همراهی با ماست.</p>';
}

/**
 * ساخت سرفصل نمونه.
 *
 * @param int $sections تعداد فصل.
 * @return array
 */
function zc_demo_curriculum( $sections = 4 ) {
	$titles = array(
		'شروع مسیر و راه‌اندازی محیط توسعه',
		'مبانی و ساختار اصلی زبان',
		'کار با داده‌ها و پایگاه داده',
		'معماری تمیز و الگوهای طراحی',
		'امنیت، تست و بهینه‌سازی',
		'پروژه‌ی نهایی و استقرار در بازار کار',
	);

	$lessons = array(
		'معرفی دوره، اهداف و نقشه راه',
		'نصب و پیکربندی ابزارهای حرفه‌ای',
		'اولین پروژه‌ی عملی ما',
		'متغیرها، انواع داده و تبدیل‌ها',
		'ساختارهای شرطی و منطق برنامه',
		'حلقه‌ها و تکرار هوشمند',
		'توابع، ماژول‌ها و بازنویسی کد',
		'کار با آرایه‌ها و ساختارهای داده',
		'اتصال امن به پایگاه داده',
		'عملیات CRUD کامل',
		'اعتبارسنجی و تمیزسازی ورودی‌ها',
		'مدیریت خطاها و لاگ‌گیری',
		'احراز هویت و کنترل دسترسی',
		'آپلود و مدیریت فایل‌ها',
		'تست خودکار و دیباگ حرفه‌ای',
		'استقرار، آپدیت و انتشار نهایی',
	);

	$out = array();
	$li  = 0;

	for ( $i = 0; $i < $sections; $i++ ) {
		$count         = wp_rand( 3, 5 );
		$section_lessons = array();

		for ( $j = 0; $j < $count; $j++ ) {
			$section_lessons[] = array(
				'title'    => $lessons[ $li % count( $lessons ) ],
				'duration' => sprintf( '%02d:%02d', wp_rand( 8, 45 ), wp_rand( 10, 59 ) ),
				'video'    => '',
				'free'     => ( 0 === $i && $j < 2 ) ? 1 : 0,
			);
			$li++;
		}

		$out[] = array(
			'title'   => $titles[ $i % count( $titles ) ],
			'lessons' => $section_lessons,
		);
	}

	return $out;
}

/**
 * سوالات آزمون نمونه برای دوره‌های دمو.
 *
 * @return array
 */
function zc_demo_quiz() {
	return array(
		array(
			'type'     => 'mc',
			'question' => 'کدام یک از موارد زیر یک زبان برنامه‌نویسی سمت سرور است؟',
			'options'  => array( 'JavaScript (در مرورگر)', 'PHP', 'CSS', 'HTML' ),
			'answer'   => 1,
		),
		array(
			'type'     => 'mc',
			'question' => 'تابع اصلی برای تعریف یک قالب وردپرس در کدام فایل قرار دارد؟',
			'options'  => array( 'style.css', 'functions.php', 'header.php', 'index.php' ),
			'answer'   => 1,
		),
		array(
			'type'     => 'blank',
			'question' => 'کدام دستور در PHP برای چاپ خروجی استفاده می‌شود؟ (نام دستور را بنویسید)',
			'answers'  => array( 'echo', 'print' ),
			'hint'     => 'یک دستور کوتاه انگلیسی.',
		),
		array(
			'type'     => 'code',
			'question' => 'برنامه‌ای به پایتون بنویسید که عبارت hello را چاپ کند.',
			'language' => 'python',
			'expected' => 'hello',
			'starter'  => "print('')",
			'hint'     => 'از تابع print استفاده کنید.',
		),
		array(
			'type'     => 'mc',
			'question' => 'کدام تگ HTML برای تعریف عنوان اصلی صفحه استفاده می‌شود؟',
			'options'  => array( '<title>', '<h1>', '<head>', '<header>' ),
			'answer'   => 1,
		),
	);
}

/**
 * سوالات تمرین نمونه (تمرین اول: مبانی پایتون).
 *
 * @return array
 */
function zc_demo_practice_python() {
	return array(
		array(
			'type'     => 'mc',
			'question' => 'در پایتون، کدام تابع برای دریافت ورودی از کاربر استفاده می‌شود؟',
			'options'  => array( 'input()', 'print()', 'scan()', 'get()' ),
			'answer'   => 0,
		),
		array(
			'type'     => 'blank',
			'question' => 'در پایتون، برای نوشتن کامنت از کدام نماد استفاده می‌شود؟',
			'answers'  => array( '#', '# ' ),
			'hint'     => 'همان نماد کامنت در فایل‌های تنظیمات.',
		),
		array(
			'type'     => 'code',
			'question' => 'برنامه‌ای به پایتون بنویسید که حاصل جمع ۷ و ۵ را چاپ کند.',
			'language' => 'python',
			'expected' => '12',
			'starter'  => "print( )",
			'hint'     => 'جمع دو عدد را داخل print بنویسید.',
		),
	);
}

/**
 * سوالات تمرین نمونه (تمرین دوم: مبانی PHP).
 *
 * @return array
 */
function zc_demo_practice_php() {
	return array(
		array(
			'type'     => 'mc',
			'question' => 'فایل‌های PHP معمولاً با چه پسوندی ذخیره می‌شوند؟',
			'options'  => array( '.php', '.html', '.py', '.js' ),
			'answer'   => 0,
		),
		array(
			'type'     => 'blank',
			'question' => 'در PHP، متغیرها با کدام نماد شروع می‌شوند؟',
			'answers'  => array( '$', '$ ' ),
			'hint'     => 'مثل $name.',
		),
		array(
			'type'     => 'code',
			'question' => 'برنامه‌ای به PHP بنویسید که عدد ۱۰ را چاپ کند. (خروجی باید فقط 10 باشد)',
			'language' => 'php',
			'expected' => '10',
			'starter'  => "<?php\necho 0;\n",
			'hint'     => 'داخل echo عدد 10 را بنویسید.',
		),
	);
}

/**
 * نصب تمرین‌های نمونه در مرحلهٔ ایمپورت محتوا.
 *
 * @return void
 */
function zc_install_demo_practices() {
	$sets = array(
		array( 'title' => 'مبانی پایتون', 'questions' => 'zc_demo_practice_python', 'langs' => array( 'python' ) ),
		array( 'title' => 'مبانی PHP', 'questions' => 'zc_demo_practice_php', 'langs' => array( 'php' ) ),
	);

	foreach ( $sets as $s ) {
		$existing = zc_get_post_by_title( $s['title'], 'zc_practice' );
		if ( $existing && $existing->post_status === 'publish' ) {
			continue;
		}
		$q = function_exists( $s['questions'] ) ? call_user_func( $s['questions'] ) : array();
		if ( empty( $q ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_practice',
				'post_title'   => $s['title'],
				'post_status'  => 'publish',
				'post_content' => '',
				'meta_input'   => array(
					'_zc_practice'       => $q,
					'_zc_practice_langs' => $s['langs'] ?? array(),
					'_zc_demo'           => '1',
				),
			)
		);
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
	}
}

/**
 * سوالات «تمرین چالشی» دوره (نمونه).
 *
 * @return array
 */
function zc_demo_course_practice() {
	return array(
		array(
			'type'     => 'mc',
			'question' => 'کدام تگ برای بسته‌بندی چند عنصر در HTML استفاده می‌شود؟',
			'options'  => array( '<div>', '<span>', '<p>', '<a>' ),
			'answer'   => 0,
		),
		array(
			'type'     => 'blank',
			'question' => 'در HTML برای تعریف عنوان سطح دوم از کدام تگ استفاده می‌شود؟',
			'answers'  => array( '<h2>', 'h2' ),
			'hint'     => 'بین h و عدد را بنویسید.',
		),
		array(
			'type'     => 'code',
			'question' => 'برنامه‌ای به پایتون بنویسید که خروجی 2 + 2 را چاپ کند.',
			'language' => 'python',
			'expected' => '4',
			'starter'  => "print( )",
			'hint'     => 'حاصل 2 + 2 را داخل print بنویسید.',
		),
	);
}

/**
 * بدنه‌ی کامل و خوانا برای مقاله‌های بلاگ دمو.
 *
 * @return string
 */
function zc_demo_article_content() {
	return '<h2>چرا این موضوع مهم است؟</h2>
<p>در دنیای پرسرعت فناوری، به‌روز ماندن یک مزیت رقابتی واقعی است. این مقاله به زبانی ساده و کاربردی نوشته شده تا هم تازه‌کارها بهره ببرند و هم حرفه‌ای‌ها نکات تازه‌ای یاد بگیرند.</p>

<h2>نکات کلیدی</h2>
<ul>
<li>با داده‌های واقعی و مثال‌های عملی پیش می‌رویم</li>
<li>راه‌حل‌هایی که همین امروز قابل استفاده‌اند</li>
<li>اشاره به ابزارها و منابع معتبر روز</li>
</ul>

<h2>جمع‌بندی</h2>
<p>در پایان این مقاله، تصویری شفاف از موضوع به دست می‌آورید و می‌توانید قدم بعدی را آگاهانه بردارید. اگر سوالی دارید، در بخش دیدگاه‌ها بپرسید؛ تیم زرین کد پاسخگوی شماست.</p>

<blockquote>یادگیری مستمر، کلید موفقیت در هر حرفه‌ای است؛ به‌ویژه در برنامه‌نویسی که هر روز تغییر می‌کند.</blockquote>';
}

/**
 * درون‌ریزی محتوا.
 *
 * @return void
 */
function zc_import_demo_content() {

	/* ---------- دسته‌بندی دوره‌ها ---------- */
	$course_cats = array(
		'برنامه‌نویسی وب'      => 'web-development',
		'برنامه‌نویسی موبایل'  => 'mobile-development',
		'وردپرس'               => 'wordpress',
		'هوش مصنوعی و دیتا'    => 'ai-data',
		'دواپس و سرور'         => 'devops',
		'طراحی رابط کاربری'    => 'ui-ux',
	);

	$cat_ids = array();
	foreach ( $course_cats as $name => $slug ) {
		$term = term_exists( $slug, 'zc_course_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'zc_course_cat', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $slug ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	/* ---------- مدرسان ---------- */
	$teachers = array(
		array( 'احسان نادری پناه', 'استاد مهندسی نرم‌افزار و مدرس ارشد برنامه‌نویسی', 15, 8200, 'احسان نادری پناه استاد رشته‌ی مهندسی نرم‌افزار و از مدرسان ارشد برنامه‌نویسی است. او با بیش از ۱۵ سال سابقه‌ی تدریس و اجرای پروژه‌های واقعی در حوزه‌ی وب، موبایل و علوم داده، روشی پروژه‌محور و روان دارد و دانشجویانش را از صفر تا سطح حرفه‌ای هدایت می‌کند.' ),
	);

	$zc_t_i = 0;

	foreach ( $teachers as $t ) {
		if ( zc_get_post_by_title( $t[0], 'zc_teacher' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_teacher',
				'post_title'   => $t[0],
				'post_content' => $t[4],
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_zc_demo'             => '1',
					'_zc_teacher_role'     => $t[1],
					'_zc_teacher_courses'  => $t[2],
					'_zc_teacher_students' => $t[3],
					'_zc_teacher_telegram' => 'https://t.me/zarincode',
					'_zc_teacher_linkedin' => 'https://linkedin.com/in/zarincode',
					'_zc_teacher_github'   => 'https://github.com/zarincode',
				),
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			zc_demo_attach_image( $id, 'avatars/teacher-' . ( ( $zc_t_i++ % 8 ) + 1 ) . '.jpg' );
		}
	}

	/* ---------- دوره‌های آموزشی تکمیلی (غیرتکراری) ---------- */
	$courses = array(
		array( 'دوره جامع PHP و Laravel؛ از صفر تا ورود به بازار کار', 'web-development', 2890000, 1890000, 'advanced', 'احسان نادری پناه', 3420, 4.9 ),
		array( 'طراحی قالب وردپرس اختصاصی با استانداردهای حرفه‌ای', 'wordpress', 1990000, 1390000, 'intermediate', 'احسان نادری پناه', 4180, 4.9 ),
		array( 'افزونه‌نویسی وردپرس — از مقدمات تا انتشار در مارکت', 'wordpress', 1790000, 0, 'advanced', 'احسان نادری پناه', 2260, 4.7 ),
		array( 'هوش مصنوعی و یادگیری ماشین با پایتون — پروژه‌محور', 'ai-data', 2490000, 1790000, 'intermediate', 'احسان نادری پناه', 3050, 4.8 ),
		array( 'Docker و DevOps — خودکارسازی و استقرار حرفه‌ای', 'devops', 2190000, 1590000, 'advanced', 'احسان نادری پناه', 1180, 4.8 ),
		array( 'طراحی UI/UX با Figma — از ایده تا محصول کاربرپسند', 'ui-ux', 990000, 690000, 'beginner', 'احسان نادری پناه', 2340, 4.7 ),
	);

	$zc_img_i = 0;

	foreach ( $courses as $c ) {
		if ( zc_get_post_by_title( $c[0], 'zc_course' ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_course',
				'post_title'   => $c[0],
				'post_content' => zc_demo_course_content(),
				'post_excerpt' => 'در این دوره پروژه‌محور، تمام مهارت‌های موردنیاز بازار کار را به صورت عملی و گام به گام یاد می‌گیرید و چندین پروژه‌ی واقعی می‌سازید.',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_zc_demo'          => '1',
					'_zc_price'         => $c[2],
					'_zc_sale_price'    => $c[3],
					'_zc_level'         => $c[4],
					'_zc_teacher'       => $c[5],
					'_zc_students'      => $c[6],
					'_zc_rating'        => $c[7],
					'_zc_rating_count'  => wp_rand( 80, 900 ),
					'_zc_course_status' => 'completed',
					'_zc_duration'      => wp_rand( 12, 60 ) . ' ساعت',
					'_zc_access_days'   => 0,
				'_zc_curriculum'    => zc_demo_curriculum( wp_rand( 4, 6 ) ),
				'_zc_quiz'          => zc_demo_quiz(),
				'_zc_course_practice' => zc_demo_course_practice(),
				'_zc_quiz_langs'    => array( 'python', 'php', 'javascript' ),
					'_zc_features'      => array(
						'تسلط کامل بر مفاهیم پایه و پیشرفته',
						'پیاده‌سازی چند پروژه واقعی بازار کار',
						'آشنایی با بهترین شیوه‌های کدنویسی',
						'آمادگی کامل برای مصاحبه شغلی',
						'دریافت گواهی معتبر پایان دوره',
						'پشتیبانی مستقیم مدرس',
						'دسترسی مادام‌العمر و به‌روزرسانی رایگان',
					),
					'_zc_prerequisites' => 'آشنایی مقدماتی با کامپیوتر و اینترنت کافی است. تمام مباحث از پایه توضیح داده می‌شود.',
					'_zc_audience'      => 'علاقه‌مندان به ورود به بازار کار برنامه‌نویسی، دانشجویان کامپیوتر و توسعه‌دهندگانی که می‌خواهند مهارت خود را ارتقا دهند.',
				),
			)
		);

		if ( $id && ! is_wp_error( $id ) && isset( $cat_ids[ $c[1] ] ) ) {
			wp_set_object_terms( $id, (int) $cat_ids[ $c[1] ], 'zc_course_cat' );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			zc_demo_attach_image( $id, zc_demo_image_for( $zc_img_i++ ) );
		}
	}

	/* ---------- دسته آموزش‌ها ---------- */
	$tut_cats = array( 'ترفندهای کدنویسی' => 'coding-tips', 'رفع خطا' => 'debugging', 'ابزارها' => 'tools' );
	$tut_ids  = array();
	foreach ( $tut_cats as $name => $slug ) {
		$term = term_exists( $slug, 'zc_tutorial_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'zc_tutorial_cat', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$tut_ids[] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	/* ---------- ۱۰ آموزش رایگان ---------- */
	$tutorials = array(
		'آموزش نصب و راه‌اندازی Composer در ویندوز و لینوکس',
		'۱۰ ترفند VS Code که بهره‌وری شما را دو برابر می‌کند',
		'رفع خطای CORS در پروژه‌های API به زبان ساده',
		'آموزش کار با Git و GitHub برای مبتدیان',
		'بهینه‌سازی سرعت سایت وردپرسی در ۷ گام عملی',
		'آشنایی با REST API و ساخت اولین اندپوینت',
		'راهنمای کامل استفاده از Docker Compose',
		'امنیت در PHP؛ جلوگیری از SQL Injection',
		'آموزش CSS Grid و Flexbox با مثال عملی',
		'دیباگ حرفه‌ای جاوااسکریپت با Chrome DevTools',
	);

	foreach ( $tutorials as $i => $title ) {
		if ( zc_get_post_by_title( $title, 'zc_tutorial' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_tutorial',
				'post_title'   => $title,
				'post_content' => '<h2>شروع کار</h2>' . zc_demo_text( 2 ) . "\n<pre><code>// نمونه کد\nfunction zarincode() {\n    return 'Hello Developer!';\n}</code></pre>\n<h2>مراحل گام‌به‌گام</h2>" . zc_demo_text( 3 ) . "\n<h2>نتیجه</h2>" . zc_demo_text( 1 ),
				'post_excerpt' => 'در این آموزش رایگان، به صورت گام به گام و با مثال عملی این موضوع را یاد می‌گیرید.',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_zc_demo'  => '1',
					'_zc_level' => array( 'beginner', 'intermediate', 'advanced' )[ $i % 3 ],
					'zc_views'  => wp_rand( 300, 9000 ),
				),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			zc_demo_attach_image( $id, zc_demo_image_for( $i + 3 ) );

			if ( $tut_ids ) {
				wp_set_object_terms( $id, (int) $tut_ids[ $i % count( $tut_ids ) ], 'zc_tutorial_cat' );
			}
		}
	}

	/* ---------- ۱۰ مقاله بلاگ ---------- */
	$blog_cat = term_exists( 'اخبار برنامه‌نویسی', 'category' );
	if ( ! $blog_cat ) {
		$blog_cat = wp_insert_term( 'اخبار برنامه‌نویسی', 'category' );
	}
	$blog_cat_id = is_array( $blog_cat ) ? $blog_cat['term_id'] : $blog_cat;

	$posts = array(
		'۱۰ زبان برنامه‌نویسی پردرآمد در سال ۲۰۲۶؛ کدام را یاد بگیریم؟',
		'چگونه یک رزومه و پورتفولیو حرفه‌ای برای برنامه‌نویسان بنویسیم؟',
		'Laravel یا Symfony؟ مقایسه‌ی کامل برای انتخاب درست',
		'آینده‌ی هوش مصنوعی در توسعه‌ی نرم‌افزار؛ فرصت‌ها و چالش‌ها',
		'راهنمای کامل شروع فریلنسری برای برنامه‌نویسان ایرانی',
		'چرا وردپرس همچنان محبوب‌ترین سیستم مدیریت محتوای جهان است؟',
		'معرفی ۱۵ ابزار ضروری برای هر توسعه‌دهنده‌ی وب',
		'مسیر تبدیل شدن به یک توسعه‌دهنده‌ی Full-Stack در ۶ ماه',
		'اشتباهات رایج برنامه‌نویسان تازه‌کار و راه‌حل آن‌ها',
		'درآمد واقعی برنامه‌نویسی در ایران؛ آمار و تجربه‌ها',
	);

	$zc_post_i = 0;

	foreach ( $posts as $title ) {
		if ( zc_get_post_by_title( $title, 'post' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_title'   => $title,
				'post_content' => zc_demo_article_content(),
				'post_excerpt' => 'در این مقاله به بررسی دقیق و کاربردی این موضوع می‌پردازیم و نکات مهمی را با شما به اشتراک می‌گذاریم.',
				'post_status'  => 'publish',
				'meta_input'   => array( '_zc_demo' => '1', 'zc_views' => wp_rand( 500, 15000 ) ),
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			zc_demo_attach_image( $id, zc_demo_image_for( $zc_post_i++ + 9 ) );
		}
		if ( $id && ! is_wp_error( $id ) && $blog_cat_id ) {
			wp_set_object_terms( $id, (int) $blog_cat_id, 'category' );
		}
	}

	/* ---------- محصولات ووکامرس ---------- */
	if ( zc_is_woo() ) {
		zc_import_demo_products();
	}

	/* ---------- نظرات مشتریان ---------- */
	$testimonials = array(
		array( 'محمد مرادی', 'استخدام به عنوان توسعه‌دهنده لاراول در یک شرکت بزرگ' ),
		array( 'سارا رضایی', 'شروع فریلنسری با درآمد دلاری بعد از ۶ ماه' ),
		array( 'نیما حسینی', 'راه‌اندازی فروشگاه اینترنتی شخصی' ),
		array( 'فاطمه کاظمی', 'ارتقای شغلی و افزایش ۳ برابری حقوق' ),
		array( 'حسین نجفی', 'ورود به بازار کار بدون مدرک دانشگاهی' ),
		array( 'زهرا موسوی', 'ساخت اولین اپلیکیشن موبایل و انتشار در کافه‌بازار' ),
	);

	foreach ( $testimonials as $t ) {
		if ( zc_get_post_by_title( $t[0], 'zc_testimonial' ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'zc_testimonial',
				'post_title'   => $t[0],
				'post_content' => 'قبل از شرکت در دوره‌های زرین کد، هیچ دیدی نسبت به بازار کار نداشتم. کیفیت آموزش‌ها و مهم‌تر از آن، پشتیبانی عالی مدرسان باعث شد در کوتاه‌ترین زمان به هدفم برسم. واقعاً از انتخابم راضی هستم.',
				'post_status'  => 'publish',
				'meta_input'   => array( '_zc_demo' => '1', '_zc_role' => $t[1] ),
			)
		);
	}

	/* ---------- سوالات متداول ---------- */
	$faqs = array(
		array( 'آیا دوره‌ها دارای پشتیبانی هستند؟', 'بله، تمام دوره‌های زرین کد دارای پشتیبانی مستقیم مدرس از طریق سیستم تیکتینگ سایت هستند و تا پایان دوره در کنار شما خواهیم بود.' ),
		array( 'مدت دسترسی به دوره‌ها چقدر است؟', 'دسترسی شما به دوره‌های خریداری‌شده مادام‌العمر است و تمام بروزرسانی‌های آینده نیز رایگان در اختیار شما قرار می‌گیرد.' ),
		array( 'آیا امکان بازگشت وجه وجود دارد؟', 'بله، تا ۷ روز پس از خرید در صورت عدم رضایت، مبلغ پرداختی به کیف پول شما بازگردانده می‌شود.' ),
		array( 'گواهی پایان دوره چگونه صادر می‌شود؟', 'پس از مشاهده ۱۰۰٪ جلسات دوره، گواهی پایان دوره با کد رهگیری منحصربه‌فرد در پنل کاربری شما صادر می‌شود.' ),
		array( 'آیا پیش‌نیاز خاصی برای شروع لازم است؟', 'برای دوره‌های مقدماتی هیچ پیش‌نیازی لازم نیست. برای دوره‌های پیشرفته، پیش‌نیازها در صفحه هر دوره ذکر شده است.' ),
		array( 'روش‌های پرداخت چیست؟', 'پرداخت آنلاین از طریق درگاه امن زرین‌پال با تمام کارت‌های عضو شتاب و همچنین استفاده از موجودی کیف پول امکان‌پذیر است.' ),
		array( 'آیا محصولات فروشگاه بروزرسانی می‌شوند؟', 'بله، تمام قالب‌ها و افزونه‌های فروشگاه دارای بروزرسانی رایگان مادام‌العمر و پشتیبانی فنی هستند.' ),
		array( 'چطور می‌توانم با پشتیبانی در ارتباط باشم؟', 'از طریق سیستم تیکتینگ در پنل کاربری، چت آنلاین سایت یا شماره تماس درج شده در صفحه تماس با ما.' ),
	);

	foreach ( $faqs as $faq ) {
		if ( zc_get_post_by_title( $faq[0], 'zc_faq' ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'zc_faq',
				'post_title'   => $faq[0],
				'post_content' => $faq[1],
				'post_status'  => 'publish',
				'meta_input'   => array( '_zc_demo' => '1' ),
			)
		);
	}

	/* ---------- خدمات (برای رزرو نوبت) ---------- */
	/*
	 * خدمات آژانس: زرین کد علاوه بر آموزش، پروژه هم می‌پذیرد.
	 * ساختار هر ردیف:
	 * [ عنوان، دسته، آیکن، شروع قیمت، مدت، رنگ، توضیح، امکانات[]، بسته‌ها[] ]
	 */
	$service_cats = array(
		'طراحی و توسعه وب' => 'web-development',
		'خدمات وردپرس'     => 'wordpress',
		'طراحی گرافیک'     => 'graphic-design',
		'برنامه‌نویسی اختصاصی' => 'programming',
		'سئو و دیجیتال مارکتینگ' => 'seo-marketing',
		'آموزش و مشاوره'   => 'consulting',
		'پشتیبانی و نگهداری' => 'support',
	);

	$svc_terms = array();

	foreach ( $service_cats as $name => $slug ) {
		$term = term_exists( $slug, 'zc_service_cat' );

		if ( ! $term ) {
			$term = wp_insert_term( $name, 'zc_service_cat', array( 'slug' => $slug ) );
		}

		if ( ! is_wp_error( $term ) ) {
			$svc_terms[ $slug ] = (int) ( $term['term_id'] ?? 0 );
		}
	}

	$services = array(
		array(
			'طراحی و توسعه سایت وردپرسی',
			'web-development',
			'code',
			15000000,
			'۳ تا ۶ هفته',
			'#0B2187',
			'طراحی سایت اختصاصی با وردپرس؛ از فروشگاه اینترنتی و سایت شرکتی تا سامانه‌های آموزشی. کدنویسی تمیز، سرعت بالا و کاملاً سازگار با موبایل.',
			array( 'طراحی اختصاصی و منحصربه‌فرد', 'کاملاً واکنش‌گرا (موبایل و تبلت)', 'سرعت بارگذاری زیر ۲ ثانیه', 'پنل مدیریت فارسی و ساده', 'آموزش کار با سایت', 'سه ماه پشتیبانی رایگان' ),
			array(
				array( 'title' => 'پایه', 'price' => 15000000, 'delivery' => '۳ هفته', 'popular' => 0, 'features' => array( 'تا ۷ صفحه', 'طراحی واکنش‌گرا', 'فرم تماس', 'سئوی پایه' ) ),
				array( 'title' => 'حرفه‌ای', 'price' => 32000000, 'delivery' => '۴ هفته', 'popular' => 1, 'features' => array( 'صفحات نامحدود', 'فروشگاه ووکامرس', 'درگاه پرداخت', 'سئوی پیشرفته', 'پنل کاربری' ) ),
				array( 'title' => 'سازمانی', 'price' => 68000000, 'delivery' => '۶ هفته', 'popular' => 0, 'features' => array( 'همه امکانات حرفه‌ای', 'افزونه اختصاصی', 'اتصال به API', 'یک سال پشتیبانی' ) ),
			),
		),
		array(
			'سئو و بهینه‌سازی محتوا',
			'seo-marketing',
			'chart',
			8000000,
			'۳ تا ۶ ماه',
			'#16A34A',
			'بهینه‌سازی فنی و محتوایی سایت برای رسیدن به صفحه اول گوگل. تحلیل کلمات کلیدی، بهبود سرعت، ساختار لینک‌سازی و تولید محتوای هدفمند.',
			array( 'ممیزی کامل فنی سایت', 'تحقیق کلمات کلیدی', 'بهینه‌سازی سرعت و Core Web Vitals', 'تولید محتوای سئوشده', 'گزارش ماهانه رتبه', 'لینک‌سازی اصولی' ),
			array(
				array( 'title' => 'ممیزی سئو', 'price' => 8000000, 'delivery' => '۱ هفته', 'popular' => 0, 'features' => array( 'گزارش کامل مشکلات', 'اولویت‌بندی اقدامات', 'جلسه توضیح' ) ),
				array( 'title' => 'سئو ماهانه', 'price' => 14000000, 'delivery' => 'ماهانه', 'popular' => 1, 'features' => array( 'بهینه‌سازی مستمر', '۸ محتوای سئوشده', 'گزارش ماهانه', 'پشتیبانی تلفنی' ) ),
			),
		),
		array(
			'انجام پروژه‌های برنامه‌نویسی',
			'web-development',
			'plugin',
			10000000,
			'بسته به پروژه',
			'#7C3AED',
			'انجام پروژه‌های دانشجویی و تجاری با پایتون، PHP، لاراول، ری‌اکت و وردپرس. از اسکریپت‌های کوچک تا سامانه‌های تحت وب.',
			array( 'تحلیل و مستندسازی', 'کد تمیز و استاندارد', 'تحویل مرحله‌ای', 'آموزش کار با پروژه', 'گارانتی رفع باگ' ),
			array(),
		),
		array(
			'طراحی افزونه و قالب اختصاصی',
			'web-development',
			'gift',
			12000000,
			'۲ تا ۵ هفته',
			'#C9A227',
			'ساخت افزونه یا قالب وردپرس دقیقاً مطابق نیاز کسب‌وکار شما؛ سازگار با آخرین نسخه وردپرس و مطابق استانداردهای رسمی.',
			array( 'کدنویسی بر پایه استاندارد وردپرس', 'سازگار با المنتور و ووکامرس', 'مستندات کامل', 'بروزرسانی رایگان یک‌ساله' ),
			array(),
		),
		array(
			'مشاوره فنی و انتخاب مسیر شغلی',
			'consulting',
			'headphone',
			500000,
			'۶۰ دقیقه',
			'#0891B2',
			'جلسه اختصاصی با متخصصان زرین کد برای انتخاب مسیر یادگیری، بررسی رزومه، آماده‌سازی مصاحبه یا حل چالش فنی پروژه.',
			array( 'جلسه ۶۰ دقیقه‌ای آنلاین', 'بررسی رزومه و گیت‌هاب', 'نقشه راه شخصی‌سازی‌شده', 'پشتیبانی متنی پس از جلسه' ),
			array(),
		),
		array(
			'پشتیبانی و نگهداری سایت',
			'support',
			'shield',
			3000000,
			'ماهانه',
			'#DC2626',
			'نگهداری ماهانه سایت شامل بروزرسانی، پشتیبان‌گیری، رفع مشکلات امنیتی و پایش سرعت؛ تا شما فقط روی کسب‌وکارتان تمرکز کنید.',
			array( 'بروزرسانی هسته و افزونه‌ها', 'پشتیبان‌گیری خودکار روزانه', 'پایش امنیتی و ضدهک', 'رفع اشکال فوری', 'گزارش ماهانه سلامت سایت' ),
			array(
				array( 'title' => 'پایه', 'price' => 3000000, 'delivery' => 'ماهانه', 'popular' => 0, 'features' => array( 'بروزرسانی ماهانه', 'پشتیبان‌گیری هفتگی', 'پشتیبانی ایمیلی' ) ),
				array( 'title' => 'ویژه', 'price' => 6500000, 'delivery' => 'ماهانه', 'popular' => 1, 'features' => array( 'پایش ۲۴ ساعته', 'پشتیبان‌گیری روزانه', 'پشتیبانی تلفنی', 'رفع باگ نامحدود' ) ),
			),
		),

		/* ---------- خدمات گرافیک ---------- */
		array(
			'طراحی رابط و تجربه کاربری (UI/UX)',
			'graphic-design',
			'edit',
			18000000,
			'۲ تا ۴ هفته',
			'#DB2777',
			'طراحی رابط کاربری سایت و اپلیکیشن در فیگما؛ از تحقیق کاربر و وایرفریم تا دیزاین سیستم کامل و فایل آماده تحویل به تیم توسعه.',
			array( 'تحقیق کاربر و رقبا', 'وایرفریم و نمونه اولیه تعاملی', 'دیزاین سیستم و کتابخانه کامپوننت', 'طراحی واکنش‌گرا برای موبایل', 'تحویل فایل فیگما با لایه‌بندی تمیز' ),
			array(
				array( 'title' => 'لندینگ', 'price' => 8000000, 'delivery' => '۱ هفته', 'popular' => 0, 'features' => array( 'یک صفحه فرود', 'دو نوبت بازبینی', 'نسخه موبایل' ) ),
				array( 'title' => 'سایت کامل', 'price' => 18000000, 'delivery' => '۳ هفته', 'popular' => 1, 'features' => array( 'تا ۱۲ صفحه', 'دیزاین سیستم', 'نمونه اولیه تعاملی', 'بازبینی نامحدود' ) ),
			),
		),
		array(
			'طراحی هویت بصری و لوگو',
			'graphic-design',
			'sparkle',
			9000000,
			'۱ تا ۳ هفته',
			'#9333EA',
			'ساخت هویت بصری منسجم برای برند شما: لوگو، پالت رنگ، تایپوگرافی و اقلام تبلیغاتی، همراه با کتابچه راهنمای استفاده از برند.',
			array( 'سه طرح اولیه لوگو', 'فایل وکتور و تمام فرمت‌ها', 'پالت رنگ و تایپوگرافی', 'کتابچه هویت برند', 'ست اداری و کارت ویزیت' ),
			array(),
		),
		array(
			'طراحی بنر، پست و محتوای شبکه‌های اجتماعی',
			'graphic-design',
			'image',
			4500000,
			'۳ تا ۷ روز',
			'#F59E0B',
			'تولید بسته‌ی گرافیکی ماهانه برای اینستاگرام، تلگرام و تبلیغات کلیکی؛ قالب‌های آماده و قابل ویرایش برای انتشار مستمر.',
			array( 'طراحی مطابق هویت برند', 'قالب قابل ویرایش', 'اندازه‌های استاندارد هر شبکه', 'تحویل سریع', 'اصلاح رایگان' ),
			array(),
		),

		/* ---------- خدمات وردپرس ---------- */
		array(
			'طراحی افزونه اختصاصی وردپرس',
			'wordpress',
			'plugin',
			14000000,
			'۲ تا ۶ هفته',
			'#2563EB',
			'ساخت افزونه‌ی سفارشی برای نیاز دقیق کسب‌وکار شما؛ منطبق بر استانداردهای رسمی وردپرس، امن، بهینه و قابل توسعه توسط هر تیم دیگری.',
			array( 'کدنویسی مطابق WordPress Coding Standards', 'سازگار با ووکامرس و المنتور', 'پنل تنظیمات فارسی', 'مستندات فنی کامل', 'یک سال بروزرسانی رایگان' ),
			array(),
		),
		array(
			'مهاجرت، بهینه‌سازی و افزایش سرعت وردپرس',
			'wordpress',
			'refresh',
			6000000,
			'۳ تا ۱۰ روز',
			'#0891B2',
			'انتقال امن سایت به هاست جدید و بهینه‌سازی کامل سرعت؛ کاهش زمان بارگذاری، بهبود Core Web Vitals و افزایش امتیاز گوگل پیج‌اسپید.',
			array( 'مهاجرت بدون قطعی سایت', 'بهینه‌سازی تصاویر و کش', 'کاهش درخواست‌های اضافی', 'گزارش قبل و بعد', 'تضمین افزایش امتیاز' ),
			array(),
		),
		array(
			'آموزش خصوصی و سازمانی وردپرس',
			'wordpress',
			'users',
			3500000,
			'۴ تا ۱۲ جلسه',
			'#7C3AED',
			'دوره‌ی حضوری یا آنلاین برای تیم شما؛ از مدیریت محتوا و ووکامرس تا افزونه‌نویسی و امنیت، با سرفصل اختصاصی متناسب با نیاز سازمان.',
			array( 'سرفصل اختصاصی سازمان شما', 'جلسات آنلاین یا حضوری', 'تمرین عملی روی سایت واقعی', 'ضبط جلسات', 'گواهی پایان دوره' ),
			array(),
		),

		/* ---------- برنامه‌نویسی اختصاصی ---------- */
		array(
			'طراحی و توسعه اپلیکیشن موبایل',
			'programming',
			'phone',
			45000000,
			'۶ تا ۱۲ هفته',
			'#059669',
			'ساخت اپلیکیشن اندروید و iOS با فلاتر یا ری‌اکت نیتیو؛ از تحلیل و طراحی رابط تا انتشار در کافه‌بازار، مایکت و گوگل‌پلی.',
			array( 'کد یکپارچه برای اندروید و iOS', 'اتصال به سرویس سمت سرور', 'پنل مدیریت اختصاصی', 'انتشار در بازارها', 'شش ماه پشتیبانی' ),
			array(),
		),
		array(
			'طراحی و پیاده‌سازی API و سرویس سمت سرور',
			'programming',
			'settings',
			22000000,
			'۳ تا ۸ هفته',
			'#475569',
			'ساخت سرویس واسط برنامه‌نویسی امن و مقیاس‌پذیر با لاراول یا Node.js؛ همراه با مستندسازی، احراز هویت و آزمون‌های خودکار.',
			array( 'معماری تمیز و لایه‌ای', 'مستندسازی با Swagger', 'احراز هویت و کنترل دسترسی', 'آزمون خودکار', 'استقرار روی سرور' ),
			array(),
		),
	);

	$svc_i = 0;

	foreach ( $services as $srv ) {
		if ( zc_get_post_by_title( $srv[0], 'zc_service' ) ) {
			continue;
		}

		$sid = wp_insert_post(
			array(
				'post_type'    => 'zc_service',
				'post_title'   => $srv[0],
				'post_content' => $srv[6] . "\n\n" . zc_demo_text( 3 ),
				'post_excerpt' => $srv[6],
				'post_status'  => 'publish',
				'menu_order'   => $svc_i,
				'meta_input'   => array(
					'_zc_demo'                => '1',
					'_zc_service_icon'        => $srv[2],
					'_zc_service_price_from'  => $srv[3],
					'_zc_service_duration'    => $srv[4],
					'_zc_service_color'       => $srv[5],
					'_zc_features'            => $srv[7],
					'_zc_packages'            => $srv[8],
				),
			)
		);

		if ( $sid && ! is_wp_error( $sid ) ) {
			zc_demo_attach_image( $sid, zc_demo_image_for( $svc_i + 11 ) );

			if ( ! empty( $svc_terms[ $srv[1] ] ) ) {
				wp_set_object_terms( $sid, $svc_terms[ $srv[1] ], 'zc_service_cat' );
			}
		}

		$svc_i++;
	}

	/* ---------- نمونه‌کارها ---------- */
	$project_cats = array(
		'فروشگاه اینترنتی' => 'ecommerce',
		'سایت شرکتی'      => 'corporate',
		'سامانه آموزشی'   => 'lms',
		'اپلیکیشن وب'     => 'webapp',
	);

	$prj_terms = array();

	foreach ( $project_cats as $name => $slug ) {
		$term = term_exists( $slug, 'zc_project_cat' );

		if ( ! $term ) {
			$term = wp_insert_term( $name, 'zc_project_cat', array( 'slug' => $slug ) );
		}

		if ( ! is_wp_error( $term ) ) {
			$prj_terms[ $slug ] = (int) ( $term['term_id'] ?? 0 );
		}
	}

	$projects = array(
		array( 'فروشگاه اینترنتی دیجی‌استایل', 'ecommerce', 'گروه پوشاک استایل', array( 'WordPress', 'WooCommerce', 'PHP' ), array( 'افزایش ۲۴۰٪ فروش آنلاین', 'کاهش نرخ پرش به ۳۲٪', 'سرعت بارگذاری ۱.۴ ثانیه' ) ),
		array( 'سامانه آموزش آنلاین مهرگان', 'lms', 'موسسه مهرگان', array( 'WordPress', 'LMS', 'JavaScript' ), array( 'بیش از ۵۰۰۰ دانشجوی فعال', 'پخش ویدیو اختصاصی', 'صدور خودکار گواهینامه' ) ),
		array( 'وب‌سایت شرکت مهندسی آریا', 'corporate', 'شرکت آریا صنعت', array( 'WordPress', 'Elementor', 'SEO' ), array( 'رتبه یک گوگل در ۱۲ کلمه کلیدی', 'افزایش ۱۸۰٪ ترافیک ارگانیک' ) ),
		array( 'اپلیکیشن مدیریت پروژه تسکو', 'webapp', 'استارتاپ تسکو', array( 'React', 'Laravel', 'MySQL' ), array( 'پشتیبانی از ۱۰ هزار کاربر همزمان', 'API اختصاصی', 'داشبورد تحلیلی' ) ),
		array( 'فروشگاه لوازم دیجیتال تک‌شاپ', 'ecommerce', 'تک‌شاپ', array( 'WooCommerce', 'PHP', 'Redis' ), array( 'پردازش ۸۰۰ سفارش روزانه', 'اتصال به انبارداری' ) ),
		array( 'پرتال خبری زوم‌تک', 'corporate', 'رسانه زوم‌تک', array( 'WordPress', 'AMP', 'SEO' ), array( 'ماهانه ۱.۲ میلیون بازدید', 'نمره ۹۸ در PageSpeed' ) ),
	);

	$prj_i = 0;

	foreach ( $projects as $prj ) {
		if ( zc_get_post_by_title( $prj[0], 'zc_project' ) ) {
			continue;
		}

		$pid = wp_insert_post(
			array(
				'post_type'    => 'zc_project',
				'post_title'   => $prj[0],
				'post_content' => zc_demo_text( 4 ),
				'post_excerpt' => 'پروژه‌ای که با همکاری تیم زرین کد از ایده تا اجرا پیش رفت و با موفقیت تحویل داده شد.',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_zc_demo'             => '1',
					'_zc_project_client'   => $prj[2],
					'_zc_project_url'      => 'https://example.com',
					'_zc_project_date'     => 'سال ۱۴۰۴',
					'_zc_project_duration' => wp_rand( 2, 12 ) . ' هفته',
					'_zc_project_results'  => $prj[4],
				),
			)
		);

		if ( $pid && ! is_wp_error( $pid ) ) {
			zc_demo_attach_image( $pid, zc_demo_image_for( $prj_i + 17 ) );

			if ( ! empty( $prj_terms[ $prj[1] ] ) ) {
				wp_set_object_terms( $pid, $prj_terms[ $prj[1] ], 'zc_project_cat' );
			}

			wp_set_object_terms( $pid, $prj[3], 'zc_project_tech' );
		}

		$prj_i++;
	}
}

/**
 * درون‌ریزی محصولات ووکامرس.
 *
 * @return void
 */
function zc_import_demo_products() {

	$product_cats = array(
		'قالب وردپرس' => 'wordpress-themes',
		'افزونه وردپرس' => 'wordpress-plugins',
		'سورس کد آماده' => 'source-code',
		'فونت فارسی'  => 'persian-fonts',
		'اسکریپت و ابزار' => 'scripts',
	);

	$pcat_ids = array();
	foreach ( $product_cats as $name => $slug ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$pcat_ids[ $slug ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	$groups = array(
		'wordpress-themes' => array(
			array( 'قالب فروشگاهی زرین شاپ', 890000, 590000 ),
			array( 'قالب شرکتی زرین بیزینس', 790000, 0 ),
			array( 'قالب آموزشی زرین آکادمی', 990000, 690000 ),
			array( 'قالب وبلاگی زرین بلاگ', 490000, 0 ),
			array( 'قالب رزومه زرین پروفایل', 390000, 290000 ),
			array( 'قالب املاک زرین هوم', 890000, 0 ),
			array( 'قالب پزشکی زرین کلینیک', 790000, 590000 ),
			array( 'قالب رستوران زرین فود', 690000, 0 ),
			array( 'قالب خبری زرین نیوز', 590000, 390000 ),
			array( 'قالب چندمنظوره زرین مگا', 1290000, 890000 ),
		),
		'wordpress-plugins' => array(
			array( 'افزونه درگاه پرداخت زرین‌پال حرفه‌ای', 390000, 290000 ),
			array( 'افزونه پیامک کاوه‌نگار پیشرفته', 490000, 0 ),
			array( 'افزونه کیف پول ووکامرس', 590000, 390000 ),
			array( 'افزونه تیکتینگ حرفه‌ای', 690000, 0 ),
			array( 'افزونه رزرو نوبت آنلاین', 790000, 590000 ),
			array( 'افزونه بهینه‌ساز سرعت زرین اسپید', 490000, 0 ),
			array( 'افزونه فروش فایل و محصول دانلودی', 590000, 390000 ),
			array( 'افزونه اتصال به تلگرام و بله', 390000, 0 ),
			array( 'افزونه سیستم امتیازدهی و باشگاه مشتریان', 690000, 490000 ),
			array( 'افزونه امنیت و فایروال زرین گارد', 890000, 0 ),
		),
		'persian-fonts' => array(
			array( 'مجموعه فونت زرین سنس (۸ وزن)', 290000, 190000 ),
			array( 'فونت زرین نستعلیق', 190000, 0 ),
			array( 'فونت زرین تیتر', 150000, 99000 ),
			array( 'پک کامل فونت‌های فارسی وب', 490000, 349000 ),
			array( 'فونت زرین مدرن', 190000, 0 ),
			array( 'فونت زرین کد (مونواسپیس فارسی)', 250000, 190000 ),
			array( 'فونت دست‌نویس زرین', 150000, 0 ),
			array( 'فونت زرین بولد نمایشی', 180000, 129000 ),
			array( 'مجموعه آیکون‌فونت فارسی', 220000, 0 ),
			array( 'پک فونت اینفوگرافیک', 350000, 250000 ),
		),
		'scripts' => array(
			array( 'اسکریپت کوتاه‌کننده لینک', 490000, 0 ),
			array( 'اسکریپت سیستم فاکتور و حسابداری', 890000, 690000 ),
			array( 'ابزار تولید Sitemap پیشرفته', 290000, 0 ),
			array( 'اسکریپت چت آنلاین PHP', 590000, 390000 ),
			array( 'API آماده احراز هویت با موبایل', 390000, 0 ),
			array( 'قالب ادمین پنل React', 990000, 790000 ),
			array( 'کامپوننت‌های آماده Tailwind فارسی', 490000, 0 ),
			array( 'اسکریپت سیستم نظرسنجی', 290000, 190000 ),
			array( 'داشبورد آنالیتیکس اختصاصی', 790000, 0 ),
			array( 'پکیج آماده Laravel Starter Kit', 690000, 490000 ),
		),
		'source-code' => array(
			array( 'سورس کامل فروشگاه اینترنتی با لاراول', 2490000, 1790000 ),
			array( 'سورس اپلیکیشن فروشگاهی فلاتر', 1990000, 1490000 ),
			array( 'سورس سامانه آموزش آنلاین (LMS) با PHP', 2290000, 0 ),
			array( 'سورس پنل مدیریت ری‌اکت و Next.js', 1690000, 1190000 ),
			array( 'سورس سیستم رزرو نوبت آنلاین', 1290000, 0 ),
			array( 'سورس سامانه تیکتینگ و پشتیبانی', 990000, 690000 ),
			array( 'سورس ربات تلگرام فروشگاهی پایتون', 890000, 0 ),
			array( 'سورس سیستم حسابداری و انبارداری', 2790000, 1990000 ),
			array( 'سورس اپلیکیشن پیام‌رسان با Node.js', 1890000, 0 ),
			array( 'سورس سامانه املاک و مشاور املاک', 1590000, 1090000 ),
		),
	);

	$zc_prod_i = 0;

	foreach ( $groups as $slug => $items ) {
		foreach ( $items as $item ) {
			if ( zc_get_post_by_title( $item[0], 'product' ) ) {
				continue;
			}

			$product_id = wp_insert_post(
				array(
					'post_type'    => 'product',
					'post_title'   => $item[0],
					'post_content' => zc_demo_text( 3 ),
					'post_excerpt' => 'محصولی حرفه‌ای با کدنویسی استاندارد، پشتیبانی کامل و بروزرسانی رایگان مادام‌العمر.',
					'post_status'  => 'publish',
					'meta_input'   => array( '_zc_demo' => '1' ),
				)
			);

			if ( $product_id && ! is_wp_error( $product_id ) ) {
				zc_demo_attach_image( $product_id, zc_demo_image_for( $zc_prod_i++ ) );
			}

			if ( ! $product_id || is_wp_error( $product_id ) ) {
				continue;
			}

			$price = $item[2] > 0 ? $item[2] : $item[1];

			update_post_meta( $product_id, '_regular_price', $item[1] );
			if ( $item[2] > 0 ) {
				update_post_meta( $product_id, '_sale_price', $item[2] );
			}
			update_post_meta( $product_id, '_price', $price );
			update_post_meta( $product_id, '_virtual', 'yes' );
			update_post_meta( $product_id, '_downloadable', 'yes' );
			update_post_meta( $product_id, '_manage_stock', 'no' );
			update_post_meta( $product_id, '_stock_status', 'instock' );
			update_post_meta( $product_id, 'total_sales', wp_rand( 20, 800 ) );
			update_post_meta( $product_id, '_wc_average_rating', wp_rand( 40, 50 ) / 10 );
			update_post_meta( $product_id, '_wc_review_count', wp_rand( 5, 90 ) );
			update_post_meta(
				$product_id,
				'_zc_specs',
				array(
					array( 'key' => 'نسخه', 'value' => zc_fa_num( '1.' . wp_rand( 0, 9 ) . '.' . wp_rand( 0, 9 ) ) ),
					array( 'key' => 'سازگاری', 'value' => 'وردپرس ۶.۰ به بالا' ),
					array( 'key' => 'پشتیبانی', 'value' => '۱۲ ماه رایگان' ),
					array( 'key' => 'بروزرسانی', 'value' => 'مادام‌العمر' ),
				)
			);
			update_post_meta( $product_id, '_zc_preview_url', 'https://zarincode.com/' . $product_id );
			update_post_meta( $product_id, '_zc_product_author', 'تیم زرین کد' );
			update_post_meta( $product_id, '_zc_last_update', zc_fa_num( wp_rand( 1, 29 ) ) . ' مرداد ۱۴۰۵' );
			update_post_meta( $product_id, '_zc_install_guide', 'پس از خرید، فایل را از پنل کاربری دانلود کرده و از مسیر پیشخوان وردپرس » افزونه‌ها » افزودن » بارگذاری، نصب و فعال کنید.' );

			wp_set_object_terms( $product_id, 'simple', 'product_type' );

			if ( isset( $pcat_ids[ $slug ] ) ) {
				wp_set_object_terms( $product_id, (int) $pcat_ids[ $slug ], 'product_cat' );
			}
		}
	}
}

/**
 * درون‌ریزی صفحات و منوها.
 *
 * @return void
 */
function zc_import_demo_pages() {

	$pages = array(
		'home'    => array( 'title' => 'صفحه اصلی', 'slug' => 'home', 'tpl' => '' ),
		'about'   => array( 'title' => 'درباره ما', 'slug' => 'about-us', 'tpl' => '' ),
		'contact' => array( 'title' => 'تماس با ما', 'slug' => 'contact-us', 'tpl' => '' ),
		'panel'   => array( 'title' => 'پنل کاربری', 'slug' => 'panel', 'tpl' => 'templates/template-panel.php' ),
		'login'   => array( 'title' => 'ورود و ثبت‌نام', 'slug' => 'login', 'tpl' => 'templates/template-login.php' ),
		'booking' => array( 'title' => 'رزرو نوبت مشاوره', 'slug' => 'booking', 'tpl' => 'templates/template-booking.php' ),
		'faq'     => array( 'title' => 'سوالات متداول', 'slug' => 'faq', 'tpl' => '' ),
		'terms'   => array( 'title' => 'قوانین و مقررات', 'slug' => 'terms', 'tpl' => '' ),
		'privacy' => array( 'title' => 'حریم خصوصی', 'slug' => 'privacy-policy', 'tpl' => '' ),
		'blog'    => array( 'title' => 'مجله زرین کد', 'slug' => 'blog', 'tpl' => '' ),
	);

	$ids = array();

	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( $page['slug'] );

		if ( $existing ) {
			$ids[ $key ] = $existing->ID;

			/*
			 * وردپرس یک برگه‌ی «Privacy Policy» به صورت پیش‌نویس و با
			 * عنوان انگلیسی می‌سازد؛ اگر منتشر نشود، پیوند آن در منو
			 * به شکل ?page_id=3 درمی‌آید و صفحه هم قابل دیدن نیست.
			 */
			if ( 'draft' === get_post_status( $existing ) || 'auto-draft' === get_post_status( $existing ) ) {
				wp_update_post(
					array(
						'ID'           => $existing->ID,
						'post_title'   => $page['title'],
						'post_status'  => 'publish',
						'post_content' => zc_demo_page_content( $key ),
					)
				);
			}
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_title'   => $page['title'],
					'post_name'    => $page['slug'],
					'post_content' => zc_demo_page_content( $key ),
					'post_status'  => 'publish',
					'meta_input'   => array( '_zc_demo' => '1' ),
				)
			);
			$ids[ $key ] = $id;
		}

		if ( $page['tpl'] && $ids[ $key ] ) {
			update_post_meta( $ids[ $key ], '_wp_page_template', $page['tpl'] );
		}
	}

	// تنظیم صفحه اصلی و بلاگ.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( ! empty( $ids['blog'] ) ) {
		update_option( 'page_for_posts', $ids['blog'] );
	}

	// ذخیره در تنظیمات قالب.
	$options = get_option( ZC_PREFIX, array() );
	$options['zc_panel_page']   = $ids['panel'] ?? 0;
	$options['zc_login_page']   = $ids['login'] ?? 0;
	$options['zc_booking_page'] = $ids['booking'] ?? 0;
	$options['zc_terms_link']   = ! empty( $ids['terms'] ) ? get_permalink( $ids['terms'] ) : '';
	update_option( ZC_PREFIX, $options );

	/* ---------- ساخت منوی اصلی ---------- */
	$menu_name = 'منوی اصلی زرین کد';
	$menu      = wp_get_nav_menu_object( $menu_name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );

		/*
		 * منوی اصلی شامل همه‌ی صفحات الزامی سایت است. آیتم‌هایی که
		 * زیرمجموعه دارند با کلید children تعریف می‌شوند و پس از ساخت
		 * والد، به آن متصل می‌گردند.
		 */
		$items = array(
			array( 'title' => 'صفحه اصلی', 'url' => home_url( '/' ) ),
			array( 'title' => 'دوره‌های آموزشی', 'url' => get_post_type_archive_link( 'zc_course' ), 'mega' => true ),
			array(
				'title'    => 'فروشگاه',
				'url'      => zc_is_woo() ? wc_get_page_permalink( 'shop' ) : '#',
				'children' => array(
					array( 'title' => 'همه‌ی محصولات', 'slug' => 'shop' ),
					array( 'title' => 'جستجوی محصولات', 'slug' => 'products' ),
				),
			),
			array(
				'title'    => 'خدمات',
				'url'      => ( $zc_sv_page = get_page_by_path( 'services' ) ) ? get_permalink( $zc_sv_page ) : get_post_type_archive_link( 'zc_service' ),
				'children' => array(
					array( 'title' => 'نمونه‌کارها', 'slug' => 'projects' ),
					array( 'title' => 'قراردادهای خدمات', 'slug' => 'contracts' ),
					array( 'title' => 'درخواست پروژه', 'slug' => 'request' ),
				),
			),
			array(
				'title'    => 'مجله',
				'url'      => ! empty( $ids['blog'] ) ? get_permalink( $ids['blog'] ) : '#',
				'children' => array(
					array( 'title' => 'آموزش‌های رایگان', 'slug' => 'tutorials' ),
					array( 'title' => 'مقالات بلاگ', 'slug' => 'blog' ),
				),
			),
			array( 'title' => 'تماس با ما', 'url' => ! empty( $ids['contact'] ) ? get_permalink( $ids['contact'] ) : '#' ),
		);

		foreach ( $items as $item ) {
			$parent_id = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'   => $item['title'],
					'menu-item-url'     => $item['url'],
					'menu-item-status'  => 'publish',
					'menu-item-classes' => ! empty( $item['mega'] ) ? 'zc-mega' : '',
				)
			);

			if ( empty( $item['children'] ) || is_wp_error( $parent_id ) ) {
				continue;
			}

			foreach ( $item['children'] as $child ) {
				$page = get_page_by_path( $child['slug'] );

				/*
				 * آیتم‌ها به صورت «شیء برگه» ثبت می‌شوند تا اگر بعداً
				 * نشانی یکتای برگه تغییر کند، لینک منو هم خودکار
				 * به‌روز شود (برخلاف لینک دستی که ثابت می‌ماند).
				 *
				 * برخی «زیرمنوها» صفحه نیستند؛ آرشیوِ انواع محتوا یا
				 * صفحه‌های خودکار ووکامرس هستند. برای آن‌ها لینک دستی
				 * معادل ساخته می‌شود.
				 */
				$zc_child_urls = array(
					'shop'      => zc_is_woo() ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
					'products'  => ( $zc_pp = get_page_by_path( 'products' ) ) ? get_permalink( $zc_pp ) : home_url( '/products/' ),
					'projects'  => get_post_type_archive_link( 'zc_project' ),
					'contracts' => ( $zc_cp = get_page_by_path( 'contracts' ) ) ? get_permalink( $zc_cp ) : home_url( '/contracts/' ),
					'request'   => ( $zc_rp = get_page_by_path( 'request' ) ) ? get_permalink( $zc_rp ) : home_url( '/request/' ),
					'tutorials' => get_post_type_archive_link( 'zc_tutorial' ),
					'blog'      => ! empty( $ids['blog'] ) ? get_permalink( $ids['blog'] ) : home_url( '/blog/' ),
				);

				$args = array(
					'menu-item-title'     => $child['title'],
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent_id,
				);

				if ( $page ) {
					$args['menu-item-type']      = 'post_type';
					$args['menu-item-object']    = 'page';
					$args['menu-item-object-id'] = $page->ID;
				} elseif ( ! empty( $zc_child_urls[ $child['slug'] ] ) ) {
					$args['menu-item-url'] = $zc_child_urls[ $child['slug'] ];
				} else {
					$args['menu-item-url'] = home_url( '/' . $child['slug'] . '/' );
				}

				wp_update_nav_menu_item( $menu_id, 0, $args );
			}
		}

		$locations           = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		$locations['mobile']  = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/* ---------- منوی فوتر ---------- */
	$footer_menu = wp_get_nav_menu_object( 'منوی فوتر زرین کد' );
	if ( ! $footer_menu ) {
		$fid = wp_create_nav_menu( 'منوی فوتر زرین کد' );

		$zc_warranty = get_page_by_path( 'warranty' );
		$zc_refund   = get_page_by_path( 'refund-policy' );

		foreach ( array(
			array( 'شرایط و قوانین', $ids['terms'] ?? 0 ),
			array( 'گارانتی و تضمین کیفیت', $zc_warranty ? $zc_warranty->ID : 0 ),
			array( 'شرایط بازگشت وجه', $zc_refund ? $zc_refund->ID : 0 ),
			array( 'حریم خصوصی', $ids['privacy'] ?? 0 ),
			array( 'قراردادهای خدمات', ( $zc_contracts = get_page_by_path( 'contracts' ) ) ? $zc_contracts->ID : 0 ),
			array( 'سوالات متداول', $ids['faq'] ?? 0 ),
			array( 'تماس با ما', $ids['contact'] ?? 0 ),
		) as $item ) {
			$args = array(
				'menu-item-title'  => $item[0],
				'menu-item-status' => 'publish',
			);

			if ( $item[1] ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = (int) $item[1];
			} else {
				$args['menu-item-url'] = '#';
			}

			wp_update_nav_menu_item( $fid, 0, $args );
		}

		$locations             = get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer_2'] = $fid;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * محتوای پیش‌فرض صفحات دمو (شورت‌کدهای المنتور-فرندلی).
 *
 * @param string $key کلید.
 * @return string
 */
function zc_demo_page_content( $key ) {
	switch ( $key ) {
		case 'about':
			return "<h2>درباره زرین کد</h2>\n" . zc_demo_text( 3 ) . "\n<h3>ماموریت ما</h3>\n" . zc_demo_text( 2 );

		case 'contact':
			return "<h2>با ما در تماس باشید</h2>\n<p>تیم پشتیبانی زرین کد از شنبه تا چهارشنبه، ساعت ۹ تا ۱۸ پاسخگوی شماست.</p>";

		case 'faq':
			return "<h2>سوالات متداول</h2>\n<p>پاسخ پرتکرارترین سوالات شما درباره دوره‌ها، خرید و پشتیبانی.</p>";

		case 'terms':
			return "<h2>قوانین و مقررات</h2>\n" . zc_demo_text( 4 );

		case 'privacy':
			return "<h2>سیاست حریم خصوصی</h2>\n" . zc_demo_text( 4 );

		case 'home':
			return '<!-- این صفحه را با المنتور و ویجت‌های زرین کد بسازید -->';

		default:
			return zc_demo_text( 2 );
	}
}

/**
 * اعمال تنظیمات دمو.
 *
 * @return void
 */
function zc_import_demo_settings() {

	// واحد پول ایران برای ووکامرس.
	if ( zc_is_woo() ) {
		update_option( 'woocommerce_currency', 'IRT' );
		update_option( 'woocommerce_currency_pos', 'right_space' );
		update_option( 'woocommerce_price_num_decimals', 0 );
		update_option( 'woocommerce_price_thousand_sep', ',' );
		update_option( 'woocommerce_coming_soon', 'no' );

		/*
		 * ووکامرس برگه‌های خود را با عنوان انگلیسی می‌سازد
		 * (Shop، Cart، …). این عنوان‌ها هم در منو و هم در تگ title
		 * صفحات دیده می‌شوند و برای سایت فارسی مناسب نیستند.
		 */
		$zc_woo_titles = array(
			'shop'      => 'فروشگاه',
			'cart'      => 'سبد خرید',
			'checkout'  => 'تسویه حساب',
			'myaccount' => 'حساب کاربری',
		);

		foreach ( $zc_woo_titles as $zc_key => $zc_title ) {
			$zc_pid = (int) wc_get_page_id( $zc_key );
			$zc_p   = $zc_pid ? get_post( $zc_pid ) : null;

			// فقط اگر پست واقعی و از نوع page باشد ادامه می‌دهیم.
			if ( ! $zc_p || 'page' !== $zc_p->post_type ) {
				continue;
			}

			/*
			 * به‌جای wp_update_post (که هوک‌های save_post را فعال می‌کند و
			 * در برخی محیط‌ها ممکن است خطای «Invalid post» از افزونه‌ها
			 * تولید کند)، عنوان را مستقیماً در دیتابیس به‌روزرسانی می‌کنیم.
			 * این روش بدون side-effect است و هیچ هوکی را فعال نمی‌کند.
			 */
			global $wpdb;
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->posts,
				array( 'post_title' => $zc_title ),
				array( 'ID' => $zc_pid )
			);

			// پاک‌سازی کش عنوان ووکامرس.
			wp_cache_delete( $zc_pid, 'posts' );
			clean_post_cache( $zc_pid );
		}
	}
	$options = get_option( ZC_PREFIX, array() );

	$demo = array(
		'zc_site_name_1'        => 'زرین',
		'zc_site_name_2'        => 'کد',
		'zc_site_tagline'       => 'ZARINCODE',
		'zc_color_primary'      => '#C9A227',
		'zc_color_primary_2'    => '#F5D061',
		'zc_color_dark'         => '#141A31',
		'zc_color_dark_2'       => '#0B2187',
		'zc_body_bg'            => '#FAFCFE',
		'zc_radius'             => 18,
		'zc_container'          => 1280,
		'zc_font_size'          => 15,
		'zc_font_body'          => 'samim',
		'zc_font_heading'       => 'samim',
		'zc_font_weight'        => 400,
		'zc_heading_weight'     => 700,
		'zc_preloader'          => true,
		'zc_back_to_top'        => true,
		'zc_sticky_header'      => true,
		'zc_animations'         => true,
		'zc_ajax_search'        => true,
		'zc_topbar_enable'      => true,
		'zc_header_cta_text'    => 'مشاوره رایگان',
		'zc_phone'              => '071-42380267',
		'zc_mobile'             => '09024561001',
		'zc_site_url'           => 'https://zarincode.com',
		'zc_email'              => 'info@zarincode.com',
		'zc_address'            => 'استان فارس، شهرستان کازرون، دهستان انارستان، برج سوخته سفلی',
		'zc_working_hours'      => 'شنبه تا چهارشنبه ۹ تا ۱۸',
		'zc_footer_about'       => 'زرین کد، مرجع تخصصی آموزش برنامه‌نویسی و فروش محصولات دیجیتال. با بیش از ۲۰۰ دوره آموزشی پروژه‌محور، صدها قالب و افزونه وردپرس و تیم پشتیبانی حرفه‌ای، همراه شما در مسیر یادگیری و درآمدزایی از کدنویسی هستیم.',
		'zc_copyright'          => 'تمامی حقوق مادی و معنوی این وبسایت متعلق به زرین کد می‌باشد.',
		'zc_social_telegram'    => 'https://t.me/zarincode',
		'zc_social_instagram'   => 'https://instagram.com/zarincode',
		'zc_social_linkedin'    => 'https://linkedin.com/company/zarincode',
		'zc_social_github'      => 'https://github.com/zarincode',
		'zc_social_aparat'      => 'https://aparat.com/zarincode',
		'zc_share_enable'       => true,
		'zc_share_auto'         => false,
		'zc_wallet_enable'      => true,
		'zc_ticket_enable'      => true,
		'zc_booking_enable'     => true,
		'zc_chat_enable'        => true,
		'zc_login_method'       => 'both',
		'zc_allow_registration' => true,
		'zc_currency_symbol'    => 'تومان',
		'zc_popular_searches'   => 'لاراول, ری‌اکت, وردپرس, پایتون, جاوااسکریپت',
		'zc_seo_enable'         => true,
		'zc_schema_enable'      => true,
		'zc_lazyload'           => true,
	);

	update_option( ZC_PREFIX, array_merge( $demo, $options ) );

	// تنظیمات وردپرس.
	update_option( 'blogname', 'زرین کد' );
	update_option( 'blogdescription', 'مرجع تخصصی آموزش برنامه‌نویسی و محصولات دیجیتال' );
	update_option( 'posts_per_page', 12 );
	update_option( 'timezone_string', 'Asia/Tehran' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'permalink_structure', '/%postname%/' );

	// بازنویسی قواعد با محافظت؛ برخی افزونه‌ها در این لحظه روی پست‌ها کار می‌کنند.
	try {
		flush_rewrite_rules();
	} catch ( \Throwable $e ) { // phpcs:ignore
		// بی‌صدا رد شود؛ اگر این مرحله خطا بدهد نباید کل درون‌ریزی متوقف شود.
	}
}

/**
 * ساخت ویجت‌های دمو.
 *
 * @return void
 */
function zc_import_demo_widgets() {
	$sidebars = get_option( 'sidebars_widgets', array() );

	// ویجت جستجو + دسته‌بندی در سایدبار اصلی.
	$search = get_option( 'widget_search', array() );
	$search[100] = array( 'title' => 'جستجو در سایت' );
	update_option( 'widget_search', $search );

	$cats = get_option( 'widget_categories', array() );
	$cats[100] = array( 'title' => 'دسته‌بندی‌ها', 'count' => 1, 'hierarchical' => 1, 'dropdown' => 0 );
	update_option( 'widget_categories', $cats );

	$recent = get_option( 'widget_recent-posts', array() );
	$recent[100] = array( 'title' => 'جدیدترین مقالات', 'number' => 5 );
	update_option( 'widget_recent-posts', $recent );

	$tags = get_option( 'widget_tag_cloud', array() );
	$tags[100] = array( 'title' => 'برچسب‌های داغ', 'taxonomy' => 'post_tag' );
	update_option( 'widget_tag_cloud', $tags );

	$sidebars['sidebar-main'] = array( 'search-100', 'categories-100', 'recent-posts-100', 'tag_cloud-100' );
	$sidebars['sidebar-blog'] = array( 'search-100', 'recent-posts-100', 'categories-100' );

	update_option( 'sidebars_widgets', $sidebars );
}

/**
 * افزودن تصویر شاخص از پوشه‌ی تصاویر دمو به یک نوشته.
 *
 * تصویر در کتابخانه‌ی رسانه کپی و ثبت می‌شود تا مانند یک تصویر
 * واقعی قابل مدیریت باشد. تصاویر تکراری دوباره آپلود نمی‌شوند.
 *
 * @param int    $post_id شناسه نوشته.
 * @param string $file    نام فایل داخل demo/images.
 * @return int شناسه پیوست یا 0.
 */
function zc_demo_attach_image( $post_id, $file ) {
	/*
	 * اگر پست والد نامعتبر یا حذف‌شده باشد، هیچ عملیات تصویری نباید
	 * اجرا شود؛ در غیر این صورت توابع هسته مانند set_post_thumbnail
	 * خطای «Invalid post» تولید می‌کنند.
	 */
	if ( ! $post_id || ! get_post( $post_id ) ) {
		return 0;
	}

	$file = ltrim( str_replace( array( '..', '\\' ), '', $file ), '/' );
	$src  = ZC_DIR . 'demo/images/' . $file;

	if ( ! file_exists( $src ) ) {
		return 0;
	}

	// جلوگیری از آپلود تکراری همان تصویر.
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_zc_demo_img',
			'meta_value'     => $file,
		)
	);

	if ( ! empty( $existing ) ) {
		set_post_thumbnail( $post_id, $existing[0] );
		return $existing[0];
	}

	/*
	 * اگر افزودن تصویر (که نیازمند GD و پوشه‌ی رسانه است) به هر دلیلی
	 * شکست بخورد، نباید کل درون‌ریزی دمو متوقف شود. نوشته همچنان با
	 * محتوای خود ساخته می‌شود و تنها تصویر شاخص نخواهد داشت.
	 */
	try {
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$uploads = wp_upload_dir();
		$name    = wp_unique_filename( $uploads['path'], basename( $file ) );
		$dest    = trailingslashit( $uploads['path'] ) . $name;

		if ( ! copy( $src, $dest ) ) {
			return 0;
		}

		$attach_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => sanitize_file_name( pathinfo( basename( $name ), PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$dest,
			$post_id
		);

		if ( is_wp_error( $attach_id ) || ! $attach_id ) {
			@unlink( $dest ); // phpcs:ignore
			return 0;
		}

		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $dest ) );
		update_post_meta( $attach_id, '_zc_demo_img', $file );
		update_post_meta( $attach_id, '_zc_demo', '1' );
		set_post_thumbnail( $post_id, $attach_id );

		return $attach_id;
	} catch ( \Throwable $e ) {
		return 0;
	}
}

/**
 * انتخاب یک تصویر دمو بر اساس شاخص، به صورت چرخشی.
 *
 * @param int $index شاخص.
 * @return string نام فایل.
 */
function zc_demo_image_for( $index ) {
	/*
	 * تصاویر اختصاصی مدرن دمو. ترتیب این آرایه با ترتیب دوره‌های دمو
	 * هماهنگ است تا هر دوره تصویرِ مرتبط با موضوع خود را بگیرد.
	 * برای مقالات، خدمات، پروژه‌ها و محصولات، از همین مجموعه به‌صورت
	 * چرخشی استفاده می‌شود تا دمو یکدست و شیک دیده شود.
	 */
	$named = array(
		'course-php.jpg',
		'course-js.jpg',
		'course-react.jpg',
		'course-wordpress.jpg',
		'course-python-ai.jpg',
		'course-devops.jpg',
		'course-android.jpg',
		'course-frontend.jpg',
		'course-figma.jpg',
		'course-api.jpg',
	);

	static $files = null;

	if ( null === $files ) {
		$files = glob( ZC_DIR . 'demo/images/*.jpg' );
		$files = $files ? array_map( 'basename', $files ) : array();
		sort( $files );
	}

	if ( ! empty( $named ) && isset( $named[ $index % count( $named ) ] ) ) {
		$candidate = $named[ $index % count( $named ) ];
		if ( file_exists( ZC_DIR . 'demo/images/' . $candidate ) ) {
			return $candidate;
		}
	}

	if ( empty( $files ) ) {
		return '';
	}

	return $files[ $index % count( $files ) ];
}

/**
 * درون‌ریزی اطلاعیه‌های نمونه.
 *
 * سه اطلاعیه با سبک، محل نمایش و مخاطب متفاوت می‌سازد تا کاربر
 * بلافاصله ببیند سامانه چطور کار می‌کند.
 *
 * @return void
 */
function zc_import_demo_announcements() {
	// اگر قبلاً ساخته شده، دوباره نساز.
	$existing = get_posts(
		array(
			'post_type'   => 'zc_announce',
			'numberposts' => 1,
			'post_status' => 'any',
			'fields'      => 'ids',
		)
	);

	if ( $existing ) {
		return;
	}

	$items = array(
		array(
			'title'   => __( 'جشنواره تخفیف زرین کد آغاز شد', 'zarincode' ),
			'content' => __( 'تا ۵۰٪ تخفیف روی همه دوره‌های آموزشی و قالب‌های وردپرس. فرصت محدود است.', 'zarincode' ),
			'meta'    => array(
				'_zc_an_style'       => 'gold',
				'_zc_an_priority'    => '5',
				'_zc_an_audience'    => 'all',
				'_zc_an_btn_text'    => __( 'مشاهده فروشگاه', 'zarincode' ),
				'_zc_an_btn_url'     => home_url( '/shop/' ),
				'_zc_an_dismissible' => '1',
			),
			'places'  => array( 'bar', 'panel' ),
		),
		array(
			'title'   => __( 'دوره تازه: لاراول از صفر تا استقرار', 'zarincode' ),
			'content' => __( 'دوره کامل لاراول با ۶۰ ساعت آموزش پروژه‌محور منتشر شد. ثبت‌نام زودهنگام ارزان‌تر است.', 'zarincode' ),
			'meta'    => array(
				'_zc_an_style'       => 'info',
				'_zc_an_priority'    => '10',
				'_zc_an_audience'    => 'all',
				'_zc_an_btn_text'    => __( 'مشاهده دوره‌ها', 'zarincode' ),
				'_zc_an_btn_url'     => get_post_type_archive_link( 'zc_course' ),
				'_zc_an_dismissible' => '1',
			),
			'places'  => array( 'panel' ),
		),
		array(
			'title'   => __( 'بروزرسانی زیرساخت در شب جمعه', 'zarincode' ),
			'content' => __( 'سرویس‌ها بین ساعت ۲ تا ۴ بامداد جمعه ممکن است چند دقیقه در دسترس نباشند.', 'zarincode' ),
			'meta'    => array(
				'_zc_an_style'       => 'warning',
				'_zc_an_priority'    => '20',
				'_zc_an_audience'    => 'members',
				'_zc_an_dismissible' => '1',
			),
			'places'  => array( 'panel' ),
		),
	);

	foreach ( $items as $item ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_announce',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_content' => $item['content'],
			)
		);

		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		foreach ( $item['meta'] as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}

		update_post_meta( $id, '_zc_an_placements', $item['places'] );

		// اطلاعیه‌های دمو نباید پیامک یا پیام ربات بفرستند.
		update_post_meta( $id, '_zc_an_sent_at', current_time( 'mysql' ) );
		update_post_meta( $id, '_zc_an_sent_count', 0 );
	}
}
