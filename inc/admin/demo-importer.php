<?php
/**
 * درون‌ریز محتوای دمو
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * افزودن صفحه درون‌ریزی دمو.
 *
 * @return void
 */
function zc_demo_menu() {
	add_submenu_page(
		'zarincode',
		__( 'درون‌ریزی دمو زرین کد', 'zarincode' ),
		__( 'درون‌ریزی دمو', 'zarincode' ),
		'manage_options',
		'zarincode-demo',
		'zc_demo_page'
	);
}
add_action( 'admin_menu', 'zc_demo_menu', 20 );

/**
 * صفحه درون‌ریزی.
 *
 * @return void
 */
function zc_demo_page() {
	$imported = get_option( 'zc_demo_imported', false );
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div>
				<h1><?php esc_html_e( 'درون‌ریزی محتوای دمو', 'zarincode' ); ?></h1>
				<p><?php esc_html_e( 'با یک کلیک، محتوای نمونه قالب زرین کد را وارد سایت کنید.', 'zarincode' ); ?></p>
			</div>
		</div>

		<?php if ( $imported ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'دمو قبلاً درون‌ریزی شده است. درون‌ریزی مجدد باعث ایجاد محتوای تکراری می‌شود.', 'zarincode' ); ?></p></div>
		<?php endif; ?>

		<div class="zc-demo-grid">
			<div class="zc-demo-card">
				<h2><?php esc_html_e( 'محتوای دمو شامل:', 'zarincode' ); ?></h2>
				<ul class="zc-demo-list">
					<li><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( '۱۰ دوره آموزشی کامل با سرفصل و جلسات', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-media-code"></span> <?php esc_html_e( '۱۰ آموزش رایگان', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-cart"></span> <?php esc_html_e( '۱۰ محصول عمومی فروشگاه', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( '۱۰ قالب وردپرس', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( '۱۰ افزونه وردپرس', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e( '۱۰ فونت فارسی', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-edit"></span> <?php esc_html_e( '۱۰ مقاله بلاگ', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-businessperson"></span> <?php esc_html_e( '۶ مدرس', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-format-quote"></span> <?php esc_html_e( '۶ نظر دانشجو', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( '۸ سوال متداول', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'صفحات: خانه، درباره ما، تماس با ما، پنل، ورود', 'zarincode' ); ?></li>
					<li><span class="dashicons dashicons-menu"></span> <?php esc_html_e( 'منوها، دسته‌بندی‌ها و تنظیمات قالب', 'zarincode' ); ?></li>
				</ul>
			</div>

			<div class="zc-demo-card">
				<h2><?php esc_html_e( 'شروع درون‌ریزی', 'zarincode' ); ?></h2>

				<p class="description" style="margin-bottom:16px">
					<?php esc_html_e( 'توصیه می‌شود قبل از درون‌ریزی، از سایت خود پشتیبان بگیرید. این عملیات ممکن است تا ۲ دقیقه طول بکشد.', 'zarincode' ); ?>
				</p>

				<div class="zc-demo-options">
					<label><input type="checkbox" id="zc-demo-content" checked> <?php esc_html_e( 'محتوا (دوره، محصول، مقاله)', 'zarincode' ); ?></label>
					<label><input type="checkbox" id="zc-demo-pages" checked> <?php esc_html_e( 'صفحات و منوها', 'zarincode' ); ?></label>
					<label><input type="checkbox" id="zc-demo-settings" checked> <?php esc_html_e( 'تنظیمات قالب', 'zarincode' ); ?></label>
					<label><input type="checkbox" id="zc-demo-widgets" checked> <?php esc_html_e( 'ویجت‌های سایدبار', 'zarincode' ); ?></label>
				</div>

				<div class="zc-demo-progress" style="display:none">
					<div class="zc-demo-progress__bar"><span></span></div>
					<p class="zc-demo-progress__text"></p>
				</div>

				<button type="button" class="button button-primary button-hero" id="zc-import-demo">
					<span class="dashicons dashicons-download" style="margin-top:4px"></span>
					<?php esc_html_e( 'شروع درون‌ریزی دمو', 'zarincode' ); ?>
				</button>

				<div id="zc-demo-result" style="margin-top:16px"></div>

				<hr style="margin:24px 0">

				<h3><?php esc_html_e( 'حذف محتوای دمو', 'zarincode' ); ?></h3>
				<p class="description"><?php esc_html_e( 'تمام محتوای وارد شده توسط دمو حذف خواهد شد.', 'zarincode' ); ?></p>
				<button type="button" class="button" id="zc-remove-demo" style="color:#b32d2e;border-color:#b32d2e">
					<?php esc_html_e( 'حذف محتوای دمو', 'zarincode' ); ?>
				</button>
			</div>
		</div>
	</div>

	<script>
	jQuery(function($){
		var steps=['content','pages','settings','widgets'];
		var labels={content:'در حال ساخت محتوا…',pages:'در حال ساخت صفحات و منوها…',settings:'در حال اعمال تنظیمات…',widgets:'در حال ساخت ویجت‌ها…'};

		$('#zc-import-demo').on('click',function(){
			var btn=$(this);
			var selected=steps.filter(function(s){return $('#zc-demo-'+s).is(':checked');});
			if(!selected.length){alert('حداقل یک گزینه را انتخاب کنید.');return;}

			btn.prop('disabled',true);
			$('.zc-demo-progress').show();
			$('#zc-demo-result').html('');

			var index=0;
			function runStep(){
				if(index>=selected.length){
					$('.zc-demo-progress__bar span').css('width','100%');
					$('.zc-demo-progress__text').text('تکمیل شد!');
					$('#zc-demo-result').html('<div class="notice notice-success inline"><p>✅ درون‌ریزی با موفقیت انجام شد. <a href="'+location.origin+'" target="_blank">مشاهده سایت</a></p></div>');
					btn.prop('disabled',false);
					return;
				}
				var step=selected[index];
				$('.zc-demo-progress__text').text(labels[step]);
				$('.zc-demo-progress__bar span').css('width',((index/selected.length)*100)+'%');

				$.post(ZCAdmin.ajaxUrl,{action:'zc_import_demo',nonce:ZCAdmin.nonce,step:step},function(res){
					if(res.success){index++;runStep();}
					else{
						$('#zc-demo-result').html('<div class="notice notice-error inline"><p>'+(res.data.message||'خطا')+'</p></div>');
						btn.prop('disabled',false);
					}
				}).fail(function(){
					$('#zc-demo-result').html('<div class="notice notice-error inline"><p>خطا در ارتباط با سرور.</p></div>');
					btn.prop('disabled',false);
				});
			}
			runStep();
		});

		$('#zc-remove-demo').on('click',function(){
			if(!confirm('آیا از حذف تمام محتوای دمو مطمئن هستید؟'))return;
			var btn=$(this).prop('disabled',true);
			$.post(ZCAdmin.ajaxUrl,{action:'zc_remove_demo',nonce:ZCAdmin.nonce},function(res){
				btn.prop('disabled',false);
				$('#zc-demo-result').html('<div class="notice notice-'+(res.success?'success':'error')+' inline"><p>'+res.data.message+'</p></div>');
			});
		});
	});
	</script>
	<?php
}

/**
 * هندلر ای‌جکس درون‌ریزی.
 *
 * @return void
 */
function zc_ajax_import_demo() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	/*
	 * درون‌ریزی دمو محتوای زیادی می‌سازد؛ حافظه و زمان اجرا را بالا
	 * می‌بریم تا روی سرورهای اشتراکی هم بدون خطا تمام شود.
	 */
	@ini_set( 'memory_limit', '512M' ); // phpcs:ignore
	@set_time_limit( 300 ); // phpcs:ignore

	$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';

	/*
	 * برخی افزونه‌ها (مثل المنتور روی PHP 8.4) هنگام ساخت محتوا
	 * هشدار/اعلان چاپ می‌کنند. اگر این خروجی پیش از پاسخ JSON باشد،
	 * پاسخ خراب می‌شود و مرورگر پیام «خطا در ارتباط با سرور» می‌دهد.
	 * خروجی را بافر می‌کنیم و پیش از ارسال JSON پاک می‌کنیم.
	 */
	ob_start();

	try {
		require_once ZC_DIR . 'demo/demo-data.php';
		require_once ZC_DIR . 'demo/demo-contracts.php';

		switch ( $step ) {
			case 'content':
				zc_import_demo_content();

				if ( function_exists( 'zc_import_demo_announcements' ) ) {
					zc_import_demo_announcements();
				}

				// الگوهای قرارداد آماده.
				if ( function_exists( 'zc_import_demo_contracts' ) ) {
					zc_import_demo_contracts();
				}

				// پلن‌های اشتراک نمونه.
				if ( function_exists( 'zc_install_demo_subscriptions' ) ) {
					zc_install_demo_subscriptions();
				}

				// تمرین‌های کدنویسی نمونه.
				if ( function_exists( 'zc_install_demo_practices' ) ) {
					zc_install_demo_practices();
				}

				// دوره‌های آموزشی جامع (۲۱ دوره رایگان برنامه‌نویسی).
				if ( file_exists( ZC_DIR . 'demo/demo-tech-courses.php' ) ) {
					require_once ZC_DIR . 'demo/demo-tech-courses.php';
					if ( function_exists( 'zc_install_tech_courses' ) ) {
						zc_install_tech_courses();
					}
				}
				break;
			case 'pages':
				/*
				 * برگه‌های حقوقی باید پیش از ساخت منو ایجاد شوند، وگرنه
				 * آیتم‌های «گارانتی» و «بازگشت وجه» نشانی درستی نمی‌گیرند.
				 */
				if ( zc_is_elementor() ) {
					require_once ZC_DIR . 'demo/demo-legal-pages.php';
					zc_install_demo_legal_pages();

					/*
					 * صفحات اختصاصی فرم جستجو و درخواست پروژه/مشاوره پیش از
					 * ساخت منو نصب می‌شوند تا بتوان در منو به آن‌ها لینک داد.
					 */
					require_once ZC_DIR . 'demo/demo-subpages.php';
					zc_install_demo_shop_page();
					zc_install_demo_request_page();
				}

				zc_import_demo_pages();

				if ( zc_is_elementor() ) {
					require_once ZC_DIR . 'demo/homepage-elementor.php';
					zc_install_demo_homepage();

					// بازسازی دیزاین جذاب صفحات تماس و درخواست پروژه.
					require_once ZC_DIR . 'demo/demo-pages-builder.php';
					zc_install_demo_pages_builder();
				}
				break;
			case 'settings':
				zc_import_demo_settings();
				break;
			case 'widgets':
				zc_import_demo_widgets();
				break;
			default:
				ob_end_clean();
				wp_send_json_error( array( 'message' => __( 'مرحله نامعتبر.', 'zarincode' ) ) );
		}

		update_option( 'zc_demo_imported', true );

		// خروجیِ هشدارها/اعلان‌ها را دور می‌ریزیم تا JSON سالم بماند.
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		wp_send_json_success( array( 'message' => __( 'انجام شد.', 'zarincode' ), 'step' => $step ) );
	} catch ( \Throwable $e ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		/*
		 * خطای دقیق به همراه مرحله و محل وقوع را برمی‌گردانیم تا کاربر
		 * بتواند مشکل را گزارش دهد. پیام خام «Invalid post.» بدون بافت،
		 * به‌تنهایی تشخیص مشکل را ناممکن می‌کند.
		 */
		$zc_err_msg = $e->getMessage();

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$zc_err_msg .= ' [' . basename( $e->getFile() ) . ':' . $e->getLine() . ']';
		}

		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: 1: step 2: message */
					__( 'خطا در درون‌ریزی (مرحله‌ی %1$s): %2$s', 'zarincode' ),
					$step,
					$zc_err_msg
				),
			)
		);
	}
}
add_action( 'wp_ajax_zc_import_demo', 'zc_ajax_import_demo' );

/**
 * حذف محتوای دمو.
 *
 * @return void
 */
function zc_ajax_remove_demo() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$types = array( 'zc_course', 'zc_tutorial', 'zc_teacher', 'zc_testimonial', 'zc_faq', 'zc_service', 'zc_contract_tpl', 'post', 'product', 'page' );
	$count = 0;

	foreach ( $types as $type ) {
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'meta_key'       => '_zc_demo', // phpcs:ignore
				'meta_value'     => '1', // phpcs:ignore
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
			$count++;
		}
	}

	delete_option( 'zc_demo_imported' );

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: count */
				__( '%s مورد حذف شد.', 'zarincode' ),
				zc_fa_num( $count )
			),
		)
	);
}
add_action( 'wp_ajax_zc_remove_demo', 'zc_ajax_remove_demo' );
