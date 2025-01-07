<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\UnlimitedElements;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\UnlimitedElements::exclude_inline_from_rucss
 * @group UnlimitedElements
 * @group ThirdParty
 */
class Test_ExcludeInlineFromRucss extends TestCase {
	protected $unlimited_elements;

	protected function setUp(): void
	{
		parent::setUp();
		$this->unlimited_elements = new UnlimitedElements();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

		$this->assertSame( $expected, $this->unlimited_elements->exclude_inline_from_rucss( $config['excluded'] ) );
	}
}
