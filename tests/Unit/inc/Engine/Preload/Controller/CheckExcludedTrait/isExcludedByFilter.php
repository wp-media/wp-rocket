<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\CheckExcludedTrait;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\CheckExcludedTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

class Test_IsExcludedByFilter extends TestCase
{
	protected $trait;

	protected function setUp(): void
	{
		parent::setUp();
		$this->trait = Mockery::mock(CheckExcludedTrait::class)->makePartial();
		$GLOBALS['wp_rewrite'] = (object) [ 'pagination_base' => 'page' ];
	}

	public function tearDown(): void {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		global $wp_rewrite;
		$pagination_regex = "/$wp_rewrite->pagination_base/\d+";
		$config['regexes'][]= $pagination_regex;
		Filters\expectApplied('rocket_preload_exclude_urls')->with([$pagination_regex])->andReturn($config['regexes']);
		$method = $this->get_reflective_method('is_excluded_by_filter',  get_class($this->trait));
		$this->assertSame($expected, $method->invokeArgs($this->trait,[$config['url']]));
	}
}
