<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Cron\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\DuplicatesCleanup;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::process_duplicates_cleanup
 *
 * @group RUCSS
 */
class Test_ProcessDuplicatesCleanup extends TestCase {
	private $cleanup;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->cleanup    = Mockery::mock( DuplicatesCleanup::class );
		$this->subscriber = new Subscriber( Mockery::mock( JobProcessor::class ), $this->createMock( UsedCSS_Query::class ), $this->cleanup );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRescheduleOnlyWhenUnfinished( $config, $expected ) {
		$this->cleanup->shouldReceive( 'run' )->once()->andReturn( $config['result'] );

		if ( false === $expected ) {
			Functions\expect( 'wp_schedule_single_event' )->never();
		} else {
			Functions\expect( 'wp_schedule_single_event' )
				->once()
				->with(
					Mockery::on(
						function ( $timestamp ) use ( $expected ) {
							$delay = $timestamp - time();

							return $delay >= $expected - 2 && $delay <= $expected;
						}
					),
					DuplicatesCleanup::HOOK
				);
		}

		$this->subscriber->process_duplicates_cleanup();
	}
}
