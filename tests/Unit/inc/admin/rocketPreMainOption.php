<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_pre_main_option
 *
 * Regression coverage for a bug where a stale WP_ROCKET_SLUG transient (set by
 * rocket_check_key() to carry license fields into the next settings save) could
 * silently overwrite unrelated keys - e.g. 'cdn' - via the unscoped array_merge(),
 * clobbering a value another part of the plugin had just written (see
 * DataManagerSubscriber::disable_rocketcdn_free_with_rocket_license_expired()).
 *
 * @group admin
 * @group Options
 * @group SaveOptions
 */
class Test_RocketPreMainOption extends TestCase {
	protected $mock_rocket_get_constant = false;

	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_SLUG' ) ) {
			define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		}

		if ( ! defined( 'WP_ROCKET_PLUGIN_SLUG' ) ) {
			define( 'WP_ROCKET_PLUGIN_SLUG', 'wprocket' );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/options.php';

		$this->stubTranslationFunctions();

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'rocket_get_constant' )->justReturn( true ); // Skips the advanced-cache branch.
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( 'wp_next_scheduled' )->justReturn( false );

		unset( $_POST['option_page'] );
	}

	protected function tearDown(): void {
		unset( $_POST['option_page'] );

		parent::tearDown();
	}

	public function testShouldOnlyCarryLicenseFieldsFromTheTransientNotOverwriteUnrelatedKeys() {
		Functions\expect( 'get_transient' )
			->once()
			->with( WP_ROCKET_SLUG )
			->andReturn(
				[
					'consumer_key'   => 'stale-key',
					'consumer_email' => 'stale@example.org',
					'cdn'            => 1, // Stale snapshot from an earlier license check - must NOT win.
				]
			);

		Functions\expect( 'delete_transient' )->once()->with( WP_ROCKET_SLUG );

		$newvalue = rocket_pre_main_option(
			[
				'cdn'       => 0,
				'cdn_state' => 'nothing',
			],
			[
				'cdn'       => 1,
				'cdn_state' => 'rocketcdn_free',
			]
		);

		$this->assertSame( 0, $newvalue['cdn'], 'The freshly-saved cdn=0 must survive the merge with the license transient.' );
		$this->assertSame( 'stale-key', $newvalue['consumer_key'], 'License fields carried by the transient are still applied.' );
		$this->assertSame( 'stale@example.org', $newvalue['consumer_email'] );
	}

	public function testShouldReturnNewValueUnchangedWhenNoTransientIsPending() {
		Functions\expect( 'get_transient' )->once()->with( WP_ROCKET_SLUG )->andReturn( false );
		Functions\expect( 'delete_transient' )->never();

		$newvalue = rocket_pre_main_option( [ 'cdn' => 0 ], [ 'cdn' => 1 ] );

		$this->assertSame( 0, $newvalue['cdn'] );
	}
}
