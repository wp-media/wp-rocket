<?php

use Brain\Monkey\{Actions, Filters};
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\DefaultProcess::execute
 *
 * @group RUCSS
 */
class Test_Execute extends TestCase {
	protected $used_css_query;
	protected $wpr_clock;
	protected $strategy;

	public function setUp(): void {
		parent::setUp();
		$this->used_css_query = $this->createMock( UsedCSS_Query::class );
		$this->wpr_clock      = Mockery::mock( WPRClock::class );
		$this->strategy       = new DefaultProcess( $this->used_css_query, $this->wpr_clock );
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

			$this->used_css_query->expects( self::once() )
				->method( 'make_status_failed' )
				->with( $config['row_details']->id, strval( $config['job_details']['code'] ), $config['job_details']['message'] );
			$this->strategy->execute( $config['row_details'], $config['job_details'] );

			return;
		}

		$this->used_css_query->expects( self::once() )
		->method( 'increment_retries' )
		->with( $config['row_details']->id, (int) $config['job_details']['code'] );

		Filters\expectApplied( 'rocket_rucss_retry_duration' )->andReturn( $config['duration_retry'] );

		$this->wpr_clock->expects( 'current_time' )->with( 'timestamp', true )->andReturn( 0 );
		// update the `next_retry_time` column.

		$this->used_css_query->expects( self::once() )
			->method( 'update_message' )
			->with( $config['row_details']->id, $config['job_details']['code'], $config['job_details']['message'], $config['row_details']->error_message );
		$this->used_css_query->expects( self::once() )
		->method( 'update_next_retry_time' )
		->with( $config['job_id'], $config['duration_retry'] );

		$this->strategy->execute( $config['row_details'], $config['job_details'] );
	}
}
