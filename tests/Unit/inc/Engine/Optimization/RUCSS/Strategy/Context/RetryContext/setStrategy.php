<?php

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;


/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext::set_strategy
 *
 * @group  RUCSS
 */
class Test_setStrategy extends TestCase {
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
