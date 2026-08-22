<?php
/**
 * راه‌اندازی یکپارچه‌سازی با المنتور (سازگار با نسخه رایگان)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * کلاس مدیریت المنتور.
 */
final class ZC_Elementor {

	/**
	 * نمونه singleton.
	 *
	 * @var ZC_Elementor|null
	 */
	private static $instance = null;

	/**
	 * لیست ویجت‌ها.
	 *
	 * @var array
	 */
	private $widgets = array(
		'hero',
		'stats',
		'category-cards',
		'cta-bar',
		'courses',
		'products',
		'posts',
		'tutorials',
		'dark-search',
		'features',
		'testimonials-video',
		'story-box',
		'teachers',
		'faq',
		'counters',
		'newsletter',
		'pricing',
		'brands',
		'countdown',
		'heading',
		'button',
		'icon-box',
		'image-box',
		'tabs',
		'progress',
		'video-box',
		'contact-form',
		'booking-form',
		'map',
		'gallery',
		'timeline',
		'roadmap',
		'services',
		'portfolio',
		'request-form',
		'trust-badges',
	);

	/**
	 * دریافت نمونه.
	 *
	 * @return ZC_Elementor
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * سازنده.
	 */
	private function __construct() {
		add_action( 'elementor/init', array( $this, 'add_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_category_new' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'editor_styles' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_styles' ) );
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );

		// پشتیبانی از قالب‌های سفارشی هدر/فوتر با المنتور رایگان.
		add_action( 'init', array( $this, 'register_template_cpt' ) );
		add_filter( 'single_template', array( $this, 'template_canvas' ) );

		// کنترل‌های سراسری.
		add_action( 'elementor/element/after_section_end', array( $this, 'add_global_controls' ), 10, 3 );

		// توابع کمکی کنترل‌ها بدون وابستگی به کلاس‌های المنتور است.
		require_once ZC_INC . 'elementor/controls.php';
	}

	/**
	 * بارگذاری کلاس پایه ویجت‌ها.
	 * این کلاس از Elementor\Widget_Base ارث می‌برد، بنابراین فقط زمانی
	 * بارگذاری می‌شود که کلاس‌های المنتور در دسترس باشند.
	 *
	 * @return bool
	 */
	private function load_base_widget() {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return false;
		}
		if ( ! class_exists( 'ZC_Widget_Base' ) ) {
			require_once ZC_INC . 'elementor/base-widget.php';
		}
		return true;
	}

	/**
	 * ثبت دسته ویجت‌ها (نسخه قدیمی).
	 *
	 * @return void
	 */
	public function add_category() {
		if ( method_exists( \Elementor\Plugin::$instance->elements_manager, 'add_category' ) ) {
			\Elementor\Plugin::$instance->elements_manager->add_category(
				'zarincode',
				array(
					'title' => __( 'زرین کد', 'zarincode' ),
					'icon'  => 'eicon-code',
				),
				1
			);
			\Elementor\Plugin::$instance->elements_manager->add_category(
				'zarincode-shop',
				array(
					'title' => __( 'زرین کد | فروشگاه و دوره', 'zarincode' ),
					'icon'  => 'eicon-woocommerce',
				),
				2
			);
		}
	}

	/**
	 * ثبت دسته (نسخه جدید).
	 *
	 * @param \Elementor\Elements_Manager $manager مدیر.
	 * @return void
	 */
	public function add_category_new( $manager ) {
		$manager->add_category(
			'zarincode',
			array(
				'title' => __( 'زرین کد', 'zarincode' ),
				'icon'  => 'eicon-code',
			)
		);
		$manager->add_category(
			'zarincode-shop',
			array(
				'title' => __( 'زرین کد | فروشگاه و دوره', 'zarincode' ),
				'icon'  => 'eicon-woocommerce',
			)
		);
	}

