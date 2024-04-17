<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSGeneration;

use Mockery;
use WP_Error;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;

class test_Task extends TestCase
{
    protected static $transients = ['rocket_critical_css_generation_process_running' => null, 'rocket_cpcss_generation_pending' => null];
    protected static $generation;
    protected static $processor;
    public function set_up()
    {
        parent::set_up();
        set_transient('rocket_critical_css_generation_process_running', ['total' => 1, 'items' => []]);
        self::$processor = Mockery::mock(ProcessorService::class);
        self::$generation = new CriticalCSSGeneration(self::$processor);
    }
    public function tear_down()
    {
        delete_transient('rocket_critical_css_generation_process_running');
        delete_transient('rocket_cpcss_generation_pending');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($item, $result, $transient)
    {
        $task = $this->get_reflective_method('task', CriticalCSSGeneration::class);
        if (false === $result['success']) {
            self::$processor->shouldReceive('process_generate')->once()->andReturnUsing(function () use($result) {
                return new WP_Error($result['code'], $result['message']);
            });
        } else {
            self::$processor->shouldReceive('process_generate')->once()->andReturn($result);
        }
        $this->assertFalse($task->invoke(self::$generation, $item));
        if (isset($transient)) {
            $this->assertSame($transient, get_transient('rocket_critical_css_generation_process_running'));
        } else {
            $this->assertSame(self::$transients['rocket_critical_css_generation_process_running'], $transient);
            $this->assertSame([$item['path'] => $item], get_transient('rocket_cpcss_generation_pending'));
        }
    }
}
