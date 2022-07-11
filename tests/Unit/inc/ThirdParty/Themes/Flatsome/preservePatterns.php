<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Flatsome;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Flatsome;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Flatsome::preserve_patterns
 * @group ThirdParty
 */
class Test_PreservePatterns extends TestCase {
	protected $flatsome;

	protected function setUp(): void
	{
		parent::setUp();
		$this->flatsome = new Flatsome();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

		$this->assertSame( $expected, $this->flatsome->preserve_patterns( $config['excluded'] ) );
	}
}
