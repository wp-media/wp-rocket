<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Avada;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Avada;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::disable_compilers
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_DisableCompilers extends TestCase {
	protected $options;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = \Mockery::mock(Options_Data::class);
		$this->subscriber = new Avada($this->options);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDefinedConstant($config, $expected) {
		$this->options->expects()->get('remove_unused_css' , false)->andReturn($config['rucss_enable']);
		$this->assertFalse(defined('FUSION_DISABLE_COMPILERS'));
		$this->subscriber->disable_compilers();
		$this->assertSame($expected, defined('FUSION_DISABLE_COMPILERS'));

	}
}
