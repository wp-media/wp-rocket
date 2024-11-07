<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Media\Fonts\Factory\Fonts\GoogleFontV2;

use WP_Rocket\Engine\Media\Fonts\Factory\Fonts\GoogleFontV2;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_GetLocalUrl extends TestCase
{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected($config, $expected)
	{
		$googleFontV1 = new GoogleFontV2($config['font_url']);

		Functions\expect('WP_Rocket\Engine\Media\Fonts\Factory\Fonts\rocket_get_constant')
			->once()
			->with('WP_ROCKET_CACHE_PATH')
			->andReturn('/wp-content/cache/wp-rocket/');

		$result = $googleFontV1->get_local_url();
		$this->assertEquals($expected, $result);
	}
}
