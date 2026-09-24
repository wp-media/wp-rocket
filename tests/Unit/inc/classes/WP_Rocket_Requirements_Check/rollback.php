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
	private $upgrader;
	private $skin;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Legacy global class, not autoloaded.
		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/classes/class-wp-rocket-requirements-check.php';
	}

	protected function setUp(): void {
		parent::setUp();

		// Defines Plugin_Upgrader_Skin so the SUT skips requiring the WP core upgrader file.
		$this->skin     = Mockery::mock( 'Plugin_Upgrader_Skin' );
		$this->upgrader = Mockery::mock( Plugin_Upgrader::class );

		$this->upgrader->skin = $this->skin;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->constants = $config['constants'];

		Functions\expect( 'get_option' )->once()->with( 'wp_rocket_settings' )->andReturn( [ 'consumer_key' => 'consumer-key' ] );
		Functions\expect( 'check_ajax_referer' )->once()->with( 'rocket_rollback' )->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( true );
		Functions\expect( 'get_site_transient' )->once()->with( 'update_plugins' )->andReturn( new \stdClass() );
		Functions\when( 'plugin_basename' )->alias( 'basename' );
		Functions\expect( 'set_site_transient' )->once()->with( 'update_plugins', Mockery::type( 'stdClass' ) );
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\expect( 'remove_filter' )->once()->with( 'site_transient_update_plugins', 'rocket_check_update', 1 );
		Functions\expect( 'wp_die' )->once();

		$requirements_check = Mockery::mock(
			'WP_Rocket_Requirements_Check[get_plugin_upgrader]',
			[
				[
					'plugin_name'         => 'WP Rocket',
					'plugin_file'         => 'wp-rocket/wp-rocket.php',
					'plugin_version'      => '3.0',
					'plugin_last_version' => '2.9',
					'wp_version'          => '5.0',
					'php_version'         => '7.2',
				],
			]
		)->shouldAllowMockingProtectedMethods();

		$requirements_check->shouldReceive( 'get_plugin_upgrader' )->once()->andReturn( $this->upgrader );

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
				->with( 'wp-rocket/wp-rocket.php' )
				->andReturn( $config['upgrade'] )
				->ordered();
			$this->upgrader->shouldReceive( 'maintenance_mode' )->once()->with( false )->ordered();
		} else {
			$this->upgrader->shouldReceive( 'maintenance_mode' )->never();
			$this->upgrader->shouldReceive( 'upgrade' )->never();
		}

		$this->skin->shouldReceive( 'footer' )->once()->ordered();

		$requirements_check->rollback();
	}
}
