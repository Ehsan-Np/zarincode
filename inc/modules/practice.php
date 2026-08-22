<?php
/**
 * ماژول تمرین کدنویسی زرین کد (در پنل کاربری)
 * ---------------------------------------------------------------------------
 * یک بانک سوال تمرینی شامل سه نوع سوال (چندگزینه‌ای، جای خالی، کدنویسی)
 * در قالب یک نوع محتوای اختصاصی `zc_practice` تعریف می‌شود. مدیر با همان
 * ویرایشگر سوالات آزمون دوره، سوالات را می‌نویسد و اعضا در پنل کاربری با
 * حالت «گام‌به‌گام» حل می‌کنند.
 *
 * این ماژول کاملاً مدیریت‌پذیر است: می‌توان آن را جدا از آزمون دوره غیرفعال
 * کرد، زبان‌های مجاز هر تمرین و حد نصاب قبولی آن را جداگانه تعیین نمود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا بخش «تمرین کدنویسی» فعال است؟
 *
 * @return bool
 */
function zc_practice_enabled() {
	return zc_quiz_module_enabled() && (bool) zc_opt( 'zc_practice_enable', true );
}

/**
 * ثبت نوع محتوای «تمرین».
 *
 * @return void
 */
function zc_register_practice_cpt() {
	register_post_type(
		'zc_practice',
		array(
			'labels'        => array(
				'name'          => __( 'تمرین‌های کدنویسی', 'zarincode' ),
				'singular_name' => __( 'تمرین', 'zarincode' ),
				'add_new'       => __( 'افزودن تمرین', 'zarincode' ),
				'add_new_item'  => __( 'افزودن تمرین جدید', 'zarincode' ),
				'edit_item'     => __( 'ویرایش تمرین', 'zarincode' ),
				'all_items'     => __( 'همه تمرین‌ها', 'zarincode' ),
				'search_items'  => __( 'جستجوی تمرین', 'zarincode' ),
				'menu_name'     => __( 'تمرین کدنویسی', 'zarincode' ),
			),
			'public'        => false,   // فقط داخل پنل کاربری نمایش داده می‌شود.
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-editor-code',
			'menu_position' => 27,
			'supports'      => array( 'title', 'editor', 'custom-fields' ),
			'show_in_rest'  => false,
			'has_archive'   => false,
		)
	);
}
add_action( 'init', 'zc_register_practice_cpt' );

/**
 * افزودن تب «تمرین» به پنل کاربری.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_practice_panel_tab( $tabs ) {
	if ( ! zc_practice_enabled() ) {
		return $tabs;
	}
	$count = zc_practice_count();
	$tabs['practice'] = array(
		'label' => __( 'تمرین کدنویسی', 'zarincode' ),
		'icon'  => 'code',
		'order' => 55,
		'badge' => $count ? (int) $count : 0,
	);
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_practice_panel_tab' );

/**
 * تعداد تمرین‌های منتشرشده.
 *
 * @return int
 */
