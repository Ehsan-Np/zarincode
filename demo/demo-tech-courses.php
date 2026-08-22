<?php
/**
 * دوره‌های آموزشی جامع زرین کد — موتور محتوای برنامه‌نویسی فارسی
 * ---------------------------------------------------------------------------
 * ۲۱ دورهٔ آموزشی رایگان و تخصصی را در دموی پیش‌فرض زرین کد می‌سازد.
 * محتوا بر اساس مستندات رسمی و منابع معتبر هر فناوری (MDN، Python.org،
 * Java Docs، MySQL، React، Django، Kotlin و ...) و با لحن دوستانه و فارسی
 * تولید شده است. هر دوره شامل:
 *   - توضیحات کامل و سئو شده (meta + excerpt + features)
 *   - سرفصل جامع از مقدمات تا پیشرفته
 *   - آزمون آنلاین اختصاصی (سوالات چندگزینه‌ای/جای خالی/کد)
 *   - تمرین چالشی گام‌به‌گام
 *   - زبان‌های قابل اجرا در محیط کدنویسی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * معرفی برند و لینک‌سازی داخلی/خارجی در همهٔ دوره‌ها.
 *
 * @param string $tech نام فناوری.
 * @param string $docs آدرس مستندات رسمی.
 * @param string $slug نامک صفحه.
 * @return string
 */
function zc_tech_brand_block( $tech, $docs, $slug ) {
	return '<div class="zc-legal__note" style="margin:22px 0">
<strong>' . esc_html( $tech ) . ' در زرین کد:</strong> این دوره بخشی از
<a href="' . esc_url( home_url( '/' ) ) . '">زرین کد</a> است؛ مرجع تخصصی آموزش برنامه‌نویسی و توسعه‌ی نرم‌افزار.
برای ادامه‌ی مسیر یادگیری، حتماً از دوره‌های مرتبط در
<a href="' . esc_url( home_url( '/courses/' ) ) . '">فهرست دوره‌ها</a>،
<a href="' . esc_url( home_url( '/tutorials/' ) ) . '">آموزش‌های رایگان</a> و
<a href="' . esc_url( home_url( '/blog/' ) ) . '">مقالات مجله‌ی زرین کد</a> دیدن کنید.
مستندات رسمی: <a href="' . esc_url( $docs ) . '" rel="noopener" target="_blank">' . esc_html( $docs ) . '</a>
</div>';
}

/**
 * داده‌های ۲۱ دورهٔ آموزشی. هر آیتم شامل عنوان، خلاصه، دسته، سختی،
 * محتوای معرفی، سرفصل‌ها، آزمون، تمرین چالشی و لینک مستندات رسمی است.
 *
 * @return array
 */
function zc_tech_courses_data() {
	$courses = array(
		'html-css' => array(
			'title'    => 'آموزش کامل HTML و CSS — از صفر تا طراحی وب‌سایت حرفه‌ای',
			'slug'     => 'html-css',
			'cat'      => 'web-development',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://developer.mozilla.org/',
			'desc'     => 'راهنمای جامع و گام‌به‌گام یادگیری HTML و CSS به فارسی؛ از تگ‌های پایه تا Flexbox، Grid و طراحی واکنش‌گرا.',
			'excerpt'  => 'در این دوره‌ی رایگان، HTML و CSS را از پایه تا سطح حرفه‌ای یاد می‌گیرید و اولین وب‌سایت واقعی‌تان را می‌سازید.',
		),
		'javascript' => array(
			'title'    => 'آموزش زبان برنامه‌نویسی JavaScript — از مقدمات تا ES2024',
			'slug'     => 'javascript',
			'cat'      => 'web-development',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://developer.mozilla.org/en-US/docs/Web/JavaScript',
			'desc'     => 'جاوااسکریپت را از صفر بیاموزید: متغیرها، توابع، DOM، رویدادها، async/await و مفاهیم مدرن ES.',
			'excerpt'  => 'کامل‌ترین آموزش رایگان جاوااسکریپت فارسی؛ از اولین خط کد تا ساخت اپلیکیشن‌های تعاملی مدرن.',
		),
		'sql' => array(
			'title'    => 'آموزش SQL — زبان کوئری‌نویسی پایگاه داده از پایه تا پیشرفته',
			'slug'     => 'sql',
			'cat'      => 'database',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://www.iso.org/standard/63555.html',
			'desc'     => 'یادگیری SQL به زبان ساده: SELECT، JOIN، زیرکوئری، ایندکس و بهینه‌سازی کوئری با مثال‌های واقعی.',
			'excerpt'  => 'زبان استاندارد پایگاه داده را گام‌به‌گام یاد بگیرید و به یک تحلیل‌گر داده‌ی حرفه‌ای تبدیل شوید.',
		),
		'python' => array(
			'title'    => 'آموزش زبان برنامه‌نویسی Python — از صفر تا پروژه‌های واقعی',
			'slug'     => 'python',
			'cat'      => 'ai-data',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://docs.python.org/3/',
			'desc'     => 'پایتون را از پایه یاد بگیرید: نحو زبان، ساختارهای داده، توابع، شیءگرایی و ورود به هوش مصنوعی.',
			'excerpt'  => 'محبوب‌ترین زبان دنیا را با روشی پروژه‌محور و فارسی یاد بگیرید؛ بدون نیاز به پیش‌نیاز.',
		),
		'java' => array(
			'title'    => 'آموزش زبان برنامه‌نویسی Java — از مبانی تا معماری شیءگرا',
			'slug'     => 'java',
			'cat'      => 'software-engineering',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://docs.oracle.com/en/java/',
			'desc'     => 'جاوا را از صفر بیاموزید: JVM، کلاس‌ها، وراثت، رابط، Collections و توسعه‌ی اپلیکیشن Enterprise.',
			'excerpt'  => 'دوره‌ی جامع و رایگان جاوا؛ از نصب JDK تا ساخت برنامه‌های شیءگرای قدرتمند.',
		),
		'mysql' => array(
			'title'    => 'آموزش MySQL — مدیریت کامل پایگاه داده‌ی رابطه‌ای',
			'slug'     => 'mysql',
			'cat'      => 'database',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://dev.mysql.com/doc/',
			'desc'     => 'MySQL را کامل یاد بگیرید: طراحی جدول، روابط، ایندکس، تراکنش و مدیریت کاربران با مثال عملی.',
			'excerpt'  => 'محبوب‌ترین پایگاه داده‌ی متن‌باز دنیا را از نصب تا بهینه‌سازی پیشرفته بیاموزید.',
		),
		'bootstrap' => array(
			'title'    => 'آموزش Bootstrap 5 — طراحی رابط کاربری سریع و واکنش‌گرا',
			'slug'     => 'bootstrap',
			'cat'      => 'web-development',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://getbootstrap.com/docs/5.3/',
			'desc'     => 'با فریم‌ورک Bootstrap 5 رابط‌های کاربری حرفه‌ای و واکنش‌گرا بسازید: Grid، کامپوننت‌ها و سفارشی‌سازی.',
			'excerpt'  => 'سریع‌ترین راه برای ساخت رابط کاربری مدرن با Bootstrap 5 به زبان فارسی.',
		),
		'wordpress' => array(
			'title'    => 'آموزش وردپرس — از نصب تا ساخت وب‌سایت و فروشگاه حرفه‌ای',
			'slug'     => 'wordpress',
			'cat'      => 'wordpress',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://developer.wordpress.org/',
			'desc'     => 'وردپرس را کامل یاد بگیرید: نصب، مدیریت، قالب‌سازی، افزونه‌نویسی، ووکامرس و بهینه‌سازی سئو.',
			'excerpt'  => 'از نصب وردپرس تا ساخت فروشگاه و قالب اختصاصی؛ همه‌چیز در یک دوره‌ی رایگان فارسی.',
		),
		'django' => array(
			'title'    => 'آموزش Django — فریم‌ورک وب پایتون از پایه تا پروژه',
			'slug'     => 'django',
			'cat'      => 'backend',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://docs.djangoproject.com/',
			'desc'     => 'با Django وب‌اپلیکیشن‌های امن و قدرتمند بسازید: مدل‌ها، اورم، ویوها، احراز هویت و استقرار.',
			'excerpt'  => 'فریم‌ورک قدرتمند پایتون را با معماری MVT و پروژه‌های واقعی بیاموزید.',
		),
		'aspnet' => array(
			'title'    => 'آموزش ASP.NET Core — توسعه‌ی وب مدرن مایکروسافت',
			'slug'     => 'asp-net-core',
			'cat'      => 'backend',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://learn.microsoft.com/aspnet/core/',
			'desc'     => 'ASP.NET Core را یاد بگیرید: MVC، Web API، EF Core، DI و ساخت سرویس‌های مقیاس‌پذیر.',
			'excerpt'  => 'پلتفرم قدرتمند مایکروسافت برای توسعه‌ی وب؛ از مبانی تا API و معماری تمیز.',
		),
		'react' => array(
			'title'    => 'آموزش React — ساخت رابط‌های کاربری مدرن و تعاملی',
			'slug'     => 'react',
			'cat'      => 'web-development',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://react.dev/',
			'desc'     => 'React را از پایه بیاموزید: کامپوننت‌ها، Hooks، Context، Router و ساخت SPAهای مدرن.',
			'excerpt'  => 'محبوب‌ترین کتابخانه‌ی جاوااسکریپت دنیا را با رویکرد پروژه‌محور یاد بگیرید.',
		),
		'nodejs' => array(
			'title'    => 'آموزش Node.js — برنامه‌نویسی سمت سرور با جاوااسکریپت',
			'slug'     => 'nodejs',
			'cat'      => 'backend',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://nodejs.org/docs/',
			'desc'     => 'Node.js را یاد بگیرید: ماژول‌ها، Event Loop، Express، REST API و اتصال به پایگاه داده.',
			'excerpt'  => 'جاوااسکریپت را به سمت سرور ببرید و APIها و اپلیکیشن‌های مقیاس‌پذیر بسازید.',
		),
		'c' => array(
			'title'    => 'آموزش زبان C — برنامه‌نویسی ساخت‌یافته از پایه',
			'slug'     => 'c',
			'cat'      => 'software-engineering',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://en.cppreference.com/w/c',
			'desc'     => 'زبان C را عمیق یاد بگیرید: اشاره‌گرها، حافظه، ساختارها و الگوریتم‌های پایه با مثال‌های واقعی.',
			'excerpt'  => 'زبان مادر برنامه‌نویسی را با عمق کامل و تمرین‌های عملی فرا بگیرید.',
		),
		'cpp' => array(
			'title'    => 'آموزش زبان C++ — از مبانی تا برنامه‌نویسی شیءگرا',
			'slug'     => 'cpp',
			'cat'      => 'software-engineering',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://en.cppreference.com/',
			'desc'     => 'C++ را از صفر یاد بگیرید: کلاس‌ها، وراثت، STL، الگوها و مدیریت حافظه‌ی هوشمند.',
			'excerpt'  => 'دوره‌ی جامع C++ فارسی؛ مناسب مبتدیان و علاقه‌مندان به بازی و سیستم‌های پرسرعت.',
		),
		'csharp' => array(
			'title'    => 'آموزش زبان C# — توسعه‌ی برنامه با دات‌نت',
			'slug'     => 'csharp',
			'cat'      => 'software-engineering',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://learn.microsoft.com/dotnet/csharp/',
			'desc'     => 'C# را یاد بگیرید: نحو زبان، شیءگرایی، LINQ، async و ساخت برنامه‌های مدرن دات‌نت.',
			'excerpt'  => 'زبان اصلی اکوسیستم دات‌نت مایکروسافت را با مثال‌های عملی و فارسی بیاموزید.',
		),
		'go' => array(
			'title'    => 'آموزش زبان Go — برنامه‌نویسی همزمان و کارآمد گوگل',
			'slug'     => 'go',
			'cat'      => 'software-engineering',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://go.dev/doc/',
			'desc'     => 'Go را یاد بگیرید: گوروتین‌ها، کانال‌ها، رابط‌ها و ساخت سرویس‌های ابری مقیاس‌پذیر.',
			'excerpt'  => 'زبان سریع و همزمان گوگل را با رویکرد عملی و مدرن بیاموزید.',
		),
		'git-github' => array(
			'title'    => 'آموزش کامل Git و GitHub — کنترل نسخه و همکاری تیمی',
			'slug'     => 'git-github',
			'cat'      => 'devops',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://git-scm.com/doc',
			'desc'     => 'Git و GitHub را کامل یاد بگیرید: commit، branch، merge، pull request و گردش کار تیمی.',
			'excerpt'  => 'مهارت ضروری هر برنامه‌نویس؛ از نصب Git تا همکاری حرفه‌ای روی گیت‌هاب.',
		),
		'r' => array(
			'title'    => 'آموزش زبان R — تحلیل آماری و علم داده',
			'slug'     => 'r',
			'cat'      => 'ai-data',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://cran.r-project.org/manuals.html',
			'desc'     => 'R را یاد بگیرید: ساختارهای داده، گرافیک‌سازی، آمار و تحلیل داده با tidyverse.',
			'excerpt'  => 'زبان تخصصی آمار و علم داده را با تمرین‌های واقعی و فارسی بیاموزید.',
		),
		'java2' => array(
			'title'    => 'آموزش پیشرفته‌ی Java — بهینه‌سازی، همزمانی و معماری',
			'slug'     => 'java-advanced',
			'cat'      => 'software-engineering',
			'level'    => 'advanced',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://docs.oracle.com/en/java/',
			'desc'     => 'جاوا را به سطح پیشرفته برسانید: Streams، Concurrency، JVM و الگوهای معماری Enterprise.',
			'excerpt'  => 'برای توسعه‌دهندگانی که می‌خواهند جاوا را در سطح معماری و بهینه‌سازی عمیق بدانند.',
		),
		'maui' => array(
			'title'    => 'آموزش .NET MAUI — ساخت اپلیکیشن چندپلتفرمی',
			'slug'     => 'dotnet-maui',
			'cat'      => 'mobile-development',
			'level'    => 'intermediate',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://learn.microsoft.com/dotnet/maui/',
			'desc'     => '.NET MAUI را یاد بگیرید و با یک کد، اپلیکیشن اندروید، iOS، ویندوز و مک بسازید.',
			'excerpt'  => 'فریم‌ورک چندپلتفرمی مایکروسافت را برای توسعه‌ی اپلیکیشن‌های موبایل و دسکتاپ بیاموزید.',
		),
		'kotlin' => array(
			'title'    => 'آموزش زبان Kotlin — توسعه‌ی مدرن اندروید',
			'slug'     => 'kotlin',
			'cat'      => 'mobile-development',
			'level'    => 'beginner',
			'teacher'  => 'احسان نادری پناه',
			'docs'     => 'https://kotlinlang.org/docs/',
			'desc'     => 'Kotlin را از پایه یاد بگیرید: نحو مدرن، Coroutines، و توسعه‌ی اپلیکیشن اندروید با Jetpack.',
			'excerpt'  => 'زبان رسمی اندروید را با رویکرد مدرن و پروژه‌محور به فارسی بیاموزید.',
		),
	);

	return $courses;
}

