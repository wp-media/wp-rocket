<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

class Test_RocketIsPluginActive extends TestCase
{
    private $config;
    public function set_up()
    {
        parent::set_up();
        if (empty($this->config)) {
            $this->loadConfig();
        }
        update_option('active_plugins', $this->config['active_plugins']);
    }
    /**
     * @dataProvider nonMultisiteTestData
     */
    public function testShouldReturnCorrectStateWhenNotMultisite($plugin, $expected)
    {
        $this->assertSame($expected, rocket_is_plugin_active($plugin));
    }
    /**
     * @dataProvider multisiteTestData
     * @group        Multisite
     */
    public function testShouldReturnCorrectStateWhenMultisite($plugin, $expected)
    {
        update_site_option('active_sitewide_plugins', $this->config['active_sitewide_plugins']);
        $this->assertSame($this->config['active_sitewide_plugins'], get_site_option('active_sitewide_plugins'));
        $this->assertSame($expected, rocket_is_plugin_active($plugin));
    }
    public function nonMultisiteTestData()
    {
        if (empty($this->config)) {
            $this->loadConfig();
        }
        return $this->config['test_data']['non_multisite'];
    }
    public function multisiteTestData()
    {
        if (empty($this->config)) {
            $this->loadConfig();
        }
        return $this->config['test_data']['multisite'];
    }
    private function loadConfig()
    {
        $this->config = $this->getTestData(__DIR__, 'rocketIsPluginActive');
    }
}
