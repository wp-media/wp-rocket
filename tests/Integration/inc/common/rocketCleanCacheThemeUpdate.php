<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_RocketCleanCacheThemeUpdate extends FilesystemTestCase
{
    use DBTrait;
    protected $path_to_test_data = '/inc/common/rocketCleanCacheThemeUpdate.php';
    protected static $hooks;
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::installFresh();
        self::$hooks = $GLOBALS['wp_filter']['upgrader_process_complete']->callbacks;
    }
    public static function tear_down_after_class()
    {
        self::uninstallAll();
        parent::tear_down_after_class();
        $GLOBALS['wp_filter']['upgrader_process_complete']->callbacks = self::$hooks;
    }
    public function set_up()
    {
        parent::set_up();
        // Unregister all of the callbacks registered to the action event for these tests.
        $GLOBALS['wp_filter']['upgrader_process_complete']->callbacks = [];
        add_action('upgrader_process_complete', 'rocket_clean_cache_theme_update', 10, 2);
    }
    public function tear_down()
    {
        parent::tear_down();
        unset($GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang']);
        unset($GLOBALS['debug_fs']);
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldCleanExpected($hook_extra, $expected)
    {
        if (empty($expected['cleaned'])) {
            Functions\expect('rocket_clean_domain')->never();
        }
        if (isset($expected['debug']) && $expected['debug']) {
            $GLOBALS['debug_fs'] = true;
        }
        $this->dumpResults = isset($expected['dump_results']) ? $expected['dump_results'] : false;
        $this->generateEntriesShouldExistAfter($expected['cleaned']);
        // Update it.
        do_action('upgrader_process_complete', null, $hook_extra);
        $this->checkEntriesDeleted($expected['cleaned']);
        $this->checkShouldNotDeleteEntries();
    }
}
