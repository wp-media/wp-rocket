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
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::clear_duplicates_cleanup
 *
 * @group RUCSS
 */
class Test_ClearDuplicatesCleanup extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldClearEvent( $config, $expected ) {
		$subscriber = new Subscriber(
			Mockery::mock( JobProcessor::class ),
			$this->createMock( UsedCSS_Query::class ),
			Mockery::mock( DuplicatesCleanup::class )
		);

		Functions\expect( 'wp_clear_scheduled_hook' )->once()->with( $expected );

		$subscriber->clear_duplicates_cleanup();
	}
}
