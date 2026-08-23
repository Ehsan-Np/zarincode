<?php
/**
 * تنظیمات و بهبودهای پیشخوان
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * افزودن منوی یکپارچه‌ی زرین کد در پیشخوان.
 *
 * همه‌ی صفحه‌های قالب زیر یک منوی واحد جمع می‌شوند. پیش‌تر دو ورودی
 * جداگانه وجود داشت («زرین کد» و «تنظیمات زرین کد» زیر نمایش) که
 * کاربر را سردرگم می‌کرد؛ اکنون تنظیمات ردوکس و درون‌ریزی دمو هر دو
 * زیرمنوی همین منو هستند.
 *
 * نکته: زیرمنوی نخست باید همان اسلاگ منوی اصلی را داشته باشد تا با
 * کلیک روی عنوان «زرین کد»، داشبورد باز شود و وردپرس ورودی تکراری
 * نسازد.
 *
 * @return void
 */
function zc_admin_menu() {
	add_menu_page(
		__( 'زرین کد', 'zarincode' ),
		__( 'زرین کد', 'zarincode' ),
		'manage_options',
		'zarincode',
		'zc_admin_dashboard_page',
		'dashicons-star-filled',
		2
	);

	add_submenu_page( 'zarincode', __( 'داشبورد', 'zarincode' ), __( 'داشبورد', 'zarincode' ), 'manage_options', 'zarincode', 'zc_admin_dashboard_page' );

	/*
	 * اگر ردوکس نصب نباشد، پنل فالبک با همین اسلاگ ثبت می‌شود؛
	 * بنابراین اینجا چیزی اضافه نمی‌کنیم و آن فایل خودش زیرمنو را
	 * می‌سازد. در حالت ردوکس هم خود ردوکس زیرمنو را اضافه می‌کند.
	 */

	add_submenu_page( 'zarincode', __( 'گزارش فروش', 'zarincode' ), __( 'گزارش فروش', 'zarincode' ), 'manage_options', 'zarincode-sales', 'zc_admin_sales_page' );
	add_submenu_page( 'zarincode', __( 'گزارش مالی', 'zarincode' ), __( 'گزارش مالی', 'zarincode' ), 'manage_options', 'zarincode-accounting', 'zc_admin_accounting_page' );
	add_submenu_page( 'zarincode', __( 'ارسال گروهی', 'zarincode' ), __( 'ارسال گروهی', 'zarincode' ), 'manage_options', 'zarincode-broadcast', 'zc_admin_broadcast_page' );
	add_submenu_page( 'zarincode', __( 'ربات و کران', 'zarincode' ), __( 'ربات و کران', 'zarincode' ), 'manage_options', 'zarincode-bot', 'zc_admin_bot_page' );
}
add_action( 'admin_menu', 'zc_admin_menu' );

/**
 * مرتب‌سازی زیرمنوهای زرین کد.
 *
 * ردوکس و صفحه‌های دیگر با اولویت‌های متفاوت ثبت می‌شوند و ترتیب
 * نهایی نامرتب می‌شود. این تابع ترتیب منطقی و ثابتی اعمال می‌کند.
 *
 * @return void
 */
function zc_sort_admin_submenu() {
	global $submenu;

	if ( empty( $submenu['zarincode'] ) ) {
		return;
	}

	$order = array(
		'zarincode'            => 10, // داشبورد.
		'zarincode-options'    => 20, // تنظیمات قالب (ردوکس یا فالبک).
		'zarincode-plugins'    => 25, // مدیریت افزونه‌ها (شبیه TGM).
		'zarincode-demo'       => 30, // درون‌ریزی دمو.
		'zarincode-accounting' => 40,
		'zarincode-sales'      => 45,
		'zarincode-kpi'        => 46,
		'zarincode-growth'     => 47,
		'zarincode-credentials' => 48,
		'zarincode-subscriptions' => 49,
		'zarincode-checkout-fields' => 50,
		'zarincode-chats'      => 50,
		'zarincode-sms'      => 60,
		'zarincode-code'     => 65,
		'zarincode-quiz-report' => 66,
		'zarincode-contacts'   => 70,
		'zc-newsletter'        => 75,
		'zarincode-broadcast'  => 80,
		'zarincode-bot'        => 90,
	);

	usort(
		$submenu['zarincode'],
		function ( $a, $b ) use ( $order ) {
			$wa = $order[ $a[2] ] ?? 999;
			$wb = $order[ $b[2] ] ?? 999;

			return $wa <=> $wb;
		}
	);
}
add_action( 'admin_menu', 'zc_sort_admin_submenu', 999 );

/**
 * حذف ورودی‌های تکراری قالب از منوی «نمایش».
 *
 * برخی صفحه‌ها پیش‌تر با add_theme_page ثبت می‌شدند. برای سازگاری
 * با نصب‌های قدیمی، اگر چنین ورودی‌هایی باقی مانده باشند حذف می‌شوند.
 *
 * @return void
 */
function zc_cleanup_theme_submenu() {
	remove_submenu_page( 'themes.php', 'zarincode-options' );
	remove_submenu_page( 'themes.php', 'zarincode-demo' );
}
add_action( 'admin_menu', 'zc_cleanup_theme_submenu', 998 );

/**
 * چاپ جای‌گاه پیام‌های مدیریتی پیش از سربرگ تیره.
 *
 * وردپرس با اسکریپت common.js همه‌ی .notice ها را به بالای نخستین
 * عنوان صفحه منتقل می‌کند. اگر نخستین عنوان داخل سربرگ سرمه‌ای قالب
 * باشد، پیام روی پس‌زمینه‌ی تیره می‌افتد و ناخوانا می‌شود. با قراردادن
 * یک عنوان پنهان و ظرف مخصوص در ابتدای صفحه، وردپرس پیام‌ها را همان‌جا
 * می‌گذارد و ظاهر درست می‌ماند.
 *
 * @return void
 */
