<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\ASTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Frontend\Subscriber::parse_sitemap
 *
 * @group Preload
 */
class Test_ParseSitemap extends TestCase {
	use ASTrait;

	protected $config;

	public function set_up() {
		parent::set_up();

		// Install the preload cache table.
		self::installPreloadCacheTable();
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

		$this->config = $config;

		add_filter( 'pre_http_request', [ $this, 'requestResult' ] );

		do_action( 'rocket_preload_job_parse_sitemap', $config['sitemap_url'] );

		foreach ( $expected['children'] as $child ) {
			$this->assertEquals(
				$expected['children_exists'],
				self::taskExist( 'rocket_preload_job_parse_sitemap', [ $child ] )
			);
		}

		foreach ( $expected['links'] as $link ) {
			$exists = $expected['links_exists'] ? '' : "n't";
			$this->assertEquals(
				$expected['links_exists'],
				self::cacheFound( [ 'url' => $link ] ), "Link {$link} should$exists exist"
			);
		}
	}

	public function requestResult() {
		if ( ! empty( $this->config['process_generate']['is_wp_error'] ) ) {
			return new WP_Error( 'error', 'error_data' );
		} else {
			$message = $this->config['process_generate']['response'];
			return [ 'body' => $message, 'response' => [ 'code' => 200 ] ];
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'parseSitemap' );
	}
}
