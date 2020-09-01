<?php

namespace WP_Rocket\Tests\Integration\Addon\Varnish\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::clean_home
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanHome extends TestCase {
	private $filter;
	private $option;

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option' ] );
		remove_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->option = $config['option'];
		$this->filter = $config['filter'];

		add_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option' ] );
		add_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter' ] );

		if ( $expected ) {
			Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://example.org/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'default',
					],
				]
            )
            ->andAlsoExpectIt()
            ->once()
            ->with(
				'http://example.org/page/.*',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'regex',
					],
				]
            );
		} else {
			Functions\expect( 'wp_remote_request' )
			->never();
		}

		do_action( 'before_rocket_clean_home', 'wp-rocket/cache', '' );
	}

	public function set_option() {
		return $this->option;
	}

	public function set_filter() {
		return $this->filter;
	}
}
