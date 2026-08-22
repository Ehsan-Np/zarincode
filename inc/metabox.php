<?php
/**
 * متاباکس‌های سفارشی (دوره، آموزش، مدرس، تیکت)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت متاباکس‌ها.
 *
 * @return void
 */
function zc_register_metaboxes() {

	add_meta_box( 'zc_course_details', __( 'مشخصات دوره', 'zarincode' ), 'zc_course_metabox', 'zc_course', 'normal', 'high' );
	add_meta_box( 'zc_curriculum', __( 'سرفصل‌ها و جلسات دوره', 'zarincode' ), 'zc_curriculum_metabox', 'zc_course', 'normal', 'high' );
	add_meta_box( 'zc_tutorial_details', __( 'مشخصات آموزش', 'zarincode' ), 'zc_tutorial_metabox', 'zc_tutorial', 'side', 'default' );
	add_meta_box( 'zc_teacher_details', __( 'مشخصات مدرس', 'zarincode' ), 'zc_teacher_metabox', 'zc_teacher', 'normal', 'high' );
	add_meta_box( 'zc_ticket_details', __( 'مدیریت تیکت', 'zarincode' ), 'zc_ticket_metabox', 'zc_ticket', 'side', 'high' );
	add_meta_box( 'zc_page_options', __( 'تنظیمات صفحه (زرین کد)', 'zarincode' ), 'zc_page_metabox', array( 'page', 'post' ), 'side', 'default' );
}
add_action( 'add_meta_boxes', 'zc_register_metaboxes' );

