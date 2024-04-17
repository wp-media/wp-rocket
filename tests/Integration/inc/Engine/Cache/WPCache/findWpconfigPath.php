<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use ReflectionMethod;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_FindWpconfigPath extends FilesystemTestCase
{
    protected $path_to_test_data = '/inc/Engine/Cache/WPCache/findWpconfigPath.php';
    private static $wp_cache;
    private $config_file_name = null;
    public static function set_up_before_class()
    {
        $container = apply_filters('rocket_container', null);
        self::$wp_cache = $container->get('wp_cache');
    }
    public function set_up()
    {
        parent::set_up();
        $this->abspath = $this->filesystem->getUrl($this->config['vfs_dir']);
    }
    public function tear_down()
    {
        if (!is_null($this->config_file_name)) {
            remove_filter('rocket_wp_config_name', [$this, 'changeWpconfigFileName']);
        }
        parent::tear_down();
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnValidConfigFileName($config, $expected)
    {
        $this->config_file_name = isset($config['config_file_name']) ? $config['config_file_name'] : null;
        if (!is_null($this->config_file_name)) {
            add_filter('rocket_wp_config_name', [$this, 'changeWpconfigFileName']);
        }
        $find_wpconfig_path = new ReflectionMethod('WP_Rocket\\Engine\\Cache\\WPCache', 'find_wpconfig_path');
        $find_wpconfig_path->setAccessible(true);
        $actual = $find_wpconfig_path->invoke(self::$wp_cache);
        if (false !== $actual) {
            $actual = $this->filesystem->getUrl($actual);
        }
        $this->assertEquals($expected, $actual);
    }
    public function changeWpconfigFileName($config_original_file_name)
    {
        return $this->config_file_name;
    }
}
