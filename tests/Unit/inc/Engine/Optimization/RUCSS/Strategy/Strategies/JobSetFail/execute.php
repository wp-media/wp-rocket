<?php

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobSetFail;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobSetFail::execute
 *
 * @group  RUCSS
 */
class Test_JobSetFail_Execute extends TestCase {

	/**
	 * UsedCSS_Query mock.
	 *
	 * @var UsedCSS_Query
	 */
	protected $usedCssQuery;

	public function setUp():void {
		parent::setUp();
		$this->usedCssQuery = $this->createMock( UsedCSS_Query::class );

	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected )
	{
		if ( $config['row_details'] ) {
			$row_details = new UsedCSS_Row( $config['row_details'] );
		} else {
			$row_details = null;
		}
		if ( isset( $config['job_details'] ) ) {
			$job_details = $config['job_details'];
		}

		Actions\expectDone('rocket_preload_unlock_url')->once();

		$this->usedCssQuery->expects( self::once() )
			->method( 'make_status_failed' )
			->with( $config['job_id'], $job_details['code'], $job_details['message'] );

		$strategy = new JobSetFail($this->usedCssQuery);
		$strategy->execute($row_details, $job_details);
	}
}
