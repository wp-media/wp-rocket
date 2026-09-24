<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Plugin\UpdaterSubscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use Plugin_Upgrader;
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
	private $upgrader;
	private $skin;
	private $event_manager;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		// Defines Plugin_Upgrader_Skin so the SUT skips requiring the WP core upgrader file.
		$this->skin     = Mockery::mock( 'Plugin_Upgrader_Skin' );
		$this->upgrader = Mockery::mock( Plugin_Upgrader::class );

		$this->upgrader->skin = $this->skin;

		$this->event_manager = Mockery::mock( Event_Manager::class );
		$this->subscriber    = Mockery::mock(
			UpdaterSubscriber::class . '[get_plugin_upgrader]',
			[
				Mockery::mock( RenewalNotice::class ),
				[
					'plugin_file'    => 'wp-rocket/wp-rocket.php',
					'plugin_version' => '3.0',
					'vendor_url'     => 'https://wp-rocket.me',
				],
			]
		)->shouldAllowMockingProtectedMethods();

		$this->subscriber->set_event_manager( $this->event_manager );

		$_GET = [];
	}

	protected function tearDown(): void {
		$_GET = [];

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->constants  = $config['constants'];
		$_GET['_wpnonce'] = 'nonce';

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( true );
		Functions\expect( 'wp_nonce_ays' )->never();
		Functions\when( 'plugin_basename' )->alias( 'basename' );
		Functions\when( 'get_rocket_option' )->justReturn( 'consumer-key' );
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'add_filter' )->justReturn( true );
		Functions\expect( 'get_site_transient' )->once()->with( 'update_plugins' )->andReturn( new \stdClass() );
		Functions\expect( 'set_site_transient' )
			->once()
			->with(
				'update_plugins',
				Mockery::on(
					function ( $transient ) use ( $expected ) {
						return $expected['new_version'] === $transient->response[ $expected['plugin'] ]->new_version;
					}
				)
			);
		Functions\expect( 'rocket_put_content' )->once()->with( 'vfs://public/wp-content/advanced-cache.php', '' );
		Functions\expect( 'wp_die' )->once();

		Actions\expectDone( 'rocket_before_rollback' )->once();

		$this->event_manager->shouldReceive( 'remove_callback' )
			->once()
			->with( 'pre_set_site_transient_update_plugins', Mockery::type( 'array' ) );

		$this->subscriber->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $this->upgrader );

		$this->upgrader->shouldReceive( 'init' )->once()->ordered();
		$this->skin->shouldReceive( 'header' )->once()->ordered();
		$this->upgrader->shouldReceive( 'fs_connect' )
			->once()
			->with( $expected['fs_connect'] )
			->andReturn( $config['fs_connect'] )
			->ordered();

		if ( $expected['maintenance_mode'] ) {
			$this->upgrader->shouldReceive( 'maintenance_mode' )->once()->with( true )->ordered();
			$this->upgrader->shouldReceive( 'upgrade' )
				->once()
				->with( $expected['plugin'] )
				->andReturn( $config['upgrade'] )
				->ordered();
			$this->upgrader->shouldReceive( 'maintenance_mode' )->once()->with( false )->ordered();
		} else {
			$this->upgrader->shouldReceive( 'maintenance_mode' )->never();
			$this->upgrader->shouldReceive( 'upgrade' )->never();
		}

		$this->skin->shouldReceive( 'footer' )->once()->ordered();

		$this->subscriber->rollback();
	}
}
