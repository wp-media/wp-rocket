<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\TestCase;

class Test_Activate extends TestCase
{
    public function testShouldSetCorrectHooks()
    {
        $advanced_cache = new AdvancedCache('http://path/to/filesystem', null);
        $advanced_cache->activate();
        $this->assertEquals(10, has_action('rocket_activation', [$advanced_cache, 'update_advanced_cache']));
    }
}
