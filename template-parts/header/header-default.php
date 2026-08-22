<?php
/**
 * هدر پیش‌فرض قالب زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_topbar   = zc_opt( 'zc_topbar_enable', true );
$zc_phone    = zc_opt( 'zc_phone', '071-42380267' );
$zc_email    = zc_site_email();
$zc_cta_text = zc_opt( 'zc_header_cta_text', 'مشاوره رایگان' );
$zc_cta_link = zc_opt( 'zc_header_cta_link', '#' );
?>

<?php if ( $zc_topbar ) : ?>
<div class="zc-topbar">
	<div class="zc-container zc-topbar__in">
		<ul class="zc-topbar__list">
			<?php if ( $zc_phone ) : ?>
			<li><?php zc_the_icon( 'phone', 15 ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $zc_phone ) ); ?>" class="zc-ltr"><?php echo esc_html( zc_fa_num( $zc_phone ) ); ?></a></li>
			<?php endif; ?>
			<?php if ( $zc_email ) : ?>
			<li><?php zc_the_icon( 'mail', 15 ); ?><a href="mailto:<?php echo esc_attr( $zc_email ); ?>"><?php echo esc_html( $zc_email ); ?></a></li>
			<?php endif; ?>
		</ul>
		<ul class="zc-topbar__list">
			<?php
			if ( has_nav_menu( 'topbar' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'topbar',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			} else {
				?>
				<li><?php zc_the_icon( 'headphone', 15 ); ?><a href="<?php echo esc_url( zc_panel_url( 'tickets' ) ); ?>"><?php esc_html_e( 'پشتیبانی آنلاین', 'zarincode' ); ?></a></li>
				<li><?php zc_the_icon( 'shield', 15 ); ?><span><?php esc_html_e( 'ضمانت بازگشت وجه', 'zarincode' ); ?></span></li>
				<?php
			}
			?>
		</ul>
	</div>
</div>
<?php endif; ?>

<header class="zc-header" id="zc-header">
	<div class="zc-container zc-header__in">

		<?php zc_site_logo(); ?>

		<nav class="zc-nav" role="navigation" aria-label="<?php esc_attr_e( 'منوی اصلی', 'zarincode' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'zc-nav__list',
						'walker'         => new ZC_Nav_Walker(),
						'depth'          => 3,
					)
				);
			} else {
				zc_default_menu();
			}
			?>
		</nav>

		<div class="zc-header__actions">

			<?php if ( zc_opt( 'zc_ajax_search', true ) ) : ?>
			<button class="zc-hicon" data-zc-open="search" aria-label="<?php esc_attr_e( 'جستجو', 'zarincode' ); ?>" title="<?php esc_attr_e( 'جستجو (Ctrl+K)', 'zarincode' ); ?>">
				<?php zc_the_icon( 'search', 21 ); ?>
			</button>
			<?php endif; ?>

			<?php if ( zc_is_woo() && zc_opt( 'zc_header_cart', true ) ) : ?>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="zc-hicon" aria-label="<?php esc_attr_e( 'سبد خرید', 'zarincode' ); ?>">
				<?php zc_the_icon( 'cart', 21 ); ?>
				<span class="zc-hicon__count" data-zc-cart-count<?php echo WC()->cart && WC()->cart->get_cart_contents_count() ? '' : ' style="display:none"'; ?>>
					<?php echo esc_html( zc_fa_num( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?>
				</span>
			</a>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php $zc_user = wp_get_current_user(); ?>
				<a href="<?php echo esc_url( zc_panel_url() ); ?>" class="zc-hicon" title="<?php echo esc_attr( $zc_user->display_name ); ?>" aria-label="<?php esc_attr_e( 'پنل کاربری', 'zarincode' ); ?>">
					<?php zc_the_icon( 'user', 21 ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( zc_login_url() ); ?>" class="zc-hicon" aria-label="<?php esc_attr_e( 'ورود و ثبت‌نام', 'zarincode' ); ?>">
					<?php zc_the_icon( 'user', 21 ); ?>
				</a>
			<?php endif; ?>

			<div class="zc-header__cta">
				<?php if ( $zc_cta_text ) : ?>
				<a href="<?php echo esc_url( $zc_cta_link ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
					<?php zc_the_icon( 'sparkle', 17 ); ?>
					<span><?php echo esc_html( $zc_cta_text ); ?></span>
				</a>
				<?php endif; ?>
				<button class="zc-hicon zc-burger" aria-label="<?php esc_attr_e( 'منو', 'zarincode' ); ?>" aria-expanded="false">
					<?php zc_the_icon( 'menu', 22 ); ?>
				</button>
			</div>
		</div>
	</div>
</header>
<div class="zc-header-spacer"></div>
