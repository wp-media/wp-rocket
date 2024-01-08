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
use Brain\Monkey\Filters;

use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\JobProcessor::process_on_submit_jobs
 */
class Test_processOnSubmitJobs extends TestCase {

	use HasLoggerTrait;

    protected $api;
	protected $queue;
	protected $strategy_factory;
	protected $wpr_clock;
	protected $job_processor;
	private $factories;
	private $manager;

    protected function setUp(): void {
        parent::setUp();
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
                ->andReturn( $config['is_allowed'] );

            $factory->shouldReceive('manager')
				->andReturn( $this->manager );
        }

        if ( ! $config['is_allowed'] ) {
            $this->manager->shouldReceive( 'get_on_submit_jobs' )->never();

            $this->job_processor->process_on_submit_jobs();
            return;
        }

        Filters\expectApplied('rocket_saas_pending_jobs_cron_rows_count')->with(100)->andReturn($config['pending_count']);
        Filters\expectApplied('rocket_saas_max_pending_jobs')->with(3 * $expected['pending_count'])->andReturn($config['max_processing']);

        foreach ( $this->factories as $factory ) {
            $this->manager->shouldReceive( 'get_on_submit_jobs' )
                ->andReturn( $config['rows'] );

            $factory->shouldReceive('manager')
				->andReturn( $this->manager );
        }

        foreach ( $this->factories as $factory ) {
			$this->manager->shouldReceive( 'get_optimization_type_from_row' )
					->with($config['rows'])
					->andReturn( 'all' );

			$factory->shouldReceive('manager')
				->andReturn( $this->manager );
		}

        Functions\when('home_url')->justReturn($config['home_url']);

        foreach ( $this->factories as $factory ) {
            $this->manager->shouldReceive( 'get_optimization_type' )
                ->andReturn( 'all' );

            $this->manager->shouldReceive( 'set_request_param' )
                ->andReturn( $config['add_to_queue'][0]['configs'] );

            $factory->shouldReceive('manager')
                    ->andReturn( $this->manager );
        }

        $this->api->expects()->add_to_queue($config['add_to_queue'][0]['url'], $config['add_to_queue'][0]['configs'])->andReturn($config['add_to_queue'][0]['response']);

        if ( 200 !== $config['add_to_queue'][0]['response']['code'] ) {
            foreach ( $this->factories as $factory ) {
    
                $this->manager->shouldReceive( 'make_status_failed' )
                    ->withArgs([$config['make_status_failed'][0]['url'], $config['make_status_failed'][0]['is_mobile'], $config['make_status_failed'][0]['code'], $config['make_status_failed'][0]['message'], 'all']);
    
                $factory->shouldReceive('manager')
                        ->andReturn( $this->manager );
            }

            $this->logger->expects()->error()->with($config['logger']['message'], $config['logger']['details']);

            $this->job_processor->process_on_submit_jobs();
            return;
        }

        foreach ( $this->factories as $factory ) {
    
            $this->manager->shouldReceive( 'make_status_pending' )
                ->withArgs([$config['make_status_pending'][0]['url'], $config['make_status_pending'][0]['jobId'], $config['make_status_pending'][0]['queueName'], $config['make_status_pending'][0]['mobile'], 'all']);

            $factory->shouldReceive('manager')
                    ->andReturn( $this->manager );
        }

		$this->job_processor->process_on_submit_jobs();
	}
}
