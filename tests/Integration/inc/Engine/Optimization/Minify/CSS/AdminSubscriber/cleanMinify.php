<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @uses ::rocket_clean_minify
 * @group  Optimize
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_CleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/AdminSubscriber/cleanMinify.php';
	private $original_settings;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		update_option(
			'wp_rocket_settings',
			array_merge(
				get_option( 'wp_rocket_settings', [] ),
				[
					'minify_css'  => false,
					'exclude_css' => [],
					'cdn'         => false,
					'cdn_cnames'  => [],
				]
			)
		);
	}

	public function setUp() {
		parent::setUp();

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public function tearDown() {
		parent::tearDown();

		if ( empty( $this->original_settings ) ) {
			delete_option( 'wp_rocket_settings' );
		} else {
			update_option( 'wp_rocket_settings', $this->original_settings );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $value, $should_run ) {
		$cache_files = $this->filesystem->getFilesListing( 'wp-content/cache/min' );

		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $value )
		);

		$after_cache = $this->filesystem->getFilesListing( 'wp-content/cache/min' );

		if ( $should_run ) {
			$this->assertEmpty( $after_cache );
		} else {
			$this->assertEquals( $cache_files, $after_cache );
		}
	}
}
