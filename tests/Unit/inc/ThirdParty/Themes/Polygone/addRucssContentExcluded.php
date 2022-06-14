<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Polygone;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Polygone;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Polygone::add_rucss_content_excluded
 *
 * @group  ThirdParty
 */
class Test_AddRucssContentExcluded extends TestCase
{
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new Polygone();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->assertSame($expected, $this->subscriber->add_rucss_content_excluded($config));
	}
}
