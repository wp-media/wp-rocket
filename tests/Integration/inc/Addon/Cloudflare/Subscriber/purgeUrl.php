<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::purge_url
 *
 * @group Cloudflare
 */
class TestPurgeUrl extends TestCase {
	use IsolateHookTrait;

	private $page_rule_response;
	private $purge_response;
	private $purge_request_body;
	private $hook;

	public function set_up() {
		parent::set_up();

		set_transient( 'rocket_cloudflare_is_api_keys_valid', 1 );

		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_purge_cloudflare_cache' );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'http_request' ] );

		if ( $this->hook ) {
			$this->restoreWpHook( $this->hook );
			$this->hook = null;
		}

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

		$this->purge_request_body = null;

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->page_rule_response = $config['page_rule_response'];
		$this->purge_response     = $config['purge_response'];
		$this->hook               = $config['hook'];

		if ( $config['cap'] ) {
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );

		$this->unregisterAllCallbacksExcept( $config['hook'], 'purge_url' );

		add_filter( 'pre_http_request', [ $this, 'http_request' ], 10, 3 );

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

	public function http_request( $preempt, $args, $url ) {
		if ( false !== strpos( $url, 'pagerules' ) ) {
			return $this->page_rule_response;
		}

		if ( false !== strpos( $url, 'purge_cache' ) ) {
			$this->purge_request_body = json_decode( $args['body'], true );

			return $this->purge_response;
		}

		return $preempt;
	}
}
