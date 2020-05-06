<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_user_cache
 * @uses ::rocket_clean_user
 * @uses ::get_rocket_parse_url
 * @uses ::get_rocket_i18n_uri
 * @uses ::get_rocket_i18n_home_url
 * @uses ::get_rocket_option
 * @group purge_actions
 * @group vfs
 */
class Test_PurgeUserCache extends FilesystemTestCase {
	use GlobTrait;

	protected $path_to_test_data = '/inc/Engine/Cache/PurgeActionsSubscriber/purgeUserCache.php';
	private $settings;

	public function setUp() {
		parent::setUp();

		// Unhook WooCommerce, as it throws wpdb::prepare errors.
		remove_action( 'delete_user', 'wc_delete_user_data' );

		// Store the original settings. Set the secret cache key.
		$settings = $this->settings = get_option( 'wp_rocket_settings' );
		$settings['secret_cache_key'] = '594d03f6ae698691165999';
		update_option( 'wp_rocket_settings', $settings );
	}

	public function tearDown() {
		parent::tearDown();

		// Restore the options.
		update_option( 'wp_rocket_settings', $this->settings );

		// Rewire WooCommerce.
		add_action( 'delete_user', 'wc_delete_user_data' );
	}

	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );

		Functions\expect( 'rocket_clean_user' )->never();

		do_action( 'delete_user', $this->getUserId() );

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );
	}

	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		add_filter( 'rocket_common_cache_logged_users', '__return_true' );

		Functions\expect( 'rocket_clean_user' )->never();

		do_action( 'delete_user', $this->getUserId() );

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		remove_filter( 'rocket_common_cache_logged_users', '__return_true' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPurgeCacheForUser( $username, $dir, $userCacheFiles ) {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );

		// Check the files exist before running.
		foreach( $userCacheFiles as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		// rocket_clean_user() uses glob(), which not compatible with vfsStream.
		$this->deleteFiles( $dir, $this->filesystem );

		do_action( 'delete_user', $this->getUserId( $username ) );

		// Check the files were deleted.
		foreach( $userCacheFiles as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Check that all the other files were not deleted.
		foreach( array_diff( $this->original_files, $userCacheFiles ) as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
	}

	private function getUserId( $username = 'wpmedia' ) {
		return $this->factory->user->create( [ 'user_login' => $username, 'role' => 'editor' ] );
	}
}
