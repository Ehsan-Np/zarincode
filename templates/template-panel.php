<?php
/**
 * Template Name: پنل کاربری زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

$zc_user    = wp_get_current_user();
$zc_tabs    = zc_panel_tabs();
$zc_current = zc_current_panel_tab();
$zc_stats   = zc_user_stats();
?>

<div class="zc-panel">
	<div class="zc-container">

		<?php
		// پیام‌های سیستمی.
		$zc_msg = get_transient( 'zc_wallet_msg_' . get_current_user_id() );
		if ( $zc_msg ) {
			printf(
				'<div class="zc-alert zc-alert--%1$s" style="margin-bottom:20px">%2$s<span>%3$s</span></div>',
				esc_attr( $zc_msg['type'] ),
				zc_icon( 'success' === $zc_msg['type'] ? 'check' : 'info', 20 ), // phpcs:ignore
				esc_html( $zc_msg['text'] )
			);
			delete_transient( 'zc_wallet_msg_' . get_current_user_id() );
		}
		?>

		<div class="zc-panel__layout">

			<!-- سایدبار -->
			<aside class="zc-panel__sidebar" id="zc-panel-sidebar" aria-label="<?php esc_attr_e( 'منوی پنل کاربری', 'zarincode' ); ?>">
				<div class="zc-panel__offcanvas-head">
					<span class="zc-panel__offcanvas-title"><?php zc_the_icon( 'grid', 18 ); ?><?php esc_html_e( 'منوی کاربری', 'zarincode' ); ?></span>
					<button type="button" class="zc-panel__offcanvas-close" data-zc-panel-close aria-label="<?php esc_attr_e( 'بستن منو', 'zarincode' ); ?>">
						<?php zc_the_icon( 'close', 22 ); ?>
					</button>
				</div>
				<div class="zc-panel__user">
					<div class="zc-panel__user-bg"></div>
					<div class="zc-avatar zc-avatar--lg zc-panel__avatar">
						<?php echo get_avatar( $zc_user->ID, 96 ); ?>
					</div>
					<h3 class="zc-panel__username"><?php echo esc_html( $zc_user->display_name ); ?></h3>
					<span class="zc-panel__role">
						<?php
						$zc_roles = array(
							'administrator' => __( 'مدیر سایت', 'zarincode' ),
							'zc_teacher'    => __( 'مدرس', 'zarincode' ),
							'zc_student'    => __( 'دانشجو', 'zarincode' ),
							'customer'      => __( 'مشتری', 'zarincode' ),
						);
						$zc_role = $zc_user->roles[0] ?? 'subscriber';
						echo esc_html( $zc_roles[ $zc_role ] ?? __( 'کاربر', 'zarincode' ) );
						?>
					</span>

					<?php if ( zc_opt( 'zc_wallet_enable', true ) ) : ?>
					<div class="zc-panel__wallet">
						<span><?php zc_the_icon( 'wallet', 17 ); ?><?php esc_html_e( 'موجودی:', 'zarincode' ); ?></span>
						<strong><?php echo esc_html( zc_fa_num( number_format( $zc_stats['wallet'] ) ) ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></strong>
					</div>
					<?php endif; ?>
				</div>

				<nav class="zc-panel__nav" aria-label="<?php esc_attr_e( 'منوی پنل کاربری', 'zarincode' ); ?>">
					<?php foreach ( $zc_tabs as $zc_key => $zc_tab ) : ?>
						<?php if ( 'subscription' === $zc_key ) : ?>
							<div class="zc-panel__nav-sep"></div>
						<?php endif; ?>
						<a href="<?php echo esc_url( zc_panel_url( $zc_key ) ); ?>"
							class="zc-panel__nav-item<?php echo $zc_current === $zc_key ? ' is-active' : ''; ?>">
							<span class="zc-panel__nav-icon"><?php zc_the_icon( $zc_tab['icon'], 19 ); ?></span>
							<span class="zc-panel__nav-label"><?php echo esc_html( $zc_tab['label'] ); ?></span>
							<?php if ( ! empty( $zc_tab['badge'] ) ) : ?>
								<span class="zc-panel__nav-badge"><?php echo esc_html( zc_fa_num( $zc_tab['badge'] ) ); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>

					<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="zc-panel__nav-item zc-panel__nav-item--logout">
						<span class="zc-panel__nav-icon"><?php zc_the_icon( 'logout', 19 ); ?></span>
						<span class="zc-panel__nav-label"><?php esc_html_e( 'خروج از حساب', 'zarincode' ); ?></span>
					</a>
				</nav>
			</aside>

			<!-- محتوای اصلی -->
			<div class="zc-panel__content">

				<div class="zc-panel__topbar">
					<button type="button" class="zc-panel__menu-toggle" id="zc-panel-menu-toggle"
						aria-controls="zc-panel-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e( 'باز و بسته کردن منوی پنل', 'zarincode' ); ?>">
						<span class="zc-panel__burger" aria-hidden="true">
							<span class="zc-panel__burger-line"></span>
							<span class="zc-panel__burger-line"></span>
							<span class="zc-panel__burger-line"></span>
						</span>
					</button>
					<h1 class="zc-panel__title">
						<?php zc_the_icon( $zc_tabs[ $zc_current ]['icon'], 22 ); ?>
						<span class="zc-panel__title-text"><?php echo esc_html( $zc_tabs[ $zc_current ]['label'] ); ?></span>
					</h1>

					<div class="zc-panel__topbar-actions">
						<button class="zc-hicon" data-zc-notif aria-label="<?php esc_attr_e( 'اعلان‌ها', 'zarincode' ); ?>">
							<?php zc_the_icon( 'bell', 20 ); ?>
							<?php if ( $zc_stats['notifications'] > 0 ) : ?>
								<span class="zc-hicon__count"><?php echo esc_html( zc_fa_num( $zc_stats['notifications'] ) ); ?></span>
							<?php endif; ?>
						</button>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="zc-panel__back" aria-label="<?php esc_attr_e( 'بازگشت به سایت', 'zarincode' ); ?>">
							<?php zc_the_icon( 'arrow-left', 15 ); ?>
							<span class="zc-panel__back-text"><?php esc_html_e( 'بازگشت', 'zarincode' ); ?></span>
						</a>
					</div>
				</div>

				<div class="zc-panel__body">
					<?php
					$zc_tpl = ZC_DIR . 'template-parts/panel/' . $zc_current . '.php';
					if ( file_exists( $zc_tpl ) ) {
						include $zc_tpl;
					} else {
						do_action( 'zc_panel_tab_' . $zc_current );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
