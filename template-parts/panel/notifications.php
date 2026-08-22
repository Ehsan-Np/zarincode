<?php
/**
 * پنل کاربری — اعلان‌ها و اتصال به ربات تلگرام/بله
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user_id    = get_current_user_id();
$zc_code       = zc_get_connect_code( $zc_user_id );
$zc_messengers = zc_messengers();
$zc_types      = zc_notification_types();

// اعلان‌های مدیریتی فقط برای مدیران و ویراستاران معنا دارد.
if ( ! current_user_can( 'edit_posts' ) ) {
	unset( $zc_types['admin_alerts'] );
}
$zc_prefs      = get_user_meta( $zc_user_id, 'zc_notify_prefs', true );
$zc_prefs      = is_array( $zc_prefs ) ? $zc_prefs : array();
?>

<div class="zc-panel__head">
	<h2 class="zc-panel__title">
		<?php zc_the_icon( 'bell', 22 ); ?>
		<?php esc_html_e( 'اعلان‌ها و ربات پیام‌رسان', 'zarincode' ); ?>
	</h2>
</div>

<p class="zc-panel__intro">
	<?php esc_html_e( 'حساب کاربری خود را به ربات تلگرام یا بله متصل کنید تا اعلان‌های سایت — مانند پاسخ تیکت، دوره‌های تازه و وضعیت سفارش — را مستقیماً در پیام‌رسان دریافت کنید.', 'zarincode' ); ?>
</p>

<div class="zc-bots">
	<?php foreach ( $zc_messengers as $zc_key => $zc_m ) : ?>
		<?php
		$zc_chat_id   = zc_user_chat_id( $zc_key, $zc_user_id );
		$zc_connected = ! empty( $zc_chat_id );
		$zc_bot_user  = $zc_m['bot'];
		$zc_deeplink  = $zc_bot_user
			? sprintf(
				'telegram' === $zc_key ? 'https://t.me/%1$s?start=%2$s' : 'https://ble.ir/%1$s?start=%2$s',
				rawurlencode( ltrim( $zc_bot_user, '@' ) ),
				rawurlencode( $zc_code )
			)
			: '';
		?>
		<div class="zc-bot-card zc-bot-card--<?php echo esc_attr( $zc_key ); ?><?php echo $zc_connected ? ' is-connected' : ''; ?>">

			<div class="zc-bot-card__head">
				<span class="zc-bot-card__icon">
					<?php echo zc_social_icon( $zc_key, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>

				<div>
					<h3 class="zc-bot-card__name"><?php echo esc_html( $zc_m['label'] ); ?></h3>
					<span class="zc-bot-card__status">
						<?php if ( $zc_connected ) : ?>
							<?php zc_the_icon( 'check', 14 ); ?>
							<?php esc_html_e( 'متصل است', 'zarincode' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'متصل نیست', 'zarincode' ); ?>
						<?php endif; ?>
					</span>
				</div>
			</div>

			<?php if ( ! $zc_m['token'] ) : ?>
				<p class="zc-bot-card__warn">
					<?php zc_the_icon( 'info', 15 ); ?>
					<?php esc_html_e( 'این سرویس هنوز توسط مدیر سایت پیکربندی نشده است.', 'zarincode' ); ?>
				</p>

			<?php elseif ( $zc_connected ) : ?>
				<p class="zc-bot-card__desc">
					<?php esc_html_e( 'اعلان‌های شما به این حساب ارسال می‌شود.', 'zarincode' ); ?>
				</p>

				<button type="button" class="zc-btn zc-btn--outline zc-btn--sm zc-btn--block"
					data-zc-action="zc_disconnect_bot"
					data-zc-payload='{"messenger":"<?php echo esc_attr( $zc_key ); ?>"}'
					data-zc-confirm="<?php esc_attr_e( 'از قطع اتصال مطمئن هستید؟', 'zarincode' ); ?>">
					<?php esc_html_e( 'قطع اتصال', 'zarincode' ); ?>
				</button>

			<?php else : ?>
				<ol class="zc-bot-card__steps">
					<li><?php esc_html_e( 'روی دکمه‌ی زیر بزنید تا ربات باز شود.', 'zarincode' ); ?></li>
					<li><?php esc_html_e( 'دستور /start را همراه کد زیر ارسال کنید.', 'zarincode' ); ?></li>
					<li><?php esc_html_e( 'همین! از این پس اعلان‌ها را دریافت می‌کنید.', 'zarincode' ); ?></li>
				</ol>

				<?php if ( $zc_deeplink ) : ?>
					<a href="<?php echo esc_url( $zc_deeplink ); ?>" class="zc-btn zc-btn--gold zc-btn--sm zc-btn--block"
						target="_blank" rel="noopener">
						<?php echo zc_social_icon( $zc_key, 16 ); // phpcs:ignore ?>
						<?php
						/* translators: %s: نام پیام‌رسان */
						printf( esc_html__( 'اتصال به ربات %s', 'zarincode' ), esc_html( $zc_m['label'] ) );
						?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>

