<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\JobProcessor;

use Mockery;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Factory as RUCSSFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\Factory as ATFFactory;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\JobProcessor::process_pending_jobs
 */
class Test_processPendingJobs extends TestCase {

	use HasLoggerTrait;

	protected $api;
	protected $queue;
	protected $strategy_factory;
	protected $wpr_clock;
	protected $job_processor;
	private $factories;
	private $manager;

	public function set_up() {
		parent::set_up();
		$this->api = Mockery::mock( APIClient::class );
		$this->queue = Mockery::mock( QueueInterface::class );
		$this->wpr_clock = Mockery::mock(WPRClock::class);
		$this->strategy_factory = Mockery::mock(StrategyFactory::class, [$this->wpr_clock]);
		$this->manager = Mockery::mock( Manager::class );

		$rucss_factory = Mockery::mock( RUCSSFactory::class );
		$atf_factory = Mockery::mock( ATFFactory::class );

		$this->factories = [
			$rucss_factory,
			$atf_factory,
		];

		$this->job_processor = new JobProcessor(
			$this->factories,
			$this->queue,
			$this->strategy_factory,
			$this->api,
			$this->wpr_clock
		);

		$this->set_logger($this->job_processor);
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		foreach ( $this->factories as $factory ) {
            $this->manager->shouldReceive( 'is_allowed' )
                ->andReturn( $config['enabled'] );

            $factory->shouldReceive('manager')
				->andReturn( $this->manager );
        }

		$this->configureDisabled($config, $expected);
		$this->configureEnabled($config, $expected);
		$this->wpr_clock->shouldReceive( 'current_time' )
			->with( 'timestamp', true )
			->atMost()
			->once()
			->andReturn( 1700999999 );

		$this->job_processor->process_pending_jobs();
    }

	protected function configureDisabled($config, $expected) {
		if( $config['enabled']) {
			return;
		}

		$this->manager->shouldReceive( 'make_status_inprogress' )
				->never();
	}

	protected function configureEnabled($config, $expected) {
		if(! $config['enabled']) {
			return;
		}

		Filters\expectApplied('rocket_saas_pending_jobs_cron_rows_count')->with(100)->andReturn($config['rows_count']);

		foreach ( $this->factories as $factory ) {
            $this->manager->shouldReceive( 'get_pending_jobs' )
				->with($config['rows_count'])
                ->andReturn( $config['rows'] );

            $factory->shouldReceive('manager')
				->andReturn( $this->manager );
        }

		if( ! $expected['in_progress'] ) {
			return;
		}

		foreach ( $this->factories as $factory ) {
			$this->manager->shouldReceive( 'get_optimization_type_from_row' )
					->with($config['rows'])
					->andReturn( 'all' );

			$factory->shouldReceive('manager')
				->andReturn( $this->manager );
		}

		$this->queue->shouldReceive( 'add_job_status_check_async' )
				->withArgs([$config['rows'][0]->url, $config['rows'][0]->is_mobile, 'all']);

		foreach ( $this->factories as $factory ) {
            $this->manager->shouldReceive( 'make_status_inprogress' )
				->withArgs([$config['rows'][0]->url, $config['rows'][0]->is_mobile, 'all']);

            $factory->shouldReceive('manager')
				->andReturn( $this->manager );
        }
	}
}
