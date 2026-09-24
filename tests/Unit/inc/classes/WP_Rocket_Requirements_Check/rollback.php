<?php

namespace WP_Rocket\Tests\Unit\inc\classes\WP_Rocket_Requirements_Check;

use Brain\Monkey\Functions;
use Mockery;
use Plugin_Upgrader;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket_Requirements_Check::rollback
 *
 * @group Plugin
 */
class Test_Rollback extends TestCase {

	/**
	 * Mocked Plugin_Upgrader instance returned by the overridden factory method.
	 *
	 * @var Mockery\MockInterface|Plugin_Upgrader
	 */
	private $upgrader;

	/**
	 * Loads the class under test (not autoloaded, it's a legacy global class) and
	 * defines the constants used by rollback() once for the whole test class.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if ( ! class_exists( 'WP_Rocket_Requirements_Check' ) ) {
			require_once WP_ROCKET_PLUGIN_ROOT . 'inc/classes/class-wp-rocket-requirements-check.php';
		}

		if ( ! defined( 'WP_ROCKET_KEY' ) ) {
			define( 'WP_ROCKET_KEY', 'default-key' );
		}

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', '/path/to/wp-content' );
		}

		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			define( 'WP_PLUGIN_DIR', '/path/to/wp-content/plugins' );
		}
	}

	/**
	 * Sets up the test.
	 */
	protected function setUp(): void {
		parent::setUp();

		// Predefine the two WP core classes the SUT relies on, so the guarded
		// `require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'` is skipped
		// (ABSPATH is faked as boolean `true` by the BrainMonkey test bootstrap, so the
		// real file can never be found from within the unit test environment).
		if ( ! class_exists( 'Plugin_Upgrader_Skin' ) ) {
			Mockery::mock( 'Plugin_Upgrader_Skin' );
		}

		$this->upgrader = Mockery::mock( Plugin_Upgrader::class );
	}

	/**
	 * Asserts that init() -> skin->header() -> fs_connect() -> maintenance_mode(true) ->
	 * upgrade() -> maintenance_mode(false) always run in that order, and that maintenance
	 * mode is always disabled afterward, whatever the result of upgrade().
	 *
	 * @dataProvider upgradeResultProvider
	 *
	 * @param bool|\WP_Error $upgrade_result Value returned by Plugin_Upgrader::upgrade().
	 */
	public function testShouldEnableMaintenanceModeAroundUpgradeAndAlwaysDisableItAfter( $upgrade_result ): void {
		$skin = $this->configureCommonExpectations();

		$this->upgrader->shouldReceive( 'init' )->once()->ordered();
		$skin->shouldReceive( 'header' )->once()->ordered();
		$this->upgrader->shouldReceive( 'fs_connect' )->once()->with( [ WP_CONTENT_DIR, WP_PLUGIN_DIR ] )->andReturn( true )->ordered();
		$this->upgrader->shouldReceive( 'maintenance_mode' )->once()->with( true )->ordered();
		$this->upgrader->shouldReceive( 'upgrade' )->once()->with( 'wp-rocket/wp-rocket.php' )->andReturn( $upgrade_result )->ordered();
		$this->upgrader->shouldReceive( 'maintenance_mode' )->once()->with( false )->ordered();
		$skin->shouldReceive( 'footer' )->never();

		$this->getSubject()->rollback();
	}

	/**
	 * Asserts that when the filesystem connection fails, maintenance mode is never
	 * enabled and upgrade() is never called, mirroring core's bulk_upgrade() behaviour.
	 */
	public function testShouldNotEnableMaintenanceModeWhenFilesystemConnectionFails(): void {
		$skin = $this->configureCommonExpectations();

		$this->upgrader->shouldReceive( 'init' )->once()->ordered();
		$skin->shouldReceive( 'header' )->once()->ordered();
		$this->upgrader->shouldReceive( 'fs_connect' )->once()->with( [ WP_CONTENT_DIR, WP_PLUGIN_DIR ] )->andReturn( false )->ordered();
		$skin->shouldReceive( 'footer' )->once()->ordered();
		$this->upgrader->shouldReceive( 'maintenance_mode' )->never();
		$this->upgrader->shouldReceive( 'upgrade' )->never();

		$this->getSubject()->rollback();
	}

	/**
	 * Provides the different values Plugin_Upgrader::upgrade() may return.
	 */
	public function upgradeResultProvider(): array {
		return [
			'upgrade succeeds'           => [ true ],
			'upgrade returns false'      => [ false ],
			'upgrade returns a WP_Error' => [ new \WP_Error( 'error', 'Download failed' ) ],
		];
	}

	/**
	 * Stubs everything rollback() executes before reaching the upgrader sequence,
	 * and attaches a mocked skin to the upgrader mock.
	 *
	 * @return Mockery\MockInterface Mocked Plugin_Upgrader_Skin, accessible via $upgrader->skin.
	 */
	private function configureCommonExpectations() {
		Functions\expect( 'check_ajax_referer' )->once()->with( 'rocket_rollback' )->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( true );
		Functions\expect( 'wp_die' )->once();

		Functions\expect( 'get_option' )->once()->with( 'wp_rocket_settings' )->andReturn(
			[
				'consumer_key' => 'test-consumer-key',
			]
		);

		$plugin_transient = new \stdClass();
		Functions\expect( 'get_site_transient' )->once()->with( 'update_plugins' )->andReturn( $plugin_transient );
		Functions\when( 'plugin_basename' )->alias( 'basename' );
		Functions\expect( 'set_site_transient' )->once()->with( 'update_plugins', Mockery::type( 'stdClass' ) );

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );

		Functions\expect( 'remove_filter' )->once()->with( 'site_transient_update_plugins', 'rocket_check_update', 1 );

		$skin                 = Mockery::mock( 'Plugin_Upgrader_Skin' );
		$this->upgrader->skin = $skin;

		return $skin;
	}

	/**
	 * Builds the WP_Rocket_Requirements_Check partial mock under test, with
	 * get_plugin_upgrader() overridden to return the mocked Plugin_Upgrader instance.
	 *
	 * @return Mockery\MockInterface|\WP_Rocket_Requirements_Check
	 */
	private function getSubject() {
		$subject = Mockery::mock(
			'WP_Rocket_Requirements_Check[get_plugin_upgrader]',
			[
				[
					'plugin_name'         => 'WP Rocket',
					'plugin_file'         => '/path/to/wp-rocket/wp-rocket.php',
					'plugin_version'      => '3.0',
					'plugin_last_version' => '2.9',
					'wp_version'          => '5.0',
					'php_version'         => '7.2',
				],
			]
		);
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $this->upgrader );

		return $subject;
	}
}
