<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::add_preconnect_cdn
 * @uses   \WP_Rocket\Engine\CDN\CDN::get_cdn_urls
 * @group  CDN
 */
class Test_addPreconnectCdn extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddPreconnectCdn($cnames, $expected) {
		$this->cnames = $cnames;
//		$this->cnames = [
//			'https://123456.rocketcdn.me',
//		];

//		$html = <<<HTML
//<link rel='dns-prefetch' href='//s.w.org' />
//<link rel='dns-prefetch' href='//123456.rocketcdn.me' />
//<link href='https://123456.rocketcdn.me' rel='preconnect' />
//HTML;


		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true'] );
		add_filter( 'rocket_cdn_cnames', [ $this, 'setCnames' ] );

		ob_start();
		wp_resource_hints();

		$this->assertSame(
			$this->format_the_html($expected),
			$this->format_the_html(ob_get_clean())
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'add-preconnect-cdn' );
	}
}
