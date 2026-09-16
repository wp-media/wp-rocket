<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets::is_activated
 *
 * eval() declares as3cf_assets_init() in the global namespace for the present case;
 * @runInSeparateProcess keeps it isolated. Brain\Monkey's Functions\when() is not used
 * because it leaves a real global function declared that leaks into the absent set.
 *
 * @group WPOffloadS3Assets
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the as3cf_assets_init() function.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_as3cf_assets_init'] ) {
			eval( 'function as3cf_assets_init() {}' );
		}

		$this->assertSame( $expected, WPOffloadS3Assets::is_activated() );
	}
}
