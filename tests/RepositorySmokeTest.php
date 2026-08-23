<?php
use PHPUnit\Framework\TestCase;

final class RepositorySmokeTest extends TestCase {
	public function test_all_php_files_have_valid_syntax(): void {
		$root = dirname( __DIR__ );
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( 'php' !== $file->getExtension() || false !== strpos( $file->getPathname(), DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR ) ) { continue; }
			$output = array(); $status = 0;
			exec( escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $file->getPathname() ), $output, $status );
			self::assertSame( 0, $status, implode( "\n", $output ) );
		}
	}

	public function test_bootstrap_local_files_exist(): void {
		$root = dirname( __DIR__ );
		self::assertFileExists( $root . '/inc/modules/typography.php' );
		self::assertFileExists( $root . '/assets/css/redesign.css' );
		self::assertFileExists( $root . '/assets/js/main.js' );
	}

	public function test_version_is_consistent(): void {
		$root = dirname( __DIR__ );
		$functions = file_get_contents( $root . '/functions.php' );
		$style = file_get_contents( $root . '/style.css' );
		self::assertMatchesRegularExpression( "/ZC_VERSION', '3\\.36\\.0'/", $functions );
		self::assertStringContainsString( 'Version: 3.36.0', $style );
	}
}
