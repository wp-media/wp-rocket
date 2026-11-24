<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Fonts\Filesystem;

use WP_Rocket\Engine\Media\Fonts\Filesystem;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Filesystem::write_font_css
 *
 * @group HostFontsLocally
 */
class Test_WriteFontCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Filesystem/writeFontCss.php';

	protected $config;

	private const FONTS_ROOT = 'vfs://public/wp-content/cache/fonts/1/google-fonts/';

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldStoreOnlyValidFontsInsideFontsDirectory( $config, $expected ) {
		if ( ! empty( $config['windows_paths'] ) ) {
			$this->markTestSkipped( 'Windows path handling is covered by the unit test.' );
		}

		$this->config = $config;

		$filesystem = new Filesystem( $this->filesystem );
		$css_path   = self::FONTS_ROOT . 'css/' . $filesystem->hash_to_path( md5( $config['url'] ) ) . '.css';
		$before     = $this->filesystem->getListing( 'vfs://public/wp-content' );

		$this->assertSame( $expected['result'], $filesystem->write_font_css( $config['url'], 'google-fonts' ) );
		$this->assertSame( $expected['requests'], array_column( $this->get_http_requests(), 'url' ) );

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

	/**
	 * Keeps the RocketCDN subscription state from clearing the cache while the font CSS is written.
	 */
	public function cdn_type() {
		return 'byocdn';
	}
}
