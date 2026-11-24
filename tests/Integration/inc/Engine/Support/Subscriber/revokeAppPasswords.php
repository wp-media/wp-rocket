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
	 * Deactivation revokes WP Rocket's chat password and keeps every other one.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Passwords to create, and the library version the case needs.
	 * @param array $expected Exact names, and the passwords kept and revoked.
	 *
	 * @return void
	 */
	public function testShouldRevokeOnlyTheWpRocketChatPassword( $config, $expected ) {
		if ( '' !== $config['min_library_version'] && version_compare( Config::version(), $config['min_library_version'], '<' ) ) {
			$this->markTestSkipped( "Needs wap-client {$config['min_library_version']}, which decides ownership by app_id and names the password as given (wp-media/wp-rocket#9217)." );
		}

		$uuids = $this->create_passwords( $config['passwords'] );

		foreach ( $expected['names'] as $label => $name ) {
			$this->assertSame( $name, WP_Application_Passwords::get_user_application_password( $this->user_id, $uuids[ $label ] )['name'] );
		}

		do_action( 'rocket_deactivation', 0 );

		foreach ( $expected['kept'] as $label ) {
			$this->assertNotNull( WP_Application_Passwords::get_user_application_password( $this->user_id, $uuids[ $label ] ), "The \"{$config['passwords'][ $label ]['name']}\" password must be kept." );
		}

		foreach ( $expected['revoked'] as $label ) {
			$this->assertNull( WP_Application_Passwords::get_user_application_password( $this->user_id, $uuids[ $label ] ), "The \"{$config['passwords'][ $label ]['name']}\" password must be revoked." );
		}
	}

	/**
	 * Creates the case's passwords: chat passwords through the library, the others as the user would.
	 *
	 * @param array $passwords Passwords keyed by label.
	 *
	 * @return array<string, string> Uuids keyed by label.
	 */
	private function create_passwords( array $passwords ): array {
		$manager = new AppPasswordManager();
		$uuids   = [];

		foreach ( $passwords as $label => $password ) {
			if ( isset( $password['product'] ) ) {
				$this->assertIsString( $manager->provision( $this->user_id, $password['product'], $password['name'] ) );
				$uuids[ $label ] = get_user_meta( $this->user_id, AppPasswordGuard::build_meta_key( $password['product'] ), true );
			} else {
				$created = WP_Application_Passwords::create_new_application_password( $this->user_id, [ 'name' => $password['name'] ] );
				$this->assertIsArray( $created );
				$uuids[ $label ] = $created[1]['uuid'];
			}

			$this->assertNotEmpty( $uuids[ $label ] );

			if ( ! empty( $password['backdate'] ) ) {
				$this->backdate( $uuids[ $label ] );
			}
		}

		return $uuids;
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
