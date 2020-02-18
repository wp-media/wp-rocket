<?php
namespace WP_Rocket\Tests\Integration\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_get_compressed_assets_rules()
 * @group Functions
 * @group Htaccess
 * @group AdminOnly
 */
class Test_RocketGetCompressedAssetsRules extends FilesystemTestCase {
	protected $structure = [
		'.htaccess' => '',
	];

	public function setUp() {
		parent::setUp();

		global $is_apache;

		$is_apache = true;

		Functions\when( 'rocket_valid_key' )->justReturn( true );

		add_option( 'wp_rocket_settings', [
			'minify_css' => 0,
			'minify_js' => 0,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );
	}

	public function tearDown() {
		delete_option( 'wp_rocket_settings' );

		parent::tearDown();
	}

	public function testShouldContainHtaccessRules() {
		Functions\expect( 'get_home_path' )
			->once()
			->andReturn( 'vfs://cache/' );

		update_option( 'wp_rocket_settings', [
			'minify_css' => 1,
			'minify_js' => 0,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );

		$expected = <<<HTACCESS
		<IfModule mod_headers.c>
			# Serve gzip compressed CSS and JS files if they exist
			# and the client accepts gzip.
			RewriteCond "%{HTTP:Accept-encoding}" "gzip"
			RewriteCond "%{REQUEST_FILENAME}\.gz" -s
			RewriteRule "^(.*)\.(css|js)"         "$1\.$2\.gz" [QSA]
			# Serve correct content types, and prevent mod_deflate double gzip.
			RewriteRule "\.css\.gz$" "-" [T=text/css,E=no-gzip:1]
			RewriteRule "\.js\.gz$"  "-" [T=text/javascript,E=no-gzip:1]
			<FilesMatch "(\.js\.gz|\.css\.gz)$">
				# Serve correct encoding type.
				Header append Content-Encoding gzip
				# Force proxies to cache gzipped &
				# non-gzipped css/js files separately.
				Header append Vary Accept-Encoding
			</FilesMatch>
		</IfModule>
		HTACCESS;

		$htaccess = $this->filesystem->get_contents( '.htaccess' );

		$this->assertContains( $expected, $htaccess );
	}
}
