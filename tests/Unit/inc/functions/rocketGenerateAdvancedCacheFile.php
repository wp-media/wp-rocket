<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_generate_advanced_cache_file
 * @uses   WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content
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
	private $container;
	private $advanced_cache;

	public function setUp(): void {
		parent::setUp();

		$this->container      = Mockery::mock( 'container' );
		$this->advanced_cache = Mockery::mock( AdvancedCache::class );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFile( $settings, $expected_content, $when_file_not_exist = false ) {
		if ( $when_file_not_exist ) {
			$this->filesystem->delete( $this->advanced_cache_file );
		}

		if ( isset( $settings['filter'] ) ) {
			Filters\expectApplied( 'rocket_generate_advanced_cache_file' )
				->once()
				->andReturn( $settings['filter'] );
		} else {
			Filters\expectApplied( 'rocket_container' )
				->andReturn( $this->container );

			$this->container->shouldReceive( 'get' )
				->with( 'advanced_cache' )
				->andReturn( $this->advanced_cache );

			$this->advanced_cache->shouldReceive( 'get_advanced_cache_content' )
				->andReturn( $expected_content );
		}

		// Run it.
		rocket_generate_advanced_cache_file();

		$this->assertTrue( $this->filesystem->exists( $this->advanced_cache_file ) );

		// Check that the file was generated with the expected content.
		$actual_content = $this->filesystem->get_contents( $this->advanced_cache_file );
		$this->assertSame( $expected_content, $actual_content );
	}
}
