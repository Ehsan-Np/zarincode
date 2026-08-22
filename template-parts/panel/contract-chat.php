<?php
/**
 * تب گفتگوی قرارداد
 *
 * برگه‌ای مستقل از تیکت پشتیبانی: برای هر قرارداد یک اتاق گفتگو با
 * امکان ارسال متن، تصویر و فایل.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_list = zc_user_contracts();

// قرارداد فعال: از نشانی یا نخستین قرارداد فهرست.
$zc_active = isset( $_GET['contract'] ) ? absint( $_GET['contract'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

if ( ! $zc_active && $zc_list ) {
	$zc_first  = reset( $zc_list );
	$zc_active = (int) $zc_first['id'];
}
?>

<div class="zc-panel__head">
	<h2 class="zc-panel__title">
		<?php zc_the_icon( 'chat', 20 ); ?>
		<?php esc_html_e( 'گفتگوی قرارداد', 'zarincode' ); ?>
	</h2>
</div>

<?php if ( ! $zc_list ) : ?>

	<div class="zc-panel__box">
		<div class="zc-panel__box-body">
			<div class="zc-empty">
				<?php zc_the_icon( 'chat', 34 ); ?>
				<p><?php esc_html_e( 'برای شروع گفتگو باید حداقل یک قرارداد ثبت‌شده داشته باشید.', 'zarincode' ); ?></p>

				<a class="zc-btn zc-btn--gold zc-btn--sm" href="<?php echo esc_url( add_query_arg( 'tab', 'contracts', zc_panel_url() ) ); ?>">
					<?php esc_html_e( 'ثبت قرارداد', 'zarincode' ); ?>
				</a>
			</div>
		</div>
	</div>

<?php else : ?>

	<div class="zc-cchat" data-zc-cchat data-contract="<?php echo esc_attr( $zc_active ); ?>">

		<!-- فهرست گفتگوها -->
		<aside class="zc-cchat__side">
			<div class="zc-cchat__side-head">
				<?php esc_html_e( 'قراردادها', 'zarincode' ); ?>
			</div>

			<div class="zc-cchat__rooms">
				<?php foreach ( $zc_list as $zc_c ) : ?>
					<a class="zc-cchat__room <?php echo ( (int) $zc_c['id'] === $zc_active ) ? 'is-active' : ''; ?>"
						href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contract-chat', 'contract' => $zc_c['id'] ), zc_panel_url() ) ); ?>">

						<span class="zc-cchat__room-ic" style="--s:<?php echo esc_attr( $zc_c['status_color'] ); ?>">
							<?php zc_the_icon( 'file', 16 ); ?>
						</span>

						<span class="zc-cchat__room-body">
							<strong><?php echo esc_html( wp_trim_words( $zc_c['title'], 5 ) ); ?></strong>
							<em dir="ltr"><?php echo esc_html( $zc_c['number'] ); ?></em>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</aside>

		<!-- اتاق گفتگو -->
		<section class="zc-cchat__main">
			<?php $zc_cur = zc_contract_data( $zc_active ); ?>

			<header class="zc-cchat__top">
				<div class="zc-cchat__top-info">
					<strong><?php echo esc_html( $zc_cur['title'] ?? '' ); ?></strong>

					<span class="zc-ct__state" style="--s:<?php echo esc_attr( $zc_cur['status_color'] ?? '#8A93A6' ); ?>">
						<?php echo esc_html( $zc_cur['status_label'] ?? '' ); ?>
					</span>
				</div>

				<a class="zc-btn zc-btn--ghost zc-btn--sm"
					href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contracts', 'contract' => $zc_active ), zc_panel_url() ) ); ?>">
					<?php esc_html_e( 'مشاهده‌ی قرارداد', 'zarincode' ); ?>
				</a>
			</header>

			<div class="zc-cchat__body" data-zc-cchat-body>
				<div class="zc-cchat__loading"><?php esc_html_e( 'در حال دریافت پیام‌ها…', 'zarincode' ); ?></div>
			</div>

			<form class="zc-cchat__form" data-zc-cchat-form enctype="multipart/form-data">
				<div class="zc-cchat__preview" data-zc-cchat-preview hidden></div>

				<div class="zc-cchat__row">
					<label class="zc-cchat__attach" title="<?php esc_attr_e( 'پیوست فایل', 'zarincode' ); ?>">
						<?php zc_the_icon( 'download', 19 ); ?>
						<input type="file" data-zc-cchat-file hidden
							accept="image/*,.pdf,.zip,.rar,.doc,.docx,.txt">
					</label>

					<textarea class="zc-cchat__input" data-zc-cchat-input rows="1"
						placeholder="<?php esc_attr_e( 'پیام خود را بنویسید…', 'zarincode' ); ?>"></textarea>

					<button type="submit" class="zc-cchat__send" aria-label="<?php esc_attr_e( 'ارسال', 'zarincode' ); ?>">
						<?php zc_the_icon( 'send', 18 ); ?>
					</button>
				</div>

				<span class="zc-cchat__hint">
					<?php esc_html_e( 'برای خط جدید Shift + Enter را بزنید.', 'zarincode' ); ?>
				</span>
			</form>
		</section>
	</div>

<?php endif; ?>
