<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Avada;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Avada;
use WPMedia\PHPUnit\Unit\TestCase;

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

	public function testShouldDefinedConstant() {
		$this->assertFalse(defined('FUSION_DISABLE_COMPILERS'));
		$this->subscriber->disable_compilers();
		$this->assertTrue(defined('FUSION_DISABLE_COMPILERS'));

	}
}
