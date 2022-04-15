<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::rewrite_srcset
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite_srcset
 * @group  CDN
 */
class Test_RewriteSrcset extends TestCase {
	public function set_up() {
		parent::set_up();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
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

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'rewriteSrcset' );
	}
}
