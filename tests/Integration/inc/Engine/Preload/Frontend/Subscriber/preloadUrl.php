<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Frontend\Subscriber::preload_url
 *
 * @group Preload
 */
class Test_PreloadUrl extends AdminTestCase {
	use ASTrait, HttpRequestTrait;

	protected $mobile_cache;

	protected $config;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		self::installPreloadCacheTable();

		add_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'mobile_cache']);
	}

	public function tear_down() {
		self::uninstallPreloadCacheTable();

		remove_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'mobile_cache']);

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->config = $config;
		// One response answers both the desktop and the mobile preload requests.
		$this->config['http'] = [ user_trailingslashit( $config['url'] ) => $this->preload_response() ];

		$this->mobile_cache = $config['mobile_cache'];


		self::addCache($config['existing_job']);

		do_action('rocket_preload_job_preload_url', $config['url']);

		$this->assertTrue(self::cacheFound($expected));
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadUrl' );
	}

	public function mobile_cache() {
		return $this->mobile_cache;
	}

	/**
	 * Builds the preload response from the data set.
	 *
	 * @return array|WP_Error
	 */
	private function preload_response() {
		if ( ! empty( $this->config['process_generate']['is_wp_error'] ) ) {
			return new WP_Error( 'error', 'error_data' );
		} else {
			$message = $this->config['process_generate']['response'];
			return [ 'body' => $message, 'response' => ['code' => 200 ]];
		}
	}
}
