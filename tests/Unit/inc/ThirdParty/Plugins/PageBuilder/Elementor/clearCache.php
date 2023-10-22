<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_cache
 * @group Elementor
 * @group ThirdParty
 */
class Test_ClearCache extends TestCase {
	use ElementorTestTrait;

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
