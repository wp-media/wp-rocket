<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::set_varnish_purge_request_host
 *
 * @group Cloudflare
 */
class TestSetVarnishPurgeRequestHost extends TestCase {
	use HttpRequestTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	private $option;
	private $filter;

	public function set_up() {
		parent::set_up();

		$this->setup_http();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option'] );
		remove_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter'] );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->option = $config['option'];
		$this->filter = $config['filter'];

		add_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option'] );
		add_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter'] );


		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_purge_request_host', $config['value'] )
		);
	}

	public function set_option() {
		return $this->option;
	}

	public function set_filter() {
		return $this->filter;
	}
}
