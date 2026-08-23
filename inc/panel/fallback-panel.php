<?php
/**
 * پنل تنظیمات داخلی (فالبک در صورت نبود Redux Framework)
 * تمام فیلدهای اسکیما را با همان کلیدها ذخیره می‌کند تا سازگاری کامل حفظ شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * افزودن منوی پنل داخلی.
 *
 * @return void
 */
function zc_fallback_menu() {
	if ( zc_has_redux() ) {
		return;
	}

	add_submenu_page(
		'zarincode',
		__( 'تنظیمات قالب زرین کد', 'zarincode' ),
		__( 'تنظیمات قالب', 'zarincode' ),
		'manage_options',
		'zarincode-options',
		'zc_fallback_page'
	);
}
add_action( 'admin_menu', 'zc_fallback_menu', 11 );

/**
 * ثبت تنظیمات.
 *
 * @return void
 */
function zc_fallback_register() {
	if ( zc_has_redux() ) {
		return;
	}
	register_setting( 'zc_options_group', ZC_PREFIX, array( 'sanitize_callback' => 'zc_sanitize_options' ) );
}
add_action( 'admin_init', 'zc_fallback_register' );

/**
 * پاکسازی مقادیر ورودی.
 *
 * @param array $input ورودی.
 * @return array
 */
function zc_sanitize_options( $input ) {
	$schema   = zc_settings_schema();
	$existing = get_option( ZC_PREFIX, array() );
	/* کلیدهای افزونه‌ای/سیستمی خارج از schema نباید با ذخیرهٔ یک تب حذف شوند. */
	$clean    = is_array( $existing ) ? $existing : array();

	foreach ( $schema as $section ) {
		foreach ( $section['fields'] as $field ) {
			$id = $field['id'];

			if ( ! isset( $input[ $id ] ) ) {
				$clean[ $id ] = ( 'switch' === $field['type'] ) ? false : ( $field['default'] ?? '' );
				continue;
			}

			$value = $input[ $id ];

			switch ( $field['type'] ) {
				case 'textarea':
					$clean[ $id ] = sanitize_textarea_field( $value );
					break;
				case 'color':
					$clean[ $id ] = sanitize_hex_color( $value ) ? $value : ( $field['default'] ?? '#C9A227' );
					break;
				case 'switch':
					$clean[ $id ] = (bool) $value;
					break;
				case 'slider':
					$clean[ $id ] = ( isset( $field['step'] ) && (float) $field['step'] < 1 ) ? (float) $value : (int) $value;
					break;
				case 'media':
					$clean[ $id ] = esc_url_raw( is_array( $value ) ? ( $value['url'] ?? '' ) : $value );
					break;
				case 'checkbox':
					$clean[ $id ] = is_array( $value ) ? array_map( 'sanitize_key', $value ) : array();
					break;
				case 'password':
					$clean[ $id ] = trim( wp_strip_all_tags( $value ) );
					break;
				case 'number':
					$clean[ $id ] = (int) $value;
					break;
				case 'rich':
					$clean[ $id ] = wp_kses_post( $value );
					break;
				case 'slides':
					$clean[ $id ] = zc_sanitize_slides( $value );
					break;
				default:
					$clean[ $id ] = sanitize_text_field( $value );
			}
		}
	}

	// حفظ کلیدهای سیستمی.
	$existing = get_option( ZC_PREFIX, array() );
	foreach ( array( 'zc_panel_page', 'zc_login_page', 'zc_booking_page' ) as $key ) {
		if ( ! isset( $clean[ $key ] ) && isset( $existing[ $key ] ) ) {
			$clean[ $key ] = $existing[ $key ];
		}
	}

	// پاکسازی کش پس از ذخیره.
	if ( function_exists( 'zc_clear_cache' ) ) {
		zc_clear_cache();
	}
	if ( function_exists( 'zc_flush_asset_cache' ) ) {
		zc_flush_asset_cache();
	}

	return $clean;
}

