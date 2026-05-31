<?php

namespace WP_Rocket\Tests\Unit\inc\admin\enqueue;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

// Define constants required by enqueue.php at file-scope (add_action call on require).
if ( ! defined( 'WP_ROCKET_PLUGIN_SLUG' ) ) {
	define( 'WP_ROCKET_PLUGIN_SLUG', 'wprocket' );
}
if ( ! defined( 'WP_ROCKET_ASSETS_CSS_URL' ) ) {
	define( 'WP_ROCKET_ASSETS_CSS_URL', 'https://example.com/assets/css/' );
}
if ( ! defined( 'WP_ROCKET_ASSETS_JS_URL' ) ) {
	define( 'WP_ROCKET_ASSETS_JS_URL', 'https://example.com/assets/js/' );
}
if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
	define( 'WP_ROCKET_VERSION', '3.99.0-test' );
}

/**
 * Test class covering ::rocket_add_admin_css_js, specifically the reset_sidebar flag injection.
 *
 * @group admin
 * @group AdminOnly
 */
class Test_RocketAddAdminCssJs extends TestCase {
	/**
	 * Sets up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/ui/enqueue.php';
	}

	/**
	 * Registers Brain\Monkey expectations for script/style enqueueing functions.
	 */
	private function mockScriptEnqueue(): void {
		Functions\expect( 'wp_enqueue_style' )->andReturnNull();
		Functions\expect( 'wp_enqueue_script' )->andReturnNull();
		Functions\expect( 'wp_create_nonce' )
			->with( 'rocket-ajax' )
			->andReturn( 'test_nonce' );
		Functions\expect( 'is_rtl' )->andReturn( false );
	}

	/**
	 * When the rocket_reset_sidebar transient exists, reset_sidebar must be true
	 * and the transient must be deleted after being read.
	 */
	public function testShouldPassResetSidebarTrueWhenTransientExists(): void {
		$this->mockScriptEnqueue();

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_reset_sidebar' )
			->andReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_reset_sidebar' );

		$localized_data = null;

		Functions\expect( 'wp_localize_script' )
			->once()
			->andReturnUsing(
				function ( $handle, $object_name, $data ) use ( &$localized_data ) {
					$localized_data = $data;
					return true;
				}
			);

		Functions\expect( 'apply_filters' )
			->with( 'rocket_localize_admin_script', \Mockery::type( 'array' ) )
			->andReturnUsing(
				function ( $tag, $data ) {
					return $data;
				}
			);

		rocket_add_admin_css_js();

		$this->assertTrue( $localized_data['reset_sidebar'] );
	}

	/**
	 * When the rocket_reset_sidebar transient does not exist, reset_sidebar must be false
	 * and delete_transient must not be called for it.
	 */
	public function testShouldPassResetSidebarFalseWhenTransientAbsent(): void {
		$this->mockScriptEnqueue();

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_reset_sidebar' )
			->andReturn( false );

		Functions\expect( 'delete_transient' )->never();

		$localized_data = null;

		Functions\expect( 'wp_localize_script' )
			->once()
			->andReturnUsing(
				function ( $handle, $object_name, $data ) use ( &$localized_data ) {
					$localized_data = $data;
					return true;
				}
			);

		Functions\expect( 'apply_filters' )
			->with( 'rocket_localize_admin_script', \Mockery::type( 'array' ) )
			->andReturnUsing(
				function ( $tag, $data ) {
					return $data;
				}
			);

		rocket_add_admin_css_js();

		$this->assertFalse( $localized_data['reset_sidebar'] );
	}
}
