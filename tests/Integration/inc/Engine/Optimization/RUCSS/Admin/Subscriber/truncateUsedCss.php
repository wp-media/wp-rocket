<?php
declare (strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

class Test_TruncateUsedCss extends TestCase
{
    use DBTrait;
    private $input;
    public static function set_up_before_class()
    {
        self::installFresh();
        parent::set_up_before_class();
    }
    public static function tear_down_after_class()
    {
        parent::tear_down_after_class();
        self::uninstallAll();
    }
    public function tear_down() : void
    {
        remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldTruncateTableWhenOptionIsEnabled($input)
    {
        $container = apply_filters('rocket_container', null);
        $rucss_usedcss_query = $container->get('rucss_used_css_query');
        $this->input = $input;
        add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        foreach ($input['items'] as $item) {
            $rucss_usedcss_query->add_item($item);
        }
        $result = $rucss_usedcss_query->query();
        $this->assertCount(count($input['items']), $result);
        do_action('switch_theme', 'Test Theme', new \WP_Theme('test', 'test'), new \WP_Theme('test2', 'test2'));
        $rucss_usedcss_query = $container->get('rucss_used_css_query');
        $resultAfterTruncate = $rucss_usedcss_query->query();
        if ($this->input['remove_unused_css']) {
            $this->assertCount(0, $resultAfterTruncate);
        } else {
            $this->assertCount(count($input['items']), $result);
        }
    }
    public function set_rucss_option()
    {
        return $this->input['remove_unused_css'] ?? false;
    }
}
