<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Polygon;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Polygon;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Polygon::add_rucss_content_excluded
 *
 * @group  ThirdParty
 */
class Test_AddRucssContentExcluded extends TestCase
{
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new Polygon();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->assertSame($expected, $this->subscriber->add_rucss_content_excluded($config));
	}
}
