<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite_srcset
 *
 * @uses \WP_Rocket\Engine\CDN\CDN::rewrite_srcset
 *
 * @group CDN
 */
class Test_RewriteSrcset extends TestCase {
	public function set_up() {
		parent::set_up();

		// Set after parent::set_up() so it stays inside the test transaction and is rolled back.
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ] );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRewriteSrcsetURLs( $options ) {
		$this->cnames    = $options['cdn_cnames']['value'];
		$this->cdn_zone  = $options['cdn_zone']['value'];

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );

		$this->assertSame(
			$this->format_the_html( $this->config['expected'] ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $this->config['original'] ) )
		);
	}
}
