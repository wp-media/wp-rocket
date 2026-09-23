<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::save_cloudflare_old_settings
 *
 * @group Cloudflare
 */
class TestSaveCloudflareOldSettings extends TestCase {
	use HttpRequestTrait;
	use IsolateHookTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		$this->unregisterAllCallbacksExcept( 'pre_update_option_wp_rocket_settings', 'save_cloudflare_old_settings' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'pre_update_option_wp_rocket_settings' );

		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$zone_id   = $container->get( 'options' )->get( 'cloudflare_zone_id', '' );

		$this->config['http'] = [
			Client::CLOUDFLARE_API . "zones/{$zone_id}/settings" => $config['response'],
		];

		set_transient( 'rocket_cloudflare_is_api_keys_valid', 1 );

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
}
