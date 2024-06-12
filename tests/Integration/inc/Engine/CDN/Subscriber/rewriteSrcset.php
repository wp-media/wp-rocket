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

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

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
