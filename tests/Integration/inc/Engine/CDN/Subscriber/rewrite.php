<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite
 *
 * @uses \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group CDN
 */
class Test_Rewrite extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 20 );
	}

	public function tear_down() {
		remove_filter( 'content_url', [ $this, 'setContentURL' ] );
		remove_filter( 'includes_url', [ $this, 'setIncludesURL' ] );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURL( $home_url, $original, $expected ) {
		$this->cnames       = [
			'cdn.example.org',
		];
		$this->cdn_zone     = [
			'all',
		];
		$this->home_url     = $home_url;
		$this->content_url  = "{$home_url}/wp-content/";
		$this->includes_url = "{$home_url}/wp-includes/";

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		add_filter( 'home_url', [ $this, 'setHomeURL' ] );
		add_filter( 'content_url', [ $this, 'setContentURL' ] );
		add_filter( 'includes_url', [ $this, 'setIncludesURL' ] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewrite' );
	}
}
