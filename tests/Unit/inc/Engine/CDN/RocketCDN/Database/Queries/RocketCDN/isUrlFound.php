<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN::is_url_found
 * @group  CDN
 */
class Test_IsUrlFound extends TestCase {

	/**
	 * @var RocketCDN|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $query;

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'wp_parse_url' )->alias( function ( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		$this->query = $this->createPartialMock( RocketCDN::class, [ 'query' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ): void {
		$this->query->expects( self::once() )
			->method( 'query' )
			->with( [ 'count' => true, 'url' => $config['normalized_url'] ] )
			->willReturn( $config['query_result'] );

		$this->assertSame( $expected, $this->query->is_url_found( $config['url'] ) );
	}
}
