<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::protocol_rewrite_srcset
 *
 * @group Cloudflare
 */
class TestProtocolRewriteSrcset extends TestCase {
	private $cf_option;
	private $cf_rewrite;
	private $filter;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'wp_calculate_image_srcset', 'protocol_rewrite_srcset', PHP_INT_MAX );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_do_cloudflare', [ $this, 'set_cloudflare'] );
		remove_filter( 'pre_get_rocket_option_cloudflare_protocol_rewrite', [ $this, 'set_rewrite'] );
		remove_filter( 'do_rocket_protocol_rewrite', [ $this, 'set_filter'] );

		$this->restoreWpHook( 'wp_calculate_image_srcset' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $value, $expected ) {
		$this->cf_option = $config['cloudflare'];
		$this->cf_rewrite = $config['rewrite'];
		$this->filter = $config['filter'];

		add_filter( 'pre_get_rocket_option_do_cloudflare', [ $this, 'set_cloudflare'] );
		add_filter( 'pre_get_rocket_option_cloudflare_protocol_rewrite', [ $this, 'set_rewrite'] );
		add_filter( 'do_rocket_protocol_rewrite', [ $this, 'set_filter'] );

		$this->assertSame(
			$expected,
			apply_filters( 'wp_calculate_image_srcset', $value )
		);
	}

	public function set_cloudflare() {
		return $this->cf_option;
	}

	public function set_rewrite() {
		return $this->cf_rewrite;
	}

	public function set_filter() {
		return $this->filter;
	}
}
