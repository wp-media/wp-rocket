<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\{PreloadUrl,Queue};
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

class Test_CleanOnUpdate extends TestCase {
	protected $preload_url;
	protected $activation;
	protected $queue;
	protected $query;
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->preload_url = Mockery::mock( PreloadUrl::class );
		$this->queue      = Mockery::mock(Queue::class);
		$this->query      = $this->createMock(Cache::class);
		$this->options    = $this->createMock( Options_Data::class );
		$this->activation = new Activation( $this->preload_url, $this->queue, $this->query, $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {

		$this->configureCancelJobs($config);
		$this->configureCancelCron($config);
		$this->activation->clean_on_update($config['new_version'], $config['old_version']);
	}

	public function configureCancelJobs($config) {
		if($config['old_version'] !== '3.11.0') {
			$this->queue->expects()->cancel_pending_jobs()->never();
			return;
		}

		$this->queue->expects()->cancel_pending_jobs();

		Functions\expect('wp_next_scheduled')->with('rocket_preload_process_pending')->andReturn($config['cron_present']);
	}

	public function configureCancelCron($config) {
		if($config['old_version'] !== '3.11.0'|| ! $config['cron_present']) {
			return;
		}
		Functions\expect('wp_clear_scheduled_hook')->with('rocket_preload_process_pending');
	}
}
