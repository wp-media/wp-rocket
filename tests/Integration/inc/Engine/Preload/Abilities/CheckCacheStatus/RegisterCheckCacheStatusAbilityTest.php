<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Abilities\CheckCacheStatus;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-cache-status ability registration and execution.
 *
 * @group Preload
 * @group Abilities
 * @group AdminOnly
 */
class RegisterCheckCacheStatusAbilityTest extends TestCase {
	use DBTrait;

	private const ABILITY_ID = 'wp-rocket/get-cache-status';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPreloadCacheTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPreloadCacheTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		self::truncatePreloadCacheTable();
	}

	public function tear_down() {
		self::truncatePreloadCacheTable();

		parent::tear_down();
	}

	public function testAbilityIsRegistered(): void {
		$this->set_up_user( true );

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered' );
	}

	public function testShouldReturnWpErrorWithoutPermission(): void {
		$this->set_up_user( false );

		$ability = wp_get_ability( self::ABILITY_ID );
		$this->assertNotNull( $ability );

		$result = $ability->execute( [ 'url' => home_url( '/some-page' ) ] );

		$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks rocket_manage_options.' );
	}

	public function testShouldReturnTrackedTrueWhenPreloadEnabledAndRowExists(): void {
		$this->set_up_user( true );
		$this->enable_manual_preload();

		$url = home_url( '/tracked-page' );

		self::addCache(
			[
				'url'    => $url,
				'status' => 'completed',
			]
		);

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute( [ 'url' => $url ] );

		$this->assertTrue( $result['tracked'] );
		$this->assertSame( 'completed', $result['status'] );
		$this->assertNull( $result['error'] );
	}

	public function testShouldReturnNotTrackedWhenPreloadDisabled(): void {
		$this->set_up_user( true );
		$this->disable_manual_preload();

		$url = home_url( '/untracked-page' );

		self::addCache(
			[
				'url'    => $url,
				'status' => 'completed',
			]
		);

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute( [ 'url' => $url ] );

		$this->assertFalse( $result['tracked'] );
		$this->assertNull( $result['status'] );
		$this->assertNotEmpty( $result['note'] );
	}

	public function testShouldReturnErrorWhenMultipleIdentifiersSupplied(): void {
		$this->set_up_user( true );

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute(
			[
				'url'     => home_url( '/some-page' ),
				'post_id' => 1,
			]
		);

		$this->assertNotEmpty( $result['error'] );
		$this->assertNull( $result['resolved_url'] );
	}

	/**
	 * Enables the manual_preload option.
	 *
	 * Uses the shared SettingsTrait helper (already wired through TestCase) instead of a
	 * raw get_option()/update_option() call, which the DiscourageWPOptionUsage PHPStan rule
	 * flags for any file under tests/Integration/.
	 *
	 * @return void
	 */
	private function enable_manual_preload(): void {
		$this->mergeExistingSettingsAndUpdate( [ 'manual_preload' => 1 ] );
	}

	/**
	 * Disables the manual_preload option.
	 *
	 * @return void
	 */
	private function disable_manual_preload(): void {
		$this->mergeExistingSettingsAndUpdate( [ 'manual_preload' => 0 ] );
	}

	/**
	 * Set up user with or without permission.
	 *
	 * @param bool $has_permission Whether user should have permission.
	 *
	 * @return void
	 */
	private function set_up_user( bool $has_permission ): void {
		$admin = get_role( 'administrator' );

		if ( $has_permission ) {
			$admin->add_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$admin->remove_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		}

		wp_set_current_user( $user_id );
	}
}
