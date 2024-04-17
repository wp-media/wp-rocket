<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

class Test_ShowEmptyProductGalleryWithDelayJS extends TestCase
{
    use WooTrait, DBTrait;
    private $delay_js_option;
    private $product_with_gallery;
    private $product_without_gallery;
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
    public function set_up()
    {
        parent::set_up();
        $this->product_without_gallery = $this->create_product();
        $this->product_with_gallery = $this->create_product([1, 2, 3]);
    }
    public function tear_down() : void
    {
        parent::tear_down();
        remove_filter('pre_get_rocket_option_delay_js', [$this, 'set_delay_js']);
        $this->restoreWpHook('wp_head');
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        $this->delay_js_option = $config['is_allowed'] ?? false;
        $in_product_page = $config['in_product_page'] ?? null;
        $has_images = $config['has_images'] ?? null;
        add_filter('pre_get_rocket_option_delay_js', [$this, 'set_delay_js']);
        if ($in_product_page) {
            $this->go_to($has_images ? $this->product_with_gallery->get_permalink() : $this->product_without_gallery->get_permalink());
        } else {
            $this->go_to(home_url());
        }
        $this->unregisterAllCallbacksExcept('wp_head', 'show_empty_product_gallery_with_delayJS');
        do_action('wp_head');
        $this->expectOutputString($expected);
    }
    public function set_delay_js()
    {
        return $this->delay_js_option;
    }
}
