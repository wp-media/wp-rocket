<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Scanner;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber::collect_resources
 *
 * @group  RUCSS
 */
class Test_CollectResources extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/Subscriber/collectResources.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$options_data = Mockery::mock( Options_Data::class );
		$fetcher = Mockery::mock( ResourceFetcher::class );
		$scanner = Mockery::mock( Scanner::class );

		$this->donotrocketoptimize = isset( $input['DONOTROCKETOPTIMIZE'] ) ? $input['DONOTROCKETOPTIMIZE'] : false;

		if ( isset( $input['remove_unused_css'] ) ) {
			$options_data
				->shouldReceive( 'get' )
				->with( 'remove_unused_css', 0 )
				->once()
				->andReturn( $input['remove_unused_css'] );
		}

		if ( isset( $input['rocket_bypass'] ) ) {
			Functions\expect( 'rocket_bypass' )
				->atMost()
				->once()
				->andReturn( $input['rocket_bypass'] );
		}

		if ( isset( $input['post_metabox_option_excluded'] ) ) {
			Functions\when( 'is_rocket_post_excluded_option' )->justReturn( $input['post_metabox_option_excluded'] );
		}

		if( $expected['allowed'] ) {
			$fetcher
				->shouldReceive( 'data' )
				->with( [ 'html' => $input['html'] ] )
				->once()
				->andReturnSelf()

				->shouldReceive( 'dispatch' )
				->once()
				->andReturn( null );
		}

		$subscriber = new Subscriber( $options_data, $fetcher, $scanner );
		$this->assertSame( $input['html'], $subscriber->collect_resources( $input['html'] ) );

	}
}
