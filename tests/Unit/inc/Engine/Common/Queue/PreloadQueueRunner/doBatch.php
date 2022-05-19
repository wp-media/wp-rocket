<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\PreloadQueueRunner;

use ActionScheduler_ActionClaim;
use ActionScheduler_AsyncRequest_QueueRunner;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_Store;
use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class Test_DoBatch extends TestCase
{
	protected $queueRunner;
	protected $store;
	protected $monitor;
	protected $claim;

	protected function setUp(): void
	{
		parent::setUp();
		\Mockery::mock('alias:'. ActionScheduler_AsyncRequest_QueueRunner::class);
		$this->store = Mockery::mock(ActionScheduler_Store::class);
		$this->monitor = Mockery::mock(ActionScheduler_FatalErrorMonitor::class);
		$this->queueRunner = Mockery::mock(PreloadQueueRunner::class. '[process_action,batch_limits_exceeded]',
			[$this->store, $this->monitor]);
		$this->claim = Mockery::mock(ActionScheduler_ActionClaim::class);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->store->expects()->stake_claim($config['size'], null, [], $config['group'])->andReturn($this->claim);
		$this->monitor->expects()->attach($this->claim);
		$this->claim->expects()->get_actions()->andReturn($config['action_ids']);

		$this->configureClaim($config);
		$this->configureActions($config);
		$this->configureClearCache($config);

		$this->store->expects()->release_claim($this->claim);
		$this->monitor->expects()->detach();

		$this->assertSame($expected, $this->queueRunner->do_batch($config['size'], $config['context']));
	}

	protected function configureClaim($config) {
		if(count($config['action_ids']) === 0) {
			return;
		}
		$this->store->shouldReceive('find_actions_by_claim_id')->with($config['claim_id'])->andReturn($config['claim_actions_ids'])->atLeast()->once();
		$this->claim->shouldReceive('get_id')->andReturn($config['claim_id'])->atLeast()->once();
	}

	protected function configureActions($config) {
		if(count($config['claim_actions_ids']) === 0) {
			return;
		}
		foreach ($config['action_ids'] as $index => $id) {
			$this->queueRunner->expects()->process_action($id, $config['context']);
			$this->queueRunner->expects()->batch_limits_exceeded($index + 1)->andReturn($config['action_max'][$index]);
		}
	}

	protected function configureClearCache($config) {
		Functions\expect('wp_using_ext_object_cache')->andReturn($config['is_using_object_cache']);
		if($config['is_using_object_cache']) {
			Filters\expectApplied('action_scheduler_queue_runner_flush_cache')->with(false)->andReturn($config['flush_cache']);
		}
		if(! $config['flush_cache'] && $config['is_using_object_cache']) {
			return;
		}
		Functions\expect('wp_cache_flush');
	}
}
