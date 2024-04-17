<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_GetAdvancedCacheContent extends FilesystemTestCase
{
    protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/getAdvancedCacheContent.php';
    // Saves and restores original settings.
    protected static $use_settings_trait = false;
    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnExpectedContent($settings, $expected)
    {
        $this->mergeExistingSettingsAndUpdate($settings);
        // Run it.
        $advanced_cache = new AdvancedCache($this->filesystem->getUrl($this->config['vfs_dir']), $this->filesystem);
        $this->assertSame($expected, $advanced_cache->get_advanced_cache_content());
    }
}
