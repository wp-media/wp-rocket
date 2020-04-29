<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_after_save_options
 * @uses  ::rocket_clean_domain
 * @uses  ::rocket_clean_minify
 * @uses  ::rocket_generate_advanced_cache_file
 * @uses  ::rocket_valid_key
 * @uses  ::flush_rocket_htaccess
 * @uses  ::rocket_generate_config_file
 * @uses  ::rocket_get_constant
 * @uses  ::set_rocket_wp_cache_define
 *
 * @group admin
 * @group AdminOnly
 * @group Options
 * @group SaveOptions
 * @group thisone
 */
class Test_RocketAfterSaveOptions extends FilesystemTestCase {
	protected      $path_to_test_data = '/inc/admin/rocketAfterSaveOptions.php';
	private        $is_apache;
	private        $hooks             = [];
	private static $original_settings = [];
	private        $options           = [];
	private static $transients        = [
		'rocket_analytics_optin' => null,
	];
	private        $count             = 0;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$original_settings = get_option( 'wp_rocket_settings', [] );
		foreach ( array_keys( self::$transients ) as $transient ) {
			self::$transients[ $transient ] = get_transient( $transient );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		// Restore the originals before exiting.
		update_option( 'wp_rocket_settings', self::$original_settings );
		self::resetTransitions();
	}

	private static function resetTransitions() {
		foreach ( self::$transients as $transient => $value ) {
			if ( ! empty( $transient ) ) {
				set_transient( $transient, $value );
			} else {
				delete_transient( $transient );
			}
		}
	}

	public function setUp() {
		parent::setUp();

		// Mocks the various filesystem constants.
		$this->whenRocketGetConstant();

		$this->is_apache = $GLOBALS['is_apache'];
		$this->options   = array_merge( self::$original_settings, $this->config['settings'] );
		update_option( 'wp_rocket_settings', $this->options );

		$GLOBALS['is_apache'] = true;
		Functions\when( 'wp_remote_get' )->justReturn();
		$this->count ++;
	}

	public function tearDown() {
		parent::tearDown();

		unset( $_POST['foo'], $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );

		$GLOBALS['is_apache'] = $this->is_apache;
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldTriggerCleaningsWhenOptionsChange( $settings, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;

		if ( isset( $expected['rocket_clean_domain'] ) ) {
			$this->generateEntriesShouldExistAfter( $expected['rocket_clean_domain'], 'vfs://public/wp-content/cache/wp-rocket/' );
		}

		if ( isset( $expected['flush_rocket_htaccess'] ) ) {
			Functions\expect( 'rocket_valid_key' )->andReturn( true );
			Functions\expect( 'get_home_path' )->andReturn( 'vfs://public/' );
		}

		if ( isset( $expected['rocket_generate_advanced_cache_file'] ) ) {
			$_POST['foo'] = 1;
		}

		// Run it.
		update_option( 'wp_rocket_settings', $settings );

		if ( isset( $expected['rocket_clean_minify'] ) ) {

		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}

		if ( isset( $expected['rocket_clean_domain'] ) ) {
			$this->checkEntriesDeleted( $expected['rocket_clean_domain'] );
			$this->checkShouldNotDeleteEntries( 'vfs://public/wp-content/cache/wp-rocket/' );
		} else {
			Functions\expect( 'rocket_clean_domain' )->never();
		}

		if ( isset( $expected['rocket_generate_advanced_cache_file'] ) ) {
			$this->assertSame(
				$expected['rocket_generate_advanced_cache_file'],
				$this->filesystem->get_contents( 'vfs://public/wp-content/advanced-cache.php' )
			);
		} else {
			Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		}

		if ( isset( $expected['flush_rocket_htaccess'] ) ) {
			$this->assertSame(
				$expected['flush_rocket_htaccess'],
				$this->filesystem->get_contents( 'vfs://public/.htaccess' )
			);
		} else {
			Functions\expect( 'flush_rocket_htaccess' )->never();
		}

		if ( isset( $expected['rocket_generate_config_file'] ) ) {
			$this->assertSame(
				$expected['rocket_generate_config_file'],
				$this->filesystem->get_contents( 'vfs://public/wp-content/wp-rocket-config/example.org.php' )
			);
		} else {
			Functions\expect( 'rocket_generate_config_file' )->never();
		}

		if ( isset( $expected['set_rocket_wp_cache_define'] ) ) {
			Functions\expect( 'rocket_get_constant' )->with( 'WP_CACHE' )->andReturn( true );
			$this->assertSame(
				$expected['set_rocket_wp_cache_define'],
				$this->filesystem->get_contents( 'vfs://public/wp-config.php' )
			);
		} else {
			Functions\expect( 'rocket_get_constant' )->with( 'WP_CACHE' )->andReturn( false );
			Functions\expect( 'set_rocket_wp_cache_define' )->never();
		}

		if ( isset( $expected['set_transient'] ) ) {
			$this->assertEquals( '1', get_transient( 'rocket_analytics_optin' ) );
		} else {
			Functions\expect( 'set_transient' )->with( 'rocket_analytics_optin', 1 )->never();
		}
	}

	private function silently_update_option( $new_value ) {
		global $wp_filter;

		$hooks = [
			"pre_update_option_{$this->option_name}",
			"update_option_{$this->option_name}",
			'update_option',
			"update_option_{$this->option_name}",
			'updated_option',
		];

		foreach ( $hooks as $hook ) {
			if ( ! empty( $wp_filter[ $hook ] ) ) {
				$this->hooks[ $hook ] = $wp_filter[ $hook ];
				unset( $wp_filter[ $hook ] );
			}
			$this->assertFalse( has_filter( $hook ) );
		}

		$this->assertFalse( has_action( 'update_option_' . $this->option_name, 'rocket_after_save_options' ) );

		update_option( $this->option_name, $new_value );

		if ( $this->hooks ) {
			$wp_filter = array_merge( $wp_filter, $this->hooks );
		}

		$this->assertNotFalse( has_action( 'update_option_' . $this->option_name, 'rocket_after_save_options' ) );
	}
}
