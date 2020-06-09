<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Fixtures\DIContainer;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_generate_advanced_cache_file
 * @uses \WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses ::get_rocket_option
 * @uses   ::rocket_put_content
 * @uses   ::rocket_get_constant
 *
 * @group  AdvancedCache
 * @group  Functions
 * @group  Files
 */
class Test_RocketGenerateAdvancedCacheFile extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketGenerateAdvancedCacheFile.php';
	private   $advanced_cache_file = 'vfs://public/wp-content/advanced-cache.php';

	protected static $use_settings_trait = true;

	private $dicontainer;

	public function setUp() {
		parent::setUp();

		// Set up the container.
		$this->dicontainer = new DIContainer();
		$this->dicontainer->setUp();
	}

	public function tearDown() {
		parent::tearDown();

		$this->dicontainer->tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFile( $settings, $expected_content, $when_file_not_exist = false ) {
		$this->mergeExistingSettingsAndUpdate( $settings );

		if ( $when_file_not_exist ) {
			$this->filesystem->delete( $this->advanced_cache_file );
		}

		$this->dicontainer->addAdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		// Run it.
		rocket_generate_advanced_cache_file();

		$this->assertTrue( $this->filesystem->exists( $this->advanced_cache_file ) );

		// Check that the file was generated with the expected content.
		$actual_content = $this->filesystem->get_contents( $this->advanced_cache_file );
		$this->assertSame( $expected_content, $actual_content );
	}
}
