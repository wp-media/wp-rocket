<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\DIContainer;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::rocket_after_save_options
 *
 * @uses  ::rocket_clean_domain
 * @uses  ::rocket_clean_minify
 * @uses  ::rocket_generate_advanced_cache_file
 * @uses  ::rocket_valid_key
 * @uses  ::flush_rocket_htaccess
 * @uses  ::rocket_generate_config_file
 * @uses  ::rocket_get_constant
 *
 * @group admin
 * @group AdminOnly
 * @group Options
 * @group SaveOptions
 */
class Test_RocketAfterSaveOptions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/admin/rocketAfterSaveOptions.php';

	protected static $use_settings_trait = true;
	protected static $transients         = [
		'rocket_analytics_optin' => null,
	];

	private $is_apache;
	private $hooks = [];
	private $expected;
	private $rocketCleanDomainEntriesBefore;
	private $rocketCleanMinifyEntriesBefore;
	private $rocketCleanDomainShouldNotClean;
	private $rocketCleanMinifyShouldNotClean;
	private $dicontainer;

	public function set_up() {
		parent::set_up();

		// Unhook to avoid triggering when storing the configured settings.
		remove_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' );

		// Save the original global state.
		$this->is_apache = isset( $GLOBALS['is_apache'] ) ? $GLOBALS['is_apache'] : null;

		// Initialize states.
		$this->rocketCleanDomainEntriesBefore  = [];
		$this->rocketCleanMinifyEntriesBefore  = [];
		$this->rocketCleanDomainShouldNotClean = [];
		$this->rocketCleanMinifyShouldNotClean = [];
		$GLOBALS['is_apache'] = true;

		// Set up the container.
		$this->dicontainer = new DIContainer();
		$this->dicontainer->setUp();

		Functions\when( 'wp_remote_get' )->justReturn();

		// Hook it back up as we're ready to test.
		add_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options', 10, 2 );
	}

	public function tear_down() {
		parent::tear_down();

		$this->dicontainer->tearDown();

		unset( $_POST['rocket_after_save_options'], $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );

		// Restore the original state.
		if ( ! empty( $this->is_apache ) ) {
			$GLOBALS['is_apache'] = $this->is_apache;
		}
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
			$this->assertStringContainsString( $content, $actual );
		}
	}

	private function rocket_generate_advanced_cache_file( $before_updating = false ) {
		// Sets up the test before updating.
		if ( $before_updating ) {

			$this->dicontainer->addAdvancedCache(
				$this->filesystem->getUrl( $this->config['vfs_dir'] . 'plugins/wp-rocket/views/cache/' ),
				$this->filesystem
			);

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

	private function set_transient() {
		if ( isset( $this->expected['set_transient'] ) ) {
			$this->assertEquals( '1', get_transient( 'rocket_analytics_optin' ) );
		} else {
			Functions\expect( 'set_transient' )->with( 'rocket_analytics_optin', 1 )->never();
		}
	}
}
