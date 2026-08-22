<?php
/**
 * قالب صفحه ۴۰۴
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="zc-container">
	<div class="zc-404" data-zc-anim="up">
		<div class="zc-404__code">۴۰۴</div>
		<h1><?php esc_html_e( 'صفحه مورد نظر یافت نشد!', 'zarincode' ); ?></h1>
		<p><?php esc_html_e( 'به نظر می‌رسد این صفحه حذف شده یا آدرس آن تغییر کرده است. می‌توانید از طریق جستجو یا لینک‌های زیر ادامه دهید.', 'zarincode' ); ?></p>

		<div style="max-width:440px;margin:0 auto 24px">
			<?php get_search_form(); ?>
		</div>

		<div class="zc-404__actions">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'arrow-left', 17 ); ?><?php esc_html_e( 'صفحه اصلی', 'zarincode' ); ?></a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--outline"><?php zc_the_icon( 'video', 17 ); ?><?php esc_html_e( 'دوره‌های آموزشی', 'zarincode' ); ?></a>
			<?php if ( zc_is_woo() ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="zc-btn zc-btn--outline"><?php zc_the_icon( 'cart', 17 ); ?><?php esc_html_e( 'فروشگاه', 'zarincode' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>

<style>
.zc-404{text-align:center;padding:70px 20px}
.zc-404__code{
  font-size:clamp(5rem,16vw,10rem);font-weight:800;line-height:1;
  background:var(--zc-grad-gold);-webkit-background-clip:text;background-clip:text;color:transparent;
  margin-bottom:10px;animation:zc-float 4s ease-in-out infinite
}
.zc-404 p{color:var(--zc-muted);max-width:520px;margin:0 auto 26px}
.zc-404__actions{display:flex;gap:11px;justify-content:center;flex-wrap:wrap}
</style>

<?php
get_footer();
