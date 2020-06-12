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
	public function testShouldReturnCompressedAssetsRules() {
		$expected = '<IfModule mod_headers.c>
		RewriteCond %{HTTP:Accept-Encoding} gzip
		RewriteCond %{REQUEST_FILENAME}\.gz -f
		RewriteRule \.(css|js)$ %{REQUEST_URI}.gz [L]
	
		# Prevent mod_deflate double gzip
		RewriteRule \.gz$ - [E=no-gzip:1]
	
		<FilesMatch "\.gz$">
	
			# Serve correct content types
			<IfModule mod_mime.c>
				# (1)
				RemoveType gz
	
				# Serve correct content types
				AddType text/css              css.gz
				AddType text/javascript       js.gz
	
				# Serve correct content charset
				AddCharset utf-8 .css.gz \
								 .js.gz
			</IfModule>
	
			# Force proxies to cache gzipped and non-gzipped files separately
			Header append Vary Accept-Encoding
		</FilesMatch>
	
		# Serve correct encoding type
		AddEncoding gzip .gz
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
