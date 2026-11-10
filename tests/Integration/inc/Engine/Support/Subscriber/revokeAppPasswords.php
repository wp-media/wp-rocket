<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Subscriber;

use GroupOne\WapClient\AppPasswordManager;
use WP_Application_Passwords;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::revoke_app_passwords
 *
 * @group Support
 */
class Test_RevokeAppPasswords extends TestCase {
	private $user_id;

	public function set_up() {
		parent::set_up();

		// The bootstrap loads Composer before WordPress defines ABSPATH, so the library's bootstrap
		// returned early and never registered its class map. Run it now, as WP Rocket's load does in
		// production. require, not require_once: PHP already counts the early return as an include.
		if ( ! class_exists( 'WapClient', false ) ) {
			require WP_ROCKET_PLUGIN_ROOT . 'vendor/groupone/wap-client/wap-client.php';
		}

		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );

		add_filter( 'wp_is_application_passwords_available', '__return_true' );
		add_filter( 'wap_client_consent_granted', '__return_true' );

		$this->unregisterAllCallbacksExcept( 'rocket_deactivation', 'revoke_app_passwords' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_deactivation' );

		remove_filter( 'wp_is_application_passwords_available', '__return_true' );
		remove_filter( 'wap_client_consent_granted', '__return_true' );

		parent::tear_down();
	}

	public function testShouldRevokeOnlyTheWpRocketChatPasswords() {
		$manager = new AppPasswordManager();

		$this->assertIsString( $manager->provision( $this->user_id, 'wprocket', 'wp-rocket-chat' ) );
		$this->assertIsString( $manager->provision( $this->user_id, 'imagify', 'other-product-chat' ) );
		$this->assertIsArray( WP_Application_Passwords::create_new_application_password( $this->user_id, [ 'name' => 'Created by the user' ] ) );

		do_action( 'rocket_deactivation', 0 );

		$names = implode( '|', wp_list_pluck( WP_Application_Passwords::get_user_application_passwords( $this->user_id ), 'name' ) );

		$this->assertStringNotContainsString( 'wp-rocket-chat', $names );
		$this->assertStringContainsString( 'other-product-chat', $names );
		$this->assertStringContainsString( 'Created by the user', $names );
	}
}