/**
 * تولید سرفصل جامع برای یک دوره بر اساس کلید آن.
 *
 * @param string $key کلید دوره.
 * @return array
 */
function zc_tech_curriculum( $key ) {
	$all = array(
		'html-css' => array(
			'مقدمه‌ای بر وب و HTML' => array( 'وب چگونه کار می‌کند؟', 'نصب ویرایشگر و اولین فایل HTML', 'ساختار و تگ‌های پایه', 'تگ‌های متنی و لیست‌ها' ),
			'پیوند و تصویر' => array( 'لینک‌ها و ناوبری', 'کار با تصویر و مسیر نسبی', 'جداول در HTML', 'فرم‌ها و ورودی کاربر' ),
			'CSS از پایه' => array( 'انتخاب‌گرها و نحوه‌ی اتصال', 'مدل جعبه (Box Model)', 'تایپوگرافی و رنگ', 'پس‌زمینه و حاشیه' ),
			'چیدمان مدرن' => array( 'Flexbox قدم‌به‌قدم', 'CSS Grid پیشرفته', 'موقعیت‌دهی (Position)', 'طراحی واکنش‌گرا (Responsive)' ),
			'پروژه‌ی نهایی' => array( 'طراحی یک صفحه‌ی کامل', 'بهینه‌سازی دسترس‌پذیری', 'انتشار آنلاین اولین سایت' ),
		),
		'javascript' => array(
			'مقدمات جاوااسکریپت' => array( 'جاوااسکریپت در مرورگر', 'متغیرها، let و const', 'انواع داده و تبدیل', 'عملگرها و عبارات' ),
			'ساختار کنترل' => array( 'شرط‌ها (if/switch)', 'حلقه‌ها (for/while)', 'تابع‌ها و scope', 'آرایه‌ها و اشیاء' ),
			'DOM و رویدادها' => array( 'انتخاب و ویرایش DOM', 'مدیریت رویدادها', 'فرم‌ها و اعتبارسنجی', 'ذخیره‌سازی محلی' ),
			'جاوااسکریپت مدرن' => array( 'Arrow function و template', 'Destructuring و Spread', 'Promises و async/await', 'ماژول‌های ES' ),
			'پروژه‌های عملی' => array( 'ساخت اپلیکیشن To-Do', 'اپلیکیشن آب‌وهوا با API', 'ساخت بازی تعاملی' ),
		),
		'sql' => array(
			'مفاهیم پایگاه داده' => array( 'پایگاه داده چیست؟', 'مدل رابطه‌ای', 'نصب و راه‌اندازی', 'اولین کوئری SELECT' ),
			'کوئری‌نویسی پایه' => array( 'WHERE و فیلتر', 'ORDER BY و LIMIT', 'توابع تجمیعی', 'GROUP BY' ),
			'اتصال جداول' => array( 'INNER JOIN', 'LEFT/RIGHT JOIN', 'زیرکوئری‌ها', 'UNION' ),
			'پیشرفته' => array( 'نمایه (Index) و بهینه‌سازی', 'Transaction و ACID', 'Store Procedure', 'امنیت و تزریق SQL' ),
			'پروژه‌ی تحلیلی' => array( 'تحلیل داده با SQL', 'گزارش‌سازی', 'بهینه‌سازی کوئری‌های سنگین' ),
		),
		'python' => array(
			'شروع با پایتون' => array( 'نصب پایتون و محیط توسعه', 'اولین برنامه: print', 'متغیرها و انواع داده', 'ورودی و خروجی' ),
			'ساختارهای کنترل' => array( 'شرط‌ها (if/elif/else)', 'حلقه‌های for و while', 'توابع و آرگومان‌ها', 'ماژول‌ها و import' ),
			'ساختارهای داده' => array( 'لیست و تاپل', 'دیکشنری و مجموعه', 'رشته‌ها و روش‌های آن', 'کامپرهنشن‌ها' ),
			'شیءگرایی' => array( 'کلاس‌ها و اشیاء', 'وراثت و polymorphism', 'مدیریت خطا (try/except)', 'فایل و استثنا' ),
			'ورود به داده و هوش مصنوعی' => array( 'کار با کتابخانه‌ی NumPy', 'پانداس و تحلیل داده', 'تجسم داده با Matplotlib', 'پروژه‌ی نهایی' ),
		),
		'java' => array(
			'مقدمات جاوا' => array( 'نصب JDK و IDE', 'ساختار یک برنامه', 'متغیرها و انواع', 'عملگرها' ),
			'برنامه‌نویسی شیءگرا' => array( 'کلاس‌ها و اشیاء', 'متدها و سازنده‌ها', 'کپسوله‌سازی', 'وراثت و رابط' ),
			'ساختارهای داده' => array( 'آرایه و ArrayList', 'HashMap و Set', 'رشته و StringBuilder', 'Enum و record' ),
			'پیشرفته' => array( 'استثنا و مدیریت خطا', 'جریان (Streams) و لامبدا', 'کار با فایل و I/O', 'همزمانی و Thread' ),
			'پروژه' => array( 'طراحی اپلیکیشن کنسولی', 'اتصال به پایگاه داده', 'ساخت برنامه‌ی کامل' ),
		),
		'mysql' => array(
			'نصب و پیکربندی' => array( 'نصب MySQL', 'ابزار MySQL Workbench', 'ایجاد اولین پایگاه داده', 'انواع داده' ),
			'طراحی جدول' => array( 'CREATE TABLE', 'کلید اصلی و خارجی', 'روابط و نرمال‌سازی', 'تغییر ساختار' ),
			'مدیریت داده' => array( 'INSERT و UPDATE', 'DELETE و TRUNCATE', 'کوپسی و پشتیبان‌گیری', 'Transaction' ),
			'پیشرفته' => array( 'ایندکس و performance', 'View و Store Procedure', 'کاربران و دسترسی‌ها', 'بهینه‌سازی' ),
			'پروژه' => array( 'طراحی پایگاه داده‌ی فروشگاه', 'کوپری‌های تحلیلی', 'مدیریت امن' ),
		),
		'bootstrap' => array(
			'شروع با بوت‌استرپ' => array( 'بوت‌استرپ چیست؟', 'نصب و راه‌اندازی', 'سیستم Grid', 'کلاس‌های کمکی' ),
			'کامپوننت‌ها' => array( 'دکمه و نشان', 'کارت‌ها', 'ناوبری و منو', 'فرم‌ها' ),
			'پیشرفته' => array( 'مودال و ابزارک', 'کاروسل و اسلایدر', 'شبکه‌ی واکنش‌گرا', 'سفارشی‌سازی با Sass' ),
			'پروژه' => array( 'ساخت قالب سایت', 'صفحه‌ی فرود', 'بهینه‌سازی نهایی' ),
		),
		'wordpress' => array(
			'شروع با وردپرس' => array( 'وردپرس چیست؟', 'نصب روی هاست', 'پیشخوان و تنظیمات', 'مدیریت نوشته و برگه' ),
			'ظاهر و قالب' => array( 'انتخاب و نصب قالب', 'شخصی‌سازی (Customizer)', 'ساخت منو', 'ویجت‌ها' ),
			'افزونه و فروشگاه' => array( 'نصب و مدیریت افزونه', 'ووکامرس و فروشگاه', 'درگاه پرداخت', 'مدیریت سفارش' ),
			'قالب‌سازی و توسعه' => array( 'ساخت قالب از صفر', 'هیرارشی قالب', 'فانکشن و هوک', 'افزونه‌نویسی پایه' ),
			'سئو و امنیت' => array( 'بهینه‌سازی سئو', 'سرعت و کش', 'بکاپ و امنیت', 'انتشار نهایی' ),
		),
		'django' => array(
			'شروع با جنگو' => array( 'جنگو چیست؟', 'نصب و پروژه‌ی اول', 'مفهوم MVT', 'ساخت اولین اپ' ),
			'مدل و دیتابیس' => array( 'مدل‌ها (Models)', 'مایگریشن', 'اورم و کوئری', 'Admin جنگو' ),
			'ویو و قالب' => array( 'ویوها و URL', 'قالب‌ها و Template', 'فرم‌ها', 'احراز هویت' ),
			'پیشرفته' => array( 'REST API با DRF', 'آپلود و فایل', 'کش و بهینه‌سازی', 'تست‌نویسی' ),
			'پروژه' => array( 'بلاگ کامل', 'فروشگاه کوچک', 'استقرار' ),
		),
		'aspnet' => array(
			'شروع با ASP.NET' => array( 'دات‌نت و ASP.NET Core', 'نصب SDK', 'ساخت اولین پروژه', 'ساختار MVC' ),
			'کنترلر و ویو' => array( 'کنترلرها', 'مدل و View', 'Tag Helper', 'راه‌اندازی (Middleware)' ),
			'داده و EF Core' => array( 'EF Core و DbContext', 'مدل و مایگریشن', 'CRUD کامل', 'رابطه و لود' ),
			'API و پیشرفته' => array( 'Web API', 'اعتبارسنجی و DI', 'احراز هویت JWT', 'استقرار' ),
			'پروژه' => array( 'ساخت API فروشگاه', 'تست و مستندسازی', 'انتشار' ),
		),
		'react' => array(
			'شروع با ری‌اکت' => array( 'ری‌اکت چیست؟', 'نصب و Vite', 'اولین کامپوننت', 'JSX و props' ),
			'وضعیت و رویداد' => array( 'State و useState', 'رویدادها', 'شرطی و لیست', 'فرم‌ها' ),
			'Hooks پیشرفته' => array( 'useEffect', 'Context', 'useReducer', 'کامپوننت سفارشی' ),
			'روتر و API' => array( 'React Router', 'فراخوانی API', 'State مدیریت (Zustand)', 'بست‌پرکتیس' ),
			'پروژه' => array( 'اپلیکیشن کامل', 'تست و بهینه‌سازی', 'استقرار' ),
		),
		'nodejs' => array(
			'شروع با Node.js' => array( 'Node.js چیست؟', 'نصب و اولین اسکریپت', 'ماژول و require', 'npm و پکیج‌ها' ),
			'سمت سرور' => array( 'سرور HTTP', 'سیستم فایل', 'رویدادها و Event Loop', 'Streams' ),
			'Express' => array( 'راه‌اندازی Express', 'روت‌ها و Middleware', 'ورودی و اعتبارسنجی', 'خطا و لاگ' ),
			'داده و API' => array( 'اتصال به MongoDB', 'REST API کامل', 'احراز هویت JWT', 'آپلود فایل' ),
			'پروژه' => array( 'ساخت API کامل', 'تست و بهینه‌سازی', 'استقرار' ),
		),
		'c' => array(
			'مقدمات C' => array( 'نصب کامپایلر', 'ساختار برنامه', 'متغیر و انواع', 'ورودی/خروجی' ),
			'کنترل جریان' => array( 'شرط‌ها', 'حلقه‌ها', 'توابع', 'آرایه‌ها' ),
			'اشاره‌گر و حافظه' => array( 'اشاره‌گر (Pointer)', 'رشته و کاراکتر', 'مدیریت حافظه', 'ساختار (Struct)' ),
			'پیشرفته' => array( 'فایل و I/O', 'مکرو و Preprocessor', 'مدیریت خطا', 'الگوریتم پایه' ),
			'پروژه' => array( 'سیستم مدیریت', 'ساخت ابزار CLI', 'بهینه‌سازی' ),
		),
		'cpp' => array(
			'مقدمات C++' => array( 'نصب و کامپایل', 'ساختار برنامه', 'متغیر و انواع', 'ورودی/خروجی' ),
			'شیءگرایی' => array( 'کلاس و آبجکت', 'سازنده و مخرب', 'وراثت', 'Polymorphism' ),
			'STL و مدرن' => array( 'الگو (Template)', 'Vector و String', 'Map و Set', 'ویژگی C++11/17' ),
			'مدیریت حافظه' => array( 'اشاره‌گر هوشمند', 'Move Semantics', 'مدیریت استثنا', 'چندنخی' ),
			'پروژه' => array( 'سیستم بانکی', 'بازی ساده', 'بهینه‌سازی' ),
		),
		'csharp' => array(
			'مقدمات C#' => array( 'نصب دات‌نت', 'ساختار برنامه', 'متغیر و انواع', 'ورودی/خروجی' ),
			'شیءگرایی' => array( 'کلاس و آبجکت', 'خاصیت و اندیس', 'وراثت و رابط', 'ژنریک' ),
			'دات‌نت مدرن' => array( 'LINQ', 'async/await', 'مدیریت خطا', 'collection' ),
			'پیشرفته' => array( 'Delegate و Event', 'Reflection', 'کار با فایل', 'EF Core پایه' ),
			'پروژه' => array( 'اپلیکیشن کنسول', 'سرویس وب', 'انتشار' ),
		),
		'go' => array(
			'مقدمات Go' => array( 'نصب و workspace', 'ساختار برنامه', 'متغیر و انواع', 'ورودی/خروجی' ),
			'ساختار کنترل' => array( 'شرط و حلقه', 'توابع', 'ساختار و رابط', 'خطا' ),
			'همزمانی' => array( 'گوروتین', 'کانال (Channel)', 'Select', 'پترن همزمانی' ),
			'وب و API' => array( 'سرور HTTP', 'Router', 'JSON', 'اتصال دیتابیس' ),
			'پروژه' => array( 'سرویس REST', 'تست', 'استقرار' ),
		),
		'git-github' => array(
			'شروع با Git' => array( 'کنترل نسخه چیست؟', 'نصب و پیکربندی', 'git init و اولین commit', 'بررسی وضعیت' ),
			'شاخه و ادغام' => array( 'branch و checkout', 'merge و rebase', 'حل تعارض (Conflict)', 'stash' ),
			'گیت‌هاب' => array( 'ایجاد مخزن', 'push و pull', 'pull request', 'issue و پروژه' ),
			'گردش کار تیمی' => array( 'GitFlow', 'همکاری روی فورک', 'CI پایه', 'بست‌پرکتیس' ),
			'پروژه' => array( 'مدیریت پروژه‌ی تیمی', 'نسخه‌دهی (Tag)', 'مستندسازی' ),
		),
		'r' => array(
			'شروع با R' => array( 'نصب R و RStudio', 'ساختار زبان', 'متغیر و انواع', 'وکتور و ماتریس' ),
			'داده و تجزیه' => array( 'داده‌فریم', 'خواندن فایل', 'فیلتر و خلاصه', 'dplyr و tidyverse' ),
			'آمار و گرافیک' => array( 'آمار توصیفی', 'ggplot2', 'همبستگی', 'توزیع' ),
			'پیشرفته' => array( 'مدل‌های آماری', 'پیش‌بینی', 'گزارش با R Markdown', 'پروژه' ),
		),
		'java2' => array(
			'بهینه‌سازی جاوا' => array( 'Streams پیشرفته', 'Optional و fluent', 'جمع‌کننده‌ها', 'بست‌پرکتیس' ),
			'همزمانی' => array( 'Thread و Executor', 'Concurrent Collections', 'Lock و Atomic', 'CompletableFuture' ),
			'معماری' => array( 'الگوهای طراحی', 'چندلایه و Hexagonal', 'DI و Spring پایه', 'Microservices' ),
			'JVM' => array( 'حافظه و GC', 'Profiling', 'کلاس‌لودر', 'بهینه‌سازی' ),
			'پروژه' => array( 'سرویس مقیاس‌پذیر', 'تست و CI', 'معماری کامل' ),
		),
		'maui' => array(
			'شروع با MAUI' => array( 'MAUI چیست؟', 'نصب و قالب', 'ساختار پروژه', 'صفحه و چیدمان' ),
			'UI و کنترل' => array( 'کنترل‌ها', 'داده‌بایندینگ', 'کالکشن‌ویو', 'استایل و تم' ),
			'داده و شبکه' => array( 'اتصال به API', 'ذخیره‌سازی محلی', 'نقشه', 'اعلان' ),
			'پلتفرم' => array( 'دسترسی به API پلتفرم', 'انتشار اندروید', 'انتشار iOS', 'بهینه‌سازی' ),
			'پروژه' => array( 'اپلیکیشن کامل', 'تست', 'انتشار' ),
		),
		'kotlin' => array(
			'مقدمات کاتلین' => array( 'کاتلین چیست؟', 'نصب و Intellij', 'متغیر و val/var', 'رشته و قالب' ),
			'مبانی زبان' => array( 'تابع و lambda', 'داده‌کلاس', 'when و شرط', 'کالکشن' ),
			'شیءگرایی' => array( 'کلاس و آبجکت', 'وراثت و interface', 'اکستنشن', 'sealed و enum' ),
			'اندروید و Coroutines' => array( 'Coroutines و async', 'Jetpack Compose', 'ViewModel', 'اتصال به API' ),
			'پروژه' => array( 'اپلیکیشن اندروید', 'نقشه و داده', 'انتشار' ),
		),
	);

	// fallback برای هر کلید ناشناخته.
	if ( ! isset( $all[ $key ] ) ) {
		$key = 'python';
	}

	$out = array();
	foreach ( $all[ $key ] as $sec => $lessons ) {
		$out[] = array(
			'title'   => $sec,
			'lessons' => array_map(
				function ( $title ) {
					return array(
						'title'    => $title,
						'duration' => sprintf( '%02d:%02d', wp_rand( 9, 40 ), wp_rand( 0, 59 ) ),
						'video'    => '',
						'free'     => 0,
					);
				},
				$lessons
			),
		);
	}

	return $out;
}

