<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Tests the dispatch chain: after_rocket_clean_home and after_rocket_clean_files
 * hooks fire auto_purge() via the Cloudflare Subscriber.
 *
 * @group Cloudflare
 */
class TestAutoPurge extends TestCase {

	/**
	 * Whether a Cloudflare purge HTTP call was recorded.
	 *
	 * @var bool
	 */
	private $purge_called = false;

	public function set_up() {
		parent::set_up();

		// Isolate: only the auto_purge callback should run on these hooks.
		$this->unregisterAllCallbacksExcept( 'after_rocket_clean_home', 'auto_purge' );
		$this->unregisterAllCallbacksExcept( 'after_rocket_clean_files', 'auto_purge' );

		// Set up WP Rocket options with valid Cloudflare credentials.
		update_option(
			'wp_rocket_settings',
			[
				'cloudflare_email'   => 'test@example.com',
				'cloudflare_api_key' => 'test_api_key_12345',
				'cloudflare_zone_id' => 'test_zone_id_abc',
			]
		);

		// Pre-set the connection transient to bypass the zones HTTP call.
		set_transient( 'rocket_cloudflare_is_api_keys_valid', true, 2 * WEEK_IN_SECONDS );

		// Grant the administrator role the purge capability.
		$role = get_role( 'administrator' );
		if ( $role ) {
			$role->add_cap( 'rocket_purge_cloudflare_cache' );
		}

		// Create and set an admin user as the current user.
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'intercept_http_request' ] );

		$this->restoreWpHook( 'after_rocket_clean_home' );
		$this->restoreWpHook( 'after_rocket_clean_files' );

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

		$role = get_role( 'administrator' );
		if ( $role ) {
			$role->remove_cap( 'rocket_purge_cloudflare_cache' );
		}

		wp_set_current_user( 0 );
		$this->purge_called = false;

		parent::tear_down();
	}

	/**
	 * Fires after_rocket_clean_home and asserts that auto_purge() executes
	 * far enough to call the Cloudflare purge endpoint.
	 */
	public function testShouldCallAutoPurgeWhenAfterRocketCleanHomeActionFires() {
		$this->purge_called = false;

		add_filter( 'pre_http_request', [ $this, 'intercept_http_request' ], 10, 3 );

		do_action( 'after_rocket_clean_home' );

		$this->assertTrue(
			$this->purge_called,
			'Expected Cloudflare purge to be called when after_rocket_clean_home fires'
		);
	}

	/**
	 * Fires after_rocket_clean_files and asserts that auto_purge() executes
	 * far enough to call the Cloudflare purge endpoint.
	 */
	public function testShouldCallAutoPurgeWhenAfterRocketCleanFilesActionFires() {
		$this->purge_called = false;

		add_filter( 'pre_http_request', [ $this, 'intercept_http_request' ], 10, 3 );

		do_action( 'after_rocket_clean_files' );

		$this->assertTrue(
			$this->purge_called,
			'Expected Cloudflare purge to be called when after_rocket_clean_files fires'
		);
	}

	/**
	 * Intercepts outgoing HTTP requests from auto_purge() and returns mocked responses.
	 *
	 * Routes by URL:
	 *  - pagerules endpoint → returns a page rule containing 'cache_everything'
	 *  - purge_cache endpoint → sets the $purge_called flag and returns success
	 *
	 * @param false|array $preempt    False to continue, or response array to preempt.
	 * @param array       $args      Request arguments.
	 * @param string      $url       Request URL.
	 *
	 * @return false|array
	 */
	public function intercept_http_request( $preempt, $args, $url ) {
		if ( false !== strpos( $url, 'pagerules' ) ) {
			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					(object) [
						'success' => true,
						'result'  => [
							(object) [
								'actions' => [
									(object) [
										'id'    => 'cache_everything',
										'value' => 'cache_everything',
									],
								],
							],
						],
					]
				),
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'cookies'  => [],
				'filename' => '',
			];
		}

		if ( false !== strpos( $url, 'purge_cache' ) ) {
			$this->purge_called = true;

			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					(object) [
						'success' => true,
						'result'  => (object) [],
					]
				),
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'cookies'  => [],
				'filename' => '',
			];
		}

		return $preempt;
	}
}
