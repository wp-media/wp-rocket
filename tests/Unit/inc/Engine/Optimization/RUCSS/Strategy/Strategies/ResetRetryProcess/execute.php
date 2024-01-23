<?php

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess;
use Brain\Monkey\Functions;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess::execute
 *
 * @group  RUCSS
 */
class Test_ResetRetryProcess_Execute extends TestCase {
	protected $used_css_query;
	protected $strategy;

	public function setUp():void {
		parent::setUp();

		$this->used_css_query = $this->createMock( UsedCSS_Query::class );
		$this->strategy = new ResetRetryProcess($this->used_css_query);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected )
	{
		$this->used_css_query->expects(self::once())
			->method('get_row')
			->with($config['row_details']->url, $config['row_details']->is_mobile)
			->willReturn($config['row_details']);


		if ( empty( $config['row_details'] ) ) {
			$this->used_css_query->expects(self::once())
				->method('create_new_job')
				->with($config['row_details']->url, $config['row_details']->job_id, $config['row_details']->queue_name, $config['row_details']->is_mobile);
				$this->strategy->execute($config['row_details'], $config['job_details']);

			return;
		}
		$this->used_css_query->expects(self::once())->method('reset_job')->with($config['row_details']->id);

		$this->strategy->execute($config['row_details'], $config['job_details']);
	}
}
