<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostSubscriberFactory;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory::get_subscriber
 *
 * @group  Hostings
 * @group  ThirdParty
 */
class Test_GetSubscriber extends TestCase {
	private $factory;

	public function setUp() {
		parent::setUp();

		$this->factory = new HostSubscriberFactory(
			Mockery::mock( AdminSubscriber::class ),
			Mockery::mock( Event_Manager::class )
		);
	}

	public function tearDown() {
		parent::tearDown();

		unset( $_SERVER['cw_allowed_ip'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnSubscriber( $host, $expected ) {
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

		$this->assertTrue( $this->factory->get_subscriber() instanceof $expected );
	}
}
