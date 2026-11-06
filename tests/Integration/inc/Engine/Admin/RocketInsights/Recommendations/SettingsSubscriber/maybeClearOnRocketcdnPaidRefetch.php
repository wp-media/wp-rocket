<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as RocketCDNTestCase;

/**
 * Test class covering a recommendations refetch in the same request as the settings write that clears
 * the cache in SettingsSubscriber::maybe_clear_on_rocketcdn_paid().
 *
 * The DataManager holds an Options_Data snapshot taken before the write, so the refetch must still
 * resolve the new cdn_state.
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_MaybeClearOnRocketcdnPaidRefetch extends RocketCDNTestCase {
	use DBTrait;

	/**
	 * Recommendations transient name.
	 *
	 * @var string
	 */
	private const TRANSIENT = 'wpr_ri_recommendations';

	/**
	 * HTTP responses mocked for the test.
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Subscribers wired for the test, as admin subscribers are not registered by the test bootstrap.
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
	 * Installs the Performance Monitoring table read while building the API parameters.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPerformanceMonitoringTable();
	}

	/**
	 * Uninstalls the Performance Monitoring table.
	 */
	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		global $wp_filter;
		$this->original_settings_hook                  = $wp_filter['update_option_wp_rocket_settings'] ?? null;
		$wp_filter['update_option_wp_rocket_settings'] = new \WP_Hook(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$container           = apply_filters( 'rocket_container', null );
		$this->event_manager = $container->get( 'event_manager' );
		$this->subscribers   = [
			$container->get( 'ri_recommendations_settings_subscriber' ),
			$container->get( 'rocketcdn_frontend_subscriber' ),
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
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );

		global $wp_filter;
		if ( null === $this->original_settings_hook ) {
			unset( $wp_filter['update_option_wp_rocket_settings'] );
		} else {
			$wp_filter['update_option_wp_rocket_settings'] = $this->original_settings_hook; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		parent::tear_down();
	}

	/**
	 * Test that a refetch after the clear, in the same request, reads the fresh cdn_state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	public function testShouldRefetchWithFreshCdnState( $config, $expected ) {
		$this->config['http'] = [
			'https://saas.wp-rocket.me/recommendations/' => [
				'body'     => wp_json_encode(
					[
						'recommendations' => [],
						'metadata'        => [],
					]
				),
				'response' => [ 'code' => 200 ],
			],
		];

		add_filter(
			'pre_get_rocket_option_consumer_email',
			function () {
				return 'test@example.org';
			}
		);

		$container    = apply_filters( 'rocket_container', null );
		$options_api  = $container->get( 'options_api' );
		$old_settings = array_merge(
			$options_api->get( 'settings', [] ),
			[
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			]
		);

		$options_api->set( 'settings', $old_settings );

		// Built before the write, so its Options_Data snapshot does not hold the new cdn_state.
		$data_manager = $container->get( 'ri_recommendations_data_manager' );

		update_option( 'rocketcdn_user_token', 'token' );
		set_transient( 'rocketcdn_status', $config['subscription'], HOUR_IN_SECONDS );

		set_transient(
			self::TRANSIENT,
			[
				'status'    => 'completed',
				'timestamp' => time(),
				'data'      => [],
			],
			HOUR_IN_SECONDS
		);

		$options_api->set( 'settings', array_merge( $old_settings, [ 'cdn_state' => $config['new_cdn_state'] ] ) );

		$snapshot = $this->getNonPublicPropertyValue( 'options', DataManager::class, $data_manager );
		$this->assertNotSame( 'rocketcdn_paid', $snapshot->get_options()['cdn_state'] ?? '', 'The DataManager snapshot must not hold the new cdn_state.' );

		if ( $expected['transient_cleared'] ) {
			$this->assertFalse( get_transient( self::TRANSIENT ) );
		} else {
			$this->assertNotFalse( get_transient( self::TRANSIENT ) );
		}

		$sent_params = [];

		add_filter(
			'rocket_insights_api_recommendations_params',
			function ( $params ) use ( &$sent_params ) {
				$sent_params = $params;

				return $params;
			},
			PHP_INT_MAX
		);

		$this->assertTrue( $data_manager->fetch_recommendations() );
		$this->assertSame( 'completed', get_transient( self::TRANSIENT )['status'] );
		$this->assertSame(
			$expected['sends_rocketcdn'],
			in_array( 'plugin_rocketcdn', $sent_params['enabled_options'], true )
		);
	}
}
