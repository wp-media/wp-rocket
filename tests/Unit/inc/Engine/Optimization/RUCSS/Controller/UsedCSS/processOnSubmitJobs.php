<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\UsedCSS;

use Mockery;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use Brain\Monkey\Filters;

use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory\StrategyFactory;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::process_on_submit_jobs
 */
class Test_processOnSubmitJobs extends TestCase {

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
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var ContextInterface
     */
    protected $optimize_url_context;

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
        $this->context = Mockery::mock(ContextInterface::class);
        $this->optimize_url_context = Mockery::mock(ContextInterface::class);
		$this->strategy_factory = Mockery::mock(StrategyFactory::class);
		$this->wpr_clock = Mockery::mock(WPRClock::class);

        $this->usedcss = new UsedCSS($this->options, $this->used_css_query, $this->api, $this->queue, $this->data_manager, $this->filesystem, $this->context, $this->optimize_url_context, $this->strategy_factory, $this->wpr_clock);

		$this->set_logger($this->usedcss);
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {

		$this->options->expects()->get( 'remove_unused_css', 0 )->andReturn($config['rucss_enabled']);

		if($config['rucss_enabled']) {
			$this->logger->expects()->error(Mockery::any(), Mockery::any());
			$this->options->allows()->get( 'remove_unused_css_safelist', [] )->andReturn([]);

			Functions\when('home_url')->justReturn($config['home_url']);

			Filters\expectApplied('rocket_rucss_pending_jobs_cron_rows_count')->with(100)->andReturn($config['pending_count']);
			Filters\expectApplied('rocket_rucss_max_pending_jobs')->with(3 * $expected['pending_count'])->andReturn($config['max_processing']);

			$this->used_css_query->expects(self::once())->method('get_on_submit_jobs')->with($expected['max_processing'])->willReturn($config['rows']);

			foreach ($config['add_to_queue'] as $queue) {
				$this->api->expects()->add_to_queue($queue['url'], $queue['configs'])->andReturn($queue['response']);
			}

			foreach ($config['make_status_pending'] as $pending) {
				$this->used_css_query->expects(self::once())->method('make_status_pending')->with($pending['id'], $pending['jobId'], $pending['queueName'], $pending['mobile']);
			}

			foreach ($config['make_status_failed'] as $failed) {
				$this->used_css_query->expects(self::once())->method('make_status_failed')->with($failed['id'], $failed['code'], $failed['message']);
			}
		} else {
			$this->logger->expects()->debug(Mockery::any());
		}

		$this->usedcss->process_on_submit_jobs();
	}
}