/**
 * متاباکس مشخصات دوره.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_course_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$fields = array(
		'_zc_price'          => get_post_meta( $post->ID, '_zc_price', true ),
		'_zc_sale_price'     => get_post_meta( $post->ID, '_zc_sale_price', true ),
		'_zc_product_id'     => get_post_meta( $post->ID, '_zc_product_id', true ),
		'_zc_teacher'        => get_post_meta( $post->ID, '_zc_teacher', true ),
		'_zc_level'          => get_post_meta( $post->ID, '_zc_level', true ),
		'_zc_duration'       => get_post_meta( $post->ID, '_zc_duration', true ),
		'_zc_students'       => get_post_meta( $post->ID, '_zc_students', true ),
		'_zc_rating'         => get_post_meta( $post->ID, '_zc_rating', true ),
		'_zc_rating_count'   => get_post_meta( $post->ID, '_zc_rating_count', true ),
		'_zc_course_status'  => get_post_meta( $post->ID, '_zc_course_status', true ),
		'_zc_preview_video'  => get_post_meta( $post->ID, '_zc_preview_video', true ),
		'_zc_access_days'    => get_post_meta( $post->ID, '_zc_access_days', true ),
		'_zc_prerequisites'  => get_post_meta( $post->ID, '_zc_prerequisites', true ),
		'_zc_audience'       => get_post_meta( $post->ID, '_zc_audience', true ),
	);

	$features = get_post_meta( $post->ID, '_zc_features', true );
	$features = is_array( $features ) ? implode( "\n", array_map( function ( $f ) {
		return is_array( $f ) ? ( $f['text'] ?? '' ) : $f;
	}, $features ) ) : '';

	$teachers = get_posts( array( 'post_type' => 'zc_teacher', 'posts_per_page' => 100 ) );
	$products = zc_is_woo() ? get_posts( array( 'post_type' => 'product', 'posts_per_page' => 200 ) ) : array();
	?>
	<div class="zc-metabox">
		<div class="zc-metabox__grid">

			<p>
				<label for="zc_price"><strong><?php esc_html_e( 'قیمت (تومان)', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_price" name="_zc_price" value="<?php echo esc_attr( $fields['_zc_price'] ); ?>" min="0" step="1000">
				<span class="description"><?php esc_html_e( 'صفر یا خالی = رایگان', 'zarincode' ); ?></span>
			</p>

			<p>
				<label for="zc_sale_price"><strong><?php esc_html_e( 'قیمت با تخفیف', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_sale_price" name="_zc_sale_price" value="<?php echo esc_attr( $fields['_zc_sale_price'] ); ?>" min="0" step="1000">
			</p>

			<p>
				<label for="zc_product_id"><strong><?php esc_html_e( 'محصول ووکامرس متصل', 'zarincode' ); ?></strong></label>
				<select id="zc_product_id" name="_zc_product_id">
					<option value=""><?php esc_html_e( '— بدون محصول —', 'zarincode' ); ?></option>
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $fields['_zc_product_id'], $product->ID ); ?>>
							<?php echo esc_html( $product->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="description"><?php esc_html_e( 'برای فروش دوره از طریق سبد خرید', 'zarincode' ); ?></span>
			</p>

			<p>
				<label for="zc_teacher"><strong><?php esc_html_e( 'مدرس دوره', 'zarincode' ); ?></strong></label>
				<input list="zc_teachers_list" id="zc_teacher" name="_zc_teacher" value="<?php echo esc_attr( $fields['_zc_teacher'] ); ?>">
				<datalist id="zc_teachers_list">
					<?php foreach ( $teachers as $teacher ) : ?>
						<option value="<?php echo esc_attr( $teacher->post_title ); ?>"></option>
					<?php endforeach; ?>
				</datalist>
			</p>

			<p>
				<label for="zc_level"><strong><?php esc_html_e( 'سطح دوره', 'zarincode' ); ?></strong></label>
				<select id="zc_level" name="_zc_level">
					<?php
					$levels = array(
						'beginner'     => __( 'مقدماتی', 'zarincode' ),
						'intermediate' => __( 'متوسط', 'zarincode' ),
						'advanced'     => __( 'پیشرفته', 'zarincode' ),
					);
					foreach ( $levels as $k => $v ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $fields['_zc_level'], $k, false ), esc_html( $v ) );
					}
					?>
				</select>
			</p>

			<p>
				<label for="zc_course_status"><strong><?php esc_html_e( 'وضعیت دوره', 'zarincode' ); ?></strong></label>
				<select id="zc_course_status" name="_zc_course_status">
					<?php
					$statuses = array(
						'completed'   => __( 'تکمیل شده', 'zarincode' ),
						'in-progress' => __( 'در حال برگزاری', 'zarincode' ),
						'upcoming'    => __( 'به زودی', 'zarincode' ),
					);
					foreach ( $statuses as $k => $v ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $fields['_zc_course_status'], $k, false ), esc_html( $v ) );
					}
					?>
				</select>
			</p>

			<p>
				<label for="zc_duration"><strong><?php esc_html_e( 'مدت زمان کل', 'zarincode' ); ?></strong></label>
				<input type="text" id="zc_duration" name="_zc_duration" value="<?php echo esc_attr( $fields['_zc_duration'] ); ?>" placeholder="<?php esc_attr_e( 'مثال: ۱۲ ساعت', 'zarincode' ); ?>">
			</p>

			<p>
				<label for="zc_students"><strong><?php esc_html_e( 'تعداد دانشجو', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_students" name="_zc_students" value="<?php echo esc_attr( $fields['_zc_students'] ); ?>" min="0">
			</p>

			<p>
				<label for="zc_rating"><strong><?php esc_html_e( 'امتیاز (۰ تا ۵)', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_rating" name="_zc_rating" value="<?php echo esc_attr( $fields['_zc_rating'] ); ?>" min="0" max="5" step="0.1">
			</p>

			<p>
				<label for="zc_rating_count"><strong><?php esc_html_e( 'تعداد رای', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_rating_count" name="_zc_rating_count" value="<?php echo esc_attr( $fields['_zc_rating_count'] ); ?>" min="0">
			</p>

			<p>
				<label for="zc_access_days"><strong><?php esc_html_e( 'مدت دسترسی (روز)', 'zarincode' ); ?></strong></label>
				<input type="number" id="zc_access_days" name="_zc_access_days" value="<?php echo esc_attr( $fields['_zc_access_days'] ); ?>" min="0">
				<span class="description"><?php esc_html_e( 'صفر = مادام‌العمر', 'zarincode' ); ?></span>
			</p>

			<p class="zc-metabox__full">
				<label for="zc_preview_video"><strong><?php esc_html_e( 'ویدیوی پیش‌نمایش', 'zarincode' ); ?></strong></label>
				<input type="url" id="zc_preview_video" name="_zc_preview_video" value="<?php echo esc_attr( $fields['_zc_preview_video'] ); ?>" placeholder="https://www.aparat.com/video/video/embed/...">
			</p>

			<p class="zc-metabox__full">
				<label for="zc_features"><strong><?php esc_html_e( 'در این دوره چه می‌آموزید؟ (هر خط یک مورد)', 'zarincode' ); ?></strong></label>
				<textarea id="zc_features" name="_zc_features" rows="6"><?php echo esc_textarea( $features ); ?></textarea>
			</p>

			<p class="zc-metabox__full">
				<label for="zc_prerequisites"><strong><?php esc_html_e( 'پیش‌نیازها', 'zarincode' ); ?></strong></label>
				<textarea id="zc_prerequisites" name="_zc_prerequisites" rows="3"><?php echo esc_textarea( $fields['_zc_prerequisites'] ); ?></textarea>
			</p>

			<p class="zc-metabox__full">
				<label for="zc_audience"><strong><?php esc_html_e( 'مخاطبان دوره', 'zarincode' ); ?></strong></label>
				<textarea id="zc_audience" name="_zc_audience" rows="3"><?php echo esc_textarea( $fields['_zc_audience'] ); ?></textarea>
			</p>
		</div>
	</div>
	<?php
}

/**
 * متاباکس سرفصل‌ها.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_curriculum_metabox( $post ) {
	$sections = zc_get_curriculum( $post->ID );
	?>
	<div class="zc-curriculum-builder" id="zc-curriculum-builder">
		<div class="zc-sections" id="zc-sections">
			<?php
			if ( $sections ) :
				foreach ( $sections as $si => $section ) :
					?>
					<div class="zc-section-item" data-index="<?php echo (int) $si; ?>">
						<div class="zc-section-item__head">
							<span class="dashicons dashicons-menu zc-drag"></span>
							<input type="text" name="zc_curriculum[<?php echo (int) $si; ?>][title]"
								value="<?php echo esc_attr( $section['title'] ?? '' ); ?>"
								placeholder="<?php esc_attr_e( 'عنوان فصل', 'zarincode' ); ?>" class="zc-section-title">
							<button type="button" class="button zc-add-lesson"><?php esc_html_e( '+ افزودن جلسه', 'zarincode' ); ?></button>
							<button type="button" class="button zc-remove-section"><span class="dashicons dashicons-trash"></span></button>
						</div>
						<div class="zc-lessons-list">
							<?php
							foreach ( (array) ( $section['lessons'] ?? array() ) as $li => $lesson ) :
								?>
								<div class="zc-lesson-item">
									<span class="dashicons dashicons-video-alt3"></span>
									<input type="text" name="zc_curriculum[<?php echo (int) $si; ?>][lessons][<?php echo (int) $li; ?>][title]"
										value="<?php echo esc_attr( $lesson['title'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'عنوان جلسه', 'zarincode' ); ?>">
									<input type="text" name="zc_curriculum[<?php echo (int) $si; ?>][lessons][<?php echo (int) $li; ?>][duration]"
										value="<?php echo esc_attr( $lesson['duration'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'مدت', 'zarincode' ); ?>" style="max-width:90px">
									<input type="url" name="zc_curriculum[<?php echo (int) $si; ?>][lessons][<?php echo (int) $li; ?>][video]"
										value="<?php echo esc_attr( $lesson['video'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'لینک ویدیو', 'zarincode' ); ?>">
									<label class="zc-lesson-free">
										<input type="checkbox" name="zc_curriculum[<?php echo (int) $si; ?>][lessons][<?php echo (int) $li; ?>][free]"
											value="1" <?php checked( ! empty( $lesson['free'] ) ); ?>>
										<?php esc_html_e( 'رایگان', 'zarincode' ); ?>
									</label>
									<button type="button" class="button zc-remove-lesson"><span class="dashicons dashicons-no-alt"></span></button>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				endforeach;
			endif;
			?>
		</div>

		<button type="button" class="button button-primary" id="zc-add-section" style="margin-top:12px">
			<?php esc_html_e( '+ افزودن فصل جدید', 'zarincode' ); ?>
		</button>

		<p class="description" style="margin-top:10px">
			<?php esc_html_e( 'جلساتی که «رایگان» علامت بخورند، برای همه کاربران قابل مشاهده هستند.', 'zarincode' ); ?>
		</p>
	</div>
	<?php
}

/**
 * متاباکس آموزش.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_tutorial_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );
	$level = get_post_meta( $post->ID, '_zc_level', true );
	$video = get_post_meta( $post->ID, '_zc_video', true );
	?>
	<p>
		<label for="zc_t_level"><strong><?php esc_html_e( 'سطح آموزش', 'zarincode' ); ?></strong></label>
		<select id="zc_t_level" name="_zc_level" style="width:100%">
			<?php
			$levels = array(
				'beginner'     => __( 'مقدماتی', 'zarincode' ),
				'intermediate' => __( 'متوسط', 'zarincode' ),
				'advanced'     => __( 'پیشرفته', 'zarincode' ),
			);
			foreach ( $levels as $k => $v ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $level, $k, false ), esc_html( $v ) );
			}
			?>
		</select>
	</p>
	<p>
		<label for="zc_t_video"><strong><?php esc_html_e( 'لینک ویدیو (اختیاری)', 'zarincode' ); ?></strong></label>
		<input type="url" id="zc_t_video" name="_zc_video" value="<?php echo esc_attr( $video ); ?>" style="width:100%">
	</p>
	<?php
}

/**
 * متاباکس مدرس.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_teacher_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$fields = array(
		'_zc_teacher_role'     => array( __( 'تخصص / سمت', 'zarincode' ), 'text' ),
		'_zc_teacher_courses'  => array( __( 'تعداد دوره', 'zarincode' ), 'number' ),
		'_zc_teacher_students' => array( __( 'تعداد دانشجو', 'zarincode' ), 'number' ),
		'_zc_teacher_telegram' => array( __( 'تلگرام', 'zarincode' ), 'url' ),
		'_zc_teacher_linkedin' => array( __( 'لینکدین', 'zarincode' ), 'url' ),
		'_zc_teacher_github'   => array( __( 'گیت‌هاب', 'zarincode' ), 'url' ),
	);
	?>
	<div class="zc-metabox__grid">
		<?php foreach ( $fields as $key => $data ) : ?>
			<p>
				<label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $data[0] ); ?></strong></label>
				<input type="<?php echo esc_attr( $data[1] ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>">
			</p>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * متاباکس تیکت.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_ticket_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$status = get_post_meta( $post->ID, '_zc_status', true );
	$prio   = get_post_meta( $post->ID, '_zc_priority', true );
	$dept   = get_post_meta( $post->ID, '_zc_department', true );
	?>
	<p>
		<label><strong><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></strong></label>
		<select name="_zc_status" style="width:100%">
			<?php foreach ( zc_ticket_statuses() as $k => $v ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>><?php echo esc_html( $v['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label><strong><?php esc_html_e( 'اولویت', 'zarincode' ); ?></strong></label>
		<select name="_zc_priority" style="width:100%">
			<?php foreach ( zc_ticket_priorities() as $k => $v ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $prio, $k ); ?>><?php echo esc_html( $v['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label><strong><?php esc_html_e( 'دپارتمان', 'zarincode' ); ?></strong></label>
		<select name="_zc_department" style="width:100%">
			<?php foreach ( zc_ticket_departments() as $k => $v ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $dept, $k ); ?>><?php echo esc_html( $v ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

/**
 * متاباکس تنظیمات صفحه.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_page_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$hide_header = get_post_meta( $post->ID, '_zc_hide_header', true );
	$hide_footer = get_post_meta( $post->ID, '_zc_hide_footer', true );
	$hide_hero   = get_post_meta( $post->ID, '_zc_hide_hero', true );
	$meta_desc   = get_post_meta( $post->ID, '_zc_meta_desc', true );
	?>
	<p><label><input type="checkbox" name="_zc_hide_header" value="1" <?php checked( $hide_header, '1' ); ?>> <?php esc_html_e( 'مخفی کردن هدر', 'zarincode' ); ?></label></p>
	<p><label><input type="checkbox" name="_zc_hide_footer" value="1" <?php checked( $hide_footer, '1' ); ?>> <?php esc_html_e( 'مخفی کردن فوتر', 'zarincode' ); ?></label></p>
	<p><label><input type="checkbox" name="_zc_hide_hero" value="1" <?php checked( $hide_hero, '1' ); ?>> <?php esc_html_e( 'مخفی کردن سربرگ صفحه', 'zarincode' ); ?></label></p>
	<p>
		<label for="zc_meta_desc"><strong><?php esc_html_e( 'توضیحات متا (سئو)', 'zarincode' ); ?></strong></label>
		<textarea id="zc_meta_desc" name="_zc_meta_desc" rows="3" style="width:100%" maxlength="160"><?php echo esc_textarea( $meta_desc ); ?></textarea>
	</p>
	<?php
}

/**
 * ذخیره متاباکس‌ها.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_save_metaboxes( $post_id ) {
	if ( ! isset( $_POST['zc_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zc_meta_nonce'] ) ), 'zc_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// فیلدهای ساده.
	$simple = array(
		'_zc_price', '_zc_sale_price', '_zc_product_id', '_zc_teacher', '_zc_level',
		'_zc_duration', '_zc_students', '_zc_rating', '_zc_rating_count', '_zc_course_status',
		'_zc_preview_video', '_zc_access_days', '_zc_video',
		'_zc_teacher_role', '_zc_teacher_courses', '_zc_teacher_students',
		'_zc_teacher_telegram', '_zc_teacher_linkedin', '_zc_teacher_github',
		'_zc_status', '_zc_priority', '_zc_department',
	);

	foreach ( $simple as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// فیلدهای متنی چندخطی.
	foreach ( array( '_zc_prerequisites', '_zc_audience', '_zc_meta_desc' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// ویژگی‌ها (هر خط یکی).
	if ( isset( $_POST['_zc_features'] ) ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", sanitize_textarea_field( wp_unslash( $_POST['_zc_features'] ) ) ) ) );
		update_post_meta( $post_id, '_zc_features', array_values( $lines ) );
	}

	// چک‌باکس‌ها.
	foreach ( array( '_zc_hide_header', '_zc_hide_footer', '_zc_hide_hero' ) as $key ) {
		update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
	}

	// سرفصل‌ها.
	if ( isset( $_POST['zc_curriculum'] ) && is_array( $_POST['zc_curriculum'] ) ) {
		$clean = array();

		foreach ( wp_unslash( $_POST['zc_curriculum'] ) as $section ) { // phpcs:ignore
			if ( empty( $section['title'] ) ) {
				continue;
			}

			$lessons = array();
			foreach ( (array) ( $section['lessons'] ?? array() ) as $lesson ) {
				if ( empty( $lesson['title'] ) ) {
					continue;
				}
				$lessons[] = array(
					'title'    => sanitize_text_field( $lesson['title'] ),
					'duration' => sanitize_text_field( $lesson['duration'] ?? '' ),
					'video'    => esc_url_raw( $lesson['video'] ?? '' ),
					'free'     => ! empty( $lesson['free'] ) ? 1 : 0,
				);
			}

			$clean[] = array(
				'title'   => sanitize_text_field( $section['title'] ),
				'lessons' => $lessons,
			);
		}

		update_post_meta( $post_id, '_zc_curriculum', $clean );
	}
}
add_action( 'save_post', 'zc_save_metaboxes' );

/* ==========================================================================
   متاباکس‌های سیستم خدمات
   ========================================================================== */