/**
 * تولید محتوای معرفی سئو شده برای هر دوره.
 *
 * @param string $key   کلید دوره.
 * @param string $title عنوان.
 * @param string $docs  آدرس مستندات.
 * @return string
 */
function zc_tech_course_intro( $key, $title, $docs ) {
	$data = zc_tech_courses_data();
	$d    = $data[ $key ] ?? $data['python'];

	$lessons = function_exists( 'zc_tech_lessons_html' ) ? zc_tech_lessons_html( $key ) : '';

	return '<p>' . esc_html( $d['desc'] ) . '</p>' . "\n\n"
		. zc_tech_brand_block( $d['title'], $docs, $key ) . "\n\n"
		. '<h2>در این دوره چه چیزی یاد می‌گیرید؟</h2><ul>'
		. '<li>مفاهیم پایه تا پیشرفته با مثال‌های واقعی و استاندارد</li>'
		. '<li>توضیحات متنی کامل فارسی مطابق سرفصل‌های آموزشی</li>'
		. '<li>کدهای اصولی، دقیق و حرفه‌ای برای هر مبحث</li>'
		. '<li>تمرین‌های گام‌به‌گام و آزمون آنلاین در پایان هر فصل</li>'
		. '<li>پروژه‌های عملی برای ورود به بازار کار</li>'
		. '</ul>'
		. '<h2>این دوره برای چه کسانی مناسب است؟</h2>'
		. '<p>اگر تازه‌کار هستید یا می‌خواهید به سطح حرفه‌ای برسید، این دوره برای شماست. پیش‌نیاز خاصی ندارد و تمام مباحث از پایه و با لحنی دوستانه توضیح داده می‌شود.</p>'
		. "\n\n" . $lessons;
}

/**
 * آزمون اختصاصی برای هر دوره (سوالات واقعی و مرتبط با همان فناوری).
 *
 * @param string $key کلید دوره.
 * @return array
 */
