<?php
/**
 * قالب اختصاصی صفحه‌ی تک‌نمونه‌کار
 *
 * روایت پروژه از چالش تا دستاورد، همراه با گالری، آمار، تکنولوژی‌ها
 * و کارت اطلاعات چسبان.
 *
 * مثل صفحه‌ی محصول، هر بخش فقط با داشتن داده رندر می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$pid    = get_the_ID();
	$schema = zc_project_detail_fields();

	$identity  = zc_detail_identity_rows( $pid, $schema );
	$challenge = zc_detail_value( $pid, '_zc_project_challenge' );
	$solution  = zc_detail_value( $pid, '_zc_project_solution' );
	$features  = zc_detail_value( $pid, '_zc_project_features', 'lines' );
	$results   = zc_detail_value( $pid, '_zc_project_results', 'lines' );
	$techs     = zc_detail_value( $pid, '_zc_project_tech_list', 'lines' );
	$services  = zc_detail_value( $pid, '_zc_project_services', 'lines' );
	$stats     = zc_detail_value( $pid, '_zc_project_stats', 'rows' );
	$phases    = zc_detail_value( $pid, '_zc_project_phases', 'log' );
	$quote     = zc_detail_value( $pid, '_zc_project_quote' );
	$quote_by  = zc_detail_value( $pid, '_zc_project_quote_by' );
	$url       = zc_detail_value( $pid, '_zc_project_url' );
	$repo      = zc_detail_value( $pid, '_zc_project_repo' );
	$client    = zc_detail_value( $pid, '_zc_project_client' );
	$version   = zc_detail_value( $pid, '_zc_project_version' );

	// گالری: شناسه‌های جداشده با کاما.
	$gallery_raw = zc_detail_value( $pid, '_zc_project_gallery' );
	$gallery     = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) $gallery_raw ) ) ) );
	$images      = array_values( array_filter( array_merge( array( get_post_thumbnail_id( $pid ) ), $gallery ) ) );

	$cats = get_the_terms( $pid, 'zc_project_cat' );
	$tech = get_the_terms( $pid, 'zc_project_tech' );
	?>

	<div class="zc-pdp zc-prj">

		<!-- ================= سربرگ ================= -->
		<div class="zc-pdp__top">
			<div class="zc-container">
				<?php zc_breadcrumb(); ?>

				<div class="zc-pdp__title-row">
					<h1 class="zc-pdp__title"><?php the_title(); ?></h1>

					<?php if ( $version ) : ?>
						<span class="zc-pdp__version" dir="ltr"><?php echo esc_html( zc_fa_num( $version ) ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $client ) : ?>
					<p class="zc-prj__client">
						<?php zc_the_icon( 'user', 16 ); ?>
						<?php
						printf(
							/* translators: %s: نام کارفرما */
							esc_html__( 'کارفرما: %s', 'zarincode' ),
							'<strong>' . esc_html( $client ) . '</strong>'
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
					<div class="zc-pdp__cats">
						<?php foreach ( $cats as $cat ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="zc-container">

			<!-- نوار آمار برجسته -->
			<?php if ( $stats ) : ?>
				<div class="zc-prj-stats">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="zc-prj-stats__item">
							<strong><?php echo esc_html( zc_fa_num( $stat['value'] ) ); ?></strong>
							<span><?php echo esc_html( $stat['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="zc-pdp__layout">

				<!-- ================= ستون محتوا ================= -->
				<div class="zc-pdp__main">

					<!-- گالری -->
					<?php if ( $images ) : ?>
						<div class="zc-pdp-gallery" data-zc-gallery>
							<div class="zc-pdp-gallery__stage">
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $images[0], 'large' ) ); ?>"
									alt="<?php echo esc_attr( get_the_title() ); ?>"
									class="zc-pdp-gallery__img" />

								<?php if ( $url ) : ?>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"
										class="zc-pdp-gallery__preview">
										<?php zc_the_icon( 'globe', 18 ); ?>
										<span><?php esc_html_e( 'مشاهده سایت پروژه', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>
							</div>

							<?php if ( count( $images ) > 1 ) : ?>
								<div class="zc-pdp-gallery__thumbs">
									<?php foreach ( array_slice( $images, 0, 8 ) as $i => $img ) : ?>
										<button type="button"
											class="zc-pdp-gallery__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>"
											data-full="<?php echo esc_url( wp_get_attachment_image_url( $img, 'large' ) ); ?>">
											<?php echo wp_get_attachment_image( $img, 'thumbnail' ); ?>
										</button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- چالش و راهکار -->
					<?php if ( $challenge || $solution ) : ?>
						<section class="zc-pdp-block">
							<div class="zc-prj-cs">
								<?php if ( $challenge ) : ?>
									<div class="zc-prj-cs__col zc-prj-cs__col--problem">
										<h2>
											<?php zc_the_icon( 'alert', 19 ); ?>
											<?php esc_html_e( 'چالش پروژه', 'zarincode' ); ?>
										</h2>
										<p><?php echo esc_html( $challenge ); ?></p>
									</div>
								<?php endif; ?>

								<?php if ( $solution ) : ?>
									<div class="zc-prj-cs__col zc-prj-cs__col--solution">
										<h2>
											<?php zc_the_icon( 'bulb', 19 ); ?>
											<?php esc_html_e( 'راهکار ما', 'zarincode' ); ?>
										</h2>
										<p><?php echo esc_html( $solution ); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- شرح کامل -->
					<?php if ( get_the_content() ) : ?>
						<section class="zc-pdp-block">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'file', 20 ); ?>
								<?php esc_html_e( 'شرح پروژه', 'zarincode' ); ?>
							</h2>

							<div class="zc-pdp-block__body zc-prose"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<!-- ویژگی‌ها و خدمات -->
					<?php if ( $features || $services ) : ?>
						<section class="zc-pdp-block">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'sparkle', 20 ); ?>
								<?php esc_html_e( 'ویژگی‌های پروژه', 'zarincode' ); ?>
							</h2>

							<?php zc_render_check_list( $features, 'zc-check-list--2col' ); ?>

							<?php if ( $services ) : ?>
								<h3 class="zc-pdp-block__sub"><?php esc_html_e( 'خدمات ارائه‌شده', 'zarincode' ); ?></h3>
								<?php zc_render_check_list( $services, 'zc-check-list--2col', 'target' ); ?>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<!-- مراحل اجرا -->
					<?php if ( $phases ) : ?>
						<section class="zc-pdp-block">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'clock', 20 ); ?>
								<?php esc_html_e( 'مراحل اجرا', 'zarincode' ); ?>
							</h2>

							<?php zc_render_changelog( $phases, false ); ?>
						</section>
					<?php endif; ?>

					<!-- دستاوردها -->
					<?php if ( $results ) : ?>
						<section class="zc-pdp-block zc-prj-results">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'award', 20 ); ?>
								<?php esc_html_e( 'دستاوردهای پروژه', 'zarincode' ); ?>
							</h2>

							<?php zc_render_check_list( $results, 'zc-check-list--2col' ); ?>
						</section>
					<?php endif; ?>

					<!-- نظر کارفرما -->
					<?php if ( $quote ) : ?>
						<section class="zc-prj-quote">
							<?php zc_the_icon( 'chat', 34 ); ?>
							<blockquote><?php echo esc_html( $quote ); ?></blockquote>

							<?php if ( $quote_by ) : ?>
								<cite><?php echo esc_html( $quote_by ); ?></cite>
							<?php endif; ?>
						</section>
					<?php endif; ?>
				</div>

				<!-- ================= ستون اطلاعات ================= -->
				<aside class="zc-pdp__side">
					<div class="zc-pdp-buy">

						<?php if ( $identity ) : ?>
							<div class="zc-pdp-buy__identity">
								<h3><?php esc_html_e( 'مشخصات پروژه', 'zarincode' ); ?></h3>
								<?php zc_render_spec_table( $identity, 'zc-spec-table--sm' ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $techs || ( $tech && ! is_wp_error( $tech ) ) ) : ?>
							<div class="zc-prj-tech">
								<h3><?php esc_html_e( 'تکنولوژی‌ها', 'zarincode' ); ?></h3>

								<div class="zc-prj-tech__list">
									<?php
									foreach ( $techs as $t ) :
										?>
										<span class="zc-prj-tech__chip"><?php echo esc_html( $t ); ?></span>
										<?php
									endforeach;

									if ( $tech && ! is_wp_error( $tech ) ) :
										foreach ( $tech as $t ) :
											?>
											<a class="zc-prj-tech__chip" href="<?php echo esc_url( get_term_link( $t ) ); ?>">
												<?php echo esc_html( $t->name ); ?>
											</a>
											<?php
										endforeach;
									endif;
									?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $url || $repo ) : ?>
							<div class="zc-pdp-buy__links">
								<?php if ( $url ) : ?>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow" class="zc-pdp-buy__link">
										<?php zc_the_icon( 'globe', 17 ); ?>
										<span><?php esc_html_e( 'مشاهده پروژه', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>

								<?php if ( $repo ) : ?>
									<a href="<?php echo esc_url( $repo ); ?>" target="_blank" rel="noopener" class="zc-pdp-buy__link">
										<?php zc_the_icon( 'code', 17 ); ?>
										<span><?php esc_html_e( 'مخزن کد', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<!-- فراخوان سفارش -->
						<div class="zc-prj-cta">
							<strong><?php esc_html_e( 'پروژه‌ای مشابه دارید؟', 'zarincode' ); ?></strong>
							<span><?php esc_html_e( 'رایگان مشاوره بگیرید و برآورد هزینه دریافت کنید.', 'zarincode' ); ?></span>

							<a href="<?php echo esc_url( home_url( '/#zc-request' ) ); ?>" class="zc-btn zc-btn--gold">
								<?php zc_the_icon( 'send', 17 ); ?>
								<span><?php esc_html_e( 'ثبت درخواست پروژه', 'zarincode' ); ?></span>
							</a>
						</div>

						<div class="zc-pdp-buy__share">
							<?php zc_share_buttons( $pid, true ); ?>
						</div>
					</div>
				</aside>
			</div>

			<!-- نمونه‌کارهای مرتبط -->
			<?php
			$related = new WP_Query(
				array(
					'post_type'           => 'zc_project',
					'posts_per_page'      => 3,
					'post__not_in'        => array( $pid ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'orderby'             => 'rand',
				)
			);

			if ( $related->have_posts() ) :
				?>
				<section class="zc-pdp-related">
					<h2 class="zc-pdp-block__title">
						<?php zc_the_icon( 'grid', 20 ); ?>
						<?php esc_html_e( 'نمونه‌کارهای مرتبط', 'zarincode' ); ?>
					</h2>

					<div class="zc-grid zc-grid--3">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							?>
							<a class="zc-prj-card" href="<?php the_permalink(); ?>">
								<span class="zc-prj-card__media">
									<?php echo zc_thumbnail( get_the_ID(), 'zc-card' ); // phpcs:ignore ?>
								</span>
								<span class="zc-prj-card__body">
									<strong><?php the_title(); ?></strong>
									<em><?php echo esc_html( zc_excerpt( 14 ) ); ?></em>
								</span>
							</a>
							<?php
						endwhile;

						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

get_footer();
