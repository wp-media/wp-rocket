<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::defer_inline_js
 *
 * @group  DeferJS
 */
class Test_DeferInlineJs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotrocketoptimize'];

		$options  = Mockery::mock( Options_Data::class );
		$defer_js = new DeferJS( $options );

		$options->shouldReceive( 'get' )
			->atMost()
			->twice()
			->with( 'defer_all_js', 0 )
			->andReturn( $config['options']['defer_all_js'] );
		
		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'exclude_defer_js', [] )
			->andReturn( $config['options']['exclude_defer_js'] );

		Functions\when( 'is_rocket_post_excluded_option' )
			->justReturn( $config['post_meta'] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $defer_js->defer_inline_js( $html ) )
		);
	}
}
