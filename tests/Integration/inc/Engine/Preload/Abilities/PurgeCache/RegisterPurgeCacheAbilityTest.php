<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Abilities\PurgeCache;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/clear-cache ability registration and execution.
 *
 * @group Preload
 * @group Abilities
 * @group AdminOnly
 */
class RegisterPurgeCacheAbilityTest extends TestCase {
	private const ABILITY_ID = 'wp-rocket/clear-cache';

	public function set_up() {
		parent::set_up();

		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}
	}

	public function testAbilityIsRegistered(): void {
		$this->set_up_user( true );

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered' );
	}

	public function testShouldReturnWpErrorWithoutPurgeCacheCapability(): void {
		$this->set_up_user( false );

		$ability = wp_get_ability( self::ABILITY_ID );
		$this->assertNotNull( $ability );

		$result = $ability->execute( [ 'scope' => 'domain' ] );

		$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks rocket_purge_cache.' );
	}

	public function testShouldDenyAccessEvenWithRocketManageOptionsOnly(): void {
		// rocket_purge_cache is a distinct capability from rocket_manage_options:
		// a user with only the latter must still be denied.
		$admin = get_role( 'administrator' );
		$admin->remove_cap( 'rocket_purge_cache' );
		$admin->add_cap( 'rocket_manage_options' );
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute( [ 'scope' => 'domain' ] );

		$this->assertInstanceOf( 'WP_Error', $result );

		$admin->add_cap( 'rocket_purge_cache' );
	}

	public function testShouldClearPostScopeEndToEnd(): void {
		$this->set_up_user( true );

		$post_id = self::factory()->post->create(
			[
				'post_title'  => 'Cache clear test post',
				'post_status' => 'publish',
			]
		);

		Functions\expect( 'rocket_clean_post' )
			->once()
			->with( $post_id );

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute(
			[
				'scope'   => 'post',
				'post_id' => $post_id,
			]
		);

		$this->assertTrue( $result['accepted'] );
		$this->assertSame( 'post', $result['scope'] );
		$this->assertNull( $result['error'] );
	}

	public function testShouldRejectPostScopeWhenPostDoesNotExist(): void {
		$this->set_up_user( true );

		Functions\expect( 'rocket_clean_post' )->never();

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute(
			[
				'scope'   => 'post',
				'post_id' => PHP_INT_MAX - 1,
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldClearDomainScopeEndToEnd(): void {
		$this->set_up_user( true );

		Functions\expect( 'rocket_clean_domain' )
			->once()
			->with( '' );

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute( [ 'scope' => 'domain' ] );

		$this->assertTrue( $result['accepted'] );
	}

	public function testShouldSucceedEvenWhenNoProblemDetected(): void {
		// No precondition check: clearing must succeed unconditionally.
		$this->set_up_user( true );

		Functions\expect( 'rocket_clean_domain' )->once();

		$ability = wp_get_ability( self::ABILITY_ID );
		$result  = $ability->execute( [ 'scope' => 'domain' ] );

		$this->assertTrue( $result['accepted'] );
	}

	/**
	 * Set up user with or without the rocket_purge_cache capability.
	 *
	 * @param bool $has_permission Whether user should have permission.
	 *
	 * @return void
	 */
	private function set_up_user( bool $has_permission ): void {
		$admin = get_role( 'administrator' );

		if ( $has_permission ) {
			$admin->add_cap( 'rocket_purge_cache' );
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$admin->remove_cap( 'rocket_purge_cache' );
			$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		}

		wp_set_current_user( $user_id );
	}
}
