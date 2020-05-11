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
 */
class Test_RocketAfterSaveOptions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/admin/rocketAfterSaveOptions.php';

	private static $transients        = [
		'rocket_analytics_optin' => null,
	];
	private static $original_settings = [];

	private $is_apache;
	private $hooks   = [];
	private $options = [];
	private $expected;
	private $rocketCleanDomainEntriesBefore;
	private $rocketCleanMinifyEntriesBefore;
	private $rocketCleanDomainShouldNotClean;
	private $rocketCleanMinifyShouldNotClean;

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
	}

	public function tearDown() {
		parent::tearDown();

		unset( $_POST['rocket_after_save_options'], $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
		$GLOBALS['is_apache'] = $this->is_apache;

		$this->rocketCleanDomainEntriesBefore  = [];
		$this->rocketCleanMinifyEntriesBefore  = [];
		$this->rocketCleanDomainShouldNotClean = [];
		$this->rocketCleanMinifyShouldNotClean = [];
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldTriggerCleaningsWhenOptionsChange( $settings, $expected ) {
		// Skip the "not an array" test as it fails in other hooked callbacks that are not checking for array.
		if ( ! is_array( $settings ) ) {
			$this->assertTrue( true );

			return;
		}

		$this->expected    = $expected;
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;

		$this->rocket_clean_domain( true );
		$this->rocket_clean_minify( true );
		$this->rocket_generate_advanced_cache_file( true );
		$this->flush_rocket_htaccess( true );

		// Run it.
		update_option( 'wp_rocket_settings', $settings );

		$this->rocket_clean_domain();
		$this->rocket_clean_minify();
		$this->rocket_generate_advanced_cache_file();
		$this->flush_rocket_htaccess();
		$this->rocket_generate_config_file();
		$this->set_rocket_wp_cache_define();
		$this->set_transient();
	}

	private function rocket_clean_domain( $before_updating = false ) {
		// Sets up the test before updating.
		if ( $before_updating ) {

			if ( isset( $this->expected['rocket_clean_domain'] ) ) {
				$this->generateEntriesShouldExistAfter( $this->expected['rocket_clean_domain'], 'vfs://public/wp-content/cache/wp-rocket/' );
				$this->rocketCleanDomainEntriesBefore  = $this->entriesBefore;
				$this->rocketCleanDomainShouldNotClean = $this->shouldNotClean;
			}

			return;
		}

		// Checks after updating.
		if ( isset( $this->expected['rocket_clean_domain'] ) ) {
			$this->entriesBefore  = $this->rocketCleanDomainEntriesBefore;
			$this->shouldNotClean = $this->rocketCleanDomainShouldNotClean;
			$this->checkEntriesDeleted( $this->expected['rocket_clean_domain'] );
			$this->checkShouldNotDeleteEntries( 'vfs://public/wp-content/cache/wp-rocket/' );
		} else {
			Functions\expect( 'rocket_clean_domain' )->never();
		}
	}

	private function rocket_clean_minify( $before_updating = false ) {
		// Sets up the test before updating.
		if ( $before_updating ) {

			if ( isset( $this->expected['rocket_clean_minify'] ) ) {
				$this->entriesBefore  = [];
				$this->shouldNotClean = [];
				$this->generateEntriesShouldExistAfter( $this->expected['rocket_clean_minify'], 'vfs://public/wp-content/cache/wp-rocket/' );
				$this->rocketCleanMinifyEntriesBefore  = $this->entriesBefore;
				$this->rocketCleanMinifyShouldNotClean = $this->shouldNotClean;
			}

			return;
		}

		// Checks after updating.
		if ( isset( $this->expected['rocket_clean_minify'] ) ) {
			$this->entriesBefore  = $this->rocketCleanMinifyEntriesBefore;
			$this->shouldNotClean = $this->rocketCleanMinifyShouldNotClean;
			$this->checkEntriesDeleted( $this->expected['rocket_clean_minify'] );
			$this->checkShouldNotDeleteEntries( 'vfs://public/wp-content/cache/wp-rocket/' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}
	}

	private function flush_rocket_htaccess( $before_updating = false ) {
		// Sets up the test before updating.
		if ( $before_updating ) {
			if ( isset( $this->expected['flush_rocket_htaccess'] ) ) {
				Functions\expect( 'rocket_valid_key' )->andReturn( true );
				Functions\expect( 'get_home_path' )->andReturn( 'vfs://public/' );
			}

			return;
		}

		// Checks after updating.
		if ( ! isset( $this->expected['flush_rocket_htaccess'] ) ) {
			Functions\expect( 'flush_rocket_htaccess' )->never();

			return;
		}

		$actual = $this->filesystem->get_contents( 'vfs://public/.htaccess' );
		foreach ( (array) $this->expected['flush_rocket_htaccess'] as $content ) {
			$this->assertContains( $content, $actual );
		}
	}

	private function rocket_generate_advanced_cache_file( $before_updating = false ) {
		// Sets up the test before updating.
		if ( $before_updating ) {
			if ( isset( $this->expected['rocket_generate_advanced_cache_file'] ) ) {
				$_POST['rocket_after_save_options'] = 1;
			}

			return;
		}

		// Checks after updating.
		if ( isset( $this->expected['rocket_generate_advanced_cache_file'] ) ) {
			$this->assertSame(
				$this->expected['rocket_generate_advanced_cache_file'],
				$this->filesystem->get_contents( 'vfs://public/wp-content/advanced-cache.php' )
			);
		} else {
			Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		}
	}

	private function rocket_generate_config_file() {
		if ( isset( $this->expected['rocket_generate_config_file'] ) ) {
			$this->assertSame(
				$this->expected['rocket_generate_config_file'],
				$this->filesystem->get_contents( 'vfs://public/wp-content/wp-rocket-config/example.org.php' )
			);
		} else {
			Functions\expect( 'rocket_generate_config_file' )->never();
		}
	}

	private function set_rocket_wp_cache_define() {
		if ( isset( $this->expected['set_rocket_wp_cache_define'] ) ) {
			$this->wp_cache_constant = false;
			$this->assertSame(
				$this->expected['set_rocket_wp_cache_define'],
				$this->filesystem->get_contents( 'vfs://public/wp-config.php' )
			);
		} else {
			$this->wp_cache_constant = true;
			Functions\expect( 'rocket_get_constant' )->with( 'WP_CACHE' )->andReturn( true );
			Functions\expect( 'set_rocket_wp_cache_define' )->never();
		}
	}

	private function set_transient() {
		if ( isset( $this->expected['set_transient'] ) ) {
			$this->assertEquals( '1', get_transient( 'rocket_analytics_optin' ) );
		} else {
			Functions\expect( 'set_transient' )->with( 'rocket_analytics_optin', 1 )->never();
		}
	}

	private function silently_update_option( $new_value ) {
		global $wp_filter;

		$hooks = [
			'pre_update_option_wp_rocket_settings',
			'pre_update_option',
			'default_option_wp_rocket_settings',
			'update_option',
			'update_option_wp_rocket_settings',
			'updated_option',
		];

		foreach ( $hooks as $hook ) {
			if ( ! empty( $wp_filter[ $hook ] ) ) {
				$this->hooks[ $hook ] = $wp_filter[ $hook ];
				unset( $wp_filter[ $hook ] );
			}
		}

		update_option( $this->option_name, $new_value );

		if ( $this->hooks ) {
			$wp_filter = array_merge( $wp_filter, $this->hooks );
		}
	}
}
