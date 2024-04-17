<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

class Test_CleanMinify extends TestCase
{
    use DBTrait;
    protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/AdminSubscriber/cleanMinify.php';
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::installFresh();
    }
    public static function tear_down_after_class()
    {
        self::uninstallAll();
        parent::tear_down_after_class();
    }
    /**
     * @dataProvider providerTestData
     */
    public function testCleanMinify($settings, $expected)
    {
        $this->dumpResults = isset($expected['dump_results']) ? $expected['dump_results'] : false;
        $this->generateEntriesShouldExistAfter($expected['cleaned']);
        // Run it.
        $this->mergeExistingSettingsAndUpdate($settings);
        $this->checkEntriesDeleted($expected['cleaned']);
        $this->checkShouldNotDeleteEntries();
    }
}
