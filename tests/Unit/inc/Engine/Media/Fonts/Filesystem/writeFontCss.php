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

		Functions\when('rocket_mkdir_p')->alias(function($dir) {
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
			return true;
		});

		Functions\when( 'wp_safe_remote_get' )
			->justReturn( $config['response'] );

		Functions\when( 'wp_remote_retrieve_response_code' )
			->justReturn( $config['response_code'] );

		Functions\when( 'content_url' )->justReturn( $config['local_url']);

		$this->assertTrue( $filesystem->write_font_css( $config['url'], $config['provider']) );

		//$this->assertTrue( $this->filesystem->exists( $expected['path'] ) );
    }
}