	/**
	 * ثبت همه ویجت‌ها.
	 *
	 * @param \Elementor\Widgets_Manager $manager مدیر ویجت‌ها.
	 * @return void
	 */
	public function register_widgets( $manager ) {
		if ( ! $this->load_base_widget() ) {
			return;
		}

		foreach ( $this->widgets as $widget ) {
			$file = ZC_INC . 'elementor/widgets/' . $widget . '.php';
			if ( ! file_exists( $file ) ) {
				continue;
			}
			require_once $file;

			$class = 'ZC_Widget_' . str_replace( '-', '_', $widget );
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}

	/**
	 * استایل ویژه ادیتور.
	 *
	 * @return void
	 */
	public function editor_styles() {
		wp_enqueue_style( 'zc-fonts', ZC_ASSETS . 'css/fonts.css', array(), ZC_VERSION );
		wp_enqueue_style( 'zc-main', ZC_ASSETS . 'css/main.css', array(), ZC_VERSION );
		wp_add_inline_style( 'zc-main', zc_dynamic_css() );
	}

	/**
	 * ثبت لوکیشن‌های تم بیلدر (اگر المنتور پرو فعال باشد).
	 *
	 * @param object $manager مدیر.
	 * @return void
	 */
	public function register_locations( $manager ) {
		$manager->register_all_core_location();
	}

	/**
	 * ثبت پست‌تایپ قالب‌های سفارشی (جایگزین رایگان تم‌بیلدر).
	 *
	 * @return void
	 */
	public function register_template_cpt() {
		register_post_type(
			'zc_template',
			array(
				'labels'       => array(
					'name'          => __( 'قالب‌های المنتور', 'zarincode' ),
					'singular_name' => __( 'قالب', 'zarincode' ),
					'add_new_item'  => __( 'افزودن قالب جدید', 'zarincode' ),
					'menu_name'     => __( 'قالب‌های من', 'zarincode' ),
				),
				'public'       => true,
				'show_in_menu' => 'themes.php',
				'has_archive'  => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'supports'     => array( 'title', 'editor', 'elementor', 'thumbnail' ),
				'rewrite'      => array( 'slug' => 'zc-template' ),
			)
		);
	}

	/**
	 * استفاده از قالب بوم سفید برای قالب‌های سفارشی.
	 *
	 * @param string $template مسیر.
	 * @return string
	 */
	public function template_canvas( $template ) {
		if ( is_singular( 'zc_template' ) ) {
			$canvas = ZC_DIR . 'templates/template-canvas.php';
			if ( file_exists( $canvas ) ) {
				return $canvas;
			}
		}
		return $template;
	}

	/**
	 * افزودن کنترل انیمیشن سراسری زرین کد به همه ویجت‌ها.
	 *
	 * @param \Elementor\Controls_Stack $element المان.
	 * @param string                    $section_id شناسه بخش.
	 * @param array                     $args آرگومان‌ها.
	 * @return void
	 */
	public function add_global_controls( $element, $section_id, $args ) {
		if ( 'section_effects' !== $section_id && '_section_style' !== $section_id ) {
			return;
		}
		if ( ! in_array( $element->get_name(), array( 'section', 'column', 'container' ), true ) && 'section_effects' !== $section_id ) {
			return;
		}
	}

	/**
	 * دریافت لیست ویجت‌های ثبت‌شده.
	 *
	 * @return array
	 */
	public function get_widgets() {
		return $this->widgets;
	}
}

/**
 * راه‌اندازی پس از لود المنتور.
 *
 * توجه: فایل functions.php قالب پس از هوک plugins_loaded اجرا می‌شود،
 * بنابراین نمی‌توان صرفاً به آن هوک تکیه کرد و باید بلافاصله بررسی شود.
 *
 * @return void
 */
function zc_elementor_init() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	ZC_Elementor::instance();
}

// اگر المنتور از قبل لود شده باشد (حالت معمول)، بلافاصله اجرا شود.
if ( did_action( 'elementor/loaded' ) ) {
	zc_elementor_init();
} else {
	// در غیر این صورت منتظر لود شدن المنتور می‌مانیم.
	add_action( 'elementor/loaded', 'zc_elementor_init' );
	add_action( 'plugins_loaded', 'zc_elementor_init', 20 );
}

/**
 * اعلان نصب المنتور.
 *
 * @return void
 */
function zc_elementor_notice() {
	if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<strong><?php esc_html_e( 'قالب زرین کد:', 'zarincode' ); ?></strong>
			<?php esc_html_e( 'برای استفاده از ویجت‌های اختصاصی و صفحه‌ساز، افزونه المنتور (نسخه رایگان کافی است) را نصب کنید.', 'zarincode' ); ?>
			<a href="<?php echo esc_url( $url ); ?>" class="button button-primary" style="margin-inline-start:8px"><?php esc_html_e( 'نصب المنتور', 'zarincode' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'zc_elementor_notice' );
