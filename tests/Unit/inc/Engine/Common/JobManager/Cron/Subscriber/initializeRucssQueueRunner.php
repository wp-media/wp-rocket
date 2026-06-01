<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\Cron\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Factory as RIFactory;
use WP_Rocket\Engine\Common\JobManager\Cron\Subscriber;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Factory as RUCSSFactory;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::initialize_rucss_queue_runner
 *
 * Covers early-return (guard) cases only. The happy-path (RUCSS allowed → init() called)
 * is an integration concern because RUCSSQueueRunner uses a static singleton backed by Action Scheduler.
 */
class Test_InitializeRucssQueueRunner extends TestCase {

	/**
	 * Test initialize_rucss_queue_runner returns early when RUCSS is not allowed.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config Test configuration.
	 */
	public function testShouldDoExpected( array $config ): void {
		$factories = [];

		if ( $config['has_rucss_factory'] ) {
			$rucss_factory = Mockery::mock( RUCSSFactory::class );
			$manager       = Mockery::mock( Manager::class );

			$manager->shouldReceive( 'is_allowed' )
				->once()
				->andReturn( $config['rucss_is_allowed'] );
			$rucss_factory->shouldReceive( 'manager' )
				->andReturn( $manager );

			$factories['rucss'] = $rucss_factory;
		}

		// Rocket Insights factory must never be consulted by this method.
		$ri_factory = Mockery::mock( RIFactory::class );
		$ri_factory->shouldNotReceive( 'manager' );
		$factories['rocket_insights'] = $ri_factory;

		$subscriber = new Subscriber( Mockery::mock( JobProcessor::class ), $factories );

		// Should return early without error in all guard cases.
		$subscriber->initialize_rucss_queue_runner();
	}
}
