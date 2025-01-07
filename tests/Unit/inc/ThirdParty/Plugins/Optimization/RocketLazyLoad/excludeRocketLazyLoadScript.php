<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad::exclude_rocket_lazyload_script
 */
class Test_ExcludeRocketLazyLoadScript extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new RocketLazyLoad();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $excluded, $expected ) {
		$this->assertSame( $expected, $this->subscriber->exclude_rocket_lazyload_script( $excluded ) );
	}
}
