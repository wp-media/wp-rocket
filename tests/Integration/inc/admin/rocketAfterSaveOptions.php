<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_after_save_options
 * @group admin
 * @group AdminOnly
 * @group Options
 */
class Test_RocketAfterSaveOptions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/admin/rocketAfterSaveOptions.php';
	private $option_name;
	private $options;
	private $analytics_transient_value;
	private $hooks = [];

	public function setUp() {
		parent::setUp();

		$this->option_name               = rocket_get_constant( 'WP_ROCKET_SLUG' );
		$this->options                   = get_option( $this->option_name );
		$this->analytics_transient_value = get_transient( 'rocket_analytics_optin' );

		if ( false !== $this->analytics_transient_value ) {
			delete_transient( 'rocket_analytics_optin' );
		}
	}

	public function tearDown() {
		parent::tearDown();

		$this->silently_update_option( $this->options );

		if ( false !== $this->analytics_transient_value ) {
			set_transient( 'rocket_analytics_optin', $this->analytics_transient_value );
		} else {
			delete_transient( 'rocket_analytics_optin' );
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

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldTriggerCleaningsWhenOptionsChange( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => false,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'foobar'              => 'barbaz', // This one will trigger cleaning and preload.
			]
		);

		// `rocket_clean_domain()`.
		$this->assertFilesDeleted( $data['rocket_clean_domain'] );

		// `flush_rocket_htaccess()`.
		$this->assertFlushRocketHtaccess( $data['flush_rocket_htaccess'] );

		// `rocket_generate_config_file()`.
		$this->assertRocketGenerateConfigFile( $data['rocket_generate_config_file'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyCSSWhenMinifyOptionChanges( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => true,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		// `rocket_clean_minify( 'css' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_css'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyCSSWhenExcludeOptionChanges( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => 'foobar',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		// `rocket_clean_minify( 'css' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_css'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyJSWhenMinifyOptionChanges( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => true,
				'exclude_js'          => '',
			]
		);

		// `rocket_clean_minify( 'js' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_js'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyJSWhenExcludeOptionChanges( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => 'foobar',
			]
		);

		// `rocket_clean_minify( 'js' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_js'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyCSSJSWhenCdnOptionIsDisabled( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'cdn'                 => '',
			]
		);

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		// `rocket_clean_minify( 'css' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_css'] );

		// `rocket_clean_minify( 'js' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_js'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFileWhenOptionIsDisabled( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'            => true,
				'purge_cron_interval'     => true,
				'purge_cron_unit'         => true,
				'minify_css'              => false,
				'exclude_css'             => '',
				'minify_js'               => false,
				'exclude_js'              => '',
				'do_caching_mobile_files' => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		// `rocket_generate_advanced_cache_file()`
		$this->assertAdvancedCacheFile( $data['rocket_generate_advanced_cache_file'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFileWhenOptionIsEnabled( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'            => true,
				'purge_cron_interval'     => true,
				'purge_cron_unit'         => true,
				'minify_css'              => false,
				'exclude_css'             => '',
				'minify_js'               => false,
				'exclude_js'              => '',
				'do_caching_mobile_files' => '',
			]
		);

		// `rocket_generate_advanced_cache_file()`
		$this->assertAdvancedCacheFile( $data['rocket_generate_advanced_cache_file'] );

		unset( $_POST['foo'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFileWhenOptionChanges( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'            => true,
				'purge_cron_interval'     => true,
				'purge_cron_unit'         => true,
				'minify_css'              => false,
				'exclude_css'             => '',
				'minify_js'               => false,
				'exclude_js'              => '',
				'do_caching_mobile_files' => 'foo',
			]
		);

		$_POST['foo'] = 1;

		update_option(
			$this->option_name,
			[
				'cache_mobile'            => true,
				'purge_cron_interval'     => true,
				'purge_cron_unit'         => true,
				'minify_css'              => false,
				'exclude_css'             => '',
				'minify_js'               => false,
				'exclude_js'              => '',
				'do_caching_mobile_files' => 'bar',
			]
		);

		// `rocket_generate_advanced_cache_file()`
		$this->assertAdvancedCacheFile( $data['rocket_generate_advanced_cache_file'] );

		unset( $_POST['foo'] );
	}

	public function testShouldEnableAnalyticsWhenOptionIsEnabled() {
		Functions\expect( 'wp_remote_get' )->never();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'analytics_enabled'   => 'foo',
			]
		);

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'analytics_enabled'   => '1',
			]
		);

		$this->assertSame( 1, get_transient( 'rocket_analytics_optin' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinifyCSSWhenCdnOptionIsEnabled( $data ) {
		Functions\expect( 'wp_remote_get' )->once();

		$this->silently_update_option(
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			]
		);

		update_option(
			$this->option_name,
			[
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'cdn'                 => '',
			]
		);

		// `rocket_clean_minify( 'css' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_css'] );

		// `rocket_clean_minify( 'js' )`
		$this->assertFilesDeleted( $data['rocket_clean_minify_js'] );
	}

	private function assertFilesDeleted( $paths ) {
		foreach ( $paths as $file ) {
			$this->assertFalse( $this->filesystem->getUrl( $file ), "The file $file exists." );
		}
	}

	private function assertFlushRocketHtaccess( $paths ) {
		foreach ( $paths as $file ) {
			$path = $this->filesystem->getUrl( $file );
			$this->assertTrue( $this->filesystem->exists( $path ), "The file $path does not exist." );

			$contents = $this->filesystem->get_contents( $path );
			$matching = preg_match( '/\s*# BEGIN WP Rocket(?<rules>.*)# END WP Rocket\s*?/isU', $contents, $matches );
			$this->assertSame( 1, $matching, "No WP Rocket tags in file $path." );
			$rules = trim( $matches['rules'] );
			$this->assertTrue( ! empty( $rules ) && 'Some rules.' !== $rules, "No WP Rocket rewrite rules in file $path." );
		}
	}

	private function assertRocketGenerateConfigFile( $paths ) {
		foreach ( $paths as $file ) {
			$config_path = $this->filesystem->getUrl( $file );
			$this->assertTrue( $this->filesystem->exists( $config_path ), "The config file $config_path does not exist." );

			$config_contents = $this->filesystem->get_contents( $config_path );
			$this->assertContains( '$rocket_cookie_hash', $config_contents, "The config file $config_path does not contain `\$rocket_cookie_hash`." );
		}
	}

	private function assertAdvancedCacheFile( $paths ) {
		foreach ( $paths as $file ) {
			$path = $this->filesystem->getUrl( $file );
			$this->assertTrue( $this->filesystem->exists( $path ), "The file $path does not exist." );

			$contents = $this->filesystem->get_contents( $path );
			$this->assertContains( 'WP_ROCKET_ADVANCED_CACHE', $contents, "No WP Rocket contents in file $path." );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketAfterSaveOptions' );
	}
}
