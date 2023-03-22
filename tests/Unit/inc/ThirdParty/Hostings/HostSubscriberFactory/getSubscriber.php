<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostSubscriberFactory;

use Mockery;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory::get_subscriber
 *
 * @group  Hostings
 * @group  ThirdParty
 */
class Test_GetSubscriber extends TestCase {
	private $factory;

	protected function setUp(): void {
		parent::setUp();

		$this->factory = new HostSubscriberFactory(
			Mockery::mock( AdminSubscriber::class ),
			Mockery::mock( Event_Manager::class )
		);
	}

	protected function tearDown(): void {
		unset( $_SERVER['cw_allowed_ip'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );
		unset( $GLOBALS['is_nginx'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnSubscriber( $host, $expected ) {
		$is_valid = false;

		switch ( $host ) {
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

		$this->assertInstanceOf( $expected, $this->factory->get_subscriber());
	}
}
