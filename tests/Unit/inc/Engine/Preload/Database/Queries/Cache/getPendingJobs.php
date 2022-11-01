<?php

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\RocketCache::get_pending_jobs
 *
 * @group Database
 * @group Preload
 */
class Test_GetPendingJobs extends TestCase {
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['get_rows_by_url', 'query']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnPending($config, $expected) {

		$this->query->expects($this->any())
             ->method('query')
			 ->with($this->anything())
             ->will($this->returnCallback(
				function($arg) use ($config) {
					if( array_key_exists( 'count', $arg ) ) {
						return $config['in-progress'];
					}

					return $config['results'];
				}
			 ));

		$this->assertSame($expected, $this->query->get_pending_jobs($config['total']));
	}

}
