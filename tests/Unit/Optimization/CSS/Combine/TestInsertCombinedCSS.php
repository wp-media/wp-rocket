<?php
namespace WP_Rocket\Tests\Unit\Optimize\CSS\Combine;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Optimization\CSS\Combine;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Optimization\CSS\Combine::insert_combined_css()
 * @group Optimize
 */
class TestInsertCombinedCSS extends TestCase {
	public function testShouldInsertCombinedCSS() {
		Functions\when('create_rocket_uniqid')->justReturn('123456');
		Functions\when('get_current_blog_id')->justReturn('1');

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'wp-content/cache/min/' )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_URL' )
			->andReturn( 'http://example.org/wp-content/cache/min/' );

		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$minify  = $this->createMock('MatthiasMullie\Minify\CSS');

		$combine = new Combine( $options, $minify );
		$combined_url = 'http://example.org/wp-content/cache/min/1/combined.css';
		$styles = [
			[
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/storefront/style.css" />',
			],
		];

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/original.html');
		$combined = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/combined.html');

		$this->assertSame(
			$combined,
			$combine->insert_combined_css( $original, $combined_url, $styles )
		);
	}
}
