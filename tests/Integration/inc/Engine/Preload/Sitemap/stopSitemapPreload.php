<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Sitemap;

use Brain\Monkey\Actions;
use WP_Rocket\Engine\Preload\Sitemap;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Sitemap::stop_sitemap_preload
 * @uses   ::get_rocket_cache_reject_uri
 * @group  Preload
 */
class Test_StopSitemapPreload extends PreloadTestCase {
	protected $post_id;
	protected $setUpFilters = true;
	protected $tearDownFilters = true;
	use FilterTrait;
	public function setUp() : void {
		parent::setUp();
		$this->markTestSkipped(
			'Skipping sitemap test'
		);
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'stop_sitemap_preload', 9 );
		$this->unregisterAllCallbacksExcept( 'admin_post_rocket_rollback', 'stop_sitemap_preload', 9 );
	}
	public function tearDown() {
		parent::tearDown();
		wp_delete_post( $this->post_id, true );
		remove_filter( 'page_link', [ $this, 'change_page_link' ] );
		$this->post_id = null;
		$this->restoreWpFilter( 'wp_rocket_upgrade' );
		$this->restoreWpFilter( 'admin_post_rocket_rollback' );
	}


	public function testShouldStopSitemapPreloadRollback() {
		$this->startSitemapPreload();
		do_action('admin_post_rocket_rollback');
		$is_cron_exists= wp_get_schedule('rocket_preload_cron');

		$this->assertSame(  false , $is_cron_exists );
	}

	public function testShouldStopSitemapPreloadUpgrade() {
		$this->startSitemapPreload();
		do_action('wp_rocket_upgrade','3.9.4.1', '3.10');
		$is_cron_exists= wp_get_schedule('rocket_preload_cron');

		$this->assertSame(  false , $is_cron_exists );
	}
	private function startSitemapPreload(){
		$sitemaps = [
			"{$this->site_url}/mobile-preload-sitemap.xml",
			"{$this->site_url}/mobile-preload-sitemap-that-does-not-exist.xml",
		];

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Hoy',
				'post_content' => 'Hello World',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			]
		);

		if ( method_exists( $this, 'assertIsInt' ) ) {
			$this->assertIsInt( $this->post_id );
		} else {
			// Deprecated in phpunit 8.
			$this->assertInternalType( 'int', $this->post_id );
		}

		add_filter( 'page_link', [ $this, 'change_page_link' ], 10, 3 );

		$permalink = get_permalink( $this->post_id );

		$this->assertNotFalse( $permalink );

		$this->setUpFilters();

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );
	}

	public function change_page_link( $link, $post_id, $sample ) {
		if ( $sample || $post_id !== $this->post_id ) {
			return $link;
		}

		return 'https://smashingcoding.com/category/mobile-preload/';
	}
}
