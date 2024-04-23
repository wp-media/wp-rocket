<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::create_new_job
 */
class Test_createNewJob extends TestCase {

    /**
     * @var UsedCSS
     */
    protected $usedcss;

    public function set_up() {
        parent::set_up();

        $this->usedcss = $this->createPartialMock(UsedCSS::class, ['add_item']);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('current_time')->justReturn($config['now']);
		$this->usedcss::$table_exists = true;

		$this->usedcss->expects(self::once())->method('add_item')->with($expected['item'])->willReturn($config['result']);

        $this->assertSame($expected['result'], $this->usedcss->create_new_job($config['url'], $config['job_id'], $config['queue_name'], $config['is_mobile']));
    }
}
