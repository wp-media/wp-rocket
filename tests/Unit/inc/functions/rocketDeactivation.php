<?php
/**
 * Tests for rocket_deactivation().
 *
 * @package WP_Rocket\Tests\Unit\inc\functions
 * @author  Caspar Green
 * @since   ver 3.6.1
 */

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * Test class for rocket_deactivation().
 *
 * @package WP_Rocket\Tests\Unit\inc\functions
 * @author  Caspar Green
 * @covers ::rocket_deactivation
 * @group   Functions
 * @since   ver 3.6.1
 */
class Test_RocketDeactivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketDeactivation.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/deactivation.php';
	}

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_WEB_API' )
			->andReturn( 'https://wp-rocket.me/api/wp-rocket' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test should not call cleanup functions when WP_ROCKET_CONFIG_PATH is not empty.
	 *
	 * @return void
	 * @since  ver 3.6.1
	 *
	 * @author Caspar Green
	 */
	public function testShouldNotCallCleanUpFunctionsWhenRocketConfigPathNotEmpty() {
		$_GET['rocket_nonce'] = 'some-nonce-randomness';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'rocket_delete_config_file' )->justReturn();
		Functions\when( 'wp_remote_get' )->justReturn();
		Functions\when( 'delete_transient' )->justReturn();
		Functions\when( 'delete_site_transient' )->justReturn();
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn();
		Functions\when( 'WP_Rocket\Subscriber\Plugin\get_role' )->justReturn( false );

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_CONFIG_PATH' )
			->andReturn( 'vfs://public/wp-content/wp-rocket-config/' );
		Functions\expect( 'flush_rocket_htaccess' )
			->never();
		Functions\expect( 'set_rocket_wp_cache_define' )
			->never();
		Functions\expect( 'rocket_put_content' )
			->never();

		rocket_deactivation();
	}

	/**
	 * Test should call cleanup functions when WP_ROCKET_CONFIG_PATH is empty.
	 *
	 * @return void
	 *
	 * @throws ExpectationArgsRequired
	 * @author Caspar Green
	 * @since  ver 3.6.1
	 *
	 */
	public function testShouldCallCleanUpFunctionsWhenRocketConfigPathEmpty() {
		$_GET['rocket_nonce'] = 'some-nonce-randomness';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'wp_remote_get' )->justReturn();
		Functions\when( 'delete_transient' )->justReturn();
		Functions\when( 'delete_site_transient' )->justReturn();
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn();
		Functions\when( 'WP_Rocket\Subscriber\Plugin\get_role' )->justReturn( false );

		Functions\stubs(
			[
				'rocket_delete_config_file' => function () {
					{
						$this->filesystem->delete( 'vfs://public/wp-content/wp-rocket-config/example.org.php' );
					};
				},
			]
		);

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_CONFIG_PATH' )
			->andReturn( 'vfs://public/wp-content/wp-rocket-config/' );
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_CONTENT_DIR' )
			->andReturn( 'vfs://public/wp-content/' );
		Functions\expect( 'flush_rocket_htaccess' )
			->once()
			->with( true )->andReturn();
		Functions\expect( 'set_rocket_wp_cache_define' )
			->once()
			->with( false )
			->andReturn();
		Functions\expect( 'rocket_put_content' )
			->once()
			->with( 'vfs://public/wp-content/advanced-cache.php', '' )
			->andReturn();

		rocket_deactivation();
	}
}