function zc_practice_count() {
	static $count = null;
	if ( null === $count ) {
		$q = new WP_Query(
			array(
				'post_type'           => 'zc_practice',
				'post_status'         => 'publish',
				'posts_per_page'      => 1,
				'fields'              => 'ids',
				'no_found_rows'       => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		$count = (int) $q->found_posts;
	}
	return $count;
}

/**
 * سوالات یک تمرین.
 *
 * @param int $practice_id تمرین.
 * @return array
 */
function zc_practice_questions( $practice_id ) {
	$questions = get_post_meta( $practice_id, '_zc_practice', true );
	return is_array( $questions ) ? $questions : array();
}

/**
 * تعداد سوالات یک تمرین.
 *
 * @param int $practice_id تمرین.
 * @return int
 */
function zc_practice_question_count( $practice_id ) {
	return count( zc_practice_questions( $practice_id ) );
}

/**
 * تنظیمات تمرین (حد نصاب قبولی).
 *
 * @param int $practice_id تمرین.
 * @return array
 */
function zc_practice_settings( $practice_id ) {
	$pass = get_post_meta( $practice_id, '_zc_practice_pass', true );
	return array(
		'pass' => $pass ? (float) $pass : (float) zc_opt( 'zc_practice_pass', 70 ),
	);
}

/**
 * زبان‌های مجاز برای یک تمرین.
 *
 * @param int $practice_id تمرین.
 * @return array|null
 */
function zc_practice_languages( $practice_id ) {
	$langs = get_post_meta( $practice_id, '_zc_practice_langs', true );
	$langs = is_array( $langs ) ? array_map( 'strval', array_filter( $langs ) ) : array();
	return empty( $langs ) ? null : $langs;
}

/**
 * متاباکس سوالات تمرین.
 *
 * @return void
 */
function zc_register_practice_metabox() {
	add_meta_box(
		'zc_practice_metabox',
		__( 'سوالات تمرین (گام‌به‌گام)', 'zarincode' ),
		'zc_practice_metabox_html',
		'zc_practice',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'zc_register_practice_metabox' );

/**
 * محتوای متاباکس سوالات تمرین.
 *
 * @param \WP_Post $post پست.
 * @return void
 */
function zc_practice_metabox_html( $post ) {
	wp_nonce_field( 'zc_practice_save', 'zc_practice_nonce' );
	$zc_settings = zc_practice_settings( $post->ID );
	$zc_all_langs = zc_quiz_languages();
	$zc_plangs    = zc_practice_languages( $post->ID );
	?>
	<p style="font-size:.8rem;color:#64748b">
		<?php esc_html_e( 'این تمرین در تب «تمرین کدنویسی» پنل کاربری با حالت گام‌به‌گام (هر پاسخ درست، سوال بعدی را باز می‌کند) نمایش داده می‌شود.', 'zarincode' ); ?>
	</p>
	<div class="zc-quiz-opt">
		<label><?php esc_html_e( 'حد نصاب قبولی این تمرین:', 'zarincode' ); ?></label>
		<input type="number" name="zc_practice_pass" min="0" max="100" value="<?php echo esc_attr( $zc_settings['pass'] ); ?>" style="width:70px">
		<span class="description"><?php esc_html_e( '(خالی = پیش‌فرض قالب)', 'zarincode' ); ?></span>
	</div>

	<?php if ( $zc_all_langs ) : ?>
		<div class="zc-quiz-opt" style="display:block">
			<label style="font-weight:600"><?php esc_html_e( 'زبان‌های مجاز این تمرین:', 'zarincode' ); ?></label>
			<span class="description" style="margin-inline-start:6px"><?php esc_html_e( '(اگر هیچ‌کدام انتخاب نشود، همهٔ زبان‌های فعال سراسری)', 'zarincode' ); ?></span>
			<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
				<?php foreach ( $zc_all_langs as $k => $l ) : ?>
					<label style="display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:3px 12px;cursor:pointer">
						<input type="checkbox" name="zc_practice_langs[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( is_array( $zc_plangs ) && in_array( $k, $zc_plangs, true ) ); ?>>
						<?php echo esc_html( $l['label'] ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<h3 style="margin:14px 0 8px"><?php esc_html_e( 'سوالات', 'zarincode' ); ?></h3>
	<?php
	zc_render_questions_editor( $post, '_zc_practice' );
}

/**
 * ذخیره سوالات و تنظیمات تمرین.
 *
 * @param int $post_id پست.
 * @return void
 */
function zc_practice_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['zc_practice_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['zc_practice_nonce'] ), 'zc_practice_save' ) ) { // phpcs:ignore
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$pass = isset( $_POST['zc_practice_pass'] ) ? (float) wp_unslash( $_POST['zc_practice_pass'] ) : 0; // phpcs:ignore
	update_post_meta( $post_id, '_zc_practice_pass', $pass > 0 ? $pass : '' );

	$langs = isset( $_POST['zc_practice_langs'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['zc_practice_langs'] ) ) : array(); // phpcs:ignore
	update_post_meta( $post_id, '_zc_practice_langs', array_values( array_filter( $langs ) ) );

	zc_save_questions_meta( $post_id, 'zcp_q', '_zc_practice', 'zc_practice_nonce', 'zc_practice_save' );
}
add_action( 'save_post_zc_practice', 'zc_practice_save' );
