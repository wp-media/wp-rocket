<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\CheckExcludedTrait;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\CheckExcludedTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_AddCacheRejectUriToExcluded extends TestCase
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
		Functions\expect('get_rocket_option')->with('cache_reject_uri', [])->andReturn($config['option_excluded_urls']);
		Functions\expect('get_rocket_cache_reject_uri')->andReturn($config['excluded_urls']);
		$this->assertSame($expected, $this->trait->add_cache_reject_uri_to_excluded($config['regexes']));
	}
}
