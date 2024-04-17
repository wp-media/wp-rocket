<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;


class Test_Disable extends TestCase
{
    protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/disable.php';
    public function set_up()
    {
        parent::set_up();
        add_option('rocketcdn_user_token', '123456');
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldDisableCDNOptions($expected)
    {
        $this->dumpResults = isset($expected['dump_results']) ? $expected['dump_results'] : false;
        $this->generateEntriesShouldExistAfter($expected['cleaned']);
        // Run it.
        $this->getCDNOptionsManager()->disable();
        // Check the settings.
        $this->assertSettings($expected);
        // Check the option and transient are deleted.
        $this->assertFalse(get_option('rocketcdn_user_token'));
        $this->assertFalse(get_transient('rocketcdn_status'));
        // Check the cache.
        $this->assertCacheDeleted($expected);
    }
}
