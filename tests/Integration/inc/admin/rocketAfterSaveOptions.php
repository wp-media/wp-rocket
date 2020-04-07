<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

//use Brain\Monkey\Filters;
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
	private $hooks = [];

	public function setUp() {
		parent::setUp();

		$this->option_name = rocket_get_constant( 'WP_ROCKET_SLUG' );
		$this->options     = get_option( $this->option_name );
	}

	public function tearDown() {
		parent::tearDown();

		$this->silently_update_option( $this->options );
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

		update_option( $this->option_name, $new_value );

		if ( $this->hooks ) {
			$wp_filter = array_merge( $wp_filter, $this->hooks );
		}
	}

	public function testShouldTriggerCleaningsWhenOptionsChange() {
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

		$this->assertNotFalse( has_action( 'update_option_' . $this->option_name, 'rocket_after_save_options' ) );

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

		foreach ( $this->original_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ), "The file $file exists." );
		}

		$config_path = 'wp-content/wp-rocket-config/example.org.php';
		$this->assertTrue( $this->filesystem->exists( $config_path ), "The config file $config_path does not exist." );
		$config_contents = $this->filesystem->get_contents( $config_path );
		$this->assertContains( 'WP_ROCKET_CONFIG_PATH', $config_contents, "The config file $config_path does not contain WP_ROCKET_CONFIG_PATH." );
	}
}
