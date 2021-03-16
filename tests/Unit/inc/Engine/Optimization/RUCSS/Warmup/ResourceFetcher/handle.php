<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\ResourceFetcher;

use Brain\Monkey;
use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher::handle
 *
 * @group  RUCSS
 */
class Test_ResourceFetcher extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/ResourceFetcher/handle.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected($expected) {

		$localCache      = Mockery::mock( AssetsLocalCache::class );
		$process         = Mockery::mock( ResourceFetcherProcess::class );
		$resourceFetcher = new ResourceFetcher( $localCache, $process );

		$html = <<<HTML
<!doctype html>
<html lang="fr">
<head>
	<link href="http://www.example.com/path/to/style.css">
	<link rel="stylesheet" href="assets/css/styles.min.css" media="all">
	<link href="assets2/css/styles.min.css" media="all" rel="stylesheet">
	<script src="//www.example.com/path/to/myscript.js"></script>
</head>
<body>
<style>
h1 {color:red;}
</style>
<link href="https://www.styles-r-us.com/path/to/style.css">
<script src="https://my-site.org/activate-my-slides.js"></script>
</body>
</html>
HTML;

		Monkey\Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Monkey\Functions\when( 'content_url' )->justReturn('https://www.example.com/wp-content/');
		Monkey\Functions\when( 'rocket_add_url_protocol' )->alias(function ($url) {
			return 'https://' . $url;
		});

		$process->shouldReceive( 'push_to_queue' )
			->once()
		->with(
			[]
		);
		$process->shouldReceive( 'save' )
			->once()
			->andReturn( $process );
		$process->shouldReceive( 'dispatch' )->once();

		$localCache->shouldReceive( 'get_filepath' )
			->once()
			->andReturn('path/to/style.css');
		$localCache->shouldReceive('get_content')
			->once()
			->andReturn('this is fake css or js');

		$resourceFetcher->handle( $html );
	}
}
