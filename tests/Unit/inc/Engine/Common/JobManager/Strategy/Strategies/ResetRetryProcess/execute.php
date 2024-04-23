<?php

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\ResetRetryProcess;
use Brain\Monkey\Functions;


/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\ResetRetryProcess::execute
 */
class Test_ResetRetryProcess_Execute extends TestCase {
	protected $used_css_query;
	protected $strategy;

	public function setUp():void {
		parent::setUp();

		$this->manager = Mockery::mock( Manager::class );
		$this->strategy = new ResetRetryProcess($this->manager);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected )
	{
		$this->manager->shouldReceive( 'add_url_to_the_queue' )
			->once()
			->withArgs([$config['row_details']->url,$config['row_details']->is_mobile]);

		$this->strategy->execute($config['row_details'], $config['job_details']);
	}
}
