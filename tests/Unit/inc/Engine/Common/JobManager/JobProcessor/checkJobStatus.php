<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\JobProcessor;

use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Factory as RUCSSFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\Factory as ATFFactory;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Rows\AboveTheFold as AboveTheFold_Row;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\JobProcessor::check_job_status
 *
 */
class Test_CheckJobStatus extends TestCase {
	use \WP_Rocket\Tests\Unit\HasLoggerTrait;

	protected $api;
	protected $queue;
	protected $strategy_factory;
	protected $wpr_clock;
	protected $job_processor;
	private $factories;
	private $manager;
	private $row_details;
	private $job_details;

	protected function setUp(): void {
		parent::setUp();
		$this->api = Mockery::mock( APIClient::class );
		$this->queue = Mockery::mock( QueueInterface::class );
		$this->wpr_clock = Mockery::mock(WPRClock::class);
		$this->strategy_factory = Mockery::mock(StrategyFactory::class, [$this->wpr_clock]);
		$this->manager = Mockery::mock( Manager::class );
		$this->factories = [
			Mockery::mock( RUCSSFactory::class ),
			Mockery::mock( ATFFactory::class ),
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

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->logger->allows()->error(Mockery::any());

		$this->row_details = new UsedCSS_Row( $config['row_details'] );

		switch ( $config['optimization_type'] ) {
			case 'rucss':
				$this->row_details = $this->row_details;
				break;
			case 'atf':
				$this->row_details = new AboveTheFold_Row( $config['row_details'] );
				break;
		}

		$this->job_details = $config['job_details'];

		$this->get_single_job( $config );

		Functions\expect( 'home_url' )
			->with()
			->zeroOrMoreTimes()
			->andReturn( $this->row_details->url );
		
		$this->api->shouldReceive('get_queue_job_status')
				->withArgs([$this->row_details->job_id, $this->row_details->queue_name, $this->row_details->is_home])
				->andReturn( $this->job_details );

		$this->validate_and_fail( $config );

		if ( 200 !== $this->job_details['code'] ) {
			$this->manager->shouldReceive( 'get_optimization_type' )
				->andReturn( $config['optimization_type'] );

			foreach ( $this->factories as $factory ) {
				$factory->shouldReceive('manager')
					->andReturn( $this->manager );
			}
			$this->strategy_factory->expects( 'manage' )
				->atLeast()
				->once()
				->with( $this->row_details, $this->job_details, $this->manager );

			$this->job_processor->check_job_status( $this->row_details->url, $this->row_details->is_mobile, $config['optimization_type'] );
			return;
		}

		Actions\expectDone('rocket_preload_unlock_url')->with( $this->row_details->url );

		$this->process( $config );

		Actions\expectDone('rocket_saas_complete_job_status')->with( $this->row_details->url, $this->job_details );

		$this->job_processor->check_job_status( $this->row_details->url, $this->row_details->is_mobile, $config['optimization_type'] );
	}

	private function get_single_job( $config ) {

		foreach ( $this->factories as $factory ) {	
			$this->manager->shouldReceive( 'get_optimization_type' )
				->andReturn( $config['optimization_type'] );

			$this->manager->shouldReceive( 'get_single_job' )
				->withArgs([$this->row_details->url, $this->row_details->is_mobile])
				->andReturn( $this->row_details );

				$factory->shouldReceive('manager')
					->andReturn( $this->manager );
		}
	}

	private function validate_and_fail( $config ) {
		foreach ( $this->factories as $factory ) {
			$this->manager->shouldReceive( 'validate_and_fail' )
			->withArgs([$this->job_details, $this->row_details, $config['optimization_type']]);

			$factory->shouldReceive('manager')
					->andReturn( $this->manager );
		}
	}

	private function process( $config ) {
		foreach ( $this->factories as $factory ) {
			$this->manager->shouldReceive( 'process' )
				->withArgs([$this->job_details, $this->row_details, $config['optimization_type']]);

			$factory->shouldReceive('manager')
					->andReturn( $this->manager );
		}
	}
}
