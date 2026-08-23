<?php
/**
 * صفحه تک‌دوره — طراحی الهام‌گرفته از مکتب‌خونه
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$zc_id       = get_the_ID();
	$zc_price    = (float) get_post_meta( $zc_id, '_zc_price', true );
	$zc_sale     = (float) get_post_meta( $zc_id, '_zc_sale_price', true );
	$zc_students = (int) get_post_meta( $zc_id, '_zc_students', true );
	$zc_level    = get_post_meta( $zc_id, '_zc_level', true );
	$zc_rating   = (float) get_post_meta( $zc_id, '_zc_rating', true );
	$zc_teacher  = get_post_meta( $zc_id, '_zc_teacher', true );
	$zc_video    = get_post_meta( $zc_id, '_zc_preview_video', true );
	$zc_product  = (int) get_post_meta( $zc_id, '_zc_product_id', true );
	$zc_status   = get_post_meta( $zc_id, '_zc_course_status', true );
	$zc_features = get_post_meta( $zc_id, '_zc_features', true );
	$zc_prereq   = get_post_meta( $zc_id, '_zc_prerequisites', true );
	$zc_audience = get_post_meta( $zc_id, '_zc_audience', true );
	$zc_sections = zc_get_curriculum( $zc_id );
	$zc_lessons  = zc_count_lessons( $zc_id );
	$zc_cats     = get_the_terms( $zc_id, 'zc_course_cat' );
	$zc_has      = zc_user_has_course( get_current_user_id(), $zc_id );
	$zc_progress = $zc_has ? zc_get_course_progress( get_current_user_id(), $zc_id ) : 0;

	$zc_levels = array(
		'beginner'     => __( 'مقدماتی', 'zarincode' ),
		'intermediate' => __( 'متوسط', 'zarincode' ),
		'advanced'     => __( 'پیشرفته', 'zarincode' ),
	);
	$zc_statuses = array(
		'completed'   => array( __( 'تکمیل شده', 'zarincode' ), 'green' ),
		'in-progress' => array( __( 'در حال برگزاری', 'zarincode' ), 'orange' ),
		'upcoming'    => array( __( 'به زودی', 'zarincode' ), 'blue' ),
	);
	?>

	<article class="zc-course-single">

		<!-- هدر دوره -->
		<div class="zc-course-hero">
			<div class="zc-container">
				<?php zc_breadcrumb( true ); ?>

				<div class="zc-course-hero__grid">
					<div class="zc-course-hero__info">

						<div class="zc-course-hero__badges">
							<?php if ( $zc_cats && ! is_wp_error( $zc_cats ) ) : ?>
								<a href="<?php echo esc_url( get_term_link( $zc_cats[0] ) ); ?>" class="zc-badge zc-badge--solid"><?php echo esc_html( $zc_cats[0]->name ); ?></a>
							<?php endif; ?>
							<?php if ( isset( $zc_levels[ $zc_level ] ) ) : ?>
								<span class="zc-badge" style="background:rgba(255,255,255,.14);color:#fff"><?php zc_the_icon( 'chart', 14 ); ?><?php echo esc_html( $zc_levels[ $zc_level ] ); ?></span>
							<?php endif; ?>
							<?php if ( isset( $zc_statuses[ $zc_status ] ) ) : ?>
								<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_statuses[ $zc_status ][1] ); ?>"><?php echo esc_html( $zc_statuses[ $zc_status ][0] ); ?></span>
							<?php endif; ?>
						</div>

						<h1 class="zc-course-hero__title"><?php the_title(); ?></h1>

						<?php if ( has_excerpt() ) : ?>
							<p class="zc-course-hero__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="zc-course-hero__meta">
							<?php if ( $zc_teacher ) : ?>
								<span><?php zc_the_icon( 'user', 17 ); ?><?php esc_html_e( 'مدرس:', 'zarincode' ); ?> <strong><?php echo esc_html( $zc_teacher ); ?></strong></span>
							<?php endif; ?>
							<span><?php zc_the_icon( 'users', 17 ); ?><?php echo esc_html( zc_fa_num( number_format( $zc_students ) ) ); ?> <?php esc_html_e( 'دانشجو', 'zarincode' ); ?></span>
							<span><?php zc_the_icon( 'video', 17 ); ?><?php echo esc_html( zc_fa_num( $zc_lessons ) ); ?> <?php esc_html_e( 'جلسه', 'zarincode' ); ?></span>
							<span><?php zc_the_icon( 'clock', 17 ); ?><?php echo esc_html( zc_course_total_duration( $zc_id ) ); ?></span>
							<span class="zc-course-hero__rating"><?php echo zc_stars( $zc_rating ?: 5 ); // phpcs:ignore ?></span>
						</div>
					</div>

					<div class="zc-course-hero__deco">
						<?php zc_the_icon( 'code', 200 ); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="zc-container">
			<div class="zc-course-layout">

				<!-- محتوای اصلی -->
				<div class="zc-course-main">

					<!-- تب‌های دوره -->
					<div class="zc-tabs zc-course-tabs">
						<div class="zc-tabs__nav" role="tablist">
							<button class="zc-tabs__btn is-active" data-tab="intro" role="tab"><?php zc_the_icon( 'info', 17 ); ?><?php esc_html_e( 'معرفی دوره', 'zarincode' ); ?></button>
							<?php if ( $zc_sections ) : ?>
								<button class="zc-tabs__btn" data-tab="curriculum" role="tab"><?php zc_the_icon( 'book', 17 ); ?><?php esc_html_e( 'سرفصل‌ها', 'zarincode' ); ?></button>
							<?php endif; ?>
							<button class="zc-tabs__btn" data-tab="teacher" role="tab"><?php zc_the_icon( 'award', 17 ); ?><?php esc_html_e( 'مدرس', 'zarincode' ); ?></button>
							<?php if ( zc_quiz_enabled() && zc_quiz_questions( $zc_id ) ) : ?>
								<button class="zc-tabs__btn" data-tab="quiz" role="tab"><?php zc_the_icon( 'certificate', 17 ); ?><?php esc_html_e( 'آزمون', 'zarincode' ); ?></button>
							<?php endif; ?>
							<?php if ( zc_quiz_module_enabled() && zc_course_practice_questions( $zc_id ) ) : ?>
								<button class="zc-tabs__btn" data-tab="course-practice" role="tab"><?php zc_the_icon( 'code', 17 ); ?><?php esc_html_e( 'تمرین', 'zarincode' ); ?></button>
							<?php endif; ?>
							<?php if ( comments_open() ) : ?>
								<button class="zc-tabs__btn" data-tab="comments" role="tab"><?php zc_the_icon( 'chat', 17 ); ?><?php esc_html_e( 'نظرات', 'zarincode' ); ?> (<?php echo esc_html( zc_fa_num( get_comments_number() ) ); ?>)</button>
							<?php endif; ?>
						</div>

						<!-- معرفی -->
						<div class="zc-tabs__pane is-active" data-pane="intro">
							<div class="zc-entry__content">
								<?php the_content(); ?>
							</div>

							<?php if ( $zc_features && is_array( $zc_features ) ) : ?>
								<div class="zc-course-box">
									<h3><?php zc_the_icon( 'check', 19 ); ?><?php esc_html_e( 'در این دوره چه می‌آموزید؟', 'zarincode' ); ?></h3>
									<ul class="zc-check-list">
										<?php foreach ( $zc_features as $zc_feature ) : ?>
											<li><?php zc_the_icon( 'check', 17 ); ?><span><?php echo esc_html( is_array( $zc_feature ) ? ( $zc_feature['text'] ?? '' ) : $zc_feature ); ?></span></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<div class="zc-grid zc-grid--2" style="margin-top:24px">
								<?php if ( $zc_prereq ) : ?>
									<div class="zc-course-box">
										<h3><?php zc_the_icon( 'info', 19 ); ?><?php esc_html_e( 'پیش‌نیازها', 'zarincode' ); ?></h3>
										<div class="zc-entry__content" style="font-size:.9rem"><?php echo wp_kses_post( wpautop( $zc_prereq ) ); ?></div>
									</div>
								<?php endif; ?>

								<?php if ( $zc_audience ) : ?>
									<div class="zc-course-box">
										<h3><?php zc_the_icon( 'users', 19 ); ?><?php esc_html_e( 'مخاطبان دوره', 'zarincode' ); ?></h3>
										<div class="zc-entry__content" style="font-size:.9rem"><?php echo wp_kses_post( wpautop( $zc_audience ) ); ?></div>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- سرفصل‌ها -->
						<?php if ( $zc_sections ) : ?>
						<div class="zc-tabs__pane" data-pane="curriculum">
							<div class="zc-curriculum">
								<div class="zc-curriculum__summary">
									<span><?php zc_the_icon( 'grid', 16 ); ?><?php echo esc_html( zc_fa_num( count( $zc_sections ) ) ); ?> <?php esc_html_e( 'فصل', 'zarincode' ); ?></span>
									<span><?php zc_the_icon( 'video', 16 ); ?><?php echo esc_html( zc_fa_num( $zc_lessons ) ); ?> <?php esc_html_e( 'جلسه', 'zarincode' ); ?></span>
									<span><?php zc_the_icon( 'clock', 16 ); ?><?php echo esc_html( zc_course_total_duration( $zc_id ) ); ?></span>
								</div>

								<div class="zc-accordion" data-single="no">
									<?php foreach ( $zc_sections as $zc_si => $zc_section ) : ?>
										<div class="zc-accordion__item<?php echo 0 === $zc_si ? ' is-open' : ''; ?>">
											<button class="zc-accordion__head" type="button" aria-expanded="<?php echo 0 === $zc_si ? 'true' : 'false'; ?>">
												<span style="display:flex;align-items:center;gap:11px;text-align:start">
													<span class="zc-curriculum__num"><?php echo esc_html( zc_fa_num( $zc_si + 1 ) ); ?></span>
													<span>
														<strong><?php echo esc_html( $zc_section['title'] ?? '' ); ?></strong>
														<small style="display:block;font-weight:400;color:var(--zc-muted);font-size:.78rem">
															<?php echo esc_html( zc_fa_num( count( $zc_section['lessons'] ?? array() ) ) ); ?> <?php esc_html_e( 'جلسه', 'zarincode' ); ?>
														</small>
													</span>
												</span>
												<span class="zc-accordion__icon"><?php zc_the_icon( 'chevron', 18 ); ?></span>
											</button>

											<div class="zc-accordion__body">
												<div class="zc-accordion__inner" style="padding:0 0 10px">
													<ul class="zc-lessons">
														<?php
														foreach ( (array) ( $zc_section['lessons'] ?? array() ) as $zc_li => $zc_lesson ) :
															$zc_key      = $zc_si . '-' . $zc_li;
															$zc_free     = ! empty( $zc_lesson['free'] );
															$zc_locked   = ! $zc_has && ! $zc_free;
															$zc_done     = $zc_has && zc_is_lesson_completed( get_current_user_id(), $zc_id, $zc_key );
															?>
															<li class="zc-lesson<?php echo $zc_locked ? ' is-locked' : ''; ?><?php echo $zc_done ? ' is-done' : ''; ?>">
																<span class="zc-lesson__icon">
																	<?php
																	if ( $zc_done ) {
																		zc_the_icon( 'check', 16 );
																	} elseif ( $zc_locked ) {
																		zc_the_icon( 'lock', 16 );
																	} else {
																		zc_the_icon( 'play', 14 );
																	}
																	?>
																</span>
																<span class="zc-lesson__title"><?php echo esc_html( $zc_lesson['title'] ?? '' ); ?></span>
																<?php if ( $zc_free && ! $zc_has ) : ?>
																	<span class="zc-badge zc-badge--green"><?php esc_html_e( 'رایگان', 'zarincode' ); ?></span>
																<?php endif; ?>
																<span class="zc-lesson__duration"><?php echo esc_html( zc_fa_num( $zc_lesson['duration'] ?? '' ) ); ?></span>
										<?php if ( ! $zc_locked ) : ?>
											<a class="zc-lesson__play" href="<?php echo esc_url( function_exists( 'zc_classroom_url' ) ? zc_classroom_url( $zc_id, $zc_key ) : get_permalink( $zc_id ) ); ?>"
												aria-label="<?php esc_attr_e( 'پخش در کلاس درس', 'zarincode' ); ?>">
												<?php zc_the_icon( 'play', 15 ); ?>
											</a>
										<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ul>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<!-- مدرس -->
						<div class="zc-tabs__pane" data-pane="teacher">
							<?php
							$zc_teacher_post = $zc_teacher ? zc_get_post_by_title( $zc_teacher, 'zc_teacher' ) : null;
							if ( $zc_teacher_post ) :
								?>
								<div class="zc-teacher-box">
									<div class="zc-avatar zc-avatar--xl"><?php echo zc_thumbnail( $zc_teacher_post->ID, 'zc-avatar' ); // phpcs:ignore ?></div>
									<div>
										<h3><?php echo esc_html( $zc_teacher_post->post_title ); ?></h3>
										<p style="color:var(--zc-gold-3);font-weight:600;font-size:.88rem;margin-bottom:10px">
											<?php echo esc_html( get_post_meta( $zc_teacher_post->ID, '_zc_teacher_role', true ) ); ?>
										</p>
										<div class="zc-entry__content" style="font-size:.9rem"><?php echo wp_kses_post( wpautop( $zc_teacher_post->post_content ) ); ?></div>
										<a href="<?php echo esc_url( get_permalink( $zc_teacher_post ) ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm" style="margin-top:12px">
											<?php esc_html_e( 'مشاهده پروفایل و دوره‌ها', 'zarincode' ); ?>
										</a>
									</div>
								</div>
							<?php else : ?>
								<p style="color:var(--zc-muted)"><?php esc_html_e( 'اطلاعات مدرس ثبت نشده است.', 'zarincode' ); ?></p>
							<?php endif; ?>
						</div>

					<!-- آزمون -->
					<?php if ( zc_quiz_enabled() && zc_quiz_questions( $zc_id ) ) : ?>
						<div class="zc-tabs__pane" data-pane="quiz">
							<?php zc_quiz_render( $zc_id ); ?>
						</div>
					<?php endif; ?>

					<?php if ( zc_quiz_module_enabled() && zc_course_practice_questions( $zc_id ) ) : ?>
						<div class="zc-tabs__pane" data-pane="course-practice">
							<?php zc_course_practice_render( $zc_id ); ?>
						</div>
					<?php endif; ?>

					<!-- نظرات -->
					<?php if ( comments_open() ) : ?>
					<div class="zc-tabs__pane" data-pane="comments">
						<?php
						try {
							comments_template();
						} catch ( \Throwable $zc_e ) {
							// نظرات خطا داد؛ بدنه‌ی دوره سالم می‌ماند.
							do_action( 'zc_single_section_error', 'comments', $zc_e );
						}
						?>
					</div>
					<?php endif; ?>
				</div>

				<?php
				zc_share_buttons();
				try {
					zc_related_posts( 3, 'zc_course' );
				} catch ( \Throwable $zc_e ) {
					// نوشته‌های مرتبط خطا داد؛ صفحه‌ی دوره سالم می‌ماند.
					do_action( 'zc_single_section_error', 'related', $zc_e );
				}
				?>
				</div>

				<!-- سایدبار چسبان خرید -->
				<aside class="zc-course-sidebar" data-zc-sticky="100">
					<div class="zc-course-buy">

						<div class="zc-course-buy__media">
							<?php echo zc_thumbnail( $zc_id, 'zc-card-lg' ); // phpcs:ignore ?>
							<?php if ( $zc_video ) : ?>
								<div class="zc-play-overlay" style="opacity:1;background:rgba(20,26,49,.3)">
									<button class="zc-play-btn zc-play-btn--pulse" data-zc-video="<?php echo esc_url( $zc_video ); ?>" aria-label="<?php esc_attr_e( 'پیش‌نمایش دوره', 'zarincode' ); ?>">
										<?php zc_the_icon( 'play', 24 ); ?>
									</button>
								</div>
								<span class="zc-course-buy__preview-label"><?php esc_html_e( 'پیش‌نمایش رایگان', 'zarincode' ); ?></span>
							<?php endif; ?>
						</div>

						<div class="zc-course-buy__body">

							<?php if ( $zc_has ) : ?>
								<div class="zc-course-buy__owned">
									<?php zc_the_icon( 'check', 22 ); ?>
									<div>
										<strong><?php esc_html_e( 'شما این دوره را دارید', 'zarincode' ); ?></strong>
										<small><?php echo esc_html( zc_fa_num( $zc_progress ) ); ?>٪ <?php esc_html_e( 'تکمیل شده', 'zarincode' ); ?></small>
									</div>
								</div>
								<div class="zc-progress" style="margin-bottom:16px"><div class="zc-progress__bar" data-value="<?php echo esc_attr( $zc_progress ); ?>"></div></div>
								<a href="<?php echo esc_url( function_exists( 'zc_classroom_url' ) ? zc_classroom_url( $zc_id ) : '#curriculum' ); ?>" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg">
									<?php zc_the_icon( 'play', 18 ); ?>
									<?php echo $zc_progress > 0 ? esc_html__( 'ادامه یادگیری', 'zarincode' ) : esc_html__( 'شروع دوره', 'zarincode' ); ?>
								</a>

							<?php else : ?>

								<div class="zc-course-buy__price">
									<?php if ( $zc_price <= 0 ) : ?>
										<span class="zc-price__free" style="font-size:1.6rem"><?php esc_html_e( 'رایگان', 'zarincode' ); ?></span>
									<?php elseif ( $zc_sale > 0 ) : ?>
										<del><?php echo esc_html( zc_fa_num( number_format( $zc_price ) ) ); ?></del>
										<strong><?php echo esc_html( zc_fa_num( number_format( $zc_sale ) ) ); ?> <small><?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></small></strong>
										<span class="zc-badge zc-badge--red">
											<?php echo esc_html( zc_fa_num( round( ( ( $zc_price - $zc_sale ) / $zc_price ) * 100 ) ) . '٪ ' . __( 'تخفیف', 'zarincode' ) ); ?>
										</span>
									<?php else : ?>
										<strong><?php echo esc_html( zc_fa_num( number_format( $zc_price ) ) ); ?> <small><?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></small></strong>
									<?php endif; ?>
								</div>

								<?php if ( $zc_price <= 0 ) : ?>
									<button class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg" data-zc-free-enroll="<?php echo esc_attr( $zc_id ); ?>">
										<?php zc_the_icon( 'gift', 18 ); ?><?php esc_html_e( 'ثبت‌نام رایگان', 'zarincode' ); ?>
									</button>
								<?php elseif ( $zc_product && zc_is_woo() ) : ?>
									<button class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg" data-zc-addcart="<?php echo esc_attr( $zc_product ); ?>">
										<?php zc_the_icon( 'cart', 18 ); ?><?php esc_html_e( 'افزودن به سبد خرید', 'zarincode' ); ?>
									</button>
									<a href="<?php echo esc_url( wc_get_checkout_url() . '?add-to-cart=' . $zc_product ); ?>" class="zc-btn zc-btn--dark zc-btn--block" style="margin-top:9px">
										<?php esc_html_e( 'خرید سریع', 'zarincode' ); ?>
									</a>
								<?php else : ?>
									<a href="<?php echo esc_url( zc_login_url() ); ?>" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg">
										<?php esc_html_e( 'ثبت‌نام در دوره', 'zarincode' ); ?>
									</a>
								<?php endif; ?>

							<?php endif; ?>

							<ul class="zc-course-buy__features">
								<li><?php zc_the_icon( 'video', 17 ); ?><span><?php echo esc_html( zc_fa_num( $zc_lessons ) ); ?> <?php esc_html_e( 'جلسه ویدیویی', 'zarincode' ); ?></span></li>
								<li><?php zc_the_icon( 'clock', 17 ); ?><span><?php echo esc_html( zc_course_total_duration( $zc_id ) ); ?> <?php esc_html_e( 'محتوای آموزشی', 'zarincode' ); ?></span></li>
								<li><?php zc_the_icon( 'refresh', 17 ); ?><span><?php esc_html_e( 'دسترسی مادام‌العمر', 'zarincode' ); ?></span></li>
								<li><?php zc_the_icon( 'certificate', 17 ); ?><span><?php esc_html_e( 'گواهی پایان دوره', 'zarincode' ); ?></span></li>
								<li><?php zc_the_icon( 'headphone', 17 ); ?><span><?php esc_html_e( 'پشتیبانی مستقیم مدرس', 'zarincode' ); ?></span></li>
								<li><?php zc_the_icon( 'shield', 17 ); ?><span><?php esc_html_e( '۷ روز ضمانت بازگشت وجه', 'zarincode' ); ?></span></li>
							</ul>

							<div class="zc-course-buy__share">
								<button class="zc-share__btn" data-net="copy" data-url="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'کپی لینک', 'zarincode' ); ?>">
									<?php zc_the_icon( 'code', 17 ); ?>
								</button>
								<button class="zc-share__btn<?php echo in_array( $zc_id, zc_get_wishlist(), true ) ? ' is-active' : ''; ?>"
									data-zc-wishlist="<?php echo esc_attr( $zc_id ); ?>" title="<?php esc_attr_e( 'علاقه‌مندی', 'zarincode' ); ?>">
									<?php zc_the_icon( 'heart', 17 ); ?>
								</button>
								<a href="https://t.me/share/url?url=<?php echo rawurlencode( get_permalink() ); ?>" class="zc-share__btn" data-net="telegram" target="_blank" rel="noopener">
									<?php echo zc_social_icon( 'telegram', 17 ); // phpcs:ignore ?>
								</a>
							</div>
						</div>
					</div>
				</aside>
			</div>
		</div>
	</article>

	<script>
	(function(){
		// ثبت‌نام رایگان
		document.querySelectorAll('[data-zc-free-enroll]').forEach(function(btn){
			btn.addEventListener('click',function(){
				if(!ZC.isLogged){window.location.href=ZC.loginUrl;return;}
				btn.classList.add('is-loading');
				window.zcAjax('zc_free_enroll',{course_id:btn.dataset.zcFreeEnroll}).then(function(res){
					btn.classList.remove('is-loading');
					window.zcToast(res.data.message,res.success?'success':'error');
					if(res.success)setTimeout(function(){location.reload();},900);
				});
			});
		});
		// پیشرفت فقط در کلاس درس پس از تماشای واقعی ثبت می‌شود.
	})();
	</script>

	<?php
endwhile;

get_footer();
