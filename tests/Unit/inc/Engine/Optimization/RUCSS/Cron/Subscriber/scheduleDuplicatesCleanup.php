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
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::schedule_duplicates_cleanup
 *
 * @group RUCSS
 */
class Test_ScheduleDuplicatesCleanup extends TestCase {
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
	public function testShouldScheduleOnlyWhenNeeded( $config, $expected ) {
		$this->cleanup->shouldReceive( 'is_done' )->andReturn( $config['done'] );

		Functions\when( 'wp_next_scheduled' )->justReturn( $config['scheduled'] );

		if ( $expected ) {
			Functions\expect( 'wp_schedule_single_event' )->once()->with( Mockery::type( 'int' ), DuplicatesCleanup::HOOK );
		} else {
			Functions\expect( 'wp_schedule_single_event' )->never();
		}

		$this->subscriber->schedule_duplicates_cleanup();
	}
}