function zc_admin_notice_anchor() {
	echo '<h1 class="screen-reader-text zc-admin-anchor"></h1>';
	echo '<div class="zc-admin-notices"></div>';
}

/**
 * صفحه داشبورد قالب.
 *
 * @return void
 */
function zc_admin_dashboard_page() {
	$stats   = zc_site_stats();
	$summary = zc_accounting_summary();
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div>
				<h1><?php esc_html_e( 'داشبورد قالب زرین کد', 'zarincode' ); ?></h1>
				<p><?php printf( esc_html__( 'نسخه %s — خلاصه وضعیت سایت شما', 'zarincode' ), esc_html( ZC_VERSION ) ); ?></p>
			</div>
			<div class="zc-admin-header__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-options' ) ); ?>" class="button button-primary"><?php esc_html_e( 'تنظیمات قالب', 'zarincode' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-demo' ) ); ?>" class="button"><?php esc_html_e( 'درون‌ریزی دمو', 'zarincode' ); ?></a>
			</div>
		</div>

		<div class="zc-admin-stats">
			<?php
			$cards = array(
				array( 'welcome-learn-more', __( 'دوره آموزشی', 'zarincode' ), $stats['courses'], '#C9A227' ),
				array( 'cart', __( 'محصول فروشگاه', 'zarincode' ), $stats['products'], '#2563EB' ),
				array( 'admin-users', __( 'کاربر ثبت‌نام شده', 'zarincode' ), $stats['students'], '#16A34A' ),
				array( 'tickets-alt', __( 'تیکت پشتیبانی', 'zarincode' ), $stats['tickets'], '#F59E0B' ),
				array( 'media-code', __( 'آموزش رایگان', 'zarincode' ), $stats['tutorials'], '#7C3AED' ),
				array( 'edit', __( 'مقاله بلاگ', 'zarincode' ), $stats['posts'], '#DB2777' ),
			);
			foreach ( $cards as $card ) :
				?>
				<div class="zc-admin-stat">
					<span class="zc-admin-stat__icon" style="background:<?php echo esc_attr( $card[3] ); ?>1a;color:<?php echo esc_attr( $card[3] ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $card[0] ); ?>"></span>
					</span>
					<div>
						<strong><?php echo esc_html( zc_fa_num( number_format( $card[2] ) ) ); ?></strong>
						<span><?php echo esc_html( $card[1] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="zc-admin-grid-2">
			<div class="zc-admin-box">
				<h2><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'خلاصه مالی این ماه', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<tr><td><?php esc_html_e( 'درآمد کل', 'zarincode' ); ?></td><td><strong style="color:#16A34A"><?php echo esc_html( zc_fa_num( number_format( $summary['income'] ) ) ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'برداشت‌ها', 'zarincode' ); ?></td><td><strong style="color:#DC2626"><?php echo esc_html( zc_fa_num( number_format( $summary['expense'] ) ) ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'سود خالص', 'zarincode' ); ?></td><td><strong><?php echo esc_html( zc_fa_num( number_format( $summary['profit'] ) ) ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'تعداد سفارش', 'zarincode' ); ?></td><td><strong><?php echo esc_html( zc_fa_num( $summary['orders_count'] ) ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'مبلغ سفارش‌ها', 'zarincode' ); ?></td><td><strong><?php echo esc_html( zc_fa_num( number_format( $summary['orders_total'] ) ) ); ?></strong></td></tr>
				</table>
				<p style="margin-top:12px">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-accounting' ) ); ?>" class="button"><?php esc_html_e( 'گزارش کامل', 'zarincode' ); ?></a>
				</p>
			</div>

			<div class="zc-admin-box">
				<h2><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'وضعیت سیستم', 'zarincode' ); ?></h2>
				<?php echo zc_system_status_html(); // phpcs:ignore ?>
			</div>
		</div>

		<div class="zc-admin-box">
			<h2><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'شروع سریع', 'zarincode' ); ?></h2>
			<div class="zc-admin-quick">
				<?php
				$quick = array(
					array( 'admin-appearance', __( 'تنظیم رنگ و لوگو', 'zarincode' ), admin_url( 'admin.php?page=zarincode-options&section=styling' ) ),
					array( 'email-alt', __( 'پیکربندی پیامک', 'zarincode' ), admin_url( 'admin.php?page=zarincode-options&section=sms' ) ),
					array( 'money-alt', __( 'تنظیم درگاه پرداخت', 'zarincode' ), admin_url( 'admin.php?page=zarincode-options&section=payment' ) ),
					array( 'welcome-add-page', __( 'افزودن دوره جدید', 'zarincode' ), admin_url( 'post-new.php?post_type=zc_course' ) ),
					array( 'menu', __( 'مدیریت منوها', 'zarincode' ), admin_url( 'nav-menus.php' ) ),
					array( 'download', __( 'درون‌ریزی دمو', 'zarincode' ), admin_url( 'admin.php?page=zarincode-demo' ) ),
				);
				foreach ( $quick as $q ) :
					?>
					<a href="<?php echo esc_url( $q[2] ); ?>" class="zc-admin-quick__item">
						<span class="dashicons dashicons-<?php echo esc_attr( $q[0] ); ?>"></span>
						<span><?php echo esc_html( $q[1] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php
	/**
	 * هوک انتهای داشبورد — برای افزودن بخش‌های تکمیلی (مثل بکاپ).
	 */
	do_action( 'zc_admin_dashboard_after_stats' );
	?>
	<?php
}

/**
 * صفحه گزارش مالی.
 *
 * @return void
 */
function zc_admin_accounting_page() {
	global $wpdb;

	$from    = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : gmdate( 'Y-m-01' ); // phpcs:ignore
	$to      = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : gmdate( 'Y-m-d' ); // phpcs:ignore
	$summary = zc_accounting_summary( $from . ' 00:00:00', $to . ' 23:59:59' );

	$table = $wpdb->prefix . 'zc_transactions';
	$rows  = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( "SELECT * FROM {$table} WHERE created_at BETWEEN %s AND %s ORDER BY id DESC LIMIT 200", $from . ' 00:00:00', $to . ' 23:59:59' )
	);
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div><h1><?php esc_html_e( 'گزارش مالی و حسابداری', 'zarincode' ); ?></h1></div>
			<div class="zc-admin-header__actions">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=zarincode-accounting&zc_export=accounting' ), 'zc_export_accounting' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'خروجی اکسل (CSV)', 'zarincode' ); ?>
				</a>
			</div>
		</div>

		<form method="get" class="zc-admin-box" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
			<input type="hidden" name="page" value="zarincode-accounting">
			<label><?php esc_html_e( 'از تاریخ', 'zarincode' ); ?><br><input type="date" name="from" value="<?php echo esc_attr( $from ); ?>"></label>
			<label><?php esc_html_e( 'تا تاریخ', 'zarincode' ); ?><br><input type="date" name="to" value="<?php echo esc_attr( $to ); ?>"></label>
			<button class="button button-primary"><?php esc_html_e( 'اعمال فیلتر', 'zarincode' ); ?></button>
		</form>

		<div class="zc-admin-stats">
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#16A34A1a;color:#16A34A"><span class="dashicons dashicons-arrow-down-alt"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['income'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#DC26261a;color:#DC2626"><span class="dashicons dashicons-arrow-up-alt"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['expense'] ) ) ); ?></strong><span><?php esc_html_e( 'هزینه', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#C9A2271a;color:#C9A227"><span class="dashicons dashicons-chart-line"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['profit'] ) ) ); ?></strong><span><?php esc_html_e( 'سود خالص', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#2563EB1a;color:#2563EB"><span class="dashicons dashicons-cart"></span></span><div><strong><?php echo esc_html( zc_fa_num( $summary['orders_count'] ) ); ?></strong><span><?php esc_html_e( 'سفارش', 'zarincode' ); ?></span></div></div>
		</div>

		<div class="zc-admin-box">
			<h2><?php esc_html_e( 'لیست تراکنش‌ها', 'zarincode' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'کاربر', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'مبلغ', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'شرح', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'کد پیگیری', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $rows ) : ?>
						<?php foreach ( $rows as $row ) : ?>
							<?php $user = get_user_by( 'id', $row->user_id ); ?>
							<tr>
								<td><?php echo esc_html( $row->id ); ?></td>
								<td><?php echo esc_html( $user ? $user->display_name : '—' ); ?></td>
								<td style="color:<?php echo $row->amount >= 0 ? '#16A34A' : '#DC2626'; ?>"><strong><?php echo esc_html( number_format( $row->amount ) ); ?></strong></td>
								<td><?php echo esc_html( $row->type ); ?></td>
								<td><?php echo esc_html( $row->description ); ?></td>
								<td><?php echo esc_html( $row->ref_id ); ?></td>
								<td><?php echo esc_html( $row->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="7" style="text-align:center;padding:24px"><?php esc_html_e( 'تراکنشی در این بازه یافت نشد.', 'zarincode' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}

/**
 * صفحه ارسال گروهی.
 *
 * @return void
 */
function zc_admin_broadcast_page() {
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div>
				<h1><?php esc_html_e( 'ارسال گروهی پیام', 'zarincode' ); ?></h1>
				<p><?php esc_html_e( 'ارسال تبلیغات و اطلاعیه به کانال تلگرام، بله و پیامک', 'zarincode' ); ?></p>
			</div>
		</div>

		<div class="zc-admin-grid-2">
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'ارسال پیام', 'zarincode' ); ?></h2>
				<p>
					<label><strong><?php esc_html_e( 'متن پیام', 'zarincode' ); ?></strong></label>
					<textarea id="zc-broadcast-message" rows="8" style="width:100%" placeholder="<?php esc_attr_e( 'متن پیام خود را بنویسید… (پشتیبانی از HTML ساده در تلگرام)', 'zarincode' ); ?>"></textarea>
				</p>
				<p>
					<label><strong><?php esc_html_e( 'آدرس تصویر (اختیاری)', 'zarincode' ); ?></strong></label>
					<input type="url" id="zc-broadcast-image" style="width:100%" placeholder="https://">
				</p>
				<p>
					<strong><?php esc_html_e( 'کانال‌های ارسال:', 'zarincode' ); ?></strong><br>
					<label><input type="checkbox" class="zc-channel" value="telegram" checked> <?php esc_html_e( 'تلگرام', 'zarincode' ); ?></label>
					<label style="margin-inline-start:14px"><input type="checkbox" class="zc-channel" value="bale"> <?php esc_html_e( 'بله', 'zarincode' ); ?></label>
					<label style="margin-inline-start:14px"><input type="checkbox" class="zc-channel" value="sms"> <?php esc_html_e( 'پیامک (اعضای خبرنامه)', 'zarincode' ); ?></label>
				</p>
				<button class="button button-primary button-large" id="zc-send-broadcast"><?php esc_html_e( 'ارسال پیام', 'zarincode' ); ?></button>
				<div id="zc-broadcast-result" style="margin-top:14px"></div>
			</div>

			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'تست اتصال', 'zarincode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'قبل از ارسال گروهی، اتصال سرویس‌ها را بررسی کنید.', 'zarincode' ); ?></p>
				<p><button class="button zc-test-messenger" data-channel="telegram"><?php esc_html_e( 'تست تلگرام', 'zarincode' ); ?></button></p>
				<p><button class="button zc-test-messenger" data-channel="bale"><?php esc_html_e( 'تست بله', 'zarincode' ); ?></button></p>
				<p>
					<input type="tel" id="zc-test-mobile" placeholder="09xxxxxxxxx" style="width:160px">
					<button class="button zc-test-messenger" data-channel="sms"><?php esc_html_e( 'تست پیامک', 'zarincode' ); ?></button>
				</p>
				<div id="zc-test-result" style="margin-top:12px"></div>
			</div>
		</div>
	</div>

	<script>
	jQuery(function($){
		$('#zc-send-broadcast').on('click',function(){
			var btn=$(this).prop('disabled',true);
			var channels=$('.zc-channel:checked').map(function(){return this.value;}).get();
			$.post(ZCAdmin.ajaxUrl,{
				action:'zc_messenger_broadcast',nonce:ZCAdmin.nonce,
				message:$('#zc-broadcast-message').val(),
				image:$('#zc-broadcast-image').val(),
				channels:channels
			},function(res){
				btn.prop('disabled',false);
				var html='<div class="notice notice-'+(res.success?'success':'error')+' inline"><p>'+res.data.message+'</p>';
				if(res.data.results){html+='<ul>';$.each(res.data.results,function(k,v){html+='<li><b>'+k+'</b>: '+v+'</li>';});html+='</ul>';}
				html+='</div>';
				$('#zc-broadcast-result').html(html);
			});
		});

		$('.zc-test-messenger').on('click',function(){
			var btn=$(this).prop('disabled',true);
			$.post(ZCAdmin.ajaxUrl,{
				action:'zc_test_messenger',nonce:ZCAdmin.nonce,
				channel:btn.data('channel'),mobile:$('#zc-test-mobile').val()
			},function(res){
				btn.prop('disabled',false);
				$('#zc-test-result').html('<div class="notice notice-'+(res.success?'success':'error')+' inline"><p>'+res.data.message+'</p></div>');
			});
		});
	});
	</script>
	<?php
}

/**
 * افزودن لینک تنظیمات به نوار مدیریت.
 *
 * @param WP_Admin_Bar $bar نوار.
 * @return void
 */
function zc_admin_bar_link( $bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$bar->add_node(
		array(
			'id'    => 'zarincode',
			'title' => '⭐ ' . __( 'زرین کد', 'zarincode' ),
			'href'  => admin_url( 'admin.php?page=zarincode' ),
		)
	);

	$bar->add_node(
		array(
			'parent' => 'zarincode',
			'id'     => 'zc-options',
			'title'  => __( 'تنظیمات قالب', 'zarincode' ),
			'href'   => admin_url( 'admin.php?page=zarincode-options' ),
		)
	);

	$bar->add_node(
		array(
			'parent' => 'zarincode',
			'id'     => 'zc-clear-cache',
			'title'  => __( 'پاکسازی کش قالب', 'zarincode' ),
			'href'   => wp_nonce_url( admin_url( 'admin.php?page=zarincode&zc_action=clear_cache' ), 'zc_clear_cache' ),
		)
	);
}
add_action( 'admin_bar_menu', 'zc_admin_bar_link', 90 );

/**
 * اجرای اکشن‌های ادمین.
 *
 * @return void
 */
function zc_handle_admin_actions() {
	if ( ! isset( $_GET['zc_action'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore
		return;
	}

	$action = sanitize_key( wp_unslash( $_GET['zc_action'] ) ); // phpcs:ignore

	if ( 'clear_cache' === $action && check_admin_referer( 'zc_clear_cache' ) ) {
		zc_clear_cache();
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'کش قالب با موفقیت پاک شد.', 'zarincode' ) . '</p></div>';
			}
		);
	}
}
add_action( 'admin_init', 'zc_handle_admin_actions' );

