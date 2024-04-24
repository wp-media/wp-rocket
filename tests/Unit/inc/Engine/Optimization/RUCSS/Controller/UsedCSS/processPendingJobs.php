<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\UsedCSS;

use Mockery;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;


use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory\StrategyFactory;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::process_pending_jobs
 */
class Test_processPendingJobs extends TestCase {

	use HasLoggerTrait;

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var UsedCSS_Query
     */
    protected $used_css_query;

    /**
     * @var APIClient
     */
    protected $api;

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var DataManager
     */
    protected $data_manager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var UsedCSS
     */
    protected $usedcss;

	/**
	 * @var StrategyFactory
	 */
	protected $strategy_factory;

	/**
	 * @var WPRClock
	 */
	protected $wpr_clock;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->used_css_query = $this->createMock(UsedCSS_Query::class);
		$this->api = Mockery::mock(APIClient::class);
		$this->queue = Mockery::mock(QueueInterface::class);
		$this->data_manager = Mockery::mock(DataManager::class);
		$this->filesystem = Mockery::mock(Filesystem::class);
		$this->database = Mockery::mock(Database::class);
		$this->context = Mockery::mock(ContextInterface::class);
		$this->optimisedContext = Mockery::mock(ContextInterface::class);
		$this->strategy_factory = Mockery::mock(StrategyFactory::class);
		$this->wpr_clock = Mockery::mock(WPRClock::class);

		$this->usedcss = new UsedCSS($this->options, $this->used_css_query, $this->api, $this->queue, $this->data_manager, $this->filesystem, $this->context, $this->optimisedContext, $this->strategy_factory, $this->wpr_clock);
		$this->set_logger($this->usedcss);
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->wpr_clock->shouldReceive('current_time')->with('mysql', true)->zeroOrMoreTimes()->andReturn('2024-01-26');
		$this->options->allows()->get('remove_unused_css', 0)->andReturn($config['enabled']);

		$this->configureDisabled($config, $expected);
		$this->configureEnabled($config, $expected);
		$this->wpr_clock->shouldReceive( 'current_time' )
			->with( 'timestamp', true )
			->atMost()
			->once()
			->andReturn( 1700999999 );

		$this->usedcss->process_pending_jobs();
    }

	protected function configureDisabled($config, $expected) {
		if( $config['enabled']) {
			return;
		}
		$this->used_css_query->expects(self::never())->method('make_status_inprogress');
	}

	protected function configureEnabled($config, $expected) {
		if(! $config['enabled']) {
			return;
		}

		Filters\expectApplied('rocket_rucss_pending_jobs_cron_rows_count')->with(100)->andReturn($config['rows_count']);

		$this->used_css_query->expects(self::once())->method('get_pending_jobs')->with($expected['rows_count'])->willReturn($config['rows']);

		if( ! $expected['in_progress'] ) {
			return;
		}

		$this->queue->expects()->add_job_status_check_async($expected['in_progress']);
		$this->used_css_query->expects(self::atLeastOnce())->method('make_status_inprogress')->with($expected['in_progress']);
	}
}
