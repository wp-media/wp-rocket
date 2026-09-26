<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::purge_url
 *
 * @group Cloudflare
 */
class TestPurgeUrl extends TestCase {
	use HttpRequestTrait;
	use IsolateHookTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	private $purge_request_body;
	private $hook;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		add_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );

		set_transient( 'rocket_cloudflare_is_api_keys_valid', 1 );

		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_purge_cloudflare_cache' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );
		remove_filter( 'pre_http_request', [ $this, 'record_purge_request' ], 5 );

		if ( $this->hook ) {
			$this->restoreWpHook( $this->hook );
			$this->hook = null;
		}

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

		$this->purge_request_body = null;

		$this->tear_down_http();

		parent::tear_down();
	}

	/** Forces the Cloudflare zone ID option to a fixed value for the mocked HTTP fixtures. */
	public function mock_cloudflare_zone_id() {
		return '12345';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->hook = $config['hook'];

		$base = Client::CLOUDFLARE_API . 'zones/12345/';

		$this->config['http'] = [
			$base . 'pagerules?status=active' => $config['page_rule_response'],
			$base . 'purge_cache'              => $config['purge_response'],
		];

		if ( $config['cap'] ) {
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );

		$this->unregisterAllCallbacksExcept( $config['hook'], 'purge_url' );

		add_filter( 'pre_http_request', [ $this, 'record_purge_request' ], 5, 3 );

		do_action( $config['hook'], ...$config['args'] );

		if ( null === $expected ) {
			$this->assertNull( $this->purge_request_body );

			return;
		}

		$this->assertSame(
			$expected,
			array_values( $this->purge_request_body['files'] )
		);
	}

	/**
	 * Records the purge_cache request body so the test can assert on it, without short-circuiting
	 * the request: the trait still answers it via the fixture at a lower priority.
	 *
	 * @param false|array|\WP_Error $preempt Preemptive response.
	 * @param array                 $args    Request arguments.
	 * @param string                $url     Requested URL.
	 *
	 * @return false|array|\WP_Error
	 */
	public function record_purge_request( $preempt, $args, $url ) {
		if ( false !== strpos( $url, 'purge_cache' ) ) {
			$this->purge_request_body = json_decode( $args['body'], true );
		}

		return $preempt;
	}
}
