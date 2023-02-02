<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\CheckExcludedTrait;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\CheckExcludedTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class Test_IsExcludedByFilter extends TestCase
{
	protected $trait;

	protected function setUp(): void
	{
		parent::setUp();
		$this->trait = Mockery::mock(CheckExcludedTrait::class)->makePartial();
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when('wp_parse_url')->justReturn($config['queries']);
		Functions\when('add_query_arg')->alias(function ($queries, $url) use ($config) {
			if(in_array($url, $config['regexes'])) {
				return $config['regex_with_query'];
			}
			return $config['url_with_query'];
		});
		Filters\expectApplied('rocket_preload_exclude_urls')->with([])->andReturn($config['regexes']);
		Filters\expectApplied('rocket_preload_exclude_urls_regexes')->with([])->andReturn($config['regexes']);

		$method = $this->get_reflective_method('is_excluded_by_filter',  get_class($this->trait));
		$this->assertSame($expected, $method->invokeArgs($this->trait,[$config['url']]));
	}
}
