<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Admin;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Admin_Bar;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Admin::add_regenerate_menu_item
 *
 * @group  CriticalPath
 * @group  CriticalPathAdmin
 */
class Test_AddRegenerateMenuItem extends TestCase {
	use AdminTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}

	protected function setUp() {
		parent::setUp();

		$this->setUpMocks();

		$this->admin = new Admin(
			$this->options,
			Mockery::mock( ProcessorService::class )
		);
	}

	protected function tearDown() {
		parent::tearDown();
	
		unset( $_SERVER['REQUEST_URI'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $cap, $admin, $option, $filter, $request, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( $cap );
		Functions\when( 'is_admin' )->justReturn( $admin );
		Functions\when( 'admin_url' )->alias( function( $path ) {
			return "http://example.org/wp-admin/{$path}";
		} );
		Functions\when( 'wp_nonce_url' )->alias( function( $url ) {
			return "{$url}&_wpnonce=wp_rocket_nonce";
		} );
		Functions\when( 'wp_unslash' )->alias( function( $value ) {
			return stripslashes( $value );
		} );
		Functions\when( 'remove_query_arg' )->returnArg( 2 );

		$this->options->shouldReceive( 'get' )
			->atMost()
			->times( 1 )
			->with( 'async_css', 0 )
			->andReturn( $option );

		Filters\expectApplied( 'do_rocket_critical_css_generation' )
			->atMost()
			->times( 1 )
			->andReturn( $filter );

		$_SERVER['REQUEST_URI' ] = $request;
		$wp_admin_bar = new WP_Admin_Bar();

		if ( false === $expected ) {
			$this->assertNull( $this->admin->add_regenerate_menu_item( $wp_admin_bar ) );
		} else {
			$this->admin->add_regenerate_menu_item( $wp_admin_bar );

			$this->assertEquals(
				$wp_admin_bar->get_node( 'regenerate-critical-path' ),
				$expected
			);
		}
	}
}