function zc_tech_quiz( $key ) {
	$q = array(
		'html-css' => array(
			array( 'type' => 'mc', 'question' => 'کدام تگ برای نمایش بزرگ‌ترین عنوان در HTML استفاده می‌شود؟', 'options' => array( '<h1>', '<h6>', '<head>', '<title>' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام ویژگی CSS برای چیدمان افقی آیتم‌ها در یک ردیف استفاده می‌شود؟', 'options' => array( 'display: flex', 'position: static', 'float: none', 'z-index: 1' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'ویژگی CSS که برای تعریف رنگ متن استفاده می‌شود چه نام دارد؟', 'answers' => array( 'color' ), 'hint' => 'خواص رنگ متن.' ),
			array( 'type' => 'mc', 'question' => 'Grid در CSS با کدام ویژگی فعال می‌شود؟', 'options' => array( 'display: grid', 'display: block', 'display: flex', 'display: none' ), 'answer' => 0 ),
		),
		'javascript' => array(
			array( 'type' => 'mc', 'question' => 'برای تعریف یک متغیر ثابت در جاوااسکریپت مدرن از کدام کلمه استفاده می‌شود؟', 'options' => array( 'const', 'var', 'let', 'static' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام متد برای بررسی درستی شرط قبل از ادامه استفاده می‌شود؟', 'options' => array( 'if', 'for', 'while', 'switch' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'تابع چاپ در کنسول مرورگر چه نام دارد؟', 'answers' => array( 'console.log' ), 'hint' => 'console و متد log.' ),
			array( 'type' => 'code', 'question' => 'برنامه‌ای بنویسید که عبارت hello را در کنسول چاپ کند.', 'language' => 'javascript', 'expected' => 'hello', 'starter' => "console.log('')", 'hint' => 'console.log استفاده کنید.' ),
		),
		'sql' => array(
			array( 'type' => 'mc', 'question' => 'کدام دستور SQL برای خواندن داده از جدول استفاده می‌شود؟', 'options' => array( 'SELECT', 'INSERT', 'DELETE', 'UPDATE' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'برای اتصال دو جدول از کدام کلیدواژه استفاده می‌شود؟', 'options' => array( 'JOIN', 'MERGE', 'LINK', 'CONNECT' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'برای فیلتر ردیف‌ها در SELECT از کدام کلیدواژه استفاده می‌شود؟', 'answers' => array( 'WHERE' ), 'hint' => 'بعد از FROM.' ),
			array( 'type' => 'mc', 'question' => 'کدام دستور برای حذف جدول استفاده می‌شود؟', 'options' => array( 'DROP TABLE', 'DELETE TABLE', 'REMOVE TABLE', 'CLEAR TABLE' ), 'answer' => 0 ),
		),
		'python' => array(
			array( 'type' => 'mc', 'question' => 'برای چاپ خروجی در پایتون از کدام تابع استفاده می‌شود؟', 'options' => array( 'print()', 'echo()', 'console.log()', 'output()' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام ساختار داده‌ی پایتون ترتیب‌پذیر و قابل‌تغییر است؟', 'options' => array( 'list', 'tuple', 'set', 'frozenset' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'برنامه‌ای بنویسید که خروجی 2 + 2 را چاپ کند.', 'language' => 'python', 'expected' => '4', 'starter' => "print( )", 'hint' => 'حاصل 2+2.' ),
			array( 'type' => 'blank', 'question' => 'برای تعریف کامنت در پایتون از کدام نماد استفاده می‌شود؟', 'answers' => array( '#' ), 'hint' => 'نماد هش.' ),
		),
		'java' => array(
			array( 'type' => 'mc', 'question' => 'روش شروع برنامه در جاوا کدام است؟', 'options' => array( 'public static void main', 'public void run', 'static main', 'void start' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'برای وراثت در جاوا از کدام کلمه استفاده می‌شود؟', 'options' => array( 'extends', 'inherits', 'implements', 'super' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلمه‌ی کلیدی تعریف کلاس در جاوا چیست؟', 'answers' => array( 'class' ), 'hint' => 'کلاس.' ),
			array( 'type' => 'mc', 'question' => 'کدام نوع داده برای عدد صحیح استفاده می‌شود؟', 'options' => array( 'int', 'float', 'char', 'boolean' ), 'answer' => 0 ),
		),
		'mysql' => array(
			array( 'type' => 'mc', 'question' => 'برای ایجاد پایگاه داده در MySQL از کدام دستور استفاده می‌شود؟', 'options' => array( 'CREATE DATABASE', 'MAKE DATABASE', 'NEW DATABASE', 'ADD DATABASE' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کلید اصلی یک جدول با کدام مفهوم مشخص می‌شود؟', 'options' => array( 'PRIMARY KEY', 'UNIQUE', 'INDEX', 'FOREIGN' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'برای افزودن ردیف به جدول از کدام دستور استفاده می‌شود؟', 'answers' => array( 'INSERT' ), 'hint' => 'درج داده.' ),
			array( 'type' => 'mc', 'question' => 'کدام نوع داده برای متن بلند مناسب است؟', 'options' => array( 'TEXT', 'INT', 'DATE', 'BOOLEAN' ), 'answer' => 0 ),
		),
		'bootstrap' => array(
			array( 'type' => 'mc', 'question' => 'سیستم چیدمان اصلی بوت‌استرپ چه نام دارد؟', 'options' => array( 'Grid', 'Flex', 'Block', 'Table' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام کلاس بوت‌استرپ برای دکمه‌ی اصلی استفاده می‌شود؟', 'options' => array( 'btn btn-primary', 'btn main', 'button primary', 'primary-btn' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلاس واکنش‌گرای ردیف در بوت‌استرپ چیست؟', 'answers' => array( 'row' ), 'hint' => 'ردیف.' ),
			array( 'type' => 'mc', 'question' => 'بوت‌استرپ بر پایه چه زبانی ساخته شده است؟', 'options' => array( 'CSS/JS', 'Python', 'PHP', 'Java' ), 'answer' => 0 ),
		),
		'wordpress' => array(
			array( 'type' => 'mc', 'question' => 'وردپرس با کدام زبان نوشته شده است؟', 'options' => array( 'PHP', 'Python', 'Java', 'Go' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'قالب‌های وردپرس در کدام پوشه قرار دارند؟', 'options' => array( 'wp-content/themes', 'wp-content/plugins', 'wp-admin', 'wp-includes' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'فایل اصلی تنظیمات قالب وردپرس چه نام دارد؟', 'answers' => array( 'functions.php' ), 'hint' => 'فانکشن.' ),
			array( 'type' => 'mc', 'question' => 'کدام افزونه برای فروشگاه استفاده می‌شود؟', 'options' => array( 'WooCommerce', 'Elementor', 'Yoast', 'Akismet' ), 'answer' => 0 ),
		),
		'django' => array(
			array( 'type' => 'mc', 'question' => 'Django با کدام زبان نوشته شده است؟', 'options' => array( 'Python', 'PHP', 'Java', 'Ruby' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'معماری Django چیست؟', 'options' => array( 'MVT', 'MVC', 'MVVM', 'MVP' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'دستور ساخت پروژه‌ی جنگو چیست؟', 'answers' => array( 'startproject' ), 'hint' => 'django-admin ...' ),
			array( 'type' => 'mc', 'question' => 'برای کار با دیتابیس در جنگو از چه چیزی استفاده می‌شود؟', 'options' => array( 'ORM', 'SQL raw', 'NoSQL', 'JSON' ), 'answer' => 0 ),
		),
		'aspnet' => array(
			array( 'type' => 'mc', 'question' => 'ASP.NET Core محصول کدام شرکت است؟', 'options' => array( 'Microsoft', 'Google', 'Apple', 'Oracle' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'معماری ASP.NET Core MVC چیست؟', 'options' => array( 'Model-View-Controller', 'Model-View-Template', 'View-Model', 'Module' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'زبان اصلی برنامه‌نویسی ASP.NET چیست؟', 'answers' => array( 'C#' ), 'hint' => 'سی شارپ.' ),
			array( 'type' => 'mc', 'question' => 'برای دسترسی به داده در ASP.NET از کدام ORM استفاده می‌شود؟', 'options' => array( 'Entity Framework', 'Hibernate', 'Sequelize', 'Django ORM' ), 'answer' => 0 ),
		),
		'react' => array(
			array( 'type' => 'mc', 'question' => 'React محصول کدام شرکت است؟', 'options' => array( 'Meta (Facebook)', 'Google', 'Microsoft', 'Amazon' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'واحد اصلی UI در ری‌اکت چیست؟', 'options' => array( 'Component', 'Module', 'Class', 'Widget' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'هوک مدیریت state در ری‌اکت چیست؟', 'answers' => array( 'useState' ), 'hint' => 'use...' ),
			array( 'type' => 'mc', 'question' => 'زبان قالب‌بندی در ری‌اکت چیست؟', 'options' => array( 'JSX', 'HTML', 'XML', 'TSX' ), 'answer' => 0 ),
		),
		'nodejs' => array(
			array( 'type' => 'mc', 'question' => 'Node.js روی کدام موتور جاوااسکریپت اجرا می‌شود؟', 'options' => array( 'V8', 'SpiderMonkey', 'JavaScriptCore', 'Chakra' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'مدیر پکیج‌های Node.js چیست؟', 'options' => array( 'npm', 'pip', 'composer', 'maven' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'فریم‌ورک محبوب وب برای Node.js چیست؟', 'answers' => array( 'Express' ), 'hint' => 'Express.js' ),
			array( 'type' => 'mc', 'question' => 'Node.js برای چه منظوری عمدتاً استفاده می‌شود؟', 'options' => array( 'سمت سرور', 'سمت کلاینت فقط', 'طراحی گرافیک', 'پایگاه داده' ), 'answer' => 0 ),
		),
		'c' => array(
			array( 'type' => 'mc', 'question' => 'کدام تابع برای چاپ در زبان C استفاده می‌شود؟', 'options' => array( 'printf', 'print', 'echo', 'cout' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام فایل هدر برای ورودی/خروجی است؟', 'options' => array( 'stdio.h', 'stdlib.h', 'string.h', 'math.h' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'علامت مقداردهی در C چیست؟', 'answers' => array( '=' ), 'hint' => 'تساوی.' ),
			array( 'type' => 'mc', 'question' => 'کدام نوع داده برای عدد صحیح است؟', 'options' => array( 'int', 'float', 'double', 'char' ), 'answer' => 0 ),
		),
		'cpp' => array(
			array( 'type' => 'mc', 'question' => 'برای چاپ در C++ از کدام استفاده می‌شود؟', 'options' => array( 'cout', 'printf', 'echo', 'print' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'C++ گسترش یافته‌ی کدام زبان است؟', 'options' => array( 'C', 'Java', 'Python', 'Go' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'عملگر ورودی در C++ چیست؟', 'answers' => array( 'cin' ), 'hint' => 'cin >>' ),
			array( 'type' => 'mc', 'question' => 'کدام مفهوم برای ارث‌بری استفاده می‌شود؟', 'options' => array( 'inheritance', 'module', 'interface', 'package' ), 'answer' => 0 ),
		),
		'csharp' => array(
			array( 'type' => 'mc', 'question' => 'برای چاپ در C# از کدام استفاده می‌شود؟', 'options' => array( 'Console.WriteLine', 'printf', 'print', 'echo' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'C# در کدام پلتفرم اجرا می‌شود؟', 'options' => array( '.NET', 'JVM', 'V8', 'Node' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلیدواژه‌ی تعریف کلاس در C# چیست؟', 'answers' => array( 'class' ), 'hint' => 'کلاس.' ),
			array( 'type' => 'mc', 'question' => 'LINQ برای چه منظوری است؟', 'options' => array( 'کوئری بر روی داده', 'رسم گرافیک', 'شبکه', 'فایل' ), 'answer' => 0 ),
		),
		'go' => array(
			array( 'type' => 'mc', 'question' => 'Go محصول کدام شرکت است؟', 'options' => array( 'Google', 'Microsoft', 'Apple', 'Meta' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'برای چاپ در Go از کدام پکیج استفاده می‌شود؟', 'options' => array( 'fmt', 'io', 'os', 'net' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'تابع چاپ در Go چیست؟', 'answers' => array( 'fmt.Println' ), 'hint' => 'fmt.Println' ),
			array( 'type' => 'mc', 'question' => 'نوع داده‌ی صحیح در Go چیست؟', 'options' => array( 'int', 'number', 'integer', 'float' ), 'answer' => 0 ),
		),
		'git-github' => array(
			array( 'type' => 'mc', 'question' => 'دستور ساخت مخزن جدید در Git چیست؟', 'options' => array( 'git init', 'git start', 'git new', 'git create' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'برای ثبت تغییرات از کدام دستور استفاده می‌شود؟', 'options' => array( 'git commit', 'git save', 'git push', 'git log' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'دستور ارسال تغییرات به گیت‌هاب چیست؟', 'answers' => array( 'git push' ), 'hint' => 'push' ),
			array( 'type' => 'mc', 'question' => 'برای ساخت شاخه‌ی جدید از کدام دستور استفاده می‌شود؟', 'options' => array( 'git branch', 'git fork', 'git split', 'git new-branch' ), 'answer' => 0 ),
		),
		'r' => array(
			array( 'type' => 'mc', 'question' => 'برای بردار در R از کدام تابع استفاده می‌شود؟', 'options' => array( 'c()', 'v()', 'list()', 'array()' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'بسته‌ی محبوب گرافیک‌سازی در R چیست؟', 'options' => array( 'ggplot2', 'matplotlib', 'chartjs', 'plotlyjs' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'نوع داده‌ی جدولی در R چیست؟', 'answers' => array( 'data.frame' ), 'hint' => 'data.frame' ),
			array( 'type' => 'mc', 'question' => 'R عمدتاً برای چه کاری استفاده می‌شود؟', 'options' => array( 'آمار و تحلیل داده', 'بازی‌سازی', 'طراحی وب', 'موبایل' ), 'answer' => 0 ),
		),
		'java2' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای پردازش جریان داده در جاوا استفاده می‌شود؟', 'options' => array( 'Stream', 'Flow', 'Pipe', 'Channel' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام کلاس برای مدیریت همزمانی پیشرفته استفاده می‌شود؟', 'options' => array( 'CompletableFuture', 'FutureTask', 'Promise', 'Async' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'فریم‌ورک محبوب برای Dependency Injection در جاوا چیست؟', 'answers' => array( 'Spring' ), 'hint' => 'Spring' ),
			array( 'type' => 'mc', 'question' => 'کدام مفهوم برای الگوی طراحی Singleton است؟', 'options' => array( 'تک‌نمونه', 'چندنمونه', 'بی‌نمونه', 'نمونه‌ساز' ), 'answer' => 0 ),
		),
		'maui' => array(
			array( 'type' => 'mc', 'question' => '.NET MAUI چیست؟', 'options' => array( 'فریم‌ورک چندپلتفرمی', 'پایگاه داده', 'سیستم‌عامل', 'مرورگر' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'کدام زبان در MAUI استفاده می‌شود؟', 'options' => array( 'C#', 'Python', 'Java', 'Kotlin' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'پلتفرم‌های MAUI کدامند؟ (یکی)', 'answers' => array( 'Android', 'iOS', 'Windows', 'macOS' ), 'hint' => 'Android/iOS/Windows/macOS' ),
			array( 'type' => 'mc', 'question' => 'برای چیدمان در MAUI از کدام استفاده می‌شود؟', 'options' => array( 'Grid/StackLayout', 'HTML', 'Flexbox', 'CSS Grid' ), 'answer' => 0 ),
		),
		'kotlin' => array(
			array( 'type' => 'mc', 'question' => 'Kotlin با کدام زبان سازگار است؟', 'options' => array( 'Java', 'C', 'Python', 'Ruby' ), 'answer' => 0 ),
			array( 'type' => 'mc', 'question' => 'برای تعریف متغیر ثابت در کاتلین از کدام استفاده می‌شود؟', 'options' => array( 'val', 'var', 'let', 'const' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'تابع چاپ در کاتلین چیست؟', 'answers' => array( 'println' ), 'hint' => 'println' ),
			array( 'type' => 'mc', 'question' => 'Kotlin زبان رسمی کدام پلتفرم است؟', 'options' => array( 'Android', 'iOS', 'Windows', 'Web' ), 'answer' => 0 ),
		),
	);

	if ( ! isset( $q[ $key ] ) ) {
		return zc_demo_quiz();
	}
	return $q[ $key ];
}

/**
 * تمرین چالشی اختصاصی هر دوره.
 *
 * @param string $key کلید دوره.
 * @return array
 */
function zc_tech_practice( $key ) {
	$p = array(
		'html-css' => array(
			array( 'type' => 'mc', 'question' => 'کدام تگ برای ساخت یک دکمه در HTML استفاده می‌شود؟', 'options' => array( '<button>', '<input>', '<a>', '<div>' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'ویژگی CSS برای تعریف حاشیه بیرونی چیست؟', 'answers' => array( 'margin' ), 'hint' => 'فاصله بیرونی.' ),
			array( 'type' => 'code', 'question' => 'یک پاراگراف با متن hello در HTML بنویسید و خروجی را چاپ کنید.', 'language' => 'python', 'expected' => 'hello', 'starter' => "print('<p>hello</p>')", 'hint' => 'تگ p.' ),
		),
		'javascript' => array(
			array( 'type' => 'mc', 'question' => 'کدام نوع داده برای true/false است؟', 'options' => array( 'boolean', 'number', 'string', 'object' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'حاصل جمع 7 و 5 را در جاوااسکریپت چاپ کنید.', 'language' => 'javascript', 'expected' => '12', 'starter' => "console.log(7 + )", 'hint' => 'console.log' ),
		),
		'sql' => array(
			array( 'type' => 'mc', 'question' => 'کدام دستور برای به‌روزرسانی داده استفاده می‌شود؟', 'options' => array( 'UPDATE', 'EDIT', 'CHANGE', 'MODIFY' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'برای حذف ردیف از جدول از کدام دستور استفاده می‌شود؟', 'answers' => array( 'DELETE' ), 'hint' => 'DELETE FROM' ),
		),
		'python' => array(
			array( 'type' => 'mc', 'question' => 'کدام متد برای افزودن به لیست استفاده می‌شود؟', 'options' => array( 'append()', 'add()', 'push()', 'insert()' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'حاصل 10 - 3 را چاپ کنید.', 'language' => 'python', 'expected' => '7', 'starter' => "print(10 - )", 'hint' => 'print' ),
		),
		'java' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای ساخت لیست در جاوا استفاده می‌شود؟', 'options' => array( 'ArrayList', 'ListArray', 'Array', 'Vector' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلمه‌ی کلیدی برای ساخت نمونه از کلاس چیست؟', 'answers' => array( 'new' ), 'hint' => 'new' ),
		),
		'mysql' => array(
			array( 'type' => 'mc', 'question' => 'برای مشاهده همه‌ی جدول‌ها از کدام استفاده می‌شود؟', 'options' => array( 'SHOW TABLES', 'LIST TABLES', 'VIEW TABLES', 'GET TABLES' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'دستور ایجاد جدول چیست؟', 'answers' => array( 'CREATE TABLE' ), 'hint' => 'CREATE TABLE' ),
		),
		'bootstrap' => array(
			array( 'type' => 'mc', 'question' => 'کدام کلاس برای تصویر دایره‌ای استفاده می‌شود؟', 'options' => array( 'rounded-circle', 'circle', 'round', 'oval' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلاس رنگ پس‌زمینه در بوت‌استرپ با چه پیشوندی شروع می‌شود؟', 'answers' => array( 'bg-' ), 'hint' => 'bg-primary' ),
		),
		'wordpress' => array(
			array( 'type' => 'mc', 'question' => 'کدام فایل قالب وردپرس برای سربرگ است؟', 'options' => array( 'header.php', 'footer.php', 'index.php', 'single.php' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'فایل استایل اصلی قالب چیست؟', 'answers' => array( 'style.css' ), 'hint' => 'style.css' ),
		),
		'django' => array(
			array( 'type' => 'mc', 'question' => 'دستور ساخت اپ در جنگو چیست؟', 'options' => array( 'startapp', 'newapp', 'createapp', 'addapp' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'با پایتون عبارت hello را چاپ کنید.', 'language' => 'python', 'expected' => 'hello', 'starter' => "print('')", 'hint' => 'print' ),
		),
		'aspnet' => array(
			array( 'type' => 'mc', 'question' => 'دستور ساخت پروژه‌ی ASP.NET Core چیست؟', 'options' => array( 'dotnet new', 'dotnet make', 'dotnet create', 'dotnet build' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'زبان برنامه‌نویسی اصلی ASP.NET Core چیست؟', 'answers' => array( 'C#' ), 'hint' => 'سی شارپ' ),
		),
		'react' => array(
			array( 'type' => 'mc', 'question' => 'دستور ساخت پروژه‌ی ری‌اکت با Vite چیست؟', 'options' => array( 'npm create vite', 'npm new', 'npm init react', 'npm start' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'با پایتون عبارت React را چاپ کنید.', 'language' => 'python', 'expected' => 'React', 'starter' => "print('')", 'hint' => 'print' ),
		),
		'nodejs' => array(
			array( 'type' => 'mc', 'question' => 'دستور اجرای اسکریپت Node.js چیست؟', 'options' => array( 'node app.js', 'run app.js', 'start app.js', 'exec app.js' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'در جاوااسکریپت عبارت node را چاپ کنید.', 'language' => 'javascript', 'expected' => 'node', 'starter' => "console.log('')", 'hint' => 'console.log' ),
		),
		'c' => array(
			array( 'type' => 'mc', 'question' => 'کامپایلر محبوب C چیست؟', 'options' => array( 'gcc', 'javac', 'python', 'node' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'علامت دستور پایان در C چیست؟', 'answers' => array( ';' ), 'hint' => 'سمیکالن' ),
		),
		'cpp' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای ورودی در C++ است؟', 'options' => array( 'cin', 'input', 'scanf', 'read' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'سرآیند ورودی/خروجی در C++ چیست؟', 'answers' => array( 'iostream' ), 'hint' => 'iostream' ),
		),
		'csharp' => array(
			array( 'type' => 'mc', 'question' => 'کدام کلمه برای وراثت در C# است؟', 'options' => array( ':', 'extends', 'inherits', '->' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'کلیدواژه‌ی تعریف فضای نام چیست؟', 'answers' => array( 'namespace' ), 'hint' => 'namespace' ),
		),
		'go' => array(
			array( 'type' => 'mc', 'question' => 'نوع داده‌ی کانال در Go چیست؟', 'options' => array( 'chan', 'channel', 'pipe', 'stream' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'در پایتون عبارت Go را چاپ کنید.', 'language' => 'python', 'expected' => 'Go', 'starter' => "print('')", 'hint' => 'print' ),
		),
		'git-github' => array(
			array( 'type' => 'mc', 'question' => 'دستور مشاهده‌ی تاریخچه چیست؟', 'options' => array( 'git log', 'git history', 'git show', 'git list' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'دستور دریافت تغییرات از گیت‌هاب چیست؟', 'answers' => array( 'git pull' ), 'hint' => 'pull' ),
		),
		'r' => array(
			array( 'type' => 'mc', 'question' => 'برای نصب بسته در R از کدام استفاده می‌شود؟', 'options' => array( 'install.packages()', 'pip install', 'npm install', 'apt install' ), 'answer' => 0 ),
			array( 'type' => 'blank', 'question' => 'تابع نمایش در R چیست؟', 'answers' => array( 'print' ), 'hint' => 'print' ),
		),
		'java2' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای مدیریت جریان موازی است؟', 'options' => array( 'parallelStream', 'stream', 'flow', 'pipe' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'در پایتون عبارت Java را چاپ کنید.', 'language' => 'python', 'expected' => 'Java', 'starter' => "print('')", 'hint' => 'print' ),
		),
		'maui' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای چیدمان ستونی در MAUI است؟', 'options' => array( 'VerticalStackLayout', 'ColumnLayout', 'StackV', 'VBox' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'در پایتون عبارت MAUI را چاپ کنید.', 'language' => 'python', 'expected' => 'MAUI', 'starter' => "print('')", 'hint' => 'print' ),
		),
		'kotlin' => array(
			array( 'type' => 'mc', 'question' => 'کدام برای تعریف کلاس داده در کاتلین است؟', 'options' => array( 'data class', 'class', 'data', 'struct' ), 'answer' => 0 ),
			array( 'type' => 'code', 'question' => 'در پایتون عبارت Kotlin را چاپ کنید.', 'language' => 'python', 'expected' => 'Kotlin', 'starter' => "print('')", 'hint' => 'print' ),
		),
	);

	if ( ! isset( $p[ $key ] ) ) {
		return zc_demo_course_practice();
	}
	return $p[ $key ];
}

/**
 * نصب همهٔ ۲۱ دورهٔ آموزشی در دمو.
 *
 * @return array گزارش.
 */
function zc_install_tech_courses() {
	$data     = zc_tech_courses_data();
	$teachers = array();
	$tq       = get_posts( array( 'post_type' => 'zc_teacher', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	foreach ( $tq as $tid ) {
		$teachers[ get_the_title( $tid ) ] = $tid;
	}

	// اطمینان از وجود دسته‌ها.
	$cats = array(
		'web-development'      => 'توسعه وب',
		'database'             => 'پایگاه داده',
		'ai-data'              => 'هوش مصنوعی و داده',
		'software-engineering' => 'مهندسی نرم‌افزار',
		'backend'              => 'برنامه‌نویسی سمت سرور',
		'wordpress'            => 'وردپرس',
		'mobile-development'   => 'توسعه موبایل',
		'devops'               => 'DevOps و ابزارها',
	);
	$cat_ids = array();
	foreach ( $cats as $slug => $name ) {
		$term = term_exists( $slug, 'zc_course_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'zc_course_cat', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $slug ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	$report = array( 'created' => array(), 'updated' => array(), 'skipped' => array() );
	$img_i  = 0;

	foreach ( $data as $key => $c ) {
		$existing = zc_get_post_by_title( $c['title'], 'zc_course' );
		if ( $existing && 'publish' === $existing->post_status ) {
			// اگر دوره از قبل وجود دارد، محتوا و متادیتا را به‌روزرسانی کن
			// تا نسخه‌های قدیمی که محتوای کامل نداشتند اصلاح شوند.
			$intro = zc_tech_course_intro( $key, $c['title'], $c['docs'] );
			wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_content' => $intro,
					'post_excerpt' => $c['excerpt'],
				)
			);
			update_post_meta( $existing->ID, '_zc_teacher', 'احسان نادری پناه' );
			update_post_meta( $existing->ID, '_zc_curriculum', zc_tech_curriculum( $key ) );
			update_post_meta( $existing->ID, '_zc_quiz', zc_tech_quiz( $key ) );
			update_post_meta( $existing->ID, '_zc_course_practice', zc_tech_practice( $key ) );
			$report['updated'][] = $key;
			continue;
		}

		$langs = array( 'python', 'javascript', 'php' );

		$id = wp_insert_post(
			array(
				'post_type'    => 'zc_course',
				'post_title'   => $c['title'],
				'post_name'    => $c['slug'],
				'post_content' => zc_tech_course_intro( $key, $c['title'], $c['docs'] ),
				'post_excerpt' => $c['excerpt'],
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_zc_demo'              => '1',
					'_zc_price'             => 0, // کاملاً رایگان.
					'_zc_sale_price'        => 0,
					'_zc_level'             => $c['level'],
					'_zc_teacher'           => $c['teacher'],
					'_zc_students'          => wp_rand( 120, 2400 ),
					'_zc_rating'            => 4.8,
					'_zc_rating_count'      => wp_rand( 60, 800 ),
					'_zc_course_status'     => 'completed',
					'_zc_duration'          => wp_rand( 8, 40 ) . ' ساعت',
					'_zc_access_days'       => 0,
					'_zc_free'              => 1,
					'_zc_curriculum'        => zc_tech_curriculum( $key ),
					'_zc_quiz'              => zc_tech_quiz( $key ),
					'_zc_course_practice'   => zc_tech_practice( $key ),
					'_zc_quiz_langs'        => $langs,
					'_zc_features'          => array(
						'کاملاً رایگان و مادام‌العمر',
						'از صفر تا پیشرفته با مثال واقعی',
						'آزمون آنلاین و تمرین چالشی در پایان هر فصل',
						'پشتیبانی مستقیم در تیکتینگ زرین کد',
						'گواهی پایان دوره',
						'به‌روزرسانی رایگان بر اساس مستندات رسمی',
					),
					'_zc_prerequisites' => 'آشنایی مقدماتی با کامپیوتر کافی است؛ تمام مباحث از پایه توضیح داده می‌شود.',
					'_zc_audience'      => 'مبتدیان، دانشجویان و توسعه‌دهندگانی که می‌خواهند ' . $c['title'] . ' را به‌صورت حرفه‌ای یاد بگیرند.',
				),
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			// اختصاص مدرس.
			if ( isset( $teachers[ $c['teacher'] ] ) ) {
				update_post_meta( $id, '_zc_teacher_id', $teachers[ $c['teacher'] ] );
			}
			// اختصاص دسته.
			if ( isset( $cat_ids[ $c['cat'] ] ) ) {
				wp_set_object_terms( $id, (int) $cat_ids[ $c['cat'] ], 'zc_course_cat' );
			}
			// تصویر دوره.
			zc_demo_attach_image( $id, zc_demo_image_for( $img_i++ ) );
			$report['created'][] = $key;
		}
	}

	return $report;
}

/**
 * رندر یک بلوک کد با نحو (syntax) تمیز در HTML.
 *
 * @param string $lang زبان برای برچسب.
 * @param string $code کد.
 * @return string
 */
function zc_tech_code_block( $lang, $code ) {
	return '<div class="zc-code-block"><div class="zc-code-block__head">' . esc_html( $lang ) . '</div><pre class="zc-code-block__pre"><code>' . esc_html( $code ) . '</code></pre></div>';
}

/**
 * محتوای کامل درسی برای هر دوره بر اساس سرفصل‌ها.
 * هر ورودی شامل: فصل (title) و درس‌ها؛ هر درس متن فارسی + کد.
 *
 * @param string $key کلید دوره.
 * @return array
 */
function zc_tech_full_lessons( $key ) {
	$map = array();

	$map['html-css'] = array(
		array(
			'title' => 'فصل اول: مبانی HTML',
			'items' => array(
				array(
					'h' => 'ساختار یک سند HTML',
					't' => 'هر سند HTML با تگ <!DOCTYPE html> شروع می‌شود و شامل دو بخش اصلی <head> و <body> است. <head> اطلاعات فراداده و <body> محتوای قابل نمایش را در خود جای می‌دهد. این ساختار استاندارد، پایه‌ی هر وب‌سایتی است.',
					'lang' => 'html', 'code' => "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>اولین صفحه</title>\n</head>\n<body>\n    <h1>سلام دنیا!</h1>\n    <p>این اولین صفحه‌ی من است.</p>\n</body>\n</html>",
				),
				array(
					'h' => 'تگ‌های متنی و لیست‌ها',
					't' => 'تگ‌های معنایی مثل <header>، <nav>، <main> و <footer> به مرورگر و موتور جستجو کمک می‌کنند ساختار صفحه را بهتر بفهمند. لیست‌ها با <ul> (نامرتب) و <ol> (مرتب) ساخته می‌شوند.',
					'lang' => 'html', 'code' => "<main>\n    <h2>مهارت‌های من</h2>\n    <ul>\n        <li>HTML</li>\n        <li>CSS</li>\n        <li>JavaScript</li>\n    </ul>\n</main>",
				),
			),
		),
		array(
			'title' => 'فصل دوم: فرم‌ها و ورودی',
			'items' => array(
				array(
					'h' => 'ساخت فرم استاندارد',
					't' => 'فرم‌ها با تگ <form> ساخته می‌شوند و برای جمع‌آوری ورودی کاربر استفاده می‌شوند. هر فیلد باید دارای ویژگی name باشد تا داده هنگام ارسال قابل تشخیص باشد.',
					'lang' => 'html', 'code' => "<form action=\"/submit\" method=\"post\">\n    <label for=\"email\">ایمیل:</label>\n    <input type=\"email\" id=\"email\" name=\"email\" required>\n    <button type=\"submit\">ارسال</button>\n</form>",
				),
			),
		),
		array(
			'title' => 'فصل سوم: CSS از پایه',
			'items' => array(
				array(
					'h' => 'انتخاب‌گرها و مدل جعبه',
					't' => 'CSS برای استایل‌دهی به عناصر استفاده می‌شود. مدل جعبه (Box Model) شامل margin، border، padding و content است و اساس چیدمان در CSS محسوب می‌شود.',
					'lang' => 'css', 'code' => ".card {\n    margin: 20px auto;\n    padding: 24px;\n    border: 1px solid #e0e0e0;\n    border-radius: 12px;\n    max-width: 480px;\n    background: #fff;\n}\n\n.card h2 { color: #1f2937; }",
				),
				array(
					'h' => 'Flexbox',
					't' => 'Flexbox یک روش مدرن برای چیدمان عناصر در یک ردیف یا ستون است. با display:flex و ویژگی‌هایی مثل justify-content و align-items می‌توان چیدمان را کنترل کرد.',
					'lang' => 'css', 'code' => ".navbar {\n    display: flex;\n    justify-content: space-between;\n    align-items: center;\n    padding: 16px 24px;\n}",
				),
			),
		),
		array(
			'title' => 'فصل چهارم: چیدمان مدرن',
			'items' => array(
				array(
					'h' => 'CSS Grid',
					't' => 'Grid سیستم چیدمانی دو‌بعدی است که برای طراحی صفحات پیچیده‌تر عالی است. با grid-template-columns تعداد ستون‌ها را تعیین می‌کنید.',
					'lang' => 'css', 'code' => ".grid {\n    display: grid;\n    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));\n    gap: 24px;\n}",
				),
				array(
					'h' => 'طراحی واکنش‌گرا',
					't' => 'با Media Query می‌توان استایل را بر اساس اندازه‌ی صفحه تغییر داد تا سایت در موبایل، تبلت و دسکتاپ به‌درستی نمایش داده شود.',
					'lang' => 'css', 'code' => "@media (max-width: 768px) {\n    .navbar {\n        flex-direction: column;\n        gap: 12px;\n    }\n}",
				),
			),
		),
	);

	$map['javascript'] = array(
		array(
			'title' => 'فصل اول: مبانی جاوااسکریپت',
			'items' => array(
				array(
					'h' => 'متغیرها و انواع داده',
					't' => 'در جاوااسکریپت مدرن، const برای متغیرهای ثابت و let برای متغیرهای تغییرپذیر استفاده می‌شود. انواع داده شامل number، string، boolean، array و object است.',
					'lang' => 'javascript', 'code' => "const name = \"زرین کد\";\nlet count = 10;\nconst isActive = true;\nconst skills = [\"HTML\", \"CSS\", \"JS\"];\n\nconsole.log(name, count, isActive, skills);",
				),
				array(
					'h' => 'توابع و آرگومان‌ها',
					't' => 'توابع بلوک‌های قابل استفاده‌ی مجدد هستند. Arrow function سبک مدرن و خلاصه‌ای برای تعریف تابع است.',
					'lang' => 'javascript', 'code' => "const sum = (a, b) => a + b;\n\nfunction greet(user) {\n    return `سلام ${user}!`;\n}\n\nconsole.log(sum(5, 3));   // 8\nconsole.log(greet(\"رضا\"));",
				),
			),
		),
		array(
			'title' => 'فصل دوم: DOM و رویدادها',
			'items' => array(
				array(
					'h' => 'کار با DOM',
					't' => 'DOM به جاوااسکریپت امکان می‌دهد عناصر صفحه را انتخاب و تغییر دهد. متد querySelector انتخاب‌گرهای CSS را می‌پذیرد.',
					'lang' => 'javascript', 'code' => "const button = document.querySelector(\"#btn\");\nconst output = document.querySelector(\"#output\");\n\nbutton.addEventListener(\"click\", () => {\n    output.textContent = \"دکمه کلیک شد!\";\n});",
				),
			),
		),
		array(
			'title' => 'فصل سوم: جاوااسکریپت مدرن',
			'items' => array(
				array(
					'h' => 'Promises و async/await',
					't' => 'برای کار با عملیات ناهمزمان (مثل فراخوانی API) از Promises و async/await استفاده می‌شود که کد را خواناتر و قابل‌نگهداری‌تر می‌کند.',
					'lang' => 'javascript', 'code' => "async function fetchUsers() {\n    try {\n        const res = await fetch(\"https://api.example.com/users\");\n        const users = await res.json();\n        console.log(users);\n    } catch (err) {\n        console.error(\"خطا:\", err);\n    }\n}",
				),
			),
		),
	);

	$map['python'] = array(
		array(
			'title' => 'فصل اول: مبانی پایتون',
			'items' => array(
				array(
					'h' => 'متغیرها و انواع داده',
					't' => 'پایتون زبانی با تایپ پویا است؛ یعنی نیازی به اعلام نوع متغیر نیست. انواع اصلی شامل int، float، str، list، tuple، dict و set است.',
					'lang' => 'python', 'code' => "name = \"زرین کد\"\nage = 15\nscore = 9.5\nskills = [\"Python\", \"Django\", \"SQL\"]\ninfo = {\"name\": name, \"age\": age}\n\nprint(name, age, score, skills, info)",
				),
				array(
					'h' => 'شرط‌ها و حلقه‌ها',
					't' => 'کنترل جریان با if/elif/else و تکرار با for و while انجام می‌شود. پایتون بلاک‌ها را با فاصله‌گذاری (indentation) مشخص می‌کند.',
					'lang' => 'python', 'code' => "def is_even(n):\n    return n % 2 == 0\n\nfor i in range(1, 11):\n    if is_even(i):\n        print(f\"{i} زوج است\")\n    else:\n        print(f\"{i} فرد است\")",
				),
			),
		),
		array(
			'title' => 'فصل دوم: توابع و ساختارهای داده',
			'items' => array(
				array(
					'h' => 'لیست و دیکشنری',
					't' => 'لیست‌ها مجموعه‌ی مرتب و تغییرپذیر هستند. دیکشنری‌ها برای نگاشت کلید به مقدار استفاده می‌شوند. کامپرهنشن روشی خلاصه برای ساخت مجموعه است.',
					'lang' => 'python', 'code' => "numbers = [1, 2, 3, 4, 5]\nsquares = [n ** 2 for n in numbers]\n\nstudent = {\"name\": \"علی\", \"gpa\": 18.5}\nprint(squares)\nprint(student[\"name\"])",
				),
				array(
					'h' => 'مدیریت خطا',
					't' => 'با try/except می‌توان خطاها را مدیریت کرد تا برنامه به‌جای متوقف شدن، پیام مناسب نشان دهد.',
					'lang' => 'python', 'code' => "try:\n    num = int(input(\"یک عدد وارد کنید: \"))\n    print(10 / num)\nexcept ValueError:\n    print(\"ورودی معتبر نیست.\")\nexcept ZeroDivisionError:\n    print(\"تقسیم بر صفر مجاز نیست.\")",
				),
			),
		),
		array(
			'title' => 'فصل سوم: شیءگرایی',
			'items' => array(
				array(
					'h' => 'کلاس و آبجکت',
					't' => 'شیءگرایی به سازمان‌دهی کد کمک می‌کند. کلاس یک قالب است و آبجکت نمونه‌ای از آن. متدها رفتار کلاس را تعریف می‌کنند.',
					'lang' => 'python', 'code' => "class Student:\n    def __init__(self, name, grade):\n        self.name = name\n        self.grade = grade\n\n    def is_passed(self):\n        return self.grade >= 10\n\ns = Student(\"مریم\", 17)\nprint(f\"{s.name}: {'قبول' if s.is_passed() else 'رد'}\")",
				),
			),
		),
		array(
			'title' => 'فصل چهارم: کار با داده',
			'items' => array(
				array(
					'h' => 'فایل و NumPy',
					't' => 'کار با فایل برای ذخیره‌سازی داده ضروری است. NumPy کتابخانه‌ای قدرتمند برای عملیات عددی و آرایه‌های چندبعدی است.',
					'lang' => 'python', 'code' => "import numpy as np\n\n# کار با فایل\nwith open(\"data.txt\", \"w\") as f:\n    f.write(\"hello zarincode\\n\")\n\n# عملیات آرایه‌ای\narr = np.array([1, 2, 3, 4])\nprint(arr * 2, arr.mean())",
				),
			),
		),
	);

	$map['java'] = array(
		array(
			'title' => 'فصل اول: مبانی جاوا',
			'items' => array(
				array(
					'h' => 'ساختار یک برنامه',
					't' => 'جاوا زبانی شیءگرا و تایپ‌ایستا است. نقطه‌ی شروع هر برنامه، متد main است. همه‌چیز داخل کلاس تعریف می‌شود.',
					'lang' => 'java', 'code' => "public class Main {\n    public static void main(String[] args) {\n        String name = \"زرین کد\";\n        int count = 10;\n        System.out.println(name + \" - \" + count);\n    }\n}",
				),
				array(
					'h' => 'شرط و حلقه',
					't' => 'کنترل جریان در جاوا با if/else، switch و حلقه‌های for/while انجام می‌شود.',
					'lang' => 'java', 'code' => "for (int i = 1; i <= 10; i++) {\n    if (i % 2 == 0) {\n        System.out.println(i + \" زوج\");\n    }\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: شیءگرایی',
			'items' => array(
				array(
					'h' => 'کلاس و وراثت',
					't' => 'کپسوله‌سازی با private و getter/setter انجام می‌شود. وراثت با extends و رابط‌ها با implements پیاده می‌شوند.',
					'lang' => 'java', 'code' => "class Animal {\n    protected String name;\n    public Animal(String name) { this.name = name; }\n    public void speak() { System.out.println(\"...\"); }\n}\n\nclass Dog extends Animal {\n    public Dog(String name) { super(name); }\n    @Override\n    public void speak() { System.out.println(name + \" says Woof\"); }\n}",
				),
			),
		),
		array(
			'title' => 'فصل سوم: ساختارهای داده',
			'items' => array(
				array(
					'h' => 'Collections',
					't' => 'فریم‌ورک Collections شامل ArrayList، HashMap و Set است و مدیریت مجموعه‌های داده را آسان می‌کند.',
					'lang' => 'java', 'code' => "import java.util.*;\n\npublic class Main {\n    public static void main(String[] args) {\n        List<String> list = new ArrayList<>(List.of(\"HTML\", \"CSS\"));\n        list.add(\"JS\");\n        Map<String, Integer> scores = new HashMap<>();\n        scores.put(\"python\", 95);\n        System.out.println(list + \" \" + scores);\n    }\n}",
				),
			),
		),
		array(
			'title' => 'فصل چهارم: Streams و Lambda',
			'items' => array(
				array(
					'h' => 'برنامه‌نویسی تابعی',
					't' => 'استریم‌ها پردازش داده را به‌صورت اعلانی و زنجیره‌ای ممکن می‌کنند. لامبداها توابع ناشناس هستند.',
					'lang' => 'java', 'code' => "import java.util.*;\nimport java.util.stream.*;\n\nList<Integer> nums = List.of(1,2,3,4,5,6);\nint sumEven = nums.stream()\n    .filter(n -> n % 2 == 0)\n    .mapToInt(Integer::intValue)\n    .sum();\nSystem.out.println(sumEven);",
				),
			),
		),
	);

	$map['sql'] = array(
		array(
			'title' => 'فصل اول: مبانی SQL',
			'items' => array(
				array(
					'h' => 'SELECT و WHERE',
					't' => 'SQL زبان استاندارد برای کار با پایگاه‌های داده‌ی رابطه‌ای است. SELECT برای خواندن و WHERE برای فیلتر کردن داده استفاده می‌شود.',
					'lang' => 'sql', 'code' => "SELECT name, email\nFROM users\nWHERE status = 'active'\nORDER BY created_at DESC\nLIMIT 10;",
				),
				array(
					'h' => 'توابع تجمیعی',
					't' => 'توابعی مثل COUNT، SUM، AVG برای خلاصه‌سازی داده استفاده می‌شوند. GROUP BY داده را دسته‌بندی می‌کند.',
					'lang' => 'sql', 'code' => "SELECT department, COUNT(*) AS employees, AVG(salary) AS avg_salary\nFROM staff\nGROUP BY department\nHAVING COUNT(*) > 5;",
				),
			),
		),
		array(
			'title' => 'فصل دوم: JOIN و زیرکوئری',
			'items' => array(
				array(
					'h' => 'اتصال جداول',
					't' => 'JOIN برای ترکیب داده از چند جدول استفاده می‌شود. INNER JOIN فقط ردیف‌های منطبق را برمی‌گرداند.',
					'lang' => 'sql', 'code' => "SELECT o.id, c.name AS customer, o.total\nFROM orders o\nJOIN customers c ON o.customer_id = c.id\nWHERE o.total > 1000;",
				),
			),
		),
		array(
			'title' => 'فصل سوم: بهینه‌سازی و امنیت',
			'items' => array(
				array(
					'h' => 'ایندکس و Transaction',
					't' => 'ایندکس‌ها سرعت جستجو را افزایش می‌دهند. تراکنش‌ها (Transaction) تضمین می‌کنند مجموعه‌ای از عملیات اتمی اجرا شوند.',
					'lang' => 'sql', 'code' => "START TRANSACTION;\nUPDATE accounts SET balance = balance - 100 WHERE id = 1;\nUPDATE accounts SET balance = balance + 100 WHERE id = 2;\nCOMMIT;\n\nCREATE INDEX idx_users_email ON users(email);",
				),
			),
		),
	);

	$map['mysql'] = array(
		array(
			'title' => 'فصل اول: طراحی پایگاه داده',
			'items' => array(
				array(
					'h' => 'ایجاد جدول',
					't' => 'MySQL یکی از محبوب‌ترین پایگاه‌های داده‌ی متن‌باز است. با CREATE TABLE و تعریف کلید اصلی، ساختار داده را مشخص می‌کنید.',
					'lang' => 'sql', 'code' => "CREATE TABLE products (\n    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n    name VARCHAR(150) NOT NULL,\n    price DECIMAL(10,2) NOT NULL,\n    stock INT UNSIGNED DEFAULT 0,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n);",
				),
				array(
					'h' => 'روابط و کلید خارجی',
					't' => 'کلید خارجی (FOREIGN KEY) رابطه بین جداول را برقرار می‌کند و یکپارچگی داده را تضمین می‌کند.',
					'lang' => 'sql', 'code' => "CREATE TABLE orders (\n    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n    product_id INT UNSIGNED NOT NULL,\n    quantity INT NOT NULL,\n    FOREIGN KEY (product_id) REFERENCES products(id)\n);",
				),
			),
		),
		array(
			'title' => 'فصل دوم: مدیریت داده',
			'items' => array(
				array(
					'h' => 'CRUD',
					't' => 'چهار عملیات اصلی: ایجاد (INSERT)، خواندن (SELECT)، به‌روزرسانی (UPDATE) و حذف (DELETE).',
					'lang' => 'sql', 'code' => "INSERT INTO products (name, price) VALUES ('قالب وردپرس', 1490000);\n\nUPDATE products SET stock = stock + 10 WHERE id = 1;\n\nDELETE FROM products WHERE id = 2;",
				),
			),
		),
		array(
			'title' => 'فصل سوم: بهینه‌سازی',
			'items' => array(
				array(
					'h' => 'ایندکس و پشتیبان‌گیری',
					't' => 'برای پایگاه‌داده‌های بزرگ، ایندکس و پشتیبان‌گیری منظم حیاتی است. EXPLAIN برای تحلیل عملکرد کوئری استفاده می‌شود.',
					'lang' => 'sql', 'code' => "EXPLAIN SELECT * FROM orders WHERE product_id = 5;\n\n-- پشتیبان‌گیری از طریق ترمینال\n-- mysqldump -u root mydb > backup.sql",
				),
			),
		),
	);

	$map['bootstrap'] = array(
		array(
			'title' => 'فصل اول: سیستم Grid',
			'items' => array(
				array(
					'h' => 'ساختار Grid',
					't' => 'بوت‌استرپ ۵ یک فریم‌ورک CSS محبوب است. سیستم Grid بر پایه‌ی ۱۲ ستون است و با کلاس‌هایی مثل col-md-6 چیدمان واکنش‌گرا می‌سازد.',
					'lang' => 'html', 'code' => "<div class=\"container\">\n  <div class=\"row g-4\">\n    <div class=\"col-md-6 col-lg-4\">\n      <div class=\"card p-3\">کارت ۱</div>\n    </div>\n    <div class=\"col-md-6 col-lg-4\">\n      <div class=\"card p-3\">کارت ۲</div>\n    </div>\n  </div>\n</div>",
				),
			),
		),
		array(
			'title' => 'فصل دوم: کامپوننت‌ها',
			'items' => array(
				array(
					'h' => 'دکمه و فرم',
					't' => 'بوت‌استرپ کامپوننت‌های آماده دارد. کلاس‌های btn، form-control و navbar سرعت توسعه را به‌شدت افزایش می‌دهند.',
					'lang' => 'html', 'code' => "<div class=\"mb-3\">\n  <label class=\"form-label\">نام</label>\n  <input type=\"text\" class=\"form-control\" placeholder=\"نام شما\">\n</div>\n<button class=\"btn btn-primary\">ثبت</button>\n<button class=\"btn btn-outline-secondary\">انصراف</button>",
				),
				array(
					'h' => 'مودال',
					't' => 'مودال یک پنجره‌ی شناور برای دیالوگ‌هاست که با جاوااسکریپت بوت‌استرپ فعال می‌شود.',
					'lang' => 'html', 'code' => "<button class=\"btn btn-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#myModal\">باز کردن</button>\n\n<div class=\"modal fade\" id=\"myModal\">\n  <div class=\"modal-dialog\">\n    <div class=\"modal-content\">\n      <div class=\"modal-header\"><h5>عنوان</h5></div>\n      <div class=\"modal-body\">محتوای مودال</div>\n    </div>\n  </div>\n</div>",
				),
			),
		),
	);

	$map['wordpress'] = array(
		array(
			'title' => 'فصل اول: توسعه قالب',
			'items' => array(
				array(
					'h' => 'ساختار یک قالب',
					't' => 'قالب وردپرس شامل فایل‌هایی مثل style.css، functions.php و index.php است. فایل style.css شامل هدر اطلاعات قالب است.',
					'lang' => 'php', 'code' => "<?php\n/*\nTheme Name: My Theme\nTheme URI: https://zarincode.com\nAuthor: Zarincode\nVersion: 1.0\n*/\n?>\n\n<?php get_header(); ?>\n<main>\n  <?php if ( have_posts() ) : ?>\n    <?php while ( have_posts() ) : the_post(); ?>\n      <article>\n        <h1><?php the_title(); ?></h1>\n        <div><?php the_content(); ?></div>\n      </article>\n    <?php endwhile; ?>\n  <?php endif; ?>\n</main>\n<?php get_footer(); ?>",
				),
				array(
					'h' => 'هوک و فانکشن',
					't' => 'هوک‌ها (actions و filters) به توسعه‌دهنده اجازه می‌دهند بدون تغییر هسته، به وردپرس قابلیت اضافه کنند.',
					'lang' => 'php', 'code' => "<?php\n// افزودن استایل به قالب\nfunction my_theme_assets() {\n    wp_enqueue_style('main', get_stylesheet_uri());\n}\nadd_action('wp_enqueue_scripts', 'my_theme_assets');\n\n// ثبت منو\nfunction my_theme_setup() {\n    register_nav_menus(array('primary' => __('منوی اصلی')));\n}\nadd_action('after_setup_theme', 'my_theme_setup');",
				),
			),
		),
		array(
			'title' => 'فصل دوم: افزونه‌نویسی',
			'items' => array(
				array(
					'h' => 'ساخت افزونه',
					't' => 'افزونه وردپرس یک فایل PHP با هدر مشخص است. برای افزودن کوتاه‌کد (shortcode) از add_shortcode استفاده می‌شود.',
					'lang' => 'php', 'code' => <<<'CODE'
<?php
/**
 * Plugin Name: Zarincode Helper
 * Description: افزودن کوتاه‌کد ساده
 */

function zc_greet_shortcode($atts) {
    $atts = shortcode_atts(array('name' => 'کاربر'), $atts);
    return '<p>سلام ' . esc_html($atts['name']) . '!</p>';
}
add_shortcode('greet', 'zc_greet_shortcode');
CODE,
				),
			),
		),
	);

	$map['django'] = array(
		array(
			'title' => 'فصل اول: مدل و ORM',
			'items' => array(
				array(
					'h' => 'تعریف مدل',
					't' => 'Django یک فریم‌ورک وب پایتون است که از معماری MVT پیروی می‌کند. مدل‌ها با کلاس پایتون تعریف و به جدول دیتابیس تبدیل می‌شوند.',
					'lang' => 'python', 'code' => "# models.py\nfrom django.db import models\n\nclass Category(models.Model):\n    name = models.CharField(max_length=100)\n\nclass Course(models.Model):\n    title = models.CharField(max_length=200)\n    category = models.ForeignKey(Category, on_delete=models.CASCADE)\n    price = models.DecimalField(max_digits=10, decimal_places=2)\n\n    def __str__(self):\n        return self.title",
				),
				array(
					'h' => 'ویو و قالب',
					't' => 'ویوها درخواست را دریافت و پاسخ را برمی‌گردانند. قالب‌ها HTML با سینتکس مخصوص جنگو هستند.',
					'lang' => 'python', 'code' => "# views.py\nfrom django.shortcuts import render\nfrom .models import Course\n\ndef course_list(request):\n    courses = Course.objects.filter(price__gt=0)\n    return render(request, 'courses/list.html', {'courses': courses})",
				),
			),
		),
		array(
			'title' => 'فصل دوم: فرم و احراز هویت',
			'items' => array(
				array(
					'h' => 'فرم‌ها',
					't' => 'فرم‌های جنگو اعتبارسنجی و رندر را خودکار انجام می‌دهند. با ModelForm می‌توان از روی مدل فرم ساخت.',
					'lang' => 'python', 'code' => "# forms.py\nfrom django import forms\nfrom .models import Course\n\nclass CourseForm(forms.ModelForm):\n    class Meta:\n        model = Course\n        fields = ['title', 'category', 'price']",
				),
			),
		),
	);

	$map['aspnet'] = array(
		array(
			'title' => 'فصل اول: مبانی ASP.NET Core',
			'items' => array(
				array(
					'h' => 'ساختار MVC',
					't' => 'ASP.NET Core فریم‌ورک وب مایکروسافت است که از معماری MVC پیروی می‌کند. کنترلرها درخواست را پردازش و ویوها HTML تولید می‌کنند.',
					'lang' => 'csharp', 'code' => "public class HomeController : Controller\n{\n    public IActionResult Index()\n    {\n        var courses = _db.Courses.ToList();\n        return View(courses);\n    }\n}",
				),
				array(
					'h' => 'Dependency Injection',
					't' => 'DI برای مدیریت وابستگی‌ها استفاده می‌شود و کد را قابل‌تست و قابل‌نگهداری می‌کند.',
					'lang' => 'csharp', 'code' => "builder.Services.AddControllersWithViews();\nbuilder.Services.AddDbContext<AppDbContext>(opts =>\n    opts.UseSqlServer(builder.Configuration.GetConnectionString(\"Default\")));",
				),
			),
		),
		array(
			'title' => 'فصل دوم: EF Core',
			'items' => array(
				array(
					'h' => 'مدل و کوئری',
					't' => 'Entity Framework Core یک ORM برای دات‌نت است. با LINQ کوئری می‌نویسید و EF آن را به SQL ترجمه می‌کند.',
					'lang' => 'csharp', 'code' => "public class Course\n{\n    public int Id { get; set; }\n    public string Title { get; set; }\n    public decimal Price { get; set; }\n}\n\nvar free = _db.Courses.Where(c => c.Price == 0).ToList();",
				),
			),
		),
	);

	$map['react'] = array(
		array(
			'title' => 'فصل اول: کامپوننت‌ها',
			'items' => array(
				array(
					'h' => 'کامپوننت و JSX',
					't' => 'React کتابخانه‌ای برای ساخت رابط کاربری است. همه‌چیز با کامپوننت ساخته می‌شود و JSX ترکیب HTML و جاوااسکریپت است.',
					'lang' => 'javascript', 'code' => "function CourseCard({ title, price }) {\n  return (\n    <div className=\"card\">\n      <h3>{title}</h3>\n      <p>{price === 0 ? 'رایگان' : price + ' تومان'}</p>\n    </div>\n  );\n}\n\nexport default function App() {\n  return <CourseCard title=\"Python\" price={0} />;\n}",
				),
				array(
					'h' => 'Hooks',
					't' => 'هوک‌ها در نسخه‌های مدرن React برای مدیریت state و افکت‌ها استفاده می‌شوند.',
					'lang' => 'javascript', 'code' => "import { useState, useEffect } from 'react';\n\nfunction Counter() {\n  const [count, setCount] = useState(0);\n\n  useEffect(() => {\n    document.title = `Count: ${count}`;\n  }, [count]);\n\n  return <button onClick={() => setCount(c => c + 1)}>{count}</button>;\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: Router و API',
			'items' => array(
				array(
					'h' => 'فراخوانی API',
					't' => 'با fetch یا axios می‌توان داده را از API دریافت کرد. useEffect برای بارگذاری اولیه مناسب است.',
					'lang' => 'javascript', 'code' => "import { useState, useEffect } from 'react';\n\nfunction Users() {\n  const [users, setUsers] = useState([]);\n\n  useEffect(() => {\n    fetch('https://api.example.com/users')\n      .then(r => r.json())\n      .then(setUsers);\n  }, []);\n\n  return <ul>{users.map(u => <li key={u.id}>{u.name}</li>)}</ul>;\n}",
				),
			),
		),
	);

	$map['nodejs'] = array(
		array(
			'title' => 'فصل اول: مبانی Node.js',
			'items' => array(
				array(
					'h' => 'ماژول‌ها',
					't' => 'Node.js اجازه می‌دهد جاوااسکریپت در سمت سرور اجرا شود. ماژول‌ها با require و در نسخه‌های جدید با import استفاده می‌شوند.',
					'lang' => 'javascript', 'code' => "const http = require('http');\n\nconst server = http.createServer((req, res) => {\n  res.writeHead(200, { 'Content-Type': 'text/plain' });\n  res.end('سلام از زرین کد!');\n});\n\nserver.listen(3000, () => console.log('Server on 3000'));",
				),
				array(
					'h' => 'Express',
					't' => 'Express محبوب‌ترین فریم‌ورک وب Node.js است و ساخت API را ساده می‌کند.',
					'lang' => 'javascript', 'code' => "const express = require('express');\nconst app = express();\napp.use(express.json());\n\napp.get('/api/courses', (req, res) => {\n  res.json([{ id: 1, title: 'Python' }]);\n});\n\napp.listen(3000);",
				),
			),
		),
		array(
			'title' => 'فصل دوم: API و دیتابیس',
			'items' => array(
				array(
					'h' => 'REST API',
					't' => 'یک REST API کامل با عملیات CRUD با Express ساخته می‌شود.',
					'lang' => 'javascript', 'code' => "app.post('/api/courses', (req, res) => {\n  const { title } = req.body;\n  if (!title) return res.status(400).json({ error: 'title required' });\n  const course = { id: Date.now(), title };\n  res.status(201).json(course);\n});",
				),
			),
		),
	);

	$map['c'] = array(
		array(
			'title' => 'فصل اول: مبانی C',
			'items' => array(
				array(
					'h' => 'ساختار برنامه',
					't' => 'زبان C یک زبان سطح پایین و قدرتمند است. برنامه با تابع main شروع می‌شود و برای چاپ از printf استفاده می‌شود.',
					'lang' => 'c', 'code' => "#include <stdio.h>\n\nint main() {\n    int count = 10;\n    printf(\"تعداد: %d\\n\", count);\n    return 0;\n}",
				),
				array(
					'h' => 'اشاره‌گر',
					't' => 'اشاره‌گر (Pointer) آدرس حافظه را نگه می‌دارد و قدرت زبان C را نشان می‌دهد.',
					'lang' => 'c', 'code' => "#include <stdio.h>\n\nint main() {\n    int x = 42;\n    int *ptr = &x;\n    printf(\"مقدار: %d\\n\", *ptr);\n    *ptr = 100;\n    printf(\"مقدار جدید: %d\\n\", x);\n    return 0;\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: ساختار و حافظه',
			'items' => array(
				array(
					'h' => 'تخصیص حافظه',
					't' => 'برای تخصیص پویای حافظه از malloc و free استفاده می‌شود.',
					'lang' => 'c', 'code' => "#include <stdio.h>\n#include <stdlib.h>\n\nint main() {\n    int n = 5;\n    int *arr = malloc(n * sizeof(int));\n    for (int i = 0; i < n; i++) arr[i] = i * i;\n    for (int i = 0; i < n; i++) printf(\"%d \", arr[i]);\n    free(arr);\n    return 0;\n}",
				),
			),
		),
	);

	$map['cpp'] = array(
		array(
			'title' => 'فصل اول: مبانی C++',
			'items' => array(
				array(
					'h' => 'ورودی/خروجی',
					't' => 'C++ یک زبان شیءگرا و توسعه‌یافته‌ی C است. با cout و cin ورودی/خروجی انجام می‌شود.',
					'lang' => 'cpp', 'code' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    int a, b;\n    cin >> a >> b;\n    cout << \"جمع: \" << (a + b) << endl;\n    return 0;\n}",
				),
				array(
					'h' => 'کلاس و وراثت',
					't' => 'شیءگرایی در C++ با class پیاده می‌شود و وراثت با کاراکتر : انجام می‌شود.',
					'lang' => 'cpp', 'code' => "#include <iostream>\nusing namespace std;\n\nclass Shape {\npublic:\n    virtual double area() = 0;\n};\n\nclass Circle : public Shape {\n    double r;\npublic:\n    Circle(double r) : r(r) {}\n    double area() override { return 3.14159 * r * r; }\n};\n\nint main() {\n    Circle c(2.0);\n    cout << c.area() << endl;\n    return 0;\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: STL',
			'items' => array(
				array(
					'h' => 'Container های مدرن',
					't' => 'کتابخانه‌ی استاندارد (STL) شامل vector، map و string است و کار با داده را ساده می‌کند.',
					'lang' => 'cpp', 'code' => "#include <iostream>\n#include <vector>\n#include <algorithm>\nusing namespace std;\n\nint main() {\n    vector<int> nums = {5, 2, 8, 1};\n    sort(nums.begin(), nums.end());\n    for (int n : nums) cout << n << \" \";\n    return 0;\n}",
				),
			),
		),
	);

	$map['csharp'] = array(
		array(
			'title' => 'فصل اول: مبانی C#',
			'items' => array(
				array(
					'h' => 'ساختار برنامه',
					't' => 'C# یک زبان شیءگرا برای اکوسیستم دات‌نت است. با Console.WriteLine خروجی می‌گیریم.',
					'lang' => 'csharp', 'code' => "using System;\n\nclass Program\n{\n    static void Main()\n    {\n        string name = \"زرین کد\";\n        int count = 10;\n        Console.WriteLine($\"{name} - {count}\");\n    }\n}",
				),
				array(
					'h' => 'LINQ',
					't' => 'LINQ پرس‌وجو بر روی داده را به‌صورت یکپارچه در C# ممکن می‌کند.',
					'lang' => 'csharp', 'code' => "using System;\nusing System.Linq;\nusing System.Collections.Generic;\n\nvar nums = new List<int> { 1,2,3,4,5,6 };\nvar evens = nums.Where(n => n % 2 == 0).Sum();\nConsole.WriteLine(evens);",
				),
			),
		),
		array(
			'title' => 'فصل دوم: async و ژنریک',
			'items' => array(
				array(
					'h' => 'برنامه‌نویسی async',
					't' => 'با async/await می‌توان عملیات ناهمزمان را بدون مسدود کردن اجرا کرد.',
					'lang' => 'csharp', 'code' => "using System;\nusing System.Threading.Tasks;\n\nasync Task<string> GetDataAsync()\n{\n    await Task.Delay(100);\n    return \"data\";\n}\n\nvar result = await GetDataAsync();\nConsole.WriteLine(result);",
				),
			),
		),
	);

	$map['go'] = array(
		array(
			'title' => 'فصل اول: مبانی Go',
			'items' => array(
				array(
					'h' => 'ساختار برنامه',
					't' => 'Go یک زبان سریع و همزمان از گوگل است. با پکیج fmt چاپ می‌کنیم و سینتکس آن تمیز و ساده است.',
					'lang' => 'go', 'code' => "package main\n\nimport \"fmt\"\n\nfunc main() {\n    name := \"زرین کد\"\n    fmt.Printf(\"سلام %s\\n\", name)\n}",
				),
				array(
					'h' => 'گوروتین',
					't' => 'گوروتین‌ها (goroutine) راهی سبک برای اجرای همزمان توابع هستند و با کلیدواژه‌ی go شروع می‌شوند.',
					'lang' => 'go', 'code' => "package main\n\nimport (\n\t\"fmt\"\n\t\"time\"\n)\n\nfunc print(msg string) {\n\tfor i := 0; i < 3; i++ {\n\t\tfmt.Println(msg)\n\t\ttime.Sleep(100 * time.Millisecond)\n\t}\n}\n\nfunc main() {\n\tgo print(\"goroutine\")\n\tprint(\"main\")\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: رابط و کانال',
			'items' => array(
				array(
					'h' => 'رابط (Interface)',
					't' => 'رابط‌ها در Go رفتار را تعریف می‌کنند و به‌صورت ضمنی پیاده می‌شوند.',
					'lang' => 'go', 'code' => "package main\n\nimport \"fmt\"\n\ntype Speaker interface {\n\tSpeak() string\n}\n\ntype Dog struct{ Name string }\n\nfunc (d Dog) Speak() string { return d.Name + \" woof\" }\n\nfunc main() {\n\tvar s Speaker = Dog{Name: \"Rex\"}\n\tfmt.Println(s.Speak())\n}",
				),
			),
		),
	);

	$map['git-github'] = array(
		array(
			'title' => 'فصل اول: مبانی Git',
			'items' => array(
				array(
					'h' => 'مخزن و commit',
					't' => 'Git یک سیستم کنترل نسخه‌ی توزیع‌شده است. با git init مخزن می‌سازیم و با commit تغییرات را ثبت می‌کنیم.',
					'lang' => 'bash', 'code' => "git init\n# افزودن فایل و ثبت اولین commit\ngit add .\ngit commit -m \"پروژه اولیه زرین کد\"\n\n# مشاهده وضعیت و تاریخچه\ngit status\ngit log --oneline",
				),
				array(
					'h' => 'شاخه و ادغام',
					't' => 'شاخه‌ها (branch) امکان توسعه‌ی موازی را می‌دهند. با merge تغییرات به شاخه‌ی اصلی می‌آید.',
					'lang' => 'bash', 'code' => "git checkout -b feature/login\ngit add .\ngit commit -m \"افزودن صفحه ورود\"\n\ngit checkout main\ngit merge feature/login",
				),
			),
		),
		array(
			'title' => 'فصل دوم: گیت‌هاب',
			'items' => array(
				array(
					'h' => 'push و pull request',
					't' => 'گیت‌هاب سرویس میزبانی مخازن Git است. با push کد به سرور می‌رود و با pull request کد بررسی و ادغام می‌شود.',
					'lang' => 'bash', 'code' => "git remote add origin https://github.com/user/repo.git\ngit push -u origin main\n\n# دریافت تغییرات\ngit pull origin main",
				),
			),
		),
	);

	$map['r'] = array(
		array(
			'title' => 'فصل اول: مبانی R',
			'items' => array(
				array(
					'h' => 'وکتور و داده‌فریم',
					't' => 'R زبان تخصصی آمار و علم داده است. ساختار اصلی آن وکتور و data.frame است.',
					'lang' => 'r', 'code' => "# ساخت وکتور و داده‌فریم\nscores <- c(15, 18, 20, 12)\nmean(scores)\n\nstudents <- data.frame(\n  name = c(\"علی\", \"مریم\"),\n  grade = c(17, 19)\n)\nprint(students)",
				),
				array(
					'h' => 'ggplot2',
					't' => 'بسته‌ی ggplot2 برای ترسیم نمودارهای حرفه‌ای استفاده می‌شود.',
					'lang' => 'r', 'code' => "library(ggplot2)\n\ndata <- data.frame(x = 1:10, y = (1:10)^2)\n\nggplot(data, aes(x, y)) +\n  geom_line(color = \"#E0A82E\", linewidth = 1.5) +\n  labs(title = \"نمودار سهمی\")",
				),
			),
		),
	);

	$map['java2'] = array(
		array(
			'title' => 'فصل اول: Streams پیشرفته',
			'items' => array(
				array(
					'h' => 'پردازش زنجیره‌ای داده',
					't' => 'در سطح پیشرفته، Streams و Optional کد تمیز و بدون خطای NullPointer می‌سازند.',
					'lang' => 'java', 'code' => "import java.util.*;\nimport java.util.stream.*;\n\nList<String> names = List.of(\"ali\", \"sara\", \"mehdi\");\n\nString result = names.stream()\n    .map(String::toUpperCase)\n    .filter(n -> n.length() >= 4)\n    .collect(Collectors.joining(\", \"));\n\nSystem.out.println(result);",
				),
			),
		),
		array(
			'title' => 'فصل دوم: همزمانی',
			'items' => array(
				array(
					'h' => 'CompletableFuture',
					't' => 'برنامه‌نویسی همزمان با CompletableFuture امکان اجرای غیرمسدود را فراهم می‌کند.',
					'lang' => 'java', 'code' => "import java.util.concurrent.*;\n\nCompletableFuture<Integer> future = CompletableFuture\n    .supplyAsync(() -> { try { Thread.sleep(100); } catch (Exception e) {} return 42; })\n    .thenApply(x -> x * 2);\n\nSystem.out.println(future.join());  // 84",
				),
			),
		),
	);

	$map['maui'] = array(
		array(
			'title' => 'فصل اول: UI در MAUI',
			'items' => array(
				array(
					'h' => 'صفحه و کنترل',
					't' => '.NET MAUI با یک کد، اپلیکیشن چندپلتفرمی می‌سازد. رابط کاربری با XAML تعریف می‌شود.',
					'lang' => 'xml', 'code' => "<ContentPage xmlns=\"http://schemas.microsoft.com/dotnet/2021/maui\"\n             x:Class=\"MyApp.MainPage\">\n    <VerticalStackLayout Padding=\"30\" Spacing=\"20\">\n        <Label Text=\"سلام از زرین کد\" FontSize=\"28\" HorizontalOptions=\"Center\"/>\n        <Button Text=\"کلیک کن\" Clicked=\"OnBtnClicked\"/>\n    </VerticalStackLayout>\n</ContentPage>",
				),
				array(
					'h' => 'داده‌بایندینگ',
					't' => 'با Data Binding می‌توان رابط را به داده متصل کرد.',
					'lang' => 'csharp', 'code' => "public partial class MainPage : ContentPage\n{\n    public string Message { get; set; } = \"سلام!\";\n\n    public MainPage()\n    {\n        InitializeComponent();\n        BindingContext = this;\n    }\n}",
				),
			),
		),
	);

	$map['kotlin'] = array(
		array(
			'title' => 'فصل اول: مبانی کاتلین',
			'items' => array(
				array(
					'h' => 'متغیر و تابع',
					't' => 'Kotlin زبان مدرن و رسمی اندروید است. با val/var متغیر و با fun تابع تعریف می‌شود.',
					'lang' => 'kotlin', 'code' => <<<'CODE'
fun main() {
    val name = "زرین کد"
    var count = 5
    count++
    println("$name - $count")

    val result = sum(4, 6)
    println(result)
}

fun sum(a: Int, b: Int): Int = a + b
CODE,
				),
				array(
					'h' => 'داده‌کلاس و Coroutines',
					't' => 'data class کلاس‌های ساده و قدرتمند می‌سازد. Coroutines برای عملیات ناهمزمان استفاده می‌شود.',
					'lang' => 'kotlin', 'code' => "data class User(val name: String, val age: Int)\n\nsuspend fun fetchUser(): User {\n    delay(100)\n    return User(\"علی\", 25)\n}\n\nfun main() = runBlocking {\n    val user = fetchUser()\n    println(user)\n}",
				),
			),
		),
		array(
			'title' => 'فصل دوم: Jetpack Compose',
			'items' => array(
				array(
					'h' => 'UI با Compose',
					't' => 'Jetpack Compose رویکرد مدرن برای ساخت رابط کاربری اندروید با کد است.',
					'lang' => 'kotlin', 'code' => <<<'CODE'
@Composable
fun Greeting(name: String) {
    Text(
        text = "سلام $name!",
        fontSize = 28.sp,
        modifier = Modifier.padding(16.dp)
    )
}
CODE,
				),
			),
		),
	);

	if ( ! isset( $map[ $key ] ) ) {
		return array();
	}

	return $map[ $key ];
}

/**
 * رندر HTML کامل درس‌ها برای یک دوره.
 *
 * @param string $key کلید دوره.
 * @return string
 */
function zc_tech_lessons_html( $key ) {
	$lessons = zc_tech_full_lessons( $key );
	if ( empty( $lessons ) ) {
		return '';
	}

	$html = '<h2>سرفصل‌های آموزشی و توضیحات کامل</h2>' . "\n";
	foreach ( $lessons as $section ) {
		$html .= '<h3>' . esc_html( $section['title'] ) . '</h3>' . "\n";
		foreach ( $section['items'] as $item ) {
			$html .= '<h4>' . esc_html( $item['h'] ) . '</h4>' . "\n";
			$html .= '<p>' . esc_html( $item['t'] ) . '</p>' . "\n";
			$html .= zc_tech_code_block( $item['lang'], $item['code'] ) . "\n";
		}
	}

	return $html;
}
