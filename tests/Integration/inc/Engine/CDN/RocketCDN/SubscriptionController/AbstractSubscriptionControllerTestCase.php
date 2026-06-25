<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Shared scaffolding for the RocketCDN SubscriptionController integration tests.
 *
 * Scenario-specific setup (the `wp_rocket_settings` shape being simulated, the
 * `cdn_context`/`cdn_render_controller` services, the RocketCDN DB table, the
 * `rocket_buffer` hook isolation) is intentionally left to each subclass, since
 * those vary per test and forcing them here would hide what each test actually
 * relies on.
 *
 */
abstract class AbstractSubscriptionControllerTestCase extends TestCase {

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

		$container = $this->getRocketContainer();

		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );
		$this->options_api             = $container->get( 'options_api' );

		$this->clear_rocketcdn_transients();
		$this->clear_rocketcdn_options();

		$this->reset_frontend_subscriber_memo( $container );

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		$this->clear_rocketcdn_transients();
		$this->clear_rocketcdn_options();

		set_current_screen( 'front' );

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
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_transient( 'wp_rocket_customer_data' );
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
		$prop     = new ReflectionProperty( $frontend, 'rocketcdn_url' );
		$prop->setValue( $frontend, null );
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
}