<div class="zc-bot-code">
	<div class="zc-bot-code__label">
		<?php zc_the_icon( 'lock', 17 ); ?>
		<?php esc_html_e( 'کد اتصال اختصاصی شما', 'zarincode' ); ?>
	</div>

	<div class="zc-bot-code__value">
		<code id="zc-bot-code"><?php echo esc_html( $zc_code ); ?></code>

		<button type="button" class="zc-bot-code__copy" data-zc-copy="#zc-bot-code"
			aria-label="<?php esc_attr_e( 'کپی کد', 'zarincode' ); ?>">
			<?php zc_the_icon( 'edit', 16 ); ?>
			<span><?php esc_html_e( 'کپی', 'zarincode' ); ?></span>
		</button>

		<button type="button" class="zc-bot-code__refresh" data-zc-action="zc_refresh_bot_code">
			<?php zc_the_icon( 'refresh', 16 ); ?>
			<span><?php esc_html_e( 'کد جدید', 'zarincode' ); ?></span>
		</button>
	</div>

	<p class="zc-bot-code__hint">
		<?php esc_html_e( 'این کد را در اختیار دیگران قرار ندهید. برای اتصال، دستور زیر را به ربات بفرستید:', 'zarincode' ); ?>
		<code dir="ltr">/start <?php echo esc_html( $zc_code ); ?></code>
	</p>
</div>

<form class="zc-notify-prefs" data-zc-form="zc_save_notify_prefs">
	<h3 class="zc-notify-prefs__title">
		<?php zc_the_icon( 'filter', 19 ); ?>
		<?php esc_html_e( 'چه اعلان‌هایی دریافت کنم؟', 'zarincode' ); ?>
	</h3>

	<div class="zc-notify-prefs__grid">
		<?php foreach ( $zc_types as $zc_type => $zc_label ) : ?>
			<?php
			// پیش‌فرض روشن است، مگر اینکه کاربر قبلاً خاموشش کرده باشد.
			$zc_on = empty( $zc_prefs ) ? true : ! empty( $zc_prefs[ $zc_type ] );
			?>
			<label class="zc-switch-row">
				<span class="zc-switch-row__label"><?php echo esc_html( $zc_label ); ?></span>

				<span class="zc-switch">
					<input type="checkbox" name="prefs[<?php echo esc_attr( $zc_type ); ?>]" value="1" <?php checked( $zc_on ); ?> />
					<span class="zc-switch__track"><span class="zc-switch__thumb"></span></span>
				</span>
			</label>
		<?php endforeach; ?>
	</div>

	<button type="submit" class="zc-btn zc-btn--gold">
		<?php zc_the_icon( 'check', 17 ); ?>
		<?php esc_html_e( 'ذخیره تنظیمات', 'zarincode' ); ?>
	</button>

	<div class="zc-form-msg" role="status" aria-live="polite"></div>
</form>
