<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_cache_home
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaCacheHome extends TestCase
{
	use HttpRequestTrait;

	protected $cache;
	protected $cache_purge;
	private $kinsta_requests = [];

	public function setUp(): void
	{
		parent::setUp();
		$this->setup_http();
		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
	}

	public function tearDown(): void
	{
		unset($GLOBALS['kinsta_cache']);
		remove_filter( 'pre_http_request', [ $this, 'record_kinsta_request' ], 5 );
		$this->tear_down_http();
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->config['http'] = [
			$expected['url'] => [
				'body'     => '',
				'response' => [ 'code' => 200, 'message' => 'OK' ],
			],
		];
		add_filter( 'pre_http_request', [ $this, 'record_kinsta_request' ], 5, 3 );

		do_action('after_rocket_clean_home', $config['root'], $config['lang']);

		$this->assertSame( [ [ 'url' => $expected['url'] ] + $expected['config'] ], $this->kinsta_requests );
	}

	/**
	 * Records the Kinsta cache clearing requests, without answering them.
	 *
	 * @param mixed  $preempt Preemptive response.
	 * @param array  $args    Request arguments.
	 * @param string $url     Request URL.
	 *
	 * @return mixed
	 */
	public function record_kinsta_request( $preempt, $args, $url ) {
		$this->kinsta_requests[] = [
			'url'      => $url,
			'blocking' => $args['blocking'],
			'timeout'  => $args['timeout'],
		];

		return $preempt;
	}
}
