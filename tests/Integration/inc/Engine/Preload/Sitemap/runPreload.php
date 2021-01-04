<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Sitemap;

use Brain\Monkey\Actions;
use WP_Rocket\Engine\Preload\Sitemap;
use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Sitemap::run_preload
 * @uses   ::get_rocket_cache_reject_uri
 * @group  Preload
 */
class Test_RunPreload extends PreloadTestCase {
	protected $post_id;
	protected $setUpFilters = true;
	protected $tearDownFilters = true;

	public function setUp() {
		parent::setUp();

		$this->markTestSkipped(
			'Skipping sitemap test'
		  );
	}

	public function tearDown() {
		parent::tearDown();

		$this->post_id = null;
	}

	public function testShouldNotPreloadWhenNoUrls() {
		Actions\expectDone( 'before_run_rocket_sitemap_preload' )->never();

		// No URLs.
		( new Sitemap( $this->process ) )->run_preload( [] );
	}

	public function testShouldPreloadSitemapsWhenValidUrls() {
		$sitemaps = [
			"{$this->site_url}/mobile-preload-sitemap.xml",
			"{$this->site_url}/mobile-preload-sitemap-mobile.xml",
		];

		$this->setUpFilters();

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$expected = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => false, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => false, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => true, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => true, 'source' => 'sitemap' ],
		];

		$this->assertSame( $expected, $queue );
		$this->assertCount( 5, $queue );
	}

	public function testShouldPreloadFallbackUrlsWhenInvalidSitemap() {
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

		$expected = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => false, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => false, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => true, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => false, 'source' => 'sitemap' ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => true, 'source' => 'sitemap' ],
		];

		$this->assertSame( $expected, $queue );
		$this->assertCount( 6, $queue );

		wp_delete_post( $this->post_id, true );

		remove_filter( 'page_link', [ $this, 'change_page_link' ] );
	}

	public function change_page_link( $link, $post_id, $sample ) {
		if ( $sample || $post_id !== $this->post_id ) {
			return $link;
		}

		return 'https://smashingcoding.com/category/mobile-preload/';
	}
}
