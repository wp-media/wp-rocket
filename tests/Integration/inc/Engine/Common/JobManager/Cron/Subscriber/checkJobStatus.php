<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::check_job_status
 *
 * @group JobManager
 */
class Test_CheckJobStatus extends FilesystemTestCase {
	use HttpRequestTrait;

	protected $path_to_test_data = '/inc/Engine/Common/JobManager/Cron/Subscriber/checkJobStatus.php';

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists() requiring not temporary table.
		self::installUsedCssTable();
	}

	public static function tear_down_after_class() {
		self::uninstallUsedCssTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		self::installPreloadCacheTable();

		$this->setup_http();

		add_filter( 'rocket_rucss_hash', [ $this, 'rucss_hash' ] );
	}

	public function tear_down() {
		self::uninstallPreloadCacheTable();

		remove_filter( 'rocket_rucss_hash', [ $this, 'rucss_hash' ] );
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$this->config = $config;
		$this->config['http'] = $this->http_fixture( $config );
		self::addResource( $config['row'] );

		do_action( 'rocket_saas_job_check_status', $config['row']['url'], $config['row']['is_mobile'], $config['optimization_type'] );

		foreach ( $expected['rows'] as $row ) {
			self::assertTrue( self::resourceFound( $row ) );
		}
		foreach ( $expected['files'] as $path => $file ) {
			self::assertSame( $file['exists'], $this->filesystem->exists( $path ) );
		}
	}


	/**
	 * Builds the fixture: the status check (GET) comes first, then the re-submission (POST) on the same URL.
	 *
	 * @param array $config Data set config.
	 *
	 * @return array
	 */
	private function http_fixture( array $config ): array {
		if ( ! isset( $config['create'] ) ) {
			return [ $config['request']['url'] => $config['request']['response'] ];
		}

		return [ $config['request']['url'] => [ $config['request']['response'], $config['create']['response'] ] ];
	}

	public function rucss_hash() {
		return $this->config['hash'];
	}

	public function set_rucss_option() {
		return 1;
	}
}

