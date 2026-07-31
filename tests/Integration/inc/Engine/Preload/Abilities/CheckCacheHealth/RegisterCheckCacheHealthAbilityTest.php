<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Abilities\CheckCacheHealth;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-cache-health ability registration and execution.
 *
 * @group Preload
 * @group Abilities
 * @group AdminOnly
 */
class RegisterCheckCacheHealthAbilityTest extends TestCase {
	use DBTrait;

	private const ABILITY_ID = 'wp-rocket/get-cache-health';

	/**
	 * Value returned by the manual_preload pre_get_rocket_option filter override.
	 *
	 * @var int
	 */
	private $manual_preload = 0;

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
		remove_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_override' ] );

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

		$result = $ability->execute();

		$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks rocket_manage_options.' );
	}

	public function testShouldReturnCountsMatchingSeededRows(): void {
		$this->set_up_user( true );
		$this->enable_manual_preload();

		self::addCache(
			[
				'url'    => home_url( '/pending-1' ),
				'status' => 'pending',
			]
		);
		self::addCache(
			[
				'url'    => home_url( '/pending-2' ),
				'status' => 'pending',
			]
		);
		self::addCache(
			[
				'url'    => home_url( '/completed-1' ),
				'status' => 'completed',
			]
		);
		self::addCache(
			[
				'url'    => home_url( '/failed-1' ),
				'status' => 'failed',
			]
		);

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute();

		$this->assertSame( 2, $result['counts']['pending'] );
		$this->assertSame( 1, $result['counts']['completed'] );
		$this->assertSame( 1, $result['counts']['failed'] );
		$this->assertSame( 0, $result['counts']['in-progress'] );
		$this->assertTrue( $result['tracking_enabled'] );
		$this->assertTrue( $result['estimate']['is_estimate'] );
		$this->assertIsInt( $result['estimate']['estimated_seconds_remaining'] );
	}

	public function testShouldReturnNullEstimateWhenTrackingDisabled(): void {
		$this->set_up_user( true );
		$this->disable_manual_preload();

		self::addCache(
			[
				'url'    => home_url( '/pending-only' ),
				'status' => 'pending',
			]
		);

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute();

		$this->assertFalse( $result['tracking_enabled'] );
		$this->assertNull( $result['estimate']['estimated_seconds_remaining'] );
	}

	/**
	 * Enables the manual_preload option for the duration of the current test.
	 *
	 * The ability's Options_Data dependency is built once by the DI container at plugin
	 * bootstrap from a snapshot of the wp_rocket_settings option; it does not re-read
	 * get_option() afterwards. Updating the option mid-test (e.g. via SettingsTrait's
	 * mergeExistingSettingsAndUpdate()) therefore has no effect on
	 * Options_Data::get( 'manual_preload' ) here. Overriding the
	 * pre_get_rocket_option_manual_preload filter is the established pattern this codebase
	 * uses instead — see tests/Integration/inc/Engine/Preload/Subscriber/*.php and
	 * tests/Integration/inc/Engine/HealthCheck/HealthCheck/missedCron.php.
	 *
	 * @return void
	 */
	private function enable_manual_preload(): void {
		$this->manual_preload = 1;
		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_override' ] );
	}

	/**
	 * Disables the manual_preload option for the duration of the current test.
	 *
	 * @return void
	 */
	private function disable_manual_preload(): void {
		$this->manual_preload = 0;
		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload_override' ] );
	}

	/**
	 * Filter callback returning the overridden manual_preload value.
	 *
	 * @return int
	 */
	public function manual_preload_override() {
		return $this->manual_preload;
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
