<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\Varnish\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::clean_urls
 * @group  Addons
 * @group  Varnish
 */
class Test_CleanUrls extends TestCase {
	private $permalink_structure;
	private $varnish_auto_purge;
	private $did_filter;

	public function setUp() {
		parent::setUp();

		$this->did_filter   = [
			'rocket_varnish_ip' => 0,
		];
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'varnish_auto_purge_filter' ] );
		remove_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );
		remove_filter( 'rocket_varnish_ip', [ $this, 'rocket_varnish_ip_callback' ] );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldDoExpected( $config, $urls, $expected_clean_urls ) {
		$this->permalink_structure = $config['do_rocket_varnish_http_purge'];
		$this->varnish_auto_purge  = $config['varnish_auto_purge'];

		add_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'varnish_auto_purge_filter' ] );
		add_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );
		add_filter( 'rocket_varnish_ip', [ $this, 'rocket_varnish_ip_callback' ] );

		do_action( 'rocket_after_automatic_cache_purge', $urls );

		$this->assertEquals( count( $expected_clean_urls ), $this->did_filter['rocket_varnish_ip'] );
	}

	public function rocket_varnish_ip_callback( $value ) {
		$this->did_filter['rocket_varnish_ip'] ++;
		return $value;
	}

	public function varnish_auto_purge_filter() {
		return $this->varnish_auto_purge;
	}

	public function permalink_structure_filter() {
		return $this->permalink_structure;
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'cleanUrls' );
	}
}
