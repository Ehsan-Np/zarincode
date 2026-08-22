<?php
/**
 * مدیریت افزونه‌های قالب زرین کد (مشابه معماری TGM-Plugin-Activation)
 * ---------------------------------------------------------------------------
 * بدون استفاده از افزونهٔ TGM، همان ساختار و UX را پیاده می‌کند:
 *  - فهرست افزونه‌های «الزامی» و «پیشنهادی» با وضعیت (نصب/فعال/نسخه).
 *  - صفحهٔ اختصاصی مدیریت با دکمه‌های «نصب»، «فعال‌سازی»، «به‌روزرسانی» (AJAX).
 *  - اعلان مدیریتی برای افزونه‌های نیازمند اقدام.
 *  - نصب خودکار به‌صورت اختیاری (غیرفعال در پیش‌فرض تا اختیار کامل با مدیر باشد).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فهرست افزونه‌های قالب.
 * هر آیتم می‌تواند «الزامی» یا «پیشنهادی» باشد (مثل TGM).
 *
 * @return array
 */
function zc_plugin_manager_plugins() {
	$plugins = array(
		'elementor'       => array(
			'name'        => __( 'المنتور', 'zarincode' ),
			'slug'        => 'elementor',
			'required'    => true,
			'min_version' => '3.0.0',
			'description' => __( 'ساخت و ویرایش صفحات با بیلدر پیشرفته.', 'zarincode' ),
			'type'        => 'page-builder',
		),
		'woocommerce'     => array(
			'name'        => __( 'ووکامرس', 'zarincode' ),
			'slug'        => 'woocommerce',
			'required'    => true,
			'min_version' => '7.0.0',
			'description' => __( 'فروشگاه آنلاین و مدیریت محصولات.', 'zarincode' ),
			'type'        => 'commerce',
		),
		'redux-framework' => array(
			'name'        => __( 'رداکس فریم‌ورک', 'zarincode' ),
			'slug'        => 'redux-framework',
			'required'    => true,
			'min_version' => '4.3.0',
			'description' => __( 'پنل تنظیمات پیشرفته‌ی قالب.', 'zarincode' ),
			'type'        => 'framework',
		),
		'contact-form-7'  => array(
			'name'        => __( 'فرم تماس ۷', 'zarincode' ),
			'slug'        => 'contact-form-7',
			'required'    => false,
			'min_version' => '',
			'description' => __( 'فرم‌های تماس و درخواست پروژه.', 'zarincode' ),
			'type'        => 'forms',
		),
	);

	return apply_filters( 'zc_plugin_manager_plugins', $plugins );
}

/**
 * یافتن فایل اصلی (basename) یک افزونهٔ نصب‌شده.
 *
 * @param string $slug اسلاگ.
 * @return string
 */
function zc_pm_plugin_basename( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	foreach ( get_plugins() as $file => $data ) {
		if ( 0 === strpos( $file, $slug . '/' ) ) {
			return $file;
		}
	}
	return '';
}

/**
 * وضعیت یک افزونه.
 *
 * @param string $slug اسلاگ.
 * @return array
 */
function zc_pm_plugin_status( $slug ) {
	$out = array( 'installed' => false, 'active' => false, 'version' => '', 'basename' => '' );

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$base = zc_pm_plugin_basename( $slug );
	if ( ! $base ) {
		return $out;
	}

	$plugins           = get_plugins();
	$out['installed']  = true;
	$out['active']     = is_plugin_active( $base );
	$out['version']    = isset( $plugins[ $base ]['Version'] ) ? $plugins[ $base ]['Version'] : '';
	$out['basename']   = $base;

	return $out;
}

/**
 * دانلود و نصب یک افزونه از مخزن وردپرس.
 *
 * @param string $slug    اسلاگ.
 * @param bool   $upgrade اگر true به‌روزرسانی.
 * @return true|WP_Error
 */
function zc_pm_install( $slug, $upgrade = false ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader-skin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';

	$api = plugins_api(
		'plugin_information',
		array(
			'slug'   => $slug,
			'fields' => array(
				'sections'          => false,
				'short_description' => false,
				'description'       => false,
				'rating'            => false,
				'tested'            => false,
				'downloaded'        => false,
				'last_updated'      => false,
			),
		)
	);
	if ( is_wp_error( $api ) ) {
		return $api;
	}

	$skin     = new WP_Ajax_Upgrader_Skin( array( 'plugin' => $slug ) );
	$upgrader = new Plugin_Upgrader( $skin );

	$result = $upgrade
		? $upgrader->upgrade( $api->download_link )
		: $upgrader->install( $api->download_link );

	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return true;
}

/**
 * فعال‌سازی یک افزونهٔ نصب‌شده.
 *
 * @param string $slug اسلاگ.
 * @return bool|WP_Error
 */
