<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::save_cloudflare_old_settings
 *
 * @group Cloudflare
 */
class TestSaveCloudflareOldSettings extends TestCase {
	use IsolateHookTrait;
	private $response;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('pre_update_option_wp_rocket_settings', 'save_cloudflare_old_settings');
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'http_request'] );
		$this->restoreWpHook('pre_update_option_wp_rocket_settings');

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->response = $config['response'];

		set_transient( 'rocket_cloudflare_is_api_keys_valid', 1 );

		add_filter( 'pre_http_request', [ $this, 'http_request'] );

		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		if ( $config['cap'] ) {
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );

		$this->assertSame(
			$expected,
			apply_filters( 'pre_update_option_wp_rocket_settings', $config['value'], $config['old_value'] )
		);
	}

	public function http_request() {
		return $this->response;
	}
}
