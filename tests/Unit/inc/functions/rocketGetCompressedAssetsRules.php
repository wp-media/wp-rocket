<?php
namespace WP_Rocket\Tests\Unit\inc\Functions;

use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_get_compressed_assets_rules()
 * @group Functions
 * @group Htaccess
 */
class Test_RocketGetCompressedAssetsRules extends TestCase {
	public function setUp() {
		parent::setUp();

		require_once( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/htaccess.php' );
	}

	public function testShouldReturnCompressedAssetsRules() {
		$expected = '<IfModule mod_headers.c>
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
		</IfModule>';

		$this->assertSame(
			$this->format_htaccess( $expected ),
			$this->format_htaccess( rocket_get_compressed_assets_rules() )
		);
	}

	private function format_htaccess( $string ) {
		$string = trim( $string );

		return preg_replace( '/^\s*/m', '', $string );
	}
}
