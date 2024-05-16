<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::maybe_load_initial_sitemap
 *
 * @group Preload
 */
class Test_LoadInitialSitemap extends AdminTestCase {
	use ASTrait;

	protected $sitemaps;

	public function set_up() {
		parent::set_up();

		add_filter( 'rocket_sitemap_preload_list', [ $this, 'return_sitemaps' ] );
	}

	public function tear_down() {
		remove_filter( 'rocket_sitemap_preload_list', [ $this, 'return_sitemaps' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->sitemaps = $config['return_sitemaps'];

		do_action( 'update_option_wp_rocket_settings', $config['old_values'], $config['values'] );

		foreach ( $expected['sitemaps'] as $sitemap ) {
			$this->assertEquals(
				$expected['exist'],
				self::taskExist( 'rocket_preload_job_parse_sitemap', [ $sitemap ] )
			);
		}
	}

	public function return_sitemaps() {
		return $this->sitemaps;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'loadInitialSitemap' );
	}
}
