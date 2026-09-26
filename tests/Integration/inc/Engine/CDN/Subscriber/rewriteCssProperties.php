<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite_css_properties
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @group  CDN
 */
class Test_RewriteCssProperties extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->cnames = [
			'cdn.example.org',
		];
		$this->cdn_zone = [
			'all'
		];

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );

		// Subscriber::rewrite_css_properties() has its own, separate (pre-existing,
		// out-of-scope for this ticket) has_active_subscription() gate, unrelated to the
		// CDN driver resolution above — an active RocketCDN subscription state is required
		// for it to proceed at all, regardless of cdn_type/driver.
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ], HOUR_IN_SECONDS );
	}

	public function tear_down() {
		remove_filter( 'do_rocket_cdn_css_properties', [ $this, 'return_false' ] );
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteCSSProperties( $original, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_css_content', $original )
		);

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_usedcss_content', $original )
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

		$this->assertSame(
			$original,
			apply_filters( 'rocket_usedcss_content', $original )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteCssProperties' );
	}
}
