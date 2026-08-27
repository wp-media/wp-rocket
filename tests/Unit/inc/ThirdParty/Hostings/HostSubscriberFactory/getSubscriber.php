<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostSubscriberFactory;

use Mockery;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory::get_subscriber
 *
 * @group Hostings
 * @group ThirdParty
 */
class TestGetSubscriber extends TestCase {
	private $factory;

	protected function setUp(): void {
		parent::setUp();

		$this->factory = new HostSubscriberFactory();
	}

	protected function tearDown(): void {
		unset( $_SERVER['cw_allowed_ip'] );
		putenv( 'SPINUPWP_CACHE_PATH=' );

		parent::tearDown();
	}

	/**
	 * Sticky-state cases (flywheel/wpserveur/presslabs/pagely) rely on class_exists()/defined(), which cannot
	 * be reverted within a process. Running each data set in its own process avoids cross-test pollution.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 */
	public function testShouldReturnSubscriber( $host, $expected ) {
		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return is_string( $value ) ? stripslashes( $value ) : $value;
			}
		);
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return is_string( $value ) ? strip_tags( $value ) : $value;
			}
		);

		// The siteground branch (rocket_is_plugin_active()) is reached, and evaluated, for every case
		// that doesn't match an earlier branch; default it to "not active" unless overridden below.
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'is_multisite' )->justReturn( false );

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
			case 'flywheel':
				if ( ! class_exists( 'FlywheelNginxCompat' ) ) {
					Mockery::mock( 'overload:FlywheelNginxCompat' );
				}
				break;
			case 'siteground':
				Functions\when( 'rocket_is_plugin_active' )->justReturn( true );
				break;
			case 'wpserveur':
				$this->constants['DB_HOST'] = 'db.example.wpserveur.net';
				break;
			case 'presslabs':
				if ( ! defined( 'PL_INSTANCE_REF' ) ) {
					define( 'PL_INSTANCE_REF', 'test-instance' );
				}
				if ( ! defined( 'WP_CONTENT_DIR' ) ) {
					define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
				}
				if ( ! class_exists( '\Presslabs\Cache\CacheHandler' ) ) {
					Mockery::mock( 'overload:Presslabs\Cache\CacheHandler' );
				}
				break;
			case 'pagely':
				if ( ! class_exists( 'PagelyCachePurge' ) ) {
					Mockery::mock( 'overload:PagelyCachePurge' );
				}
				break;
			default:
				break;
		}

		$this->assertInstanceOf(
			$expected,
			$this->factory->get_subscriber()
		);
	}
}