/**
 * پاک‌سازی فیلد تکرارشونده (نمادهای اعتماد و ...).
 *
 * هر آیتم می‌تواند شامل type، html، image، link، size و builtin باشد.
 *
 * @param mixed $value مقدار خام.
 * @return array
 */
function zc_sanitize_slides( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$type  = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'html';
		$item  = array(
			'type'    => $type,
			'html'    => '',
			'image'   => '',
			'link'    => '',
			'size'    => 0,
			'builtin' => sanitize_key( $row['builtin'] ?? 'enamad' ),
		);

		if ( 'html' === $type && ! empty( $row['html'] ) ) {
			$item['html'] = function_exists( 'zc_kses_badge' ) ? zc_kses_badge( $row['html'] ) : wp_kses_post( $row['html'] );
		} elseif ( 'image' === $type && ! empty( $row['image'] ) ) {
			$item['image'] = esc_url_raw( $row['image'] );
			$item['link']  = esc_url_raw( $row['link'] ?? '' );
		} elseif ( 'builtin' === $type ) {
			$item['builtin'] = sanitize_key( $row['builtin'] ?? 'enamad' );
			$item['link']    = esc_url_raw( $row['link'] ?? '' );
		}

		if ( ! empty( $row['size'] ) ) {
			$item['size'] = (int) $row['size'];
		}

		if ( 'html' === $type && $item['html'] || 'image' === $type && $item['image'] || 'builtin' === $type ) {
			$clean[] = $item;
		}
	}

	return $clean;
}

/**
 * حفظ صحیح گزینه‌های تودرتو هنگام ذخیرهٔ یک تب دیگر.
 *
 * @param string $name  نام ورودی.
 * @param mixed  $value مقدار.
 * @return void
 */
function zc_fallback_hidden_fields( $name, $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $child ) {
			zc_fallback_hidden_fields( $name . '[' . $key . ']', $child );
		}
		return;
	}
	printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( (string) $value ) );
}

/**
 * رندر صفحه پنل داخلی.
 *
 * @return void
 */
