<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Cache_Dynamic_Resource;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Optimization\Cache_Dynamic_Resource;
use WP_Rocket\Tests\Unit\inc\classes\optimization\TestCase;

/**
 * @covers \WP_Rocket\Optimization\Cache_Dynamic_Resource::is_excluded_file
 * @group  Optimize
 * @group  CacheDynamicResource
 */
class Test_IsExcludedFile extends TestCase {
	protected $path_to_test_data = '/inc/classes/optimization/Cache_Dynamic_Resource/isExcludedFile.php';
	private $cache_resource;

	public function setUp() {
		parent::setUp();

		$this->cache_resource = new Cache_Dynamic_Resource(
			$this->options,
			$this->filesystem->getUrl( 'wordpress/wp-content/cache/busting/' ),
			'http://example.org/wp-content/cache/busting/'
		);

		Filters\expectApplied( 'rocket_cdn_hosts' )
			->atMost()
			->times(1)
			->with( [], [ 'all', 'css_and_js', '' ] )
			->andReturn( [
				'123456.rocketcdn.me',
				'cdn.example.org/path',
			]
		);

		Functions\when( 'remove_query_arg' )->alias( function( $key, $query ) {
			return str_replace( [ '&ver=5.3', 'ver=5.3' ], '', $query );
		} );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnTrueWhenURLIsExcluded( $url, $expected ) {
		$this->assertSame(
			$expected,
			$this->cache_resource->is_excluded_file( $url )
		);
	}
}
