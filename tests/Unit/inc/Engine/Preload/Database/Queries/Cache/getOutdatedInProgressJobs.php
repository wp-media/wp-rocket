<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering Cache::get_outdated_in_progress_jobs
 *
 * @group Preload
 */
class Test_GetOutdatedInProgressJobs extends TestCase {
	protected $query;

	protected function setUp(): void {
		parent::setUp();

		$this->query = $this->createPartialMock( Cache::class, [ 'get_rows_by_url', 'query' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->query->method( 'query' )->with(
			[
				'status'     => 'in-progress',
				'is_locked'  => false,
				'date_query' => [
					[
						'column' => 'modified',
						'before' => '3 minute ago',
					],
				],
			]
		)->willReturn( $config['results'] );

		$this->assertSame(
			$expected,
			$this->query->get_outdated_in_progress_jobs()
		);
	}
}
