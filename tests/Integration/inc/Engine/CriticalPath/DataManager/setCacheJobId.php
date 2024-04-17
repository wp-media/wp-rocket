<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\TestCase;

class Test_SetCacheJobId extends TestCase
{
    private $transient;
    public function tear_down()
    {
        parent::tear_down();
        delete_transient($this->transient);
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($item_url, $job_id, $is_mobile = false)
    {
        $this->transient = 'rocket_specific_cpcss_job_' . md5($item_url) . ($is_mobile ? '_mobile' : '');
        $data_manager = new DataManager('', null);
        $actual = $data_manager->set_cache_job_id($item_url, $job_id, $is_mobile);
        $this->assertTrue($actual);
        $this->assertSame($job_id, get_transient($this->transient));
    }
}
