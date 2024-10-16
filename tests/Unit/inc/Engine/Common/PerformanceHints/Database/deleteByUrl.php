<?php

namespace Engine\Common\PerformanceHints\Database;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\AbstractQueries;
/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\AbstractQueries::delete_by_url
 *
 * @group PerformanceHints
 */
class Test_DeleteByUrl extends TestCase
{
    protected $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->createPartialMock( AbstractQueries::class, [ 'table_exists', 'get_rows_by_url', 'delete_item' ] );
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected) {
        $this->query->expects($this->once())
            ->method('table_exists')
            ->willReturn(true);

        $this->query->expects(self::once())->method('get_rows_by_url')->with($config['url'])->willReturn($config['results']);
        $this->configureDelete($config, $expected);

        $this->assertSame($expected, $this->query->delete_by_url($config['url']));
    }

    protected function configureDelete($config, $expected) {
        if(count($config['results']) === 0) {
            return;
        }
        if ( ! $expected ) {
            return;
        }

        $this->query->expects(self::exactly($config['deleted_item']))->method('delete_item')
			->withConsecutive([$config['delete_id_one']], [$config['delete_id_two']])
			->willReturnOnConsecutiveCalls($config['delete_return_one'], $config['delete_return_two']);
    }
}
