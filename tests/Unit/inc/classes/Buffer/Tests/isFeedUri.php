<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Tests;

use WP_Rewrite;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Buffer\Tests::is_feed_uri
 *
 * @group  Buffer
 */
class Test_IsFeedURI extends TestCase {
	protected $tests;
	protected $config;

	public function setUp(): void
	{
		parent::setUp();
		$this->config = \Mockery::mock(Config::class);
		$this->tests = \Mockery::mock(Tests::class.'[get_clean_request_uri]', [$this->config]);
	}


	protected function tearDown(): void
	{
		unset($GLOBALS['wp_rewrite']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected)
	{
		$wp_rewrite = new WP_Rewrite();
		$wp_rewrite->feed_base = $config['feed_base'];
		$GLOBALS['wp_rewrite'] = $wp_rewrite;
		$this->tests->expects()->get_clean_request_uri()->andReturn($config['clean_uri']);
		$this->assertSame($expected, $this->tests->is_feed_uri());
	}
}
