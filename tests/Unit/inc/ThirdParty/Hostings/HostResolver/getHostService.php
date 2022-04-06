<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostResolver;

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
	protected function tear_down() {
		unset( $_SERVER['cw_allowed_ip'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );

		parent::tear_down();
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
			case 'savvii':
				$this->constants['\Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW']       = true;
				$this->constants['\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW'] = true;
				break;
			default:
				break;
		}

		$this->assertSame(
			$expected,
			HostResolver::get_host_service( true )
		);
	}
}
