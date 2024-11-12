<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Controller\Filesystem;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\Fonts\Controller\Filesystem;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Controller\Filesystem::write_font_css
 *
 * @group FontOptimisation
 */
class test_WriteFontCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Controller/Filesystem/writeFontCss.php';
    protected function setUp(): void {
        parent::setUp();

        Functions\when( 'get_current_blog_id' )->justReturn( 1 );
    }


    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnExpected( $config, $expected ) {
        $filesystem = new Filesystem( $this->filesystem->getUrl( 'wp-content/cache/wp-rocket/fonts/' ) );
		$filesystem->set_version( 1 );

		Functions\when( 'wp_remote_retrieve_body' )
			->justReturn( $config['css_content'] );

		Functions\when( 'rocket_mkdir_p' )->justReturn( true );

		Functions\when( 'wp_remote_get' )
			->justReturn( $config['response'] );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = - 1 ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'content_url' )->justReturn( $config['local_url']);

		$this->assertTrue( $filesystem->write_font_css( $config['url'], $config['provider']) );

		//$this->assertTrue( $this->filesystem->exists( $expected['path'] ) );
    }
}
