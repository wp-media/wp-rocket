<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\FormatUrlTrait;

use Mockery;
use WP_Rocket\Engine\Preload\FormatUrlTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_FormatUrl extends TestCase
{
	protected $trait;

	protected function set_up()
	{
		parent::set_up();
		$this->trait = Mockery::mock(FormatUrlTrait::class)->makePartial();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\expect('wp_parse_url')->with($config['url'], PHP_URL_QUERY )->andReturn($config['queries']);
		Functions\expect('add_query_arg')->with($config['queries_array'], $config['simple_url'])->andReturn($config['return_url']);
		Functions\when('untrailingslashit')->alias(function ($url) {
			return rtrim( $url, '/\\' );
		});
		$this->assertEquals($expected, $this->trait->format_url($config['url']));
	}
}
