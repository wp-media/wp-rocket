<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_cache_url
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaCacheUrl extends TestCase
{
	protected $cache;
	protected $cache_purge;

	public function setUp(): void
	{
		parent::setUp();
		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
	}

	public function tearDown(): void
	{
		unset($GLOBALS['kinsta_cache']);
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

		do_action($config['hook'], $config['url']);

		$requests = array_map(
			static function ( $request ) {
				return [
					'url'      => $request['url'],
					'blocking' => $request['args']['blocking'],
					'timeout'  => $request['args']['timeout'],
				];
			},
			$this->get_http_requests()
		);

		$this->assertSame( [ [ 'url' => $expected['url'] ] + $expected['config'] ], $requests );
	}
}
