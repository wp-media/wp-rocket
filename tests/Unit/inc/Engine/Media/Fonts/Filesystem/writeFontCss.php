<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Filesystem;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\Fonts\Filesystem;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Filesystem::write_font_css
 *
 * @group HostFontsLocally
 */
class Test_WriteFontCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Filesystem/writeFontCss.php';

	private const FONTS_ROOT = 'vfs://public/wp-content/cache/fonts/1/google-fonts/';

	/**
	 * URLs requested during the test, in order.
	 *
	 * @var string[]
	 */
	private $requests = [];

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'content_url' )->alias(
			static function ( $path = '' ) {
				return 'http://example.org/wp-content' . $path;
			}
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			static function ( $response ) {
				return $response['response']['code'] ?? '';
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			static function ( $response ) {
				return $response['body'] ?? '';
			}
		);

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldStoreOnlyValidFontsInsideFontsDirectory( $config, $expected ) {
		$this->requests = [];

		Functions\when( 'wp_safe_remote_get' )->alias(
			function ( $url ) use ( $config ) {
				$this->requests[] = $url;

				return $config['http'][ $url ] ?? [];
			}
		);

		if ( ! empty( $config['windows_paths'] ) ) {
			$this->stubRocketRealpath();
		}

		$filesystem = new Filesystem( $this->filesystem );
		$css_path   = self::FONTS_ROOT . 'css/' . $filesystem->hash_to_path( md5( $config['url'] ) ) . '.css';
		$before     = $this->filesystem->getListing( 'vfs://public/wp-content' );

		$this->assertSame( $expected['result'], $filesystem->write_font_css( $config['url'], 'google-fonts' ) );
		$this->assertSame( $expected['requests'], $this->requests );

		if ( null === $expected['css'] ) {
			$this->assertFalse( $this->filesystem->exists( $css_path ) );
		} else {
			$this->assertSame( $expected['css'], $this->filesystem->get_contents( $css_path ) );
		}

		$created = array_diff( $this->filesystem->getListing( 'vfs://public/wp-content' ), $before );
		$created = array_filter(
			$created,
			static function ( $entry ) {
				return self::FONTS_ROOT !== $entry && 0 !== strpos( $entry, self::FONTS_ROOT . 'css/' );
			}
		);
		$created = str_replace( self::FONTS_ROOT, '', array_values( $created ) );

		sort( $created );

		$this->assertSame( $expected['files'], $created );
	}
}
