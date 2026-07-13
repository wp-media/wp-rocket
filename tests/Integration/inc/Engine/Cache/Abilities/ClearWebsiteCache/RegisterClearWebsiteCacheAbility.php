<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Abilities\ClearWebsiteCache;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering wp-rocket/clear-website-cache ability registration & execution.
 *
 * @group Abilities
 * @group Cache
 * @group AdminOnly
 */
class Test_RegisterClearWebsiteCacheAbility extends FilesystemTestCase {
	/**
	 * Minimum WordPress version required.
	 */
	private const MIN_WP_VERSION = '6.9';

	/**
	 * Ability ID.
	 */
	private const ABILITY_ID = 'wp-rocket/clear-website-cache';

	/**
	 * Path to the config and test data in the Fixtures directory.
	 *
	 * @var string
	 */
	protected $path_to_test_data = '/inc/Engine/Cache/Abilities/ClearWebsiteCache/RegisterClearWebsiteCacheAbility.php';

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
	 * @dataProvider providerTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$this->set_up_user( $config['has_permission'] );

		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		add_filter( 'rocket_is_importing', $config['is_importing'] ? '__return_true' : '__return_false' );

		if ( ! $config['has_urls'] ) {
			add_filter( 'rocket_clean_domain_urls', '__return_empty_array' );
		}

		$ability = wp_get_ability( self::ABILITY_ID );

		$this->assertNotNull( $ability, 'Ability should be registered.' );

		$result = $ability->execute();

		remove_filter( 'rocket_is_importing', $config['is_importing'] ? '__return_true' : '__return_false' );

		if ( ! $config['has_urls'] ) {
			remove_filter( 'rocket_clean_domain_urls', '__return_empty_array' );
		}

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		$this->assertIsArray( $result, 'Should return array.' );
		$this->assertSame( $expected['success'], $result['success'], 'Success status should match.' );
		$this->assertSame( $expected['error'], $result['error'], 'Error message should match.' );
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