/**
 * فوتر پیشخوان.
 *
 * @param string $text متن.
 * @return string
 */
function zc_admin_footer_text( $text ) {
	$screen = get_current_screen();
	if ( $screen && false !== strpos( $screen->id, 'zarincode' ) ) {
		return sprintf(
			/* translators: %s: version */
			__( 'قدرت گرفته از <strong>قالب زرین کد</strong> نسخه %s', 'zarincode' ),
			ZC_VERSION
		);
	}
	return $text;
}
add_filter( 'admin_footer_text', 'zc_admin_footer_text' );

/**
 * صفحه راه‌اندازی ربات و کران.
 *
 * آدرس‌های وب‌هوک و کران را نمایش می‌دهد و امکان ثبت خودکار
 * وب‌هوک و اجرای دستی صف را فراهم می‌کند.
 *
 * @return void
 */
function zc_admin_bot_page() {
	$options = get_option( ZC_PREFIX, array() );

	// تولید کلیدها در اولین بازدید.
	$changed = false;

	if ( empty( $options['zc_bot_secret'] ) ) {
		$options['zc_bot_secret'] = wp_generate_password( 20, false, false );
		$changed                  = true;
	}

	if ( empty( $options['zc_cron_key'] ) ) {
		$options['zc_cron_key'] = wp_generate_password( 24, false, false );
		$changed                = true;
	}

	if ( $changed ) {
		update_option( ZC_PREFIX, $options );
	}

	$cron_url = home_url( '/?zc_cron=' . $options['zc_cron_key'] );
	$queue    = get_option( 'zc_notify_queue', array() );
	$last     = (int) get_option( 'zc_notify_last_run', 0 );

	// شمار کاربران متصل.
	$connected = count(
		get_users(
			array(
				'fields'     => 'ID',
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array( 'key' => 'zc_telegram_chat_id', 'compare' => 'EXISTS' ),
					array( 'key' => 'zc_bale_chat_id', 'compare' => 'EXISTS' ),
				),
			)
		)
	);
	?>
	<div class="wrap zc-admin-wrap zc-admin">
		<?php zc_admin_notice_anchor(); ?>
		<h1><?php esc_html_e( 'راه‌اندازی ربات و کران', 'zarincode' ); ?></h1>

		<div class="zc-admin-cards">
			<div class="zc-admin-card">
				<span class="zc-admin-card__num"><?php echo esc_html( zc_fa_num( $connected ) ); ?></span>
				<span class="zc-admin-card__label"><?php esc_html_e( 'کاربر متصل به ربات', 'zarincode' ); ?></span>
			</div>
			<div class="zc-admin-card">
				<span class="zc-admin-card__num"><?php echo esc_html( zc_fa_num( count( $queue ) ) ); ?></span>
				<span class="zc-admin-card__label"><?php esc_html_e( 'اعلان در صف', 'zarincode' ); ?></span>
			</div>
			<div class="zc-admin-card">
				<span class="zc-admin-card__num">
					<?php echo $last ? esc_html( human_time_diff( $last ) ) : '—'; ?>
				</span>
				<span class="zc-admin-card__label"><?php esc_html_e( 'آخرین اجرای کران', 'zarincode' ); ?></span>
			</div>
		</div>

		<h2><?php esc_html_e( 'آدرس وب‌هوک ربات‌ها', 'zarincode' ); ?></h2>
		<p class="description"><?php esc_html_e( 'این آدرس‌ها را نزد تلگرام/بله ثبت کنید تا پیام‌های ربات به سایت برسد.', 'zarincode' ); ?></p>

		<table class="widefat striped" style="max-width:900px">
			<tbody>
				<?php foreach ( zc_messengers() as $key => $m ) : ?>
					<tr>
						<td style="width:100px"><strong><?php echo esc_html( $m['label'] ); ?></strong></td>
						<td>
							<code style="direction:ltr;display:inline-block;word-break:break-all">
								<?php echo esc_html( zc_bot_webhook_url( $key ) ); ?>
							</code>
						</td>
						<td style="width:170px">
							<?php if ( $m['token'] ) : ?>
								<button class="button button-primary zc-set-webhook" data-messenger="<?php echo esc_attr( $key ); ?>">
									<?php esc_html_e( 'ثبت خودکار وب‌هوک', 'zarincode' ); ?>
								</button>
							<?php else : ?>
								<span style="color:#b32d2e"><?php esc_html_e( 'توکن تنظیم نشده', 'zarincode' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2 style="margin-top:30px"><?php esc_html_e( 'کران جاب هاست', 'zarincode' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'این دستور را در بخش Cron Jobs هاست خود با بازه‌ی هر ۵ دقیقه ثبت کنید:', 'zarincode' ); ?>
		</p>

		<textarea readonly rows="2" style="width:100%;max-width:900px;direction:ltr;font-family:monospace;padding:10px">*/5 * * * * curl -s "<?php echo esc_url( $cron_url ); ?>" >/dev/null 2>&1</textarea>

		<p>
			<a href="<?php echo esc_url( $cron_url ); ?>" target="_blank" rel="noopener" class="button">
				<?php esc_html_e( 'اجرای دستی صف (تست)', 'zarincode' ); ?>
			</a>
		</p>

		<h2 style="margin-top:30px"><?php esc_html_e( 'راهنمای کامل', 'zarincode' ); ?></h2>
		<p>
			<?php esc_html_e( 'راهنمای گام‌به‌گام ساخت ربات و تنظیم کران در فایل زیر قرار دارد:', 'zarincode' ); ?>
			<code>wp-content/themes/zarincode/docs/راهنمای-ربات-و-کران.md</code>
		</p>

		<script>
			jQuery( function ( $ ) {
				$( '.zc-set-webhook' ).on( 'click', function () {
					var btn = $( this );
					btn.prop( 'disabled', true ).text( '<?php echo esc_js( __( 'در حال ثبت…', 'zarincode' ) ); ?>' );

					$.post( ajaxurl, {
						action: 'zc_set_webhook',
						nonce: '<?php echo esc_js( wp_create_nonce( 'zc_admin_nonce' ) ); ?>',
						messenger: btn.data( 'messenger' )
					} ).done( function ( res ) {
						alert( res.data.message || 'انجام شد' );
					} ).fail( function () {
						alert( '<?php echo esc_js( __( 'خطا در ارتباط با سرور', 'zarincode' ) ); ?>' );
					} ).always( function () {
						btn.prop( 'disabled', false ).text( '<?php echo esc_js( __( 'ثبت خودکار وب‌هوک', 'zarincode' ) ); ?>' );
					} );
				} );
			} );
		</script>
	</div>
	<?php
}

