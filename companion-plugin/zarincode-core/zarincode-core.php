<?php
/**
 * Plugin Name: Zarincode Core
 * Description: لایهٔ پایدار داده‌های زرین کد؛ CPTها و taxonomyها را مستقل از قالب نگه می‌دارد.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author: Zarincode
 * Text Domain: zarincode-core
 */
defined( 'ABSPATH' ) || exit;
if ( ! defined( 'ZARINCODE_CORE_ACTIVE' ) ) { define( 'ZARINCODE_CORE_ACTIVE', true ); }

final class Zarincode_Core_Data_Portability {
	public const VERSION = '1.0.0';

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register_data_types' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'theme_notice' ) );
	}

	private static function theme_active() {
		$theme = wp_get_theme();
		return 'zarincode' === $theme->get_stylesheet() || 'zarincode' === $theme->get_template() || 'zarincode' === $theme->get( 'TextDomain' );
	}

	public static function register_data_types() {
		if ( self::theme_active() ) {
			return; // قالب تعریف کامل و برچسب‌های اصلی را ثبت می‌کند.
		}

		$public = array(
			'zc_course' => array( 'دوره‌ها', 'course', true ), 'zc_lesson' => array( 'جلسات', 'lesson', false ),
			'zc_tutorial' => array( 'آموزش‌ها', 'tutorial', true ), 'zc_teacher' => array( 'مدرسان', 'teacher', true ),
			'zc_testimonial' => array( 'نظرات مشتریان', 'testimonial', false ), 'zc_service' => array( 'خدمات', 'service', true ),
			'zc_project' => array( 'نمونه‌کارها', 'project', true ), 'zc_faq' => array( 'سؤالات متداول', 'faq', false ),
			'zc_learning_path' => array( 'مسیرهای یادگیری', 'learning-path', true ),
		);
		foreach ( $public as $type => $cfg ) {
			if ( post_type_exists( $type ) ) { continue; }
			register_post_type( $type, array( 'labels' => array( 'name' => $cfg[0], 'singular_name' => $cfg[0] ), 'public' => true, 'has_archive' => $cfg[2], 'rewrite' => array( 'slug' => $cfg[1] ), 'show_in_rest' => true, 'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ) ) );
		}

		$private = array( 'zc_ticket' => 'تیکت‌ها', 'zc_booking' => 'رزروها', 'zc_request' => 'درخواست‌ها', 'zc_announce' => 'اطلاعیه‌ها', 'zc_contract_tpl' => 'الگوهای قرارداد', 'zc_contract' => 'قراردادها', 'zc_practice' => 'تمرین‌ها', 'zc_subscription' => 'اشتراک‌ها', 'zc_template' => 'قالب‌ها' );
		foreach ( $private as $type => $label ) {
			if ( ! post_type_exists( $type ) ) {
				register_post_type( $type, array( 'labels' => array( 'name' => $label, 'singular_name' => $label ), 'public' => false, 'show_ui' => true, 'supports' => array( 'title', 'editor', 'custom-fields', 'author' ) ) );
			}
		}

		$taxes = array(
			'zc_course_cat' => array( array( 'zc_course' ), true ), 'zc_course_tag' => array( array( 'zc_course', 'zc_tutorial' ), false ),
			'zc_tutorial_cat' => array( array( 'zc_tutorial' ), true ), 'zc_teacher_skill' => array( array( 'zc_teacher' ), false ),
			'zc_faq_cat' => array( array( 'zc_faq' ), true ), 'zc_service_cat' => array( array( 'zc_service' ), true ),
			'zc_project_cat' => array( array( 'zc_project' ), true ), 'zc_project_tech' => array( array( 'zc_project' ), false ),
		);
		foreach ( $taxes as $tax => $cfg ) {
			if ( ! taxonomy_exists( $tax ) ) {
				register_taxonomy( $tax, $cfg[0], array( 'label' => $tax, 'public' => true, 'hierarchical' => $cfg[1], 'show_in_rest' => true ) );
			}
		}
	}

	public static function theme_notice() {
		if ( self::theme_active() || ! current_user_can( 'manage_options' ) ) { return; }
		echo '<div class="notice notice-info"><p>' . esc_html__( 'Zarincode Core داده‌های زرین کد را حفظ کرده است؛ برای رابط کامل، قالب زرین کد را فعال کنید.', 'zarincode-core' ) . '</p></div>';
	}
}
Zarincode_Core_Data_Portability::boot();
