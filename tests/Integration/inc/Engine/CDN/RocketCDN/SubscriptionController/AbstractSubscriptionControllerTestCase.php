<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use WP_Error;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Shared scaffolding for the RocketCDN SubscriptionController integration tests.
 *
 */
abstract class AbstractSubscriptionControllerTestCase extends TestCase {
	use HttpRequestTrait;

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	public const TOKEN = '1234567890123456789012345678901234567890';

	protected $subscription_controller;

	protected $options_api;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		$container = $this->getRocketContainer();

		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );
		$this->options_api             = $container->get( 'options_api' );

		$this->clear_rocketcdn_transients();
		$this->clear_rocketcdn_options();

		$this->reset_frontend_subscriber_memo( $container );

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tear_down() {
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		$this->clear_rocketcdn_transients();
		$this->clear_rocketcdn_options();

		set_current_screen( 'front' );

		$this->restoreWpHook( 'current_screen' );

		$this->tear_down_http();

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}

	protected function getRocketContainer() {
		return apply_filters( 'rocket_container', null );
	}

	protected function clear_rocketcdn_transients(): void {
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'wp_rocket_customer_data' );

		// 'wpr_user_information' is UserClient's own transient key (get_user_data()'s cache); it
		// must be cleared too, otherwise a timeout lockout tripped by an unrelated/earlier test
		// silently short-circuits every get_user_data() call here before it reaches mock_api().
		foreach ( [ 'rocket_cdn_website_search', 'rocket_cdn_create_request', 'rocket_cdn_check_status_request', 'wpr_user_information' ] as $transient_key ) {
			delete_transient( $transient_key );
			delete_transient( $transient_key . '_timeout' );
			delete_transient( $transient_key . '_timeout_active' );
		}
	}

	protected function clear_rocketcdn_options(): void {
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
	}

	/**
	 * Resets the FrontendSubscriber's per-request memoized CDN URL, since it's
	 * a shared container singleton that otherwise leaks state between tests.
	 */
	protected function reset_frontend_subscriber_memo( $container ): void {
		$frontend = $container->get( 'rocketcdn_frontend_subscriber' );

		$this->set_reflective_property( null, 'rocketcdn_url', $frontend );
	}

	/**
	 * Merges the given overrides into the current `wp_rocket_settings` option.
	 */
	protected function update_rocketcdn_settings( array $overrides ): void {
		$settings = array_merge( $this->options_api->get( 'settings', [] ), $overrides );
		$this->options_api->set( 'settings', $settings );
	}

	protected function set_rocketcdn_user_token(): void {
		update_option( 'rocketcdn_user_token', self::TOKEN );
	}

	/**
	 * Overrides cdn/cdn_type/cdn_cnames via the pre_get_rocket_option_*
	 * filters, bypassing any Options_Data singleton caching.
	 *
	 *
	 * @return void
	 */
	protected function set_cdn_option_overrides( int $cdn, string $cdn_type, array $cdn_cnames ): void {
		$cdn_zone = array_fill( 0, count( $cdn_cnames ), 'all' );

		add_filter( 'pre_get_rocket_option_cdn', function () use ( $cdn ) {
			return $cdn;
		}, 5 );
		add_filter( 'pre_get_rocket_option_cdn_type', function () use ( $cdn_type ) {
			return $cdn_type;
		}, 5 );
		add_filter( 'pre_get_rocket_option_cdn_cnames', function () use ( $cdn_cnames ) {
			return $cdn_cnames;
		}, 5 );
		add_filter( 'pre_get_rocket_option_cdn_zone', function () use ( $cdn_zone ) {
			return $cdn_zone;
		}, 5 );
	}

	protected function clear_cdn_option_overrides(): void {
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		remove_all_filters( 'pre_get_rocket_option_cdn_cnames' );
		remove_all_filters( 'pre_get_rocket_option_cdn_zone' );
	}

	/**
	 * Registers the RocketCDN API fixture responses a SubscriptionController call may reach.
	 *
	 * Every URL below is always registered, on the base's 404/empty-body default, so any of
	 * them answers a fixture instead of the network; a genuinely unexpected URL still fails
	 * the test via the trait. A child that expects zero requests (e.g.
	 * isSubscriptionCreationLoading) simply never calls this method, leaving `$config['http']`
	 * empty.
	 *
	 * Recognised config keys:
	 *   subscription_status_code (int, default 404)
	 *   subscription_status_body (array, used when code is 200)
	 *   website_search_code      (int, default 404)
	 *   website_search_body      (array, used when code is 200)
	 *   create_free_code         (int, default 404)
	 *   create_free_body         (array, used when code is 200)
	 *   create_free_error        (string) — returns WP_Error instead of an HTTP response
	 *   task_code                (int, default 404)
	 *   task_body                (array, used when code is 200)
	 *   user_data_code           (int, default 404)
	 *   user_data_body           (array, used when code is 200)
	 */
	protected function mock_api( array $config ): void {
		$this->config['http'] = [
			UserClient::USER_ENDPOINT => $this->fixture_response( $config, 'user_data' ),

			sprintf( '%1$ssubscription/%2$s/status', APIClient::ROCKETCDN_API, 'example.org' ) => $this->fixture_response( $config, 'subscription_status' ),

			add_query_arg( 'url', 'http://example.org', APIClient::ROCKETCDN_API . 'website/search/' ) => $this->fixture_response( $config, 'website_search' ),

			APIClient::ROCKETCDN_API . 'website/create-free/' => isset( $config['create_free_error'] )
				? new WP_Error( 'http_request_failed', $config['create_free_error'] )
				: $this->fixture_response( $config, 'create_free' ),

			sprintf( '%1$swebsite/task/%2$s/', APIClient::ROCKETCDN_API, 'task_abc_123' ) => $this->fixture_response( $config, 'task' ),
		];
	}

	/**
	 * Builds a mocked HTTP response array from a fixture's `{prefix}_code` / `{prefix}_body`
	 * keys, preserving the base's original 200-or-404 semantics.
	 *
	 * @return array
	 */
	private function fixture_response( array $config, string $prefix ): array {
		if ( 200 === ( $config[ $prefix . '_code' ] ?? 404 ) ) {
			return [
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'body'     => json_encode( $config[ $prefix . '_body' ] ?? [] ),
			];
		}

		return [ 'response' => [ 'code' => 404, 'message' => 'Not Found' ], 'body' => '' ];
	}
}
