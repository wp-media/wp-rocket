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

	// Valid license triplet so rocket_valid_key() is satisfiable.
	private const CONSUMER_EMAIL = 'valid@wp-rocket.test';
	private const CONSUMER_KEY   = '12345678';

	/**
	 * @var callable
	 */
	private $sanitize_callback;

	public function set_up() {
		parent::set_up();

		// add_settings_error() lives in wp-admin/includes/template.php, not auto-loaded here.
		if ( ! function_exists( 'add_settings_error' ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}

		// Register the sanitize_option filter, as register_setting() does on admin_init.
		$options_api             = new Options( 'wp_rocket_' );
		$options                 = new Options_Data( $options_api->get( 'settings', [] ) );
		$this->sanitize_callback = [ new Settings( $options ), 'sanitize_callback' ];

		add_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );

		// Reset so a notice from a previous data set can't mask this one.
		$GLOBALS['wp_settings_errors'] = [];
	}

	public function tear_down() {
		remove_filter( 'sanitize_option_wp_rocket_settings', $this->sanitize_callback );

		$GLOBALS['wp_settings_errors'] = [];

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration: the CDN state to set.
	 * @param array $expected Expected persisted cdn_state.
	 */
	public function testShouldPersistCdnStateWithoutIgnoreOrSpuriousNotice( array $config, array $expected ) {
		$options_api = new Options( 'wp_rocket_' );
		$secret_key  = hash( 'crc32', self::CONSUMER_EMAIL );

		// Seed the license without the sanitize filter, so seeding itself can't queue a notice.
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

		$notices_before = isset( $GLOBALS['wp_settings_errors'] ) ? (array) $GLOBALS['wp_settings_errors'] : [];

		// Run it.
		$this->getCDNOptionsManager()->set_cdn_state( $config['state'] );

		$settings = $options_api->get( 'settings' );

		// (a) cdn_state is persisted.
		$this->assertArrayHasKey( 'cdn_state', $settings );
		$this->assertSame( $expected['cdn_state'], $settings['cdn_state'] );

		// (b) the internal 'ignore' flag never reaches the DB.
		$this->assertArrayNotHasKey( 'ignore', $settings );

		// (c) no "Settings saved." notice was queued for this internal write.
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