function zc_pm_activate( $slug ) {
	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$base = zc_pm_plugin_basename( $slug );
	if ( ! $base || is_plugin_active( $base ) ) {
		return true;
	}
	$r = activate_plugin( $base, '', false, true );
	return is_wp_error( $r ) ? $r : true;
}

/**
 * غیرفعال‌سازی یک افزونه.
 *
 * @param string $slug اسلاگ.
 * @return bool
 */
function zc_pm_deactivate( $slug ) {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$base = zc_pm_plugin_basename( $slug );
	if ( $base && is_plugin_active( $base ) ) {
		deactivate_plugins( $base );
	}
	return true;
}

/**
 * ثبت صفحهٔ مدیریت افزونه‌ها.
 *
 * @return void
 */
function zc_plugin_manager_menu() {
	add_submenu_page(
		'zarincode',
		__( 'مدیریت افزونه‌ها', 'zarincode' ),
		__( 'افزونه‌های قالب', 'zarincode' ),
		'manage_options',
		'zarincode-plugins',
		'zc_plugin_manager_page'
	);
}
add_action( 'admin_menu', 'zc_plugin_manager_menu', 20 );

/**
 * AJAX: نصب / فعال‌سازی / به‌روزرسانی / غیرفعال‌سازی یک افزونه.
 *
 * @return void
 */
function zc_plugin_manager_ajax() {
	check_ajax_referer( 'zc_pm_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی ندارید.', 'zarincode' ) ) );
	}

	$slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : ''; // phpcs:ignore
	$do   = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : ''; // phpcs:ignore

	$plugins = zc_plugin_manager_plugins();
	if ( ! $slug || ! isset( $plugins[ $slug ] ) ) {
		wp_send_json_error( array( 'message' => __( 'افزونه‌ای یافت نشد.', 'zarincode' ) ) );
	}

	$message = '';
	$success = false;

	switch ( $do ) {
		case 'install':
			$r = zc_pm_install( $slug, false );
			if ( is_wp_error( $r ) ) {
				wp_send_json_error( array( 'message' => $r->get_error_message() ) );
			}
			$success = true;
			$message = sprintf( __( 'افزونهٔ «%s» نصب شد.', 'zarincode' ), $plugins[ $slug ]['name'] );
			break;

		case 'update':
			$r = zc_pm_install( $slug, true );
			if ( is_wp_error( $r ) ) {
				wp_send_json_error( array( 'message' => $r->get_error_message() ) );
			}
			$success = true;
			$message = sprintf( __( 'افزونهٔ «%s» به‌روزرسانی شد.', 'zarincode' ), $plugins[ $slug ]['name'] );
			break;

		case 'activate':
			$r = zc_pm_activate( $slug );
			if ( is_wp_error( $r ) ) {
				wp_send_json_error( array( 'message' => $r->get_error_message() ) );
			}
			$success = true;
			$message = sprintf( __( 'افزونهٔ «%s» فعال شد.', 'zarincode' ), $plugins[ $slug ]['name'] );
			break;

		case 'deactivate':
			zc_pm_deactivate( $slug );
			$success = true;
			$message = sprintf( __( 'افزونهٔ «%s» غیرفعال شد.', 'zarincode' ), $plugins[ $slug ]['name'] );
			break;

		default:
			wp_send_json_error( array( 'message' => __( 'عملیات نامعتبر است.', 'zarincode' ) ) );
	}

	// وضعیت به‌روز برای رندر مجدد.
	$status = zc_pm_plugin_status( $slug );
	wp_send_json_success(
		array(
			'message' => $message,
			'status'  => $status,
		)
	);
}
add_action( 'wp_ajax_zc_plugin_manager', 'zc_plugin_manager_ajax' );

/**
 * رندر صفحهٔ مدیریت افزونه‌ها.
 *
 * @return void
 */
