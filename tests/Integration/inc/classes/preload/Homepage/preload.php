<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Homepage;

use WP_Rocket\Tests\Integration\PreloadTestCase as TestCase;
use WP_Rocket\Preload\Homepage;

/**
 * @covers \WP_Rocket\Preload\Homepage::preload
 * @group Preload
 */
class Test_Preload extends TestCase {

	public function testShouldPreloadWhenValidUrls() {
		$home_urls = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/" ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => 1 ],
		];

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );

		delete_transient( 'rocket_preload_errors' );

		( new Homepage( $this->process ) )->preload( $home_urls );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		// This one is excluded when requesting "{$this->site_url}/mobile-preload-homepage/", but included when requesting "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" and "{$this->site_url}/category/mobile-preload/".
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/", $queue );
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/fr", $queue );
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/es", $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/fr", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/es", 'mobile' => true ], $queue );
		$this->assertNotContains( 'https://toto.org', $queue );
	}
}
