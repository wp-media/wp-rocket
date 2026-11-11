<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Avada;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Avada;

/**
 * Test class covering \WP_Rocket\ThirdParty\Avada::fix_regex_lazyload_bg_images
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_FixRegexLazyloadBgImages extends TestCase
{
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new Avada(Mockery::mock(Options_Data::class));
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->assertSame($expected, $this->subscriber->fix_regex_lazyload_bg_images($config));
	}
}
