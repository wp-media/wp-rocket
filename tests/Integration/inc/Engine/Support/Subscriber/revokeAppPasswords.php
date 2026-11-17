<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Subscriber;

use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordGuard;
use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordManager;
use WP_Rocket\Dependencies\GroupOne\WapClient\Config;
use WP_Application_Passwords;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::revoke_app_passwords
 *
 * @group Support
 */
class Test_RevokeAppPasswords extends TestCase {
	/**
	 * Administrator user id.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Creates an administrator and enables Application Passwords and consent.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );

		add_filter( 'wp_is_application_passwords_available', '__return_true' );
		add_filter( 'wap_client_consent_granted', '__return_true' );

		$this->unregisterAllCallbacksExcept( 'rocket_deactivation', 'revoke_app_passwords' );
	}

	/**
	 * Restores the hooks and filters changed in set_up().
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->restoreWpHook( 'rocket_deactivation' );

		remove_filter( 'wp_is_application_passwords_available', '__return_true' );
		remove_filter( 'wap_client_consent_granted', '__return_true' );

		parent::tear_down();
	}

	/**
	 * Only WP Rocket's chat password is revoked on deactivation.
	 *
	 * @return void
	 */
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

	/**
	 * Passwords the user created are kept, including one named like the chat's.
	 *
	 * @return void
	 */
	public function testShouldKeepTheUsersOwnPasswordsEvenWhenNamedLikeTheChatOnes() {
		$this->skipBeforeOwnershipByAppId();

		$manager = new AppPasswordManager();

		$this->assertIsString( $manager->provision( $this->user_id, 'wprocket', 'WP Rocket AI Assistant' ) );
		$own = WP_Application_Passwords::create_new_application_password( $this->user_id, [ 'name' => 'WAP – my staging deploy key' ] );
		$this->assertIsArray( $own );
		$plain = WP_Application_Passwords::create_new_application_password( $this->user_id, [ 'name' => 'plain key' ] );
		$this->assertIsArray( $plain );

		$this->backdate( $own[1]['uuid'] );
		$this->backdate( $plain[1]['uuid'] );

		do_action( 'rocket_deactivation', 0 );

		$names = wp_list_pluck( WP_Application_Passwords::get_user_application_passwords( $this->user_id ), 'name', 'uuid' );

		$this->assertSame( 'WAP – my staging deploy key', $names[ $own[1]['uuid'] ] ?? null, 'The user\'s own "WAP – " password must be kept.' );
		$this->assertSame( 'plain key', $names[ $plain[1]['uuid'] ] ?? null );
	}

	/**
	 * The chat password gets the configured name and is the only one of WP Rocket's revoked.
	 *
	 * @return void
	 */
	public function testShouldNameTheChatPasswordExactlyAndRevokeIt() {
		$this->skipBeforeOwnershipByAppId();

		$manager = new AppPasswordManager();

		$this->assertIsString( $manager->provision( $this->user_id, 'wprocket', 'WP Rocket AI Assistant' ) );
		$this->assertIsString( $manager->provision( $this->user_id, 'imagify', 'Imagify Assistant' ) );

		$rocket_uuid = get_user_meta( $this->user_id, AppPasswordGuard::build_meta_key( 'wprocket' ), true );
		$other_uuid  = get_user_meta( $this->user_id, AppPasswordGuard::build_meta_key( 'imagify' ), true );

		$this->assertNotEmpty( $rocket_uuid );
		$this->assertNotEmpty( $other_uuid );

		$item = WP_Application_Passwords::get_user_application_password( $this->user_id, $rocket_uuid );
		$this->assertSame( 'WP Rocket AI Assistant', $item['name'] );

		do_action( 'rocket_deactivation', 0 );

		$this->assertNull( WP_Application_Passwords::get_user_application_password( $this->user_id, $rocket_uuid ), 'WP Rocket\'s chat password must be revoked.' );
		$this->assertNotNull( WP_Application_Passwords::get_user_application_password( $this->user_id, $other_uuid ), 'Another product\'s chat password must be kept.' );
	}

	/**
	 * Skips on libraries older than the one deciding ownership by app_id.
	 *
	 * @return void
	 */
	private function skipBeforeOwnershipByAppId(): void {
		if ( version_compare( Config::version(), '3.1.0', '<' ) ) {
			$this->markTestSkipped( 'Needs wap-client 3.1.0, which decides ownership by app_id and names the password as given (wp-media/wp-rocket#9217).' );
		}
	}

	/**
	 * Moves the password creation time two hours back by writing the user meta, as core cannot change it.
	 *
	 * @param string $uuid Application Password uuid.
	 *
	 * @return void
	 */
	private function backdate( string $uuid ): void {
		$items = WP_Application_Passwords::get_user_application_passwords( $this->user_id );

		foreach ( $items as $key => $item ) {
			if ( $item['uuid'] === $uuid ) {
				$items[ $key ]['created'] = time() - 2 * HOUR_IN_SECONDS;
			}
		}

		update_user_meta( $this->user_id, WP_Application_Passwords::USERMETA_KEY_APPLICATION_PASSWORDS, $items );

		$this->assertGreaterThan( 60, time() - WP_Application_Passwords::get_user_application_password( $this->user_id, $uuid )['created'] );
	}
}
