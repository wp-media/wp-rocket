<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::process_on_submit_jobs
 *
 * @group JobManager
 */
class Test_ProcessOnSubmitJobs extends TestCase {

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPerformanceMonitoringTable();
		self::installUsedCssTable();
		self::installPreloadCacheTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();
		self::uninstallUsedCssTable();
		self::uninstallPreloadCacheTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'rocket_saas_max_pending_jobs', [ $this, 'max_rows' ] );
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss_enabled' ] );
		add_filter( 'rocket_rocket_insights_enabled', '__return_false' );
		add_filter( 'pre_http_request', [ $this, 'http_callback' ], 10, 3 );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'http_callback' ], 10 );

		remove_filter( 'rocket_saas_max_pending_jobs', [ $this, 'max_rows' ] );
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss_enabled' ] );
		remove_filter( 'rocket_rocket_insights_enabled', '__return_false' );

		parent::tear_down();
	}

	public function http_callback( $preempt, $args, $url ) {
		if ( ! empty( $this->config['http'][ $url ] ) ) {
			return $this->config['http'][ $url ];
		}
		return $preempt;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->config = $config;

		foreach ( $config['rows'] as $row ) {
			self::addResource( $row );
		}

		do_action( 'rocket_saas_on_submit_jobs' );

		foreach ( $expected['rows'] as $row ) {
			$this->assertTrue( self::resourceFound( $row ), json_encode( $row ) . ' not found' );
		}
	}

	public function max_rows() {
		return $this->config['max_rows'];
	}

	public function rucss_enabled() {
		return $this->config['rucss_enabled'];
	}
}
