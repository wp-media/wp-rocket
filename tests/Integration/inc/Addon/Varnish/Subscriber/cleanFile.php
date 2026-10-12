<?php

namespace WP_Rocket\Tests\Integration\Addon\Varnish\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Varnish\Subscriber::clean_file
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanFile extends TestCase {
	use HttpRequestTrait;

	private $filter;
	private $option;
	private $purge_requests = [];

	public function set_up() {
		parent::set_up();

		$this->setup_http();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', [ $this, 'set_option' ] );
		remove_filter( 'do_rocket_varnish_http_purge', [ $this, 'set_filter' ] );
		remove_filter( 'pre_http_request', [ $this, 'record_purge_request' ], 5 );

		$this->tear_down_http();

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

		add_filter( 'pre_http_request', [ $this, 'record_purge_request' ], 5, 3 );

		do_action( $config['hook'], $config['arg'] );

		$this->assertSame( $purges, $this->purge_requests );
	}

	/**
	 * Records the purge requests Varnish sends, without answering them.
	 *
	 * @param mixed  $preempt Preemptive response.
	 * @param array  $args    Request arguments.
	 * @param string $url     Request URL.
	 *
	 * @return mixed
	 */
	public function record_purge_request( $preempt, $args, $url ) {
		$this->purge_requests[] = [
			'url'         => $url,
			'method'      => $args['method'],
			'blocking'    => $args['blocking'],
			'redirection' => $args['redirection'],
			'headers'     => $args['headers'],
		];

		return $preempt;
	}

	public function set_option() {
		return $this->option;
	}

	public function set_filter() {
		return $this->filter;
	}
}
