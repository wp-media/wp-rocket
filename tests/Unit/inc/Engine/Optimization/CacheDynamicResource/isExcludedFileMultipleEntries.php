<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\CacheDynamicResource;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\CacheDynamicResource;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\CacheDynamicResource::is_excluded_file
 * with several entries returned by the rocket_exclude_static_dynamic_resources filter.
 *
 * @group  Optimize
 * @group  CacheDynamicResource
 */
class Test_IsExcludedFileMultipleEntries extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/CacheDynamicResource/isExcludedFileMultipleEntries.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResultWhenFilterHasMultipleEntries( $entries, $url, $expected ) {
		Filters\expectApplied( 'rocket_exclude_static_dynamic_resources' )
			->once()
			->with( [] )
			->andReturn( $entries );

		Functions\when( 'remove_query_arg' )->returnArg( 2 );

		$cache_resource = new CacheDynamicResource(
			$this->options,
			$this->filesystem->getUrl( 'wordpress/wp-content/cache/busting/' ),
			'http://example.org/wp-content/cache/busting/'
		);

		$this->assertSame( $expected, $cache_resource->is_excluded_file( $url ) );
	}
}
