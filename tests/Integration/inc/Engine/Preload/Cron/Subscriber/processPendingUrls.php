<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Cron\Subscriber::process_pending_urls
 *
 * @group Preload
 */
class Test_ProcessPendingUrls extends TestCase {
	protected $config;

	public function set_up() {
		self::installPreloadCacheTable();

		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		add_filter('rocket_preload_outdated', [$this, 'rocket_preload_outdated']);
		add_filter('rocket_preload_cache_pending_jobs_cron_rows_count', [$this, 'rocket_preload_cache_pending_jobs_cron_rows_count']);

		parent::set_up();
	}

	public function tear_down() {
		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		remove_filter('rocket_preload_outdated', [$this, 'rocket_preload_outdated']);
		remove_filter('rocket_preload_cache_pending_jobs_cron_rows_count', [$this, 'rocket_preload_cache_pending_jobs_cron_rows_count']);

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->config = $config;

		$container = apply_filters( 'rocket_container', null );
		$queue     = $container->get( 'preload_queue' );

		foreach ( $config['rows'] as $row ) {
			self::addCache( $row );
		}

		foreach ( $config['actions'] as $action ) {
			$queue->add_job_preload_job_preload_url_async( $action );
		}

		do_action( 'rocket_preload_process_pending' );

		foreach ( $expected['rows'] as $row ) {
			$this->assertTrue(self::cacheFound($row), json_encode($row) . ' not found');
		}

		foreach ( $expected['actions'] as $action ) {
			$this->assertSame($action['exists'], count($queue->search($action['args'])) > 0, json_encode($action));
		}
	}

	public function manual_preload() {
		return $this->config['manual_preload'];
	}

	public function rocket_preload_outdated() {
		return $this->config['rocket_preload_outdated'];
	}

	public function rocket_preload_cache_pending_jobs_cron_rows_count() {
		return $this->config['rocket_preload_cache_pending_jobs_cron_rows_count'];
	}
}
