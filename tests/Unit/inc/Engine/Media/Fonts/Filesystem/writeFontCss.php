<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Filesystem;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\Fonts\Filesystem;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Controller\Filesystem::write_font_css
 *
 * @group HostFontsLocally
 */
class test_WriteFontCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Filesystem/writeFontCss.php';

    protected function setUp(): void {
        parent::setUp();

        Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->stubWpParseUrl();
    }

    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnExpected( $config, $expected ) {

        $filesystem = new Filesystem();

		Functions\when( 'wp_remote_retrieve_body' )
			->justReturn( $config['css_content'] );

		Functions\when( 'wp_safe_remote_get' )
			->justReturn( $config['response'] );

		Functions\when( 'wp_remote_retrieve_response_code' )
			->justReturn( $config['response_code'] );

		Functions\when( 'content_url' )->justReturn( $config['local_url']);

		$this->assertTrue( $filesystem->write_font_css( $config['url'], $config['provider']) );

		//$this->assertTrue( $this->filesystem->exists( $expected['path'] ) );
    }

	/**
	 * Subsetted fonts (text= parameter) come back from gstatic on a shared path (e.g. /l/font) and
	 * differ only by the query string, so each one must be stored as its own local file.
	 *
	 * @see https://github.com/wp-media/wp-rocket/issues/9191
	 */
	public function testShouldStoreSubsettedFontsInSeparateFiles() {
		$css = 'url(https://fonts.gstatic.com/l/font?kit=AAAA&skey=111&v=v30) format(\'woff2\');'
			. 'url(https://fonts.gstatic.com/l/font?kit=BBBB&skey=222&v=v30) format(\'woff2\');';

		$filesystem = new Filesystem();

		Functions\when( 'wp_remote_retrieve_body' )->justReturn( $css );
		Functions\when( 'wp_safe_remote_get' )->justReturn( [] );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'content_url' )->alias(
			function ( $path ) {
				return 'http://example.org/wp-content' . $path;
			}
		);

		$this->assertTrue(
			$filesystem->write_font_css( 'https://fonts.googleapis.com/css2?family=Open+Sans&text=abc', 'google-font' )
		);

		$fonts_dir = 'vfs://public/wp-content/cache/fonts/1/google-font/fonts/l';
		$files     = array_values( array_diff( scandir( $fonts_dir ), [ '.', '..' ] ) );

		$this->assertCount( 2, $files );
	}
}
