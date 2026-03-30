<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Options\SetOption;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration tests for wp-rocket/set-options ability execution.
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
	private const ABILITY_ID = 'wp-rocket/set-options';

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

		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API is not available.' );
		}

		// Filter for mocking get_rocket_option results.
		add_filter( 'pre_get_rocket_option', [ $this, 'filter_get_option' ], 10, 3 );
	}

	/**
	 * Tear down the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'pre_get_rocket_option', [ $this, 'filter_get_option' ] );

		parent::tear_down();
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
	 * Filter get_rocket_option to return test settings.
	 *
	 * @param mixed  $value   Pre-filter value.
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function filter_get_option( $value, $option, $default ) {
		$settings = $this->config['settings'];

		if ( isset( $settings[ $option ] ) ) {
			return $settings[ $option ];
		}

		return $default;
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