function zc_plugin_manager_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$plugins = zc_plugin_manager_plugins();
	$nonce   = wp_create_nonce( 'zc_pm_nonce' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'مدیریت افزونه‌های قالب', 'zarincode' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'افزونه‌های موردنیاز قالب زرین کد را نصب، فعال یا به‌روزرسانی کنید. افزونه‌های «الزامی» برای عملکرد کامل قالب لازم هستند.', 'zarincode' ); ?>
		</p>

		<table class="widefat striped" style="margin-top:16px;max-width:980px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'افزونه', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'نسخه', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'عملیات', 'zarincode' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $plugins as $slug => $meta ) :
				$s = zc_pm_plugin_status( $slug );
				$is_req  = ! empty( $meta['required'] );
				$outdated = $s['installed'] && $meta['min_version'] && version_compare( $s['version'], $meta['min_version'], '<' );
				$badge = $is_req
					? '<span style="background:#dc32321a;color:#b32d2e;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700">' . esc_html__( 'الزامی', 'zarincode' ) . '</span>'
					: '<span style="background:#2271b11a;color:#2271b1;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700">' . esc_html__( 'پیشنهادی', 'zarincode' ) . '</span>';
				?>
				<tr data-slug="<?php echo esc_attr( $slug ); ?>">
					<td>
						<strong><?php echo esc_html( $meta['name'] ); ?> <?php echo $badge; ?></strong>
						<p class="description" style="margin:4px 0 0"><?php echo esc_html( $meta['description'] ); ?></p>
					</td>
					<td class="zc-pm-status">
						<?php
						if ( $s['active'] ) {
							echo '<span style="color:#16a34a;font-weight:700">✓ ' . esc_html__( 'فعال', 'zarincode' ) . '</span>';
						} elseif ( $s['installed'] ) {
							echo '<span style="color:#d97706;font-weight:700">' . esc_html__( 'نصب، غیرفعال', 'zarincode' ) . '</span>';
						} else {
							echo '<span style="color:#6b7280">' . esc_html__( 'نصب نشده', 'zarincode' ) . '</span>';
						}
						if ( $outdated ) {
							echo ' <span style="color:#b91c1c;font-size:.8rem">(' . esc_html__( 'نیاز به به‌روزرسانی', 'zarincode' ) . ')</span>';
						}
						?>
					</td>
					<td class="zc-pm-ver"><?php echo esc_html( $s['version'] ? $s['version'] : '—' ); ?></td>
					<td class="zc-pm-actions">
						<?php if ( ! $s['installed'] ) : ?>
							<button type="button" class="button button-primary zc-pm-btn" data-do="install"><?php esc_html_e( 'نصب', 'zarincode' ); ?></button>
						<?php else : ?>
							<?php if ( $outdated ) : ?>
								<button type="button" class="button zc-pm-btn" data-do="update"><?php esc_html_e( 'به‌روزرسانی', 'zarincode' ); ?></button>
							<?php endif; ?>
							<?php if ( ! $s['active'] ) : ?>
								<button type="button" class="button button-primary zc-pm-btn" data-do="activate"><?php esc_html_e( 'فعال‌سازی', 'zarincode' ); ?></button>
							<?php else : ?>
								<button type="button" class="button zc-pm-btn" data-do="deactivate"><?php esc_html_e( 'غیرفعال‌سازی', 'zarincode' ); ?></button>
							<?php endif; ?>
						<?php endif; ?>
						<span class="zc-pm-msg" style="display:block;margin-top:6px;font-size:.8rem"></span>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<script>
	(function () {
		var nonce = <?php echo wp_json_encode( $nonce ); ?>;
		document.querySelectorAll('.zc-pm-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var row = btn.closest('tr');
				var slug = row.dataset.slug;
				var doAction = btn.dataset.do;
				var msg = row.querySelector('.zc-pm-msg');
				btn.disabled = true;
				btn.classList.add('is-loading');
				btn.textContent = '…';

				var body = new FormData();
				body.append('action', 'zc_plugin_manager');
				body.append('nonce', nonce);
				body.append('slug', slug);
				body.append('do', doAction);

				fetch(ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (res.success) {
							if (msg) { msg.textContent = res.data.message; msg.style.color = '#16a34a'; }
							setTimeout(function () { window.location.reload(); }, 900);
						} else {
							if (msg) { msg.textContent = (res.data && res.data.message) || 'خطا'; msg.style.color = '#b91c1c'; }
							btn.disabled = false;
							btn.classList.remove('is-loading');
							btn.textContent = btn.dataset.origText || btn.textContent;
						}
					});
			});
		});
	})();
	</script>
	<?php
}

/**
 * اعلان مدیریتی اگر افزونهٔ الزامی نصب/فعال نباشد.
 *
 * @return void
 */
function zc_plugin_manager_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$needs = false;
	foreach ( zc_plugin_manager_plugins() as $slug => $meta ) {
		if ( ! empty( $meta['required'] ) ) {
			$s = zc_pm_plugin_status( $slug );
			if ( ! $s['installed'] || ! $s['active'] ) {
				$needs = true;
				break;
			}
		}
	}
	if ( ! $needs ) {
		return;
	}
	echo '<div class="notice notice-warning is-dismissible"><p>';
	echo esc_html__( 'برخی افزونه‌های لازم قالب زرین کد نصب یا فعال نیستند.', 'zarincode' );
	echo ' <a class="button button-small" href="' . esc_url( admin_url( 'admin.php?page=zarincode-plugins' ) ) . '">' . esc_html__( 'مدیریت افزونه‌ها', 'zarincode' ) . '</a>';
	echo '</p></div>';
}
add_action( 'admin_notices', 'zc_plugin_manager_admin_notice' );
