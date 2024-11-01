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
    protected function setUp(): void {
        parent::setUp();

        Functions\when( 'get_current_blog_id' )->justReturn( 1 );
    }


    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnExpected( $url, $file ) {
        $filesystem = new Filesystem( $this->filesystem->getUrl( 'wp-content/cache/wp-rocket/fonts/' ) );


    }
}
