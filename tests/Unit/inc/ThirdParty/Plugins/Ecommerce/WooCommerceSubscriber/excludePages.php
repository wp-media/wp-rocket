<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use Mockery;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::exclude_pages
 */
class Test_excludePages extends TestCase {

    /**
     * @var HTML
     */
    protected $delayjs_html;

    /**
     * @var WooCommerceSubscriber
     */
    protected $woocommercesubscriber;

    public function set_up() {
        parent::set_up();
        $this->delayjs_html = Mockery::mock(HTML::class);

        $this->woocommercesubscriber = new WooCommerceSubscriber($this->delayjs_html);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('get_option')->justReturn(false);
		Functions\when('get_post_status')->justReturn('publish');
		Functions\expect('wc_get_page_id')->with('checkout')->andReturn($config['checkout_id']);
		Functions\expect('wc_get_page_id')->with('cart')->andReturn($config['cart_id']);
		Functions\expect('wc_get_page_id')->with('myaccount')->andReturn($config['myaccount_id']);
		Functions\expect('get_rocket_i18n_translated_post_urls')->with($expected['checkout_id'], $expected['type'], $expected['pattern'])->andReturn($config['i18n_urls']);
		Functions\expect('get_rocket_i18n_translated_post_urls')->with($expected['cart_id'])->andReturn($config['i18n_urls']);
		Functions\expect('get_rocket_i18n_translated_post_urls')->with($expected['myaccount_id'], $expected['type'], $expected['pattern'])->andReturn($config['i18n_urls']);
        $this->assertSame($expected['urls'], $this->woocommercesubscriber->exclude_pages($config['urls']));
    }
}
