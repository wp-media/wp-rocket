<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::rewrite
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group  CDN
 * @group  rewrite
 */
class Test_Rewrite extends TestCase {
	private $cnames;
	private $cdn_zone;
	private $site_url;
	private $content_url;
	private $includes_url;

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'site_url', [ $this, 'setSiteURL' ] );
		remove_filter( 'content_url', [ $this, 'setContentURL' ] );
		remove_filter( 'includes_url', [ $this, 'setIncludesURL' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURL( $site_url, $original, $expected ) {
		$this->cnames = [
			'cdn.example.org',
		];
		$this->cdn_zone = [
			'all'
		];
		$this->site_url = $site_url;
		$this->content_url = "{$site_url}/wp-content/";
		$this->includes_url = "{$site_url}/wp-includes/";

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		add_filter( 'site_url', [ $this, 'setSiteURL' ] );
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

	public function setCnames() {
		return $this->cnames;
	}

	public function setCDNZone() {
		return $this->cdn_zone;
	}

	public function setSiteURL() {
		return $this->site_url;
	}

	public function setContentURL() {
		return $this->content_url;
	}

	public function setIncludesURL() {
		return $this->includes_url;
	}
}
