<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Database\Queries\Cache::get_status_counts
 *
 * @group Database
 * @group Preload
 */
class GetStatusCountsTest extends TestCase {
	/**
	 * Cache query partial mock (only the `query` method is mocked).
	 *
	 * @var Cache|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $query;

	protected function setUp(): void {
		parent::setUp();

		$this->query = $this->createPartialMock( Cache::class, [ 'query' ] );
	}

	public function testShouldReturnCountsForEachStatusUsingIndexedColumn(): void {
		$counts_by_status = [
			'pending'     => 12,
			'in-progress' => 3,
			'completed'   => 145,
			'failed'      => 2,
		];

		$this->query->expects( $this->exactly( 4 ) )
			->method( 'query' )
			->willReturnCallback(
				function ( array $params ) use ( $counts_by_status ) {
					$this->assertArrayHasKey( 'count', $params );
					$this->assertTrue( $params['count'] );
					$this->assertArrayHasKey( 'status', $params );
					$this->assertArrayHasKey( $params['status'], $counts_by_status );

					return $counts_by_status[ $params['status'] ];
				}
			);

		$result = $this->query->get_status_counts();

		$this->assertSame( $counts_by_status, $result );
	}

	public function testShouldReturnZeroCountsWhenTableIsEmpty(): void {
		$this->query->expects( $this->exactly( 4 ) )
			->method( 'query' )
			->willReturn( 0 );

		$result = $this->query->get_status_counts();

		$this->assertSame(
			[
				'pending'     => 0,
				'in-progress' => 0,
				'completed'   => 0,
				'failed'      => 0,
			],
			$result
		);
	}

	public function testShouldCastResultsToInteger(): void {
		$this->query->expects( $this->exactly( 4 ) )
			->method( 'query' )
			->willReturn( '7' );

		$result = $this->query->get_status_counts();

		foreach ( $result as $count ) {
			$this->assertSame( 7, $count );
		}
	}
}