function zc_fallback_page() {
	$schema  = zc_settings_schema();
	$options = wp_parse_args( get_option( ZC_PREFIX, array() ), zc_default_options() );
	$current = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'general'; // phpcs:ignore

	if ( ! isset( $schema[ $current ] ) ) {
		$current = 'general';
	}
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div>
				<h1><?php esc_html_e( 'تنظیمات قالب زرین کد', 'zarincode' ); ?></h1>
				<p><?php printf( esc_html__( 'نسخه %s | پنل تنظیمات داخلی', 'zarincode' ), esc_html( ZC_VERSION ) ); ?></p>
			</div>
			<div class="zc-admin-header__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-demo' ) ); ?>" class="button"><?php esc_html_e( 'درون‌ریزی دمو', 'zarincode' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'مشاهده سایت', 'zarincode' ); ?></a>
			</div>
		</div>

		<?php settings_errors(); ?>

		<div class="zc-admin-layout">

			<nav class="zc-admin-nav">
				<?php foreach ( $schema as $key => $section ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-options&section=' . $key ) ); ?>"
						class="<?php echo $current === $key ? 'is-active' : ''; ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( zc_section_dashicon( $key ) ); ?>"></span>
						<?php echo esc_html( $section['title'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="zc-admin-content">
				<form method="post" action="options.php">
					<?php settings_fields( 'zc_options_group' ); ?>

					<?php
					// فیلدهای مخفی برای حفظ مقادیر سایر بخش‌ها.
					foreach ( $schema as $sec_key => $section ) {
						if ( $sec_key === $current ) {
							continue;
						}
						foreach ( $section['fields'] as $field ) {
							$id  = $field['id'];
							$val = $options[ $id ] ?? '';
								zc_fallback_hidden_fields( ZC_PREFIX . '[' . $id . ']', $val );
						}
					}
					?>

					<h2 class="zc-admin-section-title"><?php echo esc_html( $schema[ $current ]['title'] ); ?></h2>

					<table class="form-table zc-admin-table" role="presentation">
						<?php
						foreach ( $schema[ $current ]['fields'] as $field ) {
							zc_render_fallback_field( $field, $options );
						}
						?>
					</table>

					<?php submit_button( __( 'ذخیره تنظیمات', 'zarincode' ), 'primary large' ); ?>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * رندر یک فیلد.
 *
 * @param array $field   فیلد.
 * @param array $options مقادیر.
 * @return void
 */
function zc_render_fallback_field( $field, $options ) {
	$id    = $field['id'];
	$name  = ZC_PREFIX . '[' . $id . ']';
	$value = $options[ $id ] ?? ( $field['default'] ?? '' );
	$desc  = $field['desc'] ?? ( $field['subtitle'] ?? '' );
	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
		</th>
		<td>
			<?php
				switch ( $field['type'] ) {

					case 'info':
						echo '<div class="notice notice-info inline"><p>' . wp_kses_post( $desc ) . '</p></div>';
						$desc = '';
						break;

					case 'textarea':
					printf(
						'<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text code">%4$s</textarea>',
						esc_attr( $id ),
						esc_attr( $name ),
						(int) ( $field['rows'] ?? 4 ),
						esc_textarea( $value )
					);
					break;

				case 'select':
					$opts = $field['options'] ?? array();
					if ( 'pages' === $opts ) {
						$opts = zc_pages_list();
					} elseif ( 'elementor_templates' === $opts ) {
						$opts = zc_get_elementor_templates();
					}
					printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
					foreach ( (array) $opts as $k => $label ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $k ),
							selected( (string) $value, (string) $k, false ),
							esc_html( $label )
						);
					}
					echo '</select>';
					break;

				case 'switch':
					printf(
						'<label class="zc-admin-switch"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s><span class="zc-admin-switch__slider"></span></label>',
						esc_attr( $id ),
						esc_attr( $name ),
						checked( (bool) $value, true, false )
					);
					break;

				case 'color':
					printf(
						'<input type="color" id="%1$s" name="%2$s" value="%3$s" class="zc-admin-color"><code style="margin-inline-start:10px">%3$s</code>',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( is_array( $value ) ? ( $value['color'] ?? '#C9A227' ) : $value )
					);
					break;

				case 'slider':
					printf(
'<input type="range" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" class="zc-admin-range" oninput="this.nextElementSibling.textContent=this.value"><output style="margin-inline-start:10px;font-weight:700">%3$s</output>',
							esc_attr( $id ),
							esc_attr( $name ),
							esc_attr( $value ),
							esc_attr( $field['min'] ?? 0 ),
							esc_attr( $field['max'] ?? 100 ),
							esc_attr( $field['step'] ?? 1 )
					);
					break;

				case 'media':
					$url = is_array( $value ) ? ( $value['url'] ?? '' ) : $value;
					printf(
						'<div class="zc-admin-media">
							<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text zc-admin-media__input">
							<button type="button" class="button zc-admin-media__btn">%4$s</button>
							<div class="zc-admin-media__preview">%5$s</div>
						</div>',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_url( $url ),
						esc_html__( 'انتخاب تصویر', 'zarincode' ),
						$url ? '<img src="' . esc_url( $url ) . '" style="max-width:180px;max-height:70px;margin-top:8px;border-radius:8px">' : ''
					);
					break;

				case 'checkbox':
					$value = is_array( $value ) ? $value : array();
					foreach ( (array) ( $field['options'] ?? array() ) as $k => $label ) {
						printf(
							'<label style="display:block;margin-bottom:6px"><input type="checkbox" name="%s[%s][]" value="%s" %s> %s</label>',
							esc_attr( ZC_PREFIX ),
							esc_attr( $id ),
							esc_attr( $k ),
							checked( in_array( (string) $k, array_map( 'strval', $value ), true ), true, false ),
							esc_html( $label )
						);
					}
					break;

				case 'password':
					printf(
						'<input type="password" id="%1$s" name="%2$s" value="%3$s" class="regular-text" autocomplete="new-password">',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value )
					);
					break;

				case 'rich':
					// ویرایشگر متنی کامل برای پیامک‌ها / اطلاع‌رسانی ربات.
					$editor_id = 'zc_' . $id;
					wp_editor(
						(string) $value,
						$editor_id,
						array(
							'textarea_name' => $name,
							'textarea_rows' => (int) ( $field['rows'] ?? 8 ),
							'media_buttons' => false,
							'teeny'         => false,
							'quicktags'     => true,
						)
					);
					?>
					<p class="description" style="margin-top:6px">
						<?php
						esc_html_e( 'می‌توانید از HTML و متغیرهای جایگذاری استفاده کنید:', 'zarincode' );
						$ph = $field['placeholders'] ?? '';
						if ( $ph ) {
							echo '<code style="margin-inline-start:6px">' . esc_html( $ph ) . '</code>';
						}
						?>
					</p>
					<?php
					break;

				case 'slides':
					zc_render_fallback_slides( $id, $name, (array) $value );
					break;

				default:
					printf(
						'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value )
					);
			}

			if ( $desc ) {
				printf( '<p class="description">%s</p>', esc_html( $desc ) );
			}
			?>
		</td>
	</tr>
	<?php
}

/**
 * رندر فیلد تکرارشونده (نمادهای اعتماد).
 *
 * @param string $id    شناسه.
 * @param string $name  نام.
 * @param array  $value مقادیر.
 * @return void
 */
function zc_render_fallback_slides( $id, $name, $value ) {
	$items = is_array( $value ) ? $value : array();
	$types = array(
		'html'    => __( 'کد HTML رسمی (زرین‌پال / ای‌نماد / ساماندهی)', 'zarincode' ),
		'image'   => __( 'تصویر + لینک', 'zarincode' ),
		'builtin' => __( 'نماد آماده‌ی قالب', 'zarincode' ),
	);
	$builtins = array(
		'enamad'    => __( 'ای‌نماد', 'zarincode' ),
		'samandehi' => __( 'ساماندهی', 'zarincode' ),
		'zarinpal'  => __( 'زرین‌پال', 'zarincode' ),
		'irandigi'  => __( 'رسانه دیجیتال', 'zarincode' ),
	);
	?>
	<div class="zc-admin-repeater" data-zc-repeater="<?php echo esc_attr( $id ); ?>">
		<p class="description" style="margin-top:0"><?php esc_html_e( 'نمادهای اعتماد را با کد HTML رسمی، تصویر یا نماد آماده اضافه کنید. اندازه هر نماد را می‌توانید تعیین کنید.', 'zarincode' ); ?></p>

		<div class="zc-admin-repeater__rows" data-zc-repeater-rows>
			<?php
			foreach ( $items as $i => $row ) {
				zc_render_fallback_slides_row( $name, $i, (array) $row, $types, $builtins );
			}
			?>
		</div>

		<button type="button" class="button zc-admin-repeater__add" data-zc-repeater-add>
			<span aria-hidden="true">+</span> <?php esc_html_e( 'افزودن نماد', 'zarincode' ); ?>
		</button>
	</div>

	<script type="text/html" id="zc-tpl-<?php echo esc_attr( $id ); ?>">
		<?php
		ob_start();
		zc_render_fallback_slides_row( $name, '__IDX__', array( 'type' => 'html', 'html' => '', 'image' => '', 'link' => '', 'size' => 0, 'builtin' => 'enamad' ), $types, $builtins );
		echo str_replace( array( '__IDX__', "\n" ), array( '{{IDX}}', '' ), ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>
	</script>
	<?php
}

/**
 * رندر یک ردیف از فیلد تکرارشونده.
 *
 * @param string $name     نام فیلد.
 * @param int    $i        ایندکس.
 * @param array  $row      مقادیر ردیف.
 * @param array  $types    انواع.
 * @param array  $builtins نمادهای آماده.
 * @return void
 */
function zc_render_fallback_slides_row( $name, $i, $row, $types, $builtins ) {
	$type    = $row['type'] ?? 'html';
	$html    = (string) ( $row['html'] ?? '' );
	$image   = (string) ( $row['image'] ?? '' );
	$link    = (string) ( $row['link'] ?? '' );
	$size    = (int) ( $row['size'] ?? 0 );
	$builtin = (string) ( $row['builtin'] ?? 'enamad' );
	?>
	<div class="zc-admin-repeater__row" data-zc-repeater-row>
		<div class="zc-admin-repeater__head">
			<span class="zc-admin-repeater__grip">≡</span>
			<select data-zc-repeater-type>
				<?php foreach ( $types as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $type, $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button-link-delete zc-admin-repeater__remove" data-zc-repeater-remove><?php esc_html_e( 'حذف', 'zarincode' ); ?></button>
		</div>

		<div class="zc-admin-repeater__fields">
			<div class="zc-admin-repeater__field" data-zc-show="html">
				<label><?php esc_html_e( 'کد HTML نماد', 'zarincode' ); ?></label>
				<textarea name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][html]" rows="4" class="large-text code"><?php echo esc_textarea( $html ); ?></textarea>
				<p class="description"><?php esc_html_e( 'مثال: کد تایید زرین‌پال، ای‌نماد و ... را اینجا بچسبانید.', 'zarincode' ); ?></p>
			</div>

			<div class="zc-admin-repeater__field" data-zc-show="image">
				<label><?php esc_html_e( 'نشانی تصویر', 'zarincode' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][image]" value="<?php echo esc_url( $image ); ?>" class="large-text" placeholder="https://...">
			</div>

			<div class="zc-admin-repeater__field" data-zc-show="builtin">
				<label><?php esc_html_e( 'نماد آماده', 'zarincode' ); ?></label>
				<select name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][builtin]">
					<?php foreach ( $builtins as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $builtin, $k ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zc-admin-repeater__field" data-zc-show="image builtin">
				<label><?php esc_html_e( 'لینک (اختیاری)', 'zarincode' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][link]" value="<?php echo esc_url( $link ); ?>" class="large-text" placeholder="https://...">
			</div>

			<div class="zc-admin-repeater__field">
				<label><?php esc_html_e( 'اندازه (پیکسل)', 'zarincode' ); ?></label>
				<input type="number" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][size]" value="<?php echo esc_attr( $size ); ?>" min="40" step="1" placeholder="<?php esc_attr_e( 'پیش‌فرض', 'zarincode' ); ?>">
			</div>

			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][type]" value="<?php echo esc_attr( $type ); ?>">
		</div>
	</div>
	<?php
}

/**
 * آیکن داشبورد برای هر بخش.
 *
 * @param string $key کلید.
 * @return string
 */
function zc_section_dashicon( $key ) {
	$icons = array(
		'general'     => 'admin-home',
		'styling'     => 'art',
		'header'      => 'align-center',
		'footer'      => 'align-wide',
		'contact'     => 'phone',
		'social'      => 'share',
		'share'       => 'megaphone',
		'sms'         => 'email-alt',
		'payment'     => 'money-alt',
		'wallet'      => 'bank',
		'courses'     => 'welcome-learn-more',
		'ticket'      => 'tickets-alt',
		'booking'     => 'calendar-alt',
		'messengers'  => 'admin-comments',
		'account'     => 'admin-users',
		'blog'        => 'edit',
		'performance' => 'performance',
	);

	return $icons[ $key ] ?? 'admin-generic';
}
