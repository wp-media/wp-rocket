<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::purge_url
 *
 * @group Cloudflare
 */
class TestPurgeUrl extends TestCase {
	use IsolateHookTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	private $hook;

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );

		set_transient( 'rocket_cloudflare_is_api_keys_valid', 1 );

		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_purge_cloudflare_cache' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );

		if ( $this->hook ) {
			$this->restoreWpHook( $this->hook );
			$this->hook = null;
		}

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

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

		do_action( $config['hook'], ...$config['args'] );

		$purges = $this->get_http_requests( $base . 'purge_cache' );

		if ( null === $expected ) {
			$this->assertSame( [], $purges );

			return;
		}

		$body = json_decode( end( $purges )['args']['body'], true );

		$this->assertSame(
			$expected,
			array_values( $body['files'] )
		);
	}
}
