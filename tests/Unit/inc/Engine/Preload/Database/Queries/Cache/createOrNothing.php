<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::create_or_nothing
 *
 * @group Database
 * @group Preload
 */
class Test_CreateOrNothing extends TestCase {
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['query','add_item']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when('current_time')->justReturn($config['time']);
		$this->query->expects(self::once())->method('query')->with([
			'url' => $config['resource']['url'],
		])->willReturn($config['rows']);

		$this->configureCreate($config);

		$this->assertSame($expected, $this->query->create_or_nothing($config['resource']));
	}

	protected function configureCreate($config) {
		if(count($config['rows']) > 0) {
			return;
		}
		$this->query->expects(self::once())->method('add_item')->with($config['save'])->willReturn($config['id']);
	}
}
