<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as RocketCDNTestCase;

/**
 * Test class covering the chain DataManagerSubscriber::maybe_sync_cdn_state() ->
 * wp_rocket_settings update -> SettingsSubscriber::maybe_clear_on_rocketcdn_paid().
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_MaybeClearOnRocketcdnPaidChain extends RocketCDNTestCase {
	/**
	 * Recommendations transient name.
	 *
	 * @var string
	 */
	private const TRANSIENT = 'wpr_ri_recommendations';

	/**
	 * Subscribers wired for the chain, as admin subscribers are not registered by the test bootstrap.
	 *
	 * @var array
	 */
	private $subscribers = [];

	/**
	 * Original settings update hook, restored after each test.
	 *
	 * @var \WP_Hook|null
	 */
	private $original_settings_hook;

	/**
	 * Event manager instance.
	 *
	 * @var \WP_Rocket\Event_Management\Event_Manager
	 */
	private $event_manager;

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		// Isolate the settings update hook from unrelated CDN listeners that need their own DB tables.
		global $wp_filter;
		$this->original_settings_hook                  = $wp_filter['update_option_wp_rocket_settings'] ?? null;
		$wp_filter['update_option_wp_rocket_settings'] = new \WP_Hook(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		get_role( 'administrator' )->add_cap( 'rocket_manage_options' );

		$container           = apply_filters( 'rocket_container', null );
		$this->event_manager = $container->get( 'event_manager' );
		$this->subscribers   = [
			$container->get( 'rocketcdn_data_manager_subscriber' ),
			$container->get( 'ri_recommendations_settings_subscriber' ),
		];

		foreach ( $this->subscribers as $subscriber ) {
			$this->event_manager->remove_subscriber( $subscriber );
			$this->event_manager->add_subscriber( $subscriber );
		}
	}

	/**
	 * Tear down after each test.
	 */
	public function tear_down() {
		foreach ( $this->subscribers as $subscriber ) {
			$this->event_manager->remove_subscriber( $subscriber );
		}

		delete_transient( self::TRANSIENT );
		global $wp_filter;
		if ( null === $this->original_settings_hook ) {
			unset( $wp_filter['update_option_wp_rocket_settings'] );
		} else {
			$wp_filter['update_option_wp_rocket_settings'] = $this->original_settings_hook; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		$this->restoreWpHook( 'current_screen' );

		parent::tear_down();
	}

	/**
	 * Test that a subscription plan sync persists the state and clears the cached recommendations only on a real upgrade.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	public function testShouldSyncStateAndClearRecommendationsAsExpected( $config, $expected ) {
		wp_set_current_user( self::factory()->user->create( [ 'role' => $config['role'] ] ) );
		set_current_screen( 'settings_page_wprocket' );

		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = $config['initial_cdn_state'];
		update_option( 'wp_rocket_settings', $settings );

		set_transient(
			self::TRANSIENT,
			[
				'status'    => 'completed',
				'timestamp' => time(),
				'data'      => [],
			],
			HOUR_IN_SECONDS
		);

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core transient hook.
		do_action( 'set_transient_rocketcdn_status', $config['transient_value'] );

		$this->assertSame( $expected['cdn_state'], get_option( 'wp_rocket_settings' )['cdn_state'] );

		if ( $expected['transient_cleared'] ) {
			$this->assertFalse( get_transient( self::TRANSIENT ) );
		} else {
			$this->assertNotFalse( get_transient( self::TRANSIENT ) );
		}
	}
}
