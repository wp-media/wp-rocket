<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::reset_job
 */
class Test_resetJob extends TestCase {

    /**
     * @var UsedCSS
     */
    protected $usedcss;

    public function set_up() {
        parent::set_up();

		$this->usedcss = $this->createPartialMock(UsedCSS::class, ['update_item']);
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('current_time')->justReturn($config['now']);

		$this->usedcss::$table_exists = true;

		/* @phpstan-ignore-next-line */
		$this->usedcss->expects(self::once())->method('update_item')->with($expected['id'], $expected['data'])->willReturn($config['updated']);

        $this->assertSame($expected['result'], $this->usedcss->reset_job($config['id'], $config['job_id']));
    }
}
