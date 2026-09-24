<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Plugin\UpdaterSubscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use Plugin_Upgrader;
use Plugin_Upgrader_Skin;
use WP_Rocket\Engine\Plugin\RenewalNotice;
use WP_Rocket\Engine\Plugin\UpdaterSubscriber;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Plugin\UpdaterSubscriber::rollback
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
	 * Mocked Event_Manager instance injected into the subject.
	 *
	 * @var Mockery\MockInterface|Event_Manager
	 */
	private $event_manager;

	/**
	 * Defines the plugin constants used by rollback() once for the whole test class.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if ( ! defined( 'WP_ROCKET_FILE' ) ) {
			define( 'WP_ROCKET_FILE', '/path/to/wp-rocket/wp-rocket.php' );
		}

		if ( ! defined( 'WP_ROCKET_LASTVERSION' ) ) {
			define( 'WP_ROCKET_LASTVERSION', '3.0' );
		}

		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
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

		// Reset rather than unset(): some other unit test in the suite may have
		// `unset( $_GET )` entirely (a superglobal, so that removes it process-wide),
		// which would make `unset( $_GET['_wpnonce'] )` fatal with "Undefined variable".
		$_GET = [];
	}

	/**
	 * Tears down the test.
	 */
	protected function tearDown(): void {
		$_GET = [];

		parent::tearDown();
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
		$_GET['_wpnonce'] = 'nonce';

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( true );
		Functions\expect( 'wp_nonce_ays' )->never();

		Actions\expectDone( 'rocket_before_rollback' )->once();

		$plugin_transient = new \stdClass();
		Functions\expect( 'get_site_transient' )->once()->with( 'update_plugins' )->andReturn( $plugin_transient );
		Functions\when( 'plugin_basename' )->alias( 'basename' );
		Functions\expect( 'get_rocket_option' )->once()->with( 'consumer_key' )->andReturn( 'consumer-key' );

		$event_manager = Mockery::mock( Event_Manager::class );
		$event_manager->shouldReceive( 'remove_callback' )
			->once()
			->with( 'pre_set_site_transient_update_plugins', Mockery::type( 'array' ) );

		Functions\expect( 'set_site_transient' )->once()->with( 'update_plugins', Mockery::type( 'stdClass' ) );

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );

		Functions\when( 'add_filter' )->justReturn( true );
		Functions\expect( 'rocket_put_content' )->once();

		Functions\expect( 'wp_die' )->once();

		$skin                 = Mockery::mock( 'Plugin_Upgrader_Skin' );
		$this->upgrader->skin = $skin;

		$this->event_manager = $event_manager;

		return $skin;
	}

	/**
	 * Builds the UpdaterSubscriber partial mock under test, with get_plugin_upgrader()
	 * overridden to return the mocked Plugin_Upgrader instance.
	 *
	 * @return Mockery\MockInterface|UpdaterSubscriber
	 */
	private function getSubject() {
		$renewal_notice = Mockery::mock( RenewalNotice::class );
		$subject        = Mockery::mock(
			UpdaterSubscriber::class . '[get_plugin_upgrader]',
			[
				$renewal_notice,
				[
					'plugin_file'    => WP_ROCKET_FILE,
					'plugin_version' => '3.0',
					'vendor_url'     => 'https://wp-rocket.me',
				],
			]
		);
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $this->upgrader );
		$subject->set_event_manager( $this->event_manager );

		return $subject;
	}
}
