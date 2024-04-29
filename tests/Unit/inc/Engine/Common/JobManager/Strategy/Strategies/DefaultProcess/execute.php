<?php

use Brain\Monkey\{Actions, Filters};
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\DefaultProcess::execute
 */
class Test_Execute extends TestCase {
	protected $used_css_query;
	protected $wpr_clock;
	protected $manager;

	protected $strategy;

	public function setUp(): void {
		parent::setUp();
		$this->wpr_clock = Mockery::mock(WPRClock::class);
		$this->manager = Mockery::mock( Manager::class );

		$this->strategy = new DefaultProcess($this->manager, $this->wpr_clock);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected ) {
		if ( $config['row_details']->retries >= count( $config['time_table'] ) ) {
			Actions\expectDone( 'rocket_preload_unlock_url' )->with( $config['row_details']->url );

			$this->manager->shouldReceive( 'make_status_failed' )
				->withArgs([$config['row_details']->url, $config['row_details']->is_mobile, strval($config['job_details']['code']), $config['job_details']['message']]);

			$this->strategy->execute($config['row_details'], $config['job_details']);
			return;
		}

		$this->manager->shouldReceive( 'increment_retries' )
			->withArgs([$config['row_details']->url, $config['row_details']->is_mobile, strval($config['job_details']['code']), $config['job_details']['message']]);

		Filters\expectApplied( 'rocket_rucss_retry_duration' )->andReturn( $config['duration_retry'] );

		$this->wpr_clock->expects( 'current_time' )->with( 'timestamp', true )->andReturn( 0 );
		// update the `next_retry_time` column.

		$this->manager->shouldReceive( 'update_message' )
			->withArgs([$config['row_details']->url, $config['row_details']->is_mobile, $config['job_details']['code'], $config['job_details']['message'], $config['row_details']->error_message]);
		
		$this->manager->shouldReceive( 'update_next_retry_time' )
			->withArgs([$config['row_details']->url, $config['row_details']->is_mobile, $config['duration_retry']]);

		$this->strategy->execute( $config['row_details'], $config['job_details'] );
	}
}
