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
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\Admin::add_regenerate_menu_item
 *
 * @group  CriticalPath
 * @group  CriticalPathAdmin
 */
class Test_AddRegenerateMenuItem extends TestCase {
	use AdminTrait;

	private $admin;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->setUpMocks();
		$this->stubTranslationFunctions();

		$this->admin = new Admin(
			$this->options,
			Mockery::mock( ProcessorService::class )
		);
	}

	protected function tearDown(): void {
		unset( $_SERVER['REQUEST_URI'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $cap, $admin, $option, $filter, $request, $expected ) {
		$_SERVER['REQUEST_URI'] = $request;
		$wp_admin_bar           = new WP_Admin_Bar();

		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_regenerate_critical_css' )
			->andReturn( $cap );

		if ( $cap ) {
			$this->assertMocks( $admin, $option, $filter );
		} else {
			$this->assertBailsOut();
		}

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

	private function assertBailsOut() {
		Functions\expect( 'is_admin' )->never();

		$this->options->shouldReceive( 'get' )->with( 'async_css', 0 )->never();

	}

	private function assertMocks( $admin, $option, $filter ) {
		Functions\expect( 'is_admin' )->andReturn( $admin );

		if ( ! $admin ) {
			return $this->assertAsycnCssDisabled();
		}

		$this->options
			->shouldReceive( 'get' )
			->once()
			->with( 'async_css', 0 )
			->andReturn( $option );

		if ( $option ) {
			Filters\expectApplied( 'do_rocket_critical_css_generation' )
				->once()
				->with( true )
				->andReturn( $filter );
		} else {
			return $this->assertAsycnCssDisabled();
		}

		if ( ! $filter ) {
			return $this->assertBailOutForFilter();
		}

		$request_uri = $this->assertUsesRequestUri();
		$referer     = '' === $request_uri
			? ''
			: '&_wp_http_referer=' . rawurlencode( $request_uri );

		$action = 'rocket_generate_critical_css';
		$admin_path = "admin-post.php?action={$action}{$referer}";
		$admin_url  = "http://example.org/wp-admin/{$admin_path}";

		Functions\expect( 'admin_url' )
			->once()
			->with( $admin_path )
			->andReturn( $admin_url );
		Functions\expect( 'wp_nonce_url' )
			->once()
			->with( $admin_url, $action )
			->andReturnUsing(
				function ( $url ) {
					return str_replace( '&', '&amp;', "{$url}&_wpnonce=wp_rocket_nonce" );
				}
			);
	}

	private function assertAsycnCssDisabled() {
		Filters\expectApplied( 'do_rocket_critical_css_generation' )->never();
		Functions\expect( 'admin_url' )->never();
		Functions\expect( 'wp_nonce_url' )->never();
	}

	private function assertBailOutForFilter() {
		Functions\expect( 'admin_url' )->never();
		Functions\expect( 'wp_nonce_url' )->never();
	}

	private function assertUsesRequestUri() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			Functions\expect( 'wp_unslash' )->never();
			Functions\expect( 'remove_query_arg' )->never();

			return '';
		}

		$request_uri = stripslashes( $_SERVER['REQUEST_URI'] );

		Functions\expect( 'wp_unslash' )
			->once()
			->with( $_SERVER['REQUEST_URI'] )
			->andReturn( $request_uri );

		Functions\expect( 'remove_query_arg' )
			->once()
			->with( 'fl_builder', $request_uri )
			->andReturn( $request_uri );

		return $request_uri;
	}
}
