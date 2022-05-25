<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\RocketCache;

use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\RocketCache::create_or_update
 *
 * @group Database
 * @group Preload
 */
class Test_CreateOrUpdate extends TestCase {
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(RocketCache::class, ['query','add_item','update_item']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when('current_time')->justReturn($config['time']);
		$this->query->expects(self::once())->method('query')->with([
			'url' => $config['resource']['url'],
			'is_mobile' => $config['resource']['is_mobile'],
		])->willReturn($config['rows']);

		$this->configureCreate($config);
		$this->configureUpdate($config);

		$this->assertSame($expected, $this->query->create_or_update($config['resource']));
	}

	protected function configureCreate($config) {
		if(count($config['rows']) > 0) {
			return;
		}
		$this->query->expects(self::once())->method('add_item')->with($config['save'])->willReturn($config['id']);
	}

	protected function configureUpdate($config) {
		if(count($config['rows']) === 0) {
			return;
		}
		$this->query->expects(self::exactly(2))->method('update_item')->withConsecutive($config['update_time'], [$config['id'], $config['save']]);
	}
}
