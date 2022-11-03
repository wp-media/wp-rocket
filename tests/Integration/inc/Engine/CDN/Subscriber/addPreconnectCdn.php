<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::add_preconnect_cdn
 * @uses   \WP_Rocket\Engine\CDN\CDN::get_cdn_urls
 * @group  CDN
 */
class Test_addPreconnectCdn extends TestCase {

	public function set_up() {
		$this->unregisterAllCallbacksExcept( 'wp_resource_hints', 'add_preconnect_cdn', 10 );

		parent::set_up();
	}

	public function tear_down() {
		$this->restoreWpFilter( 'wp_resource_hints' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddPreconnectCdn($cnames, $expected) {
		$this->cnames = $cnames;

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true'] );
		add_filter( 'rocket_cdn_cnames', [ $this, 'setCnames' ] );

		ob_start();
		wp_resource_hints();
		$expected_str = $expected['new'];
		if ( substr( get_bloginfo( 'version' ), 0, 3 ) === '5.6') {
			$expected_str = $expected['legacy'];
		}
		$this->assertSame(
			$this->format_the_html($expected_str),
			$this->format_the_html(ob_get_clean())
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'add-preconnect-cdn' );
	}
}
