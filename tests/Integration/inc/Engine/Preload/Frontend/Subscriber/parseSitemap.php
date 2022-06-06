<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\Subscriber::parse_sitemap
 * @group  Preload
 */
class Test_ParseSitemap extends AdminTestCase {

	use ASTrait;


	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
		$container             = apply_filters( 'rocket_container', null );
		$cache_table   = $container->get( 'preload_caches_table' );
		$cache_table->install();
	}

	public static function tear_down_after_class()
	{
		$container             = apply_filters( 'rocket_container', null );
		$cache_table   = $container->get( 'preload_caches_table' );
		if ( $cache_table->exists() ) {
			$cache_table->uninstall();
		}
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->configureRequest($config);

		do_action('rocket_preload_job_parse_sitemap', $config['sitemap_url']);

		foreach ($expected['children'] as $child) {
			$this->assertEquals($expected['children_exists'], self::taskExist('rocket_preload_job_parse_sitemap', [$child]));
		}

		foreach ($expected['links'] as $link) {
			$exists = $expected['links_exists'] ? "" :"n't";
			$this->assertEquals($expected['links_exists'], self::cacheFound(['url' => $link]), "Link {$link} should$exists exist");
		}
	}

	protected function configureRequest($config) {
		if ( ! isset( $config['process_generate'] ) ) {
			return;
		}

		if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with(
					$config['sitemap_url']
				)
				->andReturn( new WP_Error( 'error', 'error_data' ) );
		} else {
			$message = $config['process_generate']['response'];
			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with(
					$config['sitemap_url']
				)
				->andReturn( [ 'body' => $message, 'response' => ['code' => 200 ]] );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'parseSitemap' );
	}
}
