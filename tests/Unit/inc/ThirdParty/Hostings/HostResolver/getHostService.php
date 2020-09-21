<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostResolver;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\HostResolver::get_host_service
 * @uses   ::rocket_get_constant
 *
 * @group  Hostings
 * @group  ThirdParty
 */
class Test_GetHostResolver extends TestCase {
	public function tearDown() {
		parent::tearDown();

		unset( $_SERVER['cw_allowed_ip'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( $expected ) {
		switch ( $expected ) {
			case 'cloudways':
				$_SERVER['cw_allowed_ip'] = true;
				break;
			case 'pressable':
				$this->constants['IS_PRESSABLE'] = true;
				break;
			case 'spinupwp':
				putenv( 'SPINUPWP_CACHE_PATH=/wp-content/spinupwp-cache/' );
				break;
			case 'wpengine':
				require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/wpe_param.php';
				require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/WpeCommon.php';
				break;
			case 'savvii':
				$this->constants['\Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW']       = true;
				$this->constants['\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW'] = true;
				break;
			default:
				break;
		}

		Functions\when( 'get_transient' )->justReturn( 0 );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		$this->assertSame(
			$expected,
			HostResolver::get_host_service( true )
		);
	}
}
