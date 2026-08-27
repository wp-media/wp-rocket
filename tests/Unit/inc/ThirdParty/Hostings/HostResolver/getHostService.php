<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\HostResolver;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\HostResolver::get_host_service
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
	 * Sticky-state cases (flywheel/wpserveur/presslabs/pagely) rely on class_exists()/defined() on real PHP
	 * constants and classes, which cannot be "undefined" again once set within a process. Running each data
	 * set in its own process (same technique used in Logger\DebugEnabledTest) keeps these safe to test both
	 * ways without polluting other data sets or other test files in the same PHPUnit run.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( $expected ) {
		Functions\when( 'wp_unslash' )
			->returnArg();

		Functions\when( 'sanitize_text_field' )
			->returnArg();

		// The siteground branch (rocket_is_plugin_active()) is reached, and evaluated, for every case
		// that doesn't match an earlier branch; default it to "not active" unless overridden below.
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'is_multisite' )->justReturn( false );

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

		$this->assertSame(
			$expected,
			HostResolver::get_host_service( true )
		);
	}
}
