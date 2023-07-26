<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\FormatUrlTrait;

use Mockery;
use WP_Rocket\Engine\Preload\FormatUrlTrait;
use WP_Rocket\Tests\Integration\TestCase;

class Test_FormatUrl extends TestCase
{
	protected $trait;

	public function set_up()
	{
		parent::set_up();
		$this->trait = Mockery::mock(FormatUrlTrait::class)->makePartial();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->assertEquals($expected, $this->trait->format_url($config['url']));
	}
}
