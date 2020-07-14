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
		switch( $host ) {
			case 'cloudways':
				$_SERVER['cw_allowed_ip'] = true;
				break;
			case 'pressable':
				Functions\when( 'rocket_get_constant' )
					->justReturn( true );
				break;
			case 'spinupwp':
				putenv( 'SPINUPWP_CACHE_PATH=/wp-content/spinupwp-cache/' );
				break;
			case 'wpengine':
				require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/wpe_param.php';
				require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/WpeCommon.php';
				break;
			default:
				break;
		}

		$this->assertTrue( $this->factory->get_subscriber() instanceOf $expected );
	}
}
