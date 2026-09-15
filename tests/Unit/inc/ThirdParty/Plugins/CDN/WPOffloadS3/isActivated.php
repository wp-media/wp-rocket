<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\WPOffloadS3;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3::is_activated
 *
 * eval() declares as3cf_init()/as3cf_pro_init() in the global namespace for the present
 * cases; @runInSeparateProcess keeps them isolated. Brain\Monkey's Functions\when() is
 * not used because it leaves a real global function declared that leaks into other sets.
 *
 * @group WPOffloadS3
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of as3cf_init()/as3cf_pro_init().
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_as3cf_init'] ) {
			eval( 'function as3cf_init() {}' );
		}

		if ( $config['define_as3cf_pro_init'] ) {
			eval( 'function as3cf_pro_init() {}' );
		}

		$this->assertSame( $expected, WPOffloadS3::is_activated() );
	}
}
