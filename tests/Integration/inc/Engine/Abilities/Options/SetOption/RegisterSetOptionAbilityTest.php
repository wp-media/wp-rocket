<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Options\SetOption;

use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration tests for wp-rocket/set-option ability execution.
 *
 * @group Abilities
 */
class RegisterSetOptionAbilityTest extends TestCase {
	/**
	 * Minimum WordPress version required.
	 */
	private const MIN_WP_VERSION = '6.9';

	/**
	 * Ability ID.
	 */
	private const ABILITY_ID = 'wp-rocket/set-option';

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	public function set_up() {
		global $wp_version;

		parent::set_up();

		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API requires WordPress ' . self::MIN_WP_VERSION . ' or higher.' );
		}
	}

	/**
	 * Test ability execution with various scenarios.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		if ( array_key_exists( 'action', $config ) ) {
			$this->assertCacheClearingBehavior( $config, $expected );
			return;
		}

		$this->set_up_user( $config['has_permission'] );

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered.' );

		$result = $ability->execute( $config['input'] );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		$this->assertIsArray( $result, 'Should return array.' );
		$this->assertSame( $expected['success'], $result['success'], 'Success status should match.' );

		if ( $expected['success'] ) {
			$this->assertArrayHasKey( 'previous_value', $result, 'Should have previous_value.' );
			$this->assertArrayHasKey( 'new_value', $result, 'Should have new_value.' );
			$this->assertSame( $expected['previous_value'], $result['previous_value'], 'Previous value should match.' );
			$this->assertSame( $expected['new_value'], $result['new_value'], 'New value should match.' );
		} else {
			$this->assertArrayHasKey( 'error', $result, 'Should have error message.' );
			$this->assertSame( $expected['error'], $result['error'], 'Error message should match.' );
		}
	}

	/**
	 * Asserts the cache-clearing behavior of an option change made outside wp-admin, exercising
	 * the same hook path (rocket_after_save_options()) a wp-admin settings save uses.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	private function assertCacheClearingBehavior( array $config, array $expected ): void {
		$before = did_action( 'rocket_options_changed' );
		$result = null;

		switch ( $config['action'] ) {
			case 'update_rocket_option':
				update_rocket_option( $config['option_name'], $config['option_value'] );
				break;

			case 'set_option_execute':
				$result = ( new SetOption( new AllowedOptions() ) )->execute(
					[
						'option_name'  => $config['option_name'],
						'option_value' => $config['option_value'],
					]
				);
				break;
		}

		if ( array_key_exists( 'is_admin', $expected ) ) {
			$this->assertSame( $expected['is_admin'], is_admin() );
		}

		if ( array_key_exists( 'hooked', $expected ) ) {
			$this->assertSame( $expected['hooked'], (bool) has_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' ) );
		}

		if ( array_key_exists( 'success', $expected ) ) {
			$this->assertSame( $expected['success'], $result['success'] );
		}

		if ( array_key_exists( 'options_changed_fired', $expected ) ) {
			$this->assertSame( $expected['options_changed_fired'], did_action( 'rocket_options_changed' ) > $before );
		}
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
