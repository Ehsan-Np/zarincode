<?php
/**
 * قالب تک خدمت — بازطراحی کامل
 *
 * ساختار: بنر تیره‌ی تمام‌عرض با آمار شناور، ستون محتوا (شرح، شامل چه
 * مواردی، مراحل اجرا، بسته‌های قیمتی، پرسش‌های متداول، فرم سفارش) و
 * ستون کناری چسبان با کارت سفارش — نه سایدبار عمومی وبلاگ.
 *
 * هر بخش فقط با داشتن داده رندر می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$zc_id       = get_the_ID();
	$zc_icon     = get_post_meta( $zc_id, '_zc_service_icon', true );
	$zc_icon     = $zc_icon ? $zc_icon : 'code';
	$zc_from     = (float) get_post_meta( $zc_id, '_zc_service_price_from', true );
	$zc_duration = get_post_meta( $zc_id, '_zc_service_duration', true );
	$zc_color    = get_post_meta( $zc_id, '_zc_service_color', true );

	$zc_features = get_post_meta( $zc_id, '_zc_features', true );
	$zc_features = is_array( $zc_features ) ? array_filter( $zc_features ) : array();

	$zc_packages = zc_get_service_packages( $zc_id );

	// فیلدهای تازه‌ی این بازطراحی.
	$zc_steps = zc_service_lines( $zc_id, '_zc_service_steps' );
	$zc_faq   = zc_service_lines( $zc_id, '_zc_service_faq' );
	$zc_stats = zc_service_lines( $zc_id, '_zc_service_stats' );
	$zc_tools = zc_service_lines( $zc_id, '_zc_service_tools' );

	$zc_phone = zc_opt( 'zc_phone', '071-42380267' );
	?>

	<article id="service-<?php the_ID(); ?>" <?php post_class( 'zc-svc' ); ?>
		<?php echo $zc_color ? 'style="--zc-svc:' . esc_attr( $zc_color ) . '"' : ''; ?>>

		<!-- ============ بنر ============ -->
		<header class="zc-svc-hero">
			<span class="zc-svc-hero__glow zc-svc-hero__glow--a" aria-hidden="true"></span>
			<span class="zc-svc-hero__glow zc-svc-hero__glow--b" aria-hidden="true"></span>
			<span class="zc-svc-hero__mesh" aria-hidden="true"></span>

			<div class="zc-container">
				<?php zc_breadcrumb( true ); ?>

				<div class="zc-svc-hero__in">
					<div class="zc-svc-hero__body" data-zc-anim="up">
						<span class="zc-svc-hero__icon"><?php zc_the_icon( $zc_icon, 30 ); ?></span>

						<h1 class="zc-svc-hero__title"><?php the_title(); ?></h1>

						<?php if ( has_excerpt() ) : ?>
							<p class="zc-svc-hero__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="zc-svc-hero__cta">
							<a href="#zc-order" class="zc-btn zc-btn--gold zc-btn--lg">
								<?php zc_the_icon( 'send', 18 ); ?>
								<span><?php esc_html_e( 'ثبت درخواست', 'zarincode' ); ?></span>
							</a>

							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $zc_phone ) ); ?>"
								class="zc-btn zc-btn--ghost zc-btn--lg">
								<?php zc_the_icon( 'phone', 18 ); ?>
								<span><?php esc_html_e( 'مشاوره تلفنی', 'zarincode' ); ?></span>
							</a>
						</div>
					</div>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="zc-svc-hero__media" data-zc-anim="flip" data-zc-parallax="0.08">
							<?php the_post_thumbnail( 'zc-card', array( 'loading' => 'eager' ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="zc-container">

			<!-- نوار آمار شناور -->
			<?php if ( $zc_stats || $zc_from || $zc_duration ) : ?>
				<div class="zc-svc-facts" data-zc-stagger>
					<?php if ( $zc_from ) : ?>
						<div class="zc-svc-facts__item">
							<?php zc_the_icon( 'wallet', 20 ); ?>
							<div>
								<small><?php esc_html_e( 'شروع قیمت از', 'zarincode' ); ?></small>
								<strong><?php echo esc_html( zc_price_text( $zc_from ) ); ?></strong>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $zc_duration ) : ?>
						<div class="zc-svc-facts__item">
							<?php zc_the_icon( 'clock', 20 ); ?>
							<div>
								<small><?php esc_html_e( 'زمان تحویل', 'zarincode' ); ?></small>
								<strong><?php echo esc_html( zc_fa_num( $zc_duration ) ); ?></strong>
							</div>
						</div>
					<?php endif; ?>

					<?php
					foreach ( $zc_stats as $zc_stat ) :
						$zc_parts = array_map( 'trim', explode( '|', $zc_stat, 2 ) );
						?>
						<div class="zc-svc-facts__item">
							<?php zc_the_icon( 'award', 20 ); ?>
							<div>
								<small><?php echo esc_html( $zc_parts[0] ); ?></small>
								<strong><?php echo esc_html( zc_fa_num( $zc_parts[1] ?? '' ) ); ?></strong>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="zc-svc-layout">

				<!-- ============ ستون محتوا ============ -->
				<div class="zc-svc-main">

					<!-- شرح خدمت -->
					<?php if ( get_the_content() ) : ?>
						<section class="zc-svc-block" data-zc-anim="up">
							<h2 class="zc-svc-block__title">
								<?php zc_the_icon( 'file', 20 ); ?>
								<?php esc_html_e( 'درباره این خدمت', 'zarincode' ); ?>
							</h2>

							<div class="zc-svc-block__body zc-prose"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<!-- شامل چه مواردی -->
					<?php if ( $zc_features ) : ?>
						<section class="zc-svc-block" data-zc-anim="up">
							<h2 class="zc-svc-block__title">
								<?php zc_the_icon( 'package', 20 ); ?>
								<?php esc_html_e( 'این خدمت شامل چه مواردی است؟', 'zarincode' ); ?>
							</h2>

							<div class="zc-svc-inc" data-zc-stagger>
								<?php foreach ( $zc_features as $zc_feature ) : ?>
									<div class="zc-svc-inc__item">
										<span class="zc-svc-inc__tick"><?php zc_the_icon( 'check', 15 ); ?></span>
										<span><?php echo esc_html( $zc_feature ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- مراحل اجرا -->
					<?php if ( $zc_steps ) : ?>
						<section class="zc-svc-block" data-zc-anim="up">
							<h2 class="zc-svc-block__title">
								<?php zc_the_icon( 'target', 20 ); ?>
								<?php esc_html_e( 'مراحل انجام کار', 'zarincode' ); ?>
							</h2>

							<ol class="zc-svc-steps" data-zc-stagger>
								<?php
								foreach ( $zc_steps as $zc_i => $zc_step ) :
									$zc_parts = array_map( 'trim', explode( '|', $zc_step, 2 ) );
									?>
									<li class="zc-svc-steps__item">
										<span class="zc-svc-steps__num"><?php echo esc_html( zc_fa_num( $zc_i + 1 ) ); ?></span>

										<div class="zc-svc-steps__body">
											<strong><?php echo esc_html( $zc_parts[0] ); ?></strong>

											<?php if ( ! empty( $zc_parts[1] ) ) : ?>
												<p><?php echo esc_html( $zc_parts[1] ); ?></p>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>

					<!-- بسته‌های قیمتی -->
					<?php if ( $zc_packages ) : ?>
						<section class="zc-svc-block zc-svc-block--flush" id="zc-packages">
							<h2 class="zc-svc-block__title">
								<?php zc_the_icon( 'wallet', 20 ); ?>
								<?php esc_html_e( 'بسته‌های قیمتی', 'zarincode' ); ?>
							</h2>

							<div class="zc-pkgs">
								<?php
								foreach ( $zc_packages as $zc_i => $zc_pkg ) :
									$zc_pop = ! empty( $zc_pkg['popular'] );
									?>
									<div class="zc-pkg<?php echo $zc_pop ? ' is-popular' : ''; ?>"
										data-zc-anim="rise" data-zc-delay="<?php echo (int) ( $zc_i * 90 ); ?>">

										<?php if ( $zc_pop ) : ?>
											<span class="zc-pkg__badge"><?php esc_html_e( 'پیشنهاد ویژه', 'zarincode' ); ?></span>
										<?php endif; ?>

										<h3 class="zc-pkg__name"><?php echo esc_html( $zc_pkg['title'] ); ?></h3>

										<div class="zc-pkg__price">
											<?php echo esc_html( zc_price_text( (float) $zc_pkg['price'] ) ); ?>
										</div>

										<?php if ( ! empty( $zc_pkg['delivery'] ) ) : ?>
											<p class="zc-pkg__delivery">
												<?php zc_the_icon( 'clock', 14 ); ?>
												<?php echo esc_html( zc_fa_num( $zc_pkg['delivery'] ) ); ?>
											</p>
										<?php endif; ?>

										<?php if ( ! empty( $zc_pkg['features'] ) ) : ?>
											<ul class="zc-pkg__list">
												<?php foreach ( (array) $zc_pkg['features'] as $zc_pf ) : ?>
													<li><?php zc_the_icon( 'check', 15 ); ?><span><?php echo esc_html( $zc_pf ); ?></span></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>

										<a href="#zc-order"
											class="zc-btn <?php echo $zc_pop ? 'zc-btn--gold' : 'zc-btn--outline'; ?> zc-btn--block"
											data-zc-pkg="<?php echo esc_attr( $zc_pkg['title'] ); ?>">
											<?php esc_html_e( 'انتخاب این بسته', 'zarincode' ); ?>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- پرسش‌های متداول -->
					<?php if ( $zc_faq ) : ?>
						<section class="zc-svc-block" data-zc-anim="up">
							<h2 class="zc-svc-block__title">
								<?php zc_the_icon( 'question', 20 ); ?>
								<?php esc_html_e( 'پرسش‌های متداول', 'zarincode' ); ?>
							</h2>

							<div class="zc-svc-faq">
								<?php
								foreach ( $zc_faq as $zc_i => $zc_row ) :
									$zc_parts = array_map( 'trim', explode( '|', $zc_row, 2 ) );

									if ( empty( $zc_parts[1] ) ) {
										continue;
									}
									?>
									<details class="zc-svc-faq__item"<?php echo 0 === $zc_i ? ' open' : ''; ?>>
										<summary>
											<span><?php echo esc_html( $zc_parts[0] ); ?></span>
											<?php zc_the_icon( 'chevron', 18 ); ?>
										</summary>

										<div class="zc-svc-faq__a"><?php echo esc_html( $zc_parts[1] ); ?></div>
									</details>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- فرم سفارش -->
					<section id="zc-order" class="zc-svc-block zc-svc-order" data-zc-anim="up">
						<h2 class="zc-svc-block__title">
							<?php zc_the_icon( 'edit', 20 ); ?>
							<?php esc_html_e( 'ثبت درخواست این خدمت', 'zarincode' ); ?>
						</h2>

						<p class="zc-svc-order__sub">
							<?php esc_html_e( 'فرم زیر را پر کنید؛ کارشناسان ما در کمتر از ۲۴ ساعت با شما تماس می‌گیرند.', 'zarincode' ); ?>
						</p>

						<?php echo do_shortcode( '[zc_request_form service="' . (int) $zc_id . '"]' ); ?>
					</section>

					<?php
					if ( comments_open() || get_comments_number() ) {
						try {
							comments_template();
						} catch ( \Throwable $zc_e ) {
							do_action( 'zc_single_section_error', 'comments', $zc_e );
						}
					}
					?>
				</div>

				<!-- ============ ستون کناری ============ -->
				<aside class="zc-svc-side">
					<div class="zc-svc-card">

						<div class="zc-svc-card__head">
							<span class="zc-svc-card__icon"><?php zc_the_icon( $zc_icon, 24 ); ?></span>
							<strong><?php esc_html_e( 'سفارش سریع', 'zarincode' ); ?></strong>
						</div>

						<?php if ( $zc_from ) : ?>
							<div class="zc-svc-card__price">
								<small><?php esc_html_e( 'شروع از', 'zarincode' ); ?></small>
								<strong><?php echo esc_html( zc_price_text( $zc_from ) ); ?></strong>
							</div>
						<?php endif; ?>

						<a href="#zc-order" class="zc-btn zc-btn--gold zc-btn--block">
							<?php zc_the_icon( 'send', 17 ); ?>
							<span><?php esc_html_e( 'ثبت درخواست', 'zarincode' ); ?></span>
						</a>

						<?php if ( $zc_packages ) : ?>
							<a href="#zc-packages" class="zc-btn zc-btn--outline zc-btn--block">
								<?php zc_the_icon( 'wallet', 17 ); ?>
								<span><?php esc_html_e( 'مشاهده بسته‌ها', 'zarincode' ); ?></span>
							</a>
						<?php endif; ?>

						<ul class="zc-svc-card__trust">
							<li><?php zc_the_icon( 'shield', 16 ); ?><span><?php esc_html_e( 'قرارداد رسمی و شفاف', 'zarincode' ); ?></span></li>
							<li><?php zc_the_icon( 'refresh', 16 ); ?><span><?php esc_html_e( 'تحویل مرحله‌ای', 'zarincode' ); ?></span></li>
							<li><?php zc_the_icon( 'headphone', 16 ); ?><span><?php esc_html_e( 'پشتیبانی پس از تحویل', 'zarincode' ); ?></span></li>
						</ul>

						<div class="zc-svc-card__contact">
							<span><?php esc_html_e( 'مشاوره رایگان:', 'zarincode' ); ?></span>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $zc_phone ) ); ?>" dir="ltr">
								<?php zc_the_icon( 'phone', 16 ); ?>
								<?php echo esc_html( zc_fa_num( $zc_phone ) ); ?>
							</a>
						</div>
					</div>

					<?php if ( $zc_tools ) : ?>
						<div class="zc-svc-card zc-svc-card--tools">
							<h3><?php esc_html_e( 'ابزارها و تکنولوژی‌ها', 'zarincode' ); ?></h3>

							<div class="zc-svc-chips">
								<?php foreach ( $zc_tools as $zc_tool ) : ?>
									<span class="zc-svc-chip"><?php echo esc_html( $zc_tool ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="zc-svc-card zc-svc-card--share">
						<?php zc_share_buttons( $zc_id, true ); ?>
					</div>
				</aside>
			</div>

			<!-- خدمات دیگر -->
			<?php
			$zc_others = new WP_Query(
				array(
					'post_type'           => 'zc_service',
					'posts_per_page'      => 3,
					'post__not_in'        => array( $zc_id ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'orderby'             => 'rand',
				)
			);

			if ( $zc_others->have_posts() ) :
				?>
				<section class="zc-svc-others">
					<h2 class="zc-svc-block__title">
						<?php zc_the_icon( 'grid', 20 ); ?>
						<?php esc_html_e( 'سایر خدمات ما', 'zarincode' ); ?>
					</h2>

					<div class="zc-grid zc-grid--3" data-zc-stagger>
						<?php
						while ( $zc_others->have_posts() ) :
							$zc_others->the_post();

							$zc_oi = get_post_meta( get_the_ID(), '_zc_service_icon', true );
							?>
							<a class="zc-svc-mini" href="<?php the_permalink(); ?>">
								<span class="zc-svc-mini__icon"><?php zc_the_icon( $zc_oi ? $zc_oi : 'code', 22 ); ?></span>
								<strong><?php the_title(); ?></strong>
								<em><?php echo esc_html( zc_excerpt( 13 ) ); ?></em>
							</a>
							<?php
						endwhile;

						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</article>

	<?php
endwhile;

get_footer();
