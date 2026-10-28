<?php

namespace WP_Rocket\Tests\Integration\Addon\Varnish\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Varnish\Subscriber::clean_file
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanFile extends TestCase {
	private $filter;
	private $option;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option' ] );
		remove_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->option = $config['option'];
		$this->filter = $config['filter'];

		add_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option' ] );
		add_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter' ] );

		$purges = $expected ? [
			[
				'url'         => 'http://example.org/about/.*',
				'method'      => 'PURGE',
				'blocking'    => false,
				'redirection' => 0,
				'headers'     => [
					'host'           => 'example.org',
					'X-Purge-Method' => 'regex',
				],
			],
		] : [];

		$this->config['http'] = array_fill_keys(
			array_column( $purges, 'url' ),
			[
				'headers'  => [],
				'body'     => '',
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'cookies'  => [],
			]
		);

		do_action( $config['hook'], $config['arg'] );

		$requests = array_map(
			static function ( $request ) {
				return [
					'url'         => $request['url'],
					'method'      => $request['args']['method'],
					'blocking'    => $request['args']['blocking'],
					'redirection' => $request['args']['redirection'],
					'headers'     => $request['args']['headers'],
				];
			},
			$this->get_http_requests()
		);

		$this->assertSame( $purges, $requests );
	}

	public function set_option() {
		return $this->option;
	}

	public function set_filter() {
		return $this->filter;
	}
}
