<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Jetpack;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::sitemap_preload_jetpack_option
 * @group Jetpack
 * @group ThirdParty
 */
class Test_SitemapPreloadJetpackOption extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new Jetpack($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->stubTranslationFunctions();
		$this->assertSame($expected, $this->subscriber->sitemap_preload_jetpack_option($config));
	}
}
