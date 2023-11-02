<?php

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobFoundNoResult::execute
 *
 * @group  RUCSS
 */
class Test_JobFoundNoResult_Execute extends TestCase {
	public function setUp():void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected )
	{
	}
}
