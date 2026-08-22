<?php
/**
 * منوی موبایل
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="zc-overlay"></div>
<nav class="zc-mobile-nav" aria-hidden="true" aria-label="<?php esc_attr_e( 'منوی موبایل', 'zarincode' ); ?>">
	<div class="zc-mobile-nav__head">
		<?php zc_site_logo(); ?>
		<button class="zc-hicon zc-mobile-nav__close" aria-label="<?php esc_attr_e( 'بستن منو', 'zarincode' ); ?>">
			<?php zc_the_icon( 'close', 20 ); ?>
		</button>
	</div>

	<div class="zc-mobile-nav__body">
		<?php
		$zc_loc = has_nav_menu( 'mobile' ) ? 'mobile' : 'primary';
		if ( has_nav_menu( $zc_loc ) ) {
			wp_nav_menu(
				array(
					'theme_location' => $zc_loc,
					'container'      => false,
					'menu_class'     => 'zc-mobile-nav__list',
					'depth'          => 3,
				)
			);
		} else {
			zc_default_menu( 'zc-mobile-nav__list' );
		}
		?>
	</div>

	<div class="zc-mobile-nav__foot">
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( zc_panel_url() ); ?>" class="zc-btn zc-btn--gold zc-btn--block">
				<?php zc_the_icon( 'user', 18 ); ?><?php esc_html_e( 'پنل کاربری', 'zarincode' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="zc-btn zc-btn--outline zc-btn--block zc-btn--sm">
				<?php zc_the_icon( 'logout', 17 ); ?><?php esc_html_e( 'خروج', 'zarincode' ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( zc_login_url() ); ?>" class="zc-btn zc-btn--gold zc-btn--block">
				<?php zc_the_icon( 'user', 18 ); ?><?php esc_html_e( 'ورود / ثبت‌نام', 'zarincode' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( zc_is_woo() ) : ?>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="zc-btn zc-btn--dark zc-btn--block zc-btn--sm">
				<?php zc_the_icon( 'cart', 17 ); ?><?php esc_html_e( 'سبد خرید', 'zarincode' ); ?>
			</a>
		<?php endif; ?>
	</div>
</nav>
