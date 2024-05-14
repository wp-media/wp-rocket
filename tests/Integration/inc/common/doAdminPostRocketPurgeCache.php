<?php

namespace WP_Rocket\Tests\Integration\Inc\Common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::do_admin_post_rocket_purge_cache
 * @uses ::get_rocket_i18n_home_url
 * @uses ::get_rocket_option
 * @uses ::get_rocket_parse_url
 * @uses ::rocket_clean_cache_busting
 * @uses ::rocket_clean_domain
 * @uses ::rocket_clean_files
 * @uses ::rocket_clean_home
 * @uses ::rocket_clean_minify
 * @uses ::rocket_clean_post
 * @uses ::rocket_clean_term
 * @uses ::rocket_clean_user
 * @uses ::rocket_dismiss_box
 *
 * @group admin
 * @group Common
 * @group vfs
 * @group AdminOnly
 */
class Test_DoAdminPostRocketPurgeCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/doAdminPostRocketPurgeCache.php';
	protected static $original_transients = [];
	protected static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$original_transients = [
			'rocket_clear_cache' => get_transient( 'rocket_clear_cache' ),
		];

		foreach ( array_keys( self::$original_transients ) as $transient ) {
			delete_transient( $transient );
		}

		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cache' );
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		foreach ( self::$original_transients as $transient => $value ) {
			set_transient( $transient, $value, HOUR_IN_SECONDS );
		}
	}

	public function set_up() {
		parent::set_up();

		$this->set_permalink_structure( '/%postname%/' );
	}

	public function tear_down() {
		parent::tear_down();

		unset( $GLOBALS['tonya'] );

		foreach ( array_keys( self::$original_transients ) as $transient ) {
			delete_transient( $transient );
		}
	}

	/**
	 * @dataProvider purgeTestData
	 */
	public function testShouldPurge( $_get, array $config ) {
		wp_set_current_user( self::$user_id );

		if ( 'post' === $config['type'] ) {
			$config['post_id'] = $this->factory->post->create( $config['post_data'] );
			$_get['type']      = str_replace( '123', $config['post_id'], $_get['type'] );
			$_get['_wpnonce']  = str_replace( '123', $config['post_id'], $_get['_wpnonce'] );
		}

		foreach ( $_get as $key => $value ) {
			$_GET[ $key ] = $value;
		}

		$GLOBALS['tonya'] = true;

		$_GET['_wpnonce'] = wp_create_nonce( 'purge_cache_' . $_get['_wpnonce'] );
		Functions\expect( 'wp_nonce_ays' )->never();

		// Let's don't redirect or bail out.
		Functions\expect( 'wp_safe_redirect' )->once()->andReturnNull();
		Functions\expect( 'wp_die' )->once()->andReturnNull();

		do_action( 'admin_post_purge_cache' );

		$this->assertSame( 1, did_action( 'rocket_purge_cache' ) );
		$this->assertGreaterThan( 0, did_action( 'before_rocket_clean_post' ) );
		$this->assertSame( $config['type'], get_transient( 'rocket_clear_cache' ) );
	}

	/**
	 * @dataProvider wontPurgeTestData
	 */
	public function testShouldWontPurge( $_get, array $config ) {
		foreach ( $_get as $key => $value ) {
			$_GET[ $key ] = $value;
		}

		if ( $config['current_user_can'] ) {
			wp_set_current_user( self::$user_id );
		}

		Functions\expect( 'wp_nonce_ays' )->once()->with( '' )->andReturnNull();

		if ( ! empty( $_get['_wpnonce'] ) ) {
			$_GET['_wpnonce'] = wp_create_nonce( "purge_cache_{$_get['_wpnonce']}" );
		}

		Functions\expect( 'wp_safe_redirect' )->never();
		Functions\expect( 'wp_die' )->never();

		do_action( 'admin_post_purge_cache' );

		$this->assertSame( 0, did_action( 'rocket_purge_cache' ) );
	}

	public function purgeTestData() {
		$this->loadConfig();

		return $this->config['test_data']['purge'];
	}

	public function wontPurgeTestData() {
		$this->loadConfig();

		return $this->config['test_data']['wontpurge'];
	}
}
