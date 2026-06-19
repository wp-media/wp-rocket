<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\GetRecommendations;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-recommendations ability registration and output.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterGetRecommendationsAbilityTest extends TestCase {
	/**
	 * Ability ID.
	 */
	private const ABILITY_ID = 'wp-rocket/get-recommendations';

	/**
	 * Transient storing the recommendations data (DataManager::TRANSIENT_NAME).
	 */
	private const TRANSIENT_NAME = 'wpr_ri_recommendations';

	public function set_up() {
		parent::set_up();

		// Skip test if WordPress version is less than 6.9 (Abilities API not available).
		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		delete_transient( self::TRANSIENT_NAME );
	}

	public function tear_down() {
		delete_transient( self::TRANSIENT_NAME );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOutput( $config, $expected ) {
		$this->set_up_user( $config['has_permission'] );

		if ( null !== $config['transient'] ) {
			set_transient( self::TRANSIENT_NAME, $config['transient'] );
		}

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		$result = $ability->execute();

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertArrayHasKey( 'recommendations', $result );

		$this->assertSame( $expected['status'], $result['status'] );
		$this->assertSame( $expected['recommendations'], $result['recommendations'] );
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
