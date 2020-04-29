<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::rewrite_css_properties
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @group  CDN
 */
class Test_RewriteCssProperties extends TestCase {
	private $cnames;
	private $cdn_zone;

	public function setUp() {
		parent::setUp();

		$this->cnames = [
			'cdn.example.org',
		];
		$this->cdn_zone = [
			'all'
		];

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'do_rocket_cdn_css_properties', [ $this, 'return_false' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteCSSProperties( $original, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_css_content', $original )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnOriginalWhenFilterIsFalse( $original ) {
		add_filter( 'do_rocket_cdn_css_properties', [ $this, 'return_false' ] );

		$this->assertSame(
			$original,
			apply_filters( 'rocket_css_content', $original )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteCssProperties' );
	}

	public function setCnames() {
		return $this->cnames;
	}

	public function setCDNZone() {
		return $this->cdn_zone;
	}
}
