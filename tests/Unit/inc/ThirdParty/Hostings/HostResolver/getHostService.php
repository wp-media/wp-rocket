<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostResolver;

use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
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
		unset( $_SERVER['ONECOM_DOMAIN_NAME'] );
		unset( $GLOBALS['is_nginx'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( $expected ) {
		$is_valid = false;

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
				$_SERVER['ONECOM_DOMAIN_NAME'] = true;
				break;
			case 'nginx':
				global $is_nginx;
				$is_nginx = true;
				break;
			case 'siteground':
				$is_valid = true;
				break;
			default:
				break;
		}

		Functions\when('rocket_is_plugin_active')->alias(function ($plugin) use ($is_valid) {
			return $is_valid;
		});

		$this->assertSame(
			$expected,
			HostResolver::get_host_service( true )
		);
	}
}
