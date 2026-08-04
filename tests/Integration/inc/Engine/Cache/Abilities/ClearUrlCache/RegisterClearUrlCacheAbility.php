<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Abilities\ClearUrlCache;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering wp-rocket/clear-url-cache ability registration & execution.
 *
 * @group Abilities
 * @group Cache
 * @group AdminOnly
 */
class Test_RegisterClearUrlCacheAbility extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Abilities/ClearUrlCache/RegisterClearUrlCacheAbility.php';

	/**
	 * Minimum WordPress version required for the Abilities API.
	 */
	private const MIN_WP_VERSION = '6.9';

	/**
	 * Ability ID.
	 */
	private const ABILITY_ID = 'wp-rocket/clear-url-cache';

	public function set_up() {
		global $wp_version;

		parent::set_up();

		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API requires WordPress ' . self::MIN_WP_VERSION . ' or higher.' );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedOutput( $config, $expected ) {
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] ?? [] );

		$this->set_up_user( $config['has_permission'] );

		$clean_home_calls_before  = did_action( 'before_rocket_clean_home' );
		$clean_files_calls_before = did_action( 'before_rocket_clean_files' );

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered.' );

		$result = $ability->execute( $config['input'] );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		$this->assertIsArray( $result, 'Should return array when user has permission.' );
		$this->assertSame( $expected['success'], $result['success'], 'success flag should match expectation.' );
		$this->assertSame( $expected['error'], $result['error'], 'error list should match expectation.' );

		$this->checkEntriesDeleted( $expected['cleaned'] ?? [] );
		$this->checkShouldNotDeleteEntries();

		if ( isset( $expected['clean_home_calls'] ) ) {
			$this->assertSame(
				$expected['clean_home_calls'],
				did_action( 'before_rocket_clean_home' ) - $clean_home_calls_before,
				'rocket_clean_home() call count should match expectation.'
			);
		}

		if ( isset( $expected['clean_files_calls'] ) ) {
			$this->assertSame(
				$expected['clean_files_calls'],
				did_action( 'before_rocket_clean_files' ) - $clean_files_calls_before,
				'rocket_clean_files() call count should match expectation.'
			);
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