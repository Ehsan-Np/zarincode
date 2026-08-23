<?php
use PHPUnit\Framework\TestCase;

final class SecurityRegressionTest extends TestCase {
	private function source( string $path ): string {
		return file_get_contents( dirname( __DIR__ ) . '/' . $path );
	}

	public function test_quiz_score_comes_from_server_state(): void {
		$source = $this->source( 'inc/modules/quiz.php' );
		self::assertStringContainsString( 'zc_quiz_attempt_get( $token )', $source );
		self::assertStringContainsString( "\$state['first_correct']", $source );
		self::assertStringNotContainsString( "\$_POST['first_correct']", $source );
	}

	public function test_login_redirect_is_local_only(): void {
		self::assertStringContainsString( 'wp_validate_redirect', $this->source( 'inc/modules/auth.php' ) );
		self::assertStringContainsString( 'wp_validate_redirect', $this->source( 'templates/template-login.php' ) );
	}

	public function test_wallet_has_lock_idempotency_and_reversal(): void {
		$wallet = $this->source( 'inc/modules/wallet.php' );
		$order  = $this->source( 'inc/modules/zarinpal.php' );
		self::assertStringContainsString( 'GET_LOCK', $wallet );
		self::assertStringContainsString( 'zc_transaction_by_ref', $wallet );
		self::assertStringContainsString( "'status' => 'pending'", $order );
		self::assertStringContainsString( 'zc_restore_order_wallet', $order );
	}

	public function test_webhook_secret_is_mandatory(): void {
		$source = $this->source( 'inc/modules/messenger-bot.php' );
		self::assertStringContainsString( 'hash_equals( $secret, $provided )', $source );
		self::assertStringContainsString( 'x-telegram-bot-api-secret-token', $source );
	}

	public function test_public_query_cannot_accept_raw_post_type(): void {
		$search = $this->source( 'inc/modules/ajax-search.php' );
		self::assertStringContainsString( 'zc_sanitize_public_query_args', $search );
		self::assertStringContainsString( 'نوع جستجو نامعتبر است', $search );
		$actions = $this->source( 'inc/modules/ajax-actions.php' );
		self::assertStringNotContainsString( "\$_POST['receiver']", $actions );
	}

	public function test_otp_test_mode_does_not_return_code(): void {
		$sms = $this->source( 'inc/modules/sms-kavenegar.php' );
		self::assertStringContainsString( "return array( 'test' => true );", $sms );
		self::assertStringNotContainsString( "'test' => true, 'code' => \$code", $sms );
		self::assertStringContainsString( 'wp_rand( 100000, 999999 )', $sms );
	}

	public function test_support_capability_is_not_edit_posts(): void {
		$ticket = $this->source( 'inc/modules/ticket.php' );
		self::assertStringContainsString( 'zc_can_support()', $ticket );
		self::assertStringNotContainsString( "current_user_can( 'edit_posts' )", $ticket );
	}

	public function test_newsletter_is_batched(): void {
		$source = $this->source( 'inc/modules/newsletter.php' );
		self::assertStringContainsString( 'zc_newsletter_process_batch', $source );
		self::assertStringContainsString( 'zc_schedule_action', $source );
		self::assertStringNotContainsString( "update_option( 'zc_newsletter_campaigns'", $source );
	}
}