/**
 * ثبت متاباکس‌های خدمات، نمونه‌کار و درخواست پروژه.
 *
 * @return void
 */
function zc_register_service_metaboxes() {
	add_meta_box( 'zc_service_details', __( 'مشخصات خدمت', 'zarincode' ), 'zc_service_metabox', 'zc_service', 'normal', 'high' );
	add_meta_box( 'zc_service_packages', __( 'بسته‌های قیمتی', 'zarincode' ), 'zc_packages_metabox', 'zc_service', 'normal', 'high' );
	// متاباکس نمونه‌کار به inc/modules/details.php منتقل شد (طرح‌داده‌محور).
	add_meta_box( 'zc_request_details', __( 'اطلاعات درخواست', 'zarincode' ), 'zc_request_metabox', 'zc_request', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'zc_register_service_metaboxes' );

/**
 * متاباکس مشخصات خدمت.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_service_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$icon     = get_post_meta( $post->ID, '_zc_service_icon', true );
	$from     = get_post_meta( $post->ID, '_zc_service_price_from', true );
	$duration = get_post_meta( $post->ID, '_zc_service_duration', true );
	$color    = get_post_meta( $post->ID, '_zc_service_color', true );
	$features = get_post_meta( $post->ID, '_zc_features', true );
	$features = is_array( $features ) ? implode( "\n", $features ) : '';
	$icons    = array_keys( zc_icon_library() );
	?>
	<table class="form-table zc-metabox">
		<tr>
			<th><label for="_zc_service_icon"><?php esc_html_e( 'آیکن', 'zarincode' ); ?></label></th>
			<td>
				<select name="_zc_service_icon" id="_zc_service_icon">
					<?php foreach ( $icons as $ic ) : ?>
						<option value="<?php echo esc_attr( $ic ); ?>" <?php selected( $icon, $ic ); ?>><?php echo esc_html( $ic ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'آیکنی که در کارت خدمت نمایش داده می‌شود.', 'zarincode' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_service_price_from"><?php esc_html_e( 'شروع قیمت (تومان)', 'zarincode' ); ?></label></th>
			<td><input type="number" name="_zc_service_price_from" id="_zc_service_price_from" value="<?php echo esc_attr( $from ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="_zc_service_duration"><?php esc_html_e( 'مدت زمان تحویل', 'zarincode' ); ?></label></th>
			<td><input type="text" name="_zc_service_duration" id="_zc_service_duration" value="<?php echo esc_attr( $duration ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'مثلاً ۲ تا ۴ هفته', 'zarincode' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="_zc_service_color"><?php esc_html_e( 'رنگ شاخص', 'zarincode' ); ?></label></th>
			<td><input type="text" name="_zc_service_color" id="_zc_service_color" value="<?php echo esc_attr( $color ); ?>" class="regular-text" placeholder="#C9A227" /></td>
		</tr>
		<tr>
			<th><label for="_zc_features"><?php esc_html_e( 'موارد شامل خدمت', 'zarincode' ); ?></label></th>
			<td>
				<textarea name="_zc_features" id="_zc_features" rows="6" class="large-text"><?php echo esc_textarea( $features ); ?></textarea>
				<p class="description"><?php esc_html_e( 'هر مورد در یک خط جداگانه.', 'zarincode' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_service_steps"><?php esc_html_e( 'مراحل انجام کار', 'zarincode' ); ?></label></th>
			<td>
				<textarea name="_zc_service_steps" id="_zc_service_steps" rows="5" class="large-text"><?php echo esc_textarea( get_post_meta( $post->ID, '_zc_service_steps', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'هر خط: عنوان مرحله | توضیح کوتاه — مثال: تحلیل نیاز | بررسی کسب‌وکار و تدوین نقشه راه', 'zarincode' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_service_stats"><?php esc_html_e( 'آمار خدمت', 'zarincode' ); ?></label></th>
			<td>
				<textarea name="_zc_service_stats" id="_zc_service_stats" rows="3" class="large-text"><?php echo esc_textarea( get_post_meta( $post->ID, '_zc_service_stats', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'هر خط: برچسب | مقدار — مثال: پروژه انجام‌شده | +۱۲۰', 'zarincode' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_service_tools"><?php esc_html_e( 'ابزارها و تکنولوژی‌ها', 'zarincode' ); ?></label></th>
			<td>
				<textarea name="_zc_service_tools" id="_zc_service_tools" rows="3" class="large-text"><?php echo esc_textarea( get_post_meta( $post->ID, '_zc_service_tools', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'هر ابزار در یک خط. در ستون کناری نمایش داده می‌شود.', 'zarincode' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_service_faq"><?php esc_html_e( 'پرسش‌های متداول', 'zarincode' ); ?></label></th>
			<td>
				<textarea name="_zc_service_faq" id="_zc_service_faq" rows="5" class="large-text"><?php echo esc_textarea( get_post_meta( $post->ID, '_zc_service_faq', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'هر خط: پرسش | پاسخ', 'zarincode' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * متاباکس بسته‌های قیمتی خدمت.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_packages_metabox( $post ) {
	$packages = get_post_meta( $post->ID, '_zc_packages', true );
	$packages = is_array( $packages ) ? $packages : array();
	?>
	<div id="zc-packages">
		<?php foreach ( $packages as $i => $pkg ) : ?>
			<div class="zc-pkg">
				<div class="zc-pkg__row">
					<input type="text" name="zc_packages[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $pkg['title'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'نام بسته (مثلاً پایه)', 'zarincode' ); ?>" />
					<input type="number" name="zc_packages[<?php echo (int) $i; ?>][price]" value="<?php echo esc_attr( $pkg['price'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'قیمت', 'zarincode' ); ?>" />
					<input type="text" name="zc_packages[<?php echo (int) $i; ?>][delivery]" value="<?php echo esc_attr( $pkg['delivery'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'زمان تحویل', 'zarincode' ); ?>" />
					<label class="zc-pkg__pop">
						<input type="checkbox" name="zc_packages[<?php echo (int) $i; ?>][popular]" value="1" <?php checked( ! empty( $pkg['popular'] ) ); ?> />
						<?php esc_html_e( 'پیشنهاد ویژه', 'zarincode' ); ?>
					</label>
					<button type="button" class="button zc-remove-pkg"><?php esc_html_e( 'حذف', 'zarincode' ); ?></button>
				</div>
				<textarea name="zc_packages[<?php echo (int) $i; ?>][features]" rows="4" placeholder="<?php esc_attr_e( 'امکانات این بسته — هر مورد در یک خط', 'zarincode' ); ?>"><?php echo esc_textarea( is_array( $pkg['features'] ?? '' ) ? implode( "\n", $pkg['features'] ) : ( $pkg['features'] ?? '' ) ); ?></textarea>
			</div>
		<?php endforeach; ?>
	</div>

	<p><button type="button" class="button button-primary" id="zc-add-pkg"><?php esc_html_e( '+ افزودن بسته', 'zarincode' ); ?></button></p>

	<style>
		.zc-pkg{background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:12px;margin-bottom:12px}
		.zc-pkg__row{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:8px}
		.zc-pkg__row input[type=text],.zc-pkg__row input[type=number]{flex:1;min-width:130px}
		.zc-pkg__pop{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
		.zc-pkg textarea{width:100%}
	</style>

	<script>
		( function () {
			var wrap = document.getElementById( 'zc-packages' );
			var add  = document.getElementById( 'zc-add-pkg' );

			if ( ! wrap || ! add ) { return; }

			add.addEventListener( 'click', function () {
				var i = wrap.querySelectorAll( '.zc-pkg' ).length;
				var d = document.createElement( 'div' );
				d.className = 'zc-pkg';
				d.innerHTML =
					'<div class="zc-pkg__row">' +
					'<input type="text" name="zc_packages[' + i + '][title]" placeholder="<?php echo esc_js( __( 'نام بسته', 'zarincode' ) ); ?>" />' +
					'<input type="number" name="zc_packages[' + i + '][price]" placeholder="<?php echo esc_js( __( 'قیمت', 'zarincode' ) ); ?>" />' +
					'<input type="text" name="zc_packages[' + i + '][delivery]" placeholder="<?php echo esc_js( __( 'زمان تحویل', 'zarincode' ) ); ?>" />' +
					'<label class="zc-pkg__pop"><input type="checkbox" name="zc_packages[' + i + '][popular]" value="1" /> <?php echo esc_js( __( 'پیشنهاد ویژه', 'zarincode' ) ); ?></label>' +
					'<button type="button" class="button zc-remove-pkg"><?php echo esc_js( __( 'حذف', 'zarincode' ) ); ?></button>' +
					'</div>' +
					'<textarea name="zc_packages[' + i + '][features]" rows="4" placeholder="<?php echo esc_js( __( 'امکانات — هر مورد در یک خط', 'zarincode' ) ); ?>"></textarea>';
				wrap.appendChild( d );
			} );

			wrap.addEventListener( 'click', function ( e ) {
				if ( e.target.classList.contains( 'zc-remove-pkg' ) ) {
					e.target.closest( '.zc-pkg' ).remove();
				}
			} );
		}() );
	</script>
	<?php
}


/**
 * متاباکس اطلاعات درخواست پروژه.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_request_metabox( $post ) {
	wp_nonce_field( 'zc_meta_save', 'zc_meta_nonce' );

	$statuses = zc_request_statuses();
	$budgets  = zc_request_budgets();
	$dead     = zc_request_deadlines();
	$status   = get_post_meta( $post->ID, '_zc_req_status', true );
	$sid      = (int) get_post_meta( $post->ID, '_zc_req_service', true );
	$quote    = get_post_meta( $post->ID, '_zc_req_quote', true );
	$note     = get_post_meta( $post->ID, '_zc_req_note', true );
	?>
	<table class="form-table zc-metabox">
		<tr>
			<th><?php esc_html_e( 'نام مشتری', 'zarincode' ); ?></th>
			<td><strong><?php echo esc_html( get_post_meta( $post->ID, '_zc_req_name', true ) ); ?></strong></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'موبایل', 'zarincode' ); ?></th>
			<td>
				<a href="tel:<?php echo esc_attr( get_post_meta( $post->ID, '_zc_req_mobile', true ) ); ?>" dir="ltr">
					<?php echo esc_html( get_post_meta( $post->ID, '_zc_req_mobile', true ) ); ?>
				</a>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></th>
			<td dir="ltr"><?php echo esc_html( get_post_meta( $post->ID, '_zc_req_email', true ) ? get_post_meta( $post->ID, '_zc_req_email', true ) : '—' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'خدمت درخواستی', 'zarincode' ); ?></th>
			<td><?php echo $sid ? esc_html( get_the_title( $sid ) ) : '—'; ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'بودجه', 'zarincode' ); ?></th>
			<td><?php echo esc_html( $budgets[ get_post_meta( $post->ID, '_zc_req_budget', true ) ] ?? '—' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'زمان تحویل', 'zarincode' ); ?></th>
			<td><?php echo esc_html( $dead[ get_post_meta( $post->ID, '_zc_req_deadline', true ) ] ?? '—' ); ?></td>
		</tr>
		<tr>
			<th><label for="_zc_req_status"><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></label></th>
			<td>
				<select name="_zc_req_status" id="_zc_req_status">
					<?php foreach ( $statuses as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="_zc_req_quote"><?php esc_html_e( 'مبلغ پیشنهادی (تومان)', 'zarincode' ); ?></label></th>
			<td><input type="number" name="_zc_req_quote" id="_zc_req_quote" value="<?php echo esc_attr( $quote ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="_zc_req_note"><?php esc_html_e( 'یادداشت داخلی', 'zarincode' ); ?></label></th>
			<td><textarea name="_zc_req_note" id="_zc_req_note" rows="4" class="large-text"><?php echo esc_textarea( $note ); ?></textarea></td>
		</tr>
	</table>
	<?php
}

/**
 * ذخیره‌ی متاهای سیستم خدمات.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_save_service_metaboxes( $post_id ) {
	if ( ! isset( $_POST['zc_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zc_meta_nonce'] ) ), 'zc_meta_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// فیلدهای نمونه‌کار در inc/modules/details.php ذخیره می‌شوند.
	$simple = array(
		'_zc_service_icon', '_zc_service_price_from', '_zc_service_duration', '_zc_service_color',
		'_zc_req_status', '_zc_req_quote',
	);

	// فیلدهای چندخطی خدمت.
	foreach ( array( '_zc_service_steps', '_zc_service_stats', '_zc_service_tools', '_zc_service_faq' ) as $zc_multi ) {
		if ( isset( $_POST[ $zc_multi ] ) ) {
			$zc_val = sanitize_textarea_field( wp_unslash( $_POST[ $zc_multi ] ) );

			if ( '' === trim( $zc_val ) ) {
				delete_post_meta( $post_id, $zc_multi );
			} else {
				update_post_meta( $post_id, $zc_multi, $zc_val );
			}
		}
	}

	foreach ( $simple as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	if ( isset( $_POST['_zc_req_note'] ) ) {
		update_post_meta( $post_id, '_zc_req_note', sanitize_textarea_field( wp_unslash( $_POST['_zc_req_note'] ) ) );
	}

	// بسته‌های قیمتی.
	if ( isset( $_POST['zc_packages'] ) && is_array( $_POST['zc_packages'] ) ) {
		$clean = array();

		foreach ( wp_unslash( $_POST['zc_packages'] ) as $pkg ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( empty( $pkg['title'] ) ) {
				continue;
			}

			$features = array_filter( array_map( 'trim', explode( "\n", (string) ( $pkg['features'] ?? '' ) ) ) );

			$clean[] = array(
				'title'    => sanitize_text_field( $pkg['title'] ),
				'price'    => (int) ( $pkg['price'] ?? 0 ),
				'delivery' => sanitize_text_field( $pkg['delivery'] ?? '' ),
				'popular'  => ! empty( $pkg['popular'] ) ? 1 : 0,
				'features' => array_map( 'sanitize_text_field', array_values( $features ) ),
			);
		}

		update_post_meta( $post_id, '_zc_packages', $clean );
	}
}
add_action( 'save_post_zc_service', 'zc_save_service_metaboxes' );
add_action( 'save_post_zc_project', 'zc_save_service_metaboxes' );
add_action( 'save_post_zc_request', 'zc_save_service_metaboxes' );
