<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\ASTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\Subscriber::parse_sitemap
 * @group  Preload
 */
class Test_ParseSitemap extends TestCase {

	use ASTrait, DBTrait;

	protected $config;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up()
	{
		parent::set_up();
		add_filter('rocket_preload_query_string', [$this, 'query_enabled']);
		add_filter('rocket_cache_ignored_parameters', [$this, 'excluded_query_params']);
	}

	public function tear_down()
	{
		remove_filter('rocket_preload_query_string', [$this, 'query_enabled']);
		remove_filter('rocket_cache_ignored_parameters', [$this, 'excluded_query_params']);
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->config = $config;

		add_filter('pre_http_request', [$this, 'requestResult']);

		do_action('rocket_preload_job_parse_sitemap', $config['sitemap_url']);

		foreach ($expected['children'] as $child) {
			$this->assertEquals($expected['children_exists'], self::taskExist('rocket_preload_job_parse_sitemap', [$child]));
		}

		foreach ($expected['links'] as $link) {
			$exists = $expected['links_exists'] ? "" :"n't";
			$this->assertEquals($expected['links_exists'], self::cacheFound(['url' => $link]), "Link {$link} should$exists exist");
		}
	}

	public function requestResult() {
		if ( ! empty( $this->config['process_generate']['is_wp_error'] ) ) {
			return new WP_Error( 'error', 'error_data' );
		} else {
			$message = $this->config['process_generate']['response'];
			return [ 'body' => $message, 'response' => ['code' => 200 ]];
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'parseSitemap' );
	}

	public function query_enabled() {
		return $this->config['query_enabled'];
	}

	public function excluded_query_params($exclusions) {
		return  array_merge($exclusions, $this->config['excluded_params']);
	}
}
