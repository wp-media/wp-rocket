<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_cache
 * @group Elementor
 * @group ThirdParty
 */
class Test_ClearCache extends TestCase {
	private $elementor;

	public function setUp() {
		parent::setUp();

		$this->elementor = new Elementor( $this->createMock( Options_Data::class ) );
	}

	public function testShouldDoNothingWhenNotExternal() {
		Functions\when( 'get_option' )->justReturn( 'internal' );

		Functions\expect( 'rocket_clean_domain' )
			->never();
		Functions\expect( 'rocket_clean_minify' )
			->never();

		$this->elementor->clear_cache();
	}

	public function testShouldCleanRocketCacheDirectories() {
		Functions\when( 'get_option' )->justReturn( 'external' );

		Functions\expect( 'rocket_clean_domain' )
			->once();
		Functions\expect( 'rocket_clean_minify' )
			->once()
			->with( 'css' );

		$this->elementor->clear_cache();
	}
}
