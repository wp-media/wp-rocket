<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Context\Context;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestIsAllowed extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$cache_logged_user = $config['cache_logged_user'] ?? 0;

		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )
			->with( 'cache_logged_user', 0 )
			->andReturn( $cache_logged_user );

		$context = new Context( $options );

		Functions\when( 'get_option' )->justReturn( $config['licence'] );
		Functions\when( 'is_user_logged_in' )->justReturn( $config['is_logged_in'] ?? false );

		$reaches_filter = ! $config['licence']
			&& ! ( ( $config['is_logged_in'] ?? false ) && $cache_logged_user );

		if ( $reaches_filter ) {
			Filters\expectApplied( 'rocket_lrc_optimization' )
				->andReturn( $config['filter'] );
		}

		$this->assertSame(
			$expected,
			$context->is_allowed()
		);
	}
}
