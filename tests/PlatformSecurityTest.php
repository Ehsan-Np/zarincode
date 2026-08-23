<?php
use PHPUnit\Framework\TestCase;

final class PlatformSecurityTest extends TestCase {
	private function source( string $path ): string {
		return file_get_contents( dirname( __DIR__ ) . '/' . $path );
	}

	public function test_badge_kses_does_not_allow_onclick(): void {
		$helpers = $this->source( 'inc/helpers.php' );
		self::assertStringNotContainsString( "'onclick'  => true", $helpers );
		self::assertStringContainsString( 'zc_sanitize_badge_html', $helpers );
		$hard = $this->source( 'inc/modules/security-hardening.php' );
		self::assertStringContainsString( 'zc_badge_iframe_hosts', $hard );
		self::assertStringContainsString( 'zc_code_looks_dangerous', $hard );
	}

	public function test_admin_login_bypass_requires_secret_and_audits(): void {
		$auth = $this->source( 'inc/modules/auth.php' );
		self::assertStringContainsString( 'zc_admin_login_secret', $auth );
		self::assertStringContainsString( 'hash_equals', $auth );
		self::assertStringContainsString( 'zc_audit', $auth );
	}

	public function test_quiz_run_requires_context_and_js_sends_it(): void {
		$quiz = $this->source( 'inc/modules/quiz.php' );
		self::assertStringContainsString( 'zc_quiz_can_attempt', $quiz );
		$js = $this->source( 'assets/js/main.js' );
		self::assertStringContainsString( "ajax('zc_quiz_run'", $js );
		self::assertStringContainsString( 'type: quiz ? quiz.dataset.type', $js );
	}

	public function test_backup_encrypts_after_gzip_and_sends_enc_mime(): void {
		$backup = $this->source( 'inc/modules/backup.php' );
		self::assertStringContainsString( 'zc_backup_encrypt_file', $backup );
		self::assertStringContainsString( 'zc_backup_mime', $backup );
		self::assertStringContainsString( 'application/octet-stream', $backup );
		self::assertStringNotContainsString( 'cdn.jsdelivr.net', $this->source( 'inc/admin/admin.php' ) );
	}

	public function test_settings_schema_is_filterable_and_image_default_safe(): void {
		$config = $this->source( 'inc/panel/config.php' );
		self::assertStringContainsString( "apply_filters( 'zc_settings_schema'", $config );
		self::assertStringContainsString( "'zc_image_opt_delete_original'", $config );
		self::assertMatchesRegularExpression( "/zc_image_opt_delete_original'.+'default'\\s*=>\\s*false/s", $config );
		self::assertStringContainsString( "'platform'", $config );
	}

	public function test_complete_lesson_does_not_double_fire_when_classroom_saves(): void {
		$course = $this->source( 'inc/modules/course.php' );
		self::assertStringContainsString( '! $fired && 100 === $progress', $course );
	}
}
