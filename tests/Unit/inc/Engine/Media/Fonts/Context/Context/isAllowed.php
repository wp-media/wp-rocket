<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Context\Context;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
use Brain\Monkey\Functions;

class Test_IsAllowed extends TestCase
{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected($config, $expected)
	{
		$context = new Context();

		Functions\expect('get_option')
			->once()
			->with('local_google_fonts')
			->andReturn($config['local_google_fonts']);

		$this->assertSame($expected, $context->is_allowed($config));
	}
}
