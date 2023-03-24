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
	protected function tearDown(): void {
		unset( $_SERVER['cw_allowed_ip'] );
		unset( $_SERVER['GROUPONE_BRAND_NAME'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( $expected ) {
		Functions\when( 'wp_unslash' )
			->returnArg();

		Functions\when( 'sanitize_text_field' )
			->returnArg();

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
			case 'onecom':
				$_SERVER['GROUPONE_BRAND_NAME'] = 'one.com';
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