/* ==========================================================================
   گزارش فروش جامع
   ========================================================================== */

/**
 * پالت رنگ نمودارها (قابل تنظیم از پنل).
 *
 * @return array
 */
function zc_chart_palette() {
	return array(
		zc_opt( 'zc_chart_primary', '#C9A227' ),
		zc_opt( 'zc_chart_secondary', '#2563EB' ),
		zc_opt( 'zc_chart_tertiary', '#16A34A' ),
		zc_opt( 'zc_chart_quaternary', '#DC2626' ),
		'#7C3AED',
		'#0EA5E9',
		'#F97316',
		'#64748B',
	);
}

/**
 * داده‌ی گزارش فروش جامع.
 *
 * @param string $from از تاریخ.
 * @param string $to   تا تاریخ.
 * @return array
 */
function zc_sales_report( $from = '', $to = '' ) {
	$from = $from ? $from . ' 00:00:00' : gmdate( 'Y-m-01' ) . ' 00:00:00';
	$to   = $to ? $to . ' 23:59:59' : current_time( 'mysql' );

	$data = array(
		'total_revenue'     => 0,
		'orders_count'      => 0,
		'avg_order'         => 0,
		'by_type'           => array( 'product' => 0, 'course' => 0, 'subscription' => 0, 'service' => 0 ),
		'by_gateway'        => array(),
		'top_products'      => array(),
		'daily'             => array(),
		'monthly'           => array(),
		'contract_revenue'  => 0,
		'contract_count'    => 0,
		'subscribers'       => 0,
		'mrr'               => 0,
	);

	if ( ! function_exists( 'wc_get_orders' ) ) {
		return $data;
	}

	$orders = wc_get_orders(
		array(
			'limit'        => -1,
			'status'       => array( 'completed', 'processing' ),
			'date_created' => strtotime( $from ) . '...' . strtotime( $to ),
			'return'       => 'objects',
		)
	);

	$product_totals = array();

	foreach ( $orders as $order ) {
		$total      = (float) $order->get_total();
		$gateway    = $order->get_payment_method() ? $order->get_payment_method_title() : '—';
		$created    = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d' ) : gmdate( 'Y-m-d' );

		$data['total_revenue'] += $total;
		$data['orders_count']++;
		$data['by_gateway'][ $gateway ] = (float) ( $data['by_gateway'][ $gateway ] ?? 0 ) + $total;

		$data['daily'][ $created ] = (float) ( $data['daily'][ $created ] ?? 0 ) + $total;

		$month_key = gmdate( 'Y-m', strtotime( $created ) );
		$data['monthly'][ $month_key ] = (float) ( $data['monthly'][ $month_key ] ?? 0 ) + $total;

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$line_total = (float) $item->get_total();

			// دسته‌بندی نوع.
			if ( function_exists( 'zc_subscription_plan_by_product' ) && zc_subscription_plan_by_product( $product_id ) ) {
				$data['by_type']['subscription'] += $line_total;
			} elseif ( get_post_meta( $product_id, '_zc_linked_course', true ) ) {
				$data['by_type']['course'] += $line_total;
			} else {
				$data['by_type']['product'] += $line_total;
			}

			$product_totals[ $product_id ] = (float) ( $product_totals[ $product_id ] ?? 0 ) + $line_total;
		}
	}

	// پرفروش‌ترین‌ها.
	arsort( $product_totals );
	$data['top_products'] = array_slice( $product_totals, 0, 8, true );

	$data['avg_order'] = $data['orders_count'] ? round( $data['total_revenue'] / $data['orders_count'], 0 ) : 0;
	krsort( $data['daily'] );
	$data['daily'] = array_slice( $data['daily'], 0, 30, true );

	// درآمد قراردادها (پرداخت‌های انجام‌شده).
	$contracts = get_posts(
		array(
			'post_type'      => 'zc_contract',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	foreach ( $contracts as $cid ) {
		if ( function_exists( 'zc_contract_payments' ) ) {
			foreach ( zc_contract_payments( $cid ) as $pay ) {
				if ( ! empty( $pay['paid_at'] ) ) {
					$pdate = gmdate( 'Y-m-d', strtotime( (string) $pay['paid_at'] ) );
					if ( $pdate >= gmdate( 'Y-m-d', strtotime( $from ) ) && $pdate <= gmdate( 'Y-m-d', strtotime( $to ) ) ) {
						$data['contract_revenue'] += (float) $pay['amount'];
						$data['contract_count']++;
					}
				}
			}
		}
	}

	// آمار اشتراک.
	if ( function_exists( 'zc_subscription_report' ) ) {
		$sub = zc_subscription_report();
		$data['subscribers'] = $sub['active_count'];
		$data['mrr']         = round( $sub['mrr'], 0 );
	}

	return $data;
}

/**
 * خروجی CSV گزارش فروش.
 *
 * @param array  $data داده‌ی گزارش.
 * @param string $from از.
 * @param string $to   تا.
 * @return void
 */
function zc_sales_export_csv( $data, $from, $to ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="zarincode-sales-' . gmdate( 'Ymd-His' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, "\xEF\xBB\xBF" );

	fputcsv( $out, array( __( 'گزارش فروش', 'zarincode' ), $from, $to ) );
	fputcsv( $out, array( __( 'درآمد کل', 'zarincode' ), $data['total_revenue'] ) );
	fputcsv( $out, array( __( 'تعداد سفارش', 'zarincode' ), $data['orders_count'] ) );
	fputcsv( $out, array( __( 'درآمد قرارداد', 'zarincode' ), $data['contract_revenue'] ) );
	fputcsv( $out, array() );

	fputcsv( $out, array( __( 'نوع', 'zarincode' ), __( 'درآمد', 'zarincode' ) ) );
	$labels = array( 'product' => __( 'محصولات', 'zarincode' ), 'course' => __( 'دوره‌ها', 'zarincode' ), 'subscription' => __( 'اشتراک‌ها', 'zarincode' ), 'service' => __( 'خدمات', 'zarincode' ) );
	foreach ( $labels as $k => $l ) {
		fputcsv( $out, array( $l, $data['by_type'][ $k ] ) );
	}
	fputcsv( $out, array() );

	fputcsv( $out, array( __( 'روز', 'zarincode' ), __( 'درآمد', 'zarincode' ) ) );
	foreach ( $data['daily'] as $day => $amt ) {
		fputcsv( $out, array( $day, $amt ) );
	}

	fclose( $out );
}

/**
 * صفحه‌ی گزارش فروش جامع.
 *
 * @return void
 */
function zc_admin_sales_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : gmdate( 'Y-m-01' ); // phpcs:ignore
	$to   = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : gmdate( 'Y-m-d' ); // phpcs:ignore

	$data = zc_sales_report( $from, $to );

	// دوره‌ی قبل (مقایسه‌ی بازه‌به‌بازه).
	$zc_len    = max( 1, (int) ( strtotime( $to ) - strtotime( $from ) ) );
	$zc_prev_f = gmdate( 'Y-m-d', strtotime( $from ) - $zc_len );
	$zc_prev_t = gmdate( 'Y-m-d', strtotime( $from ) - DAY_IN_SECONDS );
	$zc_prev   = zc_sales_report( $zc_prev_f, $zc_prev_t );

	$zc_rev_change = $zc_prev['total_revenue'] > 0 ? round( ( ( $data['total_revenue'] - $zc_prev['total_revenue'] ) / $zc_prev['total_revenue'] ) * 100, 1 ) : 0;
	$zc_ord_change = $zc_prev['orders_count']  > 0 ? round( ( ( $data['orders_count'] - $zc_prev['orders_count'] ) / $zc_prev['orders_count'] ) * 100, 1 ) : 0;

	// خروجی CSV.
	if ( isset( $_GET['zc_csv'] ) && '1' === $_GET['zc_csv'] ) { // phpcs:ignore
		zc_sales_export_csv( $data, $from, $to );
		exit;
	}

	$type_labels = array(
		'product'      => __( 'محصولات', 'zarincode' ),
		'course'       => __( 'دوره‌ها', 'zarincode' ),
		'subscription' => __( 'اشتراک‌ها', 'zarincode' ),
		'service'      => __( 'خدمات', 'zarincode' ),
	);
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div><h1><?php esc_html_e( 'گزارش فروش جامع', 'zarincode' ); ?></h1></div>
			<div class="zc-admin-header__actions">
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-sales&zc_csv=1&from=' . $from . '&to=' . $to ) ); ?>"><?php esc_html_e( 'خروجی CSV', 'zarincode' ); ?></a>
			</div>
		</div>

		<form method="get" class="zc-admin-box" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
			<input type="hidden" name="page" value="zarincode-sales">
			<label><?php esc_html_e( 'از تاریخ', 'zarincode' ); ?><br><input type="date" name="from" value="<?php echo esc_attr( $from ); ?>"></label>
			<label><?php esc_html_e( 'تا تاریخ', 'zarincode' ); ?><br><input type="date" name="to" value="<?php echo esc_attr( $to ); ?>"></label>
			<button class="button button-primary"><?php esc_html_e( 'اعمال فیلتر', 'zarincode' ); ?></button>
		</form>

		<div class="zc-admin-stats">
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#C9A2271a;color:#C9A227"><span class="dashicons dashicons-chart-area"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['total_revenue'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد کل', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#2563EB1a;color:#2563EB"><span class="dashicons dashicons-cart"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['orders_count'] ) ); ?></strong><span><?php esc_html_e( 'تعداد سفارش', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#16A34A1a;color:#16A34A"><span class="dashicons dashicons-chart-line"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['avg_order'] ) ) ); ?></strong><span><?php esc_html_e( 'میانگین سفارش', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#7C3AED1a;color:#7C3AED"><span class="dashicons dashicons-id-alt"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['contract_revenue'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد قرارداد', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#0EA5E91a;color:#0EA5E9"><span class="dashicons dashicons-star-filled"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['mrr'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد ماهانه (MRR)', 'zarincode' ); ?></span></div></div>
		</div>

		<div class="zc-admin-box">
			<h2><?php esc_html_e( 'مقایسه‌ی بازه‌به‌بازه (با دوره‌ی قبل)', 'zarincode' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th></th><th><?php esc_html_e( 'این بازه', 'zarincode' ); ?></th><th><?php esc_html_e( 'دوره‌ی قبل', 'zarincode' ); ?> (<?php echo esc_html( $zc_prev_f ); ?> تا <?php echo esc_html( $zc_prev_t ); ?>)</th><th><?php esc_html_e( 'تغییر', 'zarincode' ); ?></th></tr></thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'درآمد', 'zarincode' ); ?></td>
						<td><?php echo esc_html( number_format( $data['total_revenue'] ) ); ?></td>
						<td><?php echo esc_html( number_format( $zc_prev['total_revenue'] ) ); ?></td>
						<td><strong style="color:<?php echo $zc_rev_change >= 0 ? '#16a34a' : '#dc2626'; ?>"><?php echo esc_html( ( $zc_rev_change >= 0 ? '+' : '' ) . zc_fa_num( $zc_rev_change ) ); ?>%</strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'تعداد سفارش', 'zarincode' ); ?></td>
						<td><?php echo esc_html( zc_fa_num( $data['orders_count'] ) ); ?></td>
						<td><?php echo esc_html( zc_fa_num( $zc_prev['orders_count'] ) ); ?></td>
						<td><strong style="color:<?php echo $zc_ord_change >= 0 ? '#16a34a' : '#dc2626'; ?>"><?php echo esc_html( ( $zc_ord_change >= 0 ? '+' : '' ) . zc_fa_num( $zc_ord_change ) ); ?>%</strong></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="zc-admin-box">
			<h2><?php esc_html_e( 'نمودار تعاملی درآمد', 'zarincode' ); ?></h2>
			<canvas id="zc-sales-chart" style="max-height:340px" aria-label="<?php esc_attr_e( 'نمودار درآمد', 'zarincode' ); ?>" role="img"></canvas>
			<p class="description" style="margin-top:8px"><?php esc_html_e( 'برای دیدن جزئیات هر روز، روی نقاط نمودار اشاره کنید. (چارت با کتابخانه‌ی Chart.js)', 'zarincode' ); ?></p>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'درآمد به تفکیک درگاه (نمودار)', 'zarincode' ); ?></h2>
				<canvas id="zc-gateway-chart" style="max-height:300px" aria-label="<?php esc_attr_e( 'نمودار درگاه', 'zarincode' ); ?>" role="img"></canvas>
			</div>
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'مقایسه‌ی درآمد ماهانه', 'zarincode' ); ?></h2>
				<canvas id="zc-monthly-chart" style="max-height:300px" aria-label="<?php esc_attr_e( 'نمودار ماهانه', 'zarincode' ); ?>" role="img"></canvas>
			</div>
		</div>

		<div class="zc-admin-box">
			<h2><?php esc_html_e( 'درآمد به تفکیک نوع', 'zarincode' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<?php foreach ( $type_labels as $key => $label ) : ?>
						<tr>
							<td><?php echo esc_html( $label ); ?></td>
							<td><strong><?php echo esc_html( number_format( $data['by_type'][ $key ] ) ); ?></strong> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'درآمد به تفکیک درگاه', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<?php if ( $data['by_gateway'] ) : foreach ( $data['by_gateway'] as $gw => $amt ) : ?>
							<tr><td><?php echo esc_html( $gw ); ?></td><td><strong><?php echo esc_html( number_format( $amt ) ); ?></strong></td></tr>
						<?php endforeach; else : ?>
							<tr><td colspan="2"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'پرفروش‌ترین‌ها', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<?php if ( $data['top_products'] ) : foreach ( $data['top_products'] as $pid => $amt ) : ?>
							<tr><td><?php echo esc_html( get_the_title( $pid ) ); ?></td><td><strong><?php echo esc_html( number_format( $amt ) ); ?></strong></td></tr>
						<?php endforeach; else : ?>
							<tr><td colspan="2"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="zc-admin-box">
			<h2><?php esc_html_e( 'درآمد روزانه', 'zarincode' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<?php if ( $data['daily'] ) : foreach ( $data['daily'] as $day => $amt ) : ?>
						<tr><td><?php echo esc_html( $day ); ?></td><td><strong><?php echo esc_html( number_format( $amt ) ); ?></strong></td></tr>
					<?php endforeach; else : ?>
						<tr><td colspan="2"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php
	// نمودار تعاملی با Chart.js (فالبک: اگر کتابخانه لود نشد جدول همچنان نمایش داده می‌شود).
	$zc_daily_json   = wp_json_encode( array_values( $data['daily'] ) );
	$zc_days_json    = wp_json_encode( array_map( 'zc_fa_num', array_keys( $data['daily'] ) ) );
	$zc_month_json   = wp_json_encode( array_values( $data['monthly'] ) );
	$zc_months_json  = wp_json_encode( array_keys( $data['monthly'] ) );
	$zc_gw_labels    = wp_json_encode( array_keys( $data['by_gateway'] ) );
	$zc_gw_vals      = wp_json_encode( array_values( $data['by_gateway'] ) );
	$zc_palette      = wp_json_encode( zc_chart_palette() );
	$zc_c_primary    = zc_opt( 'zc_chart_primary', '#C9A227' );
	$zc_c_secondary  = zc_opt( 'zc_chart_secondary', '#2563EB' );
	?>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" id="zc-chart-js"></script>
	<script>
	(function () {
		if (typeof window.Chart === 'undefined') { return; }
		var palette = <?php echo $zc_palette; // phpcs:ignore ?>;
		var c1 = '<?php echo esc_js( $zc_c_primary ); ?>';
		var c2 = '<?php echo esc_js( $zc_c_secondary ); ?>';

		function makeChart(id, config) {
			var el = document.getElementById(id);
			if (!el) return;
			new Chart(el, config);
		}

		// درآمد روزانه (میله‌ای)
		var days = <?php echo $zc_days_json; // phpcs:ignore ?>;
		var dvals = <?php echo $zc_daily_json; // phpcs:ignore ?>;
		if (dvals.length) {
			makeChart('zc-sales-chart', { type: 'bar', data: { labels: days, datasets: [{ label: '<?php echo esc_js( __( 'درآمد روزانه', 'zarincode' ) ); ?>', data: dvals, backgroundColor: c1+'BF', borderColor: c1, borderRadius: 5 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
		}

		// مقایسه‌ی ماهانه (خطی)
		var mons = <?php echo $zc_months_json; // phpcs:ignore ?>;
		var mvals = <?php echo $zc_month_json; // phpcs:ignore ?>;
		if (mvals.length) {
			makeChart('zc-monthly-chart', { type: 'line', data: { labels: mons, datasets: [{ label: '<?php echo esc_js( __( 'درآمد ماهانه', 'zarincode' ) ); ?>', data: mvals, borderColor: c2, backgroundColor: c2+'26', fill: true, tension: .35 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
		}

		// درگاه‌ها (دایره‌ای)
		var gwLabels = <?php echo $zc_gw_labels; // phpcs:ignore ?>;
		var gwVals = <?php echo $zc_gw_vals; // phpcs:ignore ?>;
		if (gwVals.length) {
			makeChart('zc-gateway-chart', { type: 'doughnut', data: { labels: gwLabels, datasets: [{ data: gwVals, backgroundColor: palette }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
		}
	})();
	</script>
	<?php
}
