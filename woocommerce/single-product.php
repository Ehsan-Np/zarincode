<?php
/**
 * قالب اختصاصی صفحه‌ی تک‌محصول زرین کد
 *
 * چیدمان دوستونی به سبک بازارهای فایل: ستون راست گالری و توضیحات
 * کامل، ستون چپ کارت خرید چسبان با آمار، مزیت‌ها و شناسنامه.
 *
 * هر بخش فقط زمانی رندر می‌شود که داده داشته باشد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	global $product;

	if ( ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}

	$pid    = get_the_ID();
	$schema = zc_product_detail_fields();

	// همه‌ی مقادیر یک‌جا؛ خالی‌ها خودکار کنار می‌روند.
	$identity = zc_detail_identity_rows( $pid, $schema );
	$benefits = zc_detail_value( $pid, '_zc_benefits', 'lines' );
	$features = zc_detail_value( $pid, '_zc_features', 'lines' );
	$included = zc_detail_value( $pid, '_zc_included', 'lines' );
	$requires = zc_detail_value( $pid, '_zc_requirements', 'lines' );
	$specs    = zc_detail_value( $pid, '_zc_specs', 'rows' );
	$compat   = zc_detail_value( $pid, '_zc_compat', 'rows' );
	$changes  = zc_detail_value( $pid, '_zc_changelog', 'log' );

	$preview    = zc_detail_value( $pid, '_zc_preview_url' );
	$preview_fa = zc_detail_value( $pid, '_zc_preview_fa_url' );
	$docs       = zc_detail_value( $pid, '_zc_docs_url' );
	$video      = zc_detail_value( $pid, '_zc_video_url' );
	$demo_login = zc_detail_value( $pid, '_zc_demo_login' );
	$version    = zc_detail_value( $pid, '_zc_version' );
	$author     = zc_detail_value( $pid, '_zc_product_author' );

	$gallery = $product ? $product->get_gallery_image_ids() : array();
	$images  = array_values( array_filter( array_merge( array( get_post_thumbnail_id( $pid ) ), $gallery ) ) );
	$sold    = $product ? (int) $product->get_total_sales() : 0;
	$rating  = $product ? (float) $product->get_average_rating() : 0;
	$reviews = $product ? (int) $product->get_review_count() : 0;

	// درصد رضایت از میانگین امتیاز ساخته می‌شود.
	$satisfaction = $rating ? round( $rating / 5 * 100 ) : 0;
	?>

	<div class="zc-pdp">

		<!-- ================= سربرگ ================= -->
		<div class="zc-pdp__top">
			<div class="zc-container">
				<?php zc_breadcrumb(); ?>

				<div class="zc-pdp__title-row">
					<h1 class="zc-pdp__title"><?php the_title(); ?></h1>

					<?php if ( $version ) : ?>
						<span class="zc-pdp__version" dir="ltr">
							<?php echo esc_html( zc_fa_num( $version ) ); ?>
						</span>
					<?php endif; ?>
				</div>

				<?php
				$cats = get_the_terms( $pid, 'product_cat' );

				if ( $cats && ! is_wp_error( $cats ) ) :
					?>
					<div class="zc-pdp__cats">
						<?php foreach ( $cats as $cat ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="zc-container">
			<div class="zc-pdp__layout">

				<!-- ================= ستون محتوا ================= -->
				<div class="zc-pdp__main">

					<!-- گالری -->
					<?php if ( $images ) : ?>
						<div class="zc-pdp-gallery" data-zc-gallery>
							<div class="zc-pdp-gallery__stage">
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $images[0], 'large' ) ); ?>"
									alt="<?php echo esc_attr( get_the_title() ); ?>"
									class="zc-pdp-gallery__img" id="zc-pdp-main-img" />

								<?php if ( $preview || $preview_fa ) : ?>
									<a href="<?php echo esc_url( $preview_fa ? $preview_fa : $preview ); ?>"
										target="_blank" rel="noopener nofollow"
										class="zc-pdp-gallery__preview">
										<?php zc_the_icon( 'eye', 18 ); ?>
										<span><?php esc_html_e( 'پیش‌نمایش زنده', 'zarincode' ); ?></span>
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

					<!-- نوار پرش سریع -->
					<?php
					$tabs = array();

					if ( get_the_content() ) {
						$tabs['desc'] = __( 'توضیحات', 'zarincode' );
					}
					if ( $features ) {
						$tabs['features'] = __( 'ویژگی‌ها', 'zarincode' );
					}
					if ( $specs || $compat ) {
						$tabs['specs'] = __( 'مشخصات', 'zarincode' );
					}
					if ( $changes ) {
						$tabs['changelog'] = __( 'تغییرات', 'zarincode' );
					}
					if ( comments_open() || $reviews ) {
						$tabs['reviews'] = __( 'دیدگاه‌ها', 'zarincode' );
					}

					if ( count( $tabs ) > 1 ) :
						?>
						<nav class="zc-pdp-jump" data-zc-jump>
							<?php foreach ( $tabs as $key => $label ) : ?>
								<a href="#zc-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></a>
							<?php endforeach; ?>
						</nav>
					<?php endif; ?>

					<!-- توضیحات -->
					<?php if ( get_the_content() ) : ?>
						<section class="zc-pdp-block" id="zc-desc">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'file', 20 ); ?>
								<?php printf( /* translators: %s: نام محصول */ esc_html__( 'معرفی %s', 'zarincode' ), esc_html( get_the_title() ) ); ?>
							</h2>

							<div class="zc-pdp-block__body zc-prose">
								<?php the_content(); ?>
							</div>
						</section>
					<?php endif; ?>

					<!-- ویدیوی معرفی -->
					<?php if ( $video ) : ?>
						<section class="zc-pdp-block">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'video', 20 ); ?>
								<?php esc_html_e( 'ویدیوی معرفی', 'zarincode' ); ?>
							</h2>

							<div class="zc-pdp-video">
								<iframe src="<?php echo esc_url( $video ); ?>" allowfullscreen loading="lazy"
									title="<?php echo esc_attr( get_the_title() ); ?>"></iframe>
							</div>
						</section>
					<?php endif; ?>

					<!-- ویژگی‌ها + محتویات بسته -->
					<?php if ( $features || $included ) : ?>
						<section class="zc-pdp-block" id="zc-features">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'sparkle', 20 ); ?>
								<?php esc_html_e( 'ویژگی‌های محصول', 'zarincode' ); ?>
							</h2>

							<?php if ( $features ) : ?>
								<?php zc_render_check_list( $features, 'zc-check-list--2col' ); ?>
							<?php endif; ?>

							<?php if ( $included ) : ?>
								<h3 class="zc-pdp-block__sub"><?php esc_html_e( 'در این بسته دریافت می‌کنید', 'zarincode' ); ?></h3>
								<?php zc_render_check_list( $included, 'zc-check-list--2col', 'package' ); ?>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<!-- مشخصات فنی و سازگاری -->
					<?php if ( $specs || $compat || $requires ) : ?>
						<section class="zc-pdp-block" id="zc-specs">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'settings', 20 ); ?>
								<?php esc_html_e( 'مشخصات فنی', 'zarincode' ); ?>
							</h2>

							<?php zc_render_spec_table( $specs ); ?>

							<?php if ( $compat ) : ?>
								<h3 class="zc-pdp-block__sub"><?php esc_html_e( 'سازگاری با', 'zarincode' ); ?></h3>
								<?php zc_render_spec_table( $compat, 'zc-spec-table--compat' ); ?>
							<?php endif; ?>

							<?php if ( $requires ) : ?>
								<h3 class="zc-pdp-block__sub"><?php esc_html_e( 'پیش‌نیازها', 'zarincode' ); ?></h3>
								<?php zc_render_check_list( $requires, '', 'alert' ); ?>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<!-- تاریخچه تغییرات -->
					<?php if ( $changes ) : ?>
						<section class="zc-pdp-block" id="zc-changelog">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'refresh', 20 ); ?>
								<?php esc_html_e( 'تاریخچه تغییرات', 'zarincode' ); ?>
							</h2>

							<?php zc_render_changelog( $changes ); ?>
						</section>
					<?php endif; ?>

					<!-- دیدگاه‌ها -->
					<?php if ( comments_open() || $reviews ) : ?>
						<section class="zc-pdp-block" id="zc-reviews">
							<h2 class="zc-pdp-block__title">
								<?php zc_the_icon( 'chat', 20 ); ?>
								<?php esc_html_e( 'دیدگاه کاربران', 'zarincode' ); ?>
							</h2>

							<?php comments_template(); ?>
						</section>
					<?php endif; ?>
				</div>

				<!-- ================= ستون خرید ================= -->
				<aside class="zc-pdp__side">
					<div class="zc-pdp-buy">

						<!-- آمار -->
						<div class="zc-pdp-buy__stats">
							<div class="zc-pdp-buy__stat">
								<strong><?php echo esc_html( zc_fa_num( $sold ) ); ?></strong>
								<span><?php esc_html_e( 'فروش', 'zarincode' ); ?></span>
							</div>

							<div class="zc-pdp-buy__stat">
								<strong><?php echo $satisfaction ? esc_html( zc_fa_num( $satisfaction ) . '٪' ) : '—'; ?></strong>
								<span><?php esc_html_e( 'رضایت', 'zarincode' ); ?></span>
							</div>

							<div class="zc-pdp-buy__stat">
								<strong><?php echo esc_html( zc_fa_num( $reviews ) ); ?></strong>
								<span><?php esc_html_e( 'دیدگاه', 'zarincode' ); ?></span>
							</div>
						</div>

						<!-- مزیت‌های خرید -->
						<?php if ( $benefits ) : ?>
							<?php zc_render_check_list( $benefits, 'zc-pdp-buy__benefits' ); ?>
						<?php endif; ?>

						<!-- قیمت و خرید -->
						<?php if ( $product ) : ?>
							<div class="zc-pdp-buy__price">
								<span class="zc-pdp-buy__price-label"><?php esc_html_e( 'قیمت محصول:', 'zarincode' ); ?></span>
								<span class="zc-pdp-buy__price-val"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
							</div>

							<div class="zc-pdp-buy__cart">
								<?php woocommerce_template_single_add_to_cart(); ?>
							</div>
						<?php endif; ?>

						<!-- لینک‌های پیش‌نمایش -->
						<?php if ( $preview || $preview_fa || $docs ) : ?>
							<div class="zc-pdp-buy__links">
								<?php if ( $preview ) : ?>
									<a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener nofollow" class="zc-pdp-buy__link">
										<?php zc_the_icon( 'eye', 17 ); ?>
										<span><?php esc_html_e( 'پیش‌نمایش اصلی', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>

								<?php if ( $preview_fa ) : ?>
									<a href="<?php echo esc_url( $preview_fa ); ?>" target="_blank" rel="noopener nofollow" class="zc-pdp-buy__link">
										<?php zc_the_icon( 'globe', 17 ); ?>
										<span><?php esc_html_e( 'پیش‌نمایش فارسی', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>

								<?php if ( $docs ) : ?>
									<a href="<?php echo esc_url( $docs ); ?>" target="_blank" rel="noopener" class="zc-pdp-buy__link">
										<?php zc_the_icon( 'file', 17 ); ?>
										<span><?php esc_html_e( 'مستندات محصول', 'zarincode' ); ?></span>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $demo_login ) : ?>
							<p class="zc-pdp-buy__demo">
								<?php zc_the_icon( 'key', 15 ); ?>
								<span><?php esc_html_e( 'ورود به دمو:', 'zarincode' ); ?>
									<code dir="ltr"><?php echo esc_html( $demo_login ); ?></code>
								</span>
							</p>
						<?php endif; ?>

						<!-- شناسنامه -->
						<?php if ( $identity ) : ?>
							<div class="zc-pdp-buy__identity">
								<h3><?php esc_html_e( 'شناسنامه محصول', 'zarincode' ); ?></h3>
								<?php zc_render_spec_table( $identity, 'zc-spec-table--sm' ); ?>
							</div>
						<?php endif; ?>

						<!-- اشتراک‌گذاری -->
						<div class="zc-pdp-buy__share">
							<?php zc_share_buttons( $pid, true ); ?>
						</div>
					</div>
				</aside>
			</div>

			<!-- محصولات مرتبط -->
			<?php
			$related = wc_get_related_products( $pid, 4 );

			if ( $related ) :
				?>
				<section class="zc-pdp-related">
					<h2 class="zc-pdp-block__title">
						<?php zc_the_icon( 'grid', 20 ); ?>
						<?php esc_html_e( 'محصولات مرتبط', 'zarincode' ); ?>
					</h2>

					<div class="zc-grid zc-grid--4">
						<?php
						foreach ( $related as $rid ) {
							$GLOBALS['post']    = get_post( $rid ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
							$GLOBALS['product'] = wc_get_product( $rid ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

							setup_postdata( $GLOBALS['post'] );
							wc_get_template_part( 'content', 'product' );
						}

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
