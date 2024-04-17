<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;


class Test_Enable extends TestCase
{
    protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/enable.php';
    /**
     * @dataProvider providerTestData
     */
    public function testShouldEnableCDNOptions($expected)
    {
        $this->dumpResults = isset($expected['dump_results']) ? $expected['dump_results'] : false;
        $this->generateEntriesShouldExistAfter($expected['cleaned']);
        // Run it.
        $this->getCDNOptionsManager()->enable('https://rocketcdn.me');
        // Check the settings.
        $this->assertSettings($expected);
        // Check the transient was deleted.
        $this->assertFalse(get_transient('rocketcdn_status'));
        // Check the cache.
        $this->assertCacheDeleted($expected);
    }
}
