<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::set_cdn_state
 *
 * @uses   \WP_Rocket\Admin\Options::set
 * @uses   \WP_Rocket\Admin\Options::get_option_name
 *
 * @group  RocketCDN
 * @group  CDNOptionsManager
 */
class Test_SetCdnState extends TestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/setCdnState.php';

	/**
	 * Consumer email used to build a license triplet that satisfies rocket_valid_key(),
	 * so that Settings::sanitize_callback()'s "Settings saved." notice branch is actually
	 * reachable. Without the fix's 'ignore' flag, calling set_cdn_state() with this license
	 * data persisted would queue a spurious "Settings saved." notice.
	 *
	 * @var string
	 */
	private const CONSUMER_EMAIL = 'valid@wp-rocket.test';

	/**
	 * Consumer key paired with self::CONSUMER_EMAIL - must be exactly 8 characters for
	 * rocket_valid_key() to consider it valid.
	 *
	 * @var string
	 */
	private const CONSUMER_KEY = '12345678';

	/**
	 * The sanitize_option_wp_rocket_settings callback registered in set_up(), kept as a
	 * property so the test method can temporarily remove/re-add this exact callback.
	 *
	 * @var callable
	 */
	private $sanitize_callback;

	/**
	 * Sets up the test environment.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Settings::sanitize_callback() calls add_settings_error(), which lives in
		// wp-admin/includes/template.php. That file is only auto-loaded for the AdminOnly
		// test group; load it here explicitly (guarded, since another AdminOnly test running
		// in the same process may already have loaded it) rather than pulling this whole test
		// into the AdminOnly group just for one function.
		if ( ! function_exists( 'add_settings_error' ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}

		// Register the same sanitize_option_{$option} filter that Page::configure() attaches
		// via register_setting() on admin_init, so that update_option( 'wp_rocket_settings', ... )
		// runs through Settings::sanitize_callback() exactly as it does for the real, admin-page
		// write this test is guarding against. We add the filter directly (that's all
		// register_setting() does for this hook) instead of firing the whole admin_init action,
		// to avoid unrelated admin_init side effects in this lightweight FilesystemTestCase.
		$options_api             = new Options( 'wp_rocket_' );
		$options                 = new Options_Data( $options_api->get( 'settings', [] ) );
		$this->sanitize_callback = [ new Settings( $options ), 'sanitize_callback' ];

		add_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );

		// Start each data set from a clean slate: without this, a "Settings saved." notice
		// queued by one data set would still be present for the next, and sanitize_callback()'s
		// own dedup check (skip if a matching notice is already queued) would then mask a
		// regression in later data sets.
		$GLOBALS['wp_settings_errors'] = [];
	}

	/**
	 * Tears down the test environment.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );

		$GLOBALS['wp_settings_errors'] = [];

		parent::tear_down();
	}

	/**
	 * Tests that set_cdn_state() persists cdn_state, strips the internal 'ignore' flag,
	 * and does not queue a "Settings saved." notice.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration: the CDN state to set.
	 * @param array $expected Expected persisted cdn_state.
	 */
	public function testShouldPersistCdnStateWithoutIgnoreOrSpuriousNotice( array $config, array $expected ) {
		$options_api = new Options( 'wp_rocket_' );
		$secret_key  = hash( 'crc32', self::CONSUMER_EMAIL );

		// Seed a valid license with the sanitize_option filter temporarily removed, so this
		// setup write can never itself queue a "Settings saved." notice - only the
		// set_cdn_state() write under test, below, is allowed to.
		remove_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );
		$options_api->set(
			'settings',
			array_merge(
				(array) $options_api->get( 'settings', [] ),
				[
					'secret_key'     => $secret_key,
					'consumer_key'   => self::CONSUMER_KEY,
					'consumer_email' => self::CONSUMER_EMAIL,
				]
			)
		);
		add_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );

		// Snapshot right before running, without reassigning the WP global - only new entries
		// appended by set_cdn_state() itself are relevant to assertion (c) below.
		$notices_before = isset( $GLOBALS['wp_settings_errors'] ) ? (array) $GLOBALS['wp_settings_errors'] : [];

		// Run it.
		$this->getCDNOptionsManager()->set_cdn_state( $config['state'] );

		$settings = $options_api->get( 'settings' );

		// (a) cdn_state is persisted.
		$this->assertArrayHasKey( 'cdn_state', $settings );
		$this->assertSame( $expected['cdn_state'], $settings['cdn_state'] );

		// (b) the internal 'ignore' flag never reaches the DB - it's stripped by
		// Settings::sanitize_callback() before the option is persisted.
		$this->assertArrayNotHasKey( 'ignore', $settings );

		// (c) no "Settings saved." notice was queued for this internal write. The sanitize
		// callback is registered in set_up(), matching what happens on a real admin page
		// load; without the 'ignore' flag set by set_cdn_state(), this assertion fails
		// because rocket_valid_key() is satisfied by the license data seeded above.
		$notices_after = isset( $GLOBALS['wp_settings_errors'] ) ? (array) $GLOBALS['wp_settings_errors'] : [];
		$new_notices   = array_slice( $notices_after, count( $notices_before ) );
		$saved_notices = array_filter(
			$new_notices,
			function ( $error ) {
				return isset( $error['code'] ) && 'settings_updated' === $error['code'];
			}
		);

		$this->assertSame( [], $saved_notices );
	}
}
