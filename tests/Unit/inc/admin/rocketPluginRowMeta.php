<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_plugin_row_meta.
 *
 * @group admin
 */
class Test_RocketPluginRowMeta extends TestCase {

	protected $config = [ 'test_data' => [] ];

	public function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_FILE' ) ) {
			define( 'WP_ROCKET_FILE', WP_ROCKET_PLUGIN_ROOT . 'wp-rocket.php' );
		}

		Functions\when( 'plugin_basename' )->justReturn( 'wp-rocket/wp-rocket.php' );
		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/admin.php';
	}

	public function testShouldAppendTranslatedChangelogLink(): void {
		Functions\expect( 'get_site_transient' )->once()->with( 'update_plugins' )->andReturn( false );
		Functions\expect( 'esc_url' )->once()->with( 'https://wp-rocket.me/changelog/' )->andReturn( 'https://wp-rocket.me/changelog/' );
		Functions\expect( 'esc_html__' )->once()->with( 'Changelog', 'rocket' )->andReturn( 'Historique &amp; mises à jour' );

		$meta = [ 'Version 1.0' ];

		$this->assertSame(
			[
				'Version 1.0',
				'<a href="https://wp-rocket.me/changelog/" target="_blank" rel="noopener noreferrer">Historique &amp; mises à jour</a>',
			],
			rocket_plugin_row_meta( $meta, 'wp-rocket/wp-rocket.php' )
		);
	}

	public function testShouldLeaveOtherPluginsUnchanged(): void {
		Functions\expect( 'get_site_transient' )->never();

		$meta = [ 'Version 1.0' ];

		$this->assertSame( $meta, rocket_plugin_row_meta( $meta, 'other/plugin.php' ) );
	}
}
