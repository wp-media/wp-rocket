<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\HTTPCallTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::process_on_submit_jobs
 *
 * @group JobManager
 */
class Test_ProcessOnSubmitJobs extends TestCase {

	use HTTPCallTrait;

	protected $config;

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();
		self::installPreloadCacheTable();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		add_filter( 'rocket_saas_max_pending_jobs', [ $this, 'max_rows' ] );
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss_enabled' ] );
		$this->setup_http();
	}

	public function tear_down() {
		self::uninstallUsedCssTable();
		self::uninstallPreloadCacheTable();

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		$this->tear_down_http();

		remove_filter( 'rocket_saas_max_pending_jobs', [ $this, 'max_rows' ] );
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss_enabled' ] );

		parent::tear_down();
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
