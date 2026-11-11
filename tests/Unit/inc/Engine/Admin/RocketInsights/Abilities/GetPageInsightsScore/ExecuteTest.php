<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore::execute
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class ExecuteTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'rocket_add_url_protocol' )->alias( function ( $url ) {
			if ( empty( $url ) ) {
				return $url;
			}
			if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
				return 'https://' . $url;
			}
			return $url;
		} );

		Functions\when( 'untrailingslashit' )->alias( function ( $string ) {
			return rtrim( $string, '/\\' );
		} );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$mock_query = $this->createMock( Query::class );
		$mock_plan  = $this->createMock( Plan::class );

		$mock_query
			->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->with( $expected['queried_url'] )
			->willReturn( $config['rows'] );

		if ( false === $config['rows'] ) {
			$mock_plan
				->expects( $this->once() )
				->method( 'max_urls' )
				->willReturn( $config['max_urls'] );

			$mock_query
				->expects( $this->once() )
				->method( 'get_total_count' )
				->willReturn( $config['total_count'] );
		} else {
			$mock_plan
				->expects( $this->never() )
				->method( 'max_urls' );

			$mock_query
				->expects( $this->never() )
				->method( 'get_total_count' );
		}

		$ability = new GetPageInsightsScore( $mock_query, $mock_plan );
		$result  = $ability->execute( $config['input'] );

		$this->assertSame( $expected['result'], $result );
	}
}
