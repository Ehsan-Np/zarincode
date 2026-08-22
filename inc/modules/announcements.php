<?php
/**
 * سامانه‌ی اطلاعیه‌ها
 *
 * مدیر یک اطلاعیه می‌نویسد و انتخاب می‌کند کجا منتشر شود:
 *  • نوار اطلاعیه بالای سایت
 *  • پنجره‌ی شناور (مودال) هنگام ورود
 *  • صندوق اطلاعیه در پنل کاربری
 *  • تلگرام و بله از طریق ربات سایت
 *  • پیامک
 *
 * هر اطلاعیه می‌تواند بازه‌ی زمانی نمایش، اولویت، رنگ، آیکون، دکمه‌ی
 * فراخوان و مخاطب هدف (همه / فقط اعضا / فقط مهمان‌ها / نقش خاص) داشته
 * باشد. کاربر می‌تواند اطلاعیه را ببندد و دیگر نبیند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ۱. نوع محتوا
   ========================================================================== */

/**
 * ثبت نوع محتوای اطلاعیه.
 *
 * @return void
 */
function zc_register_announcement_cpt() {
	register_post_type(
		'zc_announce',
		array(
			'labels'          => array(
				'name'               => __( 'اطلاعیه‌ها', 'zarincode' ),
				'singular_name'      => __( 'اطلاعیه', 'zarincode' ),
				'add_new'            => __( 'اطلاعیه تازه', 'zarincode' ),
				'add_new_item'       => __( 'افزودن اطلاعیه تازه', 'zarincode' ),
				'edit_item'          => __( 'ویرایش اطلاعیه', 'zarincode' ),
				'all_items'          => __( 'همه اطلاعیه‌ها', 'zarincode' ),
				'search_items'       => __( 'جستجوی اطلاعیه', 'zarincode' ),
				'not_found'          => __( 'اطلاعیه‌ای یافت نشد.', 'zarincode' ),
				'menu_name'          => __( 'اطلاعیه‌ها', 'zarincode' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'zarincode',
			'supports'        => array( 'title', 'editor' ),
			'menu_icon'       => 'dashicons-megaphone',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'zc_register_announcement_cpt' );

/* ==========================================================================
   ۲. تعریف فیلدها
   ========================================================================== */

/**
 * سبک‌های رنگی اطلاعیه.
 *
 * @return array
 */
function zc_announce_styles() {
	return array(
		'info'    => array(
			'label' => __( 'اطلاع‌رسانی (آبی)', 'zarincode' ),
			'icon'  => 'info',
		),
		'success' => array(
			'label' => __( 'موفقیت (سبز)', 'zarincode' ),
			'icon'  => 'check',
		),
		'warning' => array(
			'label' => __( 'هشدار (کهربایی)', 'zarincode' ),
			'icon'  => 'alert',
		),
		'danger'  => array(
			'label' => __( 'مهم (قرمز)', 'zarincode' ),
			'icon'  => 'alert',
		),
		'gold'    => array(
			'label' => __( 'ویژه (طلایی)', 'zarincode' ),
			'icon'  => 'sparkle',
		),
	);
}

/**
 * مخاطبان هدف.
 *
 * @return array
 */
function zc_announce_audiences() {
	return array(
		'all'      => __( 'همه بازدیدکنندگان', 'zarincode' ),
		'members'  => __( 'فقط کاربران وارد‌شده', 'zarincode' ),
		'guests'   => __( 'فقط مهمان‌ها', 'zarincode' ),
		'customer' => __( 'فقط مشتریان', 'zarincode' ),
	);
}

/**
 * محل‌های نمایش.
 *
 * @return array
 */
function zc_announce_placements() {
	return array(
		'bar'    => __( 'نوار بالای سایت', 'zarincode' ),
		'modal'  => __( 'پنجره شناور', 'zarincode' ),
		'panel'  => __( 'صندوق اطلاعیه پنل کاربری', 'zarincode' ),
	);
}

/* ==========================================================================
   ۳. متاباکس
   ========================================================================== */

/**
 * ثبت متاباکس اطلاعیه.
 *
 * @return void
 */
function zc_announce_metabox() {
	add_meta_box(
		'zc_announce_settings',
		__( 'تنظیمات اطلاعیه', 'zarincode' ),
		'zc_announce_metabox_html',
		'zc_announce',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zc_announce_metabox' );

/**
 * محتوای متاباکس.
 *
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_announce_metabox_html( $post ) {
	wp_nonce_field( 'zc_announce_save', 'zc_announce_nonce' );

	$get = static function ( $key, $default = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $key, true );
		return '' === $v ? $default : $v;
	};

	$placements = (array) get_post_meta( $post->ID, '_zc_an_placements', true );
	$placements = $placements ? $placements : array( 'bar' );
	?>
	<div class="zc-an-box">

		<div class="zc-an-row">
			<div class="zc-an-field">
				<label for="zc_an_style"><?php esc_html_e( 'سبک رنگی', 'zarincode' ); ?></label>
				<select name="_zc_an_style" id="zc_an_style">
					<?php foreach ( zc_announce_styles() as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $get( '_zc_an_style', 'info' ), $k ); ?>>
							<?php echo esc_html( $v['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-an-field">
				<label for="zc_an_audience"><?php esc_html_e( 'مخاطب', 'zarincode' ); ?></label>
				<select name="_zc_an_audience" id="zc_an_audience">
					<?php foreach ( zc_announce_audiences() as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $get( '_zc_an_audience', 'all' ), $k ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-an-field">
				<label for="zc_an_priority"><?php esc_html_e( 'اولویت', 'zarincode' ); ?></label>
				<input type="number" name="_zc_an_priority" id="zc_an_priority"
					value="<?php echo esc_attr( $get( '_zc_an_priority', '10' ) ); ?>" min="1" max="99" />
				<p class="description"><?php esc_html_e( 'عدد کمتر = بالاتر.', 'zarincode' ); ?></p>
			</div>
		</div>

		<div class="zc-an-row">
			<div class="zc-an-field">
				<label for="zc_an_start"><?php esc_html_e( 'شروع نمایش', 'zarincode' ); ?></label>
				<input type="datetime-local" name="_zc_an_start" id="zc_an_start"
					value="<?php echo esc_attr( $get( '_zc_an_start' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'خالی یعنی از همین حالا.', 'zarincode' ); ?></p>
			</div>

			<div class="zc-an-field">
				<label for="zc_an_end"><?php esc_html_e( 'پایان نمایش', 'zarincode' ); ?></label>
				<input type="datetime-local" name="_zc_an_end" id="zc_an_end"
					value="<?php echo esc_attr( $get( '_zc_an_end' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'خالی یعنی بدون انقضا.', 'zarincode' ); ?></p>
			</div>

			<div class="zc-an-field">
				<label for="zc_an_icon"><?php esc_html_e( 'آیکون', 'zarincode' ); ?></label>
				<select name="_zc_an_icon" id="zc_an_icon">
					<option value=""><?php esc_html_e( '— پیش‌فرض سبک —', 'zarincode' ); ?></option>
					<?php foreach ( array_keys( zc_icon_library() ) as $ic ) : ?>
						<option value="<?php echo esc_attr( $ic ); ?>" <?php selected( $get( '_zc_an_icon' ), $ic ); ?>>
							<?php echo esc_html( $ic ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="zc-an-row">
			<div class="zc-an-field">
				<label for="zc_an_btn_text"><?php esc_html_e( 'متن دکمه', 'zarincode' ); ?></label>
				<input type="text" name="_zc_an_btn_text" id="zc_an_btn_text"
					value="<?php echo esc_attr( $get( '_zc_an_btn_text' ) ); ?>"
					placeholder="<?php esc_attr_e( 'مثلاً مشاهده جزئیات', 'zarincode' ); ?>" />
			</div>

			<div class="zc-an-field">
				<label for="zc_an_btn_url"><?php esc_html_e( 'لینک دکمه', 'zarincode' ); ?></label>
				<input type="url" name="_zc_an_btn_url" id="zc_an_btn_url" dir="ltr"
					value="<?php echo esc_attr( $get( '_zc_an_btn_url' ) ); ?>" placeholder="https://" />
			</div>

			<div class="zc-an-field">
				<label>
					<input type="checkbox" name="_zc_an_dismissible" value="1"
						<?php checked( $get( '_zc_an_dismissible', '1' ), '1' ); ?> />
					<?php esc_html_e( 'کاربر بتواند ببندد', 'zarincode' ); ?>
				</label>

				<label style="margin-top:8px">
					<input type="checkbox" name="_zc_an_sticky" value="1"
						<?php checked( $get( '_zc_an_sticky' ), '1' ); ?> />
					<?php esc_html_e( 'نوار چسبان بماند', 'zarincode' ); ?>
				</label>
			</div>
		</div>

		<hr />

		<div class="zc-an-row">
			<div class="zc-an-field zc-an-field--wide">
				<label><?php esc_html_e( 'محل نمایش', 'zarincode' ); ?></label>

				<div class="zc-an-checks">
					<?php foreach ( zc_announce_placements() as $k => $v ) : ?>
						<label>
							<input type="checkbox" name="_zc_an_placements[]" value="<?php echo esc_attr( $k ); ?>"
								<?php checked( in_array( $k, $placements, true ) ); ?> />
							<?php echo esc_html( $v ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<hr />

		<div class="zc-an-row">
			<div class="zc-an-field zc-an-field--wide">
				<label><?php esc_html_e( 'ارسال به پیام‌رسان‌ها و پیامک', 'zarincode' ); ?></label>

				<div class="zc-an-checks">
					<label>
						<input type="checkbox" name="_zc_an_send_bot" value="1"
							<?php checked( $get( '_zc_an_send_bot' ), '1' ); ?> />
						<?php esc_html_e( 'ارسال در تلگرام و بله (به کاربرانی که ربات را وصل کرده‌اند)', 'zarincode' ); ?>
					</label>

					<label>
						<input type="checkbox" name="_zc_an_send_sms" value="1"
							<?php checked( $get( '_zc_an_send_sms' ), '1' ); ?> />
						<?php esc_html_e( 'ارسال پیامک', 'zarincode' ); ?>
					</label>
				</div>

				<p class="description">
					<?php esc_html_e( 'ارسال یک‌بار و هنگام انتشار اطلاعیه انجام می‌شود. برای ارسال دوباره از دکمه‌ی زیر استفاده کنید.', 'zarincode' ); ?>
				</p>

				<?php
				$sent_at = get_post_meta( $post->ID, '_zc_an_sent_at', true );
				$sent_n  = (int) get_post_meta( $post->ID, '_zc_an_sent_count', true );

				if ( $sent_at ) :
					?>
					<p class="zc-an-sent">
						<?php
						printf(
							/* translators: 1: تاریخ 2: تعداد */
							esc_html__( 'آخرین ارسال: %1$s — %2$s پیام', 'zarincode' ),
							esc_html( zc_fa_num( mysql2date( 'j F Y ساعت H:i', $sent_at ) ) ),
							esc_html( zc_fa_num( $sent_n ) )
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( 'publish' === $post->post_status ) : ?>
					<button type="button" class="button zc-an-resend" data-id="<?php echo (int) $post->ID; ?>">
						<?php esc_html_e( 'ارسال دستی همین حالا', 'zarincode' ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<script>
		jQuery( function ( $ ) {
			$( '.zc-an-resend' ).on( 'click', function () {
				if ( ! confirm( '<?php echo esc_js( __( 'اطلاعیه برای همه‌ی گیرندگان ارسال شود؟', 'zarincode' ) ); ?>' ) ) {
					return;
				}

				var b = $( this ).prop( 'disabled', true );

				$.post( ajaxurl, {
					action: 'zc_announce_send',
					nonce: '<?php echo esc_js( wp_create_nonce( 'zc_admin_nonce' ) ); ?>',
					id: b.data( 'id' )
				} ).done( function ( r ) {
					alert( r.data.message );
				} ).always( function () {
					b.prop( 'disabled', false );
				} );
			} );
		} );
	</script>
	<?php
}

/**
 * ذخیره‌ی فیلدهای اطلاعیه.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_announce_save( $post_id ) {
	if ( ! isset( $_POST['zc_announce_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zc_announce_nonce'] ) ), 'zc_announce_save' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array(
		'_zc_an_style',
		'_zc_an_audience',
		'_zc_an_priority',
		'_zc_an_start',
		'_zc_an_end',
		'_zc_an_icon',
		'_zc_an_btn_text',
	);

	foreach ( $text_keys as $key ) {
		$val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

		if ( '' === $val ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $val );
		}
	}

	$url = isset( $_POST['_zc_an_btn_url'] ) ? esc_url_raw( wp_unslash( $_POST['_zc_an_btn_url'] ) ) : '';

	if ( $url ) {
		update_post_meta( $post_id, '_zc_an_btn_url', $url );
	} else {
		delete_post_meta( $post_id, '_zc_an_btn_url' );
	}

	// چک‌باکس‌ها.
	foreach ( array( '_zc_an_dismissible', '_zc_an_sticky', '_zc_an_send_bot', '_zc_an_send_sms' ) as $flag ) {
		if ( ! empty( $_POST[ $flag ] ) ) {
			update_post_meta( $post_id, $flag, '1' );
		} else {
			delete_post_meta( $post_id, $flag );
		}
	}

	// محل نمایش.
	$places = isset( $_POST['_zc_an_placements'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['_zc_an_placements'] ) ) : array();
	$valid  = array_keys( zc_announce_placements() );
	$places = array_values( array_intersect( $places, $valid ) );

	update_post_meta( $post_id, '_zc_an_placements', $places );
}
add_action( 'save_post_zc_announce', 'zc_announce_save' );

/* ==========================================================================
   ۴. واکشی اطلاعیه‌های فعال
   ========================================================================== */

/**
 * آیا این اطلاعیه هم‌اکنون برای کاربر جاری قابل نمایش است؟
 *
 * @param int    $id        شناسه اطلاعیه.
 * @param string $placement محل نمایش.
 * @return bool
 */
function zc_announce_is_visible( $id, $placement = '' ) {
	// بازه‌ی زمانی.
	$now   = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
	$start = get_post_meta( $id, '_zc_an_start', true );
	$end   = get_post_meta( $id, '_zc_an_end', true );

	if ( $start && strtotime( $start ) > $now ) {
		return false;
	}

	if ( $end && strtotime( $end ) < $now ) {
		return false;
	}

	// محل نمایش.
	if ( $placement ) {
		$places = (array) get_post_meta( $id, '_zc_an_placements', true );

		if ( ! in_array( $placement, $places, true ) ) {
			return false;
		}
	}

	// مخاطب.
	$audience = get_post_meta( $id, '_zc_an_audience', true );
	$logged   = is_user_logged_in();

	if ( 'members' === $audience && ! $logged ) {
		return false;
	}

	if ( 'guests' === $audience && $logged ) {
		return false;
	}

	if ( 'customer' === $audience && ( ! $logged || ! current_user_can( 'read' ) ) ) {
		return false;
	}

	// اگر کاربر آن را بسته باشد.
	if ( zc_announce_is_dismissed( $id ) ) {
		return false;
	}

	/**
	 * فیلتر نمایش اطلاعیه.
	 *
	 * @param bool   $visible   قابل نمایش.
	 * @param int    $id        شناسه.
	 * @param string $placement محل.
	 */
	return apply_filters( 'zc_announce_is_visible', true, $id, $placement );
}

/**
 * آیا کاربر این اطلاعیه را بسته است؟
 *
 * برای کاربر وارد‌شده در متا و برای مهمان در کوکی نگهداری می‌شود.
 *
 * @param int $id شناسه.
 * @return bool
 */
function zc_announce_is_dismissed( $id ) {
	if ( is_user_logged_in() ) {
		$list = (array) get_user_meta( get_current_user_id(), 'zc_announce_dismissed', true );

		if ( in_array( (int) $id, array_map( 'intval', $list ), true ) ) {
			return true;
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_COOKIE[ 'zc_an_' . (int) $id ] ) ) {
		return true;
	}

	return false;
}

/**
 * واکشی اطلاعیه‌های فعال یک محل.
 *
 * @param string $placement محل نمایش.
 * @param int    $limit     تعداد.
 * @return array آرایه‌ای از WP_Post.
 */
function zc_get_announcements( $placement = 'bar', $limit = 5 ) {
	$query = new WP_Query(
		array(
			'post_type'           => 'zc_announce',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $limit ) * 4,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_key'            => '_zc_an_priority', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'             => array(
				'meta_value_num' => 'ASC',
				'date'           => 'DESC',
			),
		)
	);

	$out = array();

	foreach ( $query->posts as $post ) {
		if ( count( $out ) >= $limit ) {
			break;
		}

		if ( zc_announce_is_visible( $post->ID, $placement ) ) {
			$out[] = $post;
		}
	}

	return $out;
}

/**
 * داده‌های آماده‌ی نمایش یک اطلاعیه.
 *
 * @param int|WP_Post $post اطلاعیه.
 * @return array
 */
function zc_announce_data( $post ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	$styles = zc_announce_styles();
	$style  = get_post_meta( $post->ID, '_zc_an_style', true );
	$style  = isset( $styles[ $style ] ) ? $style : 'info';
	$icon   = get_post_meta( $post->ID, '_zc_an_icon', true );

	/*
	 * از apply_filters('the_content') استفاده نمی‌کنیم: این فیلتر در
	 * سمت کاربر شامل هوک‌های المنتور و ووکامرس است و چون اطلاعیه در
	 * فوتر (خارج از حلقه) رندر می‌شود، کل صفحه را دوباره داخل خودش
	 * می‌ریخت. wpautop برای متن ساده کافی است.
	 */
	$content = wpautop( wp_kses_post( $post->post_content ) );

	return array(
		'id'          => $post->ID,
		'title'       => get_the_title( $post ),
		'content'     => $content,
		'text'        => wp_strip_all_tags( $post->post_content ),
		'style'       => $style,
		'icon'        => $icon ? $icon : $styles[ $style ]['icon'],
		'btn_text'    => get_post_meta( $post->ID, '_zc_an_btn_text', true ),
		'btn_url'     => get_post_meta( $post->ID, '_zc_an_btn_url', true ),
		'dismissible' => '1' === get_post_meta( $post->ID, '_zc_an_dismissible', true ),
		'sticky'      => '1' === get_post_meta( $post->ID, '_zc_an_sticky', true ),
		'date'        => get_the_date( '', $post ),
	);
}

/* ==========================================================================
   ۵. نمایش در سمت کاربر
   ========================================================================== */

/**
 * نوار اطلاعیه بالای سایت.
 *
 * @return void
 */
function zc_render_announcement_bar() {
	if ( is_admin() ) {
		return;
	}

	$items = zc_get_announcements( 'bar', 3 );

	if ( ! $items ) {
		return;
	}

	$multi = count( $items ) > 1;
	?>
	<div class="zc-anbar-wrap"<?php echo $multi ? ' data-zc-anbar' : ''; ?>>
		<?php
		foreach ( $items as $i => $post ) :
			$a = zc_announce_data( $post );
			?>
			<div class="zc-anbar zc-anbar--<?php echo esc_attr( $a['style'] ); ?><?php echo $a['sticky'] ? ' is-sticky' : ''; ?><?php echo ( $multi && $i > 0 ) ? ' is-hidden' : ''; ?>"
				data-id="<?php echo (int) $a['id']; ?>" role="status">

				<div class="zc-container zc-anbar__in">
					<span class="zc-anbar__icon"><?php zc_the_icon( $a['icon'], 18 ); ?></span>

					<span class="zc-anbar__text">
						<strong><?php echo esc_html( $a['title'] ); ?></strong>
						<?php if ( $a['text'] ) : ?>
							<em><?php echo esc_html( wp_trim_words( $a['text'], 18 ) ); ?></em>
						<?php endif; ?>
					</span>

					<?php if ( $a['btn_text'] && $a['btn_url'] ) : ?>
						<a class="zc-anbar__btn" href="<?php echo esc_url( $a['btn_url'] ); ?>">
							<?php echo esc_html( $a['btn_text'] ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $a['dismissible'] ) : ?>
						<button type="button" class="zc-anbar__close" data-zc-an-dismiss="<?php echo (int) $a['id']; ?>"
							aria-label="<?php esc_attr_e( 'بستن اطلاعیه', 'zarincode' ); ?>">
							<?php zc_the_icon( 'close', 16 ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
add_action( 'zc_before_header', 'zc_render_announcement_bar', 5 );

/**
 * آیا صفحه‌ی جاری پنل کاربری است؟
 *
 * @return bool
 */
function zc_is_panel_page() {
	if ( ! is_page() ) {
		return false;
	}

	$template = get_page_template_slug();

	if ( $template && false !== strpos( $template, 'template-panel' ) ) {
		return true;
	}

	// شناسایی از روی گزینه‌ی برگه‌ی پنل.
	$panel_id = (int) zc_opt( 'zc_panel_page', 0 );

	return $panel_id && is_page( $panel_id );
}

/**
 * پنجره‌ی شناور اطلاعیه.
 *
 * @return void
 */
function zc_render_announcement_modal() {
	if ( is_admin() ) {
		return;
	}

	/*
	 * در پنل کاربری، صندوق اطلاعیه‌ها خودش همان محتوا را نشان می‌دهد؛
	 * باز شدن پنجره‌ی شناور روی آن آزاردهنده است. همچنین در صفحه‌ی
	 * پرداخت و ورود، پنجره نباید مزاحم فرایند شود.
	 */
	if ( zc_is_panel_page() ) {
		return;
	}

	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() ) ) {
		return;
	}

	$items = zc_get_announcements( 'modal', 1 );

	if ( ! $items ) {
		return;
	}

	$a = zc_announce_data( $items[0] );
	?>
	<div class="zc-anmodal" data-zc-anmodal data-id="<?php echo (int) $a['id']; ?>" aria-hidden="true">
		<div class="zc-anmodal__backdrop" data-zc-an-close></div>

		<div class="zc-anmodal__box zc-anmodal__box--<?php echo esc_attr( $a['style'] ); ?>"
			role="dialog" aria-modal="true" aria-labelledby="zc-anmodal-title">

			<button type="button" class="zc-anmodal__close" data-zc-an-close
				aria-label="<?php esc_attr_e( 'بستن', 'zarincode' ); ?>">
				<?php zc_the_icon( 'close', 18 ); ?>
			</button>

			<span class="zc-anmodal__icon"><?php zc_the_icon( $a['icon'], 30 ); ?></span>

			<h3 class="zc-anmodal__title" id="zc-anmodal-title"><?php echo esc_html( $a['title'] ); ?></h3>

			<div class="zc-anmodal__body"><?php echo wp_kses_post( $a['content'] ); ?></div>

			<div class="zc-anmodal__foot">
				<?php if ( $a['btn_text'] && $a['btn_url'] ) : ?>
					<a class="zc-btn zc-btn--gold" href="<?php echo esc_url( $a['btn_url'] ); ?>">
						<?php echo esc_html( $a['btn_text'] ); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="zc-btn zc-btn--ghost" data-zc-an-dismiss="<?php echo (int) $a['id']; ?>">
					<?php esc_html_e( 'متوجه شدم', 'zarincode' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'zc_render_announcement_modal', 20 );

/* ==========================================================================
   ۶. بستن اطلاعیه (AJAX)
   ========================================================================== */

/**
 * ثبت بسته‌شدن اطلاعیه.
 *
 * @return void
 */
function zc_ajax_announce_dismiss() {
	zc_check_ajax();

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

	if ( ! $id ) {
		wp_send_json_error( array( 'message' => __( 'شناسه نامعتبر.', 'zarincode' ) ) );
	}

	if ( is_user_logged_in() ) {
		$uid  = get_current_user_id();
		$list = (array) get_user_meta( $uid, 'zc_announce_dismissed', true );
		$list = array_map( 'intval', $list );

		if ( ! in_array( $id, $list, true ) ) {
			$list[] = $id;
		}

		update_user_meta( $uid, 'zc_announce_dismissed', array_slice( $list, -100 ) );
	}

	// برای مهمان‌ها کوکی ۳۰ روزه.
	setcookie( 'zc_an_' . $id, '1', time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );

	wp_send_json_success( array( 'id' => $id ) );
}
add_action( 'wp_ajax_zc_announce_dismiss', 'zc_ajax_announce_dismiss' );
add_action( 'wp_ajax_nopriv_zc_announce_dismiss', 'zc_ajax_announce_dismiss' );

/* ==========================================================================
   ۷. ارسال به پیام‌رسان و پیامک
   ========================================================================== */

/**
 * ارسال اطلاعیه به گیرندگان بیرونی.
 *
 * @param int $id شناسه اطلاعیه.
 * @return int تعداد پیام‌های ارسال‌شده.
 */
function zc_announce_broadcast( $id ) {
	$post = get_post( $id );

	if ( ! $post || 'zc_announce' !== $post->post_type ) {
		return 0;
	}

	$send_bot = '1' === get_post_meta( $id, '_zc_an_send_bot', true );
	$send_sms = '1' === get_post_meta( $id, '_zc_an_send_sms', true );

	if ( ! $send_bot && ! $send_sms ) {
		return 0;
	}

	$a       = zc_announce_data( $post );
	$btn_url = $a['btn_url'] ? $a['btn_url'] : home_url();
	$count   = 0;

	// متن پیام‌رسان (HTML ساده).
	$bot_text = sprintf(
		"📢 <b>%s</b>\n\n%s",
		$a['title'],
		wp_trim_words( $a['text'], 60 )
	);

	$buttons = array();

	if ( $a['btn_text'] && $a['btn_url'] ) {
		$buttons[] = array(
			'text' => $a['btn_text'],
			'url'  => $a['btn_url'],
		);
	}

	// متن پیامک (بدون HTML، کوتاه).
	$sms_text = sprintf(
		"%s\n%s\n%s",
		$a['title'],
		wp_trim_words( $a['text'], 22 ),
		$btn_url
	);

	$audience = get_post_meta( $id, '_zc_an_audience', true );
	$args     = array( 'number' => 500 );

	if ( 'customer' === $audience ) {
		$args['role__in'] = array( 'customer' );
	}

	foreach ( get_users( $args ) as $user ) {

		// تلگرام و بله — فقط اگر کاربر اعلان‌ها را روشن کرده باشد.
		if ( $send_bot && function_exists( 'zc_notify_user' ) ) {
			if ( zc_notify_user( $user->ID, 'announce', $bot_text, $buttons ) ) {
				$count++;
			}
		}

		// پیامک.
		if ( $send_sms && function_exists( 'zc_sms_dispatch' ) ) {
			$mobile = function_exists( 'zc_user_mobile' ) ? zc_user_mobile( $user->ID ) : get_user_meta( $user->ID, 'zc_mobile', true );

			if ( $mobile && zc_sms_dispatch( $mobile, $sms_text, 'announce' ) ) {
				$count++;
			}
		}

		// اعلان داخل سایت برای همه‌ی کاربران هدف.
		if ( function_exists( 'zc_add_notification' ) ) {
			zc_add_notification(
				$user->ID,
				$a['title'],
				wp_trim_words( $a['text'], 30 ),
				$a['style'],
				$a['btn_url']
			);
		}
	}

	update_post_meta( $id, '_zc_an_sent_at', current_time( 'mysql' ) );
	update_post_meta( $id, '_zc_an_sent_count', $count );

	/**
	 * پس از ارسال اطلاعیه.
	 *
	 * @param int $id    شناسه.
	 * @param int $count تعداد.
	 */
	do_action( 'zc_announce_broadcasted', $id, $count );

	return $count;
}

/**
 * ارسال خودکار هنگام انتشار اطلاعیه.
 *
 * @param string  $new وضعیت تازه.
 * @param string  $old وضعیت پیشین.
 * @param WP_Post $post نوشته.
 * @return void
 */
function zc_announce_on_publish( $new, $old, $post ) {
	if ( 'zc_announce' !== $post->post_type ) {
		return;
	}

	if ( 'publish' !== $new || 'publish' === $old ) {
		return;
	}

	// اگر قبلاً ارسال شده، دوباره نفرست.
	if ( get_post_meta( $post->ID, '_zc_an_sent_at', true ) ) {
		return;
	}

	zc_announce_broadcast( $post->ID );
}
add_action( 'transition_post_status', 'zc_announce_on_publish', 10, 3 );

/**
 * ارسال دستی از متاباکس.
 *
 * @return void
 */
function zc_ajax_announce_send() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$n  = zc_announce_broadcast( $id );

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: تعداد */
				__( '%s پیام ارسال شد.', 'zarincode' ),
				zc_fa_num( $n )
			),
		)
	);
}
add_action( 'wp_ajax_zc_announce_send', 'zc_ajax_announce_send' );

/**
 * افزودن «اطلاعیه‌ها» به انواع اعلان ربات.
 *
 * @param array $types انواع.
 * @return array
 */
function zc_announce_notification_type( $types ) {
	$types['announce'] = __( 'اطلاعیه‌های سایت', 'zarincode' );

	return $types;
}
add_filter( 'zc_notification_types', 'zc_announce_notification_type' );

/* ==========================================================================
   ۸. ستون‌های فهرست پیشخوان
   ========================================================================== */

/**
 * قالب‌بندی تاریخ اطلاعیه با تقویم فعال سایت.
 *
 * ماژول جلالی تابع zc_jalali_date را فراهم می‌کند؛ اگر فعال نبود به
 * تاریخ میلادی برمی‌گردیم تا ستون هرگز خالی نماند.
 *
 * @param string $datetime تاریخ.
 * @return string
 */
function zc_announce_date( $datetime ) {
	$ts = strtotime( $datetime );

	if ( ! $ts ) {
		return '';
	}

	if ( function_exists( 'zc_jalali_date' ) ) {
		return zc_fa_num( zc_jalali_date( 'j F', $ts ) );
	}

	return zc_fa_num( wp_date( 'j F', $ts ) );
}

/**
 * ستون‌های سفارشی.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_announce_columns( $cols ) {
	$new = array();

	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;

		if ( 'title' === $k ) {
			$new['zc_style']  = __( 'سبک', 'zarincode' );
			$new['zc_place']  = __( 'محل نمایش', 'zarincode' );
			$new['zc_window'] = __( 'بازه نمایش', 'zarincode' );
			$new['zc_sent']   = __( 'ارسال', 'zarincode' );
		}
	}

	return $new;
}
add_filter( 'manage_zc_announce_posts_columns', 'zc_announce_columns' );

/**
 * محتوای ستون‌ها.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_announce_column_content( $col, $id ) {
	switch ( $col ) {
		case 'zc_style':
			$styles = zc_announce_styles();
			$style  = get_post_meta( $id, '_zc_an_style', true );
			$style  = isset( $styles[ $style ] ) ? $style : 'info';

			printf(
				'<span class="zc-an-dot zc-an-dot--%s"></span> %s',
				esc_attr( $style ),
				esc_html( $styles[ $style ]['label'] )
			);
			break;

		case 'zc_place':
			$places = (array) get_post_meta( $id, '_zc_an_placements', true );
			$labels = zc_announce_placements();
			$out    = array();

			foreach ( $places as $p ) {
				$out[] = $labels[ $p ] ?? $p;
			}

			echo esc_html( $out ? implode( '، ', $out ) : '—' );
			break;

		case 'zc_window':
			$start = get_post_meta( $id, '_zc_an_start', true );
			$end   = get_post_meta( $id, '_zc_an_end', true );

			if ( ! $start && ! $end ) {
				esc_html_e( 'همیشه', 'zarincode' );
				break;
			}

			echo esc_html(
				sprintf(
					'%s — %s',
					$start ? zc_announce_date( $start ) : '…',
					$end ? zc_announce_date( $end ) : '…'
				)
			);
			break;

		case 'zc_sent':
			$n = (int) get_post_meta( $id, '_zc_an_sent_count', true );

			echo $n ? esc_html( zc_fa_num( $n ) ) : '—';
			break;
	}
}
add_action( 'manage_zc_announce_posts_custom_column', 'zc_announce_column_content', 10, 2 );
