<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_cache_busting()
 * @group Functions
 * @group Files
 */
class Test_RocketCleanCacheBusting extends FilesystemTestCase {
	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( '1' );
	}

	public function testShouldCleanMinifiedCSS() {
		rocket_clean_cache_busting( 'css' );

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css.gz' ) );
	}

	public function testShouldCleanMinifiedJS() {
		rocket_clean_cache_busting( 'js' );

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
	}

	public function testShouldCleanAllMinified() {
		rocket_clean_cache_busting();

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css.gz' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
	}
}
