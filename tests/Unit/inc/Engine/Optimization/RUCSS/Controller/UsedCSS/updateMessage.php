<?php

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::check_job_status
 *
 * @group  RUCSS
 */
class Test_UpdateMessage extends TestCase {
	protected $usedCss;
	public function setUp():void {
		parent::setUp();
		$this->usedCss = $this->createPartialMock(UsedCSS::class, ['update_item']);

	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected )
	{
		$this->usedCss->expects(self::once())->method('update_item')->with(
			$expected['ressources']['job_id'],
			[
				'error_message' => $expected['ressources']['error_message']
			]
		)->willReturn($config['result']);

		Functions\expect('current_time')->andReturn('2023-10-11 20:21:00');

		$this->assertSame(
			$expected['result'],
			$this->usedCss->update_message(
				$config['ressources']['job_id'],
				$config['ressources']['code'],
				$config['ressources']['message'],
				$config['ressources']['previous_message']
			)
		);
	}
}
