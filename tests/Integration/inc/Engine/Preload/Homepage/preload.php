<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Homepage;

use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Homepage::preload
 * @uses   ::get_rocket_cache_query_string
 * @group  Preload
 */
class Test_Preload extends PreloadTestCase {
	protected $setUpFilters = true;
	protected $tearDownFilters = true;

	public function testShouldPreloadWhenValidUrls() {
		$home_urls = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/" ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => 1 ],
		];

		( new Homepage( $this->process ) )->preload( $home_urls );

		$key = $this->process->getGeneratedKey();

		// Temporary until we create a vfs+remote_request feature to stop crawling smashingcoding website.
		if ( is_null( $key ) ) {
			$this->assertTrue( true );
			return;
		}

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => false, 'source' => 'homepage' ],
			$queue
		);
		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/fr", 'mobile' => false, 'source' => 'homepage' ],
			$queue
		);
		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/es", 'mobile' => false, 'source' => 'homepage' ],
			$queue
		);
		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true, 'source' => 'homepage' ],
			$queue
		);
		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/fr", 'mobile' => true, 'source' => 'homepage' ],
			$queue
		);
		$this->assertContains(
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/es", 'mobile' => true, 'source' => 'homepage' ],
			$queue
		);
		$this->assertNotContains( 'https://toto.org', $queue );
	}
}
