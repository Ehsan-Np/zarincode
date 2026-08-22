<?php
/**
 * فوتر پیش‌فرض — بر اساس طرح مرجع
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_about   = zc_opt( 'zc_footer_about', 'زرین کد، مرجع تخصصی آموزش برنامه‌نویسی و فروش محصولات دیجیتال. با بیش از ۲۰۰ دوره آموزشی، صدها قالب و افزونه وردپرس و تیم پشتیبانی حرفه‌ای، همراه شما در مسیر یادگیری و درآمدزایی از کدنویسی هستیم.' );
$zc_phone   = zc_opt( 'zc_phone', '071-42380267' );
$zc_email   = zc_site_email();
$zc_mobile  = zc_opt( 'zc_mobile', '09024561001' );
$zc_address = zc_opt( 'zc_address', 'استان فارس، شهرستان کازرون، دهستان انارستان، برج سوخته سفلی' );
$zc_copy    = zc_opt( 'zc_copyright', 'تمامی حقوق مادی و معنوی این وبسایت متعلق به زرین کد می‌باشد.' );
$zc_socials = zc_social_links();
?>
<footer class="zc-footer" role="contentinfo">

	<div class="zc-container">
		<div class="zc-footer__social-bar">
			<div class="zc-footer__social-wrap">
				<div class="zc-footer__socials">
					<?php foreach ( $zc_socials as $zc_net => $zc_url ) : ?>
						<a href="<?php echo esc_url( $zc_url ); ?>" class="zc-social-btn" target="_blank" rel="noopener nofollow"
							aria-label="<?php echo esc_attr( $zc_net ); ?>">
							<?php echo zc_social_icon( $zc_net ); // phpcs:ignore ?>
						</a>
					<?php endforeach; ?>
				</div>
				<span style="font-size:.86rem;color:rgba(255,255,255,.75)">
					<?php echo esc_html( zc_opt( 'zc_footer_social_text', 'در شبکه‌های اجتماعی همراه ما باشید!' ) ); ?>
				</span>
			</div>

			<a href="#zc-page" class="zc-footer__top-link">
				<span><?php esc_html_e( 'بازگشت به بالا', 'zarincode' ); ?></span>
				<span class="zc-social-btn"><?php zc_the_icon( 'arrow-up', 17 ); ?></span>
			</a>
		</div>
	</div>

	<div class="zc-container">
		<div class="zc-footer__grid">

			<div class="zc-footer__brand">
				<?php zc_site_logo( 'footer' ); ?>
				<p class="zc-footer__about"><?php echo esc_html( $zc_about ); ?></p>
			</div>

			<div>
				<h4 class="zc-footer__col-title"><?php echo esc_html( zc_opt( 'zc_footer_col1_title', 'دسترسی سریع' ) ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer_1' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer_1',
							'container'      => false,
							'menu_class'     => 'zc-footer__links',
							'depth'          => 1,
						)
					);
				} else {
					zc_footer_fallback_links(
						array(
							__( 'دوره‌های آموزشی', 'zarincode' )  => '#',
							__( 'فروشگاه محصولات', 'zarincode' )  => '#',
							__( 'مقالات و آموزش‌ها', 'zarincode' ) => '#',
							__( 'درباره ما', 'zarincode' )        => '#',
						)
					);
				}
				?>
			</div>

			<div>
				<h4 class="zc-footer__col-title"><?php echo esc_html( zc_opt( 'zc_footer_col2_title', 'لینک‌های مفید' ) ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer_2' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer_2',
							'container'      => false,
							'menu_class'     => 'zc-footer__links',
							'depth'          => 1,
						)
					);
				} else {
					zc_footer_fallback_links(
						array(
							__( 'قوانین و مقررات', 'zarincode' )    => '#',
							__( 'حریم خصوصی', 'zarincode' )         => '#',
							__( 'سوالات متداول', 'zarincode' )      => '#',
							__( 'همکاری در فروش', 'zarincode' )     => '#',
						)
					);
				}
				?>
			</div>

			<div>
				<h4 class="zc-footer__col-title"><?php esc_html_e( 'تماس با ما', 'zarincode' ); ?></h4>
				<ul class="zc-footer__contact">
					<?php if ( $zc_phone ) : ?>
					<li><?php zc_the_icon( 'phone', 17 ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $zc_phone ) ); ?>"><?php echo esc_html( zc_fa_num( $zc_phone ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $zc_mobile ) : ?>
					<li>
						<?php zc_the_icon( 'phone', 17 ); ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $zc_mobile ) ); ?>"><?php echo esc_html( zc_fa_num( $zc_mobile ) ); ?></a>
					</li>
					<?php endif; ?>

					<?php if ( $zc_email ) : ?>
					<li><?php zc_the_icon( 'mail', 17 ); ?><a href="mailto:<?php echo esc_attr( $zc_email ); ?>"><?php echo esc_html( $zc_email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $zc_address ) : ?>
					<li><?php zc_the_icon( 'pin', 17 ); ?><span><?php echo esc_html( $zc_address ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="zc-container">
		<div class="zc-footer__bottom">
			<span><?php echo esc_html( $zc_copy ); ?></span>
			<div class="zc-footer__badges">
				<?php
				$zc_badges = zc_opt( 'zc_footer_badges', array() );
				$zc_defsize = max( 48, (int) zc_opt( 'zc_footer_badge_size', 84 ) );

				if ( is_array( $zc_badges ) && ! empty( $zc_badges ) ) {
					foreach ( $zc_badges as $zc_badge ) {
						$zc_type = $zc_badge['type'] ?? ( $zc_badge['image'] ? 'image' : 'html' );
						$zc_img  = isset( $zc_badge['image'] ) ? $zc_badge['image'] : '';
						$zc_link = $zc_badge['link'] ?? $zc_badge['url'] ?? '';
						$zc_html = isset( $zc_badge['html'] ) ? $zc_badge['html'] : '';
						$zc_size = ! empty( $zc_badge['size'] ) ? max( 48, (int) $zc_badge['size'] ) : $zc_defsize;

						/*
						 * کد HTML رسمی (زرین‌پال، ای‌نماد، ساماندهی و ...).
						 * درون یک کادر با اندازه‌ی دلخواه قرار می‌گیرد تا ابعاد
						 * نماد تحت کنترل کاربر باشد.
						 */
						if ( 'html' === $zc_type && $zc_html ) {
							echo '<div class="zc-footer__badge" style="width:' . (int) $zc_size . 'px;height:' . (int) $zc_size . 'px;display:flex;align-items:center;justify-content:center;overflow:hidden">';
							echo zc_kses_badge( $zc_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</div>';
							continue;
						}

						if ( 'image' === $zc_type && $zc_img ) {
							$zc_img_tag = '<img src="' . esc_url( $zc_img ) . '" alt="' . esc_attr__( 'نماد اعتماد', 'zarincode' ) . '" loading="lazy" decoding="async" style="width:' . (int) $zc_size . 'px;height:auto;max-height:' . (int) $zc_size . 'px">';
							if ( $zc_link ) {
								echo '<a href="' . esc_url( $zc_link ) . '" class="zc-footer__badge" target="_blank" rel="noopener nofollow">' . $zc_img_tag . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo '<span class="zc-footer__badge">' . $zc_img_tag . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							continue;
						}

						// نماد آماده‌ی قالب (SVG).
						if ( function_exists( 'zc_footer_badge_svg' ) ) {
							echo '<span class="zc-footer__badge" style="width:' . (int) $zc_size . 'px;height:' . (int) $zc_size . 'px">';
							echo zc_footer_badge_svg( $zc_badge['builtin'] ?? 'enamad', $zc_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '</span>';
						}
					}
				} else {
					echo '<span class="zc-footer__badge" style="padding:9px 16px;font-size:.76rem">' . esc_html__( 'نماد اعتماد الکترونیکی', 'zarincode' ) . '</span>';
					echo '<span class="zc-footer__badge" style="padding:9px 16px;font-size:.76rem">' . esc_html__( 'ساماندهی', 'zarincode' ) . '</span>';
				}

				// کد HTML دلخواه (زرین‌پال، ای‌نماد و ...) در اندازه‌ی مشخص.
				$zc_badges_html = (string) zc_opt( 'zc_footer_badges_html', '' );
				if ( '' !== trim( $zc_badges_html ) ) {
					$zc_html_size = max( 48, (int) zc_opt( 'zc_footer_badge_size', 84 ) );
					echo '<div class="zc-footer__badge zc-footer__badge--html" style="width:' . (int) $zc_html_size . 'px;height:' . (int) $zc_html_size . 'px;display:flex;align-items:center;justify-content:center;overflow:hidden">';
					echo zc_kses_badge( $zc_badges_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</div>';
				}
				?>
			</div>
		</div>
	</div>
</footer>
