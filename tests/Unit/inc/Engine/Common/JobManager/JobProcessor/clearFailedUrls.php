<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\JobProcessor;

use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Factory as RUCSSFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\Factory as ATFFactory;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\JobProcessor::clear_failed_urls
 *
 */
class Test_ClearFailedUrls extends TestCase {
	protected $api;
	protected $queue;
	protected $strategy_factory;
	protected $wpr_clock;
	protected $job_processor;
	private $factories;
	private $manager;
	private $key_factories;

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

		$this->key_factories = [
			'rucss' => $rucss_factory,
			'atf' => $atf_factory,
		];

		$this->job_processor = new JobProcessor(
			$this->factories,
			$this->queue,
			$this->strategy_factory,
			$this->api,
			$this->wpr_clock
		);
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		foreach ( $this->key_factories as $key => $factory ) {
			$this->manager->shouldReceive( 'is_allowed' )->andReturn( $config['is_allowed'] );

			if ( $config['is_allowed'] ) {
				$this->manager->shouldReceive( 'clear_failed_jobs' )
					->withArgs([$config['value'], $config['unit']])
					->andReturn( $expected['failed_urls'] );

				$this->manager->shouldReceive( 'get_optimization_type' )
					->andReturn( $config['optimization_type'] );
					
				if ( $config['optimization_type'] === $key ) {
					Actions\expectDone( 'rocket_' . $key . '_after_clearing_failed_url' )->with( $expected['failed_urls'] );
				}
				else{
					Actions\expectDone( 'rocket_' . $key . '_after_clearing_failed_url' )->never();
				}
			}
			else {
				$factory->expects()->manager()->never();
				Actions\expectDone( 'rocket_' . $key . '_after_clearing_failed_url' )->never();
			}

			$factory->shouldReceive('manager')
					->andReturn( $this->manager );
		}

        $this->job_processor->clear_failed_urls();
	}
}
