<?php
/**
 * ویجت چت آنلاین
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
$zc_chat_open   = function_exists( 'zc_chat_is_open' ) ? zc_chat_is_open() : true;
$zc_chat_avatar = zc_opt( 'zc_chat_avatar', '' );
$zc_chat_avatar = is_array( $zc_chat_avatar ) ? ( $zc_chat_avatar['url'] ?? '' ) : $zc_chat_avatar;
?>
<div class="zc-chat-widget">
	<div class="zc-chat-box" role="dialog" aria-label="<?php esc_attr_e( 'چت آنلاین', 'zarincode' ); ?>">
		<div class="zc-chat-head">
			<span class="zc-avatar zc-avatar--sm" style="background:var(--zc-grad-gold);display:flex;align-items:center;justify-content:center;color:#241C05">
				<?php if ( $zc_chat_avatar ) : ?><img src="<?php echo esc_url( $zc_chat_avatar ); ?>" alt="" width="36" height="36"><?php else : ?><?php zc_the_icon( 'headphone', 18 ); ?><?php endif; ?>
			</span>
			<div style="flex:1">
				<strong style="font-size:.9rem;display:block"><?php echo esc_html( zc_opt( 'zc_chat_title', 'پشتیبانی زرین کد' ) ); ?></strong>
				<span class="zc-chat-head__status"><?php echo $zc_chat_open ? esc_html__( 'آنلاین', 'zarincode' ) : esc_html__( 'خارج از ساعت کاری', 'zarincode' ); ?></span>
			</div>
			<button data-zc-close="chat" aria-label="<?php esc_attr_e( 'بستن', 'zarincode' ); ?>" style="color:#fff"><?php zc_the_icon( 'close', 18 ); ?></button>
		</div>

		<div class="zc-chat-body">
			<div class="zc-chat-msg zc-chat-msg--in">
				<?php echo esc_html( zc_opt( 'zc_chat_welcome', 'سلام 👋 به زرین کد خوش آمدید! چطور می‌تونم کمکتون کنم؟' ) ); ?>
			</div>
		</div>

		<form class="zc-chat-form zc-chat-foot">
			<input type="text" placeholder="<?php esc_attr_e( 'پیام خود را بنویسید…', 'zarincode' ); ?>" aria-label="<?php esc_attr_e( 'پیام', 'zarincode' ); ?>" required>
			<button type="submit" class="zc-btn zc-btn--gold zc-btn--icon" aria-label="<?php esc_attr_e( 'ارسال', 'zarincode' ); ?>">
				<?php zc_the_icon( 'send', 18 ); ?>
			</button>
		</form>
	</div>

	<button class="zc-chat-toggle" aria-label="<?php esc_attr_e( 'چت آنلاین', 'zarincode' ); ?>">
		<?php zc_the_icon( 'chat', 26 ); ?>
	</button>
</div>